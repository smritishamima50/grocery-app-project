<?php
$title = 'My Profile - GroceryApp';
ob_start();
?>

<div class="max-w-7xl mx-auto px-4 py-8 animate-fade-in">
    <!-- Breadcrumb -->
    <nav class="flex mb-8" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="/" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-green-600 transition-colors duration-200">
                    <i class="fas fa-home mr-2"></i>
                    Home
                </a>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <span class="text-sm font-medium text-gray-500">My Profile</span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Profile Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-xl p-6 animate-slide-up">
                <div class="text-center mb-6">
                    <div class="w-24 h-24 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                        <i class="fas fa-user text-white text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                    <p class="text-gray-600"><?php echo htmlspecialchars($user['email']); ?></p>
                    <span class="inline-block bg-green-100 text-green-800 text-sm font-semibold px-3 py-1 rounded-full mt-2">
                        <i class="fas fa-crown mr-1"></i><?php echo ucfirst($user['role']); ?>
                    </span>
                </div>

                <div class="space-y-3">
                    <button onclick="showSection('profile')" class="nav-tab w-full text-left px-4 py-3 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all duration-200 flex items-center active" data-section="profile">
                        <i class="fas fa-user mr-3"></i>
                        <span>Profile Information</span>
                    </button>
                    <button onclick="showSection('addresses')" class="nav-tab w-full text-left px-4 py-3 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all duration-200 flex items-center" data-section="addresses">
                        <i class="fas fa-map-marker-alt mr-3"></i>
                        <span>Addresses</span>
                    </button>
                    <button onclick="showSection('diet')" class="nav-tab w-full text-left px-4 py-3 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all duration-200 flex items-center" data-section="diet">
                        <i class="fas fa-apple-alt mr-3"></i>
                        <span>Diet Profile</span>
                    </button>
                    <button onclick="showSection('subscriptions')" class="nav-tab w-full text-left px-4 py-3 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all duration-200 flex items-center" data-section="subscriptions">
                        <i class="fas fa-sync-alt mr-3"></i>
                        <span>My Subscriptions</span>
                    </button>
                    <button onclick="showSection('security')" class="nav-tab w-full text-left px-4 py-3 rounded-xl hover:bg-blue-50 hover:text-blue-600 transition-all duration-200 flex items-center" data-section="security">
                        <i class="fas fa-shield-alt mr-3"></i>
                        <span>Security</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="lg:col-span-2">
            <!-- Profile Information Section -->
            <div id="profile-section" class="content-section bg-white rounded-2xl shadow-xl p-8 animate-slide-up">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-user mr-3 text-blue-600"></i>
                        Profile Information
                    </h2>
                    <button onclick="editProfile()" class="bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 transition-colors duration-200">
                        <i class="fas fa-edit mr-2"></i>Edit Profile
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">First Name</label>
                        <p class="text-gray-900 bg-gray-50 px-4 py-3 rounded-xl"><?php echo htmlspecialchars($user['first_name']); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Last Name</label>
                        <p class="text-gray-900 bg-gray-50 px-4 py-3 rounded-xl"><?php echo htmlspecialchars($user['last_name']); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                        <p class="text-gray-900 bg-gray-50 px-4 py-3 rounded-xl"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Phone</label>
                        <p class="text-gray-900 bg-gray-50 px-4 py-3 rounded-xl"><?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Member Since</label>
                        <p class="text-gray-900 bg-gray-50 px-4 py-3 rounded-xl"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Last Updated</label>
                        <p class="text-gray-900 bg-gray-50 px-4 py-3 rounded-xl"><?php echo date('F j, Y', strtotime($user['updated_at'])); ?></p>
                    </div>
                </div>
            </div>

            <!-- Addresses Section -->
            <div id="addresses-section" class="content-section bg-white rounded-2xl shadow-xl p-8 animate-slide-up hidden">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-map-marker-alt mr-3 text-green-600"></i>
                        My Addresses
                    </h2>
                    <button onclick="addNewAddress()" class="bg-green-600 text-white px-4 py-2 rounded-xl hover:bg-green-700 transition-colors duration-200">
                        <i class="fas fa-plus mr-2"></i>Add Address
                    </button>
                </div>

                <div id="addresses-list" class="space-y-4">
                    <?php if (empty($addresses)): ?>
                        <div class="text-center py-12">
                            <i class="fas fa-map-marker-alt text-gray-400 text-4xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No addresses found</h3>
                            <p class="text-gray-500 mb-4">Add your first delivery address</p>
                            <button onclick="addNewAddress()" class="bg-green-600 text-white px-6 py-3 rounded-xl hover:bg-green-700 transition-colors duration-200">
                                <i class="fas fa-plus mr-2"></i>Add Your First Address
                            </button>
                        </div>
                    <?php else: ?>
                        <?php foreach ($addresses as $address): ?>
                            <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-shadow duration-200">
                                <div class="flex items-start justify-between">
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
                                        <p class="text-gray-900 font-medium mb-1"><?php echo htmlspecialchars($address['address_line1']); ?></p>
                                        <?php if ($address['address_line2']): ?>
                                            <p class="text-gray-600 mb-1"><?php echo htmlspecialchars($address['address_line2']); ?></p>
                                        <?php endif; ?>
                                        <p class="text-gray-600">
                                            <?php echo htmlspecialchars($address['city']); ?><?php if ($address['state']): ?>, <?php echo htmlspecialchars($address['state']); ?><?php endif; ?>
                                            <?php if ($address['zip_code']): ?> - <?php echo htmlspecialchars($address['zip_code']); ?><?php endif; ?>
                                        </p>
                                        <p class="text-gray-600"><?php echo htmlspecialchars($address['country']); ?></p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <button onclick="editAddress(<?php echo $address['id']; ?>)" class="text-blue-600 hover:text-blue-700 p-2">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteAddress(<?php echo $address['id']; ?>)" class="text-red-600 hover:text-red-700 p-2">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Diet Profile Section -->
            <div id="diet-section" class="content-section bg-white rounded-2xl shadow-xl p-8 animate-slide-up hidden">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-apple-alt mr-3 text-orange-600"></i>
                        Diet Profile
                    </h2>
                </div>

                <?php if ($dietProfile): ?>
                    <div class="bg-gradient-to-r from-orange-50 to-yellow-50 border border-orange-200 rounded-xl p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Current Diet Profile</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <div>
                                <span class="text-sm text-gray-600">Diet Goal:</span>
                                <p class="font-semibold text-gray-900"><?php echo ucfirst(str_replace('_', ' ', $dietProfile['diet_goal'])); ?></p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Daily Calorie Target:</span>
                                <p class="font-semibold text-gray-900"><?php echo $dietProfile['calorie_target']; ?> kcal</p>
                            </div>
                            <?php if ($dietProfile['current_weight']): ?>
                            <div>
                                <span class="text-sm text-gray-600">Current Weight:</span>
                                <p class="font-semibold text-gray-900"><?php echo $dietProfile['current_weight']; ?> kg</p>
                            </div>
                            <?php endif; ?>
                            <?php if ($dietProfile['target_weight']): ?>
                            <div>
                                <span class="text-sm text-gray-600">Target Weight:</span>
                                <p class="font-semibold text-gray-900"><?php echo $dietProfile['target_weight']; ?> kg</p>
                            </div>
                            <?php endif; ?>
                            <?php if ($dietProfile['bmi']): ?>
                            <div>
                                <span class="text-sm text-gray-600">BMI:</span>
                                <p class="font-semibold text-gray-900"><?php echo $dietProfile['bmi']; ?></p>
                            </div>
                            <?php endif; ?>
                            <?php if ($dietProfile['activity_level']): ?>
                            <div>
                                <span class="text-sm text-gray-600">Activity Level:</span>
                                <p class="font-semibold text-gray-900"><?php echo ucfirst(str_replace('_', ' ', $dietProfile['activity_level'])); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-gray-900">Set Up Your Diet Profile</h3>
                    <p class="text-gray-600 mb-6">Select your diet goals and calorie targets to get personalized product recommendations.</p>
                    
                    <form id="diet-profile-form" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="diet_goal" class="block text-sm font-semibold text-gray-700 mb-2">Diet Goal</label>
                                <select id="diet_goal" name="diet_goal" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-orange-100 focus:border-orange-500 transition-all duration-300">
                                    <option value="general" <?php echo ($dietProfile && $dietProfile['diet_goal'] == 'general') ? 'selected' : ''; ?>>General Health</option>
                                    <option value="weight_loss" <?php echo ($dietProfile && $dietProfile['diet_goal'] == 'weight_loss') ? 'selected' : ''; ?>>Weight Loss</option>
                                    <option value="weight_gain" <?php echo ($dietProfile && $dietProfile['diet_goal'] == 'weight_gain') ? 'selected' : ''; ?>>Weight Gain</option>
                                    <option value="muscle_gain" <?php echo ($dietProfile && $dietProfile['diet_goal'] == 'muscle_gain') ? 'selected' : ''; ?>>Muscle Building</option>
                                    <option value="diabetes_friendly" <?php echo ($dietProfile && $dietProfile['diet_goal'] == 'diabetes_friendly') ? 'selected' : ''; ?>>Diabetes Management</option>
                                    <option value="low_sodium" <?php echo ($dietProfile && $dietProfile['diet_goal'] == 'low_sodium') ? 'selected' : ''; ?>>Low Sodium</option>
                                    <option value="vegetarian" <?php echo ($dietProfile && $dietProfile['diet_goal'] == 'vegetarian') ? 'selected' : ''; ?>>Vegetarian</option>
                                    <option value="vegan" <?php echo ($dietProfile && $dietProfile['diet_goal'] == 'vegan') ? 'selected' : ''; ?>>Vegan</option>
                                    <option value="keto" <?php echo ($dietProfile && $dietProfile['diet_goal'] == 'keto') ? 'selected' : ''; ?>>Ketogenic</option>
                                    <option value="paleo" <?php echo ($dietProfile && $dietProfile['diet_goal'] == 'paleo') ? 'selected' : ''; ?>>Paleolithic</option>
                                    <option value="mediterranean" <?php echo ($dietProfile && $dietProfile['diet_goal'] == 'mediterranean') ? 'selected' : ''; ?>>Mediterranean</option>
                                    <option value="heart_healthy" <?php echo ($dietProfile && $dietProfile['diet_goal'] == 'heart_healthy') ? 'selected' : ''; ?>>Heart Health</option>
                                    <option value="low_carb" <?php echo ($dietProfile && $dietProfile['diet_goal'] == 'low_carb') ? 'selected' : ''; ?>>Low Carbohydrate</option>
                                    <option value="high_protein" <?php echo ($dietProfile && $dietProfile['diet_goal'] == 'high_protein') ? 'selected' : ''; ?>>High Protein</option>
                                </select>
                            </div>

                            <div>
                                <label for="activity_level" class="block text-sm font-semibold text-gray-700 mb-2">Activity Level</label>
                                <select id="activity_level" name="activity_level" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-orange-100 focus:border-orange-500 transition-all duration-300">
                                    <option value="sedentary" <?php echo ($dietProfile && $dietProfile['activity_level'] == 'sedentary') ? 'selected' : ''; ?>>Sedentary (Little/No Exercise)</option>
                                    <option value="lightly_active" <?php echo ($dietProfile && $dietProfile['activity_level'] == 'lightly_active') ? 'selected' : ''; ?>>Lightly Active (Light Exercise 1-3 days/week)</option>
                                    <option value="moderately_active" <?php echo ($dietProfile && $dietProfile['activity_level'] == 'moderately_active') ? 'selected' : ''; ?>>Moderately Active (Moderate Exercise 3-5 days/week)</option>
                                    <option value="very_active" <?php echo ($dietProfile && $dietProfile['activity_level'] == 'very_active') ? 'selected' : ''; ?>>Very Active (Heavy Exercise 6-7 days/week)</option>
                                    <option value="extremely_active" <?php echo ($dietProfile && $dietProfile['activity_level'] == 'extremely_active') ? 'selected' : ''; ?>>Extremely Active (Very Heavy Exercise, Physical Job)</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="current_weight" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Current Weight (kg)
                                    <span class="text-gray-500 text-xs ml-2">(Optional)</span>
                                </label>
                                <input type="number" id="current_weight" name="current_weight" min="30" max="300" step="0.1"
                                       value="<?php echo $dietProfile ? $dietProfile['current_weight'] : ''; ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-orange-100 focus:border-orange-500 transition-all duration-300"
                                       placeholder="e.g., 70.5">
                            </div>

                            <div>
                                <label for="target_weight" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Target Weight (kg)
                                    <span class="text-gray-500 text-xs ml-2">(Optional)</span>
                                </label>
                                <input type="number" id="target_weight" name="target_weight" min="30" max="300" step="0.1"
                                       value="<?php echo $dietProfile ? $dietProfile['target_weight'] : ''; ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-orange-100 focus:border-orange-500 transition-all duration-300"
                                       placeholder="e.g., 65.0">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="height" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Height (cm)
                                    <span class="text-gray-500 text-xs ml-2">(Optional)</span>
                                </label>
                                <input type="number" id="height" name="height" min="100" max="250" step="0.1"
                                       value="<?php echo $dietProfile ? $dietProfile['height'] : ''; ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-orange-100 focus:border-orange-500 transition-all duration-300"
                                       placeholder="e.g., 170.0">
                            </div>

                            <div>
                                <label for="age" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Age (years)
                                    <span class="text-gray-500 text-xs ml-2">(Optional)</span>
                                </label>
                                <input type="number" id="age" name="age" min="10" max="120"
                                       value="<?php echo $dietProfile ? $dietProfile['age'] : ''; ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-orange-100 focus:border-orange-500 transition-all duration-300"
                                       placeholder="e.g., 25">
                            </div>
                        </div>

                        <div>
                            <label for="calorie_target" class="block text-sm font-semibold text-gray-700 mb-2">
                                Daily Calorie Target (kcal)
                                <span class="text-gray-500 text-xs ml-2">(800 - 5000 kcal)</span>
                            </label>
                            <input type="number" id="calorie_target" name="calorie_target" required min="800" max="5000" step="50"
                                   value="<?php echo $dietProfile ? $dietProfile['calorie_target'] : '2000'; ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-orange-100 focus:border-orange-500 transition-all duration-300">
                        </div>

                        <button type="submit" class="bg-orange-600 text-white px-6 py-3 rounded-xl hover:bg-orange-700 transition-colors duration-200">
                            <i class="fas fa-save mr-2"></i><?php echo $dietProfile ? 'Update' : 'Save'; ?> Diet Profile
                        </button>
                    </form>

                    <?php if ($dietProfile): ?>
                        <div class="mt-6 p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r">
                            <p class="text-sm text-blue-800">
                                <i class="fas fa-info-circle mr-2"></i>
                                Based on your diet profile, you'll see personalized product recommendations on the home page.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Subscriptions Section -->
            <div id="subscriptions-section" class="content-section bg-white rounded-2xl shadow-xl p-8 animate-slide-up hidden">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-sync-alt mr-3 text-blue-600"></i>
                        My Subscriptions
                    </h2>
                    <a href="/subscriptions/create" class="bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 transition-colors duration-200">
                        <i class="fas fa-plus mr-2"></i>New Subscription
                    </a>
                </div>

                <?php if (empty($subscriptions)): ?>
                    <div class="text-center py-12">
                        <div class="w-24 h-24 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-calendar-alt text-blue-600 text-4xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">No subscriptions yet</h3>
                        <p class="text-gray-600 mb-6">Set up a recurring order to get your groceries delivered automatically!</p>
                        <a href="/subscriptions/create" class="inline-block bg-blue-600 text-white px-8 py-3 rounded-xl font-semibold hover:bg-blue-700 transition-colors duration-200">
                            <i class="fas fa-plus mr-2"></i>Create Your First Subscription
                        </a>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($subscriptions as $subscription): ?>
                            <?php
                            $statusColors = [
                                'active' => 'bg-green-100 text-green-800',
                                'paused' => 'bg-yellow-100 text-yellow-800',
                                'cancelled' => 'bg-red-100 text-red-800'
                            ];
                            $statusColor = $statusColors[$subscription['status']] ?? 'bg-gray-100 text-gray-800';
                            
                            $frequencyLabels = [
                                'weekly' => 'Every Week',
                                'bi_weekly' => 'Every 2 Weeks',
                                'monthly' => 'Every Month'
                            ];
                            $frequencyLabel = $frequencyLabels[$subscription['frequency']] ?? $subscription['frequency'];
                            ?>
                            <div class="border border-gray-200 rounded-xl p-6 hover:shadow-md transition-all duration-200">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <!-- Subscription Header -->
                                        <div class="flex flex-wrap items-center gap-3 mb-4">
                                            <span class="px-3 py-1 rounded-full text-sm font-semibold <?php echo $statusColor; ?> capitalize">
                                                <?php echo str_replace('_', ' ', $subscription['status']); ?>
                                            </span>
                                            <span class="text-blue-600 font-semibold">
                                                <i class="fas fa-sync-alt mr-2"></i><?php echo $frequencyLabel; ?>
                                            </span>
                                            <?php if (!empty($subscription['amount']) && $subscription['amount'] > 0): ?>
                                            <span class="text-green-600 font-semibold">
                                                <i class="fas fa-dollar-sign mr-2"></i>৳<?php echo number_format($subscription['amount'], 0); ?>
                                            </span>
                                            <?php endif; ?>
                                            <span class="text-gray-600">
                                                <i class="fas fa-<?php echo $subscription['payment_method'] === 'pre_paid' ? 'credit-card' : 'money-bill-wave'; ?> mr-2"></i>
                                                <?php echo str_replace('_', ' ', ucwords($subscription['payment_method'])); ?>
                                            </span>
                                        </div>

                                        <!-- Products in Subscription -->
                                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-4">
                                            <?php foreach ($subscription['products'] as $product): ?>
                                                <div class="flex items-center space-x-2 p-2 bg-gray-50 rounded-lg">
                                                    <?php if ($product['image']): ?>
                                                        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-10 h-10 rounded-lg object-cover">
                                                    <?php else: ?>
                                                        <div class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center">
                                                            <i class="fas fa-box text-gray-400 text-xs"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="flex-1">
                                                        <p class="font-medium text-gray-900 text-xs truncate"><?php echo htmlspecialchars($product['name']); ?></p>
                                                        <p class="text-xs text-gray-600">৳<?php echo number_format($product['price'], 2); ?></p>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>

                                        <!-- Subscription Details -->
                                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                                            <div>
                                                <span class="text-gray-600">Next Delivery:</span>
                                                <p class="font-semibold text-gray-900 text-xs">
                                                    <?php echo date('M j, Y', strtotime($subscription['next_delivery_date'])); ?>
                                                </p>
                                            </div>
                                            <div>
                                                <span class="text-gray-600">Started:</span>
                                                <p class="font-semibold text-gray-900 text-xs">
                                                    <?php echo date('M j, Y', strtotime($subscription['start_date'])); ?>
                                                </p>
                                            </div>
                                            <?php if ($subscription['delivery_slot_preference']): ?>
                                            <div>
                                                <span class="text-gray-600">Delivery Time:</span>
                                                <p class="font-semibold text-gray-900 text-xs"><?php echo htmlspecialchars($subscription['delivery_slot_preference']); ?></p>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex flex-col gap-2 ml-4">
                                        <?php if ($subscription['status'] === 'active'): ?>
                                            <button onclick="pauseSubscription(<?php echo $subscription['id']; ?>)" class="bg-yellow-500 text-white px-3 py-2 rounded-lg font-semibold hover:bg-yellow-600 transition-colors duration-200 text-sm whitespace-nowrap">
                                                <i class="fas fa-pause mr-2"></i>Pause
                                            </button>
                                            <button onclick="cancelSubscription(<?php echo $subscription['id']; ?>)" class="bg-red-500 text-white px-3 py-2 rounded-lg font-semibold hover:bg-red-600 transition-colors duration-200 text-sm whitespace-nowrap">
                                                <i class="fas fa-times mr-2"></i>Cancel
                                            </button>
                                        <?php elseif ($subscription['status'] === 'paused'): ?>
                                            <button onclick="resumeSubscription(<?php echo $subscription['id']; ?>)" class="bg-green-500 text-white px-3 py-2 rounded-lg font-semibold hover:bg-green-600 transition-colors duration-200 text-sm whitespace-nowrap">
                                                <i class="fas fa-play mr-2"></i>Resume
                                            </button>
                                            <button onclick="cancelSubscription(<?php echo $subscription['id']; ?>)" class="bg-red-500 text-white px-3 py-2 rounded-lg font-semibold hover:bg-red-600 transition-colors duration-200 text-sm whitespace-nowrap">
                                                <i class="fas fa-times mr-2"></i>Cancel
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Security Section -->
            <div id="security-section" class="content-section bg-white rounded-2xl shadow-xl p-8 animate-slide-up hidden">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-shield-alt mr-3 text-purple-600"></i>
                        Security Settings
                    </h2>
                </div>

                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Change Password</h3>
                        <form id="change-password-form" class="space-y-4">
                            <div>
                                <label for="current_password" class="block text-sm font-semibold text-gray-700 mb-2">Current Password</label>
                                <input type="password" id="current_password" name="current_password" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-purple-100 focus:border-purple-500 transition-all duration-300">
                            </div>
                            <div>
                                <label for="new_password" class="block text-sm font-semibold text-gray-700 mb-2">New Password</label>
                                <input type="password" id="new_password" name="new_password" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-purple-100 focus:border-purple-500 transition-all duration-300">
                            </div>
                            <div>
                                <label for="confirm_password" class="block text-sm font-semibold text-gray-700 mb-2">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-purple-100 focus:border-purple-500 transition-all duration-300">
                            </div>
                            <button type="submit" class="bg-purple-600 text-white px-6 py-3 rounded-xl hover:bg-purple-700 transition-colors duration-200">
                                <i class="fas fa-key mr-2"></i>Change Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div id="edit-profile-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 animate-bounce-in">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">Edit Profile</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="edit-profile-form" class="space-y-4">
                <div>
                    <label for="edit_first_name" class="block text-sm font-semibold text-gray-700 mb-2">First Name</label>
                    <input type="text" id="edit_first_name" name="first_name" required
                           value="<?php echo htmlspecialchars($user['first_name']); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all duration-300">
                </div>
                <div>
                    <label for="edit_last_name" class="block text-sm font-semibold text-gray-700 mb-2">Last Name</label>
                    <input type="text" id="edit_last_name" name="last_name" required
                           value="<?php echo htmlspecialchars($user['last_name']); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all duration-300">
                </div>
                <div>
                    <label for="edit_phone" class="block text-sm font-semibold text-gray-700 mb-2">Phone</label>
                    <input type="tel" id="edit_phone" name="phone"
                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all duration-300">
                </div>
                <div class="flex space-x-4">
                    <button type="submit" class="flex-1 bg-blue-600 text-white py-3 rounded-xl hover:bg-blue-700 transition-colors duration-200">
                        <i class="fas fa-save mr-2"></i>Save Changes
                    </button>
                    <button type="button" onclick="closeModal()" class="flex-1 bg-gray-300 text-gray-700 py-3 rounded-xl hover:bg-gray-400 transition-colors duration-200">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Address Modal -->
