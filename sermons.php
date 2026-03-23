<?php
require_once "includes/db_connect.php";

// Pagination
$limit = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search
$search = isset($_GET['search']) ? trim($_GET['search']) : "";

// Build search query
$where = "";
$params = [];

if ($search !== "") {
    $where = "WHERE preacher_name LIKE ? OR sermon_title LIKE ? OR book_read LIKE ? OR theme LIKE ?";
    $params = ["%$search%", "%$search%", "%$search%", "%$search%"];
}

// Count total sermons
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM sermons $where");
$countStmt->execute($params);
$total_rows = $countStmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

// Fetch sermons
$query = "SELECT * FROM sermons $where ORDER BY sermon_date DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$sermons = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manna Bible Church - Sermons</title>
  <link rel="stylesheet" href="assets/css/style.css">

  <!-- Custom styles specifically for sermons -->
  <style>
    .sermons-container {
      max-width: 1100px;
      margin: 50px auto;
      padding: 10px;
    }

    .sermon-card {
      background: #ffffff;
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 25px;
      box-shadow: 0 3px 8px rgba(0,0,0,0.15);
    }

    .sermon-card h3 {
      font-size: 22px;
      margin-bottom: 10px;
      color: #333;
    }

    .sermon-meta {
      font-size: 15px;
      margin-bottom: 12px;
      color: #555;
    }

    .sermon-card audio {
      width: 100%;
      margin: 10px 0;
    }

    .notes-btn {
      background: #0d6efd;
      color: white;
      padding: 8px 15px;
      border-radius: 6px;
      display: inline-block;
      margin-top: 10px;
      text-decoration: none;
    }

    .notes-btn:hover {
      background: #084298;
    }

    .sermon-video iframe {
      width: 100%;
      height: 320px;
      border-radius: 10px;
      margin-top: 10px;
    }

    .search-box {
      text-align: center;
      margin-bottom: 30px;
    }

    .search-box input {
      width: 70%;
      padding: 12px;
      border-radius: 10px;
      border: 1px solid #aaa;
    }

    .pagination {
      text-align: center;
      margin: 20px 0;
    }

    .pagination a {
      padding: 10px 16px;
      background: #333;
      color: white;
      margin: 3px;
      border-radius: 6px;
      text-decoration: none;
    }

    .pagination a.active {
      background: #0d6efd;
    }
  </style>

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

<!-- HEADER / NAVBAR -->
<header>
    <a href="index.html" class="logo">
        <img src="assets/images/logo.png" alt="Manna Bible Church Logo">
        <h1>Manna Bible Church - Likoni</h1>
    </a>

    <nav class="navbar">
      <ul class="nav-links">
        <li><a href="index.html">Home</a></li>
        <li><a href="about.html">About Us</a></li>
        <li><a href="programs.html">Programs</a></li>
        <li><a href="sermons.php" class="active">Sermons</a></li>
        <li><a href="events.php">Events</a></li>
        <li><a href="gallery.php">Gallery</a></li>
        <li><a href="contact.html">Contact</a></li>
      </ul>
      <div class="menu-toggle"><i class="fa-solid fa-bars"></i></div>
    </nav>
</header>


<!-- SERMONS SECTION -->
<div class="sermons-container">

    <h2 style="text-align:center; margin-bottom:20px;">Latest Sermons</h2>

    <!-- Search -->
    <div class="search-box">
        <form method="GET">
            <input type="text" name="search" placeholder="Search by preacher, title, book, or theme..."
                   value="<?= htmlspecialchars($search) ?>">
        </form>
    </div>

    <?php if (empty($sermons)): ?>
        <p style="text-align:center;">No sermons found.</p>
    <?php else: ?>

        <?php foreach ($sermons as $sermon): ?>
        <div class="sermon-card">

            <h3><?= htmlspecialchars($sermon['sermon_title']) ?></h3>

            <p class="sermon-meta">
                <strong>Preacher:</strong> <?= htmlspecialchars($sermon['preacher_name']) ?><br>
                <strong>Book Read:</strong> <?= htmlspecialchars($sermon['book_read']) ?><br>
                <strong>Theme:</strong> <?= htmlspecialchars($sermon['theme']) ?><br>
                <strong>Date:</strong> <?= htmlspecialchars($sermon['sermon_date']) ?>
            </p>

            <!-- Audio -->
            <?php if (!empty($sermon['audio_path'])): ?>
                <audio controls>
                    <source src="<?= $sermon['audio_path'] ?>" type="audio/mpeg">
                </audio>
            <?php endif; ?>

            <!-- Video -->
            <?php if (!empty($sermon['video_link'])): ?>
                <div class="sermon-video">
                    <iframe src="<?= $sermon['video_link'] ?>" allowfullscreen></iframe>
                </div>
            <?php endif; ?>

            <!-- Notes -->
            <?php if (!empty($sermon['notes_file'])): ?>
                <a class="notes-btn" href="<?= $sermon['notes_file'] ?>" download>
                    <i class="fa-solid fa-file"></i> Download Notes
                </a>
            <?php endif; ?>

        </div>
        <?php endforeach; ?>

    <?php endif; ?>

    <!-- Pagination -->
    <div class="pagination">
      <?php if ($page > 1): ?>
        <a href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>">« Prev</a>
      <?php endif; ?>

      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a class="<?= ($i == $page ? 'active' : '') ?>" 
           href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
      <?php endfor; ?>

      <?php if ($page < $total_pages): ?>
        <a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>">Next »</a>
      <?php endif; ?>
    </div>

</div>


<!-- FOOTER -->
<footer>
    <div class="footer-content">
      <div class="quick-links">
        <h4>Quick Links</h4>
        <ul>
          <li><a href="mst.html">MST</a></li>
          <li><a href="statement-of-faith.html">Statement of Faith</a></li>
        </ul>
      </div>

      <div class="contact-info">
        <h4>Contact</h4>
        <p><i class="fa-solid fa-envelope"></i> <a href="mailto:mannabc56076@gmail.com">mannabc56076@gmail.com</a></p>
        <p><i class="fa-solid fa-phone"></i> <a href="tel:+254717907594">+254 717 907594</a></p>
        <p><i class="fa-brands fa-whatsapp"></i> <a href="https://wa.me/254717907594" target="_blank">WhatsApp</a></p>
      </div>

      <div class="socials">
        <h4>Follow Us</h4>
        <div class="social-icons">
          <a href="https://www.facebook.com/share/16YwfMuJMT/" target="_blank"><i class="fa-brands fa-facebook-f"></i></a>
          <a href="https://youtube.com/@dalmasnzai2746?si=pbBVOrkEGIsOZNfR" target="_blank"><i class="fa-brands fa-youtube"></i></a>
        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <p>© 2025 Manna Bible Church (MBC). All Rights Reserved.</p>
      <p>Created by <a href="https://www.facebook.com/share/1DKm1beyDE/" target="_blank">Dennis Mwero</a></p>
    </div>
</footer>

</body>
</html>
