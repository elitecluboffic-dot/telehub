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

// Tanggal efektif yang dipakai untuk tampilan & pengurutan: kapan artikel benar-benar
// tayang ke publik (approved_at). Fallback ke created_at untuk artikel lama yang
// di-approve sebelum kolom approved_at ada (masih NULL).
$effectiveDate = $a['approved_at'] ?? $a['created_at'];

// ===== ARTIKEL SEBELUMNYA & SESUDAHNYA (berdasarkan approved_at, kronologis) =====
// "Prev" = artikel yang lebih lama (tanggal tayang lebih kecil), diambil yang paling dekat
// "Next" = artikel yang lebih baru (tanggal tayang lebih besar), diambil yang paling dekat
$prevStmt = $pdo->prepare(
    "SELECT slug, title, image_path FROM articles
     WHERE status = 'approved'
       AND (COALESCE(approved_at, created_at) < ? OR (COALESCE(approved_at, created_at) = ? AND id < ?))
     ORDER BY COALESCE(approved_at, created_at) DESC, id DESC
     LIMIT 1"
);
$prevStmt->execute([$effectiveDate, $effectiveDate, $a['id']]);
$prevArticle = $prevStmt->fetch();

$nextStmt = $pdo->prepare(
    "SELECT slug, title, image_path FROM articles
     WHERE status = 'approved'
       AND (COALESCE(approved_at, created_at) > ? OR (COALESCE(approved_at, created_at) = ? AND id > ?))
     ORDER BY COALESCE(approved_at, created_at) ASC, id ASC
     LIMIT 1"
);
$nextStmt->execute([$effectiveDate, $effectiveDate, $a['id']]);
$nextArticle = $nextStmt->fetch();

$pageTitle = $a['title'];
$metaDesc = clean(mb_strimwidth(strip_tags($a['content']), 0, 160, '...'));
$metaKeywords = $a['category'] . ', artikel telegram, telecard';
include __DIR__ . '/includes/header.php';
?>

<style>
  .article-nav {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin-top: 32px;
  }

  .article-nav-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
    border-radius: 14px;
    border: 1.5px solid var(--border, rgba(255,255,255,0.1));
    background: rgba(255,255,255,0.02);
    text-decoration: none;
    transition: all 0.15s;
    overflow: hidden;
  }

  .article-nav-link:hover {
    border-color: var(--tg-blue, #2aabee);
    background: rgba(42,171,238,0.06);
    transform: translateY(-2px);
  }

  .article-nav-link.next {
    flex-direction: row-reverse;
    text-align: right;
  }

  .article-nav-thumb {
    width: 52px;
    height: 52px;
    border-radius: 10px;
    object-fit: cover;
    flex-shrink: 0;
    background: rgba(255,255,255,0.05);
  }

  .article-nav-thumb-placeholder {
    width: 52px;
    height: 52px;
    border-radius: 10px;
    flex-shrink: 0;
    background: rgba(255,255,255,0.05);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
  }

  .article-nav-text {
    min-width: 0;
    flex: 1;
  }

  .article-nav-label {
    font-size: 11.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-dim);
    margin-bottom: 4px;
    display: block;
  }

  .article-nav-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .article-nav-placeholder {
    padding: 14px;
    border-radius: 14px;
    border: 1.5px dashed var(--border, rgba(255,255,255,0.1));
    color: var(--text-dim);
    font-size: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
  }

  @media (max-width: 560px) {
    .article-nav {
      grid-template-columns: 1fr;
    }
    .article-nav-link.next {
      flex-direction: row;
      text-align: left;
    }
  }
</style>

<div class="article-detail-wrap">
  <?php if (!empty($a['image_path'])): ?>
    <img class="article-detail-cover" src="<?= UPLOAD_URL . clean($a['image_path']) ?>" alt="<?= clean($a['title']) ?>">
  <?php endif; ?>
  <div class="article-detail-header">
    <h1 class="article-detail-title"><?= clean($a['title']) ?></h1>
    <div class="article-detail-meta">
      <span>✍️ <?= clean($a['author_name']) ?></span>
      <span>🗓️ <?= date('d M Y', strtotime($effectiveDate)) ?></span>
      <span>👁️ <?= number_format($a['views'] ?? 0) ?> views</span>
      <div class="article-card-cat"><?= clean($a['category']) ?></div>
    </div>
  </div>
  <div class="article-detail-body">
    <?= nl2br(clean($a['content'])) ?>
  </div>

  <div class="article-nav">
    <?php if ($prevArticle): ?>
      <a href="article.php?slug=<?= urlencode($prevArticle['slug']) ?>" class="article-nav-link prev">
        <?php if (!empty($prevArticle['image_path'])): ?>
          <img class="article-nav-thumb" src="<?= UPLOAD_URL . clean($prevArticle['image_path']) ?>" alt="">
        <?php else: ?>
          <div class="article-nav-thumb-placeholder">📝</div>
        <?php endif; ?>
        <div class="article-nav-text">
          <span class="article-nav-label">← Sebelumnya</span>
          <div class="article-nav-title"><?= clean($prevArticle['title']) ?></div>
        </div>
      </a>
    <?php else: ?>
      <div class="article-nav-placeholder">Ini artikel paling lama</div>
    <?php endif; ?>

    <?php if ($nextArticle): ?>
      <a href="article.php?slug=<?= urlencode($nextArticle['slug']) ?>" class="article-nav-link next">
        <?php if (!empty($nextArticle['image_path'])): ?>
          <img class="article-nav-thumb" src="<?= UPLOAD_URL . clean($nextArticle['image_path']) ?>" alt="">
        <?php else: ?>
          <div class="article-nav-thumb-placeholder">📝</div>
        <?php endif; ?>
        <div class="article-nav-text">
          <span class="article-nav-label">Selanjutnya →</span>
          <div class="article-nav-title"><?= clean($nextArticle['title']) ?></div>
        </div>
      </a>
    <?php else: ?>
      <div class="article-nav-placeholder">Ini artikel paling baru</div>
    <?php endif; ?>
  </div>

  <div style="margin-top:24px;padding-top:24px;border-top:1px solid var(--border);display:flex;gap:10px;flex-wrap:wrap">
    <a href="articles.php" class="btn btn-outline btn-sm">← Kembali ke Artikel</a>
    <a href="submit-article.php" class="btn btn-primary btn-sm">✍️ Tulis Artikel</a>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
