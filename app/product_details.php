<?php
include 'session_manager.php'; // manages the users session (session_manager.php)
include 'CSP.php'; // increased security with a content security policy (CSP.php)
require_once 'db.php'; // config and connection for database(db.php)

try {
    $db = new Database(); // initiates databse class
    $conn = $db->connect(); // starts the database connection

    // gets the product ID from the URL
    $productId = isset($_GET['id']) ? (int)$_GET['id'] : 0; // makes sure the ID is an integer

    if ($productId) {
        // gets product details based on the ID with SQLi protection with prepared statement
        $query = "SELECT * FROM TblProducts WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC); // gets product data

        if (!$product) {
            // handles cases if no products matches the ID
            echo "Product not found!";
            exit;
        }
    } else {
        // handles invalid or missing product ID
        echo "Invalid product ID!";
        exit;
    }
} catch (PDOException $e) {
    // handles databse connection and or query errors
    echo "Error: " . $e->getMessage();
    die();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- metadata and responsiveness settings -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?> - Product Details</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- header section -->
    <header>
        <h1>Tyne Brew Coffee - Product Details</h1>
        <!-- nav bar -->
        <nav>
            <a href="index.php">Home</a>
            <a href="loyalty.php">TyneLoyalty</a>
            <a href="cart.php">Shopping-Cart</a>
            <a href="about_us.php">About Us</a>
            <a href="contact_us.php">Contact Us</a>
            <a href="careers.php">Careers</a>
            <!-- only shows if user is logged in -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php">Logout</a>
                <a href="view_orders.php">My Orders</a>
            <?php else: ?>
                <!-- only shows if user is not logged in -->
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
            <!-- only shows to admin users -->
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="admin.php">Admin-Dashboard</a>
                <?php endif; ?>
        </nav>
        <?php if (isset($_SESSION['username'])): ?>
        <div class="welcome-message">
            <!-- welcome message which includes the username -->
            <p>Hello, welcome back <strong><?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?></strong>!</p>
        </div>
        <?php endif; ?>
    </header>
            <!-- page main content -->
    <main>
        <!-- displays product details -->
        <section id="product-details">
            <h4><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h4>
            <!-- Return to Products Button -->
            <button onclick="window.location.href='product.php?category=<?= urlencode($product['category']) ?>'">Return to Products</button><br>
            <!-- product image -->
            <img src="<?= htmlspecialchars($product['image_url'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>" class="product-image">
            <!-- product price -->
            <p><strong>Price:</strong> £<?= htmlspecialchars(number_format($product['price'], 2), ENT_QUOTES, 'UTF-8') ?></p>
                <!-- product detailed discription -->
            <?php if (!empty($product['detailed_description'])): ?>
            <p><strong>Detailed Description:</strong> <?= htmlspecialchars($product['detailed_description'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
                <!-- product dimensions -->
            <?php if (!empty($product['dimensions'])): ?>
            <p><strong>Dimensions:</strong> <?= htmlspecialchars($product['dimensions'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
                <!-- product nutrition facts -->
            <?php if (!empty($product['nutrition_facts'])): ?>
            <p><strong>Nutrition Facts:</strong> <?= htmlspecialchars($product['nutrition_facts'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <!-- add to Cart Form -->
            <label for="quantity">Quantity:</label>
            <select id="quantity" name="quantity">
                <?php for ($i = 1; $i <= 10; $i++): ?>
                <option value="<?= $i ?>"><?= $i ?></option>
                <?php endfor; ?>
            </select><br>

            <button onclick="addToCart(<?= $product['id'] ?>, '<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>')">Add to Cart</button>
        </section>
    </main>
                    <!-- page footer section -->
    <footer>
        &copy; 2024 Tyne Brew Coffee. By Khalid A Alsayed (2239321). All rights reserved.
    </footer>

    <script>
        // adds products to the cart with AJAX
        function addToCart(productId, productName) {
            var quantity = document.getElementById('quantity').value;
            var csrfToken = '<?= $_SESSION["csrf_token"] ?>'; // includes CSRF token for security

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "add_to_cart.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    const response = xhr.responseText;
                    const notification = document.createElement('div');
                    notification.className = 'cart-notification';
                    notification.textContent = response;
                    document.body.appendChild(notification);

                    setTimeout(() => {
                        document.body.removeChild(notification);
                    }, 3000); //auto removes the notification 
                }
            };

            // Sends the CSRF token along with the product ID and quantity
            xhr.send("product_id=" + productId + "&quantity=" + quantity + "&csrf_token=" + encodeURIComponent(csrfToken));
        }
    </script>
</body>
</html>
