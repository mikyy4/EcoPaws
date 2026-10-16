<?php
require __DIR__ . '/../includes/admin.php';
$pdo = db();
$adminId = (int)$_SESSION['admin_id'];

/** Positive whole number from a POST field, or null. */
function post_positive_int(string $field): ?int {
    $v = filter_input(INPUT_POST, $field, FILTER_VALIDATE_INT);
    return ($v !== false && $v !== null && $v > 0) ? $v : null;
}
function log_stock(string $key, int $change, string $note, int $adminId): void {
    query('INSERT INTO stock_log (item_key, change_qty, note, admin_id) VALUES (?,?,?,?)', [$key, $change, $note, $adminId]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    switch ($_POST['action'] ?? '') {
        case 'refill':
            $key = $_POST['item_key'] ?? '';
            $amt = post_positive_int('amount');
            if ($amt && in_array($key, ['dog_food', 'cat_food', 'dental_stick'], true)) {
                query('UPDATE inventory SET quantity = LEAST(capacity, quantity + ?) WHERE item_key = ?', [$amt, $key]);
                log_stock($key, $amt, 'Refill', $adminId);
                flash_set('Stock updated (capped at capacity).');
            } else {
                flash_set('Invalid refill amount.', 'bad');
            }
            break;

        case 'empty_bin':
            $q = (int)scalar("SELECT quantity FROM inventory WHERE item_key='bottle_bin'");
            $pdo->exec("UPDATE inventory SET quantity = 0 WHERE item_key='bottle_bin'");
            log_stock('bottle_bin', -$q, 'Bin emptied by staff', $adminId);
            flash_set('Bottle bin marked as emptied.');
            break;

        case 'settings':
            $per = post_positive_int('bottles_per_stick');
            $g   = post_positive_int('grams_per_portion');
            if ($per && $g) {
                query('UPDATE settings SET setting_value = ? WHERE setting_key = ?', [$per, 'bottles_per_stick']);
                query('UPDATE settings SET setting_value = ? WHERE setting_key = ?', [$g, 'grams_per_portion']);
                flash_set('Settings saved.');
            } else {
                flash_set('Settings must be whole numbers greater than 0.', 'bad');
            }
            break;

        case 'reset_test_data':
            $pdo->exec('DELETE FROM transactions');   // cascades to entries, rewards, feeding, credits
            $pdo->exec('DELETE FROM stock_log');
            $pdo->exec("UPDATE inventory SET quantity = capacity WHERE item_key <> 'bottle_bin'");
            $pdo->exec("UPDATE inventory SET quantity = 0 WHERE item_key = 'bottle_bin'");
            flash_set('Test data cleared and stock reset to full.');
            break;
    }
    redirect('stock.php');
}

$flash = flash_get();
$rules = reward_rules();
$log   = query('SELECT * FROM stock_log ORDER BY id DESC LIMIT 20')->fetchAll();

admin_header('Stock & Capacity', 'box', 'Refill supplies, empty the bottle bin and tune the reward rule.');
?>
<?= flash_box($flash) ?>

<section class="card">
  <h2><?= ai('box') ?> Current Levels</h2>
  <?= inventory_cards(inventory_rows(), true) ?>
</section>

<div class="row2">
  <section class="card">
    <h2><?= ic_leaf() ?> System Settings</h2>
    <form method="post" action="stock.php">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="settings">
      <div class="f"><label for="per">Valid bottles needed for 1 reward</label>
        <input id="per" type="number" name="bottles_per_stick" min="1" value="<?= $rules['per'] ?>">
        <div class="hint">1 reward = 1 dental stick (food: 1 bottle = 1 portion)</div></div>
      <div class="f"><label for="grams">Grams of animal food per portion</label>
        <input id="grams" type="number" name="grams_per_portion" min="1" value="<?= $rules['g'] ?>">
        <div class="hint">in grams (g)</div></div>
      <button class="btn btn-primary sm" type="submit">Save settings</button>
    </form>
  </section>

  <section class="card">
    <h2><?= ai('list') ?> Recent Stock Changes</h2>
    <?= data_table([
          ['Time', 'created_at'],
          ['Item', 'item_key'],
          ['Change', 'change_qty', 'num'],
          ['Note', 'note'],
        ], $log, 'No changes yet.') ?>
  </section>
</div>

<section class="card danger">
  <h2><?= ai('alert') ?> Testing Tools</h2>
  <p class="sub">This deletes every transaction and refills all stock.</p>
  <form method="post" action="stock.php" onsubmit="return confirm('Delete ALL transactions and reset stock to full?');">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="reset_test_data">
    <button class="btn btn-danger sm" type="submit">Reset test data (delete all transactions, refill stock)</button>
  </form>
</section>
<?php admin_footer();
