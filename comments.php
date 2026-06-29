<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Komentar & Rating';
$metaDesc = 'Berikan rating dan komentar kamu untuk TeleCard.';

// Handle POST (kirim komentar baru)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = clean($_POST['name'] ?? '');
    $message = clean($_POST['message'] ?? '');
    $rating  = (int)($_POST['rating'] ?? 5);
    if ($name && $message && $rating >= 1 && $rating <= 5) {
        $pdo->prepare("INSERT INTO comments (name, message, rating) VALUES (?,?,?)")
            ->execute([$name, $message, $rating]);
        header('Location: comments.php?success=1');
        exit;
    }
}

// ===== PAGINATION =====
$perPage = 10; // jumlah komentar per halaman, ubah sesuka hati

$page = (int)($_GET['page'] ?? 1);
if ($page < 1) $page = 1;

$totalComments = (int)$pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();
$totalPages = max(1, (int)ceil($totalComments / $perPage));
if ($page > $totalPages) $page = $totalPages;

$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare("SELECT * FROM comments ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$comments = $stmt->fetchAll();

$avgRating = $pdo->query("SELECT AVG(rating) as avg, COUNT(*) as total FROM comments")->fetch();

include __DIR__ . '/includes/header.php';
?>

<div class="comments-wrap">

  <div class="comments-hero">
    <h1>Komentar & <span>Rating</span></h1>
    <p>Apa kata mereka tentang TeleCard? Berikan pendapatmu!</p>

    <?php if (!empty($avgRating['total'])): ?>
    <div class="rating-summary">
      <div class="rating-big"><?= number_format($avgRating['avg'], 1) ?></div>
      <div>
        <div class="stars-display">
          <?php for ($i = 1; $i <= 5; $i++): ?>
            <span style="color:<?= $i <= round($avgRating['avg']) ? '#f5c518' : 'var(--border)' ?>;font-size:24px">★</span>
          <?php endfor; ?>
        </div>
        <div style="color:var(--text-dim);font-size:13px;margin-top:4px">dari <?= $avgRating['total'] ?> ulasan</div>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <?php if ($_GET['success'] ?? false): ?>
    <div class="alert alert-success">🎉 Komentar kamu berhasil dikirim! Terima kasih.</div>
  <?php endif; ?>

  <div class="comments-layout">

    <!-- Form -->
    <div class="comment-form-wrap">
      <h3 style="margin-top:0">Tulis Komentar</h3>
      <form method="post" class="comment-form">
        <div class="form-group">
          <label>Nama kamu</label>
          <input type="text" name="name" placeholder="Nama kamu..." required maxlength="100">
        </div>
        <div class="form-group">
          <label>Rating</label>
          <div class="star-picker" id="starPicker">
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <span class="star active" data-value="<?= $i ?>">★</span>
            <?php endfor; ?>
          </div>
          <input type="hidden" name="rating" id="ratingInput" value="5">
        </div>
        <div class="form-group">
          <label>Komentar</label>
          <textarea name="message" rows="4" placeholder="Tulis pendapatmu tentang TeleCard..." required maxlength="500"></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-block">🚀 Kirim Komentar</button>
      </form>
    </div>

    <!-- List Komentar (publik, semua bisa lihat) -->
    <div class="comment-list-wrap">
      <div class="comment-list" id="commentList">
        <?php include __DIR__ . '/comments-render.php'; ?>
      </div>

      <?php if ($totalPages > 1): ?>
      <div class="comment-pagination" id="commentPagination">
        <a href="?page=<?= max(1, $page - 1) ?>"
           class="btn btn-outline btn-sm <?= $page <= 1 ? 'disabled' : '' ?>">← Sebelumnya</a>

        <span class="page-info">Halaman <?= $page ?> dari <?= $totalPages ?></span>

        <a href="?page=<?= min($totalPages, $page + 1) ?>"
           class="btn btn-outline btn-sm <?= $page >= $totalPages ? 'disabled' : '' ?>">Berikutnya →</a>
      </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<script>
let currentRating = 5;
const stars = document.querySelectorAll('#starPicker .star');
const currentPage = <?= $page ?>; // halaman yang sedang dibuka, dipakai buat auto-refresh

function setRating(val) {
  currentRating = val;
  document.getElementById('ratingInput').value = val;
  stars.forEach((s, i) => {
    s.classList.toggle('active', i < val);
  });
}

function previewHover(val) {
  stars.forEach((s, i) => s.classList.toggle('hover', i < val));
}

stars.forEach((s) => {
  const val = parseInt(s.dataset.value, 10);

  // Desktop: hover preview + click
  s.addEventListener('mouseenter', () => previewHover(val));
  s.addEventListener('mouseleave', () => previewHover(0));
  s.addEventListener('click', () => setRating(val));

  // Mobile: tangani touchstart langsung, jangan nunggu delay klik bawaan browser
  s.addEventListener('touchstart', (e) => {
    e.preventDefault();
    setRating(val);
  }, { passive: false });
});

// Init rating 5 (bintang sudah active dari render PHP, ini cuma sync state JS)
setRating(5);

// Auto refresh komentar setiap 15 detik, tetap di halaman yang sama
setInterval(() => {
  fetch('comments-data.php?page=' + currentPage)
    .then(r => r.text())
    .then(html => {
      document.getElementById('commentList').innerHTML = html;
    });
}, 15000);
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
