<?php
/** HOME. Expects $c, $page, $meta. */
$hero = $page['hero'];
$gap  = $page['gap'];
$caps = $page['capabilities'];
$proc = $page['process'];
$math = $page['maths'];
$stud = $page['studio'];
$craft = $page['craft'];
$faq  = $page['faq'];
$founders = gns_get($c, 'founders', array());

// Render the calculator's opening state server-side with the same formula
// main.js uses, so the figures are right even if the script never runs.
$tiers = gns_get($c, 'pages.pricing.tiers', array());
$feeFor = array();
foreach ($tiers as $t) {
    $feeFor[$t['key']] = (int)$t['price'];
}
$tierKey = isset($feeFor[$math['default_tier']]) ? $math['default_tier'] : key($feeFor);
$calc = gns_calc(array(
    'spend'  => $math['default_spend'],
    'job'    => $math['default_job'],
    'close'  => $math['default_close'],
    'margin' => $math['default_margin'],
    'fee'    => $feeFor[$tierKey],
));
include GNS_TEMPLATES . '/_head.php';
include GNS_TEMPLATES . '/_header.php';
?>

  <main id="main">

    <!-- ============ HERO ============ -->
    <section class="hero">
      <canvas class="hero-canvas" id="hero-canvas" aria-hidden="true"></canvas>
      <div class="grain" aria-hidden="true"></div>

      <div class="shell hero-inner">
        <p class="hero-flag">
          <span class="pulse" aria-hidden="true"></span>
          <span><?= e(gns_spots_line($c, true)) ?></span>
          <span class="hero-flag-rule" aria-hidden="true"></span>
          <span class="hero-flag-dim"><?= rich($hero['flag_dim']) ?></span>
        </p>

        <h1><?= rich($hero['h1']) ?></h1>

        <p class="lead hero-sub"><?= rich($hero['sub']) ?></p>

        <div class="btn-row">
          <a class="btn btn-primary btn-lg" href="<?= e(gns_href($hero['cta_href'])) ?>">
            <?= rich($hero['cta_label']) ?>
            <?= gns_icon_arrow() ?>
          </a>
          <a class="btn btn-secondary btn-lg" href="<?= e(gns_href($hero['alt_href'])) ?>">
            <svg viewBox="0 0 16 16" aria-hidden="true"><circle cx="8" cy="8" r="6.2" fill="none" stroke="#d2ad5c" stroke-width="1.4"/><path d="M6.6 5.6 10.6 8l-4 2.4z" fill="#d2ad5c"/></svg>
            <?= rich($hero['alt_label']) ?>
          </a>
        </div>
      </div>

      <div class="hero-rail">
        <div class="shell hero-rail-inner">
          <ul class="hero-facts">
<?php foreach ($hero['facts'] as $fact): ?>
            <li><?= gns_icon_tick() ?><?= rich($fact) ?></li>
<?php endforeach; ?>
          </ul>
          <span class="hero-scroll" aria-hidden="true">
            Scroll
            <svg viewBox="0 0 12 26"><path d="M6 2v18M2.4 16.4 6 20l3.6-3.6" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/></svg>
          </span>
        </div>
      </div>
    </section>

    <!-- ============ SECTORS ============ -->
    <div class="marquee" aria-hidden="true">
      <div class="marquee-track">
<?php for ($pass = 0; $pass < 2; $pass++): ?>
<?php foreach ($page['marquee'] as $word): ?>
        <span><?= rich($word) ?></span><span class="dot">◆</span>
<?php endforeach; ?>
<?php endfor; ?>
      </div>
    </div>

    <!-- ============ THE GAP ============ -->
    <section class="section" aria-labelledby="gap-heading">
      <div class="shell gap-grid">
        <div class="gap-copy reveal">
          <p class="eyebrow"><?= rich($gap['eyebrow']) ?></p>
          <h2 id="gap-heading"><?= rich($gap['h2']) ?></h2>
<?php foreach ($gap['body'] as $para): ?>
          <p><?= rich($para) ?></p>
