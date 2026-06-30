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

$flashError   = flash('error');
$flashSuccess = flash('success');
?>
<style>
.fp-wrap {
    position: relative;
    min-height: 78vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    overflow: hidden;
}

/* floating particles in background */
.fp-particles {
    position: absolute;
    inset: 0;
    overflow: hidden;
    pointer-events: none;
    z-index: 0;
}
.fp-particle {
    position: absolute;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,140,42,0.55) 0%, rgba(255,140,42,0) 70%);
    animation: fpFloat linear infinite;
}
@keyframes fpFloat {
    0%   { transform: translateY(0) translateX(0); opacity: 0; }
    10%  { opacity: 1; }
    90%  { opacity: 1; }
    100% { transform: translateY(-620px) translateX(40px); opacity: 0; }
}

.fp-card {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 480px;
    background: linear-gradient(160deg, #1f1626 0%, #150e1c 55%, #0c0810 100%);
    border: 1px solid rgba(255, 140, 42, 0.25);
    border-radius: 24px;
    padding: 0 38px 40px;
    text-align: center;
    box-shadow:
        0 0 0 1px rgba(255, 140, 42, 0.06),
        0 25px 70px rgba(0, 0, 0, 0.5),
        0 0 100px rgba(255, 140, 42, 0.12);
    overflow: hidden;
}
.fp-card::before {
    content: "";
    position: absolute;
    top: -50%;
    left: -10%;
    width: 130%;
    height: 130%;
    background:
        radial-gradient(circle at 20% 20%, rgba(255, 140, 42, 0.20) 0%, transparent 45%),
        radial-gradient(circle at 80% 30%, rgba(255, 70, 30, 0.15) 0%, transparent 45%);
    pointer-events: none;
    animation: fpMesh 9s ease-in-out infinite;
    z-index: 0;
}
@keyframes fpMesh {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(18px) rotate(4deg); }
}
.fp-card::after {
    content: "";
    position: absolute;
    inset: 0;
    border-radius: 24px;
    padding: 1px;
    background: linear-gradient(135deg, rgba(255,140,42,0.4), transparent 40%, transparent 60%, rgba(255,70,30,0.3));
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    pointer-events: none;
}

/* Character stage - now a "ground" the character can walk across */
.fp-stage {
    position: relative;
    z-index: 2;
    padding-top: 34px;
    margin-bottom: 4px;
    height: 220px;
    overflow: hidden;
}
.fp-ground {
    position: absolute;
    left: 6%;
    right: 6%;
    bottom: 14px;
    height: 2px;
    background: linear-gradient(90deg, transparent, rgba(255,140,42,0.35), transparent);
}
.fp-char-glow {
    position: absolute;
    width: 220px;
    height: 220px;
    top: 6px;
    left: 50%;
    transform: translateX(-50%);
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,140,42,0.30) 0%, transparent 70%);
    filter: blur(2px);
    animation: fpPulse 3s ease-in-out infinite;
    pointer-events: none;
}
@keyframes fpPulse {
    0%, 100% { transform: translateX(-50%) scale(1); opacity: 0.7; }
    50% { transform: translateX(-50%) scale(1.12); opacity: 1; }
}

/* === Walker: positions the character horizontally on the stage and walks it === */
.fp-walker {
    position: absolute;
    left: 50%;
    bottom: 8px;
    width: 200px;
    height: 200px;
    margin-left: -100px;
    transition: none;
}
.fp-walker.walking {
    animation: none; /* movement is driven by JS inline transform for variable distances */
}

/* === Base character: always-on idle "breathing" loop === */
.fp-char {
    position: relative;
    width: 200px;
    height: 200px;
    filter: drop-shadow(0 14px 22px rgba(0,0,0,0.5));
    transform-origin: 100px 180px;
    animation: fpBreathe 3.6s ease-in-out infinite;
}
.fp-char.walking { animation: fpWalkBob 0.5s ease-in-out infinite; }
@keyframes fpBreathe {
    0%, 100% { transform: translateY(0) scaleY(1); }
    50% { transform: translateY(-3px) scaleY(1.015); }
}
@keyframes fpWalkBob {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-6px); }
}

