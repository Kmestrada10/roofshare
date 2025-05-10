<?php
session_start();
require_once("config/db.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'You must be logged in to report a listing']);
    exit;
}

// Check if required fields are present
if (!isset($_POST['listing_id']) || !isset($_POST['description'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$listing_id = (int)$_POST['listing_id'];
$description = trim($_POST['description']);
$user_id = $_SESSION['user_id'];

try {
    // Begin transaction
    $db->beginTransaction();

    // Insert the report
    $stmt = $db->prepare("
        INSERT INTO ReportedAccount (realtor_id, description, strikes, created_at)
        SELECT r.realtor_id, ?, 1, NOW()
        FROM Listing l
        JOIN Realtor r ON l.realtor_id = r.realtor_id
        WHERE l.listing_id = ?
    ");
    $stmt->execute([$description, $listing_id]);

    // Commit transaction
    $db->commit();

    // Return success response
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Listing reported successfully']);
} catch (PDOException $e) {
    // Rollback transaction on error
    $db->rollBack();
    
    // Log error
    error_log("Error reporting listing: " . $e->getMessage());
    
    // Return error response
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'An error occurred while reporting the listing']);
}
?> 