<?php
/**
 * PRIVACY and TERMS share this template. Expects $c, $page, $meta.
 *
 * The __TAGS__ placeholder in the privacy content is replaced with a list
 * generated from the integrations settings, so the policy cannot quietly go
 * out of date the day a pixel is switched on.
 */
$updated = trim((string)gns_get($page, 'updated', ''));
if ($updated === '') {
    $updated = gmdate('j F Y');
}
include GNS_TEMPLATES . '/_head.php';
include GNS_TEMPLATES . '/_header.php';
?>

  <main id="main">
    <section class="legal-page">
      <div class="shell legal-inner">
        <header class="legal-head">
          <h1><?= rich($page['h1']) ?></h1>
          <p class="lead"><?= rich($page['lead']) ?></p>
          <p class="legal-updated">Last updated <?= e($updated) ?></p>
        </header>

<?php foreach ($page['sections'] as $i => $sec): $id = 'sec-' . ($i + 1); ?>
        <section class="legal-section" aria-labelledby="<?= $id ?>">
          <h2 id="<?= $id ?>"><?= rich($sec['h']) ?></h2>
<?php foreach ($sec['p'] as $para):
    $text = (string)$para;
    if (strpos($text, '__TAGS__') !== false) {
        $text = str_replace('__TAGS__', gns_tracking_summary($c), $text);
    }
?>
          <p><?= rich($text) ?></p>
<?php endforeach; ?>
        </section>
<?php endforeach; ?>

        <section class="legal-section" aria-labelledby="sec-contact">
          <h2 id="sec-contact">Contact</h2>
          <p>
            Questions about any of this go to
            <a href="mailto:<?= e(gns_get($c, 'site.email', '')) ?>"><?= e(gns_get($c, 'site.email', '')) ?></a>,
            which both founders read.
          </p>
<?php $postal = trim((string)gns_get($c, 'site.postal', '')); if ($postal !== ''): ?>
          <p class="legal-address"><?= nl2br(e($postal)) ?></p>
<?php endif; ?>
        </section>
      </div>
    </section>
  </main>

<?php include GNS_TEMPLATES . '/_footer.php'; ?>
