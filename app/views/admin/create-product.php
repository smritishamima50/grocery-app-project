<?php
$currentPage = 'products';
$pageTitle = 'Create Product';
include 'app/views/admin/layout.php';
?>

<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create New Product</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Add a new product to your catalog</p>
                </div>
                <a href="/admin/products" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Products</span>
                </a>
            </div>
        </div>

        <!-- Product Form -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <form id="product-form" class="p-6 space-y-6">
                <!-- Basic Information -->
                <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Basic Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Product Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="name" name="name" required
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="brand" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Brand</label>
                            <input type="text" id="brand" name="brand"
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                            <textarea id="description" name="description" rows="3"
                                      class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Pricing & Inventory -->
                <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Pricing & Inventory</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Price (৳) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="price" name="price" step="0.01" min="0" required
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="unit_size" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Unit Size</label>
                            <input type="text" id="unit_size" name="unit_size" placeholder="e.g., 1kg, 500g, 12pcs"
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="stock_quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Stock Quantity</label>
                            <input type="number" id="stock_quantity" name="stock_quantity" min="0" value="0"
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="low_stock_threshold" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Low Stock Threshold</label>
                            <input type="number" id="low_stock_threshold" name="low_stock_threshold" min="0" value="10"
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="unit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Unit</label>
                            <select id="unit" name="unit"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Unit</option>
                                <option value="kg">Kilogram (kg)</option>
                                <option value="g">Gram (g)</option>
                                <option value="pcs">Pieces</option>
                                <option value="packs">Packs</option>
                                <option value="liters">Liters</option>
                                <option value="ml">Milliliters (ml)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Category & Image -->
                <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Category & Image</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Category <span class="text-red-500">*</span>
                            </label>
                            <select id="category_id" name="category_id" required
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Category</option>
                            </select>
                        </div>
                        <div>
                            <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Image URL</label>
                            <input type="url" id="image" name="image" placeholder="https://example.com/image.jpg"
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Product Features -->
                <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Product Features</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Diet Tags</label>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="diet_tags[]" value="vegan" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Vegan</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="diet_tags[]" value="vegetarian" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Vegetarian</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="diet_tags[]" value="gluten-free" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Gluten-Free</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="diet_tags[]" value="organic" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Organic</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="diet_tags[]" value="dairy-free" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Dairy-Free</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="diet_tags[]" value="sugar-free" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Sugar-Free</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="diet_tags[]" value="low-carb" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Low-Carb</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="diet_tags[]" value="keto" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Keto</span>
                                </label>
                            </div>
                        </div>
                        <div class="flex items-center space-x-6">
                            <label class="flex items-center">
                                <input type="checkbox" id="is_eco_friendly" name="is_eco_friendly" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Eco-Friendly</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" id="is_frozen" name="is_frozen" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Frozen</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" id="is_active" name="is_active" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Nutrition Information -->
                <div class="pb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Nutrition Information</h3>
                    <div>
                        <label for="nutrition_info" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nutrition Details</label>
                        <textarea id="nutrition_info" name="nutrition_info" rows="4" placeholder="Enter nutrition information..."
                                  class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4">
                    <a href="/admin/products" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg">
                        Cancel
                    </a>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg flex items-center space-x-2">
                        <i class="fas fa-save"></i>
                        <span>Create Product</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Load categories on page load
document.addEventListener('DOMContentLoaded', function() {
    loadCategories();
    
    // Setup form submission
    document.getElementById('product-form').addEventListener('submit', function(e) {
        e.preventDefault();
        createProduct();
    });
});

// Load categories for dropdown
async function loadCategories() {
    try {
        // Try API endpoint first
        let response = await fetch('/api/admin/products');
        let result = await response.json();
        
        if (result.success && result.filters && result.filters.categories) {
            const select = document.getElementById('category_id');
            select.innerHTML = '<option value="">Select Category</option>' + 
                result.filters.categories.map(cat => 
                    `<option value="${cat.id}">${cat.name}</option>`
                ).join('');
            return;
        }
        
        // Fallback: Load directly from PHP (categories are passed to view)
        <?php if (!empty($categories)): ?>
        const select = document.getElementById('category_id');
        select.innerHTML = '<option value="">Select Category</option>' + 
            <?php echo json_encode(array_map(function($cat) {
                return ['id' => $cat['id'], 'name' => $cat['name']];
            }, $categories)); ?>.map(cat => 
                `<option value="${cat.id}">${cat.name}</option>`
            ).join('');
        <?php else: ?>
        console.error('No categories available');
        showNotification('Failed to load categories. Please refresh the page.', 'error');
        <?php endif; ?>
    } catch (error) {
        console.error('Error loading categories:', error);
        // Fallback to PHP categories
        <?php if (!empty($categories)): ?>
        const select = document.getElementById('category_id');
        select.innerHTML = '<option value="">Select Category</option>' + 
            <?php echo json_encode(array_map(function($cat) {
                return ['id' => $cat['id'], 'name' => $cat['name']];
            }, $categories)); ?>.map(cat => 
                `<option value="${cat.id}">${cat.name}</option>`
            ).join('');
        <?php else: ?>
        showNotification('Failed to load categories. Please refresh the page.', 'error');
        <?php endif; ?>
    }
}

// Create product
async function createProduct() {
    try {
        // Disable submit button to prevent double submission
        const submitBtn = document.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
        
        const formData = new FormData(document.getElementById('product-form'));
        const data = {};
        
        // Convert FormData to object
        data.diet_tags = [];
        for (let [key, value] of formData.entries()) {
            if (key === 'diet_tags[]') {
                data.diet_tags.push(value);
            } else if (key !== 'diet_tags[]') {
                data[key] = value;
            }
        }
        
        // If no diet tags, ensure it's an empty array
        if (!data.diet_tags || data.diet_tags.length === 0) {
            data.diet_tags = [];
        }
        
        // Convert checkboxes to boolean
        data.is_eco_friendly = document.getElementById('is_eco_friendly').checked;
        data.is_frozen = document.getElementById('is_frozen').checked;
        data.is_active = document.getElementById('is_active').checked;
        
        // Validate required fields
        if (!data.name || !data.name.trim()) {
            showNotification('Product name is required', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            return;
        }
        
        if (!data.price || parseFloat(data.price) <= 0) {
            showNotification('Price must be greater than 0', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            return;
        }
        
        if (!data.category_id || data.category_id === '') {
            showNotification('Category is required', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
            return;
        }
        
        // Ensure numeric fields are properly formatted
        data.price = parseFloat(data.price);
        data.stock_quantity = parseInt(data.stock_quantity || 0);
        data.low_stock_threshold = parseInt(data.low_stock_threshold || 10);
        data.category_id = parseInt(data.category_id);
        
        console.log('Sending product data:', data);
        
        const response = await fetch('/api/admin/products', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Product created successfully', 'success');
            setTimeout(() => {
                window.location.href = '/admin/products';
            }, 1500);
        } else {
            const errorMsg = result.error || 'Failed to create product';
            console.error('Product creation error:', errorMsg);
            showNotification('Failed to create product: ' + errorMsg, 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Error creating product:', error);
        showNotification('Failed to create product: ' + error.message, 'error');
        const submitBtn = document.querySelector('button[type="submit"]');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save"></i><span>Create Product</span>';
    }
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