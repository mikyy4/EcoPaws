/* Deposit screen (app/deposit.php): reacts to scanner readings, saves each bottle, updates the counter. */
(function () {
  var data = JSON.parse(document.getElementById('depositData').textContent);
  var BOTTLES = data.bottles;

  var SCAN_MS = 1400;          // how long the scan animation plays
  var POLL_MS = 500;           // how often we ask the server for a new scanner reading
  var DOTS = '<span class="dots"><span>.</span><span>.</span><span>.</span></span>';

  var $ = function (id) { return document.getElementById(id); };
  var scanner = $('scanner'), statusEl = $('scanStatus'), specs = $('specs'), modal = $('modal');
  var busy = false;

  function setState(state, html) {
    scanner.setAttribute('data-state', state);
    statusEl.innerHTML = html;
  }
  function waiting() { setState('idle', 'Waiting for a bottle' + DOTS); }

  /** Fill the "detected" card with what the scanner found. */
  function showSpecs(b) {
    var ok = b.reason === null, unknown = b.cls === 'Unknown';
    $('detName').textContent = unknown ? 'Unrecognized item' : b.name;
    $('detCat').textContent = unknown ? 'Unknown' : b.cat;
    $('detSize').textContent = b.size;
    $('detCls').textContent = b.cls === 'Bottle Rejected' ? 'Colored bottle' : b.cls;
    $('detColor').textContent = b.color;
    $('detLen').textContent = typeof b.len === 'number' ? b.len + ' cm' : '-';
    $('detRes').textContent = ok ? 'VALID' : 'REJECTED';
    $('detNote').textContent = ok ? '' : b.reason;
    $('detNote').style.display = ok ? 'none' : 'block';
    specs.className = 'specs ' + (ok ? 'ok' : 'bad');
    specs.hidden = false;
  }

  function updateCounts(c) {
    $('cntNum').textContent = c.valid;
    $('cntAcc').textContent = c.valid;
    $('cntRej').textContent = c.rejected;
    $('cntLab').textContent = 'valid bottle' + (c.valid === 1 ? '' : 's');
    $('cntBar').style.width = c.pct + '%';
    $('cntToward').textContent = c.toward;
  }

  function openModal(title, item, reason, hint) {
    $('mTitle').textContent = title;
    $('mItem').textContent = item || '';
    $('mReason').textContent = reason || '';
    $('mReason').style.display = reason ? 'inline-block' : 'none';
    $('mHint').textContent = hint;
    modal.className = 'modal-back on';
    $('mOk').focus();
  }
  $('mOk').onclick = function () {
    modal.className = 'modal-back';
    waiting();
    busy = false;                       // ready for the next bottle
  };

  /** A reading arrived: play the scan animation while saving the bottle. */
  function handle(idx) {
    busy = true;
    var b = BOTTLES[idx];
    specs.hidden = true;
    setState('scanning', 'Scanning your bottle' + DOTS);

    var wait = new Promise(function (resolve) { setTimeout(resolve, SCAN_MS); });
    var save = fetch('../api/insert_bottle.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'fetch' },
      body: 'idx=' + encodeURIComponent(idx)
    }).then(function (r) { return r.json(); });

    Promise.all([save, wait]).then(function (res) {
      var d = res[0];
      if (!d.ok) {
        setState('bad', 'Could not add the item');
        openModal('Cannot accept item', '', d.error, 'Please try again or inform the barangay staff.');
        return;
      }
      updateCounts(d.counts);
      showSpecs(b);
      if (d.valid) {
        setState('ok', 'Bottle added &ndash; place the next bottle');
        busy = false;                   // keep scanning, the page stays where it is
      } else {
        setState('bad', 'Item not accepted');
        openModal('Item not accepted', b.cls === 'Unknown' ? 'Unrecognized item' : b.name + ' (' + b.size + ')',
                  b.reason, 'Please take your item back from the scanner.');
      }
    }).catch(function () {
      setState('bad', 'Connection problem');
      openModal('Connection problem', '', 'Could not reach the machine.', 'Please try again.');
    });
  }

  function poll() {
    if (busy) return;
    fetch('../api/scan.php?action=poll', { cache: 'no-store' })
      .then(function (r) { return r.json(); })
      .then(function (d) { if (d.found && !busy) handle(d.idx); })
      .catch(function () {});
  }
  setInterval(poll, POLL_MS);

  // Demo controls: pretend the sensor sent a reading.
  var demoBtn = $('demoBtn');
  if (demoBtn) demoBtn.onclick = function () {
    if (busy) return;
    fetch('../api/scan.php?action=simulate', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'idx=' + encodeURIComponent($('demoSel').value)
    }).then(poll).catch(function () {});
  };
})();
