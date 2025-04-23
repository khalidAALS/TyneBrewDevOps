<?php
include 'session_manager.php'; // manages the users session (session_manager.php)
include 'CSP.php'; // extra security with a content security policy(CSP.php)
require_once 'db.php'; // databse config and connection (db.php)

// database connection
$db = new Database();
$conn = $db->connect();

// redirects non-admin users to login page (login.php)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// gets all products from TblProducts
$query = $conn->query("SELECT * FROM TblProducts");
$products = $query->fetchAll(PDO::FETCH_ASSOC); // gets the products as an array
?>


<!DOCTYPE html>
<html>
<head>
    <title>Edit Products</title>
    <!-- page style and design (style.cscs) -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Edit Products</h1>
        <nav>
            <!-- page navigation -->
            <a href="admin.php">Admin Dashboard</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <main>
        <!-- confirmation message -->
    <?php if (isset($_SESSION['edit_message'])): ?>
    <div class="confirmation-message">
        <?= htmlspecialchars($_SESSION['edit_message'], ENT_QUOTES, 'UTF-8'); ?>
    </div>
    <?php unset($_SESSION['edit_message']); ?>
<?php endif; ?>
        <!-- products table -->
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- loops through the products and display theme -->
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($product['category'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>£<?= number_format($product['price'], 2, '.', ''); ?></td>
                        <td>
                            <!-- adds CSRF token for the edit action -->
                            <a href="edit_product.php?id=<?= htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8'); ?>&csrf_token=<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
    <!-- page footer -->
    <footer>
        &copy; 2024 Tyne Brew Coffee. All rights reserved.
    </footer>
</body>
</html>
