<?php
/**
 * Overview. The checklist is the useful part: it looks at the live content and
 * says what is actually still outstanding, rather than repeating a list
 * somebody wrote once and stopped updating.
 */
$leads = gns_leads();
$unreadCount = gns_unread_leads();
$integr = gns_get($content, 'integrations', array());
$founders = gns_get($content, 'founders', array());

$indexFile = GNS_ROOT . '/index.html';
$built = is_file($indexFile) ? filemtime($indexFile) : 0;

$photos = 0;
$linkedin = 0;
foreach ($founders as $f) {
    if (trim((string)$f['photo']) !== '') { $photos++; }
    if (trim((string)$f['linkedin']) !== '') { $linkedin++; }
}

$checks = array(
    array(
        'done'  => trim((string)gns_get($content, 'site.email', '')) !== ''
                   && strpos(gns_get($content, 'site.email', ''), 'yourdomain') === false,
        'text'  => 'A real email address is published',
        'todo'  => 'The public email still looks like a placeholder.',
        'where' => 'index.php?view=edit&screen=site',
    ),
    array(
        'done'  => gns_get($integr, 'form_mode', '') === 'builtin'
                   || trim((string)gns_get($integr, 'form_endpoint', '')) !== '',
        'text'  => 'The contact form delivers somewhere',
        'todo'  => 'The form is set to an external endpoint with no URL — submissions will go nowhere.',
        'where' => 'index.php?view=edit&screen=integrations',
    ),
    array(
        'done'  => $photos === count($founders) && count($founders) > 0,
        'text'  => 'Both founders have a photograph',
        'todo'  => 'Founder portraits are still monograms. Two real photographs is the highest-trust hour on this list.',
        'where' => 'index.php?view=edit&screen=founders',
    ),
    array(
        'done'  => $linkedin > 0,
        'text'  => 'At least one founder links to a findable profile',
        'todo'  => 'No LinkedIn links yet. A named person with a findable profile beats any amount of copy about honesty.',
        'where' => 'index.php?view=edit&screen=founders',
    ),
    array(
        'done'  => trim((string)gns_get($integr, 'ga4_id', '')) !== ''
                   || trim((string)gns_get($integr, 'meta_pixel_id', '')) !== '',
        'text'  => 'Something is measuring traffic',
        'todo'  => 'No analytics or pixel installed. You cannot tell whether outreach is landing, and a retargeting audience takes time to warm.',
        'where' => 'index.php?view=edit&screen=integrations',
    ),
    array(
        'done'  => trim((string)gns_get($content, 'site.postal', '')) !== '',
        'text'  => 'A postal address is on file',
        'todo'  => 'No postal address. CAN-SPAM requires one in any cold outreach, and the privacy page shows it when set.',
        'where' => 'index.php?view=edit&screen=site',
    ),
    array(
        'done'  => !empty(gns_get($content, 'pages.work.clients.items', array())),
        'text'  => 'Client work is published',
        'todo'  => 'The Work page shows studio pieces only. Add real client work once each client has agreed to be named.',
        'where' => 'index.php?view=edit&screen=work',
    ),
);
$open = 0;
foreach ($checks as $check) {
    if (!$check['done']) { $open++; }
}
?>

<?php if (isset($_GET['welcome'])): ?>
<div class="notice is-ok"><div>
  <strong>Admin created.</strong>
  Setup is locked now — this address needs your password from here on. Everything on
  the website is editable from the Content list on the left; every save rebuilds the
  affected pages immediately.
</div></div>
<?php endif; ?>

