<?php
/**
 * Payment Account Configuration
 * This file contains the payment account details for different payment methods
 * Admin can update these details from the admin panel
 */

return [
    'bkash' => [
        'enabled' => true,
        'account_number' => '01711-000000',
        'account_name' => 'Grocery Store Ltd',
        'instructions' => [
            'Send money to the bKash number above',
            'Use your order ID as reference',
            'Keep the payment receipt for verification',
            'You will receive confirmation within 5 minutes'
        ],
        'support_phone' => '01711-000000',
        'support_email' => 'support@grocerystore.com'
    ],
    
    'nagad' => [
        'enabled' => true,
        'account_number' => '01711-000001',
        'account_name' => 'Grocery Store Ltd',
        'instructions' => [
            'Send money to the Nagad number above',
            'Use your order ID as reference',
            'Keep the payment receipt for verification',
            'You will receive confirmation within 5 minutes'
        ],
        'support_phone' => '01711-000001',
        'support_email' => 'support@grocerystore.com'
    ],
    
    'card' => [
        'enabled' => true,
        'gateway_name' => 'SSLCommerz',
        'instructions' => [
            'You will be redirected to a secure payment page',
            'Enter your card details (Visa, Mastercard, etc.)',
            'Complete the payment process',
            'Return to our site for confirmation'
        ],
        'supported_cards' => ['Visa', 'Mastercard', 'American Express'],
        'support_phone' => '01711-000002',
        'support_email' => 'support@grocerystore.com'
    ],
    
    'cod' => [
        'enabled' => true,
        'instructions' => [
            'No advance payment required',
            'Pay when you receive your order',
            'Cash payment only',
            'Exact change preferred'
        ],
        'delivery_fee' => 50.00,
        'minimum_order' => 0
    ]
];
?>
