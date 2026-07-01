<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Login';
if (isLoggedIn()) redirect('dashboard.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $captchaResponse = $_POST['g-recaptcha-response'] ?? '';

    if (!$email || !$password) {
        flash('error', 'Email dan password wajib diisi.');
    } elseif (empty($captchaResponse) || !verifyRecaptcha($captchaResponse)) {
        flash('error', 'Verifikasi reCAPTCHA gagal. Silakan coba lagi.');
    } else {
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
}

include __DIR__ . '/includes/header.php';

$flashError   = flash('error');
$flashSuccess = flash('success');

// ── Verifikasi reCAPTCHA ke Google ──
function verifyRecaptcha(string $response): bool
{
    $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'secret'   => RECAPTCHA_SECRET_KEY,
            'response' => $response,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
        ]),
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $result = curl_exec($ch);
    curl_close($ch);

    if (!$result) return false;
    $json = json_decode($result, true);
    return !empty($json['success']);
}
?>
<div class="lamp-login-wrap">
  <input type="checkbox" id="lampSwitch" class="lamp-checkbox" <?= $flashError || $flashSuccess ? 'checked' : '' ?>>
  <label for="lampSwitch" class="lamp" aria-label="Nyalakan lampu untuk login">
    <span class="lamp-glow"></span>
    <span class="lamp-shade">
      <span class="lamp-face">
        <span class="lamp-eye"></span>
        <span class="lamp-eye"></span>
        <span class="lamp-mouth"></span>
      </span>
    </span>
    <span class="lamp-pull"></span>
    <span class="lamp-stand"></span>
    <span class="lamp-base"></span>
  </label>
  <p class="lamp-hint">Tarik / klik lampu untuk login</p>
  <div class="auth-wrap lamp-form">
    <h2>Login</h2>
    <p class="sub">Masuk ke akun TeleCard kamu</p>
    <?php if ($flashError): ?><div class="alert alert-error"><?= clean($flashError) ?></div><?php endif; ?>
    <?php if ($flashSuccess): ?><div class="alert alert-success"><?= clean($flashSuccess) ?></div><?php endif; ?>
    <form method="post" id="loginForm" novalidate>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required>
      </div>
      <p style="text-align:right;margin:-10px 0 16px">
        <a href="forgot-password.php" style="color:var(--tg-blue);font-size:0.9em">Lupa password?</a>
      </p>

      <div class="form-group" style="display:flex;justify-content:center;margin:16px 0;">
        <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars(RECAPTCHA_SITE_KEY) ?>"></div>
      </div>

      <button type="submit" class="btn btn-primary btn-block">Login</button>
    </form>
    <div class="auth-foot">Belum punya akun? <a href="register.php" style="color:var(--tg-blue)">Daftar di sini</a></div>
  </div>
</div>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
(function () {
    var form = document.getElementById('loginForm');
    if (!form) return;
    form.addEventListener('submit', function (e) {
        var captchaOk = true;
        if (typeof grecaptcha !== 'undefined') {
            captchaOk = grecaptcha.getResponse().length > 0;
        }
        if (!captchaOk) {
            e.preventDefault();
            alert('Silakan verifikasi reCAPTCHA terlebih dahulu.');
        }
    });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
