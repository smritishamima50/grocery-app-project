<?php
$title = 'My Orders - GroceryApp';
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
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <span class="text-sm font-medium text-gray-500">My Orders</span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">My Orders</h1>
        <p class="text-gray-600">Track and manage your grocery orders</p>
    </div>

    <!-- Smart Shopping List Section -->
    <?php if (!empty($orders)): ?>
        <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-2xl shadow-xl p-8 mb-8 text-white animate-slide-up">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h2 class="text-2xl font-bold mb-2 flex items-center">
                        <i class="fas fa-brain mr-3 text-yellow-300"></i>
                        AI Smart Shopping List
                    </h2>
                    <p class="text-purple-100">Quick reorder from your previous purchases</p>
                </div>
                <button onclick="reorderAll()" class="bg-white text-purple-600 px-8 py-4 rounded-xl font-bold text-lg hover:bg-purple-50 transition-all duration-300 transform hover:scale-105 flex items-center">
                    <i class="fas fa-magic mr-2"></i>1-Click Add All to Cart
                </button>
            </div>

            <!-- Quick Reorder Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                <?php 
                // Get the last 3 orders for quick reorder
                $recentOrders = array_slice($orders, 0, 3);
                foreach ($recentOrders as $recentOrder): 
                ?>
                    <div class="bg-white bg-opacity-20 backdrop-blur-lg rounded-xl p-4 hover:bg-opacity-30 transition-all duration-300 border border-white border-opacity-20">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <p class="font-semibold">Order #<?php echo $recentOrder['id']; ?></p>
                                <p class="text-xs text-purple-100"><?php echo date('M j, Y', strtotime($recentOrder['created_at'])); ?></p>
                                <p class="text-xs text-purple-100"><?php echo count($recentOrder['items'] ?? []); ?> items</p>
                            </div>
                            <span class="bg-green-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                                ৳<?php echo number_format($recentOrder['total_amount'], 0); ?>
                            </span>
                        </div>
                        
                        <!-- Show first 3 items from the order -->
                        <?php if (!empty($recentOrder['items'])): ?>
                            <div class="mb-3 space-y-1">
                                <?php foreach (array_slice($recentOrder['items'], 0, 3) as $item): ?>
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-purple-100 truncate"><?php echo htmlspecialchars($item['name']); ?></span>
                                        <span class="text-white font-semibold"><?php echo $item['quantity']; ?>x</span>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (count($recentOrder['items']) > 3): ?>
                                    <div class="text-xs text-purple-200 text-center">
                                        +<?php echo count($recentOrder['items']) - 3; ?> more items
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <button onclick="reorder(<?php echo $recentOrder['id']; ?>)" class="w-full bg-white text-purple-600 py-2 rounded-lg font-semibold hover:bg-purple-50 transition-colors duration-200">
                            <i class="fas fa-redo mr-2"></i>One-Tap Reorder
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-6 p-4 bg-white bg-opacity-10 rounded-xl border border-white border-opacity-20">
                <div class="flex items-start">
                    <i class="fas fa-lightbulb text-yellow-300 mr-3 mt-1"></i>
                    <div class="flex-1">
                        <p class="font-semibold text-sm">Smart AI Insights</p>
                        <p class="text-xs text-purple-100 mt-1">Based on your order history, our AI has prepared your personalized shopping list with your most frequently ordered items.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Smart Shopping List Section -->
        <?php if (!empty($smartList)): ?>
            <div class="bg-white rounded-2xl shadow-xl p-8 mb-8 animate-slide-up">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-brain mr-3 text-purple-600"></i>
                        Your Smart Shopping List
                    </h2>
                    <button onclick="addSmartListToCart()" class="bg-purple-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-purple-700 transition-colors duration-200">
                        <i class="fas fa-magic mr-2"></i>Add All to Cart
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <?php foreach ($smartList as $item): ?>
                        <div class="bg-gray-50 rounded-xl p-4 hover:bg-gray-100 transition-colors duration-200 border border-gray-200">
                            <div class="flex items-center space-x-3 mb-3">
                                <?php if ($item['image']): ?>
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-12 h-12 rounded-lg object-cover">
                                <?php else: ?>
                                    <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-box text-gray-400"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 text-sm truncate"><?php echo htmlspecialchars($item['name']); ?></h4>
                                    <p class="text-xs text-gray-600"><?php echo htmlspecialchars($item['unit']); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-lg font-bold text-green-600">৳<?php echo number_format($item['price'], 2); ?></span>
                                <div class="text-xs text-gray-500">
                                    <div>Ordered <?php echo $item['order_count']; ?> times</div>
                                    <div>Avg: <?php echo round($item['avg_quantity'], 1); ?>x</div>
                                </div>
                            </div>
                            <button onclick="addSmartItemToCart(<?php echo $item['product_id']; ?>, <?php echo round($item['avg_quantity']); ?>)" class="w-full bg-purple-600 text-white py-2 rounded-lg font-semibold hover:bg-purple-700 transition-colors duration-200 text-sm">
                                <i class="fas fa-plus mr-1"></i>Add to Cart
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-6 p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-500 mr-3 mt-1"></i>
                        <div>
                            <p class="text-sm font-semibold text-blue-800">AI-Powered Recommendations</p>
                            <p class="text-xs text-blue-700 mt-1">These items are suggested based on your ordering patterns and frequency. Click "Add All to Cart" to quickly reorder your favorites!</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <div class="bg-white rounded-2xl shadow-xl p-12 text-center animate-slide-up">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-shopping-bag text-gray-400 text-3xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No orders yet</h3>
            <p class="text-gray-600 mb-6">You haven't placed any orders yet. Start shopping to see your orders here!</p>
            <a href="/products" class="inline-block bg-green-600 text-white px-8 py-3 rounded-xl font-semibold hover:bg-green-700 transition-colors duration-200">
                <i class="fas fa-store mr-2"></i>Start Shopping
            </a>
        </div>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($orders as $order): ?>
                <div class="bg-white rounded-2xl shadow-xl p-6 hover:shadow-2xl transition-all duration-300 animate-slide-up">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between">
                        <div class="flex-1 mb-4 lg:mb-0">
                            <div class="flex items-center space-x-4 mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Order #<?php echo $order['id']; ?></h3>
                                    <p class="text-sm text-gray-600"><?php echo date('F j, Y \a\t g:i A', strtotime($order['created_at'])); ?></p>
                                </div>
                                <div class="flex items-center space-x-2">
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
                                    <span class="px-3 py-1 rounded-full text-sm font-semibold <?php echo $statusColor; ?> capitalize">
                                        <?php echo str_replace('_', ' ', $order['status']); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="font-medium text-gray-700">Total Amount:</span>
                                    <span class="text-gray-900">৳<?php echo number_format($order['total_amount'], 2); ?></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Payment:</span>
                                    <span class="text-gray-900 capitalize"><?php echo str_replace('_', ' ', $order['payment_method']); ?></span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Delivery:</span>
                                    <span class="text-gray-900"><?php echo htmlspecialchars($order['delivery_slot_display'] ?? ($order['delivery_slot'] ?? 'Not scheduled')); ?></span>
                                </div>
                            </div>

                            <?php if ($order['address_line1']): ?>
                                <div class="mt-3 text-sm">
                                    <span class="font-medium text-gray-700">Delivery Address:</span>
                                    <span class="text-gray-900"><?php echo htmlspecialchars($order['address_line1']); ?>, <?php echo htmlspecialchars($order['city']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3">
                            <a href="/orders/<?php echo $order['id']; ?>" class="bg-blue-600 text-white px-4 py-2 rounded-xl font-semibold hover:bg-blue-700 transition-colors duration-200 text-center">
                                <i class="fas fa-eye mr-2"></i>View Details
                            </a>
                            <a href="/orders/track/<?php echo $order['id']; ?>" class="bg-green-600 text-white px-4 py-2 rounded-xl font-semibold hover:bg-green-700 transition-colors duration-200 text-center">
                                <i class="fas fa-truck mr-2"></i>Track Order
                            </a>
                            <?php if (in_array($order['status'], ['placed', 'confirmed'])): ?>
                                <button onclick="cancelOrder(<?php echo $order['id']; ?>)" class="bg-red-600 text-white px-4 py-2 rounded-xl font-semibold hover:bg-red-700 transition-colors duration-200">
                                    <i class="fas fa-times mr-2"></i>Cancel
                                </button>
                            <?php endif; ?>
                            <button onclick="reorder(<?php echo $order['id']; ?>)" class="bg-purple-600 text-white px-4 py-2 rounded-xl font-semibold hover:bg-purple-700 transition-colors duration-200">
                                <i class="fas fa-redo mr-2"></i>Reorder
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="flex justify-center mt-12">
                <nav class="flex items-center space-x-2">
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=<?php echo $currentPage - 1; ?>" class="px-4 py-2 rounded-xl bg-white border border-gray-300 text-gray-500 hover:bg-gray-50 transition-colors duration-200">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>

                    <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                        <a href="?page=<?php echo $i; ?>" class="px-4 py-2 rounded-xl <?php echo $i == $currentPage ? 'bg-green-600 text-white' : 'bg-white border border-gray-300 text-gray-500 hover:bg-gray-50'; ?> transition-colors duration-200">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?php echo $currentPage + 1; ?>" class="px-4 py-2 rounded-xl bg-white border border-gray-300 text-gray-500 hover:bg-gray-50 transition-colors duration-200">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        <?php endif; ?>
    <?php endif; ?>
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
    console.log('🛒 Starting reorder for order ID:', orderId);
    
    // Show loading state
    showToast('Adding items to cart...', 'info');
    
    // Disable the reorder button to prevent multiple clicks
    const reorderButtons = document.querySelectorAll(`button[onclick*="reorder(${orderId})"]`);
    reorderButtons.forEach(button => {
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Adding...';
    });
    
    fetch('/orders/reorder', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'order_id=' + orderId
    })
    .then(response => {
        console.log('🛒 Reorder response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('🛒 Reorder response data:', data);
        if (data.success) {
            showToast(data.message || 'Items added to cart successfully! 🎉', 'success');
            // Update cart count
            updateCartCount();
            
            // Show notification with option to go to cart
            setTimeout(() => {
                if (confirm(data.message + '\n\nWould you like to view your cart and checkout now?')) {
                    window.location.href = '/cart';
                }
            }, 1500);
        } else {
            showToast(data.message || 'Failed to add items to cart', 'error');
        }
    })
    .catch(error => {
        console.error('🛒 Reorder error:', error);
        showToast('An error occurred while reordering: ' + error.message, 'error');
    })
    .finally(() => {
        // Re-enable the reorder buttons
        reorderButtons.forEach(button => {
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-redo mr-2"></i>One-Tap Reorder';
        });
    });
}

