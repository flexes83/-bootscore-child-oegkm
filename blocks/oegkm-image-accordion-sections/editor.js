(function (blocks, element, blockEditor, components) {
  var el = element.createElement;
  var Fragment = element.Fragment;
  var RawHTML = element.RawHTML;
  var useBlockProps = blockEditor.useBlockProps;
  var RichText = blockEditor.RichText;
  var MediaUpload = blockEditor.MediaUpload;
  var MediaUploadCheck = blockEditor.MediaUploadCheck;
  var InspectorControls = blockEditor.InspectorControls;
  var PanelBody = components.PanelBody;
  var Button = components.Button;
  var SelectControl = components.SelectControl;
  var TextControl = components.TextControl;
  var TextareaControl = components.TextareaControl;

  function toLines(value) {
    return (value || '').split('\n').map(function (line, index) {
      return index === 0 ? line : [el('br', { key: 'br-' + index }), line];
    });
  }

  function containsHtml(value) {
    return /<\/?[a-z][\s\S]*>/i.test(value || '');
  }

  function renderBody(value) {
    if (!value) {
      return null;
    }

    return containsHtml(value) ? el(RawHTML, {}, value) : toLines(value);
  }

  function plusIcon() {
    return el('span', { className: 'oegkm-image-accordion__icon', 'aria-hidden': 'true' });
  }

  function renderItem(item, sectionIndex, itemIndex, isEditor, updateItem) {
    var isOpen = !!item.openByDefault;
    return el('div', {
      className: 'oegkm-image-accordion__item' + (isOpen ? ' is-open' : ''),
      key: itemIndex
    },
      el('button', {
        type: 'button',
        className: 'oegkm-image-accordion__toggle' + (isOpen ? ' is-open' : ''),
        'aria-expanded': isOpen ? 'true' : 'false'
      },
        isEditor ? el(RichText, {
          tagName: 'span',
          className: 'oegkm-image-accordion__item-title',
          value: item.title,
          placeholder: 'Accordion-Titel',
          onChange: function (value) { updateItem(sectionIndex, itemIndex, 'title', value); }
        }) : el(RichText.Content, { tagName: 'span', className: 'oegkm-image-accordion__item-title', value: item.title }),
        plusIcon()
      ),
      el('div', {
        className: 'oegkm-image-accordion__panel' + (isOpen ? ' is-open' : ''),
        hidden: isOpen ? undefined : true
      },
        el('div', { className: 'oegkm-image-accordion__panel-content' },
          isEditor ? el(TextareaControl, {
            label: 'Accordion-Inhalt',
            value: item.body || '',
            onChange: function (value) { updateItem(sectionIndex, itemIndex, 'body', value); }
          }) : el('div', { className: 'oegkm-image-accordion__panel-text' }, renderBody(item.body || ''))
        )
      )
    );
  }

  function renderSection(section, sectionIndex, isEditor, helpers) {
    var image = el('figure', { className: 'oegkm-image-accordion__media' },
      section.imageUrl ? el('img', { src: section.imageUrl, alt: '' }) : el('div', { className: 'oegkm-image-accordion__placeholder' }, 'Bild wählen'),
      isEditor ? el('div', { className: 'oegkm-image-accordion__media-actions' },
        el(MediaUploadCheck, {},
          el(MediaUpload, {
            onSelect: function (media) {
              helpers.updateSection(sectionIndex, {
                imageUrl: media.url || '',
                imageId: media.id || 0
              });
            },
            allowedTypes: ['image'],
            value: section.imageId,
            render: function (obj) {
              return el(Button, { variant: 'secondary', onClick: obj.open }, section.imageUrl ? 'Bild ersetzen' : 'Bild auswählen');
            }
          })
        )
      ) : null
    );

    var content = el('div', { className: 'oegkm-image-accordion__content' },
      isEditor ? el(RichText, {
        tagName: 'div',
        className: 'oegkm-image-accordion__kicker',
        value: section.kicker,
        placeholder: 'Label',
        onChange: function (value) { helpers.updateField(sectionIndex, 'kicker', value); }
      }) : (section.kicker ? el(RichText.Content, { tagName: 'div', className: 'oegkm-image-accordion__kicker', value: section.kicker }) : null),
      isEditor ? el(RichText, {
        tagName: 'h2',
        className: 'oegkm-image-accordion__title',
        value: section.title,
        placeholder: 'Headline',
        onChange: function (value) { helpers.updateField(sectionIndex, 'title', value); }
      }) : el(RichText.Content, { tagName: 'h2', className: 'oegkm-image-accordion__title', value: section.title }),
      isEditor ? el(RichText, {
        tagName: 'p',
        className: 'oegkm-image-accordion__text',
        value: section.text,
        placeholder: 'Text',
        onChange: function (value) { helpers.updateField(sectionIndex, 'text', value); }
      }) : el(RichText.Content, { tagName: 'p', className: 'oegkm-image-accordion__text', value: section.text }),
      el('div', { className: 'oegkm-image-accordion__items' },
        (section.items || []).map(function (item, itemIndex) {
          return renderItem(item, sectionIndex, itemIndex, isEditor, helpers.updateItem);
        })
      )
    );

    return el('section', {
      className: 'oegkm-image-accordion__section oegkm-image-accordion__section--image-' + (section.imagePosition || 'right'),
      key: sectionIndex
    },
      section.imagePosition === 'left' ? [image, content] : [content, image]
    );
  }

  blocks.registerBlockType('oegkm/image-accordion-sections', {
    edit: function (props) {
      var attrs = props.attributes;
      var sections = attrs.sections || [];
      var blockProps = useBlockProps({ className: 'oegkm-image-accordion oegkm-image-accordion--editor' });

      function setSections(nextSections) {
        props.setAttributes({ sections: nextSections });
      }

      function updateSection(index, values) {
        var nextSections = sections.slice();
        nextSections[index] = Object.assign({}, nextSections[index], values);
        setSections(nextSections);
      }

      function updateField(index, field, value) {
        updateSection(index, Object.assign({}, { [field]: value }));
      }

      function updateItem(sectionIndex, itemIndex, field, value) {
        var nextSections = sections.slice();
        var items = (nextSections[sectionIndex].items || []).slice();
        items[itemIndex] = Object.assign({}, items[itemIndex], { [field]: value });
        nextSections[sectionIndex] = Object.assign({}, nextSections[sectionIndex], { items: items });
        setSections(nextSections);
      }

      function addSection() {
        setSections(sections.concat([{
          kicker: '',
          title: 'Neue Sektion',
          text: 'Text ergänzen.',
          imageUrl: '',
          imageId: 0,
          imagePosition: sections.length % 2 ? 'right' : 'left',
          items: [{ title: 'Neuer Accordion-Punkt', body: 'Inhalt ergänzen.' }]
        }]));
      }

      function removeSection(index) {
        if (sections.length <= 1) return;
        var nextSections = sections.slice();
        nextSections.splice(index, 1);
        setSections(nextSections);
      }

      function addItem(sectionIndex) {
        var nextSections = sections.slice();
        var items = (nextSections[sectionIndex].items || []).slice();
        items.push({ title: 'Neuer Accordion-Punkt', body: 'Inhalt ergänzen.' });
        nextSections[sectionIndex] = Object.assign({}, nextSections[sectionIndex], { items: items });
        setSections(nextSections);
      }

      function removeItem(sectionIndex, itemIndex) {
        var nextSections = sections.slice();
        var items = (nextSections[sectionIndex].items || []).slice();
        items.splice(itemIndex, 1);
        nextSections[sectionIndex] = Object.assign({}, nextSections[sectionIndex], { items: items });
        setSections(nextSections);
      }

      var helpers = {
        updateSection: updateSection,
        updateField: updateField,
        updateItem: updateItem
      };

      return el(Fragment, {},
        el(InspectorControls, {},
          el(PanelBody, { title: 'Sektionen', initialOpen: true },
            sections.map(function (section, sectionIndex) {
              return el('div', { className: 'oegkm-image-accordion-editor__section', key: sectionIndex },
                el(TextControl, {
                  label: 'Sektionstitel',
                  value: section.title || '',
                  onChange: function (value) { updateField(sectionIndex, 'title', value); }
                }),
                el(SelectControl, {
                  label: 'Bildposition',
                  value: section.imagePosition || 'right',
                  options: [
                    { label: 'Bild rechts', value: 'right' },
                    { label: 'Bild links', value: 'left' }
                  ],
                  onChange: function (value) { updateField(sectionIndex, 'imagePosition', value); }
                }),
                el(Button, { variant: 'secondary', onClick: function () { addItem(sectionIndex); } }, 'Accordion-Punkt hinzufügen'),
                (section.items || []).map(function (item, itemIndex) {
                  return el('div', { className: 'oegkm-image-accordion-editor__item', key: itemIndex },
                    el(TextControl, {
                      label: 'Accordion-Titel',
                      value: item.title || '',
                      onChange: function (value) { updateItem(sectionIndex, itemIndex, 'title', value); }
                    }),
                    el(TextareaControl, {
                      label: 'Accordion-Inhalt',
                      value: item.body || '',
                      onChange: function (value) { updateItem(sectionIndex, itemIndex, 'body', value); }
                    }),
                    (section.items || []).length > 1 ? el(Button, {
                      isDestructive: true,
                      onClick: function () { removeItem(sectionIndex, itemIndex); }
                    }, 'Punkt entfernen') : null
                  );
                }),
                sections.length > 1 ? el(Button, { isDestructive: true, onClick: function () { removeSection(sectionIndex); } }, 'Sektion entfernen') : null
              );
            }),
            el(Button, { variant: 'primary', onClick: addSection }, 'Sektion hinzufügen')
          )
        ),
        el('div', blockProps,
          sections.map(function (section, sectionIndex) {
            return renderSection(section, sectionIndex, true, helpers);
          })
        )
      );
    },
    save: function (props) {
      var sections = props.attributes.sections || [];
      var blockProps = blockEditor.useBlockProps.save({ className: 'oegkm-image-accordion' });
      return el('div', blockProps,
        sections.map(function (section, sectionIndex) {
          return renderSection(section, sectionIndex, false, {});
        })
      );
    }
  });
})(window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components);
