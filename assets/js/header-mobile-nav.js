(function () {
  'use strict';

  const header = document.querySelector('.oegkm-site-header');
  const toggle = document.querySelector('.oegkm-navbar-toggler');
  const panel = document.getElementById('oegkmMainNavigation');

  if (!header || !toggle || !panel) {
    return;
  }

  toggle.removeAttribute('data-bs-toggle');
  toggle.removeAttribute('data-bs-target');

  function isDesktop() {
    return window.matchMedia('(min-width: 1200px)').matches;
  }

  function setMenuState(isOpen) {
    panel.classList.remove('collapsing');
    panel.classList.add('collapse');
    panel.classList.toggle('show', isOpen);
    toggle.classList.toggle('is-open', isOpen);
    header.classList.toggle('is-mobile-nav-open', isOpen);
    document.body.classList.toggle('oegkm-mobile-nav-is-open', isOpen);
    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    toggle.setAttribute('aria-label', isOpen ? 'Navigation schliessen' : 'Navigation umschalten');
  }

  function handleToggle(event) {
    if (isDesktop()) {
      return;
    }

    event.preventDefault();
    event.stopPropagation();
    if (typeof event.stopImmediatePropagation === 'function') {
      event.stopImmediatePropagation();
    }
    setMenuState(!panel.classList.contains('show'));
  }

  toggle.addEventListener('click', handleToggle, true);

  panel.addEventListener('click', function (event) {
    const link = event.target.closest('a');
    if (!link || link.classList.contains('dropdown-toggle')) {
      return;
    }

    setMenuState(false);
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      setMenuState(false);
    }
  });

  window.addEventListener('resize', function () {
    if (isDesktop()) {
      setMenuState(false);
    }
  });
})();
