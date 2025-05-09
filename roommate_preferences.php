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
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .form-container {
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 90%;
        }
        h2 {
            text-align: center;
            color: #007BFF;
            margin-bottom: 20px;
        }
        label {
            font-weight: 500;
            margin-top: 15px;
            display: block;
        }
        select, input[type='text'], input[type='checkbox'] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1rem;
        }
        input[type='checkbox'] {
            width: auto;
            margin-right: 10px;
        }
        button[type='submit'] {
            margin-top: 20px;
            padding: 12px;
            width: 100%;
            background-color: #007BFF;
            border: none;
            color: white;
            font-size: 1rem;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        button[type='submit']:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Roommate Preferences</h2>
        <form method="POST">
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
                echo "<label>" . ucwords(str_replace('_', ' ', $key)) . ":</label><select name='$key'>";
                foreach ($options as $i => $label) {
                    $sel = selected($existing[$key] ?? '', $i);
                    echo "<option value='$i' $sel>$label</option>";
                }
                echo "</select>";
            }
            ?>

            <label>Allergies:</label><br>
            <?php
            $allergy_options = ['Peanuts', 'Shellfish', 'Fish', 'Milk', 'Eggs', 'Wheat', 'Soy'];
            foreach ($allergy_options as $allergy) {
                $check = checked($allergies_selected, $allergy);
                echo "<label><input type='checkbox' name='allergies[]' value='$allergy' $check> $allergy</label><br>";
            }
            ?>

            <button type="submit">Save Preferences</button>
        </form>
    </div>
</body>
</html>