/* Bounce burst on success (overrides breathing briefly via separate wrapper) */
.fp-char.bounce .fp-bouncegroup { animation: fpBounce 0.55s cubic-bezier(.36,1.5,.5,1); }
@keyframes fpBounce {
    0% { transform: translateY(0) scale(1); }
    30% { transform: translateY(-14px) scale(1.04); }
    55% { transform: translateY(3px) scale(0.99); }
    100% { transform: translateY(0) scale(1); }
}

/* Head group: idle look-around + tilt */
.fp-headgroup {
    transform-origin: 100px 100px;
    transition: transform 0.45s ease;
}
.fp-char.idle-look-left .fp-headgroup  { transform: rotate(-6deg) translateX(-4px); }
.fp-char.idle-look-right .fp-headgroup { transform: rotate(6deg) translateX(4px); }
.fp-char.idle-look-center .fp-headgroup{ transform: rotate(0deg) translateX(0); }
.fp-char.typing .fp-headgroup {
    animation: fpHeadBob 0.5s ease-in-out infinite;
}
.fp-char.walking .fp-headgroup {
    animation: fpHeadWalk 0.5s ease-in-out infinite;
}
@keyframes fpHeadBob {
    0%, 100% { transform: rotate(-2.5deg) translateY(0); }
    50% { transform: rotate(2.5deg) translateY(-1.5px); }
}
@keyframes fpHeadWalk {
    0%, 100% { transform: rotate(-3deg); }
    50% { transform: rotate(3deg); }
}

.fp-pupil { transition: transform 0.12s ease-out, cy 0.12s ease-out; }
.fp-mouth { transition: d 0.3s ease; }

/* Ears: idle twitch */
.fp-ear-l, .fp-ear-r {
    transform-origin: 100px 50px;
}
.fp-char.idle-twitch .fp-ear-l { animation: fpEarTwitchL 0.4s ease; }
.fp-char.idle-twitch .fp-ear-r { animation: fpEarTwitchR 0.4s ease 0.08s; }
@keyframes fpEarTwitchL { 0%,100%{transform:rotate(0)} 50%{transform:rotate(-9deg)} }
@keyframes fpEarTwitchR { 0%,100%{transform:rotate(0)} 50%{transform:rotate(9deg)} }
.fp-char.typing .fp-ear-l { animation: fpEarPerk 0.5s ease-in-out infinite alternate; }
.fp-char.typing .fp-ear-r { animation: fpEarPerk 0.5s ease-in-out infinite alternate-reverse; }
.fp-char.walking .fp-ear-l { animation: fpEarPerk 0.4s ease-in-out infinite alternate; }
.fp-char.walking .fp-ear-r { animation: fpEarPerk 0.4s ease-in-out infinite alternate-reverse; }
@keyframes fpEarPerk { 0%{transform:rotate(-3deg) scale(1)} 100%{transform:rotate(3deg) scale(1.03)} }

.fp-arm-right { transform-origin: 118px 128px; transition: transform 0.35s cubic-bezier(.34,1.56,.64,1); }
.fp-arm-left  { transform-origin: 82px 128px; transition: transform 0.35s cubic-bezier(.34,1.56,.64,1); }

/* Typing state: both arms do a tiny alternating "typing" wiggle */
.fp-char.typing .fp-arm-left  { animation: fpArmTypeL 0.42s ease-in-out infinite; }
.fp-char.typing .fp-arm-right { animation: fpArmTypeR 0.42s ease-in-out infinite 0.21s; }
@keyframes fpArmTypeL {
    0%, 100% { transform: rotate(0deg) translateY(0); }
    50% { transform: rotate(7deg) translateY(-3px); }
}
@keyframes fpArmTypeR {
    0%, 100% { transform: rotate(0deg) translateY(0); }
    50% { transform: rotate(-7deg) translateY(-3px); }
}

/* Walking arm swing (opposite to legs, natural gait) */
.fp-char.walking .fp-arm-left  { animation: fpArmWalkL 0.5s ease-in-out infinite; }
.fp-char.walking .fp-arm-right { animation: fpArmWalkR 0.5s ease-in-out infinite; }
@keyframes fpArmWalkL {
    0%, 100% { transform: rotate(-22deg); }
    50% { transform: rotate(22deg); }
}
@keyframes fpArmWalkR {
    0%, 100% { transform: rotate(22deg); }
    50% { transform: rotate(-22deg); }
}

