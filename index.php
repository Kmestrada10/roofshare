<?php
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// Handle logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    setcookie(session_name(), '', time() - 3600, '/');
    header("Location: index.php");
    exit();
}

require_once("config/db.php");

// Fetch recent listings from database
try {
    $query = "SELECT l.*, 
              (SELECT photo_url FROM ListingPhoto WHERE listing_id = l.listing_id ORDER BY photo_order LIMIT 1) as image_url
              FROM Listing l 
              WHERE l.status = 'Available'
              ORDER BY l.created_at DESC 
              LIMIT 6";
    
    $result = $db->query($query);
    $listings = $result->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($listings)) {
        echo "<!-- No listings found in database -->";
    }
} catch (PDOException $e) {
    echo "<!-- Database Error: " . htmlspecialchars($e->getMessage()) . " -->";
    $listings = [];
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Your Perfect Home | RoofShare</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBEYlDB7H0z4_06e7MPKycHK12jw4lpnyg&libraries=places"></script>
    
    <link rel="stylesheet" href="assets/css/landing.css">
    
    <style>
        .header {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding: 0 30px;
            height: 70px;
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
        
        .listing-card-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .listings-inner-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            column-gap: 20px;
            row-gap: 40px;
        }

        .pac-container {
            border-radius: 8px !important;
            margin-top: 5px !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1) !important;
            border: 1px solid #ddd !important;
            font-family: 'Montserrat', sans-serif !important;
            width: 650px !important; /* Decreased width */
            left: 50% !important;
            transform: translateX(-50%) !important;
            position: absolute !important;
        }

        .search-bar-container {
            position: relative !important;
        }

        .pac-container:before {
            display: none !important;
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

        .pac-container:after {
            display: none !important;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-links">
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

    <section class="hero-container">
        <div class="content-wrapper">
            <h1 class="headline">Find it. Tour it. Own it.</h1>
            
            <div class="search-bar-container">
                <input 
                    id="searchInput" 
                    class="search-input" 
                    type="text" 
                    placeholder="Enter an address, neighborhood, city, or ZIP code" 
                    aria-label="Search for homes"
                >
                <button class="search-button" onclick="handleSearch()" aria-label="Submit search">
                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                    </svg>
                </button>
            </div>
        </div>
    </section>

    <section class="listings-section">
        <div class="listings-outer-container">
            <div class="listings-content-wrapper">
                <h2 class="listings-heading">Explore Rentals in the Area</h2>
                
                <div class="listings-inner-container">
                    <?php 
                        foreach ($listings as $listing): 
                    ?>
                        <a href="listing.php?id=<?php echo htmlspecialchars($listing['listing_id']); ?>" class="listing-card-link">
                            <div class="listing-item">
                                <div class="listing-image">
                                    <img src="<?php echo htmlspecialchars($listing['image_url'] ?? 'assets/images/apartment-placeholder.jpg'); ?>" 
                                         alt="Property in <?php echo htmlspecialchars($listing['city'] . ', ' . $listing['state']); ?>"
                                         loading="lazy">
                                </div>
                                <div class="listing-details">
                                    <div class="listing-location"><?php echo htmlspecialchars($listing['city'] . ', ' . $listing['state']); ?></div>
                                    <div class="listing-price">$<?php echo number_format($listing['price']); ?> night</div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <script>
        function initAutocomplete() {
            const searchInput = document.getElementById('searchInput');
            const autocomplete = new google.maps.places.Autocomplete(searchInput, {
                types: ['(cities)'],
                fields: ['geometry', 'name', 'address_components']
            });

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
                        searchInput.value = '';
                        return;
                    }

                    searchInput.dataset.lat = place.geometry.location.lat();
                    searchInput.dataset.lng = place.geometry.location.lng();
                }
            });
        }

        function handleSearch() {
            const searchInput = document.getElementById('searchInput');
            const lat = searchInput.dataset.lat;
            const lng = searchInput.dataset.lng;

            if (searchInput.value.trim() !== '' && lat && lng) {
                window.location.href = `search_results.php?location=${encodeURIComponent(searchInput.value)}&latitude=${lat}&longitude=${lng}`;
            } else {
                alert('Please select a city from the dropdown');
            }
        }

        window.onload = function() {
            initAutocomplete();
        };
    </script>
</body>
</html>
