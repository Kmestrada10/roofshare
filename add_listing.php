<?php
require_once("configuration/db.php");
session_start();

// Restrict to Realtors only
// if ($_SESSION['user_type'] !== 'Realtor') {
//     echo "Access denied. Only realtors can add listings.";
//     exit;
// }

// $realtor_id = getRealtorId($db, $_SESSION['user_email']); // helper below

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $property_type = $_POST['property_type'] ?? '';
    $bedrooms = $_POST['bedrooms'] ?? 0;
    $bathrooms = $_POST['bathrooms'] ?? 0;
    $max_guests = $_POST['max_guests'] ?? 1;
    $street = $_POST['street_address'] ?? '';
    $city = $_POST['city'] ?? '';
    $state = $_POST['state'] ?? '';
    $zip = $_POST['zip_code'] ?? '';
    $country = $_POST['country'] ?? '';

    try {
        $stmt = $db->prepare("INSERT INTO Listing (title, description, price, status, property_type, bedrooms, bathrooms, max_guests, street_address, city, state, zip_code, country, realtor_id)
                              VALUES (?, ?, ?, 'Available', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
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
            $realtor_id
        ]);
        echo "✅ Listing added successfully.";
    } catch (PDOException $e) {
        echo "❌ Error: " . $e->getMessage();
    }
}

// Helper: fetch realtor_id from email
function getRealtorId($db, $email)
{
    $stmt = $db->prepare("SELECT realtor_id FROM Realtor WHERE email = ?");
    $stmt->execute([$email]);
    $row = $stmt->fetch();
    return $row ? $row['realtor_id'] : null;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Add Listing</title>
</head>

<body>

    <h2>Add New Listing</h2>

    <form method="POST">
        <label>Title: <input name="title" required></label><br>
        <label>Description: <textarea name="description" required></textarea></label><br>
        <label>Price: <input type="number" step="0.01" name="price" required></label><br>
        <label>Property Type: <input name="property_type" required></label><br>
        <label>Bedrooms: <input type="number" name="bedrooms" required></label><br>
        <label>Bathrooms: <input type="number" step="0.5" name="bathrooms" required></label><br>
        <label>Max Guests: <input type="number" name="max_guests" required></label><br>
        <label>Street Address: <input name="street_address" required></label><br>
        <label>City: <input name="city" required></label><br>
        <label>State: <input name="state" required></label><br>
        <label>Zip Code: <input name="zip_code" required></label><br>
        <label>Country: <input name="country" required></label><br>
        <input type="submit" value="Add Listing">
    </form>

</body>

</html>