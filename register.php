<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Daftar';
if (isLoggedIn()) redirect('dashboard.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username'] ?? '');
    $email    = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $terms    = $_POST['terms'] ?? '';
    $captchaResponse = $_POST['g-recaptcha-response'] ?? '';

    if (!$username || !$email || !$password) {
        flash('error', 'Semua field wajib diisi.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Format email tidak valid.');
    } elseif ($password !== $confirm) {
        flash('error', 'Konfirmasi password tidak cocok.');
    } elseif (strlen($password) < 6) {
        flash('error', 'Password minimal 6 karakter.');
    } elseif (empty($terms)) {
        flash('error', 'Kamu harus menyetujui Syarat & Ketentuan dan Kebijakan Privasi.');
    } elseif (empty($captchaResponse) || !verifyRecaptcha($captchaResponse)) {
        flash('error', 'Verifikasi reCAPTCHA gagal. Silakan coba lagi.');
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
<style>
*, *::before, *::after { box-sizing: border-box; }

.fp-wrap {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px 12px;
    overflow: hidden;
}

/* Particles */
.fp-particles { position: absolute; inset: 0; overflow: hidden; pointer-events: none; z-index: 0; }
.fp-particle {
    position: absolute;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,140,42,0.5) 0%, rgba(255,140,42,0) 70%);
    animation: fpFloat linear infinite;
    will-change: transform;
}
@keyframes fpFloat {
    0%   { transform: translateY(0); opacity: 0; }
    10%  { opacity: 1; }
    90%  { opacity: 1; }
    100% { transform: translateY(-400px) translateX(20px); opacity: 0; }
}

/* Card */
.fp-card {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 340px;
    background: linear-gradient(160deg, #1f1626 0%, #150e1c 55%, #0c0810 100%);
    border: 1px solid rgba(255,140,42,0.2);
    border-radius: 16px;
    padding: 0 20px 20px;
    text-align: center;
    box-shadow: 0 0 0 1px rgba(255,140,42,0.04), 0 16px 44px rgba(0,0,0,0.5), 0 0 60px rgba(255,140,42,0.09);
    overflow: hidden;
}
.fp-card::before {
    content: "";
    position: absolute;
    top: -50%; left: -10%;
    width: 130%; height: 130%;
    background: radial-gradient(circle at 20% 20%, rgba(255,140,42,0.16) 0%, transparent 45%),
                radial-gradient(circle at 80% 30%, rgba(255,70,30,0.11) 0%, transparent 45%);
    pointer-events: none;
    animation: fpMesh 9s ease-in-out infinite;
    z-index: 0;
}
@keyframes fpMesh {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(12px) rotate(3deg); }
}
.fp-card::after {
    content: "";
    position: absolute;
    inset: 0;
    border-radius: 16px;
    padding: 1px;
    background: linear-gradient(135deg, rgba(255,140,42,0.32), transparent 40%, transparent 60%, rgba(255,70,30,0.22));
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    pointer-events: none;
}

/* Stage */
.fp-stage {
    position: relative;
    z-index: 2;
    padding-top: 16px;
    margin-bottom: 0;
    height: 112px;
    overflow: hidden;
}
.fp-ground {
    position: absolute;
    left: 6%; right: 6%;
    bottom: 8px;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(255,140,42,0.28), transparent);
}
.fp-char-glow {
    position: absolute;
    width: 110px; height: 110px;
    top: 2px; left: 50%;
    transform: translateX(-50%);
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,140,42,0.22) 0%, transparent 70%);
    filter: blur(2px);
    animation: fpPulse 3s ease-in-out infinite;
    pointer-events: none;
}
@keyframes fpPulse {
    0%, 100% { transform: translateX(-50%) scale(1); opacity: 0.65; }
    50% { transform: translateX(-50%) scale(1.1); opacity: 1; }
}

.fp-walker {
    position: absolute;
    left: 50%;
    bottom: 4px;
    width: 100px; height: 100px;
    margin-left: -50px;
}
.fp-char {
    position: relative;
    width: 100px; height: 100px;
    filter: drop-shadow(0 8px 14px rgba(0,0,0,0.5));
    transform-origin: 50px 90px;
    animation: fpBreathe 3.6s ease-in-out infinite;
}
.fp-char.walking { animation: fpWalkBob 0.5s ease-in-out infinite; }
@keyframes fpBreathe {
    0%, 100% { transform: translateY(0) scaleY(1); }
    50% { transform: translateY(-2px) scaleY(1.01); }
}
@keyframes fpWalkBob {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-4px); }
}
.fp-char.bounce .fp-bouncegroup { animation: fpBounce 0.55s cubic-bezier(.36,1.5,.5,1); }
@keyframes fpBounce {
    0% { transform: translateY(0) scale(1); }
    30% { transform: translateY(-10px) scale(1.03); }
    55% { transform: translateY(2px) scale(0.99); }
    100% { transform: translateY(0) scale(1); }
}

.fp-headgroup { transform-origin: 50px 50px; transition: transform 0.45s ease; }
.fp-char.idle-look-left .fp-headgroup  { transform: rotate(-6deg) translateX(-3px); }
.fp-char.idle-look-right .fp-headgroup { transform: rotate(6deg) translateX(3px); }
.fp-char.idle-look-center .fp-headgroup{ transform: rotate(0deg); }
.fp-char.typing .fp-headgroup  { animation: fpHeadBob 0.5s ease-in-out infinite; }
.fp-char.walking .fp-headgroup { animation: fpHeadWalk 0.5s ease-in-out infinite; }
@keyframes fpHeadBob  { 0%, 100% { transform: rotate(-2deg); } 50% { transform: rotate(2deg) translateY(-1px); } }
@keyframes fpHeadWalk { 0%, 100% { transform: rotate(-3deg); } 50% { transform: rotate(3deg); } }

