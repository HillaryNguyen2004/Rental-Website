<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup'])) {
    $email = $_POST['email'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = 'user';

    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $error = "Email is already registered!";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (email, name, phone, password_hash, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $email, $name, $phone, $hashed_password, $role);
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
}
$conn->close();
?>

<!DOCTYPE html>
<html>
    <style>
        .register-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
    }

    .register-inner {
        width: 100%;
        max-width: 400px;
        padding: 30px;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
        .register-inner p {
        margin: 10px 0 5px;
        padding: 0; /* Remove padding to minimize space */
        line-height: 0;
        /* font-weight: bold; */
        text-align: left;
        margin: 0;
    }    

    </style>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="css/style.css">
    <script>
        function validateForm() {
            const pass = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            if (pass !== confirm) {
                alert('Passwords do not match!');
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <div class="register-container">
        <div class="register-inner">
            <h2>Register</h2>
            <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
            <form method="POST" onsubmit="return validateForm();">
                <p>Email</p>
                <input type="email" name="email" placeholder="Email" required>
                <p>Full Name</p>
                <input type="text" name="name" placeholder="Full name" required>
                <p>Phone Number</p>
                <input type="tel" name="phone" placeholder="Phone number" required>
                <p>Password</p>
                <input type="password" id="password" name="password" placeholder="Password" required>
                <p>Confirmed Password</p>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                <button type="submit" name="signup">Register</button>
                <a href="index.php?page=Login" class="register-link">Back to Login</a>
            </form>
        </div>
    </div>
</body>
</html>
