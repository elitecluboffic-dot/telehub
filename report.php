<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config.php';

$pageTitle   = 'Laporkan Masalah';
$metaDesc    = 'Laporkan bug, masalah teknis, atau konten bermasalah di TeleCard.';
$metaKeywords = 'laporan, bug, masalah, report, telecard';

$success = false;
$error   = '';

// ── Rate limiting sederhana via session ──
if (!isset($_SESSION['report_count'])) {
    $_SESSION['report_count']    = 0;
    $_SESSION['report_window']   = time();
}
// Reset window tiap 10 menit
if (time() - $_SESSION['report_window'] > 600) {
    $_SESSION['report_count']  = 0;
    $_SESSION['report_window'] = time();
}

// ── Konfigurasi upload gambar ──
const REPORT_MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
const REPORT_ALLOWED_MIME  = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── Validasi CSRF token ──
    if (empty($_POST['csrf']) || $_POST['csrf'] !== ($_SESSION['csrf_report'] ?? '')) {
        $error = 'Request tidak valid. Silakan refresh halaman dan coba lagi.';
    }

    // ── Rate limit: maks 3 laporan per 10 menit per session ──
    elseif ($_SESSION['report_count'] >= 3) {
        $error = 'Kamu terlalu sering mengirim laporan. Tunggu beberapa menit sebelum mencoba lagi.';
    }

    else {
        $jenis       = trim($_POST['jenis']       ?? '');
        $judul       = trim($_POST['judul']       ?? '');
        $deskripsi   = trim($_POST['deskripsi']   ?? '');
        $email       = trim($_POST['email']       ?? '');
        $url_terkait = trim($_POST['url_terkait'] ?? '');

        // ── Validasi input teks ──
        $jenisValid = ['bug', 'konten', 'akun', 'performa', 'saran', 'lainnya'];
        $imagePath  = null; // path file sementara (tmp) kalau valid
        $imageMime  = null;

        if (!in_array($jenis, $jenisValid, true)) {
            $error = 'Jenis laporan tidak valid.';
        } elseif (mb_strlen($judul) < 5 || mb_strlen($judul) > 120) {
            $error = 'Judul laporan harus antara 5–120 karakter.';
        } elseif (mb_strlen($deskripsi) < 20 || mb_strlen($deskripsi) > 2000) {
            $error = 'Deskripsi harus antara 20–2000 karakter.';
        } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Format email tidak valid.';
        }
        // ── Validasi file gambar (opsional) ──
        elseif (!empty($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {

            $file = $_FILES['gambar'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $error = 'Gagal mengunggah gambar. Silakan coba lagi.';
            } elseif ($file['size'] > REPORT_MAX_FILE_SIZE) {
                $error = 'Ukuran gambar maksimal 5MB.';
            } else {
                // ── Cek MIME asli file (bukan cuma ekstensi) ──
                $finfo    = finfo_open(FILEINFO_MIME_TYPE);
                $realMime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                if (!isset(REPORT_ALLOWED_MIME[$realMime])) {
                    $error = 'Format gambar tidak didukung. Gunakan JPG, PNG, WEBP, atau GIF.';
                } else {
                    $imagePath = $file['tmp_name'];
                    $imageMime = $realMime;
                }
            }
        }

        if (!$error) {
            // ── Bangun pesan Telegram ──
            $jenisLabel = [
                'bug'       => '🐛 Bug / Error',
                'konten'    => '🚫 Konten Bermasalah',
                'akun'      => '👤 Masalah Akun',
                'performa'  => '⚡ Performa / Lambat',
                'saran'     => '💡 Saran / Fitur',
                'lainnya'   => '📌 Lainnya',
            ];

            $ip        = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            $ua        = mb_substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 120);
            $waktu     = date('d M Y, H:i') . ' WIB';
            $emailLine = $email ? "📧 *Email:* `{$email}`" : "📧 *Email:* _tidak diisi_";
            $urlLine   = $url_terkait ? "🔗 *URL Terkait:* `{$url_terkait}`" : '';

            $msg  = "🚨 *LAPORAN MASUK — TeleCard*\n";
            $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
            $msg .= "🏷 *Jenis:* {$jenisLabel[$jenis]}\n";
            $msg .= "📋 *Judul:* " . htmlspecialchars($judul, ENT_QUOTES) . "\n";
            $msg .= "{$emailLine}\n";
            if ($urlLine) $msg .= "{$urlLine}\n";
            $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
            $msg .= "📝 *Deskripsi:*\n" . htmlspecialchars($deskripsi, ENT_QUOTES) . "\n";
            $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
            $msg .= "🌐 *IP:* `{$ip}`\n";
            $msg .= "🕐 *Waktu:* {$waktu}\n";
            $msg .= "📱 *UA:* `{$ua}`";
            if ($imagePath) $msg .= "\n🖼 *Lampiran:* Ada gambar terlampir";

            // ── Kirim lewat backend Railway (relay ke Telegram) ──
            $sent = sendViaRailway($msg, $imagePath, $imageMime);

            if ($sent) {
                $success = true;
                $_SESSION['report_count']++;
                unset($_SESSION['csrf_report']);
            } else {
                $error = 'Gagal mengirim laporan. Silakan coba lagi dalam beberapa saat.';
            }
        }
    }
}

