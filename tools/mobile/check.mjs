// GN SCALES — MOBILE USABILITY CHECK
//
// Not part of the site: tools/ is excluded from the deploy.
//
//   cd tools/mobile && npm install && npx playwright install chromium
//   python -m http.server 8080        (from the repository root, separately)
//   node check.mjs [--label before]
//
// Loads every page in sitemap.xml plus thanks.html, ka/thanks.html and
// 404.html at 375x812 and 1280x800, and reports overflow, small or crowded
// tap targets, small text, content hidden under the sticky header after an
// anchor jump, whether a booking link is in the first mobile screen, the
// mobile menu's behaviour and the contact form at 375 px. Full-page
// screenshots and a JSON report go to tools/mobile/out/.

import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const HERE = path.dirname(fileURLToPath(import.meta.url));
const ROOT = path.resolve(HERE, '..', '..');
const OUT = path.join(HERE, 'out');
const BASE = process.env.BASE_URL || 'http://127.0.0.1:8080/';
const labelArg = process.argv.indexOf('--label');
const LABEL = labelArg > -1 ? process.argv[labelArg + 1] : 'latest';

const VIEWPORTS = [
  { name: '375', width: 375, height: 812, mobile: true },
  { name: '1280', width: 1280, height: 800, mobile: false },
];
const TAP_MIN = 44;
const TAP_GAP = 8;
const TEXT_MIN = 16;

fs.mkdirSync(OUT, { recursive: true });

// ---------- Pages ----------------------------------------------------------
const sitemap = fs.readFileSync(path.join(ROOT, 'sitemap.xml'), 'utf8');
const fromSitemap = [...sitemap.matchAll(/<loc>https:\/\/gnscales\.com\/([^<]*)<\/loc>/g)]
  .map((m) => m[1] || 'index.html');
const listed = [...fromSitemap, 'thanks.html', 'ka/thanks.html', '404.html'];
const pages = listed.filter((p) => fs.existsSync(path.join(ROOT, p)));
const missing = listed.filter((p) => !pages.includes(p));

