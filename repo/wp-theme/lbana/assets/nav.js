(function () {
  var btn = document.querySelector('[data-lb-menu]');
  var drawer = document.getElementById('lb-drawer');
  if (!btn || !drawer) return;
  function set(open) {
    drawer.classList.toggle('is-open', open);
    document.body.classList.toggle('lb-menu-open', open);
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    btn.textContent = open ? 'Close' : 'Menu';
  }
  btn.addEventListener('click', function () { set(!drawer.classList.contains('is-open')); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') set(false); });
  window.addEventListener('resize', function () { if (window.innerWidth > 780) set(false); });
})();
