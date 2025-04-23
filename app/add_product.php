<?php
// included (session_manager.php, CSP.php and db.php) for user session management, security policy and database config and connection
include 'session_manager.php';
include 'CSP.php';
require_once 'db.php';

// Rredirects non admin users to the login page for role base access control.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- html metadata layout setup -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Admin Dashboard</title>
    <!-- links (style.css) for page style -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- page header section -->
    <header>
        <h1>Admin Dashboard</h1>
        <!-- welcome message for the admin using the session username -->
        <h2>Hello, Welcome Back, <?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?>!</h2>
        <nav>
            <!-- nav for admin specific pages -->
            <a href="admin.php">Admin Dashboard</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <!-- page main content-->
    <main>
        <!-- page title -->
        <h3>Tyne Brew Coffee - Add a New Product</h3>

        <!-- confirmation message for successful product addition -->
        <?php if (isset($_SESSION['product_message'])): ?>
            <div class="confirmation-message">
                <?= htmlspecialchars($_SESSION['product_message'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <!-- clears the message after its displayed -->
            <?php unset($_SESSION['product_message']); ?>
        <?php endif; ?>

        <!-- form to add product -->
        <form action="process_add_product.php" method="POST" enctype="multipart/form-data" class="product-form">
            <!-- product name input -->
            <label for="name">Product Name:</label>
            <input type="text" name="name" id="name" required>
            <!-- short discription input -->
            <label for="description">Short Description:</label>
            <textarea name="description" id="description" required></textarea>
            <!-- product price input -->
            <label for="price">Price:</label>
            <input type="number" step="0.01" name="price" id="price" required>
            <!-- product image input -->
            <label for="image">Product Image:</label>
            <input type="file" name="image" id="image" accept="img/*" required>
            <!-- product category input -->
            <label for="category">Category:</label>
            <select name="category" id="category" required>
                <option value="">--Select a Category--</option>
                <option value="Drinks">Drinks</option>
                <option value="Food">Food</option>
                <option value="Beans">Beans</option>
                <option value="Accessories">Accessories</option>
            </select>
            <!-- detailed discription input -->
            <label for="detailed_description">Detailed Description:</label>
            <textarea name="detailed_description" id="detailed_description"></textarea>
            <!-- product dimensions input -->
            <label for="dimensions">Dimensions:</label>
            <input type="text" name="dimensions" id="dimensions" placeholder="e.g., 20cm x 15cm x 10cm">
            <!-- nutrition facts input -->
            <label for="nutrition_facts">Nutrition Facts:</label>
            <textarea name="nutrition_facts" id="nutrition_facts" placeholder="e.g., Calories: 150, Fat: 6g, etc."></textarea>

            <!-- hidden field for CSRF token to protect against CSRF attacks -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <!-- submit "add product" button -->
            <button type="submit" class="btn-submit">Add Product</button>
        </form>
    </main>

    <!-- page footer -->
    <footer>
        &copy; 2024 Tyne Brew Coffee. By Khalid A Alsayed (2239321). All rights reserved.
    </footer>
</body>
</html>
