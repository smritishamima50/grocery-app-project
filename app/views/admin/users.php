<?php
$pageTitle = 'Users Management';
$currentPage = 'users';

// Get admin data
$adminMiddleware = new AdminMiddleware();
$adminData = $adminMiddleware->getAdminData();
$adminFullName = $adminMiddleware->getAdminFullName();
$adminInitials = $adminMiddleware->getAdminInitials();

ob_start();
?>

<!-- Enhanced Users Management Page -->
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Customer Management</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Full visibility and control over customer accounts, behavior, and preferences</p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <button onclick="exportUsers()" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-download mr-2"></i>Export
            </button>
            <a href="/admin/users/create" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>Add User
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                    <i class="fas fa-users text-blue-600 dark:text-blue-400 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Users</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white" id="totalUsers">-</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 dark:bg-green-900/20 rounded-lg">
                    <i class="fas fa-crown text-green-600 dark:text-green-400 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">VIP Customers</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white" id="vipUsers">-</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 dark:bg-yellow-900/20 rounded-lg">
                    <i class="fas fa-star text-yellow-600 dark:text-yellow-400 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">High Priority</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white" id="priorityUsers">-</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 dark:bg-purple-900/20 rounded-lg">
                    <i class="fas fa-gift text-purple-600 dark:text-purple-400 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Subscriptions</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white" id="activeSubscriptions">-</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                <input type="text" id="searchInput" placeholder="Search by name, email, or phone..." 
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Role</label>
                <select id="roleFilter" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                    <option value="">All Roles</option>
                    <option value="customer">Customer</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                <select id="statusFilter" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="vip">VIP</option>
                    <option value="high_priority">High Priority</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sort By</label>
                <select id="sortBy" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                    <option value="created_at">Join Date</option>
                    <option value="loyalty_points">Loyalty Points</option>
                    <option value="total_orders">Total Orders</option>
                    <option value="last_order_date">Last Order</option>
                    <option value="total_spent">Total Spent</option>
                </select>
            </div>
        </div>
        
        <div class="mt-4 flex justify-between items-center">
            <div class="flex space-x-2">
                <button onclick="applyFilters()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-search mr-2"></i>Apply Filters
                </button>
                <button onclick="clearFilters()" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                    <i class="fas fa-times mr-2"></i>Clear
                </button>
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                <span id="resultsCount">Loading...</span>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Orders & Loyalty</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Diet Profile</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <!-- Dynamic content will be loaded here -->
                </tbody>
            </table>
        </div>
        
        <!-- Loading State -->
        <div id="loadingState" class="px-6 py-12 text-center">
            <div class="inline-flex items-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <span class="ml-3 text-gray-600 dark:text-gray-400">Loading users...</span>
            </div>
        </div>
        
        <!-- Empty State -->
        <div id="emptyState" class="px-6 py-12 text-center hidden">
            <i class="fas fa-users text-4xl text-gray-400 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No users found</h3>
            <p class="text-gray-600 dark:text-gray-400">Try adjusting your search criteria or filters.</p>
        </div>
    </div>

    <!-- Pagination -->
    <div id="paginationContainer" class="flex items-center justify-between">
        <!-- Dynamic pagination will be loaded here -->
    </div>
</div>

<!-- User Detail Modal -->
<div id="userDetailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-6xl shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Customer Profile</h3>
                <button onclick="closeUserModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Modal Content -->
            <div id="userDetailContent" class="mt-6">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions Modal -->
<div id="quickActionsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Quick Actions</h3>
                <button onclick="closeQuickActionsModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div id="quickActionsContent" class="mt-4 space-y-3">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
// Global variables
let currentPage = 1;
let currentFilters = {};
let selectedUserId = null;

// Helper functions
function getInitials(firstName, lastName) {
    const first = (firstName || '').charAt(0).toUpperCase();
    const last = (lastName || '').charAt(0).toUpperCase();
    return (first + last) || 'U';
}

