<?php
session_start();
require_once "../includes/db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$admin) { echo "Admin not found!"; exit; }

$is_super_admin = ($admin['role'] === 'super_admin');

// Mark single message read (when ?view=ID)
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    $viewId = (int)$_GET['view'];
    // update is_read if column exists
    $colCheck = $pdo->query("SHOW COLUMNS FROM messages LIKE 'is_read'")->fetch();
    if ($colCheck) {
        $upd = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = ?");
        $upd->execute([$viewId]);
    }
    // fetch that message
    $mstmt = $pdo->prepare("SELECT * FROM messages WHERE id = ?");
    $mstmt->execute([$viewId]);
    $single = $mstmt->fetch(PDO::FETCH_ASSOC);
}

// handle deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $dstmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
    $dstmt->execute([$delId]);
    header("Location: messages.php");
    exit;
}

// Fetch messages
$messages = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MBC Admin - Messages</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        .table { width:100%; border-collapse: collapse; background:#B589D6; border-radius:8px; overflow:hidden; }
        .table th, .table td { padding:12px; border-bottom:1px solid #eee; text-align:left; }
        .unread { font-weight:700; background:#696969; }
        .view-box { background:rgba(255, 255, 255, 0.1); padding:16px; border-radius:8px; margin-bottom:16px; }
        .actions a { margin-right:8px; text-decoration:none; color:#b00; }
    </style>
</head>
<body>
<div class="dashboard-container">
    <aside class="sidebar">
        <h2>MBC Admin</h2>
        <p>Welcome, <?php echo htmlspecialchars($admin['name']); ?></p>
        <ul>
            <?php if ($is_super_admin): ?><li><a href="manage_admins.php">👑 Add / Manage Admins</a></li><?php endif; ?>
            <li><a href="dashboard.php">🏠 Dashboard</a></li>
            <li><a href="sermons.php">🎥 Upload Sermons</a></li>
            <li><a href="events.php">📅 Manage Events</a></li>
            <li><a href="gallery.php">🖼️ Upload Gallery</a></li>
            <li><a href="leadership.php">👥 Leadership</a></li>
            <li><a href="messages.php" class="active">📩 Messages</a></li>
            <li><a href="#" onclick="confirmLogout()">🚪 Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h1>📩 Received Messages</h1>

        <?php if (isset($single) && $single): ?>
            <div class="view-box">
                <h3><?php echo htmlspecialchars($single['name']); ?> <small>&lt;<?php echo htmlspecialchars($single['email']); ?>&gt;</small></h3>
                <p><?php echo nl2br(htmlspecialchars($single['message'])); ?></p>
                <p><small><?php echo date("d M Y, h:i A", strtotime($single['created_at'])); ?></small></p>
                <p class="actions">
                    <a href="messages.php?delete=<?php echo $single['id']; ?>" onclick="return confirm('Delete this message?')">🗑 Delete</a>
                    <a href="messages.php">⬅ Back to list</a>
                </p>
            </div>
        <?php endif; ?>

        <table class="table">
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Message</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
            <?php foreach ($messages as $index => $msg): 
                $isUnread = isset($msg['is_read']) && ((int)$msg['is_read'] === 0);
            ?>
                <tr class="<?php echo $isUnread ? 'unread' : ''; ?>">
                    <td><?php echo $index + 1; ?></td>
                    <td><?php echo htmlspecialchars($msg['name']); ?></td>
                    <td><?php echo htmlspecialchars($msg['email']); ?></td>
                    <td><?php echo htmlspecialchars(mb_strimwidth($msg['message'], 0, 120, '...')); ?></td>
                    <td><?php echo date("d M Y, h:i A", strtotime($msg['created_at'])); ?></td>
                    <td>
                        <a href="messages.php?view=<?php echo $msg['id']; ?>">View</a> |
                        <a href="messages.php?delete=<?php echo $msg['id']; ?>" onclick="return confirm('Delete this message?')">Delete</a>
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
