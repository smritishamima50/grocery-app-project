<?php
require_once 'BaseController.php';

class CartController extends BaseController {
    public function __construct() {
        parent::__construct();
        // Don't require login in constructor - check in each method as needed
    }

    public function index() {
        $this->requireLogin();
        require_once __DIR__ . '/../helpers/DietHelper.php';
        require_once __DIR__ . '/../helpers/SurpriseGiftHelper.php';
        
        $userId = $_SESSION['user_id'];

        // Ensure cart_items table exists (will create if missing)
        try {
            $this->ensureCartItemsTableExists();
            // Wait a tiny bit for table to be fully ready
            usleep(100000); // 0.1 second delay
        } catch (PDOException $e) {
            error_log("Error ensuring cart_items table: " . $e->getMessage());
            // Try to create again
            try {
                $this->ensureCartItemsTableExists();
            } catch (PDOException $e2) {
                error_log("Second attempt to create cart_items table failed: " . $e2->getMessage());
            }
        }

        // Initialize empty cart items array
        $cartItems = [];
        
        try {
            $stmt = $this->pdo->prepare("
                SELECT ci.*, p.name, p.price, p.image, p.unit, 
                       p.calories_per_unit, p.protein_per_unit, p.carbs_per_unit, 
                       p.fat_per_unit, p.fiber_per_unit, p.sodium_per_unit,
                       p.is_weight_loss_friendly, p.is_muscle_gain_friendly, 
                       p.is_diabetes_friendly, p.is_vegetarian, p.stock_quantity, p.is_active,
                       p.category_id, c.name as category_name
                FROM cart_items ci
                JOIN products p ON ci.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE ci.user_id = ?
            ");
            $stmt->execute([$userId]);
            $cartItems = $stmt->fetchAll();
        } catch (PDOException $e) {
            // If table still doesn't exist after creation attempt, show empty cart
            if (strpos($e->getMessage(), "doesn't exist") !== false || strpos($e->getMessage(), "Unknown table") !== false) {
                error_log("Cart items table still missing after creation attempt. Showing empty cart.");
                // Show empty cart instead of redirecting - this is better UX
                $cartItems = [];
            } else {
                // Different error - log it but still show empty cart
                error_log("Cart query error: " . $e->getMessage());
                $cartItems = [];
            }
        }

        $total = 0;
        foreach ($cartItems as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        // Calculate discount if coupon is applied
        $discount = 0;
        $appliedCoupon = null;
        if (isset($_SESSION['applied_coupon'])) {
            $appliedCoupon = $_SESSION['applied_coupon'];
            $discount = $appliedCoupon['discount_amount'];
        }

        $finalTotal = $total - $discount;

        // Calculate delivery fee: Free if order total >= 3000, otherwise 50
        $freeDeliveryThreshold = 3000;
        $deliveryFee = ($finalTotal >= $freeDeliveryThreshold) ? 0 : 50.00;
        $serviceCharge = 10.00; // Service charge is always applied
        $totalWithDelivery = $finalTotal + $deliveryFee + $serviceCharge;

        // Get user diet profile for warnings
        $dietHelper = new DietHelper($this->pdo);
        $userDietProfile = $dietHelper->getUserDietProfile($userId);
        
        // Get family member profiles and analyze products
        require_once __DIR__ . '/../helpers/FamilyMemberHelper.php';
        $familyMemberHelper = new FamilyMemberHelper($this->pdo);
        $familyMembers = $familyMemberHelper->getFamilyMemberProfiles($userId);
        
        // Calculate total persons (user + family members) for dynamic calorie threshold
        $totalPersons = 1; // User
        if (!empty($familyMembers)) {
            foreach ($familyMembers as $member) {
                $totalPersons += intval($member['member_count'] ?? 1);
            }
        }
        
        // Dynamic calorie threshold: 300 kcal × number of persons
        // 1 person = 300 kcal, 2 persons = 600 kcal, 3 persons = 900 kcal, etc.
        $calorieThreshold = 300 * $totalPersons;
        
        // Analyze each cart item for both user and family members
        // Skip diet analysis for Home Cleaning products (they are not food items)
        $productAnalysis = [];
        if (!empty($cartItems)) {
            foreach ($cartItems as $item) {
                // Skip diet analysis for Home Cleaning products
                $categoryName = isset($item['category_name']) ? strtolower(trim($item['category_name'])) : '';
                if ($categoryName === 'home cleaning') {
                    // No diet analysis for non-food items - set neutral status
                    $productAnalysis[$item['product_id']] = [
                        'status' => 'neutral',
                        'message' => 'Non-food item - no diet analysis available',
                        'user_suitable' => null,
                        'family_analysis' => null,
                        'skip_analysis' => true
                    ];
                    continue; // Skip to next product
                }
                
                $analysis = ['user_suitable' => null, 'family_analysis' => null];
                
                // PRIMARY CHECK: Calorie-based analysis with dynamic threshold based on number of persons
                $productCalories = floatval($item['calories_per_unit'] ?? 0);
                
                // Determine user suitability based on calories FIRST, then diet profile
                // Use dynamic threshold calculated above (300 kcal × number of persons)
                if ($productCalories > $calorieThreshold) {
                    // Above threshold - AVOID regardless of diet profile
                    $analysis['user_suitable'] = false;
                } elseif ($productCalories == $calorieThreshold) {
                    // Exactly at threshold - CAUTION regardless of diet profile
                    $analysis['user_suitable'] = null;
                } elseif ($productCalories < $calorieThreshold && $productCalories > 0) {
                    // Below threshold - check diet profile for additional considerations
                    if ($userDietProfile) {
                        $isUserSuitable = $dietHelper->isProductSuitable($item, $userId);
                        // isProductSuitable uses 300 kcal as base, but we've already checked dynamic threshold
                        // So if it passed threshold check, it should be suitable unless diet-specific issues
                        if ($isUserSuitable === false && $productCalories < $calorieThreshold) {
                            // If diet check says false but calories are acceptable, 
                            // it might be due to specific diet requirements (like vegetarian)
                            // Keep the false for now (it's a valid diet restriction)
                            $analysis['user_suitable'] = false;
                        } else {
                            $analysis['user_suitable'] = $isUserSuitable;
                        }
                    } else {
                        // No diet profile - below threshold is recommended
                        $analysis['user_suitable'] = true;
                    }
                } else {
                    // No calorie information - neutral
                    $analysis['user_suitable'] = null;
                }
                
                // Store threshold info for reference
                $analysis['calorie_threshold_used'] = $calorieThreshold;
                
                // Always check family members' suitability (if family members exist)
                if (!empty($familyMembers)) {
                    $familyAnalysis = $familyMemberHelper->analyzeProductForFamily($item, $userId);
                    $analysis['family_analysis'] = $familyAnalysis;
                    
                    // Combine user and family status for overall recommendation
                    $overallStatus = 'neutral';
                    $overallMessage = 'Product analysis';
                    
                    // Get family summary for better decision making
                    $familySummary = $familyAnalysis['summary'] ?? [];
                    $totalFamilyMembers = $familySummary['total_members'] ?? 0;
                    $familyAvoidCount = $familySummary['avoid'] ?? 0;
                    $familyCautionCount = $familySummary['caution'] ?? 0;
                    $familyRecommendedCount = $familySummary['recommended'] ?? 0;
                    
                    // Determine overall status based on both user and family
                    // Priority: Avoid > Caution > Recommended
                    if ($analysis['user_suitable'] === false && $familyAnalysis['status'] === 'avoid') {
                        // Both user and family should avoid
                        $overallStatus = 'avoid';
                        $overallMessage = 'Not suitable for you or family members';
                    } elseif ($analysis['user_suitable'] === false || $familyAnalysis['status'] === 'avoid') {
                        if ($analysis['user_suitable'] === false && $familyAnalysis['status'] === 'avoid') {
                            $overallStatus = 'avoid';
                            $overallMessage = 'Not suitable for you or family members';
                        } elseif ($familyAnalysis['status'] === 'avoid') {
                            // Family members should avoid
                            $overallStatus = 'avoid';
                            $overallMessage = 'Not suitable for ' . $familyAvoidCount . ' family member(s)';
                        } else {
                            // Only user should avoid
                            $overallStatus = 'avoid';
                            $overallMessage = 'Not suitable for your diet plan';
                        }
                    } elseif ($analysis['user_suitable'] === null || $familyAnalysis['status'] === 'caution') {
                        // User or family needs caution
                        $overallStatus = 'caution';
                        if ($familyAnalysis['status'] === 'caution') {
                            $overallMessage = 'Use caution for ' . $familyCautionCount . ' family member(s)';
                        } else {
                            $overallMessage = 'Use caution - Product requires careful consideration';
                        }
                    } elseif ($analysis['user_suitable'] === true && $familyAnalysis['status'] === 'recommended') {
                        // Both user and family recommend
                        $overallStatus = 'recommended';
                        if ($familyRecommendedCount > 0) {
                            $overallMessage = 'Recommended for ' . $familyRecommendedCount . ' family member(s)';
                        } else {
                            $overallMessage = 'Recommended for your diet plan';
                        }
                    } elseif ($analysis['user_suitable'] === true && $familyAnalysis['status'] !== 'avoid' && $familyAnalysis['status'] !== 'caution') {
                        $overallStatus = 'recommended';
                        $overallMessage = 'Recommended for your diet plan';
                    } elseif ($analysis['user_suitable'] === null && $familyAnalysis['status'] === 'recommended') {
                        $overallStatus = 'recommended';
                        $overallMessage = 'Recommended for ' . $familyRecommendedCount . ' family member(s)';
                    } else {
                        // Default based on family analysis
                        $overallStatus = $familyAnalysis['status'] ?? 'recommended';
                        $overallMessage = $familyAnalysis['message'] ?? 'Recommended';
                    }
                    
                    // Store threshold info for display
                    $analysis['calorie_threshold'] = $calorieThreshold;
                    $analysis['total_persons'] = $totalPersons;
                    
                    $analysis['status'] = $overallStatus;
                    $analysis['message'] = $overallMessage;
                } elseif ($userDietProfile) {
                    // Only user profile, no family members - use calorie-based recommendations with dynamic threshold
                    $productCalories = floatval($item['calories_per_unit'] ?? 0);
                    
                    if ($analysis['user_suitable'] === true) {
                        $analysis['status'] = 'recommended';
                        $analysis['message'] = 'Recommended for your diet plan';
                    } elseif ($analysis['user_suitable'] === false) {
                        $analysis['status'] = 'avoid';
                        $analysis['message'] = 'Not suitable for your diet plan';
                    } elseif ($analysis['user_suitable'] === null || $productCalories == $calorieThreshold) {
                        // Caution case (at threshold or null from diet check)
                        $analysis['status'] = 'caution';
                        $analysis['message'] = 'Use caution - Product requires careful consideration';
                    } else {
                        // Fallback
                        $analysis['status'] = 'recommended';
                        $analysis['message'] = 'Recommended for your diet plan';
                    }
                    
                    $analysis['calorie_threshold'] = $calorieThreshold;
                    $analysis['total_persons'] = $totalPersons;
                } else {
                    // No diet profile set - analyze based on calories with dynamic threshold
                    $productCalories = floatval($item['calories_per_unit'] ?? 0);
                    
                    if ($productCalories > $calorieThreshold) {
                        $analysis['status'] = 'avoid';
                        $analysis['message'] = 'High calorie product - above ' . number_format($calorieThreshold, 0) . ' kcal';
                        $analysis['user_suitable'] = false;
                    } elseif ($productCalories == $calorieThreshold) {
                        $analysis['status'] = 'caution';
                        $analysis['message'] = 'Product is ' . number_format($calorieThreshold, 0) . ' kcal - use caution';
                        $analysis['user_suitable'] = null;
                    } elseif ($productCalories > 0) {
                        $analysis['status'] = 'recommended';
                        $analysis['message'] = 'Product is below ' . number_format($calorieThreshold, 0) . ' kcal - recommended';
                        $analysis['user_suitable'] = true;
                    } else {
                        $analysis['status'] = 'neutral';
                        $analysis['message'] = 'No nutritional information available';
                        $analysis['user_suitable'] = null;
                    }
                    
                    $analysis['calorie_threshold'] = $calorieThreshold;
                    $analysis['total_persons'] = $totalPersons;
                }
                
                // Always store analysis for every product - ensure it has at least status and message
                if (!isset($analysis['status']) || empty($analysis['status'])) {
                    $analysis['status'] = 'neutral';
                    $analysis['message'] = 'Product analysis';
                }
                $productAnalysis[$item['product_id']] = $analysis;
            }
        }
        
        // Calculate quantities for specific categories to determine weekly/daily view
        // Different categories have different thresholds and unit types
        $isWeeklyView = false;
        $weeklyViewReasons = []; // Track which conditions triggered weekly view
        
        // Check each cart item against category-specific thresholds
        foreach ($cartItems as $item) {
            $categoryName = trim($item['category_name'] ?? '');
            $categoryLower = strtolower($categoryName);
            $quantity = floatval($item['quantity'] ?? 0);
            $unit = strtolower(trim($item['unit'] ?? ''));
            $productName = strtolower($item['name'] ?? '');
            
            // Helper function to convert quantity to kg
            $quantityInKg = 0;
            if (strpos($unit, 'kg') !== false || $unit === 'kg') {
                $quantityInKg = $quantity;
            } elseif (strpos($unit, 'g') !== false || $unit === 'g') {
                $quantityInKg = $quantity / 1000;
            } else {
                // Try to estimate from unit_size if available
                if (isset($item['unit_size']) && preg_match('/(\d+\.?\d*)\s*kg/i', $item['unit_size'], $matches)) {
                    $unitSizeKg = floatval($matches[1]);
                    $quantityInKg = $quantity * $unitSizeKg;
                }
            }
            
            // PRIORITY CHECK: Meat & Poultry, Rice & Grains: any product > 3 kg → ALWAYS Weekly View
            // NOTE: These categories have their own special rule - ONLY > 3 kg triggers weekly view, not 2-3 kg
            if (in_array($categoryLower, ['meat & poultry', 'rice & grains'])) {
                // If any product from Meat & Poultry or Rice & Grains has MORE than 3 kg, force Weekly View
                if ($quantityInKg > 3.0) {
                    $isWeeklyView = true;
                    $weeklyViewReasons[] = number_format($quantityInKg, 2) . ' kg of ' . htmlspecialchars($item['name']) . ' from ' . $categoryName . ' (> 3 kg threshold - forces Weekly View)';
                    continue; // Skip remaining checks for this item, already triggered weekly view
                }
                // For Meat & Poultry and Rice & Grains: 3 kg or less does NOT trigger weekly view (skip to next item)
                continue; // Skip the > 2 kg check below for these categories
            }
            
            // 1. Fruits & Vegetables: each product > 2 kg
            // NOTE: Meat & Poultry and Rice & Grains are excluded here (they have their own > 3 kg rule above)
            if ($categoryLower === 'fruits & vegetables') {
                // Check if this individual product has more than 2 kg
                if ($quantityInKg > 2.0) {
                    $isWeeklyView = true;
                    $weeklyViewReasons[] = number_format($quantityInKg, 2) . ' kg of ' . htmlspecialchars($item['name']) . ' from ' . $categoryName . ' (> 2 kg per product threshold)';
                }
            }
            
            // 2. Baking Needs: more than 1 kg per product
            if ($categoryLower === 'baking needs') {
                $quantityInKg = 0;
                if (strpos($unit, 'kg') !== false || $unit === 'kg') {
                    $quantityInKg = $quantity;
                } elseif (strpos($unit, 'g') !== false || $unit === 'g') {
                    $quantityInKg = $quantity / 1000;
                } else {
                    if (isset($item['unit_size']) && preg_match('/(\d+\.?\d*)\s*kg/i', $item['unit_size'], $matches)) {
                        $quantityInKg = $quantity * floatval($matches[1]);
                    }
                }
                
                if ($quantityInKg > 1.0) {
                    $isWeeklyView = true;
                    $weeklyViewReasons[] = number_format($quantityInKg, 2) . ' kg of ' . htmlspecialchars($item['name']) . ' from ' . $categoryName . ' (> 1 kg per product threshold)';
                }
            }
            
            // 3. Cooking: more than 1 kg per product (exclude spices/herbs which are checked separately)
            $isSpiceOrHerb = (strpos($productName, 'spice') !== false || 
                             strpos($productName, 'herb') !== false ||
                             strpos($categoryName, 'spice') !== false ||
                             strpos($categoryName, 'herb') !== false);
            
            if ($categoryLower === 'cooking' && !$isSpiceOrHerb) {
                $quantityInKg = 0;
                if (strpos($unit, 'kg') !== false || $unit === 'kg') {
                    $quantityInKg = $quantity;
                } elseif (strpos($unit, 'g') !== false || $unit === 'g') {
                    $quantityInKg = $quantity / 1000;
                } else {
                    if (isset($item['unit_size']) && preg_match('/(\d+\.?\d*)\s*kg/i', $item['unit_size'], $matches)) {
                        $quantityInKg = $quantity * floatval($matches[1]);
                    }
                }
                
                if ($quantityInKg > 1.0) {
                    $isWeeklyView = true;
                    $weeklyViewReasons[] = number_format($quantityInKg, 2) . ' kg of ' . htmlspecialchars($item['name']) . ' from ' . $categoryName . ' (> 1 kg per product threshold)';
                }
            }
            
            // 4. Dairy & Eggs: more than 1 units per product
            if ($categoryLower === 'dairy & eggs' || $categoryLower === 'dairy and eggs') {
                // Count units (quantity > 1 means more than 1 unit)
                if ($quantity > 1.0) {
                    $isWeeklyView = true;
                    $weeklyViewReasons[] = $quantity . ' units of ' . htmlspecialchars($item['name']) . ' from ' . $categoryName . ' (> 1 unit per product threshold)';
                }
            }
            
            // 5. Frozen Food: 1 pack or more per product (quantity >= 1)
            if ($categoryLower === 'frozen food' || $categoryLower === 'frozen foods') {
                // Check if quantity >= 1 (any unit type)
                if ($quantity >= 1.0) {
                    $isWeeklyView = true;
                    $weeklyViewReasons[] = $quantity . ' pack(s) of ' . htmlspecialchars($item['name']) . ' from ' . $categoryName . ' (>= 1 pack per product threshold)';
                }
            }
            
            // 6. Spices & Herbs (might be in Cooking category or separate): more than 1 packs per product
            // Check if product name contains spice/herb keywords or category name
            if ($isSpiceOrHerb || strpos($categoryLower, 'spice') !== false || strpos($categoryLower, 'herb') !== false) {
                // Check if unit is packs and quantity > 1
                if (strpos($unit, 'pack') !== false && $quantity > 1.0) {
                    $isWeeklyView = true;
                    $weeklyViewReasons[] = $quantity . ' packs of ' . htmlspecialchars($item['name']) . ' from Spices/Herbs (> 1 pack per product threshold)';
                }
            }
            
            // 7. Snacks (and Pasta): more than 1 packs per product
            if ($categoryLower === 'snacks' || 
                strpos($categoryLower, 'snack') !== false ||
                strpos($productName, 'pasta') !== false) {
                
                // Check if unit is packs and quantity > 1
                if (strpos($unit, 'pack') !== false && $quantity > 1.0) {
                    $isWeeklyView = true;
                    $weeklyViewReasons[] = $quantity . ' packs of ' . htmlspecialchars($item['name']) . ' from ' . $categoryName . ' (> 1 pack per product threshold)';
                }
            }
        }
        
        // Get calorie recommendations
        require_once __DIR__ . '/../helpers/CalorieHelper.php';
        $calorieHelper = new CalorieHelper($this->pdo);
        $calorieRecommendation = $calorieHelper->getCalorieRecommendation($userId);
        
        // Add weekly calculation if in weekly view mode
        if ($isWeeklyView) {
            $weeklyCalories = $calorieRecommendation['cart_calories'] ?? 0;
            $weeklyTarget = $calorieRecommendation['weekly_target'] ?? 0;
            $dailyTarget = $calorieRecommendation['daily_target'] ?? 0;
            
            // Calculate weekly percentage
            $weeklyPercentage = $weeklyTarget > 0 ? ($weeklyCalories / $weeklyTarget) * 100 : 0;
            
            // Calculate daily percentage (for weekly view, still show how much of daily target)
            $dailyPercentage = $dailyTarget > 0 ? ($weeklyCalories / $dailyTarget) * 100 : 0;
            
            $calorieRecommendation['is_weekly_view'] = true;
            $calorieRecommendation['weekly_calories'] = $weeklyCalories;
            $calorieRecommendation['weekly_percentage'] = round($weeklyPercentage, 1);
            $calorieRecommendation['daily_percentage_weekly_view'] = round($dailyPercentage, 1);
            $calorieRecommendation['weekly_view_reasons'] = $weeklyViewReasons;
        } else {
            $calorieRecommendation['is_weekly_view'] = false;
        }

        // Get surprise gift options for user selection
        $surpriseGiftOptions = [];
        $selectedGiftId = null; // Track which gift is already selected
        
        // Check if user has already selected a surprise gift (from database with NULL order_id)
        try {
            $stmt = $this->pdo->prepare("
                SELECT surprise_gift_id 
                FROM user_surprise_gifts 
                WHERE user_id = ? AND order_id IS NULL
                LIMIT 1
            ");
            $stmt->execute([$userId]);
            $selectedGift = $stmt->fetch();
            if ($selectedGift) {
                $selectedGiftId = intval($selectedGift['surprise_gift_id']);
            }
        } catch (PDOException $e) {
            // Table might not exist yet or column might not allow NULL, continue
            error_log("Note: Could not check selected gift: " . $e->getMessage());
        }
        
        if (!empty($cartItems)) {
            $surpriseGiftHelper = new SurpriseGiftHelper($this->pdo);
            $userOrderCount = $this->getUserOrderCount($userId);
            
            // Get available surprise gifts
            $stmt = $this->pdo->prepare("
                SELECT sg.*, p.name as product_name, p.image as product_image, p.price as product_price
                FROM surprise_gifts sg
                JOIN products p ON sg.product_id = p.id
                WHERE sg.is_active = 1
                AND (sg.start_date IS NULL OR sg.start_date <= CURDATE())
                AND (sg.end_date IS NULL OR sg.end_date >= CURDATE())
                AND p.stock_quantity > 0
                ORDER BY sg.probability_percentage DESC
                LIMIT 10
            ");
            $stmt->execute();
            $availableGifts = $stmt->fetchAll();
            
            // Filter gifts based on eligibility
            foreach ($availableGifts as $gift) {
                if ($surpriseGiftHelper->isUserEligibleForGift($userId, $gift['id']) && 
                    !$surpriseGiftHelper->hasGiftReachedMaxUses($gift)) {
                    $surpriseGiftOptions[] = $gift;
                }
            }
        }

        $this->render('cart/index', [
            'cartItems' => $cartItems,
            'total' => $total,
            'discount' => $discount,
            'finalTotal' => $finalTotal,
            'deliveryFee' => $deliveryFee,
            'serviceCharge' => $serviceCharge,
            'totalWithDelivery' => $totalWithDelivery,
            'freeDeliveryThreshold' => $freeDeliveryThreshold,
            'appliedCoupon' => $appliedCoupon,
            'userDietProfile' => $userDietProfile,
            'familyMembers' => $familyMembers,
            'productAnalysis' => $productAnalysis,
            'calorieRecommendation' => $calorieRecommendation,
            'surpriseGiftOptions' => $surpriseGiftOptions,
            'selectedGiftId' => $selectedGiftId
        ]);
    }

    public function add() {
        // Set JSON header for all responses (only if headers not already sent)
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }

        // Log the request for debugging
        error_log("Cart Add Request: " . print_r($_POST, true));

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Check if user is logged in
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Please login to add items to cart', 'login_required' => true]);
                return;
            }

            $userId = $_SESSION['user_id'];
            $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
            $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

            // Validate input
            if ($productId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
                return;
            }

            if ($quantity <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid quantity']);
                return;
            }

            // Check if product exists, is active, and has stock
            try {
                $stmt = $this->pdo->prepare("SELECT stock_quantity, is_active FROM products WHERE id = ?");
                $stmt->execute([$productId]);
                $product = $stmt->fetch();
            } catch (PDOException $e) {
                error_log("Product lookup error: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Product lookup failed']);
                return;
            }

            if (!$product) {
                echo json_encode(['success' => false, 'message' => 'Product not found']);
                return;
            }

            if (!$product['is_active']) {
                echo json_encode(['success' => false, 'message' => 'Product is currently unavailable']);
                return;
            }

            if ($product['stock_quantity'] < $quantity) {
                echo json_encode(['success' => false, 'message' => 'Insufficient stock available']);
                return;
            }

            // Ensure cart_items table exists
            try {
                $this->ensureCartItemsTableExists();
            } catch (PDOException $e) {
                error_log("Failed to ensure cart_items table: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Cart system error. Please contact administrator.']);
                return;
            }

            // Check if item already in cart
            try {
                $stmt = $this->pdo->prepare("SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$userId, $productId]);
                $existingItem = $stmt->fetch();
            } catch (PDOException $e) {
                error_log("Cart query error: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Cart system error. Please contact administrator.']);
                return;
            }

            try {
                if ($existingItem) {
                    $newQuantity = $existingItem['quantity'] + $quantity;
                    $stmt = $this->pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
                    $stmt->execute([$newQuantity, $existingItem['id']]);
                } else {
                    $stmt = $this->pdo->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
                    $stmt->execute([$userId, $productId, $quantity]);
                }

                echo json_encode(['success' => true, 'message' => 'Product added to cart successfully']);
            } catch (PDOException $e) {
                // Log the actual error for debugging
                error_log("Cart Add Error: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()]);
            } catch (Exception $e) {
                error_log("Cart Add Error: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'An error occurred']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        }
    }

    public function count() {
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['count' => 0]);
            return;
        }

        try {
            $this->ensureCartItemsTableExists();
        } catch (PDOException $e) {
            echo json_encode(['count' => 0]);
            return;
        }

        $userId = $_SESSION['user_id'];
        $stmt = $this->pdo->prepare("SELECT SUM(quantity) as total FROM cart_items WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();

        echo json_encode(['count' => (int)($result['total'] ?? 0)]);
    }

    public function totals() {
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            return;
        }

        $userId = $_SESSION['user_id'];

        try {
            $this->ensureCartItemsTableExists();
        } catch (PDOException $e) {
            echo json_encode([
                'success' => true,
                'itemCount' => 0,
                'subtotal' => 0.0
            ]);
            return;
        }

        // Get cart items count and subtotal
        $stmt = $this->pdo->prepare("
            SELECT SUM(ci.quantity) as item_count, SUM(ci.quantity * p.price) as subtotal
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            WHERE ci.user_id = ?
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();

        echo json_encode([
            'success' => true,
            'itemCount' => (int)($result['item_count'] ?? 0),
            'subtotal' => (float)($result['subtotal'] ?? 0)
        ]);
    }

    public function update() {
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Check if user is logged in
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Please login to update cart', 'login_required' => true]);
                return;
            }

            $userId = $_SESSION['user_id'];
            $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
            $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

            // Validate input
            if ($productId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
                return;
            }

            // Ensure cart_items table exists
            try {
                $this->ensureCartItemsTableExists();
            } catch (PDOException $e) {
                error_log("Failed to ensure cart_items table: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Cart system error. Please contact administrator.']);
                return;
            }

            try {
                if ($quantity <= 0) {
                    $stmt = $this->pdo->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?");
                    $stmt->execute([$userId, $productId]);
                } else {
                    $stmt = $this->pdo->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?");
                    $stmt->execute([$quantity, $userId, $productId]);
                }

                echo json_encode(['success' => true, 'message' => 'Cart updated successfully']);
            } catch (PDOException $e) {
                error_log("Cart Update Error: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()]);
            } catch (Exception $e) {
                error_log("Cart Update Error: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'An error occurred']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        }
    }

    public function remove() {
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Check if user is logged in
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Please login to remove items from cart', 'login_required' => true]);
                return;
            }

            $userId = $_SESSION['user_id'];
            $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

            // Validate input
            if ($productId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
                return;
            }

            // Ensure cart_items table exists
            try {
                $this->ensureCartItemsTableExists();
            } catch (PDOException $e) {
                error_log("Failed to ensure cart_items table: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Cart system error. Please contact administrator.']);
                return;
            }

            try {
                $stmt = $this->pdo->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$userId, $productId]);

                echo json_encode(['success' => true, 'message' => 'Item removed from cart successfully']);
            } catch (PDOException $e) {
                error_log("Cart Remove Error: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()]);
            } catch (Exception $e) {
                error_log("Cart Remove Error: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'An error occurred']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        }
    }

    public function clear() {
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Check if user is logged in
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Please login to clear cart', 'login_required' => true]);
                return;
            }

            $userId = $_SESSION['user_id'];

            // Ensure cart_items table exists
            try {
                $this->ensureCartItemsTableExists();
            } catch (PDOException $e) {
                error_log("Failed to ensure cart_items table: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Cart system error. Please contact administrator.']);
                return;
            }

            try {
                $stmt = $this->pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
                $stmt->execute([$userId]);

                echo json_encode(['success' => true, 'message' => 'Cart cleared successfully']);
            } catch (PDOException $e) {
                error_log("Cart Clear Error: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()]);
            } catch (Exception $e) {
                error_log("Cart Clear Error: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'An error occurred']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        }
    }

    /**
     * Apply coupon to cart
     */
    public function applyCoupon() {
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Check if user is logged in
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Please login to apply coupon']);
                return;
            }

            $userId = $_SESSION['user_id'];
            $couponCode = trim($_POST['coupon_code'] ?? '');

            if (empty($couponCode)) {
                echo json_encode(['success' => false, 'message' => 'Please enter a coupon code']);
                return;
            }

            try {
                // Get coupon details
                $stmt = $this->pdo->prepare("
                    SELECT * FROM coupons 
                    WHERE code = ? 
                    AND (expiry_date IS NULL OR expiry_date >= CURDATE()) 
                    AND (usage_limit IS NULL OR used_count < usage_limit)
                ");
                $stmt->execute([$couponCode]);
                $coupon = $stmt->fetch();

                if (!$coupon) {
                    echo json_encode(['success' => false, 'message' => 'Invalid or expired coupon code']);
                    return;
                }

                // Ensure cart_items table exists
                try {
                    $this->ensureCartItemsTableExists();
                } catch (PDOException $e) {
                    error_log("Failed to ensure cart_items table: " . $e->getMessage());
                    echo json_encode(['success' => false, 'message' => 'Cart system error. Please contact administrator.']);
                    return;
                }

                // Get cart total
                $stmt = $this->pdo->prepare("
                    SELECT SUM(ci.quantity * p.price) as total
                    FROM cart_items ci
                    JOIN products p ON ci.product_id = p.id
                    WHERE ci.user_id = ?
                ");
                $stmt->execute([$userId]);
                $cartTotal = $stmt->fetch()['total'] ?? 0;

                // Check minimum order amount
                if ($cartTotal < $coupon['min_order_amount']) {
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Minimum order amount of ৳' . number_format($coupon['min_order_amount'], 2) . ' required for this coupon'
                    ]);
                    return;
                }

                // Calculate discount
                $discount = 0;
                if ($coupon['discount_type'] === 'percentage') {
                    $discount = ($cartTotal * $coupon['discount_value']) / 100;
                } else {
                    $discount = $coupon['discount_value'];
                }

                // Don't allow discount to exceed cart total
                $discount = min($discount, $cartTotal);

                // Store applied coupon in session
                $_SESSION['applied_coupon'] = [
                    'id' => $coupon['id'],
                    'code' => $coupon['code'],
                    'discount_type' => $coupon['discount_type'],
                    'discount_value' => $coupon['discount_value'],
                    'discount_amount' => $discount
                ];

                echo json_encode([
                    'success' => true,
                    'message' => 'Coupon applied successfully!',
                    'coupon' => [
                        'code' => $coupon['code'],
                        'discount_type' => $coupon['discount_type'],
                        'discount_value' => $coupon['discount_value'],
                        'discount_amount' => $discount
                    ],
                    'new_total' => $cartTotal - $discount
                ]);

            } catch (PDOException $e) {
                error_log("Coupon apply error: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Failed to apply coupon']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        }
    }

    /**
     * Remove applied coupon
     */
    public function removeCoupon() {
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            unset($_SESSION['applied_coupon']);
            echo json_encode(['success' => true, 'message' => 'Coupon removed successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        }
    }

    /**
     * Get user's order count for surprise gift eligibility
     */
    private function getUserOrderCount($userId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch()['count'];
    }

    /**
     * Save selected surprise gift to wishlist and user_surprise_gifts
     * POST /cart/select-surprise-gift
     */
    public function selectSurpriseGift() {
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Please login to select surprise gift', 'login_required' => true]);
            return;
        }

        $userId = $_SESSION['user_id'];
        $giftIndex = isset($_POST['gift_index']) ? intval($_POST['gift_index']) : -1;

        if ($giftIndex < 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid gift selection']);
            return;
        }

        try {
            // Get available surprise gifts (same logic as in index method)
            require_once __DIR__ . '/../helpers/SurpriseGiftHelper.php';
            $surpriseGiftHelper = new SurpriseGiftHelper($this->pdo);
            
            $stmt = $this->pdo->prepare("
                SELECT sg.*, p.name as product_name, p.image as product_image, p.price as product_price
                FROM surprise_gifts sg
                JOIN products p ON sg.product_id = p.id
                WHERE sg.is_active = 1
                AND (sg.start_date IS NULL OR sg.start_date <= CURDATE())
                AND (sg.end_date IS NULL OR sg.end_date >= CURDATE())
                AND p.stock_quantity > 0
                ORDER BY sg.probability_percentage DESC
                LIMIT 10
            ");
            $stmt->execute();
            $availableGifts = $stmt->fetchAll();
            
            // Filter gifts based on eligibility
            $eligibleGifts = [];
            foreach ($availableGifts as $gift) {
                if ($surpriseGiftHelper->isUserEligibleForGift($userId, $gift['id']) && 
                    !$surpriseGiftHelper->hasGiftReachedMaxUses($gift)) {
                    $eligibleGifts[] = $gift;
                }
            }

            if (!isset($eligibleGifts[$giftIndex])) {
                echo json_encode(['success' => false, 'message' => 'Selected gift is not available']);
                return;
            }

            $selectedGift = $eligibleGifts[$giftIndex];
            $giftId = $selectedGift['id'];
            $productId = $selectedGift['product_id'];
            $quantity = $selectedGift['quantity'] ?? 1;

            $this->pdo->beginTransaction();

            // 1. Ensure cart_items table exists
            $this->ensureCartItemsTableExists();

            // 2. Add gift product to cart_items (shopping cart)
            // Check if product already exists in cart
            $stmt = $this->pdo->prepare("SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
            $existingCartItem = $stmt->fetch();
            
            if ($existingCartItem) {
                // Product already in cart, update quantity (add gift quantity to existing)
                $newQuantity = $existingCartItem['quantity'] + $quantity;
                $stmt = $this->pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
                $stmt->execute([$newQuantity, $existingCartItem['id']]);
                error_log("✅ Updated existing cart item for surprise gift product ID: $productId, new quantity: $newQuantity");
            } else {
                // Add new item to cart
                $stmt = $this->pdo->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
                $stmt->execute([$userId, $productId, $quantity]);
                error_log("✅ Added surprise gift product to cart: Product ID: $productId, Quantity: $quantity");
            }

            // 3. Add to wishlist (if not already exists)
            $stmt = $this->pdo->prepare("SELECT id FROM wishlists WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
            if (!$stmt->fetch()) {
                $stmt = $this->pdo->prepare("INSERT INTO wishlists (user_id, product_id) VALUES (?, ?)");
                $stmt->execute([$userId, $productId]);
                error_log("✅ Added surprise gift product to wishlist: Product ID: $productId");
            }

            // 4. Ensure user_surprise_gifts table allows NULL order_id
            // Try to modify the table structure if needed (allow NULL order_id)
            // This is safe to run multiple times - it will only modify if needed
            try {
                // Check current column definition first
                $stmt = $this->pdo->query("SHOW COLUMNS FROM user_surprise_gifts WHERE Field = 'order_id'");
                $columnInfo = $stmt->fetch();
                
                if ($columnInfo && strpos($columnInfo['Null'], 'YES') === false) {
                    // Column doesn't allow NULL, modify it
                    try {
                        // Temporarily disable foreign key checks
                        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
                        $this->pdo->exec("ALTER TABLE user_surprise_gifts MODIFY COLUMN order_id INT NULL");
                        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
                        error_log("Modified user_surprise_gifts table to allow NULL order_id");
                    } catch (PDOException $e) {
                        error_log("Note: Could not modify order_id column: " . $e->getMessage());
                        // Continue anyway - might work if foreign key allows it
                    }
                }
            } catch (PDOException $e) {
                // Table might not exist or other error, try to modify anyway
                try {
                    $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
                    $this->pdo->exec("ALTER TABLE user_surprise_gifts MODIFY COLUMN order_id INT NULL");
                    $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
                } catch (PDOException $e2) {
                    error_log("Note: Could not modify order_id column: " . $e2->getMessage());
                }
            }

            // 5. Remove any existing selection for this user (only NULL order_id ones - cart selections)
            // This ensures only one gift can be selected at a time
            try {
                $stmt = $this->pdo->prepare("
                    DELETE FROM user_surprise_gifts 
                    WHERE user_id = ? AND order_id IS NULL
                ");
                $stmt->execute([$userId]);
            } catch (PDOException $e) {
                // If query fails due to NULL not being allowed, try without NULL check
                error_log("Note: Could not delete with NULL check, trying alternative: " . $e->getMessage());
                // Continue - will try to insert anyway
            }

            // 6. Add to user_surprise_gifts with NULL order_id (will be updated when order is created)
            // Use INSERT IGNORE or handle duplicate key error gracefully
            try {
                $stmt = $this->pdo->prepare("
                    INSERT INTO user_surprise_gifts (user_id, order_id, surprise_gift_id, quantity) 
                    VALUES (?, NULL, ?, ?)
                ");
                $stmt->execute([$userId, $giftId, $quantity]);
            } catch (PDOException $e) {
                // If unique constraint violation, update existing record instead
                if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    $stmt = $this->pdo->prepare("
                        UPDATE user_surprise_gifts 
                        SET surprise_gift_id = ?, quantity = ? 
                        WHERE user_id = ? AND order_id IS NULL
                    ");
                    $stmt->execute([$giftId, $quantity, $userId]);
                } else {
                    throw $e; // Re-throw if it's a different error
                }
            }

            // 7. Store in session for checkout
            $_SESSION['selected_surprise_gift'] = [
                'gift_id' => $giftId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'name' => $selectedGift['name'],
                'product_name' => $selectedGift['product_name']
            ];

            $this->pdo->commit();

            error_log("✅ Successfully added surprise gift to cart and database for user: $userId, Gift ID: $giftId, Product ID: $productId");

            echo json_encode([
                'success' => true,
                'message' => 'Successfully added the surprise gift to your shopping cart!',
                'gift' => [
                    'id' => $giftId,
                    'name' => $selectedGift['name'],
                    'product_name' => $selectedGift['product_name'],
                    'product_id' => $productId,
                    'quantity' => $quantity
                ],
                'cart_updated' => true
            ]);

        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollback();
            }
            error_log("❌ Select surprise gift PDO error: " . $e->getMessage());
            error_log("❌ Error code: " . $e->getCode());
            error_log("❌ SQL State: " . $e->errorInfo[0] ?? 'N/A');
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to add surprise gift to cart. Please try again.',
                'error' => $e->getMessage()
            ]);
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollback();
            }
            error_log("❌ Select surprise gift general error: " . $e->getMessage());
            error_log("❌ Stack trace: " . $e->getTraceAsString());
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to add surprise gift to cart. Please try again.'
            ]);
        }
    }

    /**
     * Ensure cart_items table exists, create if it doesn't
     */
    private function ensureCartItemsTableExists() {
        try {
            // Try to check if table exists by querying it
            $this->pdo->query("SELECT 1 FROM cart_items LIMIT 1");
            // If no exception, table exists
            return;
        } catch (PDOException $e) {
            // Table doesn't exist, create it
            if (strpos($e->getMessage(), "doesn't exist") !== false || 
                strpos($e->getMessage(), "Unknown table") !== false ||
                strpos($e->getMessage(), "Base table or view not found") !== false) {
                try {
                    // First, try to create without foreign keys (in case they cause issues)
                    $createTableSQL = "
                    CREATE TABLE IF NOT EXISTS cart_items (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NOT NULL,
                        product_id INT NOT NULL,
                        quantity INT NOT NULL DEFAULT 1,
                        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        INDEX idx_user (user_id),
                        INDEX idx_product (product_id),
                        UNIQUE KEY unique_cart_item (user_id, product_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                    ";
                    
                    $this->pdo->exec($createTableSQL);
                    
                    // Try to add foreign keys separately (they might fail if tables don't exist)
                    try {
                        // Check if users table exists
                        $this->pdo->query("SELECT 1 FROM users LIMIT 1");
                        // If users exists, add foreign key
                        try {
                            $this->pdo->exec("ALTER TABLE cart_items ADD CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE");
                        } catch (PDOException $fkError) {
                            // Foreign key might already exist or fail, ignore
                            error_log("Foreign key for user_id could not be added: " . $fkError->getMessage());
                        }
                    } catch (PDOException $e) {
                        // Users table doesn't exist, skip foreign key
                        error_log("Users table not found, skipping foreign key for user_id");
                    }
                    
                    try {
                        // Check if products table exists
                        $this->pdo->query("SELECT 1 FROM products LIMIT 1");
                        // If products exists, add foreign key
                        try {
                            $this->pdo->exec("ALTER TABLE cart_items ADD CONSTRAINT fk_cart_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE");
                        } catch (PDOException $fkError) {
                            // Foreign key might already exist or fail, ignore
                            error_log("Foreign key for product_id could not be added: " . $fkError->getMessage());
                        }
                    } catch (PDOException $e) {
                        // Products table doesn't exist, skip foreign key
                        error_log("Products table not found, skipping foreign key for product_id");
                    }
                    
                    error_log("cart_items table created automatically");
                } catch (PDOException $createError) {
                    error_log("Failed to create cart_items table: " . $createError->getMessage());
                    // Don't throw - let the calling code handle empty cart gracefully
                }
            } else {
                // Different error, log but don't throw - show empty cart
                error_log("Unexpected error checking cart_items table: " . $e->getMessage());
            }
        }
    }
}
?>