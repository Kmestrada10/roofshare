<?php
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();


// if (isset($_SESSION['user_email'])) {
//     header("Location: dashboard.php");
//     exit();
// }


$listings = [
    [
        'id' => 1,
        'image' => 'assets/images/apartment-placeholder.jpg',
        'location' => 'Harpers Ferry, West Virginia',
        'distance' => '1.2 miles away',
        'price' => '$1,850 month'
    ],
    [
        'id' => 2,
        'image' => 'assets/images/apartment-placeholder.jpg',
        'location' => 'Charles Town, West Virginia',
        'distance' => '3.5 miles away',
        'price' => '$1,650 month'
    ],
    [
        'id' => 3,
        'image' => 'assets/images/apartment-placeholder.jpg',
        'location' => 'Shepherdstown, West Virginia',
        'distance' => '5.8 miles away',
        'price' => '$1,750 month'
    ],
    [
        'id' => 4,
        'image' => 'assets/images/apartment-placeholder.jpg',
        'location' => 'Martinsburg, West Virginia',
        'distance' => '10.1 miles away',
        'price' => '$1,500 month'
    ],
    [
        'id' => 5,
        'image' => 'assets/images/apartment-placeholder.jpg',
        'location' => 'Bolivar, West Virginia',
        'distance' => '2.0 miles away',
        'price' => '$1,900 month'
    ],
    [
        'id' => 6,
        'image' => 'assets/images/apartment-placeholder.jpg',
        'location' => 'Ranson, West Virginia',
        'distance' => '4.2 miles away',
        'price' => '$1,550 month'
    ],
    [
        'id' => 7,
        'image' => 'assets/images/apartment-placeholder.jpg',
        'location' => 'Kearneysville, West Virginia',
        'distance' => '8.5 miles away',
        'price' => '$1,450 month'
    ],
    [
        'id' => 8,
        'image' => 'assets/images/apartment-placeholder.jpg',
        'location' => 'Inwood, West Virginia',
        'distance' => '12.3 miles away',
        'price' => '$1,400 month'
    ],
    [
        'id' => 9,
        'image' => 'assets/images/apartment-placeholder.jpg',
        'location' => 'Falling Waters, West Virginia',
        'distance' => '15.0 miles away',
        'price' => '$1,600 month'
    ],
    [
        'id' => 10,
        'image' => 'assets/images/apartment-placeholder.jpg',
        'location' => 'Berkeley Springs, West Virginia',
        'distance' => '25.2 miles away',
        'price' => '$1,350 month'
    ],
    [
        'id' => 11,
        'image' => 'assets/images/apartment-placeholder.jpg',
        'location' => 'Hedgesville, West Virginia',
        'distance' => '18.7 miles away',
        'price' => '$1,520 month'
    ],
    [
        'id' => 12,
        'image' => 'assets/images/apartment-placeholder.jpg',
        'location' => 'Bunker Hill, West Virginia',
        'distance' => '14.1 miles away',
        'price' => '$1,480 month'
    ]
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
        .auth-links a:hover {
            /* background-color: rgba(0, 0, 0, 0.05); Removed hover background */
            /* transform: translateY(-2px); Removed transform */
            /* box-shadow: 0 4px 8px rgba(0,0,0,0.1); Removed box-shadow */
        }
        .listing-card-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .listings-inner-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            column-gap: 20px;
            row-gap: 40px;
        }
    </style>
</head>
<body>
    <header class="header">
        <!-- Auth Navigation (Top Right) -->
        <div class="auth-links">
            <a href="login.php">Log In</a>
            <a href="register.php">Sign Up</a>
        </div>
    </header>

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
                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" viewBox="0 0 16 16">
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
                        <a href="listing.php?id=<?php echo htmlspecialchars($listing['id']); ?>" class="listing-card-link">
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
                                </div>
                            </div>
                        </a>
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
