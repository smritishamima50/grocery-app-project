# Admin Subscription Management Setup

## Overview
This feature allows admins to create subscriptions for users with preset amounts and frequencies. The subscriptions appear in the user's profile under "My Subscriptions".

## Features

✅ Admin can create subscriptions for any user
✅ Set subscription frequency (Weekly, Bi-Weekly, Monthly)
✅ Set subscription amount (৳200, ৳500, ৳1,000)
✅ Choose delivery time slot
✅ View all subscriptions in admin panel
✅ Subscriptions appear in user's profile

## Database Migration

### Step 1: Add Amount Column
Run the following SQL in phpMyAdmin:

```sql
ALTER TABLE subscriptions 
ADD COLUMN amount DECIMAL(10,2) DEFAULT 0 AFTER product_ids;
```

Or use the migration file:
```bash
mysql -u your_username -p your_database < database/add_amount_to_subscriptions.sql
```

## How to Use

### For Admin

1. **Login** as admin
2. Go to **Admin Dashboard**
3. Click on **"Manage Subscriptions"**
4. Click **"Create New Subscription"** button
5. Fill in the form:
   - Select a user from the dropdown
   - Choose frequency (Weekly/Bi-Weekly/Monthly)
   - Choose amount (৳200/৳500/৳1,000)
   - Select delivery time slot (optional)
6. Click **"Create Subscription"**

### For Users

1. Users will see the subscription in their profile
2. Go to **Profile** → **"My Subscriptions"** tab
3. View subscription details:
   - Frequency (Weekly/Bi-Weekly/Monthly)
   - Amount (৳200/৳500/৳1,000)
   - Next delivery date
   - Status (Active/Paused/Cancelled)
   - Delivery time slot
4. Users can Pause/Resume/Cancel their subscriptions

## File Changes Made

### 1. Database Schema (`database/schema.sql`)
- Added `amount` column to `subscriptions` table
- Column: `amount DECIMAL(10,2) DEFAULT 0`

### 2. Admin Controller (`app/controllers/AdminController.php`)
- Added `subscriptions()` method to list all subscriptions
- Added `usersForSubscription()` method to display create form
- Added `createSubscription()` method to handle subscription creation
- Added `calculateNextDeliveryDate()` helper method

### 3. Routes (`index.php`)
- `/admin/subscriptions` - View all subscriptions
- `/admin/create-subscription` - Create new subscription form
- `/admin/create-subscription/store` - Save subscription

### 4. Admin Views
- `app/views/admin/subscriptions.php` - List all subscriptions
- `app/views/admin/create-subscription.php` - Create subscription form
- Updated `app/views/admin/dashboard.php` - Added subscriptions link

## Subscription Amount Packages

| Amount | Description | Use Case |
|--------|-------------|----------|
| ৳200 | Weekly Basic | Small weekly grocery needs |
| ৳500 | Bi-Weekly Standard | Medium bi-weekly needs |
| ৳1,000 | Monthly Premium | Large monthly needs |

## Testing

1. **Test Admin Creation**:
   - Login as admin
   - Create a subscription for a test user
   - Verify it appears in subscriptions list

2. **Test User View**:
   - Login as the test user
   - Go to Profile → My Subscriptions
   - Verify subscription details are correct

3. **Test Amount Display**:
   - Check that amounts display correctly (৳200, ৳500, ৳1,000)
   - Verify frequency labels (Weekly, Bi-Weekly, Monthly)

## Troubleshooting

**Subscription not showing in user profile?**
- Check that the user is logged in
- Verify the subscription was created successfully
- Check the user_id in the subscriptions table

**Amount field not found error?**
- Run the database migration: `database/add_amount_to_subscriptions.sql`
- Verify the `amount` column exists in the subscriptions table

**Form not submitting?**
- Check browser console for JavaScript errors
- Verify the route exists in `index.php`
- Check PHP error logs

## Future Enhancements

- Add ability to edit subscriptions
- Add ability to add specific products to subscriptions
- Add subscription analytics (revenue, active subscriptions, etc.)
- Add email notifications for upcoming deliveries
- Add ability to customize delivery addresses for each subscription
