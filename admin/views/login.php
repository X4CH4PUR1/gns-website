<?php
/** Sign in. Rate limited in auth.php; nothing here reveals which half was wrong. */
$locked = gns_is_locked_out();
?>
<form class="auth-card" method="post" action="index.php?view=login">
  <?= gns_csrf_field() ?>
  <div class="auth-brand">
    <?= gns_brand_mark('', '    ') ?>
    <div><h1>GN Scales</h1></div>
  </div>

<?php if (isset($_GET['bye'])): ?>
  <div class="notice is-ok"><div>Signed out.</div></div>
<?php endif; ?>

<?php foreach ($flash as $note): ?>
  <div class="notice is-bad"><div><?= e($note[1]) ?></div></div>
<?php endforeach; ?>

<?php if ($locked): ?>
  <div class="notice is-bad"><div>
    Too many failed attempts from this connection. Try again in
    <?= (int)ceil(gns_lockout_remaining() / 60) ?> minutes.
  </div></div>
<?php endif; ?>

  <div class="field">
    <label for="username">Username</label>
    <input type="text" id="username" name="username" autocomplete="username" autofocus required <?= $locked ? 'disabled' : '' ?>>
  </div>

  <div class="field">
    <label for="password">Password</label>
    <input type="password" id="password" name="password" autocomplete="current-password" required <?= $locked ? 'disabled' : '' ?>>
  </div>

  <button type="submit" class="btn btn-primary" <?= $locked ? 'disabled' : '' ?>>Sign in</button>
</form>
