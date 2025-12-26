<?php
// Entry point for the Grocery E-Commerce Application

// Timezone for consistent date/time display (Bangladesh)
date_default_timezone_set('Asia/Dhaka');

// Start session
session_start();

// Include configuration
require_once 'config/database.php';

// Autoload classes (basic implementation)
spl_autoload_register(function ($class_name) {
    $paths = [
        'app/models/',
        'app/controllers/',
        'app/helpers/'
    ];

    foreach ($paths as $path) {
        $file = $path . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Basic routing
$request = $_SERVER['REQUEST_URI'];
$base_path = '/'; // Adjust if your app is in a subdirectory

// Remove query string first
$request = explode('?', $request)[0];

// Remove only the leading slash, preserve internal slashes
$request = ltrim($request, '/');

// Handle the case where the request might be "products1" instead of "products/1"
if (preg_match('/^products(\d+)$/', $request, $matches)) {
    $request = 'products/' . $matches[1];
}

// Global CORS preflight handler for API routes
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS' && strpos($request, 'api/') === 0) {
    if (!headers_sent()) {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
    }
    http_response_code(204);
    exit;
}

// Debug: Log the request for troubleshooting
error_log("Request: '" . $request . "' | Full URI: '" . $_SERVER['REQUEST_URI'] . "'");

// Default route
if ($request === '' || $request === '/') {
    $controller = new HomeController();
    $controller->index();
} elseif ($request === 'about') {
    $controller = new HomeController();
    $controller->about();
} elseif ($request === 'contact') {
    $controller = new HomeController();
    $controller->contact();
} elseif ($request === 'login') {
    $controller = new AuthController();
    $controller->login();
} elseif ($request === 'signup') {
    $controller = new AuthController();
    $controller->signup();
} elseif ($request === 'logout') {
    $controller = new AuthController();
    $controller->logout();
} elseif ($request === 'products') {
    $controller = new ProductController();
    $controller->index();
} elseif (preg_match('/products\/(\d+)/', $request, $matches)) {
    $controller = new ProductController();
    $controller->show($matches[1]);
} elseif ($request === 'cart') {
    $controller = new CartController();
    $controller->index();
} elseif ($request === 'cart/add') {
    $controller = new CartController();
    $controller->add();
} elseif ($request === 'cart/count') {
    $controller = new CartController();
    $controller->count();
} elseif ($request === 'cart/totals') {
    $controller = new CartController();
    $controller->totals();
} elseif ($request === 'cart/update') {
    $controller = new CartController();
    $controller->update();
} elseif ($request === 'cart/remove') {
    $controller = new CartController();
    $controller->remove();
} elseif ($request === 'cart/clear') {
    $controller = new CartController();
    $controller->clear();
} elseif ($request === 'cart/apply-coupon') {
    $controller = new CartController();
    $controller->applyCoupon();
} elseif ($request === 'cart/remove-coupon') {
    $controller = new CartController();
    $controller->removeCoupon();
} elseif ($request === 'cart/select-surprise-gift') {
    $controller = new CartController();
    $controller->selectSurpriseGift();
} elseif ($request === 'wishlist') {
    require_once 'app/controllers/WishlistController.php';
    $controller = new WishlistController();
    $controller->index();
} elseif ($request === 'wishlist/add') {
    require_once 'app/controllers/WishlistController.php';
    $controller = new WishlistController();
    $controller->add();
} elseif ($request === 'wishlist/remove') {
    require_once 'app/controllers/WishlistController.php';
    $controller = new WishlistController();
    $controller->remove();
} elseif ($request === 'wishlist/count') {
    require_once 'app/controllers/WishlistController.php';
    $controller = new WishlistController();
    $controller->count();
} elseif ($request === 'coupons/active') {
    $controller = new CouponController();
    $controller->getActiveCoupons();
} elseif ($request === 'checkout') {
    $controller = new CheckoutController();
    $controller->index();
} elseif ($request === 'checkout/process') {
    $controller = new CheckoutController();
    $controller->process();
} elseif ($request === 'checkout/success') {
    include 'app/views/checkout/success.php';
    exit;
} elseif ($request === 'profile') {
    $controller = new ProfileController();
    $controller->index();
} elseif ($request === 'orders') {
    $controller = new OrdersController();
    $controller->index();
} elseif (preg_match('/^orders\/(\d+)$/', $request, $matches)) {
    $controller = new OrdersController();
    $controller->show($matches[1]);
} elseif (preg_match('/^orders\/track\/(\d+)$/', $request, $matches)) {
    $controller = new OrdersController();
    $controller->track($matches[1]);
} elseif ($request === 'orders/cancel') {
    $controller = new OrdersController();
    $controller->cancel();
} elseif ($request === 'orders/reorder') {
    $controller = new OrdersController();
    $controller->reorder();
} elseif ($request === 'orders/reorder-all') {
    $controller = new OrdersController();
    $controller->reorderAll();
} elseif ($request === 'profile/update') {
    $controller = new ProfileController();
    $controller->update();
} elseif ($request === 'profile/add-address') {
    $controller = new ProfileController();
    $controller->addAddress();
} elseif ($request === 'profile/update-address') {
    $controller = new ProfileController();
    $controller->updateAddress();
} elseif ($request === 'profile/delete-address') {
    $controller = new ProfileController();
    $controller->deleteAddress();
} elseif (preg_match('/^profile\/address\/(\d+)$/', $request, $matches)) {
    $controller = new ProfileController();
    $controller->getAddress($matches[1]);
} elseif ($request === 'profile/save-diet-profile') {
    $controller = new ProfileController();
    $controller->saveDietProfile();
} elseif ($request === 'profile/save-family-member') {
    $controller = new ProfileController();
    $controller->saveFamilyMember();
} elseif ($request === 'profile/delete-family-member') {
    $controller = new ProfileController();
    $controller->deleteFamilyMember();
} elseif ($request === 'subscriptions') {
    $controller = new SubscriptionsController();
    $controller->index();
} elseif ($request === 'subscriptions/create') {
    $controller = new SubscriptionsController();
    $controller->create();
} elseif ($request === 'subscriptions/store') {
    $controller = new SubscriptionsController();
    $controller->store();
} elseif (preg_match('/^subscriptions\/pause\/(\d+)$/', $request, $matches)) {
    $controller = new SubscriptionsController();
    $controller->pause($matches[1]);
} elseif (preg_match('/^subscriptions\/resume\/(\d+)$/', $request, $matches)) {
    $controller = new SubscriptionsController();
    $controller->resume($matches[1]);
} elseif (preg_match('/^subscriptions\/cancel\/(\d+)$/', $request, $matches)) {
    $controller = new SubscriptionsController();
    $controller->cancel($matches[1]);
} elseif ($request === 'admin') {
    $controller = new AdminController();
    $controller->dashboard();
} elseif ($request === 'admin/subscriptions') {
    $controller = new AdminController();
    $controller->subscriptions();
} elseif ($request === 'admin/create-subscription') {
    $controller = new AdminController();
    $controller->usersForSubscription();
} elseif ($request === 'admin/create-subscription/store') {
    $controller = new AdminController();
    $controller->createSubscription();
} elseif ($request === 'admin/products') {
    $controller = new AdminController();
    $controller->products();
} elseif ($request === 'admin/products/create') {
    $controller = new AdminController();
    $controller->createProduct();
} elseif ($request === 'admin/products/bulk-import') {
    $controller = new AdminController();
    $controller->bulkImportProducts();
} elseif (preg_match('/^admin\/products\/edit\/(\d+)$/', $request, $matches)) {
    $controller = new AdminController();
    $controller->editProduct($matches[1]);
} elseif (preg_match('/^admin\/products\/delete\/(\d+)$/', $request, $matches)) {
    $controller = new AdminController();
    $controller->deleteProduct($matches[1]);
} elseif ($request === 'admin/categories') {
    $controller = new AdminController();
    $controller->categories();
} elseif ($request === 'admin/categories/create') {
    $controller = new AdminController();
    $controller->createCategory();
} elseif (preg_match('/^admin\/categories\/edit\/(\d+)$/', $request, $matches)) {
    $controller = new AdminController();
    $controller->editCategory($matches[1]);
} elseif (preg_match('/^admin\/categories\/delete\/(\d+)$/', $request, $matches)) {
    $controller = new AdminController();
    $controller->deleteCategory($matches[1]);
} elseif ($request === 'admin/drivers') {
    $controller = new AdminController();
    $controller->drivers();
} elseif ($request === 'admin/orders') {
    $controller = new AdminController();
    $controller->orders();
} elseif ($request === 'admin/orders/update-status') {
    $controller = new AdminController();
    $controller->updateOrderStatus();
} elseif (preg_match('/^admin\/orders\/(\d+)$/', $request, $matches)) {
    $controller = new AdminController();
    $controller->getOrderDetails($matches[1]);
} elseif (preg_match('/^admin\/orders\/(\d+)\/delivered$/', $request, $matches)) {
    $controller = new AdminController();
    $controller->markAsDelivered($matches[1]);
} elseif (preg_match('/^admin\/orders\/(\d+)\/cancel$/', $request, $matches)) {
    $controller = new AdminController();
    $controller->cancelOrder($matches[1]);
} elseif ($request === 'admin/users') {
    $controller = new AdminController();
    $controller->users();
} elseif ($request === 'admin/users/update-role') {
    $controller = new AdminController();
    $controller->updateUserRole();
} elseif (preg_match('/^admin\/users\/delete\/(\d+)$/', $request, $matches)) {
    $controller = new AdminController();
    $controller->deleteUser($matches[1]);
} elseif ($request === 'admin/users/create') {
    $controller = new AdminController();
    $controller->createUser();
} elseif (preg_match('/^admin\/users\/(\d+)$/', $request, $matches)) {
    $controller = new AdminController();
    $controller->showUser($matches[1]);
} elseif (preg_match('/^admin\/users\/(\d+)\/edit$/', $request, $matches)) {
    $controller = new AdminController();
    $controller->editUser($matches[1]);
} elseif ($request === 'admin/coupons') {
    $controller = new AdminController();
    $controller->coupons();
} elseif ($request === 'admin/coupons/create') {
    $controller = new AdminController();
    $controller->createCoupon();
} elseif (preg_match('/^admin\/coupons\/edit\/(\d+)$/', $request, $matches)) {
    $controller = new AdminController();
    $controller->editCoupon($matches[1]);
} elseif (preg_match('/^admin\/coupons\/delete\/(\d+)$/', $request, $matches)) {
    $controller = new AdminController();
    $controller->deleteCoupon($matches[1]);
} elseif ($request === 'admin/analytics') {
    $controller = new AdminController();
    $controller->analytics();
} elseif ($request === 'admin/inventory') {
    $controller = new AdminController();
    $controller->inventory();
} elseif ($request === 'admin/inventory/update-stock') {
    $controller = new AdminController();
    $controller->updateStock();
} elseif ($request === 'admin/surprise-gifts') {
    $controller = new AdminController();
    $controller->surpriseGifts();
} elseif ($request === 'admin/surprise-gifts/save') {
    $controller = new AdminController();
    $controller->saveSurpriseGift();
} elseif ($request === 'api/admin/analytics/summary') {
    $controller = new ApiController();
    $controller->analyticsSummary();
} elseif ($request === 'api/admin/analytics/top-categories') {
    $controller = new ApiController();
    $controller->topCategories();
} elseif ($request === 'api/orders/create') {
    $controller = new ApiController();
    $controller->createClientOrder();
} elseif ($request === 'api/payments/initiate') {
    $controller = new ApiController();
    $controller->initiatePayment();
} elseif ($request === 'api/payments/confirm') {
    $controller = new ApiController();
    $controller->confirmPayment();
} elseif ($request === 'api/admin/inventory') {
    $controller = new ApiController();
    $controller->inventory();
} elseif (preg_match('/^api\/admin\/inventory\/(\d+)$/', $request, $matches)) {
    $controller = new ApiController();
    $controller->updateInventory($matches[1]);
} elseif ($request === 'api/admin/orders') {
    $controller = new ApiController();
    $controller->orders();
} elseif ($request === 'api/admin/orders/export') {
    $controller = new ApiController();
    $controller->exportOrders();
} elseif (preg_match('/^api\/admin\/orders\/(\d+)$/', $request, $matches)) {
    $controller = new ApiController();
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $controller->getOrder($matches[1]);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
        $controller->updateOrder($matches[1]);
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
    }
} elseif (preg_match('/^api\/admin\/orders\/(\d+)\/update$/', $request, $matches)) {
    $controller = new ApiController();
    $controller->updateOrder($matches[1]);
} elseif (preg_match('/^api\/admin\/orders\/(\d+)\/delivered$/', $request, $matches)) {
    $controller = new ApiController();
    $controller->markAsDelivered($matches[1]);
} elseif (preg_match('/^api\/admin\/orders\/(\d+)\/cancel$/', $request, $matches)) {
    $controller = new ApiController();
    $controller->cancelOrder($matches[1]);
} elseif ($request === 'api/admin/drivers') {
    $controller = new ApiController();
    $controller->drivers();
} elseif ($request === 'api/admin/products') {
    $controller = new ApiController();
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $controller->getProducts();
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->createProduct();
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
} elseif ($request === 'api/admin/products/bulk-import') {
    $controller = new ApiController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->bulkImportProducts();
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
} elseif (preg_match('/^api\/admin\/products\/(\d+)$/', $request, $matches)) {
    $controller = new ApiController();
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $controller->getProduct($matches[1]);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
        $controller->updateProduct($matches[1]);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $controller->deleteProduct($matches[1]);
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
} elseif ($request === 'api/admin/coupons') {
    $controller = new ApiController();
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $controller->getCoupons();
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->createCoupon();
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
} elseif (preg_match('/^api\/admin\/coupons\/(\d+)$/', $request, $matches)) {
    $controller = new ApiController();
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $controller->getCoupon($matches[1]);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
        $controller->updateCoupon($matches[1]);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $controller->deleteCoupon($matches[1]);
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
} elseif ($request === 'api/admin/users') {
    $controller = new ApiController();
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $controller->getUsers();
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
} elseif (preg_match('/^api\/admin\/users\/(\d+)$/', $request, $matches)) {
    $controller = new ApiController();
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $controller->getUser($matches[1]);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
        $controller->updateUser($matches[1]);
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
} elseif ($request === '403') {
    // 403 - Forbidden
    http_response_code(403);
    include 'app/views/errors/403.php';
    exit;
} else {
    // 404 - Page not found
    http_response_code(404);
    include 'app/views/errors/404.php';
    exit;
}
?>