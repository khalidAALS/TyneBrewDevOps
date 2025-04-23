<?php

require_once 'db.php'; // includes database config and connection
include 'CSP.php'; // adds content security policy for additional security (CSP.php)
include 'session_manager.php'; // manages the users session (session_manager.php)

try {
    $db = new Database();
    $conn = $db->connect();

    // logs the logout action into TblAuditLogs
    if (isset($_SESSION['user_id'], $_SESSION['session_token'])) {
        $user_id = $_SESSION['user_id']; // gets the logged in users ID
        $session_token = $_SESSION['session_token']; // gets the session token
        $ip_address = $_SERVER['REMOTE_ADDR']; // gets the users IP address

        // adds logout action into audit logs TblAuditLogs
        $stmt = $conn->prepare("INSERT INTO TblAuditLogs (user_id, action, ip_address, session_token) VALUES (:user_id, :action, :ip_address, :session_token)");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT); // binds user ID
        $stmt->bindValue(':action', 'User Logged Out', PDO::PARAM_STR); // logs the action
        $stmt->bindParam(':ip_address', $ip_address, PDO::PARAM_STR); // binds IP address
        $stmt->bindParam(':session_token', $session_token, PDO::PARAM_STR); // binds the session token
        $stmt->execute(); // executes the query

        // removes the session entry from TblSessions to make it invalid
        $deleteSession = $conn->prepare("DELETE FROM TblSessions WHERE user_id = :user_id AND session_token = :session_token");
        $deleteSession->bindParam(':user_id', $user_id, PDO::PARAM_INT); // binds user ID
        $deleteSession->bindParam(':session_token', $session_token, PDO::PARAM_STR); // binds the session token
        $deleteSession->execute(); // executes the query
    }
} catch (PDOException $e) { // logs any errors during the logout process
    error_log("Logout error: " . $e->getMessage());
}

//  destroy the session
$_SESSION = []; // clears all session variables
session_unset(); // unsets the session data
session_destroy(); // destroys the session completely to render it invalid ( removes from TblSessions )

// Redirect to the home page (index.php)
header("Location: index.php");
exit();
