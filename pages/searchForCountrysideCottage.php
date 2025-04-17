<?php
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$title = "Countryside Cottage";
$stmt = $conn->prepare("SELECT properties.*, users.name AS owner_name, users.email AS owner_email, users.phone AS owner_phone 
                        FROM properties 
                        JOIN users ON properties.owner_id = users.user_id 
                        WHERE properties.title = ?");
$stmt->bind_param("s", $title);
$stmt->execute();
$result = $stmt->get_result();
$property = $result->fetch_assoc();
?>
<section class="menu" id="menu">
    <h1 class="heading"> Available <span>Beach Houses</span></h1>

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

    <div class="box-container" id="result">
        <?php if ($property): ?>
            <div class="box">
                <img src="photo/Countryside_Cottage.jpg" alt="<?= htmlspecialchars($property['title']) ?>">
                <h3><?= htmlspecialchars($property['title']) ?></h3>
                <p><?= htmlspecialchars($property['description']) ?></p>

                <button class="view-details"
                        data-title="<?= htmlspecialchars($property['title']) ?>"
                        data-desc="<?= htmlspecialchars($property['description']) ?>"
                        data-location="<?= htmlspecialchars($property['location']) ?>"
                        data-price="<?= htmlspecialchars($property['price_per_night']) ?>"
                        data-guests="<?= htmlspecialchars($property['max_guests']) ?>"
                        data-img="images/cozy-beach-house.jpg"
                        data-owner-name="<?= htmlspecialchars($property['owner_name']) ?>"
                        data-owner-email="<?= htmlspecialchars($property['owner_email']) ?>"
                        data-owner-phone="<?= htmlspecialchars($property['owner_phone']) ?>">
                    View Details
                </button>
            </div>
        <?php else: ?>
            <p>No beach house found with the title "Mountain cabin".</p>
        <?php endif; ?>
    </div>
</section>

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
        button.addEventListener('click', function () {
            document.getElementById('popupTitle').textContent = this.dataset.title;
            document.getElementById('popupDesc').textContent = this.dataset.desc;
            document.getElementById('popupLocation').textContent = 'Location: ' + this.dataset.location;
            document.getElementById('popupPrice').textContent = 'Price per Night: $' + this.dataset.price;
            document.getElementById('popupGuests').textContent = 'Max Guests: ' + this.dataset.guests;
            document.getElementById('popupImg').src = this.dataset.img;
            document.getElementById('popupOwnerName').textContent = 'Name: ' + this.dataset['ownerName'];
            document.getElementById('popupOwnerEmail').textContent = 'Email: ' + this.dataset['ownerEmail'];
            document.getElementById('popupOwnerPhone').textContent = 'Phone: ' + this.dataset['ownerPhone'];

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
