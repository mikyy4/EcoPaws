<?php
require __DIR__ . '/_init.php';
$tx = require_open_tx();
$s = tx_numbers($tx);
$canFeed = $s['avail'] >= 1;      // enough saved credits for at least 1 portion
app_header('Transaction Summary', 2, $tx);
?>
<section class="card">
  <div class="eyebrow"><?= ic_leaf() ?> Transaction summary</div>
  <h1 class="title">Great job, recycler!</h1>
  <p class="lead">Here is what your bottles earned today.</p>

  <div class="tiles">
    <?= stat_tile('green', ic_bottle(), $s['valid'],    'Valid bottles',        'Accepted into the bin') ?>
    <?= stat_tile('rose',  ic_x(),      $s['rejected'], 'Rejected bottles',     'Not accepted') ?>
    <?= stat_tile('sun',   ic_bowl(),   $s['credits'],  'Food credits earned',  '1 credit per bottle &middot; ' . '1 credit = 1 portion (' . $s['g'] . ' g)') ?>
    <?= stat_tile('peach', ic_stick(),  $s['due'],      'Dental sticks earned', '1 per ' . $s['per'] . ' valid bottles') ?>
  </div>

<?php if ($s['pool'] > 0): ?>
  <div class="poolbar"><?= ic_bowl() ?>
    <span>Plus <b><?= $s['pool'] ?></b> saved <?= $s['pool'] == 1 ? 'credit' : 'credits' ?> from earlier recyclers in the machine &mdash;
    <b><?= $s['total'] ?></b> available in total (<?= plural($s['avail'], 'portion') ?>).</span>
  </div>
<?php endif; ?>
</section>

<section class="card">
  <div class="ask">
    <div class="pets"><?= ic_dog() ?><?= ic_cat() ?></div>
    <div>
<?php if (!$canFeed): ?>
      <h2>Not enough credits yet</h2>
      <p class="lead tight">
        Insert a bottle to earn food credits.
      </p>
<?php else: ?>
      <h2>Do you want to dispense food now?</h2>
      <p class="lead tight">You can feed up to <b><?= plural($s['avail'], 'portion') ?></b> (<?= $s['avail'] * $s['g'] ?> g) &mdash; or save your credits for later.</p>
<?php endif; ?>
    </div>
  </div>

<?php if ($canFeed): ?>
  <div class="choices">
    <a class="choice yes" href="choose.php">
      <b><?= ic_check() ?> YES</b>
      <span>Dispense food now &mdash; pick dog or cat food</span>
    </a>
    <form method="post" action="keep.php" class="contents">
      <button class="choice no" type="submit">
        <b>NOT NOW</b>
        <span>Keep earned food credits &middot; finish transaction</span>
      </button>
    </form>
  </div>
  <div class="actions">
    <a class="btn btn-quiet" href="deposit.php"><?= ic_back() ?> Add more bottles</a>
    <a class="btn btn-quiet" href="cancel.php">Cancel</a>
  </div>
<?php else: ?>
  <div class="actions">
    <a class="btn btn-primary btn-lg" href="deposit.php">Add more bottles <?= ic_arrow() ?></a>
<?php if ($s['valid'] > 0): ?>
    <form method="post" action="keep.php" class="inline"><button class="btn btn-ghost" type="submit">Finish &amp; keep credits</button></form>
<?php endif; ?>
    <a class="btn btn-quiet" href="cancel.php">Cancel</a>
  </div>
<?php endif; ?>
</section>
<?php app_footer();
