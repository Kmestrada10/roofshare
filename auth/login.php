<?php
session_start();
require '../configuration/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Check all user tables
    $tables = ['Admin', 'Realtor', 'Renter'];
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SELECT * FROM $table WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user[strtolower($table).'_id'];
            $_SESSION['user_type'] = strtolower($table);
            header('Location: ../index.php');
            exit;
        }
    }
    echo "Invalid login";
}
?>

<form method="post">
    Email: <input type="text" name="email" required><br>
    Password: <input type="password" name="password" required><br>
    <button type="submit">Login</button>
</form>
<p>No account? <a href="register.php">Register</a></p>