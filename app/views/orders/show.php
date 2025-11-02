<?php
$title = 'Order #' . $order['id'] . ' - GroceryApp';
ob_start();
?>

<div class="max-w-7xl mx-auto px-4 py-8 animate-fade-in">
    <!-- Breadcrumb -->
    <nav class="flex mb-8" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="/" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-green-600 transition-colors duration-200">
                    <i class="fas fa-home mr-2"></i>
                    Home
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <a href="/orders" class="text-sm font-medium text-gray-700 hover:text-green-600 transition-colors duration-200">My Orders</a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <span class="text-sm font-medium text-gray-500">Order #<?php echo $order['id']; ?></span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Order Header -->
    <div class="bg-white rounded-2xl shadow-xl p-6 mb-8 animate-slide-up">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Order #<?php echo $order['id']; ?></h1>
                <p class="text-gray-600">Placed on <?php echo date('F j, Y \a\t g:i A', strtotime($order['created_at'])); ?></p>
            </div>
            <div class="flex items-center space-x-4 mt-4 lg:mt-0">
                <?php
                $statusColors = [
                    'placed' => 'bg-blue-100 text-blue-800',
                    'confirmed' => 'bg-yellow-100 text-yellow-800',
                    'packed' => 'bg-purple-100 text-purple-800',
                    'shipped' => 'bg-indigo-100 text-indigo-800',
                    'delivered' => 'bg-green-100 text-green-800',
                    'cancelled' => 'bg-red-100 text-red-800'
                ];
                $statusColor = $statusColors[$order['status']] ?? 'bg-gray-100 text-gray-800';
                ?>
                <span class="px-4 py-2 rounded-full text-sm font-semibold <?php echo $statusColor; ?> capitalize">
                    <?php echo str_replace('_', ' ', $order['status']); ?>
                </span>
                <?php if (in_array($order['status'], ['placed', 'confirmed'])): ?>
                    <button onclick="cancelOrder(<?php echo $order['id']; ?>)" class="bg-red-600 text-white px-4 py-2 rounded-xl font-semibold hover:bg-red-700 transition-colors duration-200">
                        <i class="fas fa-times mr-2"></i>Cancel Order
                    </button>
                <?php endif; ?>
                <button onclick="reorder(<?php echo $order['id']; ?>)" class="bg-purple-600 text-white px-4 py-2 rounded-xl font-semibold hover:bg-purple-700 transition-colors duration-200">
                    <i class="fas fa-redo mr-2"></i>Reorder
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Order Items -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-xl p-6 animate-slide-up">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Order Items</h2>
                
                <?php
                // Display surprise gifts if any
                if (!empty($surpriseGifts)) {
                    echo '<div class="mb-6 p-4 bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-xl">';
                    echo '<div class="flex items-center mb-3">';
                    echo '<i class="fas fa-gift text-yellow-600 text-2xl mr-3"></i>';
                    echo '<h3 class="text-lg font-bold text-yellow-800">🎁 Surprise Gift Included!</h3>';
                    echo '</div>';
                    echo '<p class="text-yellow-700 mb-3">You received a free gift with your order:</p>';
                    
                    foreach ($surpriseGifts as $gift) {
                        echo '<div class="flex items-center space-x-3 p-3 bg-white rounded-lg border border-yellow-200">';
                        echo '<div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">';
                        echo '<i class="fas fa-gift text-yellow-600"></i>';
                        echo '</div>';
                        echo '<div class="flex-1">';
                        echo '<p class="font-semibold text-gray-900">' . htmlspecialchars($gift['product_name']) . '</p>';
                        echo '<p class="text-sm text-gray-600">' . htmlspecialchars($gift['description']) . '</p>';
                        echo '</div>';
                        echo '<div class="text-right">';
                        echo '<p class="text-sm text-gray-600">Qty: ' . $gift['quantity'] . '</p>';
                        echo '<p class="font-bold text-green-600">FREE!</p>';
                        echo '</div>';
                        echo '</div>';
                    }
                    echo '</div>';
                }
                ?>
                
                <div class="space-y-4">
                    <?php foreach ($orderItems as $item): ?>
                        <div class="flex items-center space-x-4 p-4 border border-gray-200 rounded-xl hover:shadow-md transition-shadow duration-200">
                            <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center overflow-hidden">
                                <?php if ($item['image']): ?>
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <i class="fas fa-image text-gray-400"></i>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($item['unit']); ?></p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900">৳<?php echo number_format($item['unit_price'], 2); ?></p>
                                <p class="text-sm text-gray-600">Qty: <?php echo $item['quantity']; ?></p>
                                <p class="text-sm font-semibold text-green-600">৳<?php echo number_format($item['total_price'], 2); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Order Summary -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="text-gray-900">৳<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Delivery:</span>
                            <span class="text-gray-900">৳0.00</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-300">
                            <span class="text-gray-900">Total:</span>
                            <span class="text-green-600">৳<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Details Sidebar -->
        <div class="space-y-6">
            <!-- Delivery Information -->
            <div class="bg-white rounded-2xl shadow-xl p-6 animate-slide-up" style="animation-delay: 0.2s;">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-truck mr-2 text-blue-600"></i>
                    Delivery Information
                </h3>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm font-medium text-gray-700">Delivery Slot:</span>
                        <p class="text-gray-900"><?php echo htmlspecialchars($order['delivery_slot_display'] ?? ($order['delivery_slot'] ?? 'Not scheduled')); ?></p>
                    </div>
                    <?php if ($order['address_line1']): ?>
                        <div>
                            <span class="text-sm font-medium text-gray-700">Delivery Address:</span>
                            <div class="text-gray-900 mt-1">
                                <p><?php echo htmlspecialchars($order['address_line1']); ?></p>
                                <?php if ($order['address_line2']): ?>
                                    <p><?php echo htmlspecialchars($order['address_line2']); ?></p>
                                <?php endif; ?>
                                <p><?php echo htmlspecialchars($order['city']); ?><?php if ($order['state']): ?>, <?php echo htmlspecialchars($order['state']); ?><?php endif; ?></p>
                                <p><?php echo htmlspecialchars($order['zip_code']); ?>, <?php echo htmlspecialchars($order['country']); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Payment Information -->
            <div class="bg-white rounded-2xl shadow-xl p-6 animate-slide-up" style="animation-delay: 0.4s;">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-credit-card mr-2 text-green-600"></i>
                    Payment Information
                </h3>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm font-medium text-gray-700">Payment Method:</span>
                        <p class="text-gray-900 capitalize"><?php echo str_replace('_', ' ', $order['payment_method']); ?></p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-700">Payment Status:</span>
                        <?php
                        $paymentStatusColors = [
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'completed' => 'bg-green-100 text-green-800',
                            'failed' => 'bg-red-100 text-red-800',
                            'refunded' => 'bg-blue-100 text-blue-800'
                        ];
                        $paymentColor = $paymentStatusColors[$order['payment_status']] ?? 'bg-gray-100 text-gray-800';
                        ?>
                        <span class="px-2 py-1 rounded-full text-xs font-semibold <?php echo $paymentColor; ?> capitalize">
                            <?php echo $order['payment_status']; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Order Tracking -->
            <div class="bg-white rounded-2xl shadow-xl p-6 animate-slide-up" style="animation-delay: 0.6s;">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-route mr-2 text-purple-600"></i>
                    Order Tracking
                </h3>
                <div class="space-y-3">
                    <?php if (empty($deliveryUpdates)): ?>
                        <p class="text-gray-600 text-sm">No tracking updates available yet.</p>
                    <?php else: ?>
                        <?php foreach ($deliveryUpdates as $update): ?>
                            <div class="flex items-start space-x-3">
                                <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 capitalize"><?php echo str_replace('_', ' ', $update['status']); ?></p>
                                    <p class="text-xs text-gray-600"><?php echo date('M j, g:i A', strtotime($update['updated_at'])); ?></p>
                                    <?php if ($update['message']): ?>
                                        <p class="text-sm text-gray-700 mt-1"><?php echo htmlspecialchars($update['message']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="mt-4">
                    <a href="/orders/track/<?php echo $order['id']; ?>" class="w-full bg-purple-600 text-white py-2 rounded-xl font-semibold hover:bg-purple-700 transition-colors duration-200 text-center block">
                        <i class="fas fa-map-marked-alt mr-2"></i>View Full Tracking
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function cancelOrder(orderId) {
    if (confirm('Are you sure you want to cancel this order? This action cannot be undone.')) {
        fetch('/orders/cancel', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'order_id=' + orderId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Order cancelled successfully', 'success');
                location.reload();
            } else {
                showToast(data.message || 'Failed to cancel order', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
        });
    }
}

function reorder(orderId) {
    fetch('/orders/reorder', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'order_id=' + orderId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            // Update cart count
            updateCartCount();
            setTimeout(() => {
                if (confirm('Items added to cart. Would you like to view your cart?')) {
                    window.location.href = '/cart';
                }
            }, 1000);
        } else {
            showToast(data.message || 'Failed to reorder', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    });
}

function updateCartCount() {
    fetch('/cart/count')
        .then(response => response.json())
        .then(data => {
            const cartBadge = document.querySelector('.cart-badge');
            if (cartBadge && data.count > 0) {
                cartBadge.textContent = data.count;
                cartBadge.classList.remove('hidden');
            }
        })
        .catch(error => console.error('Error updating cart count:', error));
}
</script>

<?php
$content = ob_get_clean();
include 'app/views/layouts/main.php';
?>