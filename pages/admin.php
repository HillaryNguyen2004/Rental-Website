<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure only admins can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?page=home");
    exit();
}

include 'db_connection.php'; // Include database connection

// Check if database connection is successful
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Fetch admin information
$admin_email = $_SESSION['email'];
$admin_stmt = $conn->prepare("SELECT user_id, name, email, phone FROM users WHERE email = ? AND role = 'admin'");
$admin_stmt->bind_param("s", $admin_email);
$admin_stmt->execute();
$admin_result = $admin_stmt->get_result();
$admin = $admin_result->fetch_assoc();
$admin_stmt->close();

// Fetch all users with role 'user'
$user_stmt = $conn->prepare("SELECT user_id, name, email, phone FROM users WHERE role = 'user'");
$user_stmt->execute();
$user_result = $user_stmt->get_result();

// Arrays to store properties and bookings
$properties = [];
$bookings = [];

// Fetch all properties
$property_stmt = $conn->prepare("SELECT property_id, owner_id, title, description, location, price_per_night, max_guests FROM properties");
$property_stmt->execute();
$property_result = $property_stmt->get_result();
while ($row = $property_result->fetch_assoc()) {
    $properties[$row['owner_id']][] = $row; // Group properties by owner_id
}
$property_stmt->close();

// Fetch all bookings
$booking_stmt = $conn->prepare("SELECT booking_id, user_id, property_id, check_in, check_out, total_price, status, created_at FROM bookings");
$booking_stmt->execute();
$booking_result = $booking_stmt->get_result();
while ($row = $booking_result->fetch_assoc()) {
    $bookings[$row['property_id']][] = $row; // Group bookings by property_id
}
$booking_stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            color:black;
        }
        h2, h3 {
            color: white;
        }
    </style>
</head>
<body>
    <header>
        <!-- <h1>Admin Dashboard</h1> -->
    </header>

    <main>
        <!-- Admin Information -->
        <section>
            <h2>Admin Information</h2>
            <table>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                </tr>
                <tr>
                    <td><?php echo htmlspecialchars($admin['user_id']); ?></td>
                    <td><?php echo htmlspecialchars($admin['name']); ?></td>
                    <td><?php echo htmlspecialchars($admin['email']); ?></td>
                    <td><?php echo htmlspecialchars($admin['phone']); ?></td>
                </tr>
            </table>
        </section>

        <!-- User Information with Properties and Bookings -->
        <section>
            <h2>All Users</h2>
            <?php if ($user_result->num_rows > 0): ?>
                <?php while ($user = $user_result->fetch_assoc()): ?>
                    <h3>User: <?php echo htmlspecialchars($user['name']); ?> (ID: <?php echo $user['user_id']; ?>)</h3>
                    <table>
                        <tr>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                        </tr>
                        <tr>
                            <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                        </tr>
                    </table>

                    <!-- Properties for this User -->
                    <?php if (isset($properties[$user['user_id']])): ?>
                        <h4>Properties Owned</h4>
                        <table>
                            <tr>
                                <th>Property ID</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Location</th>
                                <th>Price/Night</th>
                                <th>Max Guests</th>
                            </tr>
                            <?php foreach ($properties[$user['user_id']] as $property): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($property['property_id']); ?></td>
                                    <td><?php echo htmlspecialchars($property['title']); ?></td>
                                    <td><?php echo htmlspecialchars($property['description']); ?></td>
                                    <td><?php echo htmlspecialchars($property['location']); ?></td>
                                    <td><?php echo htmlspecialchars($property['price_per_night']); ?></td>
                                    <td><?php echo htmlspecialchars($property['max_guests']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>

                        <!-- Bookings for this User's Properties -->
                        <?php foreach ($properties[$user['user_id']] as $property): ?>
                            <?php if (isset($bookings[$property['property_id']])): ?>
                                <h4>Bookings for Property: <?php echo htmlspecialchars($property['title']); ?> (ID: <?php echo $property['property_id']; ?>)</h4>
                                <table>
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>User ID</th>
                                        <th>Check-In</th>
                                        <th>Check-Out</th>
                                        <th>Total Price</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                    </tr>
                                    <?php foreach ($bookings[$property['property_id']] as $booking): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['user_id']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['check_in']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['check_out']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['total_price']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['status']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['created_at']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No properties owned by this user.</p>
                    <?php endif; ?>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No users found with role 'user'.</p>
            <?php endif; ?>
            <?php $user_stmt->close(); ?>
        </section>
    </main>

    <footer>
        <p>© 2025 My Website. All Rights Reserved.</p>
    </footer>
</body>
</html>

<?php
$conn->close();
?>