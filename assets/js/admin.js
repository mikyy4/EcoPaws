/* Admin panel: mobile menu (off-canvas sidebar). */
(function () {
  var side = document.getElementById('sidebar'), btn = document.getElementById('menuBtn'),
      close = document.getElementById('menuClose'), scrim = document.getElementById('scrim');
  if (!side || !btn) return;
  function set(open) {
    document.body.classList.toggle('menu-open', open);
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
  }
  btn.addEventListener('click', function () { set(true); });
  close.addEventListener('click', function () { set(false); });
  scrim.addEventListener('click', function () { set(false); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') set(false); });
})();
