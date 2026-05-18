document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.oegkm-info-slider').forEach(function (slider) {
    var viewport = slider.querySelector('.oegkm-info-slider__viewport');
    var track = slider.querySelector('.oegkm-info-slider__track');
    var cards = slider.querySelectorAll('.oegkm-info-slider__card');
    var prev = slider.querySelector('.oegkm-info-slider__nav--prev');
    var next = slider.querySelector('.oegkm-info-slider__nav--next');

    if (!viewport || !track || !cards.length || !prev || !next) return;

    var index = 0;

    function maxIndex() {
      var overflow = track.scrollWidth - viewport.clientWidth;
      if (overflow <= 1) return 0;
      var cardWidth = cards[0].getBoundingClientRect().width;
      var gap = parseFloat(getComputedStyle(track).gap || getComputedStyle(track).columnGap || 16);
      return Math.max(0, Math.ceil(overflow / (cardWidth + gap)));
    }

    function update() {
      var max = maxIndex();
      index = Math.max(0, Math.min(index, max));
      var cardWidth = cards[0].getBoundingClientRect().width;
      var gap = parseFloat(getComputedStyle(track).gap || getComputedStyle(track).columnGap || 16);
      track.style.transform = 'translateX(-' + ((cardWidth + gap) * index) + 'px)';
      prev.disabled = index === 0;
      next.disabled = index >= max;
    }

    prev.addEventListener('click', function () {
      index -= 1;
      update();
    });

    next.addEventListener('click', function () {
      index += 1;
      update();
    });

    window.addEventListener('resize', update);
    update();
  });
});
