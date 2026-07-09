<?php require_once __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Galeri Foto Publik</title>
<meta name="description" content="Galeri foto publik buat siapa aja yang mau kirim & lihat jepretan random — estetik, receh, atau absurd, semua boleh masuk. Upload gampang, tinggal klik.">
<meta name="keywords" content="poto publik, galeri foto publik, upload foto online, telehub, kirim foto gratis, foto publik online">
<link rel="canonical" href="https://telehub.nfy.fyi/public-poto.php">

<link rel="icon" type="image/png" href="/assets/img/telehub-57.png">
<link rel="shortcut icon" type="image/png" href="/assets/img/telehub-57.png">
<link rel="apple-touch-icon" href="/assets/img/telehub-57.png">

<meta property="og:type" content="website">
<meta property="og:title" content="Galeri Foto Publik">
<meta property="og:description" content="Kirim fotomu ke Poto Publik, galeri random Telehub buat siapa aja — dari yang estetik sampai yang absurd, semua nyampur jadi satu.">
<meta property="og:url" content="https://telehub.nfy.fyi/public-poto.php">
<meta property="og:site_name" content="Telehub">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Galeri Foto Publik">
<meta name="twitter:description" content="Kirim fotomu ke Poto Publik, galeri random Telehub buat siapa aja — dari yang estetik sampai yang absurd, semua nyampur jadi satu.">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#0b0a08; --surface:#161310; --surface-2:#201b15; --paper:#f2ebdc; --text:#f2ebdc; --text-dim:#8c7f6d;
  --accent:#2aabee; --accent-soft:rgba(42,171,238,.14); --accent-dim:#155a80;
  --accent2:#ff8a00; --accent2-soft:rgba(255,138,0,.14); --accent2-dim:#7a4200;
  --border:rgba(242,235,220,.10); --danger:#ff6b6b;
  --rail-w:34px;
}
*{box-sizing:border-box}
html{background:var(--bg)}
body{margin:0;background:
    radial-gradient(ellipse 1000px 600px at 15% -10%, rgba(42,171,238,.06), transparent 55%),
    radial-gradient(ellipse 1000px 600px at 85% 110%, rgba(255,138,0,.06), transparent 55%),
    var(--bg);
  color:var(--text);font-family:'Inter',sans-serif;-webkit-font-smoothing:antialiased;
  padding:0 var(--rail-w);
}
h1,h2{margin:0}
.disp{font-family:'Barlow Condensed',sans-serif;font-weight:800;text-transform:uppercase}
::selection{background:var(--accent);color:#fff}

/* ---- page loader (full page, real — nunggu fetch pertama kelar) ---- */
#pageLoader{
  position:fixed;inset:0;z-index:9999;
  background:
    radial-gradient(ellipse 1000px 600px at 15% -10%, rgba(42,171,238,.08), transparent 55%),
    radial-gradient(ellipse 1000px 600px at 85% 110%, rgba(255,138,0,.08), transparent 55%),
    var(--bg);
  display:flex;flex-direction:column;align-items:center;justify-content:center;gap:22px;
  opacity:1;transition:opacity .45s ease;
}
#pageLoader.hide{opacity:0;pointer-events:none}
.pl-reel{position:relative;width:64px;height:64px}
.pl-reel svg{width:100%;height:100%;animation:pl-spin 1.1s linear infinite}
@keyframes pl-spin{to{transform:rotate(360deg)}}
.pl-brand{font-family:'JetBrains Mono',monospace;font-size:11px;letter-spacing:.18em;text-transform:uppercase;color:var(--text-dim)}
.pl-brand b{color:var(--accent)}
.pl-status{font-family:'JetBrains Mono',monospace;font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--text-dim);min-height:14px}
.pl-track{width:180px;height:3px;border-radius:99px;background:rgba(242,235,220,.08);overflow:hidden}
.pl-fill{height:100%;width:30%;border-radius:99px;background:linear-gradient(90deg,var(--accent),var(--accent2));
  animation:pl-indeterminate 1.3s ease-in-out infinite}
@keyframes pl-indeterminate{
  0%{transform:translateX(-100%)}
  50%{transform:translateX(120%)}
  100%{transform:translateX(320%)}
}
@media (prefers-reduced-motion: reduce){
  .pl-reel svg{animation:none}
  .pl-fill{animation:none;width:100%;transform:none}
}

/* ---- film rails, fixed to viewport edges ---- */
.rail{position:fixed;top:0;bottom:0;width:var(--rail-w);z-index:5;
  background-color:#1c1710;
  background-image:radial-gradient(circle at 50% 11px, var(--bg) 5px, transparent 5.6px);
  background-size:100% 22px;background-repeat:repeat-y;
  box-shadow:inset 0 0 0 1px rgba(242,235,220,.05);
}
.rail.left{left:0;border-right:1px solid rgba(42,171,238,.4);box-shadow:inset -6px 0 16px -8px rgba(42,171,238,.35)}
.rail.right{right:0;border-left:1px solid rgba(255,138,0,.4);box-shadow:inset 6px 0 16px -8px rgba(255,138,0,.35)}
@media(max-width:760px){:root{--rail-w:16px}.rail{background-size:100% 16px}.rail::before{display:none}}

