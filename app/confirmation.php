<?php
include 'session_manager.php'; // manages the users session (session_manager.php)
include 'CSP.php'; // extra security through a content security policy (CSP.php)
require_once 'db.php'; //databse config and connection(db.php)

// check the user is logged in and the order ID is provided
if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    header("Location: index.php"); // redirects to home page (index.php) if the user is not logged in or has no order ID
    exit;
}

$orderId = intval($_GET['order_id']); // sanitizes the order ID

//  Database class and establishes a connection
$db = new Database();
$conn = $db->connect();

try {
    // gets order and item details
    $stmt = $conn->prepare("
        SELECT o.id AS order_id, o.total_price, o.created_at, p.name, oi.quantity, oi.price 
        FROM TblOrders o
        JOIN TblOrderItems oi ON o.id = oi.order_id
        JOIN TblProducts p ON oi.product_id = p.id
        WHERE o.id = :order_id AND o.user_id = :user_id
    ");
    $stmt->bindParam(':order_id', $orderId); // binds the order ID
    $stmt->bindParam(':user_id', $_SESSION['user_id']); // binds the user ID
    $stmt->execute();
    $orderDetails = $stmt->fetchAll(PDO::FETCH_ASSOC); // gets all matching records

    // Check if order exists
    if (empty($orderDetails)) {
        header("Location: index.php");
        exit();
    }
} catch (PDOException $e) {
    die("Error fetching order details: " . $e->getMessage());
}

// gets total price and date from the order
$totalPrice = $orderDetails[0]['total_price'];
$createdAt = $orderDetails[0]['created_at'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- metadate and settings -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <!-- page design and style (style.css) -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- page header -->
    <header>
        <h1>Tyne Brew Coffee - Order Confirmation</h1>
        <!-- page navigation -->
        <nav>
            <a href="index.php">Home</a>
            <!-- shows if user is logged in -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="view_orders.php">My Orders</a>
                <a href="logout.php">Logout</a>
                <!-- shows if user is not logged in -->
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
            <!-- only shows to admin -->
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="admin.php">Admin-Dashboard</a>
                <?php endif; ?>
        </nav>
    </header>
                <!-- page main content -->
    <main>
        <section id="confirmation">
            <h4>Thank you for your order!</h4>
            <p>Your order <strong>#<?= htmlspecialchars($orderId, ENT_QUOTES, 'UTF-8') ?></strong> has been placed successfully.</p>
            <p><strong>Order Date:</strong> <?= htmlspecialchars($createdAt, ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Total Price:</strong> £<?= number_format($totalPrice, 2) ?></p>

            <h3>Order Details</h3>
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
                        <!-- shows all products in the order -->
                        <tr>
                            <td><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($item['quantity'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>£<?= number_format($item['price'], 2) ?></td>
                            <td>£<?= number_format($item['quantity'] * $item['price'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p><a href="product.php" class="button">Continue Shopping</a></p>
        </section>
    </main>
                        <!-- page footer -->
    <footer>
        &copy; 2024 Tyne Brew Coffee. By Khalid A Alsayed (2239321). All rights reserved.
    </footer>
</body>
</html>
