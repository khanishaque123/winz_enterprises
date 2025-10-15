<?php
session_start();
require_once '../includes/functions.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? null;
$product = null;

if (!$id || !is_numeric($id)) {
    header('Location: index.php');
    exit;
}

$product = getProductById($id);

if (!$product) {
    header('Location: index.php');
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $currentImage = $product['image'];
    $newImage = $currentImage;

    // Handle new image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadImage($_FILES['image']);
        if (!$uploadResult['success']) {
            $error = $uploadResult['message'];
        } else {
            // Delete old image if exists
            if ($currentImage) {
                $oldImagePath = "../uploads/" . $currentImage;
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            $newImage = $uploadResult['fileName'];
        }
    }

    // Handle image removal
    if (isset($_POST['remove_image']) && $_POST['remove_image'] === '1') {
        if ($currentImage) {
            $imagePath = "../uploads/" . $currentImage;
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        $newImage = null;
    }

    if (empty($error) && $name && $price && is_numeric($price)) {
        if (updateProduct($id, $name, $price, $description, $newImage)) {
            $_SESSION['success'] = "Product updated successfully!";
            header('Location: index.php');
            exit;
        } else {
            $error = "Failed to update product. Please try again.";
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
    <title>Edit Product</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <h2>Edit Product</h2>
            <a href="index.php" class="btn">Back to Dashboard</a>
        </div>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="edit_product.php?id=<?= $id ?>" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Product Name *</label>
                    <input type="text" id="name" name="name" 
                           value="<?= htmlspecialchars($product['name']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="price">Price ($) *</label>
                    <input type="number" id="price" name="price" 
                           value="<?= htmlspecialchars($product['price']) ?>" 
                           step="0.01" min="0" required>
                </div>

                <div class="form-group">
                    <label for="image">Product Image</label>
                    
                    <?php if ($product['image']): ?>
                        <div class="current-image">
                            <p>Current Image:</p>
                            <img src="../uploads/<?= htmlspecialchars($product['image']) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>" style="max-width: 200px;">
                            <label class="remove-image">
                                <input type="checkbox" name="remove_image" value="1"> Remove image
                            </label>
                        </div>
                    <?php endif; ?>
                    
                    <input type="file" id="image" name="image" accept="image/*">
                    <small>Supported formats: JPG, JPEG, PNG, GIF, WEBP (Max: 5MB)</small>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4"><?= htmlspecialchars($product['description']) ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn">Update Product</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>

        <!-- New Image Preview -->
        <div class="preview-section">
            <h3>New Image Preview</h3>
            <div id="imagePreview" class="image-preview">
                <p>No new image selected</p>
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
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 300px;">`;
                }
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '<p>No new image selected</p>';
            }
        });
    </script>
</body>
</html>