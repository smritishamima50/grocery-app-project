<?php
$title = 'Shopping Cart - GroceryApp';
ob_start();
?>

<div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 py-8 px-4 animate-fade-in">
    <div class="max-w-7xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="/" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 transition-colors duration-200">
                        <i class="fas fa-home mr-2"></i>
                        Home
                    </a>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                        <span class="text-sm font-medium text-gray-500">Shopping Cart</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-4 animate-slide-up">
                <i class="fas fa-shopping-cart mr-3 text-blue-600"></i>
                Your Shopping Cart
            </h1>
            <p class="text-xl text-gray-600 animate-fade-in" style="animation-delay: 0.2s;">
                Review your items and proceed to checkout
            </p>
        </div>

        <?php if (empty($cartItems)): ?>
            <div class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/20 p-16 text-center animate-slide-up">
                <div class="w-32 h-32 bg-gradient-to-br from-gray-200 to-gray-300 rounded-full flex items-center justify-center mx-auto mb-8">
                    <i class="fas fa-shopping-cart text-gray-400 text-6xl"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Your cart is empty</h2>
                <p class="text-xl text-gray-600 mb-8">Add some delicious groceries to get started!</p>
                <a href="/products" class="btn-primary inline-block text-white px-8 py-4 rounded-xl font-bold text-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-105">
                    <i class="fas fa-shopping-bag mr-2"></i>
                    Start Shopping
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Cart Items -->
                <div class="lg:col-span-2">
                    <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-xl border border-white/20 animate-slide-up">
                        <div class="p-8 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                                    <i class="fas fa-shopping-basket mr-3 text-blue-600"></i>
                                    Cart Items
                                </h2>
                                <span class="bg-blue-100 text-blue-800 text-sm font-semibold px-3 py-1 rounded-full">
                                    <?php echo count($cartItems); ?> items
                                </span>
                            </div>
                        </div>
                        <div class="divide-y divide-gray-100">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="p-8 hover:bg-gray-50/50 transition-colors duration-200 animate-on-scroll">
                                    <div class="flex items-center space-x-6">
                                        <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center overflow-hidden shadow-lg">
                                            <?php if ($item['image']): ?>
                                                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <i class="fas fa-image text-gray-400 text-3xl"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="font-bold text-xl text-gray-900 mb-2"><?php echo htmlspecialchars($item['name']); ?></h3>
                                            <p class="text-green-600 font-semibold text-lg">৳<?php echo number_format($item['price'], 2); ?> per <?php echo htmlspecialchars($item['unit']); ?></p>
                                            
                                            <!-- Sodium Warning for Low Sodium Diet -->
                                            <?php if (isset($userDietProfile) && $userDietProfile && $userDietProfile['diet_goal'] === 'low_sodium' && isset($item['sodium_per_unit']) && $item['sodium_per_unit'] > 300): ?>
                                                <div class="mt-2 bg-red-100 border-l-4 border-red-500 text-red-700 p-3 rounded-lg flex items-start">
                                                    <i class="fas fa-exclamation-triangle mr-3 mt-1"></i>
                                                    <div class="flex-1">
                                                        <p class="font-semibold text-sm">High Sodium Warning</p>
                                                        <p class="text-xs mt-1">This item is high in sodium (<?php echo $item['sodium_per_unit']; ?>mg) and not in your low-sodium plan.</p>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <!-- Calorie Information -->
                                            <?php if (isset($item['calories_per_unit']) && $item['calories_per_unit']): ?>
                                                <div class="mt-2 text-sm text-gray-600 flex items-center">
                                                    <i class="fas fa-fire text-orange-500 mr-2"></i>
                                                    <span><?php echo ($item['calories_per_unit'] * $item['quantity']); ?> kcal total (<?php echo $item['calories_per_unit']; ?> kcal each)</span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="flex items-center mt-3">
                                                <button class="update-quantity bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-l-xl transition-colors duration-200 font-semibold" data-product-id="<?php echo $item['product_id']; ?>" data-action="decrease">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <span class="quantity-display bg-white border-t border-b border-gray-300 px-6 py-2 font-bold text-lg min-w-20 text-center"><?php echo $item['quantity']; ?></span>
                                                <button class="update-quantity bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-r-xl transition-colors duration-200 font-semibold" data-product-id="<?php echo $item['product_id']; ?>" data-action="increase">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-bold text-2xl text-gray-900 mb-4">৳<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                                            <button class="remove-item text-red-500 hover:text-red-700 transition-colors duration-200 p-2 rounded-lg hover:bg-red-50" data-product-id="<?php echo $item['product_id']; ?>" title="Remove item">
                                                <i class="fas fa-trash-alt text-lg"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-xl border border-white/20 p-8 sticky top-4 animate-slide-up" style="animation-delay: 0.2s;">
                        <div class="flex items-center mb-6">
                            <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center mr-4">
                                <i class="fas fa-receipt text-orange-600 text-xl"></i>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900">Order Summary</h2>
                        </div>

                        <!-- Calorie Recommendation -->
                        <?php if (isset($calorieRecommendation) && $calorieRecommendation): ?>
                            <div class="mb-6 p-4 rounded-xl border-2 <?php 
                                echo $calorieRecommendation['color'] === 'red' ? 'bg-red-50 border-red-200' : 
                                    ($calorieRecommendation['color'] === 'yellow' ? 'bg-yellow-50 border-yellow-200' : 
                                    ($calorieRecommendation['color'] === 'blue' ? 'bg-blue-50 border-blue-200' : 'bg-green-50 border-green-200')); 
                            ?>">
                                <div class="flex items-center mb-3">
                                    <i class="fas fa-<?php echo $calorieRecommendation['icon']; ?> text-<?php echo $calorieRecommendation['color']; ?>-600 text-xl mr-3"></i>
                                    <h3 class="font-bold text-<?php echo $calorieRecommendation['color']; ?>-800 text-lg">
                                        <?php echo $calorieRecommendation['message']; ?>
                                    </h3>
                                </div>
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-600">Cart Calories:</span>
                                        <span class="font-semibold text-<?php echo $calorieRecommendation['color']; ?>-700">
                                            <?php echo $calorieRecommendation['cart_calories']; ?> kcal
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Daily Target:</span>
                                        <span class="font-semibold text-gray-700">
                                            <?php echo $calorieRecommendation['daily_target']; ?> kcal
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Daily %:</span>
                                        <span class="font-semibold text-<?php echo $calorieRecommendation['color']; ?>-700">
                                            <?php echo $calorieRecommendation['daily_percentage']; ?>%
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Weekly %:</span>
                                        <span class="font-semibold text-<?php echo $calorieRecommendation['color']; ?>-700">
                                            <?php echo $calorieRecommendation['weekly_percentage']; ?>%
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Coupon Section -->
                        <div class="mb-6 p-4 bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl border border-purple-200">
                            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-tag text-purple-600 mr-2"></i>
                                Apply Coupon
                            </h3>
                            
                            <?php if ($appliedCoupon): ?>
                                <!-- Applied Coupon Display -->
                                <div class="bg-green-100 border border-green-300 rounded-lg p-4 mb-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <i class="fas fa-check-circle text-green-600 mr-3"></i>
                                            <div>
                                                <p class="font-semibold text-green-800"><?php echo htmlspecialchars($appliedCoupon['code']); ?></p>
                                                <p class="text-sm text-green-600">
                                                    <?php if ($appliedCoupon['discount_type'] === 'percentage'): ?>
                                                        <?php echo $appliedCoupon['discount_value']; ?>% OFF
                                                    <?php else: ?>
                                                        ৳<?php echo number_format($appliedCoupon['discount_value'], 2); ?> OFF
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                        </div>
                                        <button onclick="removeCoupon()" class="text-red-500 hover:text-red-700 p-2 rounded-lg hover:bg-red-50 transition-colors duration-200">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- Coupon Input Form -->
                                <div class="flex space-x-2">
                                    <input type="text" id="couponCode" placeholder="Enter coupon code" 
                                           class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    <button onclick="applyCoupon()" 
                                            class="bg-purple-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-purple-700 transition-colors duration-200">
                                        <i class="fas fa-check mr-1"></i>Apply
                                    </button>
                                </div>
                                <p class="text-sm text-gray-500 mt-2">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Copy coupon codes from our special discounts section
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="space-y-4 mb-6">
                            <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                <span class="text-gray-600 font-medium">Subtotal (<?php echo count($cartItems); ?> items)</span>
                                <span class="text-gray-900 font-semibold text-lg">৳<?php echo number_format($total, 2); ?></span>
                            </div>
                            
                            <?php if ($discount > 0): ?>
                                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                    <span class="text-green-600 font-medium">
                                        <i class="fas fa-tag mr-1"></i>
                                        Discount (<?php echo htmlspecialchars($appliedCoupon['code']); ?>)
                                    </span>
                                    <span class="text-green-600 font-semibold text-lg">-৳<?php echo number_format($discount, 2); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                <span class="text-gray-600 font-medium">Delivery Fee</span>
                                <span class="text-gray-900 font-semibold text-lg">৳50.00</span>
                            </div>
                            <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                <span class="text-gray-600 font-medium">Service Charge</span>
                                <span class="text-gray-900 font-semibold text-lg">৳10.00</span>
                            </div>
                            <hr class="border-gray-300">
                            <div class="flex justify-between items-center py-3">
                                <span class="text-gray-900 font-bold text-xl">Total Amount</span>
                                <span class="text-green-600 font-bold text-2xl">৳<?php echo number_format($finalTotal + 60, 2); ?></span>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <a href="/checkout" class="w-full flex justify-center py-4 px-6 border border-transparent rounded-xl shadow-lg text-lg font-bold text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-4 focus:ring-green-200 transform hover:scale-105 transition-all duration-300">
                                <i class="fas fa-credit-card mr-2"></i>
                                Proceed to Checkout
                            </a>

                            <a href="/subscriptions/create" class="w-full flex justify-center py-4 px-6 border border-transparent rounded-xl shadow-lg text-lg font-bold text-white bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200 transform hover:scale-105 transition-all duration-300">
                                <i class="fas fa-sync-alt mr-2"></i>
                                Subscribe for Regular Delivery
                            </a>

                            <a href="/products" class="w-full flex justify-center py-3 px-6 border-2 border-gray-300 rounded-xl text-lg font-semibold text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-200 transition-all duration-300">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Continue Shopping
                            </a>
                        </div>

                        <div class="mt-6 p-4 bg-blue-50 rounded-xl border border-blue-200">
                            <div class="flex items-start">
                                <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
                                <div>
                                    <p class="text-blue-800 font-semibold text-sm">Free Delivery</p>
                                    <p class="text-blue-600 text-xs mt-1">On orders above ৳500</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Surprise Gift Options - Small Box Below Order Summary -->
                <?php if (isset($_SESSION['user_id']) && !empty($surpriseGiftOptions)): ?>
                <div class="lg:col-span-1 mt-6">
                    <div class="bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl shadow-lg border border-yellow-200 p-6 animate-slide-up" style="animation-delay: 0.4s;">
                        <div class="text-center mb-4">
                            <div class="inline-flex items-center justify-center w-12 h-12 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full mb-3 animate-bounce">
                                <i class="fas fa-gift text-white text-lg"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-2">🎁 Choose Your Surprise Gift!</h3>
                            <p class="text-sm text-gray-600">Select one free gift</p>
                        </div>

                        <div class="space-y-3">
                            <?php foreach ($surpriseGiftOptions as $index => $option): ?>
                                <div class="bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition-all duration-300 cursor-pointer border-2 border-transparent hover:border-yellow-300 surprise-gift-option" 
                                     data-option="<?php echo $index; ?>"
                                     onclick="selectSurpriseGift(<?php echo $index; ?>)">
                                    
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-gray-200 rounded-lg flex items-center justify-center overflow-hidden mr-3">
                                                <?php if ($option['product_image']): ?>
                                                    <img src="<?php echo htmlspecialchars($option['product_image']); ?>" alt="<?php echo htmlspecialchars($option['product_name']); ?>" class="w-full h-full object-cover">
                                                <?php else: ?>
                                                    <i class="fas fa-image text-gray-400 text-xs"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-900 text-sm"><?php echo htmlspecialchars($option['name']); ?></p>
                                                <p class="text-xs text-gray-600"><?php echo htmlspecialchars($option['product_name']); ?></p>
                                            </div>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="inline-block bg-gradient-to-r from-green-500 to-green-600 text-white text-xs font-bold px-2 py-1 rounded-full mr-2">
                                                FREE!
                                            </span>
                                            <div class="w-6 h-6 bg-gray-200 rounded-full border-2 border-gray-300 surprise-gift-radio flex items-center justify-center">
                                                <div class="w-3 h-3 bg-yellow-500 rounded-full opacity-0 transition-opacity duration-200"></div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-md p-2">
                                        <p class="text-xs text-blue-700 text-center">
                                            <?php
                                            switch($option['trigger_type']) {
                                                case 'order_amount':
                                                    echo "Order ৳" . number_format($option['trigger_value'], 2) . "+";
                                                    break;
                                                case 'order_count':
                                                    echo "Make your " . $option['trigger_value'] . "th order";
                                                    break;
                                                case 'random':
                                                    echo "Random chance";
                                                    break;
                                                case 'special_occasion':
                                                    echo "Special occasions";
                                                    break;
                                                default:
                                                    echo "Order groceries";
                                            }
                                            ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="text-center mt-4">
                            <p class="text-xs text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i>
                                Select one gift per order
                            </p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Update cart count in navigation
