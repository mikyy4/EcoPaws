<?php
require __DIR__ . '/../includes/admin.php';

/** A valid YYYY-MM-DD date, or $fallback. */
function date_or(string $d, string $fallback): string {
    return (preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) !== false) ? $d : $fallback;
}

$from  = date_or($_GET['from'] ?? '', date('Y-m-01'));
$to    = date_or($_GET['to'] ?? '', date('Y-m-d'));
$types = ['collection' => 'Bottle Collection', 'rewards' => 'Rewards', 'feeding' => 'Feeding', 'stock' => 'Stock', 'all' => 'All'];
$type  = isset($types[$_GET['type'] ?? '']) ? $_GET['type'] : 'all';

$range  = 'created_at >= ? AND created_at < DATE_ADD(?, INTERVAL 1 DAY)';
$params = [$from, $to];
/** Rows of $sql (which must contain {range}) for the selected period. */
$rows = fn(string $sql) => query(str_replace('{range}', $range, $sql), $params)->fetchAll();
$show = fn(string ...$which) => $type === 'all' || in_array($type, $which, true);

admin_header('Reports', 'report', 'Generate and print a summary for any date range.');
?>
<section class="card noprint">
  <form method="get" action="reports.php">
    <div class="fgrid">
      <div><label for="from">From</label><input id="from" type="date" name="from" value="<?= e($from) ?>"></div>
      <div><label for="to">To</label><input id="to" type="date" name="to" value="<?= e($to) ?>"></div>
      <div><label for="type">Report type</label>
        <select id="type" name="type">
<?php foreach ($types as $k => $label): ?>
          <option value="<?= $k ?>" <?= $k === $type ? 'selected' : '' ?>><?= e($label) ?></option>
<?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="actions">
      <button class="btn btn-primary sm" type="submit">Generate report</button>
      <button class="btn btn-ghost sm" type="button" onclick="window.print()">Print</button>
    </div>
  </form>
</section>

<section class="card">
  <div class="rhead">
    <h2><?= ic_paw() ?> EcoPaws Report: <?= e($types[$type]) ?></h2>
    <?= pill($from . ' → ' . $to, 'mute') ?>
  </div>
  <p class="sub">Generated: <?= date('Y-m-d H:i') ?></p>
</section>

<?php if ($show('collection')):
    $data = $rows('SELECT category, SUM(is_valid) AS valid, COUNT(*) - SUM(is_valid) AS rejected FROM bottle_entries WHERE {range} GROUP BY category ORDER BY category'); ?>
<section class="card">
  <h2><?= ic_bottle() ?> Bottle Collection</h2>
  <?= data_table([
        ['Category', 'category'],
        ['Valid Bottles', 'valid', 'num'],
        ['Rejected Items', 'rejected', 'num'],
      ], $data, 'No data in this period.',
      [['TOTAL'], [array_sum(array_column($data, 'valid')), 1, true], [array_sum(array_column($data, 'rejected')), 1, true]]) ?>
</section>
<?php endif; ?>

<?php if ($show('rewards')):
    $data = $rows('SELECT created_at, transaction_id, dental_sticks FROM reward_log WHERE {range} ORDER BY id'); ?>
<section class="card">
  <h2><?= ai('gift') ?> Rewards</h2>
  <?= data_table([
        ['Date & Time', 'created_at'],
        ['Transaction', fn($r) => '#' . e($r['transaction_id'])],
        ['Dental Sticks', 'dental_sticks', 'num'],
      ], $data, 'No rewards in this period.',
      [['TOTAL', 2], [array_sum(array_column($data, 'dental_sticks')), 1, true]]) ?>
</section>
<?php endif; ?>

<?php if ($show('feeding')):
    $data = $rows('SELECT created_at, transaction_id, food_type, portions, grams FROM feeding_log WHERE {range} ORDER BY id'); ?>
<section class="card">
  <h2><?= ic_bowl() ?> Feeding</h2>
  <?= data_table([
        ['Date & Time', 'created_at'],
        ['Transaction', fn($r) => '#' . e($r['transaction_id'])],
        ['Food',        fn($r) => food_pill($r['food_type'])],
        ['Portions',    'portions', 'num'],
        ['Amount (g)',  'grams', 'num'],
      ], $data, 'No feeding in this period.',
      [['TOTAL (' . plural(count($data), 'feeding incident') . ')', 3],
       [array_sum(array_column($data, 'portions')), 1, true], [array_sum(array_column($data, 'grams')), 1, true]]) ?>
</section>
<?php endif; ?>

<?php if ($show('stock')):
    $data = $rows('SELECT created_at, item_key, change_qty, note FROM stock_log WHERE {range} ORDER BY id'); ?>
<section class="card">
  <h2><?= ai('box') ?> Stock <?= pill('current levels', 'mute') ?></h2>
  <?= data_table([
        ['Item',     fn($i) => '<b>' . e($i['label']) . '</b>'],
        ['Current',  fn($i) => e($i['quantity']) . ' ' . e($i['unit']), 'num'],
        ['Capacity', fn($i) => e($i['capacity']) . ' ' . e($i['unit']), 'num'],
        ['Level',    fn($i) => pct($i['quantity'], $i['capacity']) . '%', 'num'],
      ], inventory_rows()) ?>
  <h3>Stock Changes <?= pill('refills / bin emptied', 'mute') ?></h3>
  <?= data_table([
        ['Date & Time', 'created_at'],
        ['Item', 'item_key'],
        ['Change', 'change_qty', 'num'],
        ['Note', 'note'],
      ], $data, 'No stock changes in this period.') ?>
</section>
<?php endif; ?>
<?php admin_footer();
