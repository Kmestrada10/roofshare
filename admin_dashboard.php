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
    // Get search and filter parameters
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
    
    // Base query
    $query = "SELECT realtor_id, name, email, phone_number, verification_status FROM Realtor WHERE 1=1";
    $params = [];
    
    // Add search condition if search term exists
    if (!empty($search)) {
        $query .= " AND (name LIKE ? OR email LIKE ? OR phone_number LIKE ?)";
        $search_param = "%$search%";
        $params = array_merge($params, [$search_param, $search_param, $search_param]);
    }
    
    // Add status filter if not 'all'
    if ($status_filter !== 'all') {
        $query .= " AND verification_status = ?";
        $params[] = $status_filter;
    }
    
    // Add ordering
    $query .= " ORDER BY 
        CASE 
            WHEN verification_status = 'Pending' THEN 1
            WHEN verification_status = 'Verified' THEN 2
            ELSE 3
        END,
        name ASC";
    
    // Prepare and execute the query
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $pending_realtors = $stmt->fetchAll();
    
    // Commented out reported accounts section
    /*
    $reported_accounts = $db->query("SELECT report_id, realtor_id, description, strikes FROM ReportedAccount ORDER BY created_at DESC")->fetchAll();
    */
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Google Fonts - Montserrat -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Basic Reset and Font */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: "Montserrat", sans-serif;
            color: #333;
            background-color: #ffffff;
            min-height: 100vh;
        }
        a { text-decoration: none; color: inherit; }

        /* Navbar */
        .navbar {
            background-color: #ffffff;
            padding: 0 20px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #e0e0e0;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .navbar-left {
            display: flex;
            align-items: center;
            gap: 30px;
        }
        .navbar-logo {
            font-size: 1.5em;
            font-weight: 700;
            color: #333;
        }
        .navbar-links ul {
            list-style: none;
            display: flex;
            gap: 20px;
            margin: 0;
            padding: 0;
        }
        .navbar-links .nav-link a {
            color: #555;
            font-weight: 500;
            font-size: 0.95rem;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background-color 0.2s ease, color 0.2s ease;
            display: inline-block;
            font-family: inherit;
        }
        .navbar-links .nav-link a:hover {
            background-color: rgba(0,0,0,0.05);
        }
        .navbar-links .nav-link.active a {
            background-color: transparent;
            color: #333;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-left: auto;
        }
        .user-menu {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .user-menu .user-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: #ffe8d6;
            color: #ff6600;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }
        .user-menu .user-name {
            font-weight: 500;
            font-size: 0.9em;
        }
        .user-menu .logout-link {
            font-size: 0.9em;
            color: #777;
            margin-left: 5px;
        }
        .user-menu .logout-link:hover {
            color: #ff6600;
            text-decoration: underline;
        }

        /* Main Content Area */
        .main-content {
            flex-grow: 1;
            overflow-y: auto;
            background-color: #ffffff;
            padding: 80px 150px 30px;
            min-height: 100vh;
        }

        /* Page Title */
        .page-title {
            padding: 0;
            padding-top: 25px;
            padding-bottom: 0;
            font-size: 1.8em;
            font-weight: 500;
            color: #333;
            margin-bottom: 15px;
        }

        /* Filter/Action Area */
        .filter-area {
            padding: 25px 0;
            background-color: #ffffff;
            border-bottom: 1px solid #e0e0e0;
            margin-bottom: 20px;
        }
        .filter-controls {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .filter-controls input[type="text"],
        .filter-controls select {
            padding: 16px 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 0.9em;
            flex-grow: 1;
            background-color: white;
            height: 54px;
            font-family: inherit;
        }
        .filter-controls select {
            appearance: none;
            background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23666666%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E');
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 10px auto;
            padding-right: 40px;
            outline: none;
        }

        /* Search Bar specific styling */
        .search-bar-wrapper {
            display: flex;
            flex-basis: 60%;
            border: 1px solid #ccc;
            border-radius: 50px;
            overflow: hidden;
            background-color: white;
        }
        .search-bar-wrapper input[type="text"] {
            flex-grow: 1;
            border: none;
            padding: 16px 20px;
            border-radius: 0;
            outline: none;
            font-size: 0.9em;
            font-family: inherit;
        }
        .search-bar-wrapper button {
            background-color: white;
            color: #ff6600;
            border: none;
            border-left: 1px solid #eee;
            padding: 0 20px;
            border-radius: 0;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1em;
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        .search-bar-wrapper button:hover {
            background-color: #f8f8f8;
            color: #e65c00;
        }

        /* Table Styles */
        .property-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #ffffff;
            border-radius: 0;
            overflow: hidden;
        }
        .property-table th, .property-table td {
            padding: 20px;
            text-align: left;
            border-bottom: 1px solid #e8e8e8;
        }
        .property-table th {
            background-color: #ffffff;
            font-size: 0.85em;
            font-weight: 500;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .property-table tr:last-child td {
            border-bottom: none;
        }
        .property-table td {
            font-size: 0.9em;
            color: #444;
            vertical-align: middle;
        }
        .property-table tbody tr:hover {
            background-color: #f9f9f9;
        }

        /* Status Display */
        .status-display {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .status-circle {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }
        .circle-green { background-color: #28a745; }
        .circle-yellow { background-color: #ffc107; }
        .circle-red { background-color: #dc3545; }
        .circle-grey { background-color: #adb5bd; }

        /* Action Buttons */
        .action-cell {
            text-align: center;
        }

        .action-cell button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 90px;
            height: 36px;
            padding: 0 15px;
            color: white;
            border: none;
            font-weight: 500;
            font-size: 0.85em;
            cursor: pointer;
            transition: background-color 0.2s ease;
            text-decoration: none;
            font-family: inherit;
            text-transform: uppercase;
            border-radius: 25px;
        }

        .btn-approve {
            background-color: #28a745;
        }

        .btn-approve:hover {
            background-color: #218838;
        }

        .btn-reject {
            background-color: #dc3545;
        }

        .btn-reject:hover {
            background-color: #c82333;
        }

        .action-header {
            text-align: center;
        }

        /* Welcome Header */
        .welcome-header {
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        .welcome-header h1 {
            font-size: 2.2em;
            font-weight: 500;
            color: #333;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-content { 
                padding: 70px 20px 30px;
            }
            .filter-controls { 
                flex-direction: column; 
                align-items: stretch; 
            }
            .property-table { 
                display: block; 
                overflow-x: auto; 
            }
            .property-table th,
            .property-table td {
                padding: 15px;
                white-space: nowrap;
            }
            .page-title {
                font-size: 1.6em;
            }
        }

        /* Action Buttons Layout */
        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            align-items: center;
        }

        .action-buttons form {
            margin: 0;
        }

        .btn-pending {
            background-color: #ffc107;
            color: #000;
        }

        .btn-pending:hover {
            background-color: #e0a800;
        }

        .action-cell button {
            min-width: 90px;
            padding: 8px 15px;
            border-radius: 4px;
            font-weight: 500;
            font-size: 0.85em;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            text-transform: uppercase;
        }

        /* Update existing button styles */
        .btn-approve {
            background-color: #28a745;
            color: white;
        }

        .btn-approve:hover {
            background-color: #218838;
        }

        .btn-reject {
            background-color: #dc3545;
            color: white;
        }

        .btn-reject:hover {
            background-color: #c82333;
        }

        /* Search Form Styles */
        .search-form {
            display: flex;
            gap: 15px;
            width: 100%;
        }

        .search-form .search-bar-wrapper {
            flex: 1;
        }

        .search-form .filter-select {
            min-width: 150px;
        }

        /* Add hover effect to select */
        .filter-select:hover {
            border-color: #999;
        }

        /* Add focus effect to select */
        .filter-select:focus {
            border-color: #ff6600;
            outline: none;
            box-shadow: 0 0 0 2px rgba(255, 102, 0, 0.1);
        }

        .dashboard-container {
            min-height: 100vh;
            background-color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Navbar -->
        <header class="navbar">
            <div class="navbar-left">
                <nav class="navbar-links">
                    <ul>
                        <li class="nav-link active"><a href="#">Realtor Management</a></li>
                        <li class="nav-link"><a href="#">Reported Accounts</a></li>
                    </ul>
                </nav>
            </div>
            <div class="navbar-right">
                <div class="user-menu">
                    <div class="user-icon"><i class="fa fa-user"></i></div>
                    <span class="user-name"><?php echo htmlspecialchars($name); ?></span>
                    <a href="login.php?logout=1" class="logout-link">(Logout)</a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Welcome Header -->
            <div class="welcome-header">
                <h1>Welcome, <?php echo htmlspecialchars($name); ?>!</h1>
            </div>

            <?php if ($role === 'Administrator'): ?>
                <!-- Pending Realtors Section -->
                <h2 class="page-title">Pending Realtor Verifications</h2>
                <section class="filter-area">
                    <div class="filter-controls">
                        <form method="GET" class="search-form" style="display: flex; gap: 15px; width: 100%;">
                            <div class="search-bar-wrapper">
                                <input type="text" name="search" placeholder="Search realtors..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                                <button type="submit"><i class="fa fa-search"></i></button>
                            </div>
                            <select name="status" class="filter-select" onchange="this.form.submit()">
                                <option value="all" <?php echo ($status_filter ?? 'all') === 'all' ? 'selected' : ''; ?>>All Status</option>
                                <option value="Pending" <?php echo ($status_filter ?? '') === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="Verified" <?php echo ($status_filter ?? '') === 'Verified' ? 'selected' : ''; ?>>Verified</option>
                                <option value="Rejected" <?php echo ($status_filter ?? '') === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </form>
                    </div>
                </section>

                <section class="property-list">
                    <table class="property-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th class="action-header">Approve</th>
                                <th class="action-header">Reject</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_realtors as $realtor): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($realtor['name']); ?></td>
                                    <td><?php echo htmlspecialchars($realtor['email']); ?></td>
                                    <td><?php echo htmlspecialchars($realtor['phone_number']); ?></td>
                                    <td>
                                        <div class="status-display">
                                            <span class="status-circle <?php echo $realtor['verification_status'] === 'Pending' ? 'circle-yellow' : ($realtor['verification_status'] === 'Verified' ? 'circle-green' : 'circle-red'); ?>"></span>
                                            <span class="status-text"><?php echo htmlspecialchars($realtor['verification_status']); ?></span>
                                        </div>
                                    </td>
                                    <td class="action-cell">
                                        <?php if ($realtor['verification_status'] !== 'Verified'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="realtor_id" value="<?php echo $realtor['realtor_id']; ?>">
                                                <input type="hidden" name="status" value="Verified">
                                                <button type="submit" class="btn-approve" title="Approve Realtor">Approve</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                    <td class="action-cell">
                                        <?php if ($realtor['verification_status'] !== 'Rejected'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="realtor_id" value="<?php echo $realtor['realtor_id']; ?>">
                                                <input type="hidden" name="status" value="Rejected">
                                                <button type="submit" class="btn-reject" title="Reject Realtor">Reject</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>

                <!-- Commented out Reported Accounts Section
                <h2 class="page-title" style="margin-top: 40px;">Reported Accounts</h2>
                <section class="property-list">
                    <table class="property-table">
                        <thead>
                            <tr>
                                <th>Realtor ID</th>
                                <th>Description</th>
                                <th>Strikes</th>
                                <th class="action-header">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reported_accounts as $report): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($report['realtor_id']); ?></td>
                                    <td><?php echo htmlspecialchars($report['description']); ?></td>
                                    <td><?php echo htmlspecialchars($report['strikes']); ?></td>
                                    <td class="action-cell">
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this Realtor?');">
                                            <input type="hidden" name="remove_realtor_id" value="<?php echo $report['realtor_id']; ?>">
                                            <button type="submit" class="btn-reject">Delete Realtor</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
                -->

            <?php elseif ($role === 'Moderator'): ?>
                <!-- Moderator View -->
                <h2 class="page-title">Reported Listings</h2>
                <section class="filter-area">
                    <div class="filter-controls">
                        <div class="search-bar-wrapper">
                            <input type="text" placeholder="Search listings...">
                            <button><i class="fa fa-search"></i></button>
                        </div>
                        <select class="filter-select">
                            <option>All Status</option>
                            <option>Active</option>
                            <option>Reported</option>
                        </select>
                    </div>
                </section>

                <section class="property-list">
                    <table class="property-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Location</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th class="action-header">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($listings as $listing): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($listing['title']); ?></td>
                                    <td><?php echo htmlspecialchars($listing['city'] . ', ' . $listing['state']); ?></td>
                                    <td>$<?php echo number_format($listing['price'], 2); ?></td>
                                    <td>
                                        <div class="status-display">
                                            <span class="status-circle <?php echo $listing['status'] === 'Active' ? 'circle-green' : 'circle-red'; ?>"></span>
                                            <span class="status-text"><?php echo htmlspecialchars($listing['status']); ?></span>
                                        </div>
                                    </td>
                                    <td class="action-cell">
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this listing?');">
                                            <input type="hidden" name="delete_listing_id" value="<?php echo $listing['listing_id']; ?>">
                                            <button type="submit" class="btn-reject">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
            <?php else: ?>
                <p>You are logged in as a regular Admin user.</p>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
