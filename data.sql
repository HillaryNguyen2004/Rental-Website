-- Users table
CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Properties table
CREATE TABLE Properties (
    property_id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    location VARCHAR(255) NOT NULL,
    price_per_night DECIMAL(10,2) NOT NULL,
    max_guests INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES Users(user_id) ON DELETE SET NULL
);

-- Bookings table
CREATE TABLE Bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    property_id INT,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES Properties(property_id) ON DELETE CASCADE
);

-- Reviews table
CREATE TABLE Reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    property_id INT,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES Properties(property_id) ON DELETE CASCADE
);

-- Payments table
CREATE TABLE Payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT,
    amount DECIMAL(10,2) NOT NULL,
    payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    transaction_id VARCHAR(100) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES Bookings(booking_id) ON DELETE CASCADE
);

-- Sample Data
INSERT INTO Users (name, email, password_hash, phone) VALUES
('Alice Johnson', 'alice@example.com', 'hashedpassword1', '1234567890'),
('Bob Smith', 'bob@example.com', 'hashedpassword2', '0987654321'),
('Margaretta Arlott', 'marlott0@squidoo.com', '$2a$04$tgX', '1989304708'),
('Elnora Phidgin', 'ephidgin1@joomla.org', '$2a$04$Y4zk', '1318323485'),
('Ardella Schieferstein', 'aschieferstein2@boston.com', '$2a$04$SBr', '3655517819'),
('Karyn Corrison', 'kcorrison3@virginia.edu', '$2a$04$eNF', '4814341823'),
('Nickolas Naul', 'nnaul4@constantcontact.com', '$2a$04$Lf77T', '6136544423'),
('Elsey Dust', 'edust5@devhub.com', '$2a$04$GdO6', '1498619212'),
('Perl O''Noland', 'ponoland6@paginegialle.it', '$2a$04$X', '4785699864'),
('Ban Winwright', 'bwinwright7@shoppro.jp', '$2a$04$Q', '6399182148'),
('Killian Fitzjohn', 'kfitzjohn8@woothemes.com', '$2a$04$', '9938673120'),
('Malvin Doyland', 'mdoyland9@cpanel.net', '$2a$04$UF', '4148154716');

INSERT INTO Properties (owner_id, title, description, location, price_per_night, max_guests) VALUES
(1, 'Cozy Beach House', 'A beautiful house by the sea.', 'Central Province, Maldives', 150.00, 4),
(2, 'Luxury Villa', 'A modern villa with a private pool.', 'Bali, Indonesia', 300.00, 6),
(3, 'Mountain Cabin', 'A peaceful retreat in the mountains.', 'Aspen, Colorado, USA', 120.00, 5),
(4, 'City Apartment', 'A stylish apartment in the heart of the city.', 'New York, USA', 200.00, 2),
(5, 'Tropical Bungalow', 'A relaxing bungalow near the beach.', 'Phuket, Thailand', 90.00, 3),
(6, 'Countryside Cottage', 'A cozy cottage surrounded by nature.', 'Tuscany, Italy', 110.00, 4),
(7, 'Ski Resort Condo', 'A perfect stay for winter sports lovers.', 'Whistler, Canada', 250.00, 6),
(8, 'Desert Retreat', 'An off-grid experience in the desert.', 'Dubai, UAE', 180.00, 2),
(9, 'Lakefront Cabin', 'A peaceful lakeside cabin.', 'Lake Tahoe, USA', 130.00, 4),
(10, 'Historic Townhouse', 'A charming townhouse with historic details.', 'Edinburgh, Scotland', 175.00, 5);

INSERT INTO Bookings (user_id, property_id, check_in, check_out, total_price, status) VALUES
(2, 1, '2025-06-01', '2025-06-07', 900.00, 'confirmed'),
-- (1, 2, '2025-07-15', '2025-07-20', 1000.00, 'pending');

INSERT INTO Reviews (user_id, property_id, rating, comment) VALUES
(2, 1, 5, 'Amazing place! Highly recommended.'),
(1, 2, 4, 'Very nice but a bit cold in winter.');

INSERT INTO Payments (booking_id, amount, payment_status, transaction_id) VALUES
(1, 900.00, 'completed', 'TXN12345'),
(2, 1000.00, 'pending', 'TXN67890');

-- Select properties
--SELECT * FROM Properties;
