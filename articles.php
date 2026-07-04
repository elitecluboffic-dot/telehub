<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Artikel';
$metaDesc = 'Artikel seputar Telegram dan tips channel, ditulis oleh para member TeleCard. Siapa pun bisa kontribusi artikel.';
$metaKeywords = 'artikel telegram, tips channel telegram, komunitas telegram';
$category = $_GET['category'] ?? '';

// ============================
// PAGINATION
// ============================
$perPage = 9; // jumlah artikel per halaman, bisa disesuaikan
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $perPage;

// Hitung total artikel (buat total halaman)
$countSql = "SELECT COUNT(*) FROM articles WHERE status='approved'";
$countParams = [];
if ($category) {
    $countSql .= " AND category = ?";
    $countParams[] = $category;
}
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($countParams);
$totalArticles = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalArticles / $perPage));

// Kalau user akses halaman yang lebih besar dari total halaman, clamp ke halaman terakhir
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

$sql = "SELECT * FROM articles WHERE status='approved'";
$params = [];
if ($category) {
    $sql .= " AND category = ?";
    $params[] = $category;
}
$sql .= " ORDER BY created_at DESC LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();

$categories = $pdo->query("SELECT DISTINCT category FROM articles WHERE status='approved' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

// Helper buat bikin URL pagination sambil pertahanin filter kategori
function buildPageUrl($pageNum, $category) {
    $params = ['page' => $pageNum];
    if ($category) $params['category'] = $category;
    return 'articles.php?' . http_build_query($params);
}

include __DIR__ . '/includes/header.php';
?>
<div class="article-hero">
  <h1>News <span>TeleHub</span></h1>
  <p>Artikel seputar Telegram dan tips mengelola channel, ditulis langsung oleh komunitas TeleCard. Punya pengalaman atau tips? Yuk, tulis dan kirim artikelmu!</p>
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
        <div class="article-card-views" style="color:var(--text-dim);font-size:12px;margin-top:4px">👁️ <?= number_format($a['views'] ?? 0) ?> views</div>
        <button class="article-card-btn">Baca Selengkapnya →</button>
      </div>
    </a>
  <?php endforeach; ?>
</div>

<?php if ($totalPages > 1): ?>
<style>
  .article-pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    flex-wrap: wrap;
    margin: 40px 0 20px;
  }
  .article-pagination a,
  .article-pagination span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 0 12px;
    border-radius: 10px;
    border: 1.5px solid var(--border, rgba(255,255,255,0.1));
    background: rgba(255,255,255,0.02);
    color: var(--text-dim);
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.15s;
  }
  .article-pagination a:hover {
    border-color: var(--tg-blue);
    color: var(--text);
    background: rgba(42,171,238,0.08);
  }
  .article-pagination .active {
    background: var(--tg-blue);
    border-color: var(--tg-blue);
    color: #fff;
  }
  .article-pagination .disabled {
    opacity: 0.35;
    pointer-events: none;
  }
  .article-pagination .dots {
    border: none;
    background: none;
    color: var(--text-dim);
  }
</style>
<div class="article-pagination">

  <?php if ($page > 1): ?>
    <a href="<?= buildPageUrl($page - 1, $category) ?>">&laquo; Prev</a>
  <?php else: ?>
    <span class="disabled">&laquo; Prev</span>
  <?php endif; ?>

  <?php
    // Logika nomor halaman: tampilkan halaman pertama, terakhir, dan sekitar halaman aktif
    $window = 2; // berapa banyak nomor di kiri/kanan halaman aktif
    $lastPrinted = 0;
    for ($i = 1; $i <= $totalPages; $i++):
        $showThis = ($i === 1) || ($i === $totalPages) || ($i >= $page - $window && $i <= $page + $window);
        if ($showThis):
            if ($lastPrinted && $i - $lastPrinted > 1):
                echo '<span class="dots">...</span>';
            endif;
  ?>
      <?php if ($i === $page): ?>
        <span class="active"><?= $i ?></span>
      <?php else: ?>
        <a href="<?= buildPageUrl($i, $category) ?>"><?= $i ?></a>
      <?php endif; ?>
  <?php
            $lastPrinted = $i;
        endif;
    endfor;
  ?>

  <?php if ($page < $totalPages): ?>
    <a href="<?= buildPageUrl($page + 1, $category) ?>">Next &raquo;</a>
  <?php else: ?>
    <span class="disabled">Next &raquo;</span>
  <?php endif; ?>

</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
