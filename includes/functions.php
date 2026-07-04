<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/SimpleMailer.php';
function clean($str) {
    return htmlspecialchars(trim($str ?? ''), ENT_QUOTES, 'UTF-8');
}
function redirect($path) {
    header("Location: " . SITE_URL . '/' . $path);
    exit;
}
function flash($key, $msg = null) {
    if ($msg !== null) {
        $_SESSION['flash'][$key] = $msg;
        return;
    }
    if (!empty($_SESSION['flash'][$key])) {
        $m = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $m;
    }
    return null;
}
function isLoggedIn() {
    return !empty($_SESSION['user_id']);
}
function requireLogin() {
    if (!isLoggedIn()) {
        flash('error', 'Silakan login terlebih dahulu.');
        redirect('login.php');
    }
}
function currentUser() {
    global $pdo;
    if (!isLoggedIn()) return null;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}
function isAdminLoggedIn() {
    return !empty($_SESSION['admin_id']);
}
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        redirect('admin/login.php');
    }
}
function sendVerificationEmail($email, $username, $token) {
    $link = SITE_URL . "/verify.php?token=" . $token;
    $subject = "Verifikasi Akun " . SITE_NAME;
    $body = "
        <div style='font-family:Arial,sans-serif;max-width:500px;margin:auto'>
            <h2 style='color:#2AABEE'>Halo, {$username}!</h2>
            <p>Terima kasih sudah mendaftar di " . SITE_NAME . ".</p>
            <p>Klik tombol di bawah ini untuk verifikasi akun kamu:</p>
            <p><a href='{$link}' style='background:#2AABEE;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;display:inline-block'>Verifikasi Akun</a></p>
            <p>Atau salin link ini ke browser: <br>{$link}</p>
        </div>
    ";
    $mailer = new SimpleMailer(GMAIL_EMAIL, GMAIL_APP_PASSWORD);
    $ok = $mailer->send($email, $subject, $body);
    if (!$ok) {
        error_log("Email error: " . $mailer->lastError);
    }
    return $ok;
}
function sendResetPasswordEmail($email, $username, $token) {
    $link = SITE_URL . "/reset-password.php?token=" . $token;
    $subject = "Reset Password " . SITE_NAME;
    $body = "
        <div style='font-family:Arial,sans-serif;max-width:500px;margin:auto'>
            <h2 style='color:#2AABEE'>Halo, {$username}!</h2>
            <p>Kami menerima permintaan untuk mereset password akun kamu.</p>
            <p>Klik tombol di bawah ini untuk membuat password baru (link berlaku 1 jam):</p>
            <p><a href='{$link}' style='background:#2AABEE;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;display:inline-block'>Reset Password</a></p>
            <p>Atau salin link ini ke browser: <br>{$link}</p>
            <p>Kalau kamu tidak meminta ini, abaikan saja email ini — password kamu tetap aman.</p>
        </div>
    ";
    $mailer = new SimpleMailer(GMAIL_EMAIL, GMAIL_APP_PASSWORD);
    $ok = $mailer->send($email, $subject, $body);
    if (!$ok) {
        error_log("Email error: " . $mailer->lastError);
    }
    return $ok;
}
function uploadCardImage($fileInputName) {
    if (empty($_FILES[$fileInputName]['name'])) return null;
    $file = $_FILES[$fileInputName];
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) return null;
    if ($file['size'] > 3 * 1024 * 1024) return null; // max 3MB
    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
    $newName = 'card_' . uniqid() . '_' . time() . '.' . $ext;
    $dest = UPLOAD_DIR . $newName;
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return $newName;
    }
    return null;
}
function badgeColorByType($type) {
    switch ($type) {
        case 'channel': return '#2AABEE';
        case 'group':   return '#8e44ad';
        case 'user':    return '#27ae60';
        default:        return '#2AABEE';
    }
}

/**
 * Deteksi apakah request datang dari bot/crawler berdasarkan User-Agent.
 * Dipakai untuk memisahkan hitungan views artikel (human vs bot).
 */
function isBotVisitor() {
    $ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
    if ($ua === '') return true; // tidak ada UA = biasanya bot/script

    $botPatterns = [
        'bot', 'crawl', 'spider', 'slurp', 'facebookexternalhit',
        'whatsapp', 'telegrambot', 'discordbot', 'preview', 'headless',
        'googlebot', 'bingbot', 'yandex', 'duckduckbot', 'ahrefsbot',
        'semrushbot', 'mj12bot', 'petalbot', 'curl', 'wget', 'python-requests'
    ];
    foreach ($botPatterns as $p) {
        if (strpos($ua, $p) !== false) return true;
    }
    return false;
}
