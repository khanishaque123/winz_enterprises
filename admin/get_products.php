<?php
header("Content-Type: application/json");
require_once '../includes/functions.php';

$products = getProducts();
echo json_encode($products);
?>