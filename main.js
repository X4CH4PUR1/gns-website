/* =========================================================
   GN SCALES — SITE BEHAVIOR
   Loaded on every page. Everything is feature-detected and
   guarded: any block whose target element is absent, or whose
   API the browser lacks, quietly does nothing.
   No dependencies.
   ========================================================= */

(function () {
  'use strict';

  /* ---------- Environment ----------
     Read live rather than captured once at parse time. Somebody who turns on
     Reduce Motion mid-session, or docks a laptop, or plugs a mouse into a
     tablet, should get the right behaviour without reloading the page. */
  var mqMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  var mqPointer = window.matchMedia('(hover: hover) and (pointer: fine)');
  var mqWide = window.matchMedia('(min-width: 900px)');

  function reduceMotion() { return mqMotion.matches; }
  function finePointer() { return mqPointer.matches; }

  function onMediaChange(mq, fn) {
    if (mq.addEventListener) { mq.addEventListener('change', fn); }
    else if (mq.addListener) { mq.addListener(fn); }
  }

  /* ---------- Startup ----------
     Each initialiser is wrapped, because they used to run as one unguarded
     sequence: a throw in the first one took out every feature after it,
     including the scroll reveal, which left most of the page invisible.
     One broken feature should cost you that feature and nothing else. */
  function safe(fn) {
    try {
      fn();
    } catch (err) {
      if (window.console && console.error) {
        console.error('[gns] ' + (fn.name || 'init') + ' failed:', err);
      }
    }
  }

  function start() {
    [
      setYear, initNav, initHeaderState, initReveal, initCardSpotlight,
      initCursor, initMagnetic, initFaq, initCalculator, initCompare,
      initForm, initScheduler, initConsent, initConversion, initHeroShader
    ].forEach(safe);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }

  /* ---------- Footer year ---------- */
  function setYear() {
    var el = document.getElementById('year');
    if (el) el.textContent = String(new Date().getFullYear());
  }

  /* ---------- Mobile nav ---------- */
  function initNav() {
    var toggle = document.querySelector('.nav-toggle');
    var nav = document.getElementById('site-nav');
    if (!toggle || !nav) return;

    // Everything outside the drawer while it is open. inert removes these from
    // the tab order and the accessibility tree in one attribute; the manual
    // Tab trap below covers browsers that do not support it yet.
    var behind = [document.getElementById('main'), document.querySelector('.site-footer')]
      .filter(Boolean);

    function setOpen(open) {
      document.body.classList.toggle('nav-open', open);
      toggle.setAttribute('aria-expanded', String(open));
      behind.forEach(function (el) {
        if (open) { el.setAttribute('inert', ''); } else { el.removeAttribute('inert'); }
      });
      if (open) {
        var first = nav.querySelector('a, button');
        if (first) first.focus();
      }
    }

    function isOpen() { return document.body.classList.contains('nav-open'); }

    toggle.addEventListener('click', function () { setOpen(!isOpen()); });

    nav.addEventListener('click', function (e) {
      if (e.target.closest('a')) setOpen(false);
    });

    document.addEventListener('keydown', function (e) {
      if (!isOpen()) return;

      if (e.key === 'Escape') {
        setOpen(false);
        toggle.focus();
        return;
      }
      if (e.key !== 'Tab') return;

      var focusable = nav.querySelectorAll('a[href], button:not([disabled]), input, [tabindex]:not([tabindex="-1"])');
      if (!focusable.length) return;
      var first = focusable[0];
      var last = focusable[focusable.length - 1];

      // The toggle sits outside the drawer but has to stay reachable, so it
      // is treated as the element before the first item in the cycle.
      if (e.shiftKey && (document.activeElement === first || document.activeElement === toggle)) {
        e.preventDefault();
        last.focus();
      } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault();
        toggle.focus();
      }
    });

    // A drawer left open across a resize into the desktop layout would strand
    // inert attributes on the page.
    onMediaChange(mqWide, function (e) { if (e.matches && isOpen()) setOpen(false); });
  }

  /* ---------- Header scrolled state ---------- */
  function initHeaderState() {
    var header = document.querySelector('.site-header');
    if (!header) return;
    var update = function () {
      header.classList.toggle('is-scrolled', window.scrollY > 20);
    };
    update();
    window.addEventListener('scroll', update, { passive: true });
  }

  /* ---------- Scroll reveal ---------- */
  function initReveal() {
    var targets = document.querySelectorAll('.reveal, .stagger');
    if (!targets.length) return;

    function showAll() {
      targets.forEach(function (el) { el.classList.add('is-in'); });
    }

    if (reduceMotion() || !('IntersectionObserver' in window)) {
      showAll();
      return;
    }

    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-in');
        io.unobserve(entry.target);
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -60px 0px' });

    targets.forEach(function (el) { io.observe(el); });

    // Turning on Reduce Motion mid-session should not leave the rest of the
    // page waiting behind an animation the visitor has asked not to see.
    onMediaChange(mqMotion, function (e) { if (e.matches) { io.disconnect(); showAll(); } });
  }

  /* ---------- Cursor-tracked card glow ---------- */
  function initCardSpotlight() {
    document.querySelectorAll('.card').forEach(function (card) {
      card.addEventListener('pointermove', function (e) {
        if (!finePointer()) return;
        var r = card.getBoundingClientRect();
        card.style.setProperty('--mx', ((e.clientX - r.left) / r.width) * 100 + '%');
        card.style.setProperty('--my', ((e.clientY - r.top) / r.height) * 100 + '%');
      });
    });
  }

  /* ---------- Custom cursor ---------- */
  function initCursor() {
    if (!finePointer() || reduceMotion()) return;

    var dot = document.createElement('div');
    var ring = document.createElement('div');
    dot.className = 'cursor-dot';
    ring.className = 'cursor-ring';
    dot.setAttribute('aria-hidden', 'true');
    ring.setAttribute('aria-hidden', 'true');
    document.body.appendChild(dot);
    document.body.appendChild(ring);

    var tx = window.innerWidth / 2, ty = window.innerHeight / 2;
    var rx = tx, ry = ty;
    var started = false;
    var stopped = false;

    document.addEventListener('pointermove', function (e) {
      tx = e.clientX;
      ty = e.clientY;
      if (!started) {
        started = true;
        rx = tx; ry = ty;
        document.body.classList.add('cursor-ready');
      }
    }, { passive: true });

    document.addEventListener('pointerleave', function () { document.body.classList.add('cursor-hidden'); });
    document.addEventListener('pointerenter', function () { document.body.classList.remove('cursor-hidden'); });

    // The dot is pinned to the pointer; the ring trails it, which is what
    // reads as weight rather than as lag.
    //
    // The loop bails out when the tab is hidden and when the ring has caught
    // up with the pointer, so a stationary cursor on an idle tab costs
    // nothing. It used to write two transforms every frame, forever.
    (function frame() {
      if (stopped) return;
      requestAnimationFrame(frame);
      if (document.hidden) return;

      var dx = tx - rx, dy = ty - ry;
      dot.style.transform = 'translate3d(' + tx + 'px,' + ty + 'px,0)';

      if (Math.abs(dx) < 0.1 && Math.abs(dy) < 0.1) {
        if (rx !== tx || ry !== ty) {
          rx = tx; ry = ty;
          ring.style.transform = 'translate3d(' + rx + 'px,' + ry + 'px,0)';
        }
        return;
      }
      rx += dx * 0.18;
      ry += dy * 0.18;
      ring.style.transform = 'translate3d(' + rx + 'px,' + ry + 'px,0)';
    })();

    function teardown() {
      stopped = true;
      dot.remove();
      ring.remove();
      document.body.classList.remove('cursor-ready', 'cursor-hidden', 'cursor-link', 'cursor-text', 'cursor-drag');
    }
    onMediaChange(mqMotion, function (e) { if (e.matches) teardown(); });
    onMediaChange(mqPointer, function (e) { if (!e.matches) teardown(); });

    var LINK = 'a,button,summary,[role="button"],label';
    var TEXT = 'input[type="text"],input[type="email"],input[type="tel"],textarea';

    // Work out the intended state first and only touch the DOM when it
    // changes. Three classList.toggle() calls on <body> per pointerover
    // invalidated style for the whole document on every mouse move.
    var cursorState = '';
    document.addEventListener('pointerover', function (e) {
      var t = e.target;
      if (!t || !t.closest) return;

      var next = t.closest('[data-cursor="drag"]') ? 'drag'
        : t.closest(TEXT) ? 'text'
          : t.closest(LINK) ? 'link' : '';

      if (next === cursorState) return;
      if (cursorState) document.body.classList.remove('cursor-' + cursorState);
      if (next) document.body.classList.add('cursor-' + next);
      cursorState = next;
    });
  }

  /* ---------- Magnetic primary buttons ---------- */
  function initMagnetic() {
    document.querySelectorAll('.btn-primary').forEach(function (btn) {
      btn.addEventListener('pointermove', function (e) {
        if (!finePointer() || reduceMotion()) return;
        var r = btn.getBoundingClientRect();
        var x = (e.clientX - r.left - r.width / 2) * 0.16;
        var y = (e.clientY - r.top - r.height / 2) * 0.28;
        btn.style.transform = 'translate(' + x + 'px,' + (y - 2) + 'px)';
      });
      btn.addEventListener('pointerleave', function () { btn.style.transform = ''; });
    });
  }

  /* ---------- FAQ accordion ---------- */
  function initFaq() {
    var items = document.querySelectorAll('.faq-item');
    if (!items.length) return;

    // Panels are hidden outright, not just collapsed to zero height. A purely
    // visual collapse leaves every answer in the accessibility tree, so a
    // screen reader reads the whole FAQ straight through regardless of which
    // question is open.
    function setOpen(item, open) {
      var btn = item.querySelector('.faq-q');
      var panel = item.querySelector('.faq-a');
      item.classList.toggle('is-open', open);
      if (btn) btn.setAttribute('aria-expanded', String(open));
      if (!panel) return;

      if (open) {
        panel.hidden = false;
      } else if (item.dataset.gnsReady) {
        // Wait for the collapse transition before hiding, so the animation
        // still plays. On first paint there is nothing to animate.
        var done = function () {
          if (!item.classList.contains('is-open')) panel.hidden = true;
          panel.removeEventListener('transitionend', done);
        };
        panel.addEventListener('transitionend', done);
        window.setTimeout(done, 900);
      } else {
        panel.hidden = true;
      }
    }

    items.forEach(function (item) {
      var btn = item.querySelector('.faq-q');
      if (!btn) return;
      item.dataset.gnsReady = '1';

      btn.addEventListener('click', function () {
        var open = !item.classList.contains('is-open');
        // One open at a time keeps the section from growing unreadably tall.
        items.forEach(function (other) { setOpen(other, false); });
        if (open) setOpen(item, true);
      });
    });
  }

  /* ---------- Break-even calculator ----------
     The model works in gross profit, not revenue. Dividing total cost by job
     value answers "how much revenue covers the bill", which is not the same
     question and understates the work required by roughly the inverse of the
     margin — better than two to one at the numbers these trades run at.

     gns_calc() in admin/lib/store.php is a line-for-line twin of this, so the
     statically rendered page and the live widget always agree. Change one and
     you must change the other; tools/check-calc.php asserts they match. */
  function calcModel(o) {
    var spend = Math.max(0, o.spend);
    var job = Math.max(1, o.job);
    var close = Math.min(100, Math.max(1, o.close));
    var margin = Math.min(95, Math.max(5, o.margin));
    var fee = Math.max(0, o.fee);

    var profit = job * (margin / 100);
    var total = spend + fee;
    var jobs = Math.max(1, Math.ceil(total / profit));
    var leads = Math.max(1, Math.ceil(jobs / (close / 100)));
    var cpl = Math.floor(spend / leads);

    // A planning reference, not a promise: for considered local purchases a
    // workable cost per lead sits near 4.5% of job value, floored and capped
    // so a $200 detail and a $12,000 ring both land somewhere sane. Fixed
    // dollar thresholds cannot do that.
    var benchmark = Math.round(Math.min(450, Math.max(20, job * 0.045)));
    // "unviable" is not "difficult" — it is arithmetically out of reach, and
    // saying so is the whole point of publishing the model.
    var band = cpl < benchmark * 0.25 ? 'unviable'
      : cpl < benchmark * 0.6 ? 'tight'
        : cpl <= benchmark * 1.6 ? 'middle' : 'headroom';

    return {
      spend: spend, job: job, close: close, margin: margin, fee: fee,
      total: total, profit: profit, jobs: jobs, leads: leads,
      cpl: cpl, benchmark: benchmark, band: band
    };
  }

  function calcVerdict(r, money) {
    var cpl = money(r.cpl), bm = money(r.benchmark), jv = money(r.job);
    if (r.band === 'unviable') {
      return 'At these numbers the arithmetic does not work: covering the cost needs '
        + r.leads.toLocaleString('en-US') + ' leads a month out of ' + money(r.spend)
        + ' of media. Job value, margin or budget has to move before a retainer makes sense — '
        + 'and that is exactly the kind of thing we would tell you on the call.';
    }
    if (r.band === 'tight') {
      return 'At ' + cpl + ' per lead this is tight. For a ' + jv
        + ' job we would expect to plan around ' + bm
        + ', so the spend has to work harder than usual before the retainer pays for itself.';
    }
    if (r.band === 'middle') {
      return 'A ' + cpl + ' cost per lead is the honest middle of the range for a ' + jv
        + ' job. Achievable in most local markets, but not a given.';
    }
    return 'At ' + cpl + ' per lead you have real headroom — we would plan around ' + bm
      + ' for a job this size. High job values are where design-led creative pays for itself fastest.';
  }

  function initCalculator() {
    var root = document.getElementById('calc');
    if (!root) return;

    var spendEl = root.querySelector('#calc-spend');
    var jobEl = root.querySelector('#calc-job');
    var closeEl = root.querySelector('#calc-close');
    var marginEl = root.querySelector('#calc-margin');
    var tierBtns = root.querySelectorAll('[data-tier]');
    if (!spendEl || !jobEl || !closeEl || !marginEl) return;

    var out = {
      spend: root.querySelector('#out-spend'),
      job: root.querySelector('#out-job'),
      close: root.querySelector('#out-close'),
      margin: root.querySelector('#out-margin'),
      total: root.querySelector('#out-total'),
      breakdown: root.querySelector('#out-breakdown'),
      jobs: root.querySelector('#out-jobs'),
      jobsNote: root.querySelector('#out-jobs-note'),
      leads: root.querySelector('#out-leads'),
      leadsNote: root.querySelector('#out-leads-note'),
      cpl: root.querySelector('#out-cpl'),
      verdict: root.querySelector('#out-verdict'),
      barLeads: root.querySelector('#bar-leads'),
      barJobs: root.querySelector('#bar-jobs'),
      barLeadsLabel: root.querySelector('#bar-leads-label'),
      barJobsLabel: root.querySelector('#bar-jobs-label')
    };

    var nf = new Intl.NumberFormat('en-US', {
      style: 'currency', currency: 'USD', maximumFractionDigits: 0
    });
    function money(n) { return nf.format(Math.round(n)); }

    var active = root.querySelector('[data-tier].is-active') || tierBtns[0];
    var fee = active ? Number(active.getAttribute('data-fee')) || 0 : 0;

    function set(el, text) { if (el) el.textContent = text; }

    function update() {
      var r = calcModel({
        spend: Number(spendEl.value),
        job: Number(jobEl.value),
        close: Number(closeEl.value),
        margin: Number(marginEl.value),
        fee: fee
      });

      set(out.spend, money(r.spend));
      set(out.job, money(r.job));
      set(out.close, r.close + '%');
      set(out.margin, r.margin + '%');
      set(out.total, money(r.total));
      set(out.breakdown, money(r.spend) + ' media + ' + money(r.fee) + ' retainer');
      set(out.jobs, String(r.jobs));
      set(out.jobsNote, 'at ' + money(r.profit) + ' profit per job');
      set(out.leads, String(r.leads));
      set(out.leadsNote, 'at ' + r.close + '% close rate');
      set(out.cpl, money(r.cpl));
      set(out.verdict, calcVerdict(r, money));

      // Spoken values, so a slider reads as "$6,000" rather than "6000".
      spendEl.setAttribute('aria-valuetext', money(r.spend));
      jobEl.setAttribute('aria-valuetext', money(r.job));
      closeEl.setAttribute('aria-valuetext', r.close + ' percent');
      marginEl.setAttribute('aria-valuetext', r.margin + ' percent');

      if (out.barLeads) out.barLeads.style.width = '100%';
      if (out.barJobs) out.barJobs.style.width = Math.max(14, r.close) + '%';
      set(out.barLeadsLabel, r.leads + ' leads');
      set(out.barJobsLabel, r.jobs + ' jobs');

      [spendEl, jobEl, closeEl, marginEl].forEach(paintTrack);
    }

    // Paints the filled portion of the range track without extra elements.
    function paintTrack(el) {
      var min = Number(el.min), max = Number(el.max);
      var pct = ((Number(el.value) - min) / (max - min)) * 100;
      el.style.setProperty('--fill', pct + '%');
    }

    [spendEl, jobEl, closeEl, marginEl].forEach(function (el) {
      el.addEventListener('input', update);
    });

    tierBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        fee = Number(btn.getAttribute('data-fee')) || 0;
        tierBtns.forEach(function (b) {
          var on = b === btn;
          b.classList.toggle('is-active', on);
          b.setAttribute('aria-pressed', String(on));
        });
        update();
      });
    });

    update();
  }

  /* ---------- Before / after comparison ---------- */
  function initCompare() {
    var root = document.querySelector('.compare');
    if (!root) return;

    var range = root.querySelector('.compare-range');
    var handle = root.querySelector('.compare-handle');
    var dragging = false;

    function setSplit(pct) {
      var v = Math.max(3, Math.min(97, pct));
      root.style.setProperty('--split', v + '%');
      if (range) range.value = String(Math.round(v));
    }

    function fromEvent(e) {
      var r = root.getBoundingClientRect();
      setSplit(((e.clientX - r.left) / r.width) * 100);
    }

    // Near the split line, or on the handle. Starting a drag from anywhere in
    // the widget meant a thumb landing on the caption yanked the split across
    // the screen — which reads as a bug, not as an affordance.
    function isGrab(e) {
      if (e.target.closest('.compare-range')) return false;
      if (handle && e.target.closest('.compare-handle')) return true;
      var r = root.getBoundingClientRect();
      var splitX = r.left + (r.width * (parseFloat(getComputedStyle(root).getPropertyValue('--split')) || 50)) / 100;
      return Math.abs(e.clientX - splitX) < 56;
    }

    root.addEventListener('pointerdown', function (e) {
      root.classList.add('is-touched');
      if (!isGrab(e)) return;
      dragging = true;
      root.setPointerCapture(e.pointerId);
      fromEvent(e);
    });
    root.addEventListener('pointermove', function (e) { if (dragging) fromEvent(e); });
    root.addEventListener('pointerup', function () { dragging = false; });
    root.addEventListener('pointercancel', function () { dragging = false; });

    if (range) {
      range.addEventListener('input', function () { setSplit(Number(range.value)); });
    }

    setSplit(50);
  }

  /* ---------- Contact form ----------
     Progressive enhancement only. Without JavaScript the form posts normally
     and submit.php redirects to the thank-you page; with it, the submission
     happens in place and the visitor never loses what they typed if something
     goes wrong. */
  function initForm() {
    var form = document.getElementById('form');
    if (!form) return;

    var started = form.querySelector('input[name="_started"]');
    if (started) started.value = String(Date.now());

    // Formspree and friends want the reply-to filled in from the email field.
    var replyto = form.querySelector('input[name="_replyto"]');
    var emailEl = form.querySelector('#email');
    if (replyto && emailEl) {
      form.addEventListener('submit', function () { replyto.value = emailEl.value; });
    }

    if (!form.dataset.ajax || !window.fetch || !window.FormData) return;

    var status = document.getElementById('form-status');
    var button = form.querySelector('button[type="submit"]');

    function say(message, state) {
      if (!status) return;
      status.textContent = message;
      status.className = 'form-status is-shown' + (state ? ' is-' + state : '');
    }

    form.addEventListener('submit', function (e) {
      if (!form.checkValidity()) return;   // let the browser show its own messages
      e.preventDefault();

      say('Sending…', 'busy');
      if (button) button.disabled = true;

      fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { Accept: 'application/json' },
        credentials: 'same-origin'
      })
        .then(function (res) { return res.json().catch(function () { return { ok: res.ok }; }); })
        .then(function (data) {
          if (!data || !data.ok) throw new Error(data && data.error ? data.error : 'send failed');
          say('Sent. Redirecting…', '');
          window.location.href = data.redirect || 'thanks.html';
        })
        .catch(function () {
          if (button) button.disabled = false;
          say('That did not send. Try again, or email us directly — the address is in the footer.', 'error');
        });
    });
  }

  /* ---------- Scheduler ----------
     The calendar frame is built on demand. A third-party iframe that most
     visitors never open should not be part of everybody's page load. */
  function initScheduler() {
    var host = document.querySelector('[data-scheduler]');
    if (!host) return;
    var button = host.querySelector('[data-scheduler-open]');
    if (!button) return;

    button.addEventListener('click', function () {
      var frame = document.createElement('iframe');
      frame.src = host.getAttribute('data-scheduler');
      frame.title = 'Booking calendar';
      frame.loading = 'lazy';
      frame.setAttribute('allow', 'camera; microphone; fullscreen; payment');
      host.innerHTML = '';
      host.appendChild(frame);
    });
  }

  /* ---------- Cookie consent ----------
     Only present when the banner is switched on in the admin. */
  function initConsent() {
    var bar = document.getElementById('consent');
    if (!bar) return;

    function read() {
      try { return localStorage.getItem('gns-consent'); } catch (e) { return null; }
    }
    function write(value) {
      try { localStorage.setItem('gns-consent', value); } catch (e) { /* private mode */ }
    }

    if (read()) return;
    bar.hidden = false;

    bar.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-consent]');
      if (!btn) return;
      var answer = btn.getAttribute('data-consent');
      write(answer);
      bar.hidden = true;

      if (answer !== 'yes') return;
      window.gnsConsent = true;
      if (typeof window.gtag === 'function') {
        window.gtag('consent', 'update', {
          ad_storage: 'granted', analytics_storage: 'granted',
          ad_user_data: 'granted', ad_personalization: 'granted'
        });
      }
      if (typeof window.fbq === 'function' && bar.dataset.pixel) {
        window.fbq('init', bar.dataset.pixel);
        window.fbq('track', 'PageView');
      }
    });
  }

  /* ---------- Conversion event ----------
     Fires once on the thank-you page, which is the only place we know a lead
     actually completed. */
  function initConversion() {
    var marker = document.querySelector('[data-conversion="lead"]');
    if (!marker || !window.gnsConsent) return;

    if (typeof window.gtag === 'function') {
      window.gtag('event', 'generate_lead', { event_category: 'contact', value: 1 });
    }
    if (typeof window.fbq === 'function') {
      window.fbq('track', 'Lead');
    }
  }

  /* ---------- Hero shader ----------
     A full-bleed fragment shader: two drifting light bodies (warm gold,
     cool steel) over the void navy, domain-warped by fbm, with grain and
     a falloff into the page background. The CSS gradient underneath is
     the real fallback — the canvas only fades in once a frame has
     actually rendered, so a WebGL failure is invisible.

     Deferred until the main thread is idle, and skipped entirely below the
     900px breakpoint: on a phone the hero is smaller, the GPU is weaker, the
     battery cost is a real cost to the visitor, and the gradient fallback is
     good enough that nobody would know the difference. */
  function initHeroShader() {
    var canvas = document.getElementById('hero-canvas');
    if (!canvas || reduceMotion() || !mqWide.matches) return;

    var idle = window.requestIdleCallback || function (fn) { return window.setTimeout(fn, 200); };
    idle(function () { safe(function startShader() { buildShader(canvas); }); }, { timeout: 2000 });
  }

  function buildShader(canvas) {
    var gl;
    function context() {
      try {
        return canvas.getContext('webgl', {
          antialias: false, alpha: false, depth: false, powerPreference: 'low-power'
        }) || canvas.getContext('experimental-webgl');
      } catch (err) { return null; }
    }
    gl = context();
    if (!gl) return;

    var VERT = [
      'attribute vec2 a;',
      'void main(){ gl_Position = vec4(a, 0.0, 1.0); }'
    ].join('\n');

    var FRAG = [
      'precision mediump float;',
      'uniform vec2 u_res;',
      'uniform float u_time;',
      'uniform vec2 u_mouse;',

      'float hash(vec2 p){ return fract(sin(dot(p, vec2(127.1, 311.7))) * 43758.5453123); }',

      'float noise(vec2 p){',
      '  vec2 i = floor(p), f = fract(p);',
      '  vec2 u = f * f * (3.0 - 2.0 * f);',
      '  return mix(mix(hash(i), hash(i + vec2(1.0, 0.0)), u.x),',
      '             mix(hash(i + vec2(0.0, 1.0)), hash(i + vec2(1.0, 1.0)), u.x), u.y);',
      '}',

      // Three octaves, not five. At this blur scale the last two were
      // invisible and cost roughly 40% of the fragment work.
      'float fbm(vec2 p){',
      '  float v = 0.0, a = 0.5;',
      '  for (int i = 0; i < 3; i++) { v += a * noise(p); p *= 2.03; a *= 0.5; }',
      '  return v;',
      '}',

      'void main(){',
      '  vec2 uv = gl_FragCoord.xy / u_res.xy;',
      '  vec2 p = (gl_FragCoord.xy - 0.5 * u_res.xy) / u_res.y;',
      '  float t = u_time * 0.055;',

      // domain warp — this is what stops it looking like a plain radial gradient
      '  vec2 q = vec2(fbm(p * 1.3 + vec2(t, 0.0)), fbm(p * 1.3 + vec2(5.2, 1.3) - t));',
      '  float f = fbm(p * 1.5 + q * 0.9 + t * 0.4);',

      '  vec3 base  = vec3(0.000, 0.020, 0.059);',
      '  vec3 gold  = vec3(0.824, 0.678, 0.361);',
      '  vec3 steel = vec3(0.404, 0.522, 0.769);',

      '  vec2 m = u_mouse * 0.10;',
      '  float g1 = exp(-2.3 * length(p - vec2(-0.78 + m.x,  0.30 + m.y)));',
      '  float g2 = exp(-2.7 * length(p - vec2( 0.82 - m.x, -0.10 - m.y)));',
      '  float g3 = exp(-3.1 * length(p - vec2( 0.14, -0.66)));',

      '  vec3 col = base;',
      '  col += gold  * (g1 * 1.20 + g3 * 0.55) * (0.50 + 0.90 * f);',
      '  col += steel * g2 * (0.28 + 0.50 * f);',
      '  col += gold  * pow(f, 3.0) * 0.14;',

      // vignette, then fade into the page background along the bottom edge
      '  col *= 1.0 - 0.52 * pow(length(p * vec2(0.7, 1.0)), 2.2);',
      '  col = mix(col, vec3(0.000, 0.031, 0.102), smoothstep(0.34, 0.0, uv.y));',

      '  col += (hash(gl_FragCoord.xy + fract(u_time) * 17.0) - 0.5) * 0.028;',

      '  gl_FragColor = vec4(col, 1.0);',
      '}'
    ].join('\n');

    function compile(type, src) {
      var s = gl.createShader(type);
      gl.shaderSource(s, src);
      gl.compileShader(s);
      if (!gl.getShaderParameter(s, gl.COMPILE_STATUS)) { gl.deleteShader(s); return null; }
      return s;
    }

    var uRes, uTime, uMouse;

    function build() {
      var vs = compile(gl.VERTEX_SHADER, VERT);
      var fs = compile(gl.FRAGMENT_SHADER, FRAG);
      if (!vs || !fs) return false;

      var prog = gl.createProgram();
      gl.attachShader(prog, vs);
      gl.attachShader(prog, fs);
      gl.linkProgram(prog);
      if (!gl.getProgramParameter(prog, gl.LINK_STATUS)) return false;
      gl.useProgram(prog);

      // One oversized triangle covers the clip volume with no seam.
      var buf = gl.createBuffer();
      gl.bindBuffer(gl.ARRAY_BUFFER, buf);
      gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1, -1, 3, -1, -1, 3]), gl.STATIC_DRAW);
      var loc = gl.getAttribLocation(prog, 'a');
      gl.enableVertexAttribArray(loc);
      gl.vertexAttribPointer(loc, 2, gl.FLOAT, false, 0, 0);

      uRes = gl.getUniformLocation(prog, 'u_res');
      uTime = gl.getUniformLocation(prog, 'u_time');
      uMouse = gl.getUniformLocation(prog, 'u_mouse');
      return true;
    }

    if (!build()) return;

    var mx = 0, my = 0, tmx = 0, tmy = 0;
    var painted = false;
    var visible = true;
    var lost = false;

    function resize() {
      var dpr = Math.min(window.devicePixelRatio || 1, 1.5);
      var w = Math.round(canvas.clientWidth * dpr);
      var h = Math.round(canvas.clientHeight * dpr);
      if (w === canvas.width && h === canvas.height) return;
      canvas.width = w;
      canvas.height = h;
      gl.viewport(0, 0, w, h);
    }

    window.addEventListener('pointermove', function (e) {
      if (!finePointer()) return;
      var r = canvas.getBoundingClientRect();
      tmx = ((e.clientX - r.left) / r.width) * 2 - 1;
      tmy = 1 - ((e.clientY - r.top) / r.height) * 2;
    }, { passive: true });

    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (entries) {
        visible = entries[0].isIntersecting;
      }, { threshold: 0 }).observe(canvas);
    }

    // A GPU process restart mid-session is routine on Windows laptops with
    // hybrid graphics. Without this the canvas keeps .is-live and stops
    // painting, leaving a dead black rectangle on top of the gradient that
    // would otherwise have covered for it. Dropping the class reverts to that
    // gradient, which is the graceful degradation the design already has.
    canvas.addEventListener('webglcontextlost', function (e) {
      e.preventDefault();
      lost = true;
      painted = false;
      canvas.classList.remove('is-live');
    });

    canvas.addEventListener('webglcontextrestored', function () {
      canvas.width = 0;           // force resize() to rebuild the viewport
      if (build()) lost = false;
    });

    // Reduce Motion turned on mid-session stops the animation and hands the
    // hero back to the static gradient.
    var stopped = false;
    onMediaChange(mqMotion, function (e) {
      if (!e.matches) return;
      stopped = true;
      canvas.classList.remove('is-live');
    });

    var start = performance.now();
    function frame(now) {
      if (stopped) return;
      requestAnimationFrame(frame);
      if (!visible || lost || document.hidden) return;

      resize();
      mx += (tmx - mx) * 0.05;
      my += (tmy - my) * 0.05;

      gl.uniform2f(uRes, canvas.width, canvas.height);
      gl.uniform1f(uTime, (now - start) / 1000);
      gl.uniform2f(uMouse, mx, my);
      gl.drawArrays(gl.TRIANGLES, 0, 3);

      if (!painted) {
        painted = true;
        canvas.classList.add('is-live');
      }
    }
    requestAnimationFrame(frame);
  }
})();
