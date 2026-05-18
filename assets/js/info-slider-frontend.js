
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.oegkm-info-slider').forEach(function (slider) {
    var slides = slider.querySelectorAll('.oegkm-info-slider__slide');
    var prev = slider.querySelector('.oegkm-info-slider__nav--prev');
    var next = slider.querySelector('.oegkm-info-slider__nav--next');

    if (!slides.length || !prev || !next) return;

    var index = 0;
    var total = slides.length;

    function update() {
      slides.forEach(function (slide, slideIndex) {
        var isActive = slideIndex === index;
        slide.hidden = !isActive;
        slide.classList.toggle('is-active', isActive);
      });

      prev.disabled = total <= 1 || index === 0;
      next.disabled = total <= 1 || index === total - 1;
      slider.setAttribute('data-active-slide', String(index));
    }

    prev.addEventListener('click', function () {
      index = Math.max(0, index - 1);
      update();
    });

    next.addEventListener('click', function () {
      index = Math.min(total - 1, index + 1);
      update();
    });

    update();
  });
});
