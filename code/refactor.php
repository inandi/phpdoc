<?php

/**
 * Class OrderProcessor
 * 
 * Lorem ipsum dolor sit amet consectetur adipisicing elit. Quidem, ad quasi est laudantium maiores 
 * laborum doloribus. Maxime ad consequuntur nostrum voluptate. Incidunt excepturi corporis
 * eligendi non vel deleniti error fugit.
 * 
 * Lorem ipsum dolor sit amet consectetur adipisicing elit. Autem officia quasi (@see ./DummyClass)
 * ipsa dolorum! Voluptates excepturi, ipsam ea deleniti voluptatum eius, odit corporis nihil, at 
 * delectus molestiae expedita. Nihil, cum debitis!
 * 
 * Lorem ipsum dolor sit amet consectetur adipisicing elit. Molestiae dignissimos commodi optio aspernatur 
 * repudiandae laboriosam earum! Necessitatibus voluptas ipsa delectus vel unde perferendis quia 
 * cupiditate, tempore dignissimos sunt ab placeat!
 * 
 * @category E-Commerce
 * @package Application
 * @author John Doe
 * @version 2.0.0
 * @since 1.0.0
 * @copyright 2024 Github
 * @link https://www.google.com/
 */
class OrderProcessor
{

    /**
     * Processes the order by validating input data, calculating total price, handling payment, and sending confirmation.
     * 
     * @param array $orderData The order data containing customer information, items, and payment details.
     * @return array|string Returns an array with order details on success, or an error message on failure.
     * 
     * @internal Lorem ipsum dolor sit amet consectetur adipisicing elit. Dignissimos ducimus, necessitatibus, odio error 
     * consequuntur eius nostrum, quae alias aliquam quasi ullam! Vitae debitis veniam provident, 
     * maxime voluptatem obcaecati voluptas ipsam.
     * 
     * @since 1.0.0
     * @version 2.0.0
     * @throws Exception
     */
    public function processOrder($orderData): array|string
    {
        $validationResult = $this->validateOrderData($orderData);
        if ($validationResult !== true) {
            return $validationResult; // Return validation error message
        }

        $totalPrice = $this->calculateTotalPrice($orderData['items'], $orderData['discountCode'] ?? null);
        $totalPrice = $this->applyShippingCostV2($totalPrice);

        $paymentResult = $this->processPayment($orderData, $totalPrice);
        if ($paymentResult !== true) {
            return $paymentResult; // Return payment error message
        }

        $orderId = $this->generateOrderId();
        $emailSent = $this->sendConfirmationEmail($orderData['email'], $orderId);

        if (!$emailSent) {
            return 'Failed to send confirmation email.';
        }

        return [
            'status' => 'success',
            'message' => 'Order processed successfully.',
            'orderId' => $orderId,
            'totalPrice' => $totalPrice
        ];
    }

    /**
     * Validates the order data to ensure all required fields are provided and valid.
     * 
     * @param array $orderData The order data.
     * @return bool|string Returns true if validation passes, or an error message on failure.
     * 
     * @since 1.0.0
     * @version 2.0.0
     */
    private function validateOrderData($orderData): bool|string
    {
        if (!isset($orderData['customerName']) || empty($orderData['customerName'])) {
            return 'Customer name is required.';
        }
        if (!isset($orderData['email']) || !filter_var($orderData['email'], FILTER_VALIDATE_EMAIL)) {
            return 'A valid email is required.';
        }
        if (!isset($orderData['items']) || count($orderData['items']) == 0) {
            return 'At least one item is required.';
        }
        return true;
    }

    /**
     * Calculates the total price of the items in the order, applying any discount code if provided.
     * 
     * @param array $items The items in the order.
     * @param string|null $discountCode An optional discount code.
     * @return float|int The total price after applying any discounts.
     * 
     * @since 1.0.0
     * @version 2.0.0
     */
    private function calculateTotalPrice($items, $discountCode = null): float|int
    {
        $totalPrice = 0;

        foreach ($items as $item) {
            $totalPrice += $item['quantity'] * $item['price'];
        }

        if ($discountCode) {
            $totalPrice = $this->applyDiscount($totalPrice, $discountCode);
        }

        return $totalPrice;
    }

    /**
     * Applies a discount based on the provided discount code.
     * 
     * @param float $totalPrice The total price before discount.
     * @param string $discountCode The discount code.
     * @return float The total price after applying the discount.
     * 
     * @since 1.0.0
     * @version 2.0.0
     */
    private function applyDiscount($totalPrice, $discountCode)
    {
        if ($discountCode == 'SAVE10') {
            return $totalPrice * 0.9; // 10% discount
        } elseif ($discountCode == 'SAVE20') {
            return $totalPrice * 0.8; // 20% discount
        }
        return $totalPrice;
    }

