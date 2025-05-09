<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once("config/db.php");

if (!isset($_SESSION['user_email']) || $_SESSION['user_type'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['user_email'];

$stmt = $db->prepare("SELECT name, role FROM Admin WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();
$name = $user['name'] ?? 'Admin';
$role = $user['role'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['realtor_id'], $_POST['status'])) {
        $realtor_id = $_POST['realtor_id'];
        $status = $_POST['status'];
        if (in_array($status, ['Pending', 'Verified', 'Rejected'])) {
            $update = $db->prepare("UPDATE Realtor SET verification_status = ? WHERE realtor_id = ?");
            $update->execute([$status, $realtor_id]);
        }
    }

    if (isset($_POST['delete_listing_id'])) {
        $listing_id = intval($_POST['delete_listing_id']);
        $delete = $db->prepare("DELETE FROM Listing WHERE listing_id = ?");
        $delete->execute([$listing_id]);
    }

    if (isset($_POST['remove_realtor_id'])) {
        $realtor_id = intval($_POST['remove_realtor_id']);
        $delete_realtor = $db->prepare("DELETE FROM Realtor WHERE realtor_id = ?");
        $delete_realtor->execute([$realtor_id]);
    }
}

$pending_realtors = [];
$reported_accounts = [];
$listings = [];

if ($role === 'Administrator') {
    $pending_realtors = $db->query("SELECT realtor_id, name, email, phone_number, verification_status FROM Realtor WHERE verification_status = 'Pending'")->fetchAll();
    $reported_accounts = $db->query("SELECT report_id, realtor_id, description, strikes FROM ReportedAccount ORDER BY created_at DESC")->fetchAll();
}

if ($role === 'Moderator') {
    $listings = $db->query("
        SELECT DISTINCT l.listing_id, l.title, l.city, l.state, l.price, l.status
        FROM Listing l
        JOIN Review r ON l.listing_id = r.listing_id
        WHERE r.reported = TRUE
    ")->fetchAll();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background-color: #f4f4f4; }
        .header { background-color: #007BFF; color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center; }
        .container { padding: 30px; }
        .logout { color: white; text-decoration: none; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; background: white; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
        form { display: inline; }
        select, input[type="submit"], button, textarea, input[type="number"] { padding: 5px 10px; margin-top: 5px; }
        h2 { margin-top: 40px; }
    </style>
</head>
<body>
<div class="header">
    <div class="info">Admin (<?php echo htmlspecialchars($role); ?>)</div>
    <a class="logout" href="login.php?logout=1">Logout</a>
</div>

<div class="container">
    <h1>Welcome, <?php echo htmlspecialchars($name); ?>!</h1>

    <?php if ($role === 'Administrator'): ?>
        <h2>Pending Realtor Verifications</h2>
        <?php if (empty($pending_realtors)): ?>
            <p>No pending verifications.</p>
        <?php else: ?>
            <table>
                <tr><th>Name</th><th>Email</th><th>Phone</th><th>Status</th><th>Action</th></tr>
                <?php foreach ($pending_realtors as $realtor): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($realtor['name']); ?></td>
                        <td><?php echo htmlspecialchars($realtor['email']); ?></td>
                        <td><?php echo htmlspecialchars($realtor['phone_number']); ?></td>
                        <td><?php echo htmlspecialchars($realtor['verification_status']); ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="realtor_id" value="<?php echo $realtor['realtor_id']; ?>">
                                <select name="status">
                                    <option value="Pending">Pending</option>
                                    <option value="Verified">Verified</option>
                                    <option value="Rejected">Rejected</option>
                                </select>
                                <input type="submit" value="Update">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <h2>Reported Accounts (Strikes)</h2>
        <?php if (empty($reported_accounts)): ?>
            <p>No reported accounts.</p>
        <?php else: ?>
            <table>
                <tr><th>Realtor ID</th><th>Description</th><th>Strikes</th><th>Action</th></tr>
                <?php foreach ($reported_accounts as $report): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($report['realtor_id']); ?></td>
                        <td><?php echo htmlspecialchars($report['description']); ?></td>
                        <td><?php echo htmlspecialchars($report['strikes']); ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this Realtor?');">
                                <input type="hidden" name="remove_realtor_id" value="<?php echo $report['realtor_id']; ?>">
                                <button type="submit">Delete Realtor</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

    <?php elseif ($role === 'Moderator'): ?>
        <h2>Manage Reported Listings</h2>
        <?php if (empty($listings)): ?>
            <p>No reported listings found.</p>
        <?php else: ?>
            <table>
                <tr><th>Title</th><th>City</th><th>State</th><th>Price</th><th>Status</th><th>Action</th></tr>
                <?php foreach ($listings as $listing): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($listing['title']); ?></td>
                        <td><?php echo htmlspecialchars($listing['city']); ?></td>
                        <td><?php echo htmlspecialchars($listing['state']); ?></td>
                        <td>$<?php echo number_format($listing['price'], 2); ?></td>
                        <td><?php echo htmlspecialchars($listing['status']); ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this listing?');">
                                <input type="hidden" name="delete_listing_id" value="<?php echo $listing['listing_id']; ?>">
                                <button type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
        
    <?php else: ?>
        <p>You are logged in as a regular Admin user.</p>
    <?php endif; ?>
</div>
</body>
</html>
