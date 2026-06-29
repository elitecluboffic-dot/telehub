<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Login';

if (isLoggedIn()) redirect('dashboard.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $u = $stmt->fetch();

    if (!$u || !password_verify($password, $u['password'])) {
        flash('error', 'Email atau password salah.');
    } elseif (!$u['is_verified']) {
        flash('error', 'Akun belum diverifikasi. Cek email kamu.');
    } else {
        $_SESSION['user_id'] = $u['id'];
        redirect('dashboard.php');
    }
}

include __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap">
  <h2>Login</h2>
  <p class="sub">Masuk ke akun TeleCard kamu</p>
  <?php if ($e = flash('error')): ?><div class="alert alert-error"><?= clean($e) ?></div><?php endif; ?>
  <?php if ($s = flash('success')): ?><div class="alert alert-success"><?= clean($s) ?></div><?php endif; ?>
  <form method="post">
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" required>
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" required>
    </div>
    <button type="submit" class="btn btn-primary btn-block">Login</button>
  </form>
  <div class="auth-foot">Belum punya akun? <a href="register.php" style="color:var(--tg-blue)">Daftar di sini</a></div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>