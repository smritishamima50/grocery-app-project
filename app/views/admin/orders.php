<?php
$currentPage = 'orders';
$pageTitle = 'Orders Management';
include 'app/views/admin/layout.php';
?>

<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Orders Management</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Control the full delivery pipeline and manage all orders</p>
                </div>
                <div class="flex items-center space-x-4">
                    <button type="button" onclick="refreshOrders(event)" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                        <i class="fas fa-sync-alt"></i>
                        <span>Refresh</span>
                    </button>
                    <button type="button" onclick="exportOrders(event)" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                        <i class="fas fa-download"></i>
                        <span>Export</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-shopping-cart text-blue-600 dark:text-blue-400"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Orders</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white"><?php echo $stats['total_orders'] ?? 0; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-yellow-600 dark:text-yellow-400"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white"><?php echo $stats['orders_pending'] ?? 0; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600 dark:text-green-400"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Delivered</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white"><?php echo $stats['orders_delivered'] ?? 0; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Urgent</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white"><?php echo $stats['urgent_orders'] ?? 0; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
            <div class="p-6">
                <form method="GET" action="/admin/orders" class="space-y-4" id="orders-filter-form">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <!-- Status Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                            <select name="status" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="all" <?php echo ($status === 'all') ? 'selected' : ''; ?>>All Orders</option>
                                <option value="pending" <?php echo ($status === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo ($status === 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="packed" <?php echo ($status === 'packed') ? 'selected' : ''; ?>>Packed</option>
                                <option value="out_for_delivery" <?php echo ($status === 'out_for_delivery') ? 'selected' : ''; ?>>Out for Delivery</option>
                                <option value="delivered" <?php echo ($status === 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo ($status === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>

                        <!-- Search -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                            <input type="text" name="search" placeholder="Order ID or Phone" value="<?php echo htmlspecialchars($search ?? ''); ?>" 
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>

                        <!-- Date From -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">From Date</label>
                            <input type="date" name="date_from" value="<?php echo htmlspecialchars($dateFrom ?? ''); ?>" 
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>

                        <!-- Date To -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">To Date</label>
                            <input type="date" name="date_to" value="<?php echo htmlspecialchars($dateTo ?? ''); ?>" 
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>

                        <!-- Driver Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Driver</label>
                            <select name="driver" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="">All Drivers</option>
                                <?php foreach ($drivers as $driverItem): ?>
                                    <option value="<?php echo htmlspecialchars($driverItem['name']); ?>" <?php echo ($driver === $driverItem['name']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($driverItem['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="clearFilters(event)" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                            Clear Filters
                        </button>
                        <button type="submit" id="apply-filters-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center space-x-2">
                            <i class="fas fa-filter"></i>
                            <span>Apply Filters</span>
                        </button>
                    </div>
                </form>
                
                <!-- Form will submit normally via GET, but we add handler for better UX -->
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const filterForm = document.querySelector('form[method="GET"]');
                    if (filterForm) {
                        // Allow form to submit naturally, but add visual feedback
                        filterForm.addEventListener('submit', function(e) {
                            const applyBtn = document.getElementById('apply-filters-btn');
                            if (applyBtn) {
                                applyBtn.disabled = true;
                                applyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Applying...</span>';
                            }
                            // Let form submit normally
                        });
                    }
                });
                </script>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Order</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Payment</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Delivery Slot</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Driver</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-shopping-cart text-4xl mb-4"></i>
                                    <p class="text-lg">No orders found</p>
                                    <p class="text-sm">Try adjusting your filters or check back later</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 <?php echo $order['is_urgent'] ? 'bg-red-50 dark:bg-red-900/20' : ''; ?>">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    #<?php echo $order['id']; ?>
                                                    <?php if ($order['is_urgent']): ?>
                                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                            <i class="fas fa-bolt mr-1"></i>
                                                            URGENT
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    <?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            <?php echo htmlspecialchars($order['phone']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            ৳<?php echo number_format($order['total_amount'], 2); ?>
                                        </div>
                                        <?php if ($order['discount_amount'] > 0): ?>
                                            <div class="text-sm text-green-600 dark:text-green-400">
                                                -৳<?php echo number_format($order['discount_amount'], 2); ?> discount
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                <?php 
                                                switch($order['payment_method']) {
                                                    case 'bkash': echo 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200'; break;
                                                    case 'nagad': echo 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200'; break;
                                                    case 'card': echo 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'; break;
                                                    case 'cod': echo 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'; break;
                                                    default: echo 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
                                                }
                                                ?>">
                                                <i class="fas fa-<?php echo $order['payment_method'] === 'cod' ? 'money-bill-wave' : 'credit-card'; ?> mr-1"></i>
                                                <?php echo strtoupper($order['payment_method']); ?>
                                            </span>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                <?php 
                                                $ps = $order['payment_status'];
                                                // Support legacy values mapping
                                                if ($ps === 'completed') { $ps = 'paid'; }
                                                if ($ps === 'pending') { $ps = 'pending'; }
                                                switch($ps) {
                                                    case 'unpaid': echo 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'; break;
                                                    case 'pending': echo 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'; break;
                                                    case 'paid': echo 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'; break;
                                                    case 'failed': echo 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'; break;
                                                    case 'refunded': echo 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'; break;
                                                    default: echo 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
                                                }
                                                ?>">
                                                <?php echo strtoupper($ps); ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            <?php if ($order['delivery_slot_date']): ?>
                                                <?php echo date('M j', strtotime($order['delivery_slot_date'])); ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            <?php if ($order['delivery_slot_start'] && $order['delivery_slot_end']): ?>
                                                <?php echo date('g:i A', strtotime($order['delivery_slot_start'])); ?> - <?php echo date('g:i A', strtotime($order['delivery_slot_end'])); ?>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($order['eco_friendly_delivery']): ?>
                                            <div class="mt-1">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                    <i class="fas fa-leaf mr-1"></i>
                                                    Eco-Friendly
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap" id="status-cell-<?php echo $order['id']; ?>">
                                        <select id="status-select-<?php echo $order['id']; ?>" 
                                                data-current-status="<?php echo htmlspecialchars($order['status']); ?>"
                                                onchange="updateOrderStatus(<?php echo $order['id']; ?>, this.value, this)" 
                                                class="text-sm border-0 bg-transparent font-medium
                                                <?php 
                                                switch($order['status']) {
                                                    case 'pending': echo 'text-yellow-600 dark:text-yellow-400'; break;
                                                    case 'confirmed': echo 'text-blue-600 dark:text-blue-400'; break;
                                                    case 'packed': echo 'text-purple-600 dark:text-purple-400'; break;
                                                    case 'out_for_delivery': echo 'text-orange-600 dark:text-orange-400'; break;
                                                    case 'delivered': echo 'text-green-600 dark:text-green-400'; break;
                                                    case 'cancelled': echo 'text-red-600 dark:text-red-400'; break;
                                                    default: echo 'text-gray-600 dark:text-gray-400';
                                                }
                                                ?>">
                                            <option value="pending" <?php echo ($order['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                            <option value="confirmed" <?php echo ($order['status'] === 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="packed" <?php echo ($order['status'] === 'packed') ? 'selected' : ''; ?>>Packed</option>
                                            <option value="out_for_delivery" <?php echo ($order['status'] === 'out_for_delivery') ? 'selected' : ''; ?>>Out for Delivery</option>
                                            <option value="delivered" <?php echo ($order['status'] === 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                                            <option value="cancelled" <?php echo ($order['status'] === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <select id="driver-select-<?php echo $order['id']; ?>"
                                                onchange="assignDriver(<?php echo $order['id']; ?>, this.value)" 
                                                class="text-sm border-0 bg-transparent text-gray-900 dark:text-white">
                                            <option value="">Unassigned</option>
                                            <?php foreach ($drivers as $driverItem): ?>
                                                <?php 
                                                // Normalize driver names for comparison (handle whitespace)
                                                $driverName = trim($driverItem['name']);
                                                $assignedDriver = trim($order['assigned_driver'] ?? '');
                                                $isSelected = ($assignedDriver === $driverName && $assignedDriver !== '');
                                                ?>
                                                <option value="<?php echo htmlspecialchars($driverName); ?>" 
                                                        <?php echo $isSelected ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($driverName); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <button onclick="viewOrderDetails(<?php echo $order['id']; ?>)" 
                                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($order['status'] === 'out_for_delivery'): ?>
                                                <button onclick="markAsDelivered(<?php echo $order['id']; ?>)" 
                                                        class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if (!in_array($order['status'], ['delivered', 'cancelled'])): ?>
                                                <button onclick="cancelOrder(<?php echo $order['id']; ?>)" 
                                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php 
            // Calculate order range for current page
            // Ensure all variables are properly typed (integers)
            $limit = 20; // Should match the limit in AdminController
            $currentPage = isset($currentPage) ? (int)$currentPage : 1;
            $totalPages = isset($totalPages) ? (int)$totalPages : 1;
            $total = isset($total) ? (int)$total : 0;
            
            $startOrder = (($currentPage - 1) * $limit) + 1;
            $endOrder = min($currentPage * $limit, $total);
            $totalOrders = $total;
            
            // Build query string for pagination links
            $queryParams = [];
            if ($status !== 'all') $queryParams['status'] = $status;
            if (!empty($search)) $queryParams['search'] = $search;
            if (!empty($dateFrom)) $queryParams['date_from'] = $dateFrom;
            if (!empty($dateTo)) $queryParams['date_to'] = $dateTo;
            if (!empty($driver)) $queryParams['driver'] = $driver;
            
            function buildPageUrl($pageNum, $queryParams) {
                $queryParams['page'] = $pageNum;
                return '?' . http_build_query($queryParams);
            }
            ?>
            
            <?php if ($totalPages > 1): ?>
                <div class="bg-white dark:bg-gray-800 px-4 py-3 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 sm:px-6">
                    <!-- Mobile Pagination -->
                    <div class="flex-1 flex justify-between sm:hidden">
                        <?php if ($currentPage > 1): ?>
                            <a href="<?php echo buildPageUrl($currentPage - 1, $queryParams); ?>" 
                               class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                <i class="fas fa-chevron-left mr-1"></i> Previous
                            </a>
                        <?php else: ?>
                            <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-800 cursor-not-allowed">
                                <i class="fas fa-chevron-left mr-1"></i> Previous
                            </span>
                        <?php endif; ?>
                        <?php if ($currentPage < $totalPages): ?>
                            <a href="<?php echo buildPageUrl($currentPage + 1, $queryParams); ?>" 
                               class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                Next <i class="fas fa-chevron-right ml-1"></i>
                            </a>
                        <?php else: ?>
                            <span class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-800 cursor-not-allowed">
                                Next <i class="fas fa-chevron-right ml-1"></i>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Desktop Pagination -->
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Showing <span class="font-medium"><?php echo $startOrder; ?>-<?php echo $endOrder; ?></span> of <span class="font-medium"><?php echo number_format($totalOrders); ?></span> orders
                                <?php if ($totalPages > 1): ?>
                                    (Page <span class="font-medium"><?php echo $currentPage; ?></span> of <span class="font-medium"><?php echo $totalPages; ?></span>)
                                <?php endif; ?>
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <!-- First Page -->
                                <?php if ($currentPage > 3 && $totalPages > 5): ?>
                                    <a href="<?php echo buildPageUrl(1, $queryParams); ?>" 
                                       class="relative inline-flex items-center px-3 py-2 rounded-l-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-500 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-600">
                                        <i class="fas fa-angle-double-left"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <!-- Previous Page -->
                                <?php if ($currentPage > 1): ?>
                                    <a href="<?php echo buildPageUrl($currentPage - 1, $queryParams); ?>" 
                                       class="relative inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-500 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-600">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <!-- Page Numbers -->
                                <?php
                                // Calculate page range to show
                                $startPage = max(1, $currentPage - 2);
                                $endPage = min($totalPages, $currentPage + 2);
                                
                                // If we're near the start, show more pages at the end
                                if ($currentPage <= 3) {
                                    $endPage = min($totalPages, 5);
                                }
                                // If we're near the end, show more pages at the start
                                if ($currentPage >= $totalPages - 2) {
                                    $startPage = max(1, $totalPages - 4);
                                }
                                
                                // Show first page if not in range
                                if ($startPage > 1): ?>
                                    <a href="<?php echo buildPageUrl(1, $queryParams); ?>" 
                                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-600">
                                        1
                                    </a>
                                    <?php if ($startPage > 2): ?>
                                        <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300">
                                            ...
                                        </span>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <!-- Current page range -->
                                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                    <a href="<?php echo buildPageUrl($i, $queryParams); ?>" 
                                       class="relative inline-flex items-center px-4 py-2 border text-sm font-medium <?php echo $i === $currentPage ? 'z-10 bg-blue-50 border-blue-500 text-blue-600 dark:bg-blue-900 dark:border-blue-400 dark:text-blue-200 font-bold' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600'; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <!-- Show last page if not in range -->
                                <?php if ($endPage < $totalPages): ?>
                                    <?php if ($endPage < $totalPages - 1): ?>
                                        <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300">
                                            ...
                                        </span>
                                    <?php endif; ?>
                                    <a href="<?php echo buildPageUrl($totalPages, $queryParams); ?>" 
                                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-600">
                                        <?php echo $totalPages; ?>
                                    </a>
                                <?php endif; ?>
                                
                                <!-- Next Page -->
                                <?php if ($currentPage < $totalPages): ?>
                                    <a href="<?php echo buildPageUrl($currentPage + 1, $queryParams); ?>" 
                                       class="relative inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-500 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-600">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <!-- Last Page -->
                                <?php if ($currentPage < $totalPages - 2 && $totalPages > 5): ?>
                                    <a href="<?php echo buildPageUrl($totalPages, $queryParams); ?>" 
                                       class="relative inline-flex items-center px-3 py-2 rounded-r-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-500 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-600">
                                        <i class="fas fa-angle-double-right"></i>
                                    </a>
                                <?php endif; ?>
                            </nav>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Show order count even if only one page -->
                <div class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
                    <p class="text-sm text-gray-700 dark:text-gray-300 text-center">
                        Showing all <span class="font-medium"><?php echo number_format($totalOrders); ?></span> orders
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div id="orderModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Order Details</h3>
                <button onclick="closeOrderModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="orderDetails" class="space-y-4">
                <!-- Order details will be loaded here -->
                <div id="orderPaymentAudit" class="hidden"></div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Order Modal -->
<div id="cancelModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Cancel Order</h3>
                <button onclick="closeCancelModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="space-y-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">Are you sure you want to cancel this order?</p>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reason for cancellation</label>
                    <textarea id="cancelReason" rows="3" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="Enter reason for cancellation..."></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button onclick="closeCancelModal()" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                        Cancel
                    </button>
                    <button onclick="confirmCancelOrder()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                        Cancel Order
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentOrderId = null;

// Update order status
async function updateOrderStatus(orderId, newStatus, selectElement) {
    console.log('🔄 ========== UPDATING ORDER STATUS ==========');
    console.log('🔄 Order ID:', orderId);
    console.log('🔄 New Status:', newStatus);
    console.log('🔄 Select Element:', selectElement);
    
    // Validate inputs
    if (!orderId || !newStatus || !selectElement) {
        console.error('❌ Missing required parameters:', { orderId, newStatus, selectElement });
        showNotification('Error: Missing required information', 'error');
        return;
    }
    
    // Store old value to revert if update fails
    const oldValue = selectElement.getAttribute('data-current-status') || selectElement.value;
    const oldStatus = oldValue;
    
    console.log('📝 Old Status:', oldStatus);
    console.log('📝 Current Select Value:', selectElement.value);
    console.log('📝 Data Attribute:', selectElement.getAttribute('data-current-status'));
    
    // Don't update if status hasn't changed
    if (oldStatus === newStatus) {
        console.log('⚠️ Status unchanged, skipping update');
        showNotification('Status is already set to this value', 'info');
        // Reset to old value
        selectElement.value = oldStatus;
        return;
    }
    
    // Validate status value
    const validStatuses = ['pending', 'confirmed', 'packed', 'out_for_delivery', 'delivered', 'cancelled'];
    if (!validStatuses.includes(newStatus)) {
        console.error('❌ Invalid status:', newStatus);
        showNotification('Invalid status selected', 'error');
        selectElement.value = oldStatus;
        return;
    }
    
    // Add loading state
    selectElement.disabled = true;
    selectElement.style.opacity = '0.6';
    selectElement.style.cursor = 'wait';
    
    // Show loading notification
    showNotification('Updating order status...', 'info');
    
    try {
        // Use absolute path from root
        const apiUrl = `/api/admin/orders/${orderId}`;
        const payload = { status: newStatus };
        
        console.log(`📤 Sending PATCH request to ${apiUrl}`);
        console.log(`📤 Full URL: ${window.location.origin}${apiUrl}`);
        console.log(`📤 Payload:`, payload);
        console.log(`📤 Order ID: ${orderId}, New Status: ${newStatus}`);
        
        const response = await fetch(apiUrl, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin', // Include cookies for session
            body: JSON.stringify({
                status: newStatus
            })
        });

        console.log(`📥 Response status: ${response.status} ${response.statusText}`);
        
        // Get response text first to check for errors
        const responseText = await response.text();
        console.log(`📥 Response text:`, responseText);
        
        if (!response.ok) {
            // Try to parse as JSON for error message
            let errorMessage = 'Failed to update order status';
            try {
                const errorData = JSON.parse(responseText);
                errorMessage = errorData.error || errorMessage;
            } catch (e) {
                // Not JSON, use default message
            }
            showNotification(errorMessage, 'error');
            // Re-enable select and revert to old value
            selectElement.disabled = false;
            selectElement.style.opacity = '1';
            selectElement.style.cursor = 'pointer';
            selectElement.value = oldStatus;
            return;
        }
        
        // Parse JSON response
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (e) {
            console.error('JSON parse error:', e);
            console.error('Response text that failed to parse:', responseText);
            console.error('Response text length:', responseText.length);
            console.error('Response text (first 500 chars):', responseText.substring(0, 500));
            
            // Try to extract any error message from the response
            let errorMessage = 'Invalid response from server';
            if (responseText.trim().startsWith('<')) {
                errorMessage = 'Server returned HTML instead of JSON. This usually means a PHP error occurred. Check server logs.';
            } else if (responseText.trim().startsWith('Warning') || responseText.trim().startsWith('Notice') || responseText.trim().startsWith('Fatal')) {
                errorMessage = 'PHP error detected: ' + responseText.substring(0, 200);
            } else if (responseText.length > 0) {
                errorMessage = 'Invalid JSON response. Response: ' + responseText.substring(0, 200);
            }
            
            showNotification(errorMessage, 'error');
            // Re-enable select and revert to old value
            selectElement.disabled = false;
            selectElement.style.opacity = '1';
            selectElement.style.cursor = 'pointer';
            selectElement.value = oldStatus;
            return;
        }
        
        if (result.success) {
            console.log('🎉 Status update successful!');
            console.log('📊 Full result:', result);
            
            // Get the updated status from response
            const updatedStatus = result.new_status || newStatus;
            console.log('📊 Updated status value:', updatedStatus);
            
            // Verify we got a valid status
            if (!updatedStatus || updatedStatus === 'undefined') {
                console.error('❌ Invalid status in response:', result);
                showNotification('Status update succeeded but invalid response received', 'error');
                selectElement.disabled = false;
                selectElement.style.opacity = '1';
                selectElement.style.cursor = 'pointer';
                return;
            }
            
            // Update the select's value and color - CRITICAL: Update data attribute FIRST
            selectElement.setAttribute('data-current-status', updatedStatus);
            selectElement.value = updatedStatus;
            updateStatusColor(selectElement, updatedStatus);
            
            console.log('✅ UI Updated - Status set to:', updatedStatus);
            console.log('✅ Data attribute updated to:', selectElement.getAttribute('data-current-status'));
            
            // Re-enable select
            selectElement.disabled = false;
            selectElement.style.opacity = '1';
            selectElement.style.cursor = 'pointer';
            
            // Update the row's visual state if needed (e.g., urgent badge visibility)
            updateOrderRowVisualState(orderId, updatedStatus);
            
            // Show success notification with the status change
            const statusDisplayNames = {
                'pending': 'Pending',
                'confirmed': 'Confirmed',
                'packed': 'Packed',
                'out_for_delivery': 'Out for Delivery',
                'delivered': 'Delivered',
                'cancelled': 'Cancelled'
            };
            // Show clear SUCCESS message - make it prominent
            const statusDisplayName = statusDisplayNames[updatedStatus] || updatedStatus;
            const successMessage = `✅ Successfully updated! Order status changed to "${statusDisplayName}"`;
            
            console.log('🎉 SUCCESS! Showing notification:', successMessage);
            showNotification(successMessage, 'success');
            
            // Log database confirmation (don't show duplicate notification)
            if (result.database_updated) {
                console.log('✅ Database update confirmed by backend');
            } else {
                console.warn('⚠️ Backend did not confirm database update');
            }
            if (result.verification_passed === false) {
                console.warn('⚠️ Update saved but verification had issues - check PHP error log');
            }
            
            console.log('✅ ========== STATUS UPDATE COMPLETE ==========');
            console.log('✅ UI updated successfully with status:', updatedStatus);
        } else {
            console.error('❌ Update failed:', result);
            const errorMsg = result.error || 'Failed to update order status';
            console.error('❌ Error message:', errorMsg);
            showNotification('❌ ' + errorMsg, 'error');
            // Re-enable select and revert to old value
            selectElement.disabled = false;
            selectElement.style.opacity = '1';
            selectElement.style.cursor = 'pointer';
            selectElement.value = oldStatus;
            console.error('❌ ========== STATUS UPDATE FAILED ==========');
        }
    } catch (error) {
        console.error('💥 ========== EXCEPTION IN updateOrderStatus ==========');
        console.error('💥 Error:', error);
        console.error('💥 Stack:', error.stack);
        showNotification('❌ Network error. Please check your connection and try again.', 'error');
        // Re-enable select and revert to old value
        selectElement.disabled = false;
        selectElement.style.opacity = '1';
        selectElement.style.cursor = 'pointer';
        selectElement.value = oldStatus;
    }
}

// Helper function to update status color
function updateStatusColor(selectElement, status) {
    // Remove existing color classes
    selectElement.classList.remove(
        'text-yellow-600', 'dark:text-yellow-400',
        'text-blue-600', 'dark:text-blue-400',
        'text-purple-600', 'dark:text-purple-400',
        'text-orange-600', 'dark:text-orange-400',
        'text-green-600', 'dark:text-green-400',
        'text-red-600', 'dark:text-red-400',
        'text-gray-600', 'dark:text-gray-400'
    );
    
    // Add new color class based on status
    switch(status) {
        case 'pending':
            selectElement.classList.add('text-yellow-600', 'dark:text-yellow-400');
            break;
        case 'confirmed':
            selectElement.classList.add('text-blue-600', 'dark:text-blue-400');
            break;
        case 'packed':
            selectElement.classList.add('text-purple-600', 'dark:text-purple-400');
            break;
        case 'out_for_delivery':
            selectElement.classList.add('text-orange-600', 'dark:text-orange-400');
            break;
        case 'delivered':
            selectElement.classList.add('text-green-600', 'dark:text-green-400');
            break;
        case 'cancelled':
            selectElement.classList.add('text-red-600', 'dark:text-red-400');
            break;
        default:
            selectElement.classList.add('text-gray-600', 'dark:text-gray-400');
    }
}

// Helper function to update order row visual state
function updateOrderRowVisualState(orderId, newStatus) {
    // Find the row for this order
    const statusCell = document.getElementById(`status-cell-${orderId}`);
    if (!statusCell) return;
    
    const row = statusCell.closest('tr');
    if (!row) return;
    
    // Update row background based on status if needed
    // Remove any existing status-based background classes
    row.classList.remove('bg-red-50', 'dark:bg-red-900/20');
    
    // Add urgent background back if status changed to something that might affect urgency
    // This is just visual feedback - actual urgent flag is in database
    if (newStatus === 'cancelled') {
        row.style.opacity = '0.7';
    } else {
        row.style.opacity = '1';
    }
}

// Assign driver
async function assignDriver(orderId, driverName) {
    console.log('🚗 ========== ASSIGNING DRIVER ==========');
    console.log('🚗 Order ID:', orderId);
    console.log('🚗 Driver Name:', driverName || 'NULL (unassigning)');
    
    // Validate inputs
    if (!orderId) {
        console.error('❌ Missing order ID');
        showNotification('Error: Missing order information', 'error');
        return;
    }
    
    // Find the select element for this order
    const driverSelect = document.getElementById(`driver-select-${orderId}`);
    
    if (!driverSelect) {
        console.error('❌ Driver select element not found for order:', orderId);
        console.error('❌ Looking for ID: driver-select-' + orderId);
        showNotification('Error: Failed to find driver selection element', 'error');
        return;
    }
    
    // Store old value to revert if update fails
    const oldValue = driverSelect.value || '';
    const normalizedOldValue = oldValue.trim();
    const normalizedDriverName = (driverName || '').trim();
    
    console.log('📝 Old Driver Value:', normalizedOldValue || 'NULL');
    console.log('📝 New Driver Value:', normalizedDriverName || 'NULL');
    
    // Don't update if driver hasn't changed (normalize both values for comparison)
    // Handle both "assigned" and "unassigned" cases
    if (normalizedOldValue === normalizedDriverName) {
        console.log('⚠️ Driver unchanged, skipping update');
        const currentDriver = normalizedOldValue || 'Unassigned';
        const message = normalizedOldValue 
            ? `Driver "${currentDriver}" is already assigned to this order` 
            : 'This order is already unassigned';
        showNotification(message, 'info');
        return;
    }
    
    // Add loading state
    driverSelect.disabled = true;
    driverSelect.style.opacity = '0.6';
    driverSelect.style.cursor = 'wait';
    
    // Show loading notification
    showNotification(driverName ? `Assigning driver "${driverName}"...` : 'Unassigning driver...', 'info');
    
    try {
        // Use absolute path from root to avoid path issues
        const apiUrl = `/api/admin/orders/${orderId}`;
        
        // Normalize driverName: empty string becomes null for unassigning
        const driverValue = (driverName && driverName.trim() !== '') ? driverName.trim() : null;
        
        console.log(`📤 Sending PATCH request to ${apiUrl}`);
        console.log(`📤 Full URL: ${window.location.origin}${apiUrl}`);
        console.log(`📤 Order ID: ${orderId}, Driver Name: ${driverName || 'null'}`);
        console.log(`📤 Driver value to send:`, driverValue, `(original: "${driverName}")`);
        console.log(`📤 Payload:`, { assigned_driver: driverValue });
        
        const response = await fetch(apiUrl, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin', // Include cookies for session
            body: JSON.stringify({
                assigned_driver: driverValue
            })
        });

        console.log(`📥 Response status: ${response.status} ${response.statusText}`);
        
        // Get response text first to check for errors
        const responseText = await response.text();
        console.log(`📥 Response text:`, responseText);
        
        if (!response.ok) {
            // Try to parse as JSON for error message
            let errorMessage = 'Failed to assign driver';
            try {
                const errorData = JSON.parse(responseText);
                errorMessage = errorData.error || errorMessage;
            } catch (e) {
                // Not JSON, use default message
            }
            showNotification(errorMessage, 'error');
            // Re-enable select and revert to old value
            driverSelect.disabled = false;
            driverSelect.style.opacity = '1';
            driverSelect.style.cursor = 'pointer';
            driverSelect.value = oldValue;
            return;
        }
        
        // Parse JSON response
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (e) {
            console.error('JSON parse error:', e);
            showNotification('Invalid response from server', 'error');
            // Re-enable select and revert to old value
            driverSelect.disabled = false;
            driverSelect.style.opacity = '1';
            driverSelect.style.cursor = 'pointer';
            driverSelect.value = oldValue;
            return;
        }
        
        if (result.success) {
            console.log('🎉 Driver assignment successful!');
            console.log('📊 Full result:', result);
            console.log('📊 result.assigned_driver:', result.assigned_driver, '(type:', typeof result.assigned_driver, ')');
            
            // Update the select value with the response value (handles null properly)
            // IMPORTANT: Use the actual value from database response, not the sent value
            let newDriverValue = '';
            
            // Check if assigned_driver is in the response
            if ('assigned_driver' in result) {
                if (result.assigned_driver !== null && result.assigned_driver !== undefined && result.assigned_driver !== '') {
                    newDriverValue = String(result.assigned_driver).trim();
                }
                console.log('📊 New driver value from response:', newDriverValue || 'EMPTY');
                console.log('📊 Raw assigned_driver from result:', result.assigned_driver);
            } else {
                console.warn('⚠️ WARNING: assigned_driver not found in response! Using sent value as fallback.');
                // Fallback: use the value we sent
                newDriverValue = driverValue ? String(driverValue).trim() : '';
                console.log('📊 Using fallback value:', newDriverValue || 'EMPTY');
            }
            
            // Update select element - try to find matching option first
            // CRITICAL: Compare with trimmed values and handle null/empty properly
            let optionFound = false;
            const normalizedNewValue = (newDriverValue || '').trim();
            
            console.log('🔍 Looking for option with value:', normalizedNewValue || 'EMPTY');
            console.log('🔍 Available options:', Array.from(driverSelect.options).map(o => o.value));
            
            // First, try exact match
            for (let option of driverSelect.options) {
                const optionValue = (option.value || '').trim();
                if (optionValue === normalizedNewValue) {
                    driverSelect.value = option.value; // Use original value (may have whitespace)
                    optionFound = true;
                    console.log('✅ Found exact matching option:', option.value);
                    break;
                }
            }
            
            // If not found and it's empty, set to empty (Unassigned)
            if (!optionFound && normalizedNewValue === '') {
                // Find the "Unassigned" option (value="")
                for (let option of driverSelect.options) {
                    if (option.value === '') {
                        driverSelect.value = '';
                        optionFound = true;
                        console.log('✅ Set to empty (Unassigned) - found Unassigned option');
                        break;
                    }
                }
                
                // If still not found, set to empty anyway
                if (!optionFound) {
                    driverSelect.value = '';
                    optionFound = true;
                    console.log('✅ Set to empty (Unassigned) - using empty value');
                }
            }
            
            // If still not found, try case-insensitive match and trim comparison
            if (!optionFound && normalizedNewValue !== '') {
                for (let option of driverSelect.options) {
                    const optionValue = (option.value || '').trim();
                    if (optionValue.toLowerCase() === normalizedNewValue.toLowerCase()) {
                        driverSelect.value = option.value;
                        optionFound = true;
                        console.log('✅ Found case-insensitive match:', option.value);
                        break;
                    }
                }
            }
            
            // If still not found, add the option dynamically
            if (!optionFound && normalizedNewValue !== '') {
                console.warn('⚠️ Driver value not found in options, adding dynamically:', normalizedNewValue);
                const newOption = document.createElement('option');
                newOption.value = normalizedNewValue;
                newOption.textContent = normalizedNewValue;
                driverSelect.appendChild(newOption);
                driverSelect.value = normalizedNewValue;
                optionFound = true;
            }
            
            // Force a change event to ensure UI updates
            driverSelect.dispatchEvent(new Event('change', { bubbles: true }));
            
            // Re-enable select
            driverSelect.disabled = false;
            driverSelect.style.opacity = '1';
            driverSelect.style.cursor = 'pointer';
            
            // Verify the value was actually set
            console.log('🔍 Verified select value after update:', driverSelect.value);
            console.log('🔍 Selected option text:', driverSelect.options[driverSelect.selectedIndex]?.textContent || 'NONE');
            
            // Show clear SUCCESS message - make it prominent
            const successMessage = newDriverValue && newDriverValue.trim() !== ''
                ? `✅ Driver assigned successfully! Driver "${newDriverValue}" has been assigned to this order.` 
                : `✅ Driver unassigned successfully! The driver has been removed from this order.`;
            
            console.log('🎉 SUCCESS! Showing notification:', successMessage);
            showNotification(successMessage, 'success');
            
            // Additional verification for unassigning
            if (!newDriverValue || newDriverValue.trim() === '') {
                console.log('✅ UNASSIGNED: Verified dropdown shows "Unassigned"');
                console.log('✅ UNASSIGNED: Select value is:', driverSelect.value || 'EMPTY (correct for unassigned)');
            }
            
            // Log database confirmation (don't show duplicate notification)
            if (result.database_updated) {
                console.log('✅ Database update confirmed by backend');
            } else {
                console.warn('⚠️ Backend did not confirm database update');
            }
            if (result.verification_passed === false) {
                console.warn('⚠️ Update saved but verification had issues - check PHP error log');
                showNotification('⚠️ Driver assigned but verification had issues. Please refresh to confirm.', 'warning');
            }
            
            console.log('✅ ========== DRIVER ASSIGNMENT COMPLETE ==========');
            console.log('✅ UI updated successfully with driver:', newDriverValue || 'null');
        } else {
            console.error('❌ Driver assignment failed:', result);
            const errorMsg = result.error || 'Failed to assign driver';
            console.error('❌ Error message:', errorMsg);
            showNotification('❌ ' + errorMsg, 'error');
            // Re-enable select and revert to old value
            driverSelect.disabled = false;
            driverSelect.style.opacity = '1';
            driverSelect.style.cursor = 'pointer';
            driverSelect.value = oldValue;
            console.error('❌ ========== DRIVER ASSIGNMENT FAILED ==========');
        }
    } catch (error) {
        console.error('💥 ========== EXCEPTION IN assignDriver ==========');
        console.error('💥 Error:', error);
        console.error('💥 Stack:', error.stack);
        showNotification('❌ Network error. Please check your connection and try again.', 'error');
        // Re-enable select and revert to old value
        driverSelect.disabled = false;
        driverSelect.style.opacity = '1';
        driverSelect.style.cursor = 'pointer';
        driverSelect.value = oldValue;
    }
}

// View order details
async function viewOrderDetails(orderId) {
    try {
        const response = await fetch(`/api/admin/orders/${orderId}`);
        const result = await response.json();
        
        if (result.success) {
            displayOrderDetails(result.order);
            document.getElementById('orderModal').classList.remove('hidden');
        } else {
            showNotification(result.error || 'Failed to load order details', 'error');
        }
    } catch (error) {
        console.error('Error loading order details:', error);
        showNotification('Failed to load order details', 'error');
    }
}

// Display order details in modal
function displayOrderDetails(order) {
    const orderDetails = document.getElementById('orderDetails');
    
    orderDetails.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Order Info -->
            <div>
                <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Order Information</h4>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Order ID:</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">#${order.id}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Status:</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white capitalize">${order.status.replace('_', ' ')}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Total Amount:</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">৳${parseFloat(order.total_amount).toFixed(2)}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Payment Method:</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white uppercase">${order.payment_method}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Created:</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">${new Date(order.created_at).toLocaleString()}</span>
                    </div>
                </div>
            </div>

            <!-- Customer Info -->
            <div>
                <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Customer Information</h4>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Name:</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">${order.first_name} ${order.last_name}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Phone:</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">${order.phone}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Email:</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">${order.email}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delivery Address -->
        <div>
            <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Delivery Address</h4>
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <p class="text-sm text-gray-900 dark:text-white">${order.address_line1}</p>
                ${order.address_line2 ? `<p class="text-sm text-gray-900 dark:text-white">${order.address_line2}</p>` : ''}
                <p class="text-sm text-gray-900 dark:text-white">${order.city}, ${order.state} ${order.zip_code}</p>
            </div>
        </div>

        <!-- Order Items -->
        <div>
            <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Order Items</h4>
            <div class="space-y-2">
                ${order.items.map(item => `
                    <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <img src="${item.product_image || '/images/placeholder.jpg'}" alt="${item.product_name}" class="w-12 h-12 object-cover rounded">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">${item.product_name}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Qty: ${item.quantity}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">৳${parseFloat(item.unit_price).toFixed(2)}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Total: ৳${parseFloat(item.total_price).toFixed(2)}</p>
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>

        <!-- Status History -->
        <div>
            <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Status History</h4>
            <div class="space-y-2">
                ${order.status_history.map(history => `
                    <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">${history.old_status} → ${history.new_status}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">${history.admin_name}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-500 dark:text-gray-400">${new Date(history.created_at).toLocaleString()}</p>
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            ${order.status === 'out_for_delivery' ? `
                <button onclick="markAsDelivered(${order.id})" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                    Mark as Delivered
                </button>
            ` : ''}
            ${!['delivered', 'cancelled'].includes(order.status) ? `
                <button onclick="cancelOrder(${order.id})" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                    Cancel Order
                </button>
            ` : ''}
        </div>
    `;
}

// Mark as delivered
async function markAsDelivered(orderId) {
    if (confirm('Are you sure you want to mark this order as delivered?')) {
        try {
            const response = await fetch(`/api/admin/orders/${orderId}/delivered`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            const result = await response.json();
            
            if (result.success) {
                showNotification('Order marked as delivered', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showNotification(result.error || 'Failed to mark order as delivered', 'error');
            }
        } catch (error) {
            console.error('Error marking order as delivered:', error);
            showNotification('Failed to mark order as delivered', 'error');
        }
    }
}

// Cancel order
function cancelOrder(orderId) {
    currentOrderId = orderId;
    document.getElementById('cancelModal').classList.remove('hidden');
}

// Confirm cancel order
async function confirmCancelOrder() {
    if (!currentOrderId) return;
    
    const reason = document.getElementById('cancelReason').value;
    
    try {
        const response = await fetch(`/api/admin/orders/${currentOrderId}/cancel`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                reason: reason || 'Order cancelled by admin'
            })
        });

        const result = await response.json();
        
        if (result.success) {
            showNotification('Order cancelled successfully', 'success');
            closeCancelModal();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(result.error || 'Failed to cancel order', 'error');
        }
    } catch (error) {
        console.error('Error cancelling order:', error);
        showNotification('Failed to cancel order', 'error');
    }
}

// Close modals
function closeOrderModal() {
    document.getElementById('orderModal').classList.add('hidden');
}

function closeCancelModal() {
    document.getElementById('cancelModal').classList.add('hidden');
    currentOrderId = null;
    document.getElementById('cancelReason').value = '';
}

// Clear filters
function clearFilters(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    window.location.href = '/admin/orders';
}

// Apply filters - Helper function (form submits naturally via GET)
function applyFilters() {
    const form = document.querySelector('form[method="GET"]');
    if (!form) {
        console.warn('Filter form not found');
        return;
    }
    
    // Trigger form submit
    const applyBtn = document.getElementById('apply-filters-btn');
    if (applyBtn) {
        applyBtn.disabled = true;
        applyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Applying...</span>';
    }
    
    // Form will submit naturally via GET method
    form.submit();
}

// Refresh orders with visual feedback and preserve filters
function refreshOrders(event) {
    console.log('🔄 ========== REFRESHING ORDERS ==========');
    console.log('🔄 Event:', event);
    
    // Prevent default if event exists
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    // Show loading notification
    showNotification('Refreshing orders list...', 'info');
    
    // Find and disable refresh button
    let refreshBtn = null;
    if (event && event.target) {
        refreshBtn = event.target.closest('button');
    }
    if (!refreshBtn) {
        refreshBtn = document.querySelector('button[onclick*="refreshOrders"]');
    }
    
    if (refreshBtn) {
        const originalHTML = refreshBtn.innerHTML;
        refreshBtn.disabled = true;
        refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Refreshing...</span>';
        
        console.log('✅ Refresh button disabled and showing spinner');
        
        // Preserve current URL parameters (filters) when refreshing
        const currentUrl = window.location.href;
        console.log('🔄 Reloading page with current filters:', currentUrl);
        
        // Reload page after a short delay to show the notification
        setTimeout(() => {
            window.location.href = currentUrl;
        }, 500);
    } else {
        console.warn('⚠️ Refresh button not found, using fallback reload');
        // Preserve URL parameters
        window.location.href = window.location.href;
    }
}

// Export orders to CSV
async function exportOrders(event) {
    console.log('📥 ========== EXPORTING ORDERS ==========');
    console.log('📥 Event:', event);
    
    // Prevent default if event exists
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    try {
        // Show loading notification
        showNotification('Preparing export...', 'info');
        
        // Get current filter parameters from URL
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status') || 'all';
        const search = urlParams.get('search') || '';
        const dateFrom = urlParams.get('date_from') || '';
        const dateTo = urlParams.get('date_to') || '';
        const driver = urlParams.get('driver') || '';
        
        // Build export URL with all filters
        let exportUrl = '/api/admin/orders/export?';
        if (status && status !== 'all') {
            exportUrl += `status=${encodeURIComponent(status)}&`;
        }
        if (search) {
            exportUrl += `search=${encodeURIComponent(search)}&`;
        }
        if (dateFrom) {
            exportUrl += `date_from=${encodeURIComponent(dateFrom)}&`;
        }
        if (dateTo) {
            exportUrl += `date_to=${encodeURIComponent(dateTo)}&`;
        }
        if (driver) {
            exportUrl += `driver=${encodeURIComponent(driver)}&`;
        }
        
        // Remove trailing &
        if (exportUrl.endsWith('&')) {
            exportUrl = exportUrl.slice(0, -1);
        }
        
        console.log('📥 Export URL:', exportUrl);
        console.log('📥 Filters:', { status, search, dateFrom, dateTo, driver });
        
        // Find and disable export button
        let exportBtn = null;
        if (event && event.target) {
            exportBtn = event.target.closest('button');
        }
        if (!exportBtn) {
            exportBtn = document.querySelector('button[onclick*="exportOrders"]');
        }
        
        if (exportBtn) {
            const originalHTML = exportBtn.innerHTML;
            exportBtn.disabled = true;
            exportBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Exporting...</span>';
            
            try {
                // Fetch the CSV data
                const response = await fetch(exportUrl, {
                    method: 'GET',
                    credentials: 'same-origin'
                });
                
                if (!response.ok) {
                    throw new Error(`Export failed: ${response.status} ${response.statusText}`);
                }
                
                // Get the CSV content
                const csvContent = await response.text();
                
                // Create blob and download
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);
                
                // Generate filename with timestamp
                const now = new Date();
                const timestamp = now.toISOString().slice(0, 19).replace(/:/g, '-');
                const filename = `orders_export_${timestamp}.csv`;
                
                link.setAttribute('href', url);
                link.setAttribute('download', filename);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                // Restore button
                exportBtn.disabled = false;
                exportBtn.innerHTML = originalHTML;
                
                // Show success notification
                showNotification(`✅ Orders exported successfully! File: ${filename}`, 'success');
                console.log('✅ Export completed:', filename);
                
            } catch (error) {
                console.error('❌ Export error:', error);
                showNotification('❌ Export failed: ' + error.message, 'error');
                
                // Restore button
                if (exportBtn) {
                    exportBtn.disabled = false;
                    exportBtn.innerHTML = originalHTML;
                }
            }
        } else {
            // Fallback: try direct download
            window.location.href = exportUrl;
        }
        
    } catch (error) {
        console.error('💥 Export exception:', error);
        showNotification('❌ Export failed. Please try again.', 'error');
    }
}


// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Show notification
function showNotification(message, type = 'info') {
    console.log('📢 Showing notification:', type, message);
    
    // Remove any existing notifications first
    const existing = document.querySelectorAll('.app-notification');
    existing.forEach(n => n.remove());
    
    const notification = document.createElement('div');
    notification.className = 'app-notification fixed top-4 right-4 p-4 rounded-lg shadow-xl z-[9999] min-w-[300px] max-w-[500px] animate-slide-in-right';
    
    // Enhanced styling based on type
    if (type === 'success') {
        notification.className += ' bg-green-500 text-white border-2 border-green-600';
        notification.innerHTML = `
            <div class="flex items-center space-x-3">
                <i class="fas fa-check-circle text-2xl"></i>
                <div class="flex-1">
                    <p class="font-semibold text-lg">Success!</p>
                    <p class="text-sm">${message}</p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
    } else if (type === 'error') {
        notification.className += ' bg-red-500 text-white border-2 border-red-600';
        notification.innerHTML = `
            <div class="flex items-center space-x-3">
                <i class="fas fa-exclamation-circle text-2xl"></i>
                <div class="flex-1">
                    <p class="font-semibold text-lg">Error!</p>
                    <p class="text-sm">${message}</p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
    } else if (type === 'warning') {
        notification.className += ' bg-yellow-500 text-white border-2 border-yellow-600';
        notification.innerHTML = `
            <div class="flex items-center space-x-3">
                <i class="fas fa-exclamation-triangle text-2xl"></i>
                <div class="flex-1">
                    <p class="font-semibold">Warning</p>
                    <p class="text-sm">${message}</p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
    } else {
        notification.className += ' bg-blue-500 text-white border-2 border-blue-600';
        notification.innerHTML = `
            <div class="flex items-center space-x-3">
                <i class="fas fa-info-circle text-2xl"></i>
                <div class="flex-1">
                    <p class="text-sm">${message}</p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
    }
    
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds (longer for success messages)
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.opacity = '0';
            notification.style.transition = 'opacity 0.3s';
            setTimeout(() => notification.remove(), 300);
        }
    }, type === 'success' ? 5000 : 4000);
}

// Initialize driver management form
document.addEventListener('DOMContentLoaded', function() {
    const addDriverForm = document.getElementById('add-driver-form');
    if (addDriverForm) {
        addDriverForm.addEventListener('submit', addDriver);
    }
    
    // Close driver modal when clicking outside
    const driverModal = document.getElementById('driverModal');
    if (driverModal) {
        driverModal.addEventListener('click', function(e) {
            if (e.target === driverModal) {
                closeDriverModal();
            }
        });
    }
});

// Add CSS animation for slide-in effect
if (!document.getElementById('notification-styles')) {
    const style = document.createElement('style');
    style.id = 'notification-styles';
    style.textContent = `
        @keyframes slide-in-right {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        .animate-slide-in-right {
            animation: slide-in-right 0.3s ease-out;
        }
    `;
    document.head.appendChild(style);
}
</script>
