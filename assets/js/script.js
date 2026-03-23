// Select elements
const menuToggle = document.querySelector('.menu-toggle');
const navLinks = document.querySelector('.nav-links');
const menuIcon = document.querySelector('.menu-toggle i');

// Toggle mobile menu visibility
menuToggle.addEventListener('click', () => {
  navLinks.classList.toggle('show');

  // Change icon between bars and X
  if (navLinks.classList.contains('show')) {
    menuIcon.classList.remove('fa-bars');
    menuIcon.classList.add('fa-times');
  } else {
    menuIcon.classList.remove('fa-times');
    menuIcon.classList.add('fa-bars');
  }
});

// Close menu when a link is clicked (useful for mobile)
document.querySelectorAll('.nav-links a').forEach(link => {
  link.addEventListener('click', () => {
    if (navLinks.classList.contains('show')) {
      navLinks.classList.remove('show');
      menuIcon.classList.remove('fa-times');
      menuIcon.classList.add('fa-bars');
    }
  });
});
