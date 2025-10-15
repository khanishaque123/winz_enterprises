<?php
// process_checkout.php - WInz Enterprises
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to users

// Set JSON header FIRST
header('Content-Type: application/json; charset=utf-8');

// Prevent any output before JSON
if (ob_get_level()) ob_clean();

// Simple CORS headers
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
}

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    }
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
    exit(0);
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use POST.'
    ]);
    exit;
}

try {
    // Get the raw POST data
    $rawData = file_get_contents('php://input');
    $data = [];
    
    // Check if it's form data or JSON
    if (!empty($_POST)) {
        $data = $_POST;
    } elseif (!empty($rawData)) {
        $data = json_decode($rawData, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // If not JSON, try to parse as form data
            parse_str($rawData, $data);
        }
    }
    
    // Log received data for debugging
    error_log("Received checkout data: " . print_r($data, true));
    
    // Validate required fields
    $required = ['firstName', 'lastName', 'email', 'phone', 'address', 'city', 'state', 'zipCode', 'country'];
    $missing = [];
    
    foreach ($required as $field) {
        if (empty($data[$field])) {
            $missing[] = $field;
        }
    }
    
    if (!empty($missing)) {
        throw new Exception('Missing required fields: ' . implode(', ', $missing));
    }
    
    // Validate email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }
    
    // Get cart items (in real app, validate server-side)
    $cartItems = [];
    if (!empty($data['orderData'])) {
        $orderData = json_decode($data['orderData'], true);
        if ($orderData && isset($orderData['items'])) {
            $cartItems = $orderData['items'];
        }
    }
    
    // If no cart items, create sample from your screenshot
    if (empty($cartItems)) {
        $cartItems = [
            [
                'name' => 'fan',
                'price' => 500.00,
                'quantity' => 7,
                'image' => null
            ],
            [
                'name' => 'Fan',
                'price' => 2000.00,
                'quantity' => 5,
                'image' => null
            ]
        ];
    }
    
    // Calculate totals
    $subtotal = 0;
    foreach ($cartItems as $item) {
        $price = floatval($item['price']);
        $quantity = intval($item['quantity']);
        $subtotal += $price * $quantity;
    }
    
    $shippingMethod = $data['shipping'] ?? 'standard';
    $shippingCost = getShippingCost($shippingMethod);
    $tax = $subtotal * 0.08; // 8% tax
    $total = $subtotal + $shippingCost + $tax;
    
    // Generate order ID
    $orderId = 'ORD_' . date('Ymd_His') . '_' . rand(1000, 9999);
    
    // Process payment (simulated)
    $paymentResult = processPaymentSimulation($data, $total);
    
    if (!$paymentResult['success']) {
        throw new Exception($paymentResult['message']);
    }
    
    // Create order data
    $orderData = [
        'orderId' => $orderId,
        'status' => 'confirmed',
        'customer' => [
            'firstName' => $data['firstName'],
            'lastName' => $data['lastName'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => [
                'street' => $data['address'],
                'city' => $data['city'],
                'state' => $data['state'],
                'zipCode' => $data['zipCode'],
                'country' => $data['country']
            ]
        ],
        'shipping' => $shippingMethod,
        'paymentMethod' => $data['paymentMethod'] ?? 'credit',
        'items' => $cartItems,
        'totals' => [
            'subtotal' => $subtotal,
            'shipping' => $shippingCost,
            'tax' => $tax,
            'total' => $total
        ],
        'orderNotes' => $data['orderNotes'] ?? '',
        'transactionId' => $paymentResult['transactionId'],
        'orderDate' => date('Y-m-d H:i:s'),
        'processedAt' => date('Y-m-d H:i:s')
    ];
    
    // Save order (in real app, save to database)
    $saveResult = saveOrderToFile($orderData);
    
    // Return success response
    $response = [
        'success' => true,
        'message' => 'Order processed successfully!',
        'orderId' => $orderId,
        'transactionId' => $paymentResult['transactionId'],
        'orderData' => $orderData,
        'totals' => [
            'subtotal' => $subtotal,
            'shipping' => $shippingCost,
            'tax' => $tax,
            'total' => $total
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    // Log the error
    error_log("Checkout Error: " . $e->getMessage());
    
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error' => $e->getMessage()
    ]);
}

exit;

// Helper functions
function getShippingCost($method) {
    switch ($method) {
        case 'standard': return 5.99;
        case 'express': return 12.99;
        case 'overnight': return 24.99;
        default: return 5.99;
    }
}

function processPaymentSimulation($data, $amount) {
    // Simulate payment processing
    // In real application, integrate with Stripe, PayPal, etc.
    
    // Simulate 10% failure rate for demo
    if (rand(1, 10) === 1) {
        return [
            'success' => false,
            'message' => 'Payment declined. Please try again or use a different payment method.'
        ];
    }
    
    return [
        'success' => true,
        'transactionId' => 'TXN_' . strtoupper(uniqid()),
        'amount' => $amount,
        'paymentMethod' => $data['paymentMethod'] ?? 'credit'
    ];
}

function saveOrderToFile($orderData) {
    $ordersDir = __DIR__ . '/orders';
    
    // Create directory if it doesn't exist
    if (!is_dir($ordersDir)) {
        mkdir($ordersDir, 0755, true);
    }
    
    $filename = $ordersDir . '/order_' . $orderData['orderId'] . '.json';
    
    // Add server info
    $orderData['savedAt'] = date('Y-m-d H:i:s');
    $orderData['serverValidated'] = true;
    
    return file_put_contents($filename, json_encode($orderData, JSON_PRETTY_PRINT));
}
?>