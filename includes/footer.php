</div>
<footer>
  <div class="container">
    <div class="footer-columns">

      <!-- KIRI (desktop) / ATAS (mobile): Brand, typewriter, divider, logo, copyright -->
      <div class="footer-brand-col">
        <div class="footer-typewriter">
          <span id="footerTypewriterText"></span><span class="footer-typewriter-cursor">|</span>
        </div>
        <div class="footer-divider"></div>
        <div style="margin-bottom:8px; display:flex; justify-content:center;">
          <img src="<?= SITE_URL ?>/uploads/Logo-Telehub.png" style="height:200px;width:auto;vertical-align:middle;display:block">
        </div>
        <div style="color:var(--tg-blue);font-size:12px;margin-bottom:12px">
          © <?= date('Y') ?> · Temukan &amp; Daftarkan Channel Telegram Kamu
        </div>
      </div>

      <!-- KANAN (desktop) / BAWAH (mobile): Link -->
      <div class="footer-links-col">
        <div class="footer-link-list">
          <a href="<?= SITE_URL ?>/public-poto.php">📸 Public Poto</a>
          <a href="<?= SITE_URL ?>/comments.php">💬 Komentar &amp; Rating</a>
          <a href="<?= SITE_URL ?>/report.php">🛠️ Support</a>
          <a href="<?= SITE_URL ?>/movies.php">👁️‍🗨️ Movie</a>
          <a href="<?= SITE_URL ?>/proxy.php">🛡️ Proxy</a>
        </div>
      </div>

    </div>
  </div>
</footer>

<style>
  .footer-typewriter {
    font-size: 22px;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 12px;
    letter-spacing: 0.5px;
    min-height: 30px;
  }
  .footer-typewriter-cursor {
    display: inline-block;
    margin-left: 2px;
    color: var(--tg-blue);
    animation: footerCursorBlink 0.8s step-end infinite;
  }
  @keyframes footerCursorBlink {
    0%, 50% { opacity: 1; }
    50.01%, 100% { opacity: 0; }
  }
  @media (prefers-reduced-motion: reduce) {
    .footer-typewriter-cursor { animation: none; }
  }
  .footer-divider {
    width: 300px;
    height: 4px;
    background: #ffffff;
    border-radius: 2px;
    margin: 4px auto 16px;
  }

  /* Link footer: default (mobile) horizontal & center, seperti tampilan asli */
  .footer-link-list {
    display: flex;
    gap: 14px;
    justify-content: center;
    flex-wrap: wrap;
  }
  .footer-link-list a {
    color: var(--tg-blue);
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
  }
  .footer-link-list a:hover {
    text-decoration: underline;
  }

  /* ============================
     DEFAULT (mobile-first): tampilan original — semua center, stack vertikal
     ============================ */
  .footer-columns {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
  }
  .footer-brand-col,
  .footer-links-col {
    width: 100%;
  }

  /* ============================
     DESKTOP (>=769px): layout kiri-kanan
     ============================ */
  @media (min-width: 769px) {
    .footer-columns {
      flex-direction: row;
      justify-content: space-between;
      align-items: center;
      text-align: left;
      flex-wrap: wrap;
      gap: 40px;
      padding: 20px 0;
    }
    .footer-brand-col {
      flex: 1 1 260px;
      min-width: 220px;
    }
    .footer-brand-col > div[style*="justify-content:center"] {
      justify-content: flex-start !important;
    }
    .footer-divider {
      margin: 4px 0 16px;
    }
    .footer-links-col {
      flex: 1 1 200px;
      display: flex;
      justify-content: flex-end;
    }
    .footer-link-list {
      flex-direction: column;
      align-items: flex-end;
      gap: 12px;
    }
    .footer-link-list a {
      font-size: 13px;
      color: #000; /* hitam biar kontras di atas background oranye */
    }
  }
</style>

<script>
// ── Efek Typewriter (teks ngetik bergantian) di footer ──
(function () {
  const words = ['KING TELEHUB', 'TELEHUB TELEGRAM', 'TITAN TYCOON'];
  const el = document.getElementById('footerTypewriterText');
  if (!el) return;
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (prefersReducedMotion) {
    el.textContent = words[0];
    return;
  }
  const TYPE_SPEED = 90;      // ms per huruf saat mengetik
  const DELETE_SPEED = 45;    // ms per huruf saat menghapus
  const PAUSE_AFTER_TYPE = 1500; // ms diam setelah selesai ngetik penuh
  const PAUSE_AFTER_DELETE = 400; // ms diam setelah selesai menghapus
  let wordIndex = 0;
  let charIndex = 0;
  let deleting = false;
  function tick() {
    const currentWord = words[wordIndex];
    if (!deleting) {
      charIndex++;
      el.textContent = currentWord.slice(0, charIndex);
      if (charIndex === currentWord.length) {
        deleting = true;
        setTimeout(tick, PAUSE_AFTER_TYPE);
        return;
      }
      setTimeout(tick, TYPE_SPEED);
    } else {
      charIndex--;
      el.textContent = currentWord.slice(0, charIndex);
      if (charIndex === 0) {
        deleting = false;
        wordIndex = (wordIndex + 1) % words.length;
        setTimeout(tick, PAUSE_AFTER_DELETE);
        return;
      }
      setTimeout(tick, DELETE_SPEED);
    }
  }
  tick();
})();
</script>
</body>
</html>
