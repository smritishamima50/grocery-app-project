# Subscription / Recurring Orders System

## Overview
The Subscription System allows users to set up automatic recurring deliveries for their grocery orders. Users can schedule weekly, bi-weekly, or monthly deliveries and choose between pre-paid or cash on delivery payment methods.

## Features

### 1. Subscription Management
- Create subscriptions from cart items
- Choose delivery frequency (weekly, bi-weekly, monthly)
- Select payment method (pre-paid or cash on delivery)
- Set delivery address and preferred time slot
- View all active subscriptions
- Pause, resume, or cancel subscriptions

### 2. Delivery Frequency Options
- **Weekly**: Every 7 days
- **Bi-Weekly**: Every 14 days
- **Monthly**: Every 30 days

### 3. Payment Methods
- **Cash on Delivery**: Pay when you receive the order
- **Pre-Paid**: Pay in advance before delivery

## Database Schema

The subscriptions table includes:
- `id`: Unique subscription ID
- `user_id`: Reference to the user
- `frequency`: Delivery frequency (weekly, bi_weekly, monthly)
- `payment_method`: Payment type (pre_paid, cash_on_delivery)
- `delivery_address_id`: Reference to delivery address
- `delivery_slot_preference`: Preferred delivery time
- `product_ids`: JSON array of product IDs
- `next_delivery_date`: Date of next delivery
- `start_date`: When subscription started
- `status`: active, paused, or cancelled
- `created_at` and `updated_at`: Timestamps

## Setup Instructions

### 1. Run the Database Migration
```bash
# Execute the migration file
mysql -u your_username -p your_database < database/migrate_subscription_enhancements.sql
```

### 2. Access the Feature
- Navigate to Cart: Add items to your cart
- Click "Subscribe for Regular Delivery"
- OR go to User Menu > Subscriptions

## Usage Guide

### Creating a Subscription

1. **Add items to cart**: Browse products and add them to your cart
2. **Click "Subscribe for Regular Delivery"**: On the cart page
3. **Choose frequency**: Select weekly, bi-weekly, or monthly
4. **Select payment method**: Choose pre-paid or cash on delivery
5. **Select delivery address**: Choose from your saved addresses
6. **Select delivery time**: Choose preferred delivery slot
7. **Create subscription**: Click "Create Subscription"

### Managing Subscriptions

**View Subscriptions**:
- Click on "Subscriptions" in the user menu
- See all active, paused, and cancelled subscriptions

**Pause a Subscription**:
- Click "Pause" on an active subscription
- Delivery will stop until you resume

**Resume a Paused Subscription**:
- Click "Resume" on a paused subscription
- Next delivery date will be recalculated

**Cancel a Subscription**:
- Click "Cancel" on any subscription
- This action cannot be undone

## API Endpoints

### Subscriptions
- `GET /subscriptions` - View all subscriptions
- `GET /subscriptions/create` - Create new subscription page
- `POST /subscriptions/store` - Create a new subscription
- `POST /subscriptions/pause/{id}` - Pause a subscription
- `POST /subscriptions/resume/{id}` - Resume a subscription
- `POST /subscriptions/cancel/{id}` - Cancel a subscription

## File Structure

```
app/
├── controllers/
│   └── SubscriptionsController.php    # Subscription management logic
├── views/
│   └── subscriptions/
│       ├── index.php                   # List all subscriptions
│       └── create.php                  # Create new subscription
database/
└── migrate_subscription_enhancements.sql  # Database migration
```

## Key Features

### Automatic Next Delivery Calculation
The system automatically calculates the next delivery date based on the subscription frequency:
- Weekly: +7 days
- Bi-Weekly: +14 days
- Monthly: +30 days

### Status Management
Subscriptions can have three statuses:
- **Active**: Currently scheduled and delivering
- **Paused**: Temporarily stopped
- **Cancelled**: Permanently stopped

### Product Storage
Products are stored as a JSON array in the `product_ids` column, allowing multiple products per subscription.

## Future Enhancements

Potential improvements:
1. Automated order creation on delivery dates
2. Email/SMS notifications before delivery
3. Skip a delivery feature
4. Modify subscription products/quantities
5. Multiple subscriptions per user
6. Subscription history and delivery logs
7. Discounts for long-term subscriptions

## Notes

- Users must have items in their cart to create a subscription
- Users must have at least one saved address to create a subscription
- The subscription uses products from the cart at the time of creation
- Delivery dates are calculated from the creation date

## Support

For issues or questions about the subscription system, please contact support or refer to the admin panel for subscription management.
