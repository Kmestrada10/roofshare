<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Check if user is logged in and is a renter
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || strtolower($_SESSION['user_type']) !== 'renter') {
    header("Location: listing.php?id=" . $_POST['listing_id']);
    exit;
}

if (!isset($_POST['listing_id']) || !isset($_POST['action'])) {
    header("Location: listing.php?id=" . $_POST['listing_id']);
    exit;
}

$listing_id = (int)$_POST['listing_id'];
$action = $_POST['action'];
$renter_id = $_SESSION['user_id'];

try {
    require_once 'config/db.php';
    
    if ($action === 'save') {
        $db->query("INSERT INTO Saves (renter_id, listing_id) VALUES ($renter_id, $listing_id)");
    } else if ($action === 'unsave') {
        $db->query("DELETE FROM Saves WHERE renter_id = $renter_id AND listing_id = $listing_id");
    }
} catch (Exception $e) {
    // Log the error
    error_log("Error in save_listing.php: " . $e->getMessage());
}

// Redirect back to the listing page
header("Location: listing.php?id=" . $listing_id);
exit; 