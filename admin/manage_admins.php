<?php
session_start();
require_once "../includes/db_connect.php";

// Check if logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch logged-in admin
$stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    echo "Admin not found!";
    exit;
}

// Allow only Super Admin
if ($admin['role'] !== 'super_admin') {
    echo "<script>alert('Access denied! Only Super Admin can manage admins.'); window.location.href='dashboard.php';</script>";
    exit;
}

// Handle add admin form
if (isset($_POST['add_admin'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];

    $check = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        $msg = "❌ Admin with this email already exists!";
    } else {
        $insert = $pdo->prepare("INSERT INTO admins (name, email, password, role) VALUES (?, ?, ?, ?)");
        $insert->execute([$name, $email, $password, $role]);
        $msg = "✅ Admin added successfully!";
    }
}

// Handle delete admin
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    if ($id != $_SESSION['admin_id']) { // prevent deleting yourself
        $delete = $pdo->prepare("DELETE FROM admins WHERE id = ?");
        $delete->execute([$id]);
        $msg = "🗑️ Admin deleted successfully!";
    } else {
        $msg = "⚠️ You cannot delete your own account!";
    }
}

// Fetch all admins
$admins = $pdo->query("SELECT * FROM admins ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Admins - MBC Admin</title>
  <link rel="stylesheet" href="dashboard.css">
  <style>
    .content-box {
      background: rgba(255,255,255,0.1);
      padding: 20px;
      border-radius: 10px;
      backdrop-filter: blur(10px);
    }
    form input, form select {
      padding: 10px;
      width: 100%;
      margin-bottom: 10px;
      border: none;
      border-radius: 5px;
    }
    form button {
      background: #ff007f;
      color: white;
      border: none;
      padding: 10px 15px;
      border-radius: 5px;
      cursor: pointer;
    }
    form button:hover { background: #ff4fbf; }
    table {
      width: 100%;
      border-collapse: collapse;
      background: rgba(255,255,255,0.05);
      margin-top: 20px;
      border-radius: 10px;
      overflow: hidden;
    }
    th, td {
      padding: 12px;
      border-bottom: 1px solid rgba(255,255,255,0.2);
      color: white;
    }
    th { background: rgba(0,0,0,0.6); }
    .btn-del {
      background: crimson;
      color: white;
      padding: 6px 12px;
      border-radius: 5px;
      text-decoration: none;
    }
  </style>
</head>
<body>
<div class="dashboard-container">
  <aside class="sidebar">
    <h2>MBC Admin</h2>
    <p>Welcome, <?php echo htmlspecialchars($admin['name']); ?></p>
    <ul>
      <li><a href="dashboard.php">🏠 Dashboard</a></li>
      <li><a href="manage_admins.php" class="active">👑 Manage Admins</a></li>
      <li><a href="sermons.php">🎥 Sermons</a></li>
      <li><a href="events.php">📅 Events</a></li>
      <li><a href="gallery.php">🖼️ Gallery</a></li>
      <li><a href="leadership.php">👥 Leadership</a></li>
      <li><a href="messages.php">📩 Messages</a></li>
      <li><a href="#" onclick="confirmLogout()">🚪 Logout</a></li>
    </ul>
  </aside>

  <main class="main-content">
    <header>
      <h1>Manage Admins</h1>
      <p>Only Super Admins can add or remove other admins.</p>
    </header>

    <div class="content-box">
      <?php if (isset($msg)) echo "<p style='color:#ff80b0; font-weight:bold;'>$msg</p>"; ?>

      <h3>Add New Admin</h3>
      <form method="POST">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <select name="role" required>
          <option value="regular_admin">Regular Admin</option>
          <option value="super_admin">Super Admin</option>
        </select>
        <button type="submit" name="add_admin">Add Admin</button>
      </form>
    </div>

    <h3 style="margin-top:30px;">Existing Admins</h3>
    <table>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Action</th>
      </tr>
      <?php foreach ($admins as $a): ?>
      <tr>
        <td><?php echo $a['id']; ?></td>
        <td><?php echo htmlspecialchars($a['name']); ?></td>
        <td><?php echo htmlspecialchars($a['email']); ?></td>
        <td><?php echo ucfirst(str_replace('_',' ',$a['role'])); ?></td>
        <td>
          <?php if ($a['id'] != $_SESSION['admin_id']): ?>
            <a href="?delete=<?php echo $a['id']; ?>" onclick="return confirm('Are you sure you want to delete this admin?');" class="btn-del">Delete</a>
          <?php else: ?>
            <em>Self</em>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  </main>
</div>
<script>
function confirmLogout() {
    if (confirm("Are you sure you want to logout?")) {
        window.location.href = "logout.php";
    }
}
</script>
</body>
</html>
