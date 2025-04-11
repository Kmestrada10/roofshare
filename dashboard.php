<?php
session_start();

if (!isset($_SESSION['user_email']) || !isset($_SESSION['user_type'])) {
    header("Location: index.php");
    exit();
}

require_once("config/db.php");

$email = $_SESSION['user_email'];
$type = $_SESSION['user_type'];


if ($type === 'Admin') {
    $stmt = $db->prepare("SELECT name FROM Admin WHERE email = ?");
} elseif ($type === 'Realtor') {
    $stmt = $db->prepare("SELECT name, verification_status FROM Realtor WHERE email = ?");
} else {
    $stmt = $db->prepare("SELECT name FROM Renter WHERE email = ?");
}

$stmt->execute([$email]);
$user = $stmt->fetch();
$name = $user['name'] ?? 'User';
$verification = $user['verification_status'] ?? '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .header {
            background-color: #007BFF;
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header .info {
            font-size: 16px;
        }
        .container {
            padding: 30px;
        }
        a.logout {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="info">
            <?php echo htmlspecialchars($type); ?><?php if ($verification): ?> (<?php echo htmlspecialchars($verification); ?>)<?php endif; ?>
        </div>
        <a class="logout" href="index.php?logout=1">Logout</a>
    </div>

    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($name); ?>!</h1>
    </div>
</body>
</html>

<?php
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>