// ── Generate CSRF token baru setiap load (kalau belum ada / habis dipakai) ──
if (empty($_SESSION['csrf_report'])) {
    $_SESSION['csrf_report'] = bin2hex(random_bytes(24));
}
$csrfToken = $_SESSION['csrf_report'];

// ── Fungsi kirim pesan (+ gambar opsional) lewat backend Railway ──
function sendViaRailway(string $text, ?string $imagePath = null, ?string $imageMime = null): bool
{
    $backendUrl = 'https://telehub-support-production.up.railway.app/report';

    $ch = curl_init($backendUrl);

    if ($imagePath) {
        // ── Ada gambar: kirim sebagai multipart/form-data ──
        $ext = REPORT_ALLOWED_MIME[$imageMime] ?? 'jpg';
        $cfile = new CURLFile($imagePath, $imageMime, 'report.' . $ext);

        $payload = [
            'message' => $text,
            'photo'   => $cfile,
        ];

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload, // array -> curl otomatis pakai multipart
            CURLOPT_HTTPHEADER     => [
                // Jangan set Content-Type manual, biar curl yang set boundary multipart-nya
                'Origin: https://telehub.nfy.fyi',
            ],
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
    } else {
        // ── Tanpa gambar: tetap kirim JSON seperti semula ──
        $payload = json_encode(['message' => $text]);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Origin: https://telehub.nfy.fyi',
            ],
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (!$response || $httpCode !== 200) {
        return false;
    }

    $json = json_decode($response, true);
    return !empty($json['ok']);
}

include __DIR__ . '/includes/header.php';
?>

