<?php
$title = 'Order Successful - GroceryApp';
ob_start();
?>

<div class="min-h-screen bg-gradient-to-br from-green-50 via-emerald-50 to-teal-50 flex items-center justify-center px-4 py-8">
    <div class="max-w-2xl w-full text-center animate-fade-in">
        <!-- Success Animation -->
        <div class="mb-8 animate-bounce-in">
            <div class="relative">
                <div class="w-32 h-32 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center mx-auto shadow-2xl animate-pulse">
                    <i class="fas fa-check text-white text-5xl"></i>
                </div>
                <!-- Animated rings -->
                <div class="absolute inset-0 rounded-full border-4 border-green-300 animate-ping"></div>
                <div class="absolute inset-2 rounded-full border-4 border-green-400 animate-ping" style="animation-delay: 0.5s;"></div>
            </div>
        </div>

        <!-- Success Message -->
        <div class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/20 p-8 mb-8 animate-slide-up">
            <h1 class="text-4xl font-bold text-gray-900 mb-4 animate-fade-in" style="animation-delay: 0.3s;">
                Order Placed Successfully! 🎉
            </h1>
            <p class="text-xl text-gray-600 mb-6 animate-fade-in" style="animation-delay: 0.5s;">
                Thank you for shopping with GroceryApp! Your order has been confirmed and is being processed.
            </p>

            <!-- Order Details -->
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl p-6 mb-6 animate-slide-up" style="animation-delay: 0.7s;">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-left">
                    <div>
                        <p class="text-sm text-gray-600">Order ID</p>
                        <p class="font-semibold text-gray-900">#<?php echo htmlspecialchars($_GET['order_id'] ?? 'N/A'); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Estimated Delivery</p>
                        <p class="font-semibold text-gray-900">Within 2 hours</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Payment Method</p>
                        <p class="font-semibold text-gray-900">Cash on Delivery</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Order Status</p>
                        <span class="inline-block bg-green-100 text-green-800 text-sm font-semibold px-3 py-1 rounded-full">
                            Confirmed
                        </span>
                    </div>
                </div>
                
                <?php
                // Check if order has surprise gift
                if (isset($_GET['order_id']) && !empty($_GET['order_id'])) {
                    require_once 'app/helpers/SurpriseGiftHelper.php';
                    require_once 'config/database.php';
                    
                    $surpriseGiftHelper = new SurpriseGiftHelper($pdo);
                    $surpriseGiftMessage = $surpriseGiftHelper->getSurpriseGiftMessage($_GET['order_id']);
                    
                    if ($surpriseGiftMessage) {
                        echo '<div class="mt-4 p-4 bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-xl">';
                        echo '<div class="flex items-center">';
                        echo '<i class="fas fa-gift text-yellow-600 text-2xl mr-3"></i>';
                        echo '<div>';
                        echo '<h4 class="font-bold text-yellow-800 text-lg">Surprise Gift!</h4>';
                        echo '<p class="text-yellow-700">' . htmlspecialchars($surpriseGiftMessage) . '</p>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                }
                ?>
            </div>

            <!-- What's Next -->
            <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6 mb-6 animate-slide-up" style="animation-delay: 0.9s;">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center justify-center">
                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                    What's Next?
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div class="text-center">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-box text-blue-600"></i>
                        </div>
                        <p class="font-medium text-gray-900">Order Processing</p>
                        <p class="text-gray-600">We're preparing your items</p>
                    </div>
                    <div class="text-center">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-truck text-blue-600"></i>
                        </div>
                        <p class="font-medium text-gray-900">Out for Delivery</p>
                        <p class="text-gray-600">Your order is on the way</p>
                    </div>
                    <div class="text-center">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-check-circle text-green-600"></i>
                        </div>
                        <p class="font-medium text-gray-900">Delivered</p>
                        <p class="text-gray-600">Fresh groceries at your door</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 animate-fade-in" style="animation-delay: 1.1s;">
                <a href="/orders/<?php echo htmlspecialchars($_GET['order_id'] ?? ''); ?>" class="flex-1 bg-blue-600 text-white py-4 px-6 rounded-xl font-bold hover:bg-blue-700 transition-all duration-300 transform hover:scale-105 hover:shadow-lg text-center">
                    <i class="fas fa-eye mr-2"></i>
                    View Order Details
                </a>
                <a href="/orders/track/<?php echo htmlspecialchars($_GET['order_id'] ?? ''); ?>" class="flex-1 bg-green-600 text-white py-4 px-6 rounded-xl font-bold hover:bg-green-700 transition-all duration-300 transform hover:scale-105 hover:shadow-lg text-center">
                    <i class="fas fa-map-marked-alt mr-2"></i>
                    Track Order
                </a>
                <a href="/products" class="flex-1 bg-purple-600 text-white py-4 px-6 rounded-xl font-bold hover:bg-purple-700 transition-all duration-300 transform hover:scale-105 hover:shadow-lg text-center">
                    <i class="fas fa-shopping-cart mr-2"></i>
                    Shop More
                </a>
            </div>
        </div>

        <!-- Additional Info -->
        <div class="bg-white/60 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-white/20 animate-slide-up" style="animation-delay: 1.3s;">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                <div>
                    <i class="fas fa-headset text-2xl text-blue-600 mb-2"></i>
                    <h4 class="font-semibold text-gray-900 mb-1">Need Help?</h4>
                    <p class="text-sm text-gray-600">Call us at +880 123 456 789</p>
                </div>
                <div>
                    <i class="fas fa-envelope text-2xl text-green-600 mb-2"></i>
                    <h4 class="font-semibold text-gray-900 mb-1">Email Support</h4>
                    <p class="text-sm text-gray-600">support@groceryapp.com</p>
                </div>
                <div>
                    <i class="fas fa-mobile-alt text-2xl text-purple-600 mb-2"></i>
                    <h4 class="font-semibold text-gray-900 mb-1">Live Chat</h4>
                    <p class="text-sm text-gray-600">Available 24/7</p>
                </div>
            </div>
        </div>

        <!-- Social Share -->
        <div class="mt-8 text-center animate-fade-in" style="animation-delay: 1.5s;">
            <p class="text-gray-600 mb-4">Share your shopping experience</p>
            <div class="flex justify-center space-x-4">
                <button class="w-12 h-12 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-colors duration-200 flex items-center justify-center">
                    <i class="fab fa-facebook-f"></i>
                </button>
                <button class="w-12 h-12 bg-blue-400 text-white rounded-full hover:bg-blue-500 transition-colors duration-200 flex items-center justify-center">
                    <i class="fab fa-twitter"></i>
                </button>
                <button class="w-12 h-12 bg-pink-600 text-white rounded-full hover:bg-pink-700 transition-colors duration-200 flex items-center justify-center">
                    <i class="fab fa-instagram"></i>
                </button>
                <button class="w-12 h-12 bg-green-600 text-white rounded-full hover:bg-green-700 transition-colors duration-200 flex items-center justify-center">
                    <i class="fab fa-whatsapp"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Add confetti effect (optional)
document.addEventListener('DOMContentLoaded', function() {
    // Simple confetti effect
    function createConfetti() {
        const colors = ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b', '#ef4444'];
        for (let i = 0; i < 50; i++) {
            setTimeout(() => {
                const confetti = document.createElement('div');
                confetti.style.position = 'fixed';
                confetti.style.left = Math.random() * window.innerWidth + 'px';
                confetti.style.top = '-10px';
                confetti.style.width = '10px';
                confetti.style.height = '10px';
                confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.borderRadius = '50%';
                confetti.style.zIndex = '9999';
                confetti.style.pointerEvents = 'none';
                document.body.appendChild(confetti);

                const animation = confetti.animate([
                    { transform: 'translateY(0px) rotate(0deg)', opacity: 1 },
                    { transform: `translateY(${window.innerHeight}px) rotate(${Math.random() * 360}deg)`, opacity: 0 }
                ], {
                    duration: 3000 + Math.random() * 2000,
                    easing: 'ease-out'
                });

                animation.onfinish = () => confetti.remove();
            }, i * 50);
        }
    }

    // Trigger confetti on page load
    createConfetti();

    // Add click handlers for social sharing
    const shareButtons = document.querySelectorAll('.bg-blue-600, .bg-blue-400, .bg-pink-600, .bg-green-600');
    shareButtons.forEach(button => {
        button.addEventListener('click', function() {
            showToast('Sharing feature coming soon!', 'info');
        });
    });
});
</script>

<?php
$content = ob_get_clean();
include 'app/views/layouts/main.php';
?>