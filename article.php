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

// ===== VIEW TRACKING =====
if (isBotVisitor()) {
    $pdo->prepare("UPDATE articles SET bot_views = bot_views + 1 WHERE id = ?")
        ->execute([$a['id']]);
} else {
    $sessionKey = 'viewed_article_' . $a['id'];
    if (empty($_SESSION[$sessionKey]) || (time() - $_SESSION[$sessionKey]) > 1800) {
        $pdo->prepare("UPDATE articles SET views = views + 1 WHERE id = ?")
            ->execute([$a['id']]);
        $_SESSION[$sessionKey] = time();
        $a['views'] = ($a['views'] ?? 0) + 1; // sinkronkan tampilan di request ini
    }
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
      <span>👁️ <?= number_format($a['views'] ?? 0) ?> views</span>
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
