<?php
/**
 * GN SCALES — CONTACT FORM HANDLER
 *
 * The contact form used to post to https://formspree.io/f/YOUR_FORM_ID, which
 * is not an endpoint. Every enquiry was destroyed on submit, silently, while
 * cold outreach pointed at the page.
 *
 * This stores the lead on the server first and emails it second, in that
 * order, because shared-host mail() is unreliable enough that a lead which
 * exists only in an email is a lead you can lose without knowing it arrived.
 *
 * Answers JSON to fetch() and a 303 redirect to a normal form post, so the
 * page works identically with JavaScript switched off.
 */

require __DIR__ . '/admin/lib/boot.php';

$content = gns_content();
$wantsJson = gns_wants_json();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    gns_submit_fail('This address only accepts form submissions.', 405, $wantsJson);
}

/* ---------------------------------------------------------------
   Spam gates. None of them inconvenience a person.
   --------------------------------------------------------------- */

// 1. Honeypot: a field positioned off-screen that nobody can see and most
//    bots fill in anyway. Answer 200 so the sender learns nothing.
if (trim((string)gns_post('website')) !== '') {
    gns_log('lead.spam', 'honeypot ' . gns_client_ip());
    gns_submit_done($content, $wantsJson, true);
}

// 2. Time-to-complete. Five fields cannot be filled honestly in two seconds.
$started = (float)gns_post('_started');
if ($started > 0 && (microtime(true) * 1000 - $started) < 2000) {
    gns_log('lead.spam', 'too fast ' . gns_client_ip());
    gns_submit_done($content, $wantsJson, true);
}

// 3. Rate limit per address: a burst from one IP is a script, not a studio
//    shopping around.
if (!gns_rate_ok()) {
    gns_submit_fail('Too many submissions from this connection. Try again shortly.', 429, $wantsJson);
}

/* ---------------------------------------------------------------
   Validation
   --------------------------------------------------------------- */

$name     = gns_clean(gns_post('name'), 120);
$business = gns_clean(gns_post('business'), 160);
$email    = gns_clean(gns_post('email'), 190);
$sector   = gns_clean(gns_post('sector'), 40);
$spend    = gns_clean(gns_post('spend'), 40);
$message  = gns_clean(gns_post('message'), 4000);

$errors = array();
if ($name === '')     { $errors[] = 'a name'; }
if ($business === '') { $errors[] = 'a business name'; }
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'a valid email address'; }

// The two qualifying answers must be real options, not whatever was posted.
$labels = gns_lead_labels($content);
if (!isset($labels['sector'][$sector])) { $errors[] = 'the kind of work you do'; }
if (!isset($labels['spend'][$spend]))   { $errors[] = 'your current ad spend'; }

if ($errors) {
    gns_submit_fail('We still need ' . gns_join($errors) . '.', 422, $wantsJson);
}

/* ---------------------------------------------------------------
   Store, then notify
   --------------------------------------------------------------- */

$lead = gns_add_lead(array(
    'name'     => $name,
    'business' => $business,
    'email'    => $email,
    'sector'   => $sector,
    'spend'    => $spend,
    'message'  => $message,
    'source'   => gns_clean(gns_source(), 300),
    'ip'       => gns_client_ip(),
));

$mailed = false;
try {
    $mailed = gns_mail_lead($content, $lead);
} catch (Throwable $err) {
    $mailed = false;
}
gns_update_lead($lead['id'], array('mailed' => $mailed));
gns_log('lead.new', $email . ($mailed ? ' (emailed)' : ' (stored only)'));

gns_submit_done($content, $wantsJson, false);

/* ===============================================================
   Helpers
   =============================================================== */

function gns_post($key)
{
    return isset($_POST[$key]) ? $_POST[$key] : '';
}

/** Trim, normalise newlines, strip control characters, cap the length. */
function gns_clean($value, $max)
{
    $value = (string)$value;
    $value = str_replace(array("\r\n", "\r"), "\n", $value);
    $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);
    $value = trim($value);
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $max, 'UTF-8');
    }
    return substr($value, 0, $max);
}

function gns_join(array $parts)
{
    if (count($parts) === 1) {
        return $parts[0];
    }
    $last = array_pop($parts);
    return implode(', ', $parts) . ' and ' . $last;
}

/** Where the visitor came from, for attributing outreach campaigns. */
function gns_source()
{
    $ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    $query = '';
    if ($ref !== '') {
        $parsed = parse_url($ref);
        if (!empty($parsed['query'])) {
            parse_str($parsed['query'], $params);
            $utm = array();
            foreach (array('utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'gclid', 'fbclid') as $key) {
                if (!empty($params[$key])) {
                    $utm[] = $key . '=' . $params[$key];
                }
            }
            $query = implode(' ', $utm);
        }
    }
    return $query !== '' ? $query : $ref;
}

function gns_wants_json()
{
    $accept = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';
    return strpos($accept, 'application/json') !== false;
}

/**
 * Six submissions per address per hour. Stored as a small file rather than a
 * session, because a bot will not carry a cookie.
 */
function gns_rate_ok()
{
    $file = GNS_DATA . '/ratelimit.php';
    $data = gns_read_php_array($file, array());
    $key = substr(hash('sha256', gns_client_ip()), 0, 16);
    $now = time();

    foreach ($data as $k => $row) {
        if ($now - (int)$row['first'] > 3600) {
            unset($data[$k]);
        }
    }
    $row = isset($data[$key]) ? $data[$key] : array('n' => 0, 'first' => $now);
    $row['n'] = (int)$row['n'] + 1;
    $data[$key] = $row;
    gns_write_php_array($file, $data, 'GN Scales contact form rate limit');

    return $row['n'] <= 6;
}

/** Success: JSON for fetch(), a 303 redirect for a plain form post. */
function gns_submit_done(array $content, $json, $silent)
{
    $next = gns_next_url();
    if ($json) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array('ok' => true, 'redirect' => $next, 'quiet' => (bool)$silent));
        exit;
    }
    header('Location: ' . $next, true, 303);
    exit;
}

function gns_submit_fail($message, $status, $json)
{
    if ($json) {
        header('Content-Type: application/json; charset=utf-8', true, $status);
        echo json_encode(array('ok' => false, 'error' => $message));
        exit;
    }
    header('Content-Type: text/html; charset=utf-8', true, $status);
    $safe = e($message);
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">'
       . '<meta name="viewport" content="width=device-width,initial-scale=1">'
       . '<title>That did not send — GN Scales</title>'
       . '<link rel="stylesheet" href="/style.css"><link rel="stylesheet" href="/legal.css">'
       . '</head><body><main id="main"><section class="legal-page"><div class="shell legal-inner">'
       . '<header class="legal-head"><h1>That did not send</h1>'
       . '<p class="lead">' . $safe . '</p></header>'
       . '<div class="btn-row"><a class="btn btn-primary btn-lg" href="/contact.html">Back to the form</a></div>'
       . '</div></section></main></body></html>';
    exit;
}

/** Only ever our own thank-you page — never a URL supplied in the request. */
function gns_next_url()
{
    $next = (string)gns_post('_next');
    if ($next === '' || !preg_match('#^[a-z0-9._-]+\.html$#i', $next)) {
        $next = 'thanks.html';
    }
    return '/' . $next;
}
