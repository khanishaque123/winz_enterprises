<?php
session_start();
require_once '../includes/functions.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    $_SESSION['error'] = "Invalid product ID!";
    header('Location: index.php');
    exit;
}

// Check if product exists before deleting
$product = getProductById($id);
if (!$product) {
    $_SESSION['error'] = "Product not found!";
    header('Location: index.php');
    exit;
}

// Delete the product
if (deleteProduct($id)) {
    $_SESSION['success'] = "Product '".htmlspecialchars($product['name'])."' deleted successfully!";
} else {
    $_SESSION['error'] = "Failed to delete product. Please try again.";
}

header('Location: index.php');
exit;
?>