function updateCartCount() {
    fetch('/cart/count')
        .then(response => response.json())
        .then(data => {
            const cartBadge = document.querySelector('.cart-badge');
            if (cartBadge) {
                if (data.count > 0) {
                    cartBadge.textContent = data.count;
                    cartBadge.classList.remove('hidden');
                } else {
                    cartBadge.classList.add('hidden');
                }
            }
        })
        .catch(error => console.error('Error updating cart count:', error));
}

// Update quantity without page reload
document.querySelectorAll('.update-quantity').forEach(button => {
    button.addEventListener('click', function() {
        console.log('🛒 ===== CART UPDATE CLICKED =====');
        
        const productId = this.getAttribute('data-product-id');
        const action = this.getAttribute('data-action');
        const quantityDisplay = this.parentElement.querySelector('.quantity-display');
        let currentQuantity = parseInt(quantityDisplay.textContent);

        console.log('🛒 Product ID:', productId);
        console.log('🛒 Action:', action);
        console.log('🛒 Current Quantity:', currentQuantity);

        if (action === 'increase') {
            currentQuantity++;
        } else if (action === 'decrease' && currentQuantity > 1) {
            currentQuantity--;
        }

        console.log('🛒 New Quantity:', currentQuantity);

        // Set loading state
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        console.log('🛒 Making request to: /cart/update');
        console.log('🛒 Request data:', 'product_id=' + productId + '&quantity=' + currentQuantity);

        fetch('/cart/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'product_id=' + productId + '&quantity=' + currentQuantity
        })
        .then(response => {
            console.log('🛒 Response received:', {
                status: response.status,
                statusText: response.statusText,
                ok: response.ok
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status} - ${response.statusText}`);
            }
            return response.text().then(text => {
                console.log('🛒 Raw response text:', text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('🛒 Failed to parse JSON response:', e);
                    throw new Error('Invalid JSON response: ' + text);
                }
            });
        })
        .then(data => {
            console.log('🛒 Parsed response data:', data);
            
            if (data.success) {
                console.log('✅ SUCCESS: Quantity updated successfully!');
                quantityDisplay.textContent = currentQuantity;
                updateCartTotals();
                updateCartCount();
                showToast('Quantity updated successfully', 'success');
            } else {
                console.error('❌ FAILED:', data.message || 'Failed to update quantity');
                showToast(data.message || 'Failed to update quantity', 'error');
            }
        })
        .catch(error => {
            console.error('🛒 ===== ERROR OCCURRED =====');
            console.error('🛒 Error type:', error.constructor.name);
            console.error('🛒 Error message:', error.message);
            console.error('🛒 Error stack:', error.stack);
            
            showToast('Error: ' + error.message, 'error');
        })
        .finally(() => {
            // Reset button state
            this.disabled = false;
            this.innerHTML = action === 'increase' ? '<i class="fas fa-plus"></i>' : '<i class="fas fa-minus"></i>';
        });
    });
});

// Remove item without page reload
document.querySelectorAll('.remove-item').forEach(button => {
    button.addEventListener('click', function() {
        console.log('🗑️ ===== CART REMOVE CLICKED =====');
        
        const productId = this.getAttribute('data-product-id');
        const cartItem = this.closest('[class*="p-8"]');

        console.log('🗑️ Product ID:', productId);

        if (confirm('Are you sure you want to remove this item from your cart?')) {
            // Set loading state
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            console.log('🗑️ Making request to: /cart/remove');
            console.log('🗑️ Request data:', 'product_id=' + productId);

            fetch('/cart/remove', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId
            })
            .then(response => {
                console.log('🗑️ Response received:', {
                    status: response.status,
                    statusText: response.statusText,
                    ok: response.ok
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status} - ${response.statusText}`);
                }
                return response.text().then(text => {
                    console.log('🗑️ Raw response text:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('🗑️ Failed to parse JSON response:', e);
                        throw new Error('Invalid JSON response: ' + text);
                    }
                });
            })
            .then(data => {
                console.log('🗑️ Parsed response data:', data);
                
                if (data.success) {
                    console.log('✅ SUCCESS: Item removed successfully!');
                    
                    // Animate removal
                    cartItem.style.transition = 'all 0.3s ease';
                    cartItem.style.opacity = '0';
                    cartItem.style.transform = 'translateX(-100%)';

                    setTimeout(() => {
                        cartItem.remove();
                        updateCartTotals();
                        updateCartCount();

                        // Check if cart is empty
                        const remainingItems = document.querySelectorAll('[class*="p-8"]');
                        if (remainingItems.length === 0) {
                            location.reload(); // Reload to show empty cart message
                        }
                    }, 300);

                    showToast('Item removed from cart', 'success');
                } else {
                    console.error('❌ FAILED:', data.message || 'Failed to remove item');
                    showToast(data.message || 'Failed to remove item', 'error');
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-trash-alt text-lg"></i>';
                }
            })
            .catch(error => {
                console.error('🗑️ ===== ERROR OCCURRED =====');
                console.error('🗑️ Error type:', error.constructor.name);
                console.error('🗑️ Error message:', error.message);
                console.error('🗑️ Error stack:', error.stack);
                
                showToast('Error: ' + error.message, 'error');
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-trash-alt text-lg"></i>';
            });
        }
    });
});

