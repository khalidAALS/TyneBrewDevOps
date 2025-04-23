<?php
include 'session_manager.php'; // manages the users session (session_manager.php)
require_once 'db.php'; // database config and connection (db.php)
include 'CSP.php'; // enhances security with a content security policy (CSP.php)

// access control to redirect non admin users to the login page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); // redirects to login if user is not admin
    exit(); // no further code executes for unauthorized access (if user is not admin)
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <!-- links to (style.css) for page style -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- page header -->
    <header>
        <h1>Admin Dashboard</h1>
        <!-- welcome message for logged in admin -->
        <h2>Welcome, <?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?>!</h2>
        <nav>
            <!-- nav for admin related pages -->
            <a href="index.php">Home</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <main>
        <!-- page main content  -->
        <section>
            <!-- admin section menu -->
            <ul>
                <p>Select an action below:</p>
                <!-- admin add new product (add_product.php) -->
                <li><a href="add_product.php" class="btn">Add New Product</a></li>
                <!-- admin edit product list, lists all products (edit_product_list.php) -->
                <li><a href="edit_product_list.php" class="btn">Edit Products</a></li>
                <!-- admin delete product, lists all products (delete_product_list.php) -->
                <li><a href="delete_product_list.php" class="btn">Delete Products</a></li>
                <!-- admin view and manager orders, shows all orders made by users (manage_orders.php) -->
                <li><a href="manage_orders.php" class="btn">View & Manage Orders</a></li>
            </ul>
        </section>
    </main>
    <!-- page footer -->
    <footer>
        &copy; 2024 Tyne Brew Coffee. All rights reserved.
    </footer>
</body>
</html>
