<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once("configuration/db.php");
session_start();

$view = $_GET['view'] ?? 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $account_type = $_POST['account_type'] ?? '';

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

    if ($action === 'login') {
        if ($email && $password) {
            $stmt = $db->prepare("SELECT * FROM Admin WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            $type = 'Admin';
            if (!$user) {
                $stmt = $db->prepare("SELECT * FROM Realtor WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                $type = 'Realtor';
            }
            if (!$user) {
                $stmt = $db->prepare("SELECT * FROM Renter WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                $type = 'Renter';
            }

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_type'] = $type;
                $_SESSION['verification'] = $user['verification_status'] ?? '';
                header("Location: dashboard.php");
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
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 350px;
        }
        h2 {
            margin-top: 0;
            text-align: center;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        input, select {
            margin-bottom: 12px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        input[type="submit"] {
            background-color: #007BFF;
            color: white;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .switch {
            margin-top: 10px;
            text-align: center;
        }
        .error { color: red; text-align: center; }
        .success { color: green; text-align: center; }
    </style>
    <script>
        function showFields() {
            const type = document.getElementById('account_type').value;
            document.querySelectorAll('.dynamic').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.' + type).forEach(el => el.style.display = 'block');
        }
    </script>
</head>
<body onload="showFields()">
<div class="container">
    <!-- <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    <?php if (!empty($success)) echo "<p class='success'>$success</p>"; ?> -->

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

            <input type="text" name="name" placeholder="Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="text" name="phone_number" placeholder="Phone Number">

            <select name="role" class="dynamic admin" style="display:none">
                <option value="">Select Role</option>
                <option value="Administrator">Administrator</option>
                <option value="Moderator">Moderator</option>
            </select>

            <input type="date" name="dob" class="dynamic renter" style="display:none">
            <select name="gender" class="dynamic renter" style="display:none">
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
                <option value="Prefer not to say">Prefer not to say</option>
            </select>

            <input type="submit" value="Register">
        </form>
        <div class="switch">
            Already have an account? <a href="?view=login">Login</a>
        </div>
    <?php else: ?>
        <h2>Login</h2>
        <form method="POST">
            <input type="hidden" name="action" value="login">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="submit" value="Login">
        </form>
        <div class="switch">
            Not yet registered? <a href="?view=register">Create an account</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>


