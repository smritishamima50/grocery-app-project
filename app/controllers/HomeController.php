<?php
require_once 'BaseController.php';

class HomeController extends BaseController {
    public function index() {
        // Get categories
        $stmt = $this->pdo->prepare("SELECT * FROM categories");
        $stmt->execute();
        $categories = $stmt->fetchAll();

        // Get active coupons for display
        $stmt = $this->pdo->prepare("
            SELECT * FROM coupons 
            WHERE (expiry_date IS NULL OR expiry_date >= CURDATE()) 
            AND (usage_limit IS NULL OR used_count < usage_limit)
            ORDER BY created_at DESC
            LIMIT 3
        ");
        $stmt->execute();
        $coupons = $stmt->fetchAll();

        // Get featured or recommended products
        $featuredProducts = [];
        $recommendedProducts = [];

        // ALWAYS include products from Fruits & Vegetables and Meat & Poultry categories
        // These should appear in "Recommended for You" section for ALL users
        // Get products from both categories, prioritizing Fruits & Vegetables first
        $stmt = $this->pdo->prepare("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.is_active = 1 AND p.stock_quantity > 0 
            AND LOWER(TRIM(c.name)) IN ('fruits & vegetables', 'meat & poultry')
            ORDER BY 
                CASE LOWER(TRIM(c.name))
                    WHEN 'fruits & vegetables' THEN 1
                    WHEN 'meat & poultry' THEN 2
                    ELSE 3
                END,
                p.created_at DESC, p.id DESC
            LIMIT 8
        ");
        $stmt->execute();
        $categoryProducts = $stmt->fetchAll();
        
        // Track product IDs already included to avoid duplicates
        $includedProductIds = [];
        foreach ($categoryProducts as $product) {
            $includedProductIds[] = (int)$product['id'];
        }

        if (isset($_SESSION['user_id'])) {
            // Show diet-based recommendations for logged in users
            require_once __DIR__ . '/../helpers/DietHelper.php';
            $dietHelper = new DietHelper($this->pdo);
            $dietRecommendedProducts = $dietHelper->getRecommendedProducts($_SESSION['user_id'], 8);
            
            // Merge category products with diet recommendations
            // Prioritize Fruits & Vegetables and Meat & Poultry products
            $recommendedProducts = $categoryProducts;
            
            // Add diet-based recommendations that are not already included
            foreach ($dietRecommendedProducts as $product) {
                if (!in_array($product['id'], $includedProductIds) && count($recommendedProducts) < 8) {
                    $recommendedProducts[] = $product;
                    $includedProductIds[] = $product['id'];
                }
            }
            
            // If still not enough products, fill with featured products
            if (count($recommendedProducts) < 8) {
                $neededCount = 8 - count($recommendedProducts);
                $excludeIds = !empty($includedProductIds) ? implode(',', $includedProductIds) : '0';
                
                $stmt = $this->pdo->prepare("
                    SELECT p.*, c.name as category_name 
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    WHERE p.is_active = 1 AND p.stock_quantity > 0 
                    AND (c.name IS NULL OR LOWER(TRIM(c.name)) != 'home cleaning')
                    AND p.id NOT IN ($excludeIds)
                    ORDER BY p.created_at DESC, p.id DESC
                    LIMIT $neededCount
                ");
                $stmt->execute();
                $additionalProducts = $stmt->fetchAll();
                $recommendedProducts = array_merge($recommendedProducts, $additionalProducts);
            }
            
            // Limit to 8 products total
            $recommendedProducts = array_slice($recommendedProducts, 0, 8);
            
            // If no recommendations at all, show featured
            if (empty($recommendedProducts)) {
                $stmt = $this->pdo->prepare("
                    SELECT p.*, c.name as category_name 
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    WHERE p.is_active = 1 AND p.stock_quantity > 0 
                    AND (c.name IS NULL OR LOWER(TRIM(c.name)) != 'home cleaning')
                    ORDER BY p.created_at DESC, p.id DESC
                    LIMIT 8
                ");
                $stmt->execute();
                $featuredProducts = $stmt->fetchAll();
            }
        } else {
            // For guests: Show Fruits & Vegetables and Meat & Poultry as recommendations
            // Fill remaining slots with other featured products
            $recommendedProducts = $categoryProducts;
            
            if (count($recommendedProducts) < 8) {
                $stmt = $this->pdo->prepare("
                    SELECT p.*, c.name as category_name 
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    WHERE p.is_active = 1 AND p.stock_quantity > 0 
                    AND (c.name IS NULL OR LOWER(TRIM(c.name)) != 'home cleaning')
                    AND LOWER(TRIM(c.name)) NOT IN ('fruits & vegetables', 'meat & poultry')
                    ORDER BY p.created_at DESC, p.id DESC
                    LIMIT " . (8 - count($recommendedProducts)) . "
                ");
                $stmt->execute();
                $additionalProducts = $stmt->fetchAll();
                $recommendedProducts = array_merge($recommendedProducts, $additionalProducts);
            }
            
            // Limit to 8 products total
            $recommendedProducts = array_slice($recommendedProducts, 0, 8);
        }

        // Get wishlist items for logged-in users (limit to 4 for homepage display)
        $wishlistItems = [];
        if (isset($_SESSION['user_id'])) {
            try {
                $stmt = $this->pdo->prepare("
                    SELECT 
                        w.id as wishlist_id,
                        p.id as product_id,
                        p.name,
                        p.price,
                        p.image,
                        p.unit,
                        p.stock_quantity,
                        c.name as category_name
                    FROM wishlists w
                    JOIN products p ON w.product_id = p.id
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE w.user_id = ? AND p.is_active = 1
                    ORDER BY w.added_at DESC
                    LIMIT 4
                ");
                $stmt->execute([$_SESSION['user_id']]);
                $wishlistItems = $stmt->fetchAll();
            } catch (PDOException $e) {
                error_log("Home wishlist error: " . $e->getMessage());
            }
        }
        
        $this->render('home/index', [
            'featuredProducts' => $featuredProducts,
            'recommendedProducts' => $recommendedProducts,
            'categories' => $categories,
            'coupons' => $coupons,
            'wishlistItems' => $wishlistItems
        ]);
    }

    /**
     * About Us page
     */
    public function about() {
        $this->render('home/about', []);
    }

    /**
     * Contact page
     */
    public function contact() {
        // Get contact information from payment accounts config
        $paymentConfig = require __DIR__ . '/../../config/payment_accounts.php';
        
        // Extract contact information
        $contactInfo = [
            'email' => $paymentConfig['bkash']['support_email'] ?? 'support@grocerystore.com',
            'phone' => $paymentConfig['bkash']['support_phone'] ?? '01711-000000',
            'address' => 'Dhaka, Bangladesh'
        ];
        
        $this->render('home/contact', [
            'contactInfo' => $contactInfo
        ]);
    }
}
?>