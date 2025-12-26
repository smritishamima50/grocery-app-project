<?php
$title = htmlspecialchars($product['name']) . ' - GroceryApp';
ob_start();
?>

<div class="max-w-7xl mx-auto px-4 py-8 animate-fade-in">
    <!-- Breadcrumb -->
    <nav class="flex mb-8" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="/" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-green-600 transition-colors duration-200">
                    <i class="fas fa-home mr-2"></i>
                    Home
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <a href="/products" class="text-sm font-medium text-gray-700 hover:text-green-600 transition-colors duration-200">Products</a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <span class="text-sm font-medium text-gray-500"><?php echo htmlspecialchars($product['name']); ?></span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <!-- Product Images -->
        <div class="space-y-4">
            <div class="aspect-square bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl overflow-hidden shadow-xl">
                <?php if ($product['image']): ?>
                    <img src="<?php echo htmlspecialchars($product['image']); ?>"
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
                <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center">
                        <i class="fas fa-image text-gray-400 text-8xl"></i>
                    </div>
                <?php endif; ?>
            </div>
            <!-- Additional images could go here -->
        </div>

        <!-- Product Information -->
        <div class="space-y-6">
            <div>
                <div class="flex items-center space-x-2 mb-2">
                    <span class="bg-green-100 text-green-800 text-sm font-semibold px-3 py-1 rounded-full">
                        <?php echo htmlspecialchars($product['category_name'] ?? 'Product'); ?>
                    </span>
                    <?php if (isset($product['is_eco_friendly']) && $product['is_eco_friendly']): ?>
                        <span class="bg-green-500 text-white text-sm font-bold px-4 py-2 rounded-full flex items-center shadow-lg animate-pulse">
                            <i class="fas fa-leaf mr-2"></i>
                            Eco
                        </span>
                    <?php endif; ?>
                    <div class="flex text-yellow-400">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                        <span class="text-gray-600 text-sm ml-2">(4.5)</span>
                    </div>
                </div>
                <h1 class="text-4xl font-bold text-gray-900 mb-4"><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <!-- Stock Quantity Display -->
                <?php if (isset($product['stock_quantity']) && $product['stock_quantity'] > 0): ?>
                    <?php 
                    $stockQuantity = $product['stock_quantity'];
                    $unit = $product['unit'] ?? 'pcs';
                    $isLowStock = $stockQuantity <= 10;
                    $stockClass = $isLowStock ? 'text-red-600 bg-red-50' : 'text-orange-600 bg-orange-50';
                    ?>
                    <div class="mb-4">
                        <div class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium <?php echo $stockClass; ?>">
                            <i class="fas fa-box mr-2"></i>
                            Only <?php echo $stockQuantity; ?> <?php echo htmlspecialchars($unit); ?> left in stock
                        </div>
                    </div>
                <?php elseif (isset($product['stock_quantity']) && $product['stock_quantity'] == 0): ?>
                    <div class="mb-4">
                        <div class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium text-red-600 bg-red-100">
                            <i class="fas fa-times-circle mr-2"></i>
                            Out of Stock
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="flex items-center space-x-4 mb-6">
                    <span class="text-4xl font-bold text-green-600">৳<?php echo number_format($product['price'], 2); ?></span>
                    <span class="text-lg text-gray-500">per <?php echo htmlspecialchars($product['unit']); ?></span>
                </div>
            </div>

            <!-- Description -->
            <div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">Description</h3>
                <p class="text-gray-600 leading-relaxed"><?php echo htmlspecialchars($product['description']); ?></p>
            </div>

            <!-- Nutrition Info -->
            <?php if (isset($product['category_name']) && strtolower(trim($product['category_name'])) !== 'home cleaning'): ?>
            <div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">Nutrition Information</h3>
                <div class="bg-gradient-to-br from-green-50 to-blue-50 border border-green-200 rounded-xl p-6">
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <?php if ($product['calories_per_unit']): ?>
                            <div class="bg-white rounded-lg p-3 shadow-sm">
                                <div class="text-2xl font-bold text-orange-600"><?php echo $product['calories_per_unit']; ?></div>
                                <div class="text-sm text-gray-600">Calories (kcal)</div>
                            </div>
                        <?php endif; ?>
                        <?php if ($product['protein_per_unit']): ?>
                            <div class="bg-white rounded-lg p-3 shadow-sm">
                                <div class="text-2xl font-bold text-blue-600"><?php echo $product['protein_per_unit']; ?>g</div>
                                <div class="text-sm text-gray-600">Protein</div>
                            </div>
                        <?php endif; ?>
                        <?php if ($product['carbs_per_unit']): ?>
                            <div class="bg-white rounded-lg p-3 shadow-sm">
                                <div class="text-2xl font-bold text-green-600"><?php echo $product['carbs_per_unit']; ?>g</div>
                                <div class="text-sm text-gray-600">Carbs</div>
                            </div>
                        <?php endif; ?>
                        <?php if ($product['fat_per_unit']): ?>
                            <div class="bg-white rounded-lg p-3 shadow-sm">
                                <div class="text-2xl font-bold text-yellow-600"><?php echo $product['fat_per_unit']; ?>g</div>
                                <div class="text-sm text-gray-600">Fat</div>
                            </div>
                        <?php endif; ?>
                        <?php if ($product['fiber_per_unit']): ?>
                            <div class="bg-white rounded-lg p-3 shadow-sm">
                                <div class="text-2xl font-bold text-purple-600"><?php echo $product['fiber_per_unit']; ?>g</div>
                                <div class="text-sm text-gray-600">Fiber</div>
                            </div>
                        <?php endif; ?>
                        <?php if ($product['sodium_per_unit']): ?>
                            <div class="bg-white rounded-lg p-3 shadow-sm">
                                <div class="text-2xl font-bold text-red-600"><?php echo $product['sodium_per_unit']; ?>mg</div>
                                <div class="text-sm text-gray-600">Sodium</div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Diet Compatibility Badges -->
                    <div class="mt-4 flex flex-wrap gap-2">
                        <?php if ($product['is_vegetarian']): ?>
                            <span class="bg-green-100 text-green-800 text-xs font-semibold px-3 py-1 rounded-full">
                                <i class="fas fa-leaf mr-1"></i>Vegetarian
                            </span>
                        <?php endif; ?>
                        <?php if ($product['is_diabetes_friendly']): ?>
                            <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1 rounded-full">
                                <i class="fas fa-check-circle mr-1"></i>Diabetes Friendly
                            </span>
                        <?php endif; ?>
                        <?php if ($product['is_weight_loss_friendly']): ?>
                            <span class="bg-orange-100 text-orange-800 text-xs font-semibold px-3 py-1 rounded-full">
                                <i class="fas fa-running mr-1"></i>Weight Loss
                            </span>
                        <?php endif; ?>
                        <?php if ($product['is_muscle_gain_friendly']): ?>
                            <span class="bg-purple-100 text-purple-800 text-xs font-semibold px-3 py-1 rounded-full">
                                <i class="fas fa-dumbbell mr-1"></i>Muscle Gain
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Diet Recommendation Badge -->
            <?php if (isset($_SESSION['user_id']) && isset($isRecommendedForDiet) && $isRecommendedForDiet && isset($product['category_name']) && strtolower(trim($product['category_name'])) !== 'home cleaning'): ?>
                <div class="bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-xl p-4 flex items-center space-x-3 animate-pulse">
                    <i class="fas fa-star text-yellow-300 text-2xl"></i>
                    <div>
                        <div class="font-bold">Recommended for Your Diet</div>
                        <div class="text-sm opacity-90">This product fits your dietary goals perfectly!</div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Quantity Selector -->
            <div class="space-y-4">
                <div class="flex items-center space-x-4">
                    <label class="text-lg font-semibold text-gray-900">Quantity:</label>
                    <div class="flex items-center border border-gray-300 rounded-lg">
                        <button class="px-3 py-2 text-gray-600 hover:text-gray-800 hover:bg-gray-50 decrease-quantity">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>"
                               class="w-16 text-center border-0 focus:ring-0 quantity-input">
                        <button class="px-3 py-2 text-gray-600 hover:text-gray-800 hover:bg-gray-50 increase-quantity">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <span class="text-sm text-gray-500"><?php echo htmlspecialchars($product['unit']); ?></span>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <button class="flex-1 btn-primary text-white py-4 rounded-xl font-bold text-lg add-to-cart-main"
                            data-product-id="<?php echo $product['id']; ?>"
                            data-original-text="<i class='fas fa-cart-plus mr-2'></i>Add to Cart">
                        <i class="fas fa-cart-plus mr-2"></i>Add to Cart
                    </button>
                    <button class="flex-1 bg-orange-500 hover:bg-orange-600 text-white py-4 rounded-xl font-bold text-lg transition-all duration-300 transform hover:scale-105 hover:shadow-lg buy-now"
                            data-product-id="<?php echo $product['id']; ?>">
                        <i class="fas fa-bolt mr-2"></i>Buy Now
                    </button>
                </div>

                <!-- Additional Actions -->
                <div class="flex items-center space-x-4 pt-4 border-t border-gray-200">
                    <button class="flex items-center space-x-2 text-gray-600 hover:text-red-600 transition-colors duration-200 add-to-wishlist"
                            data-product-id="<?php echo $product['id']; ?>">
                        <i class="fas fa-heart"></i>
                        <span>Add to Wishlist</span>
                    </button>
                    <button class="flex items-center space-x-2 text-gray-600 hover:text-blue-600 transition-colors duration-200 share-product">
                        <i class="fas fa-share-alt"></i>
                        <span>Share</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($relatedProducts)): ?>
        <div class="mt-16">
            <h2 class="text-3xl font-bold text-center mb-12">Related Products</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($relatedProducts as $related): ?>
                    <div class="bg-white rounded-2xl shadow-lg hover-lift card-hover overflow-hidden group">
                        <div class="relative h-48 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center overflow-hidden">
                            <?php if ($related['image']): ?>
                                <img src="<?php echo htmlspecialchars($related['image']); ?>"
                                     alt="<?php echo htmlspecialchars($related['name']); ?>"
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <?php else: ?>
                                <i class="fas fa-image text-gray-400 text-4xl"></i>
                            <?php endif; ?>
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-lg mb-2 text-gray-800 group-hover:text-green-600 transition-colors duration-300">
                                <?php echo htmlspecialchars($related['name']); ?>
                            </h3>
                            <p class="text-green-600 font-bold text-xl mb-3">৳<?php echo number_format($related['price'], 2); ?></p>
                            <a href="/products/<?php echo $related['id']; ?>"
                               class="w-full bg-gray-100 text-gray-800 py-2 rounded-lg hover:bg-gray-200 transition-colors duration-300 text-center block">
                                View Details
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Quantity selector functionality
document.querySelector('.decrease-quantity')?.addEventListener('click', function() {
    const input = document.getElementById('quantity');
    const currentValue = parseInt(input.value);
    if (currentValue > 1) {
        input.value = currentValue - 1;
    }
});

