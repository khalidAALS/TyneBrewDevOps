<?php

include 'session_manager.php'; // handles the users session (session_manager.php)
include 'CSP.php'; // extra security with a conetnt security policy (CSP.php)
require_once 'db.php'; // database config and connection (db.php)

// database connection
$db = new Database();
$conn = $db->connect();

// gets unique categories with images from TblProducts
$query = "SELECT DISTINCT category, image_url FROM TblProducts";
$result = $conn->query($query); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Tyne Brew</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- page navigation -->
    <header class="main-header">
        <div class="container">
            <h1>Tyne Brew Coffee</h1><br>
            <nav>
                <a href="product.php">Our Products</a>
                <a href="loyalty.php">TyneLoyalty</a>
                <a href="cart.php">Shopping Cart</a>
                <a href="about_us.php">About Us</a>
                <a href="contact_us.php">Contact Us</a>
                <a href="careers.php">Careers</a>
                <!-- only shows for logged in ursers -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="logout.php">Logout</a>
                    <a href="view_orders.php">My Orders</a>
                    <!-- only shows for non logged in users -->
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
                <!-- only shows for admin users -->
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="admin.php">Admin-Dashboard</a>
                <?php endif; ?>
            </nav>
        </div>
        <?php if (isset($_SESSION['username'])): ?>
            <!-- welcome message for users with username -->
            <div class="welcome-message">
                <p>Hello, welcome back <strong><?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?></strong>!</p>
            </div>
        <?php endif; ?>
    </header>

    <!-- hero section -->
    <section class="hero">
        <img src="img/newcastle.jpg" alt="Newcastle City" class="hero-image">
        <div class="hero-content">
            <h1>Making One Cup Of Memories at a Time.</h><br>
            <p1>Tyne Brew Coffee,</p1>
            <p2>Experience the finest and highest quality of coffee in Newcastle upon Tyne.</p2><br>
            <a href="about_us.php" class="btn">Learn More</a>
        </div>
    </section><br>

    <main class="container">
        <!-- about us section -->
        <section id="about-us" class="info-section">
            <h2>What Makes us Different</h2><br>
            <p>Tyne Brew is the perfect place for anybody looking for exceptional coffee and warm hospitality.
            Located in the heart of Newcastle upon Tyne, we pride ourselves on sourcing the finest beans and creating memorable experiences for our customers.</p>
        </section><br>

        <!-- category section -->
        <section id="categories" class="info-section">
            <h2>Our Products</h2><br>
            <div class="categories-container">
                <!-- food category -->
                <div class="category-card">
                    <a href="product.php?category=Food">
                        <img src="img/Food.png" alt="Food" class="category-image">
                        <h3>Food</h3>
                    </a>
                </div>
                <!-- drinks category -->
                <div class="category-card">
                    <a href="product.php?category=Drink">
                        <img src="img/Drinks.png" alt="Drink" class="category-image">
                        <h3>Drinks</h3>
                    </a>
                </div>
                <!-- beans category -->
                <div class="category-card">
                    <a href="product.php?category=Beans">
                        <img src="img/Beans.png" alt="Beans" class="category-image">
                        <h3>Beans</h3>
                    </a>
                </div>
                <!-- accessories category -->
                <div class="category-card">
                    <a href="product.php?category=Accessories">
                        <img src="img/Accessories.png" alt="Accessories" class="category-image">
                        <h3>Accessories</h3>
                    </a> 
                    <!-- with all categories if clicked will redirect to its relevant category page (product.php?category=?) -->
                </div>
            </div>
        </section>

        <br>

        <!-- loyalty section -->
        <section id="tynebrew-loyalty" class="info-section">
            <h2>Tyne Brew Loyalty</h2>
            <p><a href="loyalty.php">Join Our Loyalty</a> today and collect Tyne Points which you can then redeem for free drinks, exclusive discounts. Become part of the Tyne Brew family today!</p>
        </section><br>
    </main>

    <!-- page footer -->
    <footer class="main-footer">
        <div class="container">
            &copy; 2024 Tyne Brew Coffee. By Khalid A Alsayed (2239321). All rights reserved.
        </div>
    </footer>
</body>
</html>
