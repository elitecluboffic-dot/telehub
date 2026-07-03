<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle    = 'Private Proxy Telegram';
$metaDesc     = 'Order MTProxy Telegram private dari TeleCard. Pilih negara, tambahkan channel promosi (opsional), dan kami akan menghubungi kamu.';
$metaKeywords = 'private proxy telegram, mtproxy private, proxy telegram berbayar, telecard proxy';

$workerEndpoint = 'https://telehub-report.internetdnsofficial.workers.dev';

$countries = [
    ['value' => 'us', 'flag' => 'us', 'label' => 'US'],
    ['value' => 'sg', 'flag' => 'sg', 'label' => 'Singapore'],
    ['value' => 'nl', 'flag' => 'nl', 'label' => 'Belanda'],
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

  .pp-spinner {
    width: 16px; height: 16px;
    border: 2px solid rgba(255,255,255,0.4);
    border-top-color: #fff;
    border-radius: 50%;
    animation: pp-spin 0.7s linear infinite;
    display: none;
  }

  .pp-submit.loading .pp-spinner { display: inline-block; }
  .pp-submit.loading .pp-submit-text { display: none; }

  @keyframes pp-spin { to { transform: rotate(360deg); } }

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
    <div class="pp-price-badge">💰 Rp20.000 / bulan</div>
  </div>

  <form class="pp-form" id="ppForm" novalidate>

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
      <span class="pp-spinner"></span>
      <span class="pp-submit-text">Order Sekarang</span>
    </button>

    <a href="/proxy.php" class="pp-back" id="ppBack">
      <span>&larr;</span>
      <span>Kembali</span>
    </a>

    <div class="pp-status" id="ppStatus"></div>

  </form>

</div>

<script>
(function () {
  const WORKER_URL = <?= json_encode($workerEndpoint) ?>;

  const form = document.getElementById('ppForm');
  const submitBtn = document.getElementById('ppSubmit');
  const statusBox = document.getElementById('ppStatus');

  function showStatus(type, message) {
    statusBox.className = 'pp-status show ' + type;
    statusBox.textContent = message;
  }

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    statusBox.className = 'pp-status';

    const formData = new FormData(form);
    const payload = {
      country: formData.get('country') || '',
      channel: (formData.get('channel') || '').trim(),
      telegram_username: (formData.get('telegram_username') || '').trim(),
      whatsapp: (formData.get('whatsapp') || '').trim(),
      email: (formData.get('email') || '').trim(),
      website: formData.get('website') || '', // honeypot
    };

    if (!payload.telegram_username) {
      showStatus('error', 'Username Telegram wajib diisi.');
      return;
    }

    submitBtn.disabled = true;
    submitBtn.classList.add('loading');

    try {
      const resp = await fetch(WORKER_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });

      const data = await resp.json().catch(() => ({}));

      if (resp.ok && data.ok) {
        showStatus('success', 'Order berhasil dikirim! Tim kami akan menghubungi kamu lewat Telegram sebentar lagi.');
        form.reset();
      } else {
        showStatus('error', data.error || 'Terjadi kesalahan, coba lagi beberapa saat.');
      }
    } catch (err) {
      showStatus('error', 'Gagal mengirim order. Cek koneksi internet kamu dan coba lagi.');
    } finally {
      submitBtn.disabled = false;
      submitBtn.classList.remove('loading');
    }
  });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