document.querySelector('.increase-quantity')?.addEventListener('click', function() {
    const input = document.getElementById('quantity');
    const currentValue = parseInt(input.value);
    const maxValue = parseInt(input.max);
    if (currentValue < maxValue) {
        input.value = currentValue + 1;
    }
});

document.getElementById('quantity')?.addEventListener('change', function() {
    const value = parseInt(this.value);
    const min = parseInt(this.min);
    const max = parseInt(this.max);
    if (value < min) this.value = min;
    if (value > max) this.value = max;
});

// Enhanced add to cart functionality with comprehensive logging
document.addEventListener('DOMContentLoaded', function() {
    console.log('📄 DOM loaded, setting up cart functionality on product details page');
    
    const addToCartButton = document.querySelector('.add-to-cart-main');
    
    if (addToCartButton) {
        const productId = addToCartButton.getAttribute('data-product-id');
        console.log(`📄 Setting up add-to-cart button for product ID: ${productId}`);
        
        addToCartButton.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('📄 ===== ADD TO CART CLICKED (DETAILS) =====');
            
            const productId = this.getAttribute('data-product-id');
            const quantity = document.getElementById('quantity').value;
            const originalText = this.getAttribute('data-original-text') || '<i class="fas fa-cart-plus mr-2"></i>Add to Cart';
            
            console.log('📄 Product ID:', productId);
            console.log('📄 Quantity:', quantity);
            console.log('📄 Button element:', this);
            console.log('📄 Original text:', originalText);

            // Check if user is logged in
            <?php if (!isset($_SESSION['user_id'])): ?>
                console.log('❌ User not logged in - redirecting to login');
                alert('Please login to add items to cart');
                window.location.href = '/login';
                return;
            <?php else: ?>
                console.log('✅ User is logged in with ID: <?php echo $_SESSION['user_id']; ?>');
            <?php endif; ?>

            // Set loading state
            console.log('📄 Setting button to loading state');
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Adding...';

            // Prepare request data
            const requestData = 'product_id=' + encodeURIComponent(productId) + '&quantity=' + encodeURIComponent(quantity);
            console.log('📄 Request data:', requestData);
            console.log('📄 Making request to: /cart/add');

            fetch('/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: requestData
            })
            .then(response => {
                console.log('📄 Response received:', {
                    status: response.status,
                    statusText: response.statusText,
                    ok: response.ok,
                    url: response.url
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status} - ${response.statusText}`);
                }
                return response.text().then(text => {
                    console.log('📄 Raw response text:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('📄 Failed to parse JSON response:', e);
                        throw new Error('Invalid JSON response: ' + text);
                    }
                });
            })
            .then(data => {
                console.log('📄 Parsed response data:', data);
                
                // Reset button state
                this.disabled = false;
                this.innerHTML = originalText;
                
                if (data.success) {
                    console.log('✅ SUCCESS: Product added to cart');
                    alert(`Added ${quantity} ${quantity == 1 ? 'item' : 'items'} to cart!`);
                    updateCartCount();
                } else {
                    console.error('❌ FAILED:', data.message);
                    alert('Error: ' + (data.message || 'Failed to add product to cart'));
                }
            })
            .catch(error => {
                console.error('📄 ===== ERROR OCCURRED =====');
                console.error('📄 Error type:', error.constructor.name);
                console.error('📄 Error message:', error.message);
                console.error('📄 Error stack:', error.stack);
                
                // Reset button state
                this.disabled = false;
                this.innerHTML = originalText;
                
                // Show user-friendly error message
                let errorMessage = 'An error occurred while adding to cart';
                if (error.message.includes('HTTP error')) {
                    errorMessage = 'Server error: ' + error.message;
                } else if (error.message.includes('JSON')) {
                    errorMessage = 'Invalid response from server';
                } else {
                    errorMessage = error.message;
                }
                
                alert('Error: ' + errorMessage);
            });
        });
        
        console.log('📄 Add-to-cart button event listener set up successfully');
    } else {
        console.warn('📄 No add-to-cart button found on product details page');
    }
});

// Buy now functionality
document.querySelector('.buy-now')?.addEventListener('click', function() {
    const productId = this.getAttribute('data-product-id');
    const quantity = document.getElementById('quantity').value;

    // Check if user is logged in
    <?php if (!isset($_SESSION['user_id'])): ?>
        showToast('Please login to purchase items', 'warning');
        setTimeout(() => {
            window.location.href = '/login';
        }, 2000);
        return;
    <?php endif; ?>

    // First add to cart, then redirect to checkout
    setLoading(this, true);

    fetch('/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'product_id=' + productId + '&quantity=' + quantity
    })
    .then(response => response.json())
    .then(data => {
        setLoading(this, false);
        if (data.success) {
            showToast('Redirecting to checkout...', 'success');
            setTimeout(() => {
                window.location.href = '/checkout';
            }, 1000);
        } else {
            showToast(data.message || 'Failed to add product to cart', 'error');
        }
    })
    .catch(error => {
        setLoading(this, false);
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    });
});

// Add to wishlist functionality
document.querySelector('.add-to-wishlist')?.addEventListener('click', function() {
    console.log('❤️ ===== ADD TO WISHLIST CLICKED =====');
    
    const productId = this.getAttribute('data-product-id');
    const originalContent = this.innerHTML;
    
    console.log('❤️ Product ID:', productId);
    console.log('❤️ Button element:', this);

    // Check if user is logged in
    <?php if (!isset($_SESSION['user_id'])): ?>
        console.log('❌ User not logged in');
        showToast('Please login to add items to wishlist', 'warning');
        setTimeout(() => {
            window.location.href = '/login';
        }, 2000);
        return;
    <?php else: ?>
        console.log('✅ User is logged in with ID: <?php echo $_SESSION['user_id']; ?>');
    <?php endif; ?>

    // Set loading state
    this.disabled = true;
    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span class="ml-2">Adding...</span>';
    
    console.log('❤️ Making request to: /wishlist/add');
    console.log('❤️ Request body: product_id=' + productId);

    fetch('/wishlist/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'product_id=' + productId
    })
    .then(response => {
        console.log('❤️ Response received:', {
            status: response.status,
            statusText: response.statusText,
            ok: response.ok,
            contentType: response.headers.get('content-type')
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status} - ${response.statusText}`);
        }
        
        return response.text().then(text => {
            console.log('❤️ Raw response text:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('❤️ Failed to parse JSON response:', e);
                console.error('❤️ Response was:', text);
                throw new Error('Invalid JSON response: ' + text.substring(0, 100));
            }
        });
    })
    .then(data => {
        console.log('❤️ Parsed response data:', data);
        
        if (data.success) {
            console.log('✅ SUCCESS: Product added to wishlist!');
            console.log('✅ Wishlist ID:', data.wishlist_id);
            console.log('✅ Product:', data.product_name);
            
            // Show prominent success message
            showToast('Successfully added to wishlist!', 'success');
            
            // Update button appearance
            this.innerHTML = '<i class="fas fa-heart text-red-500"></i><span class="ml-2 text-red-500 font-semibold">Added to Wishlist</span>';
            this.classList.add('text-red-600');
            
            // Update wishlist badge count
            updateWishlistCount();
            
            // Keep button disabled to show it's added
            // User can refresh page to reset if needed
        } else {
            console.error('❌ FAILED:', data.message);
            showToast(data.message || 'Failed to add to wishlist', 'error');
            this.disabled = false;
            this.innerHTML = originalContent;
        }
    })
    .catch(error => {
        console.error('❤️ ===== ERROR OCCURRED =====');
        console.error('❤️ Error type:', error.constructor.name);
        console.error('❤️ Error message:', error.message);
        console.error('❤️ Error stack:', error.stack);
        
        showToast('Error: ' + error.message, 'error');
        this.disabled = false;
        this.innerHTML = originalContent;
    });
});

