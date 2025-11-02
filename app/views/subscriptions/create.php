<?php
$title = 'Create Subscription - GroceryApp';
ob_start();
?>

<div class="max-w-4xl mx-auto px-4 py-8 animate-fade-in">
    <!-- Breadcrumb -->
    <nav class="flex mb-8" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="/" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-green-600 transition-colors duration-200">
                    <i class="fas fa-home mr-2"></i>
                    Home
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <a href="/subscriptions" class="text-sm font-medium text-gray-700 hover:text-green-600 transition-colors duration-200">Subscriptions</a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <span class="text-sm font-medium text-gray-500">Create Subscription</span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Create New Subscription</h1>
        <p class="text-gray-600">Set up automatic recurring deliveries for your favorite groceries</p>
    </div>

    <?php if (empty($cartItems)): ?>
        <div class="bg-white rounded-2xl shadow-xl p-12 text-center">
            <div class="w-24 h-24 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-shopping-cart text-blue-600 text-4xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Your cart is empty</h3>
            <p class="text-gray-600 mb-6">Add items to your cart first to create a subscription</p>
            <a href="/products" class="inline-block bg-green-600 text-white px-8 py-3 rounded-xl font-semibold hover:bg-green-700 transition-colors duration-200">
                <i class="fas fa-store mr-2"></i>Start Shopping
            </a>
        </div>
    <?php else: ?>
        <form id="subscription-form" class="space-y-8">
            <!-- Products Section -->
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-box mr-3 text-blue-600"></i>
                    Products in Subscription
                </h2>
                <div class="space-y-4">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-xl">
                            <?php if ($item['image']): ?>
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-16 h-16 rounded-lg object-cover">
                            <?php else: ?>
                                <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-box text-gray-400"></i>
                                </div>
                            <?php endif; ?>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p class="text-sm text-gray-600">Quantity: <?php echo $item['quantity']; ?> x ৳<?php echo number_format($item['price'], 2); ?> = ৳<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                            </div>
                            <input type="hidden" name="product_ids[]" value="<?php echo $item['product_id']; ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Delivery Frequency -->
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-calendar-alt mr-3 text-blue-600"></i>
                    Delivery Frequency
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <label class="frequency-option cursor-pointer">
                        <input type="radio" name="frequency" value="weekly" class="hidden" checked>
                        <div class="border-2 border-gray-300 rounded-xl p-6 text-center hover:border-green-500 hover:bg-green-50 transition-all duration-200 checked:border-green-500 checked:bg-green-50">
                            <i class="fas fa-calendar-day text-3xl text-blue-600 mb-3"></i>
                            <h3 class="font-bold text-gray-900 mb-2">Weekly</h3>
                            <p class="text-sm text-gray-600">Every week</p>
                        </div>
                    </label>
                    <label class="frequency-option cursor-pointer">
                        <input type="radio" name="frequency" value="bi_weekly" class="hidden">
                        <div class="border-2 border-gray-300 rounded-xl p-6 text-center hover:border-green-500 hover:bg-green-50 transition-all duration-200 checked:border-green-500 checked:bg-green-50">
                            <i class="fas fa-calendar-check text-3xl text-blue-600 mb-3"></i>
                            <h3 class="font-bold text-gray-900 mb-2">Bi-Weekly</h3>
                            <p class="text-sm text-gray-600">Every 2 weeks</p>
                        </div>
                    </label>
                    <label class="frequency-option cursor-pointer">
                        <input type="radio" name="frequency" value="monthly" class="hidden">
                        <div class="border-2 border-gray-300 rounded-xl p-6 text-center hover:border-green-500 hover:bg-green-50 transition-all duration-200 checked:border-green-500 checked:bg-green-50">
                            <i class="fas fa-calendar text-3xl text-blue-600 mb-3"></i>
                            <h3 class="font-bold text-gray-900 mb-2">Monthly</h3>
                            <p class="text-sm text-gray-600">Every month</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Payment Method -->
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-credit-card mr-3 text-blue-600"></i>
                    Payment Method
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="payment-option cursor-pointer">
                        <input type="radio" name="payment_method" value="cash_on_delivery" class="hidden" checked>
                        <div class="border-2 border-gray-300 rounded-xl p-6 hover:border-green-500 hover:bg-green-50 transition-all duration-200 checked:border-green-500 checked:bg-green-50">
                            <i class="fas fa-money-bill-wave text-3xl text-green-600 mb-3"></i>
                            <h3 class="font-bold text-gray-900 mb-2">Cash on Delivery</h3>
                            <p class="text-sm text-gray-600">Pay when you receive</p>
                        </div>
                    </label>
                    <label class="payment-option cursor-pointer">
                        <input type="radio" name="payment_method" value="pre_paid" class="hidden">
                        <div class="border-2 border-gray-300 rounded-xl p-6 hover:border-green-500 hover:bg-green-50 transition-all duration-200 checked:border-green-500 checked:bg-green-50">
                            <i class="fas fa-credit-card text-3xl text-blue-600 mb-3"></i>
                            <h3 class="font-bold text-gray-900 mb-2">Pre-Paid</h3>
                            <p class="text-sm text-gray-600">Pay in advance</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Delivery Address -->
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-map-marker-alt mr-3 text-blue-600"></i>
                    Delivery Address
                </h2>
                <?php if (empty($addresses)): ?>
                    <p class="text-gray-600 mb-4">Please add an address in your profile first.</p>
                    <a href="/profile" class="text-blue-600 hover:text-blue-700 font-semibold">
                        <i class="fas fa-plus mr-2"></i>Add Address
                    </a>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($addresses as $address): ?>
                            <label class="address-option cursor-pointer">
                                <input type="radio" name="delivery_address_id" value="<?php echo $address['id']; ?>" class="hidden" <?php echo $address['is_default'] ? 'checked' : ''; ?>>
                                <div class="border-2 border-gray-300 rounded-xl p-4 hover:border-green-500 hover:bg-green-50 transition-all duration-200 checked:border-green-500 checked:bg-green-50">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <h3 class="font-bold text-gray-900"><?php echo htmlspecialchars($address['address_line1']); ?></h3>
                                            <?php if ($address['address_line2']): ?>
                                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($address['address_line2']); ?></p>
                                            <?php endif; ?>
                                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($address['city']); ?>, <?php echo htmlspecialchars($address['state'] ?? ''); ?></p>
                                        </div>
                                        <?php if ($address['is_default']): ?>
                                            <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded-full">Default</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Delivery Time Slot -->
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-clock mr-3 text-blue-600"></i>
                    Preferred Delivery Time
                </h2>
                <select name="delivery_slot" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-green-100 focus:border-green-500 transition-all duration-300">
                    <option value="">Select preferred time</option>
                    <option value="Morning (9:00 AM - 12:00 PM)">Morning (9:00 AM - 12:00 PM)</option>
                    <option value="Afternoon (12:00 PM - 3:00 PM)">Afternoon (12:00 PM - 3:00 PM)</option>
                    <option value="Evening (3:00 PM - 6:00 PM)">Evening (3:00 PM - 6:00 PM)</option>
                    <option value="Any Time">Any Time</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between bg-white rounded-2xl shadow-xl p-6">
                <a href="/cart" class="text-gray-600 hover:text-gray-900 font-semibold">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Cart
                </a>
                <button type="submit" class="bg-green-600 text-white px-8 py-3 rounded-xl font-bold text-lg hover:bg-green-700 transition-colors duration-200">
                    <i class="fas fa-check mr-2"></i>Create Subscription
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
// Handle frequency option clicks
document.querySelectorAll('.frequency-option input').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.frequency-option > div').forEach(div => {
            div.classList.remove('border-green-500', 'bg-green-50');
        });
        this.parentElement.querySelector('div').classList.add('border-green-500', 'bg-green-50');
    });
});

