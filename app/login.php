<?php
require_once 'db.php'; // database config and connection (db.php)
require_once 'session_manager.php'; // handles users session (session_manager.php)

// database connection
$db = new Database();
$conn = $db->connect();

// starts the session if not already started
session_start();

// checks if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // collects and sanitizes inputs
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        // error message if fields are empty
        $_SESSION['login_error_message'] = "Username or password cannot be empty.";
        header("Location: login.php"); // redirects back to login page (login.php)
        exit();
    }

    // gets user details from the database TblUsers with SQL injection prevention using prepared statements
    $stmt = $conn->prepare("SELECT * FROM TblUsers WHERE username = :username");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        // sets session variables if login is successful
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        // redirects based on user role
        if ($user['role'] === 'admin') {
            header("Location: admin.php"); // redirects to the admin page (admin.php) if the user has an admin role
        } else {
            header("Location: index.php");  // redirects to the home page (index.php) if the user has a user role
        }
        exit();
    } else {
        // Invalid credentials  error message
        $_SESSION['login_error_message'] = "Invalid username or password.";
        header("Location: login.php"); // redirects back to login page (login.php)
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tyne Brew Coffee - Login</title>
    <!-- page style and design (style.css) -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Tyne Brew Coffee - Login</h1>
        <nav>
            <!-- page navigation -->
            <a href="index.php">Home</a>
            <a href="loyalty.php">TyneLoyalty</a>
            <a href="product.php">Our Products</a>
            <a href="cart.php">Shopping Cart</a>
            <a href="about_us.php">About Us</a>
            <a href="contact_us.php">Contact Us</a>
            <a href="register.php">SignUp</a>
            <a href="careers.php">Careers</a>
        </nav>
    </header>

    <main>
        <h4>Enter Your Details to Login</h4>
<!-- displays login error message in a fixed error box  -->
<?php if (isset($_SESSION['login_error_message'])): ?>
    <div class="error-message"><?= htmlspecialchars($_SESSION['login_error_message'], ENT_QUOTES, 'UTF-8'); ?></div>
    <?php unset($_SESSION['login_error_message']); ?> <!--clears the error message after it displayed -->
<?php endif; ?>


        <!-- POST form to login -->
        <form method="POST" action="login.php">
            <!-- input field for username -->
            <label for="username">Username:</label><br>
            <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required><br><br>
            <!-- input field for password -->
            <label for="password">Password:</label><br>
            <input type="password" name="password" required><br><br>
            <!-- submit 'login' button -->
            <button type="submit">Login</button>
        </form>
    </main>
    <!-- page footer -->    
    <footer>
        &copy; 2024 Tyne Brew Coffee. All rights reserved.
    </footer>

    <script>
        // displays the error message in a popup
        window.onload = function() {
            var errorPopup = document.querySelector('.error-message');
            if (errorPopup) {
                errorPopup.classList.add("show");

                // automatically hides the message after diplaying for 3 seconds
                setTimeout(function() {
                    errorPopup.classList.remove("show");
                }, 3000);
            }
        };
    </script>
</body>
</html>