<style>
  .report-wrap {
    max-width: 680px;
    margin: 48px auto 64px;
    padding: 0 16px;
  }

  .report-header {
    text-align: center;
    margin-bottom: 36px;
  }

  .report-header .report-icon {
    width: 72px; height: 72px;
    border-radius: 50%;
    background: rgba(42,171,238,0.1);
    border: 1.5px solid rgba(42,171,238,0.25);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 18px;
    font-size: 30px;
  }

  .report-header h1 {
    font-size: 26px; font-weight: 800;
    color: var(--text);
    margin-bottom: 8px;
  }

  .report-header p {
    color: var(--text-dim);
    font-size: 14.5px;
    line-height: 1.65;
  }

  .report-card {
    background: var(--card-bg, rgba(255,255,255,0.04));
    border: 1px solid var(--border, rgba(255,255,255,0.08));
    border-radius: 20px;
    padding: 36px 32px;
  }

  @media (max-width: 480px) {
    .report-card { padding: 24px 18px; }
  }

  .form-group {
    margin-bottom: 22px;
  }

  .form-group label {
    display: block;
    font-size: 13.5px;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 8px;
  }

  .form-group label .req {
    color: var(--tg-blue);
    margin-left: 2px;
  }

  .form-group input,
  .form-group select,
  .form-group textarea {
    width: 100%;
    background: rgba(255,255,255,0.05);
    border: 1.5px solid rgba(255,255,255,0.10);
    border-radius: 12px;
    padding: 12px 16px;
    color: var(--text);
    font-size: 14px;
    font-family: inherit;
    transition: border-color 0.2s, box-shadow 0.2s;
    outline: none;
    box-sizing: border-box;
  }

  .form-group select {
    appearance: none;
    -webkit-appearance: none;
    cursor: pointer;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' fill='none'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%236b7280' stroke-width='1.5' stroke-linecap='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
    padding-right: 36px;
  }

  .form-group select option {
    background: #1a1a2e;
    color: #fff;
  }

  .form-group input:focus,
  .form-group select:focus,
  .form-group textarea:focus {
    border-color: var(--tg-blue);
    box-shadow: 0 0 0 3px rgba(42,171,238,0.12);
  }

  .form-group textarea {
    resize: vertical;
    min-height: 130px;
    line-height: 1.6;
  }

  .form-group .hint {
    margin-top: 6px;
    font-size: 12px;
    color: var(--text-dim);
  }

  /* Jenis Report Chips */
  .jenis-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
  }

  @media (max-width: 480px) {
    .jenis-grid { grid-template-columns: repeat(2, 1fr); }
  }

  .jenis-chip input[type="radio"] { display: none; }

  .jenis-chip label {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 14px 8px;
    border-radius: 12px;
    border: 1.5px solid rgba(255,255,255,0.08);
    background: rgba(255,255,255,0.03);
    cursor: pointer;
    transition: all 0.2s;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--text-dim);
    text-align: center;
    user-select: none;
  }

  .jenis-chip label .chip-icon { font-size: 22px; }

  .jenis-chip input[type="radio"]:checked + label {
    border-color: var(--tg-blue);
    background: rgba(42,171,238,0.10);
    color: var(--tg-blue);
    box-shadow: 0 0 0 3px rgba(42,171,238,0.10);
  }

  .jenis-chip label:hover {
    border-color: rgba(42,171,238,0.4);
    color: var(--text);
  }

  /* Alert */
  .alert {
    padding: 14px 18px;
    border-radius: 12px;
    font-size: 14px;
    margin-bottom: 24px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    line-height: 1.55;
  }

  .alert-success {
    background: rgba(34,197,94,0.08);
    border: 1px solid rgba(34,197,94,0.25);
    color: #86efac;
  }

  .alert-error {
    background: rgba(239,68,68,0.08);
    border: 1px solid rgba(239,68,68,0.25);
    color: #fca5a5;
  }

  .alert-icon { font-size: 18px; flex-shrink: 0; margin-top: 1px; }

  /* Submit button */
  .btn-report {
    width: 100%;
    padding: 15px;
    background: linear-gradient(135deg, var(--tg-blue) 0%, #1a7fd4 100%);
    color: #fff;
    font-size: 15px;
    font-weight: 700;
    border: none;
    border-radius: 14px;
    cursor: pointer;
    transition: all 0.22s;
    box-shadow: 0 4px 20px rgba(42,171,238,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 8px;
  }

  .btn-report:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 32px rgba(42,171,238,0.45);
  }

  .btn-report:active { transform: translateY(0); }

  .btn-report svg { width: 18px; height: 18px; }
  .btn-report[disabled] { opacity: 0.7; cursor: not-allowed; transform: none !important; }

  /* Divider */
  .form-divider {
    height: 1px;
    background: rgba(255,255,255,0.07);
    margin: 28px 0;
  }

  .optional-badge {
    font-size: 11px;
    font-weight: 500;
    color: var(--text-dim);
    background: rgba(255,255,255,0.06);
    border-radius: 99px;
    padding: 2px 8px;
    margin-left: 8px;
    vertical-align: middle;
  }

  /* Upload Gambar */
  .upload-box {
    position: relative;
    border: 1.5px dashed rgba(255,255,255,0.16);
    border-radius: 14px;
    padding: 22px 16px;
    text-align: center;
    cursor: pointer;
    transition: border-color 0.2s, background 0.2s;
    background: rgba(255,255,255,0.02);
  }

  .upload-box:hover,
  .upload-box.dragover {
    border-color: var(--tg-blue);
    background: rgba(42,171,238,0.05);
  }

  .upload-box input[type="file"] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
    width: 100%;
    height: 100%;
  }

  .upload-box .upload-icon {
    font-size: 26px;
    margin-bottom: 6px;
  }

  .upload-box .upload-text {
    font-size: 13px;
    color: var(--text-dim);
  }

  .upload-box .upload-text strong {
    color: var(--tg-blue);
  }

  .upload-preview {
    display: none;
    position: relative;
    margin-top: 12px;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,0.1);
  }

  .upload-preview.show { display: block; }

  .upload-preview img {
    display: block;
    width: 100%;
    max-height: 260px;
    object-fit: cover;
  }

  .upload-preview .remove-img {
    position: absolute;
    top: 8px; right: 8px;
    width: 28px; height: 28px;
    border-radius: 50%;
    background: rgba(0,0,0,0.65);
    border: none;
    color: #fff;
    font-size: 15px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
  }

  .upload-preview .remove-img:hover { background: rgba(220,38,38,0.85); }

  .upload-error {
    color: #fca5a5;
    font-size: 12px;
    margin-top: 6px;
    display: none;
  }

  .upload-error.show { display: block; }
