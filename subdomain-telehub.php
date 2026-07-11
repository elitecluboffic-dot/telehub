<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Free Subdomain Telehub</title>
<meta name="description" content="Uji domainmu tanpa ribet. Ajukan subdomain custom di telehub.web.id, arahkan CNAME, live dalam hitungan menit.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Manrope:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  :root{
    --bg: #0a0e17;
    --bg-alt: #0d1220;
    --panel: #121a2e;
    --panel-soft: #161f36;
    --border: rgba(255,255,255,0.08);
    --border-strong: rgba(255,255,255,0.14);
    --text: #e9edf7;
    --text-dim: #8b93a7;
    --text-faint: #5b637a;
    --accent: #ff8a3d;
    --accent-soft: rgba(255,138,61,0.14);
    --accent-2: #4fd1c5;
    --accent-2-soft: rgba(79,209,197,0.14);
    --danger: #ff5c5c;
    --radius: 18px;
    --font-display: 'Space Grotesk', sans-serif;
    --font-body: 'Manrope', sans-serif;
    --font-mono: 'IBM Plex Mono', monospace;
  }

  *{ box-sizing: border-box; }
  html{ scroll-behavior: smooth; }

  body{
    margin:0;
    background:
      radial-gradient(ellipse 900px 500px at 15% -10%, rgba(255,138,61,0.10), transparent 60%),
      radial-gradient(ellipse 800px 600px at 110% 10%, rgba(79,209,197,0.08), transparent 60%),
      var(--bg);
    color: var(--text);
    font-family: var(--font-body);
    line-height: 1.6;
    -webkit-font-smoothing: antialiased;
  }

  a{ color: inherit; }

  .wrap{
    max-width: 1180px;
    margin: 0 auto;
    padding: 0 28px;
  }

  /* ---------- NAV ---------- */
  nav{
    position: sticky; top:0; z-index: 50;
    backdrop-filter: blur(14px);
    background: rgba(10,14,23,0.7);
    border-bottom: 1px solid var(--border);
  }
  nav .wrap{
    display:flex; align-items:center; justify-content:space-between;
    height: 72px;
  }
  .brand{
    display:flex; align-items:center; gap:10px;
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 19px;
    letter-spacing: -0.02em;
    text-decoration:none;
  }
  .brand .dot{
    width:9px; height:9px; border-radius:50%;
    background: var(--accent);
    box-shadow: 0 0 0 4px var(--accent-soft);
  }
  .nav-links{ display:flex; gap:32px; font-size:14px; color: var(--text-dim); }
  .nav-links a{ text-decoration:none; }
  .nav-links a:hover{ color: var(--text); }
  .nav-cta{
    font-family: var(--font-mono);
    font-size: 13px;
    padding: 9px 16px;
    border-radius: 10px;
    background: var(--accent);
    color: #1a1104;
    text-decoration:none;
    font-weight: 600;
  }

  /* ---------- HERO ---------- */
  .hero{
    padding: 84px 0 60px;
  }
  .hero-grid{
    display:grid;
    grid-template-columns: 1.05fr 0.95fr;
    gap: 56px;
    align-items:center;
  }
  .eyebrow{
    display:inline-flex; align-items:center; gap:8px;
    font-family: var(--font-mono);
    font-size: 12px;
    color: var(--accent-2);
    background: var(--accent-2-soft);
    border: 1px solid rgba(79,209,197,0.25);
    padding: 6px 12px;
    border-radius: 100px;
    margin-bottom: 22px;
  }
  .eyebrow::before{
    content:''; width:6px; height:6px; border-radius:50%;
    background: var(--accent-2);
    box-shadow: 0 0 8px var(--accent-2);
  }
  h1{
    font-family: var(--font-display);
    font-size: clamp(2.2rem, 4vw, 3.4rem);
    line-height: 1.08;
    letter-spacing: -0.03em;
    margin: 0 0 20px;
    font-weight: 700;
  }
  h1 .accent{ color: var(--accent); }
  .hero-sub{
    font-size: 17px;
    color: var(--text-dim);
    max-width: 480px;
    margin: 0 0 32px;
  }
  .hero-stats{
    display:flex; gap: 28px; margin-top: 36px;
  }
  .hero-stats div{ }
  .hero-stats .num{
    font-family: var(--font-display);
    font-size: 22px; font-weight:700;
  }
  .hero-stats .label{
    font-size: 12px; color: var(--text-faint);
    font-family: var(--font-mono);
  }

  /* ---------- 3D ROUTE CARD (signature element) ---------- */
  .scene{
    perspective: 1400px;
  }
  .route-card{
    position:relative;
    background: linear-gradient(160deg, var(--panel-soft), var(--panel));
    border: 1px solid var(--border-strong);
    border-radius: 22px;
    padding: 34px 28px 28px;
    transform-style: preserve-3d;
    transform: rotateX(8deg) rotateY(-10deg);
    transition: transform 0.15s ease-out;
    box-shadow:
      0 30px 60px -20px rgba(0,0,0,0.6),
      0 0 0 1px rgba(255,255,255,0.02) inset;
  }
  .route-card::before{
    content:'';
    position:absolute; inset:0;
    border-radius: 22px;
    background: linear-gradient(120deg, rgba(255,138,61,0.10), transparent 40%, rgba(79,209,197,0.08));
    pointer-events:none;
  }
  .route-label{
    font-family: var(--font-mono);
    font-size: 11px;
    color: var(--text-faint);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 18px;
    transform: translateZ(20px);
  }
  .route-node{
    display:flex; align-items:center; gap:14px;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 14px 16px;
    transform: translateZ(30px);
    position:relative;
  }
  .route-node + .route-node{ margin-top: 0; }
  .node-icon{
    width:36px; height:36px; border-radius:10px;
    display:flex; align-items:center; justify-content:center;
    font-size:16px; flex-shrink:0;
  }
  .node-icon.you{ background: rgba(255,255,255,0.06); }
  .node-icon.sub{ background: var(--accent-soft); }
  .node-icon.srv{ background: var(--accent-2-soft); }
  .node-title{ font-size: 13px; font-weight:600; }
  .node-sub{ font-family: var(--font-mono); font-size: 12px; color: var(--text-dim); }

  .route-line{
    width: 2px; height: 30px;
    margin-left: 34px;
    background: linear-gradient(var(--border-strong), var(--border-strong));
    position: relative;
    overflow: hidden;
    transform: translateZ(25px);
  }
  .route-line::after{
    content:'';
    position:absolute; left:0; top:-30px;
    width:100%; height:16px;
    background: linear-gradient(var(--accent), transparent);
    animation: beam 2.2s linear infinite;
  }
  @keyframes beam{
    0%{ top:-16px; }
    100%{ top: 30px; }
  }

  .route-status{
    margin-top: 18px;
    display:flex; align-items:center; justify-content:space-between;
    font-family: var(--font-mono);
    font-size: 11px;
    color: var(--accent-2);
    transform: translateZ(20px);
  }
  .route-status .pill{
    display:inline-flex; align-items:center; gap:6px;
    background: var(--accent-2-soft);
    border: 1px solid rgba(79,209,197,0.25);
    padding: 4px 10px; border-radius: 100px;
  }
  .route-status .pill::before{
    content:''; width:6px; height:6px; border-radius:50%;
    background: var(--accent-2); box-shadow: 0 0 6px var(--accent-2);
  }

  @media (prefers-reduced-motion: reduce){
    .route-card{ transform: none !important; }
    .route-line::after{ animation: none; }
  }

  /* ---------- SECTION HEADER ---------- */
  .section{ padding: 70px 0; }
  .section-head{ max-width: 560px; margin-bottom: 46px; }
  .section-eyebrow{
    font-family: var(--font-mono);
    font-size: 12px; color: var(--accent);
    text-transform: uppercase; letter-spacing: 0.1em;
    margin-bottom: 12px;
  }
  h2{
    font-family: var(--font-display);
    font-size: clamp(1.6rem, 2.6vw, 2.1rem);
    letter-spacing: -0.02em;
    margin: 0 0 12px;
  }
  .section-head p{ color: var(--text-dim); margin:0; }

  /* ---------- STEPS ---------- */
  .steps{
    display:grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
  }
  .step{
    background: var(--panel);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 26px 22px;
    position:relative;
    transition: transform 0.25s ease, border-color 0.25s ease;
  }
  .step:hover{
    transform: translateY(-4px);
    border-color: var(--border-strong);
  }
  .step .num{
    font-family: var(--font-mono);
    font-size: 12px; color: var(--text-faint);
    margin-bottom: 14px;
  }
  .step h3{
    font-family: var(--font-display);
    font-size: 17px; margin: 0 0 8px;
  }
  .step p{ font-size: 14px; color: var(--text-dim); margin:0; }

  /* ---------- FORM ---------- */
  .form-shell{
    display:grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    align-items:start;
  }
  .form-card{
    background: var(--panel);
    border: 1px solid var(--border);
    border-radius: 22px;
    padding: 34px;
  }
  .field{ margin-bottom: 20px; }
  .field label{
    display:block; font-size: 13px; font-weight:600;
    margin-bottom: 8px; color: var(--text);
  }
  .field label .optional-tag{
    font-family: var(--font-mono);
    font-weight: 500;
    font-size: 11px;
    color: var(--text-faint);
    margin-left: 6px;
    text-transform: none;
  }
  .field .hint{ font-size:12px; color: var(--text-faint); margin-top:6px; }
  .input-group{
    display:flex; align-items:stretch;
    border: 1px solid var(--border-strong);
    border-radius: 12px;
    overflow:hidden;
    background: var(--bg-alt);
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
  }
  .input-group:focus-within{
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-soft);
  }
  .input-group input, .input-group textarea{
    flex:1; background: transparent; border:none; outline:none;
    color: var(--text); font-family: var(--font-body); font-size: 14px;
    padding: 12px 14px;
  }
  .input-group textarea{ resize: vertical; min-height: 84px; font-family: var(--font-body); }
  .input-suffix{
    display:flex; align-items:center;
    padding: 0 14px;
    font-family: var(--font-mono);
    font-size: 13px;
    color: var(--text-faint);
    background: rgba(255,255,255,0.03);
    border-left: 1px solid var(--border);
    white-space:nowrap;
  }
  .input-prefix{
    display:flex; align-items:center;
    padding: 0 14px;
    font-family: var(--font-mono);
    font-size: 13px;
    color: var(--text-faint);
    background: rgba(255,255,255,0.03);
    border-right: 1px solid var(--border);
    white-space:nowrap;
  }
  input:not([type]), input[type="text"], input[type="email"]{
    -webkit-appearance:none;
  }
  .honeypot{ position:absolute; left:-9999px; opacity:0; }

  .preview-line{
    font-family: var(--font-mono);
    font-size: 13px;
    color: var(--text-dim);
    margin-top: 10px;
  }
  .preview-line strong{ color: var(--accent-2); font-weight:600; }

  /* ---------- DNS RECORD GROUPS (multi CNAME/TXT) ---------- */
  .dns-records-label{
    display:flex; align-items:center; justify-content:space-between;
    margin-bottom: 12px;
  }
  .dns-records-label span{
    font-size: 13px; font-weight:600; color: var(--text);
  }
  .dns-records-label .count{
    font-family: var(--font-mono);
    font-size: 11px;
    color: var(--text-faint);
  }
  .dns-records{
    display:flex; flex-direction:column; gap:16px;
    margin-bottom: 14px;
  }
  .dns-record{
    position:relative;
    background: rgba(255,255,255,0.025);
    border: 1px dashed var(--border-strong);
    border-radius: 14px;
    padding: 16px 16px 4px;
  }
  .dns-record-head{
    display:flex; align-items:center; justify-content:space-between;
    margin-bottom: 12px;
  }
  .dns-record-tag{
    font-family: var(--font-mono);
    font-size: 11px;
    color: var(--text-faint);
    text-transform: uppercase;
    letter-spacing: 0.08em;
  }
  .remove-record-btn{
    width: 24px; height: 24px;
    border-radius: 8px;
    border: 1px solid var(--border-strong);
    background: transparent;
    color: var(--text-dim);
    font-size: 12px;
    line-height: 1;
    display:flex; align-items:center; justify-content:center;
    cursor:pointer;
    transition: border-color 0.15s ease, color 0.15s ease;
  }
  .remove-record-btn:hover{
    border-color: var(--danger);
    color: var(--danger);
  }
  .record-row-pair{
    display:grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
  }
  @media (max-width: 520px){
    .record-row-pair{ grid-template-columns: 1fr; }
  }
  .add-record-btn{
    width: 100%;
    padding: 12px;
    border-radius: 12px;
    border: 1px dashed var(--border-strong);
    background: transparent;
    color: var(--text-dim);
    font-family: var(--font-mono);
    font-size: 13px;
    cursor:pointer;
    margin-bottom: 24px;
    transition: border-color 0.15s ease, color 0.15s ease;
  }
  .add-record-btn:hover{
    border-color: var(--accent);
    color: var(--accent);
  }

  /* ---------- ADVANCED (collapsible) ---------- */
  .advanced-toggle{
    width:100%;
    display:flex; align-items:center; justify-content:space-between;
    padding: 12px 16px;
    border-radius: 12px;
    border: 1px solid var(--border);
    background: rgba(255,255,255,0.02);
    color: var(--text-dim);
    font-family: var(--font-mono);
    font-size: 12px;
    cursor:pointer;
    margin-bottom: 20px;
    transition: border-color 0.15s ease, color 0.15s ease;
  }
  .advanced-toggle:hover{ border-color: var(--border-strong); color: var(--text); }
  .advanced-toggle .chevron{ transition: transform 0.2s ease; }
  .advanced-toggle.open .chevron{ transform: rotate(180deg); }
  .advanced-body{
    display:none;
    padding-top: 4px;
  }
  .advanced-body.open{ display:block; }

  .submit-btn{
    width:100%;
    padding: 14px 20px;
    border-radius: 12px;
    border: none;
    background: var(--accent);
    color: #1a1104;
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 15px;
    cursor:pointer;
    transition: transform 0.15s ease, box-shadow 0.15s ease, opacity 0.15s ease;
    box-shadow: 0 12px 24px -8px rgba(255,138,61,0.45);
  }
  .submit-btn:hover{ transform: translateY(-2px); }
  .submit-btn:disabled{ opacity: 0.6; cursor:not-allowed; transform:none; }

  .form-msg{
    margin-top: 16px;
    padding: 12px 14px;
    border-radius: 10px;
    font-size: 13px;
    display:none;
  }
  .form-msg.show{ display:block; }
  .form-msg.success{
    background: var(--accent-2-soft);
    border: 1px solid rgba(79,209,197,0.3);
    color: var(--accent-2);
  }
  .form-msg.error{
    background: rgba(255,92,92,0.1);
    border: 1px solid rgba(255,92,92,0.3);
    color: var(--danger);
  }
  .form-msg ul{ margin: 4px 0 0; padding-left: 18px; }

  /* side info panel */
  .info-panel{
    padding-top: 8px;
  }
  .info-item{
    display:flex; gap:14px;
    padding: 18px 0;
    border-bottom: 1px solid var(--border);
  }
  .info-item:last-child{ border-bottom:none; }
  .info-icon{
    width:38px; height:38px; border-radius:10px;
    background: var(--accent-soft);
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0; font-size:16px;
  }
  .info-item h4{ margin:0 0 4px; font-size:14px; font-family: var(--font-display); }
  .info-item p{ margin:0; font-size:13px; color: var(--text-dim); }

  /* ---------- FOOTER ---------- */
  footer{
    border-top: 1px solid var(--border);
    padding: 32px 0;
    margin-top: 40px;
  }
  footer .wrap{
    display:flex; justify-content:space-between; align-items:center;
    font-size: 13px; color: var(--text-faint);
  }
  footer a{ text-decoration:none; color: var(--text-dim); }

  @media (max-width: 900px){
    .hero-grid{ grid-template-columns: 1fr; }
    .steps{ grid-template-columns: 1fr; }
    .form-shell{ grid-template-columns: 1fr; }
    .nav-links{ display:none; }
    .route-card{ transform: none; }
  }
