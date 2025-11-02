<?php
$pageTitle = 'Sales Analytics';
$currentPage = 'analytics';

// Get admin data
$adminMiddleware = new AdminMiddleware();
$adminData = $adminMiddleware->getAdminData();
$adminFullName = $adminMiddleware->getAdminFullName();
$adminInitials = $adminMiddleware->getAdminInitials();

// Extract analytics data
$revenue = $analyticsData['revenue'] ?? [];
$orders = $analyticsData['orders'] ?? [];
$customers = $analyticsData['customers'] ?? [];
$categories = $analyticsData['categories'] ?? [];
$products = $analyticsData['products'] ?? [];
$trends = $analyticsData['trends'] ?? [];
$period = $analyticsData['period'] ?? 7;

ob_start();
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Sales Analytics</h1>
        <p class="text-gray-600 dark:text-gray-400">Comprehensive sales and performance insights</p>
    </div>
    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 mt-4 sm:mt-0">
        <!-- Date Range Filter -->
        <div class="flex space-x-2">
            <a href="?period=1" class="px-3 py-1 text-sm rounded-lg transition-colors <?php echo $period == 1 ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'; ?>">
                Today
            </a>
            <a href="?period=7" class="px-3 py-1 text-sm rounded-lg transition-colors <?php echo $period == 7 ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'; ?>">
                7 Days
            </a>
            <a href="?period=30" class="px-3 py-1 text-sm rounded-lg transition-colors <?php echo $period == 30 ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'; ?>">
                30 Days
            </a>
        </div>
        <div class="flex space-x-2">
            <button onclick="exportData()" class="px-4 py-2 text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">
                <i class="fas fa-download mr-2"></i>Export
            </button>
            <button onclick="refreshData()" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-refresh mr-2"></i>Refresh
            </button>
        </div>
    </div>
</div>

<!-- Revenue Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <!-- Revenue Today -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center">
            <div class="w-8 h-8 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-dollar-sign text-green-600 dark:text-green-400"></i>
            </div>
            <div class="ml-4 flex-1">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Revenue Today</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                    ৳<?php echo number_format($revenue['today'] ?? 0, 2); ?>
                </p>
                <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                    <i class="fas fa-arrow-up mr-1"></i>
                    All orders (except cancelled)
                </p>
            </div>
        </div>
    </div>

    <!-- Revenue This Week -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center">
            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-chart-line text-blue-600 dark:text-blue-400"></i>
            </div>
            <div class="ml-4 flex-1">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Revenue This Week</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                    ৳<?php echo number_format($revenue['week'] ?? 0, 2); ?>
                </p>
                <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                    <i class="fas fa-arrow-up mr-1"></i>
                    <?php echo $revenue['growth_percentage'] ?? 0; ?>% vs last week
                </p>
            </div>
        </div>
    </div>

    <!-- Revenue This Month -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center">
            <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-calendar-alt text-purple-600 dark:text-purple-400"></i>
            </div>
            <div class="ml-4 flex-1">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Revenue This Month</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                    ৳<?php echo number_format($revenue['month'] ?? 0, 2); ?>
                </p>
                <p class="text-xs text-purple-600 dark:text-purple-400 mt-1">
                    <i class="fas fa-chart-bar mr-1"></i>
                    Current month
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Orders Count -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Orders Today -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center">
            <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-shopping-bag text-orange-600 dark:text-orange-400"></i>
            </div>
            <div class="ml-4 flex-1">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Orders Today</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                    <?php echo $orders['today'] ?? 0; ?>
                </p>
                <p class="text-xs text-orange-600 dark:text-orange-400 mt-1">
                    <i class="fas fa-clock mr-1"></i>
                    All statuses
                </p>
            </div>
        </div>
    </div>

    <!-- Orders Pending -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center">
            <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-clock text-yellow-600 dark:text-yellow-400"></i>
            </div>
            <div class="ml-4 flex-1">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Orders Pending</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                    <?php echo $orders['pending'] ?? 0; ?>
                </p>
                <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Needs attention
                </p>
            </div>
        </div>
    </div>

    <!-- Orders Delivered -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center">
            <div class="w-8 h-8 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600 dark:text-green-400"></i>
            </div>
            <div class="ml-4 flex-1">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Orders Delivered</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                    <?php echo $orders['delivered'] ?? 0; ?>
                </p>
                <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                    <i class="fas fa-check mr-1"></i>
                    Completed
                </p>
            </div>
        </div>
    </div>

    <!-- Total Orders -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center">
            <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-list text-indigo-600 dark:text-indigo-400"></i>
            </div>
            <div class="ml-4 flex-1">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Orders</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                    <?php echo $orders['total'] ?? 0; ?>
                </p>
                <p class="text-xs text-indigo-600 dark:text-indigo-400 mt-1">
                    <i class="fas fa-chart-pie mr-1"></i>
                    Last <?php echo $period; ?> days
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Charts and Insights Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Revenue Trend Chart -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Revenue Trend</h3>
            <span class="text-sm text-gray-500 dark:text-gray-400">Last <?php echo $period; ?> days</span>
        </div>
        <div class="h-64">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- Customer Insights -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Customer Insights</h3>
            <span class="text-sm text-gray-500 dark:text-gray-400">Last <?php echo $period; ?> days</span>
        </div>
        <div class="h-64">
            <canvas id="customerChart"></canvas>
        </div>
    </div>
