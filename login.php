<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once("config/db.php");
session_start();

$view = $_GET['view'] ?? 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $account_type = $_POST['account_type'] ?? '';

    // ========== REGISTER ==========
    if ($action === 'register') {
        $name = $_POST['name'] ?? '';
        $phone = $_POST['phone_number'] ?? '';
        $dob = $_POST['dob'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $role = $_POST['role'] ?? '';

        if ($name && $email && $password && $account_type) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            try {
                if ($account_type === 'admin' && $role) {
                    $stmt = $db->prepare("INSERT INTO Admin (name, email, phone_number, password_hash, role) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $email, $phone, $hashedPassword, $role]);
                } elseif ($account_type === 'realtor') {
                    $stmt = $db->prepare("INSERT INTO Realtor (name, email, phone_number, password_hash, verification_status) VALUES (?, ?, ?, ?, 'Pending')");
                    $stmt->execute([$name, $email, $phone, $hashedPassword]);
                } elseif ($account_type === 'renter' && $dob && $gender) {
                    $stmt = $db->prepare("INSERT INTO Renter (name, email, phone_number, password_hash, dob, gender) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $email, $phone, $hashedPassword, $dob, $gender]);
                } else {
                    throw new Exception("Missing required fields for account type");
                }

                $success = "Registration successful! You can now log in.";
                $view = 'login';
            } catch (Exception $e) {
                $error = "Registration failed: " . $e->getMessage();
            }
        } else {
            $error = "Please complete all fields.";
        }
    }

    // ========== LOGIN ==========
    if ($action === 'login') {
        if ($email && $password) {
            $user = null;
            $type = null;

            $stmt = $db->prepare("SELECT * FROM Admin WHERE email = ?");
            $stmt->execute([$email]);
            if ($user = $stmt->fetch()) $type = 'admin';

            if (!$user) {
                $stmt = $db->prepare("SELECT * FROM Realtor WHERE email = ?");
                $stmt->execute([$email]);
                if ($user = $stmt->fetch()) $type = 'realtor';
            }

            if (!$user) {
                $stmt = $db->prepare("SELECT * FROM Renter WHERE email = ?");
                $stmt->execute([$email]);
                if ($user = $stmt->fetch()) $type = 'renter';
            }

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_type'] = strtolower($type);
                $_SESSION['user_id'] = $user[strtolower($type) . '_id'];
                $_SESSION['verification'] = $user['verification_status'] ?? '';

                // Redirect directly to the corresponding dashboard
                if (strtolower($type) === 'admin') {
                    header("Location: admin_dashboard.php");
                } elseif (strtolower($type) === 'realtor') {
                    header("Location: realtor_dashboard.php");
                } elseif (strtolower($type) === 'renter') {
                    header("Location: renter_dashboard.php");
                } else {
                    $error = "Unknown user type.";
                }
                exit();
            } else {
                $error = "Incorrect email or password.";
            }
        } else {
            $error = "Enter both email and password.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>RoofShare | Login / Register</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Fonts and CSS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/auth_layout.css"> <!-- Changed to new stylesheet -->
    
    <script>
        function showFields() {
            const type = document.getElementById('account_type');
            if (!type) return;
            const value = type.value;
            document.querySelectorAll('.dynamic').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.' + value).forEach(el => el.style.display = 'block');
        }
    </script>
</head>

<body onload="showFields()">
<div class="auth-wrapper">
    <div class="auth-form-section">
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <?php if (!empty($success)) echo "<p class='success'>$success</p>"; ?>

        <?php if ($view === 'register'): ?>
            <h2>Register</h2>
            <form method="POST">
                <input type="hidden" name="action" value="register">
                
                <label for="account_type">Account Type</label>
                <select name="account_type" id="account_type" onchange="showFields()" required>
                    <option value="">Select Account Type</option>
                    <option value="admin">Admin</option>
                    <option value="realtor">Realtor</option>
                    <option value="renter">Renter</option>
                </select>

                <label for="name">Name</label>
                <input type="text" name="name" placeholder="Full Name" required>
                
                <label for="email">Email</label>
                <input type="email" name="email" placeholder="your@email.com" required>
                
                <label for="password">Password</label>
                <input type="password" name="password" placeholder="Password" required>
                
                <label for="phone_number">Phone Number (Optional)</label>
                <input type="text" name="phone_number" placeholder="Phone Number">

                <div class="dynamic admin" style="display:none">
                    <label for="role">Role</label>
                    <select name="role" class="admin">
                        <option value="">Select Role</option>
                        <option value="Administrator">Administrator</option>
                        <option value="Moderator">Moderator</option>
                    </select>
                </div>

                <div class="dynamic renter" style="display:none">
                    <label for="dob">Date of Birth</label>
                    <input type="date" name="dob" class="renter">
                </div>
                <div class="dynamic renter" style="display:none">
                    <label for="gender">Gender</label>
                    <select name="gender" class="renter">
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                        <option value="Prefer not to say">Prefer not to say</option>
                    </select>
                </div>

                <input type="submit" value="Register">
            </form>
            <div class="switch">
                Already have an account? <a href="?view=login">Login</a>
            </div>
        <?php else: ?>
            <h2>Login</h2>
            <form method="POST">
                <input type="hidden" name="action" value="login">
                <label for="email">Email</label>
                <input type="email" name="email" placeholder="your@email.com" required>
                <label for="password">Password</label>
                <input type="password" name="password" placeholder="Password" required>
                <input type="submit" value="Login">
            </form>
            <div class="switch">
                Not yet registered? <a href="?view=register">Create an account</a>
            </div>
        <?php endif; ?>
    </div>
    <div class="auth-image-section">
        <!-- Background image applied via CSS -->
    </div>
</div>
</body>
</html>
