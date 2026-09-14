<?php
/**
 * GN SCALES — ADMIN JSON API
 *
 * The small asynchronous pieces: media uploads and deletion, marking leads
 * read, the CSV and JSON exports. Everything requires a live session and a
 * CSRF token; nothing here is reachable without both.
 */

require __DIR__ . '/lib/boot.php';
require_once __DIR__ . '/lib/media.php';

header('X-Robots-Tag: noindex, nofollow', true);
header('X-Content-Type-Options: nosniff');

if (!gns_current_user()) {
    gns_api(array('ok' => false, 'error' => 'Not signed in.'), 401);
}

$action = isset($_GET['action']) ? (string)$_GET['action'] : (isset($_POST['action']) ? (string)$_POST['action'] : '');

// Downloads are GET and carry the token in the query string; everything that
// changes state is POST with the token in a header or the body.
$token = isset($_GET['token']) ? $_GET['token'] : null;
if (!gns_csrf_ok($token)) {
    gns_api(array('ok' => false, 'error' => 'That request expired. Reload the page.'), 403);
}

switch ($action) {

    /* ----------------------------------------------------- media */
    case 'media.list':
        gns_api(array('ok' => true, 'files' => gns_media_list()));
        break;

    case 'media.upload':
        if (empty($_FILES['file'])) {
            gns_api(array('ok' => false, 'error' => 'No file was sent.'), 400);
        }
        $result = gns_media_store($_FILES['file']);
        if (is_string($result)) {
            gns_api(array('ok' => false, 'error' => $result), 422);
        }
        gns_api(array('ok' => true, 'file' => $result));
        break;

    case 'media.delete':
        $name = isset($_POST['name']) ? $_POST['name'] : '';
        gns_api(gns_media_delete($name)
            ? array('ok' => true)
            : array('ok' => false, 'error' => 'Could not delete that file.'));
        break;

    /* ----------------------------------------------------- leads */
    case 'lead.read':
        $id = isset($_POST['id']) ? $_POST['id'] : '';
        $read = !empty($_POST['read']);
        gns_api(array('ok' => gns_update_lead($id, array('read' => $read))));
        break;

    case 'lead.readall':
        $items = gns_leads();
        foreach ($items as $i => $lead) {
            $items[$i]['read'] = true;
        }
        gns_save_leads($items);
        gns_api(array('ok' => true));
        break;

    case 'lead.delete':
        $id = isset($_POST['id']) ? $_POST['id'] : '';
        gns_api(array('ok' => gns_delete_lead($id)));
        break;

    case 'leads.csv':
        $content = gns_content();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="gnscales-enquiries-' . gmdate('Y-m-d') . '.csv"');
        echo "\xEF\xBB\xBF";   // BOM, so Excel reads the UTF-8 correctly
        echo gns_leads_csv($content);
        exit;

    /* ---------------------------------------------------- export */
    case 'export':
        $stored = gns_read_php_array(GNS_CONTENT_FILE, array());
        $data = $stored ? gns_merge(gns_default_content(), $stored) : gns_default_content();
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="gnscales-content-' . gmdate('Y-m-d') . '.json"');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;

    default:
        gns_api(array('ok' => false, 'error' => 'Unknown action.'), 400);
}

function gns_api(array $payload, $status = 200)
{
    header('Content-Type: application/json; charset=utf-8', true, $status);
    echo json_encode($payload);
    exit;
}
