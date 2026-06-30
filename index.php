<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/ipguard/IPGuard.php';
(new IPGuard())->protect();
$pageTitle = 'Beranda';
$metaDesc = 'TeleCard adalah direktori card custom untuk komunitas Telegram. Temukan dan daftarkan channel, grup, serta user Telegram kamu di sini.';
$metaKeywords = 'telegram, channel telegram, grup telegram, direktori telegram, telecard, card telegram';
$stmt = $pdo->query("SELECT * FROM card_submissions WHERE status='approved' ORDER BY created_at DESC LIMIT 6");
$cards = $stmt->fetchAll();
include __DIR__ . '/includes/header.php';
?>

<style>
  /* ── Background Carousel 3D ── */
  #bgCarousel {
    position: fixed;
    inset: 0;
    z-index: 0;
    overflow: hidden;
    pointer-events: none;
  }
  #bgCarousel::after {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse at center, rgba(0,0,0,0.55) 0%, rgba(0,0,0,0.82) 100%);
    z-index: 2;
  }
  .scene {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 220px;
    height: 320px;
    transform-style: preserve-3d;
    perspective: 1200px;
  }
  .carousel-3d {
    width: 100%;
    height: 100%;
    position: relative;
    transform-style: preserve-3d;
    animation: spinCarousel 18s linear infinite;
  }
  @keyframes spinCarousel {
    from { transform: rotateY(0deg); }
    to   { transform: rotateY(360deg); }
  }
  .carousel-3d .card-slide {
    position: absolute;
    width: 220px;
    height: 320px;
    border-radius: 16px;
    overflow: hidden;
    backface-visibility: hidden;
    box-shadow: 0 8px 32px rgba(0,0,0,0.5);
    border: 1.5px solid rgba(255,255,255,0.10);
  }
  .carousel-3d .card-slide img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }

  /* ── Page Content di atas background ── */
  .page-content {
    position: relative;
    z-index: 10;
  }

  /* ── Loader ── */
  #pageLoader {
    position: fixed;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: var(--bg);
    z-index: 99999;
  }
  #pageLoader canvas { display: block; }
  #pageLoader .loader-text {
    margin-top: 18px;
    color: var(--text);
    font-weight: 700;
    font-size: 15px;
    letter-spacing: 0.5px;
  }
  #pageLoader .loader-percent { color: var(--tg-blue); font-weight: 800; }
  #pageLoader .loader-sub {
    margin-top: 6px;
    color: var(--text-dim);
    font-size: 12.5px;
  }
  #pageLoader.loader-hidden {
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.6s ease;
  }
  body.loading-active { overflow: hidden; }
</style>

<!-- Background Carousel 3D -->
<div id="bgCarousel">
  <div class="scene">
    <div class="carousel-3d" id="carousel3d"></div>
  </div>
</div>

<!-- Loader -->
<div id="pageLoader">
  <canvas id="particleCanvas"></canvas>
  <div class="loader-text">Memuat<span id="loaderDots"></span> <span class="loader-percent" id="loaderPercent">0%</span></div>
  <div class="loader-sub">Menyiapkan TeleCard...</div>
</div>

<script>
// ── Build carousel cards ──
(function () {
  const images = [
    '/assets/img/file_00000000d7d072098bcd55b4263611ad.png',
    '/assets/img/file_0000000036b0720989c2eeb636b57cc3.png',
    '/assets/img/file_000000000d707209a333a3d428e4ff00.png',
    '/assets/img/file_000000006b2c7206bfef9cbfa1d57aab.png',
    '/assets/img/file_000000009760720687063737da31c48c.png',
    '/assets/img/file_00000000db68720696cf41b77e30301a.png',
    '/assets/img/Screenshot_20260630-205828.jpg',
    '/assets/img/file_00000000a54472099bba87b58b98d71b.png',
  ];
  const count = images.length;
  const radius = Math.round(220 / (2 * Math.tan(Math.PI / count))) + 80;
  const container = document.getElementById('carousel3d');
  images.forEach((src, i) => {
    const slide = document.createElement('div');
    slide.className = 'card-slide';
    const angle = (360 / count) * i;
    slide.style.transform = `rotateY(${angle}deg) translateZ(${radius}px)`;
    const img = document.createElement('img');
    img.src = src;
    img.alt = '';
    img.loading = 'lazy';
    slide.appendChild(img);
    container.appendChild(slide);
  });
})();

