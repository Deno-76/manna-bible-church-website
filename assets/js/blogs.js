function openTab(evt, tabName) {
  const contents = document.querySelectorAll('.tabcontent');
  contents.forEach(c => c.style.display = 'none');

  document.getElementById(tabName).style.display = 'block';

  const links = document.querySelectorAll('.tablink');
  links.forEach(l => l.classList.remove('active'));

  evt.currentTarget.classList.add('active');
}

// Initialize first tab
document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('blogs').style.display = 'block';
});

// FAQ toggle
document.addEventListener('click', e => {
  if (e.target.classList.contains('faq-question')) {
    const answer = e.target.nextElementSibling;
    answer.style.display = answer.style.display === 'block' ? 'none' : 'block';
  }
});
