<?php
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('index.php');
    exit;
}

$user_type = $_SESSION['user_type'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - RoofShare</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header>
        <h1>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></h1>
        <nav>
            <a href="auth/logout.php">Logout</a>
        </nav>
    </header>
    
    <main>
        <?php if ($user_type === 'renter'): ?>
            <h2>Renter Dashboard</h2>
            <p>Browse listings and find roommates</p>
        <?php elseif ($user_type === 'realtor'): ?>
            <h2>Realtor Dashboard</h2>
            <p>Manage your property listings</p>
        <?php elseif ($user_type === 'admin'): ?>
            <h2>Admin Dashboard</h2>
            <p>Manage users and content</p>
        <?php endif; ?>
    </main>
</body>
</html>


