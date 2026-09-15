<?php
/**
 * GN SCALES — LEAD INBOX
 *
 * Contact form submissions are written to data/leads.php and emailed. Storing
 * them locally matters: shared-host mail() is unreliable enough that a lead
 * which exists only in an email is a lead you can lose without ever knowing it
 * arrived.
 */

function gns_leads()
{
    $data = gns_read_php_array(GNS_LEADS_FILE, array('items' => array()));
    return isset($data['items']) && is_array($data['items']) ? $data['items'] : array();
}

function gns_save_leads(array $items)
{
    return gns_write_php_array(GNS_LEADS_FILE, array('items' => $items), 'GN Scales contact form submissions');
}

function gns_add_lead(array $lead)
{
    $items = gns_leads();
    $lead['id'] = bin2hex(random_bytes(8));
    $lead['at'] = time();
    $lead['read'] = false;
    array_unshift($items, $lead);

    // A two-person studio does not need an unbounded archive in a flat file.
    if (count($items) > 500) {
        $items = array_slice($items, 0, 500);
    }
    gns_save_leads($items);
    return $lead;
}

function gns_update_lead($id, array $changes)
{
    $items = gns_leads();
    foreach ($items as $i => $lead) {
        if ($lead['id'] === $id) {
            $items[$i] = array_merge($lead, $changes);
            gns_save_leads($items);
            return true;
        }
    }
    return false;
}

function gns_delete_lead($id)
{
    $items = gns_leads();
    $out = array();
    $found = false;
    foreach ($items as $lead) {
        if ($lead['id'] === $id) {
            $found = true;
            continue;
        }
        $out[] = $lead;
    }
    if ($found) {
        gns_save_leads($out);
        gns_log('lead.delete', $id);
    }
    return $found;
}

function gns_unread_leads()
{
    $n = 0;
    foreach (gns_leads() as $lead) {
        if (empty($lead['read'])) {
            $n++;
        }
    }
    return $n;
}

/**
 * Email a lead to whoever is listed in the admin.
 *
 * The envelope sender is an address on this domain rather than the visitor's,
 * because a shared host sending "From: someone@gmail.com" fails SPF and lands
 * in spam. The visitor's address goes in Reply-To, where it belongs.
 */
function gns_mail_lead(array $c, array $lead)
{
    $recipients = array_filter(array_map('trim', explode(',', (string)gns_get($c, 'integrations.lead_emails', ''))));
    if (!$recipients) {
        return false;
    }

    $domain = parse_url(gns_site_url($c), PHP_URL_HOST);
    if (!$domain) {
        $domain = 'localhost';
    }
    $from = 'website@' . $domain;
    $subject = (string)gns_get($c, 'integrations.lead_subject', 'New enquiry');

    $labels = gns_lead_labels($c);
    $lines = array(
        'A new enquiry came in from ' . gns_site_url($c),
        '',
        'Name:      ' . $lead['name'],
        'Business:  ' . $lead['business'],
        'Email:     ' . $lead['email'],
        'Sector:    ' . (isset($labels['sector'][$lead['sector']]) ? $labels['sector'][$lead['sector']] : $lead['sector']),
        'Ad spend:  ' . (isset($labels['spend'][$lead['spend']]) ? $labels['spend'][$lead['spend']] : $lead['spend']),
        '',
        'Message:',
        trim($lead['message']) !== '' ? $lead['message'] : '(no message)',
        '',
        '---',
        'Received ' . gmdate('D, d M Y H:i', $lead['at']) . ' UTC',
        'Source:   ' . ($lead['source'] !== '' ? $lead['source'] : 'direct'),
        'Also saved in the admin inbox.',
    );

    $headers = array(
        'From: ' . gns_get($c, 'site.name', 'GN Scales') . ' <' . $from . '>',
        'Reply-To: ' . gns_header_safe($lead['name']) . ' <' . $lead['email'] . '>',
        'Content-Type: text/plain; charset=UTF-8',
        'X-Mailer: gnscales-site',
    );

    $ok = false;
    foreach ($recipients as $to) {
        if (filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $sent = @mail($to, $subject, implode("\n", $lines), implode("\r\n", $headers), '-f' . $from);
            $ok = $ok || $sent;
        }
    }
    return $ok;
}

/** Strip anything that could inject a second header line. */
function gns_header_safe($value)
{
    return trim(preg_replace('/[\r\n]+/', ' ', (string)$value));
}

/** Map the form's stored values back to the labels shown on the page. */
function gns_lead_labels(array $c)
{
    $out = array('sector' => array(), 'spend' => array());
    foreach (gns_get($c, 'pages.contact.form.sector_options', array()) as $opt) {
        $out['sector'][$opt['value']] = plain($opt['label']);
    }
    foreach (gns_get($c, 'pages.contact.form.spend_options', array()) as $opt) {
        $out['spend'][$opt['value']] = plain($opt['label']);
    }
    return $out;
}

/** Leads as CSV, for the export button in the admin. */
function gns_leads_csv(array $c)
{
    $labels = gns_lead_labels($c);
    $fh = fopen('php://temp', 'r+');
    fputcsv($fh, array('Received (UTC)', 'Name', 'Business', 'Email', 'Sector', 'Monthly spend', 'Message', 'Source', 'Read'));
    foreach (gns_leads() as $lead) {
        fputcsv($fh, array(
            gmdate('Y-m-d H:i', $lead['at']),
            $lead['name'],
            $lead['business'],
            $lead['email'],
            isset($labels['sector'][$lead['sector']]) ? $labels['sector'][$lead['sector']] : $lead['sector'],
            isset($labels['spend'][$lead['spend']]) ? $labels['spend'][$lead['spend']] : $lead['spend'],
            $lead['message'],
            $lead['source'],
            empty($lead['read']) ? 'no' : 'yes',
        ));
    }
    rewind($fh);
    $csv = stream_get_contents($fh);
    fclose($fh);
    return $csv;
}