</style>
</head>
<body>

<nav>
  <div class="wrap">
    <a href="#" class="brand"><span class="dot"></span>telehub</a>
    <div class="nav-links">
      <a href="#cara-kerja">Cara kerja</a>
      <a href="#form">Ajukan subdomain</a>
      <a href="https://telehub.nfy.fyi" target="_blank" rel="noopener">Situs utama</a>
    </div>
    <a href="#form" class="nav-cta">Ajukan sekarang</a>
  </div>
</nav>

<section class="hero">
  <div class="wrap hero-grid">
    <div>
      <span class="eyebrow">Free Subdomain Tersedia</span>
      <h1>Free subdomain kami.<br>Uji domainmu <span class="accent">tanpa drama DNS.</span></h1>
      <p class="hero-sub">
        Lagi coba-coba domain baru tapi belum mau utak-atik DNS utama? Ajukan
        nama subdomain custom di <strong>telehub.web.id</strong>, arahkan
        domainmu lewat CNAME, dan kami aktifkan setelah ditinjau.
      </p>
      <a href="#form" class="nav-cta" style="display:inline-block;">Ajukan nama subdomain</a>

      <div class="hero-stats">
        <div>
          <div class="num">&lt; 15 mnt</div>
          <div class="label">RATA-RATA AKTIVASI</div>
        </div>
        <div>
          <div class="num">Manual review</div>
          <div class="label">TIAP PERMINTAAN DICEK</div>
        </div>
        <div>
          <div class="num">1 domain</div>
          <div class="label">PER PERMINTAAN</div>
        </div>
      </div>
    </div>

    <div class="scene">
      <div class="route-card" id="routeCard">
        <div class="route-label">Peta rute langsung</div>

        <div class="route-node">
          <div class="node-icon you">🌐</div>
          <div>
            <div class="node-title">Domain kamu</div>
            <div class="node-sub">domainkamu.com</div>
          </div>
        </div>

        <div class="route-line"></div>

        <div class="route-node">
          <div class="node-icon sub">🔗</div>
          <div>
            <div class="node-title">Subdomain kamu</div>
            <div class="node-sub" id="previewNode">namamu.telehub.web.id</div>
          </div>
        </div>

        <div class="route-line"></div>

        <div class="route-node">
          <div class="node-icon srv">⚡</div>
          <div>
            <div class="node-title">Server Telehub</div>
            <div class="node-sub">Edge aktif · Cloudflare</div>
          </div>
        </div>

        <div class="route-status">
          <span>CNAME · TTL 300</span>
          <span class="pill">propagasi normal</span>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section" id="cara-kerja">
  <div class="wrap">
    <div class="section-head">
      <div class="section-eyebrow">Cara kerja</div>
      <h2>Tiga langkah, bukan tiket support berhari-hari</h2>
      <p>Prosesnya linear — tiap permintaan lewat urutan yang sama supaya jelas statusnya ada di mana.</p>
    </div>
    <div class="steps">
      <div class="step">
        <div class="num">01</div>
        <h3>Ajukan nama subdomain</h3>
        <p>Isi nama subdomain yang kamu mau, domain testing kamu, email aktif, dan kontak lain buat kami hubungi balik. Kalau butuh lebih dari satu record CNAME/TXT, tinggal tambah baris.</p>
      </div>
      <div class="step">
        <div class="num">02</div>
        <h3>Kami tinjau &amp; kabari</h3>
        <p>Permintaan masuk ke Telegram kami secara real-time. Kami cek nama dan semua record yang diajukan, lalu tap approve atau reject.</p>
      </div>
      <div class="step">
        <div class="num">03</div>
        <h3>CNAME aktif, live</h3>
        <p>Begitu disetujui, email status otomatis masuk ke inbox kamu. Arahkan domainmu via CNAME (dan TXT kalau ada) ke record yang diberikan, beres tinggal tes.</p>
      </div>
    </div>
  </div>