// ── Loader ──
document.body.classList.add('loading-active');

(function () {
  const canvas = document.getElementById('particleCanvas');
  const ctx = canvas.getContext('2d');

  let size = Math.min(window.innerWidth, window.innerHeight) * 0.62;
  size = Math.max(220, Math.min(size, 360));
  canvas.width = size;
  canvas.height = size;

  const center = { x: size / 2, y: size / 2 };
  const sphereRadius = size * 0.30;
  const PARTICLE_COUNT = window.innerWidth < 480 ? 420 : 700;

  function rand(min, max) { return Math.random() * (max - min) + min; }

  class Particle {
    constructor() { this.reset(); }
    reset() {
      const angle = rand(0, Math.PI * 2);
      const dist = rand(size * 0.6, size * 1.3);
      this.x = center.x + Math.cos(angle) * dist;
      this.y = center.y + Math.sin(angle) * dist;
      const theta = rand(0, Math.PI * 2);
      const phi = Math.acos(rand(-1, 1));
      const r = sphereRadius * rand(0.85, 1.0);
      this.tx = center.x + r * Math.sin(phi) * Math.cos(theta);
      this.ty = center.y + r * Math.cos(phi) * 0.55;
      if (Math.random() < 0.35) {
        const ringAngle = rand(0, Math.PI * 2);
        const ringR = sphereRadius * rand(1.05, 1.6);
        this.tx = center.x + Math.cos(ringAngle) * ringR;
        this.ty = center.y + Math.sin(ringAngle) * ringR * 0.18;
      }
      this.size = rand(0.8, 2.3);
      this.opacity = rand(0.4, 1);
      this.twinkleSpeed = rand(0.02, 0.06);
      this.twinklePhase = rand(0, Math.PI * 2);
    }
    update(progress) {
      const ease = 1 - Math.pow(1 - progress, 3);
      this.cx = this.x + (this.tx - this.x) * ease;
      this.cy = this.y + (this.ty - this.y) * ease;
      this.twinklePhase += this.twinkleSpeed;
    }
    draw() {
      const tw = (Math.sin(this.twinklePhase) + 1) / 2;
      const alpha = this.opacity * (0.5 + tw * 0.5);
      ctx.beginPath();
      ctx.arc(this.cx, this.cy, this.size, 0, Math.PI * 2);
      ctx.fillStyle = `rgba(42, 171, 238, ${alpha})`;
      ctx.fill();
    }
  }

  const particles = [];
  for (let i = 0; i < PARTICLE_COUNT; i++) particles.push(new Particle());

  let rafId = null;
  const percentEl = document.getElementById('loaderPercent');
  const dotsEl = document.getElementById('loaderDots');
  let dotState = 0;
  const MIN_DURATION = 2800;
  let pageReady = false;
  let timerDone = false;
  let displayPct = 0;
  let targetPct = 0;
  const startTime = Date.now();

  function getRealisticTarget() {
    const elapsed = Date.now() - startTime;
    if (elapsed < 600)  return Math.min(35, elapsed / 600 * 35);
    if (elapsed < 1400) return Math.min(65, 35 + (elapsed - 600) / 800 * 30);
    if (elapsed < 2200) return Math.min(82, 65 + (elapsed - 1400) / 800 * 17);
    if (elapsed < 2800) return Math.min(91, 82 + (elapsed - 2200) / 600 * 9);
    if (pageReady) return 99;
    return Math.min(94, 91 + (elapsed - 2800) / 2000 * 3);
  }

  function animate() {
    ctx.clearRect(0, 0, size, size);
    targetPct = getRealisticTarget();
    if (displayPct < targetPct) {
      displayPct += (targetPct - displayPct) * 0.04;
      if (displayPct > targetPct) displayPct = targetPct;
    }
    const pct = Math.min(99, Math.floor(displayPct));
    percentEl.textContent = pct + '%';
    const particleProgress = Math.min(1, displayPct / 99);
    particles.forEach(p => { p.update(particleProgress); p.draw(); });
    rafId = requestAnimationFrame(animate);
  }
  animate();

  const dotInterval = setInterval(() => {
    dotState = (dotState + 1) % 4;
    dotsEl.textContent = '.'.repeat(dotState);
  }, 400);

  function tryHide() {
    if (pageReady && timerDone) hideLoader();
  }

  function hideLoader() {
    displayPct = 99;
    percentEl.textContent = '100%';
    setTimeout(() => {
      const loader = document.getElementById('pageLoader');
      if (!loader) return;
      loader.classList.add('loader-hidden');
      document.body.classList.remove('loading-active');
      cancelAnimationFrame(rafId);
      clearInterval(dotInterval);
      setTimeout(() => { loader.style.display = 'none'; }, 650);
    }, 350);
  }

  setTimeout(() => { timerDone = true; tryHide(); }, MIN_DURATION);

  function onPageReady() { pageReady = true; tryHide(); }
  if (document.readyState === 'complete') {
    onPageReady();
  } else {
    window.addEventListener('load', onPageReady);
  }

  setTimeout(hideLoader, 6000);
})();
</script>