</style>

<div class="report-wrap">

  <div class="report-header">
    <div class="report-icon">🚨</div>
    <h1>Laporkan Masalah</h1>
    <p>Temukan bug, konten bermasalah, atau punya saran?<br>Laporan kamu langsung masuk ke tim TeleCard.</p>
  </div>

  <?php if ($success): ?>
    <div class="alert alert-success">
      <span class="alert-icon">✅</span>
      <div>
        <strong>Laporan berhasil dikirim!</strong><br>
        Terima kasih sudah melaporkan. Tim TeleCard akan menindaklanjuti laporan kamu secepatnya.
      </div>
    </div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert alert-error">
      <span class="alert-icon">⚠️</span>
      <div><?= htmlspecialchars($error) ?></div>
    </div>
  <?php endif; ?>

  <?php if (!$success): ?>
  <div class="report-card">
    <form method="POST" action="report.php" autocomplete="off" enctype="multipart/form-data" id="reportForm">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrfToken) ?>">

      <!-- Jenis Laporan -->
      <div class="form-group">
        <label>Jenis Laporan <span class="req">*</span></label>
        <div class="jenis-grid">
          <div class="jenis-chip">
            <input type="radio" name="jenis" id="j-bug" value="bug"
              <?= (($_POST['jenis'] ?? '') === 'bug') ? 'checked' : '' ?>>
            <label for="j-bug">
              <span class="chip-icon">🐛</span>
              Bug / Error
            </label>
          </div>
          <div class="jenis-chip">
            <input type="radio" name="jenis" id="j-konten" value="konten"
              <?= (($_POST['jenis'] ?? '') === 'konten') ? 'checked' : '' ?>>
            <label for="j-konten">
              <span class="chip-icon">🚫</span>
              Konten Bermasalah
            </label>
          </div>
          <div class="jenis-chip">
            <input type="radio" name="jenis" id="j-akun" value="akun"
              <?= (($_POST['jenis'] ?? '') === 'akun') ? 'checked' : '' ?>>
            <label for="j-akun">
              <span class="chip-icon">👤</span>
              Masalah Akun
            </label>
          </div>
          <div class="jenis-chip">
            <input type="radio" name="jenis" id="j-performa" value="performa"
              <?= (($_POST['jenis'] ?? '') === 'performa') ? 'checked' : '' ?>>
            <label for="j-performa">
              <span class="chip-icon">⚡</span>
              Performa / Lambat
            </label>
          </div>
          <div class="jenis-chip">
            <input type="radio" name="jenis" id="j-saran" value="saran"
              <?= (($_POST['jenis'] ?? '') === 'saran') ? 'checked' : '' ?>>
            <label for="j-saran">
              <span class="chip-icon">💡</span>
              Saran / Fitur
            </label>
          </div>
          <div class="jenis-chip">
            <input type="radio" name="jenis" id="j-lainnya" value="lainnya"
              <?= (($_POST['jenis'] ?? '') === 'lainnya' || empty($_POST['jenis'])) ? 'checked' : '' ?>>
            <label for="j-lainnya">
              <span class="chip-icon">📌</span>
              Lainnya
            </label>
          </div>
        </div>
      </div>

      <!-- Judul -->
      <div class="form-group">
        <label for="judul">Judul Laporan <span class="req">*</span></label>
        <input type="text" id="judul" name="judul" maxlength="120"
          placeholder="Contoh: Tombol submit artikel tidak bisa diklik"
          value="<?= htmlspecialchars($_POST['judul'] ?? '') ?>">
        <div class="hint">5–120 karakter</div>
      </div>

      <!-- Deskripsi -->
      <div class="form-group">
        <label for="deskripsi">Deskripsi Masalah <span class="req">*</span></label>
        <textarea id="deskripsi" name="deskripsi" maxlength="2000"
          placeholder="Ceritakan masalah yang kamu temukan secara detail. Apa yang terjadi? Apa yang seharusnya terjadi? Langkah-langkah untuk mereproduksi masalah?"><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
        <div class="hint">20–2000 karakter</div>
      </div>

      <!-- Upload Gambar (opsional) -->
      <div class="form-group">
        <label for="gambar">
          Lampirkan Gambar
          <span class="optional-badge">Opsional</span>
        </label>
        <div class="upload-box" id="uploadBox">
          <input type="file" id="gambar" name="gambar" accept="image/jpeg,image/png,image/webp,image/gif">
          <div class="upload-icon">🖼️</div>
          <div class="upload-text"><strong>Klik untuk pilih gambar</strong> atau drag & drop</div>
        </div>
        <div class="hint">JPG, PNG, WEBP, atau GIF. Maksimal 5MB.</div>
        <div class="upload-error" id="uploadError"></div>
        <div class="upload-preview" id="uploadPreview">
          <img id="uploadPreviewImg" src="" alt="Preview gambar">
          <button type="button" class="remove-img" id="removeImgBtn">✕</button>
        </div>
      </div>

      <div class="form-divider"></div>

      <!-- URL Terkait (opsional) -->
      <div class="form-group">
        <label for="url_terkait">
          URL Terkait
          <span class="optional-badge">Opsional</span>
        </label>
        <input type="text" id="url_terkait" name="url_terkait" maxlength="300"
          placeholder="https://telehub.nfy.fyi/halaman-yang-bermasalah"
          value="<?= htmlspecialchars($_POST['url_terkait'] ?? '') ?>">
        <div class="hint">Isi kalau masalahnya terjadi di halaman tertentu</div>
      </div>

      <!-- Email (opsional) -->
      <div class="form-group">
        <label for="email">
          Email Kamu
          <span class="optional-badge">Opsional</span>
        </label>
        <input type="email" id="email" name="email" maxlength="120"
          placeholder="email@kamu.com (opsional, untuk tindak lanjut)"
          value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        <div class="hint">Isi kalau kamu ingin dihubungi balik soal laporan ini</div>
      </div>

      <!-- Submit -->
      <button type="submit" class="btn-report" id="reportSubmitBtn">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
        </svg>
        <span id="reportSubmitText">Kirim Laporan</span>
      </button>
    </form>
  </div>
  <?php endif; ?>