</section>

<section class="section" id="form">
  <div class="wrap">
    <div class="section-head">
      <div class="section-eyebrow">Form pengajuan</div>
      <h2>Nama subdomain kamu apa?</h2>
      <p>Isi sekali, kami yang tinjau. Status approve/reject dikirim otomatis ke email yang kamu isi di bawah.</p>
    </div>

    <div class="form-shell">
      <div class="form-card">
        <form id="subdomainForm" novalidate>
          <input type="text" name="website" class="honeypot" tabindex="-1" autocomplete="off">

          <div class="field">
            <label for="subdomain">Nama subdomain</label>
            <div class="input-group">
              <input type="text" id="subdomain" name="subdomain" placeholder="misal: proyekku" required>
              <span class="input-suffix">.telehub.web.id</span>
            </div>
            <div class="preview-line">Preview: <strong id="previewText">namamu.telehub.web.id</strong></div>
          </div>

          <div class="field">
            <label for="targetDomain">Domain testing kamu</label>
            <div class="input-group">
              <input type="text" id="targetDomain" name="targetDomain" placeholder="domainkamu.com" required>
            </div>
            <div class="hint">Domain yang mau kamu arahkan ke subdomain ini via CNAME.</div>
          </div>

          <div class="field">
            <label for="email">Email</label>
            <div class="input-group">
              <input type="email" id="email" name="email" placeholder="kamu@email.com" required>
            </div>
            <div class="hint">Wajib diisi dan aktif — notifikasi approve/reject dikirim otomatis ke sini.</div>
          </div>

          <div class="field">
            <label for="contact">Kontak lain (WA / lainnya)</label>
            <div class="input-group">
              <input type="text" id="contact" name="contact" placeholder="08xxxxxxxxxx atau info lain" required>
            </div>
          </div>

          <div class="field">
            <label for="telegramUsername">Username Telegram<span class="optional-tag">opsional</span></label>
            <div class="input-group">
              <span class="input-prefix">@</span>
              <input type="text" id="telegramUsername" name="telegramUsername" placeholder="usernamekamu">
            </div>
            <div class="hint">Biar kami gampang hubungin kamu langsung di Telegram kalau perlu klarifikasi.</div>
          </div>

          <div class="field">
            <div class="dns-records-label">
              <span>Record CNAME &amp; TXT</span>
              <span class="count" id="recordCount">1 record</span>
            </div>

            <div class="dns-records" id="dnsRecords">
              <!-- record pertama, dirender oleh JS saat load -->
            </div>

            <button type="button" class="add-record-btn" id="addRecordBtn">+ Tambah record CNAME/TXT lain</button>
          </div>

          <button type="button" class="advanced-toggle" id="advancedToggle">
            <span>Opsi tambahan (A record, catatan lain)</span>
            <span class="chevron">▾</span>
          </button>

          <div class="advanced-body" id="advancedBody">
            <div class="field">
              <label for="aRecordIp">IP untuk A record<span class="optional-tag">opsional</span></label>
              <div class="input-group">
                <input type="text" id="aRecordIp" name="aRecordIp" placeholder="192.0.2.10 atau IPv6">
              </div>
              <div class="hint">Isi kalau hosting kamu minta A record langsung ke IP, bukan cuma CNAME.</div>
            </div>

            <div class="field">
              <label for="extraRecords">Catatan record tambahan<span class="optional-tag">opsional</span></label>
              <div class="input-group">
                <textarea id="extraRecords" name="extraRecords" placeholder="Record lain yang perlu kami tahu, maks 1000 karakter."></textarea>
              </div>
            </div>
          </div>

          <div class="field">
            <label for="purpose">Tujuan pemakaian<span class="optional-tag">opsional</span></label>
            <div class="input-group">
              <textarea id="purpose" name="purpose" placeholder="lagi ngetes deploy apa, dsb."></textarea>
            </div>
          </div>

          <button type="submit" class="submit-btn" id="submitBtn">Kirim permintaan</button>

          <div class="form-msg" id="formMsg"></div>
        </form>
      </div>

      <div class="info-panel">
        <div class="info-item">
          <div class="info-icon">🧭</div>
          <div>
            <h4>Dari mana ambil name &amp; value CNAME?</h4>
            <p>Buka pengaturan custom domain di Railway/Vercel/hosting kamu — biasanya ada halaman "Configure DNS Records" dengan tabel 3 kolom: <em>Type</em>, <em>Name</em>, dan <em>Value</em>. Kolom <em>Name</em> itu subdomain kamu (misal <code>contoh</code>), kolom <em>Value</em> itu target CNAME-nya (misal <code>xxxx.up.railway.app</code>). Isi keduanya di form.</p>
          </div>
        </div>
        <div class="info-item">
          <div class="info-icon">➕</div>
          <div>
            <h4>Butuh lebih dari satu record?</h4>
            <p>Klik "Tambah record CNAME/TXT lain" buat nambah baris baru. Berguna kalau hosting kamu minta beberapa CNAME/TXT sekaligus untuk satu domain.</p>
          </div>
        </div>
        <div class="info-item">
          <div class="info-icon">⏱️</div>
          <div>
            <h4>Ditinjau manual, bukan auto-approve</h4>
            <p>Tiap permintaan masuk ke Telegram kami dulu. Ini biar nama subdomain nggak asal dan nggak bentrok.</p>
          </div>
        </div>
        <div class="info-item">
          <div class="info-icon">🔒</div>
          <div>
            <h4>Satu domain per permintaan</h4>
            <p>Supaya gampang dilacak, tiap subdomain cuma diarahkan ke satu domain testing — meski record CNAME/TXT-nya bisa lebih dari satu.</p>
          </div>
        </div>
        <div class="info-item">
          <div class="info-icon">📧</div>
          <div>
            <h4>Kabar otomatis lewat email</h4>
            <p>Begitu admin approve atau reject, email status langsung terkirim ke alamat yang kamu isi. Isi username Telegram (opsional) kalau mau jalur komunikasi tambahan.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<footer>
  <div class="wrap">
    <span>© 2026 telehub.web.id</span>
    <a href="https://telehub.nfy.fyi" target="_blank" rel="noopener">telehub.nfy.fyi</a>
  </div>
