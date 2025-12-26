<?php
$title = 'My Wishlist - GroceryApp';
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
                        <span class="text-sm font-medium text-gray-500">My Wishlist</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-4 animate-slide-up">
                <i class="fas fa-heart mr-3 text-red-500"></i>
                My Wishlist
            </h1>
            <p class="text-xl text-gray-600 animate-fade-in" style="animation-delay: 0.2s;">
                Your saved favorite products
            </p>
        </div>

        <!-- Success Message (if redirected from add action) -->
        <?php if (isset($_GET['added']) && $_GET['added'] == '1'): ?>
        <div class="max-w-7xl mx-auto mb-6 animate-slide-down">
            <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-2xl shadow-2xl p-6 border border-green-400">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                            <i class="fas fa-check-circle text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold mb-1">Successfully added to wishlist!</h3>
                            <p class="text-green-100">Your product has been saved to your wishlist.</p>
                        </div>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-green-200 transition-colors duration-300 p-2">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (empty($wishlistItems)): ?>
            <div class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/20 p-16 text-center animate-slide-up">
                <div class="w-32 h-32 bg-gradient-to-br from-red-200 to-pink-300 rounded-full flex items-center justify-center mx-auto mb-8">
                    <i class="fas fa-heart text-red-400 text-6xl"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Your wishlist is empty</h2>
                <p class="text-xl text-gray-600 mb-8">Start adding products you love to your wishlist!</p>
                
                <?php if (isset($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                        <p class="font-semibold">Error: <?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($debug_info) && $debug_info['raw_count'] > 0): ?>
                    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded-lg mb-4">
                        <p class="font-semibold">Debug Info:</p>
                        <p>Database has <?php echo $debug_info['raw_count']; ?> items, but query returned <?php echo $debug_info['query_count']; ?> items.</p>
                        <p>Please check server logs for details.</p>
                    </div>
                <?php endif; ?>
                
                <a href="/products" class="btn-primary inline-block text-white px-8 py-4 rounded-xl font-bold text-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-105">
                    <i class="fas fa-shopping-bag mr-2"></i>
                    Browse Products
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php foreach ($wishlistItems as $item): ?>
                    <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-xl border border-white/20 overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:scale-105 animate-on-scroll">
                        <!-- Product Image -->
                        <div class="relative h-48 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center overflow-hidden">
                            <?php if ($item['image']): ?>
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                     class="w-full h-full object-cover">
                            <?php else: ?>
                                <i class="fas fa-image text-gray-400 text-5xl"></i>
                            <?php endif; ?>
                            
                            <!-- Remove from wishlist button -->
                            <button onclick="removeFromWishlist(<?php echo $item['wishlist_id']; ?>, <?php echo $item['product_id']; ?>)" 
                                    class="absolute top-3 right-3 bg-white/90 hover:bg-red-500 text-red-500 hover:text-white w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 shadow-lg"
                                    title="Remove from wishlist">
                                <i class="fas fa-heart-broken"></i>
                            </button>
                            
                            <!-- Stock Badge -->
                            <?php if (isset($item['stock_quantity']) && $item['stock_quantity'] == 0): ?>
                                <div class="absolute top-3 left-3 bg-red-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                    Out of Stock
                                </div>
                            <?php elseif (isset($item['stock_quantity']) && $item['stock_quantity'] <= 10): ?>
                                <div class="absolute top-3 left-3 bg-orange-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                    Low Stock
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Product Info -->
                        <div class="p-6">
                            <?php if (isset($item['is_deleted']) && $item['is_deleted']): ?>
                                <div class="mb-2 bg-red-100 border border-red-300 text-red-700 px-3 py-2 rounded-lg">
                                    <p class="text-xs font-semibold"><i class="fas fa-exclamation-triangle mr-1"></i>Product No Longer Available</p>
                                </div>
                            <?php else: ?>
                                <div class="mb-2">
                                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">
                                        <?php echo htmlspecialchars($item['category_name'] ?? 'Uncategorized'); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <h3 class="font-bold text-lg text-gray-900 mb-2 line-clamp-2">
                                <?php echo htmlspecialchars($item['name'] ?? 'Product Name Not Available'); ?>
                            </h3>
                            
                            <?php if ($item['description']): ?>
                                <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                                    <?php echo htmlspecialchars(substr($item['description'], 0, 100)); ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <?php if (isset($item['is_deleted']) && $item['is_deleted']): ?>
                                        <span class="text-lg font-bold text-gray-400">N/A</span>
                                    <?php else: ?>
                                        <span class="text-2xl font-bold text-green-600">৳<?php echo number_format($item['price'] ?? 0, 2); ?></span>
                                        <span class="text-sm text-gray-500">/ <?php echo htmlspecialchars($item['unit'] ?? ''); ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!isset($item['is_deleted']) && isset($item['calories_per_unit']) && $item['calories_per_unit'] > 0): ?>
                                    <div class="text-xs text-gray-600 bg-orange-100 px-2 py-1 rounded">
                                        <i class="fas fa-fire text-orange-500"></i>
                                        <?php echo number_format($item['calories_per_unit'], 0); ?> kcal
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="space-y-2">
                                <?php if (isset($item['is_deleted']) && $item['is_deleted']): ?>
                                    <button disabled
                                            class="block w-full bg-gray-400 text-white py-3 rounded-xl font-semibold cursor-not-allowed text-center">
                                        <i class="fas fa-ban mr-2"></i>Product Unavailable
                                    </button>
                                <?php else: ?>
                                    <a href="/products/<?php echo $item['product_id']; ?>" 
                                       class="block w-full bg-blue-600 text-white py-3 rounded-xl font-semibold hover:bg-blue-700 transition-colors duration-200 text-center">
                                        <i class="fas fa-eye mr-2"></i>View Details
                                    </a>
                                    <button onclick="addToCart(<?php echo $item['product_id']; ?>)" 
                                            class="w-full bg-green-600 text-white py-3 rounded-xl font-semibold hover:bg-green-700 transition-colors duration-200"
                                            data-product-id="<?php echo $item['product_id']; ?>">
                                        <i class="fas fa-cart-plus mr-2"></i>Add to Cart
                                    </button>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Added Date -->
                            <p class="text-xs text-gray-500 mt-3 text-center">
                                <i class="fas fa-clock mr-1"></i>
                                Added <?php echo date('M j, Y', strtotime($item['added_at'])); ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Remove from wishlist
