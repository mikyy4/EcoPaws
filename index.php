<?php
/**
 * EcoPaws landing page.
 * Public entry point: explains the system and sends visitors to the recycling app (app/) or the admin panel.
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/layout.php';

// ---------- Content that comes from the system itself (falls back gracefully if the database is not set up yet) ----------
$rules = ['per' => 5, 'g' => 25];
$impact = null;
try {
    $rules = reward_rules();
    $impact = [
        'bottles' => (int)scalar('SELECT COUNT(*) FROM bottle_entries WHERE is_valid=1'),
        'grams'   => (int)scalar('SELECT COALESCE(SUM(grams),0) FROM feeding_log'),
        'sticks'  => (int)scalar('SELECT COALESCE(SUM(dental_sticks),0) FROM reward_log'),
        'credits' => pool_total(),
    ];
} catch (Throwable $ex) { /* show the page without live numbers */ }
['per' => $per, 'g' => $g] = $rules;

// Accepted bottle brands, grouped by category (from data/bottles.json)
$accepted = [];
foreach (load_bottles() as $b) {
    if ($b['reason'] === null) $accepted[$b['cat']][$b['name']] = true;
}

$kg = fn(int $grams) => $grams >= 1000 ? rtrim(rtrim(number_format($grams / 1000, 1), '0'), '.') . ' kg' : $grams . ' g';

$faqs = [
    ['Which bottles does the machine accept?', 'Clear plastic bottles from the brands listed above, in small, medium and large sizes. Green or colored bottles, items that are not bottles, and bottles the scanner cannot recognize are politely returned with the reason shown on screen.'],
    ['Do I need an account or an app?', 'No. Walk up, tap Start and drop your bottle in the scanner. There is nothing to download and no sign-up.'],
    ['What if I do not need to feed an animal right now?', 'Press NOT NOW. Your food credits are saved in the machine and form one shared pool, so you or anyone else can use them on a later visit.'],
    ['How are rewards calculated?', "Every valid bottle earns 1 food credit. Each credit unlocks 1 portion ($g g) of dog or cat food, so 1 bottle = 1 portion. Every $per valid bottles you deposit in one visit also earns 1 dental stick."],
    ['Can staff monitor the machine?', 'Yes. The admin dashboard shows bottles collected, rewards given, food dispensed and live stock levels, warns when food runs low or the bottle bin is full, and prints reports for any date range.'],
];
$pageTitle = 'EcoPaws - Recycle a bottle. Feed a friend.';
html_head($pageTitle, ['base', 'landing'], '', '', 'EcoPaws is a smart recycling machine for Barangay Dakila: drop in a plastic bottle, earn food credits, and feed stray and community animals.');
?>
<a class="skip" href="#main">Skip to content</a>

<header class="nav" id="top">
  <div class="container nav-in">
    <?= brand_link('#top') ?>
    <nav class="nav-links" id="navLinks" aria-label="Main">
      <a href="#how">How it works</a>
      <a href="#rewards">Rewards</a>
      <a href="#features">Features</a>
      <a href="#faq">FAQ</a>
      <a class="nav-admin" href="admin/login.php">Admin</a>
    </nav>
    <a class="btn btn-primary nav-cta" href="app/index.php">Start Recycling</a>
    <button class="nav-toggle" id="navToggle" type="button" aria-label="Open menu" aria-expanded="false" aria-controls="navLinks"><?= ai('menu') ?></button>
  </div>
</header>

<main id="main">
<!-- ================= HERO ================= -->
<section class="hero container">
  <div class="hero-copy">
    <span class="pill-top"><?= ic_leaf() ?> Barangay Dakila &middot; Smart recycling prototype</span>
    <h1>Turn every plastic bottle into a <em>meal for a stray animal.</em></h1>
    <p class="lead">Drop a bottle into the EcoPaws machine, earn food credits in seconds, and feed a hungry dog or cat on the spot. Cleaner streets, fed animals, zero cost to you.</p>
    <div class="cta-row">
      <a class="btn btn-primary btn-lg" href="app/index.php">Start Recycling <?= ic_arrow() ?></a>
      <a class="btn btn-ghost btn-lg" href="#how">See how it works</a>
    </div>
    <ul class="ticks">
      <li><?= ic_check() ?> No sign-up</li>
      <li><?= ic_check() ?> Takes under a minute</li>
      <li><?= ic_check() ?> 1 bottle = 1 portion &middot; <?= $per ?> bottles = 1 dental stick</li>
    </ul>
  </div>
  <div class="hero-art" aria-hidden="true">
    <div class="blob"></div>
    <?= ic_bottle('bot float') ?>
    <?= ic_bottle('bot2 float') ?>
    <?= ic_dog('pet dog float') ?>
    <?= ic_cat('pet cat float late') ?>
    <div class="chip c1"><?= ic_bowl() ?> +1 food credit</div>
    <div class="chip c2"><?= ic_stick() ?> Dental stick reward</div>
  </div>
