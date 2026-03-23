<?php
$host = "localhost";
$user = "root";   // default user for XAMPP
$pass = "";       // leave blank unless you've set a password
$dbname = "mbc_db";  // your database name

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
