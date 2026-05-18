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

  function updateMember(members, index, field, value) {
    var next = members.slice();
    next[index] = Object.assign({}, next[index], { [field]: value });
    return next;
  }

  blocks.registerBlockType('oegkm/team-slider', {
    edit: function (props) {
      var attrs = props.attributes;
      var members = attrs.members || [];
      var blockProps = useBlockProps({ className: 'oegkm-team-slider oegkm-team-slider--editor' });

      function setMembers(next) {
        props.setAttributes({ members: next });
      }

      function addMember() {
        setMembers(members.concat([{
          name: 'Teammitglied',
          role: 'Funktion',
          imageUrl: '',
          imageId: 0
        }]));
      }

      function removeMember(index) {
        if (members.length <= 1) return;
        var next = members.slice();
        next.splice(index, 1);
        setMembers(next);
      }

      return el(Fragment, {},
        el(InspectorControls, {},
          el(PanelBody, { title: 'Teammitglieder', initialOpen: true },
            members.map(function (member, index) {
              return el('div', { className: 'oegkm-team-slider-editor__member', key: index },
                el(TextControl, {
                  label: 'Name',
                  value: member.name || '',
                  onChange: function (value) { setMembers(updateMember(members, index, 'name', value)); }
                }),
                el(TextControl, {
                  label: 'Funktion',
                  value: member.role || '',
                  onChange: function (value) { setMembers(updateMember(members, index, 'role', value)); }
                }),
                el(MediaUploadCheck, {},
                  el(MediaUpload, {
                    onSelect: function (media) {
                      var next = updateMember(members, index, 'imageUrl', media.url || '');
                      next = updateMember(next, index, 'imageId', media.id || 0);
                      setMembers(next);
                    },
                    allowedTypes: ['image'],
                    value: member.imageId,
                    render: function (obj) {
                      return el(Button, { variant: 'secondary', onClick: obj.open }, member.imageUrl ? 'Bild ersetzen' : 'Bild auswaehlen');
                    }
                  })
                ),
                members.length > 1 ? el(Button, { isDestructive: true, onClick: function () { removeMember(index); } }, 'Entfernen') : null
              );
            }),
            el(Button, { variant: 'primary', onClick: addMember }, 'Teammitglied hinzufuegen')
          )
        ),
        el('section', blockProps,
          el('div', { className: 'oegkm-team-slider__header' },
            el(RichText, {
              tagName: 'div',
              className: 'oegkm-team-slider__eyebrow',
              value: attrs.eyebrow,
              placeholder: 'Zeitraum',
              onChange: function (value) { props.setAttributes({ eyebrow: value }); }
            }),
            el(RichText, {
              tagName: 'h2',
              className: 'oegkm-team-slider__title',
              value: attrs.title,
              placeholder: 'Titel',
              onChange: function (value) { props.setAttributes({ title: value }); }
            })
          ),
          el('div', { className: 'oegkm-team-slider__viewport' },
            el('div', { className: 'oegkm-team-slider__track' },
              members.map(function (member, index) {
                return el('article', { className: 'oegkm-team-slider__card' + (index === 0 ? ' is-active' : ''), key: index },
                  el('div', { className: 'oegkm-team-slider__image' },
                    member.imageUrl ? el('img', { src: member.imageUrl, alt: '' }) : el('span', {}, 'Bild')
                  ),
                  el('div', { className: 'oegkm-team-slider__caption' },
                    el(RichText, {
                      tagName: 'h3',
                      className: 'oegkm-team-slider__name',
                      value: member.name,
                      placeholder: 'Name',
                      onChange: function (value) { setMembers(updateMember(members, index, 'name', value)); }
                    }),
                    el(RichText, {
                      tagName: 'p',
                      className: 'oegkm-team-slider__role',
                      value: member.role,
                      placeholder: 'Funktion',
                      onChange: function (value) { setMembers(updateMember(members, index, 'role', value)); }
                    })
                  )
                );
              })
            )
          ),
          el('div', { className: 'oegkm-team-slider__navs' },
            el('button', { type: 'button', className: 'oegkm-team-slider__nav oegkm-team-slider__nav--prev', disabled: true }, chevronIcon('prev')),
            el('button', { type: 'button', className: 'oegkm-team-slider__nav oegkm-team-slider__nav--next' }, chevronIcon('next'))
          )
        )
      );
    },
    save: function (props) {
      var attrs = props.attributes;
      var members = attrs.members || [];
      var blockProps = blockEditor.useBlockProps.save({ className: 'oegkm-team-slider', 'data-members': String(members.length || 0) });

      return el('section', blockProps,
        el('div', { className: 'oegkm-team-slider__header' },
          attrs.eyebrow ? el(RichText.Content, { tagName: 'div', className: 'oegkm-team-slider__eyebrow', value: attrs.eyebrow }) : null,
          el(RichText.Content, { tagName: 'h2', className: 'oegkm-team-slider__title', value: attrs.title })
        ),
        el('div', { className: 'oegkm-team-slider__viewport' },
          el('div', { className: 'oegkm-team-slider__track' },
            members.map(function (member, index) {
              return el('article', { className: 'oegkm-team-slider__card' + (index === 0 ? ' is-active' : ''), key: index },
                el('div', { className: 'oegkm-team-slider__image' },
                  member.imageUrl ? el('img', { src: member.imageUrl, alt: '' }) : null
                ),
                el('div', { className: 'oegkm-team-slider__caption' },
                  el(RichText.Content, { tagName: 'h3', className: 'oegkm-team-slider__name', value: member.name }),
                  el(RichText.Content, { tagName: 'p', className: 'oegkm-team-slider__role', value: member.role })
                )
              );
            })
          )
        ),
        el('div', { className: 'oegkm-team-slider__navs' },
          el('button', { type: 'button', className: 'oegkm-team-slider__nav oegkm-team-slider__nav--prev', 'aria-label': 'Zurueck' }, chevronIcon('prev')),
          el('button', { type: 'button', className: 'oegkm-team-slider__nav oegkm-team-slider__nav--next', 'aria-label': 'Weiter' }, chevronIcon('next'))
        )
      );
    }
  });
})(window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components);
