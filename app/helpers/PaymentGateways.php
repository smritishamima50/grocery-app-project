<?php

class PaymentGateways {
    public static function bkashCreatePayment(array $order): array {
        // TODO: Replace mock with real bKash API calls using env vars
        // env: BKASH_APP_KEY, BKASH_APP_SECRET, BKASH_USERNAME, BKASH_PASSWORD
        return [
            'success' => true,
            'gateway_reference' => 'BKASH-' . uniqid(),
            'redirectUrl' => '/mock/bkash/checkout?order_id=' . $order['id']
        ];
    }

    public static function bkashVerifyPayment(array $transaction): array {
        // TODO: Replace mock with real verification using gateway_reference
        return [
            'success' => true,
            'transaction_id' => 'BKASH-TRX-' . substr($transaction['gateway_reference'], -8),
            'amount' => $transaction['amount']
        ];
    }

    public static function nagadCreatePayment(array $order): array {
        // TODO: Replace mock with real Nagad API calls using env vars
        // env: NAGAD_MERCHANT_ID, NAGAD_MERCHANT_SECRET
        return [
            'success' => true,
            'gateway_reference' => 'NAGAD-' . uniqid(),
            'redirectUrl' => '/mock/nagad/checkout?order_id=' . $order['id']
        ];
    }

    public static function nagadVerifyPayment(array $transaction): array {
        // TODO: Replace mock with real verification using gateway_reference
        return [
            'success' => true,
            'transaction_id' => 'NAGAD-TRX-' . substr($transaction['gateway_reference'], -8),
            'amount' => $transaction['amount']
        ];
    }

    public static function cardCreatePayment(array $order): array {
        // TODO: Replace mock with Stripe/SSLCommerz session create using env vars
        // env examples: CARD_PUBLIC_KEY, CARD_SECRET_KEY
        return [
            'success' => true,
            'gateway_reference' => 'CARDSESS-' . uniqid(),
            'redirectUrl' => '/mock/card/checkout?order_id=' . $order['id']
        ];
    }

    public static function cardVerifyPayment(array $transaction): array {
        // TODO: Replace mock with real verification using gateway_reference
        return [
            'success' => true,
            'transaction_id' => 'CARD-TRX-' . substr($transaction['gateway_reference'], -8),
            'amount' => $transaction['amount']
        ];
    }
}

?>


