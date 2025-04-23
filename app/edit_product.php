<?php
include 'session_manager.php'; // manages the users session (session_manager.php)
include 'CSP.php'; // extra security with a content security policy (CSP.php)
require_once 'db.php'; // database config and connection (db.php)

// redirects non-admin users to login page (login.php)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// checks if product ID is passed and valid
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0; // validates product ID

if (!$product_id) {
    echo "Invalid Product ID."; // error message if no valid ID is passed
    exit;
}

// gets product details from the database TblProducts
$db = new Database();
$conn = $db->connect();

$stmt = $conn->prepare("SELECT * FROM TblProducts WHERE id = :id"); //sql query to get product ID
$stmt->bindParam(':id', $product_id, PDO::PARAM_INT); // binds product ID
$stmt->execute(); // executes the query
$product = $stmt->fetch(PDO::FETCH_ASSOC); // gets the product details

if (!$product) {
    echo "Product not found."; // error message if the product doesnt exist
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- page metadata and design settings -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <!-- page design and style (style.css) -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Edit Product</h1>
        <nav>
            <!-- page navigation -->
        <a href="admin.php">Admin Dashboard</a> <!-- admin dashboard -->
        <a href="logout.php">Logout</a> <!-- admin logout -->
        </nav>
    </header>
    <main>
        <!-- product editing form -->
        <h4>Edit Product: <?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h4>
        <form action="process_edit_product.php" method="POST" enctype="multipart/form-data" class="edit-product-form">
            <input type="hidden" name="id" value="<?= htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8') ?>"> <!--hidden product ID -->
            <!-- product name input -->
            <label for="name">Product Name:</label>
            <input type="text" name="name" id="name" value="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>" required>
            <!-- product description input -->
            <label for="description">Short Description:</label>
            <textarea name="description" id="description" required><?= htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
            <!-- product price input -->
            <label for="price">Price:</label>
            <input type="number" step="0.01" name="price" id="price" value="<?= htmlspecialchars($product['price'], ENT_QUOTES, 'UTF-8') ?>" required>
            <!-- product image input -->
            <label for="image">Product Image:</label>
            <input type="file" name="image" id="image">
            <!-- product category input -->
            <label for="category">Category:</label>
            <select name="category" id="category" required>
                <option value="Drink" <?= $product['category'] === 'Drink' ? 'selected' : '' ?>>Drinks</option>
                <option value="Food" <?= $product['category'] === 'Food' ? 'selected' : '' ?>>Food</option>
                <option value="Beans" <?= $product['category'] === 'Beans' ? 'selected' : '' ?>>Beans</option>
                <option value="Accessories" <?= $product['category'] === 'Accessories' ? 'selected' : '' ?>>Accessories</option>
            </select>
            <!-- product detailed discription input -->
            <label for="detailed_description">Detailed Description:</label>
            <textarea name="detailed_description" id="detailed_description"><?= htmlspecialchars($product['detailed_description'], ENT_QUOTES, 'UTF-8') ?></textarea>
            <!-- product dimensions input -->
            <label for="dimensions">Dimensions:</label>
            <input type="text" name="dimensions" id="dimensions" value="<?= htmlspecialchars($product['dimensions'], ENT_QUOTES, 'UTF-8') ?>">
            <!-- product nutriotion facts input -->
            <label for="nutrition_facts">Nutrition Facts:</label>
            <textarea name="nutrition_facts" id="nutrition_facts"><?= htmlspecialchars($product['nutrition_facts'], ENT_QUOTES, 'UTF-8') ?></textarea>

            <!--  CSRF token to the form for form submission -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
            <!-- submit 'update product' button -->
            <button type="submit">Update Product</button>
        </form>
    </main>
    <!-- page footer -->
    <footer>
        &copy; 2024 Tyne Brew Coffee. By Khalid A Alsayed (2239321). All rights reserved.
    </footer>
</body>
</html>
