
(function (blocks, element, blockEditor, components) {
  const el = element.createElement;
  const useBlockProps = blockEditor.useBlockProps;
  const RichText = blockEditor.RichText;
  const MediaUpload = blockEditor.MediaUpload;
  const MediaUploadCheck = blockEditor.MediaUploadCheck;
  const InspectorControls = blockEditor.InspectorControls;
  const PanelBody = components.PanelBody;
  const Button = components.Button;
  const TextareaControl = components.TextareaControl;

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

  function ImageField(props) {
    return el('div', { className: 'oegkm-ziele-editor__imagefield' },
      el('label', {}, props.label),
      el(MediaUploadCheck, {},
        el(MediaUpload, {
          onSelect: function(media){
            props.onSelect({ url: media.url || '', id: media.id || 0 });
          },
          allowedTypes: ['image'],
          value: props.id,
          render: function(obj){
            return el(Button, { variant: 'secondary', onClick: obj.open }, props.url ? 'Bild ersetzen' : 'Bild auswählen');
          }
        })
      ),
      props.url ? el('div', { className: 'oegkm-ziele-editor__thumb' }, el('img', { src: props.url, alt: '' })) : null
    );
  }

  blocks.registerBlockType('oegkm/ziele', {
    edit: function (props) {
      const attrs = props.attributes;
      const blockProps = useBlockProps({ className: 'oegkm-ziele' });

      function updateCard(index, field, value) {
        const cards = (attrs.cards || []).slice();
        cards[index] = Object.assign({}, cards[index], { [field]: value });
        props.setAttributes({ cards: cards });
      }

      function addCard() {
        const cards = (attrs.cards || []).slice();
        if (cards.length >= 4) return;
        cards.push({ title: 'Neue Karte', text: 'Text ergänzen …' });
        props.setAttributes({ cards: cards });
      }

      function removeCard(index) {
        const cards = (attrs.cards || []).slice();
        cards.splice(index, 1);
        props.setAttributes({ cards: cards });
      }

      return el(element.Fragment, {},
        el(InspectorControls, {},
          el(PanelBody, { title: 'Bilder', initialOpen: true },
            el(ImageField, {
              label: 'Bild oben links',
              url: attrs.image1Url,
              id: attrs.image1Id,
              onSelect: function(media){ props.setAttributes({ image1Url: media.url, image1Id: media.id }); }
            }),
            el(ImageField, {
              label: 'Bild Mitte',
              url: attrs.image2Url,
              id: attrs.image2Id,
              onSelect: function(media){ props.setAttributes({ image2Url: media.url, image2Id: media.id }); }
            }),
            el(ImageField, {
              label: 'Bild unten links',
              url: attrs.image3Url,
              id: attrs.image3Id,
              onSelect: function(media){ props.setAttributes({ image3Url: media.url, image3Id: media.id }); }
            })
          ),
          el(PanelBody, { title: 'Karten', initialOpen: false },
            (attrs.cards || []).map(function(card, index){
              return el('div', { className: 'oegkm-ziele-editor__card', key: index },
                el(RichText, {
                  tagName: 'h4',
                  value: card.title,
                  placeholder: 'Kartentitel',
                  onChange: function(value){ updateCard(index, 'title', value); }
                }),
                el(TextareaControl, {
                  label: 'Text',
                  value: card.text || '',
                  onChange: function(value){ updateCard(index, 'text', value); }
                }),
                el(Button, { isDestructive: true, onClick: function(){ removeCard(index); } }, 'Karte entfernen')
              );
            }),
            (attrs.cards || []).length < 4 ? el(Button, { variant: 'primary', onClick: addCard }, 'Karte hinzufügen') : null
          )
        ),
        el('section', blockProps,
          el('div', { className: 'oegkm-ziele__grid' },
            el('div', { className: 'oegkm-ziele__images' },
              el('div', { className: 'oegkm-ziele__image oegkm-ziele__image--top' }, attrs.image1Url ? el('img', { src: attrs.image1Url, alt: '' }) : el('span', {}, 'Bild oben links')),
              el('div', { className: 'oegkm-ziele__image oegkm-ziele__image--main' }, attrs.image2Url ? el('img', { src: attrs.image2Url, alt: '' }) : el('span', {}, 'Bild Mitte')),
              el('div', { className: 'oegkm-ziele__image oegkm-ziele__image--bottom' }, attrs.image3Url ? el('img', { src: attrs.image3Url, alt: '' }) : el('span', {}, 'Bild unten links'))
            ),
            el('div', { className: 'oegkm-ziele__content' },
              el('div', { className: 'oegkm-kicker' }, 'Unsere Ziele'),
              el(RichText, {
                tagName: 'h2',
                className: 'oegkm-ziele__title',
                value: attrs.title,
                placeholder: 'Headline',
                onChange: function(value){ props.setAttributes({ title: value }); }
              }),
              el(RichText, {
                tagName: 'p',
                className: 'oegkm-ziele__text',
                value: attrs.text,
                placeholder: 'Text',
                onChange: function(value){ props.setAttributes({ text: value }); }
              }),
              el('div', { className: 'oegkm-ziele__slider oegkm-ziele__slider--editor' },
                (attrs.cards || []).map(function(card, index){
                  return el('article', { className: 'oegkm-ziele__card', key: index },
                    el(RichText, {
                      tagName: 'h3',
                      className: 'oegkm-ziele__card-title',
                      value: card.title,
                      placeholder: 'Kartentitel',
                      onChange: function(value){ updateCard(index, 'title', value); }
                    }),
                    el('p', { className: 'oegkm-ziele__card-text' }, card.text || '')
                  );
                })
              ),
              el('div', { className: 'oegkm-ziele__navs' },
                el('span', { className: 'oegkm-ziele__nav oegkm-ziele__nav--prev' }, ChevronIcon('prev')),
                el('span', { className: 'oegkm-ziele__nav oegkm-ziele__nav--next' }, ChevronIcon('next'))
              )
            )
          )
        )
      );
    },
    save: function (props) {
      const a = props.attributes;
      const blockProps = blockEditor.useBlockProps.save({ className: 'oegkm-ziele', 'data-cards': String((a.cards || []).length || 0) });
      return el('section', blockProps,
        el('div', { className: 'oegkm-ziele__grid' },
          el('div', { className: 'oegkm-ziele__images' },
            el('div', { className: 'oegkm-ziele__image oegkm-ziele__image--top' }, a.image1Url ? el('img', { src: a.image1Url, alt: '' }) : null),
            el('div', { className: 'oegkm-ziele__image oegkm-ziele__image--main' }, a.image2Url ? el('img', { src: a.image2Url, alt: '' }) : null),
            el('div', { className: 'oegkm-ziele__image oegkm-ziele__image--bottom' }, a.image3Url ? el('img', { src: a.image3Url, alt: '' }) : null)
          ),
          el('div', { className: 'oegkm-ziele__content' },
            el('div', { className: 'oegkm-kicker' }, 'Unsere Ziele'),
            el(RichText.Content, { tagName: 'h2', className: 'oegkm-ziele__title', value: a.title }),
            el(RichText.Content, { tagName: 'p', className: 'oegkm-ziele__text', value: a.text }),
            el('div', { className: 'oegkm-ziele__slider-wrap' },
              el('div', { className: 'oegkm-ziele__viewport' },
                el('div', { className: 'oegkm-ziele__track' },
                  (a.cards || []).map(function(card, index){
                    return el('article', { className: 'oegkm-ziele__card', key: index },
                      el(RichText.Content, { tagName: 'h3', className: 'oegkm-ziele__card-title', value: card.title }),
                      el('p', { className: 'oegkm-ziele__card-text' }, card.text || '')
                    );
                  })
                )
              ),
              el('div', { className: 'oegkm-ziele__navs' },
                el('button', { type: 'button', className: 'oegkm-ziele__nav oegkm-ziele__nav--prev', 'aria-label': 'Zurück' }, ChevronIcon('prev')),
                el('button', { type: 'button', className: 'oegkm-ziele__nav oegkm-ziele__nav--next', 'aria-label': 'Weiter' }, ChevronIcon('next'))
              )
            )
          )
        )
      );
    }
  });
})(window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components);
