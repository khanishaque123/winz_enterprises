<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'functions.php';

$products = getProducts();

if ($products) {
    echo json_encode([
        'success' => true,
        'data' => ['products' => $products]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No products found'
    ]);
}
?>