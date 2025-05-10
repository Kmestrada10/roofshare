<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once("config/db.php");

if (!isset($_SESSION['user_type']) || strtolower($_SESSION['user_type']) !== 'renter') {
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
        $stmt = $db->prepare("SELECT rp.*, r.first_name, r.last_name, r.email 
                             FROM Roommate_Preference rp 
                             JOIN Renter r ON rp.renter_id = r.renter_id 
                             WHERE rp.renter_id IN ($placeholder_renters)");
        $stmt->execute($matched_renters);
        $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Load current renter's preferences
$stmt = $db->prepare("SELECT * FROM Roommate_Preference WHERE renter_id = ?");
$stmt->execute([$renter_id]);
$current_prefs = $stmt->fetch(PDO::FETCH_ASSOC);

// Fake matches data for testing
$matches = [
    [
        'renter_id' => 2,
        'first_name' => 'Sarah',
        'last_name' => 'Johnson',
        'email' => 'sarah.j@example.com',
        'cleanliness' => 2,
        'smoking_preference' => 1,
        'noise_tolerance' => 2,
        'pet_preference' => 2,
        'cooking_habits' => 2,
        'diet_preference' => 1,
        'social_preference' => 1,
        'guest_preference' => 1,
        'gender_preference' => 0,
        'sleep_schedule' => 1,
        'temperature_preference' => 1,
        'shared_expenses' => 1,
        'lease_duration' => 2
    ],
    [
        'renter_id' => 3,
        'first_name' => 'Michael',
        'last_name' => 'Chen',
        'email' => 'mchen@example.com',
        'cleanliness' => 2,
        'smoking_preference' => 1,
        'noise_tolerance' => 1,
        'pet_preference' => 1,
        'cooking_habits' => 2,
        'diet_preference' => 2,
        'social_preference' => 2,
        'guest_preference' => 1,
        'gender_preference' => 0,
        'sleep_schedule' => 2,
        'temperature_preference' => 1,
        'shared_expenses' => 2,
        'lease_duration' => 2
    ],
    [
        'renter_id' => 4,
        'first_name' => 'Emma',
        'last_name' => 'Rodriguez',
        'email' => 'emma.r@example.com',
        'cleanliness' => 2,
        'smoking_preference' => 1,
        'noise_tolerance' => 2,
        'pet_preference' => 2,
        'cooking_habits' => 1,
        'diet_preference' => 1,
        'social_preference' => 1,
        'guest_preference' => 2,
        'gender_preference' => 0,
        'sleep_schedule' => 1,
        'temperature_preference' => 2,
        'shared_expenses' => 1,
        'lease_duration' => 1
    ],
    [
        'renter_id' => 5,
        'first_name' => 'David',
        'last_name' => 'Kim',
        'email' => 'dkim@example.com',
        'cleanliness' => 2,
        'smoking_preference' => 1,
        'noise_tolerance' => 1,
        'pet_preference' => 1,
        'cooking_habits' => 2,
        'diet_preference' => 1,
        'social_preference' => 2,
        'guest_preference' => 1,
        'gender_preference' => 0,
        'sleep_schedule' => 2,
        'temperature_preference' => 1,
        'shared_expenses' => 2,
        'lease_duration' => 2
    ]
];

function calculateCompatibility($prefsA, $prefsB) {
    $score = 0;
    $total = 0;
    foreach ($prefsA as $key => $value) {
        if ($key === 'pref_id' || $key === 'renter_id' || $key === 'first_name' || $key === 'last_name' || $key === 'email') continue;
        $total++;
        if ($prefsA[$key] == $prefsB[$key]) $score++;
    }
    return round(($score / $total) * 100);
}

// Sort matches by compatibility score
usort($matches, function($a, $b) use ($current_prefs) {
    $scoreA = calculateCompatibility($current_prefs, $a);
    $scoreB = calculateCompatibility($current_prefs, $b);
    return $scoreB - $scoreA;
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Roommate Matches</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: white;
            margin: 0;
            padding: 0;
        }
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
        .header-link:hover {
            background-color: #f3f4f6;
        }
        .logout-button {
            color: #ef4444;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }
        .logout-button:hover {
            background-color: #fee2e2;
        }
        .match-card {
            background: white;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 20px;
        }
        .compatibility-badge {
            background-color: #ff6600;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .preference-tag {
            background-color: #f3f4f6;
            color: #374151;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            margin-right: 8px;
            margin-bottom: 8px;
            display: inline-block;
        }
        .avatar-circle {
            width: 48px;
            height: 48px;
            background-color: #e5e7eb;
            color: #4b5563;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.2rem;
            margin-right: 16px;
        }
        .contact-button {
            background-color: #e5e7eb;
            color: #4b5563;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border: none;
            cursor: pointer;
        }
        .contact-button:hover {
            background-color: #d1d5db;
        }
        .contact-button svg {
            width: 18px;
            height: 18px;
            stroke-width: 2.5;
        }
        .navbar {
            background-color: #ffffff;
            padding: 0 20px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #e0e0e0;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .navbar-left {
            display: flex;
            align-items: center;
        }
        .navbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .nav-link {
            font-size: 0.95em;
            color: #555;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
        }
        .logout-link {
            font-size: 0.95em;
            color: #555;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="min-h-screen bg-white">
        <!-- Header -->
        <header class="navbar">
            <div class="navbar-left">
                <a href="index.php" class="nav-link">Home</a>
                <a href="renter_dashboard.php" class="nav-link">Bookmarked</a>
            </div>
            <div class="search-bar-container">
                <input
                    class="search-input"
                    type="text"
                    placeholder="Enter an address, neighborhood, city, or ZIP code"
                    aria-label="Search for properties"
                >
                <button class="search-button" aria-label="Submit search">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                    </svg>
                </button>
            </div>
            <div class="navbar-right">
                <a href="roommate_preferences.php" class="nav-link">Set Preferences</a>
                <a href="index.php?logout=1" class="logout-link">Logout</a>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-5xl mx-auto px-6 py-8">
            <div class="space-y-8">
                <!-- Title Section -->
                <div>
                    <h1 class="text-3xl font-semibold text-gray-900 mb-2">Potential Roommates</h1>
                    <p class="text-gray-600">Find your perfect match based on shared preferences</p>
                </div>

                <?php if (empty($matches)): ?>
                    <div class="text-center py-12">
                        <p class="text-gray-500">No matches found yet. Save some listings to see potential roommates.</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php foreach ($matches as $match): ?>
                            <div class="match-card">
                                <div class="flex items-start mb-4">
                                    <div class="avatar-circle">
                                        <?= htmlspecialchars(substr($match['first_name'], 0, 1) . substr($match['last_name'], 0, 1)) ?>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h3 class="text-lg font-semibold text-gray-900">
                                                    <?= htmlspecialchars($match['first_name'] . ' ' . $match['last_name']) ?>
                                                </h3>
                                                <p class="text-sm text-gray-500"><?= htmlspecialchars($match['email']) ?></p>
                                            </div>
                                            <div class="compatibility-badge">
                                                <?= calculateCompatibility($current_prefs, $match) ?>% Match
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="space-y-4">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-700 mb-2">Shared Preferences:</h4>
                                        <div class="flex flex-wrap">
                                            <?php
                                            $shared_prefs = [];
                                            foreach ($current_prefs as $key => $value) {
                                                if ($key === 'pref_id' || $key === 'renter_id' || $key === 'first_name' || $key === 'last_name' || $key === 'email') continue;
                                                if ($value == $match[$key]) {
                                                    $shared_prefs[] = ucwords(str_replace('_', ' ', $key));
                                                }
                                            }
                                            foreach ($shared_prefs as $pref): ?>
                                                <span class="preference-tag"><?= htmlspecialchars($pref) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="flex justify-end">
                                        <button class="contact-button">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                            </svg>
                                            Contact
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
