<?php
require __DIR__ . '/_init.php';
if (empty($_SESSION['tx'])) redirect('index.php');

$tx = query("SELECT * FROM transactions WHERE id=? AND status='completed'", [$_SESSION['tx']])->fetch();
if (!$tx) redirect('summary.php');

$s    = tx_numbers($tx);              // 'total' = credits still stored after this transaction
$fed  = $tx['food_type'] !== null;    // YES path?
$due  = $s['due'];
$kept = $s['total'];                  // credits still stored in the machine (shared pool)
$petName = $tx['food_type'] === 'cat' ? 'Cat' : 'Dog';
app_header('Success', 4, $tx, ['head' => '<meta http-equiv="refresh" content="12;url=thankyou.php">']);
?>
<section class="card center">
  <div class="stage">
    <div class="processing">
<?php if ($fed): ?>
      <div class="kibble"><i></i><i></i><i></i><i></i><?= ic_bowl('bowl') ?></div>
      <h1 class="title">Dispensing<span class="dots"><span>.</span><span>.</span><span>.</span></span></h1>
      <p class="lead">Your <?= e($tx['food_type']) ?> food is on its way. Please wait.</p>
<?php else: ?>
      <div class="okmark sun float"><?= ic_stick() ?></div>
      <h1 class="title">Saving your credits<span class="dots"><span>.</span><span>.</span><span>.</span></span></h1>
      <p class="lead">Dispensing your dental stick reward.</p>
<?php endif; ?>
      <div class="progress"><span></span></div>
    </div>

    <div class="done">
      <div class="okmark <?= $fed ? '' : 'sun' ?>"><?= ic_check() ?></div>
      <h1 class="title"><?= $fed ? 'Successfully dispensed!' : 'All done &mdash; credits saved!' ?></h1>
      <p class="lead"><?= $fed ? 'A hungry animal in Barangay Dakila thanks you.' : 'Your transaction is finished. Your food credits are kept for later.' ?></p>

      <div class="results">
<?php if ($fed): ?>
        <div class="res t-peach">
          <?= ic_pet($tx['food_type']) ?>
          <div>
<?php if ($tx['food_portions'] > 0): ?>
            <b><?= plural((int)$tx['food_portions'], 'portion') ?> &middot; <?= e($tx['food_portions'] * $s['g']) ?> g</b>
            <span><?= $petName ?> food dispensed</span>
<?php else: ?>
            <b>Out of stock</b><span><?= $petName ?> food</span>
<?php endif; ?>
          </div>
        </div>
<?php else: ?>
        <div class="res t-sun">
          <?= ic_bowl() ?>
          <div><b><?= plural($kept, 'food credit') ?> saved</b><span>Kept in the machine</span>
          <small>= <?= plural($s['avail'], 'portion') ?> ready to claim later</small></div>
        </div>
<?php endif; ?>
        <div class="res t-green">
          <?= ic_stick() ?>
          <div>
<?php if ($tx['dental_sticks'] > 0): ?>
            <b><?= plural((int)$tx['dental_sticks'], 'dental stick') ?></b><span>Reward dispensed</span>
<?php elseif ($due > 0): ?>
            <b>Out of stock</b><span>Dental sticks</span>
<?php else: ?>
            <b>0 dental sticks</b><span>Needs <?= $s['per'] ?> valid bottles</span>
<?php endif; ?>
          </div>
        </div>
      </div>

<?php if ($fed && $tx['food_portions'] < 1): ?>
      <div class="alertbox">Food stock is empty. Your credits are safe. Please inform the barangay staff.</div>
<?php endif; ?>
<?php if ($due > 0 && $tx['dental_sticks'] < $due): ?>
      <div class="alertbox">Dental stick stock ran out. Please inform the barangay staff.</div>
<?php endif; ?>
<?php if ($fed && $kept > 0): ?>
      <div class="saved"><?= ic_bowl() ?> <?= plural($kept, 'food credit') ?> still saved in the machine for the next recycler.</div>
<?php endif; ?>

      <div class="actions"><a class="btn btn-primary btn-lg" href="thankyou.php">Finish <?= ic_arrow() ?></a></div>
    </div>
  </div>
</section>
<?php app_footer();
