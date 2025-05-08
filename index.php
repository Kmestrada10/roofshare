<?php
// Remove the db.php require since this is public page
session_start();

// Redirect logged-in users to their dashboard
if (isset($_SESSION['user_email'])) {
    header("Location: dashboard.php");
    exit();
}

// Sample property listings data
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
    <title>Find Your Perfect Home | RoofShare</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/landing.css">
    
    <!-- Auth CSS -->
    <style>
        .auth-links {
            position: absolute;
            top: 25px;
            right: 30px;
            display: flex;
            gap: 20px;
            z-index: 100;
        }
        .auth-links a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 30px;
            transition: all 0.3s ease;
            background-color: rgba(0, 123, 255, 0.8);
        }
        .auth-links a:hover {
            background-color: rgba(0, 123, 255, 1);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- Auth Navigation (Top Right) -->
    <div class="auth-links">
        <a href="login.php">Log In</a>
        <a href="register.php">Sign Up</a>
    </div>

    <!-- Hero Section -->
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
                                <img src="<?php echo htmlspecialchars($listing['image']); ?>" 
                                     alt="Property in <?php echo htmlspecialchars($listing['location']); ?>"
                                     loading="lazy">
                            </div>
                            <div class="listing-details">
                                <div class="listing-location"><?php echo htmlspecialchars($listing['location']); ?></div>
                                <div class="listing-distance"><?php echo htmlspecialchars($listing['distance']); ?></div>
                                <div class="listing-price"><?php echo htmlspecialchars($listing['price']); ?></div>
                                <button class="view-details" onclick="location.href='login.php'">View Details</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <script>
        function handleSearch() {
            const searchInput = document.getElementById('searchInput');
            if (searchInput.value.trim() !== '') {
                window.location.href = 'login.php?redirect=search&q=' + encodeURIComponent(searchInput.value);
            } else {
                alert('Please enter a search term');
            }
        }
    </script>
</body>
</html>
