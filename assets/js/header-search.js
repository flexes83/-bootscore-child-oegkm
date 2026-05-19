document.addEventListener('DOMContentLoaded', function () {
  var shell = document.querySelector('[data-oegkm-search-shell]');
  if (!shell) return;

  var toggle = shell.querySelector('.oegkm-search-toggle');
  var form = shell.querySelector('.oegkm-search-form');
  var input = shell.querySelector('input[type="search"]');
  var header = shell.closest('.oegkm-site-header');

  function openSearch() {
    shell.classList.add('is-open');
    if (header) header.classList.add('is-search-open');
    document.body.classList.add('oegkm-search-is-open');
    toggle.setAttribute('aria-expanded', 'true');
    window.setTimeout(function () {
      if (input) input.focus();
    }, 180);
  }

  function closeSearch(force) {
    if (!force && input && input.value.trim() !== '') return;
    shell.classList.remove('is-open');
    if (header) header.classList.remove('is-search-open');
    document.body.classList.remove('oegkm-search-is-open');
    toggle.setAttribute('aria-expanded', 'false');
  }

  toggle.addEventListener('click', function (e) {
    e.preventDefault();
    e.stopPropagation();
    openSearch();
  });

  document.addEventListener('click', function (e) {
    if (!shell.contains(e.target)) {
      closeSearch(false);
    }
  });

  if (input) {
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        input.value = '';
        closeSearch(true);
        toggle.focus();
      }
    });
  }

  form.addEventListener('submit', function () {
    if (!input.value.trim()) {
      closeSearch(true);
    }
  });
});
