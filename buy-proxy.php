<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle    = 'Private Proxy Telegram';
$metaDesc     = 'Order MTProxy Telegram private dari TeleCard. Pilih negara, pilih paket, tambahkan channel promosi (opsional), dan kami akan menghubungi kamu.';
$metaKeywords = 'private proxy telegram, mtproxy private, proxy telegram berbayar, telecard proxy';

$workerEndpoint = 'https://telehub-report.internetdnsofficial.workers.dev';

// Ganti link ini dengan link gambar contoh tampilan promosi channel kamu
$channelExampleImage = 'https://telehub.nfy.fyi/uploads/Proxy-Sponsor-Telegram.jpg';

$countries = [
    ['value' => 'us', 'flag' => 'us', 'label' => 'US'],
    ['value' => 'sg', 'flag' => 'sg', 'label' => 'Singapore'],
    ['value' => 'nl', 'flag' => 'nl', 'label' => 'Belanda'],
];

// ── Paket: Original vs Super ──
$packages = [
    [
        'value'       => 'original',
        'label'       => 'Original',
        'price'       => 20000,
        'price_label' => 'Rp20.000',
        'desc'        => 'Private proxy khusus kamu',
    ],
    [
        'value'       => 'super',
        'label'       => 'Super',
        'price'       => 60000,
        'price_label' => 'Rp60.000',
        'desc'        => '+ 500 Followers/Member Real Indo untuk Channel/Grup kamu',
    ],
];

include __DIR__ . '/includes/header.php';
?>