</div>

<script>
(function () {
    var input      = document.getElementById('gambar');
    var box        = document.getElementById('uploadBox');
    var preview    = document.getElementById('uploadPreview');
    var previewImg = document.getElementById('uploadPreviewImg');
    var removeBtn  = document.getElementById('removeImgBtn');
    var errorEl    = document.getElementById('uploadError');
    var form       = document.getElementById('reportForm');
    var submitBtn  = document.getElementById('reportSubmitBtn');
    var submitText = document.getElementById('reportSubmitText');

    var MAX_SIZE = 5 * 1024 * 1024;
    var ALLOWED  = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    if (!input) return;

    function showError(msg) {
        errorEl.textContent = msg;
        errorEl.classList.add('show');
    }
    function clearError() {
        errorEl.textContent = '';
        errorEl.classList.remove('show');
    }
    function showPreview(file) {
        var reader = new FileReader();
        reader.onload = function (e) {
            previewImg.src = e.target.result;
            preview.classList.add('show');
            box.style.display = 'none';
        };
        reader.readAsDataURL(file);
    }
    function resetUpload() {
        input.value = '';
        preview.classList.remove('show');
        previewImg.src = '';
        box.style.display = '';
        clearError();
    }

    function handleFile(file) {
        clearError();
        if (!file) return;
        if (ALLOWED.indexOf(file.type) === -1) {
            showError('Format tidak didukung. Gunakan JPG, PNG, WEBP, atau GIF.');
            input.value = '';
            return;
        }
        if (file.size > MAX_SIZE) {
            showError('Ukuran gambar maksimal 5MB.');
            input.value = '';
            return;
        }
        showPreview(file);
    }

    input.addEventListener('change', function () {
        handleFile(this.files[0]);
    });

    removeBtn.addEventListener('click', resetUpload);

    // Drag & drop
    ['dragover', 'dragenter'].forEach(function (evt) {
        box.addEventListener(evt, function (e) {
            e.preventDefault();
            box.classList.add('dragover');
        });
    });
    ['dragleave', 'drop'].forEach(function (evt) {
        box.addEventListener(evt, function (e) {
            e.preventDefault();
            box.classList.remove('dragover');
        });
    });
    box.addEventListener('drop', function (e) {
        var file = e.dataTransfer.files[0];
        if (file) {
            input.files = e.dataTransfer.files;
            handleFile(file);
        }
    });

    form.addEventListener('submit', function () {
        submitBtn.setAttribute('disabled', 'disabled');
        submitText.textContent = 'Mengirim...';
    });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
