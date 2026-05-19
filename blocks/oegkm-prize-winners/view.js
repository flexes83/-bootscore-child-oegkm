document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.oegkm-prize-winners').forEach(function (block) {
    var select = block.querySelector('.oegkm-prize-winners__select');
    var years = Array.prototype.slice.call(block.querySelectorAll('.oegkm-prize-winners__year'));

    if (!select || !years.length) {
      return;
    }

    select.addEventListener('change', function () {
      var activeIndex = parseInt(select.value, 10) || 0;

      years.forEach(function (year, index) {
        var isActive = index === activeIndex;
        year.classList.toggle('is-active', isActive);
        year.hidden = !isActive;
      });
    });
  });
});