<?php endforeach; ?>
          <a class="link-arrow" href="<?= e(gns_href($gap['link_href'])) ?>">
            <?= rich($gap['link_label']) ?>
            <?= gns_icon_arrow() ?>
          </a>
        </div>

        <div class="order-compare reveal">
          <div class="order-col">
            <p class="order-label"><?= rich($gap['left_label']) ?></p>
            <ol class="order-list is-muted">
<?php foreach ($gap['left_steps'] as $i => $step): ?>
              <li<?= $i === count($gap['left_steps']) - 1 ? ' class="is-dead"' : '' ?>><span><?= sprintf('%02d', $i + 1) ?></span><?= rich($step) ?></li>
<?php endforeach; ?>
            </ol>
          </div>
          <div class="order-col">
            <p class="order-label is-gold"><?= rich($gap['right_label']) ?></p>
            <ol class="order-list is-live">
<?php foreach ($gap['right_steps'] as $i => $step): ?>
              <li<?= $i === count($gap['right_steps']) - 1 ? ' class="is-hot"' : '' ?>><span><?= sprintf('%02d', $i + 1) ?></span><?= rich($step) ?></li>
<?php endforeach; ?>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <!-- ============ CAPABILITIES ============ -->
    <section class="section section-alt" aria-labelledby="cap-heading">
      <div class="shell">
        <div class="section-head section-head-split reveal">
          <div>
            <p class="eyebrow"><?= rich($caps['eyebrow']) ?></p>
            <h2 id="cap-heading"><?= rich($caps['h2']) ?></h2>
          </div>
          <p class="lead"><?= rich($caps['lead']) ?></p>
        </div>

        <div class="bento stagger">
<?php foreach ($caps['items'] as $item):
    $classes = 'card';
    if (!empty($item['gold'])) { $classes .= ' card-gold'; }
    if (!empty($item['size'])) { $classes .= ' bento-' . $item['size']; }
    $visual = isset($item['visual']) ? $item['visual'] : 'none';
?>
<?php if ($visual === 'retainer'): ?>
          <article class="<?= e($classes) ?>">
            <div class="retainer">
              <span class="icon" aria-hidden="true"><?= gns_visual_icon('retainer') ?></span>
              <div>
                <h3><?= rich($item['title']) ?></h3>
                <p><?= rich($item['body']) ?></p>
              </div>
            </div>
            <a class="btn btn-ghost btn-sm" href="<?= e(gns_href($caps['all_href'])) ?>">
              <?= rich($caps['all_label']) ?>
              <?= gns_icon_arrow() ?>
            </a>
          </article>
<?php elseif ($visual === 'minipage'): ?>
          <article class="<?= e($classes) ?>">
            <div class="bento-wide-copy">
              <span class="icon" aria-hidden="true"><?= gns_visual_icon('minipage') ?></span>
              <h3><?= rich($item['title']) ?></h3>
              <p><?= rich($item['body']) ?></p>
            </div>
            <div class="mini-page" aria-hidden="true">
              <div class="mini-dots"><span></span><span></span><span></span></div>
              <div class="mini-title"></div>
              <div class="mini-line"></div>
              <div class="mini-line is-short"></div>
              <div class="mini-cta"></div>
              <div class="mini-grid"><span></span><span></span></div>
            </div>
          </article>
<?php else: ?>
          <article class="<?= e($classes) ?>">
            <span class="icon" aria-hidden="true"><?= gns_visual_icon($visual) ?></span>
            <h3><?= rich($item['title']) ?></h3>
            <p><?= rich($item['body']) ?></p>
<?php if ($visual === 'funnel'): ?>
            <div class="funnel" aria-hidden="true">
              <div class="funnel-row"><span class="funnel-bar" style="--w:100%;--c:#d2ad5c"></span><span class="funnel-key">Cold</span></div>
              <div class="funnel-row"><span class="funnel-bar" style="--w:66%;--c:#b4914a"></span><span class="funnel-key">Warm</span></div>
              <div class="funnel-row"><span class="funnel-bar" style="--w:34%;--c:#f7e7be"></span><span class="funnel-key">Convert</span></div>
            </div>
