<?php
// Load payment configuration
$paymentConfig = require_once 'config/payment_accounts.php';

$title = 'Checkout - GroceryApp';
ob_start();
?>

<div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 py-8 px-4 animate-fade-in">
    <div class="max-w-7xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="/" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 transition-colors duration-200">
                        <i class="fas fa-home mr-2"></i>
                        Home
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                        <a href="/cart" class="text-sm font-medium text-gray-700 hover:text-blue-600 transition-colors duration-200">Cart</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                        <span class="text-sm font-medium text-gray-500">Checkout</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-4 animate-slide-up">
                <i class="fas fa-shopping-cart mr-3 text-blue-600"></i>
                Complete Your Order
            </h1>
            <p class="text-xl text-gray-600 animate-fade-in" style="animation-delay: 0.2s;">
                Almost there! Just a few more steps to get your groceries delivered.
            </p>
        </div>

        <form action="/checkout/process" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-8" id="checkout-form">
            <!-- Hidden input for selected surprise gift -->
            <input type="hidden" name="selected_surprise_gift" id="selected-surprise-gift" value="">
            
            <!-- Order Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Delivery Address -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-xl p-8 animate-slide-up">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mr-4">
                            <i class="fas fa-map-marker-alt text-blue-600 text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">Delivery Address</h2>
                    </div>

                    <?php if (empty($addresses)): ?>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 mb-6">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
                                <p class="text-yellow-800 font-medium">No saved addresses found</p>
                            </div>
                            <p class="text-yellow-700 mt-2">Please add a delivery address to continue.</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="relative">
                                <label for="new_address_line1" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-home mr-2 text-blue-500"></i>Address Line 1 *
                                </label>
                                <input type="text" id="new_address_line1" name="new_address_line1" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all duration-300 bg-white/50 backdrop-blur-sm"
                                       placeholder="House number, street name">
                            </div>
                            <div class="relative">
                                <label for="new_address_line2" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-building mr-2 text-blue-500"></i>Address Line 2
                                </label>
                                <input type="text" id="new_address_line2" name="new_address_line2"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all duration-300 bg-white/50 backdrop-blur-sm"
                                       placeholder="Apartment, suite, etc. (optional)">
                            </div>
                            <div class="relative">
                                <label for="new_city" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-city mr-2 text-blue-500"></i>City *
                                </label>
                                <input type="text" id="new_city" name="new_city" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all duration-300 bg-white/50 backdrop-blur-sm"
                                       placeholder="Enter city name">
                            </div>
                            <div class="relative">
                                <label for="new_state" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-map mr-2 text-blue-500"></i>State/Province
                                </label>
                                <input type="text" id="new_state" name="new_state"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all duration-300 bg-white/50 backdrop-blur-sm"
                                       placeholder="Enter state/province">
                            </div>
                            <div class="relative">
                                <label for="new_zip_code" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-mailbox mr-2 text-blue-500"></i>ZIP/Postal Code
                                </label>
                                <input type="text" id="new_zip_code" name="new_zip_code"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all duration-300 bg-white/50 backdrop-blur-sm"
                                       placeholder="Enter postal code">
                            </div>
                            <div class="relative">
                                <label for="new_country" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-globe mr-2 text-blue-500"></i>Country *
                                </label>
                                <input type="text" id="new_country" name="new_country" value="Bangladesh" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all duration-300 bg-white/50 backdrop-blur-sm"
                                       placeholder="Enter country">
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($addresses as $address): ?>
                                <label class="relative bg-gradient-to-r from-white to-gray-50 border-2 border-gray-200 rounded-xl p-6 cursor-pointer hover:border-blue-500 hover:shadow-lg transition-all duration-300 animate-on-scroll">
                                    <input type="radio" name="address_id" value="<?php echo $address['id']; ?>" <?php echo empty($addresses) ? '' : 'required'; ?>
                                           class="absolute top-4 right-4 w-5 h-5 text-blue-600 focus:ring-blue-500">
                                    <div class="flex items-start">
                                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-4 mt-1">
                                            <i class="fas fa-map-marker-alt text-blue-600"></i>
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2 mb-2">
                                                <span class="bg-blue-100 text-blue-800 text-sm font-semibold px-3 py-1 rounded-full capitalize">
                                                    <?php echo htmlspecialchars($address['address_type']); ?>
                                                </span>
                                                <?php if ($address['is_default']): ?>
                                                    <span class="bg-green-100 text-green-800 text-sm font-semibold px-3 py-1 rounded-full">
                                                        <i class="fas fa-star mr-1"></i>Default
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="font-semibold text-gray-900 mb-1"><?php echo htmlspecialchars($address['address_line1']); ?></p>
                                            <?php if ($address['address_line2']): ?>
                                                <p class="text-gray-600 mb-1"><?php echo htmlspecialchars($address['address_line2']); ?></p>
                                            <?php endif; ?>
                                            <p class="text-gray-600 text-sm">
                                                <?php echo htmlspecialchars($address['city']); ?><?php if ($address['state']): ?>, <?php echo htmlspecialchars($address['state']); ?><?php endif; ?>
                                                <?php if ($address['zip_code']): ?> - <?php echo htmlspecialchars($address['zip_code']); ?><?php endif; ?>,
                                                <?php echo htmlspecialchars($address['country']); ?>
                                            </p>
                                        </div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Delivery Slot -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-xl p-8 animate-slide-up" style="animation-delay: 0.2s;">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mr-4">
                            <i class="fas fa-clock text-green-600 text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">Delivery Slot</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php
                        // Build dynamic delivery slots based on current date in Asia/Dhaka timezone
                        $tz = new DateTimeZone(date_default_timezone_get());
                        $today = new DateTime('today', $tz);
                        $tomorrow = (clone $today)->modify('+1 day');
                        $dayAfter = (clone $today)->modify('+2 days');

                        $ranges = [
                            ['start' => '10:00', 'end' => '12:00', 'label' => '10:00 AM - 12:00 PM'],
                            ['start' => '14:00', 'end' => '16:00', 'label' => '2:00 PM - 4:00 PM'],
                            ['start' => '18:00', 'end' => '20:00', 'label' => '6:00 PM - 8:00 PM'],
                        ];

                        $deliverySlots = [];

                        // Helper to check availability for today based on current time
                        $now = new DateTime('now', $tz);
                        foreach ([['date' => $today, 'prefix' => 'Today, '], ['date' => $tomorrow, 'prefix' => 'Tomorrow, '], ['date' => $dayAfter, 'prefix' => 'Day After, ']] as $dayIndex => $dayInfo) {
                            foreach ($ranges as $range) {
                                $dateStr = $dayInfo['date']->format('Y-m-d');
                                $value = $dateStr . ' ' . $range['start'] . '-' . $range['end'];
                                $label = $dayInfo['prefix'] . $range['label'];
                                $available = true;
                                if ($dayIndex === 0) { // today
                                    $slotEnd = DateTime::createFromFormat('Y-m-d H:i', $dateStr . ' ' . $range['end'], $tz);
                                    if ($slotEnd <= $now) {
                                        $available = false;
                                    }
                                }
                                $deliverySlots[] = ['value' => $value, 'label' => $label, 'available' => $available];
                            }
                        }
                        ?>

                        <?php foreach ($deliverySlots as $slot): ?>
                            <label class="relative bg-gradient-to-r from-white to-gray-50 border-2 border-gray-200 rounded-xl p-4 cursor-pointer hover:border-green-500 hover:shadow-lg transition-all duration-300 <?php echo !$slot['available'] ? 'opacity-50 cursor-not-allowed' : ''; ?>">
                                <input type="radio" name="delivery_slot" value="<?php echo $slot['value']; ?>" required
                                       class="absolute top-3 right-3 w-4 h-4 text-green-600 focus:ring-green-500" <?php echo !$slot['available'] ? 'disabled' : ''; ?>>
                                <div class="flex items-center">
                                    <div class="w-8 h-8 <?php echo $slot['available'] ? 'bg-green-100' : 'bg-gray-100'; ?> rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas <?php echo $slot['available'] ? 'fa-check text-green-600' : 'fa-times text-gray-400'; ?>"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($slot['label']); ?></p>
                                        <?php if (!$slot['available']): ?>
                                            <p class="text-sm text-gray-500">Not available</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Packaging Options -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-xl p-8 animate-slide-up" style="animation-delay: 0.3s;">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center mr-4">
                            <i class="fas fa-box text-orange-600 text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">Packaging Options</h2>
                    </div>

                    <div class="space-y-4">
                        <p class="text-gray-600 mb-4">Choose your preferred packaging:</p>
                        
                        <!-- Standard Packaging -->
                        <label class="relative bg-gradient-to-r from-white to-gray-50 border-2 border-gray-200 rounded-xl p-6 cursor-pointer hover:border-orange-500 hover:shadow-lg transition-all duration-300">
                            <input type="radio" name="packaging_option" value="standard" checked
                                   class="absolute top-4 right-4 w-5 h-5 text-orange-600 focus:ring-orange-500">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center mr-4">
                                    <i class="fas fa-shopping-bag text-orange-600 text-xl"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900">Standard packaging</p>
                                    <p class="text-gray-600 text-sm">Regular plastic or branded bags</p>
                                    <p class="text-orange-600 text-sm mt-1 font-medium">No additional cost</p>
                                </div>
                            </div>
                        </label>

                        <!-- Eco-friendly Packaging -->
                        <label class="relative bg-gradient-to-r from-white to-gray-50 border-2 border-gray-200 rounded-xl p-6 cursor-pointer hover:border-green-500 hover:shadow-lg transition-all duration-300">
                            <input type="radio" name="packaging_option" value="eco_friendly"
                                   class="absolute top-4 right-4 w-5 h-5 text-green-600 focus:ring-green-500">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mr-4">
                                    <i class="fas fa-leaf text-green-600 text-xl"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900">Eco-friendly packaging</p>
                                    <p class="text-gray-600 text-sm">No plastic, minimal paper</p>
                                    <p class="text-green-600 text-sm mt-1 font-medium">Environmentally conscious choice</p>
                                </div>
                            </div>
                        </label>

                        <!-- Reusable Bag Option -->
                        <label class="relative bg-gradient-to-r from-white to-gray-50 border-2 border-gray-200 rounded-xl p-6 cursor-pointer hover:border-blue-500 hover:shadow-lg transition-all duration-300">
                            <input type="radio" name="packaging_option" value="reusable_bag"
                                   class="absolute top-4 right-4 w-5 h-5 text-blue-600 focus:ring-blue-500">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mr-4">
                                    <i class="fas fa-recycle text-blue-600 text-xl"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900">Reusable bag option</p>
                                    <p class="text-gray-600 text-sm">Cloth or jute bag</p>
                                    <p class="text-blue-600 text-sm mt-1 font-medium">+৳20.00 extra cost</p>
                                </div>
                            </div>
                        </label>

                        <!-- Eco-friendly info message (hidden by default) -->
                        <div id="eco-friendly-info" class="hidden mt-4 p-4 bg-green-50 border border-green-200 rounded-xl">
                            <div class="flex items-center">
                                <i class="fas fa-seedling text-green-600 mr-3"></i>
                                <p class="text-green-800 font-medium">🌱 Eco-friendly choice helps reduce waste. Thank you for caring for the planet!</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-xl p-8 animate-slide-up" style="animation-delay: 0.4s;">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mr-4">
                            <i class="fas fa-credit-card text-purple-600 text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">Payment Method</h2>
                    </div>

                    <div class="space-y-4">
                        <!-- Cash on Delivery -->
                        <label class="relative bg-gradient-to-r from-white to-gray-50 border-2 border-green-500 rounded-xl p-6 cursor-pointer hover:shadow-lg transition-all duration-300">
                            <input type="radio" name="payment_method" value="cod" checked
                                   class="absolute top-4 right-4 w-5 h-5 text-green-600 focus:ring-green-500">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mr-4">
                                    <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900">Cash on Delivery</p>
                                    <p class="text-gray-600 text-sm">Pay when you receive your order at your doorstep</p>
                                    <div class="flex items-center mt-2">
                                        <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded-full">Most Popular</span>
                                        <span class="text-green-600 text-sm ml-2 font-medium">No advance payment required</span>
                                    </div>
                                </div>
                            </div>
                        </label>

                        <!-- bKash Payment -->
                        <label class="relative bg-gradient-to-r from-white to-gray-50 border-2 border-gray-200 rounded-xl p-6 cursor-pointer hover:border-pink-500 hover:shadow-lg transition-all duration-300">
                            <input type="radio" name="payment_method" value="bkash"
                                   class="absolute top-4 right-4 w-5 h-5 text-pink-600 focus:ring-pink-500">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-pink-100 rounded-xl flex items-center justify-center mr-4">
                                    <i class="fas fa-mobile-alt text-pink-600 text-xl"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="font-bold text-gray-900">bKash Payment</p>
                                    <p class="text-gray-600 text-sm">Pay securely with your bKash mobile wallet</p>
                                    <?php if ($paymentConfig['bkash']['enabled']): ?>
                                    <div class="mt-3 p-3 bg-pink-50 rounded-lg border border-pink-200">
                                        <div class="text-sm text-gray-700">
                                            <p class="font-semibold text-pink-800 mb-2">📱 Payment Instructions:</p>
                                            <div class="space-y-1 text-xs">
                                                <p>1. Send money to: <span class="font-mono font-bold text-pink-600"><?php echo $paymentConfig['bkash']['account_number']; ?></span></p>
                                                <p>2. Amount: <span class="font-bold">৳<span id="bkash-amount"><?php echo number_format($finalTotal + 60, 2); ?></span></span></p>
                                                <p>3. Reference: <span class="font-mono">ORDER-<?php echo time(); ?></span></p>
                                                <p>4. After payment, you'll receive confirmation</p>
                                            </div>
                                            <div class="mt-2 pt-2 border-t border-pink-200">
                                                <p class="text-xs text-pink-600">
                                                    <i class="fas fa-phone mr-1"></i> Support: <?php echo $paymentConfig['bkash']['support_phone']; ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </label>

                        <!-- Nagad Payment -->
                        <label class="relative bg-gradient-to-r from-white to-gray-50 border-2 border-gray-200 rounded-xl p-6 cursor-pointer hover:border-orange-500 hover:shadow-lg transition-all duration-300">
                            <input type="radio" name="payment_method" value="nagad"
                                   class="absolute top-4 right-4 w-5 h-5 text-orange-600 focus:ring-orange-500">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center mr-4">
                                    <i class="fas fa-wallet text-orange-600 text-xl"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="font-bold text-gray-900">Nagad Payment</p>
                                    <p class="text-gray-600 text-sm">Pay securely with your Nagad mobile wallet</p>
                                    <?php if ($paymentConfig['nagad']['enabled']): ?>
                                    <div class="mt-3 p-3 bg-orange-50 rounded-lg border border-orange-200">
                                        <div class="text-sm text-gray-700">
                                            <p class="font-semibold text-orange-800 mb-2">💳 Payment Instructions:</p>
                                            <div class="space-y-1 text-xs">
                                                <p>1. Send money to: <span class="font-mono font-bold text-orange-600"><?php echo $paymentConfig['nagad']['account_number']; ?></span></p>
                                                <p>2. Amount: <span class="font-bold">৳<span id="nagad-amount"><?php echo number_format($finalTotal + 60, 2); ?></span></span></p>
                                                <p>3. Reference: <span class="font-mono">ORDER-<?php echo time(); ?></span></p>
                                                <p>4. After payment, you'll receive confirmation</p>
                                            </div>
                                            <div class="mt-2 pt-2 border-t border-orange-200">
                                                <p class="text-xs text-orange-600">
                                                    <i class="fas fa-phone mr-1"></i> Support: <?php echo $paymentConfig['nagad']['support_phone']; ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </label>

                        <!-- Card Payment -->
                        <label class="relative bg-gradient-to-r from-white to-gray-50 border-2 border-gray-200 rounded-xl p-6 cursor-pointer hover:border-purple-500 hover:shadow-lg transition-all duration-300">
                            <input type="radio" name="payment_method" value="card"
                                   class="absolute top-4 right-4 w-5 h-5 text-purple-600 focus:ring-purple-500">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mr-4">
                                    <i class="fas fa-credit-card text-purple-600 text-xl"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="font-bold text-gray-900">Card Payment</p>
                                    <p class="text-gray-600 text-sm">Pay securely with your debit/credit card</p>
                                    <?php if ($paymentConfig['card']['enabled']): ?>
                                    <div class="mt-3 p-3 bg-purple-50 rounded-lg border border-purple-200">
                                        <div class="text-sm text-gray-700">
                                            <p class="font-semibold text-purple-800 mb-2">💳 Payment Instructions:</p>
                                            <div class="space-y-1 text-xs">
                                                <p>1. You'll be redirected to secure payment page</p>
                                                <p>2. Amount: <span class="font-bold">৳<span id="card-amount"><?php echo number_format($finalTotal + 60, 2); ?></span></span></p>
                                                <p>3. Enter your card details securely</p>
                                                <p>4. Complete payment and return to confirm</p>
                                            </div>
                                            <div class="mt-2 pt-2 border-t border-purple-200">
                                                <p class="text-xs text-purple-600">
                                                    <i class="fas fa-credit-card mr-1"></i> Supported: <?php echo implode(', ', $paymentConfig['card']['supported_cards']); ?>
                                                </p>
                                                <p class="text-xs text-purple-600">
                                                    <i class="fas fa-phone mr-1"></i> Support: <?php echo $paymentConfig['card']['support_phone']; ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-xl p-8 sticky top-4 animate-slide-up" style="animation-delay: 0.6s;">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center mr-4">
                            <i class="fas fa-receipt text-orange-600 text-xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">Order Summary</h2>
                    </div>

                    <div class="space-y-4 mb-6">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="flex items-center space-x-4 p-4 bg-gradient-to-r from-gray-50 to-white rounded-xl border border-gray-100">
                                <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center overflow-hidden">
                                    <?php if ($item['image']): ?>
                                        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <i class="fas fa-image text-gray-400"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900 text-sm"><?php echo htmlspecialchars($item['name']); ?></p>
                                    <p class="text-gray-600 text-xs"><?php echo $item['quantity']; ?> × ৳<?php echo number_format($item['price'], 2); ?></p>
                                </div>
                                <p class="font-bold text-gray-900">৳<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="border-t border-gray-200 pt-6 space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal (<?php echo count($cartItems); ?> items)</span>
                            <span class="text-gray-900 font-medium">৳<?php echo number_format($total, 2); ?></span>
                        </div>
                        
                        <?php if ($discount > 0): ?>
                            <div class="flex justify-between text-sm">
                                <span class="text-green-600 font-medium">
                                    <i class="fas fa-tag mr-1"></i>
                                    Discount (<?php echo htmlspecialchars($appliedCoupon['code']); ?>)
                                </span>
                                <span class="text-green-600 font-medium">-৳<?php echo number_format($discount, 2); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Delivery Fee</span>
                            <span class="text-gray-900 font-medium">৳50.00</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Service Charge</span>
                            <span class="text-gray-900 font-medium">৳10.00</span>
                        </div>
                        <div class="flex justify-between text-sm" id="packaging-cost-row" style="display: none;">
                            <span class="text-gray-600">Packaging Cost</span>
                            <span class="text-gray-900 font-medium" id="packaging-cost-amount">৳0.00</span>
                        </div>
                        <hr class="my-3 border-gray-300">
                        <div class="flex justify-between text-lg font-bold">
                            <span class="text-gray-900">Total Amount</span>
                            <span class="text-green-600" id="total-amount">৳<?php echo number_format($finalTotal + 60, 2); ?></span>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3">
                        <button type="submit" id="place-order-btn" class="w-full flex justify-center py-4 px-6 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-4 focus:ring-green-200 transform hover:scale-105 transition-all duration-300">
                            <i class="fas fa-check-circle mr-2"></i>
                            <span id="btn-text">Place Order</span>
                        </button>

                        <!-- Payment Status Messages -->
                        <div id="payment-status" class="hidden">
                            <div id="payment-loading" class="hidden text-center p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="inline-flex items-center">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>
                                    <span>Processing payment...</span>
                                </div>
                            </div>
                            <div id="payment-success" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                                <div class="flex items-center">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <div>
                                        <p class="font-bold">Payment Successful!</p>
                                        <p class="text-sm">Order ID: <span id="success-order-id"></span></p>
                                        <p class="text-sm">Transaction ID: <span id="success-transaction-id"></span></p>
                                    </div>
                                </div>
                            </div>
                            <div id="payment-error" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                                <div class="flex items-center">
                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                    <div>
                                        <p class="font-bold">Payment Failed</p>
                                        <p class="text-sm" id="error-message"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <a href="/cart" class="w-full flex justify-center py-3 px-6 border-2 border-gray-300 rounded-xl text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-200 transition-all duration-300 text-center">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Cart
                        </a>
                    </div>

                    <div class="mt-6 p-4 bg-blue-50 rounded-xl border border-blue-200">
                        <div class="flex items-start">
                            <i class="fas fa-shield-alt text-blue-600 mt-1 mr-3"></i>
                            <div>
                                <p class="text-blue-800 font-semibold text-sm">Secure Checkout</p>
                                <p class="text-blue-600 text-xs mt-1">Your payment information is protected with 256-bit SSL encryption</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🛒 Checkout page loaded');
    
    const form = document.getElementById('checkout-form');
    const submitBtn = document.getElementById('place-order-btn');
    let isSubmitting = false;
    
    // Get selected surprise gift from sessionStorage
    const selectedSurpriseGift = sessionStorage.getItem('selectedSurpriseGift');
    if (selectedSurpriseGift !== null) {
        document.getElementById('selected-surprise-gift').value = selectedSurpriseGift;
        console.log('🎁 Selected surprise gift:', selectedSurpriseGift);
    }
    
    // Packaging options handling
    const packagingOptions = document.querySelectorAll('input[name="packaging_option"]');
    const ecoFriendlyInfo = document.getElementById('eco-friendly-info');
    const packagingCostRow = document.getElementById('packaging-cost-row');
    const packagingCostAmount = document.getElementById('packaging-cost-amount');
    const totalAmount = document.getElementById('total-amount');
    
    // Base total (subtotal + delivery + service - discount)
    const baseTotal = <?php echo $finalTotal + 60; ?>;
    
    function updatePackagingCost() {
        const selectedOption = document.querySelector('input[name="packaging_option"]:checked');
        if (!selectedOption) return;
        
        let packagingCost = 0;
        let showEcoInfo = false;
        
        switch(selectedOption.value) {
            case 'standard':
                packagingCost = 0;
                showEcoInfo = false;
                break;
            case 'eco_friendly':
                packagingCost = 0;
                showEcoInfo = true;
                break;
            case 'reusable_bag':
                packagingCost = 20;
                showEcoInfo = false;
                break;
        }
        
        // Update packaging cost display
        if (packagingCost > 0) {
            packagingCostRow.style.display = 'flex';
            packagingCostAmount.textContent = '৳' + packagingCost.toFixed(2);
        } else {
            packagingCostRow.style.display = 'none';
        }
        
        // Update total amount
        const newTotal = baseTotal + packagingCost;
        totalAmount.textContent = '৳' + newTotal.toFixed(2);
        
        // Show/hide eco-friendly info
        if (showEcoInfo) {
            ecoFriendlyInfo.classList.remove('hidden');
        } else {
            ecoFriendlyInfo.classList.add('hidden');
        }
    }
    
    // Add event listeners to packaging options
    packagingOptions.forEach(option => {
        option.addEventListener('change', updatePackagingCost);
    });
    
    // Initialize packaging cost calculation
    updatePackagingCost();
    
    if (form) {
        console.log('✅ Checkout form found');
        
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            console.log('🛒 ===== FORM SUBMISSION STARTED =====');
            if (isSubmitting) {
                console.warn('⏳ Submission already in progress, ignoring duplicate click');
                return;
            }
            isSubmitting = true;
            
            // Get form data
            const formData = new FormData(form);
            const data = {};
            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }
            
            console.log('🛒 Form data:', data);
            
            // Show loading state
            if (submitBtn) {
                submitBtn.disabled = true;
                const btnTextEl = document.getElementById('btn-text');
                if (btnTextEl) btnTextEl.textContent = 'Processing...';
            }
            
            // Hide any previous messages
            hidePaymentMessages();
            
            try {
                // Step 1: Create order
                console.log('🛒 Creating order...');
                const orderResponse = await fetch('/api/orders/create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        cart_items: <?php echo json_encode($cartItems); ?>,
                        delivery_address_id: data.address_id,
                        delivery_slot: data.delivery_slot,
                        payment_method: data.payment_method,
                        delivery_fee: 50.00,
                        discount: <?php echo $discount; ?>
                    })
                });
                
                const orderData = await orderResponse.json();
                console.log('🛒 Order response:', orderData);
                
                if (!orderData.success) {
                    throw new Error(orderData.error || 'Failed to create order');
                }
                
                // Step 2: Handle payment based on method
                if (data.payment_method === 'cod') {
                    // Cash on Delivery - no payment processing needed
                    showPaymentSuccess(orderData.order_id, null, 'Order placed successfully! Pay cash on delivery.');
                    // Keep button disabled to prevent duplicate orders
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        const btnTextEl = document.getElementById('btn-text');
                        if (btnTextEl) btnTextEl.textContent = 'Order Placed';
                    }
                } else {
                    // Online payment - initiate payment
                    console.log('🛒 Initiating payment...');
                    showPaymentLoading();
                    
                    const paymentResponse = await fetch('/api/payments/initiate', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            order_id: orderData.order_id,
                            method: data.payment_method
                        })
                    });
                    
                    const paymentData = await paymentResponse.json();
                    console.log('🛒 Payment response:', paymentData);
                    
                    if (!paymentData.success) {
                        throw new Error(paymentData.error || 'Payment initiation failed');
                    }
                    
                    // For demo purposes, simulate payment confirmation
                    // In production, you would redirect to paymentData.redirectUrl
                    console.log('🛒 Simulating payment confirmation...');
                    await new Promise(resolve => setTimeout(resolve, 2000)); // Simulate payment processing
                    
                    const confirmResponse = await fetch('/api/payments/confirm', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            order_id: orderData.order_id
                        })
                    });
                    
                    const confirmData = await confirmResponse.json();
                    console.log('🛒 Confirmation response:', confirmData);
                    
                    if (confirmData.success && confirmData.paid) {
                        showPaymentSuccess(orderData.order_id, confirmData.transaction_id, 'Payment successful! We will deliver your order in your selected slot.');
                        // Keep button disabled after success
                        if (submitBtn) {
                            submitBtn.disabled = true;
                            const btnTextEl = document.getElementById('btn-text');
                            if (btnTextEl) btnTextEl.textContent = 'Payment Successful';
                        }
                    } else {
                        throw new Error(confirmData.message || 'Payment confirmation failed');
                    }
                }
                
            } catch (error) {
                console.error('🛒 Error:', error);
                showPaymentError(error.message);
            } finally {
                // Reset button state only if not already completed successfully
                const successVisible = document.getElementById('payment-success') && !document.getElementById('payment-success').classList.contains('hidden');
                if (submitBtn && !successVisible) {
                    submitBtn.disabled = false;
                    const btnTextEl = document.getElementById('btn-text');
                    if (btnTextEl) btnTextEl.textContent = 'Place Order';
                }
                isSubmitting = false;
            }
        });
    } else {
        console.error('❌ Checkout form not found');
    }
    
    // Payment status functions
    function hidePaymentMessages() {
        const ps = document.getElementById('payment-status');
        const pl = document.getElementById('payment-loading');
        const ok = document.getElementById('payment-success');
        const er = document.getElementById('payment-error');
        if (ps) ps.classList.add('hidden');
        if (pl) pl.classList.add('hidden');
        if (ok) ok.classList.add('hidden');
        if (er) er.classList.add('hidden');
    }
    
    function showPaymentLoading() {
        hidePaymentMessages();
        const ps = document.getElementById('payment-status');
        const pl = document.getElementById('payment-loading');
        if (ps) ps.classList.remove('hidden');
        if (pl) pl.classList.remove('hidden');
    }
    
    function showPaymentSuccess(orderId, transactionId, message) {
        hidePaymentMessages();
        const ps = document.getElementById('payment-status');
        const ok = document.getElementById('payment-success');
        if (ps) ps.classList.remove('hidden');
        if (ok) ok.classList.remove('hidden');
        const so = document.getElementById('success-order-id');
        const st = document.getElementById('success-transaction-id');
        if (so) so.textContent = orderId;
        if (st) st.textContent = transactionId || 'N/A';
        const successDiv = document.getElementById('payment-success');
        if (successDiv) {
            const messageP = successDiv.querySelector('p:last-child');
            if (messageP) { messageP.textContent = message; }
        }
    }
    
    function showPaymentError(message) {
        hidePaymentMessages();
        const ps = document.getElementById('payment-status');
        const er = document.getElementById('payment-error');
        if (ps) ps.classList.remove('hidden');
        if (er) er.classList.remove('hidden');
        const em = document.getElementById('error-message');
        if (em) em.textContent = message;
    }
    
    // Check if user is logged in
    <?php if (!isset($_SESSION['user_id'])): ?>
        console.log('❌ User not logged in');
    <?php else: ?>
        console.log('✅ User logged in with ID: <?php echo $_SESSION['user_id']; ?>');
    <?php endif; ?>
    
    // Check cart items
    console.log('🛒 Cart items count: <?php echo count($cartItems); ?>');
    <?php if (!empty($cartItems)): ?>
        console.log('🛒 Cart items:', <?php echo json_encode($cartItems); ?>);
    <?php else: ?>
        console.log('❌ No cart items found');
    <?php endif; ?>
});
</script>

<?php
$content = ob_get_clean();
include 'app/views/layouts/main.php';
?>