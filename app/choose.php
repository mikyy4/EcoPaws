<?php
require __DIR__ . '/_init.php';
$tx = require_open_tx();
$s = tx_numbers($tx);
if ($s['avail'] < 1) redirect(reward_back($tx));

$max   = ['dog' => max_portions('dog', $s), 'cat' => max_portions('cat', $s)];
$first = $max['dog'] > 0 ? 'dog' : ($max['cat'] > 0 ? 'cat' : '');
app_header('Choose Food', 3, $tx);
?>
<section class="card">
  <div class="eyebrow"><?= ic_bowl() ?> Dispense food</div>
  <h1 class="title">Who are we feeding?</h1>
  <p class="lead">Select dog food or cat food. Next, you will choose how many portions.</p>

  <form method="get" action="portions.php">
    <div class="grid2">
<?php foreach ($max as $type => $m): $ok = $m > 0; ?>
      <label class="pick <?= $type ?> <?= $ok ? '' : 'off' ?>">
        <input type="radio" name="food_type" value="<?= $type ?>" <?= $first === $type ? 'checked' : '' ?> <?= $ok ? '' : 'disabled' ?>>
        <span class="pickcard"><span class="tick"><?= ic_check() ?></span>
          <?= ic_pet($type, 'pet') ?><b><?= food_label($type) ?></b><small><?= $ok ? 'Available' : 'Out of stock' ?></small></span>
      </label>
<?php endforeach; ?>
    </div>
<?php if ($first === ''): ?>
    <div class="gap-top"><?= notice('Food stock is empty. Please inform the barangay staff. Your credits are safe.') ?></div>
<?php endif; ?>
    <div class="actions">
      <button class="btn btn-primary btn-lg" type="submit" <?= $first === '' ? 'disabled' : '' ?>>Continue <?= ic_arrow() ?></button>
      <a class="btn btn-ghost" href="<?= e(reward_back($tx)) ?>"><?= ic_back() ?> Back</a>
    </div>
  </form>
</section>
<?php app_footer();
