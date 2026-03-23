<?php
session_start();
require_once "../includes/db_connect.php";

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

// Handle new event submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $event_name = $_POST['event_name'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $location = $_POST['location'];
    $venue = $_POST['venue'];
    $entry_type = $_POST['entry_type']; // 'free' or 'registration'
    $description = $_POST['description'];

    // File upload
    $poster_path = null;
    if (!empty($_FILES['poster']['name'])) {
        $target_dir = "../uploads/events/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $poster_name = time() . "_" . basename($_FILES["poster"]["name"]);
        $target_file = $target_dir . $poster_name;

        if (move_uploaded_file($_FILES["poster"]["tmp_name"], $target_file)) {
            $poster_path = "uploads/events/" . $poster_name;
        }
    }

    // Insert into DB
    $stmt = $pdo->prepare("INSERT INTO events (event_name, event_date, event_time, location, venue, entry_type, description, poster) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$event_name, $event_date, $event_time, $location, $venue, $entry_type, $description, $poster_path]);
}

// Fetch events with pagination
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$total_events = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
$total_pages = ceil($total_events / $limit);

$stmt = $pdo->prepare("SELECT * FROM events ORDER BY event_date DESC LIMIT $limit OFFSET $offset");
$stmt->execute();
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Events - MBC Admin</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        .form-section { background: white; padding: 20px; border-radius: 10px; }
        .form-section input, textarea, select { width: 100%; margin-bottom: 10px; padding: 8px; }
        .events-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .events-table th, .events-table td { padding: 10px; border-bottom: 1px solid #ccc; text-align: left; }
        .pagination { text-align: center; margin-top: 20px; }
        .pagination a { padding: 6px 12px; background: #4b0082; color: white; border-radius: 4px; text-decoration: none; margin: 0 3px; }
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
            <li><a href="dashboard.php">🏠 Dashboard</a></li>
            <li><a href="sermons.php">🎥 Upload Sermons</a></li>
            <li><a href="events.php" class="active">📅 Manage Events</a></li>
            <li><a href="gallery.php">🖼️ Upload Gallery</a></li>
            <li><a href="leadership.php">👥 Leadership</a></li>
            <li><a href="messages.php">📩 Messages</a></li>
            <li><a href="#" onclick="confirmLogout()">🚪 Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h1>📅 Manage Events</h1>

        <section class="form-section">
            <h2>Add New Event</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="text" name="event_name" placeholder="Event Name" required>
                <input type="date" name="event_date" required>
                <input type="time" name="event_time" required>
                <input type="text" name="location" placeholder="Location" required>
                <input type="text" name="venue" placeholder="Venue" required>
                <select name="entry_type" required>
                    <option value="">Select Entry Type</option>
                    <option value="free">Free Entry</option>
                    <option value="registration">Requires Registration</option>
                </select>
                <textarea name="description" placeholder="Event Description..." rows="4" required></textarea>
                <label>Upload Poster:</label>
                <input type="file" name="poster" accept="image/*">
                <button type="submit">Add Event</button>
            </form>
        </section>

        <section>
            <h2>Existing Events</h2>
            <table class="events-table">
                <tr>
                    <th>Poster</th>
                    <th>Name</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Location</th>
                    <th>Venue</th>
                    <th>Type</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($events as $event): ?>
                    <tr>
                        <td><?php if ($event['poster']): ?><img src="../<?php echo $event['poster']; ?>" width="60"><?php endif; ?></td>
                        <td><?php echo htmlspecialchars($event['event_name']); ?></td>
                        <td><?php echo htmlspecialchars($event['event_date']); ?></td>
                        <td><?php echo htmlspecialchars($event['event_time']); ?></td>
                        <td><?php echo htmlspecialchars($event['location']); ?></td>
                        <td><?php echo htmlspecialchars($event['venue']); ?></td>
                        <td><?php echo ucfirst($event['entry_type']); ?></td>
                        <td><a href="edit_event.php?id=<?php echo $event['id']; ?>">✏️ Edit</a> | 
                            <a href="delete_event.php?id=<?php echo $event['id']; ?>" onclick="return confirm('Delete this event?');">🗑️ Delete</a></td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            </div>
        </section>
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
