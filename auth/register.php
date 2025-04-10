<?php
session_start();
require '../config/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $account_type = $_POST['account_type'];
    
    // Validate type
    if (!in_array($account_type, ['renter', 'realtor'])) {
        die("Invalid account type");
    }
    
    // Check if email exists
    $stmt = $pdo->prepare("SELECT email FROM Realtor WHERE email = ? UNION SELECT email FROM Renter WHERE email = ?");
    $stmt->execute([$email, $email]);
    
    if ($stmt->fetch()) {
        die("Email already exists");
    }
    
    // Insert user
    $table = ucfirst($account_type);
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO $table (name, email, password_hash) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $password_hash]);
    
    $_SESSION['user_id'] = $pdo->lastInsertId();
    $_SESSION['user_type'] = $account_type;
    header('Location: ../index.php');
    exit;
}
?>

<form method="post">
    Name: <input type="text" name="name" required><br>
    Email: <input type="text" name="email" required><br>
    Password: <input type="password" name="password" required><br>
    Account Type:
    <select name="account_type" required>
        <option value="renter">Renter</option>
        <option value="realtor">Realtor</option>
    </select><br>
    <button type="submit">Register</button>
</form>
<p>Already have an account? <a href="login.php">Login</a