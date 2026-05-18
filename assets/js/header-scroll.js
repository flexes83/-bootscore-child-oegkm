document.addEventListener('DOMContentLoaded', function () {
  var header = document.querySelector('.oegkm-site-header');
  if (!header) return;

  function updateHeaderState() {
    if (window.scrollY > 50) {
      header.classList.add('is-scrolled');
    } else {
      header.classList.remove('is-scrolled');
    }
  }

  updateHeaderState();
  window.addEventListener('scroll', updateHeaderState, { passive: true });
});