// Update wishlist count function
function updateWishlistCount() {
    fetch('/wishlist/count')
        .then(response => response.json())
        .then(data => {
            console.log('❤️ Wishlist count:', data.count);
            const wishlistBadge = document.querySelector('.wishlist-badge');
            if (wishlistBadge) {
                if (data.count > 0) {
                    wishlistBadge.textContent = data.count;
                    wishlistBadge.classList.remove('hidden');
                } else {
                    wishlistBadge.classList.add('hidden');
                }
            }
        })
        .catch(error => console.error('Error updating wishlist count:', error));
}

// Share functionality
document.querySelector('.share-product')?.addEventListener('click', function() {
    if (navigator.share) {
        navigator.share({
            title: '<?php echo htmlspecialchars($product['name']); ?>',
            text: 'Check out this product: <?php echo htmlspecialchars($product['name']); ?>',
            url: window.location.href
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(window.location.href).then(() => {
            showToast('Link copied to clipboard!', 'success');
        });
    }
});

// Update cart count
function updateCartCount() {
    fetch('/cart/count')
        .then(response => response.json())
        .then(data => {
            const cartBadge = document.querySelector('.cart-badge');
            if (cartBadge && data.count > 0) {
                cartBadge.textContent = data.count;
                cartBadge.classList.remove('hidden');
            }
        })
        .catch(error => console.error('Error updating cart count:', error));
}
</script>

<?php
$content = ob_get_clean();
include 'app/views/layouts/main.php';
?>