<!-- Konten Halaman -->
<div class="page-content">
  <div class="hero">
    <h1>Temukan <span>Custom Card</span><br>Channel, Grup &amp; User Telegram</h1>
    <p>TeleCard adalah direktori card custom untuk komunitas Telegram kamu. Daftar, isi form, dan biarkan card kamu tampil di galeri publik</p>
    <div class="hero-actions">
      <a href="register.php" class="btn btn-primary">Mulai Sekarang</a>
      <a href="cards.php" class="btn btn-outline">Lihat Semua Card</a>
    </div>
    <form class="search-bar" action="cards.php" method="get">
      <input type="text" name="q" placeholder="Cari channel, grup, atau user Telegram...">
      <button type="submit">Cari</button>
    </form>
  </div>
  <h2 style="margin-top:50px;">Card Terbaru</h2>
  <div class="card-grid">
    <?php if (empty($cards)): ?>
      <p style="color:var(--text-dim)">Belum ada card yang disetujui. Jadilah yang pertama!</p>
    <?php endif; ?>
    <?php foreach ($cards as $c): ?>
      <div class="tcard" style="border-top:3px solid <?= clean($c['theme_color'] ?? '#2AABEE') ?>">
        <div class="tcard-top">
          <?php if ($c['image_path']): ?>
            <img class="tcard-avatar" src="<?= UPLOAD_URL . clean($c['image_path']) ?>">
          <?php else: ?>
            <div class="tcard-avatar" style="background:<?= clean($c['theme_color'] ?? '#2AABEE') ?>"></div>
          <?php endif; ?>
          <div>
            <div class="tcard-title">
              <?= clean($c['name']) ?>
              <span class="type-badge" style="background:<?= badgeColorByType($c['type']) ?>"><?= clean($c['type']) ?></span>
              <?php if (!empty($c['is_verified'])): ?>
                <span title="Verified" style="color:#1d9bf0;font-size:15px;font-weight:bold">✓</span>
              <?php endif; ?>
            </div>
            <div class="tcard-meta"><?= $c['member_count'] ? clean($c['member_count']) . ' member' : '' ?></div>
          </div>
        </div>
        <?php if ($c['tags']): ?>
          <div class="tcard-tags">
            <?php foreach (array_slice(array_filter(array_map('trim', explode(',', $c['tags']))), 0, 4) as $t): ?>
              <span class="tag-pill"><?= clean($t) ?></span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <div class="tcard-desc"><?= clean(mb_strimwidth($c['description'] ?? '', 0, 110, '...')) ?></div>
        <div class="tcard-footer">
          <span></span>
          <a href="<?= clean($c['telegram_link']) ?>" target="_blank" class="btn btn-primary btn-sm">Join &rarr;</a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
