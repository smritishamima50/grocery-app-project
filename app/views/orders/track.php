<?php
$title = 'Track Order #' . $order['id'] . ' - GroceryApp';
ob_start();
?>

<div class="max-w-4xl mx-auto px-4 py-8 animate-fade-in">
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
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <a href="/orders/<?php echo $order['id']; ?>" class="text-sm font-medium text-gray-700 hover:text-green-600 transition-colors duration-200">Order #<?php echo $order['id']; ?></a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <span class="text-sm font-medium text-gray-500">Track Order</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Order Tracking Header -->
    <div class="bg-white rounded-2xl shadow-xl p-6 mb-8 animate-slide-up">
        <div class="text-center">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-truck text-blue-600 text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Track Order #<?php echo $order['id']; ?></h1>
            <p class="text-gray-600">Real-time updates on your order status</p>
        </div>
    </div>

    <!-- Order Status Timeline -->
    <div class="bg-white rounded-2xl shadow-xl p-8 animate-slide-up" style="animation-delay: 0.2s;">
        <h2 class="text-xl font-bold text-gray-900 mb-8 text-center">Order Progress</h2>

        <?php
        $statuses = ['placed', 'confirmed', 'packed', 'shipped', 'delivered'];
        $currentStatusIndex = array_search($order['status'], $statuses);

        // Create status updates map
        $statusUpdates = [];
        foreach ($deliveryUpdates as $update) {
            $statusUpdates[$update['status']] = $update;
        }
        ?>

        <div class="relative">
            <!-- Timeline line -->
            <div class="absolute left-8 top-0 bottom-0 w-0.5 bg-gray-300"></div>

            <?php foreach ($statuses as $index => $status): ?>
                <div class="relative flex items-center mb-8">
                    <!-- Status circle -->
                    <div class="flex-shrink-0 w-16 h-16 rounded-full flex items-center justify-center <?php echo $index <= $currentStatusIndex ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-500'; ?> relative z-10">
                        <?php if ($index < $currentStatusIndex): ?>
                            <i class="fas fa-check"></i>
                        <?php elseif ($index == $currentStatusIndex): ?>
                            <i class="fas fa-clock animate-pulse"></i>
                        <?php else: ?>
                            <i class="fas fa-circle"></i>
                        <?php endif; ?>
                    </div>

                    <!-- Status content -->
                    <div class="ml-6 flex-1">
                        <div class="bg-gray-50 rounded-xl p-4 <?php echo $index <= $currentStatusIndex ? 'border-l-4 border-green-500' : ''; ?>">
                            <h3 class="text-lg font-semibold text-gray-900 capitalize">
                                <?php echo str_replace('_', ' ', $status); ?>
                                <?php if ($index == $currentStatusIndex): ?>
                                    <span class="text-sm text-green-600 font-normal">(Current)</span>
                                <?php endif; ?>
                            </h3>

                            <?php if (isset($statusUpdates[$status])): ?>
                                <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($statusUpdates[$status]['message'] ?? 'Status updated'); ?></p>
                                <p class="text-sm text-gray-500 mt-2">
                                    <i class="fas fa-calendar-alt mr-1"></i>
                                    <?php echo date('F j, Y \a\t g:i A', strtotime($statusUpdates[$status]['updated_at'])); ?>
                                </p>
                            <?php else: ?>
                                <p class="text-gray-500 mt-1">
                                    <?php if ($index < $currentStatusIndex): ?>
                                        Completed
                                    <?php elseif ($index == $currentStatusIndex): ?>
                                        In progress
                                    <?php else: ?>
                                        Pending
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Order Details Summary -->
    <div class="bg-white rounded-2xl shadow-xl p-6 animate-slide-up" style="animation-delay: 0.4s;">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Order Summary</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="font-semibold text-gray-900 mb-3">Order Information</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Order ID:</span>
                        <span class="text-gray-900">#<?php echo $order['id']; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Order Date:</span>
                        <span class="text-gray-900"><?php echo date('F j, Y', strtotime($order['created_at'])); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Amount:</span>
                        <span class="text-gray-900 font-semibold">৳<?php echo number_format($order['total_amount'] ?? 0, 2); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Payment Method:</span>
                        <span class="text-gray-900 capitalize"><?php echo str_replace('_', ' ', $order['payment_method'] ?? 'Not specified'); ?></span>
                    </div>
                    <?php if (isset($order['packaging_option']) && $order['packaging_option'] !== 'standard'): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Packaging:</span>
                        <span class="text-gray-900 capitalize">
                            <?php 
                            $packagingLabels = [
                                'eco_friendly' => 'Eco-friendly',
                                'reusable_bag' => 'Reusable bag'
                            ];
                            echo $packagingLabels[$order['packaging_option']] ?? ucfirst($order['packaging_option']);
                            ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($surpriseGifts)): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Surprise Gift:</span>
                        <span class="text-green-600 font-semibold">
                            <i class="fas fa-gift mr-1"></i>
                            <?php echo count($surpriseGifts); ?> free item(s)
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <h3 class="font-semibold text-gray-900 mb-3">Delivery Information</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Delivery Slot:</span>
                        <span class="text-gray-900"><?php echo htmlspecialchars($order['delivery_slot_display'] ?? ($order['delivery_slot'] ?? 'Not scheduled')); ?></span>
                    </div>
                    <div>
                        <span class="text-gray-600">Estimated Delivery:</span>
                        <div class="text-gray-900 mt-1">
                            <?php
                            $deliveryDate = date('F j, Y', strtotime($order['created_at'] . ' +2 days'));
                            echo $deliveryDate;
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-col sm:flex-row gap-4 mt-8 animate-fade-in" style="animation-delay: 0.6s;">
        <a href="/orders/<?php echo $order['id']; ?>" class="flex-1 bg-blue-600 text-white py-3 rounded-xl font-semibold hover:bg-blue-700 transition-colors duration-200 text-center">
            <i class="fas fa-eye mr-2"></i>View Order Details
        </a>
        <a href="/orders" class="flex-1 bg-gray-600 text-white py-3 rounded-xl font-semibold hover:bg-gray-700 transition-colors duration-200 text-center">
            <i class="fas fa-list mr-2"></i>Back to Orders
        </a>
        <a href="/contact" class="flex-1 bg-green-600 text-white py-3 rounded-xl font-semibold hover:bg-green-700 transition-colors duration-200 text-center">
            <i class="fas fa-headset mr-2"></i>Need Help?
        </a>
    </div>
</div>

<script>
// Auto refresh tracking every 30 seconds
let refreshInterval;

function startAutoRefresh() {
    refreshInterval = setInterval(() => {
        // You could implement real-time updates here
        console.log('Checking for order updates...');
    }, 30000);
}

function stopAutoRefresh() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
}

// Start auto refresh when page loads
document.addEventListener('DOMContentLoaded', function() {
    startAutoRefresh();
});

// Stop auto refresh when page unloads
window.addEventListener('beforeunload', function() {
    stopAutoRefresh();
});
</script>

<?php
$content = ob_get_clean();
include 'app/views/layouts/main.php';
?>