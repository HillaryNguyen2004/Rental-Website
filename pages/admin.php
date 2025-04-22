<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?page=home");
    exit();
}

include 'db_connection.php';

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$admin_email = $_SESSION['email'];
$admin_stmt = $conn->prepare("SELECT user_id, name, email, phone FROM users WHERE email = ? AND role = 'admin'");
$admin_stmt->bind_param("s", $admin_email);
$admin_stmt->execute();
$admin_result = $admin_stmt->get_result();
$admin = $admin_result->fetch_assoc();
$admin_stmt->close();

$user_stmt = $conn->prepare("SELECT user_id, name, email, phone FROM users WHERE role = 'user'");
$user_stmt->execute();
$user_result = $user_stmt->get_result();

$properties = [];
$bookings = [];

$property_stmt = $conn->prepare("SELECT property_id, owner_id, title, description, location, price_per_night, max_guests FROM properties");
$property_stmt->execute();
$property_result = $property_stmt->get_result();
while ($row = $property_result->fetch_assoc()) {
    $properties[$row['owner_id']][] = $row;
}
$property_stmt->close();

$booking_stmt = $conn->prepare("SELECT booking_id, user_id, property_id, check_in, check_out, total_price, status, created_at FROM bookings");
$booking_stmt->execute();
$booking_result = $booking_stmt->get_result();
while ($row = $booking_result->fetch_assoc()) {
    $bookings[$row['property_id']][] = $row;
}
$booking_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body {
            background: #1a1a1a;
            color: #fff;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }

        h2, h3 {
            color: #00f2fe;
            text-shadow: 0 0 10px #00f2fe;
            text-align: center;
        }

        .profile-section, .properties-section {
            margin-bottom: 30px;
        }

        .profile-section p {
            margin: 5px 0;
            font-size: 16px;
            color: #ccc;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: #2a2a2a;
            border: 1px solid #00f2fe;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #00f2fe;
        }

        th {
            background: #3a3a3a;
            color: #00f2fe;
            font-weight: bold;
        }

        td {
            color: #fff;
        }

        td img {
            width: 100%;
            max-width: 200px;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .action-buttons button {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .action-buttons .edit-btn {
            background: #00f2fe;
            color: #1a1a1a;
        }

        .action-buttons .delete-btn {
            background: #ff4d4d;
            color: #fff;
        }

        .collapsible {
            background: #3a3a3a;
            color: #00f2fe;
            cursor: pointer;
            padding: 10px;
            width: 100%;
            text-align: left;
            border: none;
            outline: none;
            font-size: 16px;
            border-bottom: 1px solid #00f2fe;
            position: relative; /* For positioning the triangle */
        }

        .collapsible:hover, .collapsible.active {
            background: #4a4a4a;
        }

        .collapsible::after {
            content: '\25B6'; /* Unicode for right-pointing triangle */
            color: #00f2fe;
            font-size: 14px;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            transition: transform 0.3s ease; /* Smooth rotation */
        }

        .collapsible.active::after {
            transform: translateY(-50%) rotate(90deg); /* Rotate to point down when active */
        }

        .content {
            display: none;
            background: #2a2a2a;
            padding: 0;
        }

        footer {
            text-align: center;
            margin-top: 30px;
            color: #ccc;
        }

        @media (max-width: 768px) {
            table, th, td {
                font-size: 14px;
            }

            td img {
                max-width: 150px;
                height: 100px;
            }

            .action-buttons button {
                padding: 6px 12px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <main>
        <!-- Admin Information -->
        <section class="profile-section">
            <h2>User Profile</h2>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($admin['name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($admin['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($admin['phone']); ?></p>
        </section>

        <!-- User Information with Properties and Bookings -->
        <section class="properties-section">
            <h2>All Users</h2>
            <?php if ($user_result->num_rows > 0): ?>
                <?php while ($user = $user_result->fetch_assoc()): ?>
                    <button class="collapsible"><?php echo htmlspecialchars($user['name']); ?> (ID: <?php echo $user['user_id']; ?>)</button>
                    <div class="content">
                        <!-- User Information -->
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
                            <button class="collapsible">Properties Owned</button>
                            <div class="content">
                                <table>
                                    <tr>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Location</th>
                                        <th>Price/Night</th>
                                        <th>Max Guests</th>
                                        <th>Actions</th>
                                    </tr>
                                    <?php foreach ($properties[$user['user_id']] as $property): ?>
                                        <tr>
                                            <td>
                                                <?php echo htmlspecialchars($property['title']); ?><br>
                                                <img src="photo/<?php echo strtolower(str_replace(' ', '_', $property['title'])); ?>.jpg" alt="<?php echo htmlspecialchars($property['title']); ?>">
                                            </td>
                                            <td><?php echo htmlspecialchars($property['description']); ?></td>
                                            <td><?php echo htmlspecialchars($property['location']); ?></td>
                                            <td>$<?php echo htmlspecialchars($property['price_per_night']); ?></td>
                                            <td><?php echo htmlspecialchars($property['max_guests']); ?></td>
                                            <td class="action-buttons">
                                                <button class="edit-btn">Edit</button>
                                                <button class="delete-btn">Delete</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>

                                <!-- Bookings for this User's Properties -->
                                <?php foreach ($properties[$user['user_id']] as $property): ?>
                                    <?php if (isset($bookings[$property['property_id']])): ?>
                                        <button class="collapsible">Bookings for Property: <?php echo htmlspecialchars($property['title']); ?> (ID: <?php echo $property['property_id']; ?>)</button>
                                        <div class="content">
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
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>No properties owned by this user.</p>
                        <?php endif; ?>
                    </div>
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

    <script>
        var coll = document.getElementsByClassName("collapsible");
        for (var i = 0; i < coll.length; i++) {
            coll[i].addEventListener("click", function() {
                this.classList.toggle("active");
                var content = this.nextElementSibling;
                if (content.style.display === "block") {
                    content.style.display = "none";
                } else {
                    content.style.display = "block";
                }
            });
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>