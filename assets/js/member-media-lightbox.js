(function () {
  'use strict';

  function hydrateImage(img) {
    if (!img || !img.dataset || !img.dataset.src) return;
    img.src = img.dataset.src;
    img.removeAttribute('data-src');
  }

  document.addEventListener('click', function (event) {
    const button = event.target.closest('[data-oegkm-show-gallery]');
    if (!button) return;

    const section = button.closest('.oegkm-event-media-section');
    if (!section) return;

    section.querySelectorAll('.oegkm-event-media-gallery__item[hidden]').forEach(function (item) {
      item.hidden = false;
      hydrateImage(item.querySelector('img[data-src]'));
    });

    button.remove();
  });

  function createVideoIframe(videoButton) {
    const token = videoButton.dataset.oegkmVideoToken || '';
    if (!token) return null;

    let video = {};
    try {
      video = JSON.parse(window.atob(token));
    } catch (error) {
      return null;
    }

    const id = video.id || '';
    const provider = video.p || '';
    if (!id || !provider) return null;

    const iframe = document.createElement('iframe');
    if (provider === 'vm') {
      iframe.src = 'https://player.' + 'vimeo.com/video/' + encodeURIComponent(id) + '?autoplay=1';
    } else {
      iframe.src = 'https://www.' + 'youtube-nocookie.com/embed/' + encodeURIComponent(id) + '?autoplay=1&rel=0';
    }
    iframe.title = videoButton.getAttribute('aria-label') || 'Video';
    iframe.loading = 'lazy';
    iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
    iframe.allowFullscreen = true;

    return iframe;
  }

  const lightbox = document.createElement('div');
  lightbox.className = 'oegkm-lightbox';
  lightbox.setAttribute('role', 'dialog');
  lightbox.setAttribute('aria-modal', 'true');
  lightbox.setAttribute('aria-hidden', 'true');
  lightbox.innerHTML = `
    <button class="oegkm-lightbox__close" type="button" aria-label="Bild schließen">
      <svg class="oegkm-lightbox__icon" aria-hidden="true" viewBox="0 0 24 24" focusable="false">
        <path d="M6 6l12 12M18 6L6 18" />
      </svg>
    </button>
    <button class="oegkm-lightbox__nav oegkm-lightbox__nav--prev" type="button" aria-label="Vorheriges Bild">
      <svg class="oegkm-lightbox__icon" aria-hidden="true" viewBox="0 0 24 24" focusable="false">
        <path d="M15 5l-7 7 7 7" />
      </svg>
    </button>
    <figure class="oegkm-lightbox__figure">
      <img class="oegkm-lightbox__image" alt="" loading="eager" decoding="async">
      <div class="oegkm-lightbox__video" hidden></div>
      <figcaption class="oegkm-lightbox__caption"></figcaption>
    </figure>
    <button class="oegkm-lightbox__nav oegkm-lightbox__nav--next" type="button" aria-label="Nächstes Bild">
      <svg class="oegkm-lightbox__icon" aria-hidden="true" viewBox="0 0 24 24" focusable="false">
        <path d="M9 5l7 7-7 7" />
      </svg>
    </button>
  `;

  function ensureLightbox() {
    if (!document.body.contains(lightbox)) {
      document.body.appendChild(lightbox);
    }
  }

  let currentIndex = 0;
  let galleryLinks = [];
  let lightboxMode = 'image';

  const image = lightbox.querySelector('.oegkm-lightbox__image');
  const video = lightbox.querySelector('.oegkm-lightbox__video');
  const caption = lightbox.querySelector('.oegkm-lightbox__caption');
  const closeButton = lightbox.querySelector('.oegkm-lightbox__close');
  const prevButton = lightbox.querySelector('.oegkm-lightbox__nav--prev');
  const nextButton = lightbox.querySelector('.oegkm-lightbox__nav--next');

  function resetVideo() {
    video.hidden = true;
    video.replaceChildren();
  }

  function setImage(index) {
    if (!galleryLinks.length) return;
    lightboxMode = 'image';
    resetVideo();
    image.hidden = false;
    currentIndex = (index + galleryLinks.length) % galleryLinks.length;
    const link = galleryLinks[currentIndex];
    const title = link.dataset.caption || (link.querySelector('img') ? link.querySelector('img').alt : '') || '';
    image.src = link.href;
    image.alt = title;
    caption.textContent = title;
    caption.hidden = !title;
    prevButton.hidden = galleryLinks.length < 2;
    nextButton.hidden = galleryLinks.length < 2;
  }

  function showLightbox() {
    lightbox.classList.add('is-open');
    lightbox.setAttribute('aria-hidden', 'false');
    document.documentElement.classList.add('oegkm-lightbox-open');
    closeButton.focus({ preventScroll: true });
  }

  function openImage(link) {
    ensureLightbox();
    const group = link.dataset.oegkmLightbox || '';
    galleryLinks = Array.from(document.querySelectorAll('[data-oegkm-lightbox="' + CSS.escape(group) + '"]')).filter(function (item) {
      return !item.hidden;
    });
    const index = Math.max(0, galleryLinks.indexOf(link));
    setImage(index);
    showLightbox();
  }

  function openVideo(videoButton) {
    const iframe = createVideoIframe(videoButton);
    if (!iframe) return;

    ensureLightbox();
    lightboxMode = 'video';
    galleryLinks = [];
    image.hidden = true;
    image.removeAttribute('src');
    video.hidden = false;
    video.replaceChildren(iframe);
    caption.textContent = videoButton.querySelector('.oegkm-video-placeholder__title') ? videoButton.querySelector('.oegkm-video-placeholder__title').textContent : '';
    caption.hidden = !caption.textContent;
    prevButton.hidden = true;
    nextButton.hidden = true;
    showLightbox();
  }

  function close() {
    lightbox.classList.remove('is-open');
    lightbox.setAttribute('aria-hidden', 'true');
    document.documentElement.classList.remove('oegkm-lightbox-open');
    image.removeAttribute('src');
    image.hidden = false;
    resetVideo();
  }

  document.addEventListener('click', function (event) {
    const link = event.target.closest('[data-oegkm-lightbox]');
    if (!link) return;
    event.preventDefault();
    openImage(link);
  });

  document.addEventListener('click', function (event) {
    const videoButton = event.target.closest('.oegkm-video-placeholder');
    if (!videoButton) return;
    event.preventDefault();
    openVideo(videoButton);
  });

  closeButton.addEventListener('click', close);
  prevButton.addEventListener('click', function () {
    if (lightboxMode === 'image') setImage(currentIndex - 1);
  });
  nextButton.addEventListener('click', function () {
    if (lightboxMode === 'image') setImage(currentIndex + 1);
  });

  lightbox.addEventListener('click', function (event) {
    if (event.target === lightbox) close();
  });

  document.addEventListener('keydown', function (event) {
    if (!lightbox.classList.contains('is-open')) return;
    if (event.key === 'Escape') close();
    if (lightboxMode !== 'image') return;
    if (event.key === 'ArrowLeft') setImage(currentIndex - 1);
    if (event.key === 'ArrowRight') setImage(currentIndex + 1);
  });
})();
