<?php
$currentPage = 'drivers';
$pageTitle = 'Drivers Management';
include 'app/views/admin/layout.php';
?>

<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Drivers Management</h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Manage delivery drivers and their information</p>
                </div>
                <div class="flex items-center space-x-4">
                    <button type="button" onclick="openAddDriverModal()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                        <i class="fas fa-plus"></i>
                        <span>Add Driver</span>
                    </button>
                    <button type="button" onclick="openBulkImportModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                        <i class="fas fa-upload"></i>
                        <span>Bulk Import</span>
                    </button>
                    <button type="button" onclick="loadDrivers()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                        <i class="fas fa-sync-alt"></i>
                        <span>Refresh</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user-check text-green-600 dark:text-green-400"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Drivers</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white" id="active-drivers-count">-</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-motorcycle text-blue-600 dark:text-blue-400"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Bike Drivers</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white" id="bike-drivers-count">-</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                            <i class="fas fa-car text-purple-600 dark:text-purple-400"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Car/Van Drivers</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white" id="car-drivers-count">-</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Drivers List -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">All Drivers</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">View and manage all delivery drivers</p>
            </div>
            
            <div class="p-6">
                <div id="drivers-list" class="space-y-3">
                    <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                        <i class="fas fa-spinner fa-spin text-3xl mb-3"></i>
                        <p>Loading drivers...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Driver Modal -->
<div id="addDriverModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Add New Driver</h3>
                <button onclick="closeAddDriverModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="add-driver-form" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Driver Name *</label>
                        <input type="text" id="driver-name" name="name" required 
                               class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="Enter driver name">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone *</label>
                        <input type="text" id="driver-phone" name="phone" required 
                               class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="+8801712345678">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
                        <input type="email" id="driver-email" name="email" 
                               class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="driver@example.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Vehicle Type *</label>
                        <select id="driver-vehicle" name="vehicle_type" 
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="motorcycle">Motorcycle</option>
                            <option value="car">Car</option>
                            <option value="van">Van</option>
                            <option value="truck">Truck</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">License Number</label>
                    <input type="text" id="driver-license" name="license_number" 
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="Optional">
                </div>
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" onclick="closeAddDriverModal()" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg flex items-center space-x-2">
                        <i class="fas fa-plus"></i>
                        <span>Add Driver</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Import Modal -->
<div id="bulkImportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Bulk Import Drivers</h3>
                <button onclick="closeBulkImportModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                <h4 class="text-sm font-semibold text-blue-800 dark:text-blue-200 mb-2">
                    <i class="fas fa-info-circle mr-1"></i> JSON Format Example:
                </h4>
                <pre class="text-xs bg-white dark:bg-gray-900 p-3 rounded overflow-x-auto"><code>{
  "drivers": [
    {
      "first_name": "John",
      "last_name": "Doe",
      "phone": "+8801712345678",
      "email": "john.doe@example.com",
      "vehicle_type": "motorcycle",
      "vehicle_number": "ABC-123",
      "license_number": "DL123456",
      "status": "active",
      "availability_status": "available",
      "joining_date": "2024-01-01"
    },
    {
      "first_name": "Jane",
      "last_name": "Smith",
      "phone": "+8801712345679",
      "email": "jane.smith@example.com",
      "vehicle_type": "car",
      "vehicle_number": "XYZ-789",
      "license_number": "DL789012",
      "status": "active",
      "availability_status": "available"
    }
  ]
}</code></pre>
            </div>
            
            <form id="bulk-import-form" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fas fa-file-code mr-1"></i>
                        Paste JSON Data *
                    </label>
                    <textarea id="bulk-import-json" name="json_data" required rows="15"
                              class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent font-mono text-sm"
                              placeholder="Paste JSON data here..."></textarea>
                </div>
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" onclick="closeBulkImportModal()" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg flex items-center space-x-2">
                        <i class="fas fa-upload"></i>
                        <span>Import Drivers</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Prevent multiple simultaneous loads
let isLoadingDrivers = false;

