<?php
session_start();
require_once "../includes/db_connect.php";

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch logged-in admin
$stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$admin) die("Admin not found!");

$is_super_admin = ($admin['role'] === 'super_admin');

// ===== DELETE SERMON =====
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    // Remove files if they exist
    $fileStmt = $pdo->prepare("SELECT audio_path, notes_file FROM sermons WHERE id = ?");
    $fileStmt->execute([$id]);
    $file = $fileStmt->fetch(PDO::FETCH_ASSOC);
    if ($file) {
        if (!empty($file['audio_path']) && file_exists("../" . $file['audio_path'])) unlink("../" . $file['audio_path']);
        if (!empty($file['notes_file']) && file_exists("../" . $file['notes_file'])) unlink("../" . $file['notes_file']);
    }

    $deleteStmt = $pdo->prepare("DELETE FROM sermons WHERE id = ?");
    $deleteStmt->execute([$id]);
    header("Location: sermons.php?msg=deleted");
    exit;
}

// ===== EDIT SERMON =====
if (isset($_POST['update_sermon'])) {
    $id = $_POST['id'];
    $preacher_name = $_POST['preacher_name'];
    $sermon_title = $_POST['sermon_title'];
    $book_read = $_POST['book_read'];
    $theme = $_POST['theme'];
    $sermon_date = $_POST['sermon_date'];
    $video_link = $_POST['video_link'];

    // Optional file updates
    $audio_path = $_POST['existing_audio'];
    $notes_file = $_POST['existing_notes'];

    if (!empty($_FILES['audio_path']['name'])) {
        $audio_dir = "../uploads/sermons/audio/";
        if (!is_dir($audio_dir)) mkdir($audio_dir, 0777, true);
        $audio_file = $audio_dir . basename($_FILES["audio_path"]["name"]);
        move_uploaded_file($_FILES["audio_path"]["tmp_name"], $audio_file);
        $audio_path = "uploads/sermons/audio/" . basename($_FILES["audio_path"]["name"]);
    }

    if (!empty($_FILES['notes_file']['name'])) {
        $notes_dir = "../uploads/sermons/notes/";
        if (!is_dir($notes_dir)) mkdir($notes_dir, 0777, true);
        $notes_file_path = $notes_dir . basename($_FILES["notes_file"]["name"]);
        move_uploaded_file($_FILES["notes_file"]["tmp_name"], $notes_file_path);
        $notes_file = "uploads/sermons/notes/" . basename($_FILES["notes_file"]["name"]);
    }

    $stmt = $pdo->prepare("UPDATE sermons SET preacher_name=?, sermon_title=?, book_read=?, theme=?, sermon_date=?, audio_path=?, video_link=?, notes_file=? WHERE id=?");
    $stmt->execute([$preacher_name, $sermon_title, $book_read, $theme, $sermon_date, $audio_path, $video_link, $notes_file, $id]);
    $success = "Sermon updated successfully!";
}

