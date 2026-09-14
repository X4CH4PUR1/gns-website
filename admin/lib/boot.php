<?php
/**
 * GN SCALES — ADMIN BOOTSTRAP
 *
 * Every entry point (admin/index.php, admin/api.php, admin/rebuild.php and the
 * public submit.php) starts here. Defines paths, loads the small library, and —
 * when running over the web — starts a hardened session.
 *
 * Targets PHP 7.4+ so it runs on any current cPanel host without fuss.
 */

if (PHP_VERSION_ID < 70400) {
    header('Content-Type: text/plain; charset=utf-8', true, 500);
    exit('GN Scales admin needs PHP 7.4 or newer. This server reports ' . PHP_VERSION . ".\n"
        . "Change the PHP version in cPanel under 'MultiPHP Manager'.\n");
}

define('GNS_ADMIN', dirname(__DIR__));
define('GNS_ROOT', dirname(GNS_ADMIN));
define('GNS_DATA', GNS_ROOT . '/data');
define('GNS_BACKUPS', GNS_DATA . '/backups');
define('GNS_UPLOADS', GNS_ROOT . '/assets/uploads');
define('GNS_OG', GNS_ROOT . '/assets/og');
define('GNS_TEMPLATES', GNS_ADMIN . '/templates');
define('GNS_VIEWS', GNS_ADMIN . '/views');

define('GNS_CONTENT_FILE', GNS_DATA . '/content.php');
define('GNS_USERS_FILE', GNS_DATA . '/users.php');
define('GNS_LEADS_FILE', GNS_DATA . '/leads.php');
define('GNS_LOG_FILE', GNS_DATA . '/audit.log');

define('GNS_IS_CLI', PHP_SAPI === 'cli');

require_once __DIR__ . '/util.php';
require_once __DIR__ . '/store.php';
require_once __DIR__ . '/schema.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/render.php';
require_once __DIR__ . '/leads.php';
require_once __DIR__ . '/ogimage.php';
require_once __DIR__ . '/media.php';
require_once GNS_TEMPLATES . '/_icons.php';

gns_ensure_dirs();

if (!GNS_IS_CLI) {
    gns_start_session();
}
