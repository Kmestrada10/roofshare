<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once("config/db.php");

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Renter') {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['user_email'];
$stmt = $db->prepare("SELECT renter_id FROM Renter WHERE email = ?");
$stmt->execute([$email]);
$renter = $stmt->fetch();
$renter_id = $renter['renter_id'];

// Get listings saved by this renter
$stmt = $db->prepare("SELECT listing_id FROM Saves WHERE renter_id = ?");
$stmt->execute([$renter_id]);
$saved_listings = $stmt->fetchAll(PDO::FETCH_COLUMN);

$matches = [];

if (!empty($saved_listings)) {
    $placeholders = str_repeat('?,', count($saved_listings) - 1) . '?';

    // Find other renters who saved the same listings
    $stmt = $db->prepare("SELECT DISTINCT renter_id FROM Saves WHERE listing_id IN ($placeholders) AND renter_id != ?");
    $stmt->execute([...$saved_listings, $renter_id]);
    $matched_renters = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($matched_renters)) {
        $placeholder_renters = str_repeat('?,', count($matched_renters) - 1) . '?';
        $stmt = $db->prepare("SELECT * FROM Roommate_Preference WHERE renter_id IN ($placeholder_renters)");
        $stmt->execute($matched_renters);
        $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Load current renter's preferences
$stmt = $db->prepare("SELECT * FROM Roommate_Preference WHERE renter_id = ?");
$stmt->execute([$renter_id]);
$current_prefs = $stmt->fetch(PDO::FETCH_ASSOC);

function calculateCompatibility($prefsA, $prefsB) {
    $score = 0;
    $total = 0;
    foreach ($prefsA as $key => $value) {
        if ($key === 'pref_id' || $key === 'renter_id') continue;
        $total++;
        if ($prefsA[$key] == $prefsB[$key]) $score++;
    }
    return round(($score / $total) * 100);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Roommate Matches</title>
    <link rel="stylesheet" href="assets/css/renter_dashboard.css">
    <style>
        .matches-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h2 {
            color: #007BFF;
            margin-bottom: 20px;
        }
        .match-box {
            border-bottom: 1px solid #ccc;
            padding: 15px 0;
        }
        .match-box:last-child {
            border-bottom: none;
        }
        .match-score {
            font-weight: bold;
            color: green;
        }
    </style>
</head>
<body>
<div class="matches-container">
    <h2>Roommate Matches (Shared Listings)</h2>
    <?php if (empty($matches)): ?>
        <p>No matches found yet. Save some listings to see potential roommates.</p>
    <?php else: ?>
        <?php foreach ($matches as $match): ?>
            <div class="match-box">
                <p>Renter ID: <?= htmlspecialchars($match['renter_id']) ?></p>
                <p>Compatibility: <span class="match-score">
                    <?= calculateCompatibility($current_prefs, $match) ?>%</span></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>