// Load drivers on page load
document.addEventListener('DOMContentLoaded', function() {
    loadDrivers();
    
    // Attach form submit handlers
    const form = document.getElementById('add-driver-form');
    if (form) {
        form.addEventListener('submit', addDriver);
    }
    
    const bulkImportForm = document.getElementById('bulk-import-form');
    if (bulkImportForm) {
        bulkImportForm.addEventListener('submit', bulkImportDrivers);
    }
});

// Modal functions
function openAddDriverModal() {
    const modal = document.getElementById('addDriverModal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}

function closeAddDriverModal() {
    const modal = document.getElementById('addDriverModal');
    if (modal) {
        modal.classList.add('hidden');
        document.getElementById('add-driver-form')?.reset();
    }
}

// Bulk import modal functions
function openBulkImportModal() {
    const modal = document.getElementById('bulkImportModal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}

function closeBulkImportModal() {
    const modal = document.getElementById('bulkImportModal');
    if (modal) {
        modal.classList.add('hidden');
        document.getElementById('bulk-import-form')?.reset();
    }
}

// Load drivers list
async function loadDrivers() {
    // Prevent multiple simultaneous calls
    if (isLoadingDrivers) {
        console.log('⏳ Already loading drivers, skipping...');
        return;
    }
    
    const driversList = document.getElementById('drivers-list');
    if (!driversList) {
        console.error('❌ Drivers list container not found');
        return;
    }
    
    isLoadingDrivers = true;
    driversList.innerHTML = '<div class="text-center text-gray-500 dark:text-gray-400 py-8"><i class="fas fa-spinner fa-spin text-3xl mb-3"></i><p>Loading drivers...</p></div>';
    
    try {
        console.log('🔄 Fetching drivers from API...');
        const response = await fetch('/api/admin/drivers', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            }
        });
        
        console.log('📥 Response status:', response.status, response.statusText);
        const responseText = await response.text();
        console.log('📥 Response length:', responseText.length);
        console.log('📥 Response preview:', responseText.substring(0, 200));
        
        if (!response.ok) {
            let errorMessage = 'Failed to load drivers';
            let errorDetails = null;
            try {
                const errorData = JSON.parse(responseText);
                errorMessage = errorData.error || errorMessage;
                errorDetails = errorData;
                console.error('❌ API Error Response:', errorData);
            } catch (e) {
                if (responseText.trim().startsWith('<')) {
                    errorMessage = 'Server returned HTML instead of JSON. Please check server logs.';
                } else if (responseText.length > 0) {
                    errorMessage = 'Error: ' + responseText.substring(0, 100);
                }
                console.error('❌ Failed to parse error response:', e);
            }
            
            const errorDisplay = errorDetails && errorDetails.error_details 
                ? `${errorMessage}<br><small class="text-xs opacity-75">${escapeHtml(errorDetails.error_details)}</small>`
                : errorMessage;
                
            driversList.innerHTML = `<div class="text-center text-red-500 py-4"><p>${errorDisplay}</p></div>`;
            showNotification('Failed to load drivers: ' + errorMessage, 'error');
            return;
        }
        
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
            driversList.innerHTML = `<div class="text-center text-red-500 py-4"><p>${escapeHtml(errorMsg)}</p></div>`;
            showNotification('Error loading drivers: ' + errorMsg, 'error');
            return;
        }
        
        if (result.success) {
            const drivers = Array.isArray(result.drivers) ? result.drivers : [];
            
            console.log(`✅ Received ${drivers.length} driver(s) from API`);
            if (drivers.length > 0) {
                console.log('📋 First driver sample:', drivers[0]);
            }
            
            // Update statistics
            updateStatistics(drivers);
            
            if (drivers.length === 0) {
                driversList.innerHTML = `
                    <div class="text-center text-gray-500 dark:text-gray-400 py-12">
                        <i class="fas fa-user-tie text-4xl mb-4 opacity-50"></i>
                        <p class="text-lg font-medium mb-2">No drivers found</p>
                        <p class="text-sm mb-4">Add a driver to get started</p>
                        <button onclick="openAddDriverModal()" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg">
                            <i class="fas fa-plus mr-2"></i>Add First Driver
                        </button>
                    </div>
                `;
            } else {
                // Display all drivers with complete information
                const driversHTML = drivers.map((driver, index) => {
                    try {
                        // Extract all driver data safely
                        const driverId = parseInt(driver.id) || 0;
                        const driverName = escapeHtml(String(driver.name || 'Unknown'));
                        const driverPhone = escapeHtml(String(driver.phone || 'N/A'));
                        const driverEmail = driver.email ? escapeHtml(String(driver.email)) : null;
                        const vehicleType = escapeHtml(String(driver.vehicle_type || 'N/A'));
                        const licenseNumber = driver.license_number ? escapeHtml(String(driver.license_number)) : null;
                        const createdAt = driver.created_at ? formatDate(driver.created_at) : 'N/A';
                        const updatedAt = driver.updated_at ? formatDate(driver.updated_at) : null;
                        const isActive = driver.is_active !== undefined ? driver.is_active : true;
                        
                        // Vehicle icon mapping
                        let vehicleIcon = 'fa-car';
                        let vehicleColor = 'text-blue-600 dark:text-blue-400';
                        const vehicleTypeLower = vehicleType.toLowerCase();
                        if (vehicleTypeLower === 'bike' || vehicleTypeLower === 'motorcycle') {
                            vehicleIcon = 'fa-motorcycle';
                            vehicleColor = 'text-green-600 dark:text-green-400';
                        } else if (vehicleTypeLower === 'van' || vehicleTypeLower === 'truck') {
                            vehicleIcon = 'fa-truck';
                            vehicleColor = 'text-orange-600 dark:text-orange-400';
                        }
                        
                        // Format vehicle type for display
                        const vehicleTypeDisplay = vehicleType.charAt(0).toUpperCase() + vehicleType.slice(1).toLowerCase();
                        
                        return `
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600 hover:shadow-md transition-all mb-3">
                                <div class="flex items-start space-x-4 flex-1">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                                            <i class="fas ${vehicleIcon} ${vehicleColor} text-lg"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center space-x-2 mb-2">
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">${driverName}</h3>
                                            ${isActive ? '<span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded-full">Active</span>' : '<span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 rounded-full">Inactive</span>'}
                                        </div>
                                        <div class="mt-2 space-y-1">
                                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                                <i class="fas fa-phone mr-2 text-blue-500"></i><strong>Phone:</strong> ${driverPhone}
                                            </div>
                                            ${driverEmail ? `
                                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                                <i class="fas fa-envelope mr-2 text-blue-500"></i><strong>Email:</strong> ${driverEmail}
                                            </div>
                                            ` : ''}
                                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                                <i class="fas fa-car mr-2 text-purple-500"></i><strong>Vehicle Type:</strong> <span class="font-medium">${vehicleTypeDisplay}</span>
                                            </div>
                                            ${licenseNumber ? `
                                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                                <i class="fas fa-id-card mr-2 text-indigo-500"></i><strong>License:</strong> ${licenseNumber}
                                            </div>
                                            ` : ''}
                                            <div class="text-xs text-gray-500 dark:text-gray-500 mt-2 flex items-center space-x-3">
                                                <span><i class="fas fa-calendar-plus mr-1"></i>Added: ${createdAt}</span>
                                                ${updatedAt ? `<span><i class="fas fa-calendar-edit mr-1"></i>Updated: ${updatedAt}</span>` : ''}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2 ml-4">
                                    <button onclick="viewDriverOrders(${driverId}, '${driverName.replace(/'/g, "\\'")}')" 
                                            class="px-3 py-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                                            title="View assigned orders">
                                        <i class="fas fa-list"></i>
                                    </button>
                                    <button onclick="deleteDriver(${driverId}, '${driverName.replace(/'/g, "\\'")}')" 
                                            class="px-3 py-2 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                            title="Delete driver">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                    } catch (error) {
                        console.error(`Error rendering driver ${index}:`, error, driver);
                        return `<div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800"><p class="text-red-600 dark:text-red-400">Error displaying driver: ${escapeHtml(error.message)}</p></div>`;
                    }
                }).join('');
                
                driversList.innerHTML = driversHTML;
                console.log(`✅ Successfully loaded and displayed ${drivers.length} driver(s)`);
                
                // Log driver details for debugging
                drivers.forEach((driver, index) => {
                    console.log(`Driver ${index + 1}:`, {
                        id: driver.id,
                        name: driver.name,
                        phone: driver.phone,
                        email: driver.email,
                        vehicle_type: driver.vehicle_type,
                        license_number: driver.license_number,
                        is_active: driver.is_active,
                        created_at: driver.created_at
                    });
                });
            }
        } else {
            const errorMsg = result.error || 'Failed to load drivers';
            driversList.innerHTML = `<div class="text-center text-red-500 py-4"><p><i class="fas fa-exclamation-circle mr-2"></i>${escapeHtml(errorMsg)}</p></div>`;
            showNotification('Failed to load drivers: ' + errorMsg, 'error');
        }
    } catch (error) {
        console.error('Error loading drivers:', error);
        if (driversList) {
            driversList.innerHTML = `<div class="text-center text-red-500 py-4"><p>Network error: ${escapeHtml(error.message || 'Unknown error')}</p></div>`;
        }
        showNotification('Error loading drivers: ' + error.message, 'error');
    } finally {
        isLoadingDrivers = false;
    }
}

// Update statistics
function updateStatistics(drivers) {
    try {
        if (!Array.isArray(drivers)) {
            console.warn('⚠️ Drivers is not an array:', drivers);
            return;
        }
        
        const activeCount = drivers.length;
        const motorcycleCount = drivers.filter(d => d.vehicle_type === 'motorcycle' || d.vehicle_type === 'bike').length;
        const carVanCount = drivers.filter(d => d.vehicle_type === 'car' || d.vehicle_type === 'van' || d.vehicle_type === 'truck').length;
        
        const activeCountEl = document.getElementById('active-drivers-count');
        const bikeCountEl = document.getElementById('bike-drivers-count');
        const carCountEl = document.getElementById('car-drivers-count');
        
        if (activeCountEl) activeCountEl.textContent = activeCount;
        if (bikeCountEl) bikeCountEl.textContent = motorcycleCount;
        if (carCountEl) carCountEl.textContent = carVanCount;
        
        console.log(`📊 Statistics updated: ${activeCount} active, ${motorcycleCount} motorcycle/bike, ${carVanCount} car/van/truck`);
    } catch (error) {
        console.error('Error updating statistics:', error);
    }
}

// Add new driver
async function addDriver(event) {
    if (event) {
        event.preventDefault();
    }
    
    const form = document.getElementById('add-driver-form');
    if (!form) return;
    
    const formData = new FormData(form);
    const driverData = {
        name: formData.get('name')?.trim() || '',
        phone: formData.get('phone')?.trim() || '',
        email: formData.get('email')?.trim() || null,
        vehicle_type: formData.get('vehicle_type') || 'motorcycle',
        license_number: formData.get('license_number')?.trim() || null
    };
    
    if (!driverData.name || !driverData.phone) {
        showNotification('Driver name and phone are required', 'error');
        return;
    }
    
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalHTML = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Adding...</span>';
    
    try {
        const response = await fetch('/api/admin/drivers', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify(driverData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('✅ Driver added successfully!', 'success');
            form.reset();
            closeAddDriverModal();
            loadDrivers();
        } else {
            showNotification('❌ Failed to add driver: ' + (result.error || 'Unknown error'), 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHTML;
        }
    } catch (error) {
        console.error('Error adding driver:', error);
        showNotification('❌ Error adding driver: ' + error.message, 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHTML;
    }
}

// Delete driver
async function deleteDriver(driverId, driverName) {
    if (!confirm(`Are you sure you want to delete driver "${driverName}"?\n\nThis will unassign them from all orders.`)) {
        return;
    }
    
    try {
        const response = await fetch('/api/admin/drivers', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ id: driverId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('✅ Driver deleted successfully!', 'success');
            loadDrivers();
        } else {
            showNotification('❌ Failed to delete driver: ' + (result.error || 'Unknown error'), 'error');
        }
    } catch (error) {
        console.error('Error deleting driver:', error);
        showNotification('❌ Error deleting driver: ' + error.message, 'error');
    }
}

// View driver orders
function viewDriverOrders(driverId, driverName) {
    window.location.href = `/admin/orders?driver=${encodeURIComponent(driverName)}`;
}

// Bulk import drivers
async function bulkImportDrivers(event) {
    event.preventDefault();
    
    const form = document.getElementById('bulk-import-form');
    if (!form) return;
    
    const jsonData = document.getElementById('bulk-import-json').value.trim();
    
    if (!jsonData) {
        showNotification('Please paste JSON data', 'error');
        return;
    }
    
    let parsedData;
    try {
        parsedData = JSON.parse(jsonData);
    } catch (error) {
        showNotification('Invalid JSON format: ' + error.message, 'error');
        return;
    }
    
    if (!parsedData.drivers || !Array.isArray(parsedData.drivers)) {
        showNotification('JSON must contain a "drivers" array', 'error');
        return;
    }
    
    if (parsedData.drivers.length === 0) {
        showNotification('No drivers to import', 'error');
        return;
    }
    
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalHTML = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Importing...</span>';
    
    try {
        const response = await fetch('/api/admin/drivers', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify(parsedData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            const message = result.imported 
                ? `✅ Imported ${result.imported} driver(s) successfully!`
                : result.message || 'Import successful';
            showNotification(message, 'success');
            
            if (result.errors && result.errors.length > 0) {
                console.warn('Import errors:', result.errors);
                showNotification(`⚠️ Import completed with ${result.errors.length} error(s). Check console for details.`, 'warning');
            }
            
            form.reset();
            closeBulkImportModal();
            loadDrivers();
        } else {
            showNotification('❌ Failed to import: ' + (result.error || 'Unknown error'), 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHTML;
        }
    } catch (error) {
        console.error('Error importing drivers:', error);
        showNotification('❌ Error importing: ' + error.message, 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHTML;
    }
}

// Helper functions
function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const div = document.createElement('div');
    div.textContent = String(text);
    return div.innerHTML;
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return dateString;
        return date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (error) {
        return dateString;
    }
}

function showNotification(message, type = 'info') {
    // Use existing notification system if available (but not this function itself)
    const existingNotification = window.showNotification;
    if (existingNotification && existingNotification !== showNotification && typeof existingNotification === 'function') {
        try {
            existingNotification(message, type);
            return;
        } catch (e) {
            console.warn('Error calling existing notification:', e);
            // Fall through to fallback
        }
    }
    
    // Fallback notification - create unique ID to avoid duplicates
    const notificationId = 'driver-notification-' + Date.now();
    
    // Remove any existing notifications first
    const existing = document.querySelectorAll('.driver-notification');
    existing.forEach(n => n.remove());
    
    const notification = document.createElement('div');
    notification.id = notificationId;
    notification.className = `driver-notification fixed top-4 right-4 px-4 py-3 rounded-lg shadow-lg z-[9999] animate-slide-in-right ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        type === 'warning' ? 'bg-yellow-500 text-white' :
        'bg-blue-500 text-white'
    }`;
    
    notification.innerHTML = `
        <div class="flex items-center space-x-2">
            <span>${escapeHtml(message)}</span>
            <button onclick="document.getElementById('${notificationId}').remove()" class="ml-2 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        const notif = document.getElementById(notificationId);
        if (notif) {
            notif.style.opacity = '0';
            notif.style.transition = 'opacity 0.3s';
            setTimeout(() => {
                if (notif.parentElement) {
                    notif.remove();
                }
            }, 300);
        }
    }, 3000);
}
</script>

<style>
.animate-slide-in-right {
    animation: slide-in-right 0.3s ease-out;
}

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
</style>