// Every #fragment some page links to, keyed by the page it lands on.
const anchorTargets = {};
for (const p of pages) {
  const src = fs.readFileSync(path.join(ROOT, p), 'utf8');
  for (const m of src.matchAll(/href="([^"#]*)#([^"]+)"/g)) {
    let target = m[1] === '' ? p : m[1] === './' ? 'index.html' : m[1].replace(/^\.\//, '');
    if (/^(https?:|mailto:)/.test(target)) continue;
    (anchorTargets[target] ||= new Set()).add(m[2]);
  }
}

// ---------- In-page helpers (run in the browser) ---------------------------
const PAGE_HELPERS = () => {
  window.__desc = (el) => {
    if (!el) return '?';
    let s = el.tagName.toLowerCase();
    if (el.id) s += '#' + el.id;
    const cls = [...el.classList].slice(0, 2);
    if (cls.length) s += '.' + cls.join('.');
    if (!el.id && !cls.length && el.parentElement) {
      const p = el.parentElement;
      const pc = [...p.classList].slice(0, 1);
      s = p.tagName.toLowerCase() + (pc.length ? '.' + pc[0] : '') + ' > ' + s;
    }
    const t = (el.getAttribute('aria-label') || el.textContent || el.value || '').replace(/\s+/g, ' ').trim();
    if (t) s += ' "' + t.slice(0, 32) + (t.length > 32 ? '…' : '') + '"';
    return s;
  };
  window.__visible = (el) => {
    if (el.closest('[hidden], [inert], [aria-hidden="true"]')) return false;
    const cs = getComputedStyle(el);
    if (cs.display === 'none' || cs.visibility === 'hidden' || parseFloat(cs.opacity) === 0) return false;
    const r = el.getBoundingClientRect();
    if (r.width < 2 || r.height < 2) return false;
    // sr-only and similar: clipped down to nothing.
    if (cs.clip === 'rect(0px, 0px, 0px, 0px)' || cs.clipPath === 'inset(50%)') return false;
    // Off-screen until focused (skip link), or parked off-canvas (drawer).
    const x = r.left + scrollX, y = r.top + scrollY;
    if (x + r.width <= 0 || y + r.height <= 0 || r.left >= innerWidth) return false;
    for (let a = el.parentElement; a; a = a.parentElement) {
      const ac = getComputedStyle(a);
      if (parseFloat(ac.opacity) === 0 || ac.visibility === 'hidden') return false;
    }
    return true;
  };
  window.__inFixed = (el) => {
    for (let a = el; a; a = a.parentElement) {
      if (getComputedStyle(a).position === 'fixed') return true;
    }
    return false;
  };
};

// ---------- Checks ---------------------------------------------------------
async function prepare(page) {
  await page.evaluate(() => document.fonts.ready);
  // Scroll down in steps so every IntersectionObserver reveal fires.
  await page.evaluate(async () => {
    const step = Math.max(200, innerHeight * 0.6);
    for (let y = 0; y < document.documentElement.scrollHeight; y += step) {
      window.scrollTo({ top: y, behavior: 'instant' });
      await new Promise((r) => setTimeout(r, 60));
    }
    window.scrollTo({ top: document.documentElement.scrollHeight, behavior: 'instant' });
    await new Promise((r) => setTimeout(r, 400));
    window.scrollTo({ top: 0, behavior: 'instant' });
  });
  await page.waitForTimeout(900);
  await page.evaluate(PAGE_HELPERS);
}

async function checkOverflow(page) {
  return page.evaluate(() => {
    const out = [];
    const sw = document.documentElement.scrollWidth;
    if (sw > innerWidth) out.push({ issue: `page scrolls sideways (scrollWidth ${sw} > ${innerWidth})`, element: 'html' });
    // html and body clip overflow-x, so a wide element is cut off rather than
    // scrollable. Report anything whose nearest clipping ancestor is html/body.
    const culprits = [];
    for (const el of document.body.querySelectorAll('*')) {
      if (__inFixed(el)) continue;
      const r = el.getBoundingClientRect();
      if (r.width === 0 || r.height === 0) continue;
      if (r.right <= innerWidth + 1 && r.left >= -1) continue;
      let clipped = false;
      for (let a = el.parentElement; a && a !== document.body; a = a.parentElement) {
        const ox = getComputedStyle(a).overflowX;
        if (ox !== 'visible') { clipped = true; break; }
      }
      if (clipped) continue;
      if (culprits.some((c) => c.contains(el))) continue;
      culprits.push(el);
    }
    for (const el of culprits) {
      const r = el.getBoundingClientRect();
      out.push({ issue: `sticks out of the viewport (${Math.round(r.left)}..${Math.round(r.right)} of ${innerWidth})`, element: __desc(el) });
    }
    return out;
  });
}

async function checkTapTargets(page) {
  // Measure from the top: the sticky header moves with the scroll position.
  await page.evaluate(() => window.scrollTo({ top: 0, behavior: 'instant' }));
  await page.waitForTimeout(500);
  return page.evaluate(({ TAP_MIN, TAP_GAP }) => {
    const out = [];
    const pinned = (el) => !!el.closest('.site-header');
    const sel = 'a[href], button, input:not([type=hidden]), select, textarea, summary, [role=button]';
    const targets = [];
    for (let el of document.querySelectorAll(sel)) {
      // A visually hidden radio or checkbox is tapped through its label.
      if (el.matches('input[type=radio], input[type=checkbox]') && el.closest('label')) {
        const cs = getComputedStyle(el);
        if (parseFloat(cs.opacity) === 0 || el.getBoundingClientRect().width < 4) el = el.closest('label');
      }
      if (!__visible(el)) continue;
      if (el.tabIndex < 0 && el.tagName !== 'LABEL') continue;   // spam trap
      // Links inside running text are exempt: the paragraph (or list item or
      // table cell) has words of its own besides its links. "Privacy · Terms"
      // has none, so those two are still checked.
      if (el.tagName === 'A') {
        const block = el.closest('p, li, td, dd');
        if (block) {
          const rest = block.cloneNode(true);
          rest.querySelectorAll('a').forEach((a) => a.remove());
          if (/\p{L}/u.test(rest.textContent)) continue;
        }
      }
      if (targets.includes(el)) continue;
      targets.push(el);
    }
    for (const el of targets) {
      const r = el.getBoundingClientRect();
      if (r.width < TAP_MIN - 0.5 || r.height < TAP_MIN - 0.5) {
        out.push({ check: 'tap-size', issue: `tap target ${Math.round(r.width)}x${Math.round(r.height)} < ${TAP_MIN}x${TAP_MIN}`, element: __desc(el) });
      }
    }
    const rects = targets.map((el) => { const r = el.getBoundingClientRect(); return { el, l: r.left + scrollX, t: r.top + scrollY, r: r.right + scrollX, b: r.bottom + scrollY }; });
    for (let i = 0; i < rects.length; i++) {
      for (let j = i + 1; j < rects.length; j++) {
        const a = rects[i], b = rects[j];
        if (a.el.contains(b.el) || b.el.contains(a.el)) continue;
        // The sticky header floats over the page, so its targets are only
        // compared with each other.
        if (pinned(a.el) !== pinned(b.el)) continue;
        const dx = Math.max(0, Math.max(a.l, b.l) - Math.min(a.r, b.r));
        const dy = Math.max(0, Math.max(a.t, b.t) - Math.min(a.b, b.b));
        const gap = Math.hypot(dx, dy);
        if (gap < TAP_GAP - 0.5) {
          out.push({ check: 'tap-gap', issue: `targets ${gap.toFixed(1)}px apart (< ${TAP_GAP})`, element: __desc(a.el) + ' ↔ ' + __desc(b.el) });
        }
      }
    }
    return out;
  }, { TAP_MIN, TAP_GAP });
}

async function checkText(page, mobile) {
  return page.evaluate(({ TEXT_MIN, mobile }) => {
    const groups = new Map();
    const add = (issue, el) => {
      const key = issue + '|' + __desc(el).replace(/ ".*$/, '');
      const g = groups.get(key) || { issue, element: __desc(el), n: 0 };
      g.n++; groups.set(key, g);
    };
    for (const el of document.querySelectorAll('p, li, td, label')) {
      if (!__visible(el)) continue;
      const own = [...el.childNodes].some((n) => n.nodeType === 3 && n.textContent.trim());
      // Text wrapped in a plain inline child (<span>, <a>, <em>) counts too.
      const inline = [...el.children].some((c) => getComputedStyle(c).display.startsWith('inline') && c.textContent.trim() && !c.querySelector('svg'));
      if (!own && !inline) continue;
      const fs = parseFloat(getComputedStyle(el).fontSize);
      if (fs < TEXT_MIN - 0.05) add(`body text ${fs.toFixed(1)}px < ${TEXT_MIN}px`, el);
    }
    if (mobile) {
      for (const el of document.querySelectorAll('input:not([type=hidden]):not([type=radio]):not([type=checkbox]):not([type=range]), select, textarea')) {
        if (el.tabIndex < 0) continue;
        const fs = parseFloat(getComputedStyle(el).fontSize);
        if (fs < TEXT_MIN - 0.05) add(`form field ${fs.toFixed(1)}px < ${TEXT_MIN}px (iPhone zooms on focus)`, el);
      }
    }
    return [...groups.values()].map((g) => ({ issue: g.issue + (g.n > 1 ? ` (x${g.n})` : ''), element: g.element }));
  }, { TEXT_MIN, mobile });
}

async function checkAnchors(page, ids) {
  const out = [];
  for (const id of ids) {
    const res = await page.evaluate(async (id) => {
      const el = document.getElementById(id);
      if (!el) return { missing: true };
      el.scrollIntoView({ behavior: 'instant', block: 'start' });
      await new Promise((r) => setTimeout(r, 450));        // header shrinks on scroll
      const header = document.querySelector('.site-header');
      const hb = header ? header.getBoundingClientRect().bottom : 0;
      // The first thing a reader would look for: the first visible text.
      let first = null;
      const walker = document.createTreeWalker(el, NodeFilter.SHOW_TEXT);
      while (walker.nextNode()) {
        const n = walker.currentNode;
        if (!n.textContent.trim()) continue;
        const pe = n.parentElement;
        if (!__visible(pe)) continue;
        const range = document.createRange(); range.selectNodeContents(n);
        const rr = range.getBoundingClientRect();
        if (rr.height > 0) { first = { top: rr.top, el: pe }; break; }
      }
      const top = first ? first.top : el.getBoundingClientRect().top;
      const atBottom = Math.ceil(scrollY + innerHeight) >= document.documentElement.scrollHeight - 1;
      return { hb, top, atBottom, desc: __desc(first ? first.el : el) };
    }, id);
    if (res.missing) { out.push({ issue: `link points at #${id}, which does not exist`, element: '#' + id }); continue; }
    if (res.top < res.hb - 0.5 && !res.atBottom) {
      out.push({ issue: `#${id}: content ${Math.round(res.hb - res.top)}px under the header (${Math.round(res.hb)}px)`, element: res.desc });
    }
  }
  await page.evaluate(() => window.scrollTo({ top: 0, behavior: 'instant' }));
  return out;
}

async function checkFirstScreenCta(page) {
  return page.evaluate(() => {
    window.scrollTo({ top: 0, behavior: 'instant' });
    const links = [...document.querySelectorAll('a[href*="contact.html"]')];
    const seen = links.filter((a) => {
      if (!__visible(a)) return false;
      const r = a.getBoundingClientRect();
      if (r.top < 0 || r.bottom > innerHeight || r.left < 0 || r.right > innerWidth) return false;
      const hit = document.elementFromPoint(r.left + r.width / 2, r.top + r.height / 2);
      return hit && (a === hit || a.contains(hit));
    });
    return seen.length ? [] : [{ issue: 'no "Book a call" / contact link in the first screen', element: '-' }];
  });
}

async function checkMenu(page) {
  const out = [];
  const fail = (issue, element = '.site-nav') => out.push({ issue, element });
  const toggle = page.locator('.nav-toggle');
  if (!(await toggle.isVisible())) return [{ issue: 'menu button not visible', element: '.nav-toggle' }];

  // Start part-way down, so "page scrolls behind the menu" is measurable.
  await page.evaluate(() => window.scrollTo({ top: 600, behavior: 'instant' }));
  await page.waitForTimeout(300);
  await toggle.click();
  await page.waitForTimeout(600);
  const opened = await page.evaluate(() => {
    const nav = document.getElementById('site-nav');
    const r = nav.getBoundingClientRect();
    return {
      open: document.body.classList.contains('nav-open'),
      expanded: document.querySelector('.nav-toggle').getAttribute('aria-expanded'),
      onScreen: r.left < innerWidth - 10 && r.right <= innerWidth + 1,
      focusInside: nav.contains(document.activeElement),
      count: nav.querySelectorAll('a[href], button:not([disabled])').length,
      y: scrollY,
    };
  });
  if (!opened.open || !opened.onScreen) { fail('menu does not open'); return out; }
  if (opened.expanded !== 'true') fail('aria-expanded not "true" while open', '.nav-toggle');
  if (!opened.focusInside) fail('focus does not move into the menu when it opens');

  // Focus trap: Tab and Shift+Tab never leave the drawer (or its toggle).
  // Also watch the page underneath: focus landing on the sticky toggle can
  // make the browser scroll to it.
  const yBeforeTabs = await page.evaluate(() => scrollY);
  for (const key of ['Tab', 'Shift+Tab']) {
    for (let i = 0; i < opened.count + 3; i++) {
      await page.keyboard.press(key);
      const inside = await page.evaluate(() => {
        const a = document.activeElement;
        return document.getElementById('site-nav').contains(a) || a === document.querySelector('.nav-toggle');
      });
      if (!inside) { fail(`${key} moves focus out of the open menu`); break; }
    }
  }

  await page.waitForTimeout(1200);              // let any smooth scroll finish
  const yAfterTabs = await page.evaluate(() => scrollY);
  if (Math.abs(yAfterTabs - yBeforeTabs) > 2) fail(`tabbing round the open menu moves the page behind it (${yBeforeTabs} → ${yAfterTabs})`, 'html / .nav-toggle');

  // Page behind must not scroll: a wheel over the dimmed page.
  const yBeforeWheel = await page.evaluate(() => scrollY);
  await page.mouse.move(20, 400);
  await page.mouse.wheel(0, 500);
  await page.waitForTimeout(400);
  const yAfterWheel = await page.evaluate(() => scrollY);
  if (Math.abs(yAfterWheel - yBeforeWheel) > 2) fail(`page scrolls behind the open menu (${yBeforeWheel} → ${yAfterWheel})`, 'html / body.nav-open');

  await page.evaluate(() => window.scrollTo({ top: 600, behavior: 'instant' }));
  await page.waitForTimeout(200);
  const yBeforeEsc = await page.evaluate(() => scrollY);
  await page.keyboard.press('Escape');
  await page.waitForTimeout(1200);
  const yAfterEsc = await page.evaluate(() => scrollY);
  if (Math.abs(yAfterEsc - yBeforeEsc) > 2) fail(`closing with Escape moves the page (${yBeforeEsc} → ${yAfterEsc})`, '.nav-toggle focus');
  const esc = await page.evaluate(() => ({
    open: document.body.classList.contains('nav-open'),
    focusToggle: document.activeElement === document.querySelector('.nav-toggle'),
    inert: !!document.querySelector('#main[inert]'),
  }));
  if (esc.open) fail('Escape does not close the menu');
  if (!esc.focusToggle) fail('focus does not return to the menu button after Escape', '.nav-toggle');
  if (esc.inert) fail('page left inert after closing', '#main');

  await page.evaluate(() => window.scrollTo({ top: 600, behavior: 'instant' }));
  await page.waitForTimeout(200);
  await toggle.click();
  await page.waitForTimeout(600);
  const yBeforeX = await page.evaluate(() => scrollY);
  await page.locator('.nav-close').click();
  await page.waitForTimeout(1200);
  if (await page.evaluate(() => document.body.classList.contains('nav-open'))) fail('close button does not close the menu', '.nav-close');
  const yAfterX = await page.evaluate(() => scrollY);
  if (Math.abs(yAfterX - yBeforeX) > 2) fail(`closing with the X button moves the page (${yBeforeX} → ${yAfterX})`, '.nav-toggle focus');
  await page.evaluate(() => window.scrollTo({ top: 0, behavior: 'instant' }));
  return out;
}

async function checkContactForm(page) {
  const out = [];
  const res = await page.evaluate(async () => {
    const problems = [];
    const form = document.getElementById('form');
    if (!form) return [{ issue: 'contact form not found', element: '#form' }];
    const fields = [...form.querySelectorAll('input:not([type=hidden]), select, textarea')].filter((f) => f.tabIndex >= 0);
    for (const f of fields) {
      f.scrollIntoView({ behavior: 'instant', block: 'center' });
      await new Promise((r) => setTimeout(r, 80));
      const box = f.matches('[type=radio], [type=checkbox]') ? f.closest('label') : f;
      const r = box.getBoundingClientRect();
      if (r.left < -0.5 || r.right > innerWidth + 0.5) problems.push({ issue: `field cut off at the side (${Math.round(r.left)}..${Math.round(r.right)})`, element: __desc(box) });
      const hit = document.elementFromPoint(r.left + r.width / 2, r.top + r.height / 2);
      if (!hit || !(box === hit || box.contains(hit) || hit.contains(box))) problems.push({ issue: 'field covered by another element', element: __desc(box) + ' under ' + __desc(hit) });
      const label = f.matches('[type=radio]') ? f.closest('fieldset')?.querySelector('legend') : f.labels && f.labels[0];
      if (!label || !__visible(label)) problems.push({ issue: 'no visible label', element: __desc(f) });
      else {
        const lr = label.getBoundingClientRect();
        if (lr.left < -0.5 || lr.right > innerWidth + 0.5) problems.push({ issue: 'label cut off at the side', element: __desc(label) });
      }
    }
    const btn = form.querySelector('[type=submit]');
    btn.scrollIntoView({ behavior: 'instant', block: 'center' });
    await new Promise((r) => setTimeout(r, 450));
    const br = btn.getBoundingClientRect();
    const header = document.querySelector('.site-header').getBoundingClientRect().bottom;
    if (br.left < -0.5 || br.right > innerWidth + 0.5 || br.top < header || br.bottom > innerHeight) problems.push({ issue: `submit button not fully visible (${Math.round(br.left)},${Math.round(br.top)} → ${Math.round(br.right)},${Math.round(br.bottom)})`, element: __desc(btn) });
    if (btn.scrollWidth > btn.clientWidth + 1) problems.push({ issue: 'submit button text is cut off', element: __desc(btn) });
    window.scrollTo({ top: 0, behavior: 'instant' });
    return problems;
  });
  out.push(...res);

  // Keyboard: every field, each radio group and the button get focus in turn.
  await page.locator('#name').focus();
  const reached = new Set();
  for (let i = 0; i < 25; i++) {
    const k = await page.evaluate(() => {
      const a = document.activeElement;
      return a.type === 'radio' ? 'radio:' + a.name : a.id || (a.type === 'submit' ? 'submit' : a.tagName);
    });
    reached.add(k);
    if (k === 'submit') break;
    await page.keyboard.press('Tab');
  }
  for (const need of ['name', 'business', 'email', 'radio:sector', 'radio:spend', 'message', 'submit']) {
    if (!reached.has(need)) out.push({ issue: `not reachable by keyboard`, element: need });
  }
  return out;
}

// ---------- Run ------------------------------------------------------------
const browser = await chromium.launch();
const issues = [];
const add = (page, vp, check, list) => list.forEach((x) => issues.push({ page, viewport: vp, check: x.check || check, issue: x.issue, element: x.element }));

for (const vp of VIEWPORTS) {
  const context = await browser.newContext({
    viewport: { width: vp.width, height: vp.height },
    deviceScaleFactor: 1, isMobile: vp.mobile, hasTouch: vp.mobile,
  });
  for (const p of pages) {
    const page = await context.newPage();
    await page.goto(BASE + p, { waitUntil: 'load' });
    await prepare(page);

    add(p, vp.name, 'overflow', await checkOverflow(page));
    add(p, vp.name, 'text', await checkText(page, vp.mobile));
    const ids = new Set([...(anchorTargets[p] || [])]);
    for (const id of await page.evaluate(() => [...document.querySelectorAll('section[id]')].map((s) => s.id))) ids.add(id);
    ids.delete('main');
    add(p, vp.name, 'anchor', await checkAnchors(page, [...ids]));

    if (vp.mobile) {
      add(p, vp.name, 'tap', await checkTapTargets(page));
      if (p !== 'contact.html') add(p, vp.name, 'first-screen', await checkFirstScreenCta(page));
      add(p, vp.name, 'menu', await checkMenu(page));
      if (p === 'contact.html') add(p, vp.name, 'form', await checkContactForm(page));
    }

    await page.evaluate(() => window.scrollTo({ top: 0, behavior: 'instant' }));
    await page.screenshot({ path: path.join(OUT, `${p.replace(/[\/.]/g, '_')}-${vp.name}.png`), fullPage: true });
    await page.close();
  }
  await context.close();
}
await browser.close();

// ---------- Report ---------------------------------------------------------
const cols = ['page', 'viewport', 'check', 'issue', 'element'];
const width = Object.fromEntries(cols.map((c) => [c, Math.min(c === 'element' ? 70 : 60, Math.max(c.length, ...issues.map((i) => String(i[c]).length)))]));
const cell = (s, w) => { s = String(s); return s.length > w ? s.slice(0, w - 1) + '…' : s.padEnd(w); };
console.log(cols.map((c) => cell(c, width[c])).join(' | '));
console.log(cols.map((c) => '-'.repeat(width[c])).join('-|-'));
for (const i of issues) console.log(cols.map((c) => cell(i[c], width[c])).join(' | '));

const byCheck = {};
for (const i of issues) byCheck[i.check] = (byCheck[i.check] || 0) + 1;
console.log('\nIssues by check:', JSON.stringify(byCheck));
console.log('Total issues:', issues.length);
if (missing.length) console.log('Listed but not in the repository (skipped):', missing.join(', '));
console.log('Screenshots:', OUT);

fs.writeFileSync(path.join(OUT, `report-${LABEL}.json`), JSON.stringify({ pages, missing, byCheck, total: issues.length, issues }, null, 2));
