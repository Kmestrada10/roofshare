<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Login / Register</title>
</head>
<body>
    <h2>Login</h2>
    <form action="auth/login.php" method="POST">
        <label>Email:</label>
        <input type="email" name="email" required><br>
        <label>Password:</label>
        <input type="password" name="password" required><br>
        <input type="submit" value="Login">
    </form>

    <h2>Register</h2>
    <form action="auth/register.php" method="POST">
        <label>Name:</label>
        <input type="text" name="name" required><br>
        <label>Email:</label>
        <input type="email" name="email" required><br>
        <label>Password:</label>
        <input type="password" name="password" required><br>
        <input type="submit" value="Register">
    </form>
</body>
</html>
