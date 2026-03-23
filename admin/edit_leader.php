<?php
session_start();
require_once "../includes/db_connect.php";

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Get admin details
$stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    echo "Admin not found!";
    exit;
}

$is_super_admin = ($admin['role'] === 'super_admin');

// Validate Leader ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Invalid Leader ID!";
    exit;
}

$leader_id = $_GET['id'];

// Fetch leader data
$stmt = $pdo->prepare("SELECT * FROM leadership WHERE id = ?");
$stmt->execute([$leader_id]);
$leader = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$leader) {
    echo "Leader not found!";
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = $_POST['full_name'];
    $position = $_POST['position'];
    $start_date = $_POST['start_date'];
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
    $photo_path = $leader['photo'];

    // Handle new photo upload
    if (!empty($_FILES['photo']['name'])) {

        $target_dir = "../uploads/leadership/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $photo_name = time() . "_" . basename($_FILES["photo"]["name"]);
        $target_file = $target_dir . $photo_name;

        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            $photo_path = "uploads/leadership/" . $photo_name;

            // Delete old photo
            if (!empty($leader['photo']) && file_exists("../" . $leader['photo'])) {
                unlink("../" . $leader['photo']);
            }
        }
    }

    // Update DB
    $update = $pdo->prepare("
        UPDATE leadership 
        SET full_name=?, position=?, photo=?, start_date=?, end_date=? 
        WHERE id=?
    ");

    $update->execute([$full_name, $position, $photo_path, $start_date, $end_date, $leader_id]);

    header("Location: leadership.php?updated=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Leader - MBC Admin</title>
    <link rel="stylesheet" href="dashboard.css">

    <style>
        .edit-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            max-width: 600px;
            margin: auto;
        }
        input, select {
            width: 100%;
            padding: 8px;
            margin-bottom: 12px;
        }
        img.preview {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 6px;
            margin-bottom: 10px;
        }
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
            <li><a href="sermons.php">🎥 Sermons</a></li>
            <li><a href="events.php">📅 Events</a></li>
            <li><a href="gallery.php">🖼️ Gallery</a></li>
            <li><a href="leadership.php" class="active">👥 Leadership</a></li>
            <li><a href="messages.php">📩 Messages</a></li>
            <li><a href="#" onclick="confirmLogout()">🚪 Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">

        <h1>Edit Leader</h1>

        <div class="edit-box">
            <form method="POST" enctype="multipart/form-data">

                <label>Current Photo:</label><br>
                <?php if ($leader['photo']): ?>
                    <img src="../<?php echo $leader['photo']; ?>" class="preview">
                <?php else: ?>
                    <p>No photo</p>
                <?php endif; ?>

                <label>Upload New Photo (optional):</label>
                <input type="file" name="photo">

                <label>Full Name:</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($leader['full_name']); ?>" required>

                <label>Position:</label>
                <input type="text" name="position" value="<?php echo htmlspecialchars($leader['position']); ?>" required>

                <label>Start Date:</label>
                <input type="date" name="start_date" value="<?php echo $leader['start_date']; ?>" required>

                <label>End Date (leave blank if serving):</label>
                <input type="date" name="end_date" value="<?php echo $leader['end_date']; ?>">

                <button type="submit">Save Changes</button>
            </form>
        </div>

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
