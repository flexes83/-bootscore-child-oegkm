document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.oegkm-tabs').forEach(function (tabsBlock) {
    var tabs = Array.prototype.slice.call(tabsBlock.querySelectorAll('.oegkm-tabs__tab'));
    var panels = Array.prototype.slice.call(tabsBlock.querySelectorAll('.oegkm-tabs__panel'));

    tabs.forEach(function (tab, index) {
      tab.addEventListener('click', function () {
        tabs.forEach(function (otherTab, otherIndex) {
          var isActive = otherIndex === index;
          otherTab.classList.toggle('is-active', isActive);
          otherTab.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        panels.forEach(function (panel, panelIndex) {
          var isActive = panelIndex === index;
          panel.classList.toggle('is-active', isActive);
          panel.hidden = !isActive;
        });
      });
    });
  });
});
