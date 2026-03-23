<?php
require_once "includes/db_connect.php";

// Pagination
$limit = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Count leaders
$totalStmt = $pdo->query("SELECT COUNT(*) FROM leadership");
$total_records = $totalStmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Fetch leaders
$stmt = $pdo->prepare("SELECT * FROM leadership ORDER BY start_date DESC LIMIT $limit OFFSET $offset");
$stmt->execute();
$leaders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate Duration
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manna Bible Church - Leadership</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <script defer src="assets/js/script.js"></script>

  <style>
    .leadership-header {
        text-align: center;
        margin-top: 40px;
    }

    .leaders-grid {
        max-width: 1200px;
        margin: 40px auto;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 25px;
        padding: 10px;
    }

    .leader-card {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 3px 10px rgba(0,0,0,0.15);
        text-align: center;
        padding-bottom: 15px;
    }

    .leader-card img {
        width: 100%;
        height: 280px;
        object-fit: cover;
    }

    .leader-card h3 {
        margin: 12px 0 4px;
        color: #333;
        font-size: 20px;
        font-weight: bold;
    }

    .leader-card p {
        color: #555;
        margin: 0;
        font-size: 16px;
    }

    .duration {
        margin-top: 6px;
        font-size: 14px;
        color: #6a1b9a;
        font-weight: bold;
    }

    .pagination {
        text-align: center;
        margin: 30px 0;
    }

    .pagination a {
        padding: 10px 15px;
        background: #4b0082;
        color: #fff;
        margin: 3px;
        border-radius: 6px;
        text-decoration: none;
    }

    .pagination a.active {
        background: #8a2be2;
    }

    /* Hover Effect */
.leader-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer;
}

.leader-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.25);
}

/* POPUP VIEW */
.image-popup {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    backdrop-filter: blur(5px);
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

/* Image in popup */
.image-popup img {
    max-width: 90%;
    max-height: 90%;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(255,255,255,0.4);
}

/* Close button */
.close-popup {
    position: absolute;
    top: 25px;
    right: 35px;
    font-size: 35px;
    color: white;
    cursor: pointer;
}
  </style>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

<!-- NAVBAR -->
<header>
    <a href="index.html" class="logo">
        <img src="assets/images/logo.png" alt="Logo">
        <h1>Manna Bible Church - Likoni</h1>
    </a>

    <nav class="navbar">
      <ul class="nav-links">
        <li><a href="index.html">Home</a></li>
        <li><a href="about.html">About Us</a></li>
        <li><a href="programs.html">Programs</a></li>
        <li><a href="sermons.php">Sermons</a></li>
        <li><a href="events.php">Events</a></li>
        <li><a href="gallery.php">Gallery</a></li>
        <li><a href="leadership.php" class="active">Leadership</a></li>
        <li><a href="contact.html">Contact</a></li>
      </ul>
      <div class="menu-toggle"><i class="fa-solid fa-bars"></i></div>
    </nav>
</header>

<script>
document.querySelector(".hamburger").addEventListener("click", () => {
  document.querySelector(".nav-links").classList.toggle("active");
});
</script>

<!-- PAGE TITLE -->
<section class="leadership-header">
    <h2>Church Leadership Structure</h2>
    <p>Meet the leaders serving faithfully at Manna Bible Church.</p>
</section>

<!-- LEADERS GRID -->
<section class="leaders-grid">
    <?php foreach ($leaders as $l): ?>
        <div class="leader-card">
            <?php if ($l['photo']): ?>
                <img src="<?php echo $l['photo']; ?>" alt="Leader Photo">
            <?php else: ?>
                <img src="assets/images/default_profile.webp">
            <?php endif; ?>

            <h3><?php echo htmlspecialchars($l['full_name']); ?></h3>
            <p><?php echo htmlspecialchars($l['position']); ?></p>

            <p class="duration">
                <?php echo calculateDuration($l['start_date'], $l['end_date']); ?>
                <?php if (!$l['end_date']) echo " (Still Serving)"; ?>
            </p>
        </div>
    <?php endforeach; ?>
</section>

<!-- PAGINATION -->
<div class="pagination">
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a class="<?php echo $i == $page ? 'active' : ''; ?>"
           href="?page=<?php echo $i; ?>">
           <?php echo $i; ?>
        </a>
    <?php endfor; ?>
</div>

<div class="image-popup" id="imagePopup">
    <span class="close-popup" id="closePopup">&times;</span>
    <img id="popupImg" src="">
</div>

<script>
document.querySelectorAll(".leader-card img").forEach(img => {
    img.addEventListener("click", function() {
        document.getElementById("popupImg").src = this.src;
        document.getElementById("imagePopup").style.display = "flex";
    });
});

// Close popup
document.getElementById("closePopup").addEventListener("click", function() {
    document.getElementById("imagePopup").style.display = "none";
});

// Close popup when clicking outside the image
document.getElementById("imagePopup").addEventListener("click", function(e) {
    if (e.target === this) {
        this.style.display = "none";
    }
});
</script>

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
