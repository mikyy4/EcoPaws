<?php
require __DIR__ . '/../includes/admin.php';

$monthly = query("SELECT DATE_FORMAT(created_at,'%Y-%m') AS ym, SUM(is_valid) AS valid, COUNT(*) - SUM(is_valid) AS rejected
                  FROM bottle_entries GROUP BY ym ORDER BY ym DESC LIMIT 12")->fetchAll();
$txs     = query('SELECT * FROM transactions ORDER BY id DESC LIMIT 50')->fetchAll();
$entries = query('SELECT * FROM bottle_entries ORDER BY id DESC LIMIT 50')->fetchAll();

admin_header('Bottle Collections', 'bottle', 'Monthly totals, transactions and every item inserted.');
?>
<section class="card">
  <h2><?= ai('cal') ?> Monthly Collections</h2>
  <?= data_table([
        ['Month', 'ym'],
        ['Valid Bottles', 'valid', 'num'],
        ['Rejected Items', 'rejected', 'num'],
      ], $monthly, 'No data yet.') ?>
</section>

<section class="card">
  <h2><?= ai('list') ?> Transactions <?= pill('latest 50', 'mute') ?></h2>
  <?= data_table([
        ['ID',       fn($t) => '<b>#' . e($t['id']) . '</b>'],
        ['Started',  'started_at'],
        ['Status',   fn($t) => tx_status_pill($t['status'])],
        ['Valid',    'valid_bottles', 'num'],
        ['Rejected', 'invalid_attempts', 'num'],
        ['Sticks',   'dental_sticks', 'num'],
        ['Portions', 'food_portions', 'num'],
        ['Food',     fn($t) => food_pill($t['food_type'])],
      ], $txs, 'No transactions yet.') ?>
</section>

<section class="card">
  <h2><?= ic_bottle() ?> Item Entries <?= pill('latest 50 · valid & rejected', 'mute') ?></h2>
  <?= data_table([
        ['Time',     'created_at'],
        ['Tx',       fn($r) => '#' . e($r['transaction_id'])],
        ['Category', 'category'],
        ['Item',     fn($r) => '<b>' . e($r['item_name']) . '</b>'],
        ['Size',     'size'],
        ['Class',    'bottle_class'],
        ['Result',   fn($r) => $r['is_valid'] ? pill('VALID') : pill('INVALID', 'bad')],
        ['Reason',   fn($r) => e($r['reject_reason'] ?? '-')],
      ], $entries, 'No entries yet.') ?>
</section>
<?php admin_footer();
