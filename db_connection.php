<?php
$host = "localhost";  // XAMPP default
$user = "root";       // Default user in XAMPP (no password by default)
$password = "";       // No password in default XAMPP setup
$database = "rental"; // Your database name

// Create connection
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
