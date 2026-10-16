<?php
require __DIR__ . '/_init.php';
$tx = require_open_tx();
$s = tx_numbers($tx);
$type = $_GET['food_type'] ?? '';
if ($s['avail'] < 1) redirect(reward_back($tx));
if (!food_type_valid($type)) redirect('choose.php');
$max = max_portions($type, $s);
if ($max < 1) redirect('choose.php');

$label = food_label($type);
$start = min(max(1, (int)($_GET['portions'] ?? 1)), $max);
// Values the portion picker script needs
$config = json_encode(['max' => $max, 'per' => $s['cpp'], 'g' => $s['g'], 'total' => $s['total'], 'start' => $start]);
app_header('How Many Portions', 3, $tx);
?>
<section class="card center">
  <div class="eyebrow"><?= ic_bowl() ?> <?= $label ?></div>
  <h1 class="title">How many portions?</h1>
  <p class="lead">1 portion = <?= $s['g'] ?> g and uses <?= $s['cpp'] ?> food <?= $s['cpp'] == 1 ? 'credit' : 'credits' ?>. You can dispense up to <b><?= $max ?></b>.</p>

  <form method="get" action="confirm.php" id="f" data-config="<?= e($config) ?>">
    <input type="hidden" name="food_type" value="<?= e($type) ?>">
    <input type="hidden" name="portions" id="pval" value="<?= $start ?>">
    <div class="stepper">
      <button type="button" class="step-btn" id="minus" aria-label="Fewer portions">&minus;</button>
      <div class="big"><b id="pn"><?= $start ?></b><small id="plabel">portion</small></div>
      <button type="button" class="step-btn" id="plus" aria-label="More portions">+</button>
    </div>
<?php if ($max > 1): ?>
    <div class="chips"><button type="button" class="qchip" id="maxbtn">Use all (<?= $max ?>)</button></div>
<?php endif; ?>

    <div class="mini3">
      <div class="hl"><b id="g">0 g</b>of <?= strtolower($label) ?></div>
      <div><b id="used">0</b>credits used</div>
      <div><b id="left">0</b>credits stay saved</div>
    </div>

    <div class="actions">
      <button class="btn btn-primary btn-lg" type="submit">Continue <?= ic_arrow() ?></button>
      <a class="btn btn-ghost" href="choose.php"><?= ic_back() ?> Back</a>
    </div>
  </form>
</section>
<?php app_footer('portions.js');
