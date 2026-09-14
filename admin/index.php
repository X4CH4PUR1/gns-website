<?php
/**
 * GN SCALES — ADMIN FRONT CONTROLLER
 *
 * Every screen in one router. Views live in admin/views, forms post back to
 * this file, and anything that changes state goes through a CSRF check.
 *
 * There is no registration: the first visit creates the one account and locks
 * setup for good. Nothing on the public site links here.
 */

require __DIR__ . '/lib/boot.php';

header('X-Robots-Tag: noindex, nofollow, noarchive', true);
header('Referrer-Policy: no-referrer');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

$view = isset($_GET['view']) ? (string)$_GET['view'] : 'dashboard';
$flash = array();
$content = gns_content();

/* ---------------------------------------------------------------
   First run
   --------------------------------------------------------------- */
if (gns_needs_setup()) {
    if ($view !== 'setup') {
        header('Location: index.php?view=setup');
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && gns_csrf_ok()) {
        $result = gns_create_user(
            isset($_POST['username']) ? $_POST['username'] : '',
            isset($_POST['password']) ? $_POST['password'] : '',
            isset($_POST['name']) ? $_POST['name'] : ''
        );
        if ($result === true) {
            gns_attempt_login($_POST['username'], $_POST['password']);
            header('Location: index.php?view=dashboard&welcome=1');
            exit;
        }
        $flash[] = array('bad', $result);
    }
    gns_view('setup', compact('flash'));
    exit;
}

/* ---------------------------------------------------------------
   Session
   --------------------------------------------------------------- */
if ($view === 'logout') {
    gns_logout();
    header('Location: index.php?view=login&bye=1');
    exit;
}

if (!gns_current_user()) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && gns_csrf_ok()) {
        $result = gns_attempt_login(
            isset($_POST['username']) ? $_POST['username'] : '',
            isset($_POST['password']) ? $_POST['password'] : ''
        );
        if ($result === true) {
            header('Location: index.php?view=dashboard');
            exit;
        }
        $flash[] = array('bad', $result);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $flash[] = array('bad', 'That form expired. Try again.');
    }
    gns_view('login', compact('flash'));
    exit;
}

/* ---------------------------------------------------------------
   Actions
   --------------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!gns_csrf_ok()) {
        $flash[] = array('bad', 'That form expired — nothing was saved. Try again.');
    } else {
        $action = isset($_POST['action']) ? $_POST['action'] : '';

        if ($action === 'save' && (!isset($_POST['_end']) || $_POST['_end'] !== 'ok')) {
            // The sentinel is the last field in the form. If it did not arrive,
            // PHP dropped everything past max_input_vars and saving would
            // silently delete whatever got cut off.
            $flash[] = array('bad',
                'That submission arrived incomplete, so nothing was saved. The server is dropping form '
                . 'fields past its max_input_vars limit (currently ' . (int)ini_get('max_input_vars')
                . '). Raise it in cPanel under "MultiPHP INI Editor", or split this screen into two saves.');
        } elseif ($action === 'save') {
            $screen = isset($_POST['screen']) ? (string)$_POST['screen'] : '';
            $saved = gns_apply_save($content, $screen, $problems);
            if ($saved === null) {
                $flash[] = array('bad', 'Unknown screen.');
            } elseif (!gns_save_content($saved, $screen)) {
                $flash[] = array('bad', 'Could not write data/content.php. Check that the data directory is writable by PHP.');
            } else {
                $content = gns_content(true);
                $result = gns_build($content);
                if ($result['errors']) {
                    $flash[] = array('bad', 'Saved, but the rebuild reported problems:', $result['errors']);
                } else {
                    $flash[] = array('ok', 'Saved and published. ' . count($result['written']) . ' files rebuilt.');
                }
                if ($problems) {
                    $flash[] = array('warn', 'Some values were adjusted:', $problems);
                }
            }
        } elseif ($action === 'rebuild') {
            $result = gns_build(gns_content(true));
            $flash[] = $result['errors']
                ? array('bad', 'Rebuild reported problems:', $result['errors'])
                : array('ok', 'Rebuilt ' . count($result['written']) . ' files from the current content.');
        } elseif ($action === 'restore') {
            $ok = gns_restore_backup(isset($_POST['backup']) ? $_POST['backup'] : '');
            if ($ok) {
                $content = gns_content(true);
                gns_build($content);
                $flash[] = array('ok', 'Restored that version and rebuilt the site.');
            } else {
                $flash[] = array('bad', 'Could not restore that backup.');
            }
        } elseif ($action === 'password') {
            $result = gns_change_password(
                gns_current_user(),
                isset($_POST['current']) ? $_POST['current'] : '',
                isset($_POST['new']) ? $_POST['new'] : ''
            );
            $flash[] = $result === true
                ? array('ok', 'Password changed.')
                : array('bad', $result);
        } elseif ($action === 'adduser') {
            $result = gns_create_user(
                isset($_POST['username']) ? $_POST['username'] : '',
                isset($_POST['password']) ? $_POST['password'] : '',
                isset($_POST['name']) ? $_POST['name'] : ''
            );
            $flash[] = $result === true
                ? array('ok', 'Account created.')
                : array('bad', $result);
        } elseif ($action === 'import') {
            $flash[] = gns_apply_import($content);
            $content = gns_content(true);
        }
    }
}

/* ---------------------------------------------------------------
   Render
   --------------------------------------------------------------- */