/* Legs: new! used for walking + idle standing */
.fp-leg-left, .fp-leg-right {
    transform-origin: 90px 168px;
}
.fp-leg-right { transform-origin: 110px 168px; }
.fp-char.walking .fp-leg-left  { animation: fpLegWalkL 0.5s ease-in-out infinite; }
.fp-char.walking .fp-leg-right { animation: fpLegWalkR 0.5s ease-in-out infinite; }
@keyframes fpLegWalkL {
    0%, 100% { transform: rotate(24deg); }
    50% { transform: rotate(-24deg); }
}
@keyframes fpLegWalkR {
    0%, 100% { transform: rotate(-24deg); }
    50% { transform: rotate(24deg); }
}

.fp-blush { opacity: 0; transition: opacity 0.3s ease; }
.fp-char.valid .fp-blush { opacity: 1; }
.fp-char.valid .fp-arm-right { transform: rotate(-65deg); animation: none; }
.fp-char.valid .fp-arm-left  { animation: none; }

.fp-spark { opacity: 0; transform: scale(0.4); transition: opacity 0.35s ease, transform 0.35s ease; }
.fp-char.valid .fp-spark { opacity: 1; transform: scale(1); }
.fp-spark1 { transition-delay: 0s; }
.fp-spark2 { transition-delay: 0.08s; }
.fp-spark3 { transition-delay: 0.16s; }

.fp-aura { opacity: 0.45; animation: fpAuraSpin 7s linear infinite; transform-origin: 100px 100px; }
@keyframes fpAuraSpin {
    0% { transform: rotate(0deg) scale(1); opacity: 0.35; }
    50% { transform: rotate(180deg) scale(1.05); opacity: 0.55; }
    100% { transform: rotate(360deg) scale(1); opacity: 0.35; }
}
.fp-char.valid .fp-aura { animation-duration: 2.4s; }
.fp-char.typing .fp-aura { animation-duration: 3.2s; opacity: 0.65; }
.fp-char.walking .fp-aura { animation-duration: 2.8s; opacity: 0.55; }

/* Tail: idle sway always, faster/wider while typing (excited) */
.fp-tail { transform-origin: 140px 150px; animation: fpTailSway 3.2s ease-in-out infinite; }
@keyframes fpTailSway {
    0%, 100% { transform: rotate(-4deg); }
    50% { transform: rotate(6deg); }
}
.fp-char.typing .fp-tail { animation: fpTailSwayFast 0.9s ease-in-out infinite; }
@keyframes fpTailSwayFast {
    0%, 100% { transform: rotate(-10deg) scale(1); }
    50% { transform: rotate(14deg) scale(1.02); }
}
.fp-char.valid .fp-tail { animation: fpTailSwayFast 0.6s ease-in-out infinite; }
.fp-char.walking .fp-tail { animation: fpTailSwayFast 0.5s ease-in-out infinite; }

/* eyelids for blinking */
.fp-eyelid { transform-origin: center; transform: scaleY(0); transition: transform 0.08s ease; }
.fp-char.blinking .fp-eyelid { transform: scaleY(1); }

/* flip character horizontally when walking left */
.fp-walker.facing-left .fp-char { transform: scaleX(-1); }
.fp-walker.facing-left .fp-char.walking { animation: fpWalkBob 0.5s ease-in-out infinite; }

