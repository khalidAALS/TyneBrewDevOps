<?php
include 'session_manager.php'; // manages the users session (session_manager.php)
include 'CSP.php'; // database config and connection (db.php)
require_once 'db.php'; // enhances security with a content security policy (CSP.php)

// ensures a secure user interaction with CSRF token setup
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // generates a random 32-byte token
}

// redirects to the login page (login.php) if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // redirects to the login page (login.php)
    exit; // ensures no further code executes
}

// database connection
$db = new Database();
$conn = $db->connect();

// gets the logged in users ID
$userId = $_SESSION['user_id'];

// prepares the query to get the users orders
$query = "SELECT o.id AS order_id, o.total_price, o.created_at
          FROM TblOrders o
          WHERE o.user_id = :user_id";
$params = [':user_id' => $userId]; // binds the users ID to the query parameters

// adds the date filter if its provided
if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
    $query .= " AND DATE(o.created_at) BETWEEN :start_date AND :end_date"; // allows filtering orders by date range from and to
    $params[':start_date'] = $_GET['start_date'];
    $params[':end_date'] = $_GET['end_date'];
}

$query .= " ORDER BY o.created_at DESC"; // orders the results by the most recent first

try {
    // prepares and executes the query
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC); // gets all matching orders
} catch (PDOException $e) {
    die("Error fetching orders: " . $e->getMessage()); // handles database errors
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- page metadata and setup -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Orders</title>
     <link rel="stylesheet" href="style.css"> <!-- links the page style (style.css) -->
</head>
<body>
    <header>
        <!-- page header section -->
        <h1>Tyne Brew Coffee - Your Orders</h1>
        <nav>
            <!-- page navigation -->
            <a href="index.php">Home</a>
            <a href="loyalty.php">TyneLoyalty</a>
            <a href="product.php">Our-Products</a>
            <a href="cart.php">Shopping-Cart</a>
            <a href="about_us.php">About Us</a>
            <a href="contact_us.php">Contact Us</a>
            <a href="careers.php">Careers</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <!-- page main content -->
    <main>
        <h4>Your Previous Orders</h4>

        <!-- date Filter Form  -->
        <form method="GET" action="view_orders.php">
            <!-- includes a CSRF token for form submission security -->
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>">

            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>">
            <!-- submit "filter" button  -->
            <button type="submit">Filter</button> 
        </form>
        <!-- displays a message if no orders exist -->
        <?php if (empty($orders)): ?>
            <p>You have not placed any orders. <a href="product.php">Start shopping now!</a></p>
        <?php else: ?>
            <!-- displays all orders in a table -->
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Total Price</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <!-- displays order details -->
                            <td><?= htmlspecialchars($order['order_id']) ?></td>
                            <td><?= htmlspecialchars($order['created_at']) ?></td>
                            <td>£<?= number_format($order['total_price'], 2) ?></td>
                            <td>
                                <!-- link to view more details about the order (order_details.php) -->
                                <a href="order_details.php?order_id=<?= htmlspecialchars($order['order_id']) ?>">View Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
    <!-- page footer -->
    <footer>
        &copy; 2024 Tyne Brew Coffee. By Khalid A Alsayed (2239321). All rights reserved.
    </footer>
</body>
</html>
