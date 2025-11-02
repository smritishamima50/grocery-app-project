<?php
$currentPage = 'coupons';
$pageTitle = 'Create Coupon';
include 'app/views/admin/layout.php';
?>

<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create New Coupon</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Add a new discount code to your promotions</p>
                </div>
                <a href="/admin/coupons" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Coupons</span>
                </a>
            </div>
        </div>

        <!-- Coupon Form -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <form id="coupon-form" class="p-6 space-y-6">
                <!-- Basic Information -->
                <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Basic Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Coupon Code <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="code" name="code" required placeholder="e.g., SAVE20, WELCOME10"
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Code will be automatically converted to uppercase</p>
                        </div>
                        <div>
                            <label for="discount_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Discount Type <span class="text-red-500">*</span>
                            </label>
                            <select id="discount_type" name="discount_type" required
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Type</option>
                                <option value="percentage">Percentage (%)</option>
                                <option value="flat">Fixed Amount (৳)</option>
                            </select>
                        </div>
                        <div>
                            <label for="discount_value" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Discount Value <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="discount_value" name="discount_value" step="0.01" min="0" required
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" id="discount_hint">Enter discount value</p>
                        </div>
                        <div>
                            <label for="min_order_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Minimum Order Amount (৳)</label>
                            <input type="number" id="min_order_amount" name="min_order_amount" step="0.01" min="0" value="0"
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Usage Limits -->
                <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Usage Limits</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="usage_limit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Total Usage Limit</label>
                            <input type="number" id="usage_limit" name="usage_limit" min="0" placeholder="Leave empty for unlimited"
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Maximum number of times this coupon can be used</p>
                        </div>
                        <div>
                            <label for="max_uses_per_user" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Max Uses Per User</label>
                            <input type="number" id="max_uses_per_user" name="max_uses_per_user" min="1" value="1"
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">How many times each user can use this coupon</p>
                        </div>
                    </div>
                </div>

                <!-- Validity Period -->
                <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Validity Period</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Start Date</label>
                            <input type="date" id="start_date" name="start_date"
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave empty to start immediately</p>
                        </div>
                        <div>
                            <label for="expiry_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Expiry Date</label>
                            <input type="date" id="expiry_date" name="expiry_date"
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave empty for no expiry</p>
                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div class="pb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Status</h3>
                    <div class="flex items-center">
                        <label class="flex items-center">
                            <input type="checkbox" id="is_active" name="is_active" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active (coupon can be used)</span>
                        </label>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4">
                    <a href="/admin/coupons" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg">
                        Cancel
                    </a>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg flex items-center space-x-2">
                        <i class="fas fa-save"></i>
                        <span>Create Coupon</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Setup form on page load
document.addEventListener('DOMContentLoaded', function() {
    // Setup form submission
    document.getElementById('coupon-form').addEventListener('submit', function(e) {
        e.preventDefault();
        createCoupon();
    });
    
    // Setup discount type change handler
    document.getElementById('discount_type').addEventListener('change', function() {
        updateDiscountHint();
    });
    
    // Setup date validation
    document.getElementById('start_date').addEventListener('change', function() {
        validateDates();
    });
    
    document.getElementById('expiry_date').addEventListener('change', function() {
        validateDates();
    });
    
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('start_date').setAttribute('min', today);
    document.getElementById('expiry_date').setAttribute('min', today);
});

// Update discount hint based on type
function updateDiscountHint() {
    const type = document.getElementById('discount_type').value;
    const hint = document.getElementById('discount_hint');
    const valueInput = document.getElementById('discount_value');
    
    if (type === 'percentage') {
        hint.textContent = 'Enter percentage (0-100)';
        valueInput.setAttribute('max', '100');
    } else if (type === 'flat') {
        hint.textContent = 'Enter amount in ৳';
        valueInput.removeAttribute('max');
    } else {
        hint.textContent = 'Enter discount value';
    }
}

// Validate dates
function validateDates() {
    const startDate = document.getElementById('start_date').value;
    const expiryDate = document.getElementById('expiry_date').value;
    
    if (startDate && expiryDate && startDate > expiryDate) {
        showNotification('Start date cannot be after expiry date', 'error');
        document.getElementById('expiry_date').value = '';
    }
}

// Create coupon
async function createCoupon() {
    try {
        const formData = new FormData(document.getElementById('coupon-form'));
        const data = {};
        
        // Convert FormData to object
        for (let [key, value] of formData.entries()) {
            if (value === '') {
                data[key] = null; // Convert empty strings to null
            } else if (key === 'usage_limit' && value === '') {
                data[key] = null; // Unlimited usage
            } else {
                data[key] = value;
            }
        }
        
        // Convert checkbox
        data.is_active = document.getElementById('is_active').checked;
        
        // Validate required fields
        if (!data.code || !data.discount_type || !data.discount_value) {
            showNotification('Please fill in all required fields', 'error');
            return;
        }
        
        // Validate discount value
        if (data.discount_type === 'percentage' && data.discount_value > 100) {
            showNotification('Percentage discount cannot exceed 100%', 'error');
            return;
        }
        
        if (data.discount_value <= 0) {
            showNotification('Discount value must be greater than 0', 'error');
            return;
        }
        
        const response = await fetch('/api/admin/coupons', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Coupon created successfully', 'success');
            setTimeout(() => {
                window.location.href = '/admin/coupons';
            }, 1500);
        } else {
            showNotification('Failed to create coupon: ' + result.error, 'error');
        }
    } catch (error) {
        console.error('Error creating coupon:', error);
        showNotification('Failed to create coupon', 'error');
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