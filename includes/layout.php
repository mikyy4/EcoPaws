<?php
/**
 * Shared page chrome: <head>, brand, and the header/footer of the recycling-flow screens.
 */
require_once __DIR__ . '/icons.php';

/** Opens <html>…<body>. $styles = css file names inside assets/css/ (without folder). */
function html_head(string $title, array $styles = ['base', 'app'], string $extraHead = '', string $bodyClass = '', string $description = ''): void {
    $favicon = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='16' fill='%233F8F76'/%3E%3Cg fill='white'%3E%3Cellipse cx='32' cy='42' rx='13' ry='11'/%3E%3Cellipse cx='14' cy='28' rx='6' ry='8' transform='rotate(-20 14 28)'/%3E%3Cellipse cx='25' cy='17' rx='6' ry='8.5'/%3E%3Cellipse cx='39' cy='17' rx='6' ry='8.5'/%3E%3Cellipse cx='50' cy='28' rx='6' ry='8' transform='rotate(20 50 28)'/%3E%3C/g%3E%3C/svg%3E"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#3F8F76">
<?php if ($description): ?><meta name="description" content="<?= e($description) ?>">
<?php endif; ?>
<title><?= e($title) ?></title>
<link rel="icon" href="<?= $favicon ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@600;700;800;900&amp;display=swap">
<?php foreach ($styles as $css): ?><link rel="stylesheet" href="<?= e(rel('assets/css/' . $css . '.css')) ?>">
<?php endforeach; ?>
<?= $extraHead ?>
</head>
<body<?= $bodyClass ? ' class="' . e($bodyClass) . '"' : '' ?>>
<?php }

function brand_link(string $href, string $tagline = 'RECYCLE &middot; REWARD &middot; FEED'): string {
    return '<a class="brand" href="' . e($href) . '"><span class="logo">' . ic_paw() . '</span>'
         . '<span><b>Eco<span>Paws</span></b><small>' . $tagline . '</small></span></a>';
}

/**
 * Header of a recycling-flow screen.
 * $step: 1 Deposit | 2 Summary | 3 Reward | 4 Done  (0 = no stepper)
 * $opts: 'right' => HTML for the top-right corner, 'head' => extra <head> markup.
 */
function app_header(string $title, int $step = 0, ?array $tx = null, array $opts = []): void {
    html_head('EcoPaws - ' . $title, ['base', 'app'], $opts['head'] ?? '');
    $labels = [1 => 'Deposit', 2 => 'Summary', 3 => 'Reward', 4 => 'Done']; ?>
<div class="wrap">
  <header class="topbar">
    <?= brand_link('index.php') ?>

<?php if ($tx): ?>    <span class="txbadge">Transaction #<?= e($tx['id']) ?></span>
<?php elseif (!empty($opts['right'])): ?>    <?= $opts['right'] ?>

<?php endif; ?>
  </header>
<?php if ($step): ?>
  <ol class="steps">
<?php foreach ($labels as $i => $l): $cls = $i < $step ? 'past' : ($i === $step ? 'on' : ''); ?>
    <li class="<?= $cls ?>"><i><?= $i < $step ? ic_check() : $i ?></i><span><?= $l ?></span></li>
<?php endforeach; ?>
  </ol>
<?php endif;
}

function app_footer(string $script = ''): void {
    echo "</div>\n";
    if ($script) echo '<script src="' . e(rel('assets/js/' . $script)) . '"></script>' . "\n";
    echo "</body>\n</html>\n";
}

/** Link/button used in the top-right of the app header. */
function corner_link(string $href, string $iconHtml, string $label): string {
    return '<a class="cornerlink" href="' . e($href) . '">' . $iconHtml . ' ' . e($label) . '</a>';
}
