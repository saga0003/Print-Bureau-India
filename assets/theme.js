(() => {
  const root = document.documentElement;
  const stored = localStorage.getItem('pbi-theme');
  const defaultTheme = (window.PBI_THEME && PBI_THEME.themeDefault) || 'dark';
  root.dataset.theme = stored || defaultTheme;

  const updateLogo = () => {
    document.querySelectorAll('[data-pbi-logo]').forEach(img => {
      const src = root.dataset.theme === 'light' ? img.dataset.light : img.dataset.dark;
      if (src) img.src = src;
    });
  };
  updateLogo();

  document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-theme-toggle]');
    if (toggle) {
      root.dataset.theme = root.dataset.theme === 'light' ? 'dark' : 'light';
      localStorage.setItem('pbi-theme', root.dataset.theme);
      updateLogo();
      toggle.setAttribute('aria-label', root.dataset.theme === 'light' ? 'Switch to dark mode' : 'Switch to light mode');
    }
    const menu = event.target.closest('[data-menu-toggle]');
    if (menu) {
      const nav = document.querySelector('[data-mobile-nav]');
      if (nav) {
        const open = nav.hasAttribute('hidden');
        nav.toggleAttribute('hidden', !open);
        menu.setAttribute('aria-expanded', open ? 'true' : 'false');
      }
    }
    const choice = event.target.closest('[data-product-choice]');
    if (choice) {
      document.querySelectorAll('[data-product-choice]').forEach(el => el.classList.remove('is-selected'));
      choice.classList.add('is-selected');
      const input = document.querySelector('[name="product"]');
      if (input) input.value = choice.dataset.product || '';
      const summary = document.querySelector('[data-summary-product]');
      if (summary) summary.textContent = choice.dataset.product || 'Selected product';
    }
  });

  const nav = document.querySelector('[data-mobile-nav]');
  if (nav) nav.setAttribute('hidden','');

  const params = new URLSearchParams(location.search);
  const source = params.get('utm_source') || '';
  const medium = params.get('utm_medium') || '';
  const campaign = params.get('utm_campaign') || '';
  const content = params.get('utm_content') || '';
  const term = params.get('utm_term') || '';
  document.querySelectorAll('form[data-lead-form]').forEach(form => {
    const set = (name, value) => { const input = form.querySelector(`[name="${name}"]`); if (input) input.value = value; };
    set('source', source); set('medium', medium); set('campaign', campaign); set('content', content); set('term', term);
    set('referrer', document.referrer); set('landing_page', sessionStorage.getItem('pbi-landing') || location.href); set('current_page', location.href);
    set('device', matchMedia('(max-width: 720px)').matches ? 'mobile' : 'desktop');
  });
  if (!sessionStorage.getItem('pbi-landing')) sessionStorage.setItem('pbi-landing', location.href);

  document.querySelectorAll('[data-sync-summary]').forEach(el => {
    el.addEventListener('change', () => {
      const target = document.querySelector(`[data-summary="${el.name}"]`);
      if (target) target.textContent = el.value || '—';
    });
  });
})();
