<?php
$title = 'Sign Up - GroceryApp';
ob_start();
?>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-purple-50 via-pink-50 to-blue-50 py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
    <!-- Animated background elements -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-purple-400 to-purple-600 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-bounce-in" style="animation-delay: 0.5s;"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-br from-pink-400 to-pink-600 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-bounce-in" style="animation-delay: 1s;"></div>
        <div class="absolute top-40 left-40 w-60 h-60 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-bounce-in" style="animation-delay: 1.5s;"></div>
    </div>

    <div class="max-w-md w-full space-y-8 relative z-10 animate-slide-up">
        <!-- Logo and branding -->
        <div class="text-center">
            <div class="mx-auto w-20 h-20 bg-gradient-to-br from-purple-500 to-purple-700 rounded-2xl flex items-center justify-center shadow-2xl animate-bounce-in">
                <i class="fas fa-user-plus text-white text-3xl"></i>
            </div>
            <h2 class="mt-6 text-4xl font-bold text-gray-900 animate-fade-in" style="animation-delay: 0.3s;">
                Join GroceryApp
            </h2>
            <p class="mt-2 text-lg text-gray-600 animate-fade-in" style="animation-delay: 0.5s;">
                Create your account and start shopping
            </p>
        </div>

        <!-- Sign Up Form -->
        <div class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/20 p-8 animate-slide-up" style="animation-delay: 0.7s;">
            <form class="space-y-6" action="/signup" method="POST">
                <?php if (isset($error)): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl animate-shake">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-2 gap-4">
                    <div class="relative">
                        <label for="first_name" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user mr-2 text-purple-500"></i>First Name
                        </label>
                        <div class="relative">
                            <input id="first_name" name="first_name" type="text" required
                                   class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-xl focus:ring-4 focus:ring-purple-100 focus:border-purple-500 transition-all duration-300 bg-white/50 backdrop-blur-sm"
                                   placeholder="First name" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <div class="relative">
                        <label for="last_name" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user mr-2 text-purple-500"></i>Last Name
                        </label>
                        <div class="relative">
                            <input id="last_name" name="last_name" type="text" required
                                   class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-xl focus:ring-4 focus:ring-purple-100 focus:border-purple-500 transition-all duration-300 bg-white/50 backdrop-blur-sm"
                                   placeholder="Last name" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2 text-purple-500"></i>Email Address
                    </label>
                    <div class="relative">
                        <input id="email" name="email" type="email" required
                               class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-xl focus:ring-4 focus:ring-purple-100 focus:border-purple-500 transition-all duration-300 bg-white/50 backdrop-blur-sm"
                               placeholder="Enter your email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-phone mr-2 text-purple-500"></i>Phone Number
                    </label>
                    <div class="relative">
                        <input id="phone" name="phone" type="tel"
                               class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-xl focus:ring-4 focus:ring-purple-100 focus:border-purple-500 transition-all duration-300 bg-white/50 backdrop-blur-sm"
                               placeholder="Phone number (optional)" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-phone text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-purple-500"></i>Password
                    </label>
                    <div class="relative">
                        <input id="password" name="password" type="password" required
                               class="w-full px-4 py-3 pl-12 pr-12 border border-gray-300 rounded-xl focus:ring-4 focus:ring-purple-100 focus:border-purple-500 transition-all duration-300 bg-white/50 backdrop-blur-sm"
                               placeholder="Create a password">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center" onclick="togglePassword()">
                            <i class="fas fa-eye text-gray-400 hover:text-gray-600" id="password-toggle"></i>
                        </button>
                    </div>
                    <!-- Password strength indicator -->
                    <div class="mt-2">
                        <div class="flex space-x-1">
                            <div class="h-1 flex-1 bg-gray-200 rounded-full" id="strength-1"></div>
                            <div class="h-1 flex-1 bg-gray-200 rounded-full" id="strength-2"></div>
                            <div class="h-1 flex-1 bg-gray-200 rounded-full" id="strength-3"></div>
                            <div class="h-1 flex-1 bg-gray-200 rounded-full" id="strength-4"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1" id="strength-text">Password strength</p>
                    </div>
                </div>

                <div class="flex items-center">
                    <input id="terms" name="terms" type="checkbox" required class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                    <label for="terms" class="ml-2 block text-sm text-gray-700">
                        I agree to the
                        <a href="/terms" class="text-purple-600 hover:text-purple-500 font-medium">Terms of Service</a>
                        and
                        <a href="/privacy" class="text-purple-600 hover:text-purple-500 font-medium">Privacy Policy</a>
                    </label>
                </div>

                <button type="submit"
                        class="w-full flex justify-center py-4 px-6 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 focus:outline-none focus:ring-4 focus:ring-purple-200 transform hover:scale-105 transition-all duration-300">
                    <i class="fas fa-user-plus mr-2"></i>
                    Create Account
                </button>
            </form>

            <!-- Social Signup Options -->
            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">Or sign up with</span>
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

        <!-- Login link -->
        <div class="text-center animate-fade-in" style="animation-delay: 1s;">
            <p class="text-gray-600">
                Already have an account?
                <a href="/login" class="font-bold text-purple-600 hover:text-purple-700 transition-colors duration-200 hover:underline">
                    Sign in here
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

// Password strength indicator
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthBars = ['strength-1', 'strength-2', 'strength-3', 'strength-4'];
    const strengthText = document.getElementById('strength-text');

    let strength = 0;
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;

    const colors = ['bg-red-400', 'bg-orange-400', 'bg-yellow-400', 'bg-green-400'];
    const texts = ['Very weak', 'Weak', 'Fair', 'Strong'];

    strengthBars.forEach((bar, index) => {
        const element = document.getElementById(bar);
        if (index < strength) {
            element.className = `h-1 flex-1 ${colors[Math.min(strength - 1, 3)]} rounded-full`;
        } else {
            element.className = 'h-1 flex-1 bg-gray-200 rounded-full';
        }
    });

    strengthText.textContent = strength > 0 ? texts[Math.min(strength - 1, 3)] : 'Password strength';
    strengthText.className = `text-xs mt-1 ${strength > 2 ? 'text-green-600' : strength > 0 ? 'text-orange-600' : 'text-gray-500'}`;
});

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