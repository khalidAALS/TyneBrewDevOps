<?php
include 'session_manager.php'; // handles user session (session_manager.php)
include 'CSP.php'; // implements the security policy (CSP.php)
require_once 'db.php'; // database config and connection (db.php)

// database connection
$db = new Database();
$conn = $db->connect(); // starts the connection to database
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- metadata for a responsive desing -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tyne Loyalty</title>
    <!-- page desing and style through (style.css) -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!--  navigation bar -->
    <header class="main-header">
        <div class="container">
            <h1>Tyne Brew Coffee - Careers</h1>
            <nav>
                <!-- nav for website sections -->
                <a href="index.php">Home</a>
                <a href="loyalty.php">Tyne-Loyalty</a>
                <a href="product.php">Our-Products</a>
                <a href="cart.php">Shopping-Cart</a>
                <a href="about_us.php">About-Us</a>
                <a href="contact_us.php">Contact-Us</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- only appear for logged in users nav -->
                    <a href="view_orders.php">My-Orders</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <!-- only appear for non logged in users -->
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
                <!-- only appear for admin -->
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="admin.php">Admin-Dashboard</a>
                <?php endif; ?>
            </nav>
        </div>
        <!-- welcome message with username -->
        <?php if (isset($_SESSION['username'])): ?>
        <div class="welcome-message">
            <p>Hello, welcome back <strong><?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?></strong>!</p>
        </div>
        <?php endif; ?>
    </header>
            <!-- page main content -->
    <main class="container">
        <!-- tyne careers section -->
        <section id="about-us" class="info-section">
            <h2>Tyne Careers</h2>
            <p>Welcome to Tyne Brew Coffee Careers section, when vacancies are available they will appear below, if no vacancies are currently available please enter your information and stay up to date as soon as we are hiring!</p>

            <!-- contact form in careers  -->
        <section id="contact-form" class="info-section">
            <h2>Register your Interest</h2>
            <p>We currently have no vacancies available, but we are always expanding. If you are a creative individual with a passion for coffee, please register your interest below to stay updated as soon as a vacancy becomes available. We would love to hear from you!</p>
            <form action="index.php" method="POST">
                <!-- inpout for name -->
                <label for="name">Your Name</label>
                <input type="text" id="name" name="name" required>
                <!-- input for email -->
                <label for="email">Your Email</label>
                <input type="email" id="email" name="email" required>

                <!-- hidden CSRF token for the form security -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                <!-- submit 'send message' button -->
                <button type="submit">Send Message</button>
            </form>
        </section>

    </main>

    <!-- page footer section -->
    <footer class="main-footer">
        <div class="container">
        &copy; 2024 Tyne Brew Coffee. By Khalid A Alsayed (2239321). All rights reserved.
        </div>
    </footer>
</body>
</html>
