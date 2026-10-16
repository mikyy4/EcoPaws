<?php
require __DIR__ . '/../includes/admin.php';

$rows = query('SELECT * FROM feeding_log ORDER BY id DESC LIMIT 50')->fetchAll();
$sum  = query('SELECT food_type, COUNT(*) AS incidents, SUM(portions) AS portions, SUM(grams) AS grams FROM feeding_log GROUP BY food_type')->fetchAll();
$credits   = query('SELECT status, COALESCE(SUM(credits),0) AS c FROM food_credits GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);
$unclaimed = query("SELECT transaction_id, SUM(credits) AS c FROM food_credits WHERE status='stored'
                    GROUP BY transaction_id ORDER BY transaction_id DESC LIMIT 20")->fetchAll();

$stored  = (int)($credits['stored'] ?? 0);
$claimed = (int)($credits['claimed'] ?? 0);

admin_header('Feeding', 'bowl', 'Food dispensed for stray and community animals, and stored food credits.');
?>
<div class="tiles three">
  <?= stat_tile('sun',   ic_bowl(),  $stored,  'Saved credit pool',   'Shared &middot; = ' . plural(intdiv($stored, CREDITS_PER_PORTION), 'portion') . ' waiting') ?>
  <?= stat_tile('green', ic_check(), $claimed, 'Claimed credits',     'Turned into food') ?>
  <?= stat_tile('peach', ic_paw(),   array_sum(array_column($sum, 'incidents')), 'Feeding incidents', e(array_sum(array_column($sum, 'grams'))) . ' g dispensed') ?>
</div>

<div class="row2">
  <section class="card">
    <h2><?= ic_paw() ?> Totals</h2>
    <?= data_table([
          ['Food',      fn($s) => food_pill($s['food_type'])],
          ['Incidents', 'incidents', 'num'],
          ['Portions',  'portions', 'num'],
          ['Amount',    fn($s) => e($s['grams']) . ' g', 'num'],
        ], $sum, 'No data yet.') ?>
  </section>

  <section class="card">
    <h2><?= ic_bowl() ?> Saved Credits by Transaction</h2>
    <p class="sub">Where the pool came from (users who chose NOT NOW or used fewer portions). Any later user can spend it, oldest first.</p>
    <?= data_table([
          ['Transaction',    fn($u) => '#' . e($u['transaction_id'])],
          ['Stored Credits', 'c', 'num'],
        ], $unclaimed, 'No unclaimed credits.') ?>
  </section>
</div>

<section class="card">
  <h2><?= ai('list') ?> Feeding Records <?= pill('latest 50', 'mute') ?></h2>
  <?= data_table([
        ['ID',          'id'],
        ['Date & Time', 'created_at'],
        ['Transaction', fn($r) => '#' . e($r['transaction_id'])],
        ['Food',        fn($r) => food_pill($r['food_type'])],
        ['Portions',    'portions', 'num'],
        ['Amount',      fn($r) => e($r['grams']) . ' g', 'num'],
      ], $rows, 'No feeding incidents yet.') ?>
</section>
<?php admin_footer();