<div id="address-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 animate-bounce-in">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900" id="address-modal-title">Add Address</h3>
                <button onclick="closeAddressModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="address-form" class="space-y-4">
                <input type="hidden" id="address_id" name="address_id">
                <div>
                    <label for="address_type" class="block text-sm font-semibold text-gray-700 mb-2">Address Type</label>
                    <select id="address_type" name="address_type" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-green-100 focus:border-green-500 transition-all duration-300">
                        <option value="home">Home</option>
                        <option value="office">Office</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label for="address_line1" class="block text-sm font-semibold text-gray-700 mb-2">Address Line 1</label>
                    <input type="text" id="address_line1" name="address_line1" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-green-100 focus:border-green-500 transition-all duration-300">
                </div>
                <div>
                    <label for="address_line2" class="block text-sm font-semibold text-gray-700 mb-2">Address Line 2 (Optional)</label>
                    <input type="text" id="address_line2" name="address_line2"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-green-100 focus:border-green-500 transition-all duration-300">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="city" class="block text-sm font-semibold text-gray-700 mb-2">City</label>
                        <input type="text" id="city" name="city" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-green-100 focus:border-green-500 transition-all duration-300">
                    </div>
                    <div>
                        <label for="state" class="block text-sm font-semibold text-gray-700 mb-2">State</label>
                        <input type="text" id="state" name="state"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-green-100 focus:border-green-500 transition-all duration-300">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="zip_code" class="block text-sm font-semibold text-gray-700 mb-2">ZIP Code</label>
                        <input type="text" id="zip_code" name="zip_code"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-green-100 focus:border-green-500 transition-all duration-300">
                    </div>
                    <div>
                        <label for="country" class="block text-sm font-semibold text-gray-700 mb-2">Country</label>
                        <select id="country" name="country" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-green-100 focus:border-green-500 transition-all duration-300">
                            <option value="Bangladesh">Bangladesh</option>
                            <option value="India">India</option>
                            <option value="Pakistan">Pakistan</option>
                        </select>
                    </div>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" id="is_default" name="is_default" class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                    <label for="is_default" class="ml-2 block text-sm text-gray-700">Set as default address</label>
                </div>
                <div class="flex space-x-4">
                    <button type="submit" class="flex-1 bg-green-600 text-white py-3 rounded-xl hover:bg-green-700 transition-colors duration-200">
                        <i class="fas fa-save mr-2"></i>Save Address
                    </button>
                    <button type="button" onclick="closeAddressModal()" class="flex-1 bg-gray-300 text-gray-700 py-3 rounded-xl hover:bg-gray-400 transition-colors duration-200">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Toast notification function
