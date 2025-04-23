<?php
include 'session_manager.php'; // manages users session (session_manager.php)
include 'CSP.php'; // adds content security policy for extra security (CSP.php)
require_once 'db.php'; // config and connection to database (db.php)

try {
    $db = new Database();
    $conn = $db->connect();

    // gets search query and search filter
    $searchQuery = isset($_GET['search']) ? htmlspecialchars($_GET['search'], ENT_QUOTES, 'UTF-8') : null;
    $categoryFilter = isset($_GET['category']) ? htmlspecialchars($_GET['category'], ENT_QUOTES, 'UTF-8') : null;

    if ($categoryFilter && $categoryFilter !== '') {
        // gets products from the selected category with SQLi protection using prepared statements
        $query = "SELECT * FROM TblProducts WHERE category = :category";
        if ($searchQuery) {
            $query .= " AND name LIKE :search"; // includes search filter
        }
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':category', $categoryFilter);
        if ($searchQuery) {
            $searchTerm = "%$searchQuery%";
            $stmt->bindParam(':search', $searchTerm);
        }
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // shows all products when no category is selected with SQLi protection using prepared statements
        $query = "SELECT * FROM TblProducts";
        if ($searchQuery) {
            $query .= " WHERE name LIKE :search"; //  search filter
        }
        $stmt = $conn->prepare($query);
        if ($searchQuery) {
            $searchTerm = "%$searchQuery%";
            $stmt->bindParam(':search', $searchTerm);
        }
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage(); // handles databsse errors
    die();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- page metadata and responsive design -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Products</title>
    <link rel="stylesheet" href="style.css">
    <script>
        // toggle visability for product descriptions
        function toggleDescription(id) {
            var desc = document.getElementById(id);
            if (desc.style.display === "none") {
                desc.style.display = "block";
            } else {
                desc.style.display = "none";
            }
        }
        // add a product to the cart using AJAX
        function addToCart(productId, productName) {
            var quantity = document.getElementById('quantity-' + productId).value;
            var csrfToken = '<?= $_SESSION["csrf_token"] ?>'; // This must output the CSRF token from PHP to JS

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
                    }, 3000);
                }
            };

            // sends the data including  CSRF token, product ID and the quantity
            xhr.send("product_id=" + productId + "&quantity=" + quantity + "&csrf_token=" + encodeURIComponent(csrfToken));
        }
    </script>
</head>
<body>
<body>
    <h1>Our Products</h1>

    <!-- page header section -->
    <header>
        <h1>Tyne Brew Coffee - Product Range</h1>
        <!-- nav bar -->
        <nav>
            <a href="index.php">Home</a>
            <a href="loyalty.php">TyneLoyalty</a>
            <a href="cart.php">Shopping-Cart</a>
            <a href="about_us.php">About Us</a>
            <a href="contact_us.php">Contact Us</a>
            <a href="careers.php">Careers</a>
            <!-- only shows for logged in users -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php">Logout</a>
                <a href="view_orders.php">My Orders</a>
            <?php else: ?>
                <!-- shows for non logged in users -->
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
            <!-- only shows for admin users -->
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="admin.php">Admin-Dashboard</a>
                <?php endif; ?>
        </nav>
        <?php if (isset($_SESSION['username'])): ?>
            <div class="welcome-message">
                <p>Hello, welcome back <strong><?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?></strong>!</p>
            </div>
        <?php endif; ?>
    </header>
            <!-- page main content -->
    <main>
        <!-- Ssearch form -->
        <form method="GET" action="product.php" class="search-form">
            <input type="text" name="search" placeholder="Search for products..." value="<?= htmlspecialchars($searchQuery ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <select name="category">
                <option value="">All Categories</option>
                <option value="Drink" <?= $categoryFilter === 'Drink' ? 'selected' : '' ?>>Drinks</option>
                <option value="Food" <?= $categoryFilter === 'Food' ? 'selected' : '' ?>>Food</option>
                <option value="Beans" <?= $categoryFilter === 'Beans' ? 'selected' : '' ?>>Beans</option>
                <option value="Accessories" <?= $categoryFilter === 'Accessories' ? 'selected' : '' ?>>Accessories</option>
            </select>
            <!-- button ' search' -->
            <button type="submit">Search</button>
        </form>
            <!-- product list -->
        <section id="products">
            <?php if ($categoryFilter): ?>
                <h4>Products in <?= htmlspecialchars($categoryFilter, ENT_QUOTES, 'UTF-8') ?> Category</h4>
            <?php else: ?>
                <h4>All Products</h4>
            <?php endif; ?>

            <div class="product-grid">
                <?php if (empty($items)): ?>
                    <p>No products found.</p>
                <?php else: ?>
                    <?php foreach ($items as $product): ?>
                        <div class="product-card">
                            <img src="<?= htmlspecialchars($product['image_url'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>">
                            <h3><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p class="product-description" onclick="toggleDescription('product-description-<?= $product['id'] ?>')">
                                Description
                            </p>
                            <div id="product-description-<?= $product['id'] ?>" class="detailed-description" style="display:none;">
                                <p><?= htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8') ?></p>
                                <a href="product_details.php?id=<?= $product['id'] ?>" class="learn-more">Learn More</a>
                            </div>
                            <p><strong>Price:</strong> £<?= htmlspecialchars($product['price'], ENT_QUOTES, 'UTF-8') ?></p>
                            <!-- product quantity selection min 1 max 10 per addition to cart-->
                            <label for="quantity-<?= $product['id'] ?>">Quantity:</label>
                            <select id="quantity-<?= $product['id'] ?>" name="quantity">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                                <option value="10">10</option>
                            </select><br>
                            <button onclick="addToCart(<?= $product['id'] ?>, '<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>')">Add to Cart</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>
                        <!-- page footer -->
    <footer>
        &copy; 2024 Tyne Brew Coffee. By Khalid A Alsayed (2239321). All rights reserved.
    </footer>
</body>
</html>
