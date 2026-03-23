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

// Handle image upload
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES['image'])) {
    $title = $_POST['title'];
    $uploaded_by = $admin['name'];

    $upload_dir = "../uploads/gallery/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_name = time() . "_" . basename($_FILES['image']['name']);
    $target_path = $upload_dir . $file_name;
    $db_path = "uploads/gallery/" . $file_name;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
        $stmt = $pdo->prepare("INSERT INTO gallery (title, image_path, uploaded_by) VALUES (?, ?, ?)");
        $stmt->execute([$title, $db_path, $uploaded_by]);
        $message = "✅ Image uploaded successfully!";
    } else {
        $message = "❌ Failed to upload image.";
    }
}

// Handle deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("SELECT image_path FROM gallery WHERE id = ?");
    $stmt->execute([$id]);
    $file = $stmt->fetchColumn();

    if ($file && file_exists("../" . $file)) {
        unlink("../" . $file);
    }

    $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: gallery.php");
    exit;
}

// Pagination setup
$limit = 9; // items per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search/filter
$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$query = "SELECT * FROM gallery";
$params = [];

if (!empty($search)) {
    $query .= " WHERE title LIKE ? OR uploaded_by LIKE ?";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$totalQuery = $pdo->prepare(str_replace("*", "COUNT(*)", $query));
$totalQuery->execute($params);
$total_images = $totalQuery->fetchColumn();

$query .= " ORDER BY uploaded_at DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total pages
$total_pages = ceil($total_images / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MBC Gallery - Admin Panel</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        .upload-form, .search-form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        input[type="text"], input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
        }
        button {
            padding: 10px 20px;
            background: #4b0082;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }
        .gallery-item {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            text-align: center;
            padding: 10px;
        }
        .gallery-item img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 8px;
        }
        .delete-btn {
            background: crimson;
            color: white;
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
        }
        .message {
            background: #f0f0f0;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 6px;
        }
        .pagination {
            margin-top: 20px;
            text-align: center;
        }
        .pagination a {
            display: inline-block;
            padding: 8px 14px;
            margin: 0 3px;
            background: #eee;
            color: #333;
            border-radius: 4px;
            text-decoration: none;
        }
        .pagination a.active {
            background: #4b0082;
            color: white;
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
            <li><a href="sermons.php">🎥 Upload Sermons</a></li>
            <li><a href="events.php">📅 Manage Events</a></li>
            <li><a href="gallery.php" class="active">🖼️ Upload Gallery</a></li>
            <li><a href="leadership.php">👥 Leadership</a></li>
            <li><a href="messages.php">📩 Messages</a></li>
            <li><a href="#" onclick="confirmLogout()">🚪 Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h1>🖼️ Upload & Manage Gallery</h1>

        <?php if (!empty($message)): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>

        <section class="upload-form">
            <form method="POST" enctype="multipart/form-data">
                <input type="text" name="title" placeholder="Image Title" required>
                <input type="file" name="image" accept="image/*" required>
                <button type="submit">Upload Image</button>
            </form>
        </section>

        <section class="search-form">
            <form method="GET">
                <input type="text" name="search" placeholder="Search by title or uploader" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
            </form>
        </section>

        <section>
            <h2>Uploaded Images</h2>
            <div class="gallery-grid">
                <?php if (count($images) > 0): ?>
                    <?php foreach ($images as $img): ?>
                        <div class="gallery-item">
                            <img src="../<?php echo htmlspecialchars($img['image_path']); ?>" alt="Gallery Image">
                            <h4><?php echo htmlspecialchars($img['title']); ?></h4>
                            <small>By: <?php echo htmlspecialchars($img['uploaded_by']); ?></small><br>
                            <a href="gallery.php?delete=<?php echo $img['id']; ?>" class="delete-btn" onclick="return confirm('Delete this image?');">🗑 Delete</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No images found.</p>
                <?php endif; ?>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
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
