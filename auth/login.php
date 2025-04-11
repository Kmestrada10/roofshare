<?php
require 'config/db.php';

if (isset($_SESSION['user_id'])) {
    header('../dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>RoofShare - Find Your Perfect Housing Match</title>
    <link rel="stylesheet" href="assets/style.css">
</head> 
<body>
    <div class="container">
        <h1>Welcome to RoofShare</h1>
        <p>Find your perfect housing and roommate matches</p>
        
        <div class="auth-options">
            <a href="auth/login.php" class="btn">Login</a>
            <a href="auth/register.php" class="btn">Register</a>
        </div>
    </div>
</body>
</html>


