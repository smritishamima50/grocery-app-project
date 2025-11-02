<?php
// Test Order Placement
session_start();
require_once 'config/database.php';

echo "<h1>Order Placement Test</h1>";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<p style='color: red;'>❌ User not logged in. Please login first.</p>";
    echo "<a href='/login'>Go to Login</a>";
    exit;
}

echo "<p style='color: green;'>✅ User logged in with ID: " . $_SESSION['user_id'] . "</p>";

// Check cart items
$stmt = $pdo->prepare("
    SELECT ci.*, p.name, p.price
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    WHERE ci.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$cartItems = $stmt->fetchAll();

echo "<h2>Cart Items (" . count($cartItems) . "):</h2>";
if (empty($cartItems)) {
    echo "<p style='color: red;'>❌ No cart items found. Add some products to cart first.</p>";
    echo "<a href='/products'>Go to Products</a>";
    exit;
}

foreach ($cartItems as $item) {
    echo "<p>• " . htmlspecialchars($item['name']) . " - Qty: " . $item['quantity'] . " - Price: ৳" . number_format($item['price'], 2) . "</p>";
}

// Check addresses
$stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$addresses = $stmt->fetchAll();

echo "<h2>Addresses (" . count($addresses) . "):</h2>";
if (empty($addresses)) {
    echo "<p style='color: orange;'>⚠️ No addresses found. You'll need to add an address during checkout.</p>";
} else {
    foreach ($addresses as $address) {
        echo "<p>• " . htmlspecialchars($address['address_line1']) . ", " . htmlspecialchars($address['city']) . "</p>";
    }
}

// Test order placement
if (isset($_POST['test_order'])) {
    echo "<h2>Testing Order Placement...</h2>";
    
    try {
        $pdo->beginTransaction();
        
        // Calculate total
        $total = 0;
        foreach ($cartItems as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        echo "<p>Total Amount: ৳" . number_format($total, 2) . "</p>";
        
        // Create order
        $stmt = $pdo->prepare("
            INSERT INTO orders (user_id, total_amount, delivery_address_id, payment_method, delivery_slot, status)
            VALUES (?, ?, ?, ?, ?, 'placed')
        ");
        $stmt->execute([
            $_SESSION['user_id'], 
            $total, 
            $addresses[0]['id'] ?? null, 
            'cash_on_delivery', 
            '2023-10-15 10:00-12:00'
        ]);
        $orderId = $pdo->lastInsertId();
        
        echo "<p style='color: green;'>✅ Order created with ID: " . $orderId . "</p>";
        
        // Create order items
        foreach ($cartItems as $item) {
            $stmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $orderId,
                $item['product_id'],
                $item['quantity'],
                $item['price'],
                $item['price'] * $item['quantity']
            ]);
        }
        
        echo "<p style='color: green;'>✅ Order items created</p>";
        
        // Clear cart
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
        echo "<p style='color: green;'>✅ Cart cleared</p>";
        
        $pdo->commit();
        
        echo "<p style='color: green; font-weight: bold;'>🎉 Order placement successful! Order ID: " . $orderId . "</p>";
        echo "<a href='/orders/" . $orderId . "'>View Order</a> | ";
        echo "<a href='/checkout/success?order_id=" . $orderId . "'>Success Page</a>";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    }
}
?>

<form method="POST">
    <button type="submit" name="test_order" style="background: green; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
        Test Order Placement
    </button>
</form>

<hr>
<p><a href="/checkout">Go to Checkout</a> | <a href="/products">Go to Products</a></p>
