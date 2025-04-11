<?php
require_once 'includes/header.php';

// Sample property listings data (replace with database query later)
$listings = [
    [
        'image' => 'assets/images/apartment-placeholder.jpg',
        'location' => 'Harpers Ferry, West Virginia',
        'distance' => '1.2 miles away',
        'price' => '$1,850 month'
    ],
    [
        'image' => 'assets/images/apartment-placeholder.jpg',
        'location' => 'Charles Town, West Virginia',
        'distance' => '3.5 miles away',
        'price' => '$1,650 month'
    ],
    [
        'image' => 'assets/images/apartment-placeholder.jpg',
        'location' => 'Shepherdstown, West Virginia',
        'distance' => '5.8 miles away',
        'price' => '$1,750 month'
    ],
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <link rel="stylesheet" href="assets/css/landing.css">
</head>
<body>
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                    </svg>
                </button>
            </div>
        </div>
    </section>

    <!-- Listings Section -->
    <section class="listings-section">
        <div class="listings-outer-container">
            <div class="listings-content-wrapper">
                <h2 class="listings-heading">Explore Rentals in the Area</h2>
                
                <div class="listings-inner-container">
                    <?php foreach ($listings as $listing): ?>
                        <div class="listing-item">
                            <div class="listing-image">
                                <img src="<?php echo htmlspecialchars($listing['image']); ?>" alt="Property in <?php echo htmlspecialchars($listing['location']); ?>">
                            </div>
                            <div class="listing-details">
                                <div class="listing-location"><?php echo htmlspecialchars($listing['location']); ?></div>
                                <div class="listing-distance"><?php echo htmlspecialchars($listing['distance']); ?></div>
                                <div class="listing-price"><?php echo htmlspecialchars($listing['price']); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
    <!-- End Listings Section -->

    <script>
        function handleSearch() {
            const searchInput = document.getElementById('searchInput');
            alert('Search initiated for: ' + searchInput.value);
        }
    </script>
</body>
</html> 