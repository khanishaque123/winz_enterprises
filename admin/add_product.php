<?php
session_start();
require_once '../includes/functions.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$success = '';
$error = '';
$name = $price = $description = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $image = null;

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadImage($_FILES['image']);
        if (!$uploadResult['success']) {
            $error = $uploadResult['message'];
        } else {
            $image = $uploadResult['fileName'];
        }
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $error = "Error uploading image: " . $_FILES['image']['error'];
    }

    if (empty($error) && $name && $price && is_numeric($price)) {
        if (addProduct($name, $price, $description, $image)) {
            $success = "Product added successfully!";
            // Clear form
            $name = $price = $description = '';
        } else {
            $error = "Failed to add product. Please try again.";
        }
    } elseif (empty($error)) {
        $error = "Please fill in all required fields correctly!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <h2>Add New Product</h2>
            <a href="index.php" class="btn">Back to Dashboard</a>
        </div>

        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="add_product.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Product Name *</label>
                    <input type="text" id="name" name="name" placeholder="Enter product name" 
                           value="<?= htmlspecialchars($name) ?>" required>
                </div>

                <div class="form-group">
                    <label for="price">Price ($) *</label>
                    <input type="number" id="price" name="price" placeholder="0.00" 
                           step="0.01" min="0" value="<?= htmlspecialchars($price) ?>" required>
                </div>

                <div class="form-group">
                    <label for="image">Product Image</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <small>Supported formats: JPG, JPEG, PNG, GIF, WEBP (Max: 5MB)</small>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4" 
                              placeholder="Enter product description..."><?= htmlspecialchars($description) ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn">Add Product</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>

        <!-- Image Preview -->
        <div class="preview-section">
            <h3>Image Preview</h3>
            <div id="imagePreview" class="image-preview">
                <p>No image selected</p>
            </div>
        </div>
    </div>

    <script>
        // Image preview functionality
        document.getElementById('image').addEventListener('change', function(e) {
            const preview = document.getElementById('imagePreview');
            const file = e.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                }
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '<p>No image selected</p>';
            }
        });
    </script>
</body>
</html>