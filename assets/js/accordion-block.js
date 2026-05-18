
(function (blocks, element, blockEditor, components, i18n) {
  const el = element.createElement;
  const __ = i18n.__;
  const useBlockProps = blockEditor.useBlockProps;
  const InnerBlocks = blockEditor.InnerBlocks;
  const RichText = blockEditor.RichText;
  const InspectorControls = blockEditor.InspectorControls;
  const PanelBody = components.PanelBody;
  const ToggleControl = components.ToggleControl;

  blocks.registerBlockType('oegkm/accordion', {
    edit: function (props) {
      const blockProps = useBlockProps({ className: 'oegkm-accordion oegkm-accordion--editor' });
      return el(
        element.Fragment,
        {},
        el(
          InspectorControls,
          {},
          el(PanelBody, { title: __('Accordion settings', 'bootscore-child-oegkm'), initialOpen: true },
            el(ToggleControl, {
              label: __('Only one item open at a time', 'bootscore-child-oegkm'),
              checked: !!props.attributes.singleOpen,
              onChange: function (value) { props.setAttributes({ singleOpen: value }); }
            })
          )
        ),
        el('div', blockProps,
          el(InnerBlocks, {
            allowedBlocks: ['oegkm/accordion-item'],
            template: [['oegkm/accordion-item'], ['oegkm/accordion-item']],
            renderAppender: InnerBlocks.ButtonBlockAppender
          })
        )
      );
    },
    save: function (props) {
      const blockProps = blockEditor.useBlockProps.save({
        className: 'oegkm-accordion',
        'data-single-open': props.attributes.singleOpen ? 'true' : 'false'
      });
      return el('div', blockProps, el(InnerBlocks.Content));
    }
  });

  blocks.registerBlockType('oegkm/accordion-item', {
    edit: function (props) {
      const blockProps = useBlockProps({ className: 'oegkm-accordion__item' });
      return el(
        element.Fragment,
        {},
        el(
          InspectorControls,
          {},
          el(PanelBody, { title: __('Item settings', 'bootscore-child-oegkm'), initialOpen: true },
            el(ToggleControl, {
              label: __('Open by default', 'bootscore-child-oegkm'),
              checked: !!props.attributes.openByDefault,
              onChange: function (value) { props.setAttributes({ openByDefault: value }); }
            })
          )
        ),
        el('div', blockProps,
          el('div', { className: 'oegkm-accordion__toggle' + (props.attributes.openByDefault ? ' is-open' : '') },
            el(RichText, {
              tagName: 'div',
              className: 'oegkm-accordion__title',
              value: props.attributes.title,
              placeholder: __('Titel eingeben …', 'bootscore-child-oegkm'),
              onChange: function (value) { props.setAttributes({ title: value }); }
            }),
            el('span', { className: 'oegkm-accordion__icon', 'aria-hidden': 'true' }, props.attributes.openByDefault ? '−' : '+')
          ),
          el('div', { className: 'oegkm-accordion__panel' + (props.attributes.openByDefault ? ' is-open' : '') },
            el(InnerBlocks, {
              template: [['core/paragraph', { placeholder: __('Text zum Accordion-Inhalt …', 'bootscore-child-oegkm') }]],
              renderAppender: InnerBlocks.ButtonBlockAppender
            })
          )
        )
      );
    },
    save: function (props) {
      const blockProps = blockEditor.useBlockProps.save({
        className: 'oegkm-accordion__item' + (props.attributes.openByDefault ? ' is-open' : '')
      });
      return el('div', blockProps,
        el('button', {
            type: 'button',
            className: 'oegkm-accordion__toggle' + (props.attributes.openByDefault ? ' is-open' : ''),
            'aria-expanded': props.attributes.openByDefault ? 'true' : 'false'
          },
          el(RichText.Content, { tagName: 'span', className: 'oegkm-accordion__title', value: props.attributes.title }),
          el('span', { className: 'oegkm-accordion__icon', 'aria-hidden': 'true' }, props.attributes.openByDefault ? '−' : '+')
        ),
        el('div', { className: 'oegkm-accordion__panel' + (props.attributes.openByDefault ? ' is-open' : ''), hidden: !props.attributes.openByDefault },
          el('div', { className: 'oegkm-accordion__content' }, el(InnerBlocks.Content))
        )
      );
    }
  });
})(window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.i18n);
