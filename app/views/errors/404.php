<?php
$title = 'Page Not Found - GroceryApp';
ob_start();
?>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 relative overflow-hidden">
    <!-- Animated background elements -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-bounce-in" style="animation-delay: 0.5s;"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-bounce-in" style="animation-delay: 1s;"></div>
        <div class="absolute top-40 left-40 w-60 h-60 bg-gradient-to-br from-purple-400 to-purple-600 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-bounce-in" style="animation-delay: 1.5s;"></div>
    </div>

    <div class="max-w-lg w-full text-center relative z-10 animate-slide-up">
        <!-- 404 Illustration -->
        <div class="mb-8 animate-bounce-in">
            <div class="relative">
                <div class="w-48 h-48 mx-auto bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center shadow-2xl">
                    <i class="fas fa-search text-white text-6xl"></i>
                </div>
                <div class="absolute -top-4 -right-4 w-16 h-16 bg-red-500 rounded-full flex items-center justify-center animate-pulse">
                    <span class="text-white font-bold text-xl">404</span>
                </div>
            </div>
        </div>

        <!-- Error Message -->
        <div class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/20 p-8 animate-slide-up" style="animation-delay: 0.3s;">
            <h1 class="text-6xl font-bold text-gray-900 mb-4 animate-fade-in" style="animation-delay: 0.5s;">
                Oops!
            </h1>
            <h2 class="text-2xl font-semibold text-gray-700 mb-4 animate-fade-in" style="animation-delay: 0.7s;">
                Page Not Found
            </h2>
            <p class="text-gray-600 mb-8 leading-relaxed animate-fade-in" style="animation-delay: 0.9s;">
                The page you're looking for doesn't exist or has been moved.
                Don't worry, let's get you back on track!
            </p>

            <!-- Action Buttons -->
            <div class="space-y-4 animate-fade-in" style="animation-delay: 1.1s;">
                <a href="/" class="inline-block w-full bg-gradient-to-r from-blue-500 to-purple-600 text-white font-bold py-4 px-8 rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                    <i class="fas fa-home mr-2"></i>
                    Go to Homepage
                </a>
                <a href="/products" class="inline-block w-full bg-white text-gray-700 font-semibold py-4 px-8 rounded-xl border-2 border-gray-300 hover:border-purple-500 hover:text-purple-600 transition-all duration-300 hover:shadow-md">
                    <i class="fas fa-store mr-2"></i>
                    Browse Products
                </a>
            </div>

            <!-- Help Section -->
            <div class="mt-8 pt-6 border-t border-gray-200 animate-fade-in" style="animation-delay: 1.3s;">
                <p class="text-sm text-gray-500 mb-4">Need help finding something?</p>
                <div class="flex justify-center space-x-4">
                    <a href="/contact" class="text-blue-600 hover:text-blue-700 transition-colors duration-200">
                        <i class="fas fa-envelope mr-1"></i>Contact Us
                    </a>
                    <span class="text-gray-400">|</span>
                    <a href="/help" class="text-blue-600 hover:text-blue-700 transition-colors duration-200">
                        <i class="fas fa-question-circle mr-1"></i>Help Center
                    </a>
                </div>
            </div>
        </div>

        <!-- Fun Facts or Tips -->
        <div class="mt-8 animate-fade-in" style="animation-delay: 1.5s;">
            <div class="bg-white/60 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-white/20">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">
                    <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                    Did you know?
                </h3>
                <p class="text-gray-600 text-sm">
                    Our grocery store offers fresh produce delivered within 2 hours!
                    Check out our featured products to discover great deals.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
// Add some interactive elements
document.addEventListener('DOMContentLoaded', function() {
    // Add hover effects to buttons
    const buttons = document.querySelectorAll('a');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05) translateY(-2px)';
        });
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1) translateY(0)';
        });
    });

    // Add click tracking for analytics
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            const href = this.getAttribute('href');
            console.log('User clicked:', href);
            // You could send this to analytics here
        });
    });
});
</script>

<?php
$content = ob_get_clean();
include 'app/views/layouts/main.php';
?>