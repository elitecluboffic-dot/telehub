<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Tulis Artikel';
$metaDesc = 'Submit artikel kamu ke TeleCard. Gratis, tanpa perlu login.';
$error = '';
$success = false;
$categories = ['Tips & Trik', 'Komunitas', 'Channel', 'Grup', 'Tutorial', 'Berita', 'Lainnya'];

// ============================
// FUNGSI VERIFIKASI reCAPTCHA
// ============================
function verifyRecaptcha($token) {
    if (empty($token)) return false;

    $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'secret'   => RECAPTCHA_SECRET_KEY,
            'response' => $token,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $response = curl_exec($ch);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($response === false || $curlErr) {
        return false;
    }

    $result = json_decode($response, true);
    return !empty($result['success']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // PENTING: jangan pakai clean() (htmlspecialchars) di sini.
    // Simpan teks apa adanya ke DB, escape-nya dilakukan pas nampilin (output),
    // bukan pas nyimpen (input) — biar '&' gak jadi '&amp;' dobel.
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $author  = trim($_POST['author_name'] ?? '');
    $cat     = trim($_POST['category'] ?? '');
    $recaptchaToken = $_POST['g-recaptcha-response'] ?? '';
    $image_path = null;

    if (!$title || !$content || !$author || !$cat) {
        $error = 'Semua field wajib diisi.';
    } elseif (mb_strlen($title) < 5) {
        $error = 'Judul terlalu pendek, minimal 5 karakter.';
    } elseif (mb_strlen($content) < 50) {
        $error = 'Konten terlalu pendek, minimal 50 karakter.';
    } elseif (!verifyRecaptcha($recaptchaToken)) {
        $error = 'Verifikasi captcha gagal. Silakan centang "Saya bukan robot" terlebih dahulu.';
    } else {
        // Handle upload foto
        if (!empty($_FILES['image']['name'])) {
            $allowed = ['jpg','jpeg','png','webp'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $error = 'Format foto tidak didukung. Gunakan JPG, PNG, atau WEBP.';
            } elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
                $error = 'Ukuran foto maksimal 2MB.';
            } else {
                $filename = 'article_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
                if (move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_DIR . $filename)) {
                    $image_path = $filename;
                } else {
                    $error = 'Gagal mengupload foto.';
                }
            }
        }

        if (!$error) {
            $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($title));
            $slug = trim($slug, '-');
            $check = $pdo->prepare("SELECT id FROM articles WHERE slug = ?");
            $check->execute([$slug]);
            if ($check->fetch()) $slug .= '-' . time();

            $pdo->prepare("INSERT INTO articles (title, content, author_name, category, slug, image_path) VALUES (?,?,?,?,?,?)")
                ->execute([$title, $content, $author, $cat, $slug, $image_path]);
            $success = true;
        }
    }
}
include __DIR__ . '/includes/header.php';
?>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<style>
  .upload-progress-wrap {
    display: none;
    margin-top: 14px;
    padding: 14px 16px;
    border-radius: 12px;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--border, rgba(255,255,255,0.1));
  }
  .upload-progress-wrap.show { display: block; }

  .upload-progress-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
  }

  .upload-progress-label {
    font-size: 13.5px;
    font-weight: 700;
    color: var(--text);
  }

  .upload-progress-percent {
    font-size: 13.5px;
    font-weight: 800;
    color: var(--tg-blue, #2aabee);
  }

  .upload-progress-bar {
    width: 100%;
    height: 8px;
    border-radius: 99px;
    background: rgba(255,255,255,0.08);
    overflow: hidden;
  }

  .upload-progress-bar-fill {
    height: 100%;
    width: 0%;
    border-radius: 99px;
    background: linear-gradient(90deg, var(--tg-blue, #2aabee), #1a7fd4);
    transition: width 0.15s ease-out;
  }

  .upload-progress-bar-fill.done {
    background: linear-gradient(90deg, #22c55e, #16a34a);
  }

  .upload-progress-bar-fill.error {
    background: linear-gradient(90deg, #ef4444, #b91c1c);
  }

  .submit-article-form.submitting .form-group,
  .submit-article-form.submitting .btn-block {
    opacity: 0.6;
    pointer-events: none;
  }

  .kg-resize-hint {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-top: 10px;
    padding: 10px 14px;
    border-radius: 10px;
    background: rgba(42,171,238,0.06);
    border: 1px solid rgba(42,171,238,0.2);
    flex-wrap: wrap;
  }

  .kg-resize-hint span {
    font-size: 12.5px;
    color: var(--text-dim);
    line-height: 1.5;
  }

  .kg-resize-hint a {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    border-radius: 8px;
    background: var(--tg-blue, #2aabee);
    color: #fff;
    font-size: 12.5px;
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
    transition: all 0.15s;
  }

  .kg-resize-hint a:hover {
    background: #1a7fd4;
    transform: translateY(-1px);
  }
</style>

<div class="submit-article-wrap">
  <div class="submit-article-header">
    <h1>✍️ Tulis <span>Artikel</span></h1>
    <p>Bagikan tips, pengalaman, atau informasi seputar Telegram. Artikel akan direview Tim Kami sebelum dipublikasikan.</p>
  </div>

  <?php if ($success): ?>
    <div class="alert alert-success" style="text-align:center;padding:32px">
      <div style="font-size:48px;margin-bottom:12px">🎉</div>
      <h3 style="margin:0 0 8px">Artikel Terkirim!</h3>
      <p style="color:var(--text-dim);margin:0 0 20px">Artikel kamu sedang direview oleh Tim Kami. Terima kasih sudah berkontribusi!</p>
      <a href="articles.php" class="btn btn-primary">Lihat Semua Artikel</a>
      <a href="submit-article.php" class="btn btn-outline" style="margin-left:8px">Tulis Lagi</a>
    </div>
  <?php else: ?>
    <?php if ($error): ?><div class="alert alert-error"><?= clean($error) ?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="submit-article-form" id="articleForm">
      <div class="form-group">
        <label>Judul Artikel <span style="color:var(--red)">*</span></label>
        <input type="text" name="title" value="<?= clean($_POST['title'] ?? '') ?>" placeholder="Masukkan judul yang menarik..." required>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Nama Penulis <span style="color:var(--red)">*</span></label>
          <input type="text" name="author_name" value="<?= clean($_POST['author_name'] ?? '') ?>" placeholder="Nama kamu..." required>
        </div>
        <div class="form-group">
          <label>Kategori <span style="color:var(--red)">*</span></label>
          <select name="category" required>
            <option value="">-- Pilih Kategori --</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= clean($cat) ?>" <?= (($_POST['category'] ?? '') == $cat) ? 'selected' : '' ?>><?= clean($cat) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label>Foto Cover <span style="color:var(--text-dim);font-weight:400">(opsional, maks 2MB)</span></label>
        <div class="upload-area" id="uploadArea">
          <input type="file" name="image" id="imageInput" accept="image/jpg,image/jpeg,image/png,image/webp" style="display:none">
          <div id="uploadPlaceholder" onclick="document.getElementById('imageInput').click()" style="cursor:pointer">
            <div style="font-size:36px;margin-bottom:8px">🖼️</div>
            <p style="margin:0;color:var(--text-dim);font-size:14px">Klik untuk upload foto cover</p>
            <p style="margin:4px 0 0;color:var(--text-dim);font-size:12px">JPG, PNG, WEBP — maks 2MB</p>
          </div>
          <img id="imagePreview" src="" style="display:none;max-width:100%;max-height:220px;border-radius:10px;object-fit:cover">
          <button type="button" id="removeImage" style="display:none;margin-top:10px;background:none;border:1px solid var(--red);color:var(--red);padding:6px 14px;border-radius:8px;cursor:pointer;font-size:13px">✕ Hapus Foto</button>
        </div>
        <div class="kg-resize-hint">
          <span>📸 Foto kamu lebih dari 2MB? Kompres dulu biar ukurannya pas.</span>
          <a href="/kompres-gambar.php" target="_blank" rel="noopener">🗜️ Kompres Gambar</a>
        </div>
      </div>
      <div class="form-group">
        <label>Konten Artikel <span style="color:var(--red)">*</span></label>
        <textarea name="content" rows="14" placeholder="Tulis artikel kamu di sini... (minimal 50 karakter)" required><?= clean($_POST['content'] ?? '') ?></textarea>
        <small style="color:var(--text-dim)">Tip: Tekan Enter 2x untuk paragraf baru.</small>
      </div>

      <div class="form-group">
        <label>Verifikasi <span style="color:var(--red)">*</span></label>
        <div class="g-recaptcha" data-sitekey="<?= clean(RECAPTCHA_SITE_KEY) ?>"></div>
      </div>

      <button type="submit" class="btn btn-primary btn-block" id="articleSubmitBtn" style="margin-top:8px">🚀 Kirim Artikel</button>

      <div class="upload-progress-wrap" id="uploadProgressWrap">
        <div class="upload-progress-top">
          <span class="upload-progress-label" id="uploadProgressLabel">Mengirim artikel...</span>
          <span class="upload-progress-percent" id="uploadProgressPercent">0%</span>
        </div>
        <div class="upload-progress-bar">
          <div class="upload-progress-bar-fill" id="uploadProgressFill"></div>
        </div>
      </div>
    </form>
  <?php endif; ?>
</div>

<script>
const input = document.getElementById('imageInput');
const preview = document.getElementById('imagePreview');
const placeholder = document.getElementById('uploadPlaceholder');
const removeBtn = document.getElementById('removeImage');

input.addEventListener('change', function() {
  if (this.files && this.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      preview.src = e.target.result;
      preview.style.display = 'block';
      placeholder.style.display = 'none';
      removeBtn.style.display = 'inline-block';
    };
    reader.readAsDataURL(this.files[0]);
  }
});

removeBtn.addEventListener('click', function() {
  input.value = '';
  preview.src = '';
  preview.style.display = 'none';
  placeholder.style.display = 'block';
  removeBtn.style.display = 'none';
});

// ============================
// SUBMIT DENGAN PROGRESS UPLOAD BENERAN (XHR upload.onprogress)
// ============================
(function () {
  const form         = document.getElementById('articleForm');
  const submitBtn    = document.getElementById('articleSubmitBtn');
  const progressWrap = document.getElementById('uploadProgressWrap');
  const progressFill = document.getElementById('uploadProgressFill');
  const progressPct  = document.getElementById('uploadProgressPercent');
  const progressLbl  = document.getElementById('uploadProgressLabel');

  if (!form) return;

  function resetProgressUI() {
    progressWrap.classList.remove('show');
    progressFill.style.width = '0%';
    progressFill.classList.remove('done', 'error');
    progressPct.textContent = '0%';
    progressLbl.textContent = 'Mengirim artikel...';
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    // Validasi captcha dulu sebelum kirim apa pun
    if (typeof grecaptcha === 'undefined' || !grecaptcha.getResponse().length) {
      alert('Mohon centang "Saya bukan robot" terlebih dahulu.');
      return;
    }

    const formData = new FormData(form);
    const xhr = new XMLHttpRequest();

    xhr.open('POST', window.location.href, true);

    // UI: masuk mode submitting
    form.classList.add('submitting');
    submitBtn.disabled = true;
    progressWrap.classList.add('show');
    progressFill.style.width = '0%';
    progressFill.classList.remove('done', 'error');
    progressPct.textContent = '0%';
    progressLbl.textContent = 'Mengupload data...';

    // Progress upload beneran — dihitung dari byte yang benar-benar terkirim
    xhr.upload.addEventListener('progress', function (e) {
      if (e.lengthComputable) {
        const percent = Math.round((e.loaded / e.total) * 100);
        progressFill.style.width = percent + '%';
        progressPct.textContent = percent + '%';
        if (percent >= 100) {
          progressLbl.textContent = 'Menunggu respons server...';
        }
      }
    });

    xhr.upload.addEventListener('error', function () {
      progressFill.classList.add('error');
      progressLbl.textContent = 'Gagal mengupload. Cek koneksi kamu.';
      submitBtn.disabled = false;
      form.classList.remove('submitting');
    });

    xhr.onload = function () {
      if (xhr.status >= 200 && xhr.status < 400) {
        progressFill.style.width = '100%';
        progressFill.classList.add('done');
        progressPct.textContent = '100%';
        progressLbl.textContent = 'Selesai!';

        // Ganti seluruh halaman dengan hasil render PHP (state sukses / error dari server)
        document.open();
        document.write(xhr.responseText);
        document.close();
      } else {
        progressFill.classList.add('error');
        progressLbl.textContent = 'Server merespons dengan error (HTTP ' + xhr.status + ').';
        submitBtn.disabled = false;
        form.classList.remove('submitting');
      }
    };

    xhr.onerror = function () {
      progressFill.classList.add('error');
      progressLbl.textContent = 'Gagal terhubung ke server. Coba lagi.';
      submitBtn.disabled = false;
      form.classList.remove('submitting');
    };

    xhr.send(formData);
  });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
