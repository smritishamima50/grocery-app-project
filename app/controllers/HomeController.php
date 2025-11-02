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

        if (isset($_SESSION['user_id'])) {
            // Show diet-based recommendations for logged in users
            require_once __DIR__ . '/../helpers/DietHelper.php';
            $dietHelper = new DietHelper($this->pdo);
            $recommendedProducts = $dietHelper->getRecommendedProducts($_SESSION['user_id'], 8);
            
            // If no recommendations, show featured
            if (empty($recommendedProducts)) {
                $stmt = $this->pdo->prepare("
                    SELECT p.*, c.name as category_name 
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    WHERE p.is_active = 1 AND p.stock_quantity > 0 
                    ORDER BY p.created_at DESC, p.id DESC
                    LIMIT 8
                ");
                $stmt->execute();
                $featuredProducts = $stmt->fetchAll();
            }
        } else {
            // Show featured products for guests (newest first)
            $stmt = $this->pdo->prepare("
                SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.is_active = 1 AND p.stock_quantity > 0 
                ORDER BY p.created_at DESC, p.id DESC
                LIMIT 8
            ");
            $stmt->execute();
            $featuredProducts = $stmt->fetchAll();
        }

        $this->render('home/index', [
            'featuredProducts' => $featuredProducts,
            'recommendedProducts' => $recommendedProducts,
            'categories' => $categories,
            'coupons' => $coupons
        ]);
    }
}
?>