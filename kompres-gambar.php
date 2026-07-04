<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Kompres Gambar';
$metaDesc = 'Kompres JPG, PNG, atau WEBP tanpa mengurangi kualitas gambar. Gratis dan cepat.';
$metaKeywords = 'kompres gambar, compress image, perkecil ukuran foto';
include __DIR__ . '/includes/header.php';
?>

<style>
  .kg-wrap {
    max-width: 720px;
    margin: 48px auto 64px;
    padding: 0 16px;
  }

  .kg-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 20px;
    padding: 8px 16px;
    border-radius: 10px;
    border: 1px solid var(--border, rgba(255,255,255,0.15));
    background: rgba(255,255,255,0.02);
    color: var(--text-dim);
    font-size: 13.5px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.15s;
  }

  .kg-back-btn:hover {
    background: rgba(255,255,255,0.05);
    color: var(--text);
    border-color: rgba(255,255,255,0.25);
  }

  .kg-header {
    text-align: center;
    margin-bottom: 36px;
  }

  .kg-header h1 {
    font-size: 32px;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 12px;
  }

  .kg-header h1 span {
    color: var(--tg-blue);
  }

  .kg-header p {
    color: var(--text-dim);
    font-size: 15px;
    line-height: 1.7;
    max-width: 520px;
    margin: 0 auto;
  }

  .kg-dropzone {
    position: relative;
    border: 2.5px dashed var(--border, rgba(255,255,255,0.15));
    border-radius: 20px;
    padding: 56px 24px;
    text-align: center;
    background: rgba(255,255,255,0.02);
    transition: all 0.2s;
    cursor: pointer;
  }

  .kg-dropzone.dragover {
    border-color: var(--tg-blue);
    background: rgba(42,171,238,0.06);
  }

  .kg-dropzone input[type="file"] {
    display: none;
  }

  .kg-pick-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 16px 40px;
    border-radius: 14px;
    border: none;
    background: linear-gradient(135deg, var(--tg-blue) 0%, #1a7fd4 100%);
    color: #fff;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(42,171,238,0.35);
    transition: all 0.2s;
  }

  .kg-pick-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 28px rgba(42,171,238,0.5);
  }

  .kg-dropzone-hint {
    margin-top: 16px;
    color: var(--text-dim);
    font-size: 13.5px;
  }

  .kg-dropzone-formats {
    margin-top: 8px;
    color: var(--text-dim);
    font-size: 12px;
    opacity: 0.7;
  }

  .kg-note {
    margin-top: 20px;
    padding: 12px 16px;
    border-radius: 12px;
    background: rgba(250,204,21,0.07);
    border: 1px solid rgba(250,204,21,0.2);
    color: #fbbf24;
    font-size: 12.5px;
    line-height: 1.6;
    text-align: center;
  }

  /* ===== Hasil kompresi (list) ===== */
  .kg-results {
    margin-top: 24px;
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .kg-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 16px;
    border-radius: 16px;
    border: 1.5px solid var(--border, rgba(255,255,255,0.1));
    background: rgba(255,255,255,0.02);
  }

  .kg-item-thumb {
    width: 56px;
    height: 56px;
    border-radius: 10px;
    object-fit: cover;
    flex-shrink: 0;
    background: rgba(255,255,255,0.05);
  }

  .kg-item-info {
    flex: 1;
    min-width: 0;
  }

  .kg-item-name {
    font-size: 13.5px;
    font-weight: 700;
    color: var(--text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 6px;
  }

  .kg-item-progress-bar {
    width: 100%;
    height: 6px;
    border-radius: 99px;
    background: rgba(255,255,255,0.08);
    overflow: hidden;
    margin-bottom: 6px;
  }

  .kg-item-progress-fill {
    height: 100%;
    width: 0%;
    border-radius: 99px;
    background: linear-gradient(90deg, var(--tg-blue), #1a7fd4);
    transition: width 0.15s ease-out;
  }

  .kg-item-progress-fill.done {
    background: linear-gradient(90deg, #22c55e, #16a34a);
  }

  .kg-item-progress-fill.error {
    background: linear-gradient(90deg, #ef4444, #b91c1c);
  }

  .kg-item-status {
    font-size: 12px;
    color: var(--text-dim);
  }

  .kg-item-status .saved {
    color: #4ade80;
    font-weight: 700;
  }

  .kg-item-status .error-text {
    color: #f87171;
    font-weight: 700;
  }

  .kg-item-action {
    flex-shrink: 0;
  }

  .kg-download-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 16px;
    border-radius: 10px;
    background: rgba(34,197,94,0.12);
    border: 1px solid rgba(34,197,94,0.3);
    color: #4ade80;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
    transition: all 0.15s;
  }

  .kg-download-btn:hover {
    background: rgba(34,197,94,0.2);
  }

  .kg-clear-wrap {
    text-align: center;
    margin-top: 20px;
  }

  .kg-clear-btn {
    background: none;
    border: 1px solid var(--border, rgba(255,255,255,0.15));
    color: var(--text-dim);
    padding: 10px 20px;
    border-radius: 10px;
    font-size: 13.5px;
    cursor: pointer;
    transition: all 0.15s;
  }

  .kg-clear-btn:hover {
    background: rgba(255,255,255,0.05);
    color: var(--text);
  }
</style>

<div class="kg-wrap">

  <a href="/submit-article.php" class="kg-back-btn">← Kembali ke Tulis Artikel</a>

  <div class="kg-header">
    <h1>Kompres <span>Gambar</span></h1>
    <p>Perkecil ukuran file JPG, PNG, atau WEBP tanpa mengurangi kualitas gambar. Proses otomatis, langsung download.</p>
  </div>

  <div class="kg-dropzone" id="kgDropzone">
    <input type="file" id="kgFileInput" accept="image/jpeg,image/jpg,image/png,image/webp" multiple>
    <button type="button" class="kg-pick-btn" id="kgPickBtn">
      🖼️ Pilih Gambar
    </button>
    <div class="kg-dropzone-hint">atau jatuhkan gambar di sini</div>
    <div class="kg-dropzone-formats">Mendukung JPG, PNG, WEBP — maks 15MB per file, bisa banyak sekaligus</div>
  </div>

  <div class="kg-note">
    ⏳ File hasil kompresi disimpan sementara di server dan <strong>otomatis terhapus dalam 2 hari</strong>. Pastikan download filenya sebelum itu.
  </div>

  <div class="kg-results" id="kgResults"></div>

  <div class="kg-clear-wrap" id="kgClearWrap" style="display:none">
    <button type="button" class="kg-clear-btn" id="kgClearBtn">🗑️ Bersihkan Daftar</button>
  </div>

</div>

<script>
(function () {
  // URL backend kompresi gambar (Railway)
  const WORKER_URL = <?= json_encode(defined('IMAGE_COMPRESSOR_WORKER_URL') ? IMAGE_COMPRESSOR_WORKER_URL : 'https://telehub-image-compressor-production.up.railway.app') ?>;

  const dropzone   = document.getElementById('kgDropzone');
  const fileInput  = document.getElementById('kgFileInput');
  const pickBtn    = document.getElementById('kgPickBtn');
  const resultsBox = document.getElementById('kgResults');
  const clearWrap  = document.getElementById('kgClearWrap');
  const clearBtn   = document.getElementById('kgClearBtn');

  function formatBytes(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
  }

  pickBtn.addEventListener('click', () => fileInput.click());

  dropzone.addEventListener('click', (e) => {
    if (e.target === pickBtn) return;
    fileInput.click();
  });

  ['dragenter', 'dragover'].forEach(evt => {
    dropzone.addEventListener(evt, (e) => {
      e.preventDefault();
      e.stopPropagation();
      dropzone.classList.add('dragover');
    });
  });

  ['dragleave', 'drop'].forEach(evt => {
    dropzone.addEventListener(evt, (e) => {
      e.preventDefault();
      e.stopPropagation();
      dropzone.classList.remove('dragover');
    });
  });

  dropzone.addEventListener('drop', (e) => {
    const files = e.dataTransfer.files;
    if (files && files.length) handleFiles(files);
  });

  fileInput.addEventListener('change', () => {
    if (fileInput.files && fileInput.files.length) {
      handleFiles(fileInput.files);
      fileInput.value = '';
    }
  });

  function handleFiles(fileList) {
    clearWrap.style.display = 'block';
    Array.from(fileList).forEach(file => {
      if (!file.type.startsWith('image/')) return;
      uploadFile(file);
    });
  }

  function uploadFile(file) {
    const itemId = 'kg_' + Date.now() + '_' + Math.random().toString(36).slice(2);

    const item = document.createElement('div');
    item.className = 'kg-item';
    item.id = itemId;
    item.innerHTML = `
      <img class="kg-item-thumb" id="${itemId}_thumb" src="" alt="">
      <div class="kg-item-info">
        <div class="kg-item-name">${escapeHtml(file.name)}</div>
        <div class="kg-item-progress-bar">
          <div class="kg-item-progress-fill" id="${itemId}_fill"></div>
        </div>
        <div class="kg-item-status" id="${itemId}_status">Mengupload... 0%</div>
      </div>
      <div class="kg-item-action" id="${itemId}_action"></div>
    `;
    resultsBox.prepend(item);

    // Preview thumbnail lokal
    const reader = new FileReader();
    reader.onload = e => {
      const thumb = document.getElementById(itemId + '_thumb');
      if (thumb) thumb.src = e.target.result;
    };
    reader.readAsDataURL(file);

    const fill   = document.getElementById(itemId + '_fill');
    const status = document.getElementById(itemId + '_status');
    const action = document.getElementById(itemId + '_action');

    const formData = new FormData();
    formData.append('image', file);

    const xhr = new XMLHttpRequest();
    xhr.open('POST', WORKER_URL + '/compress', true);

    // Progress upload BENERAN, dihitung dari byte yang sudah terkirim
    xhr.upload.addEventListener('progress', function (e) {
      if (e.lengthComputable) {
        const percent = Math.round((e.loaded / e.total) * 100);
        fill.style.width = percent + '%';
        status.textContent = percent < 100
          ? `Mengupload... ${percent}%`
          : 'Memproses & mengompres gambar...';
      }
    });

    xhr.onload = function () {
      let data;
      try {
        data = JSON.parse(xhr.responseText);
      } catch (err) {
        data = null;
      }

      if (xhr.status >= 200 && xhr.status < 300 && data && data.ok) {
        fill.style.width = '100%';
        fill.classList.add('done');

        const savedPercent = data.savedPercent;
        const savedText = savedPercent > 0
          ? `<span class="saved">-${savedPercent}%</span> · ${formatBytes(data.originalSize)} → ${formatBytes(data.compressedSize)}`
          : `Ukuran sudah optimal · ${formatBytes(data.compressedSize)}`;

        status.innerHTML = savedText;

        const downloadUrl = WORKER_URL + data.downloadUrl;
        action.innerHTML = `<a href="${downloadUrl}" class="kg-download-btn" download>⬇️ Download</a>`;
      } else {
        fill.classList.add('error');
        status.innerHTML = `<span class="error-text">${(data && data.error) ? escapeHtml(data.error) : 'Gagal memproses gambar.'}</span>`;
      }
    };

    xhr.onerror = function () {
      fill.classList.add('error');
      status.innerHTML = '<span class="error-text">Gagal terhubung ke server kompresi.</span>';
    };

    xhr.send(formData);
  }

  clearBtn.addEventListener('click', () => {
    resultsBox.innerHTML = '';
    clearWrap.style.display = 'none';
  });

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
