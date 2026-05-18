(function (blocks, element, blockEditor, components) {
  var el = element.createElement;
  var Fragment = element.Fragment;
  var useBlockProps = blockEditor.useBlockProps;
  var InspectorControls = blockEditor.InspectorControls;
  var PanelBody = components.PanelBody;
  var TextControl = components.TextControl;
  var TextareaControl = components.TextareaControl;
  var Button = components.Button;

  function toLines(value) {
    var text = value || '';
    var lines = text.split('\n');
    return lines.map(function (line, index) {
      return index === 0 ? line : [el('br', { key: 'br-' + index }), line];
    });
  }

  function renderSections(sections) {
    return (sections || []).map(function (section, index) {
      return el('div', { className: 'oegkm-tabs__section', key: index },
        section.heading ? el('h3', { className: 'oegkm-tabs__section-title' }, section.heading) : null,
        section.body ? el('p', { className: 'oegkm-tabs__section-text' }, toLines(section.body)) : null
      );
    });
  }

  blocks.registerBlockType('oegkm/tabs', {
    edit: function (props) {
      var attrs = props.attributes;
      var tabs = attrs.tabs || [];
      var activeTab = Math.min(attrs.activeTab || 0, Math.max(tabs.length - 1, 0));
      var blockProps = useBlockProps({ className: 'oegkm-tabs oegkm-tabs--editor' });

      function setTabs(nextTabs) {
        props.setAttributes({ tabs: nextTabs });
      }

      function updateTab(index, field, value) {
        var nextTabs = tabs.slice();
        nextTabs[index] = Object.assign({}, nextTabs[index], { [field]: value });
        setTabs(nextTabs);
      }

      function updateSection(tabIndex, sectionIndex, field, value) {
        var nextTabs = tabs.slice();
        var sections = (nextTabs[tabIndex].sections || []).slice();
        sections[sectionIndex] = Object.assign({}, sections[sectionIndex], { [field]: value });
        nextTabs[tabIndex] = Object.assign({}, nextTabs[tabIndex], { sections: sections });
        setTabs(nextTabs);
      }

      function addTab() {
        setTabs(tabs.concat([{ title: 'Neuer Tab', sections: [{ heading: 'Neue Überschrift', body: 'Text ergänzen.' }] }]));
      }

      function removeTab(index) {
        if (tabs.length <= 1) return;
        var nextTabs = tabs.slice();
        nextTabs.splice(index, 1);
        props.setAttributes({ tabs: nextTabs, activeTab: Math.max(0, Math.min(activeTab, nextTabs.length - 1)) });
      }

      function addSection(tabIndex) {
        var nextTabs = tabs.slice();
        var sections = (nextTabs[tabIndex].sections || []).slice();
        sections.push({ heading: 'Neue Überschrift', body: 'Text ergänzen.' });
        nextTabs[tabIndex] = Object.assign({}, nextTabs[tabIndex], { sections: sections });
        setTabs(nextTabs);
      }

      function removeSection(tabIndex, sectionIndex) {
        var nextTabs = tabs.slice();
        var sections = (nextTabs[tabIndex].sections || []).slice();
        sections.splice(sectionIndex, 1);
        nextTabs[tabIndex] = Object.assign({}, nextTabs[tabIndex], { sections: sections });
        setTabs(nextTabs);
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
          el(PanelBody, { title: 'Tabs', initialOpen: true },
            tabs.map(function (tab, tabIndex) {
              return el('div', { className: 'oegkm-tabs-editor__tab', key: tabIndex },
                el(TextControl, {
                  label: 'Tabtitel',
                  value: tab.title || '',
                  onChange: function (value) { updateTab(tabIndex, 'title', value); }
                }),
                (tab.sections || []).map(function (section, sectionIndex) {
                  return el('div', { className: 'oegkm-tabs-editor__section', key: sectionIndex },
                    el(TextControl, {
                      label: 'Abschnittsüberschrift',
                      value: section.heading || '',
                      onChange: function (value) { updateSection(tabIndex, sectionIndex, 'heading', value); }
                    }),
                    el(TextareaControl, {
                      label: 'Text',
                      value: section.body || '',
                      onChange: function (value) { updateSection(tabIndex, sectionIndex, 'body', value); }
                    }),
                    (tab.sections || []).length > 1 ? el(Button, {
                      isDestructive: true,
                      onClick: function () { removeSection(tabIndex, sectionIndex); }
                    }, 'Abschnitt entfernen') : null
                  );
                }),
                el(Button, { variant: 'secondary', onClick: function () { addSection(tabIndex); } }, 'Abschnitt hinzufügen'),
                tabs.length > 1 ? el(Button, { isDestructive: true, onClick: function () { removeTab(tabIndex); } }, 'Tab entfernen') : null
              );
            }),
            el(Button, { variant: 'primary', onClick: addTab }, 'Tab hinzufügen')
          )
        ),
        el('section', blockProps,
          el('div', { className: 'oegkm-tabs__intro' },
            attrs.eyebrow ? el('p', { className: 'oegkm-tabs__eyebrow' }, attrs.eyebrow) : null,
            el('h2', { className: 'oegkm-tabs__title' }, toLines(attrs.title || ''))
          ),
          el('div', { className: 'oegkm-tabs__layout' },
            el('div', { className: 'oegkm-tabs__nav', role: 'tablist' },
              tabs.map(function (tab, index) {
                return el('button', {
                  type: 'button',
                  className: 'oegkm-tabs__tab' + (index === activeTab ? ' is-active' : ''),
                  role: 'tab',
                  'aria-selected': index === activeTab ? 'true' : 'false',
                  onClick: function () { props.setAttributes({ activeTab: index }); },
                  key: index
                }, tab.title || 'Tab');
              })
            ),
            el('div', { className: 'oegkm-tabs__panels' },
              tabs.map(function (tab, index) {
                return el('div', {
                  className: 'oegkm-tabs__panel' + (index === activeTab ? ' is-active' : ''),
                  role: 'tabpanel',
                  hidden: index !== activeTab,
                  key: index
                }, renderSections(tab.sections));
              })
            )
          )
        )
      );
    },
    save: function (props) {
      var attrs = props.attributes;
      var tabs = attrs.tabs || [];
      var activeTab = Math.min(attrs.activeTab || 0, Math.max(tabs.length - 1, 0));
      var blockProps = blockEditor.useBlockProps.save({ className: 'oegkm-tabs' });

      return el('section', blockProps,
        el('div', { className: 'oegkm-tabs__intro' },
          attrs.eyebrow ? el('p', { className: 'oegkm-tabs__eyebrow' }, attrs.eyebrow) : null,
          el('h2', { className: 'oegkm-tabs__title' }, toLines(attrs.title || ''))
        ),
        el('div', { className: 'oegkm-tabs__layout' },
          el('div', { className: 'oegkm-tabs__nav', role: 'tablist' },
            tabs.map(function (tab, index) {
              return el('button', {
                type: 'button',
                className: 'oegkm-tabs__tab' + (index === activeTab ? ' is-active' : ''),
                role: 'tab',
                'aria-selected': index === activeTab ? 'true' : 'false',
                key: index
              }, tab.title || '');
            })
          ),
          el('div', { className: 'oegkm-tabs__panels' },
            tabs.map(function (tab, index) {
              return el('div', {
                className: 'oegkm-tabs__panel' + (index === activeTab ? ' is-active' : ''),
                role: 'tabpanel',
                hidden: index !== activeTab,
                key: index
              }, renderSections(tab.sections));
            })
          )
        )
      );
    }
  });
})(window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components);
