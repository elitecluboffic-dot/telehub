<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Daftar';

if (isLoggedIn()) redirect('dashboard.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username'] ?? '');
    $email    = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (!$username || !$email || !$password) {
        flash('error', 'Semua field wajib diisi.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Format email tidak valid.');
    } elseif ($password !== $confirm) {
        flash('error', 'Konfirmasi password tidak cocok.');
    } elseif (strlen($password) < 6) {
        flash('error', 'Password minimal 6 karakter.');
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        if ($stmt->fetch()) {
            flash('error', 'Username atau email sudah terdaftar.');
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $token = bin2hex(random_bytes(32));

            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, verification_token) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hash, $token]);

            sendVerificationEmail($email, $username, $token);

            flash('success', 'Pendaftaran berhasil! Silakan cek email kamu (' . $email . ') untuk verifikasi akun sebelum login.');
            redirect('login.php');
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap">
  <h2>Buat Akun</h2>
  <p class="sub">Daftar untuk mulai membuat custom card Telegram-mu</p>

  <?php if ($e = flash('error')): ?><div class="alert alert-error"><?= clean($e) ?></div><?php endif; ?>

  <form method="post">
    <div class="form-group">
      <label>Username</label>
      <input type="text" name="username" required>
    </div>
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" required>
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" required minlength="6">
    </div>
    <div class="form-group">
      <label>Konfirmasi Password</label>
      <input type="password" name="confirm_password" required minlength="6">
    </div>
    <button type="submit" class="btn btn-primary btn-block">Daftar</button>
  </form>
  <div class="auth-foot">Sudah punya akun? <a href="login.php" style="color:var(--tg-blue)">Login di sini</a></div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>