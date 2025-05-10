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

// Load existing preferences
$stmt = $db->prepare("SELECT * FROM Roommate_Preference WHERE renter_id = ?");
$stmt->execute([$renter_id]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT allergy FROM Roommate_Allergies WHERE renter_id = ?");
$stmt->execute([$renter_id]);
$allergies_selected = $stmt->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prefs = [
        $_POST['cleanliness'], $_POST['smoking_preference'], $_POST['noise_tolerance'],
        $_POST['pet_preference'], $_POST['cooking_habits'], $_POST['diet_preference'],
        $_POST['social_preference'], $_POST['guest_preference'], $_POST['gender_preference'],
        $_POST['sleep_schedule'], $_POST['temperature_preference'], $_POST['shared_expenses'],
        $_POST['lease_duration']
    ];

    if ($existing) {
        $update = $db->prepare("UPDATE Roommate_Preference SET cleanliness=?, smoking_preference=?, noise_tolerance=?, pet_preference=?, cooking_habits=?, diet_preference=?, social_preference=?, guest_preference=?, gender_preference=?, sleep_schedule=?, temperature_preference=?, shared_expenses=?, lease_duration=? WHERE renter_id=?");
        $update->execute([...$prefs, $renter_id]);
    } else {
        $insert = $db->prepare("INSERT INTO Roommate_Preference (renter_id, cleanliness, smoking_preference, noise_tolerance, pet_preference, cooking_habits, diet_preference, social_preference, guest_preference, gender_preference, sleep_schedule, temperature_preference, shared_expenses, lease_duration) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert->execute([$renter_id, ...$prefs]);
    }

    $db->prepare("DELETE FROM Roommate_Allergies WHERE renter_id = ?")->execute([$renter_id]);
    foreach ($_POST['allergies'] as $allergy) {
        if (trim($allergy) !== '') {
            $db->prepare("INSERT INTO Roommate_Allergies (renter_id, allergy) VALUES (?, ?)")->execute([$renter_id, $allergy]);
        }
    }

    echo "<script>alert('Preferences saved successfully!'); window.location.href='renter_dashboard.php';</script>";
    exit();
}

function selected($value, $option) {
    return (string)$value === (string)$option ? 'selected' : '';
}
function checked($array, $value) {
    return in_array($value, $array) ? 'checked' : '';
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Roommate Preferences</title>
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
          font-size: 16px;
          font-weight: bold;
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
          background-color: #fb923c;
        }
        .custom-orange-button:hover {
          background-color: #f97316;
        }
    </style>
</head>
<body>
    <div class="min-h-screen bg-white">
        <!-- Header -->
        <header class="header">
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
            <div class="header-links">
                <a href="#" class="header-link">Manage Rentals</a>
                <a href="#" class="header-link">Sign In</a>
                <a href="#" class="header-link">Add Property</a>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-5xl mx-auto px-6 py-8" style="position: relative; z-index: 1;">
            <div class="space-y-8">
                <!-- Title Section -->
                <div>
                    <h1 class="text-3xl font-semibold text-gray-900 mb-2">Roommate Preferences</h1>
                    <p class="text-gray-600">Set your preferences to find the perfect roommate match</p>
                </div>

                <form method="POST">
                    <div class="bg-white">
                        <!-- Preferences Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <?php
                            $fields = [
                                'cleanliness' => ['Messy', 'Moderate', 'Very Clean'],
                                'smoking_preference' => ['No Preference', 'Non-Smoker', 'Smoker'],
                                'noise_tolerance' => ['Loud', 'Moderate', 'Quiet'],
                                'pet_preference' => ['No Pets', 'Okay with Pets', 'Loves Pets'],
                                'cooking_habits' => ['Rarely Cook', 'Sometimes Cook', 'Cook Often'],
                                'diet_preference' => ['No Restriction', 'Vegetarian', 'Vegan', 'Other'],
                                'social_preference' => ['Introvert', 'Ambivert', 'Extrovert'],
                                'guest_preference' => ['No Guests', 'Occasionally', 'Frequently'],
                                'gender_preference' => ['No Preference', 'Same Gender', 'Opposite Gender'],
                                'sleep_schedule' => ['Early Bird', 'Flexible', 'Night Owl'],
                                'temperature_preference' => ['Cool', 'Warm', 'No Preference'],
                                'shared_expenses' => ['Separate', 'Willing to Share', 'No Preference'],
                                'lease_duration' => ['Short-term (<6 months)', 'Medium-term (6-12 months)', 'Long-term (>12 months)']
                            ];

                            foreach ($fields as $key => $options) {
                                echo '<div>';
                                echo '<label class="block text-sm font-medium text-gray-700 mb-2">' . ucwords(str_replace('_', ' ', $key)) . '</label>';
                                echo '<select name="' . $key . '" class="w-full px-4 py-3 border rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">';
                                foreach ($options as $i => $label) {
                                    $sel = selected($existing[$key] ?? '', $i);
                                    echo "<option value='$i' $sel>$label</option>";
                                }
                                echo '</select>';
                                echo '</div>';
                            }
                            ?>
                        </div>

                        <!-- Allergies Section -->
                        <div class="pt-8">
                            <label class="block text-lg font-semibold text-gray-900 mb-4">Allergies</label>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-6 gap-y-4">
                                <?php
                                $allergy_options = ['Peanuts', 'Shellfish', 'Fish', 'Milk', 'Eggs', 'Wheat', 'Soy'];
                                foreach ($allergy_options as $allergy) {
                                    $check = checked($allergies_selected, $allergy);
                                    echo '<div class="flex items-center">';
                                    echo '<input type="checkbox" id="allergy_' . strtolower($allergy) . '" name="allergies[]" value="' . $allergy . '" ' . $check . ' class="custom-checkbox mr-3">';
                                    echo '<label for="allergy_' . strtolower($allergy) . '" class="text-sm text-gray-700">' . $allergy . '</label>';
                                    echo '</div>';
                                }
                                ?>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-10 text-right">
                            <button type="submit" class="cta-button">
                                Save Preferences
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
