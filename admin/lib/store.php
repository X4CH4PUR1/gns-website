<?php
/**
 * GN SCALES — CONTENT STORE
 *
 * The whole site is one nested array. It lives in data/content.php, which is a
 * PHP file that returns that array: requested over HTTP it prints nothing, and
 * it is opcached like any other include.
 *
 * Defaults live in default-content.php and are merged underneath whatever is
 * stored, so a new field added in a later release appears with its default
 * instead of rendering as a blank on the live site.
 */

$GLOBALS['gns_content_cache'] = null;

function gns_default_content()
{
    static $defaults = null;
    if ($defaults === null) {
        $defaults = require __DIR__ . '/default-content.php';
    }
    return $defaults;
}

/** The live content tree: stored values layered over the shipped defaults. */
function gns_content($fresh = false)
{
    if (!$fresh && $GLOBALS['gns_content_cache'] !== null) {
        return $GLOBALS['gns_content_cache'];
    }
    $stored = gns_read_php_array(GNS_CONTENT_FILE, array());
    $merged = gns_merge(gns_default_content(), $stored);
    $GLOBALS['gns_content_cache'] = $merged;
    return $merged;
}

/** True once the site has content of its own rather than only the defaults. */
function gns_content_is_customised()
{
    return is_file(GNS_CONTENT_FILE);
}

/**
 * Persist the content tree. Snapshots the previous version first so a bad edit
 * is always one click from being undone.
 */
function gns_save_content(array $content, $note = '')
{
    gns_backup_content($note);
    $ok = gns_write_php_array(GNS_CONTENT_FILE, $content, 'GN Scales site content');
    if ($ok) {
        $GLOBALS['gns_content_cache'] = null;
        gns_log('content.save', $note);
    }
    return $ok;
}

function gns_backup_content($note = '')
{
    if (!is_file(GNS_CONTENT_FILE)) {
        return null;
    }
    $name = gmdate('Ymd-His') . '-' . substr(bin2hex(random_bytes(3)), 0, 6) . '.php';
    $path = GNS_BACKUPS . '/' . $name;
    @copy(GNS_CONTENT_FILE, $path);

    // Keep the 40 most recent snapshots; older ones are noise.
    $all = glob(GNS_BACKUPS . '/*.php') ?: array();
    sort($all);
    while (count($all) > 40) {
        @unlink(array_shift($all));
    }
    if ($note !== '') {
        @file_put_contents(GNS_BACKUPS . '/index.log', $name . "\t" . $note . "\n", FILE_APPEND);
    }
    return $name;
}

function gns_list_backups()
{
    $notes = array();
    foreach (@file(GNS_BACKUPS . '/index.log') ?: array() as $line) {
        $parts = explode("\t", trim($line), 2);
        if (count($parts) === 2) {
            $notes[$parts[0]] = $parts[1];
        }
    }
    $out = array();
    foreach (glob(GNS_BACKUPS . '/*.php') ?: array() as $path) {
        $name = basename($path);
        $out[] = array(
            'name' => $name,
            'time' => @filemtime($path),
            'size' => @filesize($path),
            'note' => isset($notes[$name]) ? $notes[$name] : '',
        );
    }
    usort($out, function ($a, $b) {
        return $b['time'] <=> $a['time'];
    });
    return $out;
}

function gns_restore_backup($name)
{
    $name = basename($name);
    $path = GNS_BACKUPS . '/' . $name;
    if (!is_file($path)) {
        return false;
    }
    $data = gns_read_php_array($path, array());
    if (!$data) {
        return false;
    }
    return gns_save_content(gns_merge(gns_default_content(), $data), 'restored ' . $name);
}

/* ---------------------------------------------------------------
   Derived values used by both the renderer and the admin
   --------------------------------------------------------------- */

/** Absolute site URL with no trailing slash, e.g. https://gnscales.com */
function gns_site_url(array $c)
{
    return rtrim((string)gns_get($c, 'site.url', 'https://gnscales.com'), '/');
}

