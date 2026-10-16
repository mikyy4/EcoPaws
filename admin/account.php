<?php
require __DIR__ . '/../includes/admin.php';
$id = (int)$_SESSION['admin_id'];
$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $current = $_POST['current'] ?? '';
    $new     = $_POST['new'] ?? '';
    $hash    = query('SELECT password_hash FROM admins WHERE id = ?', [$id])->fetchColumn();

    if (!$hash || !password_verify($current, $hash)) {
        $flash = ['message' => 'Current password is incorrect.', 'type' => 'bad'];
    } elseif (strlen($new) < 6) {
        $flash = ['message' => 'New password must be at least 6 characters.', 'type' => 'bad'];
    } elseif ($new !== ($_POST['confirm'] ?? '')) {
        $flash = ['message' => 'New password and confirmation do not match.', 'type' => 'bad'];
    } else {
        query('UPDATE admins SET password_hash = ? WHERE id = ?', [password_hash($new, PASSWORD_DEFAULT), $id]);
        $flash = ['message' => 'Password changed successfully.', 'type' => 'ok'];
    }
}

$a = query('SELECT username, full_name, created_at, last_login FROM admins WHERE id = ?', [$id])->fetch();

admin_header('Account', 'user', 'Your admin profile and password.');
?>
<div class="row2">
  <section class="card">
    <h2><?= ai('user') ?> Profile</h2>
    <dl class="kv">
      <dt>Username</dt><dd><?= e($a['username']) ?></dd>
      <dt>Name</dt><dd><?= e($a['full_name']) ?></dd>
      <dt>Created</dt><dd><?= e($a['created_at']) ?></dd>
      <dt>Last login</dt><dd><?= e($a['last_login'] ?? '-') ?></dd>
    </dl>
  </section>

  <section class="card">
    <h2><?= ai('lock') ?> Change Password</h2>
    <?= flash_box($flash) ?>
    <form method="post" action="account.php">
      <?= csrf_field() ?>
      <div class="f"><label for="current">Current password</label><input id="current" type="password" name="current" autocomplete="current-password" required></div>
      <div class="f"><label for="new">New password</label><input id="new" type="password" name="new" autocomplete="new-password" required><div class="hint">At least 6 characters</div></div>
      <div class="f"><label for="confirm">Confirm new password</label><input id="confirm" type="password" name="confirm" autocomplete="new-password" required></div>
      <button class="btn btn-primary sm" type="submit">Change password</button>
    </form>
  </section>
</div>
<?php admin_footer();
