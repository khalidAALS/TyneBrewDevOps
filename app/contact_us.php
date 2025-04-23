<?php
include 'session_manager.php'; // managest users session 9session_manager.php
include 'CSP.php'; // enahnced security with a content security policy (CSP.php)
require_once 'db.php'; // database connection and config (db.php)

// db connection
$db = new Database();
$conn = $db->connect();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- metadata and design settings -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <!-- page desing and style (style.css) -->
    <link rel="stylesheet" href="style.css">
    
    <!--leaflet css for interactive  maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
</head>
<body>
    <header>
        <h1>Tyne Brew Coffee - Contact Us</h1>
        <nav>
            <!-- page navigation -->
                <a href="index.php">Home</a>
                <a href="loyalty.php">TyneLoyalty</a>
                <a href="product.php">Our-Products</a>
                <a href="cart.php">Shopping-Cart</a>
                <a href="about_us.php">About Us</a>
                <a href="careers.php">Careers</a>
                <!-- only appears for logged in users -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="logout.php">Logout</a>
                    <a href="view_orders.php">My Orders</a>
                    <!-- only appears for non logged in users -->
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </nav>
            <?php if (isset($_SESSION['username'])): ?>
                <!-- welcome mesage with username -->
    <div class="welcome-message">
        <p>Hello, welcome back <strong><?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?></strong>!</p>
    </div>
<?php endif; ?>

    </header>

    <main>
        <!--  contact info section -->
        <section id="contact-info" class="info-section">
            <h2>Our Contact Information</h2>
            <div class="contact-details">
                <!-- location details -->
                <div class="contact-item">
                    <h3>Visit Us</h3>
                    <p>456 Coffee Street, City Center, Newcastle, NE1233</p>
                    <!-- map container (leaflet) -->
                    <div id="map" style="height: 400px;"></div>
                </div>
                <!-- phone number -->
                <div class="contact-item">
                    <h3>Call Us</h3>
                    <p>+44 0191 1234567</p>
                </div>
                <!-- email address -->
                <div class="contact-item">
                    <h3>Email Us</h3>
                    <p><a href="mailto:info@tynebrew.com">info@tynebrewCoffee.com</a></p>
                </div>
                <!-- working hours and days -->
                <div class="contact-item">
                    <h3>Business Hours</h3>
                    <p>Mon - Sat: 10:00 AM - 11:30 PM</p>
                    <p> Sun: 10:00 AM - 9:00 PM</p>
                </div>
            </div>
        </section>

        <!-- contact form -->
        <section id="contact-form" class="info-section">
            <h2>Send Us a Message</h2>
            <p>If you have any questions, suggestions, or just want to say hello, feel free to get in touch with us. We would love to hear from you!</p>
            <form action="index.php" method="POST">
                <!-- name input -->
                <label for="name">Your Name</label>
                <input type="text" id="name" name="name" required>
                <!-- email input -->
                <label for="email">Your Email</label>
                <input type="email" id="email" name="email" required>
                <!--  users message input -->
                <label for="message">Your Message</label>
                <textarea id="message" name="message" rows="1" required></textarea>

                <!-- hidden CSRF Token for secutiy  -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                <!-- submit ' send message ' button -->
                <button type="submit">Send Message</button>
            </form>
        </section>
    </main>
                <!-- page footer -->
    <footer>
    &copy; 2024 Tyne Brew Coffee. By Khalid A Alsayed (2239321). All rights reserved.
    </footer>

    <!--  leaflet.js script for interactive map -->
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

    <script>
    // map set with the coordinated for newcastle
    var map = L.map('map').setView([54.9742, -1.6159], 15); // Coordinates for Newcastle upon Tyne

    // street view layer on map
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // adds the marker on the map to pinpoint location
    L.marker([54.9742, -1.6150]).addTo(map)
        .bindPopup('<b>Tyne Brew</b><br>Newcastle upon Tyne')
        .openPopup();
</script>

</body>
</html>
