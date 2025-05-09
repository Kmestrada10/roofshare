<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once 'config/db.php';
} catch (PDOException $e) {
    die("Unable to connect to the database. Please try again later.");
}

// Get property ID from URL
$property_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($property_id <= 0) {
    header("Location: index.php");
    exit();
}

try {
    // First check if the listing exists
    $check_stmt = $db->prepare("SELECT COUNT(*) FROM Listing WHERE listing_id = ?");
    $check_stmt->execute([$property_id]);
    if ($check_stmt->fetchColumn() == 0) {
        header("Location: index.php");
        exit();
    }

    // Fetch property details
    $stmt = $db->prepare("
        SELECT l.*, r.name as realtor_name
        FROM Listing l 
        JOIN Realtor r ON l.realtor_id = r.realtor_id 
        WHERE l.listing_id = ?
    ");
    $stmt->execute([$property_id]);
    $property = $stmt->fetch();

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
    $images = $stmt->fetchAll();

    // For now, we'll use a static list of amenities since the table structure is different
    $amenities = [
        ['name' => 'Mountain Views', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z"/></svg>'],
        ['name' => 'Fast Wifi', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path d="M8 1a5 5 0 0 0-5 5v1h1a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V6a6 6 0 1 1 12 0v6a2.5 2.5 0 0 1-2.5 2.5H9.366a1 1 0 0 1-.866.5h-1a1 1 0 1 1 0-2h1a1 1 0 0 1 .866.5H11.5A1.5 1.5 0 0 0 13 12h-1a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1h1V6a5 5 0 0 0-5-5z"/></svg>'],
        ['name' => 'Free Washer & Dryer', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16"><path d="M5 8c0-1.657 2.343-3 4-3V4a4 4 0 0 0-4 4z"/><path d="M12.318 3h2.015C15.253 3 16 3.746 16 4.667v6.666c0 .92-.746 1.667-1.667 1.667h-2.015A5.97 5.97 0 0 1 9 14a5.972 5.972 0 0 1-3.318-1H1.667C.747 13 0 12.254 0 11.333V4.667C0 3.747.746 3 1.667 3H2a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1h.682A5.97 5.97 0 0 1 9 2c1.227 0 2.367.368 3.318 1zM2 4.5a.5.5 0 1 0-1 0 .5.5 0 0 0 1 0zM14 8A5 5 0 1 0 4 8a5 5 0 0 0 10 0z"/></svg>']
    ];

} catch (PDOException $e) {
    die("Sorry, there was a problem loading the property details. Please try again later.");
} catch (Exception $e) {
    die("An unexpected error occurred. Please try again later.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Listing - Luxury Apartment</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
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
            // Check if user is logged in and is a renter
            session_start();
            $is_saved = false;
            
            if (isset($_SESSION['user_id']) && isset($_SESSION['user_type']) && strtolower($_SESSION['user_type']) === 'renter') {
                // Check if this listing is already saved by the user
                $result = $db->query("SELECT COUNT(*) FROM Saves WHERE renter_id = {$_SESSION['user_id']} AND listing_id = $property_id");
                $is_saved = $result->fetchColumn() > 0;
            }
            ?>
            <form action="save_listing.php" method="post" style="display: inline;">
                <input type="hidden" name="listing_id" value="<?php echo $property_id; ?>">
                <input type="hidden" name="action" value="<?php echo $is_saved ? 'unsave' : 'save'; ?>">
                <button type="submit" class="bookmark-button <?php echo $is_saved ? 'saved' : ''; ?>" 
                        <?php echo (!isset($_SESSION['user_id']) || strtolower($_SESSION['user_type']) !== 'renter') ? 'disabled' : ''; ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M2 2v13.5a.5.5 0 0 0 .74.439L8 13.069l5.26 2.87A.5.5 0 0 0 14 15.5V2a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/>
                    </svg>
                    <span><?php echo $is_saved ? 'Saved' : 'Save'; ?></span>
                </button>
            </form>
        </div>

        <div class="gallery">
            <div class="main-image">
                <img src="<?php echo htmlspecialchars($images[0]['photo_url'] ?? 'assets/images/apartment-placeholder.jpg'); ?>" 
                     alt="Property main image">
            </div>
            <div class="small-images">
                <?php 
                for ($i = 1; $i < min(4, count($images)); $i++) {
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
                            <?php echo $amenity['icon']; ?>
                            <?php echo htmlspecialchars($amenity['name']); ?>
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
                        <span class="amount">$<?php echo number_format($property['price'], 2); ?></span>
                        <span class="period">per month</span>
                    </div>
                    
                    <button class="cta-button">Request to Rent</button>
                    <button class="secondary-button">Contact Realtor</button>
                </div>
            </div>
        </div>
    </main>
    <script>
        document.getElementById('bookmarkBtn')?.addEventListener('click', async function() {
            if (this.disabled) return;
            
            try {
                const response = await fetch('save_listing.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        listing_id: <?php echo $property_id; ?>,
                        action: this.classList.contains('saved') ? 'unsave' : 'save'
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    this.classList.toggle('saved');
                    this.querySelector('span').textContent = this.classList.contains('saved') ? 'Saved' : 'Save';
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
    </style>
</body>
</html>