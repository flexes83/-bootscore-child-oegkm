(function () {
  const tabGroups = document.querySelectorAll('.oegkm-event-single-tabs');

  tabGroups.forEach((group) => {
    const tabs = Array.from(group.querySelectorAll('[data-oegkm-event-tab]'));
    const panels = Array.from(group.querySelectorAll('[data-oegkm-event-panel]'));

    tabs.forEach((tab) => {
      tab.addEventListener('click', () => {
        const target = tab.getAttribute('data-oegkm-event-tab');

        tabs.forEach((item) => {
          const isActive = item === tab;
          item.classList.toggle('is-active', isActive);
          item.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        panels.forEach((panel) => {
          const isActive = panel.getAttribute('data-oegkm-event-panel') === target;
          panel.classList.toggle('is-active', isActive);
          panel.hidden = !isActive;
        });
      });
    });
  });
})();