</footer>

<script>
  // ============================================================
  // KONFIGURASI PENTING -- GANTI SEBELUM DIPAKAI
  // ============================================================
  // Domain dasar buat preview subdomain (cuma dipakai tampilan, tidak
  // dikirim ke backend -- backend Worker yang nentuin domain final).
  const BASE_DOMAIN = 'telehub.web.id';

  // URL endpoint Worker kamu setelah `wrangler deploy`.
  // Ganti dengan URL asli Worker kamu, contoh:
  //   https://telehub-subdomain-worker.<akun-cf-kamu>.workers.dev/submit
  // atau custom domain kalau sudah di-setting, misal:
  //   https://api.telehub.web.id/submit
  const WORKER_SUBMIT_URL = 'https://telehub-subdomain-worker.elitecluboffic.workers.dev/submit';
  // ============================================================

  // Preview nama subdomain real-time
  const subInput = document.getElementById('subdomain');
  const previewNode = document.getElementById('previewNode');
  const previewText = document.getElementById('previewText');

  function updatePreview(){
    const val = (subInput.value || 'namamu').toLowerCase().replace(/[^a-z0-9-]/g, '');
    const full = val + '.' + BASE_DOMAIN;
    previewNode.textContent = full;
    previewText.textContent = full;
  }
  subInput.addEventListener('input', updatePreview);

  // Tilt 3D pada route card mengikuti mouse
  const routeCard = document.getElementById('routeCard');
  const scene = document.querySelector('.scene');
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if (scene && !prefersReducedMotion) {
    scene.addEventListener('mousemove', (e) => {
      const rect = scene.getBoundingClientRect();
      const x = (e.clientX - rect.left) / rect.width - 0.5;
      const y = (e.clientY - rect.top) / rect.height - 0.5;
      routeCard.style.transform = `rotateX(${8 - y * 14}deg) rotateY(${-10 + x * 18}deg)`;
    });
    scene.addEventListener('mouseleave', () => {
      routeCard.style.transform = 'rotateX(8deg) rotateY(-10deg)';
    });
  }

  // ============================================================
  // ADVANCED SECTION toggle (A record IP, catatan tambahan)
  // ============================================================
  const advancedToggle = document.getElementById('advancedToggle');
  const advancedBody = document.getElementById('advancedBody');

  advancedToggle.addEventListener('click', () => {
    const isOpen = advancedBody.classList.toggle('open');
    advancedToggle.classList.toggle('open', isOpen);
  });

  // ============================================================
  // DNS RECORDS -- multi CNAME / TXT dinamis
  // Tiap record sekarang punya 4 bagian: Name CNAME, Value CNAME,
  // Name TXT, Value TXT -- meniru persis tabel "Configure DNS
  // Records" (Type / Name / Value) yang ditampilkan Railway/Vercel/dll.
  // ============================================================
  const dnsRecordsContainer = document.getElementById('dnsRecords');
  const addRecordBtn = document.getElementById('addRecordBtn');
  const recordCountLabel = document.getElementById('recordCount');
  const MAX_DNS_RECORDS = 10; // samakan dengan MAX_DNS_RECORDS di backend Worker
  let recordIdCounter = 0;

  function createRecordRow(){
    recordIdCounter += 1;
    const id = recordIdCounter;

    const row = document.createElement('div');
    row.className = 'dns-record';
    row.dataset.recordId = id;

    row.innerHTML = `
      <div class="dns-record-head">
        <span class="dns-record-tag">Record</span>
        <button type="button" class="remove-record-btn" aria-label="Hapus record ini">✕</button>
      </div>

      <div class="record-row-pair">
        <div class="field">
          <label>Nama CNAME</label>
          <div class="input-group">
            <input type="text" class="cnameName" placeholder="contoh: contoh">
          </div>
          <div class="hint">Kolom <em>Name</em> di tabel DNS hosting kamu.</div>
        </div>
        <div class="field">
          <label>Target CNAME</label>
          <div class="input-group">
            <input type="text" class="cnameTarget" placeholder="contoh: 4vnr0i6z.up.railway.app" required>
          </div>
          <div class="hint">Kolom <em>Value</em> pada baris CNAME.</div>
        </div>
      </div>

      <div class="record-row-pair">
        <div class="field">
          <label>Nama record TXT (opsional)</label>
          <div class="input-group">
            <input type="text" class="txtName" placeholder="contoh: _railway-verify.contoh">
          </div>
        </div>
        <div class="field">
          <label>Value record TXT (opsional)</label>
          <div class="input-group">
            <input type="text" class="txtValue" placeholder="contoh: railway-verify=e36174010f2157d7...">
          </div>
        </div>
      </div>
    `;

    row.querySelector('.remove-record-btn').addEventListener('click', () => {
      removeRecordRow(row);
    });

    return row;
  }

  function refreshRecordUI(){
    const rows = dnsRecordsContainer.querySelectorAll('.dns-record');
    rows.forEach((row, i) => {
      row.querySelector('.dns-record-tag').textContent = 'Record #' + (i + 1);
      const removeBtn = row.querySelector('.remove-record-btn');
      // minimal harus ada 1 record, sembunyikan tombol hapus kalau cuma tersisa 1
      removeBtn.style.display = rows.length > 1 ? 'flex' : 'none';
    });
    recordCountLabel.textContent = rows.length + (rows.length > 1 ? ' records' : ' record');
    addRecordBtn.style.display = rows.length >= MAX_DNS_RECORDS ? 'none' : 'block';
  }

  function addRecordRow(){
    const rows = dnsRecordsContainer.querySelectorAll('.dns-record');
    if (rows.length >= MAX_DNS_RECORDS) return;
    const row = createRecordRow();
    dnsRecordsContainer.appendChild(row);
    refreshRecordUI();
    row.querySelector('.cnameName').focus();
  }

  function removeRecordRow(row){
    const rows = dnsRecordsContainer.querySelectorAll('.dns-record');
    if (rows.length <= 1) return; // minimal 1 record wajib ada
    row.remove();
    refreshRecordUI();
  }

  addRecordBtn.addEventListener('click', addRecordRow);

  // render record pertama saat halaman dimuat
  addRecordRow();

  function collectDnsRecords(){
    const rows = dnsRecordsContainer.querySelectorAll('.dns-record');
    return Array.from(rows).map(row => ({
      cnameName: row.querySelector('.cnameName').value.trim(),
      cnameTarget: row.querySelector('.cnameTarget').value.trim(),
      txtName: row.querySelector('.txtName').value.trim(),
      txtValue: row.querySelector('.txtValue').value.trim(),
    }));
  }

  function validateDnsRecords(records){
    const errors = [];
    records.forEach((r, i) => {
      if (!r.cnameName){
        errors.push('Nama CNAME pada Record #' + (i + 1) + ' wajib diisi.');
      }
      if (!r.cnameTarget){
        errors.push('Target CNAME pada Record #' + (i + 1) + ' wajib diisi.');
      }
      // kalau salah satu TXT diisi, keduanya harus diisi
      if ((r.txtName && !r.txtValue) || (!r.txtName && r.txtValue)) {
        errors.push('Nama dan Value TXT pada Record #' + (i + 1) + ' harus diisi berdua atau dikosongkan berdua.');
      }
    });
    return errors;
  }

  // ============================================================
  // Validasi field lain di sisi klien (cermin ringan dari validasi
  // backend di validate() -- backend tetap sumber kebenaran akhir).
  // ============================================================
  const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
  const TELEGRAM_USERNAME_RE = /^[a-zA-Z0-9_]{5,32}$/;
  const IPV4_RE = /^(\d{1,3}\.){3}\d{1,3}$/;
  const IPV6_RE = /^[0-9a-f:]+$/i;

  function validateOtherFields({ email, contact, telegramUsername, aRecordIp }){
    const errors = [];

    if (!email || !EMAIL_RE.test(email)) {
      errors.push('Email wajib diisi dengan format yang valid.');
    }

    if (!contact || contact.length < 3) {
      errors.push('Kontak (WA/lainnya) wajib diisi.');
    }

    if (telegramUsername && !TELEGRAM_USERNAME_RE.test(telegramUsername)) {
      errors.push('Format username Telegram tidak valid (5-32 karakter: huruf, angka, underscore).');
    }

    if (aRecordIp && !(IPV4_RE.test(aRecordIp) || IPV6_RE.test(aRecordIp))) {
      errors.push('Format IP untuk A record tidak valid (contoh: 192.0.2.10).');
    }

    return errors;
  }

  // Submit form via fetch ke Cloudflare Worker
  const form = document.getElementById('subdomainForm');
  const formMsg = document.getElementById('formMsg');
  const submitBtn = document.getElementById('submitBtn');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    formMsg.className = 'form-msg';

    const dnsRecords = collectDnsRecords();
    const email = document.getElementById('email').value.trim();
    const contact = document.getElementById('contact').value.trim();
    const telegramUsername = document.getElementById('telegramUsername').value.trim().replace(/^@/, '');
    const aRecordIp = document.getElementById('aRecordIp').value.trim();
    const extraRecords = document.getElementById('extraRecords').value.trim();

    const clientErrors = [
      ...validateDnsRecords(dnsRecords),
      ...validateOtherFields({ email, contact, telegramUsername, aRecordIp }),
    ];
    if (clientErrors.length){
      formMsg.classList.add('show', 'error');
      formMsg.innerHTML = '<ul>' + clientErrors.map(e => '<li>' + e + '</li>').join('') + '</ul>';
      return;
    }

    submitBtn.disabled = true;
    submitBtn.textContent = 'Mengirim...';

    const payload = {
      subdomain: subInput.value.trim(),
      targetDomain: document.getElementById('targetDomain').value.trim(),
      email: email,
      contact: contact,
      telegramUsername: telegramUsername,
      // array berisi semua record CNAME/TXT yang diajukan
      // (tiap item sekarang punya cnameName + cnameTarget + txtName + txtValue)
      dnsRecords: dnsRecords,
      // duplikasi record pertama di top-level, untuk kompatibilitas mundur
      // kalau backend Worker kamu masih baca field lama
      // (cnameName/cnameTarget/txtName/txtValue). Aman dikirim dobel --
      // backend versi terbaru membaca dnsRecords lebih dulu.
      cnameName: dnsRecords[0]?.cnameName || '',
      cnameTarget: dnsRecords[0]?.cnameTarget || '',
      txtName: dnsRecords[0]?.txtName || '',
      txtValue: dnsRecords[0]?.txtValue || '',
      aRecordIp: aRecordIp,
      extraRecords: extraRecords,
      purpose: document.getElementById('purpose').value.trim(),
      website: document.querySelector('.honeypot').value,
    };

    try {
      const res = await fetch(WORKER_SUBMIT_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      const data = await res.json();

      if (data.ok) {
        formMsg.classList.add('show', 'success');
        formMsg.innerHTML = 'Permintaan terkirim! <strong>' + data.fullDomain + '</strong> lagi ditinjau. Status approve/reject bakal dikirim otomatis ke email kamu.';
        form.reset();
        updatePreview();
        // reset daftar record kembali ke 1 baris kosong
        dnsRecordsContainer.innerHTML = '';
        addRecordRow();
        // tutup kembali panel opsional
        advancedBody.classList.remove('open');
        advancedToggle.classList.remove('open');
      } else {
        formMsg.classList.add('show', 'error');
        const list = (data.errors || ['Terjadi kesalahan, coba lagi.']).map(e => '<li>' + e + '</li>').join('');
        formMsg.innerHTML = '<ul>' + list + '</ul>';
      }
    } catch (err) {
      formMsg.classList.add('show', 'error');
      formMsg.innerHTML = 'Gagal menghubungi server. Coba lagi sebentar lagi.';
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Kirim permintaan';
    }
  });
</script>

</body>
</html>
