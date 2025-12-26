<?php
$title = 'Home - GroceryApp';
ob_start();
?>

<style>
/* Hero Carousel Styles */
.hero-slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    transition: opacity 1s ease-in-out;
}

.hero-slide img {
    object-fit: cover;
    width: 100%;
    height: 100%;
    animation: zoomIn 20s ease-in-out infinite;
}

@keyframes zoomIn {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}

@keyframes spin-slow {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.animate-spin-slow {
    animation: spin-slow 3s linear infinite;
}

.hero-dot {
    transition: all 0.3s ease;
    cursor: pointer;
}

.hero-dot:hover {
    transform: scale(1.2);
}

.hero-dot.active {
    width: 3rem !important;
    background-color: white !important;
}

/* Responsive adjustments for hero carousel */
@media (max-width: 768px) {
    .hero-slide h2 {
        font-size: 2.5rem !important;
    }
    
    .hero-slide p {
        font-size: 1rem !important;
    }
    
    .hero-slide a {
        padding: 0.75rem 1.5rem !important;
        font-size: 0.875rem !important;
    }
}

/* Category-Sized Coupon Carousel Styles - Matching Shop by Category dimensions */
.category-coupon-container {
    position: relative;
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border-radius: 1rem;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    overflow: hidden;
    padding: 16px;
}

.category-coupon-carousel-container {
    position: relative;
    width: 100%;
    overflow: hidden;
}

.category-coupon-carousel {
    display: flex;
    transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.category-coupon-slide {
    transition: all 0.3s ease;
}

.category-coupon-slide:hover {
    transform: translateY(-5px);
}

.category-coupon-dot {
    transition: all 0.3s ease;
    cursor: pointer;
}

.category-coupon-dot:hover {
    transform: scale(1.2);
}

/* Enhanced interactive elements */
.category-coupon-dot {
    cursor: pointer;
}

.category-coupon-carousel button {
    cursor: pointer;
}

.category-coupon-slide button {
    cursor: pointer;
}

/* Focus management for accessibility */
.category-coupon-dot:focus {
    outline: 2px solid #16a34a;
    outline-offset: 2px;
    box-shadow: 0 0 0 4px rgba(22, 163, 74, 0.2);
}

.category-coupon-carousel button:focus {
    outline: 2px solid #16a34a;
    outline-offset: 2px;
    box-shadow: 0 0 0 4px rgba(22, 163, 74, 0.2);
}

.category-coupon-slide button:focus {
    outline: 2px solid #16a34a;
    outline-offset: 2px;
    box-shadow: 0 0 0 4px rgba(22, 163, 74, 0.2);
}

/* Animation for category carousel arrows */
.category-coupon-carousel button {
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.category-coupon-carousel button:hover {
    background: rgba(255, 255, 255, 1);
    transform: scale(1.1);
}

/* Responsive adjustments for category carousel */
@media (max-width: 768px) {
    .category-coupon-container {
        margin: 0 1rem;
    }
    
    .category-coupon-slide {
        padding: 0 0.5rem;
    }
}
</style>

<!-- Order Success Message -->
<?php if (isset($_GET['order_success']) && !empty($_GET['order_success'])): ?>
<div class="max-w-7xl mx-auto px-4 mt-8 mb-4 animate-slide-down">
    <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-2xl shadow-2xl p-6 border border-green-400 animate-pulse">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center animate-bounce-in">
                    <i class="fas fa-check-circle text-3xl"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-bold mb-1">
                        <i class="fas fa-shopping-bag mr-2"></i>
                        Order Placed Successfully!
                    </h3>
                    <p class="text-green-100 text-lg">
                        <?php if (isset($_GET['payment_success']) && $_GET['payment_success'] == '1'): ?>
                            Your order #<?php echo htmlspecialchars($_GET['order_success']); ?> has been placed and payment is confirmed. We'll deliver your order soon!
                        <?php else: ?>
                            Your order #<?php echo htmlspecialchars($_GET['order_success']); ?> has been placed successfully! You can pay cash on delivery when you receive your order.
                        <?php endif; ?>
                    </p>
                    <div class="mt-3 flex items-center space-x-4">
                        <a href="/orders/<?php echo htmlspecialchars($_GET['order_success']); ?>" class="inline-flex items-center px-4 py-2 bg-white text-green-600 rounded-lg font-semibold hover:bg-green-50 transition-all duration-300">
                            <i class="fas fa-eye mr-2"></i>
                            View Order Details
                        </a>
                        <a href="/orders" class="inline-flex items-center px-4 py-2 bg-white bg-opacity-20 text-white rounded-lg font-semibold hover:bg-opacity-30 transition-all duration-300">
                            <i class="fas fa-list mr-2"></i>
                            View All Orders
                        </a>
                    </div>
                </div>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-green-200 transition-colors duration-300 p-2">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Hero Carousel Section -->
<section class="relative w-full h-[600px] md:h-[700px] overflow-hidden bg-gray-900">
    <div id="heroCarousel" class="relative w-full h-full">
        <!-- Slide 1: Fresh Fruits -->
        <div class="hero-slide absolute inset-0 w-full h-full opacity-0 transition-opacity duration-1000 ease-in-out" data-slide="0">
            <div class="relative w-full h-full">
                <img src="https://images.unsplash.com/photo-1610832958506-aa56368176cf?w=1920&q=80" 
                     alt="Fresh Fruits" 
                     class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/40 to-transparent"></div>
                <div class="absolute inset-0 flex items-center">
                    <div class="max-w-7xl mx-auto px-4 w-full">
                        <div class="max-w-2xl animate-fade-in">
                            <h2 class="text-5xl md:text-7xl font-bold text-white mb-6 animate-slide-up">
                                Fresh Fruits
                            </h2>
                            <p class="text-xl md:text-2xl text-white/90 mb-8 animate-slide-up" style="animation-delay: 0.2s;">
                                Handpicked from local farms, delivered fresh to your door
                            </p>
                            <a href="/products?category=<?php echo urlencode('Fruits & Vegetables'); ?>" 
                               class="inline-block bg-green-600 text-white px-8 py-4 rounded-full font-bold text-lg hover:bg-green-700 transition-all duration-300 transform hover:scale-105 shadow-xl animate-slide-up" 
                               style="animation-delay: 0.4s;">
                                <i class="fas fa-shopping-cart mr-2"></i>Shop Fruits
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide 2: Fresh Vegetables -->
        <div class="hero-slide absolute inset-0 w-full h-full opacity-0 transition-opacity duration-1000 ease-in-out" data-slide="1">
            <div class="relative w-full h-full">
                <img src="https://images.unsplash.com/photo-1542838132-92c53300491e?w=1920&q=80" 
                     alt="Fresh Vegetables" 
                     class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/40 to-transparent"></div>
                <div class="absolute inset-0 flex items-center">
                    <div class="max-w-7xl mx-auto px-4 w-full">
                        <div class="max-w-2xl animate-fade-in">
                            <h2 class="text-5xl md:text-7xl font-bold text-white mb-6 animate-slide-up">
                                Fresh Vegetables
                            </h2>
                            <p class="text-xl md:text-2xl text-white/90 mb-8 animate-slide-up" style="animation-delay: 0.2s;">
                                Farm-fresh vegetables, rich in nutrients and flavor
                            </p>
                            <a href="/products?category=<?php echo urlencode('Fruits & Vegetables'); ?>" 
                               class="inline-block bg-green-600 text-white px-8 py-4 rounded-full font-bold text-lg hover:bg-green-700 transition-all duration-300 transform hover:scale-105 shadow-xl animate-slide-up" 
                               style="animation-delay: 0.4s;">
                                <i class="fas fa-shopping-cart mr-2"></i>Shop Vegetables
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide 3: Spices & Masala -->
        <div class="hero-slide absolute inset-0 w-full h-full opacity-0 transition-opacity duration-1000 ease-in-out" data-slide="2">
            <div class="relative w-full h-full">
                <img src="https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=1920&q=80" 
                     alt="Spices & Masala" 
                     class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/40 to-transparent"></div>
                <div class="absolute inset-0 flex items-center">
                    <div class="max-w-7xl mx-auto px-4 w-full">
                        <div class="max-w-2xl animate-fade-in">
                            <h2 class="text-5xl md:text-7xl font-bold text-white mb-6 animate-slide-up">
                                Premium Spices
                            </h2>
                            <p class="text-xl md:text-2xl text-white/90 mb-8 animate-slide-up" style="animation-delay: 0.2s;">
                                Authentic spices and masala to enhance your cooking
                            </p>
                            <a href="/products?category=<?php echo urlencode('Spices & Herbs'); ?>" 
                               class="inline-block bg-green-600 text-white px-8 py-4 rounded-full font-bold text-lg hover:bg-green-700 transition-all duration-300 transform hover:scale-105 shadow-xl animate-slide-up" 
                               style="animation-delay: 0.4s;">
                                <i class="fas fa-shopping-cart mr-2"></i>Shop Spices
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide 4: Bakery & Dairy Products -->
        <div class="hero-slide absolute inset-0 w-full h-full opacity-0 transition-opacity duration-1000 ease-in-out" data-slide="3">
            <div class="relative w-full h-full">
                <img src="https://images.unsplash.com/photo-1486427944299-d1955d23e34d?w=1920&q=80" 
                     alt="Bakery and Dairy Products" 
                     class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-r from-amber-600/75 via-yellow-500/65 to-orange-500/75"></div>
                <div class="absolute inset-0 flex items-center">
                    <div class="max-w-7xl mx-auto px-4 w-full">
                        <div class="max-w-2xl animate-fade-in">
                            <!-- Category Badge -->
                            <div class="inline-flex items-center bg-white/90 text-amber-700 px-6 py-2 rounded-full font-bold text-lg mb-6 animate-bounce-in shadow-xl">
                                <i class="fas fa-bread-slice mr-2 text-xl"></i>
                                <span class="mr-3">BAKERY</span>
                                <i class="fas fa-circle text-xs mx-2"></i>
                                <i class="fas fa-cheese mr-2 text-xl"></i>
                                <span>DAIRY</span>
                            </div>
                            <h2 class="text-5xl md:text-7xl font-bold text-white mb-6 animate-slide-up drop-shadow-2xl">
                                Fresh Bakery & Dairy
                            </h2>
                            <p class="text-xl md:text-2xl text-white mb-8 animate-slide-up drop-shadow-lg" style="animation-delay: 0.2s;">
                                Daily fresh bread, pastries, milk, cheese, and eggs. Start your day right with our premium bakery and dairy products!
                            </p>
                            <div class="flex flex-col sm:flex-row gap-4 animate-slide-up" style="animation-delay: 0.4s;">
                                <a href="/products?category=<?php echo urlencode('Bakery'); ?>" 
                                   class="inline-block bg-gradient-to-r from-amber-500 to-orange-600 text-white px-8 py-4 rounded-full font-bold text-lg hover:from-amber-600 hover:to-orange-700 transition-all duration-300 transform hover:scale-105 shadow-2xl text-center">
                                    <i class="fas fa-bread-slice mr-2"></i>Shop Bakery
                                </a>
                                <a href="/products?category=<?php echo urlencode('Dairy & Eggs'); ?>" 
                                   class="inline-block bg-white/25 backdrop-blur-sm text-white px-8 py-4 rounded-full font-bold text-lg hover:bg-white/35 transition-all duration-300 transform hover:scale-105 shadow-xl text-center border-2 border-white/40">
                                    <i class="fas fa-cheese mr-2"></i>Shop Dairy
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Arrows -->
    <button onclick="previousHeroSlide()" 
            class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white/90 hover:bg-white text-green-600 hover:text-green-700 w-14 h-14 rounded-full flex items-center justify-center transition-all duration-300 z-20 shadow-xl hover:scale-110"
            aria-label="Previous slide"
            title="Previous slide">
        <i class="fas fa-chevron-left text-xl" aria-hidden="true"></i>
    </button>
    
    <button onclick="nextHeroSlide()" 
            class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white/90 hover:bg-white text-green-600 hover:text-green-700 w-14 h-14 rounded-full flex items-center justify-center transition-all duration-300 z-20 shadow-xl hover:scale-110"
            aria-label="Next slide"
            title="Next slide">
        <i class="fas fa-chevron-right text-xl" aria-hidden="true"></i>
    </button>

    <!-- Dot Indicators -->
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 z-20 flex space-x-3" role="tablist" aria-label="Carousel navigation">
        <button onclick="goToHeroSlide(0)" 
                class="hero-dot w-12 h-3 rounded-full bg-white hover:bg-white transition-all duration-300"
                data-slide="0"
                role="tab"
                aria-label="Go to slide 1"
                aria-selected="true"
                title="Go to slide 1"></button>
        <button onclick="goToHeroSlide(1)" 
                class="hero-dot w-3 h-3 rounded-full bg-white/50 hover:bg-white transition-all duration-300"
                data-slide="1"
                role="tab"
                aria-label="Go to slide 2"
                aria-selected="false"
                title="Go to slide 2"></button>
        <button onclick="goToHeroSlide(2)" 
                class="hero-dot w-3 h-3 rounded-full bg-white/50 hover:bg-white transition-all duration-300"
                data-slide="2"
                role="tab"
                aria-label="Go to slide 3"
                aria-selected="false"
                title="Go to slide 3"></button>
        <button onclick="goToHeroSlide(3)" 
                class="hero-dot w-3 h-3 rounded-full bg-white/50 hover:bg-white transition-all duration-300"
                data-slide="3"
                role="tab"
                aria-label="Go to slide 4"
                aria-selected="false"
                title="Go to slide 4"></button>
    </div>
</section>

<!-- Hero Section -->
<section class="gradient-bg text-white py-24 relative overflow-hidden">
    <div class="absolute inset-0 bg-black bg-opacity-20"></div>
    <div class="absolute inset-0">
        <div class="absolute top-10 left-10 w-20 h-20 bg-white bg-opacity-10 rounded-full animate-bounce-in" style="animation-delay: 0.5s;"></div>
        <div class="absolute top-20 right-20 w-16 h-16 bg-white bg-opacity-10 rounded-full animate-bounce-in" style="animation-delay: 1s;"></div>
        <div class="absolute bottom-20 left-20 w-12 h-12 bg-white bg-opacity-10 rounded-full animate-bounce-in" style="animation-delay: 1.5s;"></div>
    </div>
    <div class="max-w-7xl mx-auto px-4 text-center relative z-10 animate-fade-in">
        <h1 class="text-5xl md:text-7xl font-bold mb-6 animate-slide-up">
            Fresh Groceries
            <span class="block text-yellow-300">Delivered</span>
        </h1>
        <p class="text-xl md:text-2xl mb-10 animate-slide-up" style="animation-delay: 0.3s;">
            Get fresh fruits, vegetables, and groceries delivered to your doorstep
        </p>
        <div class="animate-slide-up" style="animation-delay: 0.6s;">
            <a href="/products" class="btn-primary inline-block text-white px-10 py-4 rounded-full font-bold text-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-105">
                <i class="fas fa-shopping-cart mr-2"></i>
                Shop Now
            </a>
        </div>
    </div>
</section>



<!-- Categories Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-4xl font-bold text-center mb-16 animate-slide-up">Shop by Category</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-8">
            <?php foreach ($categories as $category): ?>
                <a href="/products?category=<?php echo $category['id']; ?>"
                   class="bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-lg hover-lift card-hover p-8 text-center group animate-on-scroll"
                   style="animation-delay: <?php echo array_search($category, $categories) * 0.1; ?>s;">
                    <div class="w-20 h-20 bg-gradient-to-br from-green-400 to-green-600 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300 shadow-lg">
                        <i class="fas fa-shopping-basket text-white text-3xl"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 group-hover:text-green-600 transition-colors duration-300">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </h3>
                    <div class="mt-2 w-0 group-hover:w-full h-0.5 bg-green-500 transition-all duration-300 mx-auto"></div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Special Discounts Section - Category Size -->
<?php if (!empty($coupons)): ?>
<section class="py-20 bg-gradient-to-r from-green-50 to-emerald-50">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-4xl font-bold text-center mb-16 animate-slide-up">
            <i class="fas fa-tags mr-3 text-green-600"></i>
            Special Discounts
        </h2>
        
        <!-- Category-Sized Coupon Carousel Container -->
        <div class="relative category-coupon-container">
            <!-- Carousel Wrapper -->
            <div class="category-coupon-carousel-container overflow-hidden rounded-2xl">
                <div class="category-coupon-carousel flex transition-transform duration-500 ease-in-out" 
                     id="categoryCouponCarousel" 
                     role="region" 
                     aria-label="Category-sized coupon carousel"
                     aria-live="polite"
                     aria-atomic="true"
                     tabindex="0">
                    <?php foreach ($coupons as $index => $coupon): ?>
                        <div class="category-coupon-slide flex-shrink-0 w-full md:w-1/2 lg:w-1/3 px-4" 
                             role="group" 
                             aria-label="Coupon <?php echo $index + 1; ?> of <?php echo count($coupons); ?>">
                            <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-lg hover-lift card-hover p-8 text-center group animate-on-scroll border border-green-200 hover:border-green-400 transition-all duration-300">
                                <!-- Coupon Icon (matching category icon size) -->
                                <div class="w-20 h-20 bg-gradient-to-br from-green-400 to-green-600 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300 shadow-lg">
                                    <i class="fas fa-percentage text-white text-3xl"></i>
                                </div>
                                
                                <!-- Coupon Code (matching category text style) -->
                                <h3 class="font-bold text-gray-800 group-hover:text-green-600 transition-colors duration-300 mb-2">
                                    <?php echo htmlspecialchars($coupon['code']); ?>
                                </h3>
                                
                                <!-- Discount Value (compact) -->
                                <div class="mb-4">
                                    <?php if ($coupon['discount_type'] === 'percentage'): ?>
                                        <div class="text-lg font-bold text-green-600">
                                            <?php echo number_format($coupon['discount_value']); ?>% OFF
                                        </div>
                                    <?php else: ?>
                                        <div class="text-lg font-bold text-green-600">
                                            ৳<?php echo number_format($coupon['discount_value']); ?> OFF
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Minimum Order (compact) -->
                                <div class="mb-4 text-xs text-gray-500">
                                    Min: ৳<?php echo number_format($coupon['min_order_amount'], 0); ?>
                                </div>

                                <!-- Copy Button (compact) -->
                                <button onclick="copyCouponCode('<?php echo htmlspecialchars($coupon['code']); ?>')" 
                                        class="w-full bg-gradient-to-r from-green-600 to-emerald-600  py-2 px-4 rounded-lg font-bold text-sm hover:from-green-700 hover:to-emerald-700 transition-all duration-300 transform hover:scale-105 shadow-md">
                                    <i class="fas fa-copy mr-1"></i>
                                    Copy
                                </button>
                                
                                <!-- Hover line effect (matching category style) -->
                                <div class="mt-2 w-0 group-hover:w-full h-0.5 bg-green-500 transition-all duration-300 mx-auto"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Navigation Arrows -->
            <button onclick="previousCategoryCoupon()" 
                    class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white shadow-lg hover:shadow-xl text-green-600 hover:text-green-700 w-12 h-12 rounded-full flex items-center justify-center transition-all duration-300 z-10"
                    aria-label="Previous coupon"
                    title="Previous coupon">
                <i class="fas fa-chevron-left" aria-hidden="true"></i>
            </button>
            
            <button onclick="nextCategoryCoupon()" 
                    class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white shadow-lg hover:shadow-xl text-green-600 hover:text-green-700 w-12 h-12 rounded-full flex items-center justify-center transition-all duration-300 z-10"
                    aria-label="Next coupon"
                    title="Next coupon">
                <i class="fas fa-chevron-right" aria-hidden="true"></i>
            </button>

            <!-- Dots Indicator -->
            <div class="flex justify-center mt-8 space-x-2" role="tablist" aria-label="Coupon navigation">
                <?php foreach ($coupons as $index => $coupon): ?>
                    <button onclick="goToCategoryCoupon(<?php echo $index; ?>)" 
                            class="category-coupon-dot w-3 h-3 rounded-full bg-gray-300 hover:bg-green-500 transition-all duration-300 <?php echo $index === 0 ? 'bg-green-500' : ''; ?>"
                            data-slide="<?php echo $index; ?>"
                            role="tab"
                            aria-label="Go to coupon <?php echo $index + 1; ?>"
                            aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>"
                            title="Go to coupon <?php echo $index + 1; ?>"></button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Featured/Recommended Products Section -->
<section class="py-20 bg-gradient-to-br from-gray-50 to-white">
    <div class="max-w-7xl mx-auto px-4">
        <?php if (!empty($recommendedProducts)): ?>
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-4xl font-bold animate-slide-up">
                    <i class="fas fa-heart text-red-500 mr-3"></i>
                    Recommended for You
                </h2>
                <a href="/profile" class="text-green-600 hover:text-green-700 font-semibold">
                    Update preferences <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
            <p class="text-gray-600 text-center mb-12">Based on your diet profile, we recommend these products</p>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php foreach ($recommendedProducts as $product): ?>
                    <div class="bg-white rounded-2xl shadow-xl hover-lift card-hover overflow-hidden group animate-on-scroll">
                        <div class="relative h-56 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center overflow-hidden">
                            <?php if ($product['image']): ?>
                                <img src="<?php echo htmlspecialchars($product['image']); ?>"
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <?php else: ?>
                                <i class="fas fa-image text-gray-400 text-5xl"></i>
                            <?php endif; ?>
                            <div class="absolute top-4 right-4 bg-green-500 text-white px-3 py-1 rounded-full text-sm font-semibold animate-bounce-in">
                                Featured
                            </div>
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center">
                                <a href="/products/<?php echo $product['id']; ?>"
                                   class="bg-white text-green-600 px-4 py-2 rounded-full font-semibold opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-y-4 group-hover:translate-y-0 hover:bg-green-50">
                                    <i class="fas fa-eye mr-2"></i>View Details
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

                            <button class="w-full btn-primary text-white py-3 rounded-xl font-semibold add-to-cart group-hover:shadow-lg transition-all duration-300"
                                    data-product-id="<?php echo $product['id']; ?>"
                                    data-original-text="<i class='fas fa-cart-plus mr-2'></i>Add to Cart">
                                <i class="fas fa-cart-plus mr-2"></i>Add to Cart
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <h2 class="text-4xl font-bold text-center mb-16 animate-slide-up">Featured Products</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php foreach ($featuredProducts as $product): ?>
                    <div class="bg-white rounded-2xl shadow-xl hover-lift card-hover overflow-hidden group animate-on-scroll">
                        <div class="relative h-56 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center overflow-hidden">
                            <?php if ($product['image']): ?>
                                <img src="<?php echo htmlspecialchars($product['image']); ?>"
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <?php else: ?>
                                <i class="fas fa-image text-gray-400 text-5xl"></i>
                            <?php endif; ?>
                            <div class="absolute top-4 right-4 bg-green-500 text-white px-3 py-1 rounded-full text-sm font-semibold animate-bounce-in">
                                Featured
                            </div>
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center">
                                <a href="/products/<?php echo $product['id']; ?>"
                                   class="bg-white text-green-600 px-4 py-2 rounded-full font-semibold opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-y-4 group-hover:translate-y-0 hover:bg-green-50">
                                    <i class="fas fa-eye mr-2"></i>View Details
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

                            <button class="w-full btn-primary text-white py-3 rounded-xl font-semibold add-to-cart group-hover:shadow-lg transition-all duration-300"
                                    data-product-id="<?php echo $product['id']; ?>"
                                    data-original-text="<i class='fas fa-cart-plus mr-2'></i>Add to Cart">
                                <i class="fas fa-cart-plus mr-2"></i>Add to Cart
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="text-center mt-12">
            <a href="/products" class="btn-primary inline-block text-white px-8 py-4 rounded-full font-bold text-lg hover:shadow-2xl transition-all duration-300">
                <i class="fas fa-arrow-right mr-2"></i>View All Products
            </a>
        </div>
    </div>
</section>

<!-- Smart Picks This Week Section -->
<?php if (isset($_SESSION['user_id'])): ?>
<section class="py-20 bg-gradient-to-r from-purple-600 to-pink-600 text-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between mb-12">
            <h2 class="text-4xl font-bold animate-slide-up">
                <i class="fas fa-sparkles mr-3 text-yellow-300"></i>
                Your Smart Picks This Week
            </h2>
            <a href="/profile" class="text-yellow-300 hover:text-yellow-100 font-semibold flex items-center">
                Manage preferences <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
        <p class="text-center text-purple-100 mb-12 text-lg">AI-powered recommendations based on your diet profile and preferences</p>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Smart Pick 1 -->
            <div class="bg-white bg-opacity-10 backdrop-blur-lg rounded-2xl p-6 hover:bg-opacity-20 transition-all duration-300 border border-white border-opacity-20">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-16 h-16 bg-yellow-400 rounded-full flex items-center justify-center">
                        <i class="fas fa-star text-white text-2xl"></i>
                    </div>
                    <span class="bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-full">TOP PICK</span>
                </div>
                <h3 class="text-xl font-bold mb-2">Calorie-Controlled</h3>
                <p class="text-purple-100 text-sm mb-4">Products that fit your daily calorie target perfectly</p>
                <div class="flex items-center text-yellow-300">
                    <i class="fas fa-fire mr-2"></i>
                    <span class="font-semibold">Low Calorie</span>
                </div>
            </div>

            <!-- Smart Pick 2 -->
            <div class="bg-white bg-opacity-10 backdrop-blur-lg rounded-2xl p-6 hover:bg-opacity-20 transition-all duration-300 border border-white border-opacity-20">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-16 h-16 bg-blue-400 rounded-full flex items-center justify-center">
                        <i class="fas fa-heart text-white text-2xl"></i>
                    </div>
                    <span class="bg-blue-500 text-white text-xs font-bold px-3 py-1 rounded-full">BEST FOR YOU</span>
                </div>
                <h3 class="text-xl font-bold mb-2">Diet-Friendly</h3>
                <p class="text-purple-100 text-sm mb-4">Aligned with your specific dietary goals and preferences</p>
                <div class="flex items-center text-blue-300">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span class="font-semibold">Approved</span>
                </div>
            </div>

            <!-- Smart Pick 3 -->
            <div class="bg-white bg-opacity-10 backdrop-blur-lg rounded-2xl p-6 hover:bg-opacity-20 transition-all duration-300 border border-white border-opacity-20">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-16 h-16 bg-green-400 rounded-full flex items-center justify-center">
                        <i class="fas fa-leaf text-white text-2xl"></i>
                    </div>
                    <span class="bg-purple-500 text-white text-xs font-bold px-3 py-1 rounded-full">TRENDING</span>
                </div>
                <h3 class="text-xl font-bold mb-2">Nutrition Rich</h3>
                <p class="text-purple-100 text-sm mb-4">High-quality nutrition that supports your wellness goals</p>
                <div class="flex items-center text-green-300">
                    <i class="fas fa-medal mr-2"></i>
                    <span class="font-semibold">Premium</span>
                </div>
            </div>
        </div>

        <div class="text-center mt-12">
            <a href="/products" class="bg-white text-purple-600 px-8 py-4 rounded-full font-bold text-lg hover:bg-purple-50 transition-all duration-300 inline-block">
                <i class="fas fa-shopping-bag mr-2"></i>Explore All Smart Picks
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- My Wishlist Section -->
<?php if (isset($_SESSION['user_id']) && !empty($wishlistItems)): ?>
<section class="py-20 bg-gradient-to-r from-red-50 to-pink-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between mb-12">
            <h2 class="text-4xl font-bold animate-slide-up">
                <i class="fas fa-heart text-red-500 mr-3"></i>
                My Wishlist
            </h2>
            <a href="/wishlist" class="text-red-600 hover:text-red-700 font-semibold flex items-center">
                View All <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
        <p class="text-center text-gray-600 mb-12">Your saved favorite products</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php foreach ($wishlistItems as $item): ?>
                <div class="bg-white rounded-2xl shadow-xl hover-lift card-hover overflow-hidden group animate-on-scroll">
                    <div class="relative h-48 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center overflow-hidden">
                        <?php if ($item['image']): ?>
                            <img src="<?php echo htmlspecialchars($item['image']); ?>"
                                 alt="<?php echo htmlspecialchars($item['name']); ?>"
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <?php else: ?>
                            <i class="fas fa-image text-gray-400 text-5xl"></i>
                        <?php endif; ?>
                        <div class="absolute top-4 right-4 bg-red-500 text-white px-3 py-1 rounded-full text-sm font-semibold animate-bounce-in">
                            <i class="fas fa-heart"></i> Saved
                        </div>
                        <?php if (isset($item['stock_quantity']) && $item['stock_quantity'] == 0): ?>
                            <div class="absolute top-4 left-4 bg-red-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                                Out of Stock
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="p-6">
                        <div class="mb-2">
                            <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">
                                <?php echo htmlspecialchars($item['category_name'] ?? 'Uncategorized'); ?>
                            </span>
                        </div>
                        <h3 class="font-bold text-xl text-gray-800 group-hover:text-red-600 transition-colors duration-300 mb-2">
                            <?php echo htmlspecialchars($item['name']); ?>
                        </h3>
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-2xl font-bold text-green-600">৳<?php echo number_format($item['price'], 2); ?></span>
                            <span class="text-sm text-gray-500">/ <?php echo htmlspecialchars($item['unit']); ?></span>
                        </div>
                        <div class="space-y-2">
                            <a href="/products/<?php echo $item['product_id']; ?>" 
                               class="block w-full bg-blue-600 text-white py-3 rounded-xl font-semibold hover:bg-blue-700 transition-colors duration-200 text-center">
                                <i class="fas fa-eye mr-2"></i>View Details
                            </a>
                            <button onclick="addToCartFromWishlist(<?php echo $item['product_id']; ?>)" 
                                    class="w-full bg-green-600 text-white py-3 rounded-xl font-semibold hover:bg-green-700 transition-colors duration-200 add-to-cart"
                                    data-product-id="<?php echo $item['product_id']; ?>">
                                <i class="fas fa-cart-plus mr-2"></i>Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (count($wishlistItems) >= 4): ?>
            <div class="text-center mt-12">
                <a href="/wishlist" class="btn-primary inline-block text-white px-8 py-4 rounded-full font-bold text-lg hover:shadow-2xl transition-all duration-300">
                    <i class="fas fa-heart mr-2"></i>View All Wishlist Items
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- Features Section -->
<section class="py-20 bg-gradient-to-r from-green-50 to-blue-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
            <div class="text-center animate-on-scroll">
                <div class="w-24 h-24 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-xl hover-lift">
                    <i class="fas fa-truck text-white text-3xl"></i>
                </div>
                <h3 class="text-2xl font-bold mb-4 text-gray-800">Lightning Fast Delivery</h3>
                <p class="text-gray-600 text-lg leading-relaxed">Get your groceries delivered within 2 hours with our express delivery service</p>
            </div>
            <div class="text-center animate-on-scroll" style="animation-delay: 0.2s;">
                <div class="w-24 h-24 bg-gradient-to-br from-green-400 to-green-600 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-xl hover-lift">
                    <i class="fas fa-leaf text-white text-3xl"></i>
                </div>
                <h3 class="text-2xl font-bold mb-4 text-gray-800">Farm Fresh Products</h3>
                <p class="text-gray-600 text-lg leading-relaxed">Direct from local farms to your doorstep - only the freshest fruits and vegetables</p>
            </div>
            <div class="text-center animate-on-scroll" style="animation-delay: 0.4s;">
                <div class="w-24 h-24 bg-gradient-to-br from-purple-400 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-xl hover-lift">
                    <i class="fas fa-shield-alt text-white text-3xl"></i>
                </div>
                <h3 class="text-2xl font-bold mb-4 text-gray-800">Quality Guarantee</h3>
                <p class="text-gray-600 text-lg leading-relaxed">100% satisfaction guarantee or your money back - we stand behind our products</p>
            </div>
        </div>
    </div>
</section>

<script>
// Enhanced add to cart functionality with comprehensive logging
document.addEventListener('DOMContentLoaded', function() {
    console.log('🏠 DOM loaded, setting up cart functionality on home page');
    
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    console.log(`🏠 Found ${addToCartButtons.length} add-to-cart buttons`);
    
    addToCartButtons.forEach((button, index) => {
        const productId = button.getAttribute('data-product-id');
        console.log(`🏠 Setting up button ${index + 1} for product ID: ${productId}`);
        
        button.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('🏠 ===== ADD TO CART CLICKED (HOME) =====');
            
            const productId = this.getAttribute('data-product-id');
            const originalText = this.getAttribute('data-original-text') || '<i class="fas fa-cart-plus mr-2"></i>Add to Cart';
            
            console.log('🏠 Product ID:', productId);
            console.log('🏠 Button element:', this);
            console.log('🏠 Original text:', originalText);

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
            console.log('🏠 Setting button to loading state');
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Adding...';

            // Prepare request data
            const requestData = 'product_id=' + encodeURIComponent(productId) + '&quantity=1';
            console.log('🏠 Request data:', requestData);
            console.log('🏠 Making request to: /cart/add');

            fetch('/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: requestData
            })
            .then(response => {
                console.log('🏠 Response received:', {
                    status: response.status,
                    statusText: response.statusText,
                    ok: response.ok,
                    url: response.url
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status} - ${response.statusText}`);
                }
                return response.text().then(text => {
                    console.log('🏠 Raw response text:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('🏠 Failed to parse JSON response:', e);
                        throw new Error('Invalid JSON response: ' + text);
                    }
                });
            })
            .then(data => {
                console.log('🏠 Parsed response data:', data);
                
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
                console.error('🏠 ===== ERROR OCCURRED =====');
                console.error('🏠 Error type:', error.constructor.name);
                console.error('🏠 Error message:', error.message);
                console.error('🏠 Error stack:', error.stack);
                
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
    
    console.log('🏠 All cart event listeners set up successfully on home page');
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

// Add to wishlist functionality for homepage
document.addEventListener('DOMContentLoaded', function() {
    console.log('❤️ Setting up wishlist buttons on homepage');
    
    document.querySelectorAll('.add-to-wishlist-home').forEach(button => {
        button.addEventListener('click', function() {
            console.log('❤️ ===== ADD TO WISHLIST CLICKED (HOME) =====');
            
            const productId = this.getAttribute('data-product-id');
            const originalContent = this.innerHTML;
            
            console.log('❤️ Product ID:', productId);
            
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
                console.log('❤️ Response status:', response.status);
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
    
    console.log('✅ Wishlist buttons set up on homepage');
    updateCartCount();
    updateWishlistCount();
});

// Update wishlist count
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

// Add to cart from wishlist
function addToCartFromWishlist(productId) {
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
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
            updateCartCount();
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            }, 2000);
        } else {
            showToast(data.message || 'Failed to add to cart', 'error');
            button.disabled = false;
            button.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

// Copy coupon code to clipboard with enhanced error handling
const copyCouponCode = (code) => {
    // Sanitize the code to prevent XSS
    const sanitizedCode = code.replace(/[<>\"']/g, '');
    
    if (navigator.clipboard && window.isSecureContext) {
        // Modern clipboard API
        navigator.clipboard.writeText(sanitizedCode)
            .then(() => {
                showToast(`Coupon code "${sanitizedCode}" copied to clipboard!`, 'success');
            })
            .catch((err) => {
                console.error('Clipboard API failed:', err);
                fallbackCopy(sanitizedCode);
            });
    } else {
        // Fallback for older browsers or non-secure contexts
        fallbackCopy(sanitizedCode);
    }
};

// Fallback copy method
const fallbackCopy = (code) => {
    try {
        const textArea = document.createElement('textarea');
        textArea.value = code;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        const successful = document.execCommand('copy');
        document.body.removeChild(textArea);
        
        if (successful) {
            showToast(`Coupon code "${code}" copied to clipboard!`, 'success');
        } else {
            showToast('Failed to copy coupon code. Please try again.', 'error');
        }
    } catch (err) {
        console.error('Fallback copy failed:', err);
        showToast('Failed to copy coupon code. Please try again.', 'error');
    }
};

// Category-Sized Coupon Carousel Functionality
let currentCategoryCouponSlide = 0;
let categoryCouponAutoPlayInterval;
const totalCategoryCouponSlides = <?php echo count($coupons); ?>;

// Initialize category coupon carousel
const initCategoryCouponCarousel = () => {
    try {
        if (totalCategoryCouponSlides <= 1) {
            console.log('Only one category coupon slide, skipping carousel initialization');
            return;
        }
        
        console.log(`Initializing category coupon carousel with ${totalCategoryCouponSlides} slides`);
        
        // Start auto-play
        startCategoryCouponAutoPlay();
        
        // Pause auto-play on hover
        const carousel = document.getElementById('categoryCouponCarousel');
        if (carousel) {
            carousel.addEventListener('mouseenter', stopCategoryCouponAutoPlay);
            carousel.addEventListener('mouseleave', startCategoryCouponAutoPlay);
            
            // Add keyboard navigation for accessibility
            carousel.addEventListener('keydown', handleCategoryCarouselKeydown);
            
            console.log('Category carousel event listeners added successfully');
        } else {
            console.error('Category coupon carousel element not found');
        }
        
        // Add resize event listener for responsive behavior
        window.addEventListener('resize', function() {
            updateCategoryCouponCarousel();
        });
    } catch (error) {
        console.error('Error initializing category coupon carousel:', error);
    }
};

// Start auto-play for category carousel
const startCategoryCouponAutoPlay = () => {
    try {
        if (totalCategoryCouponSlides <= 1) return;
        
        stopCategoryCouponAutoPlay(); // Clear any existing interval
        categoryCouponAutoPlayInterval = setInterval(() => {
            nextCategoryCoupon();
        }, 5000); // Auto-rotate every 5 seconds (standard timing)
        
        console.log('Category coupon carousel auto-play started');
    } catch (error) {
        console.error('Error starting category coupon auto-play:', error);
    }
};

// Stop auto-play for category carousel
const stopCategoryCouponAutoPlay = () => {
    try {
        if (categoryCouponAutoPlayInterval) {
            clearInterval(categoryCouponAutoPlayInterval);
            categoryCouponAutoPlayInterval = null;
            console.log('Category coupon carousel auto-play stopped');
        }
    } catch (error) {
        console.error('Error stopping category coupon auto-play:', error);
    }
};

// Next category coupon
const nextCategoryCoupon = () => {
    try {
        if (totalCategoryCouponSlides <= 1) return;
        
        currentCategoryCouponSlide = (currentCategoryCouponSlide + 1) % totalCategoryCouponSlides;
        updateCategoryCouponCarousel();
        console.log(`Moved to category coupon slide ${currentCategoryCouponSlide + 1}`);
    } catch (error) {
        console.error('Error moving to next category coupon:', error);
    }
};

// Previous category coupon
const previousCategoryCoupon = () => {
    try {
        if (totalCategoryCouponSlides <= 1) return;
        
        currentCategoryCouponSlide = currentCategoryCouponSlide === 0 ? totalCategoryCouponSlides - 1 : currentCategoryCouponSlide - 1;
        updateCategoryCouponCarousel();
        console.log(`Moved to category coupon slide ${currentCategoryCouponSlide + 1}`);
    } catch (error) {
        console.error('Error moving to previous category coupon:', error);
    }
};

// Go to specific category coupon
const goToCategoryCoupon = (slideIndex) => {
    try {
        if (totalCategoryCouponSlides <= 1) return;
        
        // Validate slide index
        if (slideIndex < 0 || slideIndex >= totalCategoryCouponSlides) {
            console.error(`Invalid category slide index: ${slideIndex}`);
            return;
        }
        
        currentCategoryCouponSlide = slideIndex;
        updateCategoryCouponCarousel();
        console.log(`Moved to category coupon slide ${currentCategoryCouponSlide + 1}`);
    } catch (error) {
        console.error('Error going to category coupon slide:', error);
    }
};

// Update category carousel
const updateCategoryCouponCarousel = () => {
    try {
        const carousel = document.getElementById('categoryCouponCarousel');
        const dots = document.querySelectorAll('.category-coupon-dot');
        
        if (!carousel) {
            console.error('Category coupon carousel element not found');
            return;
        }
        
        // Calculate slide width based on screen size
        const slideWidth = window.innerWidth >= 1024 ? 33.333 : (window.innerWidth >= 768 ? 50 : 100);
        const translateX = -currentCategoryCouponSlide * slideWidth;
        
        // Use requestAnimationFrame for smooth animations
        requestAnimationFrame(() => {
            carousel.style.transform = `translateX(${translateX}%)`;
        });
        
        // Update dots with enhanced error handling and ARIA attributes
        dots.forEach((dot, index) => {
            try {
                if (index === currentCategoryCouponSlide) {
                    dot.classList.add('bg-green-500');
                    dot.classList.remove('bg-gray-300');
                    dot.setAttribute('aria-selected', 'true');
                } else {
                    dot.classList.remove('bg-green-500');
                    dot.classList.add('bg-gray-300');
                    dot.setAttribute('aria-selected', 'false');
                }
            } catch (error) {
                console.error(`Error updating category dot ${index}:`, error);
            }
        });
        
        // Update carousel ARIA attributes
        carousel.setAttribute('aria-live', 'polite');
        carousel.setAttribute('aria-atomic', 'true');
        
        console.log(`Updated category carousel to slide ${currentCategoryCouponSlide + 1}`);
    } catch (error) {
        console.error('Error updating category coupon carousel:', error);
    }
};

// Keyboard navigation for category carousel accessibility
const handleCategoryCarouselKeydown = (event) => {
    try {
        switch (event.key) {
            case 'ArrowLeft':
                event.preventDefault();
                previousCategoryCoupon();
                break;
            case 'ArrowRight':
                event.preventDefault();
                nextCategoryCoupon();
                break;
            case 'Home':
                event.preventDefault();
                goToCategoryCoupon(0);
                break;
            case 'End':
                event.preventDefault();
                goToCategoryCoupon(totalCategoryCouponSlides - 1);
                break;
        }
    } catch (error) {
        console.error('Error handling category carousel keyboard navigation:', error);
    }
};

// Initialize category carousel when page loads
document.addEventListener('DOMContentLoaded', function() {
    initCategoryCouponCarousel();
    initHeroCarousel();
});

// ==================== Hero Carousel Functionality ====================
let currentHeroSlide = 0;
let heroAutoPlayInterval;
const totalHeroSlides = 4;

// Initialize hero carousel
const initHeroCarousel = () => {
    try {
        console.log('Initializing hero carousel with', totalHeroSlides, 'slides');
        
        // Show first slide
        updateHeroCarousel();
        
        // Start auto-play
        startHeroAutoPlay();
        
        // Pause auto-play on hover
        const carousel = document.getElementById('heroCarousel');
        if (carousel) {
            carousel.addEventListener('mouseenter', stopHeroAutoPlay);
            carousel.addEventListener('mouseleave', startHeroAutoPlay);
            
            // Add keyboard navigation for accessibility
            carousel.addEventListener('keydown', handleHeroCarouselKeydown);
            
            console.log('Hero carousel event listeners added successfully');
        } else {
            console.error('Hero carousel element not found');
        }
    } catch (error) {
        console.error('Error initializing hero carousel:', error);
    }
};

// Start auto-play for hero carousel
const startHeroAutoPlay = () => {
    try {
        stopHeroAutoPlay(); // Clear any existing interval
        heroAutoPlayInterval = setInterval(() => {
            nextHeroSlide();
        }, 5000); // Auto-rotate every 5 seconds
        
        console.log('Hero carousel auto-play started');
    } catch (error) {
        console.error('Error starting hero auto-play:', error);
    }
};

// Stop auto-play for hero carousel
const stopHeroAutoPlay = () => {
    try {
        if (heroAutoPlayInterval) {
            clearInterval(heroAutoPlayInterval);
            heroAutoPlayInterval = null;
            console.log('Hero carousel auto-play stopped');
        }
    } catch (error) {
        console.error('Error stopping hero auto-play:', error);
    }
};

// Next hero slide
const nextHeroSlide = () => {
    try {
        currentHeroSlide = (currentHeroSlide + 1) % totalHeroSlides;
        updateHeroCarousel();
        console.log(`Moved to hero slide ${currentHeroSlide + 1}`);
    } catch (error) {
        console.error('Error moving to next hero slide:', error);
    }
};

// Previous hero slide
const previousHeroSlide = () => {
    try {
        currentHeroSlide = currentHeroSlide === 0 ? totalHeroSlides - 1 : currentHeroSlide - 1;
        updateHeroCarousel();
        console.log(`Moved to hero slide ${currentHeroSlide + 1}`);
    } catch (error) {
        console.error('Error moving to previous hero slide:', error);
    }
};

// Go to specific hero slide
const goToHeroSlide = (slideIndex) => {
    try {
        // Validate slide index
        if (slideIndex < 0 || slideIndex >= totalHeroSlides) {
            console.error(`Invalid hero slide index: ${slideIndex}`);
            return;
        }
        
        currentHeroSlide = slideIndex;
        updateHeroCarousel();
        console.log(`Moved to hero slide ${currentHeroSlide + 1}`);
    } catch (error) {
        console.error('Error going to hero slide:', error);
    }
};

// Update hero carousel
const updateHeroCarousel = () => {
    try {
        const slides = document.querySelectorAll('.hero-slide');
        const dots = document.querySelectorAll('.hero-dot');
        
        if (slides.length === 0) {
            console.error('Hero slides not found');
            return;
        }
        
        // Update slides visibility
        slides.forEach((slide, index) => {
            if (index === currentHeroSlide) {
                slide.classList.remove('opacity-0');
                slide.classList.add('opacity-100');
                slide.style.zIndex = '10';
            } else {
                slide.classList.remove('opacity-100');
                slide.classList.add('opacity-0');
                slide.style.zIndex = '1';
            }
        });
        
        // Update dots
        dots.forEach((dot, index) => {
            try {
                if (index === currentHeroSlide) {
                    dot.classList.add('bg-white', 'w-12');
                    dot.classList.remove('bg-white/50', 'w-3');
                    dot.setAttribute('aria-selected', 'true');
                } else {
                    dot.classList.remove('bg-white', 'w-12');
                    dot.classList.add('bg-white/50', 'w-3');
                    dot.setAttribute('aria-selected', 'false');
                }
            } catch (error) {
                console.error(`Error updating hero dot ${index}:`, error);
            }
        });
        
        console.log(`Updated hero carousel to slide ${currentHeroSlide + 1}`);
    } catch (error) {
        console.error('Error updating hero carousel:', error);
    }
};

// Keyboard navigation for hero carousel accessibility
const handleHeroCarouselKeydown = (event) => {
    try {
        switch (event.key) {
            case 'ArrowLeft':
                event.preventDefault();
                previousHeroSlide();
                break;
            case 'ArrowRight':
                event.preventDefault();
                nextHeroSlide();
                break;
            case 'Home':
                event.preventDefault();
                goToHeroSlide(0);
                break;
            case 'End':
                event.preventDefault();
                goToHeroSlide(totalHeroSlides - 1);
                break;
        }
    } catch (error) {
        console.error('Error handling hero carousel keyboard navigation:', error);
    }
};
</script>

<?php
$content = ob_get_clean();
include 'app/views/layouts/main.php';
?>