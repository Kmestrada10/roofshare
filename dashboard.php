<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['user_email']) || !isset($_SESSION['user_type'])) {
    header("Location: index.php");
    exit();
}


require_once("config/db.php");

$email = $_SESSION['user_email'];
$type = $_SESSION['user_type'];

if ($type === 'Admin') {
    $stmt = $db->prepare("SELECT name, role FROM Admin WHERE email = ?");
} elseif ($type === 'Realtor') {
    $stmt = $db->prepare("SELECT name, verification_status FROM Realtor WHERE email = ?");
} else {
    $stmt = $db->prepare("SELECT name FROM Renter WHERE email = ?");
}

$stmt->execute([$email]);
$user = $stmt->fetch();
$name = $user['name'] ?? 'Renter';
$role = $user['role'] ?? '';
$verification = $user['verification_status'] ?? '';


if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>RoofShare Dashboard</title>
    <style>
        body { 
            margin: 0; 
            font-family: 'Montserrat', sans-serif;
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
        .container { 
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .logout { 
            color: white;
            text-decoration: none;
            font-weight: bold;
            padding: 8px 15px;
            border-radius: 4px;
            background-color: rgba(255,255,255,0.2);
        }
        .logout:hover {
            background-color: rgba(255,255,255,0.3);
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="info">
            <?php echo htmlspecialchars($type); ?>
            <?php if ($verification): ?> 
                (<?php echo htmlspecialchars($verification); ?>)
            <?php endif; ?>
        </div>
        <a class="logout" href="dashboard.php?logout=1">Logout</a>
    </div>

    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($name); ?>!</h1>
        <?php if ($type === 'Admin'): ?>
            <p>Admin dashboard content will go here</p>
        <?php elseif ($type === 'Realtor'): ?>
            <p>Realtor dashboard content will go here</p>
        <?php else: ?>
            <p>Renter dashboard content will go here</p>
        <?php endif; ?>
    </div>
</body>
</html>
