<?php
// ============================
// KONFIGURASI WEBSITE TELECARD
// ============================
session_start();
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

// --- Database (MySQL) ---
define('DB_HOST', 'sql101.infinityfree.com');
define('DB_NAME', 'if0_42251940_telegram_telehub');
define('DB_USER', 'if0_42251940');
define('DB_PASS', 'Labibganteng11');

// --- Gmail SMTP (App Password) ---
define('GMAIL_EMAIL', 'elitecluboffic@gmail.com');
define('GMAIL_APP_PASSWORD', 'qdreohfwjdioffsk');

// --- Umum ---
define('SITE_NAME', 'TeleCard');
define('SITE_URL', 'https://telehub.nfy.fyi');
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

// ============================
// IPGUARD - VPN/PROXY BLOCKER
// ============================
define('VPNAPI_API_KEY',      '2ee89e0956cc4522aff54aa8ce59d692');
define('VPNAPI_LOG_FILE',     __DIR__ . '/ipguard/visitor_log.json');
define('VPNAPI_BLOCK_VPN',   true);
define('VPNAPI_BLOCK_PROXY', true);
define('VPNAPI_BLOCK_TOR',   true);
define('VPNAPI_BLOCK_RELAY', false);

define('VPNAPI_BLOCK_MESSAGE', <<<'HTML'
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Akses Ditolak — TeleCard</title>
<style>
  *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

  :root {
    --red:      #ef4444;
    --red-dark: #b91c1c;
    --bg:       #05050a;
    --faint:    rgba(255,255,255,0.06);
    --border:   rgba(239,68,68,0.25);
    --muted:    rgba(255,255,255,0.42);
  }

  html, body {
    height: 100%;
  }

  body {
    background: var(--bg);
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    font-family: "Segoe UI", system-ui, -apple-system, sans-serif;
    -webkit-font-smoothing: antialiased;
    overflow: hidden;
    color: #fff;
  }

  /* Ambient glow */
  .glow-top {
    position: fixed;
    top: -180px; left: 50%;
    transform: translateX(-50%);
    width: 700px; height: 400px;
    background: radial-gradient(ellipse, rgba(239,68,68,0.18) 0%, transparent 70%);
    pointer-events: none; z-index: 0;
    animation: breathe 5s ease-in-out infinite alternate;
  }
  .glow-bottom {
    position: fixed;
    bottom: -220px; left: 50%;
    transform: translateX(-50%);
    width: 500px; height: 380px;
    background: radial-gradient(ellipse, rgba(239,68,68,0.08) 0%, transparent 70%);
    pointer-events: none; z-index: 0;
    animation: breathe 5s ease-in-out infinite alternate-reverse;
  }
  @keyframes breathe {
    from { opacity: 0.6; transform: translateX(-50%) scale(1); }
    to   { opacity: 1;   transform: translateX(-50%) scale(1.08); }
  }

  /* Grid */
  .grid {
    position: fixed; inset: 0;
    background-image:
      linear-gradient(rgba(255,255,255,0.018) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255,255,255,0.018) 1px, transparent 1px);
    background-size: 48px 48px;
    pointer-events: none; z-index: 0;
    mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 30%, transparent 100%);
    -webkit-mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 30%, transparent 100%);
  }

  /* Card */
  .card {
    position: relative; z-index: 1;
    width: min(440px, 92vw);
    background: rgba(255,255,255,0.032);
    border: 1px solid var(--border);
    border-radius: 28px;
    padding: 52px 40px 44px;
    text-align: center;
    backdrop-filter: blur(28px) saturate(160%);
    -webkit-backdrop-filter: blur(28px) saturate(160%);
    box-shadow:
      0 0 0 1px rgba(239,68,68,0.08),
      0 24px 80px rgba(0,0,0,0.55),
      inset 0 1px 0 rgba(255,255,255,0.06);
    animation: card-in 0.55s cubic-bezier(0.34,1.46,0.64,1) both;
  }
  @keyframes card-in {
    from { opacity: 0; transform: translateY(36px) scale(0.94); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
  }

  /* Top accent */
  .card::before {
    content: "";
    position: absolute;
    top: 0; left: 20%; right: 20%;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(239,68,68,0.7), transparent);
    border-radius: 99px;
  }

  /* Icon */
  .icon-ring {
    width: 88px; height: 88px;
    margin: 0 auto 28px;
    position: relative;
  }
  .icon-ring::before {
    content: "";
    position: absolute; inset: -6px;
    border-radius: 50%;
    background: conic-gradient(from 0deg, transparent 30%, rgba(239,68,68,0.45) 50%, transparent 70%);
    animation: spin 4s linear infinite;
  }
  @keyframes spin { to { transform: rotate(360deg); } }

  .icon-inner {
    position: absolute; inset: 0;
    border-radius: 50%;
    background: radial-gradient(circle at 40% 35%, rgba(239,68,68,0.2), rgba(185,28,28,0.12));
    border: 1px solid rgba(239,68,68,0.35);
    display: flex; align-items: center; justify-content: center;
    animation: ipulse 2.5s ease-in-out infinite;
    backdrop-filter: blur(8px);
  }
  @keyframes ipulse {
    0%,100% { box-shadow: 0 0 0 0 rgba(239,68,68,0.35), 0 0 20px rgba(239,68,68,0.1); }
    50%     { box-shadow: 0 0 0 10px rgba(239,68,68,0), 0 0 40px rgba(239,68,68,0.2); }
  }
  .icon-inner svg {
    width: 38px; height: 38px;
    color: #f87171;
    filter: drop-shadow(0 0 8px rgba(239,68,68,0.6));
  }

  /* Badge */
  .badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(239,68,68,0.1);
    border: 1px solid rgba(239,68,68,0.3);
    color: #fca5a5;
    font-size: 10.5px; font-weight: 700;
    letter-spacing: 2.5px; text-transform: uppercase;
    padding: 5px 14px; border-radius: 99px;
    margin-bottom: 18px;
  }
  .badge::before {
    content: "";
    width: 6px; height: 6px; border-radius: 50%;
    background: #ef4444;
    animation: blink 1.4s ease-in-out infinite;
  }
  @keyframes blink {
    0%,100% { opacity: 1; }
    50%     { opacity: 0.2; }
  }

  h1 {
    font-size: 24px; font-weight: 800;
    letter-spacing: -0.5px; color: #fff;
    margin-bottom: 10px; line-height: 1.25;
  }

  .sub {
    color: var(--muted); font-size: 14px;
    line-height: 1.75; margin-bottom: 28px;
  }

  /* Pills */
  .pills {
    display: flex; justify-content: center;
    gap: 8px; flex-wrap: wrap; margin-bottom: 28px;
  }
  .pill {
    display: inline-flex; align-items: center; gap: 5px;
    background: var(--faint);
    border: 1px solid rgba(255,255,255,0.08);
    color: rgba(255,255,255,0.5);
    font-size: 12px; font-weight: 500;
    padding: 5px 12px; border-radius: 99px;
  }
  .pill svg { width: 13px; height: 13px; color: #f87171; flex-shrink: 0; }

  /* Divider */
  .divider {
    height: 1px;
    background: linear-gradient(90deg, transparent, var(--faint), transparent);
    margin: 0 0 28px;
  }

  /* Info box */
  .info-box {
    display: flex; align-items: flex-start; gap: 12px;
    background: rgba(239,68,68,0.06);
    border: 1px solid rgba(239,68,68,0.18);
    border-radius: 14px; padding: 14px 16px;
    margin-bottom: 28px; text-align: left;
  }
  .info-box svg { flex-shrink: 0; margin-top: 1px; width: 17px; height: 17px; color: #f87171; }
  .info-box p { color: rgba(255,255,255,0.48); font-size: 13px; line-height: 1.65; }
  .info-box strong { color: rgba(255,255,255,0.68); }

  /* Button */
  .btn {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    width: 100%; padding: 15px;
    background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
    color: #fff; font-size: 14px; font-weight: 700;
    letter-spacing: 0.3px; border-radius: 14px;
    border: none; cursor: pointer; text-decoration: none;
    transition: all 0.22s ease;
    box-shadow: 0 4px 24px rgba(239,68,68,0.35), inset 0 1px 0 rgba(255,255,255,0.12);
    position: relative; overflow: hidden;
  }
  .btn::after {
    content: ""; position: absolute; inset: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.12), transparent);
    opacity: 0; transition: opacity 0.2s;
  }
  .btn:hover { transform: translateY(-2px); box-shadow: 0 8px 36px rgba(239,68,68,0.5); }
  .btn:hover::after { opacity: 1; }
  .btn:active { transform: translateY(0); }
  .btn svg { width: 17px; height: 17px; }

  /* Footer */
  .footer { margin-top: 22px; font-size: 12px; color: rgba(255,255,255,0.18); }
  .footer strong { color: rgba(255,255,255,0.32); font-weight: 600; }
