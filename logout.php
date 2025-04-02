<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
} // Start the session

// Destroy the session
session_unset(); // Unset all session variables
session_destroy(); // Destroy the session

// Redirect to index.php
header("Location: index.php");
exit();
?>
