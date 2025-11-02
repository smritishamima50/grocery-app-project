<?php
$currentPage = 'coupons';
$pageTitle = 'Coupons Management';
include 'app/views/admin/layout.php';
?>

<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Coupons Management</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Manage discount codes and promotions</p>
                </div>
                <div class="flex items-center space-x-4">
                    <button onclick="refreshCoupons()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                        <i class="fas fa-sync-alt"></i>
                        <span>Refresh</span>
                    </button>
                    <button onclick="openAddCouponModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                        <i class="fas fa-plus"></i>
                        <span>Create Coupon</span>
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
                            <i class="fas fa-ticket-alt text-blue-600 dark:text-blue-400"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Coupons</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white" id="total-coupons">0</p>
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
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white" id="active-coupons">0</p>
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
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Expired</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white" id="expired-coupons">0</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-line text-purple-600 dark:text-purple-400"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Usage</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white" id="total-usage">0</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
            <div class="p-6">
                <form id="filter-form" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Search -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                            <input type="text" id="search" name="search" placeholder="Search coupons..."
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                            <select id="status" name="status" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="all">All Coupons</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="expired">Expired</option>
                            </select>
                        </div>

                        <!-- Type Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type</label>
                            <select id="type" name="type" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="all">All Types</option>
                                <option value="percentage">Percentage</option>
                                <option value="flat">Fixed Amount</option>
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

        <!-- Coupons Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type/Value</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Min Order</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Usage</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Valid Period</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="coupons-table-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <!-- Coupons will be loaded here -->
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

<!-- View Coupon Modal -->
<div id="coupon-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white" id="modal-title">Coupon Details</h3>
                <button onclick="closeCouponModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="modal-content">
                <!-- Modal content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Coupon Modal -->
<div id="coupon-form-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 xl:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800 mb-10">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white" id="form-modal-title">Add Coupon</h3>
                <button onclick="closeCouponFormModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="coupon-form" onsubmit="handleCouponFormSubmit(event)" class="space-y-6">
                <input type="hidden" id="coupon-id" name="id">
                
                <!-- Basic Information -->
                <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                    <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4">Basic Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Coupon Code <span class="text-red-500">*</span></label>
                            <input type="text" id="coupon-code" name="code" required
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white uppercase"
                                   placeholder="SAVE20">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type <span class="text-red-500">*</span></label>
                            <select id="coupon-type" name="discount_type" required
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                    onchange="updateValueLabel()">
                                <option value="percentage">Percentage</option>
                                <option value="flat">Fixed Amount</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                <span id="value-label">Discount Value</span> <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center">
                                <span id="value-prefix" class="mr-2 text-gray-700 dark:text-gray-300">%</span>
                                <input type="number" id="coupon-value" name="discount_value" step="0.01" min="0" required
                                       class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Min Order Amount</label>
                            <input type="number" id="coupon-min-order" name="min_order_amount" step="0.01" min="0" value="0"
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                    </div>
                </div>
                
                <!-- Date & Usage Limits -->
                <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                    <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4">Date & Usage Limits</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Start Date</label>
                            <input type="date" id="coupon-start-date" name="start_date"
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Expiry Date</label>
                            <input type="date" id="coupon-expiry-date" name="expiry_date"
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Usage Limit (Total)</label>
                            <input type="number" id="coupon-usage-limit" name="usage_limit" min="0"
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                   placeholder="Leave empty for unlimited">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Leave empty for unlimited uses</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Max Uses Per User</label>
                            <input type="number" id="coupon-max-uses" name="max_uses_per_user" min="1" value="1"
                                   class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                    </div>
                </div>
                
                <!-- Status -->
                <div>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" id="coupon-is-active" name="is_active" checked
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Active</span>
                    </label>
                </div>
                
                <!-- Form Actions -->
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" onclick="closeCouponFormModal()" 
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                        Save Coupon
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let currentFilters = {};

// Load coupons on page load
document.addEventListener('DOMContentLoaded', function() {
    loadCoupons();
    
    // Setup filter form
    document.getElementById('filter-form').addEventListener('submit', function(e) {
        e.preventDefault();
        currentPage = 1;
        currentFilters = {
            search: document.getElementById('search').value,
            status: document.getElementById('status').value,
            type: document.getElementById('type').value
        };
        loadCoupons();
    });
});

