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
    '.card, .why-item, .section-heading, .hero-inner, .story-copy'
  );
  if (!targets.length) return;

  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  targets.forEach((el) => el.classList.add('reveal'));

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
