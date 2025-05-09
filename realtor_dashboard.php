<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

if (isset($_GET['logout'])) {
    session_unset();                          
    session_destroy();                        
    setcookie(session_name(), '', time() - 3600, '/');
    header("Location: login.php");          
    exit();
}

require_once("config/db.php");

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Realtor') {
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Realtor Dashboard | RoofShare</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/realtor_dashboard.css"> <!-- Adjust path if needed -->
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <div class="header-content">
                <div class="user-info">
                    <h1>Welcome, <span class="user-name"><?= htmlspecialchars($name) ?></span></h1>
                    <div class="verification-status <?= strtolower(htmlspecialchars($verification)) ?>">
                        <?= htmlspecialchars($verification) ?>
                    </div>
                </div>
                <nav class="dashboard-nav">
                    <a href="?logout=1" class="logout-btn">Logout</a>
                </nav>
            </div>
        </header>

        <main class="dashboard-main">
            <?php if ($verification === 'Pending'): ?>
                <div class="alert-message warning">
                    <p>Your account is pending verification. You'll gain full access once approved.</p>
                </div>
            <?php endif; ?>

            <section class="dashboard-section">
                <h2>Your Properties</h2>
                <div class="properties-grid">
                    <!-- Property cards will be dynamically inserted here -->
                    <div class="property-card placeholder">
                        <p>No properties listed yet</p>
                        <a href="add_property.php" class="add-property-btn">Add Your First Property</a>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
