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
  var TextControl = components.TextControl;

  function renderBlock(attrs, blockProps, setAttributes) {
    var mediaControls = setAttributes ? el('div', { className: 'oegkm-media-cta__media-actions' },
      el(MediaUploadCheck, {},
        el(MediaUpload, {
          onSelect: function (media) {
            setAttributes({
              imageUrl: media.url || '',
              imageId: media.id || 0
            });
          },
          allowedTypes: ['image'],
          value: attrs.imageId,
          render: function (obj) {
            return el(Button, { variant: 'secondary', onClick: obj.open }, attrs.imageUrl ? 'Bild ersetzen' : 'Bild auswählen');
          }
        })
      )
    ) : null;

    return el('section', blockProps,
      el('div', { className: 'oegkm-media-cta__grid' },
        el('figure', { className: 'oegkm-media-cta__media' },
          attrs.imageUrl ? el('img', { src: attrs.imageUrl, alt: '' }) : el('div', { className: 'oegkm-media-cta__placeholder' }, 'Bild wählen'),
          mediaControls
        ),
        el('div', { className: 'oegkm-media-cta__content' },
          setAttributes ?
            el(RichText, {
              tagName: 'div',
              className: 'oegkm-media-cta__kicker',
              value: attrs.kicker,
              placeholder: 'Label',
              onChange: function (value) { setAttributes({ kicker: value }); }
            }) :
            (attrs.kicker ? el(RichText.Content, { tagName: 'div', className: 'oegkm-media-cta__kicker', value: attrs.kicker }) : null),
          setAttributes ?
            el(RichText, {
              tagName: 'h2',
              className: 'oegkm-media-cta__title',
              value: attrs.title,
              placeholder: 'Headline',
              onChange: function (value) { setAttributes({ title: value }); }
            }) :
            el(RichText.Content, { tagName: 'h2', className: 'oegkm-media-cta__title', value: attrs.title }),
          setAttributes ?
            el(RichText, {
              tagName: 'p',
              className: 'oegkm-media-cta__text',
              value: attrs.text,
              placeholder: 'Text',
              onChange: function (value) { setAttributes({ text: value }); }
            }) :
            el(RichText.Content, { tagName: 'p', className: 'oegkm-media-cta__text', value: attrs.text }),
          el('a', { className: 'oegkm-media-cta__button', href: attrs.buttonUrl || '#' },
            setAttributes ?
              el(RichText, {
                tagName: 'span',
                value: attrs.buttonText,
                placeholder: 'Buttontext',
                onChange: function (value) { setAttributes({ buttonText: value }); }
              }) :
              el(RichText.Content, { tagName: 'span', value: attrs.buttonText }),
            el('span', { 'aria-hidden': 'true' }, '→')
          )
        )
      )
    );
  }

  blocks.registerBlockType('oegkm/media-cta', {
    edit: function (props) {
      var attrs = props.attributes;
      var blockProps = useBlockProps({ className: 'oegkm-media-cta oegkm-media-cta--editor' });

      return el(Fragment, {},
        el(InspectorControls, {},
          el(PanelBody, { title: 'Button', initialOpen: true },
            el(TextControl, {
              label: 'Button URL',
              value: attrs.buttonUrl || '',
              onChange: function (value) { props.setAttributes({ buttonUrl: value }); }
            })
          )
        ),
        renderBlock(attrs, blockProps, props.setAttributes)
      );
    },
    save: function (props) {
      var attrs = props.attributes;
      var blockProps = blockEditor.useBlockProps.save({ className: 'oegkm-media-cta' });

      return renderBlock(attrs, blockProps, null);
    }
  });
})(window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components);
