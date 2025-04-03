<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'db_connection.php';

// Ensure user is logged in
$userEmail = $_SESSION['email'] ?? null;
if (!$userEmail) {
    die("<script>alert('Please log in to add a property.'); window.location.href='index.php?page=Login';</script>");
}

// Fetch the owner_id based on session email
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows > 0) {
    $userRow = $result->fetch_assoc();
    $owner_id = $userRow['user_id'];
} else {
    die("<script>alert('User not found.'); window.location.href='index.php?page=home';</script>");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = htmlspecialchars(trim($_POST['title']));
    $description = htmlspecialchars(trim($_POST['description']));
    $location = htmlspecialchars(trim($_POST['location']));
    $price_per_night = (float) $_POST['price_per_night'];
    $max_guests = (int) $_POST['max_guests'];
    $created_at = date('Y-m-d H:i:s');

    // Handle file upload
    $photoPath = "";
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "photo/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        // Replace spaces with underscores in the title for file name
        $safeTitle = str_replace(" ", "_", strtolower($title));  // Replace spaces with underscores
        $photoExt = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photoPath = $targetDir . $safeTitle . "." . $photoExt;
        
        // Move the uploaded file
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath)) {
            die("<script>alert('Error uploading photo.'); window.location.href='index.php?page=home';</script>");
        }
    }

    // Insert property into database
    $stmt = $conn->prepare("INSERT INTO properties (owner_id, title, description, location, price_per_night, max_guests, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssdis", $owner_id, $title, $description, $location, $price_per_night, $max_guests, $created_at);
    
    if ($stmt->execute()) {
        echo "<script>alert('Property added successfully!'); window.location.href='index.php?page=home';</script>";
    } else {
        echo "<script>alert('Error adding property: " . $conn->error . "');</script>";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Property</title>
</head>
<body>
    <h2>Add Property</h2>
    <form method="POST" action="" enctype="multipart/form-data">
        <label>Title:</label>
        <input type="text" name="title" required><br>
        
        <label>Description:</label>
        <textarea name="description" required></textarea><br>
        
        <label>Location:</label>
        <input type="text" name="location" required><br>
        
        <label>Price per Night ($):</label>
        <input type="number" name="price_per_night" step="0.01" required><br>
        
        <label>Max Guests:</label>
        <input type="number" name="max_guests" required><br>
        
        <label>Upload Photo:</label>
        <input type="file" name="photo" accept="image/*" required><br>
        
        <button type="submit">Add Property</button>
    </form>
</body>
</html>
