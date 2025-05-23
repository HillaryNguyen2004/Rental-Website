<?php
include 'db_connection.php';

$limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 6;
$offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;
$query = isset($_POST['query']) ? trim($_POST['query']) : "";
$sort = isset($_POST['sort']) ? $_POST['sort'] : "";

// Base SQL and bindings
$sql = "SELECT 
            p.*,  
            u.name AS owner_name,
            u.email AS owner_email,
            u.phone AS owner_phone
        FROM properties p
        JOIN users u ON p.owner_id = u.user_id";
$where = [];
$params = [];
$types = "";

// Add WHERE clause if search query exists
// if (!empty($query)) {
//     $where[] = "(p.title LIKE ? OR p.description LIKE ?)";
//     $searchQuery = "$query%";
//     $params[] = $searchQuery;
//     // $params[] = $searchQuery;
//     $types .= "ss";
// }

if (!empty($query)) {
    $where[] = "p.title LIKE ?"; // Only search for the title
    $searchQuery = "$query%"; // Match titles that start with the search term
    $params[] = $searchQuery; // Bind the search query to the parameter
    $types .= "s"; // Only one string parameter
}

// Combine WHERE clauses
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

// Add ORDER BY clause
switch ($sort) {
    case 'title_asc':
        $sql .= " ORDER BY p.title ASC";
        break;
    case 'title_desc':
        $sql .= " ORDER BY p.title DESC";
        break;
    default:
        $sql .= " ORDER BY p.property_id DESC"; // fallback sort
        break;
}

// Add LIMIT and OFFSET
$sql .= " LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

// Prepare and execute
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

// Bind all parameters dynamically
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Check if query failed
if ($result === false) {
    echo "Query failed: " . $conn->error; // Print the error
} else {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $image_filename = 'photo/' . strtolower(str_replace(' ', '_', $row['title'])) . '.jpg';
            
            echo '<div class="property-box">
                    <img src="' . $image_filename . '" alt="' . htmlspecialchars($row['title']) . '" loading="lazy">
                    <h3>' . htmlspecialchars($row['title']) . '</h3>
                    <p>' . htmlspecialchars($row['description']) . '</p>
                    <button class="btn view-details" 
                            data-property-id="' . htmlspecialchars($row['property_id']) . '"
                            data-owner-id="' . htmlspecialchars($row['owner_id']) . '"
                            data-title="' . htmlspecialchars($row['title']) . '" 
                            data-desc="' . htmlspecialchars($row['description']) . '"
                            data-location="' . htmlspecialchars($row['location']) . '"
                            data-price="' . htmlspecialchars($row['price_per_night']) . '"
                            data-guests="' . htmlspecialchars($row['max_guests']) . '"
                            data-img="' . $image_filename . '"
                            data-owner-name="' . htmlspecialchars($row['owner_name']) . '"
                            data-owner-email="' . htmlspecialchars($row['owner_email']) . '"
                            data-owner-phone="' . htmlspecialchars($row['owner_phone']) . '">View Details</button>
                </div>';
        }
    } else {
        echo "<p>No properties found.</p>";
    }
}

$stmt->close();
$conn->close();
?>

<!-- Popup HTML -->
<div class="popup-overlay" id="popupOverlay"></div>
<div class="popup" id="popup">
    <span class="close-btn" onclick="closePopup()">×</span>
    <img id="popupImg" src="" alt="">
    <div class="popup-content">
        <h3 id="popupTitle"></h3>
        <p id="popupDesc"></p>
        <p id="popupLocation"></p>
        <p id="popupPrice"></p>
        <p id="popupGuests"></p>
        <h4>Owner Information</h4>
        <p id="popupOwnerName"></p>
        <p id="popupOwnerEmail"></p>
        <p id="popupOwnerPhone"></p>
        <a id="rentButton" class="btn" href="index.php?page=rentProp">Rent Now</a>
    </div>
</div>

<script>
    document.querySelectorAll('.view-details').forEach(button => {
        button.addEventListener('click', function() {
            // Get data from button attributes
            const propertyId = this.getAttribute('data-property-id');
            const ownerId = this.getAttribute('data-owner-id');
            const title = this.getAttribute('data-title');
            const desc = this.getAttribute('data-desc');
            const location = this.getAttribute('data-location');
            const price = this.getAttribute('data-price');
            const guests = this.getAttribute('data-guests');
            const img = this.getAttribute('data-img');
            const ownerName = this.getAttribute('data-owner-name');
            const ownerEmail = this.getAttribute('data-owner-email');
            const ownerPhone = this.getAttribute('data-owner-phone');

            // Fill popup with data
            document.getElementById('popupTitle').textContent = title || 'No Title';
            document.getElementById('popupDesc').textContent = desc || 'No description available';
            document.getElementById('popupLocation').textContent = 'Location: ' + (location || 'Not specified');
            document.getElementById('popupPrice').textContent = 'Price per Night: $' + (price || 'N/A');
            document.getElementById('popupGuests').textContent = 'Max Guests: ' + (guests || 'Not specified');
            document.getElementById('popupImg').src = img;
            document.getElementById('popupOwnerName').textContent = 'Name: ' + (ownerName || 'Not specified');
            document.getElementById('popupOwnerEmail').textContent = 'Email: ' + (ownerEmail || 'Not specified');
            document.getElementById('popupOwnerPhone').textContent = 'Phone: ' + (ownerPhone || 'Not specified');

            // Show popup and overlay
            document.getElementById('popup').style.display = 'block';
            document.getElementById('popupOverlay').style.display = 'block';
        });
    });

    function closePopup() {
        document.getElementById('popup').style.display = 'none';
        document.getElementById('popupOverlay').style.display = 'none';
    }

    document.getElementById('popupOverlay').addEventListener('click', closePopup);
</script>