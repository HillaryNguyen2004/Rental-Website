<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'db_connection.php';

$user_id = $_SESSION['user_id'];

if (!isset($_POST['property_id']) || !is_numeric($_POST['property_id'])) {
    header("Location: index.php?page=profile");
    exit();
}

$property_id = intval($_POST['property_id']);

// Fetch existing property data
$query = $conn->prepare("SELECT * FROM properties WHERE property_id = ? AND owner_id = ?");
$query->bind_param("ii", $property_id, $user_id);
$query->execute();
$result = $query->get_result();
$property = $result->fetch_assoc();

if (!$property) {
    header("Location: index.php?page=profile");
    exit();
}

// Handle update if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    $updates = [];

    // Title is no longer editable, so we skip checking it
    // if ($_POST['title'] !== $property['title']) {
    //     $title = $conn->real_escape_string($_POST['title']);
    //     $updates[] = "title = '$title'";
    // }

    if ($_POST['description'] !== $property['description']) {
        $description = $conn->real_escape_string($_POST['description']);
        $updates[] = "description = '$description'";
    }

    if ($_POST['location'] !== $property['location']) {
        $location = $conn->real_escape_string($_POST['location']);
        $updates[] = "location = '$location'";
    }

    if (floatval($_POST['price_per_night']) != $property['price_per_night']) {
        $price = floatval($_POST['price_per_night']);
        $updates[] = "price_per_night = $price";
    }

    if (intval($_POST['max_guests']) != $property['max_guests']) {
        $max_guests = intval($_POST['max_guests']);
        $updates[] = "max_guests = $max_guests";
    }

    if (!empty($updates)) {
        $updateSQL = "UPDATE properties SET " . implode(", ", $updates) . " WHERE property_id = $property_id AND owner_id = $user_id";

        if ($conn->query($updateSQL)) {
            header("Location: index.php?page=profile");
            exit();
        } else {
            $error = "Update failed: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Property</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <div class="container">
        <h2>Edit Property</h2>
        <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
        <form method="POST" class="property-form">
            <input type="hidden" name="property_id" value="<?php echo $property_id; ?>">
            <div class="form-group">
                <label>Title:</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($property['title']); ?>" readonly>
            </div>
            <div class="form-group">
                <label>Description:</label>
                <textarea name="description" required><?php echo htmlspecialchars($property['description']); ?></textarea>
            </div>
            <div class="form-group">
                <label>Location:</label>
                <input type="text" name="location" value="<?php echo htmlspecialchars($property['location']); ?>" required>
            </div>
            <div class="form-group">
                <label>Price per Night:</label>
                <input type="number" step="0.01" name="price_per_night" value="<?php echo htmlspecialchars($property['price_per_night']); ?>" required>
            </div>
            <div class="form-group">
                <label>Max Guests:</label>
                <input type="number" name="max_guests" value="<?php echo htmlspecialchars($property['max_guests']); ?>" required>
            </div>
            <div class="form-actions">
                <input type="submit" value="Update" class="submit-btn">
                <a href="index.php?page=profile" class="cancel-btn">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>

<?php $conn->close(); ?>