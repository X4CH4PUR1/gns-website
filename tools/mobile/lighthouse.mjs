// GN SCALES — LIGHTHOUSE RUNNER
//
// Not part of the site: tools/ is excluded from the deploy.
//
//   node lighthouse.mjs [--base https://gnscales.com/] [--label before] [--runs 3] [page ...]
//
// Runs Lighthouse (mobile preset, via npx, nothing installed in the site)
// against each page several times and prints the median: the four category
// scores, LCP, CLS, TBT and the LCP element. Every accessibility, best-practice
// and SEO audit that is not a pass is listed underneath. Reports are kept in
// tools/mobile/out/lh/.

import { execFileSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const HERE = path.dirname(fileURLToPath(import.meta.url));
const OUT = path.join(HERE, 'out', 'lh');
const LIGHTHOUSE = 'lighthouse@12.6.1';

const args = process.argv.slice(2);
const opt = (name, dflt) => { const i = args.indexOf('--' + name); if (i < 0) return dflt; const v = args[i + 1]; args.splice(i, 2); return v; };
const BASE = opt('base', 'https://gnscales.com/');
const LABEL = opt('label', 'latest');
const RUNS = Number(opt('runs', '3'));
// --desktop switches to Lighthouse's desktop preset (the hero shader only
// starts at 900px and up, so this is how its main-thread cost is measured).
const DESKTOP = args.includes('--desktop') && args.splice(args.indexOf('--desktop'), 1);
const EXTRA = DESKTOP ? ['--preset=desktop'] : [];
const PAGES = args.length ? args : ['', 'pricing.html', 'services.html', 'meta-ads.html'];

fs.mkdirSync(OUT, { recursive: true });
const median = (xs) => { const s = [...xs].sort((a, b) => a - b); return s[Math.floor(s.length / 2)]; };
const npx = process.platform === 'win32' ? 'npx.cmd' : 'npx';

const rows = [];
const failing = new Map();

for (const page of PAGES) {
  const runs = [];
  for (let n = 1; n <= RUNS; n++) {
    const file = path.join(OUT, `${LABEL}-${page.replace(/\W/g, '_') || 'index'}-${n}.json`);
    // On Windows npx is a .cmd and needs a shell, which splits arguments on
    // spaces ("D:\GNS website\..."), so anything with a space is quoted.
    const q = (s) => (/\s/.test(s) ? `"${s}"` : s);
    // Lighthouse now and then fails to record a trace (NO_NAVSTART); a run is
    // retried up to three times. A report already on disk is reused, so an
    // interrupted batch can be resumed with the same --label.
    for (let attempt = 1; !fs.existsSync(file); attempt++) {
      try {
        execFileSync(npx, ['-y', LIGHTHOUSE, BASE + page,
          '--only-categories=performance,accessibility,best-practices,seo',
          '--output=json', `--output-path=${file}`, '--quiet',
          '--chrome-flags=--headless=new --no-first-run', ...EXTRA].map(q), { stdio: 'inherit', shell: process.platform === 'win32' });
      } catch (err) {
        if (attempt >= 3) throw err;
        console.error(`  retrying ${page || 'index.html'} run ${n} (attempt ${attempt + 1})`);
      }
    }
    const lhr = JSON.parse(fs.readFileSync(file, 'utf8'));
    const a = lhr.audits;
    const lcpEl = a['largest-contentful-paint-element']?.details?.items?.[0]?.items?.[0]?.node;
    runs.push({
      perf: lhr.categories.performance.score * 100,
      a11y: lhr.categories.accessibility.score * 100,
      bp: lhr.categories['best-practices'].score * 100,
      seo: lhr.categories.seo.score * 100,
      lcp: a['largest-contentful-paint'].numericValue,
      cls: a['cumulative-layout-shift'].numericValue,
      tbt: a['total-blocking-time'].numericValue,
      fcp: a['first-contentful-paint'].numericValue,
      lcpEl: lcpEl ? (lcpEl.selector || '') + ' "' + (lcpEl.nodeLabel || '').slice(0, 40) + '"' : '-',
    });
    for (const cat of ['accessibility', 'best-practices', 'seo']) {
      for (const ref of lhr.categories[cat].auditRefs) {
        const au = a[ref.id];
        if (ref.weight === 0 || au.score === null || au.score === 1) continue;
        const key = `${cat} | ${ref.id} | ${au.title}`;
        const pages = failing.get(key) || new Set(); pages.add(page || 'index.html'); failing.set(key, pages);
      }
    }
  }
  const mid = runs.slice().sort((x, y) => x.perf - y.perf)[Math.floor(runs.length / 2)];
  rows.push({
    page: page || 'index.html',
    perf: median(runs.map((r) => r.perf)), a11y: median(runs.map((r) => r.a11y)),
    bp: median(runs.map((r) => r.bp)), seo: median(runs.map((r) => r.seo)),
    lcp: median(runs.map((r) => r.lcp)), cls: median(runs.map((r) => r.cls)),
    tbt: median(runs.map((r) => r.tbt)), fcp: median(runs.map((r) => r.fcp)),
    lcpEl: mid.lcpEl,
  });
}

const fmt = (r) => [r.page.padEnd(15), String(Math.round(r.perf)).padStart(4), String(Math.round(r.a11y)).padStart(4),
  String(Math.round(r.bp)).padStart(4), String(Math.round(r.seo)).padStart(4),
  ((r.fcp / 1000).toFixed(2) + ' s').padStart(7), ((r.lcp / 1000).toFixed(2) + ' s').padStart(7),
  r.cls.toFixed(3).padStart(6), (Math.round(r.tbt) + ' ms').padStart(7), r.lcpEl].join(' | ');
console.log(`\nLighthouse ${LIGHTHOUSE}, ${DESKTOP ? 'desktop' : 'mobile'} preset, median of ${RUNS} runs — ${BASE}`);
console.log(['page'.padEnd(15), 'Perf', 'A11y', '  BP', ' SEO', '    FCP', '    LCP', '   CLS', '    TBT', 'LCP element'].join(' | '));
rows.forEach((r) => console.log(fmt(r)));
const miss = (r) => [r.lcp > 2500 && 'LCP', r.cls > 0.1 && 'CLS', r.tbt > 200 && 'TBT',
  r.perf < 90 && 'Perf', r.a11y < 90 && 'A11y', r.bp < 90 && 'BP', r.seo < 90 && 'SEO'].filter(Boolean);
rows.forEach((r) => { const m = miss(r); if (m.length) console.log(`  ${r.page} misses: ${m.join(', ')}`); });
console.log('\nAudits not passing (any run):');
if (!failing.size) console.log('  none');
for (const [k, pages] of failing) console.log(`  ${k}  [${[...pages].join(', ')}]`);
fs.writeFileSync(path.join(OUT, `summary-${LABEL}.json`), JSON.stringify({ base: BASE, rows, failing: Object.fromEntries([...failing].map(([k, v]) => [k, [...v]])) }, null, 2));