// Handle payment option clicks
document.querySelectorAll('.payment-option input').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.payment-option > div').forEach(div => {
            div.classList.remove('border-green-500', 'bg-green-50');
        });
        this.parentElement.querySelector('div').classList.add('border-green-500', 'bg-green-50');
    });
});

// Handle address option clicks
document.querySelectorAll('.address-option input').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.address-option > div').forEach(div => {
            div.classList.remove('border-green-500', 'bg-green-50');
        });
        this.parentElement.querySelector('div').classList.add('border-green-500', 'bg-green-50');
    });
});

// Check if initially checked items are styled
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('input[type="radio"]:checked').forEach(radio => {
        const parentDiv = radio.parentElement.querySelector('div');
        if (parentDiv) {
            parentDiv.classList.add('border-green-500', 'bg-green-50');
        }
    });
});

// Handle form submission
document.getElementById('subscription-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const productIds = Array.from(formData.getAll('product_ids[]'));
    
    formData.append('product_ids', JSON.stringify(productIds));
    
    fetch('/subscriptions/store', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Subscription created successfully!', 'success');
            setTimeout(() => {
                window.location.href = data.redirect || '/subscriptions';
            }, 1500);
        } else {
            showToast(data.message || 'Failed to create subscription', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    });
});
</script>

<?php
$content = ob_get_clean();
include 'app/views/layouts/main.php';
?>
