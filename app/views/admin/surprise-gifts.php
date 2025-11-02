<?php
$title = 'Manage Surprise Gifts - Admin Panel';
ob_start();
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-xl p-6 mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    <i class="fas fa-gift text-yellow-600 mr-3"></i>
                    Surprise Gift Management
                </h1>
                <p class="text-gray-600">Manage surprise gifts and promotional items</p>
            </div>
            <button onclick="showAddGiftModal()" class="bg-green-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-green-700 transition-colors duration-200">
                <i class="fas fa-plus mr-2"></i>
                Add New Gift
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-gift text-blue-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Gifts</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo count($surpriseGifts); ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Active Gifts</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo count(array_filter($surpriseGifts, function($gift) { return $gift['is_active']; })); ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-users text-yellow-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Uses</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo array_sum(array_column($surpriseGifts, 'current_uses')); ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Success Rate</p>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $successRate; ?>%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Gifts Table -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-900">All Surprise Gifts</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gift</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trigger</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Probability</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uses</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($surpriseGifts as $gift): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                                    <i class="fas fa-gift text-yellow-600"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($gift['name']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($gift['product_name']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                <?php echo ucfirst(str_replace('_', ' ', $gift['trigger_type'])); ?>
                                <?php if ($gift['trigger_value'] > 0): ?>
                                    <br><span class="text-xs text-gray-500">Min: ৳<?php echo number_format($gift['trigger_value'], 2); ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo $gift['probability_percentage']; ?>%"></div>
                                </div>
                                <span class="text-sm text-gray-900"><?php echo $gift['probability_percentage']; ?>%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                <?php echo $gift['current_uses']; ?>
                                <?php if ($gift['max_total_uses']): ?>
                                    / <?php echo $gift['max_total_uses']; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php echo $gift['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo $gift['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <button onclick="editGift(<?php echo $gift['id']; ?>)" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="toggleGiftStatus(<?php echo $gift['id']; ?>, <?php echo $gift['is_active'] ? 'false' : 'true'; ?>)" class="text-yellow-600 hover:text-yellow-900">
                                    <i class="fas fa-power-off"></i>
                                </button>
                                <button onclick="deleteGift(<?php echo $gift['id']; ?>)" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Gift Modal -->
<div id="giftModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900" id="modalTitle">Add New Gift</h3>
                <button onclick="hideGiftModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="giftForm" method="POST" action="/admin/surprise-gifts/save">
                <input type="hidden" id="giftId" name="gift_id">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Gift Name</label>
                        <input type="text" name="name" id="giftName" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" id="giftDescription" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Product</label>
                        <select name="product_id" id="giftProduct" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select a product</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?> - ৳<?php echo number_format($product['price'], 2); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                        <input type="number" name="quantity" id="giftQuantity" value="1" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Trigger Type</label>
                        <select name="trigger_type" id="giftTriggerType" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="random">Random</option>
                            <option value="order_amount">Order Amount</option>
                            <option value="order_count">Order Count</option>
                            <option value="special_occasion">Special Occasion</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Trigger Value</label>
                        <input type="number" name="trigger_value" id="giftTriggerValue" value="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Probability (%)</label>
                        <input type="number" name="probability_percentage" id="giftProbability" value="10" min="1" max="100" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="giftActive" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="giftActive" class="ml-2 block text-sm text-gray-900">Active</label>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="hideGiftModal()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors duration-200">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        Save Gift
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showAddGiftModal() {
    document.getElementById('modalTitle').textContent = 'Add New Gift';
    document.getElementById('giftForm').reset();
    document.getElementById('giftId').value = '';
    document.getElementById('giftModal').classList.remove('hidden');
}

function hideGiftModal() {
    document.getElementById('giftModal').classList.add('hidden');
}

function editGift(giftId) {
    // Implementation for editing gift
    console.log('Edit gift:', giftId);
}

function toggleGiftStatus(giftId, newStatus) {
    // Implementation for toggling gift status
    console.log('Toggle gift status:', giftId, newStatus);
}

function deleteGift(giftId) {
    if (confirm('Are you sure you want to delete this gift?')) {
        // Implementation for deleting gift
        console.log('Delete gift:', giftId);
    }
}
</script>

<?php
$content = ob_get_clean();
include 'app/views/admin/layout.php';
?>
