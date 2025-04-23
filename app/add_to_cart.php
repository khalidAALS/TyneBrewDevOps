<?php
include 'session_manager.php';  // starts/resumes the session for the user (session_manager.php)
include 'CSP.php'; // includes CSP(CSP.php) for enhanced security
require_once 'db.php'; // database config/connection (db.php)

// initializes the cart within the session for a non logged in user if not already set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// restores the cart from the cookie if its available for non logged in users to resume with there selections
if (!isset($_SESSION['user_id']) && isset($_COOKIE['cart'])) {
    $_SESSION['cart'] = json_decode($_COOKIE['cart'], true);
}
// retrieves product ID and the quantity from the POST request default at 0 and 1
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

// CSRF protection for logged in users to prevent attacks
if (isset($_SESSION['user_id'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed"); //terminates the script if the token is invalid
    }
}
// starts the database connection
try {
    $db = new Database();
    $conn = $db->connect();

    // queries the database to get product details by ID
    $stmt = $conn->prepare("SELECT name FROM TblProducts WHERE id = :id");
    $stmt->bindParam(':id', $productId); //binds the product ID to prevent SQL injection
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC); // gets the product details

    if ($product) {
        // updates the cart, adds quantity if the product already exists other wise adds new item
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] += $quantity; // increases the quantity
        } else {
            $_SESSION['cart'][$productId] = $quantity;  // adds new product
        }

        // if a user is not logged in the cart will be saved in a cookie to retuyrn to when logged in
        if (!isset($_SESSION['user_id'])) {
            setcookie('cart', json_encode($_SESSION['cart']), time() + 3600, "/"); // cookie remains valid for 1 hour
        }
        // shows the user a message that product and its quantity was added to the cart
        echo "Added $quantity x " . htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') . " to your cart.";
    } else {
        echo "Product not found."; //if product ID is not found
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage(); //handles database errors
}
?>
