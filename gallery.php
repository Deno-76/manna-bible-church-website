<?php
require_once "includes/db_connect.php";

// Pagination
$limit = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$total = $pdo->query("SELECT COUNT(*) FROM gallery")->fetchColumn();
$total_pages = ceil($total / $limit);

$stmt = $pdo->prepare("SELECT * FROM gallery ORDER BY uploaded_at DESC LIMIT $limit OFFSET $offset");
$stmt->execute();
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manna Bible Church - Gallery</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <script defer src="assets/js/script.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    /* Gallery Styles */
    .gallery-header {
        text-align: center;
        padding: 40px 20px;
    }
    .gallery-header h2 {
        font-size: 32px;
        margin-bottom: 10px;
        color: #4b0082;
    }
    .gallery-grid {
        display: grid;
        gap: 20px;
        padding: 20px;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    }
    .gallery-item {
        overflow: hidden;
        border-radius: 10px;
        cursor: pointer;
        position: relative;
    }
    .gallery-item img {
        width: 100%;
        height: 220px;
        object-fit: cover;
        transition: transform .3s ease;
    }
    .gallery-item:hover img {
        transform: scale(1.08);
    }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.9);
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }
    .modal img {
        max-width: 90%;
        max-height: 90%;
        border-radius: 10px;
    }
    .modal-close {
        position: absolute;
        top: 20px;
        right: 35px;
        font-size: 35px;
        color: white;
        cursor: pointer;
    }

    /* Pagination */
    .pagination {
        text-align: center;
        padding: 20px;
    }
    .pagination a {
        padding: 10px 16px;
        background: #eee;
        margin: 3px;
        display: inline-block;
        border-radius: 5px;
        text-decoration: none;
        color: #333;
    }
    .pagination a.active {
        background: #4b0082;
        color: white;
    }
  </style>
</head>

<body>

  <!-- Navbar / Header -->
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
        <li><a href="sermons.php">Sermons</a></li>
        <li><a href="events.php">Events</a></li>
        <li><a href="gallery.php" class="active">Gallery</a></li>
        <li><a href="contact.html">Contact</a></li>
      </ul>
      <div class="menu-toggle"><i class="fa-solid fa-bars"></i></div>
    </nav>
  </header>

  <section class="gallery-header">
      <h2>Church Gallery</h2>
      <p>Photos from our services, programs, and ministries</p>
  </section>

  <!-- Gallery Grid -->
  <section class="gallery-grid">
    <?php foreach ($images as $img): ?>
      <div class="gallery-item">
        <img src="<?php echo htmlspecialchars($img['image_path']); ?>" 
             alt="Gallery Image" 
             onclick="openModal(this.src)">
      </div>
    <?php endforeach; ?>
  </section>

  <!-- Pagination -->
  <div class="pagination">
    <?php if ($page > 1): ?>
      <a href="?page=<?php echo $page - 1; ?>">Prev</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
      <a href="?page=<?php echo $i; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>">
        <?php echo $i; ?>
      </a>
    <?php endfor; ?>

    <?php if ($page < $total_pages): ?>
      <a href="?page=<?php echo $page + 1; ?>">Next</a>
    <?php endif; ?>
  </div>

  <!-- Modal -->
  <div class="modal" id="imgModal">
      <span class="modal-close" onclick="closeModal()">&times;</span>
      <img id="modalImage">
  </div>

  <script>
    function openModal(src) {
        document.getElementById("imgModal").style.display = "flex";
        document.getElementById("modalImage").src = src;
    }
    function closeModal() {
        document.getElementById("imgModal").style.display = "none";
    }
  </script>

  <!-- Footer -->
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
