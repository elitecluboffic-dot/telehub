<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Lupa Password';
if (isLoggedIn()) redirect('dashboard.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Format email tidak valid.');
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $u = $stmt->fetch();

        // Pesan sukses ditampilkan walau email tidak ditemukan, supaya
        // orang lain tidak bisa cek email mana saja yang terdaftar.
        if ($u) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
            $stmt->execute([$token, $expires, $u['id']]);
            sendResetPasswordEmail($u['email'], $u['username'], $token);
        }
        flash('success', 'Kalau email kamu terdaftar, link reset password sudah dikirim. Cek inbox / folder spam ya.');
        redirect('forgot-password.php');
    }
}
include __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap">
  <h2>Lupa Password</h2>
  <p class="sub">Masukkan email akun TeleCard kamu, kami akan kirimkan link reset password</p>
  <?php if ($e = flash('error')): ?><div class="alert alert-error"><?= clean($e) ?></div><?php endif; ?>
  <?php if ($s = flash('success')): ?><div class="alert alert-success"><?= clean($s) ?></div><?php endif; ?>
  <form method="post">
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" required>
    </div>
    <button type="submit" class="btn btn-primary btn-block">Kirim Link Reset</button>
  </form>
  <div class="auth-foot">Sudah ingat password? <a href="login.php" style="color:var(--tg-blue)">Login di sini</a></div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
