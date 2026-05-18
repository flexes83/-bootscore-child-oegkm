(function (blocks, element, blockEditor, components) {
  var el = element.createElement;
  var Fragment = element.Fragment;
  var useBlockProps = blockEditor.useBlockProps;
  var RichText = blockEditor.RichText;
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
        d: direction === 'prev' ? 'M12.5 4.5 7 10l5.5 5.5' : 'M7.5 4.5 13 10l-5.5 5.5',
        fill: 'none',
        stroke: 'currentColor',
        strokeWidth: 1.8,
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
          buttonText: 'Mehr erfahren',
          buttonUrl: '#'
        }]));
      }

      function removeSlide(index) {
        if (slides.length <= 1) return;
        var slidesNext = slides.slice();
        slidesNext.splice(index, 1);
        setSlides(slidesNext);
      }

      return el(Fragment, {},
        el(InspectorControls, {},
          el(PanelBody, { title: 'Karten', initialOpen: true },
            slides.map(function (slide, index) {
              return el('div', { className: 'oegkm-info-slider-editor__card', key: index },
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
            el(RichText, {
              tagName: 'h2',
              className: 'oegkm-info-slider__heading',
              value: attrs.heading,
              placeholder: 'Überschrift',
              onChange: function (value) { props.setAttributes({ heading: value }); }
            }),
            el('div', { className: 'oegkm-info-slider__navs' },
              el('button', { type: 'button', className: 'oegkm-info-slider__nav oegkm-info-slider__nav--prev', disabled: true }, chevronIcon('prev')),
              el('button', { type: 'button', className: 'oegkm-info-slider__nav oegkm-info-slider__nav--next' }, chevronIcon('next'))
            )
          ),
          el('div', { className: 'oegkm-info-slider__viewport' },
            el('div', { className: 'oegkm-info-slider__track' },
              slides.map(function (slide, index) {
                return el('article', { className: 'oegkm-info-slider__card', key: index },
                  el(RichText, {
                    tagName: 'h3',
                    className: 'oegkm-info-slider__card-title',
                    value: slide.title,
                    placeholder: 'Titel',
                    onChange: function (value) { updateSlide(index, 'title', value); }
                  }),
                  el(TextareaControl, {
                    label: 'Text',
                    value: slide.text || '',
                    onChange: function (value) { updateSlide(index, 'text', value); }
                  }),
                  el('a', { className: 'oegkm-info-slider__button', href: slide.buttonUrl || '#' },
                    el(RichText, {
                      tagName: 'span',
                      value: slide.buttonText,
                      placeholder: 'Buttontext',
                      onChange: function (value) { updateSlide(index, 'buttonText', value); }
                    }),
                    chevronIcon('next')
                  )
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

      return el('section', blockProps,
        el('div', { className: 'oegkm-info-slider__header' },
          el(RichText.Content, { tagName: 'h2', className: 'oegkm-info-slider__heading', value: attrs.heading }),
          el('div', { className: 'oegkm-info-slider__navs' },
            el('button', { type: 'button', className: 'oegkm-info-slider__nav oegkm-info-slider__nav--prev', 'aria-label': 'Zurück' }, chevronIcon('prev')),
            el('button', { type: 'button', className: 'oegkm-info-slider__nav oegkm-info-slider__nav--next', 'aria-label': 'Weiter' }, chevronIcon('next'))
          )
        ),
        el('div', { className: 'oegkm-info-slider__viewport' },
          el('div', { className: 'oegkm-info-slider__track' },
            slides.map(function (slide, index) {
              return el('article', { className: 'oegkm-info-slider__card', key: index },
                el(RichText.Content, { tagName: 'h3', className: 'oegkm-info-slider__card-title', value: slide.title }),
                el('p', { className: 'oegkm-info-slider__card-text' }, slide.text || ''),
                el('a', { className: 'oegkm-info-slider__button', href: slide.buttonUrl || '#' },
                  el(RichText.Content, { tagName: 'span', value: slide.buttonText || 'Mehr erfahren' }),
                  chevronIcon('next')
                )
              );
            })
          )
        )
      );
    }
  });
})(window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components);
