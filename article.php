<?php
require_once __DIR__ . '/includes/functions.php';
$slug = clean($_GET['slug'] ?? '');
$stmt = $pdo->prepare("SELECT * FROM articles WHERE slug = ? AND status = 'approved'");
$stmt->execute([$slug]);
$a = $stmt->fetch();
if (!$a) {
    header('Location: articles.php');
    exit;
}
$pageTitle = $a['title'];
$metaDesc = clean(mb_strimwidth(strip_tags($a['content']), 0, 160, '...'));
$metaKeywords = $a['category'] . ', artikel telegram, telecard';
include __DIR__ . '/includes/header.php';
?>
<div class="article-detail-wrap">
  <?php if (!empty($a['image_path'])): ?>
    <img class="article-detail-cover" src="<?= UPLOAD_URL . clean($a['image_path']) ?>" alt="<?= clean($a['title']) ?>">
  <?php endif; ?>
  <div class="article-detail-header">
    <h1 class="article-detail-title"><?= clean($a['title']) ?></h1>
    <div class="article-detail-meta">
      <span>✍️ <?= clean($a['author_name']) ?></span>
      <span>🗓️ <?= date('d M Y', strtotime($a['created_at'])) ?></span>
      <div class="article-card-cat"><?= clean($a['category']) ?></div>
    </div>
  </div>
  <div class="article-detail-body">
    <?= nl2br(clean($a['content'])) ?>
  </div>
  <div style="margin-top:40px;padding-top:24px;border-top:1px solid var(--border);display:flex;gap:10px;flex-wrap:wrap">
    <a href="articles.php" class="btn btn-outline btn-sm">← Kembali ke Artikel</a>
    <a href="submit-article.php" class="btn btn-primary btn-sm">✍️ Tulis Artikel</a>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
