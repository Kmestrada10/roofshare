<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


session_start();


header('Content-Type: application/json');


error_log("Save listing request - Session data: " . print_r($_SESSION, true));
error_log("Save listing request - POST data: " . print_r($_POST, true));


if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || strtolower($_SESSION['user_type']) !== 'renter') {
    error_log("Save listing failed - User not logged in as renter");
    echo json_encode(['success' => false, 'message' => 'You must be logged in as a renter to save listings']);
    exit;
}

if (!isset($_POST['listing_id']) || !isset($_POST['action'])) {
    error_log("Save listing failed - Missing parameters");
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$listing_id = (int)$_POST['listing_id'];
$action = $_POST['action'];
$renter_id = $_SESSION['user_id'];

error_log("Processing save/unsave - Action: $action, Listing ID: $listing_id, Renter ID: $renter_id");

try {
    require_once 'config/db.php';
    
   
    $check = $db->prepare("SELECT COUNT(*) FROM Listing WHERE listing_id = ?");
    $check->execute([$listing_id]);
    $listingExists = $check->fetchColumn();
    error_log("Listing exists check: " . ($listingExists ? 'Yes' : 'No'));


    $check = $db->prepare("SELECT COUNT(*) FROM Renter WHERE renter_id = ?");
    $check->execute([$renter_id]);
    $renterExists = $check->fetchColumn();
    error_log("Renter exists check: " . ($renterExists ? 'Yes' : 'No'));
    
    if ($action === 'save') {
     
        $check = $db->prepare("SELECT COUNT(*) FROM Saves WHERE renter_id = ? AND listing_id = ?");
        $check->execute([$renter_id, $listing_id]);
        if ($check->fetchColumn() > 0) {
            error_log("Save failed - Listing already saved");
            echo json_encode(['success' => false, 'message' => 'Listing already saved']);
            exit;
        }

   
        $stmt = $db->prepare("INSERT INTO Saves (renter_id, listing_id, saved_at) VALUES (?, ?, NOW())");
        $result = $stmt->execute([$renter_id, $listing_id]);
        error_log("Save query result: " . ($result ? 'Success' : 'Failed'));
        if ($result) {
            error_log("Successfully saved listing");
            echo json_encode(['success' => true, 'message' => 'Listing saved successfully']);
        } else {
            error_log("Failed to save listing");
            echo json_encode(['success' => false, 'message' => 'Failed to save listing']);
        }
    } else if ($action === 'unsave') {
        $stmt = $db->prepare("DELETE FROM Saves WHERE renter_id = ? AND listing_id = ?");
        $result = $stmt->execute([$renter_id, $listing_id]);
        error_log("Unsave query result: " . ($result ? 'Success' : 'Failed'));
        if ($result) {
            error_log("Successfully unsaved listing");
            echo json_encode(['success' => true, 'message' => 'Listing unsaved successfully']);
        } else {
            error_log("Failed to unsave listing");
            echo json_encode(['success' => false, 'message' => 'Failed to unsave listing']);
        }
    } else {
        error_log("Invalid action: $action");
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (PDOException $e) {
    error_log("Database error in save_listing.php: " . $e->getMessage());
    error_log("SQL State: " . $e->getCode());
    echo json_encode(['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()]);
} 
