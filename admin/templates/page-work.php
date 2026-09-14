<?php
/**
 * WORK. Expects $c, $page, $meta.
 *
 * "Selected work", not "case studies": showing craft is not the same as
 * claiming results, and the studio has publicly committed to not inventing the
 * second. Client entries only appear once someone adds them in the admin.
 */
$intro  = $page['intro'];
$craft  = $page['craft'];
$client = $page['clients'];
include GNS_TEMPLATES . '/_head.php';
include GNS_TEMPLATES . '/_header.php';
?>

  <main id="main">

    <section class="page-intro">
      <div class="shell page-intro-grid">
        <div>
          <p class="eyebrow"><?= rich($intro['eyebrow']) ?></p>
          <h1><?= rich($intro['h1']) ?></h1>
        </div>
        <p class="lead"><?= rich($intro['lead']) ?></p>
      </div>
    </section>

    <section class="section" aria-labelledby="craft-heading">
      <div class="shell">
        <div class="section-head section-head-split reveal">
          <div>
            <p class="eyebrow"><?= rich($craft['eyebrow']) ?></p>
            <h2 id="craft-heading"><?= rich($craft['h2']) ?></h2>
          </div>
          <p class="lead"><?= rich($craft['lead']) ?></p>
        </div>

        <div class="work-list">
<?php foreach ($craft['items'] as $i => $item): $kind = $item['kind']; ?>
          <article class="work-item reveal<?= $i % 2 ? ' is-reversed' : '' ?>">
            <div class="work-visual card<?= $i % 2 ? '' : ' card-gold' ?>">
<?php if (trim((string)$item['image']) !== ''): ?>
              <img src="<?= e($item['image']) ?>" width="1200" height="900" loading="lazy" decoding="async" alt="<?= e(plain($item['title'])) ?>">
<?php elseif ($kind === 'ad'): ?>
              <div class="work-ad" aria-hidden="true">
                <p class="work-ad-kicker">Ceramic coating · 5 year</p>
                <p class="work-ad-head">Keep the showroom<br><span class="metal">finish for five years.</span></p>
                <div class="work-ad-actions">
                  <span class="work-ad-btn">Book an inspection</span>
                  <span class="work-ad-meta">Free · 20 minutes</span>
                </div>
              </div>
<?php elseif ($kind === 'page'): ?>
              <div class="browser" aria-hidden="true">
                <div class="browser-bar">
                  <span></span><span></span><span></span>
                  <em>yourbrand.com/ceramic-coating</em>
                </div>
                <div class="browser-body">
                  <p class="browser-kicker">Certified installer</p>
                  <p class="browser-head">Keep the showroom finish for five years.</p>
                  <div class="browser-actions">
                    <span class="browser-btn">Book inspection</span>
                    <span class="browser-btn is-ghost">See pricing</span>
                  </div>
                  <hr class="hair">
                  <div class="browser-grid"><span></span><span></span><span></span></div>
                </div>
              </div>
<?php else: ?>
              <div class="work-serp" aria-hidden="true">
                <div class="query">
                  <svg viewBox="0 0 16 16" aria-hidden="true"><circle cx="7" cy="7" r="4.6" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M10.4 10.4 14 14" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                  ceramic coating near me
                </div>
                <div class="result is-ad">
                  <p class="result-meta"><span class="serp-tag">Sponsored</span> yourbrand.com</p>
                  <p class="result-title">5-Year Ceramic Coating — Free Paint Inspection</p>
                  <p class="result-desc">Certified installers. Book a 20-minute inspection, get a written quote the same day.</p>
                </div>
                <div class="result is-ghost"><span style="--w:52%"></span><span style="--w:78%"></span></div>
                <div class="result is-ghost is-fainter"><span style="--w:46%"></span><span style="--w:68%"></span></div>
              </div>
<?php endif; ?>
<?php if (trim((string)$item['label']) !== ''): ?>
              <p class="work-label"><?= rich($item['label']) ?></p>
<?php endif; ?>
            </div>

            <div class="work-copy">
              <h3><?= rich($item['title']) ?></h3>
              <div class="work-block">
                <p class="work-block-label">The problem</p>
                <p><?= rich($item['problem']) ?></p>
              </div>
              <div class="work-block">
                <p class="work-block-label">What we did</p>
                <p><?= rich($item['did']) ?></p>
              </div>
            </div>
          </article>
<?php endforeach; ?>
        </div>

        <p class="work-honesty">
          <?= gns_icon_info() ?>
          <span><?= rich($page['honesty']) ?></span>
        </p>
      </div>
    </section>

<?php if (!empty($client['items'])): ?>
    <section class="section section-alt" aria-labelledby="clients-heading">
      <div class="shell">
        <div class="section-head section-head-split reveal">
          <div>
            <p class="eyebrow"><?= rich($client['eyebrow']) ?></p>
            <h2 id="clients-heading"><?= rich($client['h2']) ?></h2>
          </div>
          <p class="lead"><?= rich($client['lead']) ?></p>
        </div>

        <div class="client-grid stagger">
<?php foreach ($client['items'] as $item): ?>
          <article class="card client-card">
<?php if (trim((string)$item['image']) !== ''): ?>
            <img class="client-shot" src="<?= e($item['image']) ?>" width="1000" height="750" loading="lazy" decoding="async" alt="<?= e(plain($item['title'])) ?>">
<?php endif; ?>
            <div class="client-body">
              <p class="client-kicker"><?= rich($item['sector']) ?></p>
              <h3><?= rich($item['title']) ?></h3>
              <p><?= rich($item['body']) ?></p>
<?php if (trim((string)$item['link']) !== ''): ?>
              <a class="link-arrow" href="<?= e($item['link']) ?>" target="_blank" rel="noopener noreferrer">
                <?= rich($item['link_label']) ?>
                <?= gns_icon_arrow() ?>
              </a>
<?php endif; ?>
            </div>
          </article>
<?php endforeach; ?>
        </div>
      </div>
    </section>
<?php endif; ?>

<?php $close = $page['closing']; $showSpots = false; include GNS_TEMPLATES . '/_closing.php'; ?>
  </main>

<?php include GNS_TEMPLATES . '/_footer.php'; ?>
