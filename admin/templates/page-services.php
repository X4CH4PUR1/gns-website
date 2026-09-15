<?php
/** SERVICES. Expects $c, $page, $meta. */
$intro = $page['intro'];
$more  = $page['more'];
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
        <div>
          <p class="lead"><?= rich($intro['lead']) ?></p>
          <h2 class="page-intro-sub"><?= rich($intro['keyword_h2']) ?></h2>
        </div>
      </div>
    </section>

<?php foreach ($page['sections'] as $n => $sec):
    $alt = $n % 2 === 1;
    $hid = gns_slug($sec['id']) . '-heading';
?>
    <!-- <?= e($sec['index']) ?> — <?= e(plain($sec['title'])) ?> -->
    <section class="section<?= $alt ? ' section-alt' : '' ?>" id="<?= e(gns_slug($sec['id'])) ?>" aria-labelledby="<?= e($hid) ?>">
      <div class="shell svc<?= !empty($sec['reversed']) ? ' is-reversed' : '' ?>">
<?php if (!empty($sec['reversed'])) { include GNS_TEMPLATES . '/_svc-visual.php'; } ?>
        <div class="svc-copy reveal">
          <p class="svc-index"><span><?= e($sec['index']) ?></span><i></i><?= rich($sec['kicker']) ?></p>
          <h2 id="<?= e($hid) ?>"><?= rich($sec['title']) ?></h2>
<?php foreach ($sec['body'] as $para): ?>
          <p><?= rich($para) ?></p>
<?php endforeach; ?>
<?php if (trim((string)$sec['note']) !== ''): ?>
          <p class="note-rule"><?= rich($sec['note']) ?></p>
<?php endif; ?>
<?php if (!empty($sec['ticks'])): ?>
          <ul class="ticks">
<?php foreach ($sec['ticks'] as $tick): ?>
            <li><?= gns_icon_tick() ?><?= rich($tick) ?></li>
<?php endforeach; ?>
          </ul>
<?php endif; ?>
        </div>
<?php if (empty($sec['reversed'])) { include GNS_TEMPLATES . '/_svc-visual.php'; } ?>
      </div>
    </section>
<?php endforeach; ?>

    <!-- Also in scope: given the same card chrome and a visual each, so the
         section reads as a deliberate tier rather than three that ran out of time. -->
    <section class="section section-alt" id="more" aria-labelledby="more-heading">
      <div class="shell">
        <div class="section-head section-head-split reveal">
          <div>
            <p class="eyebrow"><?= rich($more['eyebrow']) ?></p>
            <h2 id="more-heading"><?= rich($more['h2']) ?></h2>
          </div>
          <p class="lead"><?= rich($more['lead']) ?></p>
        </div>
        <div class="svc-grid stagger">
<?php foreach ($more['items'] as $item): $v = $item['visual']; ?>
          <article class="card svc-mini">
            <span class="icon" aria-hidden="true"><?= gns_visual_icon($v) ?></span>
            <h3><?= rich($item['title']) ?></h3>
            <p><?= rich($item['body']) ?></p>
<?php if ($v === 'tiktok'): ?>
            <div class="mini-visual mini-vertical" aria-hidden="true">
              <div class="mini-vertical-frame">
                <span class="mini-vertical-keep"></span>
                <span class="mini-vertical-label">12s</span>
              </div>
              <div class="mini-scrub"><span class="mini-scrub-fill"></span><span class="mini-scrub-head"></span></div>
            </div>
<?php elseif ($v === 'sequence'): ?>
            <div class="mini-visual mini-seq" aria-hidden="true">
              <div class="mini-seq-row"><span class="mini-seq-dot is-on"></span><span class="mini-seq-line" style="--w:74%"></span><em>Day 0</em></div>
              <div class="mini-seq-row"><span class="mini-seq-dot is-on"></span><span class="mini-seq-line" style="--w:58%"></span><em>Day 3</em></div>
              <div class="mini-seq-row"><span class="mini-seq-dot"></span><span class="mini-seq-line" style="--w:40%"></span><em>Day 10</em></div>
            </div>
<?php elseif ($v === 'orbit'): ?>
            <div class="mini-visual mini-orbit" aria-hidden="true">
              <span class="mini-orbit-core">GN</span>
              <span class="mini-orbit-ring"></span>
              <span class="mini-orbit-node" style="--a:0deg"></span>
              <span class="mini-orbit-node" style="--a:72deg"></span>
              <span class="mini-orbit-node" style="--a:144deg"></span>
              <span class="mini-orbit-node" style="--a:216deg"></span>
              <span class="mini-orbit-node" style="--a:288deg"></span>
            </div>
<?php endif; ?>
          </article>
<?php endforeach; ?>
        </div>
      </div>
    </section>

<?php $close = $page['closing']; $showSpots = false; include GNS_TEMPLATES . '/_closing.php'; ?>
  </main>

<?php include GNS_TEMPLATES . '/_footer.php'; ?>