<div class="tiles">
  <div class="tile<?= $unreadCount ? ' is-alert' : '' ?>">
    <p class="tile-label">Unread enquiries</p>
    <p class="tile-value"><?= (int)$unreadCount ?></p>
    <p class="tile-note"><?= count($leads) ?> received in total</p>
  </div>
  <div class="tile">
    <p class="tile-label">Founding spots</p>
    <p class="tile-value"><?= (int)gns_get($content, 'site.spots_taken', 0) ?> / <?= (int)gns_get($content, 'site.spots_total', 10) ?></p>
    <p class="tile-note"><?= e(gns_spots_line($content, true)) ?></p>
  </div>
  <div class="tile<?= $open ? ' is-alert' : '' ?>">
    <p class="tile-label">Outstanding</p>
    <p class="tile-value"><?= (int)$open ?></p>
    <p class="tile-note">of <?= count($checks) ?> setup items</p>
  </div>
  <div class="tile">
    <p class="tile-label">Last published</p>
    <p class="tile-value" style="font-size:1.3rem"><?= $built ? e(gmdate('j M, H:i', $built)) . ' UTC' : 'never' ?></p>
    <p class="tile-note"><?= count(gns_pages()) ?> pages generated</p>
  </div>
</div>

<div class="panel">
  <div class="panel-head">
    <h2>What still needs doing</h2>
    <p>Read from the live content, so it is never out of date.</p>
  </div>
  <div class="panel-body">
    <div class="checklist">
<?php foreach ($checks as $check): ?>
      <div class="checkrow <?= $check['done'] ? 'is-done' : 'is-todo' ?>">
        <span class="dot" aria-hidden="true"></span>
        <span>
          <?= e($check['done'] ? $check['text'] : $check['todo']) ?>
<?php if (!$check['done']): ?>
          <a href="<?= e($check['where']) ?>">Fix this</a>
<?php endif; ?>
        </span>
      </div>
<?php endforeach; ?>
    </div>
  </div>
</div>

<div class="panel">
  <div class="panel-head">
    <h2>Things this admin will not do for you</h2>
    <p>Off-site work that still moves the needle more than anything on the page.</p>
  </div>
  <div class="panel-body">
    <div class="checklist">
      <div class="checkrow"><span class="dot" aria-hidden="true"></span><span>Verify the domain in <strong>Google Search Console</strong>, submit <span class="mono">/sitemap.xml</span>, then request indexing on each page. The old Shopify version of this site may still be what Google has cached.</span></div>
      <div class="checkrow"><span class="dot" aria-hidden="true"></span><span>Check the coverage report for leftover Shopify URLs (<span class="mono">/collections/*</span>, <span class="mono">/products/*</span>, <span class="mono">/cart</span>) and let them 404 cleanly — the custom 404 page is already wired up.</span></div>
      <div class="checkrow"><span class="dot" aria-hidden="true"></span><span>Add <strong>UTM tags</strong> to every outreach link, so you can tell which email did the work: <span class="mono">?utm_source=…&amp;utm_medium=email&amp;utm_campaign=…</span>. The contact form records them with the lead.</span></div>
      <div class="checkrow"><span class="dot" aria-hidden="true"></span><span>Confirm the SSL certificate covers <span class="mono">www</span> and that auto-renewal is on.</span></div>
    </div>
  </div>
</div>

<?php if ($leads): ?>
<div class="panel">
  <div class="panel-head">
    <h2>Latest enquiries</h2>
    <a class="btn btn-quiet btn-xs" href="index.php?view=leads">See all</a>
  </div>
  <div class="tbl-wrap">
    <table class="tbl">
      <thead><tr><th>Received</th><th>Name</th><th>Business</th><th>Email</th></tr></thead>
      <tbody>
<?php foreach (array_slice($leads, 0, 5) as $lead): ?>
        <tr<?= empty($lead['read']) ? ' class="is-unread"' : '' ?>>
          <td class="num"><?= e(gmdate('j M H:i', $lead['at'])) ?></td>
          <td><?= e($lead['name']) ?></td>
          <td><?= e($lead['business']) ?></td>
          <td><a href="mailto:<?= e($lead['email']) ?>"><?= e($lead['email']) ?></a></td>
        </tr>
<?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
