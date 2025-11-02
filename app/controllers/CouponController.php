<?php
require_once 'BaseController.php';

class CouponController extends BaseController {
    
    public function __construct() {
        parent::__construct();
    }

    /**
     * Get all active coupons for display on home page
     */
    public function getActiveCoupons() {
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }

        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM coupons 
                WHERE (expiry_date IS NULL OR expiry_date >= CURDATE()) 
                AND (usage_limit IS NULL OR used_count < usage_limit)
                ORDER BY created_at DESC
            ");
            $stmt->execute();
            $coupons = $stmt->fetchAll();

            echo json_encode([
                'success' => true,
                'coupons' => $coupons
            ]);
        } catch (PDOException $e) {
            error_log("Coupon fetch error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Failed to fetch coupons'
            ]);
        }
    }

    /**
     * Validate and apply coupon to cart
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
                    ]
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
     * Get applied coupon details
     */
    public function getAppliedCoupon() {
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }

        if (isset($_SESSION['applied_coupon'])) {
            echo json_encode([
                'success' => true,
                'coupon' => $_SESSION['applied_coupon']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No coupon applied'
            ]);
        }
    }

    /**
     * Update coupon usage count after successful order
     */
    public function updateCouponUsage($couponId) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE coupons 
                SET used_count = used_count + 1 
                WHERE id = ?
            ");
            $stmt->execute([$couponId]);
        } catch (PDOException $e) {
            error_log("Coupon usage update error: " . $e->getMessage());
        }
    }
}
?>
