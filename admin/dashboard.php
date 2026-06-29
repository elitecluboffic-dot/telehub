<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$pageTitle = 'Dashboard Admin';

$statusFilter = $_GET['status'] ?? '';
$sql = "SELECT cs.*, u.username, u.email FROM card_submissions cs JOIN users u ON cs.user_id = u.id";
$params = [];
if (in_array($statusFilter, ['pending','approved','rejected'])) {
    $sql .= " WHERE cs.status = ?";
    $params[] = $statusFilter;
}
$sql .= " ORDER BY cs.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$submissions = $stmt->fetchAll();

$counts = $pdo->query("SELECT status, COUNT(*) c FROM card_submissions GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
$totalUsers = $pdo->query("SELECT COUNT(*) c FROM users")->fetch()['c'];

include __DIR__ . '/includes/admin_header.php';
?>

<?php if ($s = flash('success')): ?><div class="alert alert-success" style="margin-top:24px"><?= clean($s) ?></div><?php endif; ?>

<div class="dash-wrap" style="grid-template-columns: repeat(4,1fr); margin-top:24px;">
  <div class="dash-card"><h3 style="margin:0 0 6px"><?= $totalUsers ?></h3><span style="color:var(--text-dim)">Total User</span></div>
  <div class="dash-card"><h3 style="margin:0 0 6px;color:var(--yellow)"><?= $counts['pending'] ?? 0 ?></h3><span style="color:var(--text-dim)">Pending</span></div>
  <div class="dash-card"><h3 style="margin:0 0 6px;color:var(--green)"><?= $counts['approved'] ?? 0 ?></h3><span style="color:var(--text-dim)">Approved</span></div>
  <div class="dash-card"><h3 style="margin:0 0 6px;color:var(--red)"><?= $counts['rejected'] ?? 0 ?></h3><span style="color:var(--text-dim)">Rejected</span></div>
</div>

<div class="filters">
  <a href="dashboard.php" class="filter-chip <?= $statusFilter=='' ? 'active' : '' ?>">Semua</a>
  <a href="dashboard.php?status=pending" class="filter-chip <?= $statusFilter=='pending' ? 'active' : '' ?>">Pending</a>
  <a href="dashboard.php?status=approved" class="filter-chip <?= $statusFilter=='approved' ? 'active' : '' ?>">Approved</a>
  <a href="dashboard.php?status=rejected" class="filter-chip <?= $statusFilter=='rejected' ? 'active' : '' ?>">Rejected</a>
</div>

<div class="dash-card">
  <h3>Daftar Submission Card</h3>
  <table class="simple">
    <tr>
      <th>Card</th><th>Tipe</th><th>Pengirim</th><th>Status</th><th>Tanggal</th><th>Aksi</th>
    </tr>
    <?php foreach ($submissions as $s): ?>
      <tr>
        <td style="display:flex;align-items:center;gap:10px">
          <?php if ($s['image_path']): ?>
            <img src="<?= UPLOAD_URL . clean($s['image_path']) ?>" style="width:36px;height:36px;border-radius:8px;object-fit:cover">
          <?php endif; ?>
          <div>
            <strong><?= clean($s['name']) ?></strong><br>
            <a href="<?= clean($s['telegram_link']) ?>" target="_blank" style="font-size:12px;color:var(--tg-blue)"><?= clean($s['telegram_link']) ?></a>
          </div>
        </td>
        <td><?= clean($s['type']) ?></td>
        <td><?= clean($s['username']) ?><br><span style="color:var(--text-dim);font-size:12px"><?= clean($s['email']) ?></span></td>
        <td><span class="status-pill status-<?= $s['status'] ?>"><?= clean($s['status']) ?></span></td>
        <td><?= date('d M Y', strtotime($s['created_at'])) ?></td>
        <td style="white-space:nowrap">
          <?php if ($s['status'] !== 'approved'): ?>
            <a href="submission.php?id=<?= $s['id'] ?>&action=approve" class="btn btn-success btn-sm">Approve</a>
          <?php endif; ?>
          <?php if ($s['status'] !== 'rejected'): ?>
            <a href="submission.php?id=<?= $s['id'] ?>&action=reject" class="btn btn-outline btn-sm">Reject</a>
          <?php endif; ?>
          <a href="submission.php?id=<?= $s['id'] ?>&action=delete" class="btn btn-danger btn-sm" onclick="return confirm('Hapus card ini?')">Hapus</a>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (empty($submissions)): ?>
      <tr><td colspan="6" style="color:var(--text-dim)">Tidak ada data.</td></tr>
    <?php endif; ?>
  </table>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
