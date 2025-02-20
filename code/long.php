<?php

class OrderProcessorOld
{

    public function processOrder($orderData)
    {
        // Validate order data
        if (!isset($orderData['customerName']) || empty($orderData['customerName'])) {
            return 'Customer name is required.';
        }
        if (!isset($orderData['email']) || !filter_var($orderData['email'], FILTER_VALIDATE_EMAIL)) {
            return 'A valid email is required.';
        }
        if (!isset($orderData['items']) || count($orderData['items']) == 0) {
            return 'At least one item is required.';
        }

        // Initialize total price
        $totalPrice = 0;

        // Loop through items and calculate total price
        foreach ($orderData['items'] as $item) {
            if (!isset($item['id']) || !is_numeric($item['id'])) {
                return 'Item ID must be a number.';
            }
            if (!isset($item['quantity']) || !is_numeric($item['quantity']) || $item['quantity'] <= 0) {
                return 'Item quantity must be a positive number.';
            }
            if (!isset($item['price']) || !is_numeric($item['price']) || $item['price'] <= 0) {
                return 'Item price must be a positive number.';
            }

            $totalPrice += $item['quantity'] * $item['price'];
        }

        // Apply discount if eligible
        if (isset($orderData['discountCode'])) {
            if ($orderData['discountCode'] == 'SAVE10') {
                $totalPrice *= 0.9; // 10% discount
            } elseif ($orderData['discountCode'] == 'SAVE20') {
                $totalPrice *= 0.8; // 20% discount
            } else {
                return 'Invalid discount code.';
            }
        }

        // Calculate shipping
        $shippingCost = 0;
        if ($totalPrice < 50) {
            $shippingCost = 5.99;
        } elseif ($totalPrice < 100) {
            $shippingCost = 3.99;
        }

        $totalPrice += $shippingCost;

        // Validate payment method
        if (!isset($orderData['paymentMethod']) || !in_array($orderData['paymentMethod'], ['credit_card', 'paypal', 'bank_transfer'])) {
            return 'Invalid payment method.';
        }

        // Process payment (simulated)
        $paymentProcessed = false;
        if ($orderData['paymentMethod'] == 'credit_card') {
            // Simulate credit card processing
            if (!isset($orderData['cardNumber']) || strlen($orderData['cardNumber']) != 16) {
                return 'Invalid credit card number.';
            }
            if (!isset($orderData['cardExpiry']) || !preg_match('/^\d{2}\/\d{2}$/', $orderData['cardExpiry'])) {
                return 'Invalid card expiry date.';
            }
            if (!isset($orderData['cardCVC']) || !is_numeric($orderData['cardCVC']) || strlen($orderData['cardCVC']) != 3) {
                return 'Invalid card CVC.';
            }

            // Simulate payment success
            $paymentProcessed = true;
        } elseif ($orderData['paymentMethod'] == 'paypal') {
            // Simulate PayPal payment processing
            if (!isset($orderData['paypalEmail']) || !filter_var($orderData['paypalEmail'], FILTER_VALIDATE_EMAIL)) {
                return 'Invalid PayPal email.';
            }

            // Simulate payment success
            $paymentProcessed = true;
        } elseif ($orderData['paymentMethod'] == 'bank_transfer') {
            // Simulate bank transfer processing
            if (!isset($orderData['accountNumber']) || !is_numeric($orderData['accountNumber'])) {
                return 'Invalid bank account number.';
            }

            // Simulate payment success
            $paymentProcessed = true;
        }

        if (!$paymentProcessed) {
            return 'Payment processing failed.';
        }

        // Generate order ID (simulated)
        $orderId = uniqid('order_');

        // Send confirmation email (simulated)
        $emailSent = false;
        if (mail($orderData['email'], 'Order Confirmation', 'Thank you for your order! Your order ID is ' . $orderId)) {
            $emailSent = true;
        }

        if (!$emailSent) {
            return 'Failed to send confirmation email.';
        }

        // Return success message with order ID
        return [
            'status' => 'success',
            'message' => 'Order processed successfully.',
            'orderId' => $orderId,
            'totalPrice' => $totalPrice
        ];
    }
}

// Example Usage
$orderProcessorOld = new OrderProcessorOld();
$orderData = [
    'customerName' => 'John Doe',
    'email' => 'john.doe@example.com',
    'items' => [
        ['id' => 1, 'quantity' => 2, 'price' => 19.99],
        ['id' => 2, 'quantity' => 1, 'price' => 9.99],
    ],
    'discountCode' => 'SAVE10',
    'paymentMethod' => 'credit_card',
    'cardNumber' => '1234567812345678',
    'cardExpiry' => '12/25',
    'cardCVC' => '123',
];
$result = $orderProcessorOld->processOrder($orderData);
print_r($result);
