<?php
/**
 * One content screen. Groups are collapsible; the first is open.
 * Saving writes data/content.php and rebuilds every affected page.
 */
require_once GNS_VIEWS . '/_field.php';

$def = $schema[$screen];
$pages = gns_pages();
$preview = null;
foreach ($pages as $key => $reg) {
    if (isset($def['page']) && $def['page'] === $reg['file']) {
        $preview = $reg['file'];
    }
}
?>
<form method="post" action="index.php?view=edit&amp;screen=<?= e($screen) ?>" data-dirty-watch>
  <?= gns_csrf_field() ?>
  <input type="hidden" name="action" value="save">
  <input type="hidden" name="screen" value="<?= e($screen) ?>">

<?php if ($preview): ?>
  <p class="row" style="margin-bottom:1rem">
    <a class="btn btn-quiet btn-xs" href="/<?= e($preview) ?>" target="_blank" rel="noopener">Open <?= e($preview) ?> &#8599;</a>
  </p>
<?php endif; ?>

  <div class="stack">
<?php foreach ($def['groups'] as $i => $group): ?>
    <details class="group"<?= $i === 0 ? ' open' : '' ?>>
      <summary><?= $group['title'] ?></summary>
      <div class="group-inner">
<?php if (!empty($group['blurb'])): ?>
        <p class="group-blurb"><?= $group['blurb'] ?></p>
<?php endif; ?>
        <div class="fields">
<?php foreach ($group['fields'] as $field) {
    gns_render_field($field, $content);
} ?>
        </div>
      </div>
    </details>
<?php endforeach; ?>
  </div>

  <!-- Must stay last: gns_apply_save() uses its arrival to prove the POST
       was not truncated by max_input_vars. -->
  <input type="hidden" name="_end" value="ok">

  <div class="savebar" data-savebar>
    <p data-dirty-text>No unsaved changes.</p>
    <div class="row">
      <button type="submit" class="btn btn-primary">Save and publish</button>
    </div>
  </div>
</form>