</section>

<?php if ($impact !== null): ?>
<!-- ================= LIVE IMPACT ================= -->
<section class="impact container" aria-label="Impact so far">
  <div class="impact-grid">
    <div><b><?= number_format($impact['bottles']) ?></b><span>bottles recycled</span></div>
    <div><b><?= e($kg($impact['grams'])) ?></b><span>pet food dispensed</span></div>
    <div><b><?= number_format($impact['sticks']) ?></b><span>dental sticks given</span></div>
    <div><b><?= number_format($impact['credits']) ?></b><span>credits waiting to feed</span></div>
  </div>
  <p class="impact-note"><i></i> Live numbers from the machine</p>
</section>
<?php endif; ?>

<!-- ================= PROBLEM / SOLUTION ================= -->
<section class="section container">
  <div class="section-head">
    <span class="eyebrow">Why EcoPaws</span>
    <h2>Two community problems. <em>One simple machine.</em></h2>
    <p>Plastic bottles pile up in our streets, and stray and community animals go hungry. EcoPaws connects the two, so doing the right thing is also the easy thing.</p>
  </div>
  <div class="trio">
    <article class="mini-card"><span class="ico rose"><?= ic_bottle() ?></span><h3>Plastic waste</h3><p>Used bottles end up in drains and roadsides. A reward gives people a reason to bring them in.</p></article>
    <article class="mini-card"><span class="ico sun"><?= ic_paw() ?></span><h3>Hungry strays</h3><p>Community animals depend on kind neighbors. EcoPaws turns each bottle into real pet food.</p></article>
    <article class="mini-card"><span class="ico green"><?= ic_leaf() ?></span><h3>A cleaner barangay</h3><p>Every bottle is collected in one bin, counted automatically and tracked for the barangay.</p></article>
  </div>
</section>

<!-- ================= HOW IT WORKS ================= -->
<section class="section tint" id="how">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">How it works</span>
      <h2>From bottle to bowl in <em>three easy steps</em></h2>
    </div>
    <ol class="steps3">
      <li><span class="num">1</span><?= ic_bottle('big-ic blue') ?><h3>Deposit your bottle</h3><p>Place a plastic bottle in the smart scanner. It checks the type and size and tells you right away if it is accepted.</p></li>
      <li><span class="num">2</span><?= ic_bowl('big-ic sun') ?><h3>Earn food credits</h3><p>Each valid bottle adds 1 food credit. Rejected items are returned with a clear reason.</p></li>
      <li><span class="num">3</span><?= ic_paw('big-ic peach') ?><h3>Feed an animal</h3><p>Choose dog or cat food and the number of portions, then collect it from the chute with your dental stick.</p></li>
    </ol>
    <div class="center"><a class="btn btn-primary btn-lg" href="app/index.php">Start recycling <?= ic_arrow() ?></a></div>
  </div>
</section>

<!-- ================= REWARDS ================= -->
<section class="section container" id="rewards">
  <div class="rewards">
    <div>
      <span class="eyebrow">Rewards</span>
      <h2>Every <?= $per ?> bottles gives back <em>twice</em></h2>
      <p class="lead">Recycling should feel rewarding. Here is exactly what you get:</p>
      <ul class="reward-list">
        <li><span class="ico sun"><?= ic_bowl() ?></span><div><b>1 food credit per bottle</b><span>Each credit unlocks 1 portion (<?= $g ?> g) of dog or cat food.</span></div></li>
        <li><span class="ico peach"><?= ic_stick() ?></span><div><b>1 dental stick per <?= $per ?> bottles</b><span>A treat for your own pet or the animals you feed.</span></div></li>
        <li><span class="ico green"><?= ic_leaf() ?></span><div><b>Credits never expire</b><span>Not ready to feed? Press NOT NOW. Your credits stay in the machine for anyone to use later.</span></div></li>
      </ul>
    </div>
    <aside class="rule-card" aria-label="Reward rule">
      <div class="rule-big"><b><?= $per ?></b><span>valid bottles</span></div>
      <div class="rule-eq">=</div>
      <div class="rule-gets">
        <div><?= ic_bowl() ?><b><?= $per ?> portions</b><small><?= $g ?> g of pet food each</small></div>
        <div><?= ic_stick() ?><b>1 dental stick</b><small>personal reward</small></div>
      </div>
      <a class="btn btn-peach" href="app/credits.php">Check saved credits <?= ic_arrow() ?></a>
    </aside>
  </div>
