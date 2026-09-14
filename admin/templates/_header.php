<?php
/**
 * Site header. Expects $c and $meta.
 * The "current" nav item is matched against this page's output filename, so it
 * stays correct when pages are reordered or renamed in the admin.
 */
$nav = gns_get($c, 'site.nav', array());
$here = $meta['file'];
$ctaLabel = gns_get($c, 'site.cta.header_label', 'Book a call');
$ctaHref = gns_get($c, 'site.cta.href', 'contact.html');
$spots = gns_spots_line($c, false);
?>
  <header class="site-header">
    <div class="shell header-inner">
      <a class="brand" href="/" aria-label="<?= e(gns_get($c, 'site.name', 'GN Scales')) ?> — home">
        <?= gns_brand_mark('brand-mark') ?>
        <span class="brand-type">
          <span class="brand-gn">GN</span>
          <span class="brand-scales">Scales</span>
        </span>
      </a>

      <button type="button" class="nav-toggle" aria-expanded="false" aria-controls="site-nav">
        <span aria-hidden="true"></span>
        <span aria-hidden="true"></span>
        <span class="sr-only">Menu</span>
      </button>

      <nav class="site-nav" id="site-nav" aria-label="Primary">
        <ul class="nav-list">
<?php foreach ($nav as $item):
    $href = (string)$item['href'];
    $isHere = ($href === $here) || ($href === '/' && $here === 'index.html');
?>
          <li><a href="<?= e(gns_href($href)) ?>"<?= $isHere ? ' aria-current="page"' : '' ?>><?= rich($item['label']) ?></a></li>
<?php endforeach; ?>
        </ul>
        <div class="nav-end">
          <span class="spots"><?= e($spots) ?></span>
<?php if ($meta['key'] === 'contact'): ?>
          <a class="btn btn-ghost btn-sm" href="#form">Jump to form</a>
<?php else: ?>
          <a class="btn btn-primary btn-sm" href="<?= e(gns_href($ctaHref)) ?>">
            <?= rich($ctaLabel) ?>
            <?= gns_icon_arrow() ?>
          </a>
<?php endif; ?>
        </div>
      </nav>
    </div>
  </header>
