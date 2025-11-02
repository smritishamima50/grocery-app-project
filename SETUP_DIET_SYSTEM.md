# Diet Profile System Setup Guide

## Quick Setup Instructions

### Step 1: Run Database Migration
Run the migration SQL file to add the necessary tables and fields:

```bash
mysql -u root -p grocery_app < database/migrate_diet_profile_simple.sql
```

Or if using phpMyAdmin:
1. Open phpMyAdmin
2. Select your `grocery_app` database
3. Go to the SQL tab
4. Copy and paste the contents of `database/migrate_diet_profile_simple.sql`
5. Click "Go" to execute

**Note:** If you get an error about columns already existing, that's okay - it means they were already added. You can proceed to the next step.

### Step 2: Verify Setup
1. Check that the `user_diet_profiles` table was created
2. Verify that the `products` table has the new nutrition fields
3. Check that the "Frozen Food" category was added

### Step 3: Test the Feature
1. Login to your account
2. Navigate to Profile
3. Click on "Diet Profile" tab
4. Select a diet goal (e.g., Weight Loss)
5. Set calorie target (e.g., 1500)
6. Save the profile
7. Go to Home page and see personalized recommendations

## How It Works

### User Flow
1. **Set Up Profile**: User goes to Profile → Diet Profile
2. **Choose Goal**: Select from 6 diet goals
3. **Set Target**: Enter daily calorie target (800-5000 kcal)
4. **Save**: Profile is saved to database
5. **Recommendations**: Home page shows personalized product recommendations

### Diet Goals Available
- **Weight Loss**: Low calorie, high fiber products
- **Muscle Gain**: High protein, high calorie products
- **Diabetes Friendly**: Low carb, high fiber products
- **Low Sodium**: Products with low sodium content
- **Vegetarian**: Only vegetarian products
- **General**: All products

## Troubleshooting

### "No products recommended"
- Make sure you've set up a diet profile
- Check that products have nutrition data (run migration if needed)
- Some diet goals may have strict filters, try a different goal

### "Page not found" error
- Make sure all files are in the correct directories
- Clear browser cache
- Check that the route is added to `index.php`

### Database errors
- Verify database connection in `config/database.php`
- Make sure migration file was run successfully
- Check that all tables and fields exist

## Files Modified/Created

### Controllers
- `app/controllers/ProfileController.php` - Added diet profile management
- `app/controllers/HomeController.php` - Added recommendation logic

### Views
- `app/views/profile/index.php` - Added diet profile UI
- `app/views/home/index.php` - Added recommendations display

### Helpers
- `app/helpers/DietHelper.php` - Recommendation logic

### Database
- `database/migrate_diet_profile.sql` - Migration file
- `database/schema.sql` - Original schema
- `database/dummy_data.sql` - Sample data

### Routes
- `index.php` - Added diet profile route

## API Endpoints

### Save Diet Profile
```
POST /profile/save-diet-profile
Parameters:
- diet_goal (required): weight_loss, muscle_gain, etc.
- calorie_target (required): 800-5000
```

### Get Recommendations
Automatically shown on home page when user has active diet profile.
