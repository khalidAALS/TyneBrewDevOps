<?php
include 'session_manager.php'; // manages the users session (session_manager.php)
include 'CSP.php'; // enhanced security with a content security policy (CSP.php)
require_once 'db.php'; //   database connection and config (db.php)

                            

// Database class and establishs a connection
$db = new Database();
$conn = $db->connect();

// gets product details for the cart  TblProducts
$cartItems = [];
if (!empty($_SESSION['cart'])) {
    $ids = implode(',', array_keys($_SESSION['cart']));
    $query = "SELECT * FROM TblProducts WHERE id IN ($ids)";
    try {
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error fetching cart items: " . $e->getMessage());
    }
}

// calculates the total price
$totalPrice = 0;
foreach ($cartItems as $product) {
    $quantity = $_SESSION['cart'][$product['id']];
    $price = (float)$product['price'];
    $totalPrice += $price * $quantity;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- page metadata and settings -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Tyne Brew Coffee</title>
    <!-- page design and style (style.css) -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Tyne Brew Coffee - Payment</h1>
        <nav>
            <!-- page navigation -->
            <a href="index.php">Home</a>
            <a href="loyalty.php">TyneLoyalty</a>
            <a href="product.php">Our Products</a>
            <a href="about_us.php">About Us</a>
            <a href="contact_us.php">Contact Us</a>
            <a href="careers.php">Careers</a>
            <!-- only appears to logged in users -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php">Logout</a>
                <!-- only appears to logged out users -->
            <?php else: ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </nav>
        <?php if (isset($_SESSION['username'])): ?>
            <div class="welcome-message">
                <!-- welcome message with username -->
                <p>Hello, welcome back <strong><?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?></strong>!</p>
            </div>
        <?php endif; ?>
    </header>
    <main>
        <!-- items appear in the cart in a table -->
        <h4>Checkout</h4>
        <form action="process_payment.php" method="POST" id="payment-form">
            <h3>Your Cart Items11111</h3>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $product): ?>
                    <tr>
                        <!-- displays the items added by name, quantitiy, price, and total price  -->
                        <td><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>£<?= number_format($product['price'], 2) ?></td>
                        <td><?= $_SESSION['cart'][$product['id']] ?></td>
                        <td>£<?= number_format($product['price'] * $_SESSION['cart'][$product['id']], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p><strong>Total: £<?= number_format($totalPrice, 2) ?></strong></p>
                        <!-- form fields for billing and payment -->
            <h3>Billing Information</h3>
            <!-- firnamt input field -->
            <div class="input-container">
                <label for="first_name">First Name:</label>
                <input type="text" name="first_name" id="first_name" required>
                <div class="error-message" id="first_name_error"></div>
            </div>
                        <!-- surname input field -->
            <div class="input-container">
                <label for="surname">Surname:</label>
                <input type="text" name="surname" id="surname" required>
                <div class="error-message" id="surname_error"></div>
            </div>
                        
            <h3>Address</h3>
            <!-- house number input field -->
            <div class="input-container">
                <label for="house_number">House Number:</label>
                <input type="text" name="house_number" id="house_number" required>
                <div class="error-message" id="house_number_error"></div>
            </div>
                        <!-- street name input field -->
            <div class="input-container">
                <label for="street_name">Street Name:</label>
                <input type="text" name="street_name" id="street_name" required>
                <div class="error-message" id="street_name_error"></div>
            </div>
                        <!-- post code input field -->
            <div class="input-container">
                <label for="postcode">Postcode:</label>
                <input type="text" name="postcode" id="postcode" required>
                <div class="error-message" id="postcode_error"></div>
            </div>

            <h3>Card Details</h3>
            <!-- card number input field -->
            <div class="input-container">
                <label for="card_number">Card Number (16 digits):</label>
                <input type="text" name="card_number" id="card_number" maxlength="16" required>
                <div class="error-message" id="card_number_error"></div>
            </div>
                        <!-- expiriy date input field -->
            <div class="input-container">
                <label for="expiry_date">Expiry Date (MM/YY):</label>
                <input type="text" name="expiry_date" id="expiry_date" maxlength="5" placeholder="MM/YY" required>
                <div class="error-message" id="expiry_date_error"></div>
            </div>
                        <!-- CVV input field -->
            <div class="input-container">
                <label for="cvv">CVV:</label>
                <input type="text" name="cvv" id="cvv" maxlength="3" required>
                <div class="error-message" id="cvv_error"></div>
            </div>
                        <!-- hidden CSRF token in form for security -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                        <!-- submit 'pay' button -->
            <button type="submit" name="pay">Pay</button>
        </form>
    </main>
    <!-- page footer -->
    <footer>
        &copy; 2024 Tyne Brew Coffee. By Khalid A Alsayed (2239321). All rights reserved.
    </footer>

    <script>
        document.getElementById('payment-form').addEventListener('submit', function (e) {
    e.preventDefault(); // prevents  default form submission

    console.log('Submit button clicked'); // Debugging

    // clears previous error messages
    const errorMessages = document.querySelectorAll('.error-message');
    errorMessages.forEach(error => {
        error.classList.remove('show'); // hides error messages
        error.innerText = ''; // clears error text
    });

    let isValid = true; // tracks form validity

    // defines validation rules for input fields
    const fields = [
        { id: 'first_name', regex: /^[a-zA-Z]+$/, error: 'First name can only contain letters.' }, // firname can only contain letters
        { id: 'surname', regex: /^[a-zA-Z]+$/, error: 'Surname can only contain letters.' }, // surname can only contain letters
        { id: 'house_number', regex: /^\d+$/, error: 'House number can only contain numbers.' }, // house number can only contain numbers
        { id: 'street_name', regex: /^[a-zA-Z\s]+$/, error: 'Street name can only contain letters and spaces.' }, // street name can only contain letters
        { id: 'postcode', regex: /^[a-zA-Z0-9]+$/, error: 'Postcode can only contain letters and numbers.' }, // postcode can only contain letters and numbers
        { id: 'card_number', regex: /^\d{16}$/, error: 'Card number must be exactly 16 digits.' }, // card number can only contain numbers (16 digits)
        { id: 'cvv', regex: /^\d{3}$/, error: 'CVV must be exactly 3 digits.' } // CVV can only contain numbers (3 digits)
    ];

    // validates the fields
    fields.forEach(field => {
        const input = document.getElementById(field.id);
        const errorElement = document.getElementById(`${field.id}_error`);
        const value = input ? input.value.trim() : '';

        console.log(`Validating ${field.id}: "${value}"`); // Debugging

        if (!field.regex.test(value)) {
            if (errorElement) {
                errorElement.innerText = field.error; // sets error text
                errorElement.classList.add('show'); // makes error visible
                console.log(`Error for ${field.id}: ${field.error}`); // Debugging
            } else {
                console.error(`Error element not found for: ${field.id}`); // Debugging
            }
            isValid = false; // marks form as invalid
        }
    });

    // validates the expiry Date
    const expiryDate = document.getElementById('expiry_date').value;
    const expiryParts = expiryDate.split('/'); // splits dat with /
    if (expiryParts.length !== 2 || !/^\d{2}\/\d{2}$/.test(expiryDate)) {
        const errorElement = document.getElementById('expiry_date_error'); // error message
        errorElement.innerText = 'Expiry date must be in MM/YY format.'; // expiry date must be MM/YY format
        errorElement.classList.add('show');
        isValid = false;
    } else {
        const month = parseInt(expiryParts[0], 10); // MM can only be from 01 to 12
        const year = parseInt(expiryParts[1], 10) + 2000; // YY can be current year or in the future
        const currentDate = new Date();
        const currentMonth = currentDate.getMonth() + 1; // gets current year
        const currentYear = currentDate.getFullYear(); // gets current month
        // triggers expiry date error if in the past
        if (month < 1 || month > 12 || year < currentYear || (year === currentYear && month < currentMonth)) {
            const errorElement = document.getElementById('expiry_date_error');
            errorElement.innerText = 'Expiry date must be today or in the future.';
            errorElement.classList.add('show');
            isValid = false;
        }
    }

    console.log('Form valid:', isValid); // Debugging

    if (isValid) {
        this.submit(); // submits the form if everything is valid
    }
});




    </script>
</body>
</html>
