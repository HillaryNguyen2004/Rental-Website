<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
} 
include 'pages/db_connection.php'; // Include database connection

// Check if user is logged in
$user_name = isset($_SESSION['email']) ? $_SESSION['email'] : null;
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My New Website</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1 class="logo">
            <a href="index.php?page=home">My Website</a>
        </h1>
        <nav>
            <ul>
                <?php if ($user_role === 'admin'): // Admin-specific nav bar ?>
                    <?php if (!empty($user_name)): ?>
                        <li class="user-info">
                            <a href="#">👤 <?php echo htmlspecialchars($user_name); ?></a>
                            <ul class="dropdown-menu">
                                <li><a href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                <?php else: // Regular user or non-logged-in nav bar ?>
                    <li><a href="index.php?page=home">Home</a></li>
                    <li><a href="index.php?page=Product">Product/Service</a></li>
                    <li class="dropdown">
                        <a href="index.php?page=Catagories">Categories ▾</a>
                        <ul class="dropdown-menu">
                            <li><a href="#">Beach House/Villa</a></li>
                            <li><a href="#">Mountain Cabin</a></li>
                            <li><a href="#">City Apartment</a></li>
                            <li><a href="#">Tropical Bungalow</a></li>
                            <li><a href="#">Countryside Cottage</a></li>
                        </ul>
                    </li>
                    <li><a href="index.php?page=Contact">Contact</a></li>

                    <!-- Display User Email or Login -->
                    <?php if (!empty($user_name)): ?>
                        <li class="user-info">
                            <a href="index.php?page=profile">👤 <?php echo htmlspecialchars($user_name); ?></a>
                            <ul class="dropdown-menu">
                                <li><a href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li><a href="index.php?page=Login">Login</a></li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <?php
        // Get requested page or default to 'index'
        $page = isset($_GET['page']) ? $_GET['page'] : 'index';

        // Allowed pages (Make sure these files exist)
        $allowed_pages = ['index', 'Login', 'Product', 'Catagories', 'Contact', 'rentProp', 'process_rent', 'profile', 'edit'];
        $allowed_admin_pages = ['admin'];

        // Role-based access control
        if ($user_role === 'admin') {
            // Admins can only access admin page
            if (!in_array($page, $allowed_admin_pages)) {
                header("Location: index.php?page=admin");
                exit();
            }
        }

        // Check if page is allowed and exists in 'pages/' directory
        if ($page === 'Login') {
            if (file_exists("login.php")) {
                include "login.php"; // Directly include from the main folder
            } else {
                echo "<h2>Login page not found</h2>";
            }
        } elseif ($page === 'index' || $page === 'home') {
            include "pages/index.php";
        } elseif ($page === 'admin') {
            include "pages/admin.php";
        } elseif (in_array($page, $allowed_pages) && file_exists("pages/$page.php")) {
            include "pages/$page.php";
        } else {
            echo "<h2>Page not found</h2>";
        }
        ?>
    </main>

    <footer>
        <p>© 2025 My Website. All Rights Reserved.</p>
    </footer>
</body>
</html>