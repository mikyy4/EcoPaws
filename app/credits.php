<?php
// Saved food credits
require __DIR__ . '/_init.php';

['per' => $per, 'g' => $g] = reward_rules();
$total = pool_total();               // all stored (not yet dispensed) credits in the machine
$ready = intdiv($total, CREDITS_PER_PORTION);       // full portions those credits can pay for

$recent = query("SELECT transaction_id, SUM(credits) AS c, MAX(created_at) AS at
                 FROM food_credits WHERE status='stored'
                 GROUP BY transaction_id ORDER BY MAX(created_at) DESC LIMIT 6")->fetchAll();

app_header('Saved Credits', 0, null, ['right' => corner_link('index.php', ic_back(), 'Home')]);
?>
<section class="card">
  <div class="eyebrow"><?= ic_bowl() ?> Saved food credits</div>
  <h1 class="title">Food waiting to be dispensed</h1>
  <p class="lead">Credits from earlier recyclers that were not used yet. They stay in the machine until someone dispenses them.</p>

  <div class="tiles three">
    <?= stat_tile('sun',   ic_bowl(),  $total, 'Saved credits', '1 credit = 1 valid bottle') ?>
    <?= stat_tile('green', ic_paw(),   $ready, ($ready === 1 ? 'Portion' : 'Portions') . ' ready', '1 credit = 1 portion (' . $g . ' g)') ?>
    <?= stat_tile('peach', ic_stick(), $ready * $g, 'Pet food ready', 'Dog or cat food, your choice', 'g') ?>
  </div>

<?php if ($recent): ?>
  <h2 class="subhead">Recently saved</h2>
  <ul class="saves">
<?php foreach ($recent as $r): ?>
    <li><?= ic_bottle() ?><span>Recycler #<?= e($r['transaction_id']) ?></span><b>+<?= e(plural((int)$r['c'], 'credit')) ?></b><small><?= e(date('M j, g:i A', strtotime($r['at']))) ?></small></li>
<?php endforeach; ?>
  </ul>
<?php else: ?>
  <div class="gap-top"><?= notice('No saved credits right now.') ?></div>
<?php endif; ?>

  <div class="gap-top"><?= $ready >= 1
        ? notice('You can dispense ' . plural($ready, 'portion') . ' now &mdash; no bottle needed.')
        : notice('Not enough credits for a portion yet. Deposit a bottle to earn more.') ?></div>

  <div class="actions">
    <a class="btn btn-primary btn-lg" href="start.php">Deposit a bottle <?= ic_arrow() ?></a>
<?php if ($ready >= 1): ?>
    <a class="btn btn-peach btn-lg" href="dispense_credits.php"><?= ic_bowl() ?> Dispense food now</a>
<?php else: ?>
    <button class="btn btn-lg" type="button" disabled><?= ic_bowl() ?> Dispense food now</button>
<?php endif; ?>
  </div>
</section>
<?php app_footer();
