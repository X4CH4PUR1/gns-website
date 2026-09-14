<?php
/** THANK YOU. Also the conversion trigger for analytics. Expects $c, $page, $meta. */
include GNS_TEMPLATES . '/_head.php';
include GNS_TEMPLATES . '/_header.php';
?>

  <main id="main">
    <section class="narrow-page" data-conversion="lead">
      <div class="grain" aria-hidden="true"></div>
      <div class="shell narrow-inner">
        <p class="hero-flag">
          <span class="pulse" aria-hidden="true"></span>
          <span>Received</span>
        </p>
        <h1><?= rich($page['h1']) ?></h1>
        <p class="lead"><?= rich($page['lead']) ?></p>

        <hr class="hair">

        <h2 class="narrow-sub"><?= rich($page['next_heading']) ?></h2>
        <div class="btn-row">
<?php foreach ($page['links'] as $i => $link): ?>
          <a class="btn <?= $i === 0 ? 'btn-primary' : 'btn-ghost' ?> btn-lg" href="<?= e(gns_href($link['href'])) ?>">
            <?= rich($link['label']) ?>
            <?= gns_icon_arrow() ?>
          </a>
<?php endforeach; ?>
        </div>
      </div>
    </section>
  </main>

<?php include GNS_TEMPLATES . '/_footer.php'; ?>
