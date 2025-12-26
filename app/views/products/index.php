<?php
$title = 'Products - GroceryApp';
ob_start();

// Debug: Check database status (remove this section after testing)
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM categories");
        $categoryCount = $stmt->fetch()['total'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
        $productCount = $stmt->fetch()['total'];
        
        echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px; border: 1px solid #ccc;'>";
        echo "<strong>Database Status:</strong><br>";
        echo "Categories: " . $categoryCount . "<br>";
        echo "Products: " . $productCount . "<br>";
        
        $stmt = $pdo->query("
            SELECT c.name, COUNT(p.id) as product_count 
            FROM categories c 
            LEFT JOIN products p ON c.id = p.category_id 
            GROUP BY c.id, c.name 
            ORDER BY c.id
        ");
        $categoryStats = $stmt->fetchAll();
        
        echo "<br><strong>Products by Category:</strong><br>";
        foreach ($categoryStats as $stat) {
            echo $stat['name'] . ": " . $stat['product_count'] . " products<br>";
        }
        echo "</div>";
    } catch (PDOException $e) {
        echo "<div style='background: #ffcccc; padding: 10px; margin: 10px; border: 1px solid #ff0000;'>";
        echo "Database error: " . $e->getMessage();
        echo "</div>";
    }
}
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-4 md:mb-0">Products</h1>

        <!-- Search and Filter -->
        <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto">
            <form method="GET" class="flex gap-2">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search ?? ''); ?>"
                       placeholder="Search products..."
                       class="px-4 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                    <i class="fas fa-search"></i>
                </button>
            </form>

            <select onchange="filterByCategory(this.value)" class="px-4 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" id="category-filter">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo intval($cat['id']); ?>" <?php echo ($category && intval($category) === intval($cat['id'])) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php if (empty($products)): ?>
            <div class="col-span-full text-center py-12">
                <i class="fas fa-search text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No products found</h3>
                <p class="text-gray-500">Try adjusting your search or filter criteria.</p>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="bg-white rounded-2xl shadow-xl hover-lift card-hover overflow-hidden group animate-on-scroll">
                    <div class="relative h-56 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center overflow-hidden">
                        <?php if ($product['image']): ?>
                            <img src="<?php echo htmlspecialchars($product['image']); ?>"
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <?php else: ?>
                            <i class="fas fa-image text-gray-400 text-5xl"></i>
                        <?php endif; ?>
                        <div class="absolute top-4 left-4 bg-green-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                            <?php echo htmlspecialchars($product['category_name'] ?? 'Product'); ?>
                        </div>
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center">
                            <a href="/products/<?php echo $product['id']; ?>"
                               class="bg-white text-green-600 px-4 py-2 rounded-full font-semibold opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-y-4 group-hover:translate-y-0 hover:bg-green-50">
                                <i class="fas fa-eye mr-2"></i>Quick View
                            </a>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-2">
                            <h3 class="font-bold text-xl text-gray-800 group-hover:text-green-600 transition-colors duration-300">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </h3>
                            <?php if (isset($product['is_eco_friendly']) && $product['is_eco_friendly']): ?>
                                <div class="bg-green-500 text-white px-3 py-1 rounded-full text-sm font-bold flex items-center shadow-lg animate-pulse">
                                    <i class="fas fa-leaf mr-1"></i>
                                    Eco
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Stock Quantity Display -->
                        <?php if (isset($product['stock_quantity']) && $product['stock_quantity'] > 0): ?>
                            <?php 
                            $stockQuantity = $product['stock_quantity'];
                            $unit = $product['unit'] ?? 'pcs';
                            $isLowStock = $stockQuantity <= 10;
                            $stockClass = $isLowStock ? 'text-red-600 bg-red-50' : 'text-orange-600 bg-orange-50';
                            ?>
                            <div class="mb-3">
                                <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?php echo $stockClass; ?>">
                                    <i class="fas fa-box mr-1"></i>
                                    Only <?php echo $stockQuantity; ?> <?php echo htmlspecialchars($unit); ?> left in stock
                                </div>
                            </div>
                        <?php elseif (isset($product['stock_quantity']) && $product['stock_quantity'] == 0): ?>
                            <div class="mb-3">
                                <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium text-red-600 bg-red-100">
                                    <i class="fas fa-times-circle mr-1"></i>
                                    Out of Stock
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <p class="text-gray-600 text-sm mb-3"><?php echo htmlspecialchars($product['unit']); ?></p>
                        <p class="text-gray-600 text-sm mb-4 leading-relaxed"><?php echo htmlspecialchars(substr($product['description'], 0, 80)); ?>...</p>

                        <div class="flex items-center justify-between mb-4">
                            <span class="text-2xl font-bold text-green-600">৳<?php echo number_format($product['price'], 2); ?></span>
                            <div class="flex text-yellow-400">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                        </div>

                        <!-- Nutrition Information -->
                        <?php 
                        // Debug: Check category name (remove this after testing)
                        if (isset($_GET['debug']) && $_GET['debug'] == '1') {
                            echo "<!-- Debug: Category: '" . htmlspecialchars($product['category_name']) . "' -->";
                        }
                        ?>
                        <?php if (isset($product['calories_per_unit']) && $product['calories_per_unit'] > 0 && isset($product['category_name']) && strtolower(trim($product['category_name'])) !== 'home cleaning'): ?>
                            <div class="bg-gradient-to-r from-orange-50 to-yellow-50 border border-orange-200 rounded-lg p-3 mb-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <div class="text-center">
                                            <div class="text-lg font-bold text-orange-600"><?php echo $product['calories_per_unit']; ?></div>
                                            <div class="text-xs text-gray-600">kcal</div>
                                        </div>
                                        <?php if (isset($product['protein_per_unit']) && $product['protein_per_unit'] > 0): ?>
                                            <div class="text-center">
                                                <div class="text-sm font-semibold text-blue-600"><?php echo $product['protein_per_unit']; ?>g</div>
                                                <div class="text-xs text-gray-600">protein</div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($product['carbs_per_unit']) && $product['carbs_per_unit'] > 0): ?>
                                            <div class="text-center">
                                                <div class="text-sm font-semibold text-green-600"><?php echo $product['carbs_per_unit']; ?>g</div>
                                                <div class="text-xs text-gray-600">carbs</div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        per <?php echo htmlspecialchars($product['unit']); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="flex gap-3">
                            <button class="flex-1 btn-primary text-white py-3 rounded-xl font-semibold add-to-cart group-hover:shadow-lg transition-all duration-300"
                                    data-product-id="<?php echo $product['id']; ?>"
                                    data-original-text="<i class='fas fa-cart-plus mr-2'></i>Add to Cart">
                                <i class="fas fa-cart-plus mr-2"></i>Add to Cart
                            </button>
                            <?php if (isset($_SESSION['user_id'])): ?>
                            <button class="bg-red-100 text-red-600 hover:bg-red-200 px-4 py-3 rounded-xl transition-all duration-300 add-to-wishlist"
                                    data-product-id="<?php echo $product['id']; ?>"
                                    title="Add to Wishlist">
                                <i class="fas fa-heart"></i>
                            </button>
                            <?php endif; ?>
                            <a href="/products/<?php echo $product['id']; ?>"
                               class="bg-gray-100 text-gray-700 px-4 py-3 rounded-xl hover:bg-gray-200 hover:text-green-600 transition-all duration-300 hover-lift">
                                <i class="fas fa-eye text-lg"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="flex justify-center mt-12">
            <nav class="flex items-center space-x-2">
                <?php if ($currentPage > 1): ?>
                    <a href="?page=<?php echo $currentPage - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . $category : ''; ?>"
                       class="px-3 py-2 rounded-md bg-white border border-gray-300 text-gray-500 hover:bg-gray-50">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>

                <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . $category : ''; ?>"
                       class="px-3 py-2 rounded-md <?php echo $i == $currentPage ? 'bg-green-600 text-white' : 'bg-white border border-gray-300 text-gray-500 hover:bg-gray-50'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="?page=<?php echo $currentPage + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . $category : ''; ?>"
                       class="px-3 py-2 rounded-md bg-white border border-gray-300 text-gray-500 hover:bg-gray-50">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    <?php endif; ?>
