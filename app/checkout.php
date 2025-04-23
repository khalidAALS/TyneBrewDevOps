<?php
include 'session_manager.php'; // manages user session (session_manager.php)
include 'CSP.php'; // adds security through a content security policy (CSP.php)
require_once 'db.php'; // database config and connection (db.php)

// redirects the user to login(login.php)or signup(register.php) if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=checkout");
    exit;
}

// cart items and total price
$cartItems = [];
$totalPrice = 0;

// gets items from the session cart if it exists
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $ids = implode(',', array_keys($_SESSION['cart'])); // gets the product ID in the cart
    $query = "SELECT * FROM TblProducts WHERE id IN ($ids)"; // gets product details
    $result = $conn->query($query);

    while ($row = $result->fetch_assoc()) {
        $row['quantity'] = $_SESSION['cart'][$row['id']]; // adds the quantities from the cart
        $row['line_total'] = $row['price'] * $row['quantity']; // calculates the total price
        $cartItems[] = $row; // adds product to the cart items array
        $totalPrice += $row['line_total']; // updates the total price
    }
}

// handles orders when the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($cartItems)) {
    // this will update the database base 'TblOrders' with any orders made
    $stmt = $conn->prepare("INSERT INTO TblOrders (user_id, total_price, status, created_at) VALUES (?, ?, 'Pending', NOW())");
    $stmt->bind_param("id", $_SESSION['user_id'], $totalPrice);
    $stmt->execute();
    $orderId = $stmt->insert_id; // get new order ID
    $stmt->close();

    // adds items into 'TblOrderItems'
    $stmt = $conn->prepare("INSERT INTO TblOrderItems (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($cartItems as $item) {
        $stmt->bind_param("iiid", $orderId, $item['id'], $item['quantity'], $item['price']);
        $stmt->execute();
    }
    $stmt->close();

    // clears the cart after a successful order
    unset($_SESSION['cart']);

    // once order is complete this redirects to confirmation (confirmation.php)
    header("Location: confirmation.php?order_id=$orderId");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- page metadata and responsive design -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <!-- page design and style (style.css) -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- page header section -->
    <header>
        <h1>Tyne Brew Coffee - Checkout</h1>
        <a>
            <!-- navigation links -->
            <a href="index.php">Home</a>
            <a href="loyalty.php">Tyne-Loyalty</a>
            <a href="cart.php"></a>Shopping-Cart</a>
            <a href="product.php">Our-Products</a>
            <a href="about_us.php">About-Us</a>
            <a href="contact_us.php">Contact-Us</a>
            <a href="careers.php">Careers</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- only appears to logged in users -->
                <a href="view_orders.php">My-Orders</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <!-- appears for non logged in users -->
                <a href="login.php">Login</a>
                <a href="register.php">Signup</a>
            <?php endif; ?>
            <!-- only appears for admin users -->
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="admin.php">Admin-Dashboard</a>
                <?php endif; ?>
        </nav>
        <?php if (isset($_SESSION['username'])): ?>
            <!-- welcome message with username -->
        <div class="welcome-message">
            <p>Hello, welcome back <strong><?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?></strong>!</p>
        </div>
        <?php endif; ?>
    </header>
            <!-- page main content -->
    <main>
        <section id="checkout">
            <h2>Order Summary</h2>
            <?php if (empty($cartItems)): ?>
                <!-- message appears if the cart is empty with link to shop (product.php)-->
                <p>Your cart is empty. <a href="product.php">Return to the store</a>.</p>
            <?php else: ?>
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
                        <?php foreach ($cartItems as $item): ?>
                            <!-- displays cart items -->
                            <tr>
                                <td><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($item['quantity'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>$<?= number_format($item['price'], 2) ?></td>
                                <td>$<?= number_format($item['line_total'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <!-- place order form -->
                <p><strong>Total Price:</strong> $<?= number_format($totalPrice, 2) ?></p>
                <form method="POST" action="checkout.php">
                    <!-- button 'place order' -->
                    <button type="submit" class="button">Place Order</button>
                </form>
            <?php endif; ?>
        </section>
    </main>
    <!-- page footer -->
    <footer>
    &copy; 2024 Tyne Brew Coffee. By Khalid A Alsayed (2239321). All rights reserved.
    </footer>
</body>
</html>
