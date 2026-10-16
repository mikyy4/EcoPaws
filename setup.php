<?php
/**
 * One-time installer: http://localhost/ecopaws/setup.php
 * Creates the database, tables, default settings, stock and the admin account.
 * Safe to run again (it never deletes data or duplicates the admin).
 */
require_once __DIR__ . '/includes/config.php';

$steps = [];    // [ok, message]
$fatal = null;

/** Table definitions.*/
const TABLES = [
    'admins' => "CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        last_login DATETIME NULL
    ) ENGINE=InnoDB",

    'settings' => "CREATE TABLE IF NOT EXISTS settings (
        setting_key VARCHAR(50) PRIMARY KEY,
        setting_value VARCHAR(100) NOT NULL,
        description VARCHAR(150) NULL
    ) ENGINE=InnoDB",

    // D4 Food Stock Data + reward stock + bottle bin
    'inventory' => "CREATE TABLE IF NOT EXISTS inventory (
        item_key VARCHAR(30) PRIMARY KEY,
        label VARCHAR(60) NOT NULL,
        quantity INT NOT NULL DEFAULT 0,
        capacity INT NOT NULL,
        unit VARCHAR(10) NOT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",

    'transactions' => "CREATE TABLE IF NOT EXISTS transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        completed_at DATETIME NULL,
        status ENUM('open','completed','cancelled') NOT NULL DEFAULT 'open',
        valid_bottles INT NOT NULL DEFAULT 0,
        invalid_attempts INT NOT NULL DEFAULT 0,
        dental_sticks INT NOT NULL DEFAULT 0,
        food_portions INT NOT NULL DEFAULT 0,
        food_type ENUM('dog','cat') NULL
    ) ENGINE=InnoDB",

    // D1 Bottle Data (every scanned item, valid or not)
    'bottle_entries' => "CREATE TABLE IF NOT EXISTS bottle_entries (
        id INT AUTO_INCREMENT PRIMARY KEY,
        transaction_id INT NOT NULL,
        category VARCHAR(60) NOT NULL,
        item_name VARCHAR(80) NOT NULL,
        size VARCHAR(30) NOT NULL,
        bottle_class VARCHAR(30) NOT NULL,
        color VARCHAR(40) NOT NULL,
        is_valid TINYINT(1) NOT NULL,
        reject_reason VARCHAR(150) NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX (transaction_id), INDEX (created_at),
        FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    // D2 Reward Data
    'reward_log' => "CREATE TABLE IF NOT EXISTS reward_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        transaction_id INT NOT NULL,
        dental_sticks INT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX (created_at),
        FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    // D3 Feeding Data
    'feeding_log' => "CREATE TABLE IF NOT EXISTS feeding_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        transaction_id INT NOT NULL,
        food_type ENUM('dog','cat') NOT NULL,
        portions INT NOT NULL,
        grams INT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX (created_at),
        FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    // D5 Food Credit Data (1 credit stored per valid bottle, claimed when food is dispensed)
    'food_credits' => "CREATE TABLE IF NOT EXISTS food_credits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        transaction_id INT NOT NULL,
        entry_id INT NULL,
        credits INT NOT NULL DEFAULT 1,
        status ENUM('stored','claimed') NOT NULL DEFAULT 'stored',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        claimed_at DATETIME NULL,
        INDEX (transaction_id), INDEX (status),
        FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    'stock_log' => "CREATE TABLE IF NOT EXISTS stock_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        item_key VARCHAR(30) NOT NULL,
        change_qty INT NOT NULL,
        note VARCHAR(100) NULL,
        admin_id INT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX (created_at)
    ) ENGINE=InnoDB",
];

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8mb4', DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');
    $pdo->exec('USE `' . DB_NAME . '`');
    $steps[] = 'Database <b>' . htmlspecialchars(DB_NAME) . '</b>: ready.';

    foreach (TABLES as $name => $sql) {
        $pdo->exec($sql);
        $steps[] = "Table <b>$name</b>: ready.";
    }

    // Default settings (only inserted if missing)
    $st = $pdo->prepare('INSERT IGNORE INTO settings (setting_key, setting_value, description) VALUES (?,?,?)');
    $st->execute(['bottles_per_stick', '5',  'Valid bottles needed for 1 dental stick']);
    $st->execute(['grams_per_portion', '25', 'Grams of animal food per portion (1 portion per bottles_per_stick valid bottles)']);

    // Default inventory (ASSUMED capacities - edit as needed)
    $st = $pdo->prepare('INSERT IGNORE INTO inventory (item_key, label, quantity, capacity, unit) VALUES (?,?,?,?,?)');
    $st->execute(['dog_food',     'Dog food',               5000, 5000, 'g']);
    $st->execute(['cat_food',     'Cat food',               5000, 5000, 'g']);
    $st->execute(['dental_stick', 'Dental sticks (reward)', 180,  200,  'pcs']);
    $st->execute(['bottle_bin',   'Bottle storage bin',     0,    100,  'bottles']);
    $steps[] = 'Settings and inventory: ready.';

    // Permanent admin account (only created if no admin exists yet)
    if ((int)$pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn() === 0) {
        $pdo->prepare('INSERT INTO admins (username, password_hash, full_name) VALUES (?,?,?)')
            ->execute([DEFAULT_ADMIN_USER, password_hash(DEFAULT_ADMIN_PASS, PASSWORD_DEFAULT), DEFAULT_ADMIN_NAME]);
        $steps[] = 'Admin account created.<br>Username: <b>' . htmlspecialchars(DEFAULT_ADMIN_USER) . '</b> &middot; Password: <b>'
                 . htmlspecialchars(DEFAULT_ADMIN_PASS) . '</b><br><small>Change it after your first login: Admin &rarr; Account.</small>';
    } else {
        $steps[] = 'Admin account already exists (not changed).';
    }
} catch (PDOException $ex) {
    $fatal = 'Cannot connect to MySQL. Start MySQL in the XAMPP Control Panel first.<br><small>' . htmlspecialchars($ex->getMessage()) . '</small>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>EcoPaws Setup</title>
<link rel="stylesheet" href="assets/css/base.css">
<style>
  .setup{max-width:640px;margin:6vh auto;padding:0 20px}
  .setup h1{font-size:34px;font-weight:900;margin-bottom:6px}
  .setup ul{list-style:none;margin:18px 0;padding:0;display:grid;gap:10px}
  .setup li{background:var(--mint);color:var(--green-d);border-radius:16px;padding:12px 16px;font-weight:700}
  .setup li.bad{background:var(--rose-soft);color:#8C3A29}
  .setup li small{font-weight:600;color:var(--muted)}
</style>
</head>
<body>
<main class="setup card">
  <h1>EcoPaws Setup</h1>
  <p class="lead">Creates everything the system needs. Safe to run more than once.</p>
  <ul>
<?php foreach ($steps as $s): ?>    <li><?= $s ?></li>
<?php endforeach; ?>
<?php if ($fatal): ?>    <li class="bad"><?= $fatal ?></li>
<?php endif; ?>
  </ul>
<?php if (!$fatal): ?>
  <div class="actions">
    <a class="btn btn-primary" href="index.php">Open EcoPaws</a>
    <a class="btn btn-ghost" href="app/index.php">Recycling app</a>
    <a class="btn btn-ghost" href="admin/login.php">Admin login</a>
  </div>
<?php endif; ?>
</main>
</body>
</html>
