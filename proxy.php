<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle    = 'MTProxy Telegram';
$metaDesc     = 'Daftar server MTProxy Telegram gratis dari TeleCard. Pilih server terdekat dan connect langsung.';
$metaKeywords = 'mtproxy, proxy telegram, telegram proxy gratis, telecard proxy';

// ===========================================
// BACA DAFTAR PROXY DARI includes/proxies.txt
// Format per baris: flag|label|server|port|secret|status
// - flag  = kode negara ISO 3166-1 alpha-2 (contoh: us, nl, sg, id)
//           dipakai untuk ambil gambar bendera dari flagcdn.com
// status opsional, default 'online'
// Baris kosong / diawali # akan diabaikan
// ===========================================
function loadProxies(string $path): array
{
    $proxies = [];

    if (!file_exists($path) || !is_readable($path)) {
        return $proxies;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return $proxies;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue; // skip baris kosong / komentar
        }

        $parts = array_map('trim', explode('|', $line));

        // Wajib minimal: flag, label, server, port, secret
        if (count($parts) < 5) {
            continue;
        }

        [$flag, $label, $server, $port, $secret] = array_slice($parts, 0, 5);
        $status = $parts[5] ?? 'online';

        if ($server === '' || $secret === '' || !ctype_digit($port)) {
            continue; // skip baris yang datanya gak valid
        }

        $statusValid = ['online', 'offline', 'maintenance'];
        if (!in_array($status, $statusValid, true)) {
            $status = 'online';
        }

        // Normalisasi kode negara: huruf kecil, hanya a-z, panjang 2 karakter
        $flagCode = strtolower($flag);
        $flagCode = preg_replace('/[^a-z]/', '', $flagCode);
        if (strlen($flagCode) !== 2) {
            $flagCode = ''; // kode tidak valid, nanti fallback ke placeholder
        }

        $proxies[] = [
            'flag'   => $flagCode,
            'label'  => $label,
            'server' => $server,
            'port'   => (int) $port,
            'secret' => $secret,
            'status' => $status,
        ];
    }

    return $proxies;
}

$proxies = loadProxies(__DIR__ . '/includes/proxies.txt');

include __DIR__ . '/includes/header.php';
?>

