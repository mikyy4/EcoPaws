<?php
/**
 * Loaded by every page: configuration, session, database and small helpers.
 */
require_once __DIR__ . '/config.php';

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();
date_default_timezone_set('Asia/Manila');

// ---------- Output helpers ----------

/** HTML-escape a value. */
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

/** "1 bottle" / "2 bottles" */
function plural(int $n, string $word, ?string $many = null): string {
    return $n . ' ' . ($n === 1 ? $word : ($many ?? $word . 's'));
}

function pct($q, $c): int { return $c > 0 ? (int)round($q / $c * 100) : 0; }

/** Path prefix to reach the project root from the current page (pages live in /, /app, /admin or /api). */
function rel(string $path = ''): string {
    $dir = basename(dirname($_SERVER['SCRIPT_FILENAME'] ?? ''));
    return (in_array($dir, ['app', 'admin', 'api'], true) ? '../' : '') . $path;
}

function redirect(string $to): never { header('Location: ' . $to); exit; }

function json_out(array $data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json');
    header('Cache-Control: no-store');
    echo json_encode($data);
    exit;
}

// ---------- One-time messages ----------

function flash_set(string $message, string $type = 'ok'): void {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

/** Returns ['message' => ..., 'type' => 'ok'|'bad'] once, then clears it. */
function flash_get(): ?array {
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

// ---------- CSRF (admin forms) ----------

function csrf_token(): string {
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(16));
}
function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}
/** Stops the request if the posted token is missing or wrong. */
function csrf_verify(): void {
    if (!hash_equals(csrf_token(), (string)($_POST['csrf'] ?? ''))) {
        http_response_code(400);
        exit('Invalid or expired form. Go back, reload the page and try again.');
    }
}

// ---------- Database ----------

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
        $pdo->exec("SET time_zone = '+08:00'");
    }
    return $pdo;
}

/** Run a query and return the statement (prepared when params are given). */
function query(string $sql, array $params = []): PDOStatement {
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}

/** First column of the first row, or $default when there is no row. */
function scalar(string $sql, array $params = [], $default = 0) {
    $v = query($sql, $params)->fetchColumn();
    return $v === false ? $default : $v;
}

/** Read a row from the settings table (all rows are loaded once per request). */
function setting(string $key, $default = null) {
    static $all = null;
    $all ??= db()->query('SELECT setting_key, setting_value FROM settings')->fetchAll(PDO::FETCH_KEY_PAIR);
    return $all[$key] ?? $default;
}

/** Reward rules: $per valid bottles = 1 portion + 1 dental stick, $g grams per portion. */
function reward_rules(): array {
    return [
        'per' => max(1, (int)setting('bottles_per_stick', 5)),
        'g'   => max(1, (int)setting('grams_per_portion', 25)),
    ];
}

function load_bottles(): array {
    static $bottles = null;
    return $bottles ??= json_decode(file_get_contents(BOTTLES_FILE), true) ?: [];
}

// Friendly message when something goes wrong (most often: MySQL is off or setup.php was not run).
set_exception_handler(function (Throwable $ex) {
    http_response_code(500);
    $db = $ex instanceof PDOException;
    echo '<!DOCTYPE html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
       . '<body style="font:16px system-ui;max-width:560px;margin:12vh auto;padding:0 20px;color:#23403A">'
       . '<h1>' . ($db ? 'Cannot reach the database' : 'Something went wrong') . '</h1>'
       . '<p>' . ($db ? 'Start MySQL in the XAMPP Control Panel, then open <a href="' . e(rel('setup.php')) . '">setup.php</a> once.'
                       : 'Please inform the barangay staff.') . '</p>'
       . '<p style="color:#5E766F;font-size:14px">' . e($ex->getMessage()) . '</p></body>';
});

require_once __DIR__ . '/transactions.php';
require_once __DIR__ . '/scanner.php';
