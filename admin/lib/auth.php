<?php
/**
 * GN SCALES — ADMIN AUTHENTICATION
 *
 * Password hashes in data/users.php, sessions in PHP's own store, CSRF tokens
 * on every state-changing request, and a throttle that makes online guessing
 * pointless. There is no registration: the first visit to /admin creates the
 * first account and then locks setup permanently.
 */

define('GNS_MAX_ATTEMPTS', 6);      // failures allowed inside the window
define('GNS_LOCK_WINDOW', 900);     // 15 minutes
define('GNS_SESSION_IDLE', 7200);   // 2 hours of inactivity ends the session

function gns_users()
{
    return gns_read_php_array(GNS_USERS_FILE, array('users' => array(), 'throttle' => array()));
}

function gns_save_users(array $data)
{
    return gns_write_php_array(GNS_USERS_FILE, $data, 'GN Scales admin accounts - password hashes only');
}

/** True while no account exists, which is the only time setup is reachable. */
function gns_needs_setup()
{
    $u = gns_users();
    return empty($u['users']);
}

function gns_create_user($username, $password, $name = '')
{
    $username = strtolower(trim($username));
    if (!preg_match('/^[a-z0-9._-]{3,32}$/', $username)) {
        return 'Username must be 3-32 characters: letters, numbers, dot, dash or underscore.';
    }
    $problem = gns_password_problem($password);
    if ($problem) {
        return $problem;
    }
    $data = gns_users();
    if (isset($data['users'][$username])) {
        return 'That username already exists.';
    }
    $data['users'][$username] = array(
        'name'    => trim($name) !== '' ? trim($name) : $username,
        'hash'    => password_hash($password, PASSWORD_DEFAULT),
        'created' => time(),
        'last'    => 0,
    );
    if (!gns_save_users($data)) {
        return 'Could not write data/users.php. Check that the data directory is writable.';
    }
    gns_log('user.create', $username);
    return true;
}

function gns_password_problem($password)
{
    if (strlen((string)$password) < 12) {
        return 'Password must be at least 12 characters.';
    }
    if (preg_match('/^(.)\1+$/', (string)$password)) {
        return 'Password must not be a single repeated character.';
    }
    $weak = array('password1234', 'administrator', 'gnscales2026', 'letmeinplease');
    if (in_array(strtolower((string)$password), $weak, true)) {
        return 'That password is too easy to guess.';
    }
    return null;
}

function gns_change_password($username, $current, $new)
{
    $data = gns_users();
    $username = strtolower(trim($username));
    if (!isset($data['users'][$username])) {
        return 'No such account.';
    }
    if (!password_verify($current, $data['users'][$username]['hash'])) {
        return 'Current password is not correct.';
    }
    $problem = gns_password_problem($new);
    if ($problem) {
        return $problem;
    }
    $data['users'][$username]['hash'] = password_hash($new, PASSWORD_DEFAULT);
    if (!gns_save_users($data)) {
        return 'Could not save the new password.';
    }
    gns_log('user.password', $username);
    return true;
}

/* ---------------------------------------------------------------
   Login throttling
   --------------------------------------------------------------- */

function gns_throttle_key()
{
    return substr(hash('sha256', gns_client_ip()), 0, 16);
}

function gns_throttle_state()
{
    $data = gns_users();
    $key = gns_throttle_key();
    $row = isset($data['throttle'][$key]) ? $data['throttle'][$key] : array('n' => 0, 'first' => 0);
    if (time() - (int)$row['first'] > GNS_LOCK_WINDOW) {
        $row = array('n' => 0, 'first' => 0);
    }
    return $row;
}

function gns_is_locked_out()
{
    $row = gns_throttle_state();
    return (int)$row['n'] >= GNS_MAX_ATTEMPTS;
}

function gns_lockout_remaining()
{
    $row = gns_throttle_state();
    $left = GNS_LOCK_WINDOW - (time() - (int)$row['first']);
    return max(0, $left);
}

