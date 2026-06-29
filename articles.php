<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Artikel';
$metaDesc = 'Baca artikel seputar Telegram, komunitas, dan tips channel dari TeleCard.';
$metaKeywords = 'artikel telegram, tips channel telegram, komunitas telegram';
$category = $_GET['category'] ?? '';
$sql = "SELECT * FROM articles WHERE status='approved'";
$params = [];
if ($category) {
    $sql .= " AND category = ?";
    $params[] = $category;
}
$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();
$categories = $pdo->query("SELECT DISTINCT category FROM articles WHERE status='approved' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
include __DIR__ . '/includes/header.php';
?>
<div class="article-hero">
  <h1>Artikel & <span>Tips</span></h1>
  <p>Kumpulan artikel seputar Telegram, komunitas, dan tips mengelola channel</p>
  <a href="submit-article.php" class="btn btn-primary">✍️ Tulis Artikel</a>
</div>
<?php if (!empty($categories)): ?>
<div class="filters" style="margin-top:24px">
  <a href="articles.php" class="filter-chip <?= $category=='' ? 'active' : '' ?>">Semua</a>
  <?php foreach ($categories as $cat): ?>
    <a href="articles.php?category=<?= urlencode($cat) ?>" class="filter-chip <?= $category==$cat ? 'active' : '' ?>"><?= clean($cat) ?></a>
  <?php endforeach; ?>
</div>
<?php endif; ?>
<div class="article-grid">
  <?php if (empty($articles)): ?>
    <div style="color:var(--text-dim);padding:60px 0;text-align:center;grid-column:1/-1">
      <div style="font-size:48px;margin-bottom:12px">📝</div>
      <p>Belum ada artikel. <a href="submit-article.php" style="color:var(--tg-blue)">Jadilah yang pertama!</a></p>
    </div>
  <?php endif; ?>
  <?php foreach ($articles as $a): ?>
    <a href="article.php?slug=<?= urlencode($a['slug']) ?>" class="article-card-profile">
      <div class="article-card-header">
        <?php if (!empty($a['image_path'])): ?>
          <img class="article-card-image" src="<?= UPLOAD_URL . clean($a['image_path']) ?>" alt="<?= clean($a['title']) ?>">
        <?php else: ?>
          <div class="article-card-image-placeholder">📝</div>
        <?php endif; ?>
      </div>
      <div class="article-card-content">
        <div class="article-card-badge"><?= clean($a['category']) ?></div>
        <h2 class="article-card-title"><?= clean($a['title']) ?></h2>
        <p class="article-card-description"><?= clean(mb_strimwidth(strip_tags($a['content']), 0, 120, '...')) ?></p>
        <div class="article-card-footer">
          <div class="article-card-author">✍️ <?= clean($a['author_name']) ?></div>
          <div class="article-card-date"><?= date('d M Y', strtotime($a['created_at'])) ?></div>
        </div>
        <button class="article-card-btn">Baca Selengkapnya →</button>
      </div>
    </a>
  <?php endforeach; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