function removeFromWishlist(wishlistId, productId) {
    if (!confirm('Are you sure you want to remove this product from your wishlist?')) {
        return;
    }
    
    const button = event.target.closest('button');
    const originalContent = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    fetch('/wishlist/remove', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'wishlist_id=' + wishlistId + '&product_id=' + productId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Product removed from wishlist', 'success');
            // Remove the card with animation
            const card = button.closest('.bg-white');
            card.style.transition = 'all 0.3s ease';
            card.style.opacity = '0';
            card.style.transform = 'scale(0.8)';
            setTimeout(() => {
                card.remove();
                // Check if wishlist is empty
                const remainingItems = document.querySelectorAll('.bg-white\\/80');
                if (remainingItems.length === 0) {
                    location.reload();
                }
            }, 300);
        } else {
            showToast(data.message || 'Failed to remove product', 'error');
            button.disabled = false;
            button.innerHTML = originalContent;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
        button.disabled = false;
        button.innerHTML = originalContent;
    });
}

// Add to cart
function addToCart(productId) {
    const button = event.target.closest('button');
    const originalContent = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Adding...';
    
    fetch('/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'product_id=' + productId + '&quantity=1'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Product added to cart!', 'success');
            button.innerHTML = '<i class="fas fa-check mr-2"></i>Added!';
            setTimeout(() => {
                button.innerHTML = originalContent;
                button.disabled = false;
            }, 2000);
        } else {
            showToast(data.message || 'Failed to add to cart', 'error');
            button.disabled = false;
            button.innerHTML = originalContent;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
        button.disabled = false;
        button.innerHTML = originalContent;
    });
}

// Update wishlist count on page load
document.addEventListener('DOMContentLoaded', function() {
    updateWishlistCount();
});

function updateWishlistCount() {
    fetch('/wishlist/count')
        .then(response => response.json())
        .then(data => {
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
</script>

<?php
$content = ob_get_clean();
include 'app/views/layouts/main.php';
?>

