<?php
/**
 * Shared code of the admin pages: login guard, page shell (sidebar + header) and admin-only components.
 * Every admin page starts with: require __DIR__ . '/../includes/admin.php';
 */
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/components.php';

if (empty($_SESSION['admin_id'])) redirect('login.php');

// ---------- Inventory helpers ----------

function inv_icon(string $key): string {
    return match ($key) {
        'dog_food'     => ic_dog('pi'),
        'cat_food'     => ic_cat('pi'),
        'dental_stick' => ic_stick('pi stick'),
        default        => ic_bottle('pi bottle'),
    };
}

/** Level + status of an inventory row -> [percent, label, css tone ('' | 'warn' | 'bad')] */
function inv_status(array $i): array {
    $p = pct($i['quantity'], $i['capacity']);
    if ($i['item_key'] === 'bottle_bin') {
        if ($p >= 100) return [$p, 'FULL - retrieve bottles', 'bad'];
        if ($p >= 80)  return [$p, 'Almost full', 'warn'];
        return [$p, 'OK', ''];
    }
    if ($i['quantity'] <= 0) return [$p, 'EMPTY - refill', 'bad'];
    if ($p <= 20)            return [$p, 'LOW - refill soon', 'warn'];
    return [$p, 'OK', ''];
}

function inventory_rows(): array {
    return db()->query('SELECT * FROM inventory ORDER BY item_key')->fetchAll();
}

/** Inventory items that need staff attention: [['item' => row, 'status' => label, 'tone' => warn|bad], ...] */
function inventory_alerts(array $inv): array {
    $out = [];
    foreach ($inv as $i) {
        [, $label, $tone] = inv_status($i);
        if ($tone !== '') $out[] = ['item' => $i, 'status' => $label, 'tone' => $tone];
    }
    return $out;
}

/** Grid of stock cards. With $actions the refill / empty-bin forms are shown (stock page). */
function inventory_cards(array $inv, bool $actions = false): string {
    $html = '<div class="inv">';
    foreach ($inv as $i) {
        [$p, $status, $tone] = inv_status($i);
        $html .= '<div class="invc"><div class="top">' . inv_icon($i['item_key'])
            . '<div><b>' . e($i['label']) . '</b><small>Capacity ' . e($i['capacity']) . ' ' . e($i['unit']) . '</small></div>'
            . pill($status, $tone) . '</div>'
            . '<div class="qty">' . e($i['quantity']) . ' <small>' . e($i['unit']) . '</small></div>'
            . '<div class="lvl ' . $tone . '"><span style="width:' . min(100, $p) . '%"></span></div>'
            . '<div class="meta"><span>Level</span><span>' . $p . '%</span></div>';
        if ($actions) {
            $html .= '<form method="post" action="stock.php">' . csrf_field();
            if ($i['item_key'] === 'bottle_bin') {
                $html .= '<input type="hidden" name="action" value="empty_bin">'
                       . '<button class="btn btn-peach sm" type="submit">Mark bin as emptied</button>';
            } else {
                $html .= '<input type="hidden" name="action" value="refill"><input type="hidden" name="item_key" value="' . e($i['item_key']) . '">'
                       . '<label class="sr-only" for="amt_' . e($i['item_key']) . '">Amount to add</label>'
                       . '<input class="in" id="amt_' . e($i['item_key']) . '" type="number" name="amount" min="1" required placeholder="Add ' . e($i['unit']) . '">'
                       . '<button class="btn btn-primary sm" type="submit">Refill</button>';
            }
            $html .= '</form>';
        }
        $html .= '</div>';
    }
    return $html . '</div>';
}

// ---------- Tables ----------

/**
 * Responsive data table.
 *   $cols  = [[header, key-or-callable, optional 'num'], ...]   a callable receives the row and returns HTML
 *   $foot  = [[html, colspan = 1, num = false], ...]            optional totals row
 */
