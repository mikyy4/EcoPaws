<?php
require __DIR__ . '/_init.php';

$per = reward_rules()['per'];
app_header('Welcome', 0, null, ['right' => corner_link('../admin/login.php', ic_lock(), 'Admin')]);
?>
<section class="card hero">
  <div>
    <div class="eyebrow"><?= ic_leaf() ?> Smart recycling &amp; reward system</div>
    <h1>Recycle a bottle.<br><span>Feed a friend.</span></h1>
    <p class="sub">An IoT-based smart recycling and reward management system for stray and community animals.</p>
    <div class="rule"><?= ic_stick() ?> <span>Every bottle = 1 food portion. Deposit <?= $per ?> valid bottles to also get 1 dental stick!</span></div>
    <div class="cta">
      <a class="btn btn-primary btn-lg" href="start.php">Tap to start <?= ic_arrow() ?></a>
      <a class="btn btn-sun" href="credits.php"><?= ic_bowl() ?> Saved credits</a>
    </div>
  </div>
  <div class="hero-art" aria-hidden="true">
    <div class="blob"></div>
    <?= ic_bottle('bot float') ?>
    <?= ic_bottle('bot2 float') ?>
    <?= ic_dog('pet dog float') ?>
    <?= ic_cat('pet cat float') ?>
  </div>
</section>

<div class="how">
  <div class="g"><?= ic_bottle() ?><span>1. Deposit<small>Insert plastic bottles</small></span></div>
  <div class="s"><?= ic_bowl() ?><span>2. Earn credits<small>1 food credit per bottle</small></span></div>
  <div class="p"><?= ic_paw() ?><span>3. Feed animals<small>Dog or cat food + dental stick</small></span></div>
</div>
<?php app_footer();
