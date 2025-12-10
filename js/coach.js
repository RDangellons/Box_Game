document.addEventListener('DOMContentLoaded', () => {
  const sidebar = document.getElementById('coachSidebar');
  const overlay = document.getElementById('coachOverlay');
  const btnToggle = document.getElementById('btnToggleSidebar');

  if (!sidebar || !overlay || !btnToggle) return;

  function abrirSidebar() {
    sidebar.classList.add('open');
    overlay.classList.add('visible');
  }

  function cerrarSidebar() {
    sidebar.classList.remove('open');
    overlay.classList.remove('visible');
  }

  btnToggle.addEventListener('click', () => {
    if (sidebar.classList.contains('open')) {
      cerrarSidebar();
    } else {
      abrirSidebar();
    }
  });

  overlay.addEventListener('click', cerrarSidebar);
});