.fp-card h2 {
    position: relative;
    z-index: 2;
    font-size: 1.7rem;
    font-weight: 800;
    color: #fff;
    margin: 8px 0 8px;
    letter-spacing: 0.3px;
    background: linear-gradient(135deg, #ffffff 30%, #ffc488 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}
.fp-card .fp-sub {
    position: relative;
    z-index: 2;
    color: #b3a3ad;
    font-size: 0.93rem;
    line-height: 1.55;
    margin-bottom: 30px;
}

.fp-card .form-group {
    position: relative;
    z-index: 2;
    text-align: left;
    margin-bottom: 26px;
}
.fp-float {
    position: relative;
}
.fp-float svg.fp-mail-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    width: 19px;
    height: 19px;
    stroke: #8a7783;
    fill: none;
    stroke-width: 1.8;
    pointer-events: none;
    transition: stroke 0.2s;
    z-index: 1;
}
.fp-float input[type="email"] {
    width: 100%;
    background: rgba(15, 10, 16, 0.8);
    border: 1.5px solid rgba(255, 255, 255, 0.09);
    border-radius: 14px;
    padding: 22px 44px 10px 46px;
    color: #fff;
    font-size: 0.97rem;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
    position: relative;
}
.fp-float label.fp-flabel {
    position: absolute;
    left: 46px;
    top: 50%;
    transform: translateY(-50%);
    color: #8a7783;
    font-size: 0.95rem;
    pointer-events: none;
    transition: all 0.18s ease;
    z-index: 1;
}
.fp-float input[type="email"]:focus,
.fp-float input[type="email"]:not(:placeholder-shown) {
    border-color: #ff8c2a;
    box-shadow: 0 0 0 4px rgba(255, 140, 42, 0.16);
    background: rgba(15, 10, 16, 0.95);
}
.fp-float input[type="email"]:focus ~ label.fp-flabel,
.fp-float input[type="email"]:not(:placeholder-shown) ~ label.fp-flabel {
    top: 17px;
    font-size: 0.7rem;
    color: #ff8c2a;
    font-weight: 600;
}
.fp-float:focus-within svg.fp-mail-icon { stroke: #ff8c2a; }

.fp-check {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%) scale(0.5) rotate(-20deg);
    width: 19px;
    height: 19px;
    stroke: #27ae60;
    fill: none;
    stroke-width: 2.6;
    opacity: 0;
    transition: opacity 0.25s ease, transform 0.25s cubic-bezier(.34,1.56,.64,1);
    z-index: 1;
}
.fp-float.is-valid .fp-check { opacity: 1; transform: translateY(-50%) scale(1) rotate(0deg); }

.fp-btn {
    position: relative;
    z-index: 2;
    width: 100%;
    border: none;
    border-radius: 14px;
    padding: 15px;
    font-size: 1.02rem;
    font-weight: 700;
    color: #fff;
    background: linear-gradient(135deg, #ff8c2a 0%, #ef5b1c 60%, #d8430f 100%);
    cursor: pointer;
    box-shadow: 0 10px 24px rgba(255, 140, 42, 0.38);
    transition: transform 0.15s, box-shadow 0.15s, filter 0.15s;
    overflow: hidden;
    isolation: isolate;
}
.fp-btn::before {
    content: "";
    position: absolute;
    top: 0;
    left: -120%;
    width: 60%;
    height: 100%;
    background: linear-gradient(120deg, transparent, rgba(255,255,255,0.35), transparent);
    transform: skewX(-20deg);
    transition: left 0.6s ease;
    z-index: -1;
}
.fp-btn:hover::before { left: 130%; }
.fp-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 30px rgba(255, 140, 42, 0.5);
    filter: brightness(1.06);
}
.fp-btn:active { transform: translateY(0); }
.fp-btn[disabled] {
    opacity: 0.75;
    cursor: not-allowed;
    transform: none !important;
}
.fp-btn .fp-spinner {
    display: none;
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255,255,255,0.4);
    border-top-color: #fff;
    border-radius: 50%;
    margin-right: 8px;
    vertical-align: -3px;
    animation: fpSpin 0.7s linear infinite;
}
.fp-btn.loading .fp-spinner { display: inline-block; }
@keyframes fpSpin { to { transform: rotate(360deg); } }

.fp-foot {
    position: relative;
    z-index: 2;
    margin-top: 24px;
    font-size: 0.9rem;
    color: #b3a3ad;
}
.fp-foot a {
    color: #ff8c2a;
    font-weight: 600;
    text-decoration: none;
    position: relative;
}
.fp-foot a::after {
    content: "";
    position: absolute;
    left: 0; bottom: -2px;
    width: 0; height: 1.5px;
    background: #ff8c2a;
    transition: width 0.2s ease;
}
.fp-foot a:hover::after { width: 100%; }

