<?php
require_once __DIR__ . '/BaseController.php';

class WishlistController extends BaseController {
    
    /**
     * Display user's wishlist
     * GET /wishlist
     */
    public function index() {
        $this->requireLogin();
        
        $userId = $_SESSION['user_id'];
        
        error_log("📋 ===== LOADING WISHLIST ===== ");
        error_log("📋 User ID: $userId");
        
        try {
            // First, check if wishlists table exists
            try {
                $this->pdo->query("SELECT 1 FROM wishlists LIMIT 1");
            } catch (PDOException $e) {
                error_log("📋 Wishlists table doesn't exist, creating...");
                $this->pdo->exec("
                    CREATE TABLE IF NOT EXISTS wishlists (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NOT NULL,
                        product_id INT NOT NULL,
                        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                        UNIQUE KEY unique_wishlist_item (user_id, product_id)
                    )
                ");
                error_log("✅ Wishlists table created");
            }
            
            // Check raw count first
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM wishlists WHERE user_id = ?");
            $stmt->execute([$userId]);
            $rawCount = $stmt->fetch();
            error_log("📋 Raw wishlist count for user: " . $rawCount['count']);
            
            // Get all wishlist items with product details
            // Use LEFT JOIN to show items even if product is inactive or deleted
            // Note: unit_size column may not exist in all databases, so we only select columns that are definitely needed
            $stmt = $this->pdo->prepare("
                SELECT 
                    w.id as wishlist_id,
                    w.added_at,
                    w.product_id,
                    p.id as product_id_check,
                    p.name,
                    p.description,
                    p.price,
                    p.image,
                    p.unit,
                    p.stock_quantity,
                    p.category_id,
                    c.name as category_name,
                    p.calories_per_unit,
                    p.is_active
                FROM wishlists w
                LEFT JOIN products p ON w.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE w.user_id = ?
                ORDER BY w.added_at DESC
            ");
            $stmt->execute([$userId]);
            $wishlistItems = $stmt->fetchAll();
            
            error_log("📋 Items returned from query: " . count($wishlistItems));
            
            // Log each item for debugging
            foreach ($wishlistItems as $index => $item) {
                error_log("📋 Item $index: Wishlist ID: " . $item['wishlist_id'] . ", Product ID: " . $item['product_id'] . ", Product Name: " . ($item['name'] ?? 'NULL'));
            }
            
            // Filter out items where product doesn't exist (product_id_check is NULL)
            // But keep items even if product is inactive
            $validItems = [];
            foreach ($wishlistItems as $item) {
                // Keep item if product exists (even if inactive) OR if we have at least wishlist_id
                if ($item['wishlist_id'] && $item['product_id']) {
                    // If product was deleted (product_id_check is NULL), we still show it but mark it
                    if ($item['product_id_check'] === null) {
                        // Product was deleted, but we'll still show it with a message
                        $item['name'] = $item['name'] ?? 'Product No Longer Available';
                        $item['price'] = $item['price'] ?? 0;
                        $item['image'] = $item['image'] ?? null;
                        $item['is_deleted'] = true;
                    }
                    $validItems[] = $item;
                }
            }
            
            error_log("📋 Valid items after filtering: " . count($validItems));
            
            // If we have items in database but query returns empty, try a simpler query
            if ($rawCount['count'] > 0 && count($validItems) == 0) {
                error_log("⚠️ WARNING: Database has " . $rawCount['count'] . " items but query returned 0. Trying alternative query...");
                
                // Try without JOIN to see what's in wishlists table
                $stmt = $this->pdo->prepare("SELECT * FROM wishlists WHERE user_id = ? ORDER BY added_at DESC");
                $stmt->execute([$userId]);
                $rawItems = $stmt->fetchAll();
                
                error_log("📋 Raw wishlist items: " . print_r($rawItems, true));
                
                // Try to get products separately
                foreach ($rawItems as $rawItem) {
                    $productStmt = $this->pdo->prepare("SELECT * FROM products WHERE id = ?");
                    $productStmt->execute([$rawItem['product_id']]);
                    $product = $productStmt->fetch();
                    
                    if ($product) {
                        // Get category name separately
                        $categoryStmt = $this->pdo->prepare("SELECT name FROM categories WHERE id = ?");
                        $categoryStmt->execute([$product['category_id']]);
                        $category = $categoryStmt->fetch();
                        
                        $validItems[] = [
                            'wishlist_id' => $rawItem['id'],
                            'added_at' => $rawItem['added_at'],
                            'product_id' => $product['id'],
                            'product_id_check' => $product['id'],
                            'name' => $product['name'],
                            'description' => $product['description'] ?? null,
                            'price' => $product['price'],
                            'image' => $product['image'] ?? null,
                            'unit' => $product['unit'] ?? null,
                            'stock_quantity' => $product['stock_quantity'] ?? 0,
                            'category_id' => $product['category_id'] ?? null,
                            'category_name' => $category['name'] ?? null,
                            'calories_per_unit' => $product['calories_per_unit'] ?? null,
                            'is_active' => $product['is_active'] ?? 1
                        ];
                    }
                }
                
                error_log("📋 Items after alternative query: " . count($validItems));
            }
            
            $this->render('wishlist/index', [
                'wishlistItems' => $validItems,
                'debug_info' => [
                    'raw_count' => $rawCount['count'] ?? 0,
                    'query_count' => count($wishlistItems),
                    'valid_count' => count($validItems)
                ]
            ]);
        } catch (PDOException $e) {
            error_log("❌ Wishlist error: " . $e->getMessage());
            error_log("❌ Error code: " . $e->getCode());
            error_log("❌ SQL State: " . ($e->errorInfo[0] ?? 'N/A'));
            error_log("❌ Stack trace: " . $e->getTraceAsString());
            
            $this->render('wishlist/index', [
                'wishlistItems' => [],
                'error' => 'Failed to load wishlist: ' . $e->getMessage()
            ]);
        } catch (Exception $e) {
            error_log("❌ Wishlist general error: " . $e->getMessage());
            $this->render('wishlist/index', [
                'wishlistItems' => [],
                'error' => 'Failed to load wishlist'
            ]);
        }
    }
    
    /**
     * Add product to wishlist
     * POST /wishlist/add
     */
    public function add() {
        // Set JSON header first
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        
        // Log the request
        error_log("🎁 ===== WISHLIST ADD REQUEST ===== ");
        error_log("🎁 Request Method: " . $_SERVER['REQUEST_METHOD']);
        error_log("🎁 POST Data: " . print_r($_POST, true));
        error_log("🎁 Session User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET'));
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("❌ Invalid request method: " . $_SERVER['REQUEST_METHOD']);
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            error_log("❌ User not logged in");
            echo json_encode(['success' => false, 'message' => 'Please login to add items to wishlist', 'login_required' => true]);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        
        error_log("🎁 User ID: $userId, Product ID: $productId");
        
        if ($productId <= 0) {
            error_log("❌ Invalid product ID: " . ($_POST['product_id'] ?? 'NOT SET'));
            echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
            return;
        }
        
        try {
            // Ensure wishlists table exists
            try {
                $this->pdo->query("SELECT 1 FROM wishlists LIMIT 1");
            } catch (PDOException $e) {
                // Table doesn't exist, create it
                error_log("📦 Creating wishlists table...");
                $this->pdo->exec("
                    CREATE TABLE IF NOT EXISTS wishlists (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NOT NULL,
                        product_id INT NOT NULL,
                        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                        UNIQUE KEY unique_wishlist_item (user_id, product_id)
                    )
                ");
                error_log("✅ Wishlists table created");
            }
            
            // Check if product exists and is active
            $stmt = $this->pdo->prepare("SELECT id, name FROM products WHERE id = ? AND is_active = 1");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            
            if (!$product) {
                error_log("❌ Product not found or not active: Product ID: $productId");
                echo json_encode(['success' => false, 'message' => 'Product not found or not available']);
                return;
            }
            
            error_log("✅ Product found: " . $product['name']);
            
            // Check if already in wishlist
            $stmt = $this->pdo->prepare("SELECT id FROM wishlists WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                error_log("⚠️ Product already in wishlist: Wishlist ID: " . $existing['id']);
                echo json_encode(['success' => false, 'message' => 'Product is already in your wishlist']);
                return;
            }
            
            // Add to wishlist
            $stmt = $this->pdo->prepare("INSERT INTO wishlists (user_id, product_id) VALUES (?, ?)");
            $result = $stmt->execute([$userId, $productId]);
            
            if ($result) {
                $wishlistId = $this->pdo->lastInsertId();
                error_log("✅ Product added to wishlist successfully!");
                error_log("✅ Wishlist ID: $wishlistId, User ID: $userId, Product ID: $productId");
                
                // Verify it was added
                $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM wishlists WHERE user_id = ?");
                $stmt->execute([$userId]);
                $count = $stmt->fetch();
                error_log("✅ Total wishlist items for user: " . $count['count']);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Successfully added to wishlist!',
                    'wishlist_id' => $wishlistId,
                    'product_id' => $productId,
                    'product_name' => $product['name']
                ]);
            } else {
                error_log("❌ INSERT failed but no exception thrown");
                echo json_encode(['success' => false, 'message' => 'Failed to add product to wishlist']);
            }
        } catch (PDOException $e) {
            error_log("❌ Add to wishlist PDO error: " . $e->getMessage());
            error_log("❌ Error code: " . $e->getCode());
            error_log("❌ SQL State: " . ($e->errorInfo[0] ?? 'N/A'));
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to add product to wishlist: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ]);
        } catch (Exception $e) {
            error_log("❌ Add to wishlist general error: " . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to add product to wishlist',
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Remove product from wishlist
     * POST /wishlist/remove
     */
    public function remove() {
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Please login']);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $wishlistId = isset($_POST['wishlist_id']) ? intval($_POST['wishlist_id']) : 0;
        
        try {
            if ($wishlistId > 0) {
                // Remove by wishlist ID
                $stmt = $this->pdo->prepare("DELETE FROM wishlists WHERE id = ? AND user_id = ?");
                $stmt->execute([$wishlistId, $userId]);
            } elseif ($productId > 0) {
                // Remove by product ID
                $stmt = $this->pdo->prepare("DELETE FROM wishlists WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$userId, $productId]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid request']);
                return;
            }
            
            if ($stmt->rowCount() > 0) {
                error_log("✅ Product removed from wishlist: User ID: $userId, Product ID: $productId");
                echo json_encode([
                    'success' => true,
                    'message' => 'Product removed from wishlist successfully!'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Item not found in wishlist']);
            }
        } catch (PDOException $e) {
            error_log("Remove from wishlist error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to remove product from wishlist']);
        }
    }
    
    /**
     * Get wishlist count (for badge)
     * GET /wishlist/count
     */
    public function count() {
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => true, 'count' => 0]);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM wishlists WHERE user_id = ?");
            $stmt->execute([$userId]);
            $result = $stmt->fetch();
            
            echo json_encode([
                'success' => true,
                'count' => intval($result['count'])
            ]);
        } catch (PDOException $e) {
            error_log("Wishlist count error: " . $e->getMessage());
            echo json_encode(['success' => true, 'count' => 0]);
        }
    }
}

