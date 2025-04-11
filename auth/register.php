<?php
session_start();
require_once("../config/db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($name && $email && $password) {
        $hashedPassword = hashPassword($password);
        $stmt = $db->prepare("INSERT INTO Renter (name, email, password_hash) VALUES (?, ?, ?)");

        try {
            $stmt->execute([$name, $email, $hashedPassword]);
            $_SESSION['user_email'] = $email;
            header("Location: ../dashboard.php");
            exit();
        } catch (PDOException $e) {
            echo "Registration failed: Email may already be used.";
        }
    } else {
        echo "Please fill out all fields.";
    }
}
?>
