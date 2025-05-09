<?php
// renter_dashboard.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once("config/db.php");

if ($_SESSION['user_type'] !== 'Renter') {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['user_email'];
$stmt = $db->prepare("SELECT name FROM Renter WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();
$name = $user['name'] ?? 'Renter';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background-color: #f4f4f4; }
        .header { background-color: #007BFF; color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center; }
        .header a { color: white; margin-left: 20px; text-decoration: none; font-weight: bold; }
        .container { padding: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="info">Renter</div>
        <div>
            <a href="roommate_matches.php">Matches</a>
            <a href="roommate_preferences.php">Find Roommate</a>
            <a href="login.php?logout=1">Logout</a>
        </div>
    </div>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($name); ?>!</h1>
    </div>
</body>
</html>
