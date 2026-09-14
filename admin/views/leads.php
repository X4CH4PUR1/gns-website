<?php
/** The lead inbox. Stored server-side as well as emailed, because shared-host
 *  mail() is not reliable enough to be the only copy of a lead. */
$leads = gns_leads();
$labels = gns_lead_labels($content);
?>
<div class="panel">
  <div class="panel-head">
    <h2><?= count($leads) ?> enquir<?= count($leads) === 1 ? 'y' : 'ies' ?></h2>
    <div class="row">
<?php if ($leads): ?>
      <a class="btn btn-quiet btn-xs" href="api.php?action=leads.csv&amp;token=<?= e(gns_csrf_token()) ?>">Export CSV</a>
      <button type="button" class="btn btn-quiet btn-xs" data-leads-readall>Mark all read</button>
<?php endif; ?>
    </div>
  </div>

<?php if (!$leads): ?>
  <div class="panel-body">
    <p class="muted">
      Nothing yet. The form on <a href="/contact.html" target="_blank" rel="noopener">contact.html</a>
      posts to <span class="mono">submit.php</span>, which writes here and emails
      <span class="mono"><?= e(gns_get($content, 'integrations.lead_emails', '')) ?></span>.
      Send yourself a test to confirm the mail leg works on this host.
    </p>
  </div>
<?php else: ?>
  <div class="tbl-wrap">
    <table class="tbl">
      <thead>
        <tr>
          <th>Received</th><th>Who</th><th>Sector</th><th>Spend</th>
          <th>Message</th><th>Source</th><th></th>
        </tr>
      </thead>
      <tbody>
<?php foreach ($leads as $lead):
    $sector = isset($labels['sector'][$lead['sector']]) ? $labels['sector'][$lead['sector']] : $lead['sector'];
    $spend = isset($labels['spend'][$lead['spend']]) ? $labels['spend'][$lead['spend']] : $lead['spend'];
?>
        <tr<?= empty($lead['read']) ? ' class="is-unread"' : '' ?> data-lead="<?= e($lead['id']) ?>">
          <td class="num">
            <?= e(gmdate('j M Y', $lead['at'])) ?><br><?= e(gmdate('H:i', $lead['at'])) ?>
<?php if (isset($lead['mailed']) && !$lead['mailed']): ?>
            <br><span style="color:var(--warn)">not emailed</span>
<?php endif; ?>
          </td>
          <td>
            <strong><?= e($lead['name']) ?></strong><br>
            <?= e($lead['business']) ?><br>
            <a href="mailto:<?= e($lead['email']) ?>"><?= e($lead['email']) ?></a>
          </td>
          <td><?= e($sector) ?></td>
          <td><?= e($spend) ?></td>
          <td style="max-width:38ch"><?= nl2br(e($lead['message'])) ?></td>
          <td class="num"><?= e($lead['source'] !== '' ? $lead['source'] : 'direct') ?></td>
          <td>
            <div class="list-row-tools">
              <button type="button" data-lead-toggle title="<?= empty($lead['read']) ? 'Mark read' : 'Mark unread' ?>" aria-label="Toggle read">&#10003;</button>
              <button type="button" class="is-danger" data-lead-delete title="Delete" aria-label="Delete">&times;</button>
            </div>
          </td>
        </tr>
<?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
</div>

<div class="notice">
  <div>
    <strong>Retention.</strong>
    The privacy page promises enquiries are kept for two years and then deleted, and that
    anyone can ask for theirs to be removed sooner. Deleting a row here is how you keep
    that promise.
  </div>
</div>
