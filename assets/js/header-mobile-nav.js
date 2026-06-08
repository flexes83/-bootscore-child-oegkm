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

  function getDirectSubmenu(item) {
    return Array.from(item.children).find(function (child) {
      return child.classList.contains('dropdown-menu') || child.classList.contains('sub-menu');
    });
  }

  function getParentMenuItem(link) {
    const item = link.parentElement;

    if (!item || !item.matches('.menu-item-has-children, .dropdown')) {
      return null;
    }

    return item;
  }

  function isDirectMenuToggle(link) {
    const item = getParentMenuItem(link);
    const submenu = item ? getDirectSubmenu(item) : null;

    return Boolean(item && submenu && link.parentElement === item);
  }

  function setSubmenuState(link, isOpen) {
    const item = getParentMenuItem(link);
    const submenu = item ? getDirectSubmenu(item) : null;

    if (!item || !submenu) {
      return;
    }

    item.classList.toggle('show', isOpen);
    link.classList.toggle('show', isOpen);
    submenu.classList.toggle('show', isOpen);
    submenu.hidden = !isOpen;
    submenu.style.display = isOpen ? 'block' : 'none';
    link.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  }

  function closeSubmenus() {
    panel.querySelectorAll('.menu-item-has-children > a.show, .dropdown > a.show').forEach(function (link) {
      setSubmenuState(link, false);
    });
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

    if (!isOpen) {
      closeSubmenus();
    }
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
    if (!link) {
      return;
    }

    if (isDirectMenuToggle(link) && !isDesktop()) {
      event.preventDefault();
      event.stopPropagation();
      if (typeof event.stopImmediatePropagation === 'function') {
        event.stopImmediatePropagation();
      }
      setSubmenuState(link, !link.classList.contains('show'));
      return;
    }

    setMenuState(false);
  }, true);

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      setMenuState(false);
    }
  });

  window.addEventListener('resize', function () {
    if (isDesktop()) {
      setMenuState(false);
      panel.querySelectorAll('.dropdown-menu, .sub-menu').forEach(function (submenu) {
        submenu.hidden = false;
        submenu.style.display = '';
      });
    }
  });

  if (!isDesktop()) {
    panel.querySelectorAll('.menu-item-has-children > a, .dropdown > a').forEach(function (link) {
      setSubmenuState(link, false);
    });
  }
})();
