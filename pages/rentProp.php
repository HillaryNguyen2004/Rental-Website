<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'db_connection.php';

if (!$conn) {
    die("Database connection failed.");
}

$userEmail = $_SESSION['email'] ?? null;
if (!$userEmail) {
    echo "<div class='login-container'><h2>Please Log In</h2><p>You need to be logged in to rent a property. <a href='index.php?page=Login'>Click here to log in</a>.</p></div>";
    exit();
}

$result = $conn->query("SELECT property_id, title, price_per_night, max_guests FROM properties");
if (!$result) {
    die("Error fetching properties.");
}
$properties = $result->fetch_all(MYSQLI_ASSOC);

$bookingDetails = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['PhoneNum']);
    $property_title = mysqli_real_escape_string($conn, $_POST['property']);
    $checkin = mysqli_real_escape_string($conn, $_POST['checkin']);
    $checkout = mysqli_real_escape_string($conn, $_POST['checkout']);
    $guests = (int)$_POST['guests'];

    $userQuery = "SELECT user_id FROM users WHERE email = '$userEmail'";
    $userResult = $conn->query($userQuery);
    if ($userResult->num_rows > 0) {
        $userRow = $userResult->fetch_assoc();
        $user_id = $userRow['user_id'];
    } else {
        $bookingDetails = "Error: User not found.";
        goto display;
    }

    $propertyQuery = "SELECT property_id, max_guests, price_per_night FROM properties WHERE title = '$property_title'";
    $propertyResult = $conn->query($propertyQuery);
    if ($propertyResult->num_rows > 0) {
        $propertyRow = $propertyResult->fetch_assoc();
        $property_id = $propertyRow['property_id'];
        $max_guests = $propertyRow['max_guests'];
        $price_per_night = $propertyRow['price_per_night'];
    } else {
        $bookingDetails = "Error: Property not found.";
        goto display;
    }

    if ($guests > $max_guests) {
        $bookingDetails = "Error: Number of guests ($guests) exceeds property maximum capacity ($max_guests).";
        goto display;
    }

    $checkinDate = new DateTime($checkin);
    $checkoutDate = new DateTime($checkout);
    if ($checkinDate >= $checkoutDate) {
        $bookingDetails = "Error: Check-out date must be after check-in date.";
        goto display;
    }

    $interval = $checkinDate->diff($checkoutDate);
    $total_price = $interval->days * $price_per_night;

    $overlapQuery = "SELECT booking_id FROM bookings WHERE property_id = '$property_id' AND ((check_in <= '$checkout' AND check_out >= '$checkin'))";
    $overlapResult = $conn->query($overlapQuery);
    if ($overlapResult->num_rows > 0) {
        $bookingDetails = "Error: Property is already booked for these dates.";
        goto display;
    }

    $insertQuery = "INSERT INTO bookings (user_id, property_id, check_in, check_out, total_price, status, created_at) VALUES ('$user_id', '$property_id', '$checkin', '$checkout', '$total_price', 'confirmed', NOW())";
    if ($conn->query($insertQuery)) {
        // $bookingDetails = "Booking successful!";
        echo "<script>alert('Booking successful!');</script>";
    } else {
        $bookingDetails = "Error creating booking.";
    }

    display:
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent a Property</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .notification {
            color: white;
            font-size: 1.6rem;
            margin-top: 10px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Rent a Property</h2>
        <form method="POST">
            <input type="text" name="name" placeholder="Your Name" required>
            <input type="email" name="email" placeholder="Your Email" required>
            <input type="text" name="PhoneNum" placeholder="Your Phone Number" required>
            <select name="property" id="property" required>
                <option value="">-- Select Property --</option>
                <?php foreach ($properties as $property): ?>
                    <option value="<?php echo htmlspecialchars($property['title']); ?>" data-max-guests="<?php echo $property['max_guests']; ?>">
                        <?php echo htmlspecialchars($property['title']) . " - $" . htmlspecialchars($property['price_per_night']) . "/night"; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="checkin" required>
            <input type="date" name="checkout" required>
            <select name="guests" id="guests" required>
                <option value="">-- Select Number of Guests --</option>
                <?php for ($i = 1; $i <= 20; $i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
            <div id="guest-notification" class="notification"></div>
            <button type="submit" id="submit-btn">Rent Now</button>
        </form>
            <?php if (!empty($bookingDetails)): ?>
                <div class="booking-details">
                    <?php echo $bookingDetails; ?>
                </div>
        <?php endif; ?>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const propertySelect = document.getElementById('property');
            const guestSelect = document.getElementById('guests');
            const notification = document.getElementById('guest-notification');
            const submitBtn = document.getElementById('submit-btn');

            propertySelect.addEventListener('change', function() {
                const maxGuests = parseInt(this.options[this.selectedIndex].getAttribute('data-max-guests')) || 20;
                guestSelect.innerHTML = '<option value="">-- Select Number of Guests --</option>';
                for (let i = 1; i <= maxGuests; i++) {
                    guestSelect.innerHTML += `<option value="${i}">${i}</option>`;
                }
                checkGuests(maxGuests);
            });

            guestSelect.addEventListener('change', function() {
                const maxGuests = parseInt(propertySelect.options[propertySelect.selectedIndex].getAttribute('data-max-guests')) || 20;
                checkGuests(maxGuests);
            });

            function checkGuests(maxGuests) {
                const selectedGuests = parseInt(guestSelect.value) || 0;
                if (selectedGuests > maxGuests) {
                    notification.textContent = `Error: Number of guests (${selectedGuests}) exceeds maximum capacity (${maxGuests}).`;
                    notification.style.display = 'block';
                    submitBtn.disabled = true; // Disable submit button
                } else {
                    notification.style.display = 'none';
                    submitBtn.disabled = false; // Enable submit button
                }
            }
        });
    </script>
</body>
</html>