<?php
session_start();

// Default user info if not logged in (for demonstration)
$user_name = $_SESSION['user_name'] ?? 'temp_account';
$user_type = $_SESSION['user_type'] ?? 'Realtor'; // Example type

// Sample property data (replace with actual data fetching later)
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
        'image' => 'assets/images/apartment-placeholder.jpg' // Placeholder image
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
        'status' => 'Pending', // Different status example
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
        'status' => 'Expired', // Different status example
        'price' => '$ 1,200 / mo',
        'featured' => false,
        'image' => 'assets/images/apartment-placeholder.jpg'
    ]
];

// Function to get status circle CSS class
function getStatusCircleClass($status) {
    switch (strtolower($status)) {
        case 'published':
            return 'circle-green';
        case 'pending':
            return 'circle-yellow';
        case 'expired': // Assuming Expired maps to Rejected
            return 'circle-red';
        default:
            return 'circle-grey'; // Default grey circle
    }
}

// Function to get display text for status
function getDisplayStatusText($status) {
    switch (strtolower($status)) {
        case 'published':
            return 'Approved';
        case 'pending':
            return 'Pending';
        case 'expired': // Assuming Expired maps to Rejected
            return 'Rejected';
        default:
            return ucfirst(strtolower($status)); // Default: Capitalize the status
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Dashboard</title>
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
            font-family: "Montserrat", sans-serif; /* Match listing.css */
            color: #333;
            background-color: #f8f9fa; /* Lighter gray background */
        }
        a { text-decoration: none; color: inherit; }

        /* Navbar */
        .navbar {
            background-color: #ffffff;
            padding: 0 20px; /* Match listing.css */
            height: 80px; /* Match listing.css */
            display: flex;
            align-items: center; /* Vertically center items */
            justify-content: space-between;
            border-bottom: 1px solid #e0e0e0;
            position: sticky;
            top: 0;
            z-index: 100; /* Ensure it's above content */
        }
        .navbar-left {
            display: flex;
            align-items: center;
            gap: 30px; /* Space between logo and links */
        }
        .navbar-logo {
            font-size: 1.5em;
            font-weight: 700;
            color: #333; /* Changed from orange to dark gray */
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
            font-family: inherit; /* Explicitly inherit font */
        }
        .navbar-links .nav-link a:hover {
             /* Removed orange color on hover */
             background-color: rgba(0,0,0,0.05); /* Keep subtle background change */
        }
        .navbar-links .nav-link.active a {
            background-color: transparent;
            color: #333; /* Changed from orange to dark gray */
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 20px; /* Adjust gap */
            margin-left: auto; /* Push to the right */
        }
        .user-menu {
            display: flex;
            align-items: center;
            gap: 8px; /* Smaller gap */
        }
        .user-menu .user-icon {
            width: 36px; /* Slightly larger icon */
            height: 36px;
            border-radius: 50%;
            background-color: #ffe8d6;
            color: #ff6600; /* Primary orange */
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
            padding: 80px 150px 30px; /* Increased L/R padding further */
        }

        /* Page Title */
        .page-title {
            padding: 0; /* Removed LR padding, use main-content padding */
            padding-top: 25px;
            padding-bottom: 0; /* Reset bottom padding */
            font-size: 1.8em;
            font-weight: 500; /* Reduced from 600 */
            color: #333;
            margin-bottom: 15px; /* Adjusted margin */
        }

        /* Filter/Action Area */
        .filter-area {
            padding: 25px 0; /* Removed LR padding */
            background-color: #ffffff;
            border-bottom: 1px solid #e0e0e0;
            margin-bottom: 20px; /* Space before table */
        }
        .filter-controls {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .filter-controls input[type="text"],
        .filter-controls select {
            padding: 16px 15px; /* Increased vertical padding for select */
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 0.9em;
            flex-grow: 1;
            background-color: white;
            height: 54px; /* Match approximate height of padded input */
            font-family: inherit; /* Ensure font is inherited */
        }
        .filter-controls select {
            appearance: none;
            background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23666666%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E');
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 10px auto;
            padding-right: 40px; /* Space for arrow */
            outline: none;
            font-size: 0.9em;
            font-family: inherit; /* Ensure font is inherited */
        }
        .filter-controls button {
            padding: 12px 25px;
            background-color: #ff6600; /* Orange button */
            color: white;
            border: none;
            border-radius: 8px; /* Consistent radius */
            font-weight: 600;
            font-size: 0.9em;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        .filter-controls button:hover {
            background-color: #e65c00; /* Darker orange on hover */
        }
        .filter-controls .search-input { flex-basis: 40%; }
        .filter-controls .filter-select { flex-basis: 15%; }

        /* Search Bar specific styling */
        .search-bar-wrapper {
            display: flex;
            flex-basis: 60%; /* Increased width further */
            border: 1px solid #ccc;
            border-radius: 50px; /* Fully rounded */
            overflow: hidden; /* Clip children */
            background-color: white;
        }
        .search-bar-wrapper input[type="text"] {
            flex-grow: 1;
            border: none;
            padding: 16px 20px; /* Increased vertical padding */
            border-radius: 0; /* Remove individual radius */
            outline: none;
            font-size: 0.9em;
            font-family: inherit; /* Ensure font is inherited */
        }
        .search-bar-wrapper button {
            background-color: white;
            color: #ff6600; /* Orange icon */
            border: none;
            border-left: 1px solid #eee; /* Subtle separator */
            padding: 0 20px; /* Adjust padding for icon */
            border-radius: 0; /* Remove individual radius */
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1em; /* Adjust icon size if needed */
            transition: background-color 0.2s ease, color 0.2s ease;
            font-family: inherit; /* Ensure font is inherited */
        }
        .search-bar-wrapper button:hover {
            background-color: #f8f8f8;
            color: #e65c00; /* Darker orange icon */
        }

        /* Property List Table */
        .property-list {
            padding: 0; /* Removed LR padding */
            padding-top: 20px;
        }
        .property-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #ffffff; /* Ensure white background */
            border-radius: 0; /* Remove table radius */
            overflow: hidden;
            box-shadow: none; /* Removed shadow */
        }
        .property-table th, .property-table td {
            padding: 20px 20px; /* Increased padding */
            text-align: left;
            border-bottom: 1px solid #e8e8e8; /* Lighter border */
        }
        .property-table th {
            background-color: #ffffff; /* Remove header background */
            font-size: 0.85em;
            font-weight: 500; /* Lighter header font */
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .property-table tr:last-child td {
            border-bottom: none; /* Remove border on last row */
        }
        .property-table td {
            font-size: 0.9em;
            color: #444;
            vertical-align: middle;
        }
        .property-table tbody tr:hover {
            background-color: #f9f9f9; /* Subtle row hover */
        }
        .property-info { display: flex; align-items: center; gap: 15px; }
        .property-image {
            width: 120px; height: 90px; /* Increased size */
            border-radius: 4px; /* Smaller radius for images */
            object-fit: contain; /* Changed from cover */
            position: relative;
            overflow: hidden; /* Ensure badge stays within bounds */
        }
        .property-details .title {
            font-weight: 500; /* Reduced from 600 */
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
            border-radius: 12px; /* Pill shape */
            font-size: 0.8em;
            font-weight: 500;
            text-transform: capitalize;
        }
        .status-published { background-color: #d4edda; color: #155724; } /* Greenish */
        .status-pending { background-color: #fff3cd; color: #856404; } /* Yellowish */
        .status-expired { background-color: #f8d7da; color: #721c24; } /* Reddish */
        .status-default { background-color: #e2e3e5; color: #383d41; } /* Gray */

        /* New Status Display Styles */
        .status-display {
            display: flex;
            align-items: center;
            gap: 8px; /* Space between circle and text */
        }
        .status-circle {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }
        .circle-green { background-color: #28a745; } /* Green */
        .circle-yellow { background-color: #ffc107; } /* Yellow */
        .circle-red { background-color: #dc3545; } /* Red */
        .circle-grey { background-color: #adb5bd; } /* Grey */

        .status-text {
            color: #333; /* Black/dark grey text */
            font-size: 0.9em;
        }

        .actions {
            white-space: nowrap; /* Prevent buttons from wrapping */
        }

        .action-cell {
            text-align: center; /* Center buttons in the cell */
        }

        /* Base styles for action buttons in cells */
        .action-cell button {
             display: inline-block;
             padding: 8px 15px; /* Base padding before override */
             color: white; /* Ensure text is white */
             border: none;
             font-weight: 500;
             font-size: 0.85em;
             cursor: pointer;
             transition: background-color 0.2s ease;
             text-decoration: none; /* For link buttons */
             font-family: inherit; /* Ensure font is inherited */
             text-transform: uppercase; /* Make text uppercase */
             text-align: center; /* Center text */
             min-width: 80px; /* Further reduced minimum width */
         }

         .btn-view {
             background-color: #ff6600; /* Orange */
         }
         .btn-view:hover {
            background-color: #e65c00; /* Darker orange */
         }

         .btn-approve {
            background-color: #28a745; /* Vibrant Green */
            padding: 4px 12px; /* Further reduced padding */
            border-radius: 50px; /* Pill shape */
         }
         .btn-approve:hover {
            background-color: #218838; /* Darker Vibrant Green */
         }

         .btn-reject {
             background-color: #dc3545; /* Vibrant Red */
             padding: 4px 12px; /* Further reduced padding */
             border-radius: 50px; /* Pill shape */
         }
         .btn-reject:hover {
             background-color: #c82333; /* Darker Vibrant Red */
         }

         /* Style for table links like View Listing */
         .property-table td a {
            color: #333; /* Black/dark grey */
            text-decoration: none;
         }
         .property-table td a:hover {
            text-decoration: underline;
         }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            /* Remove sidebar related responsive rules if they exist */
            .main-content { padding-top: 70px; /* Adjust if navbar height changes on mobile */}
            .filter-controls { flex-direction: column; align-items: stretch; }
            .property-table { display: block; overflow-x: auto; } /* Allow table scroll */
            .property-table th,
            .property-table td {
                 padding: 15px; /* Adjust padding for mobile */
                 white-space: nowrap; /* Prevent wrapping in table cells */
            }
            .page-title {
                 font-size: 1.6em;
            }
        }

        .action-header {
            text-align: center; /* Center action headers */
        }

        /* Welcome Header Styling */
        .welcome-header {
            padding-bottom: 15px; /* Space below welcome */
            margin-bottom: 15px; /* Space below welcome */
        }
        .welcome-header h1 {
            font-size: 2.2em; /* Increased size */
            font-weight: 500; /* Reduced from 600 */
            color: #333;
        }

    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Navbar -->
        <header class="navbar">
            <div class="navbar-left">
                <!-- Logo removed -->
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
                    <a href="#" class="logout-link">(Logout)</a> <!-- Basic Logout Link -->
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Welcome Header -->
            <div class="welcome-header">
                <h1>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>
            </div>

            <!-- Page Title -->
            <h2 class="page-title">Property List</h2>

            <!-- Filter Area -->
            <section class="filter-area">
                <div class="filter-controls">
                    <!-- Search Bar Wrapper -->
                    <div class="search-bar-wrapper">
                        <input type="text" placeholder="Search a listing">
                        <button><i class="fa fa-search"></i></button>
                    </div>
                    <!-- Filter Selects -->
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

            <!-- Property List -->
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
                                <td>
                                    <a href="#">View Listing</a>
                                </td>
                                <td class="action-cell"> <?php /* Accept Column */
                                    $status = strtolower($property['status']);
                                    if ($status === 'pending' || $status === 'expired'): ?>
                                        <button class="btn-approve">Approve</button>
                                    <?php endif; ?>
                                </td>
                                <td class="action-cell"> <?php /* Reject Column */
                                    if ($status === 'pending' || $status === 'published'): ?>
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