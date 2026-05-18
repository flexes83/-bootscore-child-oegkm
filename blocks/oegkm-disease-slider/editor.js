(function (blocks, element, blockEditor, components) {
  var el = element.createElement;
  var Fragment = element.Fragment;
  var useBlockProps = blockEditor.useBlockProps;
  var RichText = blockEditor.RichText;
  var MediaUpload = blockEditor.MediaUpload;
  var MediaUploadCheck = blockEditor.MediaUploadCheck;
  var InspectorControls = blockEditor.InspectorControls;
  var PanelBody = components.PanelBody;
  var Button = components.Button;
  var TextareaControl = components.TextareaControl;

  function ChevronIcon(direction) {
    return el('svg', {
      className: 'oegkm-slider-chevron',
      viewBox: '0 0 20 20',
      width: 20,
      height: 20,
      'aria-hidden': 'true',
      focusable: 'false'
    },
      el('path', {
        d: direction === 'prev' ? 'M12.5 4.5 7 10l5.5 5.5' : 'M7.5 4.5 13 10l-5.5 5.5',
        fill: 'none',
        stroke: 'currentColor',
        strokeWidth: 1.8,
        strokeLinecap: 'round',
        strokeLinejoin: 'round'
      })
    );
  }

  function clampIndex(index, length) {
    if (!length) return 0;
    return Math.max(0, Math.min(index, length - 1));
  }

  blocks.registerBlockType('oegkm/disease-slider', {
    edit: function (props) {
      var attributes = props.attributes;
      var slides = attributes.slides || [];
      var activeSlide = clampIndex(attributes.activeSlide || 0, slides.length);
      var currentSlide = slides[activeSlide] || null;
      var blockProps = useBlockProps({ className: 'oegkm-disease-slider oegkm-disease-slider--editor' });

      function setSlides(nextSlides, nextActiveSlide) {
        props.setAttributes({
          slides: nextSlides,
          activeSlide: typeof nextActiveSlide === 'number' ? clampIndex(nextActiveSlide, nextSlides.length) : clampIndex(activeSlide, nextSlides.length)
        });
      }

      function updateSlide(index, field, value) {
        var nextSlides = slides.slice();
        nextSlides[index] = Object.assign({}, nextSlides[index], { [field]: value });
        setSlides(nextSlides);
      }

      function addSlide() {
        var nextSlides = slides.concat([{
          kicker: 'NEUER ABSCHNITT',
          title: 'Neue Slide',
          text: 'Text ergänzen …',
          imageUrl: '',
          imageId: 0
        }]);
        setSlides(nextSlides, nextSlides.length - 1);
      }

      function removeSlide(index) {
        if (slides.length <= 1) return;
        var nextSlides = slides.slice();
        nextSlides.splice(index, 1);
        setSlides(nextSlides, Math.max(0, index - 1));
      }

      return el(Fragment, {},
        el(InspectorControls, {},
          el(PanelBody, { title: 'Slides', initialOpen: true },
            slides.map(function (slide, index) {
              return el('div', { className: 'oegkm-disease-slider-editor__slide-row', key: index },
                el(Button, {
                  variant: activeSlide === index ? 'primary' : 'secondary',
                  onClick: function () { props.setAttributes({ activeSlide: index }); }
                }, 'Slide ' + (index + 1)),
                slides.length > 1 ? el(Button, {
                  isDestructive: true,
                  onClick: function () { removeSlide(index); }
                }, 'Löschen') : null
              );
            }),
            el(Button, { variant: 'primary', onClick: addSlide }, 'Slide hinzufügen')
          )
        ),
        currentSlide ? el('section', blockProps,
          el('div', { className: 'oegkm-disease-slider__inner' },
            el('div', { className: 'oegkm-disease-slider__content' },
              el(RichText, {
                tagName: 'div',
                className: 'oegkm-disease-slider__kicker',
                value: currentSlide.kicker,
                placeholder: 'Kicker',
                onChange: function (value) { updateSlide(activeSlide, 'kicker', value); }
              }),
              el(RichText, {
                tagName: 'h2',
                className: 'oegkm-disease-slider__title',
                value: currentSlide.title,
                placeholder: 'Headline',
                onChange: function (value) { updateSlide(activeSlide, 'title', value); }
              }),
              el(TextareaControl, {
                label: 'Text',
                value: currentSlide.text || '',
                onChange: function (value) { updateSlide(activeSlide, 'text', value); }
              }),
              el('div', { className: 'oegkm-disease-slider__controls' },
                el('button', {
                  type: 'button',
                  className: 'oegkm-disease-slider__nav',
                  onClick: function () { props.setAttributes({ activeSlide: clampIndex(activeSlide - 1, slides.length) }); },
                  disabled: slides.length <= 1
                }, ChevronIcon('prev')),
                el('button', {
                  type: 'button',
                  className: 'oegkm-disease-slider__nav',
                  onClick: function () { props.setAttributes({ activeSlide: clampIndex(activeSlide + 1, slides.length) }); },
                  disabled: slides.length <= 1
                }, ChevronIcon('next'))
              )
            ),
            el('div', { className: 'oegkm-disease-slider__media' },
              currentSlide.imageUrl ?
                el('img', { src: currentSlide.imageUrl, alt: '' }) :
                el('div', { className: 'oegkm-disease-slider__image-placeholder' }, 'Bild wählen'),
              el('div', { className: 'oegkm-disease-slider__media-actions' },
                el(MediaUploadCheck, {},
                  el(MediaUpload, {
                    onSelect: function (media) {
                      updateSlide(activeSlide, 'imageUrl', media.url || '');
                      updateSlide(activeSlide, 'imageId', media.id || 0);
                    },
                    allowedTypes: ['image'],
                    value: currentSlide.imageId,
                    render: function (obj) {
                      return el(Button, { variant: 'secondary', onClick: obj.open }, currentSlide.imageUrl ? 'Bild ersetzen' : 'Bild auswählen');
                    }
                  })
                )
              )
            )
          )
        ) : null
      );
    },
    save: function (props) {
      var slides = props.attributes.slides || [];
      var blockProps = blockEditor.useBlockProps.save({ className: 'oegkm-disease-slider' });

      return el('section', blockProps,
        el('div', { className: 'oegkm-disease-slider__track' },
          slides.map(function (slide, index) {
            return el('article', {
              className: 'oegkm-disease-slider__slide' + (index === 0 ? ' is-active' : ''),
              hidden: index === 0 ? undefined : true,
              key: index
            },
              el('div', { className: 'oegkm-disease-slider__inner' },
                el('div', { className: 'oegkm-disease-slider__content' },
                  slide.kicker ? el(RichText.Content, { tagName: 'div', className: 'oegkm-disease-slider__kicker', value: slide.kicker }) : null,
                  el(RichText.Content, { tagName: 'h2', className: 'oegkm-disease-slider__title', value: slide.title }),
                  el(RichText.Content, { tagName: 'div', className: 'oegkm-disease-slider__text', value: slide.text }),
                  slides.length > 1 ? el('div', { className: 'oegkm-disease-slider__controls' },
                    el('button', { type: 'button', className: 'oegkm-disease-slider__nav oegkm-disease-slider__nav--prev', 'aria-label': 'Zurück' }, ChevronIcon('prev')),
                    el('button', { type: 'button', className: 'oegkm-disease-slider__nav oegkm-disease-slider__nav--next', 'aria-label': 'Weiter' }, ChevronIcon('next'))
                  ) : null
                ),
                el('div', { className: 'oegkm-disease-slider__media' },
                  slide.imageUrl ? el('img', { src: slide.imageUrl, alt: '' }) : el('div', { className: 'oegkm-disease-slider__image-placeholder' }, '')
                )
              )
            );
          })
        )
      );
    }
  });
})(window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components);
