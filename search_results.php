<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Apartments | RoofShare</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Main CSS (assuming this contains styles for .listing-item, etc.) -->
    <link rel="stylesheet" href="assets/css/landing.css">
    
    <style>
        html {
            box-sizing: border-box;
        }
        *, *:before, *:after {
            box-sizing: inherit;
        }
        body {
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            background-color: #f8f9fa;
            color: #333;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .header {
            display: flex;
            justify-content: space-between; /* Changed for logo and auth links */
            align-items: center;
            padding: 0 30px;
            height: 70px;
            background-color: #fff;
            border-bottom: 1px solid #e9ecef;
        }
        .logo a {
            text-decoration: none;
            color: #ff6600; /* RoofShare orange */
            font-weight: 700;
            font-size: 1.5rem;
        }
        .auth-links {
            display: flex;
            gap: 20px;
        }
        .auth-links a {
            color: #333;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background-color 0.2s ease;
        }

        .search-filters-bar {
            background-color: #fff;
            padding: 20px 30px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap; /* Allow wrapping for smaller screens */
        }
        .search-filters-bar input[type="text"],
        .search-filters-bar select {
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        .search-filters-bar button {
            padding: 10px 20px;
            background-color: #ff6600;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            font-size: 0.9rem;
        }
        .search-filters-bar button:hover {
            background-color: #e65c00;
        }

        .main-content-area {
            display: flex;
            flex-grow: 1; /* Takes up remaining vertical space */
            overflow: hidden; /* Prevent scrollbars on this container directly */
            width: 100%; /* Explicitly set width */
            height: 0; /* ADDED: Helps solidify height calculation with flex-grow */
        }

        .map-placeholder-container {
            /* width: 60%; */ /* Removed percentage width */
            flex-grow: 1; /* Added to take remaining space */
            width: 0; /* Common practice with flex-grow */
            background-color: #e0f2e9; /* Light green placeholder */
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #28a745; /* Darker green text */
            overflow-y: auto; /* Allow map to scroll if its content is too tall, though not expected for a placeholder */
        }

        .listings-column-container {
            width: 600px; /* Increased from 500px */
            flex-shrink: 0; /* Prevent shrinking */
            padding: 20px 0; /* Keep vertical padding, remove horizontal */
            overflow-y: auto; /* Make this column scrollable */
            background-color: #fff;
            box-sizing: border-box; /* Added */
        }
        
        .listings-column-container .listing-item {
            width: 100%; /* Ensure cards try to take full width of parent column */
            margin-bottom: 20px;
            margin-right: 0 !important; 
            border-radius: 0; /* Changed from 4px for a straight divider look */
            background-color: #fff; 
            border-bottom: 3px solid #e9ecef; /* ADDED for a straight divider, NOW EVEN THICKER */
            padding: 0 15px 15px 15px; /* 0 top, 15px R, 15px B, 15px L */
        }

        .listing-item .listing-location-top {
            font-size: 1.4rem; /* Increased from 1.2rem */
            font-weight: 600;
            margin-bottom: 5px; /* Adjusted for divider */
            display: block; /* Ensure it\'s full width */
        }

        .listing-item .listing-address-sub {
            font-size: 0.85rem; /* Reduced from 0.9rem */
            color: #666;
            margin-bottom: 8px; /* Adjusted for divider */
            display: block;
        }

        .listing-item .listing-divider { /* NEW RULE */
            height: 1px;
            background-color: #e9ecef;
            margin-top: 8px;    /* Space above the divider */
            margin-bottom: 15px; /* Space below the divider */
            margin-left: -15px; /* Counteract parent's left padding */
            margin-right: -15px; /* Counteract parent's right padding */
            width: calc(100% + 30px); /* Expand to fill the space created by negative margins */
        }

        .listing-item .listing-content-wrapper { /* New flex wrapper for image and details-column */
            display: flex;
            gap: 15px; /* Space between image and details column */
            align-items: flex-start; /* Align items to the top */
        }

        .listing-item .listing-image-left { /* New class for the image on the left */
            width: 300px; /* Reduced from 340px */
            flex-shrink: 0; /* Prevent image column from shrinking */
        }

        .listing-item .listing-image-left img {
            width: 100%;
            height: 200px; /* Reduced from 230px */
            object-fit: cover;
            border-radius: 3px;
        }

        .listing-item .listing-details-right { /* New class for the right details column */
            display: flex;
            flex-direction: column; /* Stack details and button vertically */
            flex-grow: 1; /* Allow this column to take remaining space */
            justify-content: space-between; /* Push button to bottom if space allows */
            min-height: 200px; /* Match new image height */
        }
        
        .listing-item .listing-details-right .listing-info { /* Wrapper for beds/baths, price */
            font-size: 1rem; /* Increased from 0.9rem */
            margin-bottom: 5px;
        }

        .listing-item .listing-details-right .listing-price {
            font-size: 1.1rem; /* Increased from 1rem */
            font-weight: 500;
            margin-bottom: 10px;
        }

        .listing-item .listing-details-right .listing-amenities {
            font-size: 0.85rem;
            color: #555;
            margin-bottom: 10px;
            line-height: 1.4; /* Helps if amenities wrap to multiple lines */
        }

        .listing-item .view-listing-button {
            display: block; /* Changed from inline-block */
            width: 100%; /* Ensure it takes full width of its container */
            margin-top: 10px;
            padding: 10px 15px; /* Adjusted padding slightly */
            background-color: #ff6600; /* Changed to RoofShare orange */
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.9rem;
            text-align: center;
        }
        .listing-item .view-listing-button:hover {
            background-color: #e65c00; /* Changed to darker orange for hover */
        }

    </style>
</head>
<body>
    <header class="header">
        <div class="logo">
            <a href="index.php">RoofShare</a>
        </div>
        <div class="auth-links">
            <a href="login.php">Log In</a>
            <a href="register.php">Sign Up</a>
        </div>
    </header>

    <div class="search-filters-bar">
        <input type="text" placeholder="City, State, or ZIP">
        <select name="beds">
            <option value="">Beds (Any)</option>
            <option value="1">1 Bed</option>
            <option value="2">2 Beds</option>
            <option value="3">3 Beds</option>
            <option value="4+">4+ Beds</option>
        </select>
        <select name="baths">
            <option value="">Baths (Any)</option>
            <option value="1">1 Bath</option>
            <option value="1.5">1.5 Baths</option>
            <option value="2">2 Baths</option>
            <option value="2.5+">2.5+ Baths</option>
        </select>
        <input type="text" placeholder="Min Price">
        <input type="text" placeholder="Max Price">
        <button type="button">Search</button>
    </div>

    <div class="main-content-area">
        <div class="map-placeholder-container">
            Map Placeholder (Green Square)
        </div>
        <div class="listings-column-container">
            <?php
            // Placeholder listings data (mimicking index.php structure)
            $search_listings = [
                [
                    'id' => 101,
                    'image' => 'assets/images/apartment-placeholder.jpg', // Ensure this path is correct
                    'location' => 'Downtown Apartment with View',
                    'address' => '123 Main St, Anytown, ST 12345',
                    'distance' => 'Beds: 2 | Baths: 2', // Using distance field for other info
                    'price' => '$2,200 / month',
                    'amenities' => ['Gym', 'Pool', 'Rooftop Deck']
                ],
                [
                    'id' => 102,
                    'image' => 'assets/images/apartment-placeholder.jpg',
                    'location' => 'Suburban House with Yard',
                    'address' => '456 Oak Ln, Suburbia, ST 67890',
                    'distance' => 'Beds: 3 | Baths: 2.5',
                    'price' => '$2,850 / month',
                    'amenities' => ['Pet Friendly', 'Garage', 'Backyard']
                ],
                [
                    'id' => 103,
                    'image' => 'assets/images/apartment-placeholder.jpg',
                    'location' => 'Cozy Studio Near Campus',
                    'address' => '789 University Ave, Collegetown, ST 10112',
                    'distance' => 'Beds: Studio | Baths: 1',
                    'price' => '$1,500 / month',
                    'amenities' => ['Furnished', 'Utilities Included', 'On-site Laundry']
                ],
                [
                    'id' => 104,
                    'image' => 'assets/images/apartment-placeholder.jpg',
                    'location' => 'Luxury Condo, Full Amenities',
                    'address' => '101 Sky High Rd, Metropolis, ST 13141',
                    'distance' => 'Beds: 1 | Baths: 1',
                    'price' => '$1,950 / month',
                    'amenities' => ['Concierge', 'Fitness Center', 'Sauna', 'Parking']
                ]
            ];

            foreach ($search_listings as $listing):
            ?>
                <div class="listing-item">
                    <div class="listing-location-top"><?php echo htmlspecialchars($listing['location']); ?></div>
                    <?php if (!empty($listing['address'])): ?>
                        <div class="listing-address-sub">
                           <?php echo htmlspecialchars($listing['address']); ?>
                        </div>
                    <?php endif; ?>
                    <div class="listing-divider"></div>
                    <div class="listing-content-wrapper">
                        <div class="listing-image-left">
                            <img src="<?php echo htmlspecialchars($listing['image']); ?>" 
                                 alt="Property in <?php echo htmlspecialchars($listing['location']); ?>"
                                 loading="lazy">
                        </div>
                        <div class="listing-details-right">
                            <div> <!-- Wrapper for Price and Info -->
                                <div class="listing-price"><?php echo htmlspecialchars($listing['price']); ?></div>
                                <div class="listing-info"><?php echo htmlspecialchars($listing['distance']); ?></div>
                            </div>

                            <?php if (!empty($listing['amenities'])): ?>
                                <div class="listing-amenities">
                                    <?php echo htmlspecialchars(implode(', ', $listing['amenities'])); ?>
                                </div>
                            <?php endif; ?>
                            
                            <a href="listing.php?id=<?php echo htmlspecialchars($listing['id']); ?>" class="view-listing-button">View Listing</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</body>
</html> 