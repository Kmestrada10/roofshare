<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once("config/db.php");


$property_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($property_id <= 0) {
    header("Location: index.php");
    exit();
}

try {
 
    $stmt = $db->prepare("
        SELECT l.*, r.name as realtor_name, r.verification_status
        FROM Listing l
        JOIN Realtor r ON l.realtor_id = r.realtor_id
        WHERE l.listing_id = ?
    ");
    $stmt->execute([$property_id]);
    $property = $stmt->fetch(PDO::FETCH_ASSOC);

   
    error_log("Property ID being queried: " . $property_id);
    error_log("Query result: " . print_r($property, true));

    if (!$property) {
        error_log("No property found for ID: " . $property_id);
        header("Location: index.php");
        exit();
    }

    
    $is_saved = false;
    if (isset($_SESSION['user_id']) && isset($_SESSION['user_type']) && strtolower($_SESSION['user_type']) === 'renter') {
        $stmt = $db->prepare("
            SELECT COUNT(*) 
            FROM Saves 
            WHERE renter_id = ? AND listing_id = ?
        ");
        $stmt->execute([$_SESSION['user_id'], $property_id]);
        $is_saved = (bool)$stmt->fetchColumn();
    }

 
    $stmt = $db->prepare("
        SELECT photo_url, photo_order
        FROM ListingPhoto
        WHERE listing_id = ?
        ORDER BY photo_order ASC
    ");
    $stmt->execute([$property_id]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);


    error_log("=== Database Debug Info ===");
    error_log("Property ID: " . $property_id);
    
    $debug_stmt = $db->prepare("SELECT * FROM ListingPhoto WHERE listing_id = ?");
    $debug_stmt->execute([$property_id]);
    $all_photos = $debug_stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("All photos in database for this listing: " . print_r($all_photos, true));
    
    $upload_dir = 'uploads/listing_images/';
    error_log("Upload directory exists: " . (is_dir($upload_dir) ? 'Yes' : 'No'));
    error_log("Upload directory is writable: " . (is_writable($upload_dir) ? 'Yes' : 'No'));
    
    error_log("Files in upload directory: " . print_r(scandir($upload_dir), true));

    if (empty($images)) {
        error_log("No images found for listing " . $property_id . ", using placeholder");
        $images = [['photo_url' => 'assets/images/apartment-placeholder.jpg', 'photo_order' => 1]];
    }

    $firstImage = $images[0];
    while (count($images) < 5) {
        $images[] = $firstImage;
    }

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
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBEYlDB7H0z4_06e7MPKycHK12jw4lpnyg&libraries=places"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <link rel="stylesheet" href="assets/css/listing.css">
</head>
<body>
    <header class="header">
        <div class="search-bar-container">
            <input
                id="searchInput"
                class="search-input"
                type="text"
                placeholder="Enter an address, neighborhood, city, or ZIP code"
                aria-label="Search for properties"
            >
            <button class="search-button" onclick="handleSearch()" aria-label="Submit search">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                </svg>
            </button>
        </div>
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

    <main class="content">
        <div class="property-title">
            <div class="title-text">
                <h1><?php echo htmlspecialchars($property['title']); ?></h1>
                <div class="location"><?php echo htmlspecialchars($property['city'] . ', ' . $property['state']); ?></div>
                <?php if ($property['verification_status'] !== 'Verified'): ?>
                    <div class="warning-message">
                        <i class="fas fa-exclamation-triangle"></i>
                        Warning: This listing is from an unverified realtor
                    </div>
                <?php endif; ?>
            </div>
            <form action="save_listing.php" method="post" style="display: inline;">
                <input type="hidden" name="listing_id" value="<?php echo $property_id; ?>">
                <input type="hidden" name="action" value="<?php echo $is_saved ? 'unsave' : 'save'; ?>">
                <button type="button" class="bookmark-button <?php echo $is_saved ? 'saved' : ''; ?>">
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

                <div class="report-section">
                    <a href="#" class="report-link" onclick="openReportModal(); return false;">Report this listing</a>
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

    <!-- Report Modal -->
    <div id="reportModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeReportModal()">&times;</span>
            <h2>Report Listing</h2>
            <p class="modal-subtitle">Describe what's wrong with this listing</p>
            <form class="report-form" id="reportForm" onsubmit="submitReport(event)">
                <input type="hidden" name="listing_id" value="<?php echo $property_id; ?>">
                <textarea name="description" placeholder="Please describe why you are reporting this listing..." required></textarea>
                <button type="submit">Submit Report</button>
            </form>
        </div>
    </div>

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
            
            const bookmarkButton = document.querySelector('.bookmark-button');
            if (bookmarkButton) {
                const newBookmarkButton = bookmarkButton.cloneNode(true);
                bookmarkButton.parentNode.replaceChild(newBookmarkButton, bookmarkButton);
                
                newBookmarkButton.addEventListener('click', async function(event) {
                    if (this.disabled) return;
                    
                    event.preventDefault(); 
                    
                    try {
                        const form = this.closest('form');
                        const formData = new FormData(form);
                        
                        const response = await fetch('save_listing.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: new URLSearchParams(formData)
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            this.classList.toggle('saved');
                            const span = this.querySelector('span');
                            span.textContent = this.classList.contains('saved') ? 'Saved' : 'Save';
                            
                            const actionInput = form.querySelector('input[name="action"]');
                            actionInput.value = this.classList.contains('saved') ? 'unsave' : 'save';
                        } else {
                            alert(data.message || 'An error occurred while saving the listing');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('An error occurred while saving the listing');
                    }
                });
            }
        };

        function openReportModal() {
            document.getElementById('reportModal').style.display = 'flex';
        }

        function closeReportModal() {
            document.getElementById('reportModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('reportModal');
            if (event.target === modal) {
                closeReportModal();
            }
        }

        async function submitReport(event) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
            
            try {
                const response = await fetch('report_listing.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    closeReportModal();
                    form.reset();
                } else {
                    alert(data.message || 'An error occurred while submitting the report');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while submitting the report');
            }
        }
    </script>
    <style>
        .bookmark-button {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border: 2px solid #ff8c00;
            border-radius: 20px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
            color: #ff8c00;
            outline: none;
        }

        .bookmark-button:hover:not(:disabled) {
            border-color: #ff8c00;
            background-color: #fff5e6;
        }

        .bookmark-button.saved {
            background: #ff8c00;
            border-color: #ff8c00;
            color: white;
        }

        .bookmark-button.saved:hover:not(:disabled) {
            background: #e67e00;
            border-color: #e67e00;
        }

        .bookmark-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
            border-color: #ddd;
            color: #999;
        }

        .bookmark-button svg {
            width: 20px;
            height: 20px;
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

        .pac-item-query {
            font-size: 0.9rem !important;
            color: #333 !important;
        }

        .pac-matched {
            font-weight: 500 !important;
        }

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

        .warning-message {
            background-color: #fff3cd;
            color: #856404;
            padding: 10px 15px;
            border-radius: 4px;
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            border: 1px solid #ffeeba;
        }

        .warning-message i {
            color: #856404;
        }

        .report-section {
            margin-top: 40px;
        }

        .report-link {
            color: #dc3545;
            text-decoration: none;
            font-size: 0.9em;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .report-link:hover {
            color: #c82333;
            text-decoration: underline;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            position: relative;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .modal-content h2 {
            color: #333;
            margin-bottom: 10px;
            font-size: 1.5em;
            font-weight: 500;
        }

        .modal-subtitle {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 20px;
        }

        .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #666;
            transition: color 0.3s ease;
        }

        .close-modal:hover {
            color: #333;
        }

        .report-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .report-form textarea {
            width: 100%;
            min-height: 150px;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            font-family: inherit;
            font-size: 0.9em;
            line-height: 1.5;
        }

        .report-form textarea:focus {
            outline: none;
            border-color: #dc3545;
            box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.1);
        }

        .report-form button {
            padding: 12px 24px;
            background-color: #ff8c00;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            font-size: 0.9em;
        }

        .report-form button:hover {
            background-color: #ff8c00;
        }
    </style>
</body>
</html>
