<?php
include 'session_manager.php'; //includes session management (session_manager.php) for user session handling
require_once 'db.php'; //includes the database connection (db.php) configuration and connection
include 'CSP.php'; //includes the content security policy (CSP.php) to enhance security

// database connection
$db = new Database();
$conn = $db->connect();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- html page metadata and respnsive design settings -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us</title>
    <!-- links the style (style.css) for page styling -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- navigation -->
    <header class="main-header">
        <div class="container">
            <!-- site title -->
            <h1>Tyne Brew Coffee - About Us</h1>
            <nav>
                <!-- navigation to other web pages within the site -->
                <a href="index.php">Home</a>
                <a href="loyalty.php">TyneLoyalty</a>
                <a href="product.php">Our-Products</a>
                <a href="cart.php">Shopping-Cart</a>
                <a href="contact_us.php">Contact Us</a>
                <a href="careers.php">Careers</a>
                <!-- nav links that only appear for logged in users -->
                <?php if (isset($_SESSION['user_id'])): ?>
                <a href="view_orders.php">My Orders</a>
                <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
                <!-- admin only nav links  -->
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="admin.php">Admin-Dashboard</a>
                <?php endif; ?>
            </nav>
        </div>
        <!-- personalised log in message that appears with the users username upon log in -->
        <?php if (isset($_SESSION['username'])): ?>
        <div class="welcome-message">
            <p>Hello, welcome back <strong><?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?></strong>!</p>
        </div>
        <?php endif; ?>
    </header>

    <main class="container">
        <!-- about us section -->
        <section id="about-us" class="info-section">
            <h2>About Us</h2>
            <p>Welcome to Tyne Brew Coffee, Newcastle's new luxury coffee experience and destination. Established in 2024, we pride ourselves on offering the finest quality coffee, exceptional service, and a cozy warm ambiance that our customers love. Our aim is to create memorable coffee experiences for all.</p>
        </section>

        <!-- Aabout the company founders section -->
        <section id="about-founders" class="info-section">
            <h2>About the Founders</h2>
            <p>Tyne Brew was founded by a group of coffee enthusiasts who discovered a space that celebrates the art of coffee-making and coming together. With a diverse background in hospitality and culinary arts, the founders have combined their expertise to bring you a quality and luxury coffee experience in a place where memories are created.</p>
        </section>

        <!-- about the Coffee Section -->
        <section id="about-coffee" class="info-section">
            <h2>About Our Coffee</h2>
            <p>Our coffee beans are sourced from the finest farms worldwide, including Argentina and Ethiopia, with a focus on ensuring ethical practices and sustainable measures. We work closely with expert roasters to bring you rich, aromatic memories in every cup. At Tyne Brew Coffee, you and the quality in every cup are our priority.</p>
        </section>

        <!-- join the tem sextion linked to (careers.php) -->
        <section id="join-team" class="info-section">
            <h2>Join the Team</h2>
            <p>Are you passionate about coffee? We are always looking for talented people to join our team. Explore career opportunities at Tyne Brew Coffee and become a part of a community that values creativity, dedication, and customer satisfaction.</p>
            <a href="careers.php" class="btn btn-black">View Careers</a>
        </section>
    </main>

    <!-- page footer -->
    <footer class="main-footer">
        <div class="container">
            <&copy; 2024 Tyne Brew Coffee. By Khalid A Alsayed (2239321). All rights reserved.
        </div>
    </footer>
</body>
</html>
