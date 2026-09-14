<?php
/**
 * Admin chrome. Login and setup render bare; everything else gets the
 * sidebar, the header strip and the flash messages.
 */
$bare = in_array($viewName, array('login', 'setup'), true);
$user = $bare ? null : gns_current_user();
$unread = $bare ? 0 : gns_unread_leads();

$titles = array(
    'dashboard' => array('Overview', 'What is live, what still needs doing.'),
    'editor'    => array('', ''),
    'media'     => array('Media', 'Images used across the site. Uploads land in assets/uploads.'),
    'leads'     => array('Enquiries', 'Everything the contact form has received, newest first.'),
    'backups'   => array('Versions', 'Every save keeps a snapshot. Restoring one rebuilds the site.'),
    'account'   => array('Account', 'Your password, and who else can sign in.'),
    'login'     => array('Sign in', ''),
    'setup'     => array('Set up the admin', ''),
);
$head = isset($titles[$viewName]) ? $titles[$viewName] : array('Admin', '');
if ($viewName === 'editor') {
    $head = array(strip_tags($schema[$screen]['label']), isset($schema[$screen]['blurb']) ? $schema[$screen]['blurb'] : '');
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e(strip_tags($head[0])) ?> — GN Scales admin</title>
  <meta name="theme-color" content="#00081a">
  <link rel="icon" href="/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="/assets/fonts/fonts.css">
  <link rel="stylesheet" href="assets/admin.css?v=<?= e(substr(md5_file(GNS_ADMIN . '/assets/admin.css'), 0, 8)) ?>">
</head>
<body<?= $bare ? ' class="auth"' : '' ?>>

<?php if ($bare): ?>
  <?php include GNS_VIEWS . '/' . $viewName . '.php'; ?>
<?php else: ?>
<div class="adm">
  <aside class="adm-side">
    <a class="adm-brand" href="index.php">
      <?= gns_brand_mark('', '      ') ?>
      <span>
        <b>GN Scales</b>
        <span>Admin</span>
      </span>
    </a>

    <nav class="adm-nav" aria-label="Admin sections">
      <a href="index.php?view=dashboard"<?= $viewName === 'dashboard' ? ' aria-current="page"' : '' ?>>Overview</a>
      <a href="index.php?view=leads"<?= $viewName === 'leads' ? ' aria-current="page"' : '' ?>>
        Enquiries
        <?php if ($unread): ?><span class="pill"><?= (int)$unread ?></span><?php endif; ?>
      </a>

      <p class="adm-nav-label">Content</p>
<?php foreach (gns_schema() as $key => $def):
    $on = $viewName === 'editor' && $screen === $key;
?>
      <a href="index.php?view=edit&amp;screen=<?= e($key) ?>"<?= $on ? ' aria-current="page"' : '' ?>><?= $def['label'] ?></a>
<?php endforeach; ?>

      <p class="adm-nav-label">Assets &amp; history</p>
      <a href="index.php?view=media"<?= $viewName === 'media' ? ' aria-current="page"' : '' ?>>Media</a>
      <a href="index.php?view=backups"<?= $viewName === 'backups' ? ' aria-current="page"' : '' ?>>Versions</a>
      <a href="index.php?view=account"<?= $viewName === 'account' ? ' aria-current="page"' : '' ?>>Account</a>
    </nav>

    <div class="adm-side-foot">
      <p class="adm-who">Signed in as <?= e($user) ?></p>
      <a class="btn btn-quiet btn-xs" href="/" target="_blank" rel="noopener">View the site</a>
      <a class="btn btn-quiet btn-xs" href="index.php?view=logout">Sign out</a>
    </div>
  </aside>

  <main class="adm-main">
    <header class="adm-top">
      <div>
        <h1><?= e(strip_tags($head[0])) ?></h1>
<?php if ($head[1] !== ''): ?>
        <p><?= $head[1] ?></p>
<?php endif; ?>
      </div>
      <div class="adm-top-actions">
        <form method="post" action="index.php?view=<?= e($viewName) ?><?= $viewName === 'editor' ? '&amp;screen=' . e($screen) : '' ?>">
          <?= gns_csrf_field() ?>
          <input type="hidden" name="action" value="rebuild">
          <button type="submit" class="btn btn-quiet" title="Regenerate every .html file from the current content">Rebuild site</button>
        </form>
      </div>
    </header>

    <div class="adm-body">
<?php foreach ($flash as $note):
    $cls = $note[0] === 'ok' ? 'is-ok' : ($note[0] === 'bad' ? 'is-bad' : 'is-warn');
?>
      <div class="notice <?= $cls ?>">
        <div>
          <strong><?= e($note[1]) ?></strong>
<?php if (!empty($note[2])): ?>
          <ul>
<?php foreach ($note[2] as $line): ?>
            <li><?= e($line) ?></li>
<?php endforeach; ?>
          </ul>
<?php endif; ?>
        </div>
      </div>
<?php endforeach; ?>

      <?php include GNS_VIEWS . '/' . $viewName . '.php'; ?>
    </div>
  </main>
</div>
<script src="assets/admin.js?v=<?= e(substr(md5_file(GNS_ADMIN . '/assets/admin.js'), 0, 8)) ?>" defer></script>
<?php endif; ?>
</body>
</html>
