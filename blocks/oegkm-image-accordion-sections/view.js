document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.oegkm-image-accordion').forEach(function (block) {
    block.querySelectorAll('.oegkm-image-accordion__toggle').forEach(function (toggle) {
      toggle.addEventListener('click', function () {
        var item = toggle.closest('.oegkm-image-accordion__item');
        var panel = item.querySelector('.oegkm-image-accordion__panel');
        var open = item.classList.contains('is-open');

        item.classList.toggle('is-open', !open);
        toggle.classList.toggle('is-open', !open);
        toggle.setAttribute('aria-expanded', open ? 'false' : 'true');
        panel.classList.toggle('is-open', !open);
        panel.hidden = open;
      });
    });
  });
});
