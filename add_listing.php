<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("config/db.php");
session_start();

// Check if user is a realtor
if (strtolower($_SESSION['user_type'] ?? '') !== 'realtor') {
    echo "Access denied. Only realtors can add listings.";
    exit;
}

// Get realtor ID from email
function getRealtorId($db, $email)
{
    $stmt = $db->prepare("SELECT realtor_id FROM Realtor WHERE email = ?");
    $stmt->execute([$email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $row['realtor_id'] : null;
}
$realtor_id = getRealtorId($db, $_SESSION['user_email']);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $title         = $_POST['title'] ?? '';
    $description   = $_POST['description'] ?? '';
    $price         = $_POST['price'] ?? 0;
    $property_type = $_POST['property_type'] ?? '';
    $bedrooms      = $_POST['bedrooms'] ?? 0;
    $bathrooms     = $_POST['bathrooms'] ?? 0;
    $max_guests    = $_POST['max_guests'] ?? 1;
    $amenities     = $_POST['amenities'] ?? []; // Get selected amenities
    
    // Get address data
    $street        = $_POST['street_address'] ?? '';
    $city          = $_POST['city'] ?? '';
    $state         = $_POST['state'] ?? '';
    $zip           = $_POST['zip_code'] ?? '';
    $country       = $_POST['country'] ?? '';
    $latitude      = $_POST['latitude'] ?? null;
    $longitude     = $_POST['longitude'] ?? null;

    try {
        $db->beginTransaction();

        // Insert new listing record
        $query = "INSERT INTO Listing 
            (title, description, price, status,
             property_type, bedrooms, bathrooms,
             max_guests, street_address, city,
             state, zip_code, country, latitude,
             longitude, realtor_id)
          VALUES 
            (?, ?, ?, 'Available', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            $title,
            $description,
            $price,
            $property_type,
            $bedrooms,
            $bathrooms,
            $max_guests,
            $street,
            $city,
            $state,
            $zip,
            $country,
            $latitude ?: null,
            $longitude ?: null,
            $realtor_id
        ]);
        
        $listing_id = $db->lastInsertId();

        // Add selected amenities
        if (!empty($amenities)) {
            $amenity_stmt = $db->prepare("INSERT INTO ListingAmenities (listing_id, amenity_id) VALUES (?, ?)");
            foreach ($amenities as $amenity_id) {
                $amenity_stmt->execute([$listing_id, $amenity_id]);
            }
        }

        // Handle file uploads
        if (isset($_FILES['photos'])) {
            $upload_dir = 'uploads/listing_images/';
            // Ensure upload directory exists and is writable
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $photo_order = 1;
            $photo_stmt = $db->prepare("INSERT INTO ListingPhoto (listing_id, photo_url, photo_order) VALUES (?, ?, ?)");
            
            foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['photos']['error'][$key] === UPLOAD_ERR_OK) {
                    $file_name = $_FILES['photos']['name'][$key];
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];

                    if (in_array($file_ext, $allowed_exts)) {
                        // Generate a unique filename
                        $safe_file_name = uniqid('img_', true) . '_' . preg_replace("/[^a-zA-Z0-9.]/", "", $file_name);
                        $upload_path = $upload_dir . $safe_file_name;
                        
                        // Debug logging
                        error_log("=== Image Upload Debug ===");
                        error_log("Original filename: " . $file_name);
                        error_log("Safe filename: " . $safe_file_name);
                        error_log("Upload path: " . $upload_path);
                        
                        if (move_uploaded_file($tmp_name, $upload_path)) {
                            error_log("File uploaded successfully to: " . $upload_path);
                            
                            // Store the relative path in the database
                            $photo_stmt->execute([$listing_id, $upload_path, $photo_order]);
                            error_log("Image path stored in database: " . $upload_path);
                            $photo_order++;
                        } else {
                            $upload_error = error_get_last();
                            error_log("Failed to move uploaded file. PHP Error: " . print_r($upload_error, true));
                            error_log("Upload error code: " . $_FILES['photos']['error'][$key]);
                        }
                    } else {
                        error_log("Invalid file type: " . $file_name);
                    }
                } else {
                    error_log("Upload error for file " . $file_name . ": " . $_FILES['photos']['error'][$key]);
                }
            }
        }

        $db->commit();
        header("Location: listing.php?id=" . $listing_id);
        exit;
    } catch (Exception $e) {
        $db->rollBack();
        echo "<p style='color:red'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Property Listing</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lucide-react/0.263.0/lucide-react.min.js"> --> <!-- Lucide is used via JS below -->

    <!-- REMOVED Cloudinary Upload Widget -->
    <!-- <script src="https://widget.cloudinary.com/v2.0/global/all.js" type="text/javascript"></script> -->
  
    <!-- Add Google Maps API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBEYlDB7H0z4_06e7MPKycHK12jw4lpnyg&libraries=places"></script>

    <style>
        /* Styles for header from listing.css - Global * and body initially omitted */
        /* body {
          font-family: "Montserrat", sans-serif; 
          color: #333; 
          background-color: white; 
        } */

        .header {
          display: flex;
          align-items: center;
          padding: 0 20px;
          height: 80px;
          background-color: white; 
          width: 100%;
          border-bottom: 1px solid #eee;
          position: sticky;
          top: 0;
          z-index: 100;
        }

        .search-bar-container {
          display: flex;
          max-width: 480px;
          width: 30%;
          background-color: white;
          border-radius: 50px;
          overflow: hidden;
          height: 44px;
          border: 1px solid #ddd;
          position: absolute;
          left: 50%;
          top: 50%;
          transform: translate(-50%, -50%);
        }

        .search-input {
          flex-grow: 1;
          border: none;
          padding: 0 20px;
          font-size: 0.9rem;
          outline: none;
          color: #333; 
          height: 100%;
          background-color: white;
        }

        .search-input::placeholder {
          color: #888;
        }

        .search-button {
          background-color: white;
          color: #ff6600;
          border: none;
          padding: 0 20px;
          height: 100%;
          border-radius: 0 50px 50px 0;
          cursor: pointer;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 1rem;
          transition: color 0.2s ease;
        }

        .search-button:hover {
          background-color: #f8f8f8;
          color: #e65c00;
        }

        .header-links {
          display: flex;
          gap: 25px;
          align-items: center;
          margin-left: auto;
        }

        .header-link {
          color: #333;
          text-decoration: none;
          font-weight: 500;
          font-size: 0.95rem;
          padding: 8px 12px;
          border-radius: 4px;
          transition: background-color 0.2s;
        }
        
        .custom-checkbox {
          appearance: none;
          -webkit-appearance: none;
          width: 20px;
          height: 20px;
          border: 2px solid #d1d5db;
          border-radius: 50%;
          position: relative;
          cursor: pointer;
          flex-shrink: 0;
        }
        
        .custom-checkbox:checked {
          background-color: #ea580c;
          border-color: #ea580c;
        }
        
        .custom-checkbox:checked::after {
          content: '×';
          position: absolute;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          color: white;
        }

        /* Lucide Icons basic styling (actual icons are SVG) */
        .lucide {
            display: inline-block;
            vertical-align: middle;
            stroke-width: 2;
            fill: none;
            stroke: currentColor;
        }

        .cta-button {
          width: 100%;
          background-color: #ff6600;
          color: white;
          border: none;
          padding: 14px;
          font-size: 1rem;
          font-weight: 600;
          border-radius: 8px;
          cursor: pointer;
          text-align: center;
          transition: background-color 0.2s ease;
        }

        .cta-button:hover {
          background-color: #e65c00;
        }

        .custom-orange-button {
          background-color: #fb923c; /* Tailwind orange-400 */
        }
        .custom-orange-button:hover {
          background-color: #f97316; /* Tailwind orange-500 for hover */
        }

        .force-square-shape {
            aspect-ratio: 1 / 1 !important;
            height: auto !important; /* Attempt to override any fixed height */
        }

        /* Google Places Autocomplete Dropdown Styling */
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
    </style>
</head>
<body>
    <div class="min-h-screen bg-white" style="font-family: 'Montserrat', sans-serif;">
      <!-- Header from listing.php -->
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
            <a href="#" class="header-link">Manage Rentals</a>
            <a href="#" class="header-link">Sign In</a>
            <a href="#" class="header-link">Add Property</a>
        </div>
    </header>

      <!-- Main Content -->
      <main class="max-w-5xl mx-auto px-6 py-8" style="position: relative; z-index: 1;">
        <form method="POST" id="add-listing-form" enctype="multipart/form-data">
          <div class="space-y-8">
            <!-- Title Section -->
            <div>
              <h1 class="text-3xl font-semibold text-gray-900 mb-2">Create Your Property Listing</h1>
              <p class="text-gray-600">Add details about your property to start hosting</p>
            </div>
            
            <div class="bg-white">
              <!-- Property Information -->
              <div class="space-y-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Property Title
                  </label>
                  <input
                    type="text"
                    class="w-full px-4 py-3 border rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
                    placeholder="e.g., Luxury Downtown Loft with Mountain Views"
                    name="title" id="title" required
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Property Description
                  </label>
                  <textarea
                    rows="6"
                    class="w-full px-4 py-3 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Describe your property, its features, and what makes it special..."
                    name="description" id="description" required
                  ></textarea>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Street Address
                  </label>
                  <input
                    type="text"
                    class="w-full px-4 py-3 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Start typing an address..." 
                    name="street_address" id="street_address" autocomplete="off" required
                  />
                  <input type="hidden" name="city" id="city">
                  <input type="hidden" name="state" id="state">
                  <input type="hidden" name="zip_code" id="zip_code">
                  <input type="hidden" name="country" id="country">
                  <input type="hidden" name="latitude" id="latitude">
                  <input type="hidden" name="longitude" id="longitude">
                </div>
              </div>

              <!-- Property Details -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-8">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Property Type
                  </label>
                  <select name="property_type" id="property_type" required class="w-full px-4 py-3 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select a property type</option>
                    <option value="Apartment">Apartment</option>
                    <option value="House">House</option>
                    <option value="Condo">Condo</option>
                    <option value="Townhouse">Townhouse</option>
                    <option value="Studio">Studio</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Price per Night ($)
                  </label>
                  <input
                    type="number"
                    class="w-full px-4 py-3 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="150"
                    name="price" id="price" step="0.01" required
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Number of Guests
                  </label>
                  <input
                    type="number"
                    class="w-full px-4 py-3 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="2"
                    name="max_guests" id="max_guests" required
                  />
                </div>
              </div>

              <!-- Property Features -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-8">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Bedrooms
                    </label>
                    <input
                      type="number"
                      class="w-full px-4 py-3 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="e.g., 3"
                      name="bedrooms" id="bedrooms" required
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Bathrooms
                    </label>
                    <input
                      type="number"
                      step="0.5"
                      class="w-full px-4 py-3 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="e.g., 2.5"
                      name="bathrooms" id="bathrooms" required
                    />
                  </div>
              </div>
              
              <!-- Amenities Section -->
              <div class="pt-8">
                <label class="block text-lg font-semibold text-gray-900 mb-4">
                  Amenities
                </label>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-6 gap-y-4">
                  <?php
                    if (isset($db)) { // Check if $db is available
                      $amenities_stmt = $db->query("SELECT amenity_id, name FROM Amenities ORDER BY name");
                      while ($amenity = $amenities_stmt->fetch(PDO::FETCH_ASSOC)) {
                          echo '<div class="flex items-center">';
                          echo '<input type="checkbox" id="amenity_' . htmlspecialchars($amenity['amenity_id']) . '" name="amenities[]" value="' . htmlspecialchars($amenity['amenity_id']) . '" class="custom-checkbox mr-3">';
                          echo '<label for="amenity_' . htmlspecialchars($amenity['amenity_id']) . '" class="text-sm text-gray-700">'. htmlspecialchars($amenity['name']) .'</label>';
                          echo '</div>';
                      }
                    } else {
                      echo '<p class="text-red-500">Error: Database connection not available for amenities.</p>';
                    }
                  ?>
                </div>
              </div>

              <!-- Photo Upload Section -->
              <div class="pt-8">
                <label class="block text-lg font-semibold text-gray-900 mb-4">
                  Upload Photos
                </label>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center" id="drop-zone">
                  <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                  <label for="photos" class="mt-2 text-sm text-gray-600 block">
                    Drag and drop your photos here, or 
                    <span class="font-medium text-orange-600 hover:text-orange-500 focus:outline-none cursor-pointer">
                      browse to upload
                    </span>
                  </label>
                  <input type="file" name="photos[]" id="photos" multiple accept="image/*" class="hidden">
                  <p class="mt-1 text-xs text-gray-500">PNG, JPG, GIF up to 10MB (Max 5 files recommended)</p>
                   <!-- Container for photo previews -->
                  <div id="photo-preview" class="mt-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                      <!-- Photo previews will be dynamically inserted here -->
                  </div>
                </div>
              </div>
              
              <!-- Map Placeholder -->
              <div class="pt-8">
                  <label class="block text-lg font-semibold text-gray-900 mb-4">Property Location on Map</label>
                  <div id="map-add" style="height: 400px; border-radius: 8px;" class="border border-gray-300">
                      <!-- Map will be initialized here by Google Maps JS -->
                  </div>
              </div>


              <!-- Submit Button -->
              <div class="pt-10 text-right">
                <button type="submit" class="cta-button">
                  Create Listing
                </button>
              </div>
            </div>
          </div>
        </form>
      </main>
    </div>

    <!-- Scripts -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
      lucide.createIcons();
    </script>
    
    <script>
      let map, marker, autocomplete;
      function initMapAndAutocomplete() {
        const defaultCenter = { lat: 40.0, lng: -75.0 }; // You might want to set a more relevant default
        map = new google.maps.Map(document.getElementById('map-add'), {
            center: defaultCenter,
            zoom: 12
        });
        marker = new google.maps.Marker({
            position: defaultCenter,
            map,
            draggable: true
        });
        
        marker.addListener('dragend', () => {
            const pos = marker.getPosition();
            if (pos) {
                document.getElementById('latitude').value  = pos.lat().toFixed(6);
                document.getElementById('longitude').value = pos.lng().toFixed(6);
            }
        });

        const streetInput = document.getElementById('street_address');
        if (streetInput) {
            autocomplete = new google.maps.places.Autocomplete(streetInput, {
                types: ['geocode']
            });
            autocomplete.addListener('place_changed', () => {
                const place = autocomplete.getPlace();
                if (!place.geometry || !place.geometry.location) return;

                map.setCenter(place.geometry.location);
                map.setZoom(15);
                marker.setPosition(place.geometry.location);

                document.getElementById('latitude').value  = place.geometry.location.lat().toFixed(6);
                document.getElementById('longitude').value = place.geometry.location.lng().toFixed(6);

                const comps = place.address_components;
                function getComp(type) {
                    const c = comps ? comps.find(c => c.types.includes(type)) : null;
                    return c ? c.long_name : '';
                }
                document.getElementById('city').value      = getComp('locality');
                document.getElementById('state').value     = getComp('administrative_area_level_1');
                document.getElementById('zip_code').value  = getComp('postal_code');
                document.getElementById('country').value   = getComp('country');
            });
        } else {
            console.error('Street address input not found for autocomplete.');
        }
      }

      // --- REMOVE Cloudinary Upload Widget Configuration and related code --- 
      // const cloudName = "daeqajxe0"; 
      // const uploadPreset = "roofshare";
      // if (typeof cloudinary !== 'undefined') { ... } else { console.error('Cloudinary library not loaded.'); }
      // --- END REMOVE Cloudinary --- 

      // --- ADD JavaScript for local file preview --- 
      const photoInput = document.getElementById('photos');
      const previewContainer = document.getElementById('photo-preview');
      const dropZone = document.getElementById('drop-zone');
      let selectedFiles = []; // To keep track of files if we want to implement more complex removal

      // Prevent default drag behaviors
      ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
      });

      // Highlight drop zone when item is dragged over it
      ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
      });

      ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
      });

      // Handle dropped files
      dropZone.addEventListener('drop', handleDrop, false);

      function preventDefaults (e) {
        e.preventDefault();
        e.stopPropagation();
      }

      function highlight(e) {
        dropZone.classList.add('border-orange-500', 'bg-orange-50');
      }

      function unhighlight(e) {
        dropZone.classList.remove('border-orange-500', 'bg-orange-50');
      }

      function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
      }

      function handleFiles(files) {
        if (files.length > 5) {
          alert('You can select a maximum of 5 images. The first 5 will be shown.');
        }
        
        // Convert FileList to Array and take first 5 files
        const validFiles = Array.from(files).slice(0, 5).filter(file => {
          if (!file.type.startsWith('image/')) {
            alert(`File ${file.name} is not an image and will not be previewed or uploaded.`);
            return false;
          }
          return true;
        });

        // If we already have files, append the new ones (up to 5 total)
        if (selectedFiles.length > 0) {
          const remainingSlots = 5 - selectedFiles.length;
          if (remainingSlots > 0) {
            selectedFiles = [...selectedFiles, ...validFiles.slice(0, remainingSlots)];
          }
        } else {
          selectedFiles = validFiles;
        }

        // Update the file input to include all selected files
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => dataTransfer.items.add(file));
        photoInput.files = dataTransfer.files;

        updatePreview();
      }

      function updatePreview() {
        previewContainer.innerHTML = '';
        
        selectedFiles.forEach((file, index) => {
          const reader = new FileReader();
          reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'relative force-square-shape';
            
            const img = document.createElement('img');
            img.src = e.target.result;
            img.alt = file.name;
            img.className = 'absolute inset-0 w-full h-full object-cover rounded-md';
            
            const removeBtn = document.createElement('button');
            removeBtn.innerHTML = '×';
            removeBtn.className = 'absolute top-1 right-1 bg-orange-500 text-white rounded-full p-0.5 leading-none text-xs w-5 h-5 flex items-center justify-center hover:bg-orange-600 transition-colors';
            removeBtn.type = 'button';
            
            removeBtn.onclick = () => {
              selectedFiles = selectedFiles.filter((_, i) => i !== index);
              // Update the file input to reflect the removed file
              const dataTransfer = new DataTransfer();
              selectedFiles.forEach(file => dataTransfer.items.add(file));
              photoInput.files = dataTransfer.files;
              updatePreview();
            };
            
            div.appendChild(img);
            div.appendChild(removeBtn);
            previewContainer.appendChild(div);
          };
          reader.readAsDataURL(file);
        });
      }

      if (photoInput && previewContainer) {
        photoInput.addEventListener('change', function(event) {
          handleFiles(event.target.files);
        });
      } else {
        if (!photoInput) console.error('Photo input #photos not found.');
        if (!previewContainer) console.error('Photo preview container #photo-preview not found.');
      }
      // --- END JavaScript for local file preview ---

      // Prevent form submission on enter key in most inputs
      const form = document.getElementById('add-listing-form');
      if (form) {
        form.addEventListener('keydown', function(e) {
          if (e.key === 'Enter' && e.target.type !== 'textarea') { // Allow enter in textareas
            e.preventDefault();
            // return false; // Not strictly necessary with preventDefault
          }
        });
      }

      // Wait for the Maps script to load, then init
      window.addEventListener('load', () => {
        if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
          initMapAndAutocomplete();
        } else {
          console.error('Google Maps API not loaded by window.load.');
          // Fallback or retry mechanism could be added here
        }
      });
    </script>
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

                    // Store the coordinates for the search
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

        // Initialize autocomplete when the page loads
        window.onload = function() {
            initAutocomplete();
        };
    </script>
</body>
</html>