<style>
  .pp-wrap {
    max-width: 640px;
    margin: 48px auto 64px;
    padding: 0 16px;
  }

  .pp-header {
    text-align: center;
    margin-bottom: 32px;
  }

  .pp-header .pp-icon {
    width: 72px; height: 72px;
    border-radius: 50%;
    background: rgba(42,171,238,0.1);
    border: 1.5px solid rgba(42,171,238,0.25);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 18px;
    font-size: 30px;
  }

  .pp-header h1 {
    font-size: 26px; font-weight: 800;
    color: var(--text);
    margin-bottom: 8px;
  }

  .pp-header p {
    color: var(--text-dim);
    font-size: 14.5px;
    line-height: 1.65;
    max-width: 460px;
    margin: 0 auto;
  }

  .pp-price-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 14px;
    background: rgba(34,197,94,0.1);
    border: 1px solid rgba(34,197,94,0.3);
    color: #4ade80;
    font-weight: 700;
    font-size: 14px;
    padding: 6px 16px;
    border-radius: 99px;
    transition: all 0.2s;
  }

  .pp-price-badge.super {
    background: rgba(250,204,21,0.1);
    border-color: rgba(250,204,21,0.35);
    color: #fbbf24;
  }

  .pp-form {
    background: var(--card-bg, rgba(255,255,255,0.04));
    border: 1px solid var(--border, rgba(255,255,255,0.08));
    border-radius: 18px;
    padding: 26px 24px;
  }

  .pp-field { margin-bottom: 18px; }
  .pp-field:last-of-type { margin-bottom: 0; }

  .pp-label {
    display: block;
    font-size: 13.5px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 7px;
  }

  .pp-label .pp-optional {
    font-weight: 500;
    color: var(--text-dim);
    font-size: 12px;
  }

  .pp-label .pp-required { color: #f87171; }

  /* ── Paket Grid (Original / Super) ── */
  .pp-package-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
  }

  .pp-package-option { position: relative; }

  .pp-package-option input {
    position: absolute;
    opacity: 0;
    width: 100%; height: 100%;
    cursor: pointer;
    margin: 0;
  }

  .pp-package-card {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 16px 14px;
    border-radius: 14px;
    border: 1.5px solid var(--border, rgba(255,255,255,0.1));
    background: rgba(255,255,255,0.02);
    transition: all 0.15s;
    height: 100%;
    box-sizing: border-box;
  }

  .pp-package-card .pp-package-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .pp-package-card .pp-package-name {
    font-size: 14.5px;
    font-weight: 800;
    color: var(--text);
  }

  .pp-package-card .pp-package-badge {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    padding: 3px 8px;
    border-radius: 99px;
    background: rgba(250,204,21,0.15);
    color: #fbbf24;
  }

  .pp-package-card .pp-package-price {
    font-size: 17px;
    font-weight: 800;
    color: var(--tg-blue);
  }

  .pp-package-card .pp-package-price span {
    font-size: 11.5px;
    font-weight: 600;
    color: var(--text-dim);
  }

  .pp-package-card .pp-package-desc {
    font-size: 12px;
    line-height: 1.5;
    color: var(--text-dim);
  }

  .pp-package-option input:checked + .pp-package-card {
    border-color: var(--tg-blue);
    background: rgba(42,171,238,0.08);
    box-shadow: 0 0 0 3px rgba(42,171,238,0.10);
  }

  .pp-package-option[data-pkg="super"] input:checked + .pp-package-card {
    border-color: #fbbf24;
    background: rgba(250,204,21,0.08);
    box-shadow: 0 0 0 3px rgba(250,204,21,0.10);
  }

  .pp-package-option input:focus-visible + .pp-package-card {
    outline: 2px solid var(--tg-blue);
    outline-offset: 2px;
  }

  @media (max-width: 420px) {
    .pp-package-grid { grid-template-columns: 1fr; }
  }

  /* ── Negara ── */
  .pp-country-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
  }

  .pp-country-option { position: relative; }

  .pp-country-option input {
    position: absolute;
    opacity: 0;
    width: 100%; height: 100%;
    cursor: pointer;
    margin: 0;
  }

  .pp-country-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 14px 8px;
    border-radius: 12px;
    border: 1.5px solid var(--border, rgba(255,255,255,0.1));
    background: rgba(255,255,255,0.02);
    transition: all 0.15s;
  }

  .pp-country-card img {
    width: 32px; height: 32px;
    border-radius: 6px;
    object-fit: cover;
  }

  .pp-country-card span {
    font-size: 12.5px;
    font-weight: 700;
    color: var(--text-dim);
  }

  .pp-country-option input:checked + .pp-country-card {
    border-color: var(--tg-blue);
    background: rgba(42,171,238,0.08);
  }

  .pp-country-option input:checked + .pp-country-card span {
    color: var(--text);
  }

  .pp-country-option input:focus-visible + .pp-country-card {
    outline: 2px solid var(--tg-blue);
    outline-offset: 2px;
  }

  .pp-input {
    width: 100%;
    padding: 12px 14px;
    border-radius: 11px;
    border: 1.5px solid var(--border, rgba(255,255,255,0.1));
    background: rgba(255,255,255,0.02);
    color: var(--text);
    font-size: 14px;
    outline: none;
    transition: border-color 0.15s;
    font-family: inherit;
  }

  .pp-input::placeholder { color: var(--text-dim); opacity: 0.7; }
  .pp-input:focus { border-color: var(--tg-blue); }

  .pp-example {
    margin-top: 10px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding: 12px;
    border-radius: 12px;
    border: 1px dashed var(--border, rgba(255,255,255,0.15));
    background: rgba(255,255,255,0.02);
  }

  .pp-example img {
    width: 100%;
    max-height: 320px;
    border-radius: 10px;
    object-fit: contain;
    background: rgba(0,0,0,0.25);
  }

  .pp-example-text {
    font-size: 12.5px;
    line-height: 1.55;
    color: var(--text-dim);
  }

  .pp-example-text strong {
    color: var(--text);
    display: block;
    margin-bottom: 2px;
    font-size: 13px;
  }

  .pp-super-note {
    margin-top: 10px;
    padding: 10px 12px;
    border-radius: 10px;
    background: rgba(250,204,21,0.08);
    border: 1px solid rgba(250,204,21,0.25);
    color: #fbbf24;
    font-size: 12.5px;
    line-height: 1.55;
    display: none;
  }

  .pp-super-note.show { display: block; }

  .pp-honeypot {
    position: absolute;
    left: -9999px;
    width: 1px;
    height: 1px;
    overflow: hidden;
  }

  .pp-submit {
    width: 100%;
    margin-top: 22px;
    padding: 14px 20px;
    border-radius: 12px;
    border: none;
    background: linear-gradient(135deg, var(--tg-blue) 0%, #1a7fd4 100%);
    color: #fff;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(42,171,238,0.3);
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
  }

  .pp-submit:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(42,171,238,0.45);
  }

  .pp-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
  }

  .pp-loading-wrap {
    display: none;
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
    width: 100%;
  }

  .pp-loading-label {
    font-size: 13.5px;
    font-weight: 700;
    color: #fff;
  }

  .pp-loading-bar {
    width: 100%;
    height: 7px;
    border-radius: 99px;
    background: rgba(255,255,255,0.22);
    overflow: hidden;
    position: relative;
  }

  .pp-loading-bar-fill {
    position: absolute;
    top: 0; left: 0;
    height: 100%;
    width: 40%;
    border-radius: 99px;
    background: #fff;
    animation: pp-bar-slide 1.1s ease-in-out infinite;
  }

  .pp-submit.loading .pp-loading-wrap { display: flex; }
  .pp-submit.loading .pp-submit-text { display: none; }

  @keyframes pp-bar-slide {
    0%   { left: -40%; }
    100% { left: 100%; }
  }

  .pp-back {
    width: 100%;
    margin-top: 12px;
    padding: 13px 20px;
    border-radius: 12px;
    border: 1.5px solid var(--border, rgba(255,255,255,0.12));
    background: rgba(255,255,255,0.03);
    color: var(--text-dim);
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.15s;
  }

  .pp-back:hover {
    background: rgba(255,255,255,0.06);
    color: var(--text);
    border-color: rgba(255,255,255,0.2);
  }

  .pp-status {
    margin-top: 16px;
    padding: 13px 15px;
    border-radius: 12px;
    font-size: 13.5px;
    line-height: 1.6;
    display: none;
  }

  .pp-status.show { display: block; }

  .pp-status.success {
    background: rgba(34,197,94,0.1);
    border: 1px solid rgba(34,197,94,0.3);
    color: #4ade80;
  }

  .pp-status.error {
    background: rgba(239,68,68,0.1);
    border: 1px solid rgba(239,68,68,0.3);
    color: #f87171;
  }

  @media (max-width: 480px) {
    .pp-country-grid { gap: 8px; }
    .pp-country-card { padding: 10px 6px; }
  }
