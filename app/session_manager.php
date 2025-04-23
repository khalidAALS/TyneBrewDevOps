<?php
require_once 'db.php'; // connection and config for database (db.php)
include 'CSP.php'; // enhances security through a content security policy (CSP.php)

// exludes validation for login (login.php) and signup (register.php)
$excluded_pages = ['login.php', 'register.php'];
$current_page = basename($_SERVER['PHP_SELF']);

if (in_array($current_page, $excluded_pages)) {
    return; // skips session validation 
}

// secure session settings to enhance security
ini_set('session.use_only_cookies', 1); // uses cookies only and no URL based sessions
ini_set('session.use_strict_mode', 1); // prevents the reuse of a session ID
ini_set('session.cookie_httponly', 1); // restricts cookie access to HTTPS only
ini_set('session.cookie_secure', 1); // requires https for cookies
ini_set('session.cookie_samesite', 'Strict'); // prevents cross site usage of cookies

//cofigures session cookie paramaters
$lifetime = 1800; // 30 minute session lifetime
session_set_cookie_params([
    'lifetime' => $lifetime, // set cookie expiration time
    'path' => '/', // cookies available to all pages
    'domain' => 'sencldigitech.co.uk/kalsayed',  // sets the domain for cookies
    'secure' => isset($_SERVER['HTTPS']), // enforces secure flag if HTTPS is used
    'httponly' => true, // restrict access to cookies via HTTPS only
    'samesite' => 'Strict' // prevents cross orgin usage of cookies
]);


session_start(); // starts/resumes the session

// CSRF token management for logged in users
if (isset($_SESSION['user_id'])) {
    // CSRF protection by validating token on POST request
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die("CSRF token validation failed"); // terinates on token mismatch
        }
    }

    // CSRF token generation if not already set
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // generates a 32-byte secure token
    }
} else {
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}
// tracks session creation time for expiration handling
if (!isset($_SESSION['createdAt'])) {
    $_SESSION['createdAt'] = time(); // records the session creation time stamp
}
// starts the database connection
$db = new Database();
$conn = $db->connect(); // connection to database
?>
