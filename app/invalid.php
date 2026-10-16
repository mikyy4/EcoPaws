<?php
// Fallback screen for the non-JavaScript deposit flow (see api/insert_bottle.php).
require __DIR__ . '/_init.php';
$tx = require_open_tx();
$entry = query('SELECT * FROM bottle_entries WHERE id=? AND transaction_id=?', [(int)($_GET['id'] ?? 0), $tx['id']])->fetch();
app_header('Item Not Accepted', 1, $tx);
?>
<section class="card rejectbox">
  <div class="xmark"><?= ic_x() ?></div>
  <h1 class="title">Item not accepted</h1>
<?php if ($entry): ?>
  <p class="lead tight"><strong><?= e($entry['item_name']) ?> (<?= e($entry['size']) ?>)</strong></p>
  <div class="reason"><?= e($entry['reject_reason']) ?></div>
<?php else: ?>
  <div class="reason">This item cannot be accepted.</div>
<?php endif; ?>
  <p class="lead">Please take your item back from the slot.</p>
  <div class="actions">
    <a class="btn btn-primary btn-lg" href="deposit.php">Try another item</a>
    <a class="btn btn-ghost" href="summary.php">Done <?= ic_arrow() ?></a>
  </div>
</section>
<?php app_footer();
