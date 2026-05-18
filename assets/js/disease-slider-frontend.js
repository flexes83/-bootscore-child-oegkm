document.addEventListener('DOMContentLoaded', function () {
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
        if (active) {
          slide.removeAttribute('hidden');
        } else {
          slide.setAttribute('hidden', 'hidden');
        }
      });
    }

    slider.querySelectorAll('.oegkm-disease-slider__nav--prev').forEach(function (button) {
      button.addEventListener('click', function () { render(index - 1); });
    });

    slider.querySelectorAll('.oegkm-disease-slider__nav--next').forEach(function (button) {
      button.addEventListener('click', function () { render(index + 1); });
    });

    render(index);
  });
});
