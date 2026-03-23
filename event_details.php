<?php
require_once "includes/db_connect.php";

// Check if event ID is provided
if (!isset($_GET['id'])) {
    die("Invalid event ID");
}

$id = $_GET['id'];

// Fetch event details
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    die("Event not found!");
}

// Create shareable link
$event_url = "http://localhost/event-details.php?id=" . $id;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($event['event_name']); ?> - MBC Event</title>

  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    .event-details-container {
        width: 90%;
        max-width: 900px;
        margin: 40px auto;
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.15);
    }
    .event-details-container img {
        width: 100%;
        border-radius: 10px;
        margin-bottom: 20px;
    }
    .event-meta {
        font-size: 1rem;
        margin: 10px 0;
        color: #444;
    }
    .event-meta i {
        color: #4b0082;
        margin-right: 5px;
    }
    .event-description-full {
        margin-top: 20px;
        line-height: 1.6;
        font-size: 1.05rem;
        color: #333;
    }

    /* SHARE BUTTONS */
    .share-box {
        margin-top: 25px;
        padding: 15px;
        background: #f0e8ff;
        border-radius: 10px;
    }
    .share-box h3 {
        margin-bottom: 10px;
        color: #4b0082;
    }
    .share-buttons {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }
    .share-buttons a, .copy-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #4b0082;
        color: white;
        padding: 10px 14px;
        border-radius: 6px;
        font-size: 0.95rem;
        text-decoration: none;
    }
    .copy-btn {
        cursor: pointer;
    }
    .copy-btn:hover, .share-buttons a:hover {
        background: #35005d;
    }
    .back-btn {
        display: inline-block;
        margin-top: 25px;
        background: #4b0082;
        color: white;
        padding: 10px 18px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 1rem;
    }
    .back-btn:hover {
        background: #35005d;
    }
  </style>

  <script>
    function copyLink() {
        navigator.clipboard.writeText("<?php echo $event_url; ?>");
        alert("Event link copied!");
    }
  </script>
</head>

<body>

<!-- NAVBAR -->
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
        <li><a href="events.php" class="active">Events</a></li>
        <li><a href="gallery.php">Gallery</a></li>
        <li><a href="contact.html">Contact</a></li>
      </ul>
      <div class="menu-toggle"><i class="fa-solid fa-bars"></i></div>
    </nav>
</header>

<!-- EVENT DETAILS -->
<div class="event-details-container">

    <?php if (!empty($event['poster'])): ?>
        <img src="<?php echo htmlspecialchars($event['poster']); ?>" alt="Event Poster">
    <?php endif; ?>

    <h2><?php echo htmlspecialchars($event['event_name']); ?></h2>

    <p class="event-meta"><i class="fa-solid fa-calendar"></i>
        <?php echo date("l, d M Y", strtotime($event['event_date'])); ?>
    </p>

    <p class="event-meta"><i class="fa-solid fa-clock"></i>
        <?php echo htmlspecialchars($event['event_time']); ?>
    </p>

    <p class="event-meta"><i class="fa-solid fa-location-dot"></i>
        <?php echo htmlspecialchars($event['venue']); ?>,
        <?php echo htmlspecialchars($event['location']); ?>
    </p>

    <p class="event-meta"><i class="fa-solid fa-door-open"></i>
        <strong><?php echo ucfirst($event['entry_type']); ?> Entry</strong>
    </p>

    <div class="event-description-full">
        <?php echo nl2br(htmlspecialchars($event['description'])); ?>
    </div>

    <!-- SHARE SECTION -->
    <div class="share-box">
        <h3><i class="fa-solid fa-share-nodes"></i> Share this Event</h3>

        <div class="share-buttons">

            <!-- Facebook -->
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($event_url); ?>" target="_blank">
              <i class="fa-brands fa-facebook"></i> Facebook
            </a>

            <!-- WhatsApp -->
            <a href="https://wa.me/?text=<?php echo urlencode($event['event_name'] . ' - ' . $event_url); ?>" target="_blank">
              <i class="fa-brands fa-whatsapp"></i> WhatsApp
            </a>

            <!-- Twitter/X -->
            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($event_url); ?>&text=<?php echo urlencode($event['event_name']); ?>" target="_blank">
              <i class="fa-brands fa-x-twitter"></i> X / Twitter
            </a>

            <!-- Copy Link -->
            <button class="copy-btn" onclick="copyLink()">
              <i class="fa-solid fa-link"></i> Copy Link
            </button>

        </div>
    </div>

    <a href="events.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i> Back to Events</a>
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
