<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/ipguard/IPGuard.php';
(new IPGuard())->protect();
$pageTitle = 'Komentar & Rating';
$metaDesc = 'Berikan rating dan komentar kamu untuk TeleCard.';

// ── Deteksi apakah ini request AJAX dari form komentar ──
$isAjax = (
    isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
);

// Handle POST (kirim komentar baru)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = clean($_POST['name'] ?? '');
    $message = clean($_POST['message'] ?? '');
    $rating  = (int)($_POST['rating'] ?? 5);

    $postError = '';
    if (!$name || !$message) {
        $postError = 'Nama dan komentar wajib diisi.';
    } elseif ($rating < 1 || $rating > 5) {
        $postError = 'Rating tidak valid.';
    }

    if (!$postError) {
        $pdo->prepare("INSERT INTO comments (name, message, rating) VALUES (?,?,?)")
            ->execute([$name, $message, $rating]);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }

        header('Location: comments.php?success=1');
        exit;
    }

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $postError]);
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

<style>
  /* ===== Loading state form komentar (real, nempel ke fetch) ===== */
  .comment-form {
    position: relative;
  }

  .comment-form-overlay {
    position: absolute;
    inset: 0;
    background: rgba(10,10,20,0.7);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    border-radius: 14px;
    display: none;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    z-index: 5;
  }

  .comment-form-overlay.show { display: flex; }

  .comment-spinner {
    width: 34px; height: 34px;
    border-radius: 50%;
    border: 3px solid rgba(255,255,255,0.15);
    border-top-color: var(--tg-blue, #2AABEE);
    animation: comment-spin 0.7s linear infinite;
  }

  @keyframes comment-spin { to { transform: rotate(360deg); } }

  .comment-form-overlay span {
    font-size: 13px;
    color: var(--text);
    font-weight: 600;
  }

  .comment-form-error {
    display: none;
    background: rgba(239,68,68,0.08);
    border: 1px solid rgba(239,68,68,0.25);
    color: #fca5a5;
    padding: 10px 14px;
    border-radius: 10px;
    font-size: 13px;
    margin-bottom: 14px;
  }

  .comment-form-error.show { display: block; }

  /* ===== Indikator loading buat auto-refresh list komentar ===== */
  .comment-list-wrap { position: relative; }

  .comment-refresh-indicator {
    display: none;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: var(--text-dim);
    margin-bottom: 10px;
  }

  .comment-refresh-indicator.show { display: flex; }

  .comment-refresh-dot {
    width: 7px; height: 7px;
    border-radius: 50%;
    background: var(--tg-blue, #2AABEE);
    animation: comment-pulse 0.9s ease-in-out infinite;
  }

  @keyframes comment-pulse {
    0%, 100% { opacity: 0.3; transform: scale(0.85); }
    50%      { opacity: 1;   transform: scale(1); }
  }

  .btn-block[disabled] {
    opacity: 0.7;
    cursor: not-allowed;
  }
</style>

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

  <div id="commentSuccessBox">
    <?php if ($_GET['success'] ?? false): ?>
      <div class="alert alert-success">🎉 Komentar kamu berhasil dikirim! Terima kasih.</div>
    <?php endif; ?>
  </div>

  <div class="comments-layout">

    <!-- Form -->
    <div class="comment-form-wrap">
      <h3 style="margin-top:0">Tulis Komentar</h3>

      <div class="comment-form-error" id="commentFormError"></div>

      <form method="post" class="comment-form" id="commentForm">

        <!-- Overlay loading real, muncul selama fetch POST berjalan -->
        <div class="comment-form-overlay" id="commentFormOverlay">
          <div class="comment-spinner"></div>
          <span>Mengirim komentar...</span>
        </div>

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
        <button type="submit" class="btn btn-primary btn-block" id="commentSubmitBtn">
          <span id="commentSubmitText">🚀 Kirim Komentar</span>
        </button>
      </form>
    </div>

    <!-- List Komentar (publik, semua bisa lihat) -->
    <div class="comment-list-wrap">
      <div class="comment-refresh-indicator" id="commentRefreshIndicator">
        <span class="comment-refresh-dot"></span>
        <span>Memperbarui komentar...</span>
      </div>
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

// ===== Submit komentar via AJAX, loading overlay real nempel ke fetch =====
const commentForm     = document.getElementById('commentForm');
const commentOverlay  = document.getElementById('commentFormOverlay');
const commentErrorBox = document.getElementById('commentFormError');
const commentSubmitBtn  = document.getElementById('commentSubmitBtn');
const commentSubmitText = document.getElementById('commentSubmitText');
const commentSuccessBox = document.getElementById('commentSuccessBox');

commentForm.addEventListener('submit', function (e) {
  e.preventDefault();

  commentErrorBox.classList.remove('show');
  commentErrorBox.textContent = '';
  commentOverlay.classList.add('show');
  commentSubmitBtn.setAttribute('disabled', 'disabled');

  const formData = new FormData(commentForm);

  fetch('comments.php', {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    body: formData
  })
    .then(r => r.json())
    .then(res => {
      commentOverlay.classList.remove('show');
      commentSubmitBtn.removeAttribute('disabled');

      if (res.success) {
        commentSuccessBox.innerHTML = '<div class="alert alert-success">🎉 Komentar kamu berhasil dikirim! Terima kasih.</div>';
        commentForm.reset();
        setRating(5);
        refreshComments(); // langsung muat ulang daftar komentar biar komentar baru kelihatan
        commentSuccessBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
      } else {
        commentErrorBox.textContent = res.error || 'Gagal mengirim komentar. Silakan coba lagi.';
        commentErrorBox.classList.add('show');
      }
    })
    .catch(() => {
      commentOverlay.classList.remove('show');
      commentSubmitBtn.removeAttribute('disabled');
      commentErrorBox.textContent = 'Koneksi terputus. Periksa jaringan kamu dan coba lagi.';
      commentErrorBox.classList.add('show');
    });
});

// ===== Auto refresh komentar, dengan indikator loading real =====
const refreshIndicator = document.getElementById('commentRefreshIndicator');

function refreshComments() {
  refreshIndicator.classList.add('show');

  fetch('comments-data.php?page=' + currentPage)
    .then(r => r.text())
    .then(html => {
      document.getElementById('commentList').innerHTML = html;
    })
    .catch(() => {
      // Diamkan saja kalau gagal, jangan ganggu tampilan yang sudah ada
    })
    .finally(() => {
      refreshIndicator.classList.remove('show');
    });
}

// Auto refresh komentar setiap 15 detik, tetap di halaman yang sama
setInterval(refreshComments, 15000);
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
