<?php
// Admin Dashboard with KPI Cards and Analytics
$pageTitle = 'Dashboard';
$currentPage = 'dashboard';

// Get admin data
$adminMiddleware = new AdminMiddleware();
$adminData = $adminMiddleware->getAdminData();
$adminFullName = $adminMiddleware->getAdminFullName();
$adminInitials = $adminMiddleware->getAdminInitials();

// Calculate eco-friendly percentage (placeholder - TODO: implement real calculation)
$ecoFriendlyPercentage = 73; // This should be calculated from actual data

ob_start();
?>

<!-- KPI Cards Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
    <!-- Revenue Today -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-green-600 dark:text-green-400"></i>
                </div>
            </div>
            <div class="ml-4 flex-1">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Revenue Today</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                    ৳<?php echo number_format($stats['revenue_today'] ?? 0, 2); ?>
                </p>
                <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                    <i class="fas fa-arrow-up mr-1"></i>
                    +12.5% from yesterday
                </p>
            </div>
        </div>
    </div>

    <!-- Orders Today -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shopping-bag text-blue-600 dark:text-blue-400"></i>
                </div>
            </div>
            <div class="ml-4 flex-1">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Orders Today</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                    <?php echo $stats['orders_today'] ?? 0; ?>
                </p>
                <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                    <i class="fas fa-arrow-up mr-1"></i>
                    +8.2% from yesterday
                </p>
            </div>
        </div>
    </div>

    <!-- Low Stock Items -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-yellow-600 dark:text-yellow-400"></i>
                </div>
            </div>
            <div class="ml-4 flex-1">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Low Stock Items</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                    <?php echo $stats['low_stock_items'] ?? 0; ?>
                </p>
                <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">
                    <i class="fas fa-arrow-down mr-1"></i>
                    -3 items this week
                </p>
            </div>
        </div>
    </div>

    <!-- Active Users Today -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-purple-600 dark:text-purple-400"></i>
                </div>
            </div>
            <div class="ml-4 flex-1">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Users Today</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                    <?php echo $stats['active_users_today'] ?? 0; ?>
                </p>
                <p class="text-xs text-purple-600 dark:text-purple-400 mt-1">
                    <i class="fas fa-arrow-up mr-1"></i>
                    +15.3% from yesterday
                </p>
            </div>
        </div>
    </div>

    <!-- Eco-Friendly Deliveries -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-leaf text-green-600 dark:text-green-400"></i>
                </div>
            </div>
            <div class="ml-4 flex-1">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Eco-Friendly %</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                    <?php echo $ecoFriendlyPercentage; ?>%
                </p>
                <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                    <i class="fas fa-arrow-up mr-1"></i>
                    +5.2% this week
                </p>
            </div>
        </div>
    </div>

    <!-- Top Category This Week -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-trophy text-indigo-600 dark:text-indigo-400"></i>
                </div>
            </div>
            <div class="ml-4 flex-1">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Top Category</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                    <?php echo $stats['top_category'] ?? 'Fruits'; ?>
                </p>
                <p class="text-xs text-indigo-600 dark:text-indigo-400 mt-1">
                    <i class="fas fa-chart-line mr-1"></i>
                    Best performer
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Charts and Analytics Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Revenue Chart -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Revenue Overview</h3>
            <div class="flex space-x-2">
                <button class="px-3 py-1 text-xs bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded-full">7D</button>
                <button class="px-3 py-1 text-xs text-gray-500 dark:text-gray-400 rounded-full">30D</button>
                <button class="px-3 py-1 text-xs text-gray-500 dark:text-gray-400 rounded-full">90D</button>
            </div>
        </div>
        <div class="h-64 flex items-center justify-center text-gray-500 dark:text-gray-400">
            <!-- TODO: Implement real chart with Chart.js or similar -->
            <div class="text-center">
                <i class="fas fa-chart-line text-4xl mb-2"></i>
                <p>Revenue chart placeholder</p>
                <p class="text-sm">Connect to analytics service</p>
            </div>
        </div>
    </div>

    <!-- Order Status Distribution -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Order Status</h3>
            <span class="text-sm text-gray-500 dark:text-gray-400">This week</span>
        </div>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                    <span class="text-sm text-gray-700 dark:text-gray-300">Placed</span>
                </div>
                <span class="text-sm font-medium text-gray-900 dark:text-white"><?php echo $stats['orders_placed'] ?? 0; ?></span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-yellow-500 rounded-full mr-3"></div>
                    <span class="text-sm text-gray-700 dark:text-gray-300">Confirmed</span>
                </div>
                <span class="text-sm font-medium text-gray-900 dark:text-white"><?php echo $stats['orders_confirmed'] ?? 0; ?></span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-purple-500 rounded-full mr-3"></div>
                    <span class="text-sm text-gray-700 dark:text-gray-300">Packed</span>
                </div>
                <span class="text-sm font-medium text-gray-900 dark:text-white"><?php echo $stats['orders_packed'] ?? 0; ?></span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-indigo-500 rounded-full mr-3"></div>
                    <span class="text-sm text-gray-700 dark:text-gray-300">Shipped</span>
                </div>
                <span class="text-sm font-medium text-gray-900 dark:text-white"><?php echo $stats['orders_shipped'] ?? 0; ?></span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                    <span class="text-sm text-gray-700 dark:text-gray-300">Delivered</span>
                </div>
                <span class="text-sm font-medium text-gray-900 dark:text-white"><?php echo $stats['orders_delivered'] ?? 0; ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders and Quick Actions -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Recent Orders -->
    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Orders</h3>
                <a href="/admin/orders" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300">
                    View all
                </a>
            </div>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            <?php if (!empty($recentOrders)): ?>
                <?php foreach (array_slice($recentOrders, 0, 5) as $order): ?>
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                        <i class="fas fa-shopping-bag text-gray-600 dark:text-gray-400 text-sm"></i>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        Order #<?php echo $order['id']; ?>
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    ৳<?php echo number_format($order['total_amount'], 2); ?>
                                </p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    <?php
                                    switch($order['status']) {
                                        case 'placed': echo 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300'; break;
                                        case 'confirmed': echo 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300'; break;
                                        case 'packed': echo 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-300'; break;
                                        case 'shipped': echo 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-300'; break;
                                        case 'delivered': echo 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300'; break;
                                        case 'cancelled': echo 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300'; break;
                                        default: echo 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-300';
                                    }
                                    ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                    <i class="fas fa-shopping-bag text-4xl mb-2"></i>
                    <p>No recent orders</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
        <div class="space-y-3">
            <a href="/admin/products/create" class="flex items-center p-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                <i class="fas fa-plus mr-3 text-blue-500"></i>
                Add New Product
            </a>
            <a href="/admin/categories/create" class="flex items-center p-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                <i class="fas fa-tag mr-3 text-green-500"></i>
                Create Category
            </a>
            <a href="/admin/coupons/create" class="flex items-center p-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                <i class="fas fa-ticket-alt mr-3 text-purple-500"></i>
                Create Coupon
            </a>
            <a href="/admin/users" class="flex items-center p-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                <i class="fas fa-users mr-3 text-indigo-500"></i>
                Manage Users
            </a>
            <a href="/admin/analytics" class="flex items-center p-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                <i class="fas fa-chart-line mr-3 text-orange-500"></i>
                View Analytics
            </a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'app/views/admin/layout.php';
?>