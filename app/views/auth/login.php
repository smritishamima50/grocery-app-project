<?php
$title = 'Login - GroceryApp';
ob_start();
?>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-green-50 via-blue-50 to-purple-50 py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
    <!-- Animated background elements -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-green-400 to-green-600 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-bounce-in" style="animation-delay: 0.5s;"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-bounce-in" style="animation-delay: 1s;"></div>
        <div class="absolute top-40 left-40 w-60 h-60 bg-gradient-to-br from-purple-400 to-purple-600 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-bounce-in" style="animation-delay: 1.5s;"></div>
    </div>

    <div class="max-w-md w-full space-y-8 relative z-10 animate-slide-up">
        <!-- Logo and branding -->
        <div class="text-center">
            <div class="mx-auto w-20 h-20 bg-gradient-to-br from-green-500 to-green-700 rounded-2xl flex items-center justify-center shadow-2xl animate-bounce-in">
                <i class="fas fa-shopping-basket text-white text-3xl"></i>
            </div>
            <h2 class="mt-6 text-4xl font-bold text-gray-900 animate-fade-in" style="animation-delay: 0.3s;">
                Welcome Back
            </h2>
            <p class="mt-2 text-lg text-gray-600 animate-fade-in" style="animation-delay: 0.5s;">
                Sign in to your GroceryApp account
            </p>
        </div>

        <!-- Login Form -->
        <div class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/20 p-8 animate-slide-up" style="animation-delay: 0.7s;">
            <form class="space-y-6" action="/login" method="POST">
                <?php if (isset($error)): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl animate-shake">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <div class="space-y-4">
                    <div class="relative">
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-envelope mr-2 text-green-500"></i>Email Address
                        </label>
                        <div class="relative">
                            <input id="email" name="email" type="email" required
                                   class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-xl focus:ring-4 focus:ring-green-100 focus:border-green-500 transition-all duration-300 bg-white/50 backdrop-blur-sm"
                                   placeholder="Enter your email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <div class="relative">
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-lock mr-2 text-green-500"></i>Password
                        </label>
                        <div class="relative">
                            <input id="password" name="password" type="password" required
                                   class="w-full px-4 py-3 pl-12 pr-12 border border-gray-300 rounded-xl focus:ring-4 focus:ring-green-100 focus:border-green-500 transition-all duration-300 bg-white/50 backdrop-blur-sm"
                                   placeholder="Enter your password">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center" onclick="togglePassword()">
                                <i class="fas fa-eye text-gray-400 hover:text-gray-600" id="password-toggle"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <label for="remember-me" class="ml-2 block text-sm text-gray-700">
                            Remember me
                        </label>
                    </div>
                    <div class="text-sm">
                        <a href="/forgot-password" class="font-medium text-green-600 hover:text-green-500 transition-colors duration-200">
                            Forgot password?
                        </a>
                    </div>
                </div>

                <button type="submit"
                        class="w-full flex justify-center py-4 px-6 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-4 focus:ring-green-200 transform hover:scale-105 transition-all duration-300">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Sign In
                </button>
            </form>

            <!-- Social Login Options -->
            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">Or continue with</span>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-3">
                    <button class="w-full inline-flex justify-center py-3 px-4 border border-gray-300 rounded-xl shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 transition-all duration-300 hover:shadow-md">
                        <i class="fab fa-google text-red-500"></i>
                        <span class="ml-2">Google</span>
                    </button>
                    <button class="w-full inline-flex justify-center py-3 px-4 border border-gray-300 rounded-xl shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 transition-all duration-300 hover:shadow-md">
                        <i class="fab fa-facebook text-blue-600"></i>
                        <span class="ml-2">Facebook</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Sign up link -->
        <div class="text-center animate-fade-in" style="animation-delay: 1s;">
            <p class="text-gray-600">
                Don't have an account?
                <a href="/signup" class="font-bold text-green-600 hover:text-green-700 transition-colors duration-200 hover:underline">
                    Create one now
                </a>
            </p>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('password-toggle');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.className = 'fas fa-eye-slash text-gray-400 hover:text-gray-600';
    } else {
        passwordInput.type = 'password';
        toggleIcon.className = 'fas fa-eye text-gray-400 hover:text-gray-600';
    }
}

// Add shake animation for errors
document.addEventListener('DOMContentLoaded', function() {
    const errorDiv = document.querySelector('.animate-shake');
    if (errorDiv) {
        errorDiv.style.animation = 'shake 0.5s ease-in-out';
    }
});
</script>

<style>
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}
</style>

<?php
$content = ob_get_clean();
include 'app/views/layouts/main.php';
?>