/* ---- tombol kembali, mengambang di pojok kiri atas ---- */
.back-btn{position:fixed;top:20px;left:calc(var(--rail-w) + 16px);z-index:50;
  display:inline-flex;align-items:center;gap:8px;
  background:rgba(22,19,16,.68);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);
  border:1px solid var(--border);color:var(--text);
  padding:10px 18px 10px 13px;border-radius:999px;
  font-family:'Inter',sans-serif;font-weight:500;font-size:13.5px;
  text-decoration:none;cursor:pointer;
  transition:border-color .2s ease, color .2s ease, transform .2s ease, background .2s ease, box-shadow .2s ease;
}
.back-btn .ring{width:22px;height:22px;border-radius:50%;border:1.5px solid currentColor;
  display:grid;place-items:center;flex:none;transition:border-color .2s ease}
.back-btn svg{width:13px;height:13px;display:block;transition:transform .25s ease}
.back-btn:hover{border-color:var(--accent);color:var(--accent);transform:translateX(-4px);
  background:rgba(22,19,16,.88);box-shadow:0 8px 22px rgba(42,171,238,.18)}
.back-btn:hover svg{transform:translateX(-2px)}
.back-btn:active{transform:translateX(-2px) scale(.97)}
@media(max-width:560px){
  .back-btn{top:12px;left:calc(var(--rail-w) + 8px);padding:8px 14px 8px 10px;font-size:12.5px}
  .back-btn .ring{width:19px;height:19px}
  .back-btn svg{width:11px;height:11px}
}

