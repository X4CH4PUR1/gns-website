<?php
/** STUDIO / ABOUT. Expects $c, $page, $meta. */
$intro = $page['intro'];
$story = $page['story'];
$fh    = $page['founders'];
$prin  = $page['principles'];
$founders = gns_get($c, 'founders', array());
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

    <!-- ORIGIN -->
    <section class="section" aria-labelledby="origin-heading">
      <div class="shell story">
        <div class="story-rail reveal">
          <p class="eyebrow eyebrow-stack"><?= rich($story['eyebrow']) ?></p>
        </div>
        <div class="story-copy reveal">
          <h2 id="origin-heading" class="sr-only"><?= rich($story['h2']) ?></h2>
          <p class="story-lede"><?= rich($story['lede']) ?></p>
<?php foreach ($story['body'] as $para): ?>
          <p><?= rich($para) ?></p>
<?php endforeach; ?>
          <p class="note-rule"><?= rich($story['note']) ?></p>
        </div>
      </div>
    </section>

    <!-- FOUNDERS -->
    <section class="section section-alt" aria-labelledby="founders-heading">
      <div class="shell">
        <div class="section-head section-head-split reveal">
          <div>
            <p class="eyebrow"><?= rich($fh['eyebrow']) ?></p>
            <h2 id="founders-heading"><?= rich($fh['h2']) ?></h2>
          </div>
          <p class="lead"><?= rich($fh['lead']) ?></p>
        </div>

        <div class="founder-grid stagger">
<?php foreach ($founders as $f): ?>
          <article class="card card-gold founder-card">
<?php if (trim((string)$f['photo']) !== ''): ?>
            <img class="portrait-lg" src="<?= e($f['photo']) ?>" width="600" height="750" loading="lazy" decoding="async" alt="<?= e(plain($f['name']) . ', ' . plain($f['role_full'])) ?>">
<?php else: ?>
            <div class="portrait-lg portrait-<?= e($f['tone']) ?>" role="img" aria-label="<?= e(plain($f['name'])) ?>">
              <span aria-hidden="true"><?= e($f['initials']) ?></span>
            </div>
<?php endif; ?>
            <div class="founder-body">
              <h3><?= rich($f['name']) ?></h3>
              <p class="founder-role"><?= rich($f['role_full']) ?></p>
              <p><?= rich($f['bio_full']) ?></p>
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

    <!-- PRINCIPLES -->
    <section class="section" aria-labelledby="principles-heading">
      <div class="shell">
        <div class="section-head reveal">
          <p class="eyebrow"><?= rich($prin['eyebrow']) ?></p>
          <h2 id="principles-heading"><?= rich($prin['h2']) ?></h2>
        </div>

        <div class="principles stagger">
<?php foreach ($prin['items'] as $i => $item): ?>
          <article class="principle">
            <p class="principle-num"><?= sprintf('%02d', $i + 1) ?></p>
            <h3><?= rich($item['title']) ?></h3>
            <p><?= rich($item['body']) ?></p>
          </article>
<?php endforeach; ?>
        </div>
      </div>
    </section>

<?php $close = $page['closing']; $showSpots = true; include GNS_TEMPLATES . '/_closing.php'; ?>
  </main>

<?php include GNS_TEMPLATES . '/_footer.php'; ?>
