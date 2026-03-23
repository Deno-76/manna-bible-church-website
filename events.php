<?php
require_once "includes/db_connect.php";

// Fetch events
$stmt = $pdo->query("SELECT * FROM events ORDER BY event_date DESC");
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MBC - Events</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <script defer src="assets/js/script.js"></script>

  <!-- Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    /* EVENTS PAGE STYLING */
    .events-header {
        text-align: center;
        padding: 40px 20px;
        background: #4b0082;
        color: white;
    }
    .events-container {
        width: 90%;
        max-width: 1100px;
        margin: 40px auto;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
    }
    .event-card {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: transform .3s;
    }
    .event-card:hover {
        transform: translateY(-5px);
    }
    .event-card img {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }
    .event-content {
        padding: 15px;
    }
    .event-content h3 {
        margin: 0;
        color: #4b0082;
    }
    .event-info {
        margin: 8px 0;
        font-size: 0.9rem;
        color: #555;
    }
    .event-description {
        margin-top: 10px;
        font-size: 0.95rem;
    }
    .no-events {
        text-align: center;
        padding: 40px;
        font-size: 1.2rem;
        color: #555;
    }
    .view-btn {
    display: inline-block;
    margin-top: 10px;
    background: #4b0082;
    color: white;
    padding: 8px 15px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.9rem;
}

.view-btn:hover {
    background: #35005d;
}
  </style>
</head>

<body>

<!-- NAVBAR (same as index.html) -->
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

<!-- PAGE TITLE -->
<section class="events-header">
    <h2>Upcoming & Recent Events</h2>
    <p>Stay updated with all Manna Bible Church activities</p>
</section>

<!-- EVENTS LIST -->
<div class="events-container">

<?php if (count($events) > 0): ?>
    <?php foreach ($events as $event): ?>
        <div class="event-card">
            <?php if (!empty($event['poster'])): ?>
                <img src="<?php echo htmlspecialchars($event['poster']); ?>" alt="Event Poster">
            <?php else: ?>
                <img src="assets/images/default-event.jpg" alt="No Poster Available">
            <?php endif; ?>

            <div class="event-content">
                <h3><?php echo htmlspecialchars($event['event_name']); ?></h3>

                <p class="event-info">
                    <i class="fa-solid fa-calendar"></i> 
                    <?php echo date("l, d M Y", strtotime($event['event_date'])); ?>
                    <br>
                    <i class="fa-solid fa-clock"></i>
                    <?php echo htmlspecialchars($event['event_time']); ?>
                    <br>
                    <i class="fa-solid fa-location-dot"></i>
                    <?php echo htmlspecialchars($event['venue']); ?> (<?php echo htmlspecialchars($event['location']); ?>)
                    <br>
                    <i class="fa-solid fa-door-open"></i>
                    <strong><?php echo ucfirst($event['entry_type']); ?> Entry</strong>
                </p>

                <p class="event-description">
    <?php echo substr(htmlspecialchars($event['description']), 0, 120); ?>...
</p>

<a href="event_details.php?id=<?php echo $event['id']; ?>" class="view-btn">
    View Event
</a>

            </div>
        </div>
    <?php endforeach; ?>

<?php else: ?>
    <p class="no-events">No events have been added yet. Please check again soon.</p>
<?php endif; ?>

</div>

<!-- FOOTER (same as index.html) -->
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
