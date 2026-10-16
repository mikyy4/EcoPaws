<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/layout.php';
if (!empty($_SESSION['admin_id'])) redirect('dashboard.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $admin = query('SELECT * FROM admins WHERE username = ?', [trim($_POST['username'] ?? '')])->fetch();
    if ($admin && password_verify($_POST['password'] ?? '', $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id']   = (int)$admin['id'];
        $_SESSION['admin_name'] = $admin['full_name'];
        query('UPDATE admins SET last_login = NOW() WHERE id = ?', [$admin['id']]);
        redirect('dashboard.php');
    }
    usleep(400000);                       // slows down password guessing
    $error = 'Invalid username or password.';
}
html_head('EcoPaws - Admin Login', ['base', 'admin'], '', 'admin');
?>
<div class="loginwrap">
  <div class="card logincard">
    <div class="badge"><?= ic_paw() ?></div>
    <h1>Eco<span>Paws</span> Admin</h1>
    <p class="lead">For authorized Barangay staff only.</p>
<?php if ($error): ?>
    <div class="flash bad" role="alert"><?= ic_x() ?> <span><?= e($error) ?></span></div>
<?php endif; ?>

    <form action="login.php" method="post">
      <?= csrf_field() ?>
      <div class="f"><label for="username">Username</label><input id="username" type="text" name="username" autocomplete="username" required autofocus></div>
      <div class="f"><label for="password">Password</label><input id="password" type="password" name="password" autocomplete="current-password" required></div>
      <button class="btn btn-primary btn-lg" type="submit">Login <?= ic_arrow() ?></button>
    </form>
    <a class="back" href="../index.php"><?= ic_back() ?> Back to home</a>
  </div>
</div>
</body>
</html>
