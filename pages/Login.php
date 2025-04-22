<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'pages/db_connection.php'; // Include the database connection file

// Debugging: Check session values
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Check if database connection is successful
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = $_POST['email']; 
    $password = $_POST['password']; // User-entered password

    // Debug: Print email and entered password
    echo "Entered Email: $email <br>";
    echo "Entered Password: $password <br>";

    // Prepare and execute SQL query
    $stmt = $conn->prepare("SELECT user_id, email, password_hash, role FROM users WHERE email = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $db_email, $db_password, $role);
        $stmt->fetch();

        // Debug: Print stored hashed password
        echo "Stored Password (Hashed in DB): $db_password <br>";

        // Check if password in database is hashed
        if (password_verify($password, $db_password)) {
            $is_valid = true;
        } else {
            $is_valid = false;
        }

        // If valid, update the session and password if needed
        if ($is_valid) {
            $_SESSION['user_id'] = $id;
            $_SESSION['email'] = $db_email;
            $_SESSION['role'] = $role;

            // If stored password is not hashed, update it
            if (password_needs_rehash($db_password, PASSWORD_DEFAULT)) {
                $new_hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
                $update_stmt->bind_param("si", $new_hashed_password, $id);
                $update_stmt->execute();
                $update_stmt->close();
                echo "Password updated to hashed format. <br>";
            }

            // Redirect based on role
            if ($role === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            echo "<p style='color: red;'>Invalid password!</p>";
        }
    } else {
        echo "<p style='color: red;'>User not found!</p>";
    }
    $stmt->close();
}

// Handle signup form submission (stores passwords securely)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup'])) {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash password before storing
    $role = 'user'; // Default role

    // Debug: Print email and hashed password before inserting
    echo "Signup Email: $email <br>";
    echo "Signup Hashed Password: $password <br>";

    // Check if email already exists
    $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        echo "<p style='color: red;'>Email is already registered!</p>";
    } else {
        // Insert new user with hashed password
        $stmt = $conn->prepare("INSERT INTO users (email, password_hash, role) VALUES (?, ?, ?)");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("sss", $email, $password, $role);
        if ($stmt->execute()) {
            echo "<p style='color: green;'>Registration successful! You can now login.</p>";
        } else {
            echo "<p style='color: red;'>Error: " . $stmt->error . "</p>";
        }

        $stmt->close();
    }
    $check_stmt->close();
}

$conn->close();
?>

<div class="login-page">
            <div class="login-container">
                <h2>Login</h2>
                <form>
                    <p>Email</p>
                    <input type="text" placeholder="Email" required>
                    <p>Password</p>
                    <input type="password" placeholder="Password" required>
                    <button type="submit">Login</button>
                    <!-- <button type="button" onclick="window.location.href='index.html';">Login</button> -->
                </form>
            </div>
        </div>
    