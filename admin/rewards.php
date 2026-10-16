<?php
require __DIR__ . '/../includes/admin.php';

$rows  = query('SELECT r.*, t.valid_bottles FROM reward_log r JOIN transactions t ON t.id = r.transaction_id ORDER BY r.id DESC LIMIT 50')->fetchAll();
$total = (int)scalar('SELECT COALESCE(SUM(dental_sticks),0) FROM reward_log');
$stock = (int)scalar("SELECT quantity FROM inventory WHERE item_key='dental_stick'");
$per   = reward_rules()['per'];

admin_header('Rewards', 'gift', 'Dental sticks dispensed to recyclers.');
?>
<div class="tiles three">
  <?= stat_tile('peach', ic_stick(),  $total, 'Total dental sticks given', 'All time') ?>
  <?= stat_tile('green', ai('box'),   $stock, 'Reward stock left',        'pcs') ?>
  <?= stat_tile('sun',   ic_bottle(), $per,   'Bottles per reward',       '1 dental stick') ?>
</div>

<section class="card">
  <h2><?= ai('gift') ?> Reward Records <?= pill('latest 50', 'mute') ?></h2>
  <?= data_table([
        ['ID',                  'id'],
        ['Date & Time',         'created_at'],
        ['Transaction',         fn($r) => '#' . e($r['transaction_id'])],
        ['Valid Bottles',       'valid_bottles', 'num'],
        ['Dental Sticks Given', 'dental_sticks', 'num'],
      ], $rows, 'No rewards given yet.') ?>
  <div class="gap-top"><?= notice('Reward rule: ' . $per . ' valid bottles = 1 dental stick. Change it in Stock &amp; Capacity.') ?></div>
</section>
<?php admin_footer();