function reorderAll() {
    if (!confirm('This will add ALL items from your recent orders to the cart. Continue?')) {
        return;
    }
    
    console.log('🛒 Starting reorder all items');
    
    // Show loading state
    showToast('Adding all items to cart...', 'info');
    
    // Disable the reorder all button
    const reorderAllButton = document.querySelector('button[onclick="reorderAll()"]');
    if (reorderAllButton) {
        reorderAllButton.disabled = true;
        reorderAllButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Adding All Items...';
    }
    
    fetch('/orders/reorder-all', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        }
    })
    .then(response => {
        console.log('🛒 Reorder all response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('🛒 Reorder all response data:', data);
        if (data.success) {
            showToast(data.message || `Successfully added ${data.itemCount || 0} items to cart! 🎉`, 'success');
            updateCartCount();
            
            setTimeout(() => {
                if (confirm('Items added to cart! Would you like to view your cart and checkout now?')) {
                    window.location.href = '/cart';
                }
            }, 1500);
        } else {
            showToast(data.message || 'Failed to add items to cart', 'error');
        }
    })
    .catch(error => {
        console.error('🛒 Reorder all error:', error);
        showToast('An error occurred: ' + error.message, 'error');
    })
    .finally(() => {
        // Re-enable the reorder all button
        if (reorderAllButton) {
            reorderAllButton.disabled = false;
            reorderAllButton.innerHTML = '<i class="fas fa-magic mr-2"></i>1-Click Add All to Cart';
        }
    });
}

