<!DOCTYPE html>
<html lang="en" class="<?php echo isset($_COOKIE['dark_mode']) && $_COOKIE['dark_mode'] === 'true' ? 'dark' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Admin Dashboard'; ?> - Grocery Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .sidebar-transition {
            transition: transform 0.3s ease-in-out;
        }
        .sidebar-hidden {
            transform: translateX(-100%);
        }
        @media (min-width: 1024px) {
            .sidebar-hidden {
                transform: translateX(0);
            }
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden"></div>
    
    <!-- Sidebar -->
    <div id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-gray-800 shadow-lg sidebar-transition lg:translate-x-0 sidebar-hidden">
        <!-- Sidebar Header -->
        <div class="flex items-center justify-between h-16 px-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-white text-sm"></i>
                </div>
                <span class="ml-3 text-xl font-bold text-gray-900 dark:text-white">Admin</span>
            </div>
            <button id="sidebar-close" class="lg:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Navigation -->
        <nav class="mt-6 px-3">
            <div class="space-y-1">
                <!-- Dashboard -->
                <a href="/admin" class="nav-item group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors <?php echo ($currentPage === 'dashboard') ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'; ?>">
                    <i class="fas fa-chart-pie mr-3 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-400"></i>
                    Dashboard
                </a>
                
                <!-- Sales Analytics -->
                <a href="/admin/analytics" class="nav-item group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors <?php echo ($currentPage === 'analytics') ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'; ?>">
                    <i class="fas fa-chart-line mr-3 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-400"></i>
                    Sales Analytics
                </a>
                
                <!-- Inventory -->
                <a href="/admin/inventory" class="nav-item group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors <?php echo ($currentPage === 'inventory') ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'; ?>">
                    <i class="fas fa-boxes mr-3 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-400"></i>
                    Inventory
                </a>
                
                <!-- Orders -->
                <a href="/admin/orders" class="nav-item group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors <?php echo ($currentPage === 'orders') ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'; ?>">
                    <i class="fas fa-shopping-bag mr-3 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-400"></i>
                    Orders
                </a>
                
                <!-- Products -->
                <a href="/admin/products" class="nav-item group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors <?php echo ($currentPage === 'products') ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'; ?>">
                    <i class="fas fa-store mr-3 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-400"></i>
                    Products
                </a>
                
                <!-- Categories -->
                <a href="/admin/categories" class="nav-item group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors <?php echo ($currentPage === 'categories') ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'; ?>">
                    <i class="fas fa-tags mr-3 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-400"></i>
                    Categories
                </a>
                
                <!-- Coupons -->
                <a href="/admin/coupons" class="nav-item group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors <?php echo ($currentPage === 'coupons') ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'; ?>">
                    <i class="fas fa-ticket-alt mr-3 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-400"></i>
                    Coupons
                </a>
                
                <!-- Users -->
                <a href="/admin/users" class="nav-item group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors <?php echo ($currentPage === 'users') ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'; ?>">
                    <i class="fas fa-users mr-3 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-400"></i>
                    Users
                </a>
                
                <!-- Subscriptions -->
                <a href="/admin/subscriptions" class="nav-item group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors <?php echo ($currentPage === 'subscriptions') ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'; ?>">
                    <i class="fas fa-sync-alt mr-3 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-400"></i>
                    Subscriptions
                </a>
                
                <!-- Surprise Gifts -->
                <a href="/admin/surprise-gifts" class="nav-item group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors <?php echo ($currentPage === 'surprise-gifts') ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'; ?>">
                    <i class="fas fa-gift mr-3 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-400"></i>
                    Surprise Gifts
                </a>
            </div>
        </nav>
        
        <!-- Sidebar Footer -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-gradient-to-r from-green-400 to-blue-500 rounded-full flex items-center justify-center text-white text-sm font-bold">
                    <?php echo $adminInitials ?? 'A'; ?>
                </div>
                <div class="ml-3 flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                        <?php echo $adminFullName ?? 'Admin User'; ?>
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                        Administrator
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="lg:pl-64">
        <!-- Top Header -->
        <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                <!-- Left side -->
                <div class="flex items-center">
                    <button id="sidebar-toggle" class="lg:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="ml-4 text-2xl font-semibold text-gray-900 dark:text-white">
                        <?php echo $pageTitle ?? 'Dashboard'; ?>
                    </h1>
                </div>
                
                <!-- Right side -->
                <div class="flex items-center space-x-4">
                    <!-- Eco-friendly Metric -->
                    <div class="hidden sm:flex items-center bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 px-3 py-1 rounded-full text-sm">
                        <i class="fas fa-leaf mr-1"></i>
                        <span class="font-medium"><?php echo $ecoFriendlyPercentage ?? '0'; ?>%</span>
                        <span class="ml-1 text-xs">eco-friendly this week</span>
                    </div>
                    
                    <!-- Dark Mode Toggle -->
                    <button id="dark-mode-toggle" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <i class="fas fa-moon dark:hidden"></i>
                        <i class="fas fa-sun hidden dark:block"></i>
                    </button>
                    
                    <!-- Admin Menu -->
                    <div class="relative">
                        <button id="admin-menu-toggle" class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <div class="w-8 h-8 bg-gradient-to-r from-green-400 to-blue-500 rounded-full flex items-center justify-center text-white text-sm font-bold">
                                <?php echo $adminInitials ?? 'A'; ?>
                            </div>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div id="admin-menu" class="hidden absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 z-50 border border-gray-200 dark:border-gray-700">
                            <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    <?php echo $adminFullName ?? 'Admin User'; ?>
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Administrator</p>
                            </div>
                            <a href="/admin/profile" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <i class="fas fa-user mr-2"></i>Profile
                            </a>
                            <a href="/admin/settings" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <i class="fas fa-cog mr-2"></i>Settings
                            </a>
                            <div class="border-t border-gray-200 dark:border-gray-700"></div>
                            <a href="/logout" class="block px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Page Content -->
        <main class="p-4 sm:p-6 lg:p-8">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="mb-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg">
                    <div class="flex">
                        <i class="fas fa-check-circle mr-2 mt-0.5"></i>
                        <span><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg">
                    <div class="flex">
                        <i class="fas fa-exclamation-circle mr-2 mt-0.5"></i>
                        <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php echo $content ?? ''; ?>
        </main>
    </div>
    
    <script>
        // Sidebar Toggle
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        const sidebarClose = document.getElementById('sidebar-close');
        
        function toggleSidebar() {
            sidebar.classList.toggle('sidebar-hidden');
            sidebarOverlay.classList.toggle('hidden');
        }
        
        sidebarToggle.addEventListener('click', toggleSidebar);
        sidebarClose.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', toggleSidebar);
        
        // Admin Menu Toggle
        const adminMenuToggle = document.getElementById('admin-menu-toggle');
        const adminMenu = document.getElementById('admin-menu');
        
        adminMenuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            adminMenu.classList.toggle('hidden');
        });
        
        // Close admin menu when clicking outside
        document.addEventListener('click', function() {
            adminMenu.classList.add('hidden');
        });
        
        // Dark Mode Toggle
        const darkModeToggle = document.getElementById('dark-mode-toggle');
        
        darkModeToggle.addEventListener('click', function() {
            const html = document.documentElement;
            const isDark = html.classList.contains('dark');
            
            if (isDark) {
                html.classList.remove('dark');
                document.cookie = 'dark_mode=false; path=/; max-age=31536000';
            } else {
                html.classList.add('dark');
                document.cookie = 'dark_mode=true; path=/; max-age=31536000';
            }
        });
        
        // Check for saved dark mode preference
        if (document.cookie.includes('dark_mode=true')) {
            document.documentElement.classList.add('dark');
        }
    </script>
</body>
</html>