/**
 * The founding-spots line, derived from two numbers instead of being typed into
 * seven places. While nothing is signed it says what is true — that the cohort
 * is open — rather than implying a count that does not exist.
 */
function gns_spots_line(array $c, $long = false)
{
    $total = max(1, (int)gns_get($c, 'site.spots_total', 10));
    $taken = max(0, min($total, (int)gns_get($c, 'site.spots_taken', 0)));
    $left = $total - $taken;

    if ($taken === 0) {
        return $long
            ? 'Taking ' . $total . ' founding partners'
            : $total . ' founding spots';
    }
    if ($left === 0) {
        return $long ? 'All ' . $total . ' founding spots are taken' : 'Roster full';
    }
    return $long
        ? $left . ' of ' . $total . ' founding spots remaining'
        : $left . ' / ' . $total . ' spots';
}

/**
 * The break-even model, in PHP, so the statically rendered page shows the same
 * figures the JavaScript will show. main.js carries a line-for-line twin of
 * this function; tools/check-calc.php asserts the two agree.
 */
function gns_calc(array $opts)
{
    $spend  = max(0, (float)$opts['spend']);
    $job    = max(1, (float)$opts['job']);
    $close  = min(100, max(1, (float)$opts['close']));
    $margin = min(95, max(5, (float)$opts['margin']));
    $fee    = max(0, (float)$opts['fee']);

    $profitPerJob = $job * ($margin / 100);
    $total = $spend + $fee;
    $jobs  = max(1, (int)ceil($total / $profitPerJob));
    $leads = max(1, (int)ceil($jobs / ($close / 100)));
    $cpl   = (int)floor($spend / $leads);

    // A planning reference, not a promise: for considered local purchases a
    // workable cost per lead sits near 4.5% of job value, floored and capped so
    // a $200 detail and a $12,000 ring both land somewhere sane.
    $benchmark = (int)round(min(450, max(20, $job * 0.045)));

    if ($cpl < $benchmark * 0.25) {
        // Not "difficult" — arithmetically out of reach. Saying so is the
        // whole point of publishing the model.
        $band = 'unviable';
    } elseif ($cpl < $benchmark * 0.6) {
        $band = 'tight';
    } elseif ($cpl <= $benchmark * 1.6) {
        $band = 'middle';
    } else {
        $band = 'headroom';
    }

    return array(
        'spend' => $spend, 'job' => $job, 'close' => $close, 'margin' => $margin,
        'fee' => $fee, 'total' => $total, 'profit' => $profitPerJob,
        'jobs' => $jobs, 'leads' => $leads, 'cpl' => $cpl,
        'benchmark' => $benchmark, 'band' => $band,
    );
}

/** The verdict sentence for a computed model. Mirrors calcVerdict() in main.js. */
function gns_calc_verdict(array $r)
{
    $cpl = gns_money($r['cpl']);
    $bm  = gns_money($r['benchmark']);
    if ($r['band'] === 'unviable') {
        return 'At these numbers the arithmetic does not work: covering the cost needs '
            . number_format($r['leads']) . ' leads a month out of ' . gns_money($r['spend'])
            . ' of media. Job value, margin or budget has to move before a retainer makes sense — '
            . 'and that is exactly the kind of thing we would tell you on the call.';
    }
    if ($r['band'] === 'tight') {
        return 'At ' . $cpl . ' per lead this is tight. For a ' . gns_money($r['job'])
            . ' job we would expect to plan around ' . $bm
            . ', so the spend has to work harder than usual before the retainer pays for itself.';
    }
    if ($r['band'] === 'middle') {
        return 'A ' . $cpl . ' cost per lead is the honest middle of the range for a '
            . gns_money($r['job']) . ' job. Achievable in most local markets, but not a given.';
    }
    return 'At ' . $cpl . ' per lead you have real headroom — we would plan around ' . $bm
        . ' for a job this size. High job values are where design-led creative pays for itself fastest.';
}
