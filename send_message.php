<?php
require_once "includes/db_connect.php"; // adjust path if needed

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);

    if (!empty($name) && !empty($email) && !empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $message]);
        header("Location: contact.html?success=1");
        exit;
    } else {
        header("Location: contact.html?error=1");
        exit;
    }
} else {
    header("Location: contact.html");
    exit;
}
?>
