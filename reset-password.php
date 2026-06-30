<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Reset Password';
if (isLoggedIn()) redirect('dashboard.php');

$token = clean($_GET['token'] ?? $_POST['token'] ?? '');
if (!$token) redirect('forgot-password.php');

$stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW()");
$stmt->execute([$token]);
$u = $stmt->fetch();

if (!$u) {
    flash('error', 'Link reset password tidak valid atau sudah kedaluwarsa.');
    redirect('forgot-password.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    if (strlen($password) < 6) {
        flash('error', 'Password minimal 6 karakter.');
    } elseif ($password !== $confirm) {
        flash('error', 'Konfirmasi password tidak cocok.');
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        $stmt->execute([$hash, $u['id']]);
        flash('success', 'Password berhasil diubah. Silakan login dengan password baru kamu.');
        redirect('login.php');
    }
}
include __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap">
  <h2>Reset Password</h2>
  <p class="sub">Buat password baru untuk akun <?= clean($u['email']) ?></p>
  <?php if ($e = flash('error')): ?><div class="alert alert-error"><?= clean($e) ?></div><?php endif; ?>
  <form method="post">
    <input type="hidden" name="token" value="<?= clean($token) ?>">
    <div class="form-group">
      <label>Password Baru</label>
      <input type="password" name="password" required minlength="6">
    </div>
    <div class="form-group">
      <label>Konfirmasi Password Baru</label>
      <input type="password" name="confirm_password" required minlength="6">
    </div>
    <button type="submit" class="btn btn-primary btn-block">Simpan Password Baru</button>
  </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
