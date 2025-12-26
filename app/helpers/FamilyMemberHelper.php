<?php

class FamilyMemberHelper {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get all active family member profiles for a user
     */
    public function getFamilyMemberProfiles($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM family_member_profiles 
                WHERE user_id = ? AND is_active = TRUE 
                ORDER BY 
                    CASE member_type 
                        WHEN 'child' THEN 1
                        WHEN 'teenager' THEN 2
                        WHEN 'adolescent' THEN 3
                        WHEN 'adult' THEN 4
                    END,
                    created_at ASC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting family member profiles: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get family member profile by ID
     */
    public function getFamilyMemberProfile($memberId, $userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM family_member_profiles 
                WHERE id = ? AND user_id = ? AND is_active = TRUE
            ");
            $stmt->execute([$memberId, $userId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error getting family member profile: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Save or update a family member profile
     */
    public function saveFamilyMemberProfile($userId, $data) {
        try {
            $this->pdo->beginTransaction();
            
            $memberId = $data['id'] ?? null;
            $memberType = $data['member_type'] ?? 'adult';
            $memberCount = intval($data['member_count'] ?? 1);
            $dietGoal = $data['diet_goal'] ?? 'general';
            $calorieTarget = !empty($data['calorie_target']) ? intval($data['calorie_target']) : null;
            $currentWeight = !empty($data['current_weight']) ? floatval($data['current_weight']) : null;
            $targetWeight = !empty($data['target_weight']) ? floatval($data['target_weight']) : null;
            $height = !empty($data['height']) ? floatval($data['height']) : null;
            $age = !empty($data['age']) ? intval($data['age']) : null;
            $activityLevel = $data['activity_level'] ?? 'moderately_active';
            $preferences = $data['dietary_preferences'] ?? null;
            
            // Calculate BMI if height and weight are provided
            $bmi = null;
            if ($height > 0 && $currentWeight > 0) {
                $heightInMeters = $height / 100;
                $bmi = round($currentWeight / ($heightInMeters * $heightInMeters), 1);
            }
            
            $preferencesJson = $preferences ? json_encode($preferences) : null;
            
            if ($memberId) {
                // Update existing profile
                $stmt = $this->pdo->prepare("
                    UPDATE family_member_profiles 
                    SET member_type = ?, member_count = ?, diet_goal = ?, calorie_target = ?, 
                        current_weight = ?, target_weight = ?, height = ?, age = ?, 
                        activity_level = ?, bmi = ?, dietary_preferences = ?
                    WHERE id = ? AND user_id = ?
                ");
                $stmt->execute([
                    $memberType, $memberCount, $dietGoal, $calorieTarget,
                    $currentWeight, $targetWeight, $height, $age,
                    $activityLevel, $bmi, $preferencesJson, $memberId, $userId
                ]);
                $resultId = $memberId;
            } else {
                // Insert new profile
                $stmt = $this->pdo->prepare("
                    INSERT INTO family_member_profiles 
                    (user_id, member_type, member_count, diet_goal, calorie_target, 
                     current_weight, target_weight, height, age, activity_level, bmi, dietary_preferences)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $userId, $memberType, $memberCount, $dietGoal, $calorieTarget,
                    $currentWeight, $targetWeight, $height, $age, $activityLevel, $bmi, $preferencesJson
                ]);
                $resultId = $this->pdo->lastInsertId();
            }
            
            $this->pdo->commit();
            return $resultId;
        } catch (Exception $e) {
            $this->pdo->rollback();
            error_log("Error saving family member profile: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Delete a family member profile
     */
    public function deleteFamilyMemberProfile($memberId, $userId) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE family_member_profiles 
                SET is_active = FALSE 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$memberId, $userId]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error deleting family member profile: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get total family members count
     */
    public function getTotalFamilyMembers($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT SUM(member_count) as total 
                FROM family_member_profiles 
                WHERE user_id = ? AND is_active = TRUE
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch();
            return intval($result['total'] ?? 0);
        } catch (Exception $e) {
            error_log("Error getting total family members: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Analyze product suitability for all family members
     * Returns: 'recommended', 'caution', 'avoid', or 'neutral'
     */
    public function analyzeProductForFamily($product, $userId) {
        // Skip diet analysis for Home Cleaning products (they are not food items)
        if (isset($product['category_name']) && strtolower(trim($product['category_name'])) === 'home cleaning') {
            return ['status' => 'neutral', 'message' => 'Non-food item - no diet analysis available', 'details' => []];
        }
        
        $profiles = $this->getFamilyMemberProfiles($userId);
        
        if (empty($profiles)) {
            return ['status' => 'neutral', 'message' => 'No family profiles set', 'details' => []];
        }
        
        $recommendations = [];
        $totalMembers = 0;
        $recommendedCount = 0;
        $cautionCount = 0;
        $avoidCount = 0;
        
        foreach ($profiles as $profile) {
            $memberCount = intval($profile['member_count']);
            $totalMembers += $memberCount;
            
            $suitability = $this->analyzeProductForProfile($product, $profile);
            $recommendations[] = [
                'type' => $profile['member_type'],
                'count' => $memberCount,
                'suitability' => $suitability['status'],
                'message' => $suitability['message']
            ];
            
            // Count by status
            switch ($suitability['status']) {
                case 'recommended':
                    $recommendedCount += $memberCount;
                    break;
                case 'caution':
                    $cautionCount += $memberCount;
                    break;
                case 'avoid':
                    $avoidCount += $memberCount;
                    break;
            }
        }
        
        // Determine overall recommendation
        $overallStatus = 'neutral';
        $overallMessage = 'Product analysis for family';
        
        if ($avoidCount > 0 && $avoidCount >= ($totalMembers * 0.5)) {
            $overallStatus = 'avoid';
            $overallMessage = "Not suitable for " . $avoidCount . " family member(s)";
        } elseif ($cautionCount > 0 && $cautionCount >= ($totalMembers * 0.5)) {
            $overallStatus = 'caution';
            $overallMessage = "Use caution for " . $cautionCount . " family member(s)";
        } elseif ($recommendedCount > 0 && $recommendedCount >= ($totalMembers * 0.5)) {
            $overallStatus = 'recommended';
            $overallMessage = "Recommended for " . $recommendedCount . " family member(s)";
        }
        
        return [
            'status' => $overallStatus,
            'message' => $overallMessage,
            'details' => $recommendations,
            'summary' => [
                'total_members' => $totalMembers,
                'recommended' => $recommendedCount,
                'caution' => $cautionCount,
                'avoid' => $avoidCount
            ]
        ];
    }
    
    /**
     * Analyze product for a single profile
     */
    private function analyzeProductForProfile($product, $profile) {
        // Skip diet analysis for Home Cleaning products (they are not food items)
        if (isset($product['category_name']) && strtolower(trim($product['category_name'])) === 'home cleaning') {
            return ['status' => 'neutral', 'message' => 'Non-food item'];
        }
        
        $dietGoal = $profile['diet_goal'] ?? 'general';
        $calorieTarget = intval($profile['calorie_target'] ?? 2000);
        $memberCount = intval($profile['member_count'] ?? 1);
        
        // Check product calories - use dynamic threshold: 300 kcal × member count
        $productCalories = floatval($product['calories_per_unit'] ?? 0);
        
        // Dynamic calorie threshold: 300 kcal × number of persons in this profile
        $calorieThreshold = 300 * $memberCount;
        
        // PRIMARY CHECK: Calorie threshold - takes priority for ALL profiles
        if ($productCalories > $calorieThreshold) {
            return ['status' => 'avoid', 'message' => 'High calorie - above ' . number_format($calorieThreshold, 0) . ' kcal (for ' . $memberCount . ' person' . ($memberCount > 1 ? 's' : '') . ')'];
        } elseif ($productCalories == $calorieThreshold) {
            return ['status' => 'caution', 'message' => 'Product is ' . number_format($calorieThreshold, 0) . ' kcal - use caution (for ' . $memberCount . ' person' . ($memberCount > 1 ? 's' : '') . ')'];
        }
        // Below threshold - proceed with diet-specific checks
        
        // Since calories are acceptable (< 300), check diet-specific requirements
        switch ($dietGoal) {
            case 'weight_loss':
                // For weight loss with calories below 300, check if between 200-300
                if ($productCalories > 200 && $productCalories < 300) {
                    return ['status' => 'caution', 'message' => 'Moderate calorie - use in moderation for weight loss'];
                }
                // Below 200 kcal is good for weight loss
                return ['status' => 'recommended', 'message' => 'Suitable for weight loss'];
                
            case 'diabetes_friendly':
                $carbs = floatval($product['carbs_per_unit'] ?? 0);
                // Even with low calories, high carbs are a concern
                if ($carbs > 25) {
                    return ['status' => 'avoid', 'message' => 'High carbs - not suitable for diabetes'];
                } elseif ($carbs > 15) {
                    return ['status' => 'caution', 'message' => 'Moderate carbs - monitor intake'];
                }
                // Low carbs and low calories - recommended
                return ['status' => 'recommended', 'message' => 'Suitable for diabetes'];
                
            case 'low_sodium':
                $sodium = floatval($product['sodium_per_unit'] ?? 0);
                // Even with low calories, high sodium is a concern
                if ($sodium > 300) {
                    return ['status' => 'avoid', 'message' => 'High sodium - not suitable for low sodium diet'];
                } elseif ($sodium > 150) {
                    return ['status' => 'caution', 'message' => 'Moderate sodium - use in moderation'];
                }
                // Low sodium and low calories - recommended
                return ['status' => 'recommended', 'message' => 'Suitable for low sodium diet'];
                
            case 'vegetarian':
                $isVegetarian = isset($product['is_vegetarian']) ? (bool)$product['is_vegetarian'] : true;
                // Even with low calories, non-vegetarian is not suitable
                if (!$isVegetarian) {
                    return ['status' => 'avoid', 'message' => 'Not vegetarian'];
                }
                // Vegetarian and low calories - recommended
                return ['status' => 'recommended', 'message' => 'Suitable for vegetarian diet'];
                
            case 'muscle_gain':
                $protein = floatval($product['protein_per_unit'] ?? 0);
                // Low protein but acceptable calories - caution, not avoid
                if ($protein < 5) {
                    return ['status' => 'caution', 'message' => 'Low protein - may not support muscle gain'];
                }
                // Good protein and low calories - recommended
                return ['status' => 'recommended', 'message' => 'Suitable for muscle gain'];
                
            case 'general':
            default:
                // For general diet, below 300 kcal is recommended
                return ['status' => 'recommended', 'message' => 'Suitable for this profile'];
        }
    }
    
    /**
     * Get combined calorie target for all family members
     */
    public function getCombinedCalorieTarget($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    SUM(calorie_target * member_count) as total_calories,
                    SUM(member_count) as total_members
                FROM family_member_profiles 
                WHERE user_id = ? AND is_active = TRUE AND calorie_target IS NOT NULL
            ");
            $stmt->execute([$userId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error getting combined calorie target: " . $e->getMessage());
            return ['total_calories' => 0, 'total_members' => 0];
        }
    }
}

?>

