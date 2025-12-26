<?php
$currentPage = 'products';
$pageTitle = 'Products Management';
include 'app/views/admin/layout.php';
?>

<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Products Management</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Manage your product catalog with advanced features</p>
                </div>
                <div class="flex items-center space-x-4">
                    <button onclick="refreshProducts()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                        <i class="fas fa-sync-alt"></i>
                        <span>Refresh</span>
                    </button>
                    <a href="/admin/products/create" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                        <i class="fas fa-plus"></i>
                        <span>Add Product</span>
                    </a>
                    <a href="/admin/products/bulk-import" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                        <i class="fas fa-upload"></i>
                        <span>Bulk Import</span>
                    </a>
        </div>
    </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-box text-blue-600 dark:text-blue-400"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Products</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white" id="total-products">0</p>
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
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white" id="active-products">0</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-yellow-600 dark:text-yellow-400"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Low Stock</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white" id="low-stock-products">0</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-times-circle text-red-600 dark:text-red-400"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Out of Stock</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white" id="out-of-stock-products">0</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
            <div class="p-6">
                <form id="filter-form" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <!-- Search -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                            <input type="text" id="search" name="search" placeholder="Search products..."
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>

                        <!-- Category Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category</label>
                            <select id="category" name="category" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="">All Categories</option>
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                            <select id="status" name="status" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="all">All Products</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <!-- Stock Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Stock</label>
                            <select id="stock" name="stock" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="all">All Stock</option>
                                <option value="in_stock">In Stock</option>
                                <option value="low_stock">Low Stock</option>
                                <option value="out_of_stock">Out of Stock</option>
                            </select>
                        </div>

                        <!-- Filter Button -->
                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                                <i class="fas fa-filter mr-2"></i>Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Products Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Image</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name + Brand</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Badges</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="products-table-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <!-- Products will be loaded here -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div id="pagination" class="p-6 border-t border-gray-200 dark:border-gray-700">
                <!-- Pagination will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Product Modal -->
<div id="product-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white" id="modal-title">Product Details</h3>
                <button onclick="closeProductModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="modal-content">
                <!-- Modal content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let currentFilters = {};

// Load products on page load
document.addEventListener('DOMContentLoaded', function() {
    loadProducts();
    loadCategories();
    
    // Setup filter form
    document.getElementById('filter-form').addEventListener('submit', function(e) {
        e.preventDefault();
        currentPage = 1;
        currentFilters = {
            search: document.getElementById('search').value,
            category: document.getElementById('category').value,
            status: document.getElementById('status').value,
            stock: document.getElementById('stock').value
        };
        loadProducts();
    });
});

// Load products from API
async function loadProducts() {
    try {
        const params = new URLSearchParams({
            page: currentPage,
            limit: 20,
            ...currentFilters
        });
        
        const response = await fetch(`/api/admin/products?${params}`);
        const result = await response.json();
        
        if (result.success) {
            displayProducts(result.data);
            updatePagination(result.pagination);
            if (result.statistics) {
                updateStatistics(result.statistics);
            } else {
                updateStatistics(result.data);
            }
        } else {
            showNotification('Failed to load products: ' + result.error, 'error');
        }
    } catch (error) {
        console.error('Error loading products:', error);
        showNotification('Failed to load products', 'error');
    }
}

