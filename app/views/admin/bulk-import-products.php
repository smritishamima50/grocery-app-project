<?php
$currentPage = 'products';
$pageTitle = 'Bulk Import Products';
include 'app/views/admin/layout.php';
?>

<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Bulk Import Products</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Import multiple products at once using JSON format</p>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/admin/products" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Products</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Instructions Card -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-3">
                <i class="fas fa-info-circle mr-2"></i>How to Import Products
            </h3>
            <div class="text-blue-800 dark:text-blue-200 space-y-2">
                <p><strong>Method 1:</strong> Upload a JSON file</p>
                <p><strong>Method 2:</strong> Paste JSON directly in the text area</p>
                <p class="text-sm mt-3">
                    <strong>Note:</strong> Categories will be automatically created if they don't exist.
                    Products with duplicate names will be skipped.
                </p>
            </div>
        </div>

        <!-- Import Form -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6 space-y-6">
                <!-- File Upload -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fas fa-file-upload mr-2"></i>Upload JSON File
                    </label>
                    <input type="file" id="json-file" accept=".json" 
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Select a JSON file containing product data
                    </p>
                </div>

                <div class="text-center text-gray-500 dark:text-gray-400">
                    <span>OR</span>
                </div>

                <!-- JSON Text Area -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fas fa-code mr-2"></i>Paste JSON Data
                    </label>
                    <textarea id="json-data" rows="20" 
                              class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-mono text-sm"
                              placeholder='{
  "products": [
    {
      "name": "Product Name",
      "brand": "Brand Name",
      "description": "Product description",
      "price": 100.00,
      "category": "Category Name",
      ...
    }
  ]
}'></textarea>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Paste your JSON product data here. Format: <code class="bg-gray-200 dark:bg-gray-700 px-1 rounded">{"products": [...]}</code>
                    </p>
                </div>

                <!-- Quick Load Button -->
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                    <button type="button" onclick="loadSampleProducts()" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                        <i class="fas fa-download"></i>
                        <span>Load Sample JSON (12 Products)</span>
                    </button>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Click to load the 12 predefined products: Salt, Honey, Dates, etc.
                    </p>
                </div>

                <!-- Import Button -->
                <div class="flex items-center justify-end space-x-4">
                    <button type="button" onclick="validateJson()" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center space-x-2">
                        <i class="fas fa-check"></i>
                        <span>Validate JSON</span>
                    </button>
                    <button type="button" id="import-btn" onclick="importProducts()" 
                            class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg flex items-center space-x-2">
                        <i class="fas fa-upload"></i>
                        <span>Import Products</span>
                    </button>
                </div>

                <!-- Results Section -->
                <div id="results-section" class="hidden mt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Import Results</h3>
                    <div id="results-content" class="space-y-4"></div>
                </div>
            </div>
        </div>

        <!-- JSON Format Guide -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow mt-6">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    <i class="fas fa-book mr-2"></i>JSON Format Guide
                </h3>
                <div class="space-y-4">
                    <div>
                        <p class="font-medium text-gray-700 dark:text-gray-300 mb-2">Required Fields:</p>
                        <ul class="list-disc list-inside text-sm text-gray-600 dark:text-gray-400 space-y-1">
                            <li><code>name</code> - Product name (string)</li>
                            <li><code>price</code> - Product price (number, e.g., 100.00)</li>
                            <li><code>category</code> - Category name (string, will be created if doesn't exist)</li>
                        </ul>
                    </div>
                    <div>
                        <p class="font-medium text-gray-700 dark:text-gray-300 mb-2">Optional Fields:</p>
                        <ul class="list-disc list-inside text-sm text-gray-600 dark:text-gray-400 space-y-1">
                            <li><code>brand</code> - Brand name</li>
                            <li><code>description</code> - Product description</li>
                            <li><code>unit_size</code> - Size (e.g., "1kg", "500g")</li>
                            <li><code>stock_quantity</code> - Stock quantity (number, default: 0)</li>
                            <li><code>low_stock_threshold</code> - Low stock warning (number, default: 10)</li>
                            <li><code>unit</code> - Unit type (e.g., "kg", "pcs", "packs")</li>
                            <li><code>image</code> - Image URL</li>
                            <li><code>nutrition_info</code> - Nutrition details</li>
                            <li><code>diet_tags</code> - Array of tags (e.g., ["vegan", "organic"])</li>
                            <li><code>is_eco_friendly</code> - Boolean (default: false)</li>
                            <li><code>is_frozen</code> - Boolean (default: false)</li>
                            <li><code>is_active</code> - Boolean (default: true)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Sample products data (12 products)
const sampleProducts = <?php 
$jsonFile = __DIR__ . '/../../../data/bulk_products_12.json';
if (file_exists($jsonFile)) {
    $data = json_decode(file_get_contents($jsonFile), true);
    echo json_encode($data ?: []);
} else {
    echo '[]';
}
?>;

// Load sample products into textarea
function loadSampleProducts() {
    // Handle both array format and object format
    let jsonData;
    if (Array.isArray(sampleProducts)) {
        // If it's an array, wrap it in products object
        jsonData = JSON.stringify({products: sampleProducts}, null, 2);
    } else if (sampleProducts.products && Array.isArray(sampleProducts.products)) {
        // If it's already in correct format
        jsonData = JSON.stringify(sampleProducts, null, 2);
    } else {
        jsonData = JSON.stringify({products: []}, null, 2);
    }
    document.getElementById('json-data').value = jsonData;
    showNotification('Sample products loaded! You can edit them before importing.', 'success');
}

// File upload handler
document.getElementById('json-file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const json = JSON.parse(e.target.result);
                let formattedJson;
                
                // Handle both formats
                if (json.products && Array.isArray(json.products)) {
                    // Already in correct format
                    formattedJson = JSON.stringify(json, null, 2);
                    showNotification('File loaded successfully!', 'success');
                } else if (Array.isArray(json)) {
                    // Direct array format - wrap it
                    formattedJson = JSON.stringify({products: json}, null, 2);
                    showNotification('File loaded! Wrapped in products array.', 'success');
                } else {
                    showNotification('Invalid format. Expected array or {"products": [...]}', 'error');
                    return;
                }
                
                document.getElementById('json-data').value = formattedJson;
            } catch (error) {
                showNotification('Invalid JSON file: ' + error.message, 'error');
            }
        };
        reader.readAsText(file);
    }
});

