<?php
include 'session_manager.php'; //manages the users session (session_manager.php)
include 'CSP.php'; // includes the content security policy for additional security (CSP.php)
require_once 'db.php'; // includes the database config and connection

// redirects non-admin users to login page (login.php)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

//  database connection
$db = new Database();
$conn = $db->connect();

// gets all products from TblProducts
$query = $conn->query("SELECT * FROM TblProducts"); // gets all product records
$products = $query->fetchAll(PDO::FETCH_ASSOC); // gets an associated array
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- page metadata and settings -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Products</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Delete Products</h1>
        <!-- page navigation -->
        <nav>
            <a href="admin.php">Admin Dashboard</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <main>
        <h4>All Products</h4>
        <?php if (isset($_SESSION['delete_message'])): ?>
            <div class="confirmation-message">
                <!-- sucess/wrror message -->
                <?= htmlspecialchars($_SESSION['delete_message'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <!-- clears the message after its displayed -->
            <?php unset($_SESSION['delete_message']); ?>
        <?php endif; ?>

        <?php if (!empty($products)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th> <!-- product ID -->
                        <th>Name</th> <!-- product anme -->
                        <th>Category</th> <!-- product category -->
                        <th>Price</th> <!-- product price-->
                        <th>Action</th> <!-- 'delete' action-->
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <!-- loops through product array to display products in table dynamically -->
                            <td><?= htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($product['category'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>£<?= htmlspecialchars(number_format($product['price'], 2), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <!--  POST method for CSRF protection in the delete form -->
                                <form method="POST" action="process_delete_product.php">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8') ?>">
                                    <button type="submit" name="delete" class="btn delete-btn">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No products found.</p>
        <?php endif; ?>
    </main>
    <!-- page footer -->
    <footer>
        &copy; 2024 Tyne Brew Coffee. By Khalid A Alsayed (2239321). All rights reserved.
    </footer>
</body>
</html>
