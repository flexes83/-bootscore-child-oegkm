document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.oegkm-period-tabs').forEach(function (block) {
    var select = block.querySelector('.oegkm-period-tabs__select');
    var periods = Array.prototype.slice.call(block.querySelectorAll('.oegkm-period-tabs__period'));

    function activateTab(period, index) {
      var tabs = Array.prototype.slice.call(period.querySelectorAll('.oegkm-period-tabs__tab'));
      var panels = Array.prototype.slice.call(period.querySelectorAll('.oegkm-period-tabs__panel'));

      tabs.forEach(function (tab, tabIndex) {
        var isActive = tabIndex === index;
        tab.classList.toggle('is-active', isActive);
        tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
      });

      panels.forEach(function (panel, panelIndex) {
        var isActive = panelIndex === index;
        panel.classList.toggle('is-active', isActive);
        panel.hidden = !isActive;
      });
    }

    function activatePeriod(index) {
      periods.forEach(function (period, periodIndex) {
        var isActive = periodIndex === index;
        period.classList.toggle('is-active', isActive);
        period.hidden = !isActive;

        if (isActive) {
          activateTab(period, 0);
        }
      });
    }

    periods.forEach(function (period) {
      var tabs = Array.prototype.slice.call(period.querySelectorAll('.oegkm-period-tabs__tab'));

      tabs.forEach(function (tab, index) {
        tab.addEventListener('click', function () {
          activateTab(period, index);
        });
      });
    });

    if (select) {
      select.addEventListener('change', function () {
        activatePeriod(parseInt(select.value, 10) || 0);
      });
    }
  });
});
