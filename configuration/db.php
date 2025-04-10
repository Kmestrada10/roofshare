<?php
$pdo = new PDO('mysql:host=localhost;dbname=s25_hkm', 'username', 'password');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ATTR_ERRMODE_EXCEPTION);
?>