// Validate JSON
function validateJson() {
    const jsonText = document.getElementById('json-data').value.trim();
    
    if (!jsonText) {
        showNotification('Please enter JSON data', 'error');
        return false;
    }
    
    try {
        const data = JSON.parse(jsonText);
        
        // Handle both formats: {"products": [...]} or just [...]
        let products;
        if (data.products && Array.isArray(data.products)) {
            products = data.products;
        } else if (Array.isArray(data)) {
            products = data;
        } else {
            showNotification('Invalid format. Expected {"products": [...]} or array of products', 'error');
            return false;
        }
        
        if (products.length === 0) {
            showNotification('Products array is empty', 'error');
            return false;
        }
        
        // Validate each product
        const errors = [];
        products.forEach((product, index) => {
            if (!product.name || !product.name.trim()) {
                errors.push(`Product #${index + 1}: Missing name`);
            }
            if (!product.price || product.price <= 0) {
                errors.push(`Product #${index + 1}: Invalid price`);
            }
            if (!product.category || !product.category.trim()) {
                errors.push(`Product #${index + 1}: Missing category`);
            }
        });
        
        if (errors.length > 0) {
            showNotification('Validation errors:\n' + errors.join('\n'), 'error');
            return false;
        }
        
        showNotification(`✅ Valid JSON! Ready to import ${products.length} product(s).`, 'success');
        return true;
    } catch (error) {
        showNotification('Invalid JSON: ' + error.message, 'error');
        return false;
    }
}

