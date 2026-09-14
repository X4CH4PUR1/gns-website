/* =========================================================
   GN SCALES — SITE BEHAVIOR
   Loaded on every page. Everything is feature-detected and
   guarded: any block whose target element is absent, or whose
   API the browser lacks, quietly does nothing.
   No dependencies.
   ========================================================= */

(function () {
  'use strict';

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

  document.addEventListener('DOMContentLoaded', function () {
    setYear();
    initNav();
    initHeaderState();
    initReveal();
    initCardSpotlight();
    initCursor();
    initMagnetic();
    initFaq();
    initCalculator();
    initCompare();
    initHeroShader();
    initViewTransitions();
  });

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

    function close() {
      document.body.classList.remove('nav-open');
      toggle.setAttribute('aria-expanded', 'false');
    }

    toggle.addEventListener('click', function () {
      var open = !document.body.classList.contains('nav-open');
      document.body.classList.toggle('nav-open', open);
      toggle.setAttribute('aria-expanded', String(open));
    });

    nav.addEventListener('click', function (e) {
      if (e.target.closest('a')) close();
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && document.body.classList.contains('nav-open')) {
        close();
        toggle.focus();
      }
    });
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

    if (reduceMotion || !('IntersectionObserver' in window)) {
      targets.forEach(function (el) { el.classList.add('is-in'); });
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
  }

  /* ---------- Cursor-tracked card glow ---------- */
  function initCardSpotlight() {
    if (!finePointer) return;
    document.querySelectorAll('.card').forEach(function (card) {
      card.addEventListener('pointermove', function (e) {
        var r = card.getBoundingClientRect();
        card.style.setProperty('--mx', ((e.clientX - r.left) / r.width) * 100 + '%');
        card.style.setProperty('--my', ((e.clientY - r.top) / r.height) * 100 + '%');
      });
    });
  }

  /* ---------- Custom cursor ---------- */
  function initCursor() {
    if (!finePointer || reduceMotion) return;

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
    (function frame() {
      rx += (tx - rx) * 0.18;
      ry += (ty - ry) * 0.18;
      dot.style.transform = 'translate3d(' + tx + 'px,' + ty + 'px,0)';
      ring.style.transform = 'translate3d(' + rx + 'px,' + ry + 'px,0)';
      requestAnimationFrame(frame);
    })();

    var LINK = 'a,button,summary,[role="button"],label';
    var TEXT = 'input[type="text"],input[type="email"],input[type="tel"],textarea';

    document.addEventListener('pointerover', function (e) {
      var t = e.target;
      if (!t || !t.closest) return;
      document.body.classList.toggle('cursor-drag', !!t.closest('[data-cursor="drag"]'));
      document.body.classList.toggle('cursor-text', !!t.closest(TEXT));
      document.body.classList.toggle(
        'cursor-link',
        !!t.closest(LINK) && !t.closest('[data-cursor="drag"]')
      );
    });
  }

  /* ---------- Magnetic primary buttons ---------- */
  function initMagnetic() {
    if (!finePointer || reduceMotion) return;
    document.querySelectorAll('.btn-primary').forEach(function (btn) {
      btn.addEventListener('pointermove', function (e) {
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

    items.forEach(function (item) {
      var btn = item.querySelector('.faq-q');
      if (!btn) return;
      btn.addEventListener('click', function () {
        var open = !item.classList.contains('is-open');
        // One open at a time keeps the section from growing unreadably tall.
        items.forEach(function (other) {
          other.classList.remove('is-open');
          var b = other.querySelector('.faq-q');
          if (b) b.setAttribute('aria-expanded', 'false');
        });
        if (open) {
          item.classList.add('is-open');
          btn.setAttribute('aria-expanded', 'true');
        }
      });
    });
  }

  /* ---------- Break-even calculator ---------- */
  function initCalculator() {
    var root = document.getElementById('calc');
    if (!root) return;

    var spendEl = root.querySelector('#calc-spend');
    var jobEl = root.querySelector('#calc-job');
    var closeEl = root.querySelector('#calc-close');
    var tierBtns = root.querySelectorAll('[data-tier]');
    if (!spendEl || !jobEl || !closeEl) return;

    var out = {
      spend: root.querySelector('#out-spend'),
      job: root.querySelector('#out-job'),
      close: root.querySelector('#out-close'),
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

    var fmt = new Intl.NumberFormat('en-US', {
      style: 'currency', currency: 'USD', maximumFractionDigits: 0
    });
    var tier = 'growth';

    function update() {
      var spend = Number(spendEl.value);
      var job = Number(jobEl.value);
      var close = Number(closeEl.value);
      var fee = tier === 'growth' ? 2500 : 1500;
      var total = spend + fee;
      var jobs = Math.max(1, Math.ceil(total / job));
      var leads = Math.max(1, Math.ceil(jobs / (close / 100)));
      var cpl = Math.floor(spend / leads);

      if (out.spend) out.spend.textContent = fmt.format(spend);
      if (out.job) out.job.textContent = fmt.format(job);
      if (out.close) out.close.textContent = close + '%';
      if (out.total) out.total.textContent = fmt.format(total);
      if (out.breakdown) out.breakdown.textContent = fmt.format(spend) + ' media + ' + fmt.format(fee) + ' retainer';
      if (out.jobs) out.jobs.textContent = String(jobs);
      if (out.jobsNote) out.jobsNote.textContent = 'at ' + fmt.format(job) + ' average';
      if (out.leads) out.leads.textContent = String(leads);
      if (out.leadsNote) out.leadsNote.textContent = 'at ' + close + '% close rate';
      if (out.cpl) out.cpl.textContent = fmt.format(cpl);

      if (out.barLeads) out.barLeads.style.width = '100%';
      if (out.barJobs) out.barJobs.style.width = Math.max(14, close) + '%';
      if (out.barLeadsLabel) out.barLeadsLabel.textContent = leads + ' leads';
      if (out.barJobsLabel) out.barJobsLabel.textContent = jobs + ' jobs';

      if (out.verdict) {
        var v;
        if (cpl < 25) {
          v = 'A ' + fmt.format(cpl) + ' cost per lead is below what most paid channels deliver. At this job value the retainer is doing a lot of the lifting — worth a hard look before committing.';
        } else if (cpl < 120) {
          v = 'A ' + fmt.format(cpl) + ' cost per lead is achievable in most local markets, but it is not a given. This is the honest middle of the range.';
        } else {
          v = 'At ' + fmt.format(cpl) + ' per lead you have real headroom. High job values are exactly where design-led creative pays for itself fastest.';
        }
        out.verdict.textContent = v;
      }

      [spendEl, jobEl, closeEl].forEach(paintTrack);
    }

    // Paints the filled portion of the range track without extra elements.
    function paintTrack(el) {
      var min = Number(el.min), max = Number(el.max);
      var pct = ((Number(el.value) - min) / (max - min)) * 100;
      el.style.setProperty('--fill', pct + '%');
    }

    [spendEl, jobEl, closeEl].forEach(function (el) {
      el.addEventListener('input', update);
    });

    tierBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        tier = btn.getAttribute('data-tier');
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

    root.addEventListener('pointerdown', function (e) {
      // The range input keeps its own keyboard and pointer behavior.
      if (e.target.closest('.compare-range')) return;
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

  /* ---------- Hero shader ----------
     A full-bleed fragment shader: two drifting light bodies (warm gold,
     cool steel) over the void navy, domain-warped by fbm, with grain and
     a falloff into the page background. The CSS gradient underneath is
     the real fallback — the canvas only fades in once a frame has
     actually rendered, so a WebGL failure is invisible.               */
  function initHeroShader() {
    var canvas = document.getElementById('hero-canvas');
    if (!canvas) return;
    if (reduceMotion) return;

    var gl;
    try {
      gl = canvas.getContext('webgl', { antialias: false, alpha: false, depth: false, powerPreference: 'low-power' })
        || canvas.getContext('experimental-webgl');
    } catch (err) { return; }
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

      'float fbm(vec2 p){',
      '  float v = 0.0, a = 0.5;',
      '  for (int i = 0; i < 5; i++) { v += a * noise(p); p *= 2.03; a *= 0.5; }',
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

    var vs = compile(gl.VERTEX_SHADER, VERT);
    var fs = compile(gl.FRAGMENT_SHADER, FRAG);
    if (!vs || !fs) return;

    var prog = gl.createProgram();
    gl.attachShader(prog, vs);
    gl.attachShader(prog, fs);
    gl.linkProgram(prog);
    if (!gl.getProgramParameter(prog, gl.LINK_STATUS)) return;
    gl.useProgram(prog);

    // One oversized triangle covers the clip volume with no seam.
    var buf = gl.createBuffer();
    gl.bindBuffer(gl.ARRAY_BUFFER, buf);
    gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1, -1, 3, -1, -1, 3]), gl.STATIC_DRAW);
    var loc = gl.getAttribLocation(prog, 'a');
    gl.enableVertexAttribArray(loc);
    gl.vertexAttribPointer(loc, 2, gl.FLOAT, false, 0, 0);

    var uRes = gl.getUniformLocation(prog, 'u_res');
    var uTime = gl.getUniformLocation(prog, 'u_time');
    var uMouse = gl.getUniformLocation(prog, 'u_mouse');

    var mx = 0, my = 0, tmx = 0, tmy = 0;
    var painted = false;
    var visible = true;

    function resize() {
      var dpr = Math.min(window.devicePixelRatio || 1, 1.5);
      var w = Math.round(canvas.clientWidth * dpr);
      var h = Math.round(canvas.clientHeight * dpr);
      if (w === canvas.width && h === canvas.height) return;
      canvas.width = w;
      canvas.height = h;
      gl.viewport(0, 0, w, h);
    }

    if (finePointer) {
      window.addEventListener('pointermove', function (e) {
        var r = canvas.getBoundingClientRect();
        tmx = ((e.clientX - r.left) / r.width) * 2 - 1;
        tmy = 1 - ((e.clientY - r.top) / r.height) * 2;
      }, { passive: true });
    }

    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (entries) {
        visible = entries[0].isIntersecting;
      }, { threshold: 0 }).observe(canvas);
    }

    var start = performance.now();
    function frame(now) {
      requestAnimationFrame(frame);
      if (!visible || document.hidden) return;

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

  /* ---------- Cross-page transitions ---------- */
  function initViewTransitions() {
    if (!document.startViewTransition || reduceMotion) return;

    document.addEventListener('click', function (e) {
      var link = e.target.closest && e.target.closest('a[href]');
      if (!link) return;
      if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) return;
      if (link.target === '_blank' || link.hasAttribute('download')) return;

      var href = link.getAttribute('href');
      if (!href || href.charAt(0) === '#' || /^[a-z]+:/i.test(href)) return;

      e.preventDefault();
      document.startViewTransition(function () { window.location.href = href; });
    });
  }
})();
