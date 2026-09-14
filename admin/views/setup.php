<?php
/** First run. Reachable only while no account exists. */
?>
<form class="auth-card" method="post" action="index.php?view=setup">
  <?= gns_csrf_field() ?>
  <div class="auth-brand">
    <?= gns_brand_mark('', '    ') ?>
    <div>
      <h1>Set up the admin</h1>
    </div>
  </div>

  <p>
    Nobody has claimed this admin yet, so whoever fills this in owns it. Do it now,
    before anyone else finds the address — the moment an account exists this screen
    stops working for good.
  </p>

<?php foreach ($flash as $note): ?>
  <div class="notice <?= $note[0] === 'ok' ? 'is-ok' : 'is-bad' ?>"><div><?= e($note[1]) ?></div></div>
<?php endforeach; ?>

  <div class="field">
    <label for="name">Your name</label>
    <input type="text" id="name" name="name" autocomplete="name" placeholder="Nick" required>
  </div>

  <div class="field">
    <label for="username">Username</label>
    <input type="text" id="username" name="username" autocomplete="username" pattern="[a-zA-Z0-9._-]{3,32}" placeholder="nick" required>
    <p class="help">3 to 32 characters: letters, numbers, dot, dash or underscore.</p>
  </div>

  <div class="field">
    <label for="password">Password</label>
    <input type="password" id="password" name="password" autocomplete="new-password" minlength="12" required data-pw>
    <div class="pw-meter" data-pw-meter aria-hidden="true"><span></span></div>
    <p class="help">At least 12 characters. Use a password manager — this is the key to the whole website.</p>
  </div>

  <button type="submit" class="btn btn-primary">Create the account</button>
</form>
