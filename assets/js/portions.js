/* Portion picker (app/portions.php): + / - buttons and live totals. */
(function () {
  var form = document.getElementById('f');
  var cfg = JSON.parse(form.dataset.config);          // {max, per, g, total, start}
  var n = cfg.start;
  var $ = function (id) { return document.getElementById(id); };

  function render() {
    $('pn').textContent = n;
    $('pval').value = n;
    $('plabel').textContent = n === 1 ? 'portion' : 'portions';
    $('g').textContent = (n * cfg.g) + ' g';
    $('used').textContent = n * cfg.per;
    $('left').textContent = cfg.total - n * cfg.per;
    $('minus').disabled = n <= 1;
    $('plus').disabled = n >= cfg.max;
  }

  $('minus').onclick = function () { if (n > 1) { n--; render(); } };
  $('plus').onclick  = function () { if (n < cfg.max) { n++; render(); } };
  if ($('maxbtn')) $('maxbtn').onclick = function () { n = cfg.max; render(); };
  render();
})();
