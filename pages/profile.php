<?php
include 'db_connection.php';

$user_id = $_SESSION['user_id'];

// Handle deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $deleteQuery = $conn->prepare("DELETE FROM properties WHERE property_id = ? AND owner_id = ?");
    $deleteQuery->bind_param("ii", $delete_id, $user_id);
    if ($deleteQuery->execute()) {
        header("Location: index.php?page=profile");
        exit();
    } else {
        echo "Error deleting property: " . $conn->error;
    }
    $deleteQuery->close();
}

// Fetch user details
$userQuery = $conn->prepare("SELECT name, email, phone FROM users WHERE user_id = ?");
$userQuery->bind_param("i", $user_id);
$userQuery->execute();
$userResult = $userQuery->get_result();
$user = $userResult->fetch_assoc();
$userQuery->close();

// Fetch properties
$propertyQuery = $conn->prepare("SELECT property_id, title, description, location, price_per_night, max_guests FROM properties WHERE owner_id = ?");
$propertyQuery->bind_param("i", $user_id);
$propertyQuery->execute();
$propertyResult = $propertyQuery->get_result();

// Fetch bookings for user's properties
$bookingQuery = $conn->prepare("
    SELECT b.booking_id, b.property_id, b.check_in, b.check_out, b.total_price, b.status, b.created_at, p.title
    FROM bookings b
    JOIN properties p ON b.property_id = p.property_id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$bookingQuery->bind_param("i", $user_id);
$bookingQuery->execute();
$bookingResult = $bookingQuery->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <div class="container">
        <h2>User Profile</h2>
        <div class="user-info">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
        </div>

        <h2>Properties Owned</h2>
        <?php if ($propertyResult->num_rows > 0) { ?>
            <div class="property-table">
                <div class="table-header">
                    <span>Title</span>
                    <span>Description</span>
                    <span>Location</span>
                    <span>Price/Night</span>
                    <span>Max Guests</span>
                    <span>Actions</span>
                </div>
                <?php while ($property = $propertyResult->fetch_assoc()) { ?>
                    <div class="table-row">
                        <?php
                        $titleForImage = str_replace(' ', '_', $property['title']);
                        $jpgPath = "./photo/" . $titleForImage . ".jpg";
                        $pngPath = "./photo/" . $titleForImage . ".png";
                        $imagePath = file_exists($jpgPath) ? $jpgPath : (file_exists($pngPath) ? $pngPath : null);
                        ?>
                        <span><?php echo htmlspecialchars($property['title']); ?></span>
                        <span><?php echo htmlspecialchars($property['description']); ?></span>
                        <span><?php echo htmlspecialchars($property['location']); ?></span>
                        <span>$<?php echo htmlspecialchars($property['price_per_night']); ?></span>
                        <span><?php echo htmlspecialchars($property['max_guests']); ?></span>
                        <span class="actions">
                            <form action="index.php?page=edit" method="POST" style="display: inline;">
                                <input type="hidden" name="property_id" value="<?php echo $property['property_id']; ?>">
                                <button type="submit" class="btn edit-btn">Edit</button>
                            </form>
                            <a href="index.php?page=profile&delete=<?php echo $property['property_id']; ?>" 
                               onclick="return confirm('Are you sure you want to delete this property?');" 
                               class="btn delete-btn">Delete</a>
                        </span>
                    </div>
                    <?php if ($imagePath): ?>
                        <div class="table-image">
                            <img src="<?php echo $imagePath; ?>" alt="Property Image" class="property-image">
                        </div>
                    <?php else: ?>
                        <div class="table-image">
                            <p><em>No image available.</em></p>
                        </div>
                    <?php endif; ?>
                <?php } ?>
            </div>
        <?php } else { ?>
            <p>No properties owned.</p>
        <?php } ?>

        <h2>Your Current Bookings</h2>
        <?php if ($bookingResult->num_rows > 0) { ?>
            <div class="booking-table">
                <div class="table-header">
                    <!-- <span>Booking ID</span> -->
                    <span>Property</span>
                    <span>Check-in</span>
                    <span>Check-out</span>
                    <span>Total Price</span>
                    <span>Status</span>
                    <span>Created</span>
                </div>
                <?php while ($booking = $bookingResult->fetch_assoc()) { ?>
                    <div class="table-row">
                        <span><?php echo htmlspecialchars($booking['title']); ?></span>
                        <span><?php echo htmlspecialchars($booking['check_in']); ?></span>
                        <span><?php echo htmlspecialchars($booking['check_out']); ?></span>
                        <span>$<?php echo htmlspecialchars($booking['total_price']); ?></span>
                        <span><?php echo htmlspecialchars($booking['status']); ?></span>
                        <span><?php echo htmlspecialchars($booking['created_at']); ?></span>
                    </div>
                <?php } ?>
            </div>
        <?php } else { ?>
            <p>No bookings found for your properties.</p>
        <?php } ?>

        <?php
        $propertyQuery->close();
        $bookingQuery->close();
        $conn->close();
        ?>
    </div>
</body>
</html>