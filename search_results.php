<?php
require_once("config/db.php");
session_start();


$items_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Apartments | RoofShare</title>
    
   
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    
  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

  
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBEYlDB7H0z4_06e7MPKycHK12jw4lpnyg&libraries=places"></script>


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
            justify-content: flex-end;
            align-items: center;
            padding: 0 30px;
            height: 70px;
            background-color: #fff;
            border-bottom: 1px solid #e9ecef;
        }
        .logo a, .nav-links, .nav-link {
            display: none;
        }
        .search-filters-bar {
            background-color: #fff;
            padding: 20px 30px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: nowrap;
            width: 100%;
        }

        .search-filters-bar form {
            display: flex;
            gap: 15px;
            align-items: center;
            width: 100%;
            flex-wrap: nowrap;
        }

        .search-filters-bar input[type="text"],
        .search-filters-bar input[type="number"],
        .search-filters-bar select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 50px;
            font-size: 0.9rem;
            font-family: 'Montserrat', sans-serif;
            height: 40px;
            box-sizing: border-box;
            min-width: 120px;
            background-color: white;
        }

        .search-filters-bar input[type="number"] {
            width: 120px;
            padding-right: 8px;
        }

        .search-filters-bar select {
            appearance: none;
            background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23666666%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E');
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 10px auto;
            padding-right: 30px;
        }

        .search-filters-bar .main-search-wrapper {
            display: flex;
            flex: 1;
            max-width: 450px;
            border: 1px solid #ddd;
            border-radius: 50px;
            overflow: hidden;
            background-color: white;
            align-items: stretch;
        }

        .search-filters-bar .main-search-wrapper input[type="text"] {
            flex-grow: 1;
            border: none;
            padding: 8px 12px;
            border-radius: 0;
            outline: none;
            font-size: 0.9rem;
            font-family: 'Montserrat', sans-serif;
            background-color: transparent;
            margin: 0;
            min-width: 0;
        }

        .search-filters-bar .main-search-wrapper button {
            background-color: #ff6600;
            color: white;
            border: none;
            padding: 0 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            transition: background-color 0.2s ease;
            border-radius: 0 50px 50px 0;
        }

        .search-filters-bar .main-search-wrapper button:hover {
            background-color: #e65c00;
        }

        .search-filters-bar .main-search-wrapper button i {
            color: white;
        }

        .main-content-area {
            display: flex;
            flex-grow: 1; 
            overflow: hidden; 
            width: 100%; 
            height: 0; 
        }

        #map-container { 
            flex-grow: 1;
            width: 0; 
            overflow-y: hidden; 
            height: 100%; 
        }

        .listings-column-container {
            width: 600px; 
            flex-shrink: 0; 
            padding: 20px 0; 
            overflow-y: auto; 
            background-color: #fff;
            box-sizing: border-box; 
        }
        
        .listings-column-container .listing-item {
            width: 100%; 
            margin-bottom: 20px;
            margin-right: 0 !important; 
            border-radius: 0; 
            background-color: #fff; 
            border-bottom: 3px solid #e9ecef; 
            padding: 0 15px 15px 15px; 
        }

        .listing-item .listing-location-top {
            font-size: 1.4rem; 
            font-weight: 600;
            margin-bottom: 2px; 
            display: block; 
        }

        .listing-item .listing-address-sub {
            font-size: 0.85rem; 
            color: #666;
            margin-bottom: 8px; 
            display: block;
        }

        .listing-item .listing-divider { 
            height: 1px;
            background-color: #e9ecef;
            margin-top: 8px;    
            margin-bottom: 15px; 
            margin-left: -15px; 
            margin-right: -15px; 
            width: calc(100% + 30px); 
        }

        .listing-item .listing-content-wrapper { 
            display: flex;
            gap: 15px; 
            align-items: flex-start; 
        }

        .listing-item .listing-image-left { 
            width: 300px; 
            flex-shrink: 0; 
        }

        .listing-item .listing-image-left img {
            width: 100%;
            height: 200px; 
            object-fit: cover;
            border-radius: 3px;
        }

        .listing-item .listing-details-right {
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            justify-content: space-between;
            min-height: 200px;
        }

        .listing-item .listing-details-right .listing-info {
            margin-bottom: 10px;
        }

        .listing-item .listing-details-right .listing-price {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .listing-item .listing-details-right .listing-beds-baths {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 10px;
        }

        .listing-item .listing-details-right .listing-type {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 5px;
        }

        .listing-item .listing-details-right .listing-distance {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 15px;
        }

        .listing-item .view-listing-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #ff6600;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: 500;
            text-align: center;
            transition: background-color 0.2s ease;
        }

        .listing-item .view-listing-button:hover {
            background-color: #e65c00;
        }

        .gmnoprint, .gm-style-cc {
            display: none !important;
        }
        
 
        .gm-style a[href^="https://maps.google.com/maps"] {
            display: none !important;
        }
        
        .gm-style-iw {
            padding: 0 !important;
        }
        
        .gm-style-iw-d {
            overflow: hidden !important;
            padding: 0 !important;
        }
        
        .gm-style-iw-c {
            padding: 0 !important;
            max-width: 300px !important;
        }
        
        .gm-ui-hover-effect {
            display: none !important;
        }

        .pac-container {
            border-radius: 8px !important;
            margin-top: 5px !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1) !important;
            border: 1px solid #ddd !important;
            font-family: 'Montserrat', sans-serif !important;
        }

        .pac-item {
            padding: 8px 12px !important;
            font-size: 0.9rem !important;
            border-top: none !important;
        }

        .pac-item:first-child {
            border-top: none !important;
        }

        .pac-item:hover {
            background-color: #f8f9fa !important;
        }

        .pac-icon {
            display: none !important;
        }

        .pac-item-query {
            font-size: 0.9rem !important;
            color: #333 !important;
        }

        .pac-matched {
            font-weight: 500 !important;
        }

        /* Hide "Powered by Google" text */
        .pac-container:after {
            display: none !important;
        }

    </style>
