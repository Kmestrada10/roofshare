<?php
$server = "cray.cs.gettysburg.edu";
$dbase = "s25_hkm";
$user = "estrke01";
$pass = "estrke01";
$dsn = "mysql:host=$server;dbname=$dbase";

try {
    $db = new PDO($dsn, $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed");
}
?>

