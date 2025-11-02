# Smart Diet-Based Recommendation System Documentation

## Overview
The grocery app now includes a comprehensive diet-based recommendation system that provides personalized product suggestions based on user's dietary goals and preferences.

## Features

### 1. Diet Profile Management
Users can create and manage their diet profiles with the following options:
- **Diet Goals:**
  - Weight Loss
  - Muscle Gain
  - Diabetes Friendly
  - Low Sodium
  - Vegetarian
  - General

- **Calorie Target:** Users can set daily calorie targets (800-5000 kcal)

### 2. Product Recommendations
The system provides personalized product recommendations based on:
- User's diet goal
- Calorie targets
- Nutritional information

### 3. Frozen Food Category
Added a new "Frozen Food" category with products including:
- Frozen Chicken Nuggets
- Frozen Mixed Vegetables
- Frozen French Fries
- Frozen Fish Fillet

## Database Changes

### New Tables
1. **user_diet_profiles** - Stores user diet preferences
   - diet_goal (ENUM)
   - calorie_target (INT)
   - dietary_preferences (JSON)
   - active (BOOLEAN)

### Updated Tables
1. **products** - Added nutrition fields:
   - calories_per_unit
   - protein_per_unit
   - carbs_per_unit
   - fat_per_unit
   - fiber_per_unit
   - sodium_per_unit
   - is_vegetarian
   - is_diabetes_friendly
   - is_weight_loss_friendly
   - is_muscle_gain_friendly

2. **categories** - Added "Frozen Food" category

## Files Created/Modified

### New Files
- `app/helpers/DietHelper.php` - Diet recommendation logic

### Modified Files
- `database/schema.sql` - Updated database schema
- `database/dummy_data.sql` - Added frozen food products and nutrition data
- `app/controllers/ProfileController.php` - Added diet profile management
- `app/controllers/HomeController.php` - Integrated recommendations
- `app/views/profile/index.php` - Added diet profile UI
- `app/views/home/index.php` - Display recommendations
- `index.php` - Added diet profile route

## How It Works

### 1. User Sets Diet Profile
- Navigate to Profile → Diet Profile
- Select diet goal
- Set calorie target
- Save profile

### 2. System Generates Recommendations
The `DietHelper` class filters products based on:
- Diet goal criteria (e.g., weight loss = low calorie, high fiber)
- Nutritional thresholds
- Product tags (diabetes-friendly, vegetarian, etc.)

### 3. Recommendations Displayed
- Home page shows "Recommended for You" section for logged-in users with diet profiles
- Products are sorted by relevance to the diet goal
- Non-logged-in users see "Featured Products"

## Product Filtering Logic

### Weight Loss
- Filter: is_weight_loss_friendly = TRUE AND calories_per_unit <= 150
- Sort by: calories (asc), fiber (desc)

### Muscle Gain
- Filter: is_muscle_gain_friendly = TRUE AND protein_per_unit >= 5
- Sort by: protein (desc), calories (desc)

### Diabetes Friendly
- Filter: is_diabetes_friendly = TRUE AND carbs_per_unit <= 20
- Sort by: carbs (asc), fiber (desc)

### Low Sodium
- Filter: sodium_per_unit <= 100
- Sort by: sodium (asc)

### Vegetarian
- Filter: is_vegetarian = TRUE
- Sort by: default sorting

## API Endpoints

### Get Diet Profile
```
GET /profile
Returns current diet profile information
```

### Save Diet Profile
```
POST /profile/save-diet-profile
Parameters:
- diet_goal (required)
- calorie_target (required)
```

## Usage Example

```php
// Get user's diet profile
$dietHelper = new DietHelper($pdo);
$profile = $dietHelper->getUserDietProfile($userId);

// Get recommended products
$products = $dietHelper->getRecommendedProducts($userId, 12);

// Check if product is suitable
$isSuitable = $dietHelper->isProductSuitable($product, $userId);
```

## Future Enhancements

1. Track user's daily calorie intake
2. Meal planning suggestions
3. Recipe recommendations based on diet
4. Allergen filtering
5. Nutritional goal tracking
6. Progress charts and reports

## Testing

To test the system:
1. Create a user account
2. Navigate to Profile → Diet Profile
3. Select a diet goal (e.g., Weight Loss)
4. Set calorie target (e.g., 1500)
5. Go to Home page
6. View "Recommended for You" section with filtered products

## Technical Notes

- The system uses PDO for database queries with prepared statements
- JSON is used for storing additional dietary preferences
- Only one active diet profile per user (previous profiles are deactivated)
- Recommendations update immediately after profile changes
