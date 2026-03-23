<?php
session_start();
require_once "../includes/db_connect.php"; // adjust path if needed

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Get logged-in admin details
$stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    echo "Admin not found!";
    exit;
}

$is_super_admin = ($admin['role'] === 'super_admin');

// Fetch counts safely
try {
    // sermons count
    $sermons_count = $pdo->query("SELECT COUNT(*) FROM sermons")->fetchColumn();

    // gallery count
    $gallery_count = $pdo->query("SELECT COUNT(*) FROM gallery")->fetchColumn();

    // messages: attempt unread count if column exists, else total messages
    // Check if 'is_read' column exists
    $colCheck = $pdo->query("SHOW COLUMNS FROM messages LIKE 'is_read'")->fetch();
    if ($colCheck) {
        $unread_messages = (int)$pdo->query("SELECT COUNT(*) FROM messages WHERE is_read = 0")->fetchColumn();
        $total_messages = (int)$pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
    } else {
        // fallback: table exists but column not present
        $unread_messages = 0;
        $total_messages = (int)$pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
    }

    // Latest 5 events (adjust column names if your events table differs)
    $events_stmt = $pdo->query("SELECT id, event_name, event_date, venue FROM events ORDER BY event_date DESC LIMIT 5");
    $latest_events = $events_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Do not expose $e->getMessage() on production; log it instead.
    $sermons_count = $gallery_count = $unread_messages = $total_messages = 0;
    $latest_events = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MBC Admin Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        /* small inline styles for the cards */
        .cards { display: grid; grid-template-columns: repeat(auto-fit,minmax(220px,1fr)); gap: 16px; margin: 20px 0; }
        .card { background:#696969; padding:18px; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.08); }
        .card h3 { margin:0 0 8px; }
        .events-list { list-style:none; padding:0; margin:0; }
        .events-list li { padding:8px 0; border-bottom:1px solid #eee; }
    </style>
</head>
<body>
<div class="dashboard-container">
    <aside class="sidebar">
        <h2>MBC Admin</h2>
        <p>Welcome, <?php echo htmlspecialchars($admin['name']); ?></p>
        <ul>
            <?php if ($is_super_admin): ?>
                <li><a href="manage_admins.php">👑 Add / Manage Admins</a></li>
            <?php endif; ?>
            <li><a href="sermons.php">🎥 Upload Sermons</a></li>
            <li><a href="events.php">📅 Manage Events</a></li>
            <li><a href="gallery.php">🖼️ Upload Gallery</a></li>
            <li><a href="leadership.php">👥 Leadership</a></li>
            <li><a href="messages.php">📩 Messages</a></li>
            <li><a href="#" onclick="confirmLogout()">🚪 Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header>
            <h1>Dashboard Overview</h1>
            <p>Welcome to the admin control panel for Manna Bible Church.</p>
        </header>

        <section class="cards">
            <div class="card">
                <h3>Sermons</h3>
                <p style="font-size:1.8rem; font-weight:600;"><?php echo (int)$sermons_count; ?></p>
                <small>Manage sermons</small>
            </div>

            <div class="card">
                <h3>Gallery</h3>
                <p style="font-size:1.8rem; font-weight:600;"><?php echo (int)$gallery_count; ?></p>
                <small>Uploaded photos</small>
            </div>

            <div class="card">
                <h3>Messages</h3>
                <p style="font-size:1.8rem; font-weight:600;"><?php echo (int)$total_messages; ?></p>
                <small><?php echo (int)$unread_messages; ?> unread</small>
            </div>

            <div class="card">
                <h3>Latest Events</h3>
                <?php if (count($latest_events) > 0): ?>
                    <ul class="events-list">
                        <?php foreach ($latest_events as $ev): ?>
                            <li>
                                <strong><?php echo htmlspecialchars($ev['event_name']); ?></strong><br>
                                <small><?php echo date("d M Y", strtotime($ev['event_date'])); ?> — <?php echo htmlspecialchars($ev['venue']); ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No events yet.</p>
                <?php endif; ?>
            </div>
        </section>

        <footer>
            <p>Logged in as <strong><?php echo ucfirst($admin['role']); ?></strong></p>
        </footer>
    </main>
</div>
</body>
<script>
function confirmLogout() {
    if (confirm("Are you sure you want to logout?")) {
        window.location.href = "logout.php";
    }
}
</script>

</html>