function addSmartItemToCart(productId, quantity) {
    console.log('🛒 Adding smart item to cart:', productId, 'quantity:', quantity);
    
    showToast('Adding item to cart...', 'info');
    
    fetch('/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Item added to cart successfully!', 'success');
            updateCartCount();
        } else {
            showToast(data.message || 'Failed to add item to cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error adding smart item to cart:', error);
        showToast('An error occurred while adding item to cart', 'error');
    });
}

function addSmartListToCart() {
    if (!confirm('This will add ALL items from your smart shopping list to the cart. Continue?')) {
        return;
    }
    
    console.log('🛒 Adding all smart list items to cart');
    
    showToast('Adding all smart items to cart...', 'info');
    
    // Get all smart list items
    const smartItems = document.querySelectorAll('[onclick*="addSmartItemToCart"]');
    let completed = 0;
    let total = smartItems.length;
    
    if (total === 0) {
        showToast('No smart items found', 'warning');
        return;
    }
    
    smartItems.forEach(button => {
        const onclick = button.getAttribute('onclick');
        const matches = onclick.match(/addSmartItemToCart\((\d+),\s*(\d+)\)/);
        if (matches) {
            const productId = matches[1];
            const quantity = matches[2];
            
            fetch('/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                completed++;
                if (completed === total) {
                    showToast(`Successfully added ${completed} items to cart! 🎉`, 'success');
                    updateCartCount();
                    
                    setTimeout(() => {
                        if (confirm('Items added to cart! Would you like to view your cart and checkout now?')) {
                            window.location.href = '/cart';
                        }
                    }, 1500);
                }
            })
            .catch(error => {
                console.error('Error adding smart item:', error);
                completed++;
                if (completed === total) {
                    showToast('Some items may not have been added. Please check your cart.', 'warning');
                }
            });
        }
    });
}

function updateCartCount() {
    fetch('/cart/count')
        .then(response => response.json())
        .then(data => {
            const cartBadge = document.querySelector('.cart-badge');
            if (cartBadge) {
                if (data.count > 0) {
                    cartBadge.textContent = data.count;
                    cartBadge.classList.remove('hidden');
                } else {
                    cartBadge.classList.add('hidden');
                }
            }
        })
        .catch(error => console.error('Error updating cart count:', error));
}
</script>

<?php
$content = ob_get_clean();
include 'app/views/layouts/main.php';
?>