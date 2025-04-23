<?php
include 'session_manager.php'; // manages the users session (session_manager.php)
include 'CSP.php'; // enhanced security with a content security policy (CSP.php)
require_once 'db.php'; // databse config and connection (db.php)

// database connection
$db = new Database();
$conn = $db->connect();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // gets users loyalty number and points for TblUsers
    $stmt = $conn->prepare("SELECT loyalty_number, loyalty_points FROM TblUsers WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC); // gets loyalty data

    // gets users transactions (completed orders) from TblOrders
    $stmt = $conn->prepare("
        SELECT 
            o.created_at AS transaction_date, 
            oi.order_id,
            SUM(oi.quantity) AS total_quantity, 
            oi.price,
            SUM(oi.quantity * oi.price) AS total_price
        FROM TblOrders o
        JOIN TblOrderItems oi ON o.id = oi.order_id
        WHERE o.user_id = :user_id AND o.status = 'completed'
        GROUP BY oi.order_id
        ORDER BY o.created_at DESC
    ");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC); //gets transaction data

    // handles case when there are no orders found
    $transactions_message = empty($orders) ? "No transactions found." : "Transaction history below.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- metadata and page settings -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tyne Loyalty</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- page navigation -->
    <header class="main-header">
        <div class="container">
            <h1>Tyne Brew Coffee - Tyne Loyalty</h1>
            <nav>
                <a href="index.php">Home</a>
                <a href="product.php">Our Products</a>
                <a href="cart.php">Shopping Cart</a>
                <a href="about_us.php">About Us</a>
                <a href="contact_us.php">Contact Us</a>
                <a href="careers.php">Careers</a>
                <!-- only appears for logged in users -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="logout.php">Logout</a>
                    <a href="view_orders.php">My Orders</a>
                    <!-- only appears for non logged in users -->
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </nav>
        </div>
        <?php if (isset($_SESSION['username'])): ?>
            <div class="welcome-message">
                <!-- welcome message with username, loyalty number and loyalty points -->
                <p>Hello, welcome back <strong><?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?></strong>! 
                Your Tyne Loyalty Number: <strong><?= $user['loyalty_number']; ?></strong>, 
                Loyalty Points: <strong><?= $user['loyalty_points']; ?></strong></p>
            </div>
        <?php endif; ?>
    </header>

    <main class="container">
        <!-- loyalty section -->
        <section id="about-us" class="info-section">
            <h2>Tyne Loyalty</h2>
            <p>Welcome to Tyne Brew's Loyalty section. Collect Tyne Points and redeem them for free drinks and exclusive rewards.</p>
            <?php if (!empty($orders)): ?>
                <h3>Transaction History</h3>
                <p><?= $transactions_message ?></p>
                <!-- shows transaction history in a table if available -->
                <table>
                    <thead>
                        <tr>
                            <th>Transaction Date</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['transaction_date'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= $order['total_quantity'] ?></td>
                                <td>£<?= number_format($order['price'], 2) ?></td>
                                <td>£<?= number_format($order['total_price'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p><?= $transactions_message ?></p>
            <?php endif; ?>
        </section>
    </main>

    <!-- page footer -->
    <footer class="main-footer">
        <div class="container">
            &copy; 2024 Tyne Brew Coffee. By Khalid A Alsayed (2239321). All rights reserved.
        </div>
    </footer>
</body>
</html>





