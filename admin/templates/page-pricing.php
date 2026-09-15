<?php
/** PRICING. Expects $c, $page, $meta. */
$intro = $page['intro'];
$setup = $page['setup'];
$faq   = $page['faq'];
include GNS_TEMPLATES . '/_head.php';
include GNS_TEMPLATES . '/_header.php';
?>

  <main id="main">

    <section class="page-intro is-center">
      <div class="shell">
        <p class="eyebrow eyebrow-center"><?= rich($intro['eyebrow']) ?></p>
        <h1><?= rich($intro['h1']) ?></h1>
        <p class="lead"><?= rich($intro['lead']) ?></p>
        <h2 class="page-intro-sub is-center"><?= rich($intro['keyword_h2']) ?></h2>
      </div>
    </section>

    <section class="section" aria-labelledby="tiers-heading">
      <div class="shell">
        <h2 id="tiers-heading" class="sr-only">Retainer tiers</h2>

        <div class="tiers stagger">
<?php foreach ($page['tiers'] as $tier):
    $featured = !empty($tier['featured']);
?>
          <article class="card<?= $featured ? ' card-gold tier is-featured' : ' tier' ?>">
<?php if (trim((string)$tier['flag']) !== ''): ?>
            <p class="tier-flag<?= $featured ? '' : ' is-quiet' ?>"><?= rich($tier['flag']) ?></p>
<?php endif; ?>
            <div class="tier-head">
              <p class="tier-for<?= $featured ? ' is-gold' : '' ?>"><?= rich($tier['for']) ?></p>
              <h3><?= rich($tier['name']) ?></h3>
            </div>
            <div class="tier-price">
              <p class="tier-figure"><?= $featured
                    ? '<span class="metal metal-figure">' . e(gns_money($tier['price'])) . '</span>'
                    : e(gns_money($tier['price'])) ?> <span><?= rich($tier['period']) ?></span></p>
              <p class="tier-setup"><?= rich($tier['setup']) ?></p>
            </div>
            <ul class="ticks<?= $featured ? ' is-bright' : '' ?>">
<?php foreach ($tier['features'] as $feature): ?>
              <li><?= gns_icon_tick() ?><?= rich($feature) ?></li>
<?php endforeach; ?>
            </ul>
            <a class="btn <?= $featured ? 'btn-primary' : 'btn-ghost' ?> btn-block" href="<?= e(gns_href($tier['cta_href'])) ?>">
              <?= rich($tier['cta_label']) ?>
              <?= gns_icon_arrow() ?>
            </a>
          </article>
<?php endforeach; ?>
        </div>

        <p class="parity">
          <?= gns_icon_info() ?>
          <?= rich($page['parity']) ?>
        </p>

        <aside class="spend-note reveal" aria-label="Ad spend disclosure">
          <span class="icon" aria-hidden="true"><?= gns_icon_shield() ?></span>
          <div>
            <h2><?= rich($page['spend_h2']) ?></h2>
            <p><?= rich($page['spend_body']) ?></p>
          </div>
        </aside>
      </div>
    </section>

    <section class="section section-alt" aria-labelledby="setup-heading">
      <div class="shell">
        <div class="section-head section-head-split reveal">
          <div>
            <p class="eyebrow"><?= rich($setup['eyebrow']) ?></p>
            <h2 id="setup-heading"><?= rich($setup['h2']) ?></h2>
          </div>
          <p class="lead"><?= rich($setup['lead']) ?></p>
        </div>

        <div class="setup-grid stagger">
<?php foreach ($setup['steps'] as $i => $step): ?>
          <article class="card setup-step">
            <p class="setup-num"><?= sprintf('%02d', $i + 1) ?></p>
            <h3><?= rich($step['title']) ?></h3>
            <p><?= rich($step['body']) ?></p>
          </article>
<?php endforeach; ?>
        </div>
      </div>
    </section>

    <section class="section" aria-labelledby="pfaq-heading">
      <div class="shell pricing-faq">
        <div class="reveal">
          <p class="eyebrow"><?= rich($faq['eyebrow']) ?></p>
          <h2 id="pfaq-heading"><?= rich($faq['h2']) ?></h2>
        </div>

        <div class="faq-list reveal">
<?php foreach ($faq['items'] as $i => $qa): $open = $i === 0; $id = 'pf-' . ($i + 1); ?>
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

<?php $close = $page['closing']; $showSpots = false; include GNS_TEMPLATES . '/_closing.php'; ?>
  </main>

<?php include GNS_TEMPLATES . '/_footer.php'; ?>
