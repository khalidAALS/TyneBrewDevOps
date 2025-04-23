<?php
include 'session_manager.php';  // manages the users session (session_manager.php)
include 'CSP.php'; // enhances security (CSP.php)
require_once 'db.php'; // database config and connection (db.php)

// database connection
$db = new Database();
$conn = $db->connect();

// default values for the form in case of error
$username = '';
$email = '';
$password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // sanitizes user input to prevent XSS attacks
    $username = htmlspecialchars(trim($_POST['username']), ENT_QUOTES, 'UTF-8');
    $password = $_POST['password'];  // password does not need to be sanitized as they will be hashed.
    $email = htmlspecialchars(trim($_POST['email']), ENT_QUOTES, 'UTF-8');

    // generates a 4 digit random number for each user that registers 'loyalty number'
    $loyaltyNumber = rand(1000, 9999); // random 4 digit number

    // loyalty points start at 0
    $loyaltyPoints = 0;

    // checks if username or email already exists in the database with SQLi protection using prepared statements
    $stmt = $conn->prepare("SELECT * FROM TblUsers WHERE username = :username OR email = :email");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // error if the username and or email already exists int he database
        $_SESSION['register_error'] = "Username or email already exists.";
    } else {
        // hashes password and for secure storage in the databsse with SQLi protection using prepared statements
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        // inserts the new user details into the database 'TblUsers'
        $stmt = $conn->prepare("INSERT INTO TblUsers (username, password_hash, email, role, loyalty_number, loyalty_points) 
            VALUES (:username, :password_hash, :email, 'user', :loyalty_number, :loyalty_points)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password_hash', $password_hash);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':loyalty_number', $loyaltyNumber);
        $stmt->bindParam(':loyalty_points', $loyaltyPoints);

        if ($stmt->execute()) {
            $_SESSION['user_id'] = $conn->lastInsertId();
            $_SESSION['username'] = $username;
            $_SESSION['loyalty_number'] = $loyaltyNumber;
            $_SESSION['loyalty_points'] = $loyaltyPoints;

            // redirects the user to the login page (login.php) after successful registration
            $redirect = isset($_GET['redirect']) ? htmlspecialchars($_GET['redirect'], ENT_QUOTES, 'UTF-8') : 'login.php';
            header("Location: $redirect");
            exit();
        } else {
            // handles errors during registration
            $_SESSION['register_error'] = "Error: Could not complete registration.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php
include 'session_manager.php';  // No need to call session_start() here
include 'CSP.php';
require_once 'db.php';

// Ensure database connection
$db = new Database();
$conn = $db->connect();

// Define the default values for the form in case of error
$username = '';
$email = '';
$password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize user input to prevent XSS
    $username = htmlspecialchars(trim($_POST['username']), ENT_QUOTES, 'UTF-8');
    $password = $_POST['password'];  // Do not sanitize passwords; they will be hashed.
    $email = htmlspecialchars(trim($_POST['email']), ENT_QUOTES, 'UTF-8');

    // Generate a random 4-digit TyneLoyalty number
    $loyaltyNumber = rand(1000, 9999);

    // Initialize loyalty points to 0
    $loyaltyPoints = 0;

    // Check if username or email already exists (SQLi protection using prepared statements)
    $stmt = $conn->prepare("SELECT * FROM TblUsers WHERE username = :username OR email = :email");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $_SESSION['register_error'] = "Username or email already exists.";
    } else {
        // Hash password and insert new user (SQLi protection using prepared statements)
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO TblUsers (username, password_hash, email, role, loyalty_number, loyalty_points) 
            VALUES (:username, :password_hash, :email, 'user', :loyalty_number, :loyalty_points)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password_hash', $password_hash);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':loyalty_number', $loyaltyNumber);
        $stmt->bindParam(':loyalty_points', $loyaltyPoints);

        if ($stmt->execute()) {
            // Log the user in
            $_SESSION['user_id'] = $conn->lastInsertId();
            $_SESSION['username'] = $username;
            $_SESSION['loyalty_number'] = $loyaltyNumber;
            $_SESSION['loyalty_points'] = $loyaltyPoints;

            // Redirect to the requested page or login page
            $redirect = isset($_GET['redirect']) ? htmlspecialchars($_GET['redirect'], ENT_QUOTES, 'UTF-8') : 'login.php';
            header("Location: $redirect");
            exit();
        } else {
            $_SESSION['register_error'] = "Error: Could not complete registration.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SignUp</title>
    <link rel="stylesheet" href="style.css">

</head>
<body>
    <header>
        <h1>Tyne Brew Coffee - Register As a Customer</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="loyalty.php">TyneLoyalty</a>
            <a href="product.php">Our-Products</a>
            <a href="cart.php">Shopping-Cart</a>
            <a href="about_us.php">About Us</a>
            <a href="contact_us.php">Contact Us</a>
            <a href="careers.php">Careers</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php">Logout</a>
                <a href="view_orders.php">My Orders</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Signup</a>
            <?php endif; ?>
        </nav>
    </header>

    <h4>Register for an account below or <a href="login.php">Login Here!</a></h4><br>

    <!-- Error Message Popup -->
    <div id="errorPopup" class="error-message">
        <?php
        // Display the error message if it exists
        if (isset($_SESSION['register_error'])) {
            echo htmlspecialchars($_SESSION['register_error'], ENT_QUOTES, 'UTF-8');
            unset($_SESSION['register_error']);
        }
        ?>
    </div>
    
    <!-- Registration Form -->
    <form method="POST" action="">
        <label for="username">Username:</label><br>
        <input type="text" name="username" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>" required><br><br>

        <label for="password">Password:</label><br>
        <input type="password" name="password" value="<?php echo htmlspecialchars($password, ENT_QUOTES, 'UTF-8'); ?>" required><br><br>

        <label for="email">Email:</label><br>
        <input type="email" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required><br><br>

        <button type="submit">Register</button>
    </form>

    <footer>
        &copy; 2024 Tyne Brew Coffee. By Khalid A Alsayed (2239321). All rights reserved.
    </footer>

    <script>
        // If an error message exists, show the popup
        window.onload = function() {
            var errorPopup = document.getElementById("errorPopup");
            if (errorPopup.innerHTML.trim() !== "") {
                errorPopup.classList.add("show");

                // Hide the popup after 3 seconds
                setTimeout(function() {
                    errorPopup.classList.remove("show");
                }, 3000);
            }
        };
    </script>
</body>
</html>
        <!-- page metadata and design -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SignUp</title>
    <!-- page design and style (style.css) -->
    <link rel="stylesheet" href="style.css">

</head>
<body>
    <!-- page header section -->
    <header>
        <h1>Tyne Brew Coffee - Register As a Customer</h1>
        <!-- navigation links -->
        <nav>
            <a href="index.php">Home</a>
            <a href="loyalty.php">TyneLoyalty</a>
            <a href="product.php">Our-Products</a>
            <a href="cart.php">Shopping-Cart</a>
            <a href="about_us.php">About Us</a>
            <a href="contact_us.php">Contact Us</a>
            <a href="careers.php">Careers</a>
            <!-- only displays to logged in users -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php">Logout</a>
                <a href="view_orders.php">My Orders</a>
                <!-- only displays to non logged in users -->
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Signup</a>
            <?php endif; ?>
        </nav>
    </header>
                <!-- prompts user to register or login (login.php) -->
    <h4>Register for an account below or <a href="login.php">Login Here!</a></h4><br>

    <!-- error Message Popup -->
    <div id="errorPopup" class="error-message">
        <?php
        // displays the error message 
        if (isset($_SESSION['register_error'])) {
            echo htmlspecialchars($_SESSION['register_error'], ENT_QUOTES, 'UTF-8');
            unset($_SESSION['register_error']);
        }
        ?>
    </div>
    
    <!-- registration Form -->
    <form method="POST" action="">
        <!-- username input -->
        <label for="username">Username:</label><br>
        <input type="text" name="username" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>" required><br><br>
        <!-- password input -->
        <label for="password">Password:</label><br>
        <input type="password" name="password" value="<?php echo htmlspecialchars($password, ENT_QUOTES, 'UTF-8'); ?>" required><br><br>
        <!-- email input -->
        <label for="email">Email:</label><br>
        <input type="email" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required><br><br>
        <!-- button 'register' -->
        <button type="submit">Register</button>
    </form>

    <!-- page footer -->
    <footer>
        &copy; 2024 Tyne Brew Coffee. By Khalid A Alsayed (2239321). All rights reserved.
    </footer>

    <script>
        // javascrript for error popups
        window.onload = function() {
            var errorPopup = document.getElementById("errorPopup");
            if (errorPopup.innerHTML.trim() !== "") {
                errorPopup.classList.add("show");

                // Hide the popup after 3 seconds
                setTimeout(function() {
                    errorPopup.classList.remove("show");
                }, 3000); //hides the popup after 3 seconds
            }
        };
    </script>
</body>
</html>
