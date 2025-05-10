<?php
session_start();

$user_name = $_SESSION['user_name'] ?? 'temp_account';
$user_type = $_SESSION['user_type'] ?? 'Realtor';

$properties = [
    [
        'id' => 1,
        'title' => 'Property with vertical slider',
        'location' => 'Jersey City, Greenville',
        'expires' => '2017-12-17',
        'category' => 'Houses, Sales',
        'status' => 'Published',
        'price' => '$ 86,000',
        'featured' => true,
        'image' => 'assets/images/apartment-placeholder.jpg'
    ],
    [
        'id' => 2,
        'title' => 'Apartment with Subunits',
        'location' => 'Jersey City, Greenville',
        'expires' => '2017-02-17',
        'category' => 'Apartments, Sales',
        'status' => 'Published',
        'price' => '$ 999',
        'featured' => true,
        'image' => 'assets/images/apartment-placeholder.jpg'
    ],
    [
        'id' => 3,
        'title' => 'Villa On Washington Ave',
        'location' => 'New York, West Side',
        'expires' => '2017-10-15',
        'category' => 'Villas, Sales',
        'status' => 'Pending',
        'price' => '$ 5,500,000',
        'featured' => false,
        'image' => 'assets/images/apartment-placeholder.jpg'
    ],
    [
        'id' => 4,
        'title' => 'Downtown Studio',
        'location' => 'Metropolis, Downtown',
        'expires' => '2024-08-01',
        'category' => 'Studio, Rent',
        'status' => 'Expired',
        'price' => '$ 1,200 / mo',
        'featured' => false,
        'image' => 'assets/images/apartment-placeholder.jpg'
    ]
];

function getStatusCircleClass($status) {
    switch (strtolower($status)) {
        case 'published':
            return 'circle-green';
        case 'pending':
            return 'circle-yellow';
        case 'expired':
            return 'circle-red';
        default:
            return 'circle-grey';
    }
}

function getDisplayStatusText($status) {
    switch (strtolower($status)) {
        case 'published':
            return 'Approved';
        case 'pending':
            return 'Pending';
        case 'expired':
            return 'Rejected';
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
    <title>Property Dashboard</title>
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
        }
        a { text-decoration: none; color: inherit; }
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
        .main-content {
            flex-grow: 1;
            overflow-y: auto;
            background-color: #ffffff;
            padding: 80px 150px 30px;
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
            background-image: url('data:image/svg+xml,...');
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 10px auto;
            padding-right: 40px;
            outline: none;
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
            width: 120px; height: 90px;
            border-radius: 4px;
            object-fit: contain;
            position: relative;
            overflow: hidden;
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
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: 500;
            text-transform: capitalize;
        }
        .status-published { background-color: #d4edda; color: #155724; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-expired { background-color: #f8d7da; color: #721c24; }
        .status-default { background-color: #e2e3e5; color: #383d41; }
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
        .btn-view { background-color: #ff6600; }
        .btn-view:hover { background-color: #e65c00; }
        .btn-approve {
            background-color: #28a745;
            padding: 4px 12px;
            border-radius: 50px;
        }
        .btn-approve:hover { background-color: #218838; }
        .btn-reject {
            background-color: #dc3545;
            padding: 4px 12px;
            border-radius: 50px;
        }
        .btn-reject:hover { background-color: #c82333; }
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
            .property-table th, .property-table td {
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
    </style>
</head>
<body>
    <div class="dashboard-container">
        <header class="navbar">
            <div class="navbar-left">
                <nav class="navbar-links">
                    <ul>
                        <li class="nav-link active"><a href="#">My Properties,</a></li>
                        <li class="nav-link"><a href="#">Approve Properties</a></li>
                    </ul>
                </nav>
            </div>
            <div class="navbar-right">
                <div class="user-menu">
                    <div class="user-icon"><i class="fa fa-user"></i></div>
                    <a href="#" class="logout-link">(Logout)</a>
                </div>
            </div>
        </header>
        <main class="main-content">
            <div class="welcome-header">
                <h1>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>
            </div>
            <h2 class="page-title">Property List</h2>
            <section class="filter-area">
                <div class="filter-controls">
                    <div class="search-bar-wrapper">
                        <input type="text" placeholder="Search a listing">
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
                        <option>Published</option>
                        <option>Pending</option>
                        <option>Expired</option>
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
                            <th class="action-header">Approve</th>
                            <th class="action-header">Reject</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($properties as $property): ?>
                            <tr>
                                <td>
                                    <div class="property-info">
                                        <div class="property-image">
                                            <img src="<?php echo htmlspecialchars($property['image']); ?>" alt="<?php echo htmlspecialchars($property['title']); ?>">
                                        </div>
                                        <div class="property-details">
                                            <div class="title"><?php echo htmlspecialchars($property['title']); ?></div>
                                            <div class="location"><?php echo htmlspecialchars($property['location']); ?></div>
                                            <div class="expires">Expires on <?php echo htmlspecialchars($property['expires']); ?></div>
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
                                <td><a href="#">View Listing</a></td>
                                <td class="action-cell">
                                    <?php
                                    $status = strtolower($property['status']);
                                    if ($status === 'pending' || $status === 'expired'): ?>
                                        <button class="btn-approve">Approve</button>
                                    <?php endif; ?>
                                </td>
                                <td class="action-cell">
                                    <?php if ($status === 'pending' || $status === 'published'): ?>
                                        <button class="btn-reject">Reject</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>
