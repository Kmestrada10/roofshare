<?php
session_start();
require_once("config/db.php");

// Set content type to JSON
header('Content-Type: application/json');

// Enable error logging
error_log("Delete listing request received");
error_log("POST data: " . print_r($_POST, true));
error_log("Session data: " . print_r($_SESSION, true));

// Check if user is logged in and is a realtor
if (!isset($_SESSION['user_type']) || strtolower($_SESSION['user_type']) !== 'realtor') {
    error_log("Access denied - User not logged in as realtor");
    echo json_encode(['success' => false, 'message' => 'You must be logged in as a realtor to delete listings']);
    exit;
}

// Check if listing_id is provided
if (!isset($_POST['listing_id'])) {
    error_log("No listing ID provided in request");
    echo json_encode(['success' => false, 'message' => 'No listing ID provided']);
    exit;
}

$listing_id = (int)$_POST['listing_id'];
$realtor_email = $_SESSION['user_email'];
error_log("Attempting to delete listing ID: " . $listing_id . " for realtor: " . $realtor_email);

try {
    // First verify that the listing belongs to this realtor
    $check_stmt = $db->prepare("
        SELECT l.listing_id 
        FROM Listing l 
        JOIN Realtor r ON l.realtor_id = r.realtor_id 
        WHERE l.listing_id = ? AND r.email = ?
    ");
    $check_stmt->execute([$listing_id, $realtor_email]);
    
    if (!$check_stmt->fetch()) {
        error_log("Permission denied - Listing does not belong to realtor");
        echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this listing']);
        exit;
    }

    // Begin transaction
    $db->beginTransaction();
    error_log("Transaction started");

    // Delete related records first
    $db->prepare("DELETE FROM ListingPhoto WHERE listing_id = ?")->execute([$listing_id]);
    error_log("Deleted listing photos");
    
    $db->prepare("DELETE FROM ListingAmenities WHERE listing_id = ?")->execute([$listing_id]);
    error_log("Deleted listing amenities");
    
    $db->prepare("DELETE FROM Saves WHERE listing_id = ?")->execute([$listing_id]);
    error_log("Deleted listing saves");
    
    // Finally delete the listing
    $delete_stmt = $db->prepare("DELETE FROM Listing WHERE listing_id = ?");
    $result = $delete_stmt->execute([$listing_id]);
    error_log("Deleted main listing record");

    if ($result) {
        $db->commit();
        error_log("Transaction committed successfully");
        echo json_encode(['success' => true, 'message' => 'Listing deleted successfully']);
    } else {
        $db->rollBack();
        error_log("Transaction rolled back - Delete operation failed");
        echo json_encode(['success' => false, 'message' => 'Failed to delete listing']);
    }
} catch (PDOException $e) {
    $db->rollBack();
    error_log("Error deleting listing: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode(['success' => false, 'message' => 'An error occurred while deleting the listing']);
} 