</div>

<!-- Top Categories and Products -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Top Categories -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Categories</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">By sales amount (Last <?php echo $period; ?> days)</p>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $index => $category): ?>
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-gradient-to-r from-blue-400 to-purple-500 rounded-lg flex items-center justify-center text-white font-bold text-sm mr-3">
                                    <?php echo $index + 1; ?>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        <?php echo $category['order_count']; ?> orders
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    ৳<?php echo number_format($category['total_sales'], 2); ?>
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    <?php echo $category['percentage']; ?>%
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                    <i class="fas fa-tags text-4xl mb-2"></i>
                    <p>No category data available</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Top Products -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Products</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Best selling products (Last <?php echo $period; ?> days)</p>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            <?php if (!empty($products)): ?>
                <?php foreach (array_slice($products, 0, 5) as $index => $product): ?>
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-gradient-to-r from-green-400 to-blue-500 rounded-lg flex items-center justify-center text-white font-bold text-sm mr-3">
                                    <?php echo $index + 1; ?>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        ৳<?php echo number_format($product['price'], 2); ?> each
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    <?php echo $product['total_sold']; ?> sold
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    ৳<?php echo number_format($product['total_revenue'], 2); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                    <i class="fas fa-box text-4xl mb-2"></i>
                    <p>No product data available</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Daily Revenue Table -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Daily Revenue Breakdown</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">Last <?php echo $period; ?> days revenue and orders</p>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Revenue</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Orders</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Avg Order Value</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <?php if (!empty($trends)): ?>
                    <?php foreach (array_slice($trends, -10) as $data): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                <?php echo date('M j, Y', strtotime($data['date'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                ৳<?php echo number_format($data['revenue'], 2); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                <?php echo $data['orders']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                ৳<?php echo $data['orders'] > 0 ? number_format($data['revenue'] / $data['orders'], 2) : '0.00'; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                            <i class="fas fa-chart-line text-4xl mb-2"></i>
                            <p>No revenue data available</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Revenue Trend Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueData = <?php echo json_encode($trends); ?>;
const revenueChart = new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: revenueData.map(item => {
            const date = new Date(item.date);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        }),
        datasets: [{
            label: 'Revenue (৳)',
            data: revenueData.map(item => item.revenue),
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '৳' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Customer Insights Chart
const customerCtx = document.getElementById('customerChart').getContext('2d');
const customerData = <?php echo json_encode($customers); ?>;
const customerChart = new Chart(customerCtx, {
    type: 'doughnut',
    data: {
        labels: ['New Customers', 'Returning Customers'],
        datasets: [{
            data: [customerData.new || 0, customerData.returning || 0],
            backgroundColor: [
                'rgb(34, 197, 94)',
                'rgb(59, 130, 246)'
            ],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    usePointStyle: true
                }
            }
        }
    }
});

// Export data function
function exportData() {
    // TODO: Implement data export functionality
    alert('Export functionality will be implemented soon!');
}

// Refresh data function
function refreshData() {
    window.location.reload();
}
</script>

<?php
$content = ob_get_clean();
include 'app/views/admin/layout.php';
?>