.fp-alert {
    position: relative;
    z-index: 2;
    text-align: left;
    font-size: 0.88rem;
    border-radius: 12px;
    padding: 13px 15px;
    margin-bottom: 22px;
    line-height: 1.45;
    display: flex;
    align-items: flex-start;
    gap: 10px;
}
.fp-alert svg { width: 18px; height: 18px; flex-shrink: 0; margin-top: 1px; }
.fp-alert-error {
    background: rgba(231, 76, 60, 0.12);
    border: 1px solid rgba(231, 76, 60, 0.35);
    color: #ff8a7a;
}
.fp-alert-error svg { stroke: #ff8a7a; fill: none; stroke-width: 2; }
.fp-alert-success {
    background: rgba(39, 174, 96, 0.12);
    border: 1px solid rgba(39, 174, 96, 0.35);
    color: #6fe0a0;
}
.fp-alert-success svg { stroke: #6fe0a0; fill: none; stroke-width: 2; }

/* ===== Responsive ===== */
@media (max-width: 560px) {
    .fp-wrap { padding: 36px 12px; min-height: auto; }
    .fp-card { max-width: 100%; padding: 0 22px 30px; border-radius: 18px; }
    .fp-stage { height: 170px; }
    .fp-char-glow { width: 170px; height: 170px; }
    .fp-walker { width: 150px; height: 150px; margin-left: -75px; }
    .fp-char { width: 150px; height: 150px; }
    .fp-card h2 { font-size: 1.4rem; }
    .fp-card .fp-sub { font-size: 0.86rem; margin-bottom: 22px; }
    .fp-float input[type="email"] { padding: 20px 40px 9px 42px; font-size: 0.92rem; }
    .fp-btn { padding: 13px; font-size: 0.96rem; }
}
@media (max-width: 380px) {
    .fp-char-glow { width: 140px; height: 140px; }
    .fp-walker { width: 128px; height: 128px; margin-left: -64px; }
    .fp-char { width: 128px; height: 128px; }
    .fp-card { padding: 0 16px 24px; }
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

          <!-- rotating energy aura rings -->
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

          <!-- fox tail (behind body) -->
          <g class="fp-tail">
            <path d="M140 158 Q172 150 170 116 Q168 96 148 96 Q160 110 150 126 Q160 124 156 142 Q150 156 140 158Z"
                  fill="url(#tailGrad)"/>
            <path d="M152 100 Q166 104 166 118" stroke="#fff3e6" stroke-width="5" fill="none" stroke-linecap="round" opacity="0.9"/>
          </g>

          <!-- legs (behind body, new!) -->
          <g class="fp-leg-right">
            <path d="M108 158 Q116 172 112 184" stroke="url(#legGrad)" stroke-width="15" fill="none" stroke-linecap="round"/>
            <ellipse cx="112" cy="186" rx="11" ry="6" fill="#241a1f"/>
          </g>
          <g class="fp-leg-left">
            <path d="M92 158 Q84 172 88 184" stroke="url(#legGrad)" stroke-width="15" fill="none" stroke-linecap="round"/>
            <ellipse cx="88" cy="186" rx="11" ry="6" fill="#241a1f"/>
          </g>

          <!-- body: orange/black cloak -->
          <path d="M54 178 Q54 128 100 124 Q146 128 146 178 Z" fill="url(#bodyGrad)"/>
          <path d="M54 178 Q54 128 100 124 L100 178Z" fill="#1a1320" opacity="0.35"/>
          <path d="M82 128 Q100 138 118 128 L118 138 Q100 146 82 138 Z" fill="#7a2e0d" opacity="0.55"/>
          <!-- collar flame trim -->
          <path d="M78 130 q22 14 44 0 l-4 8 q-18 10 -36 0z" fill="#ffb066" opacity="0.85"/>

          <!-- left arm -->
          <g class="fp-arm-left">
            <path d="M60 146 Q34 150 32 128" stroke="url(#armGrad)" stroke-width="15" fill="none" stroke-linecap="round"/>
            <circle cx="32" cy="127" r="9" fill="#f3c39c"/>
          </g>

          <!-- right arm (waves up when valid, types when typing) -->
          <g class="fp-arm-right">
            <path d="M140 146 Q166 150 168 128" stroke="url(#armGrad)" stroke-width="15" fill="none" stroke-linecap="round"/>
            <circle cx="168" cy="127" r="9" fill="#f3c39c"/>
          </g>

          <!-- head group (look-around / bob) -->
          <g class="fp-headgroup">
            <!-- neck -->
            <rect x="87" y="102" width="26" height="24" rx="9" fill="#e8b08a"/>

            <!-- head -->
            <circle cx="100" cy="76" r="46" fill="url(#headGrad)"/>

            <!-- fox ears (orange, black tips) -->
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

            <!-- hair, dark spiky -->
            <path d="M54 74 Q48 28 100 24 Q152 28 146 74 Q140 46 100 46 Q60 46 54 74Z" fill="url(#hairGrad)"/>
            <path d="M62 50 Q76 36 100 35" stroke="#100a14" stroke-width="3" fill="none" stroke-linecap="round" opacity="0.6"/>

            <!-- whisker marks -->
            <g stroke="#c9682a" stroke-width="2" stroke-linecap="round" opacity="0.85">
              <line x1="58" y1="82" x2="70" y2="80"/>
              <line x1="58" y1="88" x2="70" y2="88"/>
              <line x1="58" y1="94" x2="70" y2="96"/>
              <line x1="142" y1="82" x2="130" y2="80"/>
              <line x1="142" y1="88" x2="130" y2="88"/>
              <line x1="142" y1="94" x2="130" y2="96"/>
            </g>

            <!-- blush -->
            <g class="fp-blush">
              <ellipse cx="74" cy="86" rx="7.5" ry="4.5" fill="#ff9a4d" opacity="0.8"/>
              <ellipse cx="126" cy="86" rx="7.5" ry="4.5" fill="#ff9a4d" opacity="0.8"/>
            </g>

            <!-- eyes whites -->
            <ellipse cx="82" cy="76" rx="9.5" ry="11" fill="#fff"/>
            <ellipse cx="118" cy="76" rx="9.5" ry="11" fill="#fff"/>

            <!-- pupils (amber fox eyes) -->
            <circle id="fpPupilL" class="fp-pupil" cx="82" cy="76" r="4.6" fill="#3a1d08"/>
            <circle id="fpPupilR" class="fp-pupil" cx="118" cy="76" r="4.6" fill="#3a1d08"/>
            <circle cx="84" cy="73" r="1.4" fill="#fff" opacity="0.9"/>
            <circle cx="120" cy="73" r="1.4" fill="#fff" opacity="0.9"/>

            <!-- eyelids for blinking -->
            <ellipse class="fp-eyelid" cx="82" cy="76" rx="10" ry="11.5" fill="#eeb088"/>
            <ellipse class="fp-eyelid" cx="118" cy="76" rx="10" ry="11.5" fill="#eeb088"/>

            <!-- eyebrows -->
            <path d="M72 61 Q82 56 92 61" stroke="#1f1318" stroke-width="2.8" fill="none" stroke-linecap="round"/>
            <path d="M108 61 Q118 56 128 61" stroke="#1f1318" stroke-width="2.8" fill="none" stroke-linecap="round"/>

            <!-- mouth -->
            <path id="fpMouth" class="fp-mouth" d="M87 98 Q100 102 113 98" stroke="#a85a3a" stroke-width="3" fill="none" stroke-linecap="round"/>
          </g>

          <defs>
            <linearGradient id="bodyGrad" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stop-color="#ff9a3f"/>
              <stop offset="100%" stop-color="#d8430f"/>
            </linearGradient>
            <linearGradient id="armGrad" x1="0" y1="0" x2="1" y2="0">
              <stop offset="0%" stop-color="#ff8c2a"/>
              <stop offset="100%" stop-color="#ef5b1c"/>
            </linearGradient>
            <linearGradient id="legGrad" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stop-color="#ef5b1c"/>
              <stop offset="100%" stop-color="#c8430f"/>
            </linearGradient>
            <linearGradient id="headGrad" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stop-color="#fad2ab"/>
              <stop offset="100%" stop-color="#eeb088"/>
            </linearGradient>
            <linearGradient id="hairGrad" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stop-color="#2a1c24"/>
              <stop offset="100%" stop-color="#150c14"/>
            </linearGradient>
            <linearGradient id="earGrad" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stop-color="#ff9a3f"/>
              <stop offset="100%" stop-color="#e0631f"/>
            </linearGradient>
            <linearGradient id="tailGrad" x1="0" y1="0" x2="1" y2="1">
              <stop offset="0%" stop-color="#ff9a3f"/>
              <stop offset="100%" stop-color="#c84f17"/>
            </linearGradient>
          </defs>
          </g>
        </svg>
      </div>
    </div>

    <h2>Lupa Password?</h2>
    <p class="fp-sub">Tenang, masukkan email akun TeleCard kamu dan kami kirimkan link reset password ke inbox kamu</p>

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

    <form method="post" id="fpForm">
      <div class="form-group">
        <div class="fp-float" id="fpInputWrap">
          <svg class="fp-mail-icon" viewBox="0 0 24 24"><path d="M4 6h16v12H4z"/><path d="M4 7l8 6 8-6"/></svg>
          <input type="email" name="email" id="fpEmail" placeholder=" " autocomplete="off" required>
          <label class="fp-flabel" for="fpEmail">Alamat Email</label>
          <svg class="fp-check" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
      </div>
      <button type="submit" class="fp-btn" id="fpBtn">
        <span class="fp-spinner"></span>
        <span class="fp-btn-text">Kirim Link Reset</span>
      </button>
    </form>

    <div class="fp-foot">Sudah ingat password? <a href="login.php">Login di sini</a></div>
  </div>
</div>

<script>
(function() {
    var emailInput = document.getElementById('fpEmail');
    var char = document.getElementById('fpChar');
    var walker = document.getElementById('fpWalker');
    var stage = document.getElementById('fpStage');
    var pupilL = document.getElementById('fpPupilL');
    var pupilR = document.getElementById('fpPupilR');
    var mouth = document.getElementById('fpMouth');
    var inputWrap = document.getElementById('fpInputWrap');
    var form = document.getElementById('fpForm');
    var btn = document.getElementById('fpBtn');

    var basePupil = { l: 82, r: 118 };
    var baseCy = 76;
    var maxShift = 3.8;

    var isTyping = false;
    var isWalking = false;
    var typingTimeout = null;
    var idleTimers = [];
    var currentX = 0; // current translateX offset from center, in px

    function emailRegex(val) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
    }

    function moveEyes(val) {
        var t = Math.min(val.length / 18, 1);
        var shift = (t * maxShift * 2) - maxShift;
        pupilL.setAttribute('cx', basePupil.l + shift);
        pupilR.setAttribute('cx', basePupil.r + shift);
        pupilL.setAttribute('cy', baseCy);
        pupilR.setAttribute('cy', baseCy);
    }

    function resetEyes() {
        pupilL.setAttribute('cx', basePupil.l);
        pupilR.setAttribute('cx', basePupil.r);
        pupilL.setAttribute('cy', baseCy);
        pupilR.setAttribute('cy', baseCy);
    }

    function setValidState(isValid) {
        if (isValid) {
            char.classList.add('valid');
            inputWrap.classList.add('is-valid');
            mouth.setAttribute('d', 'M84 96 Q100 112 116 96');
            char.classList.remove('bounce');
            void char.offsetWidth;
            char.classList.add('bounce');
        } else {
            char.classList.remove('valid');
            inputWrap.classList.remove('is-valid');
            mouth.setAttribute('d', 'M87 98 Q100 102 113 98');
        }
    }

    /* ===== Typing mode toggle ===== */
    function enterTypingMode() {
        if (isWalking) return; // typing pauses walking via blur anyway, but guard
        if (isTyping) return;
        isTyping = true;
        char.classList.add('typing');
        char.classList.remove('idle-look-left', 'idle-look-right', 'idle-look-center', 'idle-twitch');
        stopIdleCycle();
    }

    function exitTypingMode() {
        isTyping = false;
        char.classList.remove('typing');
        char.classList.add('idle-look-center');
        resetEyes();
        startIdleCycle();
    }

    /* ===== Walking behaviour: the character strolls left/right across the stage ===== */
    function walkTo(targetX, durationMs, callback) {
        isWalking = true;
        char.classList.add('walking');

        var fromX = currentX;
        var distance = targetX - fromX;
        if (distance < 0) {
            walker.classList.add('facing-left');
        } else {
            walker.classList.remove('facing-left');
        }

        var startTime = null;
        function step(ts) {
            if (!startTime) startTime = ts;
            var progress = Math.min((ts - startTime) / durationMs, 1);
            // ease in-out
            var eased = progress < 0.5 ? 2 * progress * progress : 1 - Math.pow(-2 * progress + 2, 2) / 2;
            currentX = fromX + distance * eased;
            walker.style.transform = 'translateX(' + currentX + 'px)';
            if (progress < 1 && isWalking) {
                requestAnimationFrame(step);
            } else {
                currentX = targetX;
                walker.style.transform = 'translateX(' + currentX + 'px)';
                char.classList.remove('walking');
                isWalking = false;
                if (callback) callback();
            }
        }
        requestAnimationFrame(step);
    }

    function maxWalkRange() {
        var stageWidth = stage.offsetWidth;
        var range = Math.min(stageWidth / 2 - 110, 70);
        return Math.max(range, 30);
    }

    function scheduleWalk() {
        var delay = 5000 + Math.random() * 5000;
        var t = setTimeout(function() {
            if (!isTyping && !isWalking) {
                var range = maxWalkRange();
                var target = (Math.random() < 0.5 ? -1 : 1) * (range * (0.4 + Math.random() * 0.6));
                var duration = 1400 + Math.random() * 800;
                stopIdleCycle();
                walkTo(target, duration, function() {
                    startIdleCycle();
                    scheduleWalk();
                });
                return;
            }
            scheduleWalk();
        }, delay);
        idleTimers.push(t);
    }

    /* ===== Idle behaviour: random look around, blink, ear twitch ===== */
    function clearIdleTimers() {
        idleTimers.forEach(function(t) { clearTimeout(t); });
        idleTimers = [];
    }

    function scheduleBlink() {
        var delay = 2200 + Math.random() * 3200;
        var t = setTimeout(function() {
            if (!isTyping && !isWalking) {
                char.classList.add('blinking');
                var t2 = setTimeout(function() {
                    char.classList.remove('blinking');
                }, 140);
                idleTimers.push(t2);
            }
            scheduleBlink();
        }, delay);
        idleTimers.push(t);
    }

    function scheduleLook() {
        var delay = 2600 + Math.random() * 3000;
        var t = setTimeout(function() {
            if (!isTyping && !isWalking) {
                var dirs = ['idle-look-left', 'idle-look-right', 'idle-look-center'];
                var pick = dirs[Math.floor(Math.random() * dirs.length)];
                char.classList.remove('idle-look-left', 'idle-look-right', 'idle-look-center');
                char.classList.add(pick);

                // shift pupils slightly with the head turn for extra life
                if (pick === 'idle-look-left') {
                    pupilL.setAttribute('cx', basePupil.l - 2.2);
                    pupilR.setAttribute('cx', basePupil.r - 2.2);
                } else if (pick === 'idle-look-right') {
                    pupilL.setAttribute('cx', basePupil.l + 2.2);
                    pupilR.setAttribute('cx', basePupil.r + 2.2);
                } else {
                    pupilL.setAttribute('cx', basePupil.l);
                    pupilR.setAttribute('cx', basePupil.r);
                }
            }
            scheduleLook();
        }, delay);
        idleTimers.push(t);
    }

    function scheduleEarTwitch() {
        var delay = 4000 + Math.random() * 5000;
        var t = setTimeout(function() {
            if (!isTyping && !isWalking) {
                char.classList.add('idle-twitch');
                var t2 = setTimeout(function() {
                    char.classList.remove('idle-twitch');
                }, 450);
                idleTimers.push(t2);
            }
            scheduleEarTwitch();
        }, delay);
        idleTimers.push(t);
    }

    function startIdleCycle() {
        clearIdleTimers();
        scheduleBlink();
        scheduleLook();
        scheduleEarTwitch();
        scheduleWalk();
    }

    function stopIdleCycle() {
        clearIdleTimers();
        char.classList.remove('blinking', 'idle-twitch');
    }

    /* ===== Input events ===== */
    emailInput.addEventListener('input', function() {
        enterTypingMode();
        moveEyes(this.value);
        setValidState(emailRegex(this.value));

        clearTimeout(typingTimeout);
        typingTimeout = setTimeout(function() {
            exitTypingMode();
        }, 900);
    });

    emailInput.addEventListener('focus', function() {
        enterTypingMode();
        moveEyes(this.value);
    });

    emailInput.addEventListener('blur', function() {
        clearTimeout(typingTimeout);
        exitTypingMode();
    });

    form.addEventListener('submit', function() {
        btn.classList.add('loading');
        btn.setAttribute('disabled', 'disabled');
        btn.querySelector('.fp-btn-text').textContent = 'Mengirim...';
    });

    // kick off idle behaviour on load
    startIdleCycle();

    // floating particles background
    var container = document.getElementById('fpParticles');
    var count = 18;
    for (var i = 0; i < count; i++) {
        var p = document.createElement('div');
        p.className = 'fp-particle';
        var size = 4 + Math.random() * 10;
        p.style.width = size + 'px';
        p.style.height = size + 'px';
        p.style.left = Math.random() * 100 + '%';
        p.style.bottom = '-20px';
        p.style.animationDuration = (8 + Math.random() * 10) + 's';
        p.style.animationDelay = (Math.random() * 10) + 's';
        container.appendChild(p);
    }
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