<style>
  .proxy-wrap {
    max-width: 780px;
    margin: 48px auto 64px;
    padding: 0 16px;
  }

  .proxy-header {
    text-align: center;
    margin-bottom: 36px;
  }

  .proxy-header .proxy-icon {
    width: 72px; height: 72px;
    border-radius: 50%;
    background: rgba(42,171,238,0.1);
    border: 1.5px solid rgba(42,171,238,0.25);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 18px;
    font-size: 30px;
  }

  .proxy-header h1 {
    font-size: 26px; font-weight: 800;
    color: var(--text);
    margin-bottom: 8px;
  }

  .proxy-header p {
    color: var(--text-dim);
    font-size: 14.5px;
    line-height: 1.65;
    max-width: 480px;
    margin: 0 auto;
  }

  .proxy-info-box {
    display: flex; align-items: flex-start; gap: 12px;
    background: rgba(42,171,238,0.06);
    border: 1px solid rgba(42,171,238,0.18);
    border-radius: 14px; padding: 14px 16px;
    margin-bottom: 28px;
  }
  .proxy-info-box svg { flex-shrink: 0; margin-top: 1px; width: 18px; height: 18px; color: var(--tg-blue); }
  .proxy-info-box p { color: var(--text-dim); font-size: 13px; line-height: 1.65; }
  .proxy-info-box strong { color: var(--text); }

  .proxy-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
  }

  .proxy-card {
    background: var(--card-bg, rgba(255,255,255,0.04));
    border: 1px solid var(--border, rgba(255,255,255,0.08));
    border-radius: 18px;
    padding: 20px 22px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: border-color 0.2s, transform 0.2s;
  }

  .proxy-card:hover {
    border-color: rgba(42,171,238,0.35);
  }

  /* ==== FLAG SEKARANG BERUPA GAMBAR, BUKAN EMOJI ==== */
  .proxy-flag {
    flex-shrink: 0;
    width: 48px;
    height: 48px;
    border-radius: 10px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.08);
  }

  .proxy-flag img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }

  .proxy-flag .proxy-flag-fallback {
    font-size: 18px;
    font-weight: 800;
    color: var(--text-dim);
  }

  .proxy-detail {
    flex: 1;
    min-width: 0;
  }

  .proxy-detail-top {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
    flex-wrap: wrap;
  }

  .proxy-label {
    font-size: 16px;
    font-weight: 700;
    color: var(--text);
  }

  .proxy-status {
    font-size: 11px;
    font-weight: 700;
    padding: 2px 9px;
    border-radius: 99px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
  }

  .proxy-status::before {
    content: "";
    width: 6px; height: 6px;
    border-radius: 50%;
  }

  .proxy-status.online {
    background: rgba(34,197,94,0.1);
    color: #4ade80;
  }
  .proxy-status.online::before { background: #22c55e; animation: proxyPulse 1.6s ease-in-out infinite; }

  .proxy-status.offline {
    background: rgba(239,68,68,0.1);
    color: #f87171;
  }
  .proxy-status.offline::before { background: #ef4444; }

  .proxy-status.maintenance {
    background: rgba(234,179,8,0.1);
    color: #facc15;
  }
  .proxy-status.maintenance::before { background: #eab308; }

  @keyframes proxyPulse {
    0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(34,197,94,0.5); }
    50%      { opacity: 0.6; box-shadow: 0 0 0 4px rgba(34,197,94,0); }
  }

  .proxy-meta {
    font-size: 12.5px;
    color: var(--text-dim);
    font-family: 'SF Mono', Consolas, monospace;
    word-break: break-all;
  }

  .proxy-actions {
    display: flex;
    gap: 8px;
    flex-shrink: 0;
  }

  .proxy-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 10px 16px;
    border-radius: 11px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
  }

  .proxy-btn svg { width: 15px; height: 15px; flex-shrink: 0; }

  .proxy-btn-connect {
    background: linear-gradient(135deg, var(--tg-blue) 0%, #1a7fd4 100%);
    color: #fff;
    box-shadow: 0 4px 14px rgba(42,171,238,0.3);
  }

  .proxy-btn-connect:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(42,171,238,0.45);
  }

  .proxy-btn-copy {
    background: rgba(255,255,255,0.06);
    color: var(--text);
    border: 1.5px solid rgba(255,255,255,0.12);
  }

  .proxy-btn-copy:hover {
    background: rgba(255,255,255,0.1);
    border-color: rgba(255,255,255,0.2);
  }

  .proxy-btn-copy.copied {
    background: rgba(34,197,94,0.12);
    border-color: rgba(34,197,94,0.35);
    color: #4ade80;
  }

  @media (max-width: 560px) {
    .proxy-card {
      flex-wrap: wrap;
    }
    .proxy-actions {
      width: 100%;
      margin-top: 4px;
    }
    .proxy-btn { flex: 1; }
  }

  .proxy-empty {
    text-align: center;
    padding: 48px 20px;
    color: var(--text-dim);
    font-size: 14px;
  }
</style>

<div class="proxy-wrap">

  <div class="proxy-header">
    <div class="proxy-icon">🌐</div>
    <h1>MTProxy Telegram</h1>
    <p>Server proxy gratis untuk Telegram. Pilih server terdekat, klik connect, dan kamu langsung terhubung.</p>
  </div>

  <div class="proxy-info-box">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round"
        d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/>
    </svg>
    <p><strong>Cara pakai:</strong> Klik tombol "Connect", Telegram akan terbuka otomatis dan menampilkan dialog konfirmasi. Tekan <strong>"Enable Proxy"</strong> di dalam Telegram untuk mengaktifkan koneksinya.</p>
  </div>

  <div class="proxy-list">
    <?php if (empty($proxies)): ?>
      <div class="proxy-empty">Belum ada server proxy tersedia saat ini.</div>
    <?php else: ?>
      <?php foreach ($proxies as $i => $p): ?>
        <?php
          $status  = $p['status'];
          $webLink = sprintf(
              'https://t.me/proxy?server=%s&port=%d&secret=%s',
              rawurlencode($p['server']),
              $p['port'],
              rawurlencode($p['secret'])
          );
        ?>
        <div class="proxy-card" data-proxy-index="<?= (int) $i ?>">
          <div class="proxy-flag">
            <?php if ($p['flag'] !== ''): ?>
              <img
                src="https://flagcdn.com/w80/<?= htmlspecialchars($p['flag']) ?>.png"
                srcset="https://flagcdn.com/w160/<?= htmlspecialchars($p['flag']) ?>.png 2x"
                alt="Bendera <?= htmlspecialchars($p['label']) ?>"
                loading="lazy"
                onerror="this.parentElement.innerHTML='<span class=\'proxy-flag-fallback\'>?</span>';"
              >
            <?php else: ?>
              <span class="proxy-flag-fallback">?</span>
            <?php endif; ?>
          </div>
          <div class="proxy-detail">
            <div class="proxy-detail-top">
              <span class="proxy-label"><?= htmlspecialchars($p['label']) ?></span>
              <span class="proxy-status <?= htmlspecialchars($status) ?>">
                <?= $status === 'online' ? 'Online' : ($status === 'offline' ? 'Offline' : 'Maintenance') ?>
              </span>
            </div>
            <div class="proxy-meta">
              <?= htmlspecialchars($p['server']) ?>:<?= (int) $p['port'] ?>
            </div>
          </div>
          <div class="proxy-actions">
            <a href="<?= htmlspecialchars($webLink) ?>" class="proxy-btn proxy-btn-connect" target="_blank" rel="noopener">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
              </svg>
              Connect
            </a>
            <button type="button" class="proxy-btn proxy-btn-copy" data-copy="<?= htmlspecialchars($webLink) ?>">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <rect x="9" y="9" width="13" height="13" rx="2"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/>
              </svg>
              <span class="copy-text">Salin</span>
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</div>

<script>
document.querySelectorAll('.proxy-btn-copy').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var link = btn.getAttribute('data-copy');
        var textEl = btn.querySelector('.copy-text');

        navigator.clipboard.writeText(link).then(function () {
            btn.classList.add('copied');
            textEl.textContent = 'Tersalin!';
            setTimeout(function () {
                btn.classList.remove('copied');
                textEl.textContent = 'Salin';
            }, 1800);
        }).catch(function () {
            var temp = document.createElement('textarea');
            temp.value = link;
            document.body.appendChild(temp);
            temp.select();
            try { document.execCommand('copy'); } catch (e) {}
            document.body.removeChild(temp);

            btn.classList.add('copied');
            textEl.textContent = 'Tersalin!';
            setTimeout(function () {
                btn.classList.remove('copied');
                textEl.textContent = 'Salin';
            }, 1800);
        });
    });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
