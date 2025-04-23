<?php
include 'session_manager.php'; // handles the users session (session_manager.php)
include 'CSP.php'; // extra security with content security policy (CSP.php)
require_once 'db.php'; // config and connect to database (db.php)

// checks if the user is logged in and the ID is provided
if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    header("Location: login.php"); // redirects to login page (login.php) if notlogged in
    exit;
}

// gets the order ID from the URL and sanitizes it
$orderId = intval($_GET['order_id']); // coneverts to integer for security
$userId = $_SESSION['user_id']; // gets the logged in users ID

// starts the Database class and establish a connection
$db = new Database();
$conn = $db->connect();

try {
    // gets the order details from TblOrders with its associated items
    $stmt = $conn->prepare("
        SELECT o.id AS order_id, o.total_price, o.created_at, p.name, oi.quantity, oi.price 
        FROM TblOrders o
        JOIN TblOrderItems oi ON o.id = oi.order_id
        JOIN TblProducts p ON oi.product_id = p.id
        WHERE o.id = :order_id AND o.user_id = :user_id
    ");
    $stmt->bindParam(':order_id', $orderId); // binds order ID
    $stmt->bindParam(':user_id', $userId); // binds user ID
    $stmt->execute();
    $orderDetails = $stmt->fetchAll(PDO::FETCH_ASSOC); // gets results
    // redirects if no orders are found
    if (empty($orderDetails)) {
        header("Location: view_orders.php"); // redirects to orders page (view_orders.php)
        exit();
    }

    // gets general order information for display
    $totalPrice = $orderDetails[0]['total_price'];
    $createdAt = $orderDetails[0]['created_at'];
} catch (PDOException $e) {
    die("Error fetching order details: " . $e->getMessage()); // handles errors
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <!-- page design and style (style.css) -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Tyne Brew Coffee - Order Details</h1>
        <nav>
            <!-- page navigation -->
                <a href="index.php">Home</a>
                <a href="loyalty.php">TyneLoyalty</a>
                <a href="product.php">Our Products</a>
                <a href="cart.php">Shopping Cart</a>
                <a href="about_us.php">About Us</a>
                <a href="contact_us.php">Contact Us</a>
                <a href="careers.php">Careers</a>
                <a href="logout.php">Logout</a>
            </nav>
    </header>

    <main>
        <!-- displays order summary -->
        <h4>Order #<?= htmlspecialchars($orderId, ENT_QUOTES, 'UTF-8') ?></h4>
        <p><strong>Order Date:</strong> <?= htmlspecialchars($createdAt, ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Total Price:</strong> £<?= number_format($totalPrice, 2) ?></p>
        <!-- display order items in a table -->
        <h3>Order Items</h3>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orderDetails as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($item['quantity'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>£<?= number_format($item['price'], 2) ?></td>
                        <td>£<?= number_format($item['quantity'] * $item['price'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p><a href="view_orders.php" class="button">Back to Orders</a></p> <!-- navigation back to orders (view_orders.php)-->
    </main>
            <!-- page footer -->
    <footer>
        &copy; 2024 Tyne Brew Coffee. By Khalid A Alsayed (2239321). All rights reserved.
    </footer>
</body>
</html>