$schema = gns_schema();

switch ($view) {
    case 'edit':
        $screen = isset($_GET['screen']) ? (string)$_GET['screen'] : '';
        if (!isset($schema[$screen])) {
            header('Location: index.php?view=dashboard');
            exit;
        }
        gns_view('editor', compact('flash', 'content', 'schema', 'screen'));
        break;

    case 'media':
        gns_view('media', compact('flash', 'content'));
        break;

    case 'leads':
        gns_view('leads', compact('flash', 'content'));
        break;

    case 'backups':
        gns_view('backups', compact('flash', 'content'));
        break;

    case 'account':
        gns_view('account', compact('flash', 'content'));
        break;

    default:
        gns_view('dashboard', compact('flash', 'content', 'schema'));
}

/* ===============================================================
   Helpers
   =============================================================== */

/** Render a view inside the shared chrome. */
function gns_view($name, array $vars = array())
{
    extract($vars, EXTR_SKIP);
    $viewName = $name;
    include GNS_VIEWS . '/layout.php';
}

/**
 * Fold one screen's submitted fields back into the content tree.
 *
 * Only paths that this screen's schema declares are touched, so a crafted POST
 * cannot reach a field the form never offered. Values are coerced to the type
 * the schema declares, and anything adjusted on the way through is reported.
 */
function gns_apply_save(array $content, $screen, &$problems)
{
    $problems = array();
    $fields = gns_schema_fields($screen);
    if (!$fields) {
        return null;
    }

    $scalars = isset($_POST['f']) && is_array($_POST['f']) ? $_POST['f'] : array();
    $lists = isset($_POST['l']) && is_array($_POST['l']) ? $_POST['l'] : array();

    $present = isset($_POST['present']) && is_array($_POST['present']) ? $_POST['present'] : array();

    foreach ($fields as $path => $field) {
        if ($field['t'] === 'list') {
            // A list the form never rendered is left exactly as it was.
            if (!isset($present[$path])) {
                continue;
            }
            $rows = isset($lists[$path]) && is_array($lists[$path]) ? $lists[$path] : array();
            ksort($rows, SORT_NUMERIC);
            $out = array();

            foreach ($rows as $row) {
                if (!is_array($row)) {
                    continue;
                }
                if (!empty($field['simple'])) {
                    $value = trim((string)(isset($row['_v']) ? $row['_v'] : ''));
                    if ($value !== '') {
                        $out[] = $value;
                    }
                    continue;
                }
                $item = array();
                foreach ($field['item'] as $sub) {
                    $raw = isset($row[$sub['p']]) ? $row[$sub['p']] : '';
                    if ($sub['t'] === 'list') {
                        // A list nested inside a list row is edited as a
                        // textarea, one entry per line: two levels of repeater
                        // is more confusing than it is useful.
                        $lines = preg_split('/\R/', (string)$raw);
                        $item[$sub['p']] = array_values(array_filter(array_map('trim', $lines), function ($line) {
                            return $line !== '';
                        }));
                        continue;
                    }
                    $item[$sub['p']] = gns_coerce($sub, $raw);
                }
                // A row where every text field is blank is a row somebody
                // added and then thought better of.
                $meaningful = false;
                foreach ($item as $v) {
                    if ((is_string($v) && trim($v) !== '') || (is_array($v) && $v)) {
                        $meaningful = true;
                        break;
                    }
                }
                if ($meaningful) {
                    $out[] = $item;
                }
            }
            gns_set($content, $path, $out);
            continue;
        }

        if (!array_key_exists($path, $scalars)) {
            // Unchecked toggles are simply absent from the POST body.
            if ($field['t'] === 'toggle') {
                gns_set($content, $path, false);
            }
            continue;
        }

        $raw = $scalars[$path];
        $value = gns_coerce($field, $raw);
        if ($field['t'] === 'url' && trim((string)$raw) !== '' && $value === '') {
            $problems[] = $field['l'] . ': that did not look like a safe link, so it was cleared.';
        }
        if ($field['t'] === 'email' && trim((string)$raw) !== '' && $value === '') {
            $problems[] = $field['l'] . ': that did not look like an email address, so it was cleared.';
        }
        gns_set($content, $path, $value);
    }

    return $content;
}

/** Replace the whole content tree from an uploaded JSON export. */
function gns_apply_import(array $content)
{
    if (empty($_FILES['import']['tmp_name']) || !is_uploaded_file($_FILES['import']['tmp_name'])) {
        return array('bad', 'No file was uploaded.');
    }
    $json = file_get_contents($_FILES['import']['tmp_name']);
    $data = json_decode($json, true);
    if (!is_array($data) || !isset($data['site'])) {
        return array('bad', 'That file is not a GN Scales content export.');
    }
    if (!gns_save_content(gns_merge(gns_default_content(), $data), 'import')) {
        return array('bad', 'Could not write the imported content.');
    }
    $result = gns_build(gns_content(true));
    return $result['errors']
        ? array('bad', 'Imported, but the rebuild reported problems:', $result['errors'])
        : array('ok', 'Imported and rebuilt the whole site.');
}