// Update cart totals without page reload
function updateCartTotals() {
    fetch('/cart/totals')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update subtotal
                const subtotalElement = document.querySelector('.space-y-4 .py-3:first-child span:last-child');
                if (subtotalElement) {
                    subtotalElement.textContent = '৳' + data.subtotal.toFixed(2);
                }

                // Update total
                const totalElement = document.querySelector('.py-3:last-child span:last-child');
                if (totalElement) {
                    totalElement.textContent = '৳' + (data.subtotal + 60).toFixed(2);
                }

                // Update item count
                const itemCountElement = document.querySelector('.bg-blue-100');
                if (itemCountElement) {
                    itemCountElement.textContent = data.itemCount + ' items';
                }

                const subtotalText = document.querySelector('.space-y-4 .py-3:first-child span:first-child');
                if (subtotalText) {
                    subtotalText.textContent = 'Subtotal (' + data.itemCount + ' items)';
                }
            }
        })
        .catch(error => console.error('Error updating totals:', error));
}

// Initialize cart count on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
});

// Apply coupon function
function applyCoupon() {
    const couponCode = document.getElementById('couponCode').value.trim();
    
    if (!couponCode) {
        showToast('Please enter a coupon code', 'error');
        return;
    }

    const applyButton = document.querySelector('button[onclick="applyCoupon()"]');
    const originalText = applyButton.innerHTML;
    
    // Set loading state
    applyButton.disabled = true;
    applyButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Applying...';

    fetch('/cart/apply-coupon', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'coupon_code=' + encodeURIComponent(couponCode)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            // Reload page to show updated totals
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Coupon apply error:', error);
        showToast('Error applying coupon', 'error');
    })
    .finally(() => {
        // Reset button state
        applyButton.disabled = false;
        applyButton.innerHTML = originalText;
    });
}

