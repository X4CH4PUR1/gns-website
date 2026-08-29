/* =========================================================
   GN SCALES — CONTACT PAGE BEHAVIOR
   Progressive enhancement only: reveal an "open form in a new
   tab" link after the embedded Google Form loads (or after a
   timeout). The mailto fallback in contact.html is always
   visible on its own and is never gated behind this script.
   ========================================================= */

document.addEventListener('DOMContentLoaded', () => {
  const iframe = document.querySelector('.form-wrapper iframe');
  const fallbackLink = document.getElementById('form-fallback-link');
  if (!iframe || !fallbackLink) return;

  const reveal = () => {
    fallbackLink.hidden = false;
  };

  iframe.addEventListener('load', reveal, { once: true });
  window.setTimeout(reveal, 4000);
});
