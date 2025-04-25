<?php
session_start();
require_once("config/db.php");

if ($_SESSION['user_type'] !== 'Realtor') {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['user_email'];
$stmt = $db->prepare("SELECT name, verification_status FROM Realtor WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();
$name = $user['name'] ?? 'Realtor';
$verification = $user['verification_status'] ?? '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background-color: #f4f4f4; }
        .header { background-color: #007BFF; color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center; }
        .container { padding: 30px; }
        .logout { color: white; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="info">Realtor<?php if ($verification): ?> (<?php echo htmlspecialchars($verification); ?>)<?php endif; ?></div>
        <a class="logout" href="login.php?logout=1">Logout</a>
    </div>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($name); ?>!</h1>
    </div>
</body>
</html>
