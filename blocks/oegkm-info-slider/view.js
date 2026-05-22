document.addEventListener('DOMContentLoaded', function () {
  function addSwipe(element, onPrevious, onNext) {
    var startX = 0;
    var startY = 0;
    var tracking = false;
    var minDistance = 42;

    element.addEventListener('pointerdown', function (event) {
      if (event.pointerType === 'mouse' && event.button !== 0) return;
      startX = event.clientX;
      startY = event.clientY;
      tracking = true;
    }, { passive: true });

    element.addEventListener('pointerup', function (event) {
      if (!tracking) return;
      tracking = false;

      var deltaX = event.clientX - startX;
      var deltaY = event.clientY - startY;
      if (Math.abs(deltaX) < minDistance || Math.abs(deltaX) < Math.abs(deltaY) * 1.25) return;

      if (deltaX < 0) {
        onNext();
      } else {
        onPrevious();
      }
    }, { passive: true });

    element.addEventListener('pointercancel', function () {
      tracking = false;
    }, { passive: true });
  }

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
      index = Math.max(0, index - 1);
      update();
    });

    next.addEventListener('click', function () {
      index = Math.min(maxIndex(), index + 1);
      update();
    });

    addSwipe(viewport, function () {
      index = Math.max(0, index - 1);
      update();
    }, function () {
      index = Math.min(maxIndex(), index + 1);
      update();
    });

    window.addEventListener('resize', update);
    update();
  });
});
