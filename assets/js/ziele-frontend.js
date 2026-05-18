
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.oegkm-ziele').forEach(function (section) {
    var track = section.querySelector('.oegkm-ziele__track');
    var cards = section.querySelectorAll('.oegkm-ziele__card');
    var prev = section.querySelector('.oegkm-ziele__nav--prev');
    var next = section.querySelector('.oegkm-ziele__nav--next');
    if (!track || cards.length < 2 || !prev || !next) return;

    var index = 0;
    var total = cards.length;

    function maxIndex() {
      return Math.max(0, total - 2);
    }

    function update() {
      var cardWidth = cards[0].getBoundingClientRect().width;
      var gap = parseFloat(getComputedStyle(track).gap || getComputedStyle(track).columnGap || 16);
      var offset = (cardWidth + gap) * index;
      track.style.transform = 'translateX(-' + offset + 'px)';
      prev.disabled = index === 0;
      next.disabled = index >= maxIndex();
    }

    prev.addEventListener('click', function () {
      index = Math.max(0, index - 1);
      update();
    });

    next.addEventListener('click', function () {
      index = Math.min(maxIndex(), index + 1);
      update();
    });

    window.addEventListener('resize', update);
    update();
  });
});
