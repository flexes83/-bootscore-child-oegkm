(function () {
  const header = document.querySelector('.oegkm-site-header');
  const toggle = document.querySelector('.oegkm-mobile-nav-toggle');
  const panel = document.getElementById('oegkm-header-navigation');

  if (!header || !toggle || !panel) {
    return;
  }

  const closeMenu = () => {
    header.classList.remove('is-mobile-nav-open');
    toggle.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('oegkm-mobile-nav-is-open');
  };

  toggle.addEventListener('click', () => {
    const isOpen = header.classList.toggle('is-mobile-nav-open');
    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    document.body.classList.toggle('oegkm-mobile-nav-is-open', isOpen);
  });

  panel.addEventListener('click', (event) => {
    if (event.target.closest('a')) {
      closeMenu();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeMenu();
    }
  });

  window.addEventListener('resize', () => {
    if (window.matchMedia('(min-width: 992px)').matches) {
      closeMenu();
    }
  });
})();
