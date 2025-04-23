<?php
include 'session_manager.php'; // manages the users session (session_manager.php)
include 'CSP.php'; // adds content security policy (CSP.php)
require_once 'db.php'; // configs and connects the database (db.php)

// redirects non admin users to login page (login.php)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); 
    exit;
}

// (CSRF Protection) validates the CSRF token from POST data
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Invalid CSRF token'); // prevents unauthorized form submission
}

// ensures the product ID is valid
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0; // ensures valid product ID
if (!$product_id) {
    $_SESSION['delete_message'] = "Invalid product ID."; // error message
    header("Location: delete_product_list.php"); // redirects to product list (delete_product_list.php)
    exit;
}

// database connection
$db = new Database();
$conn = $db->connect();

//  deletes the product
try {
    // prepares and executes the delete query
    $stmt = $conn->prepare("DELETE FROM TblProducts WHERE id = :id");
    $stmt->bindParam(':id', $product_id, PDO::PARAM_INT); // binds product ID
    $stmt->execute(); // executes the query

    $_SESSION['delete_message'] = "Product deleted successfully!"; // success message
} catch (PDOException $e) {
    $_SESSION['delete_message'] = "Error deleting product: " . $e->getMessage(); // handles database errors if any
}

// redirects back to the product list (delete_product_list.php)
header("Location: delete_product_list.php");
exit;
?>
