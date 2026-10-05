<?php
/**
 * Jay Kay Aqua Tech Admin — Central Configuration
 * ------------------------------------------------------------------
 * Every admin page includes this file first. It centralizes:
 *  - Filesystem paths (so nothing is hardcoded per-page)
 *  - Session bootstrap (secure cookie params)
 *  - Error reporting behaviour
 *  - Small app-wide constants
 *
 * SETUP NOTE FOR DEPLOYMENT:
 * This build uses MySQL/MariaDB via PDO. Database credentials live in
 * ONE place — admin/includes/db-config.php — edit that file with your
 * Hostinger (or other host's) database host/name/user/password after
 * importing database.sql via phpMyAdmin. Every page in this admin
 * talks to the database only through PDO + prepared statements.
 * ------------------------------------------------------------------
 */

declare(strict_types=1);

error_reporting(E_ALL);
// Do not print raw errors to the browser in a way that could leak paths/queries.
// Flip display_errors to '1' locally while developing if needed.
ini_set('display_errors', '0');
ini_set('log_errors', '1');

define('ADMIN_ROOT', __DIR__ . '/..');
define('SITE_ROOT', ADMIN_ROOT . '/..');

// Where uploaded files physically land. Reuses the Phase 1 asset folders
// that were already scaffolded for real photography/catalogs, so admin
// uploads flow into exactly the places the public site's <img>/<a> tags
// would expect if Phase 1 templates are later wired to live data.
define('UPLOAD_PRODUCTS_DIR', SITE_ROOT . '/assets/images/products');
define('UPLOAD_EVENTS_DIR', SITE_ROOT . '/assets/images/events');
define('UPLOAD_CATALOG_DIR', SITE_ROOT . '/assets/catalog');

define('UPLOAD_PRODUCTS_URL', '../assets/images/products');
define('UPLOAD_EVENTS_URL', '../assets/images/events');
define('UPLOAD_CATALOG_URL', '../assets/catalog');

define('MAX_IMAGE_BYTES', 5 * 1024 * 1024);   // 5MB
define('MAX_PDF_BYTES', 20 * 1024 * 1024);    // 20MB

define('SESSION_NAME', 'jaykay_admin_sid');

// --- Secure-ish session bootstrap -----------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Session idle timeout (2 hours of inactivity logs the admin out).
const SESSION_IDLE_LIMIT = 7200;
if (isset($_SESSION['admin_last_seen']) && (time() - $_SESSION['admin_last_seen']) > SESSION_IDLE_LIMIT) {
    $_SESSION = [];
    session_destroy();
    session_start();
}
$_SESSION['admin_last_seen'] = time();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
