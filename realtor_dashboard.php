<?php
session_start();

if (isset($_GET['logout'])) {
    session_unset();                          
    session_destroy();                        
    setcookie(session_name(), '', time() - 3600, '/');
    header("Location: login.php");          
    exit();
}

require_once("config/db.php"); 

if (!isset($_SESSION['user_type']) || strtolower($_SESSION['user_type']) !== 'realtor') {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['user_email'];
$stmt = $db->prepare("SELECT realtor_id, name, verification_status FROM Realtor WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();
$name = $user['name'] ?? 'Realtor';
$verification = $user['verification_status'] ?? '';
$realtor_id = $user['realtor_id'] ?? null;

if (!$realtor_id) {
    error_log("No realtor_id found for email: " . $email);
}

try {
    $stmt = $db->prepare("
        SELECT 
            l.*,
            COALESCE(
                (SELECT photo_url FROM ListingPhoto WHERE listing_id = l.listing_id ORDER BY photo_order LIMIT 1),
                'assets/images/apartment-placeholder.jpg'
            ) as image
        FROM Listing l
        WHERE l.realtor_id = ?
        ORDER BY l.created_at DESC
    ");
    $stmt->execute([$realtor_id]);
    $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($listings as &$property) {
        $property['category'] = ucfirst($property['property_type']);
        $property['location'] = $property['city'] . ', ' . $property['state'];
        $property['price'] = '$' . number_format($property['price']);
        $property['expiry_date'] = date('M d, Y', strtotime($property['created_at'] . ' +30 days'));
    }
} catch (PDOException $e) {
    error_log("Error fetching listings: " . $e->getMessage());
    $listings = [];
}

function getStatusCircleClass($status) {
    switch (strtolower($status)) {
        case 'available':
            return 'circle-green';
        case 'rented':
            return 'circle-red';
        default:
            return 'circle-grey';
    }
}

function getDisplayStatusText($status) {
    switch (strtolower($status)) {
        case 'available':
            return 'Available';
        case 'rented':
            return 'Rented';
        default:
            return ucfirst(strtolower($status));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Realtor Dashboard | RoofShare</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: "Montserrat", sans-serif;
            color: #333;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        a { text-decoration: none; color: inherit; }

        .dashboard-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .main-content {
            flex: 1;
            padding: 20px 150px 30px;
            background-color: #ffffff;
        }

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
        }
        .navbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .nav-link {
            font-size: 0.95em;
            color: #555;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 4px;
        }
        .logout-link {
            font-size: 0.95em;
            color: #555;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 4px;
        }

        .page-title {
            padding: 0;
            padding-top: 25px;
            padding-bottom: 0;
            font-size: 1.8em;
            font-weight: 500;
            color: #333;
            margin-bottom: 15px;
        }

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
            font-size: 0.9em;
            font-family: inherit;
        }
        .filter-controls button {
            padding: 12px 25px;
            background-color: #ff6600;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9em;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        .filter-controls button:hover {
            background-color: #e65c00;
        }
        .filter-controls .search-input { flex-basis: 40%; }
        .filter-controls .filter-select { flex-basis: 15%; }

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
            font-family: inherit;
        }
        .search-bar-wrapper button:hover {
            background-color: #f8f8f8;
            color: #e65c00;
        }

        .property-list {
            padding: 0;
            padding-top: 20px;
        }
        .property-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #ffffff;
            border-radius: 0;
            overflow: hidden;
            box-shadow: none;
        }
        .property-table th, .property-table td {
            padding: 20px 20px;
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
        .property-info { display: flex; align-items: center; gap: 15px; }
        .property-image {
            width: 120px;
            height: 90px;
            border-radius: 4px;
            overflow: hidden;
            flex-shrink: 0;
        }
        .property-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }
        .property-details .title {
            font-weight: 500;
            color: #333;
            margin-bottom: 2px;
        }
        .property-details .location, .property-details .expires {
            font-size: 0.85em;
            color: #777;
        }
        .property-details .expires {
            font-size: 0.8em;
            color: #999;
        }

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
        .circle-red { background-color: #dc3545; }
        .circle-grey { background-color: #adb5bd; }

        .status-text {
            color: #333;
            font-size: 0.9em;
        }

        .actions {
            white-space: nowrap;
        }

        .action-cell {
            text-align: center;
        }

        .action-cell button {
            display: inline-block;
            padding: 8px 15px;
            color: white;
            border: none;
            font-weight: 500;
            font-size: 0.85em;
            cursor: pointer;
            transition: background-color 0.2s ease;
            text-decoration: none;
            font-family: inherit;
            text-transform: uppercase;
            text-align: center;
            min-width: 80px;
        }

        .btn-view {
            background-color: transparent;
            color: #ff6600;
            border: none;
            outline: none;
            text-decoration: none;
            font-weight: 500;
        }
        .btn-view:hover {
            color: #e65c00;
            text-decoration: underline;
        }
        .btn-view:focus {
            outline: none;
            box-shadow: none;
        }

        .btn-remove {
            background-color: #dc3545;
            padding: 4px 12px;
            border-radius: 50px;
        }
        .btn-remove:hover {
            background-color: #c82333;
        }

        .property-table td a {
            color: #333;
            text-decoration: none;
        }
        .property-table td a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .main-content { padding-top: 70px; }
            .filter-controls { flex-direction: column; align-items: stretch; }
            .property-table { display: block; overflow-x: auto; }
            .property-table th,
            .property-table td {
                padding: 15px;
                white-space: nowrap;
            }
            .page-title {
                font-size: 1.6em;
            }
        }

        .action-header {
            text-align: center;
        }

        .welcome-header {
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        .welcome-header h1 {
            font-size: 2.2em;
            font-weight: 500;
            color: #333;
        }

        .verification-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.85em;
            font-weight: 500;
            margin-left: 10px;
        }
        .verification-status.pending {
            background-color: #ffc107;
            color: #000;
        }
        .verification-status.verified {
            background-color: #28a745;
            color: #fff;
        }
        .verification-status.rejected {
            background-color: #dc3545;
            color: #fff;
        }

    </style>
</head>
<body>
    <div class="dashboard-container">
        <header class="navbar">
            <div class="navbar-left">
                    </div>
            <div class="navbar-right">
                <a href="index.php" class="nav-link">Home</a>
                <a href="add_listing.php" class="nav-link">Create Listing</a>
                <a href="index.php?logout=1" class="logout-link">Logout</a>
            </div>
        </header>

        <main class="main-content">
            <div class="welcome-header">
                <h1>Welcome, <?php echo htmlspecialchars($name); ?>! 
                    <?php if ($verification): ?>
                        <span class="verification-status <?php echo strtolower(htmlspecialchars($verification)); ?>">
                            <?php echo htmlspecialchars($verification); ?>
                        </span>
                    <?php endif; ?>
                </h1>
                </div>

            <h2 class="page-title">Your Listings</h2>

            <section class="filter-area">
                <div class="filter-controls">
                    <div class="search-bar-wrapper">
                        <input type="text" placeholder="Search your listings">
                        <button><i class="fa fa-search"></i></button>
                    </div>
                    <select class="filter-select">
                        <option>Order By</option>
                        <option>Date Added</option>
                        <option>Price (Low to High)</option>
                        <option>Price (High to Low)</option>
                    </select>
                    <select class="filter-select">
                        <option>Filter By Status</option>
                        <option>Available</option>
                        <option>Rented</option>
                    </select>
                </div>
            </section>

            <section class="property-list">
                <table class="property-table">
                    <thead>
                        <tr>
                            <th>Property</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Price</th>
                            <th>View</th>
                            <th class="action-header">Remove</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($listings)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 20px;">
                                    No listings found. <a href="add_listing.php">Create your first listing</a>!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($listings as $property): ?>
                                <tr>
                                    <td>
                                        <div class="property-info">
                                            <div class="property-image">
                                                <img src="<?php echo htmlspecialchars($property['image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($property['title']); ?>">
                                            </div>
                                            <div class="property-details">
                                                <div class="title"><?php echo htmlspecialchars($property['title']); ?></div>
                                                <div class="location"><?php echo htmlspecialchars($property['location']); ?></div>
                                                <div class="expires">Expires on <?php echo htmlspecialchars($property['expiry_date']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($property['category']); ?></td>
                                    <td>
                                        <div class="status-display">
                                            <span class="status-circle <?php echo getStatusCircleClass($property['status']); ?>"></span>
                                            <span class="status-text"><?php echo getDisplayStatusText($property['status']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($property['price']); ?></td>
                                    <td>
                                        <a href="listing.php?id=<?php echo $property['listing_id']; ?>" class="btn-view">View Listing</a>
                                    </td>
                                    <td class="action-cell">
                                        <button class="btn-remove" onclick="removeListing(<?php echo $property['listing_id']; ?>)">Remove</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <script>
        async function removeListing(listingId) {
            if (!confirm('Are you sure you want to remove this listing?')) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('listing_id', listingId);

                const response = await fetch('delete_listing.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                
                if (data.success) {
                    const row = document.querySelector(`button[onclick="removeListing(${listingId})"]`).closest('tr');
                    row.remove();
                } else {
                    alert(data.message || 'An error occurred while removing the listing');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while removing the listing');
            }
        }
    </script>
</body>
</html>
