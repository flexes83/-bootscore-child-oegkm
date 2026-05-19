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

  var DEFAULT_TABS = [
    { title: 'Vorstand', body: 'Einträge ergänzen.' },
    { title: 'Rechnungsprüfer', body: 'Einträge ergänzen.' },
    { title: 'Wissenschaftlicher Beirat', body: 'Einträge ergänzen.' }
  ];

  function cloneDefaultTabs() {
    return DEFAULT_TABS.map(function (tab) {
      return Object.assign({}, tab);
    });
  }

  function toLines(value) {
    var text = value || '';
    var lines = text.split('\n');
    return lines.map(function (line, index) {
      return index === 0 ? line : [el('br', { key: 'br-' + index }), line];
    });
  }

  function toListItems(value) {
    return (value || '').split('\n').map(function (line) {
      return line.replace(/^[\s\-•]+/, '').trim();
    }).filter(Boolean);
  }

  function renderTabBody(body) {
    var items = toListItems(body);

    if (!items.length) {
      return null;
    }

    return el('ul', { className: 'oegkm-period-tabs__list' },
      items.map(function (item, index) {
        return el('li', { key: index }, item);
      })
    );
  }

  function getTabs(period) {
    return period && period.tabs && period.tabs.length ? period.tabs : cloneDefaultTabs();
  }

  blocks.registerBlockType('oegkm/period-tabs', {
    edit: function (props) {
      var attrs = props.attributes;
      var periods = attrs.periods && attrs.periods.length ? attrs.periods : [];
      var activePeriod = Math.min(attrs.activePeriod || 0, Math.max(periods.length - 1, 0));
      var activeTab = attrs.activeTab || 0;
      var currentPeriod = periods[activePeriod] || { label: '', tabs: cloneDefaultTabs() };
      var currentTabs = getTabs(currentPeriod);
      var currentActiveTab = Math.min(activeTab, Math.max(currentTabs.length - 1, 0));
      var blockProps = useBlockProps({ className: 'oegkm-period-tabs oegkm-period-tabs--editor' });

      function setPeriods(nextPeriods) {
        props.setAttributes({ periods: nextPeriods });
      }

      function updatePeriod(index, field, value) {
        var nextPeriods = periods.slice();
        nextPeriods[index] = Object.assign({}, nextPeriods[index], { [field]: value });
        setPeriods(nextPeriods);
      }

      function updateTab(periodIndex, tabIndex, field, value) {
        var nextPeriods = periods.slice();
        var tabs = getTabs(nextPeriods[periodIndex]).slice();
        tabs[tabIndex] = Object.assign({}, tabs[tabIndex], { [field]: value });
        nextPeriods[periodIndex] = Object.assign({}, nextPeriods[periodIndex], { tabs: tabs });
        setPeriods(nextPeriods);
      }

      function addPeriod() {
        if (periods.length >= 5) return;
        setPeriods(periods.concat([{ label: 'Neue Periode', tabs: cloneDefaultTabs() }]));
      }

      function removePeriod(index) {
        if (periods.length <= 1) return;
        var nextPeriods = periods.slice();
        nextPeriods.splice(index, 1);
        props.setAttributes({
          periods: nextPeriods,
          activePeriod: Math.max(0, Math.min(activePeriod, nextPeriods.length - 1)),
          activeTab: 0
        });
      }

      function periodOptions() {
        return periods.map(function (period, index) {
          return {
            label: period.label || 'Periode ' + (index + 1),
            value: String(index)
          };
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
          el(PanelBody, { title: 'Perioden', initialOpen: true },
            periods.map(function (period, periodIndex) {
              return el('div', { className: 'oegkm-period-tabs-editor__period', key: periodIndex },
                el(TextControl, {
                  label: 'Select-Label',
                  value: period.label || '',
                  onChange: function (value) { updatePeriod(periodIndex, 'label', value); }
                }),
                getTabs(period).map(function (tab, tabIndex) {
                  return el('div', { className: 'oegkm-period-tabs-editor__tab', key: tabIndex },
                    el(TextControl, {
                      label: 'Tabtitel',
                      value: tab.title || '',
                      onChange: function (value) { updateTab(periodIndex, tabIndex, 'title', value); }
                    }),
                    el(TextareaControl, {
                      label: 'Liste',
                      help: 'Eine Zeile pro Eintrag.',
                      value: tab.body || '',
                      onChange: function (value) { updateTab(periodIndex, tabIndex, 'body', value); }
                    })
                  );
                }),
                periods.length > 1 ? el(Button, {
                  isDestructive: true,
                  onClick: function () { removePeriod(periodIndex); }
                }, 'Periode entfernen') : null
              );
            }),
            periods.length < 5 ? el(Button, { variant: 'primary', onClick: addPeriod }, 'Periode hinzufügen') : null
          )
        ),
        el('section', blockProps,
          el('div', { className: 'oegkm-period-tabs__intro' },
            attrs.eyebrow ? el('p', { className: 'oegkm-period-tabs__eyebrow' }, attrs.eyebrow) : null,
            el('h2', { className: 'oegkm-period-tabs__title' }, toLines(attrs.title || '')),
            periods.length ? el(SelectControl, {
              className: 'oegkm-period-tabs__select-control',
              label: 'Periode',
              hideLabelFromVision: true,
              value: String(activePeriod),
              options: periodOptions(),
              onChange: function (value) {
                props.setAttributes({ activePeriod: parseInt(value, 10) || 0, activeTab: 0 });
              }
            }) : null
          ),
          el('div', { className: 'oegkm-period-tabs__layout' },
            el('div', { className: 'oegkm-period-tabs__nav', role: 'tablist' },
              currentTabs.map(function (tab, index) {
                return el('button', {
                  type: 'button',
                  className: 'oegkm-period-tabs__tab' + (index === currentActiveTab ? ' is-active' : ''),
                  role: 'tab',
                  'aria-selected': index === currentActiveTab ? 'true' : 'false',
                  onClick: function () { props.setAttributes({ activeTab: index }); },
                  key: index
                }, tab.title || 'Tab');
              })
            ),
            el('div', { className: 'oegkm-period-tabs__panels' },
              currentTabs.map(function (tab, index) {
                return el('div', {
                  className: 'oegkm-period-tabs__panel' + (index === currentActiveTab ? ' is-active' : ''),
                  role: 'tabpanel',
                  hidden: index !== currentActiveTab,
                  key: index
                }, renderTabBody(tab.body));
              })
            )
          )
        )
      );
    },
    save: function (props) {
      var attrs = props.attributes;
      var periods = attrs.periods || [];
      var activePeriod = Math.min(attrs.activePeriod || 0, Math.max(periods.length - 1, 0));
      var blockProps = blockEditor.useBlockProps.save({ className: 'oegkm-period-tabs' });

      return el('section', blockProps,
        el('div', { className: 'oegkm-period-tabs__intro' },
          attrs.eyebrow ? el('p', { className: 'oegkm-period-tabs__eyebrow' }, attrs.eyebrow) : null,
          el('h2', { className: 'oegkm-period-tabs__title' }, toLines(attrs.title || '')),
          periods.length ? el('div', { className: 'oegkm-period-tabs__select-wrap' },
            el('select', { className: 'oegkm-period-tabs__select', 'aria-label': 'Periode auswählen' },
              periods.map(function (period, index) {
                return el('option', { value: String(index), selected: index === activePeriod, key: index }, period.label || 'Periode ' + (index + 1));
              })
            )
          ) : null
        ),
        el('div', { className: 'oegkm-period-tabs__periods' },
          periods.map(function (period, periodIndex) {
            var tabs = getTabs(period);
            return el('div', {
              className: 'oegkm-period-tabs__period' + (periodIndex === activePeriod ? ' is-active' : ''),
              'data-period-index': String(periodIndex),
              hidden: periodIndex !== activePeriod,
              key: periodIndex
            },
              el('div', { className: 'oegkm-period-tabs__layout' },
                el('div', { className: 'oegkm-period-tabs__nav', role: 'tablist' },
                  tabs.map(function (tab, tabIndex) {
                    var isActive = tabIndex === 0;
                    return el('button', {
                      type: 'button',
                      className: 'oegkm-period-tabs__tab' + (isActive ? ' is-active' : ''),
                      role: 'tab',
                      'aria-selected': isActive ? 'true' : 'false',
                      key: tabIndex
                    }, tab.title || '');
                  })
                ),
                el('div', { className: 'oegkm-period-tabs__panels' },
                  tabs.map(function (tab, tabIndex) {
                    return el('div', {
                      className: 'oegkm-period-tabs__panel' + (tabIndex === 0 ? ' is-active' : ''),
                      role: 'tabpanel',
                      hidden: tabIndex !== 0,
                      key: tabIndex
                    }, renderTabBody(tab.body));
                  })
                )
              )
            );
          })
        )
      );
    }
  });
})(window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components);