// Load coupons from API
async function loadCoupons() {
    try {
        console.log('🔄 Loading coupons...', { page: currentPage, filters: currentFilters });
        
        const params = new URLSearchParams({
            page: currentPage,
            limit: 20,
            ...currentFilters
        });
        
        const response = await fetch(`/api/admin/coupons?${params}`, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            }
        });
        
        console.log('📥 Response status:', response.status, response.statusText);
        
        // Get response text first
        const responseText = await response.text();
        console.log('📥 Response length:', responseText.length);
        console.log('📥 Response preview:', responseText.substring(0, 200));
        
        // Check if response is OK
        if (!response.ok) {
            let errorMessage = 'Failed to load coupons';
            try {
                const errorData = JSON.parse(responseText);
                errorMessage = errorData.error || errorMessage;
                console.error('❌ API Error Response:', errorData);
            } catch (e) {
                if (responseText.trim().startsWith('<')) {
                    errorMessage = 'Server returned HTML instead of JSON. Check server logs for PHP errors.';
                } else if (responseText.length > 0) {
                    errorMessage = 'Error: ' + responseText.substring(0, 100);
                }
                console.error('❌ Failed to parse error response:', e);
            }
            
            showNotification(errorMessage, 'error');
            return;
        }
        
        // Parse JSON response
        let result;
        try {
            result = JSON.parse(responseText);
            console.log('✅ Parsed JSON successfully:', result);
        } catch (e) {
            console.error('❌ JSON parse error:', e);
            console.error('❌ Response text that failed to parse:', responseText);
            const errorMsg = responseText.trim().startsWith('<') 
                ? 'Server returned HTML instead of JSON. Check server logs for PHP errors.'
                : 'Invalid JSON response from server';
            showNotification(errorMsg, 'error');
            return;
        }
        
        if (result.success) {
            console.log('✅ Success! Coupons count:', result.data?.length || 0);
            displayCoupons(result.data || []);
            updatePagination(result.pagination || { current_page: 1, total: 0, total_pages: 1 });
            
            // Update statistics if provided, otherwise use data array
            if (result.statistics) {
                window.couponStatistics = result.statistics;
                updateStatistics([]);
            } else {
                updateStatistics(result.data || []);
            }
        } else {
            console.error('❌ API returned success=false:', result);
            showNotification('Failed to load coupons: ' + (result.error || 'Unknown error'), 'error');
        }
    } catch (error) {
        console.error('❌ Error loading coupons:', error);
        console.error('❌ Error stack:', error.stack);
        showNotification('Failed to load coupons: ' + (error.message || 'Network error'), 'error');
    }
}

