<?php
/** Password change and additional accounts. */
$users = gns_users();
$me = gns_current_user();
?>
<div class="panel">
  <div class="panel-head">
    <h2>Change your password</h2>
    <p>Signed in as <span class="mono"><?= e($me) ?></span>.</p>
  </div>
  <form class="panel-body" method="post" action="index.php?view=account">
    <?= gns_csrf_field() ?>
    <input type="hidden" name="action" value="password">
    <div class="fields">
      <div class="field">
        <label for="current">Current password</label>
        <input type="password" id="current" name="current" autocomplete="current-password" required>
      </div>
      <div class="field">
        <label for="new">New password</label>
        <input type="password" id="new" name="new" autocomplete="new-password" minlength="12" required data-pw>
        <div class="pw-meter" data-pw-meter aria-hidden="true"><span></span></div>
        <p class="help">At least 12 characters.</p>
      </div>
    </div>
    <div class="row"><button type="submit" class="btn btn-primary">Change password</button></div>
  </form>
</div>

<div class="panel">
  <div class="panel-head">
    <h2>Who can sign in</h2>
    <p>Both founders should have their own account rather than sharing one.</p>
  </div>
  <div class="tbl-wrap">
    <table class="tbl">
      <thead><tr><th>Username</th><th>Name</th><th>Created</th><th>Last signed in</th></tr></thead>
      <tbody>
<?php foreach ($users['users'] as $name => $row): ?>
        <tr>
          <td class="mono"><?= e($name) ?><?= $name === $me ? ' (you)' : '' ?></td>
          <td><?= e($row['name']) ?></td>
          <td class="num"><?= e(gmdate('j M Y', $row['created'])) ?></td>
          <td class="num"><?= $row['last'] ? e(gmdate('j M Y, H:i', $row['last'])) . ' UTC' : 'never' ?></td>
        </tr>
<?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <form class="panel-body" method="post" action="index.php?view=account">
    <?= gns_csrf_field() ?>
    <input type="hidden" name="action" value="adduser">
    <div class="fields">
      <div class="field">
        <label for="au-name">Name</label>
        <input type="text" id="au-name" name="name" required>
      </div>
      <div class="field">
        <label for="au-username">Username</label>
        <input type="text" id="au-username" name="username" pattern="[a-zA-Z0-9._-]{3,32}" required>
      </div>
      <div class="field is-full">
        <label for="au-password">Password</label>
        <input type="password" id="au-password" name="password" autocomplete="new-password" minlength="12" required data-pw>
        <div class="pw-meter" data-pw-meter aria-hidden="true"><span></span></div>
        <p class="help">Set one now and have them change it after their first sign-in.</p>
      </div>
    </div>
    <div class="row"><button type="submit" class="btn btn-ghost">Add an account</button></div>
  </form>
</div>

<div class="panel">
  <div class="panel-head">
    <h2>Keeping this address quiet</h2>
  </div>
  <div class="panel-body">
    <p class="muted">
      Nothing on the public site links here, <span class="mono">robots.txt</span> disallows it and
      every admin page sends <span class="mono">noindex</span>. That is obscurity, not security &mdash;
      the password is what protects it, so use a long one from a password manager.
    </p>
    <p class="muted">
      Want a less guessable address? Rename the <span class="mono">admin</span> folder to anything you
      like in cPanel's File Manager. Every path inside is relative, so it keeps working, and
      <span class="mono">php &lt;newname&gt;/rebuild.php</span> still rebuilds the site. Update the
      <span class="mono">Disallow</span> line in <span class="mono">robots.txt</span> to match, or drop it &mdash;
      that line is tidiness, not a control.
    </p>
    <p class="muted">
      For a second lock, add an <span class="mono">.htaccess</span> password to the folder through
      cPanel's <em>Directory Privacy</em>. Two prompts is mildly annoying and genuinely harder to get past.
    </p>
  </div>
</div>
