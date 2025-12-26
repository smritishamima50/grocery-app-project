<?php
$title = 'Contact Us - GroceryApp';
ob_start();
?>

<!-- Hero Section -->
<section class="gradient-bg text-white py-20 relative overflow-hidden">
    <div class="absolute inset-0 bg-black bg-opacity-20"></div>
    <div class="max-w-7xl mx-auto px-4 text-center relative z-10 animate-fade-in">
        <h1 class="text-5xl md:text-6xl font-bold mb-6 animate-slide-up">
            <i class="fas fa-phone mr-3"></i>Contact Us
        </h1>
        <p class="text-xl md:text-2xl animate-slide-up" style="animation-delay: 0.3s;">
            We're here to help! Get in touch with us
        </p>
    </div>
</section>

<!-- Contact Content Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Contact Information -->
            <div class="animate-slide-up">
                <h2 class="text-4xl font-bold text-gray-800 mb-8 flex items-center">
                    <i class="fas fa-info-circle text-green-600 mr-3"></i>
                    Get in Touch
                </h2>
                
                <div class="space-y-6">
                    <!-- Main Contact -->
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl p-6 shadow-lg hover-lift">
                        <div class="flex items-start">
                            <div class="w-16 h-16 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-phone text-white text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-800 mb-2">Customer Service</h3>
                                <p class="text-gray-600 mb-2">Call us for any inquiries or support</p>
                                <a href="tel:<?php echo htmlspecialchars($contactInfo['phone']); ?>" 
                                   class="text-green-600 hover:text-green-700 font-semibold text-lg flex items-center">
                                    <i class="fas fa-phone-alt mr-2"></i>
                                    <?php echo htmlspecialchars($contactInfo['phone']); ?>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Email Contact -->
                    <div class="bg-gradient-to-r from-blue-50 to-cyan-50 rounded-2xl p-6 shadow-lg hover-lift">
                        <div class="flex items-start">
                            <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-envelope text-white text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-800 mb-2">Email Support</h3>
                                <p class="text-gray-600 mb-2">Send us an email and we'll respond within 24 hours</p>
                                <a href="mailto:<?php echo htmlspecialchars($contactInfo['email']); ?>" 
                                   class="text-blue-600 hover:text-blue-700 font-semibold text-lg flex items-center">
                                    <i class="fas fa-envelope-open mr-2"></i>
                                    <?php echo htmlspecialchars($contactInfo['email']); ?>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-2xl p-6 shadow-lg hover-lift">
                        <div class="flex items-start">
                            <div class="w-16 h-16 bg-gradient-to-br from-purple-400 to-pink-400 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-map-marker-alt text-white text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-800 mb-2">Location</h3>
                                <p class="text-gray-600 mb-2">Visit us at our office</p>
                                <p class="text-purple-600 font-semibold text-lg">
                                    <i class="fas fa-building mr-2"></i>
                                    <?php echo htmlspecialchars($contactInfo['address']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="animate-slide-up" style="animation-delay: 0.2s;">
                <!-- Quick Help Section -->
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl p-8 shadow-lg mb-8">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-question-circle text-green-600 mr-3"></i>
                        Quick Help
                    </h3>
                    <div class="space-y-4">
                        <div class="bg-white rounded-lg p-4 hover-lift">
                            <h4 class="font-semibold text-gray-800 mb-2 flex items-center">
                                <i class="fas fa-shipping-fast text-green-600 mr-2"></i>
                                Delivery Information
                            </h4>
                            <p class="text-gray-600 text-sm">
                                Free delivery on orders above ৳3,000. Standard delivery fee: ৳50. Express delivery available.
                            </p>
                        </div>
                        <div class="bg-white rounded-lg p-4 hover-lift">
                            <h4 class="font-semibold text-gray-800 mb-2 flex items-center">
                                <i class="fas fa-undo text-green-600 mr-2"></i>
                                Returns & Refunds
                            </h4>
                            <p class="text-gray-600 text-sm">
                                Not satisfied? Return products within 24 hours for a full refund. Quality guaranteed!
                            </p>
                        </div>
                        <div class="bg-white rounded-lg p-4 hover-lift">
                            <h4 class="font-semibold text-gray-800 mb-2 flex items-center">
                                <i class="fas fa-lock text-green-600 mr-2"></i>
                                Secure Payments
                            </h4>
                            <p class="text-gray-600 text-sm">
                                We accept bKash, Nagad, Card payments, and Cash on Delivery. All transactions are secure.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Social Media -->
                <div class="bg-gradient-to-r from-blue-50 to-cyan-50 rounded-2xl p-8 shadow-lg">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-share-alt text-blue-600 mr-3"></i>
                        Follow Us
                    </h3>
                    <p class="text-gray-600 mb-6">Stay connected with us on social media for updates, offers, and more!</p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-12 h-12 bg-blue-600 text-white rounded-full flex items-center justify-center hover:bg-blue-700 transition-colors duration-300 shadow-lg hover-lift">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-12 h-12 bg-sky-500 text-white rounded-full flex items-center justify-center hover:bg-sky-600 transition-colors duration-300 shadow-lg hover-lift">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="w-12 h-12 bg-pink-600 text-white rounded-full flex items-center justify-center hover:bg-pink-700 transition-colors duration-300 shadow-lg hover-lift">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-12 h-12 bg-blue-700 text-white rounded-full flex items-center justify-center hover:bg-blue-800 transition-colors duration-300 shadow-lg hover-lift">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="mt-16 text-center animate-slide-up" style="animation-delay: 0.4s;">
            <div class="bg-gradient-to-r from-green-600 to-emerald-600 rounded-2xl p-12 text-white shadow-2xl">
                <h3 class="text-3xl font-bold mb-4">Need Immediate Assistance?</h3>
                <p class="text-xl mb-8 text-green-100">
                    Our customer service team is available to help you with any questions or concerns
                </p>
                <div class="flex flex-col md:flex-row items-center justify-center space-y-4 md:space-y-0 md:space-x-6">
                    <a href="tel:<?php echo htmlspecialchars($contactInfo['phone']); ?>" 
                       class="bg-white text-green-600 px-8 py-4 rounded-full font-bold text-lg hover:bg-green-50 transition-all duration-300 inline-block shadow-lg hover:shadow-xl transform hover:scale-105">
                        <i class="fas fa-phone mr-2"></i>
                        Call Us Now
                    </a>
                    <a href="mailto:<?php echo htmlspecialchars($contactInfo['email']); ?>" 
                       class="bg-white bg-opacity-20 text-white px-8 py-4 rounded-full font-bold text-lg hover:bg-opacity-30 transition-all duration-300 inline-block shadow-lg hover:shadow-xl transform hover:scale-105">
                        <i class="fas fa-envelope mr-2"></i>
                        Send Email
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
include 'app/views/layouts/main.php';
?>

