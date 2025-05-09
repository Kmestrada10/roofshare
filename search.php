<?php
require_once("configuration/db.php");
session_start();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Search Listings</title>
</head>

<body>

    <h2>Search Listings</h2>

    <form method="GET">
        <label>City: <input type="text" name="city"></label><br>
        <label>Min Price: <input type="number" name="min_price"></label><br>
        <label>Max Price: <input type="number" name="max_price"></label><br>
        <input type="submit" value="Search">
    </form>

    <?php
    if (!empty($_GET)) {
        $city = strtolower($_GET['city'] ?? '');
        $min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? $_GET['min_price'] : 0;
        $max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? $_GET['max_price'] : 10000000;


        try {
            $query = "SELECT * FROM Listing WHERE LOWER(city) LIKE ? AND price BETWEEN ? AND ?";
            $stmt = $db->prepare($query);
            $stmt->execute(["%$city%", $min_price, $max_price]);
            $listings = $stmt->fetchAll();

            if ($listings) {
                foreach ($listings as $listing) {
                    echo "<hr>";
                    echo "<strong>Title:</strong> " . htmlspecialchars($listing['title']) . "<br>";
                    echo "<strong>Price:</strong> $" . htmlspecialchars($listing['price']) . "<br>";
                    echo "<strong>City:</strong> " . htmlspecialchars($listing['city']) . "<br>";
                    echo "<strong>Type:</strong> " . htmlspecialchars($listing['property_type']) . "<br>";
                    echo "<strong>Description:</strong> " . nl2br(htmlspecialchars($listing['description'])) . "<br>";
                }
            } else {
                echo "<p>No listings found.</p>";
            }
        } catch (PDOException $e) {
            echo "<p>Error: " . $e->getMessage() . "</p>";
        }
    }
    ?>

</body>

</html>