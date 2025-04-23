<?php
include 'session_manager.php'; // manages the users session (session_manager.php)
include 'CSP.php'; // extra security with content security policy (CSP.php)
require_once 'db.php'; // database config and conneciton

// non-admin users redirected to login page (login.php)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// ensures the  CSRF token is valid before proceeding with deletion
if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Invalid CSRF token'); // terminates if the token is invalid
}

// retrieves and validates the product ID from the GET parameters 
$product_id = $_GET['id'] ?? null;

if (!$product_id || !filter_var($product_id, FILTER_VALIDATE_INT)) {
    echo "Invalid Product ID."; // error displayed if the product ID is invalid
    exit;
}
// prepares and executes the delete query
$query = $conn->prepare("DELETE FROM TblProducts WHERE id = ?");
$query->bind_param("i", $product_id); // binds the product ID as a integer
if ($query->execute()) {
    echo "Product deleted successfully."; // sucess message
} else {
    echo "Error deleting product."; // error message if deletion fails
}

header("Location: admin.php"); // redirects back to the admin page (admin.php)
exit;
?>
