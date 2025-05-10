<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once("config/db.php");

// Get property ID from URL
$property_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($property_id <= 0) {
    header("Location: index.php");
    exit();
}

try {
    // Fetch property details
    $stmt = $db->prepare("
        SELECT l.*, r.name as realtor_name
        FROM Listing l
        JOIN Realtor r ON l.realtor_id = r.realtor_id
        WHERE l.listing_id = ?
    ");
    $stmt->execute([$property_id]);
    $property = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$property) {
        header("Location: index.php");
        exit();
    }

    // Fetch property images
    $stmt = $db->prepare("
        SELECT photo_url, photo_order
        FROM ListingPhoto
        WHERE listing_id = ?
        ORDER BY photo_order ASC
    ");
    $stmt->execute([$property_id]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If no images found, use placeholder
    if (empty($images)) {
        $images = [['photo_url' => 'assets/images/apartment-placeholder.jpg', 'photo_order' => 1]];
    }

    // Ensure we have at least 5 images by repeating the first image if needed
    $firstImage = $images[0];
    while (count($images) < 5) {
        $images[] = $firstImage;
    }

    // Fetch amenities
    $stmt = $db->prepare("
        SELECT a.name
        FROM Amenities a
        JOIN ListingAmenities la ON a.amenity_id = la.amenity_id
        WHERE la.listing_id = ?
        ORDER BY a.name
    ");
    $stmt->execute([$property_id]);
    $amenities = $stmt->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    // Log error and redirect
    error_log("Error in listing.php: " . $e->getMessage());
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($property['title']); ?> | RoofShare</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/listing.css">
</head>
<body>
    <header class="header">
        <div class="search-bar-container">
            <input
                class="search-input"
                type="text"
                placeholder="Enter an address, neighborhood, city, or ZIP code"
                aria-label="Search for properties"
            >
            <button class="search-button" aria-label="Submit search">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                </svg>
            </button>
        </div>
        <div class="header-links">
            <a href="#" class="header-link">Manage Rentals</a>
            <a href="#" class="header-link">Sign In</a>
            <a href="#" class="header-link">Add Property</a>
        </div>
    </header>

    <main class="content">
        <div class="property-title">
            <div class="title-text">
                <h1><?php echo htmlspecialchars($property['title']); ?></h1>
                <div class="location"><?php echo htmlspecialchars($property['city'] . ', ' . $property['state']); ?></div>
            </div>
            <?php
            $is_saved = false; // This will be implemented later with actual database check
            ?>
            <form action="save_listing.php" method="post" style="display: inline;">
                <input type="hidden" name="listing_id" value="<?php echo $property_id; ?>">
                <input type="hidden" name="action" value="<?php echo $is_saved ? 'unsave' : 'save'; ?>">
                <button type="submit" class="bookmark-button <?php echo $is_saved ? 'saved' : ''; ?>"
                        <?php echo (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || strtolower($_SESSION['user_type']) !== 'renter') ? 'disabled' : ''; ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M2 2v13.5a.5.5 0 0 0 .74.439L8 13.069l5.26 2.87A.5.5 0 0 0 14 15.5V2a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/>
                    </svg>
                    <span><?php echo $is_saved ? 'Saved' : 'Save'; ?></span>
                </button>
            </form>
        </div>

        <div class="gallery">
            <div class="main-image">
                <img src="<?php echo htmlspecialchars($images[0]['photo_url']); ?>"
                     alt="Property main image">
            </div>
            <div class="small-images">
                <?php
                // Show exactly 4 small images (indexes 1-4)
                for ($i = 1; $i <= 4; $i++) {
                    echo '<img src="' . htmlspecialchars($images[$i]['photo_url']) . '"
                              alt="Property image ' . ($i + 1) . '">';
                }
                ?>
            </div>
        </div>

        <div class="property-details">
            <div class="description">
                <div class="description-header">
                    <div class="host-info">
                        <div class="host-text">
                            <div class="host-name">Entire property hosted by <?php echo htmlspecialchars($property['realtor_name']); ?></div>
                            <div class="host-details"><?php echo htmlspecialchars($property['bedrooms']); ?> bedrooms • <?php echo htmlspecialchars($property['bathrooms']); ?> bathrooms • <?php echo htmlspecialchars($property['max_guests']); ?> guests</div>
                        </div>
                    </div>
                </div>

                <div class="amenities">
                    <h2>What this place offers</h2>
                    <div class="amenities-list">
                        <?php foreach ($amenities as $amenity): ?>
                        <div class="amenity">
                            <?php
                            // Map amenities to Font Awesome icons
                            $icon_map = [
                                'Wifi' => 'fa-wifi',
                                'Air Conditioning' => 'fa-snowflake',
                                'Washer' => 'fa-shirt',
                                'Dryer' => 'fa-shirt',
                                'Kitchen' => 'fa-utensils',
                                'TV' => 'fa-tv',
                                'Parking' => 'fa-car',
                                'Pool' => 'fa-person-swimming',
                                'Gym' => 'fa-dumbbell',
                                'Elevator' => 'fa-elevator',
                                'Doorman' => 'fa-door-open',
                                'Security' => 'fa-shield-halved',
                                'Pets Allowed' => 'fa-paw',
                                'Balcony' => 'fa-house',
                                'Fireplace' => 'fa-fire',
                                'Heating' => 'fa-temperature-high',
                                'Workspace' => 'fa-laptop',
                                'BBQ Grill' => 'fa-fire',
                                'Garden' => 'fa-tree',
                                'Beach Access' => 'fa-umbrella-beach'
                            ];
                            $icon = $icon_map[$amenity] ?? 'fa-circle-check';
                            ?>
                            <i class="fa-solid <?php echo $icon; ?>"></i>
                            <?php echo htmlspecialchars($amenity); ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="description-text">
                    <?php echo nl2br(htmlspecialchars($property['description'])); ?>
                </div>
            </div>

            <div class="booking-section">
                <div class="booking-card">
                    <div class="price">
                        <span class="amount">$<?php echo number_format($property['price']); ?></span>
                        <span class="period">night</span>
                    </div>

                    <button class="cta-button">Request to Rent</button>
                    <button class="secondary-button">Contact Realtor</button>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.querySelector('.bookmark-button')?.addEventListener('click', async function(event) {
            if (this.disabled) return;
            
            try {
                const response = await fetch('save_listing.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams(new FormData(this.closest('form')))
                });

                const data = await response.json();
                if (data.success) {
                    this.classList.toggle('saved');
                    this.querySelector('span').textContent = this.classList.contains('saved') ? 'Saved' : 'Save';
                    this.closest('form').querySelector('input[name="action"]').value = this.classList.contains('saved') ? 'unsave' : 'save';
                } else {
                    alert(data.message || 'An error occurred');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while saving the listing');
            }
        });
    </script>
    <style>
        .bookmark-button {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border: 2px solid #ddd;
            border-radius: 20px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .bookmark-button:hover:not(:disabled) {
            border-color: #666;
        }

        .bookmark-button.saved {
            background: #e31c5f;
            border-color: #e31c5f;
            color: white;
        }

        .bookmark-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .bookmark-button svg {
            width: 20px;
            height: 20px;
        }

        /* Image gallery styling */
        .gallery {
            width: 100%;
            margin-bottom: 24px;
        }

        .main-image {
            width: 100%;
            height: 400px;
            margin-bottom: 8px;
            overflow: hidden;
            border-radius: 12px;
        }

        .main-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .small-images {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(2, 196px);
            gap: 8px;
            width: calc(100% - 8px);
            margin: 0;
            padding: 0;
        }

        .small-images img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            transition: opacity 0.2s ease;
            margin: 0;
            padding: 0;
        }

        .small-images img:hover {
            opacity: 0.9;
        }

        .amenity {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
        }

        .amenity i {
            font-size: 20px;
            color: #333;
            width: 24px;
            text-align: center;
        }
    </style>
</body>
</html>