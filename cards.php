<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Jelajahi Card';

$q = clean($_GET['q'] ?? '');
$type = $_GET['type'] ?? '';

$sql = "SELECT * FROM card_submissions WHERE status='approved'";
$params = [];

if ($q) {
    $sql .= " AND (name LIKE ? OR description LIKE ? OR tags LIKE ?)";
    $like = "%$q%";
    $params = [$like, $like, $like];
}
if (in_array($type, ['channel','group','user'])) {
    $sql .= " AND type = ?";
    $params[] = $type;
}
$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cards = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="hero" style="padding:40px 0 10px">
  <h1 style="font-size:30px">Jelajahi Semua <span>Card</span></h1>
  <form class="search-bar" method="get">
    <input type="text" name="q" value="<?= clean($q) ?>" placeholder="Cari channel, grup, atau user...">
    <button type="submit">Cari</button>
  </form>
</div>

<div class="filters">
  <a href="cards.php?q=<?= urlencode($q) ?>" class="filter-chip <?= $type=='' ? 'active' : '' ?>">Semua</a>
  <a href="cards.php?q=<?= urlencode($q) ?>&type=channel" class="filter-chip <?= $type=='channel' ? 'active' : '' ?>">Channel</a>
  <a href="cards.php?q=<?= urlencode($q) ?>&type=group" class="filter-chip <?= $type=='group' ? 'active' : '' ?>">Group</a>
  <a href="cards.php?q=<?= urlencode($q) ?>&type=user" class="filter-chip <?= $type=='user' ? 'active' : '' ?>">User</a>
</div>

<div class="card-grid">
  <?php if (empty($cards)): ?>
    <p style="color:var(--text-dim)">Tidak ada card yang cocok.</p>
  <?php endif; ?>
  <?php foreach ($cards as $c): ?>
    <div class="tcard" style="border-top:3px solid <?= clean($c['theme_color']) ?>">
      <div class="tcard-top">
        <?php if ($c['image_path']): ?>
          <img class="tcard-avatar" src="<?= UPLOAD_URL . clean($c['image_path']) ?>">
        <?php else: ?>
          <div class="tcard-avatar" style="background:<?= clean($c['theme_color']) ?>"></div>
        <?php endif; ?>
        <div>
          <div class="tcard-title">
            <?= clean($c['name']) ?>
            <span class="type-badge" style="background:<?= badgeColorByType($c['type']) ?>"><?= clean($c['type']) ?></span>
          </div>
          <div class="tcard-meta">
            <?= $c['category'] ? clean($c['category']) . ' &middot; ' : '' ?>
            <?= $c['member_count'] ? clean($c['member_count']) . ' member' : '' ?>
          </div>
        </div>
      </div>
      <?php if ($c['tags']): ?>
        <div class="tcard-tags">
          <?php foreach (array_slice(array_filter(array_map('trim', explode(',', $c['tags']))), 0, 5) as $t): ?>
            <span class="tag-pill"><?= clean($t) ?></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <div class="tcard-desc"><?= clean(mb_strimwidth($c['description'] ?? '', 0, 130, '...')) ?></div>
      <div class="tcard-footer">
        <span></span>
        <a href="<?= clean($c['telegram_link']) ?>" target="_blank" class="btn btn-primary btn-sm">Join &rarr;</a>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>