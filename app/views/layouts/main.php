<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Grocery App'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Modern animations and transitions */
        .animate-fade-in {
            animation: fadeIn 0.6s ease-in-out;
        }

        .animate-slide-up {
            animation: slideUp 0.8s ease-out;
        }

        .animate-bounce-in {
            animation: bounceIn 0.6s ease-out;
        }

        .hover-lift {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .gradient-bg {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .pulse-animation {
            animation: pulse 2s infinite;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes bounceIn {
            0% {
                opacity: 0;
                transform: scale(0.3);
            }
            50% {
                opacity: 1;
                transform: scale(1.05);
            }
            70% {
                transform: scale(0.9);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #10b981;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #059669;
        }

        /* Loading animation */
        .loading-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #10b981;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Button hover effects */
        .btn-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
        }

        /* Card animations */
        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }

        /* Navigation animations */
        .nav-link {
            position: relative;
            transition: color 0.3s ease;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 50%;
            background: #fff;
            transition: all 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
            left: 0;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <!-- Navigation -->
    <nav class="gradient-bg text-white shadow-xl animate-fade-in">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <a href="/" class="text-2xl font-bold hover:scale-105 transition-transform duration-300">
                        <i class="fas fa-shopping-basket mr-2"></i>
                        GroceryApp
                    </a>
                </div>
                <div class="flex items-center space-x-6">
                    <a href="/products" class="nav-link hover:text-green-200 transition-colors duration-300">
                        <i class="fas fa-store mr-1"></i>Products
                    </a>
                    <a href="/about" class="nav-link hover:text-green-200 transition-colors duration-300">
                        <i class="fas fa-info-circle mr-1"></i>About Us
                    </a>
                    <a href="/contact" class="nav-link hover:text-green-200 transition-colors duration-300">
                        <i class="fas fa-phone mr-1"></i>Contacts
                    </a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="/wishlist" class="nav-link hover:text-green-200 transition-colors duration-300 relative">
                            <i class="fas fa-heart"></i>
                            <span class="ml-1">Wishlist</span>
                            <span class="wishlist-badge absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center animate-bounce-in hidden">0</span>
                        </a>
                        <a href="/cart" class="nav-link hover:text-green-200 transition-colors duration-300 relative">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="ml-1">Cart</span>
                            <span class="cart-badge absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center animate-bounce-in hidden">0</span>
                        </a>
                        <div class="relative">
                            <button class="nav-link hover:text-green-200 focus:outline-none flex items-center" id="userMenu">
                                <i class="fas fa-user-circle mr-2"></i>
                                <?php echo htmlspecialchars($_SESSION['first_name'] ?? 'User'); ?>
                                <i class="fas fa-chevron-down ml-1 transition-transform duration-300" id="dropdownIcon"></i>
                            </button>
                            <div class="absolute right-0 mt-2 w-56 z-20 bg-white rounded-xl shadow-2xl hidden animate-slide-up glass-effect border-0" id="userDropdown">
                                <div class="py-2">
                                    <a href="/profile" class="block px-4 py-3 text-gray-800 hover:bg-green-50 hover:text-green-700 transition-colors duration-200 rounded-lg mx-2">
                                        <i class="fas fa-user mr-2"></i>Profile
                                    </a>
                                    <a href="/wishlist" class="block px-4 py-3 text-gray-800 hover:bg-green-50 hover:text-green-700 transition-colors duration-200 rounded-lg mx-2">
                                        <i class="fas fa-heart mr-2"></i>My Wishlist
                                    </a>
                                    <a href="/orders" class="block px-4 py-3 text-gray-800 hover:bg-green-50 hover:text-green-700 transition-colors duration-200 rounded-lg mx-2">
                                        <i class="fas fa-shopping-bag mr-2"></i>My Orders
                                    </a>
                                    <a href="/subscriptions" class="block px-4 py-3 text-gray-800 hover:bg-green-50 hover:text-green-700 transition-colors duration-200 rounded-lg mx-2">
                                        <i class="fas fa-sync-alt mr-2"></i>Subscriptions
                                    </a>
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                        <a href="/admin" class="block px-4 py-3 text-gray-800 hover:bg-green-50 hover:text-green-700 transition-colors duration-200 rounded-lg mx-2">
                                            <i class="fas fa-cog mr-2"></i>Admin Panel
                                        </a>
                                    <?php endif; ?>
                                    <hr class="my-2 border-gray-200">
                                    <a href="/logout" class="block px-4 py-3 text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors duration-200 rounded-lg mx-2">
                                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="/login" class="nav-link hover:text-green-200 transition-colors duration-300">
                            <i class="fas fa-sign-in-alt mr-1"></i>Login
                        </a>
                        <a href="/signup" class="btn-primary text-white px-6 py-2 rounded-full font-semibold hover:shadow-lg transition-all duration-300">
                            <i class="fas fa-user-plus mr-1"></i>Sign Up
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="min-h-screen animate-fade-in">
        <?php echo $content ?? ''; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-gradient-to-r from-gray-800 to-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="animate-slide-up">
                    <h3 class="text-xl font-bold mb-4 flex items-center">
                        <i class="fas fa-shopping-basket mr-2 text-green-400"></i>
                        GroceryApp
                    </h3>
                    <p class="text-gray-300 leading-relaxed">Your trusted online grocery store delivering fresh products right to your doorstep.</p>
                    <div class="flex space-x-4 mt-4">
                        <a href="#" class="text-gray-400 hover:text-green-400 transition-colors duration-300">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-green-400 transition-colors duration-300">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-green-400 transition-colors duration-300">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
                <div class="animate-slide-up" style="animation-delay: 0.1s;">
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-3">
                        <li><a href="/products" class="text-gray-300 hover:text-green-400 transition-colors duration-300 flex items-center">
                            <i class="fas fa-chevron-right mr-2 text-xs"></i>Products
                        </a></li>
                        <li><a href="/categories" class="text-gray-300 hover:text-green-400 transition-colors duration-300 flex items-center">
                            <i class="fas fa-chevron-right mr-2 text-xs"></i>Categories
                        </a></li>
                        <li><a href="/about" class="text-gray-300 hover:text-green-400 transition-colors duration-300 flex items-center">
                            <i class="fas fa-chevron-right mr-2 text-xs"></i>About Us
                        </a></li>
                        <li><a href="/contact" class="text-gray-300 hover:text-green-400 transition-colors duration-300 flex items-center">
                            <i class="fas fa-chevron-right mr-2 text-xs"></i>Contact
                        </a></li>
                    </ul>
                </div>
                <div class="animate-slide-up" style="animation-delay: 0.2s;">
                    <h4 class="text-lg font-semibold mb-4">Customer Service</h4>
                    <ul class="space-y-3">
                        <li><a href="/help" class="text-gray-300 hover:text-green-400 transition-colors duration-300 flex items-center">
                            <i class="fas fa-chevron-right mr-2 text-xs"></i>Help Center
                        </a></li>
                        <li><a href="/shipping" class="text-gray-300 hover:text-green-400 transition-colors duration-300 flex items-center">
                            <i class="fas fa-chevron-right mr-2 text-xs"></i>Shipping Info
                        </a></li>
                        <li><a href="/returns" class="text-gray-300 hover:text-green-400 transition-colors duration-300 flex items-center">
                            <i class="fas fa-chevron-right mr-2 text-xs"></i>Returns
                        </a></li>
                        <li><a href="/faq" class="text-gray-300 hover:text-green-400 transition-colors duration-300 flex items-center">
                            <i class="fas fa-chevron-right mr-2 text-xs"></i>FAQ
                        </a></li>
                    </ul>
                </div>
                <div class="animate-slide-up" style="animation-delay: 0.3s;">
                    <h4 class="text-lg font-semibold mb-4">Contact Info</h4>
                    <div class="space-y-3">
                        <p class="text-gray-300 flex items-center">
                            <i class="fas fa-envelope mr-2 text-green-400"></i>
                            support@groceryapp.com
                        </p>
                        <p class="text-gray-300 flex items-center">
                            <i class="fas fa-phone mr-2 text-green-400"></i>
                            +880 123 456 789
                        </p>
                        <p class="text-gray-300 flex items-center">
                            <i class="fas fa-map-marker-alt mr-2 text-green-400"></i>
                            Dhaka, Bangladesh
                        </p>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-12 pt-8 text-center">
                <p class="text-gray-400">&copy; 2025 GroceryApp. All rights reserved. Made with <i class="fas fa-heart text-red-500 mx-1"></i> for fresh groceries.</p>
            </div>
        </div>
    </footer>

    <!-- Toast notifications container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <script>
        // Enhanced user dropdown toggle with animations
        document.getElementById('userMenu')?.addEventListener('click', function() {
            const dropdown = document.getElementById('userDropdown');
            const icon = document.getElementById('dropdownIcon');

            if (dropdown.classList.contains('hidden')) {
                dropdown.classList.remove('hidden');
                icon.style.transform = 'rotate(180deg)';
            } else {
                dropdown.classList.add('hidden');
                icon.style.transform = 'rotate(0deg)';
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const userMenu = document.getElementById('userMenu');
            const userDropdown = document.getElementById('userDropdown');
            const icon = document.getElementById('dropdownIcon');

            if (!userMenu?.contains(event.target)) {
                userDropdown?.classList.add('hidden');
                icon.style.transform = 'rotate(0deg)';
            }
        });

        // Toast notification system
        function showToast(message, type = 'success') {
            const toastContainer = document.getElementById('toast-container');
            const toast = document.createElement('div');

            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                warning: 'bg-yellow-500',
                info: 'bg-blue-500'
            };

            toast.className = `${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg animate-slide-up flex items-center space-x-2`;
            toast.innerHTML = `
                <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.remove()" class="ml-4 hover:opacity-75">
                    <i class="fas fa-times"></i>
                </button>
            `;

            toastContainer.appendChild(toast);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.style.animation = 'slideUp 0.3s ease-in reverse';
                    setTimeout(() => toast.remove(), 300);
                }
            }, 5000);
        }

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add loading states to buttons
        function setLoading(button, loading = true) {
            if (loading) {
                button.disabled = true;
                button.innerHTML = '<div class="loading-spinner mx-auto"></div>';
            } else {
                button.disabled = false;
                button.innerHTML = button.getAttribute('data-original-text') || 'Button';
            }
        }

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in');
                }
            });
        }, observerOptions);

        // Observe elements for animation
        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>
</html>