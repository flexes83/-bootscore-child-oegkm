(function (blocks, element, blockEditor, components) {
  var el = element.createElement;
  var Fragment = element.Fragment;
  var useBlockProps = blockEditor.useBlockProps;
  var InspectorControls = blockEditor.InspectorControls;
  var PanelBody = components.PanelBody;
  var Button = components.Button;
  var TextControl = components.TextControl;
  var TextareaControl = components.TextareaControl;

  function chevronIcon(direction) {
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

  function arrowIcon() {
    return el('svg', {
      className: 'oegkm-button-arrow',
      viewBox: '0 0 20 20',
      width: 20,
      height: 20,
      'aria-hidden': 'true',
      focusable: 'false'
    },
      el('path', {
        d: 'M4 10h11',
        fill: 'none',
        stroke: 'currentColor',
        strokeWidth: 1.7,
        strokeLinecap: 'round'
      }),
      el('path', {
        d: 'm11 6 4 4-4 4',
        fill: 'none',
        stroke: 'currentColor',
        strokeWidth: 1.7,
        strokeLinecap: 'round',
        strokeLinejoin: 'round'
      })
    );
  }

  blocks.registerBlockType('oegkm/info-slider', {
    edit: function (props) {
      var attrs = props.attributes;
      var slides = attrs.slides || [];
      var blockProps = useBlockProps({ className: 'oegkm-info-slider oegkm-info-slider--editor' });

      function setSlides(slidesNext) {
        props.setAttributes({ slides: slidesNext });
      }

      function updateSlide(index, field, value) {
        var slidesNext = slides.slice();
        slidesNext[index] = Object.assign({}, slidesNext[index], { [field]: value });
        setSlides(slidesNext);
      }

      function addSlide() {
        if (slides.length >= 8) return;
        setSlides(slides.concat([{
          title: 'Neue Karte',
          text: 'Text ergänzen …',
          buttonText: '',
          buttonUrl: ''
        }]));
      }

      function removeSlide(index) {
        if (slides.length <= 1) return;
        var slidesNext = slides.slice();
        slidesNext.splice(index, 1);
        setSlides(slidesNext);
      }

      function hasButton(slide) {
        return !!(slide.buttonText || '').trim() && !!(slide.buttonUrl || '').trim();
      }

      return el(Fragment, {},
        el(InspectorControls, {},
          el(PanelBody, { title: 'Inhalt', initialOpen: true },
            el(TextControl, {
              label: 'Überschrift',
              value: attrs.heading || '',
              onChange: function (value) { props.setAttributes({ heading: value }); }
            })
          ),
          el(PanelBody, { title: 'Karten', initialOpen: true },
            slides.map(function (slide, index) {
              return el('div', { className: 'oegkm-info-slider-editor__card', key: index },
                el(TextControl, {
                  label: 'Titel',
                  value: slide.title || '',
                  onChange: function (value) { updateSlide(index, 'title', value); }
                }),
                el(TextareaControl, {
                  label: 'Text',
                  value: slide.text || '',
                  onChange: function (value) { updateSlide(index, 'text', value); }
                }),
                el(TextControl, {
                  label: 'Buttontext',
                  value: slide.buttonText || '',
                  onChange: function (value) { updateSlide(index, 'buttonText', value); }
                }),
                el(TextControl, {
                  label: 'Button URL',
                  value: slide.buttonUrl || '',
                  onChange: function (value) { updateSlide(index, 'buttonUrl', value); }
                }),
                slides.length > 1 ? el(Button, {
                  isDestructive: true,
                  onClick: function () { removeSlide(index); }
                }, 'Karte entfernen') : null
              );
            }),
            slides.length < 8 ? el(Button, { variant: 'primary', onClick: addSlide }, 'Karte hinzufügen') : null
          )
        ),
        el('section', blockProps,
          el('div', { className: 'oegkm-info-slider__header' },
            el('h2', { className: 'oegkm-info-slider__heading' }, attrs.heading || 'Überschrift'),
            el('div', { className: 'oegkm-info-slider__navs' },
              el('button', { type: 'button', className: 'oegkm-info-slider__nav oegkm-info-slider__nav--prev', disabled: true }, chevronIcon('prev')),
              el('button', { type: 'button', className: 'oegkm-info-slider__nav oegkm-info-slider__nav--next' }, chevronIcon('next'))
            )
          ),
          el('div', { className: 'oegkm-info-slider__viewport' },
            el('div', { className: 'oegkm-info-slider__track' },
              slides.map(function (slide, index) {
                return el('article', { className: 'oegkm-info-slider__card', key: index },
                  el('h3', { className: 'oegkm-info-slider__card-title' }, slide.title || 'Titel'),
                  el('p', { className: 'oegkm-info-slider__card-text' }, slide.text || 'Text'),
                  hasButton(slide) ? el('a', { className: 'oegkm-info-slider__button', href: slide.buttonUrl },
                    el('span', {}, slide.buttonText),
                    arrowIcon()
                  ) : null
                );
              })
            )
          )
        )
      );
    },
    save: function (props) {
      var attrs = props.attributes;
      var slides = attrs.slides || [];
      var blockProps = blockEditor.useBlockProps.save({ className: 'oegkm-info-slider', 'data-slides': String(slides.length || 0) });

      function hasButton(slide) {
        return !!(slide.buttonText || '').trim() && !!(slide.buttonUrl || '').trim();
      }

      return el('section', blockProps,
        el('div', { className: 'oegkm-info-slider__header' },
          el('h2', { className: 'oegkm-info-slider__heading' }, attrs.heading || ''),
          el('div', { className: 'oegkm-info-slider__navs' },
            el('button', { type: 'button', className: 'oegkm-info-slider__nav oegkm-info-slider__nav--prev', 'aria-label': 'Zurück' }, chevronIcon('prev')),
            el('button', { type: 'button', className: 'oegkm-info-slider__nav oegkm-info-slider__nav--next', 'aria-label': 'Weiter' }, chevronIcon('next'))
          )
        ),
        el('div', { className: 'oegkm-info-slider__viewport' },
          el('div', { className: 'oegkm-info-slider__track' },
            slides.map(function (slide, index) {
              return el('article', { className: 'oegkm-info-slider__card', key: index },
                el('h3', { className: 'oegkm-info-slider__card-title' }, slide.title || ''),
                el('p', { className: 'oegkm-info-slider__card-text' }, slide.text || ''),
                hasButton(slide) ? el('a', { className: 'oegkm-info-slider__button', href: slide.buttonUrl },
                  el('span', {}, slide.buttonText),
                  arrowIcon()
                ) : null
              );
            })
          )
        )
      );
    }
  });
})(window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components);
