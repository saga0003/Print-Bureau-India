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
    const set = (name, value) => {
      const input = form.querySelector(`[name="${name}"]`);
      if (input) input.value = value;
    };
    set('source', source);
    set('medium', medium);
    set('campaign', campaign);
    set('content', content);
    set('term', term);
    set('referrer', document.referrer);
    set('landing_page', sessionStorage.getItem('pbi-landing') || location.href);
    set('current_page', location.href);
    set('device', matchMedia('(max-width: 720px)').matches ? 'mobile' : 'desktop');
  });

  if (!sessionStorage.getItem('pbi-landing')) {
    sessionStorage.setItem('pbi-landing', location.href);
  }

  document.querySelectorAll('[data-sync-summary]').forEach(el => {
    el.addEventListener('change', () => {
      const target = document.querySelector(`[data-summary="${el.name}"]`);
      if (target) target.textContent = el.value || '—';
    });
  });

  /* Product gallery: real thumbnails, next/prev, hover zoom, lightbox and swipe. */
  document.querySelectorAll('[data-product-gallery]').forEach(gallery => {
    const main = gallery.querySelector('[data-gallery-main]');
    const stage = gallery.querySelector('[data-gallery-stage]');
    const zoomButton = gallery.querySelector('[data-gallery-open]');
    const thumbs = Array.from(gallery.querySelectorAll('[data-gallery-thumb]'));
    const prevButtons = Array.from(gallery.querySelectorAll('[data-gallery-prev]'));
    const nextButtons = Array.from(gallery.querySelectorAll('[data-gallery-next]'));
    const lightbox = gallery.querySelector('[data-gallery-lightbox]');
    const lightboxImage = gallery.querySelector('[data-gallery-lightbox-image]');
    const lightboxCaption = gallery.querySelector('[data-gallery-caption]');
    const counter = gallery.querySelector('[data-gallery-counter]');

    if (!main) return;

    const images = thumbs.length
      ? thumbs.map(thumb => ({
          url: thumb.dataset.full || thumb.querySelector('img')?.src || '',
          alt: thumb.dataset.alt || ''
        }))
      : [{ url: main.currentSrc || main.src, alt: main.alt || '' }];

    let index = Math.max(0, Number.parseInt(main.dataset.galleryIndex || '0', 10) || 0);
    let touchStartX = null;

    const render = (nextIndex, updateFocus = false) => {
      if (!images.length) return;
      index = (nextIndex + images.length) % images.length;
      const item = images[index];
      main.src = item.url;
      main.alt = item.alt;
      main.dataset.galleryIndex = String(index);

      thumbs.forEach((thumb, i) => {
        const active = i === index;
        thumb.classList.toggle('is-active', active);
        thumb.setAttribute('aria-current', active ? 'true' : 'false');
        if (active && updateFocus) thumb.focus({ preventScroll: true });
      });

      if (lightboxImage) {
        lightboxImage.src = item.url;
        lightboxImage.alt = item.alt;
      }
      if (lightboxCaption) lightboxCaption.textContent = item.alt;
      if (counter) counter.textContent = `${index + 1} / ${images.length}`;
    };

    const openLightbox = () => {
      if (!lightbox) return;
      render(index);
      lightbox.hidden = false;
      lightbox.setAttribute('aria-hidden', 'false');
      document.documentElement.classList.add('pbi-lightbox-open');
      const close = lightbox.querySelector('[data-gallery-close]');
      if (close) close.focus({ preventScroll: true });
    };

    const closeLightbox = () => {
      if (!lightbox) return;
      lightbox.hidden = true;
      lightbox.setAttribute('aria-hidden', 'true');
      document.documentElement.classList.remove('pbi-lightbox-open');
      if (zoomButton) zoomButton.focus({ preventScroll: true });
    };

    thumbs.forEach((thumb, i) => {
      thumb.addEventListener('click', () => render(i));
    });

    prevButtons.forEach(button => {
      button.addEventListener('click', event => {
        event.preventDefault();
        event.stopPropagation();
        render(index - 1);
      });
    });

    nextButtons.forEach(button => {
      button.addEventListener('click', event => {
        event.preventDefault();
        event.stopPropagation();
        render(index + 1);
      });
    });

    if (zoomButton) zoomButton.addEventListener('click', openLightbox);

    if (lightbox) {
      lightbox.querySelectorAll('[data-gallery-close]').forEach(el => {
        el.addEventListener('click', closeLightbox);
      });

      lightbox.addEventListener('touchstart', event => {
        touchStartX = event.changedTouches[0]?.clientX ?? null;
      }, { passive: true });

      lightbox.addEventListener('touchend', event => {
        if (touchStartX === null) return;
        const endX = event.changedTouches[0]?.clientX ?? touchStartX;
        const delta = endX - touchStartX;
        touchStartX = null;
        if (Math.abs(delta) < 45 || images.length < 2) return;
        render(delta > 0 ? index - 1 : index + 1);
      }, { passive: true });
    }

    if (stage && zoomButton && matchMedia('(hover:hover) and (pointer:fine)').matches) {
      stage.addEventListener('mousemove', event => {
        const rect = stage.getBoundingClientRect();
        const x = Math.max(0, Math.min(100, ((event.clientX - rect.left) / rect.width) * 100));
        const y = Math.max(0, Math.min(100, ((event.clientY - rect.top) / rect.height) * 100));
        main.style.transformOrigin = `${x}% ${y}%`;
        zoomButton.classList.add('is-zooming');
      });
      stage.addEventListener('mouseleave', () => {
        zoomButton.classList.remove('is-zooming');
        main.style.transformOrigin = '50% 50%';
      });
    }

    document.addEventListener('keydown', event => {
      if (!lightbox || lightbox.hidden) return;
      if (event.key === 'Escape') closeLightbox();
      if (event.key === 'ArrowLeft' && images.length > 1) render(index - 1);
      if (event.key === 'ArrowRight' && images.length > 1) render(index + 1);
    });
  });
})();