</div>

<script>
function filterByCategory(categoryId) {
    console.log('🔍 Filtering by category:', categoryId);
    const url = new URL(window.location);
    if (categoryId && categoryId !== '' && categoryId !== '0') {
        // Ensure categoryId is a valid integer
        const categoryInt = parseInt(categoryId);
        if (!isNaN(categoryInt) && categoryInt > 0) {
            url.searchParams.set('category', categoryInt);
            console.log('🔍 Setting category filter to:', categoryInt);
        } else {
            console.warn('🔍 Invalid category ID:', categoryId);
            url.searchParams.delete('category');
        }
    } else {
        url.searchParams.delete('category');
        console.log('🔍 Clearing category filter');
    }
    url.searchParams.delete('page'); // Reset to first page when filtering
    console.log('🔍 Navigating to:', url.toString());
    window.location.href = url.toString();
}

// Enhanced add to cart functionality with comprehensive logging
document.addEventListener('DOMContentLoaded', function() {
    console.log('🛒 DOM loaded, setting up cart functionality');
    
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    console.log(`🛒 Found ${addToCartButtons.length} add-to-cart buttons`);
    
    addToCartButtons.forEach((button, index) => {
        const productId = button.getAttribute('data-product-id');
        console.log(`🛒 Setting up button ${index + 1} for product ID: ${productId}`);
        
        button.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('🛒 ===== ADD TO CART CLICKED =====');
            
            const productId = this.getAttribute('data-product-id');
            const originalText = this.getAttribute('data-original-text') || '<i class="fas fa-cart-plus mr-2"></i>Add to Cart';
            
            console.log('🛒 Product ID:', productId);
            console.log('🛒 Button element:', this);
            console.log('🛒 Original text:', originalText);

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
            console.log('🛒 Setting button to loading state');
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Adding...';

            // Prepare request data
            const requestData = 'product_id=' + encodeURIComponent(productId) + '&quantity=1';
            console.log('🛒 Request data:', requestData);
            console.log('🛒 Making request to: /cart/add');

            fetch('/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: requestData
            })
            .then(response => {
                console.log('🛒 Response received:', {
                    status: response.status,
                    statusText: response.statusText,
                    ok: response.ok,
                    url: response.url
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
                
                // Reset button state
                this.disabled = false;
                this.innerHTML = originalText;
                
                if (data.success) {
                    console.log('✅ SUCCESS: Product added to cart');
                    alert('Product added to cart successfully!');
                    updateCartCount();
                } else {
                    console.error('❌ FAILED:', data.message);
                    alert('Error: ' + (data.message || 'Failed to add product to cart'));
                }
            })
            .catch(error => {
                console.error('🛒 ===== ERROR OCCURRED =====');
                console.error('🛒 Error type:', error.constructor.name);
                console.error('🛒 Error message:', error.message);
                console.error('🛒 Error stack:', error.stack);
                
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
    });
    
    console.log('🛒 All cart event listeners set up successfully');
});

// Update cart count in navigation
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

// Add to wishlist functionality for products listing page
document.addEventListener('DOMContentLoaded', function() {
    console.log('❤️ Setting up wishlist buttons on products page');
    
    document.querySelectorAll('.add-to-wishlist').forEach(button => {
        button.addEventListener('click', function() {
            console.log('❤️ ===== ADD TO WISHLIST CLICKED (PRODUCTS PAGE) =====');
            
            const productId = this.getAttribute('data-product-id');
            const originalContent = this.innerHTML;
            
            console.log('❤️ Product ID:', productId);
            
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
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
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
                    ok: response.ok
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                return response.text().then(text => {
                    console.log('❤️ Raw response:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error('Invalid JSON: ' + text.substring(0, 100));
                    }
                });
            })
            .then(data => {
                console.log('❤️ Parsed data:', data);
                
                if (data.success) {
                    console.log('✅ SUCCESS: Added to wishlist!');
                    showToast('Successfully added to wishlist!', 'success');
                    this.innerHTML = '<i class="fas fa-heart text-red-500"></i>';
                    this.classList.add('bg-red-200', 'text-red-600');
                    updateWishlistCount();
                } else {
                    console.error('❌ FAILED:', data.message);
                    showToast(data.message || 'Failed to add to wishlist', 'error');
                    this.disabled = false;
                    this.innerHTML = originalContent;
                }
            })
            .catch(error => {
                console.error('❤️ Error:', error);
                showToast('Error: ' + error.message, 'error');
                this.disabled = false;
                this.innerHTML = originalContent;
            });
        });
    });
    
    console.log('✅ Wishlist buttons set up on products page');
});

// Update wishlist count function
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

// Initialize cart count and wishlist count on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
    updateWishlistCount();
});
</script>

<?php
$content = ob_get_clean();
include 'app/views/layouts/main.php';
?>