<?php elseif ($visual === 'serp'): ?>
            <div class="serp" aria-hidden="true">
              <div class="serp-row is-ad"><span class="serp-tag">Ad</span><span class="serp-line" style="--w:44%"></span><span class="serp-line is-dim" style="--w:20%"></span></div>
              <div class="serp-row"><span class="serp-line is-dim" style="--w:38%"></span><span class="serp-line is-faint" style="--w:26%"></span></div>
              <div class="serp-row"><span class="serp-line is-faint" style="--w:30%"></span><span class="serp-line is-faint" style="--w:18%"></span></div>
            </div>
<?php endif; ?>
          </article>
<?php endif; ?>
<?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- ============ PROCESS ============ -->
    <section class="section" id="process" aria-labelledby="process-heading">
      <div class="shell">
        <div class="section-head reveal">
          <p class="eyebrow"><?= rich($proc['eyebrow']) ?></p>
          <h2 id="process-heading"><?= rich($proc['h2']) ?></h2>
        </div>

        <ol class="process stagger">
<?php foreach ($proc['steps'] as $i => $step): ?>
          <li>
            <span class="process-num<?= $step['state'] !== '' ? ' is-' . e($step['state']) : '' ?>"><?= $i + 1 ?></span>
            <p class="process-when"><?= rich($step['when']) ?></p>
            <h3><?= rich($step['title']) ?></h3>
            <p><?= rich($step['body']) ?></p>
          </li>
<?php endforeach; ?>
        </ol>
      </div>
    </section>

    <!-- ============ BREAK-EVEN MODEL ============ -->
    <section class="section section-alt" aria-labelledby="maths-heading">
      <div class="shell maths-grid">
        <div class="maths-copy reveal">
          <p class="eyebrow"><?= rich($math['eyebrow']) ?></p>
          <h2 id="maths-heading"><?= rich($math['h2']) ?></h2>
          <p><?= rich($math['body']) ?></p>
          <p class="note-rule"><?= rich($math['note']) ?></p>
        </div>

        <div class="calc reveal" id="calc">
          <div class="calc-tier">
            <span class="calc-tier-label" id="calc-tier-label">Retainer tier</span>
            <div class="calc-tier-btns" role="group" aria-labelledby="calc-tier-label">
<?php foreach ($tiers as $tier): $on = $tier['key'] === $tierKey; ?>
              <button type="button" class="tier-btn<?= $on ? ' is-active' : '' ?>" data-tier="<?= e($tier['key']) ?>" data-fee="<?= (int)$tier['price'] ?>" aria-pressed="<?= $on ? 'true' : 'false' ?>"><?= rich($tier['name']) ?> · <?= e(gns_money($tier['price'])) ?></button>
