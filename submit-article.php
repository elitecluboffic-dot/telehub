<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Tulis Artikel';
$metaDesc = 'Submit artikel kamu ke TeleCard. Gratis, tanpa perlu login.';
$error = '';
$success = false;
$categories = ['Tips & Trik', 'Komunitas', 'Channel', 'Grup', 'Tutorial', 'Berita', 'Lainnya'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // PENTING: jangan pakai clean() (htmlspecialchars) di sini.
    // Simpan teks apa adanya ke DB, escape-nya dilakukan pas nampilin (output),
    // bukan pas nyimpen (input) — biar '&' gak jadi '&amp;' dobel.
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $author  = trim($_POST['author_name'] ?? '');
    $cat     = trim($_POST['category'] ?? '');
    $image_path = null;

    if (!$title || !$content || !$author || !$cat) {
        $error = 'Semua field wajib diisi.';
    } elseif (mb_strlen($title) < 5) {
        $error = 'Judul terlalu pendek, minimal 5 karakter.';
    } elseif (mb_strlen($content) < 50) {
        $error = 'Konten terlalu pendek, minimal 50 karakter.';
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
    <form method="post" enctype="multipart/form-data" class="submit-article-form">
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
      </div>
      <div class="form-group">
        <label>Konten Artikel <span style="color:var(--red)">*</span></label>
        <textarea name="content" rows="14" placeholder="Tulis artikel kamu di sini... (minimal 50 karakter)" required><?= clean($_POST['content'] ?? '') ?></textarea>
        <small style="color:var(--text-dim)">Tip: Tekan Enter 2x untuk paragraf baru.</small>
      </div>
      <button type="submit" class="btn btn-primary btn-block" style="margin-top:8px">🚀 Kirim Artikel</button>
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
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