// Remove coupon function
function removeCoupon() {
    const removeButton = document.querySelector('button[onclick="removeCoupon()"]');
    const originalText = removeButton.innerHTML;
    
    // Set loading state
    removeButton.disabled = true;
    removeButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch('/cart/remove-coupon', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            // Reload page to show updated totals
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Coupon remove error:', error);
        showToast('Error removing coupon', 'error');
    })
    .finally(() => {
        // Reset button state
        removeButton.disabled = false;
        removeButton.innerHTML = originalText;
    });
}

// Allow Enter key to apply coupon
document.addEventListener('DOMContentLoaded', function() {
    const couponInput = document.getElementById('couponCode');
    if (couponInput) {
        couponInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyCoupon();
            }
        });
    }
});

// Surprise Gift Selection
let selectedSurpriseGift = null;

function selectSurpriseGift(optionIndex) {
    // Remove previous selection
    document.querySelectorAll('.surprise-gift-option').forEach(option => {
        option.classList.remove('border-yellow-400', 'bg-yellow-50');
        option.classList.add('border-transparent');
        option.querySelector('.surprise-gift-radio').classList.remove('border-yellow-500', 'bg-yellow-100');
        option.querySelector('.surprise-gift-radio').classList.add('border-gray-300', 'bg-gray-200');
        option.querySelector('.surprise-gift-radio div').classList.add('opacity-0');
    });
    
    // Select new option
    const selectedOption = document.querySelector(`[data-option="${optionIndex}"]`);
    selectedOption.classList.remove('border-transparent');
    selectedOption.classList.add('border-yellow-400', 'bg-yellow-50');
    selectedOption.querySelector('.surprise-gift-radio').classList.remove('border-gray-300', 'bg-gray-200');
    selectedOption.querySelector('.surprise-gift-radio').classList.add('border-yellow-500', 'bg-yellow-100');
    selectedOption.querySelector('.surprise-gift-radio div').classList.remove('opacity-0');
    
    selectedSurpriseGift = optionIndex;
    
    // Show success message
    showSurpriseGiftMessage(optionIndex);
}