<?php endforeach; ?>
            </div>
          </div>

          <div class="calc-inputs">
            <div class="calc-field">
              <label for="calc-spend">Monthly ad spend</label>
              <output class="calc-value" id="out-spend" for="calc-spend"><?= e(gns_money($calc['spend'])) ?></output>
              <input type="range" id="calc-spend" min="1000" max="30000" step="500" value="<?= (int)$calc['spend'] ?>" aria-valuetext="<?= e(gns_money($calc['spend'])) ?>">
              <div class="calc-scale"><span>$1k</span><span>$30k</span></div>
            </div>
            <div class="calc-field">
              <label for="calc-job">Average job value</label>
              <output class="calc-value" id="out-job" for="calc-job"><?= e(gns_money($calc['job'])) ?></output>
              <input type="range" id="calc-job" min="200" max="12000" step="100" value="<?= (int)$calc['job'] ?>" aria-valuetext="<?= e(gns_money($calc['job'])) ?>">
              <div class="calc-scale"><span>$200</span><span>$12k</span></div>
            </div>
            <div class="calc-field">
              <label for="calc-margin">Gross margin per job</label>
              <output class="calc-value" id="out-margin" for="calc-margin"><?= (int)$calc['margin'] ?>%</output>
              <input type="range" id="calc-margin" min="20" max="80" step="1" value="<?= (int)$calc['margin'] ?>" aria-valuetext="<?= (int)$calc['margin'] ?> percent">
              <div class="calc-scale"><span>20%</span><span>80%</span></div>
            </div>
            <div class="calc-field">
              <label for="calc-close">Close rate on leads</label>
              <output class="calc-value" id="out-close" for="calc-close"><?= (int)$calc['close'] ?>%</output>
              <input type="range" id="calc-close" min="5" max="80" step="1" value="<?= (int)$calc['close'] ?>" aria-valuetext="<?= (int)$calc['close'] ?> percent">
              <div class="calc-scale"><span>5%</span><span>80%</span></div>
            </div>
          </div>

          <hr class="calc-rule">

          <!-- Announced as a group: dragging a slider changes four numbers, and a
               screen-reader user should hear the result, not just the input. -->
          <div class="calc-outputs" aria-live="polite">
            <div class="stat">
              <p class="stat-label">All-in monthly cost</p>
              <p class="stat-value" id="out-total"><?= e(gns_money($calc['total'])) ?></p>
              <p class="stat-note" id="out-breakdown"><?= e(gns_money($calc['spend'])) ?> media + <?= e(gns_money($calc['fee'])) ?> retainer</p>
            </div>
            <div class="stat">
              <p class="stat-label">Jobs to cover cost</p>
              <p class="stat-value metal" id="out-jobs"><?= (int)$calc['jobs'] ?></p>
              <p class="stat-note" id="out-jobs-note">at <?= e(gns_money(round($calc['profit']))) ?> profit per job</p>
            </div>
            <div class="stat">
              <p class="stat-label">Leads required</p>
              <p class="stat-value" id="out-leads"><?= (int)$calc['leads'] ?></p>
              <p class="stat-note" id="out-leads-note">at <?= (int)$calc['close'] ?>% close rate</p>
            </div>
            <div class="stat">
              <p class="stat-label">Max cost per lead</p>
              <p class="stat-value" id="out-cpl"><?= e(gns_money($calc['cpl'])) ?></p>
              <p class="stat-note">from media budget alone</p>
            </div>
          </div>

          <div class="calc-funnel" aria-hidden="true">
            <div class="funnel-row"><span class="funnel-bar is-lg" id="bar-leads" style="--c:#d2ad5c;width:100%"><em id="bar-leads-label"><?= (int)$calc['leads'] ?> leads</em></span></div>
            <div class="funnel-row"><span class="funnel-bar is-lg" id="bar-jobs" style="--c:#f7e7be;width:<?= max(14, (int)$calc['close']) ?>%"><em id="bar-jobs-label"><?= (int)$calc['jobs'] ?> jobs</em></span></div>
          </div>

          <p class="calc-verdict">
            <?= gns_icon_info() ?>
            <span id="out-verdict"><?= e(gns_calc_verdict($calc)) ?></span>
          </p>
        </div>
      </div>
    </section>

    <!-- ============ THE STUDIO ============ -->
    <section class="section" aria-labelledby="studio-heading">
      <div class="shell">
        <div class="section-head section-head-split reveal">
          <div>
            <p class="eyebrow"><?= rich($stud['eyebrow']) ?></p>
            <h2 id="studio-heading"><?= rich($stud['h2']) ?></h2>
          </div>
          <p class="lead"><?= rich($stud['lead']) ?></p>
        </div>

        <div class="founders stagger">
<?php foreach ($founders as $f): ?>
          <article class="card card-gold founder">
<?php if (trim((string)$f['photo']) !== ''): ?>
            <img class="portrait" src="<?= e($f['photo']) ?>" width="480" height="600" loading="lazy" decoding="async" alt="<?= e(plain($f['name']) . ', ' . plain($f['role_home'])) ?>">
<?php else: ?>
            <div class="portrait portrait-<?= e($f['tone']) ?>" role="img" aria-label="<?= e(plain($f['name'])) ?>">
              <span aria-hidden="true"><?= e($f['initials']) ?></span>
            </div>
