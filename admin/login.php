<?php
session_start();
require_once "../includes/db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Prepare query to fetch admin data
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        // Verify hashed password
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['role'] = $admin['role'];

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "No admin found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>MBC Admin Login</title>
<link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="login-container">
    <h2>Manna Bible Church Admin Login</h2>
    <?php if (!empty($error)) : ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" required placeholder="Enter your email">
        </div>

        <div class="form-group">
            <label>Password:</label>
            <input type="password" name="password" required placeholder="Enter your password">
        </div>

        <button type="submit">Login</button>
    </form>
</div>
</body>
</html>
