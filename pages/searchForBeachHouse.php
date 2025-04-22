<section class="menu" id="menu">
    <h1 class="heading"> Available <span>Beach Houses</span></h1>

    <div class="box-container">
        <?php
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Adjusted query to fetch properties with "Beach" in title or "beach"/"sea" in description
        $stmt = $conn->prepare("SELECT properties.*, users.name AS owner_name, users.email AS owner_email, users.phone AS owner_phone 
                                FROM properties 
                                JOIN users ON properties.owner_id = users.user_id 
                                WHERE properties.title LIKE ? OR properties.description LIKE ?");
        $titleKeyword = "%Beach%";
        $descriptionKeyword = "%beach%";
        $stmt->bind_param("ss", $titleKeyword, $descriptionKeyword);

        if ($stmt->execute()) {
            $result = $stmt->get_result();

            // Check if any rows are returned
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Construct image filename
                    $image_filename = 'photo/' . strtolower(str_replace(' ', '_', $row['title'])) . '.jpg';
                    
                    // Output each property
                    echo '<div class="box">
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
                echo "<p>No properties found matching your criteria.</p>";
            }
        } else {
            echo "Query execution failed: " . $stmt->error;
        }
        ?>
    </div>
</section>

<!-- Popup Modal (Hidden by default) -->
<div class="popup-overlay"></div>
<div class="popup">
    <button class="close-btn">×</button>
    <div class="popup-content">
        <img id="popup-img" src="" alt="">
        <h3 id="popup-title"></h3>
        <h4>Location</h4>
        <p id="popup-location"></p>
        <h4>Price Per Night</h4>
        <p id="popup-price"></p>
        <h4>Max Guests</h4>
        <p id="popup-guests"></p>
        <h4>Owner Details</h4>
        <p id="popup-owner-name"></p>
        <p id="popup-owner-email"></p>
        <p id="popup-owner-phone"></p>
        <a id="rentButton" class="btn" href="index.php?page=rentProp">Rent Now</a>
    </div>
</div>

<script>
    // Open and Close Popup Logic
    const viewDetailsButtons = document.querySelectorAll('.view-details');
    const popup = document.querySelector('.popup');
    const popupOverlay = document.querySelector('.popup-overlay');
    const closeBtn = document.querySelector('.close-btn');

    viewDetailsButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Get data attributes from the button
            const propertyData = this.dataset;

            // Populate popup content
            document.getElementById('popup-img').src = propertyData.img;
            document.getElementById('popup-title').textContent = propertyData.title;
            document.getElementById('popup-location').textContent = propertyData.location;
            document.getElementById('popup-price').textContent = `$${propertyData.price} per night`;
            document.getElementById('popup-guests').textContent = propertyData.guests;
            document.getElementById('popup-owner-name').textContent = propertyData.ownerName;
            document.getElementById('popup-owner-email').textContent = propertyData.ownerEmail;
            document.getElementById('popup-owner-phone').textContent = propertyData.ownerPhone;

            // Show popup
            popup.style.display = 'block';
            popupOverlay.style.display = 'block';
        });
    });

    closeBtn.addEventListener('click', function() {
        // Hide popup
        popup.style.display = 'none';
        popupOverlay.style.display = 'none';
    });

    popupOverlay.addEventListener('click', function() {
        // Hide popup if clicked outside
        popup.style.display = 'none';
        popupOverlay.style.display = 'none';
    });
</script>

<style>
    .menu .box-container {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
    }

    .menu .box-container .box {
        padding: 2rem;
        text-align: center;
        background-color: rgba(255, 255, 255, 0.15);
        border: 3px solid var(--main-color);
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .menu .box-container .box h3 {
        color: #fff;
        font-size: 2rem;
        padding: 1rem 0;
    }

    .menu .box-container .box p {
        color: #fff;
        font-size: 1.5rem;
    }

    .menu .box-container .box img {
        width: 100%;
        max-width: 250px;
        height: 200px;
        border-radius: 10px;
        display: block;
        margin: 0 auto 1rem;
    }

    .view-details {
        margin-top: 1rem;
        padding: 0.8rem 1.5rem;
        background-color: var(--main-color);
        color: #fff;
        border: none;
        cursor: pointer;
        font-size: 1.4rem;
        border-radius: 6px;
    }

    .popup, .popup-overlay { display: none; }

    .popup-overlay {
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.7);
        z-index: 999;
    }

    .popup {
        position: fixed;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        background: var(--second-bg-color);
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        z-index: 1000;
        max-width: 600px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
    }

    .popup img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin-bottom: 16px;
    }

    .close-btn {
        position: absolute;
        top: 16px; right: 16px;
        cursor: pointer;
        font-size: 24px;
        color: var(--text-color);
        background: none;
        border: none;
    }

    .close-btn:hover {
        color: var(--main-color);
    }

    .popup-content {
        color: var(--text-color);
        font-family: 'Arial', sans-serif;
        line-height: 1.6;
    }

    .popup-content h3 {
        font-size: 2.4rem;
        margin: 0 0 12px;
        color: var(--main-color);
        font-weight: 700;
    }

    .popup-content h4 {
        font-size: 1.8rem;
        margin: 20px 0 10px;
        color: var(--text-color);
        font-weight: 600;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        padding-bottom: 4px;
    }

    .popup-content p {
        font-size: 1.6rem;
        margin: 8px 0;
        color: rgba(255, 255, 255, 0.9);
    }

    @media (max-width: 768px) {
        .popup { max-width: 90%; padding: 16px; }
        .popup-content h3 { font-size: 2rem; }
        .popup-content h4 { font-size: 1.6rem; }
        .popup-content p { font-size: 1.4rem; }
    }
</style>
