<?php
include 'session_manager.php'; // manages users session (session_manager.php)
include 'CSP.php'; // enhanaced security with a content security policy (CSP.php)
require_once 'db.php'; // databasse connection and config

// redirects non-admin users to login page (login.php)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Database connection
$db = new Database();
$conn = $db->connect();

// handles form submission for updating order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    // (CSRF protection) checks the token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token'); // prevents CSRF attacks
    }

    $orderId = (int)$_POST['order_id']; // order ID from the form input
    $newStatus = $_POST['status']; // new status selectable from dropdown menu

    // updates order status in databsse TblOrders
    $updateStmt = $conn->prepare("UPDATE TblOrders SET status = :status WHERE id = :id");
    $updateStmt->bindParam(':status', $newStatus);
    $updateStmt->bindParam(':id', $orderId, PDO::PARAM_INT);

    if ($updateStmt->execute()) {
        $_SESSION['success_message'] = "Order status updated successfully."; // success message
    } else {
        $_SESSION['error_message'] = "Failed to update order status."; // error message
    }
    header("Location: manage_orders.php"); // redirects to avoid resubmission
    exit();
}

// gets all orders orders from TblOrders
try {
    $stmt = $conn->query("SELECT * FROM TblOrders ORDER BY created_at DESC"); // gets orders sorted by date
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC); // gets the results
} catch (PDOException $e) {
    die("Error fetching orders: " . $e->getMessage()); // handles query errors
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- page settings and metadata -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <!-- page style and design (style.css) -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Manage Orders</h1>
        <nav>
            <!-- page navigation -->
            <a href="admin.php">Admin Dashboard</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <main>
        <h4>All Orders</h4>
        <!-- sucess message -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success-message">
                <?= htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
            <!-- error message -->
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="error-message">
                <?= htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <?php if (!empty($orders)): ?>
            <!-- shows orders in table if available -->
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>User ID</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th>Order Date</th>
                        <th>Update Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['id'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($order['user_id'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>£<?= number_format($order['total_price'], 2) ?></td>
                            <td><?= htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($order['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <!-- updates the status form -->
                                <form method="POST" action="manage_orders.php">
                                    <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id'], ENT_QUOTES, 'UTF-8') ?>">
                                    <select name="status" required>
                                        <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                        <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" name="update_status">Update</button>
                                    <!-- CSRF token -->
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No orders found.</p> <!--errormessage for when no orders exists-->
        <?php endif; ?>
    </main>
    <!-- page footer -->
    <footer>
        &copy; 2024 Tyne Brew Coffee. By Khalid A Alsayed (2239321). All rights reserved.
    </footer>
</body>
</html>
