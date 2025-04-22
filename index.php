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
                            <li><a href="index.php?page=searchForBeachHouse">Beach House/Villa</a></li>
                            <li><a href="index.php?page=searchForMountanCabin">Mountain Cabin</a></li>
                            <li><a href="index.php?page=searchForCityApartment">City Apartment</a></li>
                            <!-- <li><a href="index.php?page=searchForTropicalBungalow">Tropical Bungalow</a></li> -->
                            <li><a href="index.php?page=searchForCountrysideCottage">Countryside Cottage</a></li>
                        </ul>
                    </li>
                    <li><a href="index.php?page=Contact">Add Property</a></li>

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

        // Allowed pages
        $allowed_pages = ['index', 'Login', 'Product', 'Catagories', 'Contact', 'rentProp', 'process_rent', 'profile', 'edit', 'register','searchForBeachHouse','searchForMountanCabin','searchForCityApartment','searchForTropicalBungalow','searchForCountrysideCottage'];
        $allowed_admin_pages = ['admin'];

        if ($user_role === 'admin') {
            // Admins can only access admin page
            if (!in_array($page, $allowed_admin_pages)) {
                header("Location: index.php?page=admin");
                exit();
            }
        }

        if ($page === 'Login' && file_exists("login.php")) {
            include "login.php";

        } elseif ($page === 'register' && file_exists("register.php")) {
            include "register.php";

        } elseif ($page === 'index' || $page === 'home') {
            include "pages/index.php";

        } elseif ($page === 'admin' && $user_role === 'admin') {
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