</style>

<div class="pp-wrap">

  <div class="pp-header">
    <div class="pp-icon">🔒</div>
    <h1>Private Proxy Telegram</h1>
    <p>Server proxy khusus untuk kamu sendiri, tanpa antre bareng user lain. Isi form di bawah, tim kami akan menghubungi kamu langsung lewat Telegram.</p>
    <div class="pp-price-badge" id="ppPriceBadge">💰 Rp20.000 / bulan</div>
  </div>

  <form class="pp-form" id="ppForm" novalidate>

    <!-- Pilihan Paket -->
    <div class="pp-field">
      <label class="pp-label">Pilih Paket <span class="pp-required">*</span></label>
      <div class="pp-package-grid">
        <?php foreach ($packages as $i => $p): ?>
          <label class="pp-package-option" data-pkg="<?= htmlspecialchars($p['value']) ?>">
            <input
              type="radio"
              name="package"
              value="<?= htmlspecialchars($p['value']) ?>"
              data-price="<?= (int) $p['price'] ?>"
              data-price-label="<?= htmlspecialchars($p['price_label']) ?>"
              <?= $i === 0 ? 'checked' : '' ?>
              required
            >
            <span class="pp-package-card">
              <span class="pp-package-top">
                <span class="pp-package-name"><?= htmlspecialchars($p['label']) ?></span>
                <?php if ($p['value'] === 'super'): ?>
                  <span class="pp-package-badge">Bonus</span>
                <?php endif; ?>
              </span>
              <span class="pp-package-price">
                <?= htmlspecialchars($p['price_label']) ?> <span>/bulan</span>
              </span>
              <span class="pp-package-desc"><?= htmlspecialchars($p['desc']) ?></span>
            </span>
          </label>
        <?php endforeach; ?>
      </div>
      <div class="pp-super-note" id="ppSuperNote">
        🎁 Paket <strong>Super</strong> udah termasuk private proxy + bonus <strong>500 followers/member Real Indo</strong> untuk Channel/Grup kamu. Isi username Channel/Grup kamu di kolom di bawah biar bisa kami proses.
      </div>
    </div>

    <div class="pp-field">
      <label class="pp-label">Pilih Negara Server <span class="pp-required">*</span></label>
      <div class="pp-country-grid">
        <?php foreach ($countries as $i => $c): ?>
          <label class="pp-country-option">
            <input type="radio" name="country" value="<?= htmlspecialchars($c['value']) ?>" <?= $i === 0 ? 'checked' : '' ?> required>
            <span class="pp-country-card">
              <img src="https://flagcdn.com/w80/<?= htmlspecialchars($c['flag']) ?>.png" alt="<?= htmlspecialchars($c['label']) ?>" loading="lazy">
              <span><?= htmlspecialchars($c['label']) ?></span>
            </span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="pp-field">
      <label class="pp-label" for="ppChannel">Channel yang ingin dipromosikan <span class="pp-optional">(opsional)</span></label>
      <input type="text" id="ppChannel" name="channel" class="pp-input" placeholder="@channelkamu">
      <div class="pp-example">
        <a href="<?= htmlspecialchars($channelExampleImage) ?>" target="_blank" rel="noopener">
          <img src="<?= htmlspecialchars($channelExampleImage) ?>" alt="Contoh tampilan promosi channel" loading="lazy">
        </a>
        <div class="pp-example-text">
          <strong>Contoh tampilannya nanti seperti ini</strong>
          Channel kamu akan dipromosikan ke user proxy lain dalam bentuk seperti gambar di atas. Klik gambar untuk lihat ukuran penuh.
        </div>
      </div>
    </div>

    <div class="pp-field">
      <label class="pp-label" for="ppTelegram">Username Telegram <span class="pp-required">*</span></label>
      <input type="text" id="ppTelegram" name="telegram_username" class="pp-input" placeholder="@usernamekamu" required>
    </div>

    <div class="pp-field">
      <label class="pp-label" for="ppWhatsapp">Nomor WhatsApp <span class="pp-optional">(opsional)</span></label>
      <input type="tel" id="ppWhatsapp" name="whatsapp" class="pp-input" placeholder="08xxxxxxxxxx">
    </div>

    <div class="pp-field">
      <label class="pp-label" for="ppEmail">Email <span class="pp-optional">(opsional)</span></label>
      <input type="email" id="ppEmail" name="email" class="pp-input" placeholder="nama@email.com">
    </div>

    <!-- Honeypot anti-bot, jangan dihapus -->
    <div class="pp-honeypot" aria-hidden="true">
      <label for="ppWebsite">Website</label>
      <input type="text" id="ppWebsite" name="website" tabindex="-1" autocomplete="off">
    </div>

    <button type="submit" class="pp-submit" id="ppSubmit">
      <span class="pp-submit-text">Order Sekarang</span>
      <span class="pp-loading-wrap">
        <span class="pp-loading-label">Mengirim Notification Pembelian Proxy...</span>
        <span class="pp-loading-bar"><span class="pp-loading-bar-fill"></span></span>
      </span>
    </button>

    <a href="/proxy.php" class="pp-back" id="ppBack">
      <span>&larr;</span>
      <span>Kembali ke Proxy</span>
    </a>

    <div class="pp-status" id="ppStatus"></div>

  </form>

