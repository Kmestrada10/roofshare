<?php
// renter_dashboard.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// Check if user is logged in and is a renter
if (!isset($_SESSION['user_email']) || $_SESSION['user_type'] !== 'Renter') {
    header("Location: login.php");
    exit();
}

require_once("config/db.php");

$email = $_SESSION['user_email'];
$stmt = $db->prepare("SELECT name FROM Renter WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();
$name = $user['name'] ?? 'Renter';

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/renter_dashboard.css">
</head>
<body>
    <div class="header">
        <div class="info">Renter</div>
        <div>
            <a href="roommate_preferences.php">Find Roommate</a>
            <a href="login.php?logout=1">Logout</a>
        </div>
    </div>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($name); ?>!</h1>
        
        <div class="dashboard-grid">
            <!-- Overview Card -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2 class="card-title">Overview</h2>
                </div>
                <div class="card-content">
                    <!-- Add overview content here -->
                </div>
            </div>

            <!-- Upcoming Bookings Card -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2 class="card-title">Upcoming Bookings</h2>
                </div>
                <div class="card-content">
                    <!-- Add upcoming bookings content here -->
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2 class="card-title">Quick Actions</h2>
                </div>
                <div class="card-content">
                    <a href="search.php" class="btn btn-primary">Search Listings</a>
                    <a href="#" class="btn btn-primary">View Messages</a>
                    <a href="#" class="btn btn-primary">Update Profile</a>
                </div>
            </div>
        </div>

        <!-- Recent Bookings Table -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2 class="card-title">Recent Bookings</h2>
                <a href="search.php" class="btn btn-primary">Find New Place</a>
            </div>
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>Property</th>
                        <th>Location</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Add table rows here -->
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
