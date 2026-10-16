<?php
require __DIR__ . '/_init.php';
$tx = require_open_tx();
$bottles = load_bottles();
$flash = flash_get();
$per   = reward_rules()['per'];
$valid = (int)$tx['valid_bottles'];
[$toward, $pctBar] = reward_progress($valid, $per);

$dots = '<span class="dots"><span>.</span><span>.</span><span>.</span></span>';
$data = json_encode(['bottles' => $bottles, 'demo' => SCANNER_DEMO],
                    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
app_header('Deposit Bottles', 1, $tx);
?>
<div class="deposit">
  <section class="card">
    <div class="eyebrow"><?= ic_scan() ?> Smart scanner</div>
    <h1 class="title">Place your bottle in the scanner</h1>
    <p class="lead">The machine will detect the type and size of your bottle automatically.</p>
<?php if ($flash): ?>
    <?= notice(e($flash['message'])) ?>
<?php endif; ?>

    <div class="scanner" id="scanner" data-state="idle">
      <div class="scan-box">
        <?= ic_bottle('scan-bottle') ?>
        <div class="beam"></div>
        <i class="corner tl"></i><i class="corner tr"></i><i class="corner bl"></i><i class="corner br"></i>
        <div class="scan-badge"></div>
      </div>
      <div class="scan-status" id="scanStatus" role="status" aria-live="polite">Waiting for a bottle<?= $dots ?></div>
    </div>

    <div id="specs" class="specs" hidden>
      <div class="detected" id="detName"></div>
      <div class="spec-grid">
        <div><small>Type</small><b id="detCat"></b></div>
        <div><small>Size</small><b id="detSize"></b></div>
        <div><small>Class</small><b id="detCls"></b></div>
        <div><small>Color</small><b id="detColor"></b></div>
        <div><small>Length</small><b id="detLen"></b></div>
        <div><small>Result</small><b id="detRes"></b></div>
      </div>
      <div class="detnote" id="detNote"></div>
    </div>

<?php if (SCANNER_DEMO): ?>
    <details class="demo">
      <summary>Demo controls <small></small></summary>
      <div class="demo-body">
        <select id="demoSel">
          <option value="random">Random bottle</option>
<?php $cur = null;
foreach ($bottles as $i => $b):
    if ($b['cat'] !== $cur): if ($cur !== null): ?>
          </optgroup>
<?php endif; $cur = $b['cat']; ?>
          <optgroup label="<?= e($cur) ?>">
<?php endif; ?>
            <option value="<?= $i ?>"><?= e($b['name'] . ' - ' . $b['size']) ?><?= $b['reason'] ? ' (rejected)' : '' ?></option>
<?php endforeach; if ($cur !== null): ?>
          </optgroup>
<?php endif; ?>
        </select>
        <button type="button" class="btn btn-ghost" id="demoBtn"><?= ic_scan() ?> Simulate scan</button>
      </div>
      <p class="demo-note"> <code></code><code></code>  <code></code>  <code></code></p>
    </details>
<?php endif; ?>
  </section>

  <aside class="card counter">
    <?= ic_bottle('jar') ?>
    <div class="num" id="cntNum"><?= $valid ?></div>
    <div class="lab"><span id="cntLab"><?= $valid === 1 ? 'valid bottle' : 'valid bottles' ?></span> counted</div>
    <div class="mini">
      <div><b id="cntAcc"><?= $valid ?></b>Accepted</div>
      <div class="rej"><b id="cntRej"><?= e($tx['invalid_attempts']) ?></b>Rejected</div>
    </div>
    <div class="bar"><span id="cntBar" style="width:<?= $pctBar ?>%"></span></div>
    <div class="barlab"><span id="cntToward"><?= $toward ?></span> / <?= $per ?> toward your next reward</div>
    <div class="actions stack-actions">
      <a class="btn btn-peach" href="summary.php">Done <?= ic_arrow() ?></a>
      <a class="btn btn-quiet" href="cancel.php">Cancel</a>
    </div>
  </aside>
</div>

<div class="modal-back" id="modal" role="alertdialog" aria-modal="true" aria-labelledby="mTitle">
  <div class="modal">
    <div class="xmark"><?= ic_x() ?></div>
    <h2 id="mTitle">Item not accepted</h2>
    <p class="lead tight strong" id="mItem"></p>
    <div class="reason" id="mReason"></div>
    <p class="lead" id="mHint">Please take your item back from the scanner.</p>
    <button type="button" class="btn btn-primary btn-lg" id="mOk">OK</button>
  </div>
</div>

<script type="application/json" id="depositData"><?= $data ?></script>
<?php app_footer('deposit.js');
