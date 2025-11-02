# Fix Subscriptions Feature Setup

## Issue
The subscriptions table may not exist or may be missing required columns, causing the profile page to show a 404 error or not display subscriptions.

## Solution

### Step 1: Run Database Migration

You need to run the SQL migration to fix/create the subscriptions table.

#### Option A: Using phpMyAdmin
1. Open phpMyAdmin
2. Select your database
3. Click on "SQL" tab
4. Copy and paste the entire contents of `database/fix_subscriptions_table.sql`
5. Click "Go"

#### Option B: Using MySQL Command Line
```bash
mysql -u your_username -p your_database_name < database/fix_subscriptions_table.sql
```

### Step 2: Verify the Fix

After running the migration, the subscriptions table should have all required columns:
- `frequency` (weekly, bi_weekly, monthly)
- `payment_method` (pre_paid, cash_on_delivery)
- `delivery_address_id`
- `delivery_slot_preference`
- `product_ids` (JSON)
- `next_delivery_date`
- `last_order_date`
- `start_date`
- `end_date`
- `status`
- `created_at`
- `updated_at`

### Step 3: Test the Feature

1. **Login** to your account
2. Navigate to **Profile** (click on your name in the navigation bar)
3. Click on **"My Subscriptions"** tab in the sidebar
4. You should see:
   - A tab labeled "My Subscriptions"
   - If you have no subscriptions, a message encouraging you to create one
   - If you have subscriptions, they should display with frequency labels (Weekly, Bi-Weekly, Monthly)

### What Was Fixed

1. **ProfileController.php**: Added try-catch error handling for subscriptions query
2. **app/views/profile/index.php**: Added subscriptions section with proper UI
3. **database/schema.sql**: Updated subscriptions table definition with all required columns
4. **database/fix_subscriptions_table.sql**: Created SQL script to recreate the table

### Features Now Available

✅ View all subscriptions in the profile UI
✅ Display frequency as "Weekly", "Bi-Weekly", or "Monthly"
✅ Show payment method (Pre-Paid or Cash on Delivery)
✅ Display subscription status (Active, Paused, Cancelled)
✅ Show products in each subscription
✅ Next delivery date display
✅ Pause/Resume/Cancel subscription actions

### Troubleshooting

**If you still see 404 error:**
- Make sure you ran the SQL migration script
- Clear browser cache
- Check PHP error logs for any issues

**If subscriptions tab doesn't appear:**
- Hard refresh the page (Ctrl+F5)
- Check browser console for JavaScript errors

**If database errors occur:**
- Verify that all foreign key relationships are correct
- Check that `user_addresses` table exists
- Ensure user is logged in
