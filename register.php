<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup'])) {
    $email = $_POST['email'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'user';

    $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        $error = "Email is already registered!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (email, name, phone, password_hash, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $email, $name, $phone, $password, $role);
        if ($stmt->execute()) {
            header("Location: index.php?page=Login");
            exit();
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
    $check_stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="register-container">
        <div class="register-inner">
            <h2>Register</h2>
            <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
            <form method="POST">
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="name" placeholder="Full name" required>
                <input type="tel" name="phone" placeholder="Phone number" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="signup">Register</button>
                <a href="index.php?page=Login">Back to Login</a>
            </form>
        </div>
    </div>
</body>
</html>