</head>
<body>
    <header class="header">
        <div class="header-links">
            <a href="index.php" class="header-link">Home</a>
            <?php if (isset($_SESSION['user_type'])): ?>
                <?php if (strtolower($_SESSION['user_type']) === 'renter'): ?>
                    <a href="renter_dashboard.php" class="header-link">Dashboard</a>
                <?php elseif (strtolower($_SESSION['user_type']) === 'admin'): ?>
                    <a href="admin_dashboard.php" class="header-link">Dashboard</a>
                <?php elseif (strtolower($_SESSION['user_type']) === 'realtor'): ?>
                    <a href="realtor_dashboard.php" class="header-link">Dashboard</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="login.php" class="header-link">Login</a>
                <a href="login.php?register=1" class="header-link">Sign Up</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="search-filters-bar">
        <form method="GET" id="search-form" class="search-form">
            <div class="main-search-wrapper">
                <input type="text" id="location" name="location" placeholder="Enter city name" value="<?php echo htmlspecialchars($_GET['location'] ?? ''); ?>" required>
                <input type="hidden" id="latitude" name="latitude" value="<?php echo htmlspecialchars($_GET['latitude'] ?? ''); ?>">
                <input type="hidden" id="longitude" name="longitude" value="<?php echo htmlspecialchars($_GET['longitude'] ?? ''); ?>">
                <button type="submit">
                    <i class="fas fa-search"></i>
                </button>
            </div>

            <select id="property_type" name="property_type">
                <option value="">Any Type</option>
                <option value="Apartment" <?php echo (isset($_GET['property_type']) && $_GET['property_type'] === 'Apartment') ? 'selected' : ''; ?>>Apartment</option>
                <option value="House" <?php echo (isset($_GET['property_type']) && $_GET['property_type'] === 'House') ? 'selected' : ''; ?>>House</option>
                <option value="Condo" <?php echo (isset($_GET['property_type']) && $_GET['property_type'] === 'Condo') ? 'selected' : ''; ?>>Condo</option>
            </select>

            <input type="number" id="min_price" name="min_price" placeholder="Min Price" value="<?php echo htmlspecialchars($_GET['min_price'] ?? ''); ?>">
            <input type="number" id="max_price" name="max_price" placeholder="Max Price" value="<?php echo htmlspecialchars($_GET['max_price'] ?? ''); ?>">
            
            <select id="radius" name="radius">
                <option value="10" <?php echo (isset($_GET['radius']) && $_GET['radius'] === '10') ? 'selected' : ''; ?>>10 miles</option>
                <option value="25" <?php echo (isset($_GET['radius']) && $_GET['radius'] === '25') ? 'selected' : ''; ?>>25 miles</option>
                <option value="50" <?php echo (isset($_GET['radius']) && $_GET['radius'] === '50') ? 'selected' : ''; ?>>50 miles</option>
                <option value="100" <?php echo (isset($_GET['radius']) && $_GET['radius'] === '100') ? 'selected' : ''; ?>>100 miles</option>
            </select>
        </form>
    </div>

    <div class="main-content-area">
        <div id="map-container"></div>
        <div class="listings-column-container">
            <?php
            if (!empty($_GET['location'])) {
                $location = $_GET['location'];
                $latitude = $_GET['latitude'] ?? null;
                $longitude = $_GET['longitude'] ?? null;
                $radius = isset($_GET['radius']) && $_GET['radius'] !== '' ? (float)$_GET['radius'] : 100;
                $min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : 0;
                $max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : 10000000;
                $property_type = $_GET['property_type'] ?? '';

                try {
                    if ($latitude === null || $longitude === null) {
                        throw new Exception("Location coordinates are required");
                    }

                    // Calculate distance using Haversine formula
                    $query = "SELECT *, 
                            (3959 * acos(cos(radians(" . floatval($latitude) . ")) * cos(radians(latitude)) * 
                            cos(radians(longitude) - radians(" . floatval($longitude) . ")) + 
                            sin(radians(" . floatval($latitude) . ")) * sin(radians(latitude)))) AS distance 
                            FROM Listing 
                            WHERE price BETWEEN " . floatval($min_price) . " AND " . floatval($max_price);
                    
                    if (!empty($property_type)) {
                        $query .= " AND property_type = '" . $db->quote($property_type) . "'";
                    }
                    
                    $query .= " HAVING distance < " . floatval($radius) . " ORDER BY distance";

                    $result = $db->query($query);
                    $listings = $result->fetchAll();

                    if ($listings) {
                        foreach ($listings as $listing) {
                            echo "<div class='listing-item' data-lat='" . $listing['latitude'] . "' data-lng='" . $listing['longitude'] . "'>";
                            echo "<span class='listing-location-top'>" . htmlspecialchars($listing['title']) . "</span>";
                            echo "<span class='listing-address-sub'>" . htmlspecialchars($listing['city']) . ", " . htmlspecialchars($listing['state']) . "</span>";
                            echo "<div class='listing-divider'></div>";
                            echo "<div class='listing-content-wrapper'>";
                            
                            $photo_query = "SELECT photo_url FROM ListingPhoto WHERE listing_id = ? ORDER BY photo_order LIMIT 1";
                            $photo_stmt = $db->prepare($photo_query);
                            $photo_stmt->execute([$listing['listing_id']]);
                            $photo = $photo_stmt->fetch(PDO::FETCH_ASSOC);
                            
                            echo "<div class='listing-image-left'>";
                            if ($photo) {
                                echo "<img src='" . htmlspecialchars($photo['photo_url']) . "' alt='Property Image'>";
                            } else {
                                echo "<img src='assets/images/apartment-placeholder.jpg' alt='No Image Available'>";
                            }
                            echo "</div>";
                            
                            echo "<div class='listing-details-right'>";
                            echo "<div class='listing-info'>";
                            echo "<div class='listing-price'>$" . number_format($listing['price']) . " / night</div>";
                            echo "<div class='listing-beds-baths'>" . $listing['bedrooms'] . " beds · " . $listing['bathrooms'] . " baths</div>";
                            echo "</div>";
                            echo "<div class='listing-type'>" . htmlspecialchars($listing['property_type']) . "</div>";
                            echo "<div class='listing-distance'>" . number_format($listing['distance'], 1) . " miles away</div>";
                            echo "<a href='listing.php?id=" . $listing['listing_id'] . "' class='view-listing-button'>View Listing</a>";
                            echo "</div>";
                            
                            echo "</div>"; 
                            echo "</div>"; 
                        }
                    } else {
                        echo "<div class='no-results'>No listings found within " . $radius . " miles of " . htmlspecialchars($location) . ".</div>";
                    }
                } catch (Exception $e) {
                    echo "<div class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
                }
            }
            ?>
        </div>
    </div>

    <script>
        function initMap() {
            const map = new google.maps.Map(document.getElementById('map-container'), {
                zoom: 12,
                center: { lat: 39.8283, lng: -98.5795 }, 
                mapTypeControl: true,
                streetViewControl: true,
                fullscreenControl: true
            });

            const autocomplete = new google.maps.places.Autocomplete(
                document.getElementById('location'),
                { 
                    types: ['(cities)'],
                    fields: ['geometry', 'name', 'address_components']
                }
            );

            autocomplete.addListener('place_changed', function() {
                const place = autocomplete.getPlace();
                if (place.geometry) {
                   
                    let isCity = false;
                    for (const component of place.address_components) {
                        if (component.types.includes('locality')) {
                            isCity = true;
                            break;
                        }
                    }
                    
                    if (!isCity) {
                        alert('Please select a city, not a state or country');
                        document.getElementById('location').value = '';
                        document.getElementById('latitude').value = '';
                        document.getElementById('longitude').value = '';
                        return;
                    }
                    
                    document.getElementById('latitude').value = place.geometry.location.lat();
                    document.getElementById('longitude').value = place.geometry.location.lng();
                }
            });

            addListingMarkers(map);
        }

        function addListingMarkers(map) {
            const markers = [];
            const listingCards = document.querySelectorAll('.listing-item');
            
            listingCards.forEach(card => {
                const lat = parseFloat(card.dataset.lat);
                const lng = parseFloat(card.dataset.lng);
                
                if (!isNaN(lat) && !isNaN(lng)) {
                    const marker = new google.maps.Marker({
                        position: { lat, lng },
                        map: map,
                        title: card.querySelector('.listing-location-top').textContent
                    });
                    
                    marker.addListener('click', () => {
                        card.scrollIntoView({ behavior: 'smooth' });
                        card.classList.add('highlight');
                        setTimeout(() => card.classList.remove('highlight'), 2000);
                    });
                    
                    markers.push(marker);
                }
            });

            if (markers.length > 0) {
                const bounds = new google.maps.LatLngBounds();
                markers.forEach(marker => bounds.extend(marker.getPosition()));
                map.fitBounds(bounds);
            }
        }

        window.onload = function() {
            initMap();
            
            document.getElementById('search-form').addEventListener('submit', function(e) {
                const location = document.getElementById('location').value;
                if (!location) {
                    e.preventDefault();
                    alert('Please enter a city name');
                }
            });
        };
    </script>
</body>
</html> 
