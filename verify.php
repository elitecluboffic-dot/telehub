<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Verifikasi Akun';

$token = $_GET['token'] ?? '';
$success = false;

if ($token) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE verification_token = ?");
    $stmt->execute([$token]);
    $u = $stmt->fetch();
    if ($u) {
        $upd = $pdo->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
        $upd->execute([$u['id']]);
        $success = true;
    }
}

include __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap" style="text-align:center">
  <?php if ($success): ?>
    <h2>✅ Verifikasi Berhasil!</h2>
    <p class="sub">Akun kamu sudah aktif. Sekarang kamu bisa login.</p>
    <a href="login.php" class="btn btn-primary btn-block">Login Sekarang</a>
  <?php else: ?>
    <h2>❌ Token Tidak Valid</h2>
    <p class="sub">Link verifikasi salah atau sudah digunakan.</p>
    <a href="register.php" class="btn btn-outline btn-block">Daftar Ulang</a>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>