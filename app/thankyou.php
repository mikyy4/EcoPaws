<?php
require __DIR__ . '/_init.php';
unset($_SESSION['tx']);
app_header('Thank You', 0, null, ['head' => '<meta http-equiv="refresh" content="5;url=index.php">']);
?>
<section class="card center thanks">
  <div class="thanks-pets">
    <?= ic_dog('float') ?>
    <?= ic_cat('float late') ?>
  </div>
  <h1 class="title">Thank you!</h1>
  <p class="lead">Your recycled bottles will help feed stray and community animals in Barangay Dakila.</p>
  <p class="lead">Returning to the home screen&hellip;</p>
  <div class="progress"><span style="animation-duration:5s"></span></div>
  <div class="actions"><a class="btn btn-primary" href="index.php">Back to home</a></div>
</section>
<?php app_footer();
