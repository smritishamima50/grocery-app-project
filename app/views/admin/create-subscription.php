<?php
$title = 'Create Subscription - Admin';
ob_start();
?>

<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Create Subscription</h1>
        <p class="text-gray-600 mt-2">Add a subscription for a user with preset amounts</p>
    </div>

    <form id="create-subscription-form" class="bg-white rounded-lg shadow-md p-8 space-y-6">
        <!-- User Selection -->
        <div>
            <label for="user_id" class="block text-sm font-semibold text-gray-700 mb-2">Select User</label>
            <select id="user_id" name="user_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                <option value="">Choose a user...</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo $user['id']; ?>">
                        <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name'] . ' (' . $user['email'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Frequency Selection -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Delivery Frequency</label>
            <div class="grid grid-cols-3 gap-4">
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

        <!-- Amount Selection -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Subscription Amount</label>
            <div class="grid grid-cols-3 gap-4">
                <label class="amount-option cursor-pointer">
                    <input type="radio" name="amount" value="200" class="hidden" checked>
                    <div class="border-2 border-gray-300 rounded-xl p-6 text-center hover:border-green-500 hover:bg-green-50 transition-all duration-200 checked:border-green-500 checked:bg-green-50">
                        <div class="text-3xl font-bold text-green-600 mb-2">৳200</div>
                        <p class="text-sm text-gray-600">Weekly Basic</p>
                    </div>
                </label>
                <label class="amount-option cursor-pointer">
                    <input type="radio" name="amount" value="500" class="hidden">
                    <div class="border-2 border-gray-300 rounded-xl p-6 text-center hover:border-green-500 hover:bg-green-50 transition-all duration-200 checked:border-green-500 checked:bg-green-50">
                        <div class="text-3xl font-bold text-green-600 mb-2">৳500</div>
                        <p class="text-sm text-gray-600">Bi-Weekly Standard</p>
                    </div>
                </label>
                <label class="amount-option cursor-pointer">
                    <input type="radio" name="amount" value="1000" class="hidden">
                    <div class="border-2 border-gray-300 rounded-xl p-6 text-center hover:border-green-500 hover:bg-green-50 transition-all duration-200 checked:border-green-500 checked:bg-green-50">
                        <div class="text-3xl font-bold text-green-600 mb-2">৳1,000</div>
                        <p class="text-sm text-gray-600">Monthly Premium</p>
                    </div>
                </label>
            </div>
        </div>

        <!-- Delivery Slot -->
        <div>
            <label for="delivery_slot" class="block text-sm font-semibold text-gray-700 mb-2">Delivery Time Slot</label>
            <select id="delivery_slot" name="delivery_slot" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                <option value="">Select time slot...</option>
                <option value="Morning (9:00 AM - 12:00 PM)">Morning (9:00 AM - 12:00 PM)</option>
                <option value="Afternoon (12:00 PM - 4:00 PM)">Afternoon (12:00 PM - 4:00 PM)</option>
                <option value="Evening (4:00 PM - 8:00 PM)">Evening (4:00 PM - 8:00 PM)</option>
            </select>
        </div>

        <!-- Buttons -->
        <div class="flex space-x-4 pt-4">
            <button type="submit" class="flex-1 bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition duration-300 font-semibold">
                <i class="fas fa-check-circle mr-2"></i>Create Subscription
            </button>
            <a href="/admin/subscriptions" class="flex-1 bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 transition duration-300 font-semibold text-center">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
// Handle frequency and amount selection styling
document.querySelectorAll('.frequency-option input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.frequency-option div').forEach(div => {
            div.classList.remove('border-green-500', 'bg-green-50');
        });
        if (this.checked) {
            this.closest('.frequency-option').querySelector('div').classList.add('border-green-500', 'bg-green-50');
        }
    });
});

document.querySelectorAll('.amount-option input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.amount-option div').forEach(div => {
            div.classList.remove('border-green-500', 'bg-green-50');
        });
        if (this.checked) {
            this.closest('.amount-option').querySelector('div').classList.add('border-green-500', 'bg-green-50');
        }
    });
});

// Initialize checked states
document.querySelectorAll('.frequency-option input[type="radio"]:checked').forEach(radio => {
    radio.dispatchEvent(new Event('change'));
});
document.querySelectorAll('.amount-option input[type="radio"]:checked').forEach(radio => {
    radio.dispatchEvent(new Event('change'));
});

// Form submission
document.getElementById('create-subscription-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('/admin/create-subscription/store', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Subscription created successfully!');
            window.location.href = data.redirect || '/admin/subscriptions';
        } else {
            alert(data.message || 'Failed to create subscription');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
});
</script>

<?php
$content = ob_get_clean();
include 'app/views/admin/layout.php';
?>
