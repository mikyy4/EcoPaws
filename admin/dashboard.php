<?php
require __DIR__ . '/../includes/admin.php';

$totals = query("SELECT COALESCE(SUM(is_valid=1),0) AS valid,
                        COALESCE(SUM(is_valid=1 AND created_at >= DATE_FORMAT(NOW(),'%Y-%m-01')),0) AS month_valid,
                        COALESCE(SUM(is_valid=0),0) AS rejected
                 FROM bottle_entries")->fetch();
$completed = (int)scalar("SELECT COUNT(*) FROM transactions WHERE status='completed'");
$feedings  = (int)scalar('SELECT COUNT(*) FROM feeding_log');
$sticks    = (int)scalar('SELECT COALESCE(SUM(dental_sticks),0) FROM reward_log');
$pool      = pool_total();
$inv       = inventory_rows();
$alerts    = inventory_alerts($inv);

// Valid bottles per day for the last 14 days (days without bottles count as 0)
$byDay = query("SELECT DATE(created_at) AS d, COUNT(*) AS n FROM bottle_entries
                WHERE is_valid=1 AND created_at >= DATE_SUB(CURDATE(), INTERVAL 13 DAY)
                GROUP BY DATE(created_at)")->fetchAll(PDO::FETCH_KEY_PAIR);
$days = [];
for ($i = 13; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i day"));
    $days[] = ['d' => $d, 'n' => (int)($byDay[$d] ?? 0)];
}
$maxDay = max(1, ...array_column($days, 'n'));

$recent = query('SELECT * FROM transactions ORDER BY id DESC LIMIT 6')->fetchAll();
$hour = (int)date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');

admin_header('Dashboard', 'dash', $greeting . ', ' . ($_SESSION['admin_name'] ?? 'Admin') . '. Here is how the machine is doing.');
?>
<?php if ($alerts): ?>
<section class="attention" role="alert">
  <div class="att-ic"><?= ai('alert') ?></div>
  <div class="att-body">
    <b><?= plural(count($alerts), 'item needs', 'items need') ?> your attention</b>
    <ul>
<?php foreach ($alerts as $a): ?>
      <li><?= e($a['item']['label']) ?> &mdash; <?= pill($a['status'], $a['tone']) ?></li>
<?php endforeach; ?>
    </ul>
  </div>
  <a class="btn btn-peach sm" href="stock.php">Manage stock</a>
</section>
<?php endif; ?>

<div class="tiles three">
  <?= stat_tile('green', ic_bottle(), $totals['valid'],       'Total valid bottles',      'All time') ?>
  <?= stat_tile('green', ai('cal'),   $totals['month_valid'], 'Valid bottles this month', e(date('F Y'))) ?>
  <?= stat_tile('rose',  ic_x(),      $totals['rejected'],    'Rejected items',           'All time') ?>
  <?= stat_tile('sun',   ic_check(),  $completed,             'Completed transactions',   'Finished at the machine') ?>
  <?= stat_tile('peach', ic_bowl(),   $feedings,              'Feeding incidents',        'Food dispensed &middot; ' . plural($pool, 'credit') . ' waiting') ?>
  <?= stat_tile('peach', ic_stick(),  $sticks,                'Dental sticks given',      'Reward dispensed') ?>
</div>

<div class="machine"><i></i> Machine status: <?= SCANNER_DEMO ? 'Simulation mode' : 'Scanner mode (hardware connected)' ?></div>

<div class="row2 wide-left">
  <section class="card">
    <h2><?= ai('cal') ?> Valid bottles &mdash; last 14 days</h2>
    <div class="chart" role="img" aria-label="Bar chart of valid bottles collected per day for the last 14 days">
<?php foreach ($days as $d): ?>
      <div class="col" title="<?= e(date('M j', strtotime($d['d']))) ?>: <?= $d['n'] ?> bottles">
        <span class="val"><?= $d['n'] ?: '' ?></span>
        <span class="bar-v" style="height:<?= $d['n'] ? max(4, (int)round($d['n'] / $maxDay * 100)) : 2 ?>%"></span>
        <span class="lab"><?= e(date('j', strtotime($d['d']))) ?></span>
      </div>
<?php endforeach; ?>
    </div>
  </section>

  <section class="card">
    <h2><?= ai('list') ?> Recent transactions</h2>
<?php foreach ($recent as $t): ?>
    <div class="recent">
      <b>#<?= e($t['id']) ?></b>
      <span><?= plural((int)$t['valid_bottles'], 'bottle') ?><?= $t['food_portions'] ? ' &middot; ' . plural((int)$t['food_portions'], 'portion') : '' ?></span>
      <?= tx_status_pill($t['status']) ?>
    </div>
<?php endforeach; if (!$recent): ?>
    <p class="empty-note">No transactions yet.</p>
<?php endif; ?>
    <div class="actions links"><a class="btn btn-ghost sm" href="collections.php">View all</a></div>
  </section>
</div>

<section class="card">
  <h2><?= ai('box') ?> Stock &amp; Capacity</h2>
  <?= inventory_cards($inv) ?>
  <div class="actions links">
    <a class="btn btn-primary sm" href="stock.php">Manage stock</a>
    <a class="btn btn-ghost sm" href="collections.php">View collections</a>
    <a class="btn btn-ghost sm" href="reports.php">Generate report</a>
  </div>
</section>
<?php admin_footer();