// Display coupons in table
function displayCoupons(coupons) {
    const tbody = document.getElementById('coupons-table-body');
    
    if (coupons.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                    <i class="fas fa-ticket-alt text-4xl mb-2"></i>
                    <p>No coupons found</p>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = coupons.map(coupon => {
        const isExpired = coupon.expiry_date && new Date(coupon.expiry_date) < new Date();
        const isLimitReached = coupon.usage_limit && coupon.used_count >= coupon.usage_limit;
        const isActive = !isExpired && !isLimitReached && coupon.is_active;
        
        return `
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-gradient-to-r from-purple-400 to-pink-500 rounded-lg flex items-center justify-center text-white font-bold text-sm mr-3">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white font-mono">
                                ${coupon.code}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                Created ${new Date(coupon.created_at).toLocaleDateString()}
                            </div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                            coupon.discount_type === 'percentage' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300' : 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300'
                        }">
                            ${coupon.discount_type === 'percentage' ? 'Percentage' : 'Fixed Amount'}
                        </span>
                        <span class="ml-2 text-sm font-medium text-gray-900 dark:text-white">
                            ${coupon.discount_type === 'percentage' ? 
                                `${coupon.discount_value}%` : 
                                `৳${parseFloat(coupon.discount_value).toFixed(2)}`
                            }
                        </span>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                    ৳${parseFloat(coupon.min_order_amount).toFixed(2)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-white">
                        ${coupon.used_count}
                        ${coupon.usage_limit ? `/ ${coupon.usage_limit}` : '/ ∞'}
                    </div>
                    ${coupon.usage_limit && coupon.used_count >= coupon.usage_limit ? 
                        '<div class="text-xs text-red-600 dark:text-red-400">Limit reached</div>' : ''
                    }
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-white">
                        ${coupon.start_date ? new Date(coupon.start_date).toLocaleDateString() : 'No start date'}
                        ${coupon.expiry_date ? ` - ${new Date(coupon.expiry_date).toLocaleDateString()}` : ' - No expiry'}
                    </div>
                    ${isExpired ? '<div class="text-xs text-red-600 dark:text-red-400">Expired</div>' : ''}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                            isActive ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300'
                        }">
                            ${isActive ? 'Active' : 'Inactive'}
                        </span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" ${coupon.is_active ? 'checked' : ''} 
                                   onchange="toggleCouponStatus(${coupon.id}, this.checked)"
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex space-x-2">
                        <button onclick="viewCoupon(${coupon.id})" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button onclick="editCoupon(${coupon.id})" class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteCoupon(${coupon.id})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
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
    loadCoupons();
}

// Update statistics - use overall stats from API if available
function updateStatistics(coupons) {
    // If statistics are provided from API, use them
    if (window.couponStatistics) {
        document.getElementById('total-coupons').textContent = window.couponStatistics.total || 0;
        document.getElementById('active-coupons').textContent = window.couponStatistics.active || 0;
        document.getElementById('expired-coupons').textContent = window.couponStatistics.expired || 0;
        document.getElementById('total-usage').textContent = window.couponStatistics.total_usage || 0;
        return;
    }
    
    // Fallback: calculate from current page
    const total = coupons.length;
    let active = 0;
    let expired = 0;
    let totalUsage = 0;
    
    coupons.forEach(coupon => {
        const isExpired = coupon.expiry_date && new Date(coupon.expiry_date) < new Date();
        const isLimitReached = coupon.usage_limit && coupon.used_count >= coupon.usage_limit;
        const isActive = !isExpired && !isLimitReached && coupon.is_active;
        
        if (isActive) active++;
        if (isExpired) expired++;
        totalUsage += coupon.used_count;
    });
    
    document.getElementById('total-coupons').textContent = total;
    document.getElementById('active-coupons').textContent = active;
    document.getElementById('expired-coupons').textContent = expired;
    document.getElementById('total-usage').textContent = totalUsage;
}

// Toggle coupon status
async function toggleCouponStatus(couponId, isActive) {
    try {
        const response = await fetch(`/api/admin/coupons/${couponId}`, {
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
            showNotification('Coupon status updated successfully', 'success');
            loadCoupons(); // Refresh the list
        } else {
            showNotification('Failed to update coupon status: ' + result.error, 'error');
        }
    } catch (error) {
        console.error('Error updating coupon status:', error);
        showNotification('Failed to update coupon status', 'error');
    }
}

// View coupon details
async function viewCoupon(couponId) {
    try {
        const response = await fetch(`/api/admin/coupons/${couponId}`);
        const result = await response.json();
        
        if (result.success) {
            displayCouponModal(result.data);
        } else {
            showNotification('Failed to load coupon details: ' + result.error, 'error');
        }
    } catch (error) {
        console.error('Error loading coupon details:', error);
        showNotification('Failed to load coupon details', 'error');
    }
}

// Display coupon modal
function displayCouponModal(coupon) {
    const isExpired = coupon.expiry_date && new Date(coupon.expiry_date) < new Date();
    const isLimitReached = coupon.usage_limit && coupon.used_count >= coupon.usage_limit;
    const isActive = !isExpired && !isLimitReached && coupon.is_active;
    
    document.getElementById('modal-title').textContent = `Coupon: ${coupon.code}`;
    document.getElementById('modal-content').innerHTML = `
        <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Code</label>
                    <p class="text-sm text-gray-900 dark:text-white font-mono">${coupon.code}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                    <p class="text-sm text-gray-900 dark:text-white">${coupon.discount_type === 'percentage' ? 'Percentage' : 'Fixed Amount'}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Value</label>
                    <p class="text-sm text-gray-900 dark:text-white">
                        ${coupon.discount_type === 'percentage' ? 
                            `${parseFloat(coupon.discount_value || 0).toFixed(0)}%` : 
                            `৳${parseFloat(coupon.discount_value || 0).toFixed(2)}`
                        }
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Min Order Amount</label>
                    <p class="text-sm text-gray-900 dark:text-white">৳${parseFloat(coupon.min_order_amount).toFixed(2)}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Usage</label>
                    <p class="text-sm text-gray-900 dark:text-white">
                        ${coupon.used_count} / ${coupon.usage_limit || '∞'}
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Uses Per User</label>
                    <p class="text-sm text-gray-900 dark:text-white">${coupon.max_uses_per_user}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date</label>
                    <p class="text-sm text-gray-900 dark:text-white">${coupon.start_date ? new Date(coupon.start_date).toLocaleDateString() : 'No start date'}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Expiry Date</label>
                    <p class="text-sm text-gray-900 dark:text-white">${coupon.expiry_date ? new Date(coupon.expiry_date).toLocaleDateString() : 'No expiry'}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                    <p class="text-sm text-gray-900 dark:text-white">${isActive ? 'Active' : 'Inactive'}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Created</label>
                    <p class="text-sm text-gray-900 dark:text-white">${new Date(coupon.created_at).toLocaleDateString()}</p>
                </div>
            </div>
        </div>
    `;
    document.getElementById('coupon-modal').classList.remove('hidden');
}

// Close coupon modal
function closeCouponModal() {
    document.getElementById('coupon-modal').classList.add('hidden');
}

// Delete coupon
async function deleteCoupon(couponId) {
    if (!confirm('Are you sure you want to delete this coupon?')) {
        return;
    }
    
    try {
        const response = await fetch(`/api/admin/coupons/${couponId}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Coupon deleted successfully', 'success');
            loadCoupons(); // Refresh the list
        } else {
            showNotification('Failed to delete coupon: ' + result.error, 'error');
        }
    } catch (error) {
        console.error('Error deleting coupon:', error);
        showNotification('Failed to delete coupon', 'error');
    }
}

// Refresh coupons
function refreshCoupons() {
    loadCoupons();
}

// Open Add Coupon Modal
function openAddCouponModal() {
    document.getElementById('form-modal-title').textContent = 'Add Coupon';
    document.getElementById('coupon-form').reset();
    document.getElementById('coupon-id').value = '';
    document.getElementById('coupon-is-active').checked = true;
    updateValueLabel();
    document.getElementById('coupon-form-modal').classList.remove('hidden');
}

// Close Coupon Form Modal
function closeCouponFormModal() {
    document.getElementById('coupon-form-modal').classList.add('hidden');
    document.getElementById('coupon-form').reset();
}

// Update value label based on type
function updateValueLabel() {
    const type = document.getElementById('coupon-type').value;
    const prefix = document.getElementById('value-prefix');
    const label = document.getElementById('value-label');
    
    if (type === 'percentage') {
        prefix.textContent = '%';
        label.textContent = 'Discount Percentage';
    } else {
        prefix.textContent = '৳';
        label.textContent = 'Discount Amount';
    }
}

// Edit Coupon
async function editCoupon(couponId) {
    try {
        const response = await fetch(`/api/admin/coupons/${couponId}`);
        const result = await response.json();
        
        if (result.success && result.data) {
            const coupon = result.data;
            
            document.getElementById('form-modal-title').textContent = 'Edit Coupon';
            document.getElementById('coupon-id').value = coupon.id;
            document.getElementById('coupon-code').value = coupon.code;
            document.getElementById('coupon-type').value = coupon.discount_type;
            document.getElementById('coupon-value').value = coupon.discount_value;
            document.getElementById('coupon-min-order').value = coupon.min_order_amount || 0;
            document.getElementById('coupon-start-date').value = coupon.start_date || '';
            document.getElementById('coupon-expiry-date').value = coupon.expiry_date || '';
            document.getElementById('coupon-usage-limit').value = coupon.usage_limit || '';
            document.getElementById('coupon-max-uses').value = coupon.max_uses_per_user || 1;
            document.getElementById('coupon-is-active').checked = coupon.is_active;
            
            updateValueLabel();
            document.getElementById('coupon-form-modal').classList.remove('hidden');
        } else {
            showNotification('Failed to load coupon: ' + (result.error || 'Unknown error'), 'error');
        }
    } catch (error) {
        console.error('Error loading coupon:', error);
        showNotification('Failed to load coupon', 'error');
    }
}

// Handle Coupon Form Submit
async function handleCouponFormSubmit(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const couponId = document.getElementById('coupon-id').value;
    
    const data = {
        code: formData.get('code').toUpperCase().trim(),
        discount_type: formData.get('discount_type'),
        discount_value: parseFloat(formData.get('discount_value')),
        min_order_amount: parseFloat(formData.get('min_order_amount') || 0),
        start_date: formData.get('start_date') || null,
        expiry_date: formData.get('expiry_date') || null,
        usage_limit: formData.get('usage_limit') ? parseInt(formData.get('usage_limit')) : null,
        max_uses_per_user: parseInt(formData.get('max_uses_per_user') || 1),
        is_active: document.getElementById('coupon-is-active').checked
    };
    
    // Remove null/empty fields
    Object.keys(data).forEach(key => {
        if (data[key] === null || data[key] === '') {
            delete data[key];
        }
    });
    
    try {
        const url = couponId ? `/api/admin/coupons/${couponId}` : '/api/admin/coupons';
        const method = couponId ? 'PATCH' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message || (couponId ? 'Coupon updated successfully' : 'Coupon created successfully'), 'success');
            closeCouponFormModal();
            loadCoupons(); // Refresh the list
        } else {
            showNotification('Failed to save coupon: ' + (result.error || 'Unknown error'), 'error');
        }
    } catch (error) {
        console.error('Error saving coupon:', error);
        showNotification('Failed to save coupon', 'error');
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