</section>

<!-- ================= FEATURES ================= -->
<section class="section tint" id="features">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Features</span>
      <h2>Simple for neighbors. <em>Powerful for staff.</em></h2>
    </div>
    <div class="features">
      <article class="feature"><span class="ico green"><?= ic_scan() ?></span><h3>Smart bottle scanner</h3><p>Detects length and color to recognize each bottle, with instant accept or reject feedback.</p></article>
      <article class="feature"><span class="ico sun"><?= ic_bowl() ?></span><h3>Shared credit pool</h3><p>Unused credits are saved for the next visitor, so no bottle ever goes to waste.</p></article>
      <article class="feature"><span class="ico peach"><?= ic_paw() ?></span><h3>Dog or cat choice</h3><p>Pick the food type and portion size, then confirm before anything is dispensed.</p></article>
      <article class="feature"><span class="ico blue"><?= ai('box') ?></span><h3>Live stock tracking</h3><p>Food, dental sticks and the bottle bin are tracked, with warnings before they run out or fill up.</p></article>
      <article class="feature"><span class="ico rose"><?= ai('report') ?></span><h3>Printable reports</h3><p>Generate bottle, reward, feeding and stock reports for any date range in one click.</p></article>
      <article class="feature"><span class="ico green"><?= ic_lock() ?></span><h3>Secure admin panel</h3><p>A password-protected dashboard keeps settings and records in the hands of authorized staff.</p></article>
    </div>
  </div>
</section>

<!-- ================= ACCEPTED BOTTLES ================= -->
<section class="section container" id="accepted">
  <div class="section-head">
    <span class="eyebrow">Accepted bottles</span>
    <h2>Got one of these? <em>Bring it in.</em></h2>
    <p>The machine recognizes common clear plastic bottles found in our barangay.</p>
  </div>
  <div class="accepted">
<?php foreach ($accepted as $cat => $names): ?>
    <div class="acc-group">
      <h3><?= e($cat) ?></h3>
      <p><?php foreach (array_keys($names) as $n): ?><span class="tag"><?= e($n) ?></span><?php endforeach; ?></p>
    </div>
<?php endforeach; ?>
  </div>
  <p class="not-accepted"><?= ic_info() ?> <span>Not accepted: green or colored bottles and anything that is not a plastic bottle.</span></p>
</section>

<!-- ================= FAQ ================= -->
<section class="section tint" id="faq">
  <div class="container narrow">
    <div class="section-head">
      <span class="eyebrow">FAQ</span>
      <h2>Questions? <em>Answered.</em></h2>
    </div>
    <div class="faq">
<?php foreach ($faqs as $i => [$q, $a]): ?>
      <details<?= $i === 0 ? ' open' : '' ?>><summary><?= e($q) ?></summary><p><?= e($a) ?></p></details>
<?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ================= FINAL CTA ================= -->
<section class="final container">
  <div class="final-card">
    <div class="final-pets" aria-hidden="true"><?= ic_dog() ?><?= ic_cat() ?></div>
    <h2>One bottle can be someone's next meal.</h2>
    <p>Try the EcoPaws demo now and see how quickly a bottle turns into food for a stray friend.</p>
    <div class="cta-row center-row">
      <a class="btn btn-primary btn-lg" href="app/index.php">Try the live demo <?= ic_arrow() ?></a>
      <a class="btn btn-onDark btn-lg" href="admin/login.php"><?= ic_lock() ?> Admin sign in</a>
    </div>
  </div>
</section>
</main>

<footer class="site-foot">
  <div class="container foot-in">
    <?= brand_link('#top', 'BARANGAY DAKILA') ?>
    <nav aria-label="Footer"><a href="#how">How it works</a><a href="#rewards">Rewards</a><a href="#faq">FAQ</a><a href="app/index.php">Demo</a><a href="admin/login.php">Admin</a></nav>
    <p>&copy; <?= date('Y') ?> EcoPaws &middot; An IoT-based smart recycling and reward management system for stray and community animals.</p>
  </div>
</footer>

<script>
(function () {
  var t = document.getElementById('navToggle'), n = document.getElementById('navLinks');
  function set(open) { n.classList.toggle('open', open); t.setAttribute('aria-expanded', open); }
  t.addEventListener('click', function () { set(!n.classList.contains('open')); });
  n.addEventListener('click', function (e) { if (e.target.tagName === 'A') set(false); });
})();
</script>
</body>
</html>
