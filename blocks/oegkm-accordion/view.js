
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.oegkm-accordion').forEach(function (accordion) {
    accordion.querySelectorAll('.oegkm-accordion__toggle').forEach(function (toggle) {
      toggle.addEventListener('click', function () {
        var item = toggle.closest('.oegkm-accordion__item');
        var panel = item.querySelector('.oegkm-accordion__panel');
        var icon = toggle.querySelector('.oegkm-accordion__icon');
        var isOpen = toggle.classList.contains('is-open');
        var single = accordion.getAttribute('data-single-open') === 'true';

        if (single) {
          accordion.querySelectorAll('.oegkm-accordion__item').forEach(function (otherItem) {
            if (otherItem !== item) {
              otherItem.classList.remove('is-open');
              var otherToggle = otherItem.querySelector('.oegkm-accordion__toggle');
              var otherPanel = otherItem.querySelector('.oegkm-accordion__panel');
              var otherIcon = otherItem.querySelector('.oegkm-accordion__icon');
              if (otherToggle) { otherToggle.classList.remove('is-open'); otherToggle.setAttribute('aria-expanded', 'false'); }
              if (otherPanel) { otherPanel.classList.remove('is-open'); otherPanel.hidden = true; }
              if (otherIcon) otherIcon.textContent = '+';
            }
          });
        }

        item.classList.toggle('is-open', !isOpen);
        toggle.classList.toggle('is-open', !isOpen);
        toggle.setAttribute('aria-expanded', (!isOpen).toString());
        panel.classList.toggle('is-open', !isOpen);
        panel.hidden = isOpen;
        if (icon) icon.textContent = isOpen ? '+' : '−';
      });
    });
  });
});
