(function (blocks, element, blockEditor, components) {
  var el = element.createElement;
  var Fragment = element.Fragment;
  var useState = element.useState;
  var useBlockProps = blockEditor.useBlockProps;
  var BlockControls = blockEditor.BlockControls;
  var RichText = blockEditor.RichText;
  var MediaUpload = blockEditor.MediaUpload;
  var MediaUploadCheck = blockEditor.MediaUploadCheck;
  var InspectorControls = blockEditor.InspectorControls;
  var PanelBody = components.PanelBody;
  var Button = components.Button;
  var SelectControl = components.SelectControl;
  var TextControl = components.TextControl;
  var ToolbarButton = components.ToolbarButton;
  var ToolbarGroup = components.ToolbarGroup;
  var textFormats = ['core/bold', 'core/link'];

  function splitRichTextLines(value) {
    var html = (value || '').trim();
    var matches;

    if (!html) return [];

    if (/<li[\s>]/i.test(html)) {
      matches = html.match(/<li[^>]*>[\s\S]*?<\/li>/gi) || [];
      return matches.map(function (item) {
        return item.replace(/^<li[^>]*>/i, '').replace(/<\/li>$/i, '').trim();
      }).filter(Boolean);
    }

    if (/<p[\s>]/i.test(html)) {
      matches = html.match(/<p[^>]*>[\s\S]*?<\/p>/gi) || [];
      return matches.map(function (item) {
        return item.replace(/^<p[^>]*>/i, '').replace(/<\/p>$/i, '').trim();
      }).filter(Boolean);
    }

    return html.split(/\n|<br\s*\/?>/i).map(function (item) {
      return item.trim();
    }).filter(Boolean);
  }

  function toListValue(value) {
    return splitRichTextLines(value).map(function (item) {
      return '<li>' + item + '</li>';
    }).join('');
  }

  function fromListValue(value) {
    return splitRichTextLines(value).join('<br>');
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
        d: 'M3.5 10h12m0 0-4.5-4.5M15.5 10 11 14.5',
        fill: 'none',
        stroke: 'currentColor',
        strokeWidth: 1.5,
        strokeLinecap: 'round',
        strokeLinejoin: 'round'
      })
    );
  }

  function hasButton(section) {
    return !!(section.buttonText || '').trim() && !!(section.buttonUrl || '').trim();
  }

  function renderSection(section, index, isEditor, helpers) {
    var image = el('figure', { className: 'oegkm-image-content__media' },
      section.imageUrl ? el('img', { src: section.imageUrl, alt: '' }) : el('div', { className: 'oegkm-image-content__placeholder' }, 'Bild wählen'),
      isEditor ? el('div', { className: 'oegkm-image-content__media-actions' },
        el(MediaUploadCheck, {},
          el(MediaUpload, {
            onSelect: function (media) {
              helpers.updateSection(index, {
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

    var content = el('div', { className: 'oegkm-image-content__content' },
      isEditor ? el(RichText, {
        tagName: 'div',
        className: 'oegkm-image-content__kicker',
        value: section.kicker,
        placeholder: 'Label',
        onChange: function (value) { helpers.updateField(index, 'kicker', value); }
      }) : (section.kicker ? el(RichText.Content, { tagName: 'div', className: 'oegkm-image-content__kicker', value: section.kicker }) : null),
      isEditor ? el(RichText, {
        tagName: 'h2',
        className: 'oegkm-image-content__title',
        value: section.title,
        placeholder: 'Headline',
        onChange: function (value) { helpers.updateField(index, 'title', value); }
      }) : el(RichText.Content, { tagName: 'h2', className: 'oegkm-image-content__title', value: section.title }),
      isEditor ? el(RichText, {
        tagName: section.textAsList ? 'ul' : 'p',
        className: 'oegkm-image-content__text',
        multiline: section.textAsList ? 'li' : undefined,
        value: section.text,
        allowedFormats: textFormats,
        placeholder: 'Text',
        onFocus: function () { helpers.setActiveTextIndex(index); },
        onChange: function (value) { helpers.updateField(index, 'text', value); }
      }) : el(RichText.Content, {
        tagName: section.textAsList ? 'ul' : 'p',
        className: 'oegkm-image-content__text',
        multiline: section.textAsList ? 'li' : undefined,
        value: section.text
      }),
      hasButton(section) ? el('a', { className: 'oegkm-image-content__button', href: section.buttonUrl },
        el('span', {}, section.buttonText),
        arrowIcon()
      ) : null
    );

    return el('section', {
      className: 'oegkm-image-content__section oegkm-image-content__section--image-' + (section.imagePosition || 'right'),
      key: index
    }, section.imagePosition === 'left' ? [image, content] : [content, image]);
  }

  blocks.registerBlockType('oegkm/image-content-sections', {
    edit: function (props) {
      var sections = props.attributes.sections || [];
      var blockProps = useBlockProps({ className: 'oegkm-image-content oegkm-image-content--editor' });
      var activeTextState = useState(null);
      var activeTextIndex = activeTextState[0];
      var setActiveTextIndex = activeTextState[1];

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

      function setTextAsList(index, enabled) {
        var section = sections[index];
        var nextText = enabled ? toListValue(section.text) : fromListValue(section.text);
        updateSection(index, {
          textAsList: enabled,
          text: nextText
        });
      }

      function addSection() {
        setSections(sections.concat([{
          kicker: '',
          title: 'Neue Sektion',
          text: 'Text ergänzen.',
          imageUrl: '',
          imageId: 0,
          imagePosition: sections.length % 2 ? 'right' : 'left',
          textAsList: false,
          buttonText: '',
          buttonUrl: ''
        }]));
      }

      function removeSection(index) {
        if (sections.length <= 1) return;
        var nextSections = sections.slice();
        nextSections.splice(index, 1);
        setSections(nextSections);
      }

      var helpers = {
        updateSection: updateSection,
        updateField: updateField,
        setActiveTextIndex: setActiveTextIndex
      };

      return el(Fragment, {},
        typeof activeTextIndex === 'number' && sections[activeTextIndex] ? el(BlockControls, {},
          el(ToolbarGroup, {},
            el(ToolbarButton, {
              label: 'Aufzählung',
              text: 'Aufzählung',
              isPressed: !!sections[activeTextIndex].textAsList,
              onClick: function () {
                setTextAsList(activeTextIndex, !sections[activeTextIndex].textAsList);
              }
            })
          )
        ) : null,
        el(InspectorControls, {},
          el(PanelBody, { title: 'Sektionen', initialOpen: true },
            sections.map(function (section, index) {
              return el('div', { className: 'oegkm-image-content-editor__section', key: index },
                el(TextControl, {
                  label: 'Sektionstitel',
                  value: section.title || '',
                  onChange: function (value) { updateField(index, 'title', value); }
                }),
                el(SelectControl, {
                  label: 'Bildposition',
                  value: section.imagePosition || 'right',
                  options: [
                    { label: 'Bild rechts', value: 'right' },
                    { label: 'Bild links', value: 'left' }
                  ],
                  onChange: function (value) { updateField(index, 'imagePosition', value); }
                }),
                el(TextControl, {
                  label: 'Buttontext',
                  value: section.buttonText || '',
                  onChange: function (value) { updateField(index, 'buttonText', value); }
                }),
                el(TextControl, {
                  label: 'Button URL',
                  value: section.buttonUrl || '',
                  onChange: function (value) { updateField(index, 'buttonUrl', value); }
                }),
                sections.length > 1 ? el(Button, { isDestructive: true, onClick: function () { removeSection(index); } }, 'Sektion entfernen') : null
              );
            }),
            el(Button, { variant: 'primary', onClick: addSection }, 'Sektion hinzufügen')
          )
        ),
        el('div', blockProps,
          sections.map(function (section, index) {
            return renderSection(section, index, true, helpers);
          })
        )
      );
    },
    save: function (props) {
      var sections = props.attributes.sections || [];
      var blockProps = blockEditor.useBlockProps.save({ className: 'oegkm-image-content' });

      return el('div', blockProps,
        sections.map(function (section, index) {
          return renderSection(section, index, false, {});
        })
      );
    }
  });
})(window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components);
