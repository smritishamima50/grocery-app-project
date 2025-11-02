<?php
require_once 'BaseController.php';
require_once 'app/helpers/SurpriseGiftHelper.php';

class CheckoutController extends BaseController {
    public function __construct() {
        parent::__construct();
        $this->requireLogin();
    }

    public function index() {
        $userId = $_SESSION['user_id'];

        // Get cart items
        $stmt = $this->pdo->prepare("
            SELECT ci.*, p.name, p.price, p.image, p.unit
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            WHERE ci.user_id = ?
        ");
        $stmt->execute([$userId]);
        $cartItems = $stmt->fetchAll();

        if (empty($cartItems)) {
            $this->redirect('/cart');
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

        // Get user addresses
        $stmt = $this->pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ?");
        $stmt->execute([$userId]);
        $addresses = $stmt->fetchAll();

        // Add logging
        error_log("Checkout index - User ID: " . $userId);
        error_log("Checkout index - Cart items: " . count($cartItems));
        error_log("Checkout index - Addresses: " . count($addresses));

        $this->render('checkout/index', [
            'cartItems' => $cartItems,
            'total' => $total,
            'discount' => $discount,
            'finalTotal' => $finalTotal,
            'appliedCoupon' => $appliedCoupon,
            'addresses' => $addresses
        ]);
    }

    public function process() {
        // This legacy endpoint is disabled in favor of the API flow used by the checkout page JavaScript.
        error_log("CheckoutController::process called - redirecting to /checkout to use modern API flow");
        $this->redirect('/checkout');
    }
    
    private function getUserOrderCount($userId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch()['count'];
    }
}
?>