const links = document.querySelectorAll('.sidebar nav a[data-section]');
const sections = document.querySelectorAll('.main > div');

links.forEach(link => {
  link.addEventListener('click', () => {
    links.forEach(l => l.classList.remove('active'));
    link.classList.add('active');
    sections.forEach(sec => sec.classList.add('hidden'));
    document.getElementById(link.getAttribute('data-section')).classList.remove('hidden');
  });
});

document.querySelectorAll('.section-search').forEach(searchInput => {
  searchInput.addEventListener('input', () => {
    const filter = searchInput.value.toLowerCase();
    const section = searchInput.closest('div');

    if (section.id === 'movies') {
      const posters = section.querySelectorAll('.poster-card');
      posters.forEach(card => {
        const title = card.querySelector('.poster-title').textContent.toLowerCase();
        const genre = card.querySelector('.poster-genre').textContent.toLowerCase();
        card.style.display = (title.includes(filter) || genre.includes(filter)) ? '' : 'none';
      });
    } else {
      const rows = section.querySelectorAll('tbody tr');
      rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
      });
    }
  });
});
