<?php
/**
 * GN SCALES — SHARED HELPERS
 * Escaping, safe HTML, filesystem, arrays, sessions.
 */

/* ---------------------------------------------------------------
   Output escaping
   --------------------------------------------------------------- */

/** Escape for HTML text and double-quoted attributes. */
function e($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Render a "rich" field: a small allowlist of inline HTML so editors can keep
 * the gold `<span class="metal">` treatment and the odd link, without opening
 * the site up to arbitrary markup. Anything not on the list is escaped, so the
 * worst case for a bad value is visible text rather than injected HTML.
 */
function rich($v)
{
    return gns_sanitize_inline((string)$v);
}

/** Tags allowed inside a rich field, with their permitted attributes. */
function gns_inline_allowlist()
{
    return array(
        'a'      => array('href', 'title', 'target', 'rel'),
        'br'     => array(),
        'em'     => array(),
        'i'      => array(),
        'strong' => array(),
        'b'      => array(),
        'span'   => array('class'),
        'sup'    => array(),
        'sub'    => array(),
        'small'  => array(),
    );
}

/** Class names a rich field may set on a span. Keeps the design system closed. */
function gns_allowed_classes()
{
    return array('metal', 'metal-figure', 'is-gold', 'is-dim', 'nowrap');
}

/**
 * Escape everything, then selectively restore the allowlisted tags. Working in
 * this direction (deny by default) means a tag nobody thought about is inert
 * rather than live.
 */
function gns_sanitize_inline($html)
{
    $allowed = gns_inline_allowlist();
    $out = htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    $names = implode('|', array_keys($allowed));
    $pattern = '#&lt;(/?)(' . $names . ')((?:\s+[a-zA-Z-]+\s*=\s*&quot;[^&]*&quot;)*)\s*(/?)&gt;#i';

    return preg_replace_callback($pattern, function ($m) use ($allowed) {
        $closing = $m[1] === '/';
        $tag = strtolower($m[2]);
        if ($closing) {
            return '</' . $tag . '>';
        }

        $attrs = '';
        if (trim($m[3]) !== '' && !empty($allowed[$tag])) {
            if (preg_match_all('#([a-zA-Z-]+)\s*=\s*&quot;([^&]*)&quot;#', $m[3], $pairs, PREG_SET_ORDER)) {
                foreach ($pairs as $p) {
                    $name = strtolower($p[1]);
                    if (!in_array($name, $allowed[$tag], true)) {
                        continue;
                    }
                    $val = html_entity_decode($p[2], ENT_QUOTES, 'UTF-8');

                    if ($name === 'href' && !gns_safe_url($val)) {
                        continue;
                    }
                    if ($name === 'class') {
                        $keep = array_values(array_intersect(
                            preg_split('/\s+/', trim($val)),
                            gns_allowed_classes()
                        ));
                        if (!$keep) {
                            continue;
                        }
                        $val = implode(' ', $keep);
                    }
                    if ($name === 'target' && $val !== '_blank') {
                        continue;
                    }
                    $attrs .= ' ' . $name . '="' . e($val) . '"';
                }
            }
        }
        // Any link that opens a new tab gets the opener/referrer guard for free.
        if ($tag === 'a' && strpos($attrs, 'target="_blank"') !== false && strpos($attrs, 'rel=') === false) {
            $attrs .= ' rel="noopener noreferrer"';
        }
        $selfClose = ($tag === 'br') ? ' /' : '';
        return '<' . $tag . $attrs . $selfClose . '>';
    }, $out);
}

/** Only http(s), mailto, tel and site-relative links survive. No javascript:, no data:. */
function gns_safe_url($url)
{
    $url = trim($url);
    if ($url === '') {
        return false;
    }
    if (preg_match('#^(https?:|mailto:|tel:)#i', $url)) {
        return true;
    }
    if (strpos($url, '//') === 0) {
        return false;
    }
    // Relative links only, and a colon anywhere means an unknown scheme.
    return strpos($url, ':') === false;
}

/** Strip every tag: for titles, meta descriptions and JSON-LD strings. */
function plain($v)
{
    return trim(html_entity_decode(strip_tags((string)$v), ENT_QUOTES, 'UTF-8'));
}

/* ---------------------------------------------------------------
   Arrays
   --------------------------------------------------------------- */

/** Read a dotted path out of a nested array: gns_get($c, 'site.email', ''). */
function gns_get($arr, $path, $default = null)
{
    $node = $arr;
    foreach (explode('.', $path) as $key) {
        if (!is_array($node) || !array_key_exists($key, $node)) {
            return $default;
        }
        $node = $node[$key];
    }
    return $node;
}

/** Write a dotted path into a nested array, creating levels as needed. */
function gns_set(&$arr, $path, $value)
{
    $keys = explode('.', $path);
    $node = &$arr;
    foreach ($keys as $key) {
        if (!isset($node[$key]) || !is_array($node[$key])) {
            $node[$key] = array();
        }
        $node = &$node[$key];
    }
    $node = $value;
}

/** Recursive merge where the later array wins on scalars and on list arrays. */
function gns_merge(array $base, array $over)
{
    foreach ($over as $k => $v) {
        if (is_array($v) && isset($base[$k]) && is_array($base[$k]) && !gns_is_list($v)) {
            $base[$k] = gns_merge($base[$k], $v);
        } else {
            $base[$k] = $v;
        }
    }
    return $base;
}

function gns_is_list($arr)
{
    if (!is_array($arr)) {
        return false;
    }
    return $arr === array() || array_keys($arr) === range(0, count($arr) - 1);
}

/* ---------------------------------------------------------------
   Filesystem
   --------------------------------------------------------------- */

function gns_ensure_dirs()
{
    foreach (array(GNS_DATA, GNS_BACKUPS, GNS_UPLOADS, GNS_OG) as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }
    gns_guard_dir(GNS_DATA);
}

/**
 * Drop a deny-all .htaccess into a directory that must never be served.
 * Belt and braces: the files in there are also PHP that return an array and
 * print nothing, so even a server that ignores .htaccess leaks nothing.
 */
function gns_guard_dir($dir)
{
    $file = $dir . '/.htaccess';
    if (is_file($file)) {
        return;
    }
    @file_put_contents($file, implode("\n", array(
        '# Nothing in this directory may ever be served over HTTP.',
        '<IfModule mod_authz_core.c>',
        '  Require all denied',
        '</IfModule>',
        '<IfModule !mod_authz_core.c>',
        '  Order allow,deny',
        '  Deny from all',
        '</IfModule>',
        '',
    )));
}

/**
 * Write a file atomically: write a temp file in the same directory, then
 * rename over the target. A crash mid-write leaves the old file intact
 * instead of a half-written one.
 */
function gns_write_atomic($path, $contents)
{
    $dir = dirname($path);
    if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
        return false;
    }
    $tmp = $dir . '/.gns-' . bin2hex(random_bytes(6)) . '.tmp';
    if (@file_put_contents($tmp, $contents, LOCK_EX) === false) {
        @unlink($tmp);
        return false;
    }
    @chmod($tmp, 0644);
    if (!@rename($tmp, $path)) {
        // Windows will not rename over an existing file; fall back to unlink first.
        @unlink($path);
        if (!@rename($tmp, $path)) {
            @unlink($tmp);
            return false;
        }
    }
    return true;
}

