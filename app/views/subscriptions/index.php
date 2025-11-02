<?php
$title = 'My Subscriptions - GroceryApp';
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
                    <span class="text-sm font-medium text-gray-500">My Subscriptions</span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">My Subscriptions</h1>
            <p class="text-gray-600">Manage your recurring grocery orders</p>
        </div>
        <a href="/subscriptions/create" class="bg-green-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-green-700 transition-colors duration-200">
            <i class="fas fa-plus mr-2"></i>Create Subscription
        </a>
    </div>

    <?php if (empty($subscriptions)): ?>
        <div class="bg-white rounded-2xl shadow-xl p-12 text-center animate-slide-up">
            <div class="w-24 h-24 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-calendar-alt text-blue-600 text-4xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No subscriptions yet</h3>
            <p class="text-gray-600 mb-6">Set up a recurring order to get your groceries delivered automatically!</p>
            <a href="/subscriptions/create" class="inline-block bg-green-600 text-white px-8 py-3 rounded-xl font-semibold hover:bg-green-700 transition-colors duration-200">
                <i class="fas fa-plus mr-2"></i>Create Your First Subscription
            </a>
        </div>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($subscriptions as $subscription): ?>
                <?php
                $statusColors = [
                    'active' => 'bg-green-100 text-green-800',
                    'paused' => 'bg-yellow-100 text-yellow-800',
                    'cancelled' => 'bg-red-100 text-red-800'
                ];
                $statusColor = $statusColors[$subscription['status']] ?? 'bg-gray-100 text-gray-800';
                
                $frequencyLabels = [
                    'weekly' => 'Every Week',
                    'bi_weekly' => 'Every 2 Weeks',
                    'monthly' => 'Every Month'
                ];
                $frequencyLabel = $frequencyLabels[$subscription['frequency']] ?? $subscription['frequency'];
                ?>
                <div class="bg-white rounded-2xl shadow-xl p-6 hover:shadow-2xl transition-all duration-300 animate-slide-up border-l-4 <?php echo $subscription['status'] === 'active' ? 'border-green-500' : ($subscription['status'] === 'paused' ? 'border-yellow-500' : 'border-gray-300'); ?>">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-4">
                                <span class="px-3 py-1 rounded-full text-sm font-semibold <?php echo $statusColor; ?> capitalize">
                                    <?php echo str_replace('_', ' ', $subscription['status']); ?>
                                </span>
                                <span class="text-blue-600 font-semibold">
                                    <i class="fas fa-sync-alt mr-2"></i><?php echo $frequencyLabel; ?>
                                </span>
                                <span class="text-gray-600">
                                    <i class="fas fa-<?php echo $subscription['payment_method'] === 'pre_paid' ? 'credit-card' : 'money-bill-wave'; ?> mr-2"></i>
                                    <?php echo str_replace('_', ' ', ucwords($subscription['payment_method'])); ?>
                                </span>
                            </div>

                            <!-- Products in Subscription -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                                <?php foreach ($subscription['products'] as $product): ?>
                                    <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                        <?php if ($product['image']): ?>
                                            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-12 h-12 rounded-lg object-cover">
                                        <?php else: ?>
                                            <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-box text-gray-400"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="flex-1">
                                            <p class="font-medium text-gray-900 text-sm"><?php echo htmlspecialchars($product['name']); ?></p>
                                            <p class="text-sm text-gray-600">৳<?php echo number_format($product['price'], 2); ?> / <?php echo htmlspecialchars($product['unit']); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Subscription Details -->
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">Next Delivery:</span>
                                    <p class="font-semibold text-gray-900">
                                        <?php echo date('M j, Y', strtotime($subscription['next_delivery_date'])); ?>
                                    </p>
                                </div>
                                <div>
                                    <span class="text-gray-600">Started:</span>
                                    <p class="font-semibold text-gray-900">
                                        <?php echo date('M j, Y', strtotime($subscription['start_date'])); ?>
                                    </p>
                                </div>
                                <?php if ($subscription['address_line1']): ?>
                                <div>
                                    <span class="text-gray-600">Delivery Address:</span>
                                    <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($subscription['address_line1']); ?></p>
                                </div>
                                <?php endif; ?>
                                <?php if ($subscription['delivery_slot_preference']): ?>
                                <div>
                                    <span class="text-gray-600">Delivery Time:</span>
                                    <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($subscription['delivery_slot_preference']); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col gap-2 ml-4">
                            <?php if ($subscription['status'] === 'active'): ?>
                                <button onclick="pauseSubscription(<?php echo $subscription['id']; ?>)" class="bg-yellow-500 text-white px-4 py-2 rounded-xl font-semibold hover:bg-yellow-600 transition-colors duration-200 text-sm">
                                    <i class="fas fa-pause mr-2"></i>Pause
                                </button>
                                <button onclick="cancelSubscription(<?php echo $subscription['id']; ?>)" class="bg-red-500 text-white px-4 py-2 rounded-xl font-semibold hover:bg-red-600 transition-colors duration-200 text-sm">
                                    <i class="fas fa-times mr-2"></i>Cancel
                                </button>
                            <?php elseif ($subscription['status'] === 'paused'): ?>
                                <button onclick="resumeSubscription(<?php echo $subscription['id']; ?>)" class="bg-green-500 text-white px-4 py-2 rounded-xl font-semibold hover:bg-green-600 transition-colors duration-200 text-sm">
                                    <i class="fas fa-play mr-2"></i>Resume
                                </button>
                                <button onclick="cancelSubscription(<?php echo $subscription['id']; ?>)" class="bg-red-500 text-white px-4 py-2 rounded-xl font-semibold hover:bg-red-600 transition-colors duration-200 text-sm">
                                    <i class="fas fa-times mr-2"></i>Cancel
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function pauseSubscription(id) {
    if (confirm('Are you sure you want to pause this subscription?')) {
        fetch(`/subscriptions/pause/${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Subscription paused successfully', 'success');
                location.reload();
            } else {
                showToast(data.message || 'Failed to pause subscription', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
        });
    }
}

function resumeSubscription(id) {
    fetch(`/subscriptions/resume/${id}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Subscription resumed successfully', 'success');
            location.reload();
        } else {
            showToast(data.message || 'Failed to resume subscription', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    });
}

function cancelSubscription(id) {
    if (confirm('Are you sure you want to cancel this subscription? This action cannot be undone.')) {
        fetch(`/subscriptions/cancel/${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Subscription cancelled successfully', 'success');
                location.reload();
            } else {
                showToast(data.message || 'Failed to cancel subscription', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
        });
    }
}
</script>

<?php
$content = ob_get_clean();
include 'app/views/layouts/main.php';
?>
