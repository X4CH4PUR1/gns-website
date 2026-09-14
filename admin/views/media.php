<?php
/** Uploaded images. Everything lands in assets/uploads and is referenced by path. */
$files = gns_media_list();
$total = 0;
foreach ($files as $f) { $total += $f['size']; }
?>
<div class="panel">
  <div class="panel-head">
    <h2><?= count($files) ?> file<?= count($files) === 1 ? '' : 's' ?></h2>
    <p><?= e(gns_bytes($total)) ?> in <span class="mono">assets/uploads</span></p>
  </div>
  <div class="panel-body">
    <div class="drop" data-drop>
      <p><strong>Drop images here</strong>, or choose files.</p>
      <p>JPG, PNG, WebP, GIF or SVG. Up to <?= e(gns_bytes(gns_max_upload())) ?> each.</p>
      <input type="file" class="sr-only" id="media-input" accept="image/*" multiple data-drop-input>
      <label class="btn btn-ghost" for="media-input">Choose files</label>
      <p class="mono" data-drop-status></p>
    </div>

<?php if ($files): ?>
    <div class="media" data-media-grid>
<?php foreach ($files as $file): ?>
      <figure class="media-item" data-media="<?= e($file['name']) ?>">
        <img src="<?= e($file['url']) ?>" alt="" loading="lazy">
        <figcaption class="media-meta">
          <span class="media-name"><?= e($file['name']) ?></span>
          <span class="media-size"><?= e($file['w']) ?>&times;<?= e($file['h']) ?> &middot; <?= e(gns_bytes($file['size'])) ?></span>
        </figcaption>
        <div class="media-actions">
          <button type="button" class="btn btn-quiet btn-xs" data-media-copy="<?= e($file['url']) ?>">Copy path</button>
          <button type="button" class="btn btn-danger btn-xs" data-media-delete="<?= e($file['name']) ?>">Delete</button>
        </div>
      </figure>
<?php endforeach; ?>
    </div>
<?php else: ?>
    <p class="muted">
      Nothing uploaded yet. The two founder portraits are the most valuable thing you
      could put here &mdash; consistent background, consistent crop, natural light.
    </p>
<?php endif; ?>
  </div>
</div>
