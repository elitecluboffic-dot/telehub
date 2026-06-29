<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Tulis Artikel';
$metaDesc = 'Submit artikel kamu ke TeleCard. Gratis, tanpa perlu login.';
$error = '';
$success = false;

$categories = ['Tips & Trik', 'Komunitas', 'Channel', 'Grup', 'Tutorial', 'Berita', 'Lainnya'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = clean($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $author  = clean($_POST['author_name'] ?? '');
    $cat     = clean($_POST['category'] ?? '');

    if (!$title || !$content || !$author || !$cat) {
        $error = 'Semua field wajib diisi.';
    } elseif (mb_strlen($title) < 5) {
        $error = 'Judul terlalu pendek, minimal 5 karakter.';
    } elseif (mb_strlen($content) < 50) {
        $error = 'Konten terlalu pendek, minimal 50 karakter.';
    } else {
        // buat slug unik
        $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($title));
        $slug = trim($slug, '-');
        $check = $pdo->prepare("SELECT id FROM articles WHERE slug = ?");
        $check->execute([$slug]);
        if ($check->fetch()) {
            $slug .= '-' . time();
        }
        $pdo->prepare("INSERT INTO articles (title, content, author_name, category, slug) VALUES (?,?,?,?,?)")
            ->execute([$title, $content, $author, $cat, $slug]);
        $success = true;
    }
}
include __DIR__ . '/includes/header.php';
?>

<div class="submit-article-wrap">
  <div class="submit-article-header">
    <h1>✍️ Tulis <span>Artikel</span></h1>
    <p>Bagikan tips, pengalaman, atau informasi seputar Telegram. Artikel akan direview admin sebelum dipublikasikan.</p>
  </div>

  <?php if ($success): ?>
    <div class="alert alert-success" style="text-align:center;padding:32px">
      <div style="font-size:48px;margin-bottom:12px">🎉</div>
      <h3 style="margin:0 0 8px">Artikel Terkirim!</h3>
      <p style="color:var(--text-dim);margin:0 0 20px">Artikel kamu sedang direview oleh admin. Terima kasih sudah berkontribusi!</p>
      <a href="articles.php" class="btn btn-primary">Lihat Semua Artikel</a>
      <a href="submit-article.php" class="btn btn-outline" style="margin-left:8px">Tulis Lagi</a>
    </div>
  <?php else: ?>
    <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
    <form method="post" class="submit-article-form">
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
              <option value="<?= $cat ?>" <?= (($_POST['category'] ?? '') == $cat) ? 'selected' : '' ?>><?= $cat ?></option>
            <?php endforeach; ?>
          </select>
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

<?php include __DIR__ . '/includes/footer.php'; ?>
