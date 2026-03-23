<?php
session_start();
require_once "../includes/db_connect.php";

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Validate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Leader ID.");
}

$leader_id = $_GET['id'];

// Fetch leader to remove photo as well
$stmt = $pdo->prepare("SELECT photo FROM leadership WHERE id = ?");
$stmt->execute([$leader_id]);
$leader = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$leader) {
    die("Leader not found.");
}

// Delete photo from server
if (!empty($leader['photo'])) {
    $photo_path = "../" . $leader['photo'];
    if (file_exists($photo_path)) {
        unlink($photo_path);
    }
}

// Delete database record
$stmt = $pdo->prepare("DELETE FROM leadership WHERE id = ?");
$stmt->execute([$leader_id]);

// Redirect back
header("Location: leadership.php?msg=Leader+Deleted+Successfully");
exit;