/* ---- hero title area wrapper (diagonal blue/orange background, like footer) ---- */
.hero-section{position:relative;overflow:hidden;background:#0c2233}
.hero-section::before{content:'';position:absolute;inset:0;z-index:0;
  background:var(--accent2);
  clip-path:polygon(58% 0, 100% 0, 100% 100%, 30% 100%);
}
.hero-section::after{content:'';position:absolute;inset:0;z-index:0;pointer-events:none;
  background:
    radial-gradient(ellipse 900px 500px at 10% -10%, rgba(42,171,238,.12), transparent 55%);
}
.hero-section > *{position:relative;z-index:1}
@media(max-width:560px){
  .hero-section::before{clip-path:polygon(0 78%, 100% 45%, 100% 100%, 0 100%)}
}
/* toolbar + roll-count sit outside the diagonal, plain background */
.hero-tail{background:#0b0a08}

/* ---- header ---- */
.hero{padding:64px 24px 36px;text-align:center;max-width:760px;margin:0 auto}
.hero .eyebrow{font-family:'JetBrains Mono',monospace;font-size:12px;letter-spacing:.16em;text-transform:uppercase;
  color:var(--accent);margin-bottom:16px;display:inline-flex;align-items:center;gap:10px}
.hero .eyebrow::before,.hero .eyebrow::after{content:'';width:22px;height:1px;background:var(--accent-dim)}
.hero h1{font-size:clamp(48px,9vw,92px);line-height:.92;letter-spacing:-.01em}
.hero p{color:rgba(242,235,220,.82);font-size:16.5px;font-weight:500;margin:20px auto 0;
  line-height:1.75;max-width:520px;letter-spacing:.01em;
  text-shadow:0 2px 12px rgba(0,0,0,.35)}
.hero p b{color:#fff;font-weight:700}

.toolbar{display:flex;justify-content:center;margin:0 24px 8px}

.shutter-btn{display:inline-flex;align-items:center;gap:12px;background:var(--accent);color:#fff;border:none;
  padding:15px 26px 15px 20px;border-radius:999px;font-weight:600;font-size:14.5px;cursor:pointer;
  transition:transform .18s ease, box-shadow .18s ease;position:relative;isolation:isolate;
  box-shadow:0 4px 14px rgba(42,171,238,.2);
}
.shutter-btn::after{
  content:'';position:absolute;inset:-6px;border-radius:inherit;pointer-events:none;z-index:-1;
  box-shadow:0 0 22px 9px rgba(42,171,238,.45);
  opacity:0;will-change:opacity;
  animation:shutter-glow-pulse 2.2s ease-in-out infinite;
}
.shutter-btn:hover{transform:translateY(-2px);box-shadow:0 10px 30px rgba(42,171,238,.35)}
.shutter-btn:hover::after{animation-play-state:paused;opacity:0}
@keyframes shutter-glow-pulse{
  0%,100%{opacity:0}
  50%{opacity:1}
}
@media (prefers-reduced-motion: reduce){.shutter-btn::after{animation:none}}
.shutter-btn .ap{width:22px;height:22px;border-radius:50%;border:2px solid #fff;position:relative;flex:none;
  display:grid;place-items:center;transition:transform .5s ease}
.shutter-btn .ap::before{content:'';width:6px;height:6px;border-radius:50%;background:#fff}
.shutter-btn:hover .ap{transform:rotate(70deg)}

.roll-count{text-align:center;font-family:'JetBrains Mono',monospace;font-size:11px;color:var(--text-dim);
  letter-spacing:.08em;margin:22px 0 30px;text-transform:uppercase}

.divider{height:1px;background:linear-gradient(90deg,transparent,var(--border) 15%,var(--border) 85%,transparent);
  max-width:1600px;margin:0 auto}

/* ---- gallery / contact sheet ----
   FIX: dulu pakai CSS columns (masonry) jadi tinggi tiap kartu ngikutin
   rasio foto asli -> kelihatan ga rata. Sekarang pakai CSS Grid biasa,
   dan gambar dikunci ke aspect-ratio tetap + object-fit:cover (lihat
   .frame img di bawah), jadi semua kartu selalu rata tinggi & lebarnya
   berapa pun rasio foto aslinya. */
.gallery{display:grid;grid-template-columns:repeat(5,1fr);gap:18px;padding:36px 24px 90px;max-width:1600px;margin:0 auto}
@media(max-width:1300px){.gallery{grid-template-columns:repeat(4,1fr)}}
@media(max-width:1000px){.gallery{grid-template-columns:repeat(3,1fr)}}
@media(max-width:700px){.gallery{grid-template-columns:repeat(2,1fr);gap:12px}}
@media(max-width:420px){.gallery{grid-template-columns:1fr}}

.frame{display:flex;flex-direction:column;border-radius:3px;overflow:hidden;background:var(--surface);
  border:1px solid var(--border);cursor:pointer;position:relative;transition:transform .2s ease, border-color .2s ease}
.frame:hover{transform:translateY(-3px);border-color:rgba(42,171,238,.35)}
.sprocket-strip{height:9px;background:#1c1710;
  background-image:radial-gradient(circle at 9px 4.5px, var(--surface) 2.4px, transparent 2.9px);
  background-size:18px 9px;background-repeat:repeat-x}
.frame:hover .sprocket-strip{background-image:radial-gradient(circle at 9px 4.5px, var(--surface) 2.4px, transparent 2.9px)}
.frame img{width:100%;display:block;background:#000;aspect-ratio:4/3;object-fit:cover}
.frame-tag{display:flex;align-items:center;justify-content:space-between;gap:8px;
  padding:9px 11px;background:var(--surface)}
.frame-tag .no{font-family:'JetBrains Mono',monospace;font-size:11px;color:var(--accent2);letter-spacing:.03em;flex:none}
.frame-tag .meta{min-width:0;text-align:right}
.frame-tag .t{font-size:12.5px;font-weight:500;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.frame-tag .u{font-size:10.5px;color:var(--text-dim);font-family:'JetBrains Mono',monospace;margin-top:1px}

.empty{max-width:360px;margin:40px auto 100px;padding:0 24px;text-align:center}
.empty .box{border:1.5px dashed var(--border);border-radius:6px;padding:40px 20px;position:relative}
.empty .box::before,.empty .box::after,.empty .box .c2::before,.empty .box .c2::after{content:'';position:absolute;width:14px;height:14px;border-color:var(--accent2-dim);border-style:solid}
.empty .box::before{top:8px;left:8px;border-width:2px 0 0 2px}
.empty .box::after{top:8px;right:8px;border-width:2px 2px 0 0}
.empty .box .c2::before{bottom:8px;left:8px;border-width:0 0 2px 2px;position:absolute}
.empty .box .c2::after{bottom:8px;right:8px;border-width:0 2px 2px 0;position:absolute}
.empty p{color:var(--text-dim);font-size:13.5px;line-height:1.6;margin:0}
.empty .label{font-family:'JetBrains Mono',monospace;font-size:11px;color:var(--accent2);letter-spacing:.1em;text-transform:uppercase;margin-bottom:10px;display:block}

.pager{display:flex;align-items:center;justify-content:center;gap:22px;padding:10px 24px 60px}
.pager-btn{display:inline-flex;align-items:center;gap:8px;background:var(--surface);color:var(--text);
  border:1px solid var(--border);padding:12px 20px;border-radius:999px;font-weight:500;font-size:13.5px;
  cursor:pointer;transition:border-color .15s ease, color .15s ease, transform .15s ease;font-family:'Inter',sans-serif}
.pager-btn svg{width:16px;height:16px}
.pager-btn:hover:not(:disabled){border-color:var(--accent);color:var(--accent);transform:translateY(-1px)}
.pager-btn:disabled{opacity:.35;cursor:not-allowed}
.pager-status{font-family:'JetBrains Mono',monospace;font-size:11.5px;color:var(--text-dim);letter-spacing:.08em;text-transform:uppercase;min-width:64px;text-align:center}

.loading-row{text-align:center;color:var(--text-dim);font-size:12px;padding:24px;font-family:'JetBrains Mono',monospace;letter-spacing:.06em;text-transform:uppercase}
.loading-row::before{content:'▸ '}

/* ---- upload modal ---- */
.modal-backdrop{position:fixed;inset:0;background:rgba(6,5,4,.82);backdrop-filter:blur(4px);
  display:none;align-items:center;justify-content:center;z-index:100;padding:20px}
.modal-backdrop.open{display:flex}
.modal{background:var(--surface);border:1px solid var(--border);border-radius:10px;width:100%;max-width:440px;padding:30px;position:relative;z-index:0}

.modal::before,.modal::after{content:'';position:absolute;inset:-16px;border-radius:22px;z-index:-1;
  opacity:.85;
  -webkit-mask-image:radial-gradient(closest-side, transparent 60%, #000 82%, transparent 100%);
  mask-image:radial-gradient(closest-side, transparent 60%, #000 82%, transparent 100%);
  will-change:transform;transform:translateZ(0);backface-visibility:hidden}
.modal::before{background:conic-gradient(from 0deg, var(--accent2), #ffd166, var(--accent), var(--accent2));
  animation:modal-glow-spin 5s linear infinite}
.modal::after{background:conic-gradient(from 180deg, var(--accent), #ff6b6b, var(--accent2), var(--accent));
  animation:modal-glow-spin 7s linear infinite reverse;opacity:.5}
@keyframes modal-glow-spin{to{transform:translateZ(0) rotate(360deg)}}
@media (prefers-reduced-motion: reduce){.modal::before,.modal::after{animation:none}}

.modal-topdash{position:absolute;top:0;left:26px;right:26px;height:1px;
  background:repeating-linear-gradient(90deg, var(--accent2-dim) 0 3px, transparent 3px 9px);
  pointer-events:none}

.modal .eyebrow2{font-family:'JetBrains Mono',monospace;font-size:11px;color:var(--accent2);letter-spacing:.12em;text-transform:uppercase;margin-bottom:8px;display:block}
.modal h2{font-family:'Barlow Condensed',sans-serif;font-weight:800;text-transform:uppercase;font-size:30px;letter-spacing:-.01em}
.modal .sub{color:var(--text-dim);font-size:13px;margin:8px 0 22px;line-height:1.5}
.modal-close{position:absolute;top:20px;right:20px;background:none;border:1px solid var(--border);border-radius:50%;
  width:30px;height:30px;color:var(--text-dim);font-size:16px;cursor:pointer;line-height:1;display:grid;place-items:center}
.modal-close:hover{border-color:var(--accent);color:var(--accent)}

@media(max-width:480px){
  .modal-backdrop{padding:12px}
  .modal{padding:22px 16px}
}

.dropzone{border:1.5px dashed var(--border);border-radius:8px;padding:30px 16px;text-align:center;cursor:pointer;
  position:relative;transition:border-color .15s ease, background .15s ease;margin-bottom:6px}
.dropzone::before,.dropzone::after{content:'';position:absolute;top:9px;width:16px;height:16px;border-color:var(--text-dim);border-style:solid;transition:border-color .15s ease}
.dropzone::before{left:9px;border-width:2px 0 0 2px}
.dropzone::after{right:9px;border-width:2px 2px 0 0}
.dropzone.drag{border-color:var(--accent);background:var(--accent-soft)}
.dropzone.drag::before,.dropzone.drag::after{border-color:var(--accent)}
.dropzone p{color:var(--text-dim);font-size:12.5px;margin:8px 0 0;line-height:1.5}
.dropzone .icon{font-size:24px;opacity:.8}
.dropzone img.preview{max-height:150px;border-radius:4px;margin-top:10px}

label.field{display:block;font-family:'JetBrains Mono',monospace;font-size:10.5px;text-transform:uppercase;letter-spacing:.08em;color:var(--text-dim);margin-bottom:7px;margin-top:18px}
input.field-input{width:100%;background:var(--surface-2);border:1px solid var(--border);color:var(--text);
  padding:11px 12px;border-radius:6px;font-family:'Inter',sans-serif;font-size:14px}
input.field-input:focus{outline:none;border-color:var(--accent)}

.recaptcha-wrap{
  margin-top:18px;
  width:100%;
  overflow:hidden;
  display:flex;
  justify-content:flex-start;
}
.g-recaptcha{
  transform-origin:0 0;
}

.submit-btn{margin-top:24px;width:100%;background:var(--accent);color:#fff;border:none;padding:14px;
  border-radius:6px;font-weight:600;font-size:14px;cursor:pointer;letter-spacing:.01em;transition:filter .15s ease}
.submit-btn:hover:not(:disabled){filter:brightness(1.08)}
.submit-btn:disabled{opacity:.4;cursor:not-allowed}
.notice{margin-top:14px;font-size:12.5px;padding:10px 12px;border-radius:6px;display:none;font-family:'JetBrains Mono',monospace;line-height:1.6}
.notice.ok{display:block;background:rgba(76,201,138,.12);color:#4cc98a}
.notice.err{display:block;background:rgba(255,107,107,.12);color:var(--danger)}
.notice.warn{display:block;background:rgba(255,209,102,.12);color:#ffd166}

.upload-loading-overlay{
  position:absolute;inset:0;background:rgba(11,10,8,.88);backdrop-filter:blur(5px);-webkit-backdrop-filter:blur(5px);
  border-radius:10px;display:none;flex-direction:column;align-items:center;justify-content:center;gap:16px;
  z-index:50;padding:24px;text-align:center;
}
.upload-loading-overlay.show{display:flex}
.upload-spinner{width:42px;height:42px;border-radius:50%;border:3px solid rgba(42,171,238,.18);
  border-top-color:var(--accent);animation:upload-spin .8s linear infinite;will-change:transform}
@keyframes upload-spin{to{transform:rotate(360deg)}}
.upload-loading-text{font-size:13.5px;font-weight:600;color:var(--text)}
.upload-progress-track{width:180px;height:5px;border-radius:99px;background:rgba(242,235,220,.1);overflow:hidden}
.upload-progress-fill{height:100%;width:0%;background:linear-gradient(90deg,var(--accent),var(--accent-dim));
  border-radius:99px;transition:width .15s ease-out}
.upload-progress-pct{font-family:'JetBrains Mono',monospace;font-size:11px;color:var(--text-dim)}
@media (prefers-reduced-motion: reduce){.upload-spinner{animation:none}}

/* ---- lightbox ---- */
.lightbox{position:fixed;inset:0;background:rgba(4,3,2,.94);display:none;align-items:center;justify-content:center;z-index:200;padding:24px}
.lightbox.open{display:flex}
.lightbox img{max-width:min(88vw,900px);max-height:78vh;border-radius:4px;box-shadow:0 30px 80px rgba(0,0,0,.6)}
.lightbox-info{position:absolute;bottom:30px;left:0;right:0;text-align:center;font-family:'JetBrains Mono',monospace;font-size:12px;color:var(--text-dim);letter-spacing:.04em}
.lightbox-info b{color:var(--accent2);font-weight:500}
.lightbox-close{position:absolute;top:24px;right:28px;background:none;border:1px solid var(--border);border-radius:50%;
  width:38px;height:38px;color:#fff;font-size:18px;cursor:pointer;display:grid;place-items:center}
.lightbox-close:hover{border-color:var(--accent);color:var(--accent)}

@media (prefers-reduced-motion: reduce){*{transition:none!important}}
</style>
</head>
<body>

<div id="pageLoader">
  <div class="pl-reel">
    <svg viewBox="0 0 64 64" fill="none">
      <circle cx="32" cy="32" r="26" stroke="rgba(242,235,220,.12)" stroke-width="4"></circle>
      <circle cx="32" cy="32" r="26" stroke="#2aabee" stroke-width="4" stroke-linecap="round" stroke-dasharray="60 200"></circle>
    </svg>
  </div>
  <div class="pl-brand"><b>Telehub</b> · Poto Publik</div>
  <div class="pl-track"><div class="pl-fill"></div></div>
  <div class="pl-status" id="pageLoaderStatus">memuat rol pertama…</div>
</div>

<div class="rail left"></div>
<div class="rail right"></div>

<a href="/index.php" class="back-btn" aria-label="Kembali ke beranda">
  <span class="ring">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
  </span>
  Kembali
</a>

<div class="hero-section">
  <div class="hero">
    <span class="eyebrow">Telehub</span>
    <h1 class="disp">Poto<br>Publik</h1>
    <p>Nggak ada kurasi ribet, cuma ditinjau bentar biar nggak asal. <b>Foto receh, foto estetik, foto kucing tetangga</b> — semua boleh masuk frame.</p>
  </div>
</div>

<div class="hero-tail">
  <div class="toolbar">
    <button class="shutter-btn" id="openUpload">
      <span class="ap"></span>
      Kirim Foto
    </button>
  </div>
  <div class="roll-count" id="rollCount">memuat rol…</div>
</div>

<div class="divider"></div>

<div class="gallery" id="gallery"></div>
<div class="empty" id="emptyState" style="display:none">
  <div class="box"><div class="c2"></div>
    <span class="label">Frame kosong</span>
    <p>Belum ada foto yang disetujui. Jadilah yang pertama isi rol ini.</p>
  </div>
</div>

<div class="pager" id="pager" style="display:none">
  <button class="pager-btn" id="prevBtn">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
    Sebelumnya
  </button>
  <span class="pager-status" id="pagerStatus">Rol 1</span>
  <button class="pager-btn" id="nextBtn">
    Selanjutnya
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
  </button>
</div>
<div class="loading-row" id="loadingRow" style="display:none">memutar rol</div>

<div class="modal-backdrop" id="uploadModal">
  <div class="modal" id="uploadModalInner">
    <div class="modal-topdash"></div>

    <div class="upload-loading-overlay" id="uploadLoadingOverlay">
      <div class="upload-spinner"></div>
      <div class="upload-loading-text" id="uploadLoadingText">Mengunggah foto...</div>
      <div class="upload-progress-track">
        <div class="upload-progress-fill" id="uploadProgressFill"></div>
      </div>
      <div class="upload-progress-pct" id="uploadProgressPct">0%</div>
    </div>

    <button class="modal-close" id="closeUpload">&times;</button>
    <span class="eyebrow2">Frame baru</span>
    <h2 class="disp">Kirim Foto</h2>
    <div class="sub">Foto akan tampil publik setelah disetujui. Maks 5MB.</div>
    <form id="uploadForm">
      <div class="dropzone" id="dropzone">
        <div class="icon">◎</div>
        <p id="dzText">Klik atau seret foto ke sini<br>(JPG/PNG/WEBP, maks 5MB)</p>
        <img id="preview" class="preview" style="display:none">
      </div>
      <input type="file" name="photo" id="fileInput" accept="image/jpeg,image/png,image/webp,image/gif" hidden>

      <label class="field">Judul foto (opsional)</label>
      <input class="field-input" type="text" name="title" maxlength="150" placeholder="cth: sunset di pantai">

      <label class="field">Nama kamu (opsional)</label>
      <input class="field-input" type="text" name="uploader_name" maxlength="100" placeholder="cth: budi">

      <div class="recaptcha-wrap" id="recaptchaWrap">
        <div class="g-recaptcha" id="recaptchaBox" data-sitekey="<?= htmlspecialchars(RECAPTCHA_SITE_KEY) ?>"></div>
      </div>

      <button class="submit-btn" type="submit" id="submitBtn" disabled>Kirim untuk ditinjau</button>
      <div class="notice" id="notice"></div>
    </form>
  </div>
</div>

<div class="lightbox" id="lightbox">
  <button class="lightbox-close" id="closeLightbox">&times;</button>
  <img id="lightboxImg" src="" alt="">
  <div class="lightbox-info" id="lightboxInfo"></div>
</div>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
const API_BASE = 'https://public-poto-api.internetdnsofficial.workers.dev';

const gallery = document.getElementById('gallery');
const rollCount = document.getElementById('rollCount');
const pager = document.getElementById('pager');
const pagerStatus = document.getElementById('pagerStatus');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');
const perPage = 24;
let page = 0, loading = false, hasNext = false;

function frameNo(n){ return '#' + String(n).padStart(3,'0'); }

/* ---- FIX (XSS): title & uploader_name adalah input user yang cuma
   ditinjau sekilas oleh admin sebelum "approve" -- itu bukan sanitasi
   konten. Sebelumnya cuma tanda kutip yang di-escape di atribut alt,
   sementara .t / .u / lightboxInfo diselipkan mentah-mentah ke innerHTML,
   jadi title semacam <img src=x onerror=alert(1)> bisa jalan di browser
   semua pengunjung. escapeHtml() dipakai di semua tempat yang menampilkan
   teks user sebelum masuk innerHTML. */
function escapeHtml(str){
  if (str === null || str === undefined) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function cardHTML(p, idx){
  const div = document.createElement('div');
  div.className = 'frame';

  const safeTitle    = escapeHtml(p.title || '');
  const safeUploader = escapeHtml(p.uploader_name || '');
  const safeFilename = encodeURIComponent(p.filename || '');

  div.innerHTML = `
    <div class="sprocket-strip"></div>
    <img src="${API_BASE}/photos/${safeFilename}" loading="lazy" alt="${safeTitle}">
    <div class="frame-tag">
      <span class="no">${frameNo(idx)}</span>
      <span class="meta">
        <div class="t">${safeTitle ? safeTitle : 'Tanpa judul'}</div>
        <div class="u">${safeUploader ? '@' + safeUploader : 'anonim'}</div>
      </span>
    </div>
    <div class="sprocket-strip"></div>`;
  div.addEventListener('click', () => openLightbox(p.filename, p.title, p.uploader_name, frameNo(idx)));
  return div;
}

/* ---- page loader: nutup layar penuh sampe fetch pertama beneran kelar ---- */
const pageLoader = document.getElementById('pageLoader');
const pageLoaderStatus = document.getElementById('pageLoaderStatus');
let pageLoaderHidden = false;

function setPageLoaderStatus(text){
  if (pageLoaderStatus) pageLoaderStatus.textContent = text;
}

function hidePageLoader(){
  if (pageLoaderHidden || !pageLoader) return;
  pageLoaderHidden = true;
  pageLoader.classList.add('hide');
  setTimeout(() => { pageLoader.remove(); }, 500);
}

// jaga-jaga: kalau API bener-bener nggak nyaut lama banget (misal 15 detik),
// tetep lepas loader biar halaman nggak keliatan macet total.
const pageLoaderFailSafe = setTimeout(() => {
  setPageLoaderStatus('memuat lebih lama dari biasanya…');
}, 6000);
const pageLoaderHardStop = setTimeout(hidePageLoader, 15000);

async function loadPage(p){
  if (loading) return;
  loading = true;
  gallery.innerHTML = '';
  document.getElementById('emptyState').style.display = 'none';
  pager.style.display = 'none';
  document.getElementById('loadingRow').style.display = 'block';
  prevBtn.disabled = true;
  nextBtn.disabled = true;

  const offset = p * perPage;
  try {
    const res = await fetch(`${API_BASE}/api/photos?offset=${offset}&limit=${perPage}`);
    const data = await res.json();
    if (data.photos && data.photos.length){
      data.photos.forEach((ph, i) => gallery.appendChild(cardHTML(ph, offset + i + 1)));
      hasNext = data.photos.length === perPage;
      page = p;
      pagerStatus.textContent = 'Rol ' + (page + 1);
      rollCount.textContent = (offset + data.photos.length) + ' frame · rol ' + (page + 1);
      if (page === 0 && !hasNext){
        pager.style.display = 'none';
      } else {
        pager.style.display = 'flex';
        prevBtn.disabled = page === 0;
        nextBtn.disabled = !hasNext;
      }
    } else {
      if (p === 0){
        document.getElementById('emptyState').style.display = 'block';
        rollCount.textContent = '0 frame';
      } else {
        loading = false;
        document.getElementById('loadingRow').style.display = 'none';
        return loadPage(p - 1);
      }
    }
  } catch(e){ rollCount.textContent = 'gagal memuat rol'; }
  loading = false;
  document.getElementById('loadingRow').style.display = 'none';
}

prevBtn.addEventListener('click', () => { if (page > 0) loadPage(page - 1); window.scrollTo({top:0, behavior:'smooth'}); });
nextBtn.addEventListener('click', () => { if (hasNext) loadPage(page + 1); window.scrollTo({top:0, behavior:'smooth'}); });

// panggil rol pertama, dan baru copot page loader setelah fetch-nya beneran selesai
// (sukses atau gagal, dua-duanya tetep melepas loader supaya halaman nggak nyangkut)
loadPage(0).finally(() => {
  clearTimeout(pageLoaderFailSafe);
  clearTimeout(pageLoaderHardStop);
  hidePageLoader();
});

function openLightbox(filename, title, uploader, no){
  const safeFilename = encodeURIComponent(filename || '');
  const safeTitle    = escapeHtml(title);
  const safeUploader = escapeHtml(uploader);
  document.getElementById('lightboxImg').src = API_BASE + '/photos/' + safeFilename;
  document.getElementById('lightboxInfo').innerHTML = `<b>${no}</b> &nbsp;·&nbsp; ${safeTitle || 'Tanpa judul'} &nbsp;·&nbsp; ${safeUploader ? '@' + safeUploader : 'anonim'}`;
  document.getElementById('lightbox').classList.add('open');
}
document.getElementById('closeLightbox').addEventListener('click', () => document.getElementById('lightbox').classList.remove('open'));
document.getElementById('lightbox').addEventListener('click', (e) => { if (e.target.id === 'lightbox') e.currentTarget.classList.remove('open'); });

const modal = document.getElementById('uploadModal');
document.getElementById('openUpload').addEventListener('click', () => {
  modal.classList.add('open');
  requestAnimationFrame(() => requestAnimationFrame(scaleRecaptcha));
  checkConnectionSpeed();
});
document.getElementById('closeUpload').addEventListener('click', () => modal.classList.remove('open'));
modal.addEventListener('click', (e) => { if (e.target === modal) modal.classList.remove('open'); });

const dropzone = document.getElementById('dropzone');
const fileInput = document.getElementById('fileInput');
const preview = document.getElementById('preview');
const dzText = document.getElementById('dzText');
const submitBtn = document.getElementById('submitBtn');
const notice = document.getElementById('notice');
let chosenFile = null;
const MAX_BYTES = 5 * 1024 * 1024;

dropzone.addEventListener('click', () => fileInput.click());
['dragover','dragenter'].forEach(ev => dropzone.addEventListener(ev, e => { e.preventDefault(); dropzone.classList.add('drag'); }));
['dragleave','drop'].forEach(ev => dropzone.addEventListener(ev, e => { e.preventDefault(); dropzone.classList.remove('drag'); }));
dropzone.addEventListener('drop', e => { if (e.dataTransfer.files[0]) setFile(e.dataTransfer.files[0]); });
fileInput.addEventListener('change', () => { if (fileInput.files[0]) setFile(fileInput.files[0]); });

function checkConnectionSpeed(){
  const conn = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
  if (!conn) return;
  const slow = conn.effectiveType === 'slow-2g' || conn.effectiveType === '2g' || conn.saveData;
  if (slow){
    notice.textContent = 'Koneksi kamu terdeteksi lambat. Foto akan otomatis dikompres, tapi upload mungkin tetap butuh waktu lebih lama.';
    notice.className = 'notice warn';
  }
}

function compressImage(file, maxDim = 1600, quality = 0.8){
  return new Promise((resolve) => {
    if (file.type === 'image/gif'){ resolve(file); return; }

    const img = new Image();
    const objectUrl = URL.createObjectURL(file);

    img.onload = () => {
      let { width, height } = img;
      if (width > maxDim || height > maxDim){
        if (width > height){ height = Math.round(height * maxDim / width); width = maxDim; }
        else { width = Math.round(width * maxDim / height); height = maxDim; }
      }
      const canvas = document.createElement('canvas');
      canvas.width = width;
      canvas.height = height;
      const ctx = canvas.getContext('2d');
      ctx.drawImage(img, 0, 0, width, height);

      canvas.toBlob((blob) => {
        URL.revokeObjectURL(objectUrl);
        if (!blob || blob.size >= file.size){
          resolve(file);
          return;
        }
        const compressed = new File(
          [blob],
          file.name.replace(/\.[^.]+$/, '') + '.jpg',
          { type: 'image/jpeg' }
        );
        resolve(compressed);
      }, 'image/jpeg', quality);
    };

    img.onerror = () => { URL.revokeObjectURL(objectUrl); resolve(file); };
    img.src = objectUrl;
  });
}

async function setFile(file){
  if (!file.type.startsWith('image/')) return;

  dzText.textContent = 'Mengompres foto...';
  submitBtn.disabled = true;

  let finalFile = file;
  if (file.size > 800 * 1024){
    finalFile = await compressImage(file);
  }

  if (finalFile.size > MAX_BYTES){
    dzText.textContent = 'File masih terlalu besar setelah dikompres (maks 5MB). Coba foto lain.';
    chosenFile = null;
    submitBtn.disabled = true;
    return;
  }

  chosenFile = finalFile;
  const reader = new FileReader();
  reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; dzText.textContent = file.name; };
  reader.readAsDataURL(finalFile);
  submitBtn.disabled = false;
}

const uploadOverlay      = document.getElementById('uploadLoadingOverlay');
const uploadProgressFill = document.getElementById('uploadProgressFill');
const uploadProgressPct  = document.getElementById('uploadProgressPct');
const uploadLoadingText  = document.getElementById('uploadLoadingText');

function setUploadProgress(pct){
  pct = Math.max(0, Math.min(100, Math.round(pct)));
  uploadProgressFill.style.width = pct + '%';
  uploadProgressPct.textContent = pct + '%';
}

function resetCaptcha(){
  if (window.grecaptcha){
    try { grecaptcha.reset(); } catch (e) {}
  }
}

const RECAPTCHA_NATURAL_W = 304;
const RECAPTCHA_NATURAL_H = 78;

function scaleRecaptcha(){
  const wrap = document.getElementById('recaptchaWrap');
  const box  = document.getElementById('recaptchaBox');
  if (!wrap || !box) return;
  const availableWidth = wrap.clientWidth;
  if (!availableWidth) return;
  const scale = Math.min(1, availableWidth / RECAPTCHA_NATURAL_W);
  box.style.transform = `scale(${scale})`;
  wrap.style.height = Math.ceil(RECAPTCHA_NATURAL_H * scale) + 'px';
}

const recaptchaWatcher = setInterval(() => {
  const box = document.getElementById('recaptchaBox');
  if (box && box.querySelector('iframe')){
    clearInterval(recaptchaWatcher);
    scaleRecaptcha();
  }
}, 150);

window.addEventListener('resize', scaleRecaptcha);
window.addEventListener('orientationchange', () => setTimeout(scaleRecaptcha, 200));

const MAX_AUTO_RETRIES = 2;
const RETRY_DELAY_MS = 1500;

function doUpload(formData, captchaValue, attempt){
  const xhr = new XMLHttpRequest();
  xhr.open('POST', `${API_BASE}/api/upload`, true);

  uploadOverlay.classList.add('show');
  submitBtn.setAttribute('disabled', 'disabled');
  const attemptLabel = attempt > 0 ? ` (percobaan ${attempt + 1}/${MAX_AUTO_RETRIES + 1})` : '';
  uploadLoadingText.textContent = 'Mengunggah foto...' + attemptLabel;
  setUploadProgress(0);

  xhr.upload.addEventListener('progress', function (ev) {
    if (ev.lengthComputable) {
      const pct = (ev.loaded / ev.total) * 100;
      setUploadProgress(pct);
      if (pct >= 100) {
        uploadLoadingText.textContent = 'Menunggu server...';
      }
    }
  });

  xhr.upload.addEventListener('load', function () {
    setUploadProgress(100);
    uploadLoadingText.textContent = 'Menunggu server...';
  });

  xhr.onreadystatechange = function () {
    if (xhr.readyState !== 4) return;

    let data;
    try {
      data = JSON.parse(xhr.responseText);
    } catch (err) {
      uploadOverlay.classList.remove('show');
      submitBtn.removeAttribute('disabled');
      notice.textContent = 'Terjadi kesalahan tak terduga. Silakan coba lagi.';
      notice.className = 'notice err';
      resetCaptcha();
      return;
    }

    if (xhr.status === 200 && data.ok) {
      uploadOverlay.classList.remove('show');
      submitBtn.removeAttribute('disabled');
      notice.textContent = 'Foto terkirim! Menunggu persetujuan admin.';
      notice.className = 'notice ok';
      document.getElementById('uploadForm').reset();
      preview.style.display = 'none';
      dzText.innerHTML = 'Klik atau seret foto ke sini<br>(JPG/PNG/WEBP, maks 5MB)';
      chosenFile = null;
      resetCaptcha();
      setTimeout(() => { modal.classList.remove('open'); notice.className = 'notice'; }, 1800);
    } else {
      uploadOverlay.classList.remove('show');
      submitBtn.removeAttribute('disabled');
      notice.textContent = data.error || 'Gagal mengirim foto.';
      notice.className = 'notice err';
      resetCaptcha();
    }
  };

  xhr.onerror = function () {
    if (attempt < MAX_AUTO_RETRIES) {
      uploadLoadingText.textContent = 'Koneksi terputus, mencoba lagi...';
      setTimeout(() => doUpload(formData, captchaValue, attempt + 1), RETRY_DELAY_MS);
      return;
    }
    uploadOverlay.classList.remove('show');
    submitBtn.removeAttribute('disabled');
    notice.innerHTML = 'Terjadi kesalahan jaringan saat mengirim foto. Kemungkinan penyebabnya:<br>' +
      '• Sinyal kamu lambat/putus-putus<br>' +
      '• Provider/jaringan kamu memblokir atau membatasi koneksi ke server<br><br>' +
      'Coba salah satu ini lalu kirim ulang: pindah ke WiFi, cari sinyal yang lebih stabil, atau coba pakai  VPN/proxy.';
    notice.className = 'notice err';
    resetCaptcha();
  };

  xhr.send(formData);
}

document.getElementById('uploadForm').addEventListener('submit', function (e) {
  e.preventDefault();
  if (!chosenFile) return;

  notice.className = 'notice';

  let captchaValue = null;
  if (window.grecaptcha) {
    captchaValue = grecaptcha.getResponse();
    if (!captchaValue) {
      notice.textContent = 'Silakan centang captcha "Saya bukan robot" terlebih dahulu.';
      notice.className = 'notice err';
      return;
    }
  }

  const fd = new FormData(e.target);
  fd.set('photo', chosenFile);

  doUpload(fd, captchaValue, 0);
});
</script>
</body>
</html>
