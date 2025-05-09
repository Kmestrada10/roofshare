<?php
require_once("config/db.php");
session_start();

// Set up pagination
$items_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;
?>

<!DOCTYPE html>
<html>

<head>
    <title>Search</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/search.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBEYlDB7H0z4_06e7MPKycHK12jw4lpnyg&libraries=places"></script>
</head>

<body>
    <header class="header">
        <div class="header-content">
            <a href="index.php" class="logo">RoofShare</a>
            <nav class="nav-links">
                <a href="landing.php" class="nav-link">Home</a>
                <a href="search.php" class="nav-link">Search</a>
                <?php if (isset($_SESSION['user_email'])): ?>
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                <?php else: ?>
                    <a href="index.php" class="nav-link">Login</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <div class="search-container">
        <div class="search-panel">
            <h2>Search</h2>

            <form method="GET" id="search-form">
                <div class="form-group">
                    <label for="location">City</label>
                    <input type="text" id="location" name="location" placeholder="Enter city name" value="<?php echo htmlspecialchars($_GET['location'] ?? ''); ?>" required>
                    <input type="hidden" id="latitude" name="latitude" value="<?php echo htmlspecialchars($_GET['latitude'] ?? ''); ?>">
                    <input type="hidden" id="longitude" name="longitude" value="<?php echo htmlspecialchars($_GET['longitude'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="radius">Search Radius (miles)</label>
                    <input type="number" id="radius" name="radius" min="1" max="100" value="<?php echo htmlspecialchars($_GET['radius'] ?? '100'); ?>">
                </div>

                <div class="form-group">
                    <label for="min_price">Min Price</label>
                    <input type="number" id="min_price" name="min_price" value="<?php echo htmlspecialchars($_GET['min_price'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="max_price">Max Price</label>
                    <input type="number" id="max_price" name="max_price" value="<?php echo htmlspecialchars($_GET['max_price'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="property_type">Property Type</label>
                    <select id="property_type" name="property_type">
                        <option value="">Any</option>
                        <option value="Apartment" <?php echo (isset($_GET['property_type']) && $_GET['property_type'] === 'Apartment') ? 'selected' : ''; ?>>Apartment</option>
                        <option value="House" <?php echo (isset($_GET['property_type']) && $_GET['property_type'] === 'House') ? 'selected' : ''; ?>>House</option>
                        <option value="Condo" <?php echo (isset($_GET['property_type']) && $_GET['property_type'] === 'Condo') ? 'selected' : ''; ?>>Condo</option>
                    </select>
                </div>

                <input type="submit" value="Search" class="search-button">
            </form>
        </div>

        <div class="results-container">
            <div id="map"></div>
            <div id="listings-results" class="listings-container">
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
                        // Build search query
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

                        $stmt = $db->prepare($query);
                        $stmt->execute();
                        $listings = $stmt->fetchAll();

                        if ($listings) {
                            echo "<div class='listings-grid'>";
                            foreach ($listings as $listing) {
                                echo "<div class='listing-card' data-lat='" . htmlspecialchars($listing['latitude']) . "' data-lng='" . htmlspecialchars($listing['longitude']) . "'>";
                                echo "<h3>" . htmlspecialchars($listing['title']) . "</h3>";
                                echo "<p class='price'>$" . number_format($listing['price']) . "</p>";
                                echo "<p class='location'>" . htmlspecialchars($listing['city']) . ", " . htmlspecialchars($listing['state']) . "</p>";
                                echo "<p class='type'>" . htmlspecialchars($listing['property_type']) . "</p>";
                                echo "<p class='distance'>" . number_format($listing['distance'], 1) . " miles away</p>";
                                echo "<a href='listing.php?id=" . $listing['listing_id'] . "' class='view-details'>View Details</a>";
                                echo "</div>";
                            }
                            echo "</div>";
                        } else {
                            echo "<p class='no-results'>No listings found within " . $radius . " miles of " . htmlspecialchars($location) . ".</p>";
                        }
                    } catch (PDOException $e) {
                        echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                }
                ?>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-content">
            <p>&copy; 2024 RoofShare. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function initMap() {
            const map = new google.maps.Map(document.getElementById('map'), {
                zoom: 12,
                center: { lat: 39.8283, lng: -98.5795 }, // Center of USA
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
                    // Validate city selection
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
            const listingCards = document.querySelectorAll('.listing-card');
            
            listingCards.forEach(card => {
                const lat = parseFloat(card.dataset.lat);
                const lng = parseFloat(card.dataset.lng);
                
                if (!isNaN(lat) && !isNaN(lng)) {
                    const marker = new google.maps.Marker({
                        position: { lat, lng },
                        map: map,
                        title: card.querySelector('h3').textContent
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