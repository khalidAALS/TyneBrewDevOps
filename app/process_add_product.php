<?php
include 'session_manager.php'; // manages the users session (session_manager.php)
include 'CSP.php'; // enhanced security with content security policy (CSP.php)
require_once 'db.php'; // configs and connects the database (db.php)

//connection to the database
$db = new Database();
$conn = $db->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // (CSRF Protection) validates the CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token'); // prevents unauthorized form submissions
    }

    // sanitizes inputs to prevent XSS attacks
    $name = htmlspecialchars(trim($_POST['name']), ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars(trim($_POST['description']), ENT_QUOTES, 'UTF-8');
    $price = (float)$_POST['price']; // ensures price is a float
    $category = htmlspecialchars(trim($_POST['category']), ENT_QUOTES, 'UTF-8');
    $detailed_description = htmlspecialchars(trim($_POST['detailed_description']), ENT_QUOTES, 'UTF-8');
    $dimensions = htmlspecialchars(trim($_POST['dimensions']), ENT_QUOTES, 'UTF-8');
    $nutrition_facts = htmlspecialchars(trim($_POST['nutrition_facts']), ENT_QUOTES, 'UTF-8');
    $image = $_FILES['image']; // uploaded image file data

    // checks if an image was uploaded
    if ($image && $image['error'] === UPLOAD_ERR_OK) {
        $image_path = 'img/' . basename($image['name']); // sets destination path
        move_uploaded_file($image['tmp_name'], $image_path); // moves the uploaded file to destination path
    } else {
        $image_path = null; // sets as null if no image is uploaded
    }

    // Insert into the database (prepared statement to prevent SQL injection)
    $query = "INSERT INTO TblProducts (name, description, price, image_url, category, detailed_description, dimensions, nutrition_facts) 
              VALUES (:name, :description, :price, :image_url, :category, :detailed_description, :dimensions, :nutrition_facts)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':name', $name); // binds product name
    $stmt->bindParam(':description', $description); // binds product description
    $stmt->bindParam(':price', $price); // binds product price
    $stmt->bindParam(':image_url', $image_path); // binds products image URL
    $stmt->bindParam(':category', $category); // binds products category
    $stmt->bindParam(':detailed_description', $detailed_description); // binds products detailed description
    $stmt->bindParam(':dimensions', $dimensions); // binds products dimensions
    $stmt->bindParam(':nutrition_facts', $nutrition_facts); // binds products nutriotion facts
    // executes the query and sets the session message based on the outcome
    if ($stmt->execute()) {
        $_SESSION['product_message'] = "Product '$name' was added successfully!";
    } else {
        $_SESSION['product_message'] = "Failed to add product. Please try again.";
    }

    // redirects back to add product page (add_product.php)
    header("Location: add_product.php");
    exit();
}
?>

