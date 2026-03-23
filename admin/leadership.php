<?php
session_start();
require_once "../includes/db_connect.php";

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch admin info
$stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    echo "Admin not found!";
    exit;
}

$is_super_admin = ($admin['role'] === 'super_admin');

// Handle New Leader Submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = $_POST['full_name'];
    $position = $_POST['position'];
    $start_date = $_POST['start_date'];
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;

    // Upload Photo
    $photo_path = null;
    if (!empty($_FILES['photo']['name'])) {
        $target_dir = "../uploads/leadership/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $photo_name = time() . "_" . basename($_FILES["photo"]["name"]);
        $target_file = $target_dir . $photo_name;

        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            $photo_path = "uploads/leadership/" . $photo_name;
        }
    }

    // Insert into DB
    $stmt = $pdo->prepare("
        INSERT INTO leadership (full_name, position, photo, start_date, end_date)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->execute([$full_name, $position, $photo_path, $start_date, $end_date]);
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$total = $pdo->query("SELECT COUNT(*) FROM leadership")->fetchColumn();
$total_pages = ceil($total / $limit);

// Fetch Leaders
$stmt = $pdo->prepare("SELECT * FROM leadership ORDER BY start_date DESC LIMIT $limit OFFSET $offset");
$stmt->execute();
$leaders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to calculate duration
function calculateDuration($start, $end) {
    $start = new DateTime($start);
    $end = $end ? new DateTime($end) : new DateTime();

    $interval = $start->diff($end);

    if ($interval->y > 0) {
        return $interval->y . " year(s) " . $interval->m . " month(s)";
    } else {
        return $interval->m . " month(s)";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leadership Management - MBC Admin</title>
    <link rel="stylesheet" href="dashboard.css">

    <style>
        .form-section {
            background: white; padding: 20px; border-radius: 10px;
        }
        .form-section input, select {
            width: 100%; padding: 8px; margin-bottom: 10px;
        }
        .leaders-table {
            width: 100%; border-collapse: collapse; margin-top: 20px;
        }
        .leaders-table th, .leaders-table td {
            padding: 10px; border-bottom: 1px solid #ccc; text-align: left;
        }
        .leaders-table img {
            width: 60px; height: 60px; object-fit: cover; border-radius: 6px;
        }
        .pagination { text-align: center; margin-top: 25px; }
        .pagination a {
            background: #4b0082; color: white; padding: 6px 12px;
            border-radius: 4px; text-decoration: none; margin: 0 3px;
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
        <h1>👥 Leadership Management</h1>

        <!-- Add Leader Form -->
        <section class="form-section">
            <h2>Add Leader</h2>

            <form method="POST" enctype="multipart/form-data">
                <input type="text" name="full_name" placeholder="Full Name" required>
                <input type="text" name="position" placeholder="Leadership Position" required>
                <label>Start Date:</label>
                <input type="date" name="start_date" required>

                <label>End Date (Leave blank if still serving):</label>
                <input type="date" name="end_date">

                <label>Upload Photo:</label>
                <input type="file" name="photo" accept="image/*">

                <button type="submit">Add Leader</button>
            </form>
        </section>

        <!-- List of Leaders -->
        <section>
            <h2>Existing Leaders</h2>

            <table class="leaders-table">
                <tr>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Duration</th>
                    <th>Actions</th>
                </tr>

                <?php foreach ($leaders as $l): ?>
                    <tr>
                        <td>
                            <?php if ($l['photo']): ?>
                                <img src="../<?php echo $l['photo']; ?>">
                            <?php endif; ?>
                        </td>

                        <td><?php echo htmlspecialchars($l['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($l['position']); ?></td>

                        <td>
                            <?php echo calculateDuration($l['start_date'], $l['end_date']); ?>
                            <?php if (!$l['end_date']): ?> <small>(Still Serving)</small> <?php endif; ?>
                        </td>

                        <td>
                            <a href="edit_leader.php?id=<?php echo $l['id']; ?>">✏️ Edit</a> | 
                            <a href="delete_leader.php?id=<?php echo $l['id']; ?>" onclick="return confirm('Delete this leader?');">🗑️ Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <!-- Pagination -->
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