</style>
</head>
<body>

<div class="glow-top"></div>
<div class="glow-bottom"></div>
<div class="grid"></div>

<div class="card">

  <div class="icon-ring">
    <div class="icon-inner">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M9.5 9.5l5 5M14.5 9.5l-5 5"/>
      </svg>
    </div>
  </div>

  <div class="badge">403 &mdash; Akses Ditolak</div>

  <h1>Koneksi Tidak Aman</h1>
  <p class="sub">
    VPN, Proxy, atau Tor terdeteksi pada koneksi kamu.<br>
    Layanan ini hanya bisa diakses tanpa tunneling.
  </p>

  <div class="pills">
    <span class="pill">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/>
        <path d="M12 8v4m0 4h.01"/>
      </svg>
      VPN Detected
    </span>
    <span class="pill">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636a9 9 0 11-12.728 0"/>
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v9"/>
      </svg>
      Proxy Blocked
    </span>
    <span class="pill">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
      </svg>
      Tor Restricted
    </span>
  </div>

  <div class="divider"></div>

  <div class="info-box">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round"
        d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/>
    </svg>
    <p>Matikan VPN / Proxy kamu terlebih dahulu, kemudian tekan tombol di bawah untuk mencoba kembali mengakses <strong>TeleCard</strong>.</p>
  </div>

  <a href="javascript:location.reload()" class="btn">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
      <path stroke-linecap="round" stroke-linejoin="round"
        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
    </svg>
    Coba Lagi Sekarang
  </a>

  <div class="footer">
    Dilindungi oleh <strong>TeleCard Security System</strong>
  </div>

</div>
</body>
</html>
HTML);
