<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("config/db.php");
session_start();


if (($_SESSION['user_type'] ?? '') !== 'Realtor') {
    echo "Access denied. Only realtors can add listings.";
    exit;
}


function getRealtorId($db, $email)
{
    $stmt = $db->prepare("SELECT realtor_id FROM Realtor WHERE email = ?");
    $stmt->execute([$email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $row['realtor_id'] : null;
}
$realtor_id = getRealtorId($db, $_SESSION['user_email']);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title         = $_POST['title'] ?? '';
    $description   = $_POST['description'] ?? '';
    $price         = $_POST['price'] ?? 0;
    $property_type = $_POST['property_type'] ?? '';
    $bedrooms      = $_POST['bedrooms'] ?? 0;
    $bathrooms     = $_POST['bathrooms'] ?? 0;
    $max_guests    = $_POST['max_guests'] ?? 1;
    $amenities     = $_POST['amenities'] ?? []; 
    
  
    $street        = $_POST['street_address'] ?? '';
    $city          = $_POST['city'] ?? '';
    $state         = $_POST['state'] ?? '';
    $zip           = $_POST['zip_code'] ?? '';
    $country       = $_POST['country'] ?? '';
    $latitude      = $_POST['latitude'] ?? null;
    $longitude     = $_POST['longitude'] ?? null;

    try {
        $db->beginTransaction();

        $query = "INSERT INTO Listing 
            (title, description, price, status,
             property_type, bedrooms, bathrooms,
             max_guests, street_address, city,
             state, zip_code, country, latitude,
             longitude, realtor_id)
          VALUES 
            ('$title', '$description', $price,
            'Available', '$property_type', $bedrooms, $bathrooms,
            $max_guests, '$street', '$city', '$state',
            '$zip', '$country', $latitude, $longitude,
            $realtor_id)";
        
        $db->query($query);
        $listing_id = $db->lastInsertId();

 
        if (!empty($amenities)) {
            foreach ($amenities as $amenity_id) {
                $db->query("INSERT INTO ListingAmenities (listing_id, amenity_id) VALUES ($listing_id, $amenity_id)");
            }
        }

        
        if (!empty($_POST['photo_urls'])) {
            $photo_urls = explode(',', $_POST['photo_urls']);
            foreach ($photo_urls as $key => $photo_url) {
                $db->query("INSERT INTO ListingPhoto (listing_id, photo_url, photo_order) VALUES ($listing_id, '$photo_url', " . ($key + 1) . ")");
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
  <title>Add Listing</title>
  <link rel="stylesheet" href="assets/css/add_listing.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">

  
  <script src="https://widget.cloudinary.com/v2.0/global/all.js" type="text/javascript"></script>
  
  <script
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBEYlDB7H0z4_06e7MPKycHK12jw4lpnyg&libraries=places"
    defer
  ></script>
</head>
<body>
  <div class="form-container">
    <h2>Add New Listing</h2>
    
    <form method="POST" id="add-listing-form">
      <div class="form-group">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" required>
      </div>

      <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="4" required></textarea>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="price">Price ($)</label>
          <input type="number" id="price" name="price" step="0.01" required>
        </div>

        <div class="form-group">
          <label for="property_type">Property Type</label>
          <select id="property_type" name="property_type" required>
            <option value="">Select a property type</option>
            <option value="Apartment">Apartment</option>
            <option value="House">House</option>
            <option value="Condo">Condo</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="bedrooms">Bedrooms</label>
          <input type="number" id="bedrooms" name="bedrooms" required>
        </div>

        <div class="form-group">
          <label for="bathrooms">Bathrooms</label>
          <input type="number" id="bathrooms" name="bathrooms" step="0.5" required>
        </div>
      </div>

      <div class="form-group">
        <label for="max_guests">Max Guests</label>
        <input type="number" id="max_guests" name="max_guests" required>
      </div>

      <div class="form-group">
        <label for="street_address">Street Address</label>
        <input 
          type="text" 
          id="street_address" 
          name="street_address" 
          placeholder="Start typing an address..." 
          autocomplete="off" 
          required
        >
      </div>

      <input type="hidden" name="city" id="city">
      <input type="hidden" name="state" id="state">
      <input type="hidden" name="zip_code" id="zip_code">
      <input type="hidden" name="country" id="country">
      <input type="hidden" name="latitude" id="latitude">
      <input type="hidden" name="longitude" id="longitude">
      
      <div id="map-add"></div>

      <div class="form-group">
        <label for="photos">Photos</label>
        <div class="photo-upload-container">
          <button type="button" id="upload_widget" class="cloudinary-button">Upload Photos</button>
          <div id="photo-preview" class="photo-preview"></div>
          <input type="hidden" name="photo_urls" id="photo_urls">
        </div>
      </div>

      <div class="form-group">
        <label>Amenities</label>
        <div class="amenities-grid">
          <?php
          $amenities_stmt = $db->query("SELECT * FROM Amenities ORDER BY name");
          while ($amenity = $amenities_stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<div class="amenity-item">';
            echo '<input type="checkbox" id="amenity_' . $amenity['amenity_id'] . '" name="amenities[]" value="' . $amenity['amenity_id'] . '">';
            echo '<label for="amenity_' . $amenity['amenity_id'] . '">';
            echo '<span class="amenity-icon">' . $amenity['icon'] . '</span>';
            echo $amenity['name'];
            echo '</label>';
            echo '</div>';
          }
          ?>
        </div>
      </div>

      <input type="submit" value="Add Listing">
    </form>
  </div>

  <script>
    let map, marker, autocomplete;
    function initMapAndAutocomplete() {
      const defaultCenter = { lat: 40.0, lng: -75.0 };
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
        document.getElementById('latitude').value  = pos.lat().toFixed(6);
        document.getElementById('longitude').value = pos.lng().toFixed(6);
      });

  
      const input = document.getElementById('street_address');
      autocomplete = new google.maps.places.Autocomplete(input, {
        types: ['geocode']
      });
      autocomplete.addListener('place_changed', () => {
        const place = autocomplete.getPlace();
        if (!place.geometry) return;

       
        map.setCenter(place.geometry.location);
        map.setZoom(15);
        marker.setPosition(place.geometry.location);

    
        document.getElementById('latitude').value  = place.geometry.location.lat().toFixed(6);
        document.getElementById('longitude').value = place.geometry.location.lng().toFixed(6);


        const comps = place.address_components;
        function getComp(type) {
          const c = comps.find(c => c.types.includes(type));
          return c ? c.long_name : '';
        }
        document.getElementById('city').value      = getComp('locality');
        document.getElementById('state').value     = getComp('administrative_area_level_1');
        document.getElementById('zip_code').value  = getComp('postal_code');
        document.getElementById('country').value   = getComp('country');
    });
    }

    
    const cloudName = "daeqajxe0";
    const uploadPreset = "roofshare"; 

    const myWidget = cloudinary.createUploadWidget(
      {
        cloudName: cloudName,
        uploadPreset: uploadPreset,
        sources: ['local'],
        multiple: true,
        maxFiles: 5,
        resourceType: 'image',
        clientAllowedFormats: ['jpg', 'jpeg', 'png'],
        maxFileSize: 2000000,
        showAdvancedOptions: false
      },
      (error, result) => {
        console.log('Upload result:', result); 
        console.log('Upload error:', error);   
        
        if (error) {
          console.error('Upload error details:', error);
          alert('Error uploading image. Please try again.');
          return;
        }
        
        if (result && result.event === "success") {
          console.log('Upload successful:', result.info); 
          
          const imageUrl = result.info.secure_url;
   
          const preview = document.getElementById('photo-preview');
          const div = document.createElement('div');
          div.className = 'preview-item';
          div.innerHTML = `
            <img src="${imageUrl}" alt="Preview">
            <span class="preview-number">#${preview.children.length + 1}</span>
          `;
          preview.appendChild(div);

       
          const photoUrls = document.getElementById('photo_urls');
          const currentUrls = photoUrls.value ? photoUrls.value.split(',') : [];
          currentUrls.push(imageUrl);
          photoUrls.value = currentUrls.join(',');
        }
      }
    );

    document.getElementById("upload_widget").addEventListener("click", function(e) {
      e.preventDefault();
      console.log('Opening upload widget...');
      myWidget.open();
    }, false);


    document.getElementById('add-listing-form').addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        return false;
      }
    });

   
    window.addEventListener('load', () => {
      if (typeof google !== 'undefined') {
        initMapAndAutocomplete();
      }
    });
    </script>
</body>
</html>
