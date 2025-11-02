<?php
require_once 'BaseController.php';

class ProfileController extends BaseController {
    public function __construct() {
        parent::__construct();
        $this->requireLogin();
    }

    public function index() {
        $userId = $_SESSION['user_id'];

        // Get user data
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        // Get user addresses
        $stmt = $this->pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
        $stmt->execute([$userId]);
        $addresses = $stmt->fetchAll();

        // Get user diet profile
        $dietHelper = new DietHelper($this->pdo);
        $dietProfile = $dietHelper->getUserDietProfile($userId);

        // Get user subscriptions
        $subscriptions = [];
        try {
            $stmt = $this->pdo->prepare("
                SELECT s.*, ua.address_line1, ua.city, ua.state 
                FROM subscriptions s
                LEFT JOIN user_addresses ua ON s.delivery_address_id = ua.id
                WHERE s.user_id = ?
                ORDER BY s.created_at DESC
            ");
            $stmt->execute([$userId]);
            $subscriptions = $stmt->fetchAll();

            // Parse product IDs from JSON for each subscription
            foreach ($subscriptions as &$subscription) {
                $subscription['product_ids_array'] = json_decode($subscription['product_ids'], true) ?? [];
                
                // Get product details
                if (!empty($subscription['product_ids_array'])) {
                    $placeholders = implode(',', array_fill(0, count($subscription['product_ids_array']), '?'));
                    $stmt = $this->pdo->prepare("SELECT id, name, price, image, unit FROM products WHERE id IN ($placeholders)");
                    $stmt->execute($subscription['product_ids_array']);
                    $subscription['products'] = $stmt->fetchAll();
                } else {
                    $subscription['products'] = [];
                }
            }
        } catch (PDOException $e) {
            // If subscriptions table doesn't exist yet, just use empty array
            error_log("Subscriptions query error: " . $e->getMessage());
            $subscriptions = [];
        }

        $this->render('profile/index', [
            'user' => $user,
            'addresses' => $addresses,
            'dietProfile' => $dietProfile,
            'subscriptions' => $subscriptions
        ]);
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $firstName = trim($_POST['first_name'] ?? '');
            $lastName = trim($_POST['last_name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            $errors = [];

            // Validate input
            if (empty($firstName)) {
                $errors[] = 'First name is required';
            }
            if (empty($lastName)) {
                $errors[] = 'Last name is required';
            }

            // Check current password if changing password
            if (!empty($newPassword)) {
                if (empty($currentPassword)) {
                    $errors[] = 'Current password is required to change password';
                } elseif ($newPassword !== $confirmPassword) {
                    $errors[] = 'New passwords do not match';
                } elseif (strlen($newPassword) < 6) {
                    $errors[] = 'New password must be at least 6 characters long';
                } else {
                    // Verify current password
                    $stmt = $this->pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
                    $stmt->execute([$userId]);
                    $user = $stmt->fetch();

                    if (!password_verify($currentPassword, $user['password_hash'])) {
                        $errors[] = 'Current password is incorrect';
                    }
                }
            }

            if (empty($errors)) {
                // Update user profile
                $updateData = [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone' => $phone,
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                if (!empty($newPassword)) {
                    $updateData['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
                }

                $setClause = [];
                $params = [];
                foreach ($updateData as $field => $value) {
                    $setClause[] = "$field = ?";
                    $params[] = $value;
                }
                $params[] = $userId;

                $stmt = $this->pdo->prepare("UPDATE users SET " . implode(', ', $setClause) . " WHERE id = ?");
                $stmt->execute($params);

                // Update session data
                $_SESSION['first_name'] = $firstName;
                $_SESSION['last_name'] = $lastName;

                echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
            } else {
                echo json_encode(['success' => false, 'errors' => $errors]);
            }
        }
    }

    public function addAddress() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Set JSON header
            header('Content-Type: application/json');
            
            $userId = $_SESSION['user_id'];
            $addressType = $_POST['address_type'] ?? 'home';
            $addressLine1 = trim($_POST['address_line1'] ?? '');
            $addressLine2 = trim($_POST['address_line2'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $state = trim($_POST['state'] ?? '');
            $zipCode = trim($_POST['zip_code'] ?? '');
            $country = $_POST['country'] ?? 'Bangladesh';
            $isDefault = isset($_POST['is_default']) ? 1 : 0;

            $errors = [];

            if (empty($addressLine1)) {
                $errors[] = 'Address line 1 is required';
            }
            if (empty($city)) {
                $errors[] = 'City is required';
            }

            if (empty($errors)) {
                // If this is the default address, unset other defaults
                if ($isDefault) {
                    $stmt = $this->pdo->prepare("UPDATE user_addresses SET is_default = FALSE WHERE user_id = ?");
                    $stmt->execute([$userId]);
                }

                $stmt = $this->pdo->prepare("INSERT INTO user_addresses (user_id, address_type, address_line1, address_line2, city, state, zip_code, country, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$userId, $addressType, $addressLine1, $addressLine2, $city, $state, $zipCode, $country, $isDefault]);

                echo json_encode(['success' => true, 'message' => 'Address added successfully']);
            } else {
                echo json_encode(['success' => false, 'errors' => $errors]);
            }
        }
    }

    public function updateAddress() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Set JSON header
            header('Content-Type: application/json');
            
            $userId = $_SESSION['user_id'];
            $addressId = $_POST['address_id'] ?? 0;
            $addressType = $_POST['address_type'] ?? 'home';
            $addressLine1 = trim($_POST['address_line1'] ?? '');
            $addressLine2 = trim($_POST['address_line2'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $state = trim($_POST['state'] ?? '');
            $zipCode = trim($_POST['zip_code'] ?? '');
            $country = $_POST['country'] ?? 'Bangladesh';
            $isDefault = isset($_POST['is_default']) ? 1 : 0;

            $errors = [];

            if (empty($addressLine1)) {
                $errors[] = 'Address line 1 is required';
            }
            if (empty($city)) {
                $errors[] = 'City is required';
            }

            // Verify address belongs to user
            $stmt = $this->pdo->prepare("SELECT id FROM user_addresses WHERE id = ? AND user_id = ?");
            $stmt->execute([$addressId, $userId]);
            if (!$stmt->fetch()) {
                $errors[] = 'Address not found';
            }

            if (empty($errors)) {
                // If this is the default address, unset other defaults
                if ($isDefault) {
                    $stmt = $this->pdo->prepare("UPDATE user_addresses SET is_default = FALSE WHERE user_id = ? AND id != ?");
                    $stmt->execute([$userId, $addressId]);
                }

                $stmt = $this->pdo->prepare("UPDATE user_addresses SET address_type = ?, address_line1 = ?, address_line2 = ?, city = ?, state = ?, zip_code = ?, country = ?, is_default = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$addressType, $addressLine1, $addressLine2, $city, $state, $zipCode, $country, $isDefault, $addressId, $userId]);

                echo json_encode(['success' => true, 'message' => 'Address updated successfully']);
            } else {
                echo json_encode(['success' => false, 'errors' => $errors]);
            }
        }
    }

    public function deleteAddress() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Set JSON header
            header('Content-Type: application/json');
            
            $userId = $_SESSION['user_id'];
            $addressId = $_POST['address_id'] ?? 0;

            // Check if address is being used in any orders
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ? AND delivery_address_id = ?");
            $stmt->execute([$userId, $addressId]);
            $result = $stmt->fetch();

            if ($result['count'] > 0) {
                echo json_encode(['success' => false, 'message' => 'Cannot delete address that is used in existing orders']);
                return;
            }

            $stmt = $this->pdo->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
            $stmt->execute([$addressId, $userId]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Address deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Address not found']);
            }
        }
    }

    public function getAddress($addressId = null) {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // Set JSON header
            header('Content-Type: application/json');
            
            $userId = $_SESSION['user_id'];
            
            // Get address ID from parameter or from GET request
            if ($addressId === null) {
                $addressId = $_GET['id'] ?? 0;
            }

            // Validate address ID
            if (empty($addressId) || !is_numeric($addressId)) {
                echo json_encode(['success' => false, 'message' => 'Invalid address ID']);
                return;
            }

            $stmt = $this->pdo->prepare("SELECT * FROM user_addresses WHERE id = ? AND user_id = ?");
            $stmt->execute([$addressId, $userId]);
            $address = $stmt->fetch();

            if ($address) {
                echo json_encode(['success' => true, 'address' => $address]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Address not found']);
            }
        }
    }

    public function saveDietProfile() {
        // Set JSON header for all responses (only if headers not already sent)
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        
        // Include DietHelper
        require_once __DIR__ . '/../helpers/DietHelper.php';
        
        // Log the request for debugging
        error_log("Diet Profile Save Request: " . print_r($_POST, true));
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Check if user is logged in
            if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Please login to save diet profile', 'login_required' => true]);
                return;
            }
            
            $userId = $_SESSION['user_id'];
            $dietGoal = $_POST['diet_goal'] ?? 'general';
            $calorieTarget = intval($_POST['calorie_target'] ?? 2000);
            $currentWeight = floatval($_POST['current_weight'] ?? 0);
            $targetWeight = floatval($_POST['target_weight'] ?? 0);
            $height = floatval($_POST['height'] ?? 0);
            $age = intval($_POST['age'] ?? 0);
            $activityLevel = $_POST['activity_level'] ?? 'moderately_active';

            // Log all received data
            error_log("Diet Profile Data - User: $userId, Goal: $dietGoal, Calories: $calorieTarget, Weight: $currentWeight, Target: $targetWeight, Height: $height, Age: $age, Activity: $activityLevel");

            $errors = [];

            // Validate diet goal
            $validGoals = ['general', 'weight_loss', 'weight_gain', 'muscle_gain', 'diabetes_friendly', 'low_sodium', 'vegetarian', 'vegan', 'keto', 'paleo', 'mediterranean', 'heart_healthy', 'low_carb', 'high_protein'];
            if (!in_array($dietGoal, $validGoals)) {
                $errors[] = 'Invalid diet goal';
            }

            // Validate calorie target
            if ($calorieTarget < 800 || $calorieTarget > 5000) {
                $errors[] = 'Calorie target must be between 800 and 5000';
            }

            // Validate weight (optional but if provided, must be reasonable)
            if ($currentWeight > 0 && ($currentWeight < 30 || $currentWeight > 300)) {
                $errors[] = 'Current weight must be between 30 and 300 kg';
            }

            if ($targetWeight > 0 && ($targetWeight < 30 || $targetWeight > 300)) {
                $errors[] = 'Target weight must be between 30 and 300 kg';
            }

            // Validate height (optional but if provided, must be reasonable)
            if ($height > 0 && ($height < 100 || $height > 250)) {
                $errors[] = 'Height must be between 100 and 250 cm';
            }

            // Validate age (optional but if provided, must be reasonable)
            if ($age > 0 && ($age < 10 || $age > 120)) {
                $errors[] = 'Age must be between 10 and 120 years';
            }

            // Validate activity level
            $validActivityLevels = ['sedentary', 'lightly_active', 'moderately_active', 'very_active', 'extremely_active'];
            if (!in_array($activityLevel, $validActivityLevels)) {
                $errors[] = 'Invalid activity level';
            }

            if (empty($errors)) {
                try {
                    $dietHelper = new DietHelper($this->pdo);
                    $result = $dietHelper->saveDietProfile($userId, $dietGoal, $calorieTarget, $currentWeight, $targetWeight, $height, $age, $activityLevel);
                    
                    error_log("Diet profile saved successfully with ID: " . $result);
                    echo json_encode(['success' => true, 'message' => 'Diet profile updated successfully']);
                } catch (Exception $e) {
                    error_log("Diet profile save error: " . $e->getMessage());
                    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
                }
            } else {
                error_log("Diet profile validation errors: " . implode(', ', $errors));
                echo json_encode(['success' => false, 'errors' => $errors]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        }
    }
}
?>