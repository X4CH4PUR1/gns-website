<?php
/**
 * Document head, shared by every page.
 * Expects: $c (content), $page (this page's content), $meta (view model).
 */
$siteName  = plain(gns_get($c, 'site.name', 'GN Scales'));
$themeCol  = gns_get($c, 'site.theme_color', '#00081a');
$twitter   = trim((string)gns_get($c, 'site.twitter', ''));
$customCss = trim((string)gns_get($c, 'custom.css', '')) !== '';
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($meta['title']) ?></title>
  <meta name="description" content="<?= e($meta['description']) ?>">
  <meta name="author" content="<?= e($siteName) ?>">
  <meta name="format-detection" content="telephone=no">
  <meta name="theme-color" content="<?= e($themeCol) ?>">
<?php if (!$meta['index']): ?>
  <meta name="robots" content="noindex, follow">
<?php endif; ?>
<?php if ($meta['canonical']): ?>
  <link rel="canonical" href="<?= e($meta['canonical']) ?>">
<?php endif; ?>

  <!-- Set before the stylesheets load, so the reveal animations only ever hide
       content on a page that has JavaScript to bring it back. Without this one
       line a failed script leaves most of the page permanently invisible. -->
  <script>document.documentElement.className += ' js';</script>

  <meta property="og:type" content="website">
  <meta property="og:site_name" content="<?= e($siteName) ?>">
  <meta property="og:locale" content="en_US">
<?php if ($meta['canonical']): ?>
  <meta property="og:url" content="<?= e($meta['canonical']) ?>">
<?php endif; ?>
  <meta property="og:title" content="<?= e($meta['og_title'] !== '' ? $meta['og_title'] : $meta['title']) ?>">
  <meta property="og:description" content="<?= e($meta['description']) ?>">
  <meta property="og:image" content="<?= e($meta['og_image']) ?>">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:image:alt" content="<?= e($siteName . ' — ' . plain(gns_get($c, 'site.tagline', ''))) ?>">
  <meta name="twitter:card" content="summary_large_image">
<?php if ($twitter !== ''): ?>
  <meta name="twitter:site" content="<?= e($twitter) ?>">
<?php endif; ?>

  <link rel="icon" href="/favicon.ico" sizes="32x32">
  <link rel="icon" href="/favicon.svg" type="image/svg+xml">
  <link rel="apple-touch-icon" href="/apple-touch-icon.png">
  <link rel="manifest" href="/site.webmanifest">

  <!-- Fonts are served from this origin: no DNS or TLS handshake to a third
       party on the critical path, and nothing to disclose in the privacy page.
       The two faces above the fold are preloaded; the rest arrive with the CSS. -->
  <link rel="preload" href="/assets/fonts/instrument-serif-400.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="/assets/fonts/schibsted-grotesk-var.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="<?= e(gns_asset('assets/fonts/fonts.css')) ?>">

  <link rel="stylesheet" href="<?= e(gns_asset('style.css')) ?>">
  <link rel="stylesheet" href="<?= e(gns_asset($meta['css'])) ?>">
<?php if ($customCss): ?>
  <link rel="stylesheet" href="<?= e(gns_asset('assets/site.css')) ?>">
<?php endif; ?>

<?= gns_jsonld($meta['key'], $c) ?>
<?= gns_tracking_head($c) ?>
<?php
$customHead = trim((string)gns_get($c, 'custom.head_html', ''));
if ($customHead !== ''):
?>
  <?= $customHead ?>

<?php endif; ?>
</head>
<body>
  <a class="skip-link" href="#main">Skip to main content</a>
