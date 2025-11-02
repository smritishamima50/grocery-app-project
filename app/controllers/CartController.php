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
                       p.fat_per_unit, p.fiber_per_unit, p.sodium_per_unit
                FROM cart_items ci
                JOIN products p ON ci.product_id = p.id
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

        // Get user diet profile for warnings
        $dietHelper = new DietHelper($this->pdo);
        $userDietProfile = $dietHelper->getUserDietProfile($userId);
        
        // Get calorie recommendations
        require_once __DIR__ . '/../helpers/CalorieHelper.php';
        $calorieHelper = new CalorieHelper($this->pdo);
        $calorieRecommendation = $calorieHelper->getCalorieRecommendation($userId);

        // Get surprise gift options for user selection
        $surpriseGiftOptions = [];
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
                LIMIT 2
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
            'appliedCoupon' => $appliedCoupon,
            'userDietProfile' => $userDietProfile,
            'calorieRecommendation' => $calorieRecommendation,
            'surpriseGiftOptions' => $surpriseGiftOptions
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