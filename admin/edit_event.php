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

// Validate event ID
if (!isset($_GET['id'])) {
    die("Event ID missing!");
}

$event_id = $_GET['id'];

// Fetch event info
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    die("Event not found.");
}

// Handle update form submit
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $event_name = $_POST['event_name'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $location = $_POST['location'];
    $venue = $_POST['venue'];
    $entry_type = $_POST['entry_type'];
    $description = $_POST['description'];

    // Handle poster update
    $poster_path = $event['poster']; // Keep old poster by default

    if (!empty($_FILES['poster']['name'])) {
        $target_dir = "../uploads/events/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $poster_name = time() . "_" . basename($_FILES["poster"]["name"]);
        $target_file = $target_dir . $poster_name;

        if (move_uploaded_file($_FILES["poster"]["tmp_name"], $target_file)) {
            $poster_path = "uploads/events/" . $poster_name;

            // Delete old poster if exists
            if (!empty($event['poster']) && file_exists("../" . $event['poster'])) {
                unlink("../" . $event['poster']);
            }
        }
    }

    // Update event in DB
    $stmt = $pdo->prepare("UPDATE events SET 
            event_name = ?, 
            event_date = ?, 
            event_time = ?, 
            location = ?, 
            venue = ?, 
            entry_type = ?, 
            description = ?, 
            poster = ?
        WHERE id = ?");

    $stmt->execute([
        $event_name, $event_date, $event_time, 
        $location, $venue, $entry_type, 
        $description, $poster_path, $event_id
    ]);

    header("Location: events.php?updated=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Event - MBC Admin</title>
    <link rel="stylesheet" href="dashboard.css">

    <style>
        .form-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
        }
        input, textarea, select {
            width: 100%;
            padding: 8px;
            margin-bottom: 12px;
        }
        button {
            background: #4b0082;
            color: white;
            padding: 10px 14px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        img.preview {
            width: 150px;
            border-radius: 6px;
            margin-top: 10px;
        }
    </style>
</head>

<body>
<div class="dashboard-container">

    <!-- SIDEBAR -->
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

    <!-- MAIN -->
    <main class="main-content">
        <h1>✏️ Edit Event</h1>

        <section class="form-section">
            <form method="POST" enctype="multipart/form-data">

                <label>Event Name</label>
                <input type="text" name="event_name" value="<?php echo htmlspecialchars($event['event_name']); ?>" required>

                <label>Date</label>
                <input type="date" name="event_date" value="<?php echo $event['event_date']; ?>" required>

                <label>Time</label>
                <input type="time" name="event_time" value="<?php echo $event['event_time']; ?>" required>

                <label>Location</label>
                <input type="text" name="location" value="<?php echo htmlspecialchars($event['location']); ?>" required>

                <label>Venue</label>
                <input type="text" name="venue" value="<?php echo htmlspecialchars($event['venue']); ?>" required>

                <label>Entry Type</label>
                <select name="entry_type" required>
                    <option value="free" <?php if ($event['entry_type'] === "free") echo "selected"; ?>>Free Entry</option>
                    <option value="registration" <?php if ($event['entry_type'] === "registration") echo "selected"; ?>>Requires Registration</option>
                </select>

                <label>Description</label>
                <textarea name="description" rows="5" required><?php echo htmlspecialchars($event['description']); ?></textarea>

                <label>Current Poster</label><br>
                <?php if ($event['poster']): ?>
                    <img src="../<?php echo $event['poster']; ?>" class="preview">
                <?php else: ?>
                    <p>No poster uploaded</p>
                <?php endif; ?>

                <br><br>
                <label>Upload New Poster (optional)</label>
                <input type="file" name="poster" accept="image/*">

                <button type="submit">Update Event</button>
            </form>
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