.fp-pupil  { transition: cx 0.12s ease-out, cy 0.12s ease-out; }
.fp-eyelid { transform-origin: center; transform: scaleY(0); transition: transform 0.08s ease; }
.fp-char.blinking .fp-eyelid { transform: scaleY(1); }

.fp-ear-l, .fp-ear-r { transform-origin: 50px 25px; }
.fp-char.idle-twitch .fp-ear-l { animation: fpEarTwitchL 0.4s ease; }
.fp-char.idle-twitch .fp-ear-r { animation: fpEarTwitchR 0.4s ease 0.08s; }
@keyframes fpEarTwitchL { 0%,100%{transform:rotate(0)} 50%{transform:rotate(-9deg)} }
@keyframes fpEarTwitchR { 0%,100%{transform:rotate(0)} 50%{transform:rotate(9deg)} }
.fp-char.typing  .fp-ear-l, .fp-char.walking .fp-ear-l { animation: fpEarPerk 0.5s ease-in-out infinite alternate; }
.fp-char.typing  .fp-ear-r, .fp-char.walking .fp-ear-r { animation: fpEarPerk 0.5s ease-in-out infinite alternate-reverse; }
@keyframes fpEarPerk { 0%{transform:rotate(-3deg)} 100%{transform:rotate(3deg)} }

.fp-arm-right { transform-origin: 59px 64px; transition: transform 0.35s cubic-bezier(.34,1.56,.64,1); }
.fp-arm-left  { transform-origin: 41px 64px; transition: transform 0.35s cubic-bezier(.34,1.56,.64,1); }
.fp-char.typing .fp-arm-left  { animation: fpArmTypeL 0.42s ease-in-out infinite; }
.fp-char.typing .fp-arm-right { animation: fpArmTypeR 0.42s ease-in-out infinite 0.21s; }
@keyframes fpArmTypeL { 0%,100%{transform:rotate(0) translateY(0)} 50%{transform:rotate(7deg) translateY(-2px)} }
@keyframes fpArmTypeR { 0%,100%{transform:rotate(0) translateY(0)} 50%{transform:rotate(-7deg) translateY(-2px)} }
.fp-char.walking .fp-arm-left  { animation: fpArmWalkL 0.5s ease-in-out infinite; }
.fp-char.walking .fp-arm-right { animation: fpArmWalkR 0.5s ease-in-out infinite; }
@keyframes fpArmWalkL { 0%,100%{transform:rotate(-22deg)} 50%{transform:rotate(22deg)} }
@keyframes fpArmWalkR { 0%,100%{transform:rotate(22deg)} 50%{transform:rotate(-22deg)} }

.fp-leg-left  { transform-origin: 44px 84px; }
.fp-leg-right { transform-origin: 56px 84px; }
.fp-char.walking .fp-leg-left  { animation: fpLegWalkL 0.5s ease-in-out infinite; }
.fp-char.walking .fp-leg-right { animation: fpLegWalkR 0.5s ease-in-out infinite; }
@keyframes fpLegWalkL { 0%,100%{transform:rotate(22deg)} 50%{transform:rotate(-22deg)} }
@keyframes fpLegWalkR { 0%,100%{transform:rotate(-22deg)} 50%{transform:rotate(22deg)} }

.fp-blush { opacity: 0; transition: opacity 0.3s ease; }
.fp-char.valid .fp-blush { opacity: 1; }
.fp-char.valid .fp-arm-right { transform: rotate(-65deg); animation: none; }
.fp-char.valid .fp-arm-left  { animation: none; }

.fp-spark { opacity: 0; transform: scale(0.4); transition: opacity 0.35s ease, transform 0.35s ease; }
.fp-char.valid .fp-spark { opacity: 1; transform: scale(1); }
.fp-spark1 { transition-delay: 0s; }
.fp-spark2 { transition-delay: 0.08s; }
.fp-spark3 { transition-delay: 0.16s; }

.fp-aura { opacity: 0.4; animation: fpAuraSpin 7s linear infinite; transform-origin: 50px 50px; }
@keyframes fpAuraSpin {
    0%   { transform: rotate(0deg) scale(1); opacity: 0.3; }
    50%  { transform: rotate(180deg) scale(1.04); opacity: 0.5; }
    100% { transform: rotate(360deg) scale(1); opacity: 0.3; }
}
.fp-char.valid   .fp-aura { animation-duration: 2.4s; }
.fp-char.typing  .fp-aura { animation-duration: 3.2s; opacity: 0.6; }
.fp-char.walking .fp-aura { animation-duration: 2.8s; opacity: 0.5; }

.fp-tail { transform-origin: 70px 75px; animation: fpTailSway 3.2s ease-in-out infinite; }
@keyframes fpTailSway { 0%,100%{transform:rotate(-4deg)} 50%{transform:rotate(6deg)} }
.fp-char.typing  .fp-tail,
.fp-char.valid   .fp-tail,
.fp-char.walking .fp-tail { animation: fpTailSwayFast 0.7s ease-in-out infinite; }
@keyframes fpTailSwayFast { 0%,100%{transform:rotate(-10deg)} 50%{transform:rotate(14deg)} }

