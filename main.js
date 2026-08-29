/* =========================================================
   GN SCALES — SHARED SITE BEHAVIOR
   Loaded on every page. Site name injection, mobile nav,
   smooth scroll, header scroll state, scroll-reveal, footer year.
   ========================================================= */

const SITE_NAME = 'GN Scales';

document.addEventListener('DOMContentLoaded', () => {
  injectSiteName();
  setCurrentYear();
  initMobileNav();
  initSmoothScroll();
  initHeaderScrollState();
  initScrollReveal();
  initCardSpotlight();
  initMagneticButtons();
  initViewTransitions();
  initHeroParallax();
});

function injectSiteName() {
  document.querySelectorAll('.site-name').forEach((el) => {
    el.textContent = SITE_NAME;
  });
}

function setCurrentYear() {
  const el = document.getElementById('current-year');
  if (el) {
    el.textContent = new Date().getFullYear();
  }
}

function initMobileNav() {
  const toggle = document.querySelector('.nav-toggle');
  const list = document.getElementById('primary-nav-list');
  if (!toggle || !list) return;

  toggle.addEventListener('click', () => {
    const isOpen = list.classList.toggle('is-open');
    toggle.setAttribute('aria-expanded', String(isOpen));
    document.body.classList.toggle('nav-open', isOpen);
  });

  list.addEventListener('click', (event) => {
    if (event.target.tagName === 'A' && list.classList.contains('is-open')) {
      closeMobileNav(toggle, list);
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && list.classList.contains('is-open')) {
      closeMobileNav(toggle, list);
      toggle.focus();
    }
  });
}

function closeMobileNav(toggle, list) {
  list.classList.remove('is-open');
  toggle.setAttribute('aria-expanded', 'false');
  document.body.classList.remove('nav-open');
}

function initSmoothScroll() {
  document.querySelectorAll('a[href^="#"]').forEach((link) => {
    link.addEventListener('click', (event) => {
      const targetId = link.getAttribute('href').slice(1);
      if (!targetId) return;
      const target = document.getElementById(targetId);
      if (!target) return;

      event.preventDefault();
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      target.setAttribute('tabindex', '-1');
      target.focus({ preventScroll: true });
    });
  });
}

function initHeaderScrollState() {
  const header = document.querySelector('.site-header');
  if (!header) return;

  const updateState = () => {
    header.classList.toggle('is-scrolled', window.scrollY > 24);
  };

  updateState();
  window.addEventListener('scroll', updateState, { passive: true });
}

function initScrollReveal() {
  const targets = document.querySelectorAll(
    '.card, .why-item, .section-heading, .hero-inner, .story-copy, .service-detail'
  );
  if (!targets.length) return;

  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Break the uniform fade-up rhythm: alternate left/right/scale by position within each grid,
  // and leave standalone blocks (section headings, hero, prose) on the default fade-up.
  targets.forEach((el) => {
    el.classList.add('reveal');
    const grid = el.closest('.service-grid, .results-grid, .why-grid, .founders-grid, .pricing-grid');
    if (!grid) return;
    const index = Array.prototype.indexOf.call(grid.children, el);
    const variant = index % 3;
    if (variant === 0) el.classList.add('reveal-left');
    else if (variant === 1) el.classList.add('reveal-scale');
    else el.classList.add('reveal-right');
  });

  if (prefersReducedMotion || !('IntersectionObserver' in window)) {
    targets.forEach((el) => el.classList.add('is-visible'));
    return;
  }

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.15, rootMargin: '0px 0px -40px 0px' }
  );

  targets.forEach((el) => observer.observe(el));
}

function initCardSpotlight() {
  if (window.matchMedia('(pointer: coarse)').matches) return;

  const surfaces = document.querySelectorAll('.card');
  surfaces.forEach((el) => {
    el.addEventListener('pointermove', (event) => {
      const rect = el.getBoundingClientRect();
      const x = ((event.clientX - rect.left) / rect.width) * 100;
      const y = ((event.clientY - rect.top) / rect.height) * 100;
      el.style.setProperty('--mx', x + '%');
      el.style.setProperty('--my', y + '%');
    });
  });
}

function initMagneticButtons() {
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (prefersReducedMotion || window.matchMedia('(pointer: coarse)').matches) return;

  const buttons = document.querySelectorAll('.btn-primary, .btn-secondary');
  buttons.forEach((btn) => {
    btn.addEventListener('pointermove', (event) => {
      const rect = btn.getBoundingClientRect();
      const offsetX = (event.clientX - rect.left - rect.width / 2) * 0.25;
      const offsetY = (event.clientY - rect.top - rect.height / 2) * 0.35;
      btn.style.transform = `translate(${offsetX}px, ${offsetY - 2}px)`;
    });

    btn.addEventListener('pointerleave', () => {
      btn.style.transform = '';
    });
  });
}

function initViewTransitions() {
  if (!document.startViewTransition) return;

  document.querySelectorAll('a[href]').forEach((link) => {
    const url = link.getAttribute('href');
    if (!url || url.startsWith('#') || url.startsWith('http') || url.startsWith('mailto:')) return;
    if (link.target === '_blank') return;

    link.addEventListener('click', (event) => {
      if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
      event.preventDefault();
      document.startViewTransition(() => {
        window.location.href = url;
      });
    });
  });
}

function initHeroParallax() {
  const stack = document.querySelector('.proof-stack');
  if (!stack) return;
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  if (window.matchMedia('(pointer: coarse)').matches) return;

  const cards = stack.querySelectorAll('.proof-card');

  stack.addEventListener('pointermove', (event) => {
    const rect = stack.getBoundingClientRect();
    const px = (event.clientX - rect.left) / rect.width - 0.5;
    const py = (event.clientY - rect.top) / rect.height - 0.5;

    cards.forEach((card, i) => {
      const depth = (i + 1) * 6;
      card.style.transform = `translate(${px * depth}px, ${py * depth}px) rotate(${card.dataset.tilt || 0}deg)`;
    });
  });

  stack.addEventListener('pointerleave', () => {
    cards.forEach((card) => {
      card.style.transform = `rotate(${card.dataset.tilt || 0}deg)`;
    });
  });
}

