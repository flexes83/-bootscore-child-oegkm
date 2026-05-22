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

  document.querySelectorAll('.oegkm-disease-slider').forEach(function (slider) {
    const slides = Array.from(slider.querySelectorAll('.oegkm-disease-slider__slide'));
    if (slides.length < 2) return;

    let index = slides.findIndex(function (slide) { return slide.classList.contains('is-active'); });
    if (index < 0) index = 0;

    function render(nextIndex) {
      index = (nextIndex + slides.length) % slides.length;
      slides.forEach(function (slide, slideIndex) {
        const active = slideIndex === index;
        slide.classList.toggle('is-active', active);
        slide.setAttribute('aria-hidden', active ? 'false' : 'true');
        slide.removeAttribute('hidden');
      });
    }

    slider.querySelectorAll('.oegkm-disease-slider__nav--prev').forEach(function (button) {
      button.addEventListener('click', function () { render(index - 1); });
    });

    slider.querySelectorAll('.oegkm-disease-slider__nav--next').forEach(function (button) {
      button.addEventListener('click', function () { render(index + 1); });
    });

    addSwipe(slider.querySelector('.oegkm-disease-slider__stage') || slider.querySelector('.oegkm-disease-slider__track') || slider, function () {
      render(index - 1);
    }, function () {
      render(index + 1);
    });

    render(index);
  });
});
