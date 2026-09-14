<?php
/**
 * 404. Wired up with ErrorDocument in .htaccess, so every stale Shopify URL
 * still sitting in Google's index lands on the brand rather than on a bare
 * Apache error page.
 *
 * Note the absolute asset paths in _head.php and the absolute links here: this
 * page is served from arbitrary URL depths, so relative paths would break.
 */
include GNS_TEMPLATES . '/_head.php';
include GNS_TEMPLATES . '/_header.php';
?>

  <main id="main">
    <section class="narrow-page">
      <div class="grain" aria-hidden="true"></div>
      <div class="shell narrow-inner">
        <p class="error-code" aria-hidden="true"><?= e($page['code']) ?></p>
        <h1><?= rich($page['h1']) ?></h1>
        <p class="lead"><?= rich($page['lead']) ?></p>
        <div class="btn-row">
          <a class="btn btn-primary btn-lg" href="<?= e(gns_href('/')) ?>">
            <?= rich($page['primary']) ?>
            <?= gns_icon_arrow() ?>
          </a>
          <a class="btn btn-ghost btn-lg" href="<?= e(gns_href('contact.html')) ?>">
            <?= rich($page['secondary']) ?>
            <?= gns_icon_arrow() ?>
          </a>
        </div>
      </div>
    </section>
  </main>

<?php include GNS_TEMPLATES . '/_footer.php'; ?>
