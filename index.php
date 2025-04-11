<?php

session_start();


require __DIR__ . '/../config/db.php';


if (isset($_SESSION['user_id'])) {
    header('../dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    try {
        
        $tables = ['Admin', 'Realtor', 'Renter'];
        foreach ($tables as $table) {
            $stmt = $db->prepare("SELECT * FROM $table WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user[strtolower($table).'_id'];
                $_SESSION['user_type'] = strtolower($table);
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['name'];
                
                header('Location: ../dashboard.php');
                exit;
            }
        }
        $error = "Invalid email or password";
    } catch (PDOException $e) {
        $error = "System error. Please try later.";
       
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - RoofShare</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="auth-container">
        <h1>Login</h1>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
        
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>

