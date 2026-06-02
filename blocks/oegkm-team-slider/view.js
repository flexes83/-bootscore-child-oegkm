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

  document.querySelectorAll('.oegkm-team-slider').forEach(function (slider) {
    var track = slider.querySelector('.oegkm-team-slider__track');
    var cards = slider.querySelectorAll('.oegkm-team-slider__card');
    var prev = slider.querySelector('.oegkm-team-slider__nav--prev');
    var next = slider.querySelector('.oegkm-team-slider__nav--next');

    if (!track || !cards.length || !prev || !next) return;

    var index = 0;

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
      index = Math.max(0, index - 1);
      update();
    });

    next.addEventListener('click', function () {
      index = Math.min(maxIndex(), index + 1);
      update();
    });

    addSwipe(slider.querySelector('.oegkm-team-slider__viewport') || slider, function () {
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
