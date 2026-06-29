<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$id]);
$a = $stmt->fetch();
if (!$a) { echo 'Artikel tidak ditemukan.'; exit; }
$pageTitle = 'Preview: ' . $a['title'];
include __DIR__ . '/includes/admin_header.php';
?>
<div class="article-detail-wrap" style="margin-top:24px">
  <div style="background:var(--yellow);color:#000;padding:8px 16px;border-radius:8px;margin-bottom:20px;font-size:13px">
    ⚠️ Preview — Status: <strong><?= $a['status'] ?></strong> —
    <a href="article-action.php?id=<?= $a['id'] ?>&action=approve" style="color:#000;font-weight:bold">Approve</a> |
    <a href="articles.php" style="color:#000">Kembali</a>
  </div>
  <div class="article-card-cat"><?= clean($a['category']) ?></div>
  <h1 class="article-detail-title"><?= clean($a['title']) ?></h1>
  <div class="article-detail-meta">
    <span>✍️ <?= clean($a['author_name']) ?></span>
    <span>🗓️ <?= date('d F Y', strtotime($a['created_at'])) ?></span>
  </div>
  <div class="article-detail-body" style="margin-top:24px">
    <?= nl2br(clean($a['content'])) ?>
  </div>
</div>
<?php include __DIR__ . '/includes/admin_footer.php'; ?>