<?php endif; ?>
            <div class="founder-copy">
              <h3><?= rich($f['name']) ?></h3>
              <p class="founder-role"><?= rich($f['role_home']) ?></p>
              <p><?= rich($f['bio_home']) ?></p>
<?php if (trim((string)$f['linkedin']) !== ''): ?>
              <a class="founder-link" href="<?= e($f['linkedin']) ?>" target="_blank" rel="noopener noreferrer">
                <?= gns_icon_linkedin() ?>
                <span><?= e(plain($f['name'])) ?> on LinkedIn</span>
              </a>
<?php endif; ?>
            </div>
          </article>
<?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- ============ CRAFT / COMPARISON ============ -->
    <section class="section section-alt" aria-labelledby="craft-heading">
      <div class="shell">
        <div class="section-head section-head-split reveal">
          <div>
            <p class="eyebrow"><?= rich($craft['eyebrow']) ?></p>
            <h2 id="craft-heading"><?= rich($craft['h2']) ?></h2>
          </div>
          <p class="lead"><?= rich($craft['lead']) ?></p>
        </div>

        <div class="compare reveal" data-cursor="drag">
          <div class="compare-after">
            <p class="compare-tag is-gold"><?= rich($craft['after_tag']) ?></p>
            <div class="compare-body">
              <p class="compare-kicker"><?= rich($craft['after_kicker']) ?></p>
              <p class="compare-head"><?= rich($craft['after_head']) ?></p>
              <div class="compare-actions">
                <span class="compare-btn"><?= rich($craft['after_btn']) ?></span>
                <span class="compare-meta"><?= rich($craft['after_meta']) ?></span>
              </div>
            </div>
          </div>

          <div class="compare-before">
            <p class="compare-tag"><?= rich($craft['before_tag']) ?></p>
            <div class="compare-body">
              <p class="compare-head-bad"><?= rich($craft['before_head']) ?></p>
              <p class="compare-sub-bad"><?= rich($craft['before_sub']) ?></p>
              <div class="compare-actions">
                <span class="compare-btn-bad is-red"><?= rich($craft['before_btn_a']) ?></span>
                <span class="compare-btn-bad is-green"><?= rich($craft['before_btn_b']) ?></span>
              </div>
            </div>
          </div>

          <span class="compare-line" aria-hidden="true"></span>
          <span class="compare-handle" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M9.5 8 5.5 12l4 4M14.5 8l4 4-4 4" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </span>

          <label class="sr-only" for="compare-range">Reveal the redesigned version</label>
          <input class="compare-range" id="compare-range" type="range" min="3" max="97" value="50">
        </div>

        <p class="compare-note"><?= rich($craft['note']) ?></p>
      </div>
    </section>

    <!-- ============ FAQ ============ -->
    <section class="section" aria-labelledby="faq-heading">
      <div class="shell faq-grid">
        <div class="reveal">
          <p class="eyebrow"><?= rich($faq['eyebrow']) ?></p>
          <h2 id="faq-heading"><?= rich($faq['h2']) ?></h2>
        </div>

        <div class="faq-list reveal">
<?php foreach ($faq['items'] as $i => $qa): $open = $i === 0; $id = 'fa-' . ($i + 1); ?>
          <div class="faq-item<?= $open ? ' is-open' : '' ?>">
            <h3>
              <button type="button" class="faq-q" aria-expanded="<?= $open ? 'true' : 'false' ?>" aria-controls="<?= $id ?>">
                <?= rich($qa['q']) ?>
                <span class="faq-icon" aria-hidden="true"></span>
              </button>
            </h3>
            <div class="faq-a" id="<?= $id ?>" role="region"<?= $open ? '' : ' hidden' ?>><div>
              <p><?= rich($qa['a']) ?></p>
            </div></div>
          </div>
<?php endforeach; ?>
        </div>
      </div>
    </section>

<?php $close = $page['closing']; $showSpots = true; include GNS_TEMPLATES . '/_closing.php'; ?>
  </main>

<?php include GNS_TEMPLATES . '/_footer.php'; ?>