function getDietFlagIcon(flag) {
    const icons = {
        'vegetarian': '<i class="fas fa-leaf"></i>',
        'vegan': '<i class="fas fa-seedling"></i>',
        'diabetic-friendly': '<i class="fas fa-heartbeat"></i>',
        'low-sodium': '<i class="fas fa-tint-slash"></i>',
        'keto': '<i class="fas fa-fire"></i>',
        'paleo': '<i class="fas fa-drumstick-bite"></i>',
        'mediterranean': '<i class="fas fa-fish"></i>',
        'heart-healthy': '<i class="fas fa-heart"></i>',
        'low-carb': '<i class="fas fa-bread-slice"></i>',
        'high-protein': '<i class="fas fa-dumbbell"></i>'
    };
    return icons[flag] || '<i class="fas fa-utensils"></i>';
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadUsers();
    setupEventListeners();
});

// Setup event listeners
function setupEventListeners() {
    // Search input with debounce
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            loadUsers();
        }, 500);
    });
    
    // Filter changes
    ['roleFilter', 'statusFilter', 'sortBy'].forEach(id => {
        document.getElementById(id).addEventListener('change', function() {
            currentPage = 1;
            loadUsers();
        });
    });
}

// Load users with current filters
async function loadUsers() {
    const loadingState = document.getElementById('loadingState');
    const emptyState = document.getElementById('emptyState');
    const tableBody = document.getElementById('usersTableBody');
    
    // Show loading state
    loadingState.classList.remove('hidden');
    emptyState.classList.add('hidden');
    tableBody.innerHTML = '';
    
    // Build query parameters
    const params = new URLSearchParams({
        page: currentPage,
        limit: 20,
        search: document.getElementById('searchInput').value,
        role: document.getElementById('roleFilter').value,
        status: document.getElementById('statusFilter').value,
        sort: document.getElementById('sortBy').value,
        order: 'desc'
    });
    
    try {
        console.log('🔄 Loading users...', { page: currentPage, filters: Object.fromEntries(params) });
        
        const response = await fetch(`/api/admin/users?${params}`, {
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
            let errorMessage = 'Failed to load users';
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
            
            showError(errorMessage);
            loadingState.classList.add('hidden');
            return;
        }
        
        // Parse JSON response
        let data;
        try {
            data = JSON.parse(responseText);
            console.log('✅ Parsed JSON successfully:', data);
        } catch (e) {
            console.error('❌ JSON parse error:', e);
            console.error('❌ Response text that failed to parse:', responseText);
            const errorMsg = responseText.trim().startsWith('<') 
                ? 'Server returned HTML instead of JSON. Check server logs for PHP errors.'
                : 'Invalid JSON response from server';
            showError(errorMsg);
            loadingState.classList.add('hidden');
            return;
        }
        
        if (data.success) {
            console.log('✅ Success! Users count:', data.data?.length || 0);
            displayUsers(data.data || []);
            updatePagination(data.pagination || { current_page: 1, total: 0, total_pages: 1 });
            
            // Update statistics - use overall stats from API if available
            if (data.statistics) {
                updateStatistics(data.statistics);
            } else {
                // Fallback: calculate from current page data
                updateStatisticsFromData(data.data || []);
            }
            
            document.getElementById('resultsCount').textContent = 
                `Showing ${data.data.length} of ${data.pagination.total} users`;
        } else {
            console.error('❌ API returned success=false:', data);
            showError('Failed to load users: ' + (data.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('❌ Error loading users:', error);
        console.error('❌ Error stack:', error.stack);
        showError('Failed to load users: ' + (error.message || 'Network error'));
    } finally {
        loadingState.classList.add('hidden');
    }
}

// Display users in table
function displayUsers(users) {
    const tableBody = document.getElementById('usersTableBody');
    const emptyState = document.getElementById('emptyState');
    
    if (users.length === 0) {
        emptyState.classList.remove('hidden');
        return;
    }
    
    emptyState.classList.add('hidden');
    
    tableBody.innerHTML = users.map(user => {
        const dietFlags = user.diet_flags || [];
        const statusIndicators = user.status_indicators || {};
        
        return `
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-r from-blue-400 to-purple-500 rounded-full flex items-center justify-center text-white font-bold text-lg">
                        ${getInitials(user.first_name || '', user.last_name || '')}
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                            ${(user.first_name || '')} ${(user.last_name || '')}
                            ${statusIndicators.is_vip ? '<i class="fas fa-crown text-yellow-500 ml-1" title="VIP Customer"></i>' : ''}
                            ${statusIndicators.is_high_priority ? '<i class="fas fa-star text-orange-500 ml-1" title="High Priority"></i>' : ''}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            ID: #${user.id} • ${user.role || 'customer'}
                        </div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white">${user.email || ''}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">${user.phone || 'No phone'}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 dark:text-white">
                    <div class="flex items-center">
                        <i class="fas fa-shopping-bag text-gray-400 mr-2"></i>
                        ${user.total_orders || 0} orders
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        <i class="fas fa-coins text-yellow-500 mr-1"></i>
                        ${user.loyalty_points || 0} points
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        Last: ${user.last_order_date_formatted || user.last_order_date || 'Never'}
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex flex-wrap gap-1">
                    ${dietFlags.map(flag => `
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300">
                            ${getDietFlagIcon(flag)} ${flag}
                        </span>
                    `).join('')}
                    ${dietFlags.length === 0 ? '<span class="text-gray-400 text-xs">No diet profile</span>' : ''}
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex flex-col space-y-1">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${statusIndicators.account_active !== false ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300'}">
                        ${statusIndicators.account_active !== false ? 'Active' : 'Inactive'}
                    </span>
                    ${statusIndicators.has_admin_notes ? '<span class="text-xs text-blue-600 dark:text-blue-400"><i class="fas fa-sticky-note mr-1"></i>Has notes</span>' : ''}
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                    <button onclick="viewUserDetail(${user.id})" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300" title="View Profile">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button onclick="openQuickActions(${user.id})" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300" title="Quick Actions">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>
            </td>
        </tr>
    `;
    }).join('');
}

// View user detail
async function viewUserDetail(userId) {
    selectedUserId = userId;
    const modal = document.getElementById('userDetailModal');
    const content = document.getElementById('userDetailContent');
    
    content.innerHTML = '<div class="text-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div><p class="mt-2 text-gray-600 dark:text-gray-400">Loading user details...</p></div>';
    modal.classList.remove('hidden');
    
    try {
        console.log('🔄 Loading user details for:', userId);
        
        const response = await fetch(`/api/admin/users/${userId}`, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            }
        });
        
        console.log('📥 Response status:', response.status);
        
        // Get response text first
        const responseText = await response.text();
        
        // Check if response is OK
        if (!response.ok) {
            let errorMessage = 'Failed to load user details';
            try {
                const errorData = JSON.parse(responseText);
                errorMessage = errorData.error || errorMessage;
            } catch (e) {
                if (responseText.trim().startsWith('<')) {
                    errorMessage = 'Server returned HTML instead of JSON. Check server logs.';
                }
            }
            content.innerHTML = `<div class="text-center py-8 text-red-600">${errorMessage}</div>`;
            return;
        }
        
        // Parse JSON
        let data;
        try {
            data = JSON.parse(responseText);
            console.log('✅ User details loaded successfully');
        } catch (e) {
            console.error('❌ JSON parse error:', e);
            content.innerHTML = '<div class="text-center py-8 text-red-600">Invalid response from server</div>';
            return;
        }
        
        if (data.success && data.data) {
            displayUserDetail(data.data);
        } else {
            content.innerHTML = '<div class="text-center py-8 text-red-600">Failed to load user details: ' + (data.error || 'Unknown error') + '</div>';
        }
    } catch (error) {
        console.error('❌ Error loading user details:', error);
        content.innerHTML = '<div class="text-center py-8 text-red-600">Failed to load user details: ' + error.message + '</div>';
    }
}

// Display user detail in modal
function displayUserDetail(data) {
    const { user, recent_orders, subscriptions, order_stats, addresses, loyalty_info, diet_flags, audit_log, status_indicators } = data;
    
    document.getElementById('userDetailContent').innerHTML = `
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Basic Info -->
            <div class="lg:col-span-1 space-y-6">
                <!-- User Card -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                    <div class="text-center">
                        <div class="w-20 h-20 bg-gradient-to-r from-blue-400 to-purple-500 rounded-full flex items-center justify-center text-white font-bold text-2xl mx-auto mb-4">
                            ${getInitials(user.first_name, user.last_name)}
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">${user.first_name || ''} ${user.last_name || ''}</h2>
                        <p class="text-gray-600 dark:text-gray-400">${user.email || ''}</p>
                        <p class="text-gray-600 dark:text-gray-400">${user.phone || 'No phone'}</p>
                        <p class="text-gray-600 dark:text-gray-400 text-sm">Preferred Language: ${user.preferred_language || 'en'}</p>
                        <div class="mt-4 flex justify-center flex-wrap gap-2">
                            ${status_indicators && status_indicators.is_vip ? '<span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs font-medium"><i class="fas fa-crown mr-1"></i>VIP</span>' : ''}
                            ${status_indicators && status_indicators.is_high_priority ? '<span class="bg-orange-100 text-orange-800 px-2 py-1 rounded-full text-xs font-medium"><i class="fas fa-star mr-1"></i>High Priority</span>' : ''}
                            <span class="bg-${status_indicators && status_indicators.account_active ? 'green' : 'red'}-100 text-${status_indicators && status_indicators.account_active ? 'green' : 'red'}-800 px-2 py-1 rounded-full text-xs font-medium">
                                <i class="fas fa-${status_indicators && status_indicators.account_active ? 'check' : 'ban'}-circle mr-1"></i>${status_indicators && status_indicators.account_active ? 'Active' : 'Inactive'}
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Loyalty Info -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Loyalty & Rewards</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Points:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">${loyalty_info.loyalty_points}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Tier:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">${loyalty_info.tier}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Gifts Unlocked:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">${loyalty_info.surprise_gifts_unlocked}</span>
                        </div>
                        ${loyalty_info.next_tier_points ? `
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Next Tier:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">${loyalty_info.next_tier_points} points</span>
                        </div>
                        ` : ''}
                    </div>
                </div>
                
                <!-- Diet Profile -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Diet Profile</h3>
                    <div class="space-y-3">
                        <div class="flex flex-wrap gap-2">
                            ${(diet_flags || []).map(flag => `
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300">
                                    ${getDietFlagIcon(flag)} ${flag}
                                </span>
                            `).join('')}
                            ${(!diet_flags || diet_flags.length === 0) ? '<span class="text-gray-400 text-sm">No diet profile set</span>' : ''}
                        </div>
                        ${user.diet_profile && user.diet_profile.goal ? `
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Goal:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">${user.diet_profile.goal.replace(/_/g, ' ')}</span>
                        </div>
                        ` : ''}
                        ${user.diet_profile && user.diet_profile.calorie_target ? `
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Calorie Target:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">${user.diet_profile.calorie_target} kcal/day</span>
                        </div>
                        ` : ''}
                        ${user.diet_profile && user.diet_profile.allergies && user.diet_profile.allergies.length > 0 ? `
                        <div class="mt-2">
                            <span class="text-gray-600 dark:text-gray-400 text-sm">Allergies:</span>
                            <div class="flex flex-wrap gap-1 mt-1">
                                ${user.diet_profile.allergies.map(allergy => `
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>${allergy}
                                    </span>
                                `).join('')}
                            </div>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>
            
            <!-- Right Column - Activity & Orders -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Order Statistics -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Order Statistics</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">${order_stats.total_orders || 0}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Total Orders</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">৳${(order_stats.total_spent || 0).toLocaleString()}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Total Spent</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">৳${(order_stats.avg_order_value || 0).toFixed(0)}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Avg Order</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">${order_stats.completed_orders || 0}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Completed</div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Orders -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Orders</h3>
                        <a href="/admin/orders?user_id=${user.id}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm">
                            <i class="fas fa-external-link-alt mr-1"></i>View All Orders
                        </a>
                    </div>
                    <div class="space-y-3">
                        ${(recent_orders && recent_orders.length > 0) ? recent_orders.map(order => `
                            <div class="flex justify-between items-center p-3 bg-white dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors cursor-pointer" onclick="window.location.href='/admin/orders/${order.id}'">
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">Order #${order.id}</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">${order.created_at_formatted || order.created_at}</div>
                                    ${order.item_count ? `<div class="text-xs text-gray-500 dark:text-gray-400">${order.item_count} item(s)</div>` : ''}
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold text-gray-900 dark:text-white">৳${parseFloat(order.total_amount || 0).toFixed(2)}</div>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${order.status_badge ? order.status_badge.class : 'bg-gray-100 text-gray-800'}">
                                        ${order.status_badge ? order.status_badge.text : (order.status || 'Unknown')}
                                    </span>
                                </div>
                            </div>
                        `).join('') : '<p class="text-gray-500 dark:text-gray-400 text-center py-4">No recent orders</p>'}
                    </div>
                </div>
                
                <!-- Active Subscriptions -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Active Subscriptions</h3>
                        <span class="text-sm text-gray-600 dark:text-gray-400">${(subscriptions || []).length} active</span>
                    </div>
                    <div class="space-y-3">
                        ${(subscriptions && subscriptions.length > 0) ? subscriptions.map(sub => `
                            <div class="p-3 bg-white dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-white">${sub.frequency_display || (sub.frequency ? sub.frequency.replace(/_/g, ' ').charAt(0).toUpperCase() + sub.frequency.replace(/_/g, ' ').slice(1) : 'Subscription')}</div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">৳${parseFloat(sub.amount || 0).toFixed(2)} • Next: ${sub.next_delivery_formatted || 'Not scheduled'}</div>
                                        ${sub.address_line1 ? `<div class="text-xs text-gray-500 dark:text-gray-400 mt-1">${sub.address_line1}, ${sub.city || ''}</div>` : ''}
                                    </div>
                                    <span class="bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300 px-2 py-1 rounded-full text-xs font-medium">Active</span>
                                </div>
                            </div>
                        `).join('') : '<p class="text-gray-500 dark:text-gray-400 text-center py-4">No active subscriptions</p>'}
                    </div>
                </div>
                
                <!-- Basic Information (if not shown elsewhere) -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Additional Information</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600 dark:text-gray-400">Preferred Language:</span>
                            <span class="font-medium text-gray-900 dark:text-white ml-2">${user.preferred_language || 'en'}</span>
                        </div>
                        <div>
                            <span class="text-gray-600 dark:text-gray-400">Member Since:</span>
                            <span class="font-medium text-gray-900 dark:text-white ml-2">${user.created_at ? new Date(user.created_at).toLocaleDateString() : 'N/A'}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Admin Notes Section -->
        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Admin Notes</h3>
            <div class="space-y-3">
                <textarea id="admin-notes-textarea" rows="4" 
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                          placeholder="Add internal notes about this customer (e.g., 'VIP', 'Always requests no plastic', 'Allergic to peanuts')...">${user.admin_notes || ''}</textarea>
                <div class="flex justify-end">
                    <button onclick="saveAdminNotes(${user.id})" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-save mr-2"></i>Save Notes
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Admin Actions -->
        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Admin Actions</h3>
            <div class="flex flex-wrap gap-3">
                <button onclick="toggleAccountStatus(${user.id}, ${user.account_active})" class="bg-${user.account_active ? 'red' : 'green'}-600 text-white px-4 py-2 rounded-lg hover:bg-${user.account_active ? 'red' : 'green'}-700 transition-colors">
                    <i class="fas fa-${user.account_active ? 'ban' : 'check'}-circle mr-2"></i>${user.account_active ? 'Deactivate Account' : 'Activate Account'}
                </button>
                <button onclick="toggleVipStatus(${user.id}, ${user.is_vip})" class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 transition-colors">
                    <i class="fas fa-crown mr-2"></i>${user.is_vip ? 'Remove VIP' : 'Make VIP'}
                </button>
                <button onclick="togglePriorityStatus(${user.id}, ${user.is_high_priority})" class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition-colors">
                    <i class="fas fa-star mr-2"></i>${user.is_high_priority ? 'Remove Priority' : 'Set High Priority'}
                </button>
                <button onclick="adjustLoyaltyPoints(${user.id})" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                    <i class="fas fa-coins mr-2"></i>Adjust Points
                </button>
            </div>
        </div>
        
        <!-- Audit Log Section -->
        ${audit_log && audit_log.length > 0 ? `
        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Admin Actions</h3>
            <div class="space-y-2 max-h-60 overflow-y-auto">
                ${audit_log.map(log => `
                    <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg text-sm">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">${log.action_type.replace(/_/g, ' ')}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">By ${log.admin_name} • ${log.created_at_formatted}</div>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
        ` : ''}
    `;
}

// Quick actions
function openQuickActions(userId) {
    selectedUserId = userId;
    const modal = document.getElementById('quickActionsModal');
    const content = document.getElementById('quickActionsContent');
    
    content.innerHTML = `
        <button onclick="viewUserDetail(${userId})" class="w-full text-left px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
            <i class="fas fa-eye mr-3"></i>View Profile
        </button>
        <button onclick="toggleAccountStatus(${userId})" class="w-full text-left px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
            <i class="fas fa-user-slash mr-3"></i>Toggle Account Status
        </button>
        <button onclick="toggleVipStatus(${userId})" class="w-full text-left px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
            <i class="fas fa-crown mr-3"></i>Toggle VIP Status
        </button>
        <button onclick="adjustLoyaltyPoints(${userId})" class="w-full text-left px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
            <i class="fas fa-coins mr-3"></i>Adjust Loyalty Points
        </button>
        <button onclick="addAdminNotes(${userId})" class="w-full text-left px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
            <i class="fas fa-sticky-note mr-3"></i>Add Admin Notes
        </button>
    `;
    
    modal.classList.remove('hidden');
}

// Modal controls
function closeUserModal() {
    document.getElementById('userDetailModal').classList.add('hidden');
}

function closeQuickActionsModal() {
    document.getElementById('quickActionsModal').classList.add('hidden');
}

// Filter functions
function applyFilters() {
    currentPage = 1;
    loadUsers();
}

function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('roleFilter').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('sortBy').value = 'created_at';
    currentPage = 1;
    loadUsers();
}

// Pagination
function updatePagination(pagination) {
    const container = document.getElementById('paginationContainer');
    const { current_page, total_pages, total } = pagination;
    
    if (total_pages <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let paginationHTML = `
        <div class="text-sm text-gray-700 dark:text-gray-300">
            Showing page ${current_page} of ${total_pages} (${total} total users)
        </div>
        <div class="flex space-x-2">
    `;
    
    // Previous button
    if (current_page > 1) {
        paginationHTML += `<button onclick="changePage(${current_page - 1})" class="px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">Previous</button>`;
    }
    
    // Page numbers
    const startPage = Math.max(1, current_page - 2);
    const endPage = Math.min(total_pages, current_page + 2);
    
    for (let i = startPage; i <= endPage; i++) {
        paginationHTML += `
            <button onclick="changePage(${i})" class="px-3 py-2 text-sm rounded-lg ${i === current_page ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'}">
                ${i}
            </button>
        `;
    }
    
    // Next button
    if (current_page < total_pages) {
        paginationHTML += `<button onclick="changePage(${current_page + 1})" class="px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">Next</button>`;
    }
    
    paginationHTML += '</div>';
    container.innerHTML = paginationHTML;
}

function changePage(page) {
    currentPage = page;
    loadUsers();
}

// Statistics update - use overall stats from API if available
function updateStatistics(stats) {
    if (stats.total_users !== undefined) {
        // API provided overall statistics
        document.getElementById('totalUsers').textContent = stats.total_users || 0;
        document.getElementById('vipUsers').textContent = stats.vip_users || 0;
        document.getElementById('priorityUsers').textContent = stats.high_priority_users || 0;
        document.getElementById('activeSubscriptions').textContent = stats.active_subscriptions || 0;
    } else {
        // Fallback: calculate from data
        updateStatisticsFromData(stats);
    }
}

// Statistics update from data array (fallback)
function updateStatisticsFromData(users) {
    const totalUsers = users.length;
    const vipUsers = users.filter(u => u.is_vip || u.status_indicators?.is_vip).length;
    const priorityUsers = users.filter(u => u.is_high_priority || u.status_indicators?.is_high_priority).length;
    const activeSubscriptions = users.reduce((sum, u) => sum + (u.active_subscriptions || 0), 0);
    
    document.getElementById('totalUsers').textContent = totalUsers;
    document.getElementById('vipUsers').textContent = vipUsers;
    document.getElementById('priorityUsers').textContent = priorityUsers;
    document.getElementById('activeSubscriptions').textContent = activeSubscriptions;
}

// Admin actions
async function toggleAccountStatus(userId, currentStatus) {
    if (!confirm(`Are you sure you want to ${currentStatus ? 'deactivate' : 'activate'} this account?`)) {
        return;
    }
    
    try {
        const response = await fetch(`/api/admin/users/${userId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                account_active: !currentStatus
            })
        });
        
        const responseText = await response.text();
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (e) {
            console.error('Failed to parse response:', responseText);
            showError('Invalid response from server');
            return;
        }
        
        if (data.success) {
            showSuccess(`Account ${!currentStatus ? 'activated' : 'deactivated'} successfully`);
            loadUsers(); // Refresh the table
            if (selectedUserId === userId) {
                // Reload user detail if modal is open
                viewUserDetail(userId);
            }
        } else {
            showError('Failed to update account status: ' + (data.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error updating account status:', error);
        showError('Failed to update account status. Please try again.');
    }
}

async function toggleVipStatus(userId, currentStatus) {
    if (!confirm(`Are you sure you want to ${currentStatus ? 'remove VIP status from' : 'make VIP'} this customer?`)) {
        return;
    }
    
    try {
        const response = await fetch(`/api/admin/users/${userId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                is_vip: !currentStatus
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess(`VIP status ${!currentStatus ? 'granted' : 'removed'} successfully`);
            loadUsers(); // Refresh the table
            if (selectedUserId === userId) {
                // Reload user detail if modal is open
                viewUserDetail(userId);
            }
        } else {
            showError('Failed to update VIP status: ' + (data.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error updating VIP status:', error);
        showError('Failed to update VIP status. Please try again.');
    }
}

async function togglePriorityStatus(userId, currentStatus) {
    if (!confirm(`Are you sure you want to ${currentStatus ? 'remove high priority status from' : 'set high priority for'} this customer?`)) {
        return;
    }
    
    try {
        const response = await fetch(`/api/admin/users/${userId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                is_high_priority: !currentStatus
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess(`Priority status ${!currentStatus ? 'set' : 'removed'} successfully`);
            loadUsers(); // Refresh the table
            if (selectedUserId === userId) {
                // Reload user detail if modal is open
                viewUserDetail(userId);
            }
        } else {
            showError('Failed to update priority status: ' + (data.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error updating priority status:', error);
        showError('Failed to update priority status. Please try again.');
    }
}

function adjustLoyaltyPoints(userId) {
    const currentPoints = prompt('Enter new loyalty points amount:');
    
    if (currentPoints === null) return; // User cancelled
    
    const points = parseInt(currentPoints);
    
    if (isNaN(points) || points < 0) {
        showError('Please enter a valid number of points (0 or greater)');
        return;
    }
    
    if (!confirm(`Are you sure you want to set loyalty points to ${points}?`)) {
        return;
    }
    
    updateLoyaltyPoints(userId, points);
}

async function updateLoyaltyPoints(userId, points) {
    try {
        const response = await fetch(`/api/admin/users/${userId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                loyalty_points: points
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess('Loyalty points updated successfully');
            loadUsers(); // Refresh the table
            if (selectedUserId === userId) {
                // Reload user detail if modal is open
                viewUserDetail(userId);
            }
        } else {
            showError('Failed to update loyalty points: ' + (data.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error updating loyalty points:', error);
        showError('Failed to update loyalty points. Please try again.');
    }
}

async function saveAdminNotes(userId) {
    const textarea = document.getElementById('admin-notes-textarea');
    const notes = textarea.value.trim();
    
    if (!confirm('Are you sure you want to update admin notes?')) {
        return;
    }
    
    try {
        const response = await fetch(`/api/admin/users/${userId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                admin_notes: notes
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess('Admin notes updated successfully');
            // Reload user detail to show updated notes
            viewUserDetail(userId);
        } else {
            showError('Failed to update admin notes: ' + (data.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error updating admin notes:', error);
        showError('Failed to update admin notes. Please try again.');
    }
}

function addAdminNotes(userId) {
    // Open the user detail modal - admin notes section is there
    viewUserDetail(userId);
}

async function exportUsers() {
    try {
        const response = await fetch('/api/admin/users?export=csv&limit=10000');
        
        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `users_export_${new Date().toISOString().split('T')[0]}.csv`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            showSuccess('Users exported successfully');
        } else {
            showError('Failed to export users');
        }
    } catch (error) {
        console.error('Error exporting users:', error);
        showError('Failed to export users. Please try again.');
    }
}

function showError(message) {
    // Enhanced notification system
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 bg-red-500 text-white max-w-md';
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-3"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-red-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

function showSuccess(message) {
    // Enhanced notification system
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 bg-green-500 text-white max-w-md';
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-3"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-green-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 3000);
}
</script>

<?php
$content = ob_get_clean();
include 'app/views/admin/layout.php';
?>
