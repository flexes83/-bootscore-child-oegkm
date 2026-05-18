document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.oegkm-team-slider').forEach(function (slider) {
    var track = slider.querySelector('.oegkm-team-slider__track');
    var cards = slider.querySelectorAll('.oegkm-team-slider__card');
    var prev = slider.querySelector('.oegkm-team-slider__nav--prev');
    var next = slider.querySelector('.oegkm-team-slider__nav--next');

    if (!track || !cards.length || !prev || !next) return;

    var index = cards.length > 1 ? 1 : 0;

    function maxIndex() {
      return Math.max(0, cards.length - 1);
    }

    function update() {
      var gap = parseFloat(getComputedStyle(track).gap || getComputedStyle(track).columnGap || 16);
      index = Math.max(0, Math.min(index, maxIndex()));
      cards.forEach(function (card, cardIndex) {
        card.classList.toggle('is-active', cardIndex === index);
      });
      var offset = 0;
      cards.forEach(function (card, cardIndex) {
        if (cardIndex < index) {
          offset += card.getBoundingClientRect().width + gap;
        }
      });
      track.style.transform = 'translateX(-' + offset + 'px)';
      prev.disabled = index === 0;
      next.disabled = index >= maxIndex();
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
