<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Login Admin';

if (isAdminLoggedIn()) redirect('admin/dashboard.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $a = $stmt->fetch();

    if ($a && password_verify($password, $a['password'])) {
        $_SESSION['admin_id'] = $a['id'];
        redirect('admin/dashboard.php');
    } else {
        flash('error', 'Username atau password admin salah.');
    }
}

include __DIR__ . '/includes/admin_header.php';
?>
<div class="auth-wrap">
  <h2>Login Admin</h2>
  <?php if ($e = flash('error')): ?><div class="alert alert-error"><?= clean($e) ?></div><?php endif; ?>
  <form method="post">
    <div class="form-group"><label>Username</label><input type="text" name="username" required></div>
    <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
    <button class="btn btn-primary btn-block">Login</button>
  </form>
</div>
<?php include __DIR__ . '/includes/admin_footer.php'; ?>
