<?php
include 'session_manager.php';  // manages user session (session_manager.php)
include 'CSP.php'; // adds security with a content security policy (CSP.php)
require_once 'db.php'; // database config and connection (db.php)

// starts the cart for non-logged-in users 
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// loads the cart from cookie for a non logged in user
if (!isset($_SESSION['user_id'])) {
    // makes the cart persistent for non logged in users
    if (isset($_COOKIE['cart'])) {
        $_SESSION['cart'] = json_decode($_COOKIE['cart'], true);
    }
} else {
    // ensures the cart is always in the session for non logged in users
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

// handles cart items reduction and increment 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['increment'])) {
        $productId = (int)$_POST['product_id'];
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] += 1;  // incraeses quantity
        }
    }

    if (isset($_POST['decrement'])) {
        $productId = (int)$_POST['product_id'];
        if (isset($_SESSION['cart'][$productId])) {
            // If the quantity is greater than 1, one item will be removed otherwise if its only one item the item will be removed.
            if ($_SESSION['cart'][$productId] > 1) {
                $_SESSION['cart'][$productId] -= 1;  // decreases quantity
            } else {
                // removes the item completely when quantity is 1
                unset($_SESSION['cart'][$productId]);
            }
        }
    }

    // updates the cart cookie if the user is not logged in
    if (!isset($_SESSION['user_id'])) {
        setcookie('cart', json_encode($_SESSION['cart']), time() + 3600, "/");  // expires in 1 hour
    }
}

// gets product details for the cart items
$cartItems = [];
if (!empty($_SESSION['cart'])) {
    $ids = implode(',', array_keys($_SESSION['cart'])); // gets product ID in the cart
    $query = "SELECT * FROM TblProducts WHERE id IN ($ids)"; // gets product details
    try {
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC); // gets product info
    } catch (PDOException $e) {
        die("Error fetching cart items: " . $e->getMessage()); // handles query errors
    }
}
// calculates the total price of items in the cart
$totalPrice = 0;
foreach ($cartItems as $product) {
    $productId = $product['id'];
    if (isset($_SESSION['cart'][$productId])) {
        $quantity = (int)$_SESSION['cart'][$productId];
        $price = (float)$product['price'];
        $total = $price * $quantity;
        $totalPrice += $total; // adds to the total price
    }
    //     // Debugging: Ensure each calculation is correct
    //     error_log("Product ID: $productId, Quantity: $quantity, Price: $price, Total: $total");
    // } else {
    //     // Debugging: Log if an item is missing
    //     error_log("Product ID $productId not found in session cart.");
    // }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- page metadata and responsiveness -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Shopping Cart</title>
    <!-- page design and style (style.css) -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- header and nav -->
    <header>
        <h1>Tyne Brew Coffee - Your Cart</h1>
        <nav>
            <!-- nav links to other pages -->
            <a href="index.php">Home</a>
            <a href="loyalty.php">TyneLoyalty</a>
            <a href="product.php">Our Products</a>
            <a href="about_us.php">About Us</a>
            <a href="contact_us.php">Contact Us</a>
            <a href="careers.php">Careers</a>
            <!-- nav links for logged in users -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="view_orders.php">My Orders</a>
                <a href="logout.php">Logout</a>
                <!-- nav links for non logged in users -->
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Signup</a>
            <?php endif; ?>
        </nav>
    </header>
        <!-- page main content -->
    <main>
        <h4>Your Shopping Cart</h4>
        <?php if (empty($cartItems)): ?>
            <!-- message for when the cart is empty -->
            <p>Your cart is empty, <a href="product.php">Shop Here!!</a></p>
        <?php else: ?>
            <!-- items in the cart displayed in a table  -->
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Actions (+add / -remove)</th>
                    </tr>
                </thead>
                <tbody>
                    
                <?php
                $totalPrice = 0;
                foreach ($cartItems as $product):
                    $productId = $product['id'];
                    $quantity = $_SESSION['cart'][$productId];
                    $price = (float)$product['price'];
                    $total = $price * $quantity;
                    $totalPrice += $total;
                ?>
                    <tr>
                        <!-- displays the cart item details -->
                        <td><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>£<?= number_format($price, 2) ?></td>
                        <td><?= $quantity ?></td>
                        <td>£<?= number_format($total, 2) ?></td>
                        <td>
                            <!-- buttons + and - to add or remove from the item quantites -->
                            <form method="POST" action="cart.php" style="display:inline;">
                                <input type="hidden" name="product_id" value="<?= htmlspecialchars($productId, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <button type="submit" name="increment">+</button>
                            </form>
                            <form method="POST" action="cart.php" style="display:inline;">
                                <input type="hidden" name="product_id" value="<?= htmlspecialchars($productId, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <button type="submit" name="decrement">-</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p>Total: £<?= number_format($totalPrice, 2) ?></p>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <!-- prompts the user to login in signup ig they are not logged in -->
                <p>Before proceeding to checkout, please <a href="login.php">Login</a> or <a href="register.php">Signup</a>.</p>
                <a href="login.php">
                    <!-- button " proceed to checkout" -->
                    <button type="button">Proceed to Checkout</button>
                </a>
            <?php else: ?>
                <a href="payment.php">
                    <button type="button">Proceed to Checkout</button>
                </a>
            <?php endif; ?>
        <?php endif; ?>
    </main>
</body>
<!-- page footer  -->
<footer class="main-footer">
        <div class="container">
        &copy; 2024 Tyne Brew Coffee. By Khalid A Alsayed (2239321). All rights reserved.
        </div>
    </footer>
</html>
