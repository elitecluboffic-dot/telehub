<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Beranda';

// Ambil beberapa card approved terbaru untuk ditampilkan di homepage
$stmt = $pdo->query("SELECT * FROM card_submissions WHERE status='approved' ORDER BY created_at DESC LIMIT 6");
$cards = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="hero">
  <h1>Buat & Temukan <span>Custom Card</span><br>Channel, Grup & User Telegram</h1>
  <p>TeleCard adalah direktori card custom untuk komunitas Telegram kamu. Daftar, isi form, dan biarkan card kamu tampil di galeri publik</p>
  <div class="hero-actions">
    <a href="register.php" class="btn btn-primary">Mulai Sekarang</a>
    <a href="cards.php" class="btn btn-outline">Lihat Semua Card</a>
  </div>
  <form class="search-bar" action="cards.php" method="get">
    <input type="text" name="q" placeholder="Cari channel, grup, atau user Telegram...">
    <button type="submit">Cari</button>
  </form>
</div>

<h2 style="margin-top:50px;">Card Terbaru</h2>
<div class="card-grid">
  <?php if (empty($cards)): ?>
    <p style="color:var(--text-dim)">Belum ada card yang disetujui. Jadilah yang pertama!</p>
  <?php endif; ?>
  <?php foreach ($cards as $c): ?>
    <div class="tcard">
      <div class="tcard-top">
        <?php if ($c['image_path']): ?>
          <img class="tcard-avatar" src="<?= UPLOAD_URL . clean($c['image_path']) ?>">
        <?php else: ?>
          <div class="tcard-avatar"></div>
        <?php endif; ?>
        <div>
          <div class="tcard-title">
            <?= clean($c['name']) ?>
            <span class="type-badge" style="background:<?= badgeColorByType($c['type']) ?>"><?= clean($c['type']) ?></span>
          </div>
          <div class="tcard-meta"><?= $c['member_count'] ? clean($c['member_count']) . ' member' : '' ?></div>
        </div>
      </div>
      <?php if ($c['tags']): ?>
        <div class="tcard-tags">
          <?php foreach (array_slice(array_filter(array_map('trim', explode(',', $c['tags']))), 0, 4) as $t): ?>
            <span class="tag-pill"><?= clean($t) ?></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <div class="tcard-desc"><?= clean(mb_strimwidth($c['description'] ?? '', 0, 110, '...')) ?></div>
      <div class="tcard-footer">
        <span></span>
        <a href="<?= clean($c['telegram_link']) ?>" target="_blank" class="btn btn-primary btn-sm">Join &rarr;</a>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>