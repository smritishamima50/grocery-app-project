<?php

class DietHelper {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Get user's active diet profile
     */
    public function getUserDietProfile($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM user_diet_profiles WHERE user_id = ? AND active = TRUE");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    /**
     * Save or update user diet profile
     */
    public function saveDietProfile($userId, $dietGoal, $calorieTarget, $currentWeight = null, $targetWeight = null, $height = null, $age = null, $activityLevel = 'moderately_active', $preferences = null, $familyMembers = null) {
        try {
            // Start transaction
            $this->pdo->beginTransaction();
            
            // First, delete any existing active profile to avoid constraint issues
            $stmt = $this->pdo->prepare("DELETE FROM user_diet_profiles WHERE user_id = ? AND active = TRUE");
            $stmt->execute([$userId]);

            // Calculate BMI if height and weight are provided
            $bmi = null;
            if ($height > 0 && $currentWeight > 0) {
                $heightInMeters = $height / 100;
                $bmi = round($currentWeight / ($heightInMeters * $heightInMeters), 1);
            }

            // Insert new active profile (including family_members)
            $stmt = $this->pdo->prepare("INSERT INTO user_diet_profiles (user_id, diet_goal, calorie_target, current_weight, target_weight, height, age, activity_level, bmi, dietary_preferences, family_members, active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, TRUE)");
            $preferencesJson = $preferences ? json_encode($preferences) : null;
            $stmt->execute([$userId, $dietGoal, $calorieTarget, $currentWeight, $targetWeight, $height, $age, $activityLevel, $bmi, $preferencesJson, $familyMembers]);
            
            $newId = $this->pdo->lastInsertId();
            
            // Commit transaction
            $this->pdo->commit();
            
            return $newId;
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->pdo->rollback();
            throw $e;
        }
    }

    /**
     * Get recommended products based on user's diet profile
     */
    public function getRecommendedProducts($userId, $limit = 12) {
        $profile = $this->getUserDietProfile($userId);
        
        // Ensure limit is an integer to prevent SQL injection
        $limit = (int)$limit;
        
        if (!$profile) {
            // Return all active products if no diet profile (newest first), excluding Home Cleaning
            $stmt = $this->pdo->prepare("
                SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.is_active = 1 AND p.stock_quantity > 0 
                AND (c.name IS NULL OR LOWER(TRIM(c.name)) != 'home cleaning')
                ORDER BY p.created_at DESC, p.id DESC
                LIMIT $limit
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        }

        $dietGoal = $profile['diet_goal'];
        $calorieTarget = $profile['calorie_target'];

        $whereClauses = [];
        $params = [];

        // Exclude Home Cleaning products from recommendations
        $whereClauses[] = "(c.name IS NULL OR LOWER(TRIM(c.name)) != 'home cleaning')";

        // Filter based on diet goal
        switch ($dietGoal) {
            case 'weight_loss':
                $whereClauses[] = "is_weight_loss_friendly = TRUE AND calories_per_unit <= 150";
                break;
            case 'muscle_gain':
                $whereClauses[] = "is_muscle_gain_friendly = TRUE AND protein_per_unit >= 5";
                break;
            case 'diabetes_friendly':
                $whereClauses[] = "is_diabetes_friendly = TRUE AND carbs_per_unit <= 20";
                break;
            case 'low_sodium':
                $whereClauses[] = "sodium_per_unit <= 100";
                break;
            case 'vegetarian':
                $whereClauses[] = "is_vegetarian = TRUE";
                break;
            case 'general':
            default:
                break;
        }

        // Base query - always filter for active products
        $baseWhere = "p.is_active = 1 AND p.stock_quantity > 0";
        $whereClause = "WHERE " . implode(" AND ", $whereClauses) . " AND $baseWhere";

        // Order by relevance to diet goal
        $orderBy = $this->getOrderByClause($dietGoal);

        $sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id $whereClause ORDER BY $orderBy LIMIT $limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get order by clause based on diet goal
     */
    private function getOrderByClause($dietGoal) {
        switch ($dietGoal) {
            case 'weight_loss':
                return "calories_per_unit ASC, fiber_per_unit DESC";
            case 'muscle_gain':
                return "protein_per_unit DESC, calories_per_unit DESC";
            case 'diabetes_friendly':
                return "carbs_per_unit ASC, fiber_per_unit DESC";
            case 'low_sodium':
                return "sodium_per_unit ASC";
            default:
                return "created_at DESC";
        }
    }

    /**
     * Check if product matches diet profile
     * Returns true if suitable, false if not suitable, null for caution
     * PRIMARY CRITERIA: 300 kcal threshold (calories take priority)
     */
    public function isProductSuitable($product, $userId) {
        // Exclude Home Cleaning products from diet recommendations (they are not food items)
        if (isset($product['category_name']) && strtolower(trim($product['category_name'])) === 'home cleaning') {
            return false;
        }
        
        $profile = $this->getUserDietProfile($userId);
        $productCalories = isset($product['calories_per_unit']) ? (float)$product['calories_per_unit'] : 0;
        
        // PRIMARY CHECK: Calorie threshold (300 kcal) takes priority for ALL diet goals
        if ($productCalories > 300) {
            return false; // Above 300 kcal - AVOID (regardless of diet goal)
        } elseif ($productCalories == 300) {
            return null; // Exactly 300 kcal - CAUTION (regardless of diet goal)
        }
        // Below 300 kcal - proceed with diet-specific checks
        
        if (!$profile) {
            // No profile - below 300 kcal is recommended
            return true;
        }

        $dietGoal = $profile['diet_goal'];
        $calorieTarget = intval($profile['calorie_target'] ?? 2000);

        // Since calories are below 300, check diet-specific requirements
        switch ($dietGoal) {
            case 'weight_loss':
                // For weight loss, if calories are already below 300, check if it's weight loss friendly
                // But if calories are very low (below 200), it's generally suitable
                if ($productCalories > 200) {
                    // Between 200-300 kcal - check flag
                    $isWeightLossFriendly = isset($product['is_weight_loss_friendly']) ? (bool)$product['is_weight_loss_friendly'] : false;
                    return $isWeightLossFriendly;
                }
                // Below 200 kcal is suitable for weight loss
                return true;
                
            case 'muscle_gain':
                // For muscle gain, check protein content but calories below 300 are still okay
                $protein = isset($product['protein_per_unit']) ? (float)$product['protein_per_unit'] : 0;
                if ($protein < 5) {
                    // Low protein but calories are acceptable - return null (caution) not false
                    return null; // Caution: low protein but acceptable calories
                }
                // Good protein or calories are fine
                return true;
                
            case 'diabetes_friendly':
                // For diabetes, check carbs but calories below 300 are still acceptable
                $carbs = isset($product['carbs_per_unit']) ? (float)$product['carbs_per_unit'] : 0;
                if ($carbs > 25) {
                    return false; // High carbs - not suitable even with low calories
                } elseif ($carbs > 15) {
                    return null; // Moderate carbs - caution
                }
                // Low carbs and low calories - suitable
                return true;
                
            case 'low_sodium':
                // For low sodium, check sodium content
                $sodium = isset($product['sodium_per_unit']) ? (float)$product['sodium_per_unit'] : 0;
                if ($sodium > 300) {
                    return false; // High sodium - not suitable
                } elseif ($sodium > 150) {
                    return null; // Moderate sodium - caution
                }
                // Low sodium and low calories - suitable
                return true;
                
            case 'vegetarian':
                // For vegetarian, check if it's vegetarian
                $isVegetarian = isset($product['is_vegetarian']) ? (bool)$product['is_vegetarian'] : false;
                if (!$isVegetarian) {
                    return false; // Not vegetarian - not suitable
                }
                // Vegetarian and low calories - suitable
                return true;
                
            case 'general':
            default:
                // For general diet, below 300 kcal is recommended
                return true;
        }
    }
    
    /**
     * Analyze product calories and return recommendation based on 300 kcal threshold
     */
    public function analyzeProductCalories($product, $userId) {
        $profile = $this->getUserDietProfile($userId);
        $calorieTarget = $profile ? intval($profile['calorie_target'] ?? 2000) : 2000;
        $productCalories = floatval($product['calories_per_unit'] ?? 0);
        
        if ($productCalories <= 0) {
            return ['status' => 'neutral', 'message' => 'No calorie information available'];
        }
        
        // Use 300 kcal as the threshold
        if ($productCalories > 300) {
            return ['status' => 'avoid', 'message' => 'High calorie product - above 300 kcal'];
        } elseif ($productCalories == 300) {
            return ['status' => 'caution', 'message' => 'Product is 300 kcal - use caution'];
        } else {
            return ['status' => 'recommended', 'message' => 'Product is below 300 kcal - recommended'];
        }
    }

    /**
     * Get nutrition summary for products in cart
     */
    public function getCartNutritionSummary($userId) {
        $stmt = $this->pdo->prepare("
            SELECT 
                SUM(p.calories_per_unit * ci.quantity) as total_calories,
                SUM(p.protein_per_unit * ci.quantity) as total_protein,
                SUM(p.carbs_per_unit * ci.quantity) as total_carbs,
                SUM(p.fat_per_unit * ci.quantity) as total_fat,
                SUM(p.fiber_per_unit * ci.quantity) as total_fiber,
                SUM(p.sodium_per_unit * ci.quantity) as total_sodium
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            WHERE ci.user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
}

?>