</div>

<script>
(function () {
  const WORKER_URL = <?= json_encode($workerEndpoint) ?>;

  const form         = document.getElementById('ppForm');
  const submitBtn    = document.getElementById('ppSubmit');
  const statusBox    = document.getElementById('ppStatus');
  const priceBadge   = document.getElementById('ppPriceBadge');
  const superNote    = document.getElementById('ppSuperNote');
  const packageInputs = form.querySelectorAll('input[name="package"]');

  function updatePackageUI() {
    const checked = form.querySelector('input[name="package"]:checked');
    if (!checked) return;

    const priceLabel = checked.getAttribute('data-price-label') || '';
    priceBadge.textContent = '💰 ' + priceLabel + ' / bulan';

    if (checked.value === 'super') {
      priceBadge.classList.add('super');
      superNote.classList.add('show');
    } else {
      priceBadge.classList.remove('super');
      superNote.classList.remove('show');
    }
  }

  packageInputs.forEach(function (input) {
    input.addEventListener('change', updatePackageUI);
  });
  updatePackageUI(); // set state awal

  function showStatus(type, message) {
    statusBox.className = 'pp-status show ' + type;
    statusBox.textContent = message;
  }

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    statusBox.className = 'pp-status';

    const formData = new FormData(form);
    const packageInput = form.querySelector('input[name="package"]:checked');

    const payload = {
      package: formData.get('package') || '',
      package_price: packageInput ? (packageInput.getAttribute('data-price') || '') : '',
      country: formData.get('country') || '',
      channel: (formData.get('channel') || '').trim(),
      telegram_username: (formData.get('telegram_username') || '').trim(),
      whatsapp: (formData.get('whatsapp') || '').trim(),
      email: (formData.get('email') || '').trim(),
      website: formData.get('website') || '', // honeypot
    };

    if (!payload.package) {
      showStatus('error', 'Pilih paket dulu ya (Original / Super).');
      return;
    }

    if (!payload.telegram_username) {
      showStatus('error', 'Username Telegram wajib diisi.');
      return;
    }

    if (payload.package === 'super' && !payload.channel) {
      showStatus('error', 'Paket Super butuh username Channel/Grup kamu buat proses bonus followers.');
      return;
    }

    submitBtn.disabled = true;
    submitBtn.classList.add('loading');

    const MIN_LOADING_MS = 2200;
    const startTime = Date.now();

    function waitMinLoading() {
      const elapsed = Date.now() - startTime;
      const remaining = MIN_LOADING_MS - elapsed;
      return remaining > 0 ? new Promise((res) => setTimeout(res, remaining)) : Promise.resolve();
    }

    try {
      const resp = await fetch(WORKER_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });

      const data = await resp.json().catch(() => ({}));

      await waitMinLoading();

      if (resp.ok && data.ok) {
        showStatus('success', 'Order berhasil dikirim! Tim kami akan menghubungi kamu lewat Telegram sebentar lagi.');
        form.reset();
        updatePackageUI();
      } else {
        showStatus('error', data.error || 'Terjadi kesalahan, coba lagi beberapa saat.');
      }
    } catch (err) {
      await waitMinLoading();
      showStatus('error', 'Gagal mengirim order. Cek koneksi internet kamu dan coba lagi.');
    } finally {
      submitBtn.disabled = false;
      submitBtn.classList.remove('loading');
    }
  });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