function showToast(message, type = 'info') {
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.toast');
    existingToasts.forEach(toast => toast.remove());

    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-medium transform transition-all duration-300 translate-x-full`;
    
    // Set colors based on type
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500',
        info: 'bg-blue-500'
    };
    
    toast.classList.add(colors[type] || colors.info);
    toast.textContent = message;
    
    // Add to page
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

// Tab switching functionality
function showSection(sectionName) {
    // Hide all sections
    document.querySelectorAll('.content-section').forEach(section => {
        section.classList.add('hidden');
    });

    // Remove active class from all tabs
    document.querySelectorAll('.nav-tab').forEach(tab => {
        tab.classList.remove('active', 'bg-blue-50', 'text-blue-600');
    });

    // Show selected section
    document.getElementById(sectionName + '-section').classList.remove('hidden');

    // Add active class to selected tab
    event.target.closest('.nav-tab').classList.add('active', 'bg-blue-50', 'text-blue-600');
}

// Edit profile functionality
function editProfile() {
    document.getElementById('edit-profile-modal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('edit-profile-modal').classList.add('hidden');
}

// Address management
function addNewAddress() {
    document.getElementById('address-modal-title').textContent = 'Add Address';
    document.getElementById('address-form').reset();
    document.getElementById('address_id').value = '';
    document.getElementById('address-modal').classList.remove('hidden');
}

function editAddress(addressId) {
    console.log('🔧 Editing address with ID:', addressId);
    console.log('🔧 Address ID type:', typeof addressId);
    console.log('🔧 Making request to:', `/profile/address/${addressId}`);
    
    // Validate address ID
    if (!addressId || isNaN(addressId)) {
        console.error('🔧 Invalid address ID:', addressId);
        showToast('Invalid address ID', 'error');
        return;
    }
    
    // Show loading state
    const editButton = event.target.closest('button');
    const originalContent = editButton.innerHTML;
    editButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    editButton.disabled = true;
    
    // Fetch address data and populate form
    fetch(`/profile/address/${addressId}`)
        .then(response => {
            console.log('🔧 Response status:', response.status);
            console.log('🔧 Response URL:', response.url);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text().then(text => {
                console.log('🔧 Raw response:', text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('🔧 JSON parse error:', e);
                    throw new Error('Invalid JSON response: ' + text);
                }
            });
        })
        .then(data => {
            console.log('🔧 Address data received:', data);
            if (data.success) {
                const address = data.address;
                console.log('🔧 Address object:', address);
                document.getElementById('address-modal-title').textContent = 'Edit Address';
                document.getElementById('address_id').value = address.id;
                document.getElementById('address_type').value = address.address_type;
                document.getElementById('address_line1').value = address.address_line1;
                document.getElementById('address_line2').value = address.address_line2 || '';
                document.getElementById('city').value = address.city;
                document.getElementById('state').value = address.state || '';
                document.getElementById('zip_code').value = address.zip_code || '';
                document.getElementById('country').value = address.country;
                document.getElementById('is_default').checked = address.is_default == 1;
                document.getElementById('address-modal').classList.remove('hidden');
                showToast('Address loaded successfully', 'success');
            } else {
                console.error('🔧 API Error:', data.message);
                showToast(data.message || 'Failed to load address', 'error');
            }
        })
        .catch(error => {
            console.error('🔧 Error loading address:', error);
            showToast('Error loading address: ' + error.message, 'error');
        })
        .finally(() => {
            // Reset button state
            editButton.innerHTML = originalContent;
            editButton.disabled = false;
        });
}

function closeAddressModal() {
    document.getElementById('address-modal').classList.add('hidden');
}

function deleteAddress(addressId) {
    if (confirm('Are you sure you want to delete this address?')) {
        console.log('🗑️ Deleting address with ID:', addressId);
        
        // Show loading state
        const deleteButton = event.target.closest('button');
        const originalContent = deleteButton.innerHTML;
        deleteButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        deleteButton.disabled = true;
        
        fetch('/profile/delete-address', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'address_id=' + addressId
        })
        .then(response => {
            console.log('🗑️ Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('🗑️ Response data:', data);
            if (data.success) {
                showToast('Address deleted successfully', 'success');
                // Reload after a short delay to show the success message
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.message || 'Failed to delete address', 'error');
            }
        })
        .catch(error => {
            console.error('🗑️ Error deleting address:', error);
            showToast('Error deleting address: ' + error.message, 'error');
        })
        .finally(() => {
            // Reset button state
            deleteButton.innerHTML = originalContent;
            deleteButton.disabled = false;
        });
    }
}

// Form submissions
document.getElementById('edit-profile-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('/profile/update', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Profile updated successfully', 'success');
            closeModal();
            location.reload();
        } else {
            showToast(data.errors ? data.errors.join(', ') : 'Failed to update profile', 'error');
        }
    });
});

document.getElementById('address-form').addEventListener('submit', function(e) {
    e.preventDefault();
    console.log('💾 Submitting address form');

    const formData = new FormData(this);
    const addressId = document.getElementById('address_id').value;
    const url = addressId ? '/profile/update-address' : '/profile/add-address';
    const isEdit = !!addressId;
    
    console.log('💾 URL:', url, 'Is Edit:', isEdit);

    // Show loading state
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    submitButton.disabled = true;

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('💾 Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('💾 Response data:', data);
        if (data.success) {
            showToast(data.message, 'success');
            closeAddressModal();
            // Reload after a short delay to show the success message
            setTimeout(() => location.reload(), 1000);
        } else {
            const errorMessage = data.errors ? data.errors.join(', ') : (data.message || 'Failed to save address');
            showToast(errorMessage, 'error');
        }
    })
    .catch(error => {
        console.error('💾 Error saving address:', error);
        showToast('Error saving address: ' + error.message, 'error');
    })
    .finally(() => {
        // Reset button state
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    });
});

document.getElementById('change-password-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('/profile/update', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Password changed successfully', 'success');
            this.reset();
        } else {
            showToast(data.errors ? data.errors.join(', ') : 'Failed to change password', 'error');
        }
    });
});

// Enhanced diet profile form submission with comprehensive logging
document.getElementById('diet-profile-form').addEventListener('submit', function(e) {
    e.preventDefault();
    console.log('🥗 ===== DIET PROFILE FORM SUBMITTED =====');

    const formData = new FormData(this);
    console.log('🥗 Form data collected');

    // Log all form data
    for (let [key, value] of formData.entries()) {
        console.log(`🥗 ${key}: ${value}`);
    }

    console.log('🥗 Making request to: /profile/save-diet-profile');

    fetch('/profile/save-diet-profile', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('🥗 Response received:', {
            status: response.status,
            statusText: response.statusText,
            ok: response.ok,
            url: response.url
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status} - ${response.statusText}`);
        }
        return response.text().then(text => {
            console.log('🥗 Raw response text:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('🥗 Failed to parse JSON response:', e);
                throw new Error('Invalid JSON response: ' + text);
            }
        });
    })
    .then(data => {
        console.log('🥗 Parsed response data:', data);
        
        if (data.success) {
            console.log('✅ SUCCESS: Diet profile saved successfully!');
            showToast('Diet profile saved successfully! You\'ll now see personalized recommendations.', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            console.error('❌ FAILED:', data.errors ? data.errors.join(', ') : data.message);
            showToast(data.errors ? data.errors.join(', ') : 'Failed to save diet profile', 'error');
        }
    })
    .catch(error => {
        console.error('🥗 ===== ERROR OCCURRED =====');
        console.error('🥗 Error type:', error.constructor.name);
        console.error('🥗 Error message:', error.message);
        console.error('🥗 Error stack:', error.stack);
        
        showToast('Error: ' + error.message, 'error');
    });
});

// Subscription management functions
function pauseSubscription(id) {
    if (confirm('Are you sure you want to pause this subscription?')) {
        fetch(`/subscriptions/pause/${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Subscription paused successfully', 'success');
                location.reload();
            } else {
                showToast(data.message || 'Failed to pause subscription', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
        });
    }
}

function resumeSubscription(id) {
    fetch(`/subscriptions/resume/${id}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Subscription resumed successfully', 'success');
            location.reload();
        } else {
            showToast(data.message || 'Failed to resume subscription', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    });
}

function cancelSubscription(id) {
    if (confirm('Are you sure you want to cancel this subscription? This action cannot be undone.')) {
        fetch(`/subscriptions/cancel/${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Subscription cancelled successfully', 'success');
                location.reload();
            } else {
                showToast(data.message || 'Failed to cancel subscription', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
        });
    }
}
</script>

<?php
$content = ob_get_clean();
include 'app/views/layouts/main.php';
?>