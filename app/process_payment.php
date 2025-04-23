<?php
include 'session_manager.php'; // manages users session (session_manager.php)
include 'CSP.php'; // adds content security policy (CSP.php)
require_once 'db.php'; // cincludes the database config and connection

// CSRF Protection - validates the CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Invalid CSRF token'); // terminates if the token is invalid
}

// checks if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database(); // initiated the databse class
    $conn = $db->connect(); // starts the databse connection

    // collects and sanitizes data from the form to prevent XSS attacks
    $first_name = htmlspecialchars(trim($_POST['first_name']), ENT_QUOTES, 'UTF-8');
    $surname = htmlspecialchars(trim($_POST['surname']), ENT_QUOTES, 'UTF-8');
    $loyalty_number = htmlspecialchars(trim($_POST['loyalty_number']), ENT_QUOTES, 'UTF-8');
    $house_number = htmlspecialchars(trim($_POST['house_number']), ENT_QUOTES, 'UTF-8');
    $street_name = htmlspecialchars(trim($_POST['street_name']), ENT_QUOTES, 'UTF-8');
    $postcode = htmlspecialchars(trim($_POST['postcode']), ENT_QUOTES, 'UTF-8');
    $card_number = htmlspecialchars(trim($_POST['card_number']), ENT_QUOTES, 'UTF-8');
    $expiry_date = htmlspecialchars(trim($_POST['expiry_date']), ENT_QUOTES, 'UTF-8');
    $cvv = htmlspecialchars(trim($_POST['cvv']), ENT_QUOTES, 'UTF-8');

    // initializes variables for the cart and total price
    $cartItems = [];
    $totalPrice = 0;
    // calculates total cost based on the carts contents
    if (!empty($_SESSION['cart'])) {
        $ids = implode(',', array_keys($_SESSION['cart'])); // gets the product ID in the cart
        $query = "SELECT * FROM TblProducts WHERE id IN ($ids)"; // gets product details
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC); // get cart items

        foreach ($cartItems as $product) {
            $quantity = $_SESSION['cart'][$product['id']];
            $price = (float)$product['price'];
            $totalPrice += $price * $quantity; // calculates the total price
        }
    }

    try {
        // adds the order into TblOrders with SQL injection protection using prepared statements
        $stmt = $conn->prepare("INSERT INTO TblOrders (user_id, total_price, status) VALUES (:user_id, :total_price, 'completed')");
        $stmt->bindParam(':user_id', $_SESSION['user_id']); // binds the user ID
        $stmt->bindParam(':total_price', $totalPrice); // binds the total price
        $stmt->execute();
        $orderId = $conn->lastInsertId(); // gets the ID or the inserted order

        // adds each item into TblOrderItems with SQL injection protection using prepared statements
        $stmt = $conn->prepare("INSERT INTO TblOrderItems (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)");

        foreach ($cartItems as $product) {
            $stmt->bindParam(':order_id', $orderId); // binds the order ID
            $stmt->bindParam(':product_id', $product['id']); // binds the product ID
            $stmt->bindParam(':quantity', $_SESSION['cart'][$product['id']]); //Binds the quantity
            $stmt->bindParam(':price', $product['price']); // binds the price
            $stmt->execute();
        }

        // clears the cart session
        unset($_SESSION['cart']);

        // redirects the user to the confirmation page (confirmation.php) with the order ID
        header("Location: confirmation.php?order_id=" . $orderId);
        exit();
    } catch (PDOException $e) {
        // handles errors during databse operations
        die("Error processing your order: " . $e->getMessage());
    }
} else {
    // redirects users to the cart (cart.php) if script is accessed directly
    header('Location: cart.php');
    exit();
}
?>
