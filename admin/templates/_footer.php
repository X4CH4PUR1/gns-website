<?php
/**
 * Site footer plus the closing script tags. Expects $c and $meta.
 */
$nav   = gns_get($c, 'site.nav', array());
$email = trim((string)gns_get($c, 'site.email', ''));
$caps  = gns_get($c, 'site.footer.caps', array());
$social = array_filter(array_map('trim', (array)gns_get($c, 'site.social', array())));
$customBody = trim((string)gns_get($c, 'custom.body_html', ''));
$consent = !empty(gns_get($c, 'integrations.consent_banner', false));
?>
  <footer class="site-footer">
    <div class="shell">
      <div class="footer-grid">
        <div class="footer-brand">
          <a class="brand" href="<?= e(gns_href('/')) ?>" aria-label="<?= e(gns_get($c, 'site.name', 'GN Scales')) ?> — home">
            <?= gns_brand_mark('brand-mark', '            ') ?>
            <span class="brand-type">
              <span class="brand-gn">GN</span>
              <span class="brand-scales">Scales</span>
            </span>
          </a>
          <p><?= rich(gns_get($c, 'site.blurb', '')) ?></p>
<?php if ($social): ?>
          <div class="footer-social">
<?php foreach ($social as $network => $url): ?>
            <a href="<?= e($url) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e(gns_get($c, 'site.name', 'GN Scales') . ' on ' . ucfirst($network)) ?>"><?= ucfirst(e($network)) ?></a>
<?php endforeach; ?>
          </div>
<?php endif; ?>
        </div>

        <div class="footer-col">
          <h2><?= e(gns_get($c, 'site.footer.pages_heading', 'Pages')) ?></h2>
<?php foreach ($nav as $item): ?>
          <a href="<?= e(gns_href($item['href'])) ?>"><?= rich($item['label']) ?></a>
<?php endforeach; ?>
        </div>

        <div class="footer-col">
          <h2><?= e(gns_get($c, 'site.footer.caps_heading', 'Capabilities')) ?></h2>
<?php foreach ($caps as $cap): ?>
          <a href="<?= e(gns_href($cap['href'])) ?>"><?= rich($cap['label']) ?></a>
<?php endforeach; ?>
        </div>

        <div class="footer-col">
          <h2><?= e(gns_get($c, 'site.footer.touch_heading', 'Get in touch')) ?></h2>
<?php if ($email !== ''): ?>
          <a class="footer-mail" href="mailto:<?= e($email) ?>"><?= e($email) ?></a>
<?php endif; ?>
          <a href="<?= e(gns_href(gns_get($c, 'site.cta.href', 'contact.html'))) ?>"><?= rich(gns_get($c, 'site.footer.cta_label', 'Book a strategy call')) ?></a>
          <span><?= rich(gns_get($c, 'site.location', '')) ?></span>
        </div>
      </div>

      <div class="footer-base">
        <p>© <span id="year"><?= e(gmdate('Y')) ?></span> <?= e(gns_get($c, 'site.legal_name', 'GN Scales')) ?>. All rights reserved.</p>
        <p class="footer-legal">
          <a href="<?= e(gns_href('privacy.html')) ?>">Privacy</a>
          <span aria-hidden="true">·</span>
          <a href="<?= e(gns_href('terms.html')) ?>">Terms</a>
        </p>
        <p><?= rich(gns_get($c, 'site.footer.note', '')) ?></p>
      </div>
    </div>
  </footer>

<?php if ($consent): ?>
  <div class="consent" id="consent" hidden>
    <p><?= rich(gns_get($c, 'integrations.consent_text', '')) ?> <a href="<?= e(gns_href('privacy.html')) ?>">Privacy</a></p>
    <div class="consent-actions">
      <button type="button" class="btn btn-ghost btn-sm" data-consent="no">Decline</button>
      <button type="button" class="btn btn-primary btn-sm" data-consent="yes">Accept</button>
    </div>
  </div>
<?php endif; ?>
<?php if ($customBody !== ''): ?>
  <?= $customBody ?>

<?php endif; ?>
  <script src="<?= e(gns_asset('main.js')) ?>" defer></script>
</body>
</html>
