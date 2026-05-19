(function (blocks, element, blockEditor, components) {
  var el = element.createElement;
  var Fragment = element.Fragment;
  var useBlockProps = blockEditor.useBlockProps;
  var InspectorControls = blockEditor.InspectorControls;
  var PanelBody = components.PanelBody;
  var TextControl = components.TextControl;
  var TextareaControl = components.TextareaControl;
  var Button = components.Button;
  var SelectControl = components.SelectControl;

  function arrowDown() {
    return el('svg', {
      className: 'oegkm-prize-winners__arrow',
      viewBox: '0 0 20 20',
      'aria-hidden': 'true',
      focusable: 'false'
    },
      el('path', {
        d: 'm5 7.5 5 5 5-5',
        fill: 'none',
        stroke: 'currentColor',
        strokeWidth: 1.5,
        strokeLinecap: 'round',
        strokeLinejoin: 'round'
      })
    );
  }

  function defaultItem() {
    return {
      recipient: 'Name (Ort)',
      project: 'Projektbeschreibung',
      institution: 'Institution',
      linkText: 'Mehr',
      linkUrl: ''
    };
  }

  function renderItems(items) {
    return (items || []).map(function (item, index) {
      return el('div', { className: 'oegkm-prize-winners__row', key: index },
        el('div', { className: 'oegkm-prize-winners__cell' }, item.recipient || ''),
        el('div', { className: 'oegkm-prize-winners__cell' }, item.project || ''),
        el('div', { className: 'oegkm-prize-winners__cell oegkm-prize-winners__cell--institution' },
          el('span', {}, item.institution || ''),
          item.linkText && item.linkUrl ? el('a', { className: 'oegkm-prize-winners__more', href: item.linkUrl },
            item.linkText,
            arrowDown()
          ) : null
        )
      );
    });
  }

  blocks.registerBlockType('oegkm/prize-winners', {
    edit: function (props) {
      var attrs = props.attributes;
      var years = attrs.years || [];
      var activeYear = Math.min(attrs.activeYear || 0, Math.max(years.length - 1, 0));
      var currentYear = years[activeYear] || { label: '', items: [] };
      var blockProps = useBlockProps({ className: 'oegkm-prize-winners oegkm-prize-winners--editor' });

      function setYears(nextYears) {
        props.setAttributes({ years: nextYears });
      }

      function updateYear(yearIndex, field, value) {
        var nextYears = years.slice();
        nextYears[yearIndex] = Object.assign({}, nextYears[yearIndex], { [field]: value });
        setYears(nextYears);
      }

      function updateItem(yearIndex, itemIndex, field, value) {
        var nextYears = years.slice();
        var items = (nextYears[yearIndex].items || []).slice();
        items[itemIndex] = Object.assign({}, items[itemIndex], { [field]: value });
        nextYears[yearIndex] = Object.assign({}, nextYears[yearIndex], { items: items });
        setYears(nextYears);
      }

      function addYear() {
        setYears(years.concat([{ label: 'Neues Jahr', items: [defaultItem()] }]));
      }

      function removeYear(index) {
        if (years.length <= 1) return;
        var nextYears = years.slice();
        nextYears.splice(index, 1);
        props.setAttributes({ years: nextYears, activeYear: Math.max(0, Math.min(activeYear, nextYears.length - 1)) });
      }

      function addItem(yearIndex) {
        var nextYears = years.slice();
        var items = (nextYears[yearIndex].items || []).slice();
        items.push(defaultItem());
        nextYears[yearIndex] = Object.assign({}, nextYears[yearIndex], { items: items });
        setYears(nextYears);
      }

      function removeItem(yearIndex, itemIndex) {
        var nextYears = years.slice();
        var items = (nextYears[yearIndex].items || []).slice();
        items.splice(itemIndex, 1);
        nextYears[yearIndex] = Object.assign({}, nextYears[yearIndex], { items: items });
        setYears(nextYears);
      }

      function yearOptions() {
        return years.map(function (year, index) {
          return { label: year.label || 'Jahr ' + (index + 1), value: String(index) };
        });
      }

      return el(Fragment, {},
        el(InspectorControls, {},
          el(PanelBody, { title: 'Intro', initialOpen: true },
            el(TextControl, {
              label: 'Label',
              value: attrs.eyebrow || '',
              onChange: function (value) { props.setAttributes({ eyebrow: value }); }
            }),
            el(TextareaControl, {
              label: 'Überschrift',
              value: attrs.title || '',
              onChange: function (value) { props.setAttributes({ title: value }); }
            })
          ),
          el(PanelBody, { title: 'Jahre und Einträge', initialOpen: true },
            years.map(function (year, yearIndex) {
              return el('div', { className: 'oegkm-prize-winners-editor__year', key: yearIndex },
                el(TextControl, {
                  label: 'Jahr',
                  value: year.label || '',
                  onChange: function (value) { updateYear(yearIndex, 'label', value); }
                }),
                (year.items || []).map(function (item, itemIndex) {
                  return el('div', { className: 'oegkm-prize-winners-editor__item', key: itemIndex },
                    el(TextControl, {
                      label: 'Preisträger:in',
                      value: item.recipient || '',
                      onChange: function (value) { updateItem(yearIndex, itemIndex, 'recipient', value); }
                    }),
                    el(TextareaControl, {
                      label: 'Projekt',
                      value: item.project || '',
                      onChange: function (value) { updateItem(yearIndex, itemIndex, 'project', value); }
                    }),
                    el(TextareaControl, {
                      label: 'Institution',
                      value: item.institution || '',
                      onChange: function (value) { updateItem(yearIndex, itemIndex, 'institution', value); }
                    }),
                    el(TextControl, {
                      label: 'Linktext',
                      value: item.linkText || '',
                      onChange: function (value) { updateItem(yearIndex, itemIndex, 'linkText', value); }
                    }),
                    el(TextControl, {
                      label: 'Link URL',
                      value: item.linkUrl || '',
                      onChange: function (value) { updateItem(yearIndex, itemIndex, 'linkUrl', value); }
                    }),
                    (year.items || []).length > 1 ? el(Button, { isDestructive: true, onClick: function () { removeItem(yearIndex, itemIndex); } }, 'Eintrag entfernen') : null
                  );
                }),
                el(Button, { variant: 'secondary', onClick: function () { addItem(yearIndex); } }, 'Eintrag hinzufügen'),
                years.length > 1 ? el(Button, { isDestructive: true, onClick: function () { removeYear(yearIndex); } }, 'Jahr entfernen') : null
              );
            }),
            el(Button, { variant: 'primary', onClick: addYear }, 'Jahr hinzufügen')
          )
        ),
        el('section', blockProps,
          el('div', { className: 'oegkm-prize-winners__inner' },
            el('div', { className: 'oegkm-prize-winners__header' },
              el('div', { className: 'oegkm-prize-winners__intro' },
                attrs.eyebrow ? el('p', { className: 'oegkm-prize-winners__eyebrow' }, attrs.eyebrow) : null,
                el('h2', { className: 'oegkm-prize-winners__title' }, attrs.title || '')
              ),
              years.length ? el(SelectControl, {
                className: 'oegkm-prize-winners__select-control',
                label: 'Jahr',
                hideLabelFromVision: true,
                value: String(activeYear),
                options: yearOptions(),
                onChange: function (value) { props.setAttributes({ activeYear: parseInt(value, 10) || 0 }); }
              }) : null
            ),
            el('div', { className: 'oegkm-prize-winners__table' },
              el('div', { className: 'oegkm-prize-winners__head' },
                el('span', {}, 'Preisträger:in'),
                el('span', {}, 'Projekt'),
                el('span', {}, 'Institution')
              ),
              renderItems(currentYear.items)
            )
          )
        )
      );
    },
    save: function (props) {
      var attrs = props.attributes;
      var years = attrs.years || [];
      var activeYear = Math.min(attrs.activeYear || 0, Math.max(years.length - 1, 0));
      var blockProps = blockEditor.useBlockProps.save({ className: 'oegkm-prize-winners' });

      return el('section', blockProps,
        el('div', { className: 'oegkm-prize-winners__inner' },
          el('div', { className: 'oegkm-prize-winners__header' },
            el('div', { className: 'oegkm-prize-winners__intro' },
              attrs.eyebrow ? el('p', { className: 'oegkm-prize-winners__eyebrow' }, attrs.eyebrow) : null,
              el('h2', { className: 'oegkm-prize-winners__title' }, attrs.title || '')
            ),
            years.length ? el('div', { className: 'oegkm-prize-winners__select-wrap' },
              el('select', { className: 'oegkm-prize-winners__select', 'aria-label': 'Jahr auswählen' },
                years.map(function (year, index) {
                  return el('option', { value: String(index), selected: index === activeYear, key: index }, year.label || 'Jahr ' + (index + 1));
                })
              )
            ) : null
          ),
          years.map(function (year, yearIndex) {
            return el('div', {
              className: 'oegkm-prize-winners__year' + (yearIndex === activeYear ? ' is-active' : ''),
              hidden: yearIndex !== activeYear,
              key: yearIndex
            },
              el('div', { className: 'oegkm-prize-winners__table' },
                el('div', { className: 'oegkm-prize-winners__head' },
                  el('span', {}, 'Preisträger:in'),
                  el('span', {}, 'Projekt'),
                  el('span', {}, 'Institution')
                ),
                renderItems(year.items)
              )
            );
          })
        )
      );
    }
  });
})(window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components);
