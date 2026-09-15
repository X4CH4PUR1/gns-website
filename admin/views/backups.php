<?php
/** Every save snapshots the previous content. Restoring one rebuilds the site. */
$backups = gns_list_backups();
?>
<div class="panel">
  <div class="panel-head">
    <h2>Saved versions</h2>
    <p>The forty most recent. Restoring replaces the current content and republishes.</p>
  </div>

<?php if (!$backups): ?>
  <div class="panel-body">
    <p class="muted">No versions yet &mdash; the first one is written the next time you save.</p>
  </div>
<?php else: ?>
  <div class="tbl-wrap">
    <table class="tbl">
      <thead><tr><th>When</th><th>Screen saved</th><th>Size</th><th></th></tr></thead>
      <tbody>
<?php foreach ($backups as $backup): ?>
        <tr>
          <td class="num"><?= e(gmdate('j M Y, H:i', $backup['time'])) ?> UTC</td>
          <td><?= e($backup['note'] !== '' ? $backup['note'] : 'content') ?></td>
          <td class="num"><?= e(gns_bytes($backup['size'])) ?></td>
          <td>
            <form method="post" action="index.php?view=backups" onsubmit="return confirm('Restore this version? The current content is snapshotted first, so this is reversible.');">
              <?= gns_csrf_field() ?>
              <input type="hidden" name="action" value="restore">
              <input type="hidden" name="backup" value="<?= e($backup['name']) ?>">
              <button type="submit" class="btn btn-quiet btn-xs">Restore</button>
            </form>
          </td>
        </tr>
<?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
</div>

<div class="panel">
  <div class="panel-head">
    <h2>Export and import</h2>
    <p>The whole site content as one JSON file. Useful as an off-server backup, and for committing content back to the repository after editing it here.</p>
  </div>
  <div class="panel-body">
    <div class="row">
      <a class="btn btn-ghost" href="api.php?action=export&amp;token=<?= e(gns_csrf_token()) ?>">Download content.json</a>
    </div>
    <form method="post" action="index.php?view=backups" enctype="multipart/form-data" class="row" onsubmit="return confirm('Import replaces all site content. The current version is snapshotted first. Continue?');">
      <?= gns_csrf_field() ?>
      <input type="hidden" name="action" value="import">
      <input type="file" name="import" accept="application/json,.json" required>
      <button type="submit" class="btn btn-quiet">Import</button>
    </form>
  </div>
</div>

<div class="notice is-warn">
  <div>
    <strong>Deploying from git overwrites the generated HTML.</strong>
    Content itself is safe &mdash; it lives in <span class="mono">data/</span>, which the deploy
    script excludes. Run <span class="mono">php admin/rebuild.php</span> after a deploy, or press
    <em>Rebuild site</em> above, and the live content is written back over the repository copies.
  </div>
</div>
