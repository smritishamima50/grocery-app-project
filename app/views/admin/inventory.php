<?php
$pageTitle = 'Inventory Management';
$currentPage = 'inventory';

// Get admin data
$adminMiddleware = new AdminMiddleware();
$adminData = $adminMiddleware->getAdminData();
$adminFullName = $adminMiddleware->getAdminFullName();
$adminInitials = $adminMiddleware->getAdminInitials();

ob_start();
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Inventory Management</h1>
        <p class="text-gray-600 dark:text-gray-400">Monitor and manage product stock levels for perishable grocery items</p>
    </div>
    <div class="mt-4 sm:mt-0 flex space-x-2">
        <a href="/admin/products/create" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>Add Product
        </a>
        <button onclick="exportInventory()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
            <i class="fas fa-download mr-2"></i>Export
        </button>
        <button onclick="refreshInventory()" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
            <i class="fas fa-sync-alt mr-2"></i>Refresh
        </button>
    </div>
</div>

<!-- Inventory Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex items-center">
            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-boxes text-blue-600 dark:text-blue-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Products</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white"><?php echo $stats['total_products'] ?? 0; ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex items-center">
            <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-yellow-600 dark:text-yellow-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Low Stock</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white"><?php echo $stats['low_stock'] ?? 0; ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex items-center">
            <div class="w-8 h-8 bg-red-100 dark:bg-red-900/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-times-circle text-red-600 dark:text-red-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Out of Stock</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white"><?php echo $stats['out_of_stock'] ?? 0; ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex items-center">
            <div class="w-8 h-8 bg-cyan-100 dark:bg-cyan-900/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-snowflake text-cyan-600 dark:text-cyan-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Frozen</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white"><?php echo $stats['frozen'] ?? 0; ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex items-center">
            <div class="w-8 h-8 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-leaf text-green-600 dark:text-green-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Eco-Friendly</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white"><?php echo $stats['eco_friendly'] ?? 0; ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex items-center">
            <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                <i class="fas fa-pause-circle text-gray-600 dark:text-gray-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Inactive</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white"><?php echo $stats['inactive'] ?? 0; ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Search -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
    <form method="GET" action="/admin/inventory" class="space-y-4">
        <div class="flex flex-col lg:flex-row gap-4">
            <!-- Filter Buttons -->
            <div class="flex flex-wrap gap-2">
                <a href="?filter=all" 
                   class="px-4 py-2 text-sm rounded-lg transition-colors <?php echo ($filter ?? 'all') === 'all' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'; ?>">
                    All Products
                </a>
                <a href="?filter=low_stock" 
                   class="px-4 py-2 text-sm rounded-lg transition-colors <?php echo ($filter ?? '') === 'low_stock' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'; ?>">
                    Low Stock
                </a>
                <a href="?filter=out_of_stock" 
                   class="px-4 py-2 text-sm rounded-lg transition-colors <?php echo ($filter ?? '') === 'out_of_stock' ? 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'; ?>">
                    Out of Stock
                </a>
                <a href="?filter=frozen" 
                   class="px-4 py-2 text-sm rounded-lg transition-colors <?php echo ($filter ?? '') === 'frozen' ? 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/20 dark:text-cyan-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'; ?>">
                    Frozen
                </a>
                <a href="?filter=eco_friendly" 
                   class="px-4 py-2 text-sm rounded-lg transition-colors <?php echo ($filter ?? '') === 'eco_friendly' ? 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'; ?>">
                    Eco-Friendly
                </a>
                <a href="?filter=active" 
                   class="px-4 py-2 text-sm rounded-lg transition-colors <?php echo ($filter ?? '') === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'; ?>">
                    Active
                </a>
                <a href="?filter=inactive" 
                   class="px-4 py-2 text-sm rounded-lg transition-colors <?php echo ($filter ?? '') === 'inactive' ? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'; ?>">
                    Inactive
                </a>
            </div>
            
            <!-- Search and Category Filters -->
            <div class="flex-1 flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" name="search" placeholder="Search products..." 
                           value="<?php echo htmlspecialchars($search ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                </div>
                <div class="sm:w-48">
                    <select name="category" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($category ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Inventory Table -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Stock Count</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Low Stock Threshold</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Restock ETA</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Badges</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700" data-product-id="<?php echo $product['id']; ?>">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center overflow-hidden mr-4">
                                        <?php if ($product['image']): ?>
                                            <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                 class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <i class="fas fa-image text-gray-400"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            <?php echo htmlspecialchars($product['name']); ?>
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            ID: #<?php echo $product['id']; ?> | ৳<?php echo number_format($product['price'], 2); ?>/<?php echo htmlspecialchars($product['unit'] ?? 'unit'); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <input type="number" 
                                           value="<?php echo $product['stock_quantity']; ?>" 
                                           min="0"
                                           class="w-20 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                           onchange="updateInventory(<?php echo $product['id']; ?>, 'stock_count', this.value)">
                                    <div class="text-xs text-gray-500 dark:text-gray-400 ml-2">
                                        <?php echo htmlspecialchars($product['unit'] ?? 'units'); ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <input type="number" 
                                           value="<?php echo $product['low_stock_threshold']; ?>" 
                                           min="0"
                                           class="w-20 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                           onchange="updateInventory(<?php echo $product['id']; ?>, 'low_stock_threshold', this.value)">
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $stock = $product['stock_quantity'];
                                $threshold = $product['low_stock_threshold'];
                                if ($stock == 0) {
                                    $statusClass = 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300';
                                    $statusText = 'OUT OF STOCK';
                                } elseif ($stock <= $threshold) {
                                    $statusClass = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300';
                                    $statusText = 'LOW STOCK';
                                } else {
                                    $statusClass = 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300';
                                    $statusText = 'IN STOCK';
                                }
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusClass; ?>">
                                    <?php echo $statusText; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <input type="datetime-local" 
                                           value="<?php echo $product['restock_eta'] ? date('Y-m-d\TH:i', strtotime($product['restock_eta'])) : ''; ?>" 
                                           class="px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                           onchange="updateInventory(<?php echo $product['id']; ?>, 'restock_eta', this.value)">
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex space-x-1">
                                    <?php if ($product['is_eco_friendly']): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300">
                                            <i class="fas fa-leaf mr-1"></i>Eco
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($product['is_frozen']): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-cyan-100 text-cyan-800 dark:bg-cyan-900/20 dark:text-cyan-300">
                                            <i class="fas fa-snowflake mr-1"></i>Frozen
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!$product['is_active']): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            <i class="fas fa-pause mr-1"></i>Inactive
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button onclick="toggleActive(<?php echo $product['id']; ?>, <?php echo $product['is_active'] ? 'false' : 'true'; ?>)" 
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                            title="<?php echo $product['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                        <i class="fas fa-<?php echo $product['is_active'] ? 'pause' : 'play'; ?>"></i>
                                    </button>
                                    <a href="/admin/products/<?php echo $product['id']; ?>/edit" 
                                       class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300"
                                       title="Edit Product">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="/admin/products/<?php echo $product['id']; ?>" 
                                       class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                                       title="View Product">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                            <i class="fas fa-boxes text-4xl mb-2"></i>
                            <p>No products found</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination or Single Page Info -->
