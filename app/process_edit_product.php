<?php
include 'session_manager.php'; // manages the users session (session_manager.php)
include 'CSP.php'; // adds security with a security content policy (CSP.php)
require_once 'db.php'; // configures andf connectes to the database (db.php)

// Ensure the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); // redirects on admin users to the login page
    exit;
}

// (CSRF Protection) validates the CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Invalid CSRF token'); // prevents unauthorized form submissions
}

// checks the form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // collects and sanatizes the input data to prevent XSS attacks
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0; // ensures a valid product ID
    $name = htmlspecialchars(trim($_POST['name']), ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars(trim($_POST['description']), ENT_QUOTES, 'UTF-8');
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0.00; // ensures a valid price format
    $category = htmlspecialchars(trim($_POST['category']), ENT_QUOTES, 'UTF-8');
    $detailed_description = htmlspecialchars(trim($_POST['detailed_description']), ENT_QUOTES, 'UTF-8');
    $dimensions = htmlspecialchars(trim($_POST['dimensions']), ENT_QUOTES, 'UTF-8');
    $nutrition_facts = htmlspecialchars(trim($_POST['nutrition_facts']), ENT_QUOTES, 'UTF-8');

    // ensures product ID is valid
    if (!$id) {
        $_SESSION['edit_message'] = "Invalid product ID."; // sets error message
        header("Location: edit_product_list.php"); // redirects to the product list (edit_product_list.php)
        exit;
    }

    // database connection
    $db = new Database();
    $conn = $db->connect();

    // manages image upload
    $image_url = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_tmp_name = $_FILES['image']['tmp_name']; // temp image path
        $image_name = basename($_FILES['image']['name']); // file name
        $image_path = 'img/' . $image_name; // image destination path (img/)

        if (move_uploaded_file($image_tmp_name, $image_path)) {
            $image_url = $image_path; // sets image URL upload successful upload
        } else {
            $_SESSION['edit_message'] = "Failed to upload image."; // sets error message
            header("Location: edit_product.php?id=$id"); // redirects back to the edit page upon failure (edit_product_list.php)
            exit;
        }
    }

    // adds the product to the database (TblProducts)
    try {
        $query = "UPDATE TblProducts SET 
                  name = :name, 
                  description = :description, 
                  price = :price, 
                  category = :category, 
                  detailed_description = :detailed_description, 
                  dimensions = :dimensions, 
                  nutrition_facts = :nutrition_facts";

        // includes the product image 
        if ($image_url) {
            $query .= ", image_url = :image_url";
        }

        $query .= " WHERE id = :id";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':name', $name); // binds the name parameters
        $stmt->bindParam(':description', $description); // binds the description parameters
        $stmt->bindParam(':price', $price); // binds the price parameters
        $stmt->bindParam(':category', $category); // binds the category parameters
        $stmt->bindParam(':detailed_description', $detailed_description); // binds the detailed description parameters
        $stmt->bindParam(':dimensions', $dimensions); // binds the dimension parameters
        $stmt->bindParam(':nutrition_facts', $nutrition_facts); // binds nutriotion facts parameters
        $stmt->bindParam(':id', $id); // binds product ID

        if ($image_url) {
            $stmt->bindParam(':image_url', $image_url); // binds the image URL
        }

        $stmt->execute(); // executes the query

        $_SESSION['edit_message'] = "Product updated successfully!"; //sucess message
    } catch (PDOException $e) {
        $_SESSION['edit_message'] = "Error updating product: " . $e->getMessage(); // if any, handles database errors
    }

    // redirects back to the product list (edit_product_list.php)
    header("Location: edit_product_list.php");
    exit;
} else {
    // if script accessed directly redirects back to product list (edit_product_list.php)
    header("Location: edit_product_list.php");
    exit;
}