function data_table(array $cols, array $rows, string $empty = 'No data yet.', ?array $foot = null): string {
    $h = '<div class="tblwrap"><table class="tbl"><thead><tr>';
    foreach ($cols as $c) $h .= '<th' . (($c[2] ?? '') === 'num' ? ' class="num"' : '') . '>' . e($c[0]) . '</th>';
    $h .= '</tr></thead><tbody>';
    foreach ($rows as $r) {
        $h .= '<tr>';
        foreach ($cols as $c) {
            $v = $c[1] instanceof Closure ? $c[1]($r) : e($r[$c[1]]);
            $h .= '<td' . (($c[2] ?? '') === 'num' ? ' class="num"' : '') . '>' . $v . '</td>';
        }
        $h .= '</tr>';
    }
    if (!$rows) $h .= '<tr><td colspan="' . count($cols) . '" class="empty">' . e($empty) . '</td></tr>';
    $h .= '</tbody>';
    if ($foot) {
        $h .= '<tfoot><tr>';
        foreach ($foot as $f) $h .= '<td' . (($f[1] ?? 1) > 1 ? ' colspan="' . $f[1] . '"' : '') . (!empty($f[2]) ? ' class="num"' : '') . '>' . $f[0] . '</td>';
        $h .= '</tr></tfoot>';
    }
    return $h . '</table></div>';
}

/** A message box. $tone: '' (success) | 'bad' */
function flash_box(?array $f): string {
    if (!$f) return '';
    $bad = ($f['type'] ?? 'ok') === 'bad';
    return '<div class="flash ' . ($bad ? 'bad' : '') . '" role="status">' . ($bad ? ai('alert') : ic_check()) . ' <span>' . e($f['message']) . '</span></div>';
}

// ---------- Page shell ----------

function admin_nav(): array {
    return [
        'Overview'   => ['dashboard.php'   => ['dash',   'Dashboard']],
        'Operations' => ['collections.php' => ['bottle', 'Bottle Collections'],
                         'rewards.php'     => ['gift',   'Rewards'],
                         'feeding.php'     => ['bowl',   'Feeding']],
        'Management' => ['stock.php'       => ['box',    'Stock & Capacity'],
                         'reports.php'     => ['report', 'Reports'],
                         'account.php'     => ['user',   'Account']],
    ];
}

function admin_header(string $title, string $icon = 'dash', string $sub = ''): void {
    $cur = basename($_SERVER['SCRIPT_NAME']);
    html_head('EcoPaws Admin - ' . $title, ['base', 'admin'], '', 'admin'); ?>
<a class="skip" href="#content">Skip to content</a>
<div class="mobilebar noprint">
  <?= brand_link('dashboard.php', 'ADMIN PANEL') ?>
  <button class="menu-btn" id="menuBtn" type="button" aria-label="Open menu" aria-expanded="false" aria-controls="sidebar"><?= ai('menu') ?></button>
</div>
<div class="scrim" id="scrim"></div>
<div class="shell">
  <aside class="side noprint" id="sidebar">
    <div class="side-top">
      <?= brand_link('dashboard.php', 'BARANGAY DAKILA') ?>
      <button class="menu-close" id="menuClose" type="button" aria-label="Close menu"><?= ai('close') ?></button>
    </div>
    <span class="role">ADMIN PANEL</span>
    <nav class="nav" aria-label="Admin">
<?php foreach (admin_nav() as $group => $links): ?>
      <span class="nav-group"><?= e($group) ?></span>
<?php foreach ($links as $file => [$ic, $label]): ?>
      <a href="<?= $file ?>" class="<?= $cur === $file ? 'on' : '' ?>"<?= $cur === $file ? ' aria-current="page"' : '' ?>><?= ai($ic) ?><?= e($label) ?></a>
<?php endforeach; endforeach; ?>
    </nav>
    <div class="me">
      <a class="viewapp" href="../app/index.php" target="_blank" rel="noopener"><?= ai('external') ?> Open recycling app</a>
      <div class="who"><span class="av"><?= ai('user') ?></span><span><?= e($_SESSION['admin_name'] ?? 'Admin') ?><small>Logged in</small></span></div>
      <a class="out" href="logout.php"><?= ai('logout') ?> Logout</a>
    </div>
  </aside>
  <main class="main" id="content">
    <div class="pagehead">
      <div>
        <h1><span class="pi"><?= ai($icon) ?></span><?= e($title) ?></h1>
<?php if ($sub): ?>        <p><?= e($sub) ?></p>
<?php endif; ?>
      </div>
      <span class="date noprint"><?= date('D, M j Y') ?></span>
    </div>
<?php }

function admin_footer(): void { ?>
  </main>
</div>
<script src="<?= e(rel('assets/js/admin.js')) ?>"></script>
</body>
</html>
<?php }
