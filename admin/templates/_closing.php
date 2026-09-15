<?php
/**
 * The closing call-to-action band.
 * Expects $c and $close (the page's 'closing' array). $showSpots adds the
 * founding-partner line, which is derived from two numbers in the admin rather
 * than typed into every page.
 */
$showSpots = isset($showSpots) ? $showSpots : false;
?>
    <section class="closing">
      <div class="grain" aria-hidden="true"></div>
      <div class="shell">
<?php if ($showSpots): ?>
        <p class="hero-flag">
          <span class="pulse" aria-hidden="true"></span>
          <span><?= e(gns_spots_line($c, true)) ?></span>
        </p>
<?php endif; ?>
        <h2><?= rich($close['h2']) ?></h2>
        <p class="lead"><?= rich($close['lead']) ?></p>
        <div class="btn-row btn-row-center">
          <a class="btn btn-primary btn-lg" href="<?= e(gns_href($close['href'])) ?>">
            <?= rich($close['label']) ?>
            <?= gns_icon_arrow() ?>
          </a>
        </div>
      </div>
    </section>