// Display products in table
function displayProducts(products) {
    const tbody = document.getElementById('products-table-body');
    
    if (products.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                    <i class="fas fa-box text-4xl mb-2"></i>
                    <p>No products found</p>
                                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = products.map(product => `
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="w-16 h-16 rounded-lg overflow-hidden">
                    ${product.image ? 
                        `<img src="${product.image}" alt="${product.name}" class="w-full h-full object-cover">` :
                        `<div class="w-full h-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                                            <i class="fas fa-image text-gray-400"></i>
                        </div>`
                    }
                                        </div>
                                </td>
            <td class="px-6 py-4">
                                    <div class="max-w-xs">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">${product.name}</div>
                    ${product.brand ? `<div class="text-sm text-gray-500 dark:text-gray-400">${product.brand}</div>` : ''}
                    <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                        ${product.description ? product.description.substring(0, 50) + '...' : ''}
                                        </div>
                                    </div>
                                </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                ${product.category_name || 'No Category'}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600 dark:text-green-400">
                ৳${parseFloat(product.price).toFixed(2)}
                                </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <span class="text-sm text-gray-900 dark:text-white">${product.stock_quantity}</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">${product.unit || ''}</span>
                </div>
                ${product.stock_quantity <= product.low_stock_threshold ? 
                    `<div class="text-xs text-yellow-600 dark:text-yellow-400">Low stock</div>` : ''
                }
                                </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex flex-wrap gap-1">
                    ${product.is_eco_friendly ? 
                        '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300"><i class="fas fa-leaf mr-1"></i>Eco</span>' : ''
                    }
                    ${product.is_frozen ? 
                        '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300"><i class="fas fa-snowflake mr-1"></i>Frozen</span>' : ''
                    }
                    ${product.diet_tags ? JSON.parse(product.diet_tags).map(tag => 
                        `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-300">${tag}</span>`
                    ).join('') : ''}
                </div>
                                </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" ${product.is_active ? 'checked' : ''} 
                           onchange="toggleProductStatus(${product.id}, this.checked)"
                           class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                </label>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                    <button onclick="viewProduct(${product.id})" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                        <i class="fas fa-eye"></i>
                    </button>
                    <a href="/admin/products/edit/${product.id}" class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button onclick="deleteProduct(${product.id})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                        <i class="fas fa-trash"></i>
                    </button>
                                    </div>
                                </td>
                            </tr>
    `).join('');
}

// Load categories for filter
async function loadCategories() {
    try {
        const response = await fetch('/api/admin/products');
        const result = await response.json();
        
        if (result.success && result.filters && result.filters.categories) {
            const select = document.getElementById('category');
            select.innerHTML = '<option value="">All Categories</option>' + 
                result.filters.categories.map(cat => 
                    `<option value="${cat.id}">${cat.name}</option>`
                ).join('');
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

// Update pagination
function updatePagination(pagination) {
    const container = document.getElementById('pagination');
    
    if (pagination.total_pages <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '<div class="flex justify-center"><nav class="flex items-center space-x-2">';
    
    // Previous button
    if (pagination.current_page > 1) {
        html += `<button onclick="changePage(${pagination.current_page - 1})" class="px-3 py-2 rounded-md bg-white border border-gray-300 text-gray-500 hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600">
                                <i class="fas fa-chevron-left"></i>
        </button>`;
    }
    
    // Page numbers
    const startPage = Math.max(1, pagination.current_page - 2);
    const endPage = Math.min(pagination.total_pages, pagination.current_page + 2);
    
    for (let i = startPage; i <= endPage; i++) {
        html += `<button onclick="changePage(${i})" class="px-3 py-2 rounded-md ${i === pagination.current_page ? 'bg-blue-600 text-white' : 'bg-white border border-gray-300 text-gray-500 hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600'}">
            ${i}
        </button>`;
    }
    
    // Next button
    if (pagination.current_page < pagination.total_pages) {
        html += `<button onclick="changePage(${pagination.current_page + 1})" class="px-3 py-2 rounded-md bg-white border border-gray-300 text-gray-500 hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600">
            <i class="fas fa-chevron-right"></i>
        </button>`;
    }
    
    html += '</nav></div>';
    container.innerHTML = html;
}

// Change page
function changePage(page) {
    currentPage = page;
    loadProducts();
}

// Update statistics
function updateStatistics(stats) {
    // If stats is an object with statistics, use it directly
    if (stats.total_products !== undefined) {
        document.getElementById('total-products').textContent = stats.total_products;
        document.getElementById('active-products').textContent = stats.active_products;
        document.getElementById('low-stock-products').textContent = stats.low_stock_products;
        document.getElementById('out-of-stock-products').textContent = stats.out_of_stock_products;
    } else {
        // Fallback to old calculation from products array
        const total = stats.length;
        const active = stats.filter(p => p.is_active).length;
        const lowStock = stats.filter(p => p.stock_quantity > 0 && p.stock_quantity <= p.low_stock_threshold).length;
        const outOfStock = stats.filter(p => p.stock_quantity === 0).length;
        
        document.getElementById('total-products').textContent = total;
        document.getElementById('active-products').textContent = active;
        document.getElementById('low-stock-products').textContent = lowStock;
        document.getElementById('out-of-stock-products').textContent = outOfStock;
    }
}

// Toggle product status
async function toggleProductStatus(productId, isActive) {
    try {
        const response = await fetch(`/api/admin/products/${productId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                is_active: isActive
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Product status updated successfully', 'success');
            loadProducts(); // Refresh the list
        } else {
            showNotification('Failed to update product status: ' + result.error, 'error');
        }
    } catch (error) {
        console.error('Error updating product status:', error);
        showNotification('Failed to update product status', 'error');
    }
}

// View product details
async function viewProduct(productId) {
    try {
        const response = await fetch(`/api/admin/products/${productId}`);
        const result = await response.json();
        
        if (result.success) {
            displayProductModal(result.data);
        } else {
            showNotification('Failed to load product details: ' + result.error, 'error');
        }
    } catch (error) {
        console.error('Error loading product details:', error);
        showNotification('Failed to load product details', 'error');
    }
}

// Display product modal
function displayProductModal(product) {
    document.getElementById('modal-title').textContent = product.name;
    document.getElementById('modal-content').innerHTML = `
        <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                    <p class="text-sm text-gray-900 dark:text-white">${product.name}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Brand</label>
                    <p class="text-sm text-gray-900 dark:text-white">${product.brand || 'N/A'}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Price</label>
                    <p class="text-sm text-gray-900 dark:text-white">৳${parseFloat(product.price).toFixed(2)}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stock</label>
                    <p class="text-sm text-gray-900 dark:text-white">${product.stock_quantity} ${product.unit || ''}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                    <p class="text-sm text-gray-900 dark:text-white">${product.category_name || 'No Category'}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                    <p class="text-sm text-gray-900 dark:text-white">${product.is_active ? 'Active' : 'Inactive'}</p>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                <p class="text-sm text-gray-900 dark:text-white">${product.description || 'No description'}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Diet Tags</label>
                <div class="flex flex-wrap gap-1">
                    ${product.diet_tags ? JSON.parse(product.diet_tags).map(tag => 
                        `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-300">${tag}</span>`
                    ).join('') : '<span class="text-sm text-gray-500 dark:text-gray-400">No diet tags</span>'}
                </div>
    </div>
</div>
    `;
    document.getElementById('product-modal').classList.remove('hidden');
}

// Close product modal
function closeProductModal() {
    document.getElementById('product-modal').classList.add('hidden');
}

// Delete product
async function deleteProduct(productId) {
    if (!confirm('Are you sure you want to delete this product?')) {
        return;
    }
    
    try {
        const response = await fetch(`/api/admin/products/${productId}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Product deleted successfully', 'success');
            loadProducts(); // Refresh the list
        } else {
            showNotification('Failed to delete product: ' + result.error, 'error');
        }
    } catch (error) {
        console.error('Error deleting product:', error);
        showNotification('Failed to delete product', 'error');
    }
}

// Refresh products
function refreshProducts() {
    loadProducts();
}

// Show notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        type === 'warning' ? 'bg-yellow-500 text-white' :
        'bg-blue-500 text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>