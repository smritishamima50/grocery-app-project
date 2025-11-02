<?php
session_start();

// Ensure user is logged in for testing
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['first_name'] = 'Admin';
    $_SESSION['last_name'] = 'User';
    $_SESSION['email'] = 'admin@grocery.com';
}

$title = 'Cart Debug Complete';
ob_start();
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Complete Cart Debug</h1>
    
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <h3 class="text-lg font-semibold text-blue-900 mb-2">🔍 Debug Information:</h3>
        <p><strong>User ID:</strong> <?php echo $_SESSION['user_id']; ?></p>
        <p><strong>Session ID:</strong> <?php echo session_id(); ?></p>
        <p><strong>Session Status:</strong> <?php echo session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive'; ?></p>
        <p><strong>Current Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Test Product 1 -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
            <div class="h-48 bg-gradient-to-br from-green-400 to-blue-500 flex items-center justify-center">
                <i class="fas fa-apple-alt text-white text-4xl"></i>
            </div>
            <div class="p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Organic Apples</h3>
                <p class="text-gray-600 mb-4">Fresh organic apples from local farms.</p>
                <div class="flex items-center justify-between mb-4">
                    <span class="text-2xl font-bold text-green-600">৳150.00</span>
                    <span class="text-sm text-gray-500">Stock: 100</span>
                </div>
                <button class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-xl font-semibold transition-colors duration-200 add-to-cart"
                        data-product-id="1"
                        data-original-text="<i class='fas fa-cart-plus mr-2'></i>Add to Cart">
                    <i class="fas fa-cart-plus mr-2"></i>Add to Cart
                </button>
            </div>
        </div>

        <!-- Test Product 2 -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
            <div class="h-48 bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center">
                <i class="fas fa-lemon text-white text-4xl"></i>
            </div>
            <div class="p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Banana</h3>
                <p class="text-gray-600 mb-4">Fresh bananas, perfect for snacking.</p>
                <div class="flex items-center justify-between mb-4">
                    <span class="text-2xl font-bold text-green-600">৳50.00</span>
                    <span class="text-sm text-gray-500">Stock: 200</span>
                </div>
                <button class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-xl font-semibold transition-colors duration-200 add-to-cart"
                        data-product-id="2"
                        data-original-text="<i class='fas fa-cart-plus mr-2'></i>Add to Cart">
                    <i class="fas fa-cart-plus mr-2"></i>Add to Cart
                </button>
            </div>
        </div>

        <!-- Test Product 3 -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
            <div class="h-48 bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                <i class="fas fa-wine-bottle text-white text-4xl"></i>
            </div>
            <div class="p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Milk</h3>
                <p class="text-gray-600 mb-4">Fresh dairy milk, 1 liter.</p>
                <div class="flex items-center justify-between mb-4">
                    <span class="text-2xl font-bold text-green-600">৳80.00</span>
                    <span class="text-sm text-gray-500">Stock: 50</span>
                </div>
                <button class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-xl font-semibold transition-colors duration-200 add-to-cart"
                        data-product-id="3"
                        data-original-text="<i class='fas fa-cart-plus mr-2'></i>Add to Cart">
                    <i class="fas fa-cart-plus mr-2"></i>Add to Cart
                </button>
            </div>
        </div>
    </div>

    <div class="mt-8 bg-gray-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Debug Console:</h3>
        <div id="debug-console" class="bg-black text-green-400 p-4 rounded-lg font-mono text-sm h-64 overflow-y-auto">
            <div>=== CART DEBUG CONSOLE ===</div>
            <div>Page loaded at: <?php echo date('Y-m-d H:i:s'); ?></div>
            <div>User ID: <?php echo $_SESSION['user_id']; ?></div>
            <div>Session ID: <?php echo session_id(); ?></div>
            <div>Ready for testing...</div>
        </div>
    </div>

    <div class="mt-6 flex space-x-4">
        <a href="/cart" class="bg-blue-600 text-white px-6 py-3 rounded-xl hover:bg-blue-700 transition-colors duration-200">
            <i class="fas fa-shopping-cart mr-2"></i>View Cart
        </a>
        <a href="/products" class="bg-green-600 text-white px-6 py-3 rounded-xl hover:bg-green-700 transition-colors duration-200">
            <i class="fas fa-store mr-2"></i>Go to Products
        </a>
    </div>
</div>

<script>
// Debug console function
function debugLog(message) {
    const console = document.getElementById('debug-console');
    const time = new Date().toLocaleTimeString();
    console.innerHTML += `<div>[${time}] ${message}</div>`;
    console.scrollTop = console.scrollHeight;
    console.log(`[DEBUG] ${message}`);
}

// Enhanced add to cart functionality with complete debugging
document.addEventListener('DOMContentLoaded', function() {
    debugLog('DOM loaded, setting up cart functionality');
    
    document.querySelectorAll('.add-to-cart').forEach((button, index) => {
        debugLog(`Setting up button ${index + 1} for product: ${button.getAttribute('data-product-id')}`);
        
        button.addEventListener('click', function(e) {
            e.preventDefault();
            debugLog('=== ADD TO CART CLICKED ===');
            
            const productId = this.getAttribute('data-product-id');
            debugLog(`Product ID: ${productId}`);

            // Check if user is logged in
            <?php if (!isset($_SESSION['user_id'])): ?>
                debugLog('❌ User not logged in');
                alert('Please login to add items to cart');
                window.location.href = '/login';
                return;
            <?php else: ?>
                debugLog('✅ User is logged in with ID: <?php echo $_SESSION['user_id']; ?>');
            <?php endif; ?>

            // Set loading state
            debugLog('Setting button to loading state');
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Adding...';

            debugLog('Making request to /cart/add');
            fetch('/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId + '&quantity=1'
            })
            .then(response => {
                debugLog(`Response received: ${response.status} ${response.statusText}`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                debugLog(`Response data: ${JSON.stringify(data)}`);
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-cart-plus mr-2"></i>Add to Cart';
                
                if (data.success) {
                    debugLog('✅ SUCCESS! Product added to cart');
                    alert('Product added to cart successfully!');
                    updateCartCount();
                } else {
                    debugLog(`❌ FAILED: ${data.message}`);
                    alert('Error: ' + (data.message || 'Failed to add product to cart'));
                }
            })
            .catch(error => {
                debugLog(`❌ ERROR: ${error.message}`);
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-cart-plus mr-2"></i>Add to Cart';
                alert('Error: ' + error.message);
            });
        });
    });
    
    debugLog('All event listeners set up successfully');
});

// Cart count update function
function updateCartCount() {
    debugLog('Updating cart count...');
    fetch('/cart/count')
        .then(response => response.json())
        .then(data => {
            debugLog(`Cart count updated: ${data.count} items`);
        })
        .catch(error => {
            debugLog(`Failed to update cart count: ${error.message}`);
        });
}
</script>

<?php
$content = ob_get_clean();
include 'app/views/layouts/main.php';
?>