<?php if ($totalPages > 1): ?>
    <div class="mt-6 flex items-center justify-between">
        <div class="text-sm text-gray-700 dark:text-gray-300">
            Showing <?php echo $offset + 1; ?>-<?php echo min($offset + $limit, $total); ?> of <?php echo $total; ?> total products (Page <?php echo intval($page); ?> of <?php echo intval($totalPages); ?>)
        </div>
        <div class="flex space-x-2">
            <?php if (intval($page) > 1): ?>
                <a href="?page=<?php echo intval($page) - 1; ?>&filter=<?php echo urlencode($filter ?? 'all'); ?>&search=<?php echo urlencode($search ?? ''); ?>&category=<?php echo urlencode($category ?? ''); ?>" 
                   class="px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">
                    Previous
                </a>
            <?php endif; ?>
            
            <?php for ($i = max(1, intval($page) - 2); $i <= min(intval($totalPages), intval($page) + 2); $i++): ?>
                <a href="?page=<?php echo $i; ?>&filter=<?php echo urlencode($filter ?? 'all'); ?>&search=<?php echo urlencode($search ?? ''); ?>&category=<?php echo urlencode($category ?? ''); ?>" 
                   class="px-3 py-2 text-sm rounded-lg <?php echo $i === intval($page) ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            
            <?php if (intval($page) < intval($totalPages)): ?>
                <a href="?page=<?php echo intval($page) + 1; ?>&filter=<?php echo urlencode($filter ?? 'all'); ?>&search=<?php echo urlencode($search ?? ''); ?>&category=<?php echo urlencode($category ?? ''); ?>" 
                   class="px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">
                    Next
                </a>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <!-- Single page info -->
    <?php if (isset($total) && $total > 0): ?>
        <div class="mt-6 text-center text-sm text-gray-700 dark:text-gray-300">
            Showing all <?php echo $total; ?> products
        </div>
    <?php endif; ?>
