<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'db_connection.php'; // Include the database connection file

// Check if database connection is successful
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Define admin emails
$admin_emails = ['admin1@example.com', 'admin2@example.com'];

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = $_POST['email']; 
    $password = $_POST['password'];

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

        // Verify password (supports both hashed and plaintext for backward compatibility)
        if (!password_needs_rehash($db_password, PASSWORD_DEFAULT)) {
            $is_valid = password_verify($password, $db_password);
        } else {
            $is_valid = ($password === $db_password);
            if ($is_valid) {
                $new_hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
                $update_stmt->bind_param("si", $new_hashed_password, $id);
                $update_stmt->execute();
                $update_stmt->close();
            }
        }

        if ($is_valid) {
            $_SESSION['user_id'] = $id;
            $_SESSION['email'] = $db_email;
            $_SESSION['role'] = $role ?: 'user'; // Default to 'user' if role is null

            // Check if the email is in the admin list
            if (in_array($email, $admin_emails)) {
                $_SESSION['role'] = 'admin'; // Force role to admin for these emails
                header("Location: index.php?page=admin");
            } elseif ($role === 'admin') {
                header("Location: index.php?page=admin"); // Respect DB role if not in admin_emails
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- Add Google's CSS for icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        .register-link {
            display: block;
            text-align: center;
            margin-top: 15px;
        }
        .login-container p {
            font-size: 12px;
            color: #555;
            margin-top: 0px;
            /* background-color: #f9f9f9;
            border-left: 4px solid #4CAF50;
            padding: 10px 15px;
            margin: 10px 0;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); */
        }

    </style>
</head>
<body>
    <div class="login-page">
        <div class="login-container">
            <p>Log in for admin: admin1@example.com/admin2@example.com, Password: 123</p>
            <!-- <p></p> -->
            <h2>Login</h2>
            <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
            <form method="POST">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login" value="1">Login</button>
            </form>
            
            <!-- Google Sign-in Button (commented out) -->
            <!-- <a href="google_login.php" class="google-btn">
                <i class="material-icons">google</i>
                Sign in with Google
            </a> -->
            
            <!-- Register Link -->
            <a href="index.php?page=register" class="register-link">Don't have an account? Register here</a>
            
        </div>
    </div>
</body>
</html>