    /**
     * Adds shipping cost to the total price based on the price thresholds.
     * 
     * Lorem ipsum dolor sit amet consectetur adipisicing elit. Esse velit iusto nihil sapiente voluptate 
     * aliquid ipsa rerum, officia cum iure rem, cupiditate excepturi tenetur fuga labore enim non a ullam.
     * 
     * @param float $totalPrice The total price of the items.
     * @return float The total price after adding shipping cost.
     * 
     * @since 1.0.0
     * @version 2.0.0
     */
    private function applyShippingCostV2($totalPrice): float
    {
        if ($totalPrice < 50) {
            $totalPrice += 5.99;
        } elseif ($totalPrice < 100) {
            $totalPrice += 3.99;
        }
        return $totalPrice;
    }

    /**
     * @deprecated 2.0 Use applyShippingCostV2 instead.
     * 
     * Adds shipping cost to the total price based on the price thresholds.
     * 
     * @param float $totalPrice The total price of the items.
     * @return float The total price after adding shipping cost.
     * 
     * @since 1.1
     * @version 2.0.0
     */
    private function applyShippingCost($totalPrice)
    {
        if ($totalPrice < 50) {
            $totalPrice += 5.99;
        } elseif ($totalPrice < 100) {
            $totalPrice += 3.99;
        }
        return $totalPrice;
    }

    /**
     * Processes the payment based on the payment method and validates necessary payment details.
     * 
     * @param array $orderData The order data containing payment method and payment details.
     * @param float $totalPrice The total price to be charged.
     * @return true|string Returns true on successful payment, or an error message on failure.
     * 
     * @internal Lorem ipsum dolor sit amet consectetur adipisicing elit. Dignissimos ducimus, necessitatibus, odio error 
     * consequuntur eius nostrum, quae alias aliquam quasi ullam! Vitae debitis veniam provident, 
     * maxime voluptatem obcaecati voluptas ipsam.
     * 
     * @since 1.0.0
     * @version 2.0.0
     */
    private function processPayment($orderData, $totalPrice): bool|string
    {
        if (!isset($orderData['paymentMethod']) || !in_array($orderData['paymentMethod'], ['credit_card', 'paypal', 'bank_transfer'])) {
            return 'Invalid payment method.';
        }

        switch ($orderData['paymentMethod']) {
            case 'credit_card':
                return $this->processCreditCardPayment(orderData: $orderData);
            case 'paypal':
                return $this->processPaypalPayment(orderData: $orderData);
            case 'bank_transfer':
                return $this->processBankTransfer(orderData: $orderData);
        }

        return 'Payment processing failed.';
    }

    /**
     * Processes credit card payment.
     * 
     * @param array $orderData The order data containing credit card details.
     * @return true|string Returns true if payment is successful, or an error message on failure.
     * 
     * @since 1.0.0
     * @version 2.0.0
     */
    private function processCreditCardPayment($orderData): bool|string
    {
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
        return true;
    }

    /**
     * Processes PayPal payment.
     * 
     * @param array $orderData The order data containing PayPal details.
     * @return true|string Returns true if payment is successful, or an error message on failure.
     * 
     * @since 1.0.0
     * @version 2.0.0
     */
    private function processPaypalPayment($orderData): bool|string
    {
        if (!isset($orderData['paypalEmail']) || !filter_var($orderData['paypalEmail'], FILTER_VALIDATE_EMAIL)) {
            return 'Invalid PayPal email.';
        }

        // Simulate payment success
        return true;
    }

    /**
     * Processes bank transfer payment.
     * 
     * @param array $orderData The order data containing bank account details.
     * @return true|string Returns true if payment is successful, or an error message on failure.
     * 
     * @since 1.0.0
     * @version 2.0.0
     */
    private function processBankTransfer($orderData): bool|string
    {
        if (!isset($orderData['accountNumber']) || !is_numeric($orderData['accountNumber'])) {
            return 'Invalid bank account number.';
        }

        // Simulate payment success
        return true;
    }

    /**
     * Generates a unique order ID.
     * 
     * @return string A unique order ID.
     * 
     * @since 1.0.0
     * @version 2.0.0
     */
    private function generateOrderId(): string
    {
        return uniqid('order_');
    }

    /**
     * Sends a confirmation email to the customer.
     * 
     * Lorem ipsum dolor sit amet consectetur adipisicing elit. Veritatis culpa fuga aspernatur voluptate 
     * blanditiis perferendis ratione enim soluta, iste quis ipsa cupiditate tenetur. Corporis 
     * quibusdam adipisci minus quia nostrum praesentium.
     * 
     * @param string $email The customer's email address.
     * @param string $orderId The order ID.
     * @return bool Returns true if the email was sent successfully, false otherwise.
     * 
     * @since 1.0.0
     * @version 2.0.0
     */
    private function sendConfirmationEmail($email, $orderId): bool
    {
        return mail($email, 'Order Confirmation', 'Thank you for your order! Your order ID is ' . $orderId);
    }
}

// Example Usage
$orderProcessor = new OrderProcessor();
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
$result = $orderProcessor->processOrder($orderData);
print_r($result);
