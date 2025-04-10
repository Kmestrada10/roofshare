<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

$user_type = $_SESSION['user_type'];
$table = ucfirst($user_type);
$stmt = $pdo->prepare("SELECT name FROM $table WHERE ".$user_type."_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<h1>Welcome <?= htmlspecialchars($user['name']) ?></h1>
<p>You are logged in as <?= $user_type ?></p>
<a href="auth/logout.php">Logout</a>

<?php if ($user_type === 'renter'): ?>
    <h2>Renter Dashboard</h2>
    <p>Search for apartments and roommates here</p>
<?php elseif ($user_type === 'realtor'): ?>
    <h2>Realtor Dashboard</h2>
    <p>Manage your property listings here</p>
<?php endif; ?>