function gns_record_failure()
{
    $data = gns_users();
    $key = gns_throttle_key();
    $row = gns_throttle_state();
    if ((int)$row['first'] === 0) {
        $row['first'] = time();
    }
    $row['n'] = (int)$row['n'] + 1;
    $data['throttle'][$key] = $row;

    // Forget throttle rows that have aged out so the file cannot grow forever.
    foreach ($data['throttle'] as $k => $r) {
        if (time() - (int)$r['first'] > GNS_LOCK_WINDOW * 4) {
            unset($data['throttle'][$k]);
        }
    }
    gns_save_users($data);
}

function gns_clear_failures()
{
    $data = gns_users();
    unset($data['throttle'][gns_throttle_key()]);
    gns_save_users($data);
}

/* ---------------------------------------------------------------
   Sessions
   --------------------------------------------------------------- */

function gns_attempt_login($username, $password)
{
    if (gns_is_locked_out()) {
        return 'Too many failed attempts. Try again in '
            . ceil(gns_lockout_remaining() / 60) . ' minutes.';
    }
    $data = gns_users();
    $username = strtolower(trim($username));
    $user = isset($data['users'][$username]) ? $data['users'][$username] : null;

    // Always run a hash comparison so a wrong username and a wrong password
    // take the same amount of time.
    $hash = $user ? $user['hash'] : '$2y$10$usesomesillystringfoeswhereobyefoiCsudMxbiuKbLwkI0lWzfQ2u';
    $ok = password_verify($password, $hash) && $user !== null;

    if (!$ok) {
        gns_record_failure();
        gns_log('login.fail', $username);
        return 'Username or password is not correct.';
    }

    if (password_needs_rehash($user['hash'], PASSWORD_DEFAULT)) {
        $data['users'][$username]['hash'] = password_hash($password, PASSWORD_DEFAULT);
    }
    $data['users'][$username]['last'] = time();
    gns_save_users($data);
    gns_clear_failures();

    session_regenerate_id(true);
    $_SESSION['gns_user'] = $username;
    $_SESSION['gns_name'] = $user['name'];
    $_SESSION['gns_seen'] = time();
    $_SESSION['gns_agent'] = gns_agent_fingerprint();
    gns_log('login.ok', $username);
    return true;
}

function gns_agent_fingerprint()
{
    return substr(hash('sha256', (string)(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '')), 0, 32);
}

function gns_current_user()
{
    if (empty($_SESSION['gns_user'])) {
        return null;
    }
    if (time() - (int)(isset($_SESSION['gns_seen']) ? $_SESSION['gns_seen'] : 0) > GNS_SESSION_IDLE) {
        gns_logout();
        return null;
    }
    if (!hash_equals((string)(isset($_SESSION['gns_agent']) ? $_SESSION['gns_agent'] : ''), gns_agent_fingerprint())) {
        gns_logout();
        return null;
    }
    $_SESSION['gns_seen'] = time();
    return $_SESSION['gns_user'];
}

function gns_require_login()
{
    if (!gns_current_user()) {
        header('Location: index.php?view=login');
        exit;
    }
}

function gns_logout()
{
    $who = isset($_SESSION['gns_user']) ? $_SESSION['gns_user'] : '-';
    $_SESSION = array();
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    @session_destroy();
    if ($who !== '-') {
        gns_log('logout', $who);
    }
}

/* ---------------------------------------------------------------
   CSRF
   --------------------------------------------------------------- */

function gns_csrf_token()
{
    if (empty($_SESSION['gns_csrf'])) {
        $_SESSION['gns_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['gns_csrf'];
}

function gns_csrf_field()
{
    return '<input type="hidden" name="_csrf" value="' . e(gns_csrf_token()) . '">';
}

function gns_csrf_ok($token = null)
{
    if ($token === null) {
        $token = isset($_POST['_csrf'])
            ? $_POST['_csrf']
            : (isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : '');
    }
    return !empty($_SESSION['gns_csrf']) && is_string($token)
        && hash_equals($_SESSION['gns_csrf'], $token);
}