function showSurpriseGiftMessage(optionIndex) {
    // Create or update success message
    let messageDiv = document.getElementById('surprise-gift-message');
    if (!messageDiv) {
        messageDiv = document.createElement('div');
        messageDiv.id = 'surprise-gift-message';
        messageDiv.className = 'fixed top-4 right-4 bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-4 rounded-xl shadow-2xl z-50 animate-slide-in-right';
        document.body.appendChild(messageDiv);
    }
    
    messageDiv.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-gift text-2xl mr-3"></i>
            <div>
                <p class="font-bold text-lg">🎁 You unlocked a surprise gift!</p>
                <p class="text-sm opacity-90">Free gift will be added to your order</p>
            </div>
        </div>
    `;
    
    // Auto-hide after 3 seconds
    setTimeout(() => {
        messageDiv.style.opacity = '0';
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.parentNode.removeChild(messageDiv);
            }
        }, 300);
    }, 3000);
}

// Update checkout button to include surprise gift selection
document.addEventListener('DOMContentLoaded', function() {
    const checkoutBtn = document.querySelector('a[href="/checkout"]');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function(e) {
            if (selectedSurpriseGift !== null) {
                // Store selected surprise gift in sessionStorage
                sessionStorage.setItem('selectedSurpriseGift', selectedSurpriseGift);
            }
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include 'app/views/layouts/main.php';
?>