// Import products
async function importProducts() {
    const jsonText = document.getElementById('json-data').value.trim();
    
    if (!jsonText) {
        showNotification('Please enter JSON data', 'error');
        return;
    }
    
    // Validate first
    if (!validateJson()) {
        return;
    }
    
    let data;
    try {
        const parsed = JSON.parse(jsonText);
        
        // Handle both formats: {"products": [...]} or just [...]
        if (parsed.products && Array.isArray(parsed.products)) {
            data = parsed;
        } else if (Array.isArray(parsed)) {
            data = {products: parsed};
        } else {
            showNotification('Invalid format', 'error');
            return;
        }
    } catch (error) {
        showNotification('Invalid JSON: ' + error.message, 'error');
        return;
    }
    
    // Disable button and show loading
    const importBtn = document.getElementById('import-btn');
    const originalText = importBtn.innerHTML;
    importBtn.disabled = true;
    importBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Importing...';
    
    try {
        const response = await fetch('/api/admin/products/bulk-import', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        // Show results
        displayResults(result);
        
        if (result.success) {
            showNotification(result.message, 'success');
            // Clear form if all succeeded
            if (result.results.failed_count === 0) {
                document.getElementById('json-data').value = '';
                document.getElementById('json-file').value = '';
            }
            // Refresh after 3 seconds if successful
            if (result.results.success_count > 0) {
                setTimeout(() => {
                    window.location.href = '/admin/products';
                }, 3000);
            }
        } else {
            showNotification(result.error || 'Import failed', 'error');
        }
    } catch (error) {
        console.error('Import error:', error);
        showNotification('Failed to import: ' + error.message, 'error');
    } finally {
        importBtn.disabled = false;
        importBtn.innerHTML = originalText;
    }
}

// Display import results
function displayResults(result) {
    const resultsSection = document.getElementById('results-section');
    const resultsContent = document.getElementById('results-content');
    
    resultsSection.classList.remove('hidden');
    
    let html = `
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-4">
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">${result.results.total}</p>
                </div>
                <div>
                    <p class="text-sm text-green-600 dark:text-green-400">Success</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">${result.results.success_count}</p>
                </div>
                <div>
                    <p class="text-sm text-red-600 dark:text-red-400">Failed</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">${result.results.failed_count}</p>
                </div>
            </div>
        </div>
    `;
    
    // Show successful imports
    if (result.results.success && result.results.success.length > 0) {
        html += `
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <h4 class="font-semibold text-green-900 dark:text-green-100 mb-2">
                    <i class="fas fa-check-circle mr-2"></i>Successfully Imported (${result.results.success.length})
                </h4>
                <ul class="list-disc list-inside text-sm text-green-800 dark:text-green-200 space-y-1">
        `;
        result.results.success.forEach(item => {
            html += `<li>#${item.index}: ${item.name} (ID: ${item.id})</li>`;
        });
        html += `</ul></div>`;
    }
    
    // Show failed imports
    if (result.results.failed && result.results.failed.length > 0) {
        html += `
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <h4 class="font-semibold text-red-900 dark:text-red-100 mb-2">
                    <i class="fas fa-times-circle mr-2"></i>Failed to Import (${result.results.failed.length})
                </h4>
                <ul class="list-disc list-inside text-sm text-red-800 dark:text-red-200 space-y-1">
        `;
        result.results.failed.forEach(item => {
            html += `<li>#${item.index}: ${item.name} - ${item.error}</li>`;
        });
        html += `</ul></div>`;
    }
    
    resultsContent.innerHTML = html;
    resultsSection.scrollIntoView({ behavior: 'smooth' });
}

// Show notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 max-w-md ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        type === 'warning' ? 'bg-yellow-500 text-white' :
        'bg-blue-500 text-white'
    }`;
    notification.style.whiteSpace = 'pre-line';
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transition = 'opacity 0.3s';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

// Initialize - try to load sample file if available
document.addEventListener('DOMContentLoaded', function() {
    // Auto-load sample products on page load for convenience
    // Uncomment the line below if you want auto-loading
    // loadSampleProducts();
});
</script>

