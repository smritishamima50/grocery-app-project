<?php
require_once 'BaseController.php';

class ProductController extends BaseController {
    public function index() {
        // Get and validate category parameter - can be either ID (integer) or name (string)
        $category = null;
        $categoryId = null;
        if (isset($_GET['category']) && !empty($_GET['category'])) {
            $categoryParam = $_GET['category'];
            
            // First, try to validate as integer (category ID)
            $categoryId = filter_var($categoryParam, FILTER_VALIDATE_INT);
            
            // If not a valid integer, treat it as a category name and look it up
            if ($categoryId === false || $categoryId <= 0) {
                // Look up category by name (case-insensitive, flexible matching)
                $stmt = $this->pdo->prepare("SELECT id FROM categories WHERE LOWER(TRIM(name)) = LOWER(TRIM(?)) OR LOWER(TRIM(name)) LIKE LOWER(TRIM(?))");
                $searchName = trim($categoryParam);
                $stmt->execute([$searchName, "%$searchName%"]);
                $categoryResult = $stmt->fetch();
                
                if ($categoryResult) {
                    $categoryId = $categoryResult['id'];
                } else {
                    // Try common variations
                    $categoryMappings = [
                        'fruits' => 'Fruits & Vegetables',
                        'vegetables' => 'Fruits & Vegetables',
                        'fruit' => 'Fruits & Vegetables',
                        'vegetable' => 'Fruits & Vegetables',
                        'spices' => 'Spices & Herbs',
                        'spice' => 'Spices & Herbs',
                        'masala' => 'Spices & Herbs',
                        'herbs' => 'Spices & Herbs',
                        'herb' => 'Spices & Herbs',
                        'baking' => 'Baking Needs',
                        'bakery' => 'Bakery',
                        'dairy' => 'Dairy & Eggs',
                        'eggs' => 'Dairy & Eggs',
                        'meat' => 'Meat & Poultry',
                        'poultry' => 'Meat & Poultry',
                        'natural products' => 'Other Natural Products',
                        'natural' => 'Other Natural Products'
                    ];
                    
                    $searchLower = strtolower(trim($categoryParam));
                    if (isset($categoryMappings[$searchLower])) {
                        $stmt = $this->pdo->prepare("SELECT id FROM categories WHERE name = ?");
                        $stmt->execute([$categoryMappings[$searchLower]]);
                        $categoryResult = $stmt->fetch();
                        if ($categoryResult) {
                            $categoryId = $categoryResult['id'];
                        }
                    }
                }
            }
        }
        
        $search = $_GET['search'] ?? null;
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = 12;
        $offset = ($page - 1) * $limit;

        $where = [];
        $params = [];

        // Always filter for active products
        $where[] = "p.is_active = 1";

        if ($categoryId) {
            $where[] = "p.category_id = ?";
            $params[] = $categoryId;
            $category = $categoryId; // Store for view
        }

        if ($search) {
            $where[] = "p.name LIKE ?";
            $params[] = "%$search%";
        }

        // Build WHERE clause - always has at least is_active = 1, so never empty
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

        // Get categories for filter - ordered by name for better UX
        $stmt = $this->pdo->prepare("SELECT * FROM categories ORDER BY name ASC");
        $stmt->execute();
        $categories = $stmt->fetchAll();

        // Log for debugging (remove in production)
        if ($category) {
            error_log("ProductController: Filtering by category ID: $category");
            error_log("ProductController: Found " . count($products) . " products");
        }

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