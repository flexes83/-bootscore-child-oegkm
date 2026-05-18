(function (blocks, element, blockEditor, components) {
  var el = element.createElement;
  var Fragment = element.Fragment;
  var useBlockProps = blockEditor.useBlockProps;
  var RichText = blockEditor.RichText;
  var InspectorControls = blockEditor.InspectorControls;
  var PanelBody = components.PanelBody;
  var TextControl = components.TextControl;

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

  function renderBlock(attrs, blockProps, setAttributes) {
    return el('section', blockProps,
      el('div', { className: 'oegkm-podcast-cta__content' },
        setAttributes ?
          el(RichText, {
            tagName: 'div',
            className: 'oegkm-podcast-cta__kicker',
            value: attrs.kicker,
            placeholder: 'Label',
            onChange: function (value) { setAttributes({ kicker: value }); }
          }) :
          (attrs.kicker ? el(RichText.Content, { tagName: 'div', className: 'oegkm-podcast-cta__kicker', value: attrs.kicker }) : null),
        setAttributes ?
          el(RichText, {
            tagName: 'h2',
            className: 'oegkm-podcast-cta__title',
            value: attrs.title,
            placeholder: 'Headline',
            onChange: function (value) { setAttributes({ title: value }); }
          }) :
          el(RichText.Content, { tagName: 'h2', className: 'oegkm-podcast-cta__title', value: attrs.title }),
        setAttributes ?
          el(RichText, {
            tagName: 'p',
            className: 'oegkm-podcast-cta__text',
            value: attrs.text,
            placeholder: 'Text',
            onChange: function (value) { setAttributes({ text: value }); }
          }) :
          el(RichText.Content, { tagName: 'p', className: 'oegkm-podcast-cta__text', value: attrs.text }),
        el('a', { className: 'oegkm-podcast-cta__button', href: attrs.buttonUrl || '#' },
          setAttributes ?
            el(RichText, {
              tagName: 'span',
              value: attrs.buttonText,
              placeholder: 'Buttontext',
              onChange: function (value) { setAttributes({ buttonText: value }); }
            }) :
            el(RichText.Content, { tagName: 'span', value: attrs.buttonText }),
          arrowIcon()
        )
      ),
      el('div', { className: 'oegkm-podcast-cta__visual', 'aria-hidden': 'true' },
        el('div', { className: 'oegkm-podcast-cta__orb' })
      )
    );
  }

  blocks.registerBlockType('oegkm/podcast-cta', {
    edit: function (props) {
      var attrs = props.attributes;
      var blockProps = useBlockProps({ className: 'oegkm-podcast-cta oegkm-podcast-cta--editor' });

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
      var blockProps = blockEditor.useBlockProps.save({ className: 'oegkm-podcast-cta' });

      return renderBlock(attrs, blockProps, null);
    }
  });
})(window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components);
