<?php
session_start();
require_once "../includes/db_connect.php";

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Validate event ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: events.php?error=invalid_id");
    exit;
}

$event_id = (int) $_GET['id'];

// Fetch event to delete (to remove image too)
$stmt = $pdo->prepare("SELECT poster FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    header("Location: events.php?error=not_found");
    exit;
}

// Delete poster file if exists
if (!empty($event['poster'])) {
    $file_path = "../" . $event['poster'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }
}

// Delete event from database
$stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
$stmt->execute([$event_id]);

header("Location: events.php?success=deleted");
exit;
?>