.fp-walker.facing-left .fp-char { transform: scaleX(-1); }
.fp-walker.facing-left .fp-char.walking { animation: fpWalkBob 0.5s ease-in-out infinite; }

/* Text */
.fp-card h2 {
    position: relative; z-index: 2;
    font-size: 1.15rem; font-weight: 800;
    margin: 4px 0 3px;
    background: linear-gradient(135deg, #ffffff 30%, #ffc488 100%);
    -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;
}
.fp-sub {
    position: relative; z-index: 2;
    color: #b3a3ad; font-size: 0.75rem; line-height: 1.45; margin-bottom: 12px;
}

/* Form */
.fp-card .form-group { position: relative; z-index: 2; text-align: left; margin-bottom: 10px; }
.fp-float { position: relative; }

.fp-float svg.fp-mail-icon {
    position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
    width: 13px; height: 13px; stroke: #8a7783; fill: none; stroke-width: 1.8;
    pointer-events: none; transition: stroke 0.2s; z-index: 1;
}
.fp-float input[type="text"],
.fp-float input[type="email"],
.fp-float input[type="password"] {
    width: 100%;
    background: rgba(15,10,16,0.8);
    border: 1.5px solid rgba(255,255,255,0.09);
    border-radius: 9px;
    padding: 15px 32px 5px 32px;
    color: #fff; font-size: 0.8rem; outline: none;
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
}
.fp-float label.fp-flabel {
    position: absolute; left: 32px; top: 50%; transform: translateY(-50%);
    color: #8a7783; font-size: 0.78rem;
    pointer-events: none; transition: all 0.18s ease; z-index: 1;
}
.fp-float input:focus,
.fp-float input:not(:placeholder-shown) {
    border-color: #ff8c2a;
    box-shadow: 0 0 0 3px rgba(255,140,42,0.13);
    background: rgba(15,10,16,0.95);
}
.fp-float input:focus ~ label.fp-flabel,
.fp-float input:not(:placeholder-shown) ~ label.fp-flabel {
    top: 10px; font-size: 0.58rem; color: #ff8c2a; font-weight: 600;
}
.fp-float:focus-within svg.fp-mail-icon { stroke: #ff8c2a; }
.fp-float.is-invalid input { border-color: #e74c3c; box-shadow: 0 0 0 3px rgba(231,76,60,0.12); }

.fp-check {
    position: absolute; right: 10px; top: 50%;
    transform: translateY(-50%) scale(0.5) rotate(-20deg);
    width: 13px; height: 13px; stroke: #27ae60; fill: none; stroke-width: 2.6;
    opacity: 0; transition: opacity 0.25s ease, transform 0.25s cubic-bezier(.34,1.56,.64,1); z-index: 1;
}
.fp-float.is-valid .fp-check { opacity: 1; transform: translateY(-50%) scale(1) rotate(0deg); }

.fp-hint { display: block; min-height: 11px; margin-top: 3px; font-size: 0.63rem; color: #8a7783; }
.fp-hint.ok  { color: #6fe0a0; }
.fp-hint.bad { color: #ff8a7a; }

.fp-pwmeter { display: flex; gap: 3px; margin-top: 5px; }
.fp-pwmeter i {
    flex: 1; height: 2.5px; border-radius: 3px;
    background: rgba(255,255,255,0.1);
    transition: background .25s ease, box-shadow .25s ease;
}
.fp-pwmeter.l1 i:nth-child(1)    { background:#e74c3c; box-shadow:0 0 6px -1px #e74c3c; }
.fp-pwmeter.l2 i:nth-child(-n+2) { background:#ffb84d; box-shadow:0 0 6px -1px #ffb84d; }
.fp-pwmeter.l3 i:nth-child(-n+3) { background:#ff8c2a; box-shadow:0 0 6px -1px #ff8c2a; }
.fp-pwmeter.l4 i                 { background:#6fe0a0; box-shadow:0 0 6px -1px #6fe0a0; }

/* Terms checkbox */
.fp-terms {
    position: relative; z-index: 2;
    display: flex; align-items: flex-start; gap: 8px;
    text-align: left; margin: 12px 0 4px;
}
.fp-terms input[type="checkbox"] {
    margin-top: 2px; width: 15px; height: 15px; flex-shrink: 0;
    accent-color: #ff8c2a; cursor: pointer;
}
.fp-terms label {
    font-size: 0.7rem; color: #b3a3ad; line-height: 1.5; cursor: pointer;
}
.fp-terms a { color: #ff8c2a; font-weight: 600; text-decoration: none; }
.fp-terms a:hover { text-decoration: underline; }
.fp-terms.is-invalid label { color: #ff8a7a; }

/* reCAPTCHA wrap */
.fp-captcha-wrap {
    position: relative; z-index: 2;
    display: flex; justify-content: center;
    margin: 12px 0 4px;
    transform: scale(0.92);
    transform-origin: center;
}
@media (max-width: 340px) {
    .fp-captcha-wrap { transform: scale(0.8); }
}

/* Button */
.fp-btn {
    position: relative; z-index: 2;
    width: 100%; border: none; border-radius: 9px;
    padding: 10px; font-size: 0.85rem; font-weight: 700; color: #fff;
    background: linear-gradient(135deg, #ff8c2a 0%, #ef5b1c 60%, #d8430f 100%);
    cursor: pointer; box-shadow: 0 6px 16px rgba(255,140,42,0.33);
    transition: transform 0.15s, box-shadow 0.15s, filter 0.15s;
    overflow: hidden; isolation: isolate; margin-top: 4px;
}
.fp-btn::before {
    content: ""; position: absolute;
    top: 0; left: -120%; width: 60%; height: 100%;
    background: linear-gradient(120deg, transparent, rgba(255,255,255,0.3), transparent);
    transform: skewX(-20deg); transition: left 0.6s ease; z-index: -1;
}
.fp-btn:hover::before { left: 130%; }
.fp-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 22px rgba(255,140,42,0.46); filter: brightness(1.06); }
.fp-btn:active { transform: translateY(0); }
.fp-btn[disabled] { opacity: 0.75; cursor: not-allowed; transform: none !important; }
.fp-btn .fp-spinner {
    display: none; width: 12px; height: 12px;
    border: 2px solid rgba(255,255,255,0.4); border-top-color: #fff;
    border-radius: 50%; margin-right: 6px; vertical-align: -2px;
    animation: fpSpin 0.7s linear infinite;
}
.fp-btn.loading .fp-spinner { display: inline-block; }
@keyframes fpSpin { to { transform: rotate(360deg); } }

/* Footer */
.fp-foot { position: relative; z-index: 2; margin-top: 12px; font-size: 0.75rem; color: #b3a3ad; }
.fp-foot a { color: #ff8c2a; font-weight: 600; text-decoration: none; position: relative; }
.fp-foot a::after {
    content: ""; position: absolute; left: 0; bottom: -2px;
    width: 0; height: 1.5px; background: #ff8c2a; transition: width 0.2s ease;
}
.fp-foot a:hover::after { width: 100%; }

/* Alert */
.fp-alert {
    position: relative; z-index: 2; text-align: left;
    font-size: 0.75rem; border-radius: 8px;
    padding: 8px 10px; margin-bottom: 10px; line-height: 1.4;
    display: flex; align-items: flex-start; gap: 7px;
}
.fp-alert svg { width: 13px; height: 13px; flex-shrink: 0; margin-top: 1px; }
.fp-alert-error  { background: rgba(231,76,60,0.1);  border: 1px solid rgba(231,76,60,0.3);  color: #ff8a7a; }
.fp-alert-error  svg { stroke: #ff8a7a; fill: none; stroke-width: 2; }
.fp-alert-success{ background: rgba(39,174,96,0.1);  border: 1px solid rgba(39,174,96,0.3);  color: #6fe0a0; }
.fp-alert-success svg { stroke: #6fe0a0; fill: none; stroke-width: 2; }

/* Responsive */
@media (max-width: 400px) {
    .fp-wrap { padding: 10px 8px; }
    .fp-card { padding: 0 14px 16px; border-radius: 13px; }
    .fp-stage { height: 96px; }
    .fp-char-glow { width: 94px; height: 94px; }
    .fp-walker { width: 86px; height: 86px; margin-left: -43px; }
    .fp-char  { width: 86px; height: 86px; }
    .fp-card h2 { font-size: 1rem; }
    .fp-sub { font-size: 0.7rem; }
    .fp-float input { padding: 14px 30px 4px 30px; font-size: 0.76rem; }
    .fp-float svg.fp-mail-icon { width: 12px; height: 12px; }
    .fp-float label.fp-flabel { font-size: 0.74rem; }
    .fp-btn { padding: 9px; font-size: 0.8rem; }
    .fp-card .form-group { margin-bottom: 8px; }
}
@media (max-width: 320px) {
    .fp-card { padding: 0 10px 12px; }
    .fp-stage { height: 84px; }
    .fp-char-glow { width: 82px; height: 82px; }
    .fp-walker { width: 74px; height: 74px; margin-left: -37px; }
    .fp-char  { width: 74px; height: 74px; }
}
</style>

<div class="fp-wrap">
  <div class="fp-particles" id="fpParticles"></div>

  <div class="fp-card">

    <div class="fp-stage" id="fpStage">
      <div class="fp-char-glow"></div>
      <div class="fp-ground"></div>
      <div class="fp-walker" id="fpWalker">
        <svg id="fpChar" class="fp-char idle-look-center" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
          <g class="fp-bouncegroup">
          <ellipse cx="100" cy="184" rx="44" ry="7" fill="rgba(0,0,0,0.4)"/>

          <g class="fp-aura">
            <circle cx="100" cy="100" r="92" fill="none" stroke="#ff8c2a" stroke-width="1.4" stroke-dasharray="6 10" opacity="0.5"/>
            <circle cx="100" cy="100" r="80" fill="none" stroke="#ffb066" stroke-width="1" stroke-dasharray="3 8" opacity="0.4"/>
          </g>

          <g class="fp-spark fp-spark1">
            <path d="M22 54 l3.5 8.5 8.5 3.5 -8.5 3.5 -3.5 8.5 -3.5 -8.5 -8.5 -3.5 8.5 -3.5z" fill="#ffd76b"/>
          </g>
          <g class="fp-spark fp-spark2">
            <path d="M174 42 l2.5 6 6 2.5 -6 2.5 -2.5 6 -2.5 -6 -6 -2.5 6 -2.5z" fill="#ff9a4d"/>
          </g>
          <g class="fp-spark fp-spark3">
            <circle cx="160" cy="84" r="3.2" fill="#ffd76b"/>
          </g>

          <g class="fp-tail">
            <path d="M140 158 Q172 150 170 116 Q168 96 148 96 Q160 110 150 126 Q160 124 156 142 Q150 156 140 158Z" fill="url(#tailGrad)"/>
            <path d="M152 100 Q166 104 166 118" stroke="#fff3e6" stroke-width="5" fill="none" stroke-linecap="round" opacity="0.9"/>
          </g>

          <g class="fp-leg-right">
            <path d="M108 158 Q116 172 112 184" stroke="url(#legGrad)" stroke-width="15" fill="none" stroke-linecap="round"/>
            <ellipse cx="112" cy="186" rx="11" ry="6" fill="#241a1f"/>
          </g>
          <g class="fp-leg-left">
            <path d="M92 158 Q84 172 88 184" stroke="url(#legGrad)" stroke-width="15" fill="none" stroke-linecap="round"/>
            <ellipse cx="88" cy="186" rx="11" ry="6" fill="#241a1f"/>
          </g>

          <path d="M54 178 Q54 128 100 124 Q146 128 146 178 Z" fill="url(#bodyGrad)"/>
          <path d="M54 178 Q54 128 100 124 L100 178Z" fill="#1a1320" opacity="0.35"/>
          <path d="M82 128 Q100 138 118 128 L118 138 Q100 146 82 138 Z" fill="#7a2e0d" opacity="0.55"/>
          <path d="M78 130 q22 14 44 0 l-4 8 q-18 10 -36 0z" fill="#ffb066" opacity="0.85"/>

          <g class="fp-arm-left">
            <path d="M60 146 Q34 150 32 128" stroke="url(#armGrad)" stroke-width="15" fill="none" stroke-linecap="round"/>
            <circle cx="32" cy="127" r="9" fill="#f3c39c"/>
          </g>
          <g class="fp-arm-right">
            <path d="M140 146 Q166 150 168 128" stroke="url(#armGrad)" stroke-width="15" fill="none" stroke-linecap="round"/>
            <circle cx="168" cy="127" r="9" fill="#f3c39c"/>
          </g>

          <g class="fp-headgroup">
            <rect x="87" y="102" width="26" height="24" rx="9" fill="#e8b08a"/>
            <circle cx="100" cy="76" r="46" fill="url(#headGrad)"/>
            <g class="fp-ear-l">
              <path d="M58 48 Q44 8 78 32 Q66 38 64 56Z" fill="url(#earGrad)"/>
              <path d="M58 48 Q50 26 65 32Z" fill="#241a1f"/>
              <path d="M64 44 Q58 26 70 32Z" fill="#ffe2c2" opacity="0.7"/>
            </g>
            <g class="fp-ear-r">
              <path d="M142 48 Q156 8 122 32 Q134 38 136 56Z" fill="url(#earGrad)"/>
              <path d="M142 48 Q150 26 135 32Z" fill="#241a1f"/>
              <path d="M136 44 Q142 26 130 32Z" fill="#ffe2c2" opacity="0.7"/>
            </g>
            <path d="M54 74 Q48 28 100 24 Q152 28 146 74 Q140 46 100 46 Q60 46 54 74Z" fill="url(#hairGrad)"/>
            <path d="M62 50 Q76 36 100 35" stroke="#100a14" stroke-width="3" fill="none" stroke-linecap="round" opacity="0.6"/>
            <g stroke="#c9682a" stroke-width="2" stroke-linecap="round" opacity="0.85">
              <line x1="58" y1="82" x2="70" y2="80"/>
              <line x1="58" y1="88" x2="70" y2="88"/>
              <line x1="58" y1="94" x2="70" y2="96"/>
              <line x1="142" y1="82" x2="130" y2="80"/>
              <line x1="142" y1="88" x2="130" y2="88"/>
              <line x1="142" y1="94" x2="130" y2="96"/>
            </g>
            <g class="fp-blush">
              <ellipse cx="74" cy="86" rx="7.5" ry="4.5" fill="#ff9a4d" opacity="0.8"/>
              <ellipse cx="126" cy="86" rx="7.5" ry="4.5" fill="#ff9a4d" opacity="0.8"/>
            </g>
            <ellipse cx="82" cy="76" rx="9.5" ry="11" fill="#fff"/>
            <ellipse cx="118" cy="76" rx="9.5" ry="11" fill="#fff"/>
            <circle id="fpPupilL" class="fp-pupil" cx="82" cy="76" r="4.6" fill="#3a1d08"/>
            <circle id="fpPupilR" class="fp-pupil" cx="118" cy="76" r="4.6" fill="#3a1d08"/>
            <circle cx="84" cy="73" r="1.4" fill="#fff" opacity="0.9"/>
            <circle cx="120" cy="73" r="1.4" fill="#fff" opacity="0.9"/>
            <ellipse class="fp-eyelid" cx="82" cy="76" rx="10" ry="11.5" fill="#eeb088"/>
            <ellipse class="fp-eyelid" cx="118" cy="76" rx="10" ry="11.5" fill="#eeb088"/>
            <path d="M72 61 Q82 56 92 61" stroke="#1f1318" stroke-width="2.8" fill="none" stroke-linecap="round"/>
            <path d="M108 61 Q118 56 128 61" stroke="#1f1318" stroke-width="2.8" fill="none" stroke-linecap="round"/>
            <path id="fpMouth" d="M87 98 Q100 102 113 98" stroke="#a85a3a" stroke-width="3" fill="none" stroke-linecap="round"/>
          </g>

          <defs>
            <linearGradient id="bodyGrad" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stop-color="#ff9a3f"/><stop offset="100%" stop-color="#d8430f"/>
            </linearGradient>
            <linearGradient id="armGrad" x1="0" y1="0" x2="1" y2="0">
              <stop offset="0%" stop-color="#ff8c2a"/><stop offset="100%" stop-color="#ef5b1c"/>
            </linearGradient>
            <linearGradient id="legGrad" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stop-color="#ef5b1c"/><stop offset="100%" stop-color="#c8430f"/>
            </linearGradient>
            <linearGradient id="headGrad" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stop-color="#fad2ab"/><stop offset="100%" stop-color="#eeb088"/>
            </linearGradient>
            <linearGradient id="hairGrad" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stop-color="#2a1c24"/><stop offset="100%" stop-color="#150c14"/>
            </linearGradient>
            <linearGradient id="earGrad" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stop-color="#ff9a3f"/><stop offset="100%" stop-color="#e0631f"/>
            </linearGradient>
            <linearGradient id="tailGrad" x1="0" y1="0" x2="1" y2="1">
              <stop offset="0%" stop-color="#ff9a3f"/><stop offset="100%" stop-color="#c84f17"/>
            </linearGradient>
          </defs>
          </g>
        </svg>
      </div>
    </div>

    <h2>Buat Akun</h2>
    <p class="fp-sub">Daftar untuk membuat custom card Telegram-mu</p>

    <?php if ($flashError): ?>
      <div class="fp-alert fp-alert-error">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="13"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span><?= clean($flashError) ?></span>
      </div>
    <?php endif; ?>
    <?php if ($flashSuccess): ?>
      <div class="fp-alert fp-alert-success">
        <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        <span><?= clean($flashSuccess) ?></span>
      </div>
    <?php endif; ?>

    <form method="post" id="fpForm" autocomplete="off" novalidate>
      <div class="form-group">
        <div class="fp-float" id="fpUsernameWrap">
          <svg class="fp-mail-icon" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          <input type="text" name="username" id="fpUsername" placeholder=" " required minlength="3">
          <label class="fp-flabel" for="fpUsername">Username</label>
          <svg class="fp-check" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <small class="fp-hint" id="fpUsernameHint">Minimal 3 karakter, tanpa spasi</small>
      </div>

      <div class="form-group">
        <div class="fp-float" id="fpEmailWrap">
          <svg class="fp-mail-icon" viewBox="0 0 24 24"><path d="M4 6h16v12H4z"/><path d="M4 7l8 6 8-6"/></svg>
          <input type="email" name="email" id="fpEmail" placeholder=" " required>
          <label class="fp-flabel" for="fpEmail">Alamat Email</label>
          <svg class="fp-check" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <small class="fp-hint" id="fpEmailHint">Contoh: nama@email.com</small>
      </div>

      <div class="form-group">
        <div class="fp-float" id="fpPasswordWrap">
          <svg class="fp-mail-icon" viewBox="0 0 24 24"><rect x="4" y="11" width="16" height="9" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
          <input type="password" name="password" id="fpPassword" placeholder=" " required minlength="6">
          <label class="fp-flabel" for="fpPassword">Password</label>
          <svg class="fp-check" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <div class="fp-pwmeter" id="fpPwMeter"><i></i><i></i><i></i><i></i></div>
        <small class="fp-hint" id="fpPasswordHint">Minimal 6 karakter</small>
      </div>

      <div class="form-group">
        <div class="fp-float" id="fpConfirmWrap">
          <svg class="fp-mail-icon" viewBox="0 0 24 24"><rect x="4" y="11" width="16" height="9" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
          <input type="password" name="confirm_password" id="fpConfirm" placeholder=" " required minlength="6">
          <label class="fp-flabel" for="fpConfirm">Konfirmasi Password</label>
          <svg class="fp-check" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <small class="fp-hint" id="fpConfirmHint">Harus sama dengan password di atas</small>
      </div>

      <div class="fp-terms" id="fpTermsWrap">
        <input type="checkbox" name="terms" id="fpTerms" required>
        <label for="fpTerms">
          Saya menyetujui <a href="terms.php" target="_blank" rel="noopener">Syarat &amp; Ketentuan</a>
          dan <a href="privacy.php" target="_blank" rel="noopener">Kebijakan Privasi</a> TeleCard.
        </label>
      </div>

      <div class="fp-captcha-wrap">
        <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars(RECAPTCHA_SITE_KEY) ?>"></div>
      </div>

      <button type="submit" class="fp-btn" id="fpBtn">
        <span class="fp-spinner"></span>
        <span class="fp-btn-text">Daftar</span>
      </button>
    </form>

    <div class="fp-foot">Sudah punya akun? <a href="login.php">Login di sini</a></div>
  </div>
</div>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
(function () {
    var uName  = document.getElementById('fpUsername');
    var uEmail = document.getElementById('fpEmail');
    var uPass  = document.getElementById('fpPassword');
    var uConf  = document.getElementById('fpConfirm');
    var uTerms = document.getElementById('fpTerms');
    var char   = document.getElementById('fpChar');
    var walker = document.getElementById('fpWalker');
    var stage  = document.getElementById('fpStage');
    var pupilL = document.getElementById('fpPupilL');
    var pupilR = document.getElementById('fpPupilR');
    var mouth  = document.getElementById('fpMouth');
    var form   = document.getElementById('fpForm');
    var btn    = document.getElementById('fpBtn');
    var pwMeter= document.getElementById('fpPwMeter');
    var pwHint = document.getElementById('fpPasswordHint');
    var termsWrap = document.getElementById('fpTermsWrap');

    var basePupil = { l: 82, r: 118 };
    var baseCy = 76, maxShift = 3.8;
    var isTyping = false, isWalking = false;
    var typingTimeout = null, idleTimers = [], currentX = 0;

    function emailRegex(v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); }

    function moveEyes(val) {
        var shift = (Math.min(val.length / 18, 1) * maxShift * 2) - maxShift;
        pupilL.setAttribute('cx', basePupil.l + shift);
        pupilR.setAttribute('cx', basePupil.r + shift);
        pupilL.setAttribute('cy', baseCy);
        pupilR.setAttribute('cy', baseCy);
    }
    function resetEyes() {
        pupilL.setAttribute('cx', basePupil.l); pupilR.setAttribute('cx', basePupil.r);
        pupilL.setAttribute('cy', baseCy);      pupilR.setAttribute('cy', baseCy);
    }

    function setFieldState(wrapId, hintId, ok, okText, badText, neutralText) {
        var wrap = document.getElementById(wrapId);
        var hint = hintId ? document.getElementById(hintId) : null;
        wrap.classList.remove('is-valid', 'is-invalid');
        if (hint) hint.classList.remove('ok', 'bad');
        if (ok === null) { if (hint) hint.textContent = neutralText; return; }
        if (ok) {
            wrap.classList.add('is-valid');
            if (hint) { hint.textContent = okText; hint.classList.add('ok'); }
        } else {
            wrap.classList.add('is-invalid');
            if (hint) { hint.textContent = badText; hint.classList.add('bad'); }
        }
    }

    function validateUsername() {
        var v = uName.value.trim();
        setFieldState('fpUsernameWrap','fpUsernameHint',
            v.length === 0 ? null : (v.length >= 3 && !/\s/.test(v)),
            'Username oke!', 'Minimal 3 karakter & tanpa spasi', 'Minimal 3 karakter, tanpa spasi');
    }
    function validateEmail() {
        var v = uEmail.value.trim();
        setFieldState('fpEmailWrap','fpEmailHint',
            v.length === 0 ? null : emailRegex(v),
            'Email valid!', 'Format email tidak valid', 'Contoh: nama@email.com');
    }
    function passwordScore(v) {
        var s = 0;
        if (v.length >= 6) s++;
        if (v.length >= 10) s++;
        if (/[A-Z]/.test(v) && /[a-z]/.test(v)) s++;
        if (/[0-9]/.test(v) && /[^A-Za-z0-9]/.test(v)) s++;
        return s;
    }
    function validatePassword() {
        var v = uPass.value;
        setFieldState('fpPasswordWrap', null, v.length === 0 ? null : v.length >= 6, '', '', '');
        var score = v.length === 0 ? 0 : Math.max(1, passwordScore(v));
        pwMeter.classList.remove('l1','l2','l3','l4');
        if (v.length > 0) pwMeter.classList.add('l' + score);
        pwHint.classList.remove('ok','bad');
        if (v.length === 0)       pwHint.textContent = 'Minimal 6 karakter';
        else if (v.length < 6)  { pwHint.textContent = 'Kurang ' + (6-v.length) + ' karakter lagi'; pwHint.classList.add('bad'); }
        else if (score <= 1)      pwHint.textContent = 'Lemah — tambah huruf besar & angka';
        else if (score === 2)     pwHint.textContent = 'Cukup — bisa lebih kuat';
        else if (score === 3)   { pwHint.textContent = 'Kuat 👍'; pwHint.classList.add('ok'); }
        else                    { pwHint.textContent = 'Sangat kuat 🔒'; pwHint.classList.add('ok'); }
        validateConfirm();
    }
    function validateConfirm() {
        var v = uConf.value;
        setFieldState('fpConfirmWrap','fpConfirmHint',
            v.length === 0 ? null : (v === uPass.value && v.length >= 6),
            'Password cocok!', 'Konfirmasi tidak cocok', 'Harus sama dengan password di atas');
    }

    function setValidState(isValid) {
        if (isValid) {
            char.classList.add('valid');
            mouth.setAttribute('d', 'M84 96 Q100 112 116 96');
            char.classList.remove('bounce'); void char.offsetWidth; char.classList.add('bounce');
        } else {
            char.classList.remove('valid');
            mouth.setAttribute('d', 'M87 98 Q100 102 113 98');
        }
    }
    function allValid() {
        return uName.value.trim().length >= 3 && !/\s/.test(uName.value.trim()) &&
               emailRegex(uEmail.value.trim()) &&
               uPass.value.length >= 6 && uConf.value === uPass.value && uConf.value.length >= 6;
    }

    function enterTypingMode() {
        if (isWalking || isTyping) return;
        isTyping = true;
        char.classList.add('typing');
        char.classList.remove('idle-look-left','idle-look-right','idle-look-center','idle-twitch');
        stopIdleCycle();
    }
    function exitTypingMode() {
        isTyping = false;
        char.classList.remove('typing');
        char.classList.add('idle-look-center');
        resetEyes(); startIdleCycle();
    }

    function walkTo(targetX, durationMs, callback) {
        isWalking = true; char.classList.add('walking');
        var fromX = currentX, distance = targetX - fromX;
        if (distance < 0) walker.classList.add('facing-left');
        else walker.classList.remove('facing-left');
        var startTime = null;
        function step(ts) {
            if (!startTime) startTime = ts;
            var p = Math.min((ts - startTime) / durationMs, 1);
            var e = p < 0.5 ? 2*p*p : 1 - Math.pow(-2*p+2,2)/2;
            currentX = fromX + distance * e;
            walker.style.transform = 'translateX(' + currentX + 'px)';
            if (p < 1 && isWalking) { requestAnimationFrame(step); }
            else {
                currentX = targetX;
                walker.style.transform = 'translateX(' + currentX + 'px)';
                char.classList.remove('walking'); isWalking = false;
                if (callback) callback();
            }
        }
        requestAnimationFrame(step);
    }
    function maxWalkRange() { return Math.max(Math.min(stage.offsetWidth / 2 - 70, 50), 15); }

    function clearIdleTimers() { idleTimers.forEach(function(t){clearTimeout(t);}); idleTimers=[]; }

    function scheduleBlink() {
        var t = setTimeout(function() {
            if (!isTyping && !isWalking) {
                char.classList.add('blinking');
                var t2 = setTimeout(function(){char.classList.remove('blinking');}, 140);
                idleTimers.push(t2);
            }
            scheduleBlink();
        }, 2200 + Math.random() * 3200);
        idleTimers.push(t);
    }
    function scheduleLook() {
        var t = setTimeout(function() {
            if (!isTyping && !isWalking) {
                var dirs = ['idle-look-left','idle-look-right','idle-look-center'];
                var pick = dirs[Math.floor(Math.random() * 3)];
                char.classList.remove('idle-look-left','idle-look-right','idle-look-center');
                char.classList.add(pick);
                var shift = pick === 'idle-look-left' ? -2.2 : pick === 'idle-look-right' ? 2.2 : 0;
                pupilL.setAttribute('cx', basePupil.l + shift);
                pupilR.setAttribute('cx', basePupil.r + shift);
            }
            scheduleLook();
        }, 2600 + Math.random() * 3000);
        idleTimers.push(t);
    }
    function scheduleEarTwitch() {
        var t = setTimeout(function() {
            if (!isTyping && !isWalking) {
                char.classList.add('idle-twitch');
                var t2 = setTimeout(function(){char.classList.remove('idle-twitch');}, 450);
                idleTimers.push(t2);
            }
            scheduleEarTwitch();
        }, 4000 + Math.random() * 5000);
        idleTimers.push(t);
    }
    function scheduleWalk() {
        var t = setTimeout(function() {
            if (!isTyping && !isWalking) {
                var range = maxWalkRange();
                var target = (Math.random() < 0.5 ? -1 : 1) * (range * (0.4 + Math.random() * 0.6));
                stopIdleCycle();
                walkTo(target, 1200 + Math.random() * 600, function() { startIdleCycle(); scheduleWalk(); });
                return;
            }
            scheduleWalk();
        }, 6000 + Math.random() * 6000);
        idleTimers.push(t);
    }
    function startIdleCycle() { clearIdleTimers(); scheduleBlink(); scheduleLook(); scheduleEarTwitch(); scheduleWalk(); }
    function stopIdleCycle()  { clearIdleTimers(); char.classList.remove('blinking','idle-twitch'); }

    function bindField(input, validateFn) {
        input.addEventListener('input', function() {
            enterTypingMode(); moveEyes(this.value); validateFn(); setValidState(allValid());
            clearTimeout(typingTimeout); typingTimeout = setTimeout(exitTypingMode, 900);
        });
        input.addEventListener('focus', function() { enterTypingMode(); moveEyes(this.value); });
        input.addEventListener('blur',  function() { clearTimeout(typingTimeout); exitTypingMode(); validateFn(); });
    }

    bindField(uName, validateUsername);
    bindField(uEmail, validateEmail);
    bindField(uPass, validatePassword);
    bindField(uConf, validateConfirm);

    uTerms.addEventListener('change', function() {
        termsWrap.classList.toggle('is-invalid', !this.checked);
    });

    form.addEventListener('submit', function(e) {
        validateUsername(); validateEmail(); validatePassword(); validateConfirm();

        var termsOk = uTerms.checked;
        termsWrap.classList.toggle('is-invalid', !termsOk);

        var captchaOk = true;
        if (typeof grecaptcha !== 'undefined') {
            captchaOk = grecaptcha.getResponse().length > 0;
        }

        if (!allValid() || !termsOk || !captchaOk) {
            e.preventDefault();
            if (!allValid()) {
                var fi = document.querySelector('.fp-float.is-invalid input, .fp-float input:invalid');
                if (fi) { fi.focus(); return; }
            }
            if (!termsOk) { termsWrap.scrollIntoView({behavior:'smooth', block:'center'}); return; }
            if (!captchaOk) { alert('Silakan verifikasi reCAPTCHA terlebih dahulu.'); return; }
            return;
        }
        btn.classList.add('loading');
        btn.setAttribute('disabled','disabled');
        btn.querySelector('.fp-btn-text').textContent = 'Mendaftarkan...';
    });

    startIdleCycle();

    var container = document.getElementById('fpParticles');
    for (var i = 0; i < 7; i++) {
        var p = document.createElement('div');
        p.className = 'fp-particle';
        var sz = 2 + Math.random() * 7;
        p.style.cssText = 'width:'+sz+'px;height:'+sz+'px;left:'+(Math.random()*100)+'%;bottom:-10px;'
            + 'animation-duration:'+(7+Math.random()*9)+'s;animation-delay:'+(Math.random()*10)+'s;';
        container.appendChild(p);
    }

    document.addEventListener('visibilitychange', function() {
        if (document.hidden) stopIdleCycle(); else startIdleCycle();
    });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
