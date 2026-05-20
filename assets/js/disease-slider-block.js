(function (blocks, element, blockEditor, components) {
  const el = element.createElement;
  const Fragment = element.Fragment;
  const useBlockProps = blockEditor.useBlockProps;
  const RichText = blockEditor.RichText;
  const MediaUpload = blockEditor.MediaUpload;
  const MediaUploadCheck = blockEditor.MediaUploadCheck;
  const InspectorControls = blockEditor.InspectorControls;
  const PanelBody = components.PanelBody;
  const Button = components.Button;
  const ToggleControl = components.ToggleControl;
  const textFormats = ['core/bold', 'core/link'];

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
        d: direction === 'prev' ? 'M11.5 5 6.5 10l5 5' : 'M8.5 5l5 5-5 5',
        fill: 'none',
        stroke: 'currentColor',
        strokeWidth: 1.6,
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
      const attributes = props.attributes;
      const slides = attributes.slides || [];
      const activeSlide = clampIndex(attributes.activeSlide || 0, slides.length);
      const currentSlide = slides[activeSlide] || null;
      const blockProps = useBlockProps({ className: 'oegkm-disease-slider oegkm-disease-slider--editor' });

      function setSlides(nextSlides, nextActiveSlide) {
        props.setAttributes({
          slides: nextSlides,
          activeSlide: typeof nextActiveSlide === 'number' ? clampIndex(nextActiveSlide, nextSlides.length) : clampIndex(activeSlide, nextSlides.length)
        });
      }

      function updateSlide(index, field, value) {
        const nextSlides = slides.slice();
        nextSlides[index] = Object.assign({}, nextSlides[index], { [field]: value });
        setSlides(nextSlides);
      }

      function addSlide() {
        if (slides.length >= 8) return;
        const nextSlides = slides.concat([{ kicker: 'NEUER ABSCHNITT', title: 'Neue Slide', text: 'Text ergänzen …', textAsList: false, imageUrl: '', imageId: 0 }]);
        setSlides(nextSlides, nextSlides.length - 1);
      }

      function removeSlide(index) {
        if (slides.length <= 1) return;
        const nextSlides = slides.slice();
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
                }, 'Löschen') : null,
                el(ToggleControl, {
                  label: 'Text als Liste',
                  checked: !!slide.textAsList,
                  onChange: function (value) { updateSlide(index, 'textAsList', value); }
                })
              );
            }),
            slides.length < 8 ? el(Button, { variant: 'primary', onClick: addSlide }, 'Slide hinzufügen') : null
          )
        ),
        currentSlide ? el('section', blockProps,
          el('div', { className: 'oegkm-disease-slider__stage' },
            el('article', { className: 'oegkm-disease-slider__slide is-active' },
              el('div', { className: 'oegkm-disease-slider__panel' },
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
                el(RichText, {
                  tagName: currentSlide.textAsList ? 'ul' : 'div',
                  className: 'oegkm-disease-slider__text',
                  multiline: currentSlide.textAsList ? 'li' : undefined,
                  value: currentSlide.text || '',
                  allowedFormats: textFormats,
                  placeholder: 'Text',
                  onChange: function (value) { updateSlide(activeSlide, 'text', value); }
                }),
                el('div', { className: 'oegkm-disease-slider__navs' },
                  el('button', {
                    type: 'button',
                    className: 'oegkm-disease-slider__nav oegkm-disease-slider__nav--prev',
                    disabled: activeSlide === 0,
                    onClick: function () { props.setAttributes({ activeSlide: Math.max(0, activeSlide - 1) }); }
                  }, ChevronIcon('prev')),
                  el('button', {
                    type: 'button',
                    className: 'oegkm-disease-slider__nav oegkm-disease-slider__nav--next',
                    disabled: activeSlide >= slides.length - 1,
                    onClick: function () { props.setAttributes({ activeSlide: Math.min(slides.length - 1, activeSlide + 1) }); }
                  }, ChevronIcon('next'))
                )
              ),
              el('div', { className: 'oegkm-disease-slider__media' },
                currentSlide.imageUrl ? el('img', { src: currentSlide.imageUrl, alt: '' }) : el('div', { className: 'oegkm-disease-slider__image-placeholder' }, 'Bild wählen'),
                el('div', { className: 'oegkm-disease-slider__media-actions' },
                  el(MediaUploadCheck, {},
                    el(MediaUpload, {
                      onSelect: function (media) {
                        const nextSlides = slides.slice();
                        nextSlides[activeSlide] = Object.assign({}, nextSlides[activeSlide], {
                          imageUrl: media.url || '',
                          imageId: media.id || 0
                        });
                        setSlides(nextSlides);
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
          )
        ) : null
      );
    },
    save: function (props) {
      const slides = props.attributes.slides || [];
      const blockProps = blockEditor.useBlockProps.save({ className: 'oegkm-disease-slider', 'data-slides': String(slides.length || 0) });

      return el('section', blockProps,
        el('div', { className: 'oegkm-disease-slider__stage' },
          slides.map(function (slide, index) {
            return el('article', {
              className: 'oegkm-disease-slider__slide' + (index === 0 ? ' is-active' : ''),
              'aria-hidden': index === 0 ? 'false' : 'true',
              key: index
            },
              el('div', { className: 'oegkm-disease-slider__panel' },
                slide.kicker ? el(RichText.Content, { tagName: 'div', className: 'oegkm-disease-slider__kicker', value: slide.kicker }) : null,
                el(RichText.Content, { tagName: 'h2', className: 'oegkm-disease-slider__title', value: slide.title }),
                el(RichText.Content, {
                  tagName: slide.textAsList ? 'ul' : 'div',
                  className: 'oegkm-disease-slider__text',
                  multiline: slide.textAsList ? 'li' : undefined,
                  value: slide.text
                }),
                el('div', { className: 'oegkm-disease-slider__navs' },
                  el('button', { type: 'button', className: 'oegkm-disease-slider__nav oegkm-disease-slider__nav--prev', 'aria-label': 'Zurück' }, ChevronIcon('prev')),
                  el('button', { type: 'button', className: 'oegkm-disease-slider__nav oegkm-disease-slider__nav--next', 'aria-label': 'Weiter' }, ChevronIcon('next'))
                )
              ),
              el('div', { className: 'oegkm-disease-slider__media' }, slide.imageUrl ? el('img', { src: slide.imageUrl, alt: '' }) : null)
            );
          })
        )
      );
    }
  });
})(window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components);
