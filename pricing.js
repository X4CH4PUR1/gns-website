/* =========================================================
   GN SCALES — PRICING PAGE BEHAVIOR
   FAQ accordion: independent, multi-open panels with a
   smooth height transition (measured via scrollHeight).
   ========================================================= */

document.addEventListener('DOMContentLoaded', () => {
  initFaqAccordion();
});

function initFaqAccordion() {
  const questions = document.querySelectorAll('.faq-question');

  questions.forEach((button) => {
    const panel = document.getElementById(button.getAttribute('aria-controls'));
    if (!panel) return;

    button.addEventListener('click', () => {
      const isOpen = button.getAttribute('aria-expanded') === 'true';
      button.setAttribute('aria-expanded', String(!isOpen));
      isOpen ? closePanel(panel) : openPanel(panel);
    });
  });
}

function openPanel(panel) {
  panel.hidden = false;
  const targetHeight = panel.scrollHeight;
  panel.style.maxHeight = '0px';

  requestAnimationFrame(() => {
    panel.style.maxHeight = targetHeight + 'px';
  });

  panel.addEventListener('transitionend', function onOpenEnd(event) {
    if (event.propertyName === 'max-height') {
      panel.style.maxHeight = 'none';
      panel.removeEventListener('transitionend', onOpenEnd);
    }
  });
}

function closePanel(panel) {
  const currentHeight = panel.scrollHeight;
  panel.style.maxHeight = currentHeight + 'px';

  requestAnimationFrame(() => {
    panel.style.maxHeight = '0px';
  });

  panel.addEventListener('transitionend', function onCloseEnd(event) {
    if (event.propertyName === 'max-height') {
      panel.hidden = true;
      panel.removeEventListener('transitionend', onCloseEnd);
    }
  });
}

