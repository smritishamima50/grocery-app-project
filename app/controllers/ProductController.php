<?php
require_once 'BaseController.php';

class ProductController extends BaseController {
    public function index() {
        $category = $_GET['category'] ?? null;
        $search = $_GET['search'] ?? null;
        $page = $_GET['page'] ?? 1;
        $limit = 12;
        $offset = ($page - 1) * $limit;

        $where = [];
        $params = [];

        // Always filter for active products
        $where[] = "p.is_active = 1";

        if ($category) {
            $where[] = "p.category_id = ?";
            $params[] = $category;
        }

        if ($search) {
            $where[] = "p.name LIKE ?";
            $params[] = "%$search%";
        }

        $whereClause = "WHERE " . implode(" AND ", $where);

        // Get products - ordered by creation date (newest first) so new products appear
        $stmt = $this->pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id $whereClause ORDER BY p.created_at DESC, p.id DESC LIMIT $limit OFFSET $offset");
        $stmt->execute($params);
        $products = $stmt->fetchAll();

        // Get total count for pagination
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM products p $whereClause");
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];
        $totalPages = ceil($total / $limit);

        // Get categories for filter
        $stmt = $this->pdo->prepare("SELECT * FROM categories");
        $stmt->execute();
        $categories = $stmt->fetchAll();

        $this->render('products/index', [
            'products' => $products,
            'categories' => $categories,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'category' => $category
        ]);
    }

    public function show($id) {
        require_once __DIR__ . '/../helpers/DietHelper.php';
        
        $stmt = $this->pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();

        if (!$product) {
            http_response_code(404);
            echo "Product not found";
            return;
        }

        // Get related products from same category (excluding current product) - only active products
        $stmt = $this->pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.category_id = ? AND p.id != ? AND p.is_active = 1 ORDER BY p.created_at DESC LIMIT 4");
        $stmt->execute([$product['category_id'], $id]);
        $relatedProducts = $stmt->fetchAll();

        // Check if product is recommended for user's diet
        $isRecommendedForDiet = false;
        if (isset($_SESSION['user_id'])) {
            $dietHelper = new DietHelper($this->pdo);
            $isRecommendedForDiet = $dietHelper->isProductSuitable($product, $_SESSION['user_id']);
        }

        $this->render('products/details', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'isRecommendedForDiet' => $isRecommendedForDiet
        ]);
    }
}
?>