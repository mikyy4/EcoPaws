<?php
/**
 * EcoPaws - settings you may need to edit.
 */

// ---- Database ----
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'ecopaws');
define('DB_USER', 'root');
define('DB_PASS', '');

// ---- Default admin account (created once by setup.php) ----
define('DEFAULT_ADMIN_USER', 'admin');
define('DEFAULT_ADMIN_PASS', 'admin123');
define('DEFAULT_ADMIN_NAME', 'Barangay Staff');

// ---- Smart scanner ----
// api/scan.php?action=push and the deposit page picks it up automatically.
// While SCANNER_DEMO is true, the deposit page shows a "Demo controls" box so the
// flow can be tested without the sensor. Set it to false on the real machine.
define('SCANNER_DEMO', true);
define('SCANNER_KEY', 'ecopaws-scanner');          // shared secret the hardware sends as &key=
define('SCAN_TOLERANCE_CM', 0.6);                  // max length difference that still counts as a match
define('SCAN_MAX_AGE_SECONDS', 20);                // ignore readings older than this

// ---- Food credits ----
// 1 valid bottle = 1 credit, and 1 credit = 1 food portion (the bottles-per-reward
// setting only controls dental sticks).
define('CREDITS_PER_PORTION', 1);

// ---- Paths ----
define('ROOT_PATH', dirname(__DIR__));
define('BOTTLES_FILE', ROOT_PATH . '/data/bottles.json');
define('SCAN_FILE', ROOT_PATH . '/data/scan_latest.json');   // data/ is blocked from the web by .htaccess
