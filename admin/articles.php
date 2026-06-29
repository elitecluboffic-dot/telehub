<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$pageTitle = 'Kelola Artikel';

$statusFilter = $_GET['status'] ?? '';
$sql = "SELECT * FROM articles";
$params = [];
if (in_array($statusFilter, ['pending','approved','rejected'])) {
    $sql .= " WHERE status = ?";
    $params[] = $statusFilter;
}
$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();

$counts = $pdo->query("SELECT status, COUNT(*) c FROM articles GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);

include __DIR__ . '/includes/admin_header.php';
?>

<?php if ($s = flash('success')): ?><div class="alert alert-success" style="margin-top:24px"><?= clean($s) ?></div><?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-top:24px">
  <h2 style="margin:0">Kelola Artikel</h2>
</div>

<div class="filters" style="margin-top:16px">
  <a href="articles.php" class="filter-chip <?= $statusFilter=='' ? 'active' : '' ?>">Semua (<?= array_sum($counts) ?>)</a>
  <a href="articles.php?status=pending" class="filter-chip <?= $statusFilter=='pending' ? 'active' : '' ?>">Pending (<?= $counts['pending'] ?? 0 ?>)</a>
  <a href="articles.php?status=approved" class="filter-chip <?= $statusFilter=='approved' ? 'active' : '' ?>">Approved (<?= $counts['approved'] ?? 0 ?>)</a>
  <a href="articles.php?status=rejected" class="filter-chip <?= $statusFilter=='rejected' ? 'active' : '' ?>">Rejected (<?= $counts['rejected'] ?? 0 ?>)</a>
</div>

<div class="dash-card" style="margin-top:16px">
  <div class="table-responsive">
    <table class="simple">
      <tr>
        <th>Judul</th><th>Penulis</th><th>Kategori</th><th>Tanggal</th><th>Status</th><th>Aksi</th>
      </tr>
      <?php foreach ($articles as $a): ?>
        <tr>
          <td style="min-width:180px">
            <strong><?= clean($a['title']) ?></strong><br>
            <small style="color:var(--text-dim)"><?= clean(mb_strimwidth(strip_tags($a['content']), 0, 80, '...')) ?></small>
          </td>
          <td style="white-space:nowrap"><?= clean($a['author_name']) ?></td>
          <td><span class="type-badge" style="background:#6c757d;white-space:nowrap"><?= clean($a['category']) ?></span></td>
          <td style="white-space:nowrap"><?= date('d M Y', strtotime($a['created_at'])) ?></td>
          <td><span class="status-pill status-<?= $a['status'] ?>"><?= $a['status'] ?></span></td>
          <td style="white-space:nowrap">
            <a href="article-preview.php?id=<?= $a['id'] ?>" target="_blank" class="btn btn-sm btn-outline">👁 Preview</a>
            <?php if ($a['status'] !== 'approved'): ?>
              <a href="article-action.php?id=<?= $a['id'] ?>&action=approve" class="btn btn-success btn-sm">Approve</a>
            <?php endif; ?>
            <?php if ($a['status'] !== 'rejected'): ?>
              <a href="article-action.php?id=<?= $a['id'] ?>&action=reject" class="btn btn-outline btn-sm">Reject</a>
            <?php endif; ?>
            <a href="article-action.php?id=<?= $a['id'] ?>&action=delete" class="btn btn-danger btn-sm" onclick="return confirm('Hapus artikel ini?')">Hapus</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($articles)): ?>
        <tr><td colspan="6" style="color:var(--text-dim);padding:20px">Tidak ada artikel.</td></tr>
      <?php endif; ?>
    </table>
  </div>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