/** Persist a PHP array as an executable file that returns it. */
function gns_write_php_array($path, array $data, $header = '')
{
    $body = "<?php\n"
        . '// ' . ($header !== '' ? $header : 'GN Scales data file') . "\n"
        . '// Generated ' . gmdate('Y-m-d H:i:s') . " UTC. Edited through /admin.\n"
        . "// Requested directly this file prints nothing - it only returns an array.\n"
        . 'return ' . gns_export($data) . ";\n";
    return gns_write_atomic($path, $body);
}

/** var_export with tidier indentation for nested arrays. */
function gns_export($value, $indent = 0)
{
    $pad = str_repeat('    ', $indent);
    if (is_array($value)) {
        if ($value === array()) {
            return 'array()';
        }
        $isList = gns_is_list($value);
        $out = "array(\n";
        foreach ($value as $k => $v) {
            $out .= $pad . '    ';
            if (!$isList) {
                $out .= gns_export($k) . ' => ';
            }
            $out .= gns_export($v, $indent + 1) . ",\n";
        }
        return $out . $pad . ')';
    }
    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }
    if ($value === null) {
        return 'null';
    }
    if (is_int($value) || is_float($value)) {
        return (string)$value;
    }
    return "'" . str_replace(array('\\', "'"), array('\\\\', "\\'"), (string)$value) . "'";
}

/** Load a PHP array file, returning the fallback if it is absent or malformed. */
function gns_read_php_array($path, $fallback = array())
{
    if (!is_file($path)) {
        return $fallback;
    }
    $data = @include $path;
    return is_array($data) ? $data : $fallback;
}

/* ---------------------------------------------------------------
   Session and request
   --------------------------------------------------------------- */

function gns_is_https()
{
    if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
        return true;
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])
        && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
        return true;
    }
    return (int)(isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : 0) === 443;
}

function gns_start_session()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $secure = gns_is_https();
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params(array(
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Strict',
        ));
    } else {
        session_set_cookie_params(0, '/; samesite=Strict', '', $secure, true);
    }
    session_name('gnsadm');
    @session_start();
}

function gns_client_ip()
{
    return (string)(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'cli');
}

/** Append a line to the audit log so every change has a trail. */
function gns_log($action, $detail = '')
{
    $who = isset($_SESSION['gns_user']) ? $_SESSION['gns_user'] : (GNS_IS_CLI ? 'cli' : '-');
    $line = sprintf(
        "%s\t%s\t%s\t%s\t%s\n",
        gmdate('c'),
        gns_client_ip(),
        $who,
        $action,
        str_replace(array("\n", "\t"), ' ', (string)$detail)
    );
    @file_put_contents(GNS_LOG_FILE, $line, FILE_APPEND | LOCK_EX);

    // Keep the log from growing without bound on a long-lived site.
    if (@filesize(GNS_LOG_FILE) > 1048576) {
        $kept = array_slice(@file(GNS_LOG_FILE) ?: array(), -2000);
        gns_write_atomic(GNS_LOG_FILE, implode('', $kept));
    }
}

/* ---------------------------------------------------------------
   Formatting
   --------------------------------------------------------------- */

function gns_money($n)
{
    return '$' . number_format((float)$n, 0, '.', ',');
}

function gns_slug($s)
{
    $s = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', (string)$s), '-'));
    return $s === '' ? 'item' : $s;
}

/** PHP 7 safety net for a function used in a couple of places. */
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle)
    {
        return $needle === '' || strpos($haystack, $needle) !== false;
    }
}
