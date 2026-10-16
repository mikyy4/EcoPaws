<?php
require __DIR__ . '/_init.php';
$tx = require_open_tx();
$s = tx_numbers($tx);
$type = $_GET['food_type'] ?? '';
if ($s['avail'] < 1) redirect(reward_back($tx));
if (!food_type_valid($type)) redirect('choose.php');
$max = max_portions($type, $s);
$p = (int)($_GET['portions'] ?? 0);
if ($max < 1 || $p < 1) redirect('choose.php');

$p    = min($p, $max);
$used = $p * $s['cpp'];
app_header('Confirm Dispensing', 3, $tx);
?>
<section class="card">
  <div class="confirm-pet">
    <?= ic_pet($type, 'pet') ?>
    <div>
      <div class="eyebrow"><?= ic_check() ?> Confirm dispensing</div>
      <h1 class="title">Ready to dispense?</h1>
      <p class="lead tight">Please place your container or bowl under the chute.</p>
    </div>
  </div>

  <div class="receipt">
    <div class="row"><span class="k"><?= ic_pet($type) ?> Food type</span><span class="val"><?= food_label($type) ?></span></div>
    <div class="row"><span class="k"><?= ic_bowl() ?> Amount</span><span class="val"><?= plural($p, 'portion') ?> (<?= $p * $s['g'] ?> g)</span></div>
    <div class="row"><span class="k"><?= ic_leaf() ?> Credits used</span><span class="val"><?= $used ?></span></div>
    <div class="row"><span class="k"><?= ic_bowl() ?> Credits kept for later</span><span class="val"><?= $s['total'] - $used ?></span></div>
<?php if ($s['due'] > 0): ?>
    <div class="row"><span class="k"><?= ic_stick() ?> Dental sticks</span><span class="val"><?= $s['due'] ?></span></div>
<?php endif; ?>
  </div>

  <form method="post" action="dispense.php">
    <input type="hidden" name="food_type" value="<?= e($type) ?>">
    <input type="hidden" name="portions" value="<?= $p ?>">
    <div class="actions">
      <button class="btn btn-primary btn-lg" type="submit"><?= ic_check() ?> Confirm dispensing</button>
      <a class="btn btn-ghost" href="portions.php?food_type=<?= e($type) ?>&amp;portions=<?= $p ?>"><?= ic_back() ?> Change</a>
    </div>
  </form>
</section>
<?php app_footer();