// ===== ADD NEW SERMON =====
if (isset($_POST['upload_sermon'])) {
    $preacher_name = $_POST['preacher_name'];
    $sermon_title = $_POST['sermon_title'];
    $book_read = $_POST['book_read'];
    $theme = $_POST['theme'];
    $sermon_date = $_POST['sermon_date'];
    $video_link = $_POST['video_link'];

    $audio_path = null;
    $notes_file = null;

    if (!empty($_FILES['audio_path']['name'])) {
        $audio_dir = "../uploads/sermons/audio/";
        if (!is_dir($audio_dir)) mkdir($audio_dir, 0777, true);
        $audio_file = $audio_dir . basename($_FILES["audio_path"]["name"]);
        move_uploaded_file($_FILES["audio_path"]["tmp_name"], $audio_file);
        $audio_path = "uploads/sermons/audio/" . basename($_FILES["audio_path"]["name"]);
    }

    if (!empty($_FILES['notes_file']['name'])) {
        $notes_dir = "../uploads/sermons/notes/";
        if (!is_dir($notes_dir)) mkdir($notes_dir, 0777, true);
        $notes_file_path = $notes_dir . basename($_FILES["notes_file"]["name"]);
        move_uploaded_file($_FILES["notes_file"]["tmp_name"], $notes_file_path);
        $notes_file = "uploads/sermons/notes/" . basename($_FILES["notes_file"]["name"]);
    }

    $stmt = $pdo->prepare("INSERT INTO sermons (preacher_name, sermon_title, book_read, theme, sermon_date, audio_path, video_link, notes_file)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$preacher_name, $sermon_title, $book_read, $theme, $sermon_date, $audio_path, $video_link, $notes_file]);
    $success = "Sermon uploaded successfully!";
}

// ===== PAGINATION & SEARCH =====
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$query = "SELECT * FROM sermons WHERE sermon_title LIKE :search OR preacher_name LIKE :search ORDER BY sermon_date DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($query);
$stmt->bindValue(':search', "%$search%");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$sermons = $stmt->fetchAll(PDO::FETCH_ASSOC);

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM sermons WHERE sermon_title LIKE :search OR preacher_name LIKE :search");
$count_stmt->bindValue(':search', "%$search%");
$count_stmt->execute();
$total_pages = ceil($count_stmt->fetchColumn() / $limit);

// Fetch sermon for edit
$edit_sermon = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM sermons WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_sermon = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Sermons - MBC Admin</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        input, textarea { width: 100%; padding: 10px; margin: 6px 0; border-radius: 6px; border: 1px solid #ccc; }
        button { background: #0066cc; color: white; padding: 8px 14px; border: none; border-radius: 6px; cursor: pointer; }
        button:hover { background: #004999; }
        table { width: 100%; border-collapse: collapse; background: #3d1663ff; color: white; }
        th, td { padding: 10px; border-bottom: 1px solid #ccc; }
        th { background: #0066cc; }
        .actions a { color: yellow; margin-right: 10px; text-decoration: none; }
        .actions a.delete { color: red; }
    </style>
</head>
<body>
<div class="dashboard-container">
    <aside class="sidebar">
        <h2>MBC Admin</h2>
        <p>Welcome, <?= htmlspecialchars($admin['name']); ?></p>
        <ul>
            <?php if ($is_super_admin): ?><li><a href="manage_admins.php">👑 Add / Manage Admins</a></li><?php endif; ?>
            <li><a href="dashboard.php">🏠 Dashboard</a></li>
            <li><a href="sermons.php" class="active">🎥 Upload Sermons</a></li>
            <li><a href="events.php">📅 Manage Events</a></li>
            <li><a href="gallery.php">🖼️ Upload Gallery</a></li>
            <li><a href="leadership.php">👥 Leadership</a></li>
            <li><a href="messages.php">📩 Messages</a></li>
            <li><a href="#" onclick="confirmLogout()">🚪 Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h2>🎙️ Manage Sermons</h2>
        <?php if (!empty($success)) echo "<p style='color:lime;'>$success</p>"; ?>

        <!-- ===== Upload/Edit Sermon Form ===== -->
        <div class="form-section">
            <h3><?= $edit_sermon ? "✏️ Edit Sermon" : "📤 Upload New Sermon" ?></h3>
            <form method="POST" enctype="multipart/form-data">
                <?php if ($edit_sermon): ?>
                    <input type="hidden" name="id" value="<?= $edit_sermon['id'] ?>">
                    <input type="hidden" name="existing_audio" value="<?= $edit_sermon['audio_path'] ?>">
                    <input type="hidden" name="existing_notes" value="<?= $edit_sermon['notes_file'] ?>">
                <?php endif; ?>

                <label>Preacher's Name:</label>
                <input type="text" name="preacher_name" required value="<?= $edit_sermon['preacher_name'] ?? '' ?>">

                <label>Sermon Title/Theme:</label>
                <input type="text" name="sermon_title" required value="<?= $edit_sermon['sermon_title'] ?? '' ?>">

                <label>Book Read:</label>
                <input type="text" name="book_read" value="<?= $edit_sermon['book_read'] ?? '' ?>">

                <label>Theme:</label>
                <input type="text" name="theme" value="<?= $edit_sermon['theme'] ?? '' ?>">

                <label>Sermon Date:</label>
                <input type="date" name="sermon_date" required value="<?= $edit_sermon['sermon_date'] ?? '' ?>">

                <label>Video Link (YouTube/Facebook):</label>
                <input type="url" name="video_link" value="<?= $edit_sermon['video_link'] ?? '' ?>">

                <label>Audio Upload:</label>
                <input type="file" name="audio_path" accept="audio/*">

                <label>Notes File (PDF/DOC):</label>
                <input type="file" name="notes_file" accept=".pdf,.doc,.docx">

                <button type="submit" name="<?= $edit_sermon ? 'update_sermon' : 'upload_sermon' ?>">
                    <?= $edit_sermon ? 'Update Sermon' : 'Upload Sermon' ?>
                </button>
            </form>
        </div>

        <!-- ===== Search ===== -->
        <form method="GET">
            <input type="text" name="search" placeholder="Search by preacher or title" value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
        </form>

        <!-- ===== Sermons Table ===== -->
        <h3>📚 All Sermons</h3>
        <table>
            <tr>
                <th>Preacher</th><th>Title</th><th>Book</th><th>Date</th>
                <th>Audio</th><th>Video</th><th>Notes</th><th>Actions</th>
            </tr>
            <?php foreach ($sermons as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['preacher_name']) ?></td>
                    <td><?= htmlspecialchars($s['sermon_title']) ?></td>
                    <td><?= htmlspecialchars($s['book_read']) ?></td>
                    <td><?= htmlspecialchars($s['sermon_date']) ?></td>
                    <td><?php if ($s['audio_path']): ?><a href="../<?= $s['audio_path'] ?>" target="_blank">🎧 Listen</a><?php endif; ?></td>
                    <td><?php if ($s['video_link']): ?><a href="<?= $s['video_link'] ?>" target="_blank">▶ Watch</a><?php endif; ?></td>
                    <td><?php if ($s['notes_file']): ?><a href="../<?= $s['notes_file'] ?>" download>📄 Download</a><?php endif; ?></td>
                    <td class="actions">
                        <a href="sermons.php?edit=<?= $s['id'] ?>">✏️ Edit</a>
                        <a href="sermons.php?delete=<?= $s['id'] ?>" class="delete" onclick="return confirm('Are you sure you want to delete this sermon?');">🗑️ Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <!-- ===== Pagination ===== -->
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
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