<?php endif; ?>

<script>
// Update inventory via API
async function updateInventory(productId, field, value) {
    try {
        const response = await fetch(`/api/admin/inventory/${productId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                [field]: value
            })
        });

        const result = await response.json();
        
        if (result.success) {
            // Show success message
            showNotification('Inventory updated successfully', 'success');
            
            // Update status badge if stock count changed
            if (field === 'stock_count' || field === 'low_stock_threshold') {
                updateStatusBadge(productId);
            }
        } else {
            showNotification(result.error || 'Failed to update inventory', 'error');
        }
    } catch (error) {
        console.error('Error updating inventory:', error);
        showNotification('Failed to update inventory', 'error');
    }
}

// Toggle product active status
async function toggleActive(productId, isActive) {
    try {
        const response = await fetch(`/api/admin/inventory/${productId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                is_active: isActive === 'true'
            })
        });

        const result = await response.json();
        
        if (result.success) {
            showNotification('Product status updated successfully', 'success');
            // Reload page to update the UI
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(result.error || 'Failed to update product status', 'error');
        }
    } catch (error) {
        console.error('Error updating product status:', error);
        showNotification('Failed to update product status', 'error');
    }
}

// Update status badge based on stock levels
function updateStatusBadge(productId) {
    const row = document.querySelector(`tr[data-product-id="${productId}"]`);
    if (!row) return;

    const stockInput = row.querySelector('input[onchange*="stock_count"]');
    const thresholdInput = row.querySelector('input[onchange*="low_stock_threshold"]');
    const statusSpan = row.querySelector('td:nth-child(5) span');

    if (!stockInput || !thresholdInput || !statusSpan) return;

    const stock = parseInt(stockInput.value) || 0;
    const threshold = parseInt(thresholdInput.value) || 0;

    let statusClass, statusText;

    if (stock === 0) {
        statusClass = 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300';
        statusText = 'OUT OF STOCK';
    } else if (stock <= threshold) {
        statusClass = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300';
        statusText = 'LOW STOCK';
    } else {
        statusClass = 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300';
        statusText = 'IN STOCK';
    }

    statusSpan.className = `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}`;
    statusSpan.textContent = statusText;
}

// Export inventory data
function exportInventory() {
    // TODO: Implement CSV export functionality
    showNotification('Export functionality coming soon', 'info');
}

// Refresh inventory data
function refreshInventory() {
    location.reload();
}

// Show notification
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white ${
        type === 'success' ? 'bg-green-500' : 
        type === 'error' ? 'bg-red-500' : 
        type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500'
    }`;
    notification.textContent = message;

    document.body.appendChild(notification);

    // Remove notification after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}

// Auto-save functionality for inputs
let saveTimeout;
document.addEventListener('input', function(e) {
    if (e.target.matches('input[onchange*="updateInventory"]')) {
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(() => {
            const onchangeAttr = e.target.getAttribute('onchange');
            const match = onchangeAttr.match(/updateInventory\((\d+), '([^']+)', this\.value\)/);
            if (match) {
                const productId = match[1];
                const field = match[2];
                const value = e.target.value;
                updateInventory(productId, field, value);
            }
        }, 1000); // Auto-save after 1 second of inactivity
    }
});
</script>

<?php
$content = ob_get_clean();
include 'app/views/admin/layout.php';
?>