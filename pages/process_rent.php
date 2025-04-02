<?php
session_start();
include 'db_connection.php'; // Include your MySQLi connection

// Check if database connection is successful
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Check if user is logged in
$userEmail = isset($_SESSION['email']) ? $_SESSION['email'] : null;
if (!$userEmail) {
    header("Location: index.php?page=Login");
    exit();
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and get form data
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['PhoneNum']);
    $property_title = mysqli_real_escape_string($conn, $_POST['property']);
    $checkin = mysqli_real_escape_string($conn, $_POST['checkin']);
    $checkout = mysqli_real_escape_string($conn, $_POST['checkout']);
    $guests = (int)$_POST['guests'];

    // Get user_id from users table based on session email
    $userQuery = "SELECT user_id FROM users WHERE email = '$userEmail'";
    $userResult = $conn->query($userQuery);
    
    if ($userResult && $userResult->num_rows > 0) {
        $userRow = $userResult->fetch_assoc();
        $user_id = $userRow['user_id'];
    } else {
        die("Error: User not found");
    }

    // Get property_id from properties table based on title
    $propertyQuery = "SELECT property_id, max_guests FROM properties WHERE title = '$property_title'";
    $propertyResult = $conn->query($propertyQuery);
    
    if ($propertyResult && $propertyResult->num_rows > 0) {
        $propertyRow = $propertyResult->fetch_assoc();
        $property_id = $propertyRow['property_id'];
        $max_guests = $propertyRow['max_guests'];
    } else {
        die("Error: Property not found");
    }

    // Validate number of guests
    if ($guests > $max_guests) {
        die("Error: Number of guests exceeds property maximum capacity");
    }

    // Validate dates
    $checkinDate = new DateTime($checkin);
    $checkoutDate = new DateTime($checkout);
    $currentDate = new DateTime();
    
    if ($checkinDate >= $checkoutDate) {
        die("Error: Check-out date must be after check-in date");
    }
    if ($checkinDate < $currentDate) {
        die("Error: Check-in date cannot be in the past");
    }

    // Check for existing bookings (prevent double booking)
    $overlapQuery = "SELECT booking_id FROM bookings 
                    WHERE property_id = '$property_id' 
                    AND (
                        (checkin_date <= '$checkout' AND checkout_date >= '$checkin')
                    )";
    $overlapResult = $conn->query($overlapQuery);
    
    if ($overlapResult && $overlapResult->num_rows > 0) {
        die("Error: Property is already booked for these dates");
    }

    // Insert booking into bookings table with AUTO_INCREMENT (assuming booking_id is set as AUTO_INCREMENT)
    $insertQuery = "INSERT INTO bookings (user_id, property_id, checkin_date, checkout_date, guests, name, email, phone_number) 
                    VALUES ('$user_id', '$property_id', '$checkin', '$checkout', '$guests', '$name', '$email', '$phone')";
    
    if ($conn->query($insertQuery) === TRUE) {
        $booking_id = $conn->insert_id; // Get the auto-generated booking_id
        header("Location: booking_confirmation.php?booking_id=$booking_id");
        exit();
    } else {
        die("Error creating booking: " . $conn->error);
    }
} else {
    // If not POST request, redirect back to form
    header("Location: rent_property.php");
    exit();
}

$conn->close();
?>