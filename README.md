# Grocery E-Commerce Application

A full-featured grocery e-commerce platform built with PHP, MySQL, and Tailwind CSS.

## Features

### Core Features
- **User Authentication**: Signup/Login with email/phone, profile management
- **Product Catalog**: Categories, product search & filtering, detailed product pages
- **Shopping Cart**: Add/remove items, quantity updates, real-time calculations
- **Checkout Process**: Address selection, delivery slots, multiple payment options
- **Order Management**: Order tracking, status updates, order history
- **Secure Payments**: Integration with bKash, Nagad, and cash on delivery

### Customer Features
- **Wishlist/Favorites**: Save items for later
- **Coupons & Discounts**: Promo codes and discounts
- **Subscription Orders**: Weekly/monthly grocery delivery
- **Order History & Reorder**: Easy repeat purchases
- **Delivery Tracking**: Real-time status updates
- **Push Notifications**: Order updates and promotions
- **Multi-language Support**: English & Bangla
- **Multiple Addresses**: Home, office, etc.
- **Guest Checkout**: Buy without account creation

### Admin Features
- **Dashboard**: Sales analytics, revenue tracking, user growth
- **Product Management**: Add/edit/delete products and categories
- **Order Management**: View orders, update statuses, assign delivery
- **Inventory Management**: Stock tracking and low stock alerts
- **Coupon Management**: Create and manage discount codes
- **User Management**: Manage customer accounts

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, Tailwind CSS, JavaScript
- **Architecture**: MVC (Model-View-Controller)
- **Environment**: XAMPP (Apache, MySQL, PHP)

## Installation

### Prerequisites
- XAMPP (or similar Apache/MySQL/PHP stack)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer (optional, for dependency management)

### Setup Steps

1. **Clone or Download** the project to your XAMPP htdocs directory:
   ```
   cd C:\xampp\htdocs
   git clone <repository-url> grocery-app
   ```

2. **Database Setup**:
   - Start XAMPP and ensure Apache and MySQL are running
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `grocery_app`
   - Import the `database/schema.sql` file

3. **Configuration**:
   - Update database credentials in `config/database.php` if needed
   - Default configuration uses:
     - Host: localhost
     - Database: grocery_app
     - Username: root
     - Password: (empty)

4. **Access the Application**:
   - Open your browser and go to: `http://localhost/grocery-app`
   - The application should now be running

## Project Structure

```
grocery-app/
├── app/
│   ├── controllers/     # Controller classes
│   │   ├── BaseController.php
│   │   ├── HomeController.php
│   │   ├── AuthController.php
│   │   ├── ProductController.php
│   │   ├── CartController.php
│   │   ├── CheckoutController.php
│   │   └── AdminController.php
│   ├── models/          # Model classes (for future expansion)
│   ├── views/           # View templates
│   │   ├── layouts/
│   │   │   └── main.php
│   │   ├── home/
│   │   ├── auth/
│   │   ├── products/
│   │   ├── cart/
│   │   ├── checkout/
│   │   └── admin/
│   └── helpers/         # Helper functions
├── config/
│   └── database.php     # Database configuration
├── database/
│   └── schema.sql       # Database schema
├── public/              # Public assets
│   ├── css/
│   ├── js/
│   └── images/
├── index.php            # Entry point
└── README.md
```

## Database Schema

The application uses the following main tables:
- `users` - Customer and admin accounts
- `categories` - Product categories
- `products` - Product catalog
- `cart_items` - Shopping cart items
- `orders` - Customer orders
- `order_items` - Individual order items
- `user_addresses` - Delivery addresses
- `coupons` - Discount codes
- `wishlists` - Customer wishlists
- `subscriptions` - Recurring orders
- `payments` - Payment records
- `notifications` - User notifications
- `delivery_updates` - Order tracking updates

## Usage

### For Customers
1. **Browse Products**: Use the homepage or products page to browse categories
2. **Search & Filter**: Use search bar and category filters
3. **Add to Cart**: Click "Add to Cart" on product cards
4. **Checkout**: Review cart and proceed to checkout
5. **Payment**: Choose payment method and place order
6. **Track Orders**: View order status in account dashboard

### For Admins
1. **Login**: Use admin credentials to access admin panel
2. **Dashboard**: View sales analytics and recent orders
3. **Manage Products**: Add, edit, or remove products
4. **Manage Orders**: Update order statuses and track deliveries
5. **Manage Categories**: Organize product categories

## Security Features

- Password hashing using PHP's `password_hash()`
- Session-based authentication
- Input validation and sanitization
- SQL injection prevention with prepared statements
- XSS protection with `htmlspecialchars()`
- CSRF protection (recommended for production)

## Future Enhancements

- **Payment Gateway Integration**: Stripe, PayPal, bKash API
- **Real-time Notifications**: WebSocket or Server-Sent Events
- **Advanced Search**: Elasticsearch integration
- **Image Upload**: Cloud storage (AWS S3, Cloudinary)
- **Multi-language**: i18n support
- **Mobile App**: React Native or Flutter
- **Analytics**: Google Analytics, custom dashboards
- **Email/SMS**: Order confirmations and marketing
- **Inventory Management**: Automatic stock updates
- **Reviews & Ratings**: Customer feedback system

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is open source and available under the [MIT License](LICENSE).

## Support

For support or questions, please create an issue in the repository or contact the development team.

---

**Note**: This is a basic implementation. For production use, additional security measures, testing, and optimizations are recommended.