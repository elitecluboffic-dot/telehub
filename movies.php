<?php
session_start();

// ============ KONFIGURASI (isi sesuai punya lo) ============
$BOT_TOKEN         = "8889368072:AAFG3ELfL0FtYkZcc8sI4qJs8iEe3L8x3GM";          // dari @BotFather, buat verifikasi login
$BOT_USERNAME      = "MusicRobotBot";          // username bot, tanpa @
$WORKER_URL        = "https://movie-follow-gate.internetdnsofficial.workers.dev";
$CHANNEL_USERNAME  = "bimnihnge";      // username channel, tanpa @
$MOVIES_FILE       = __DIR__ . "/includes/movies.txt";      // format per baris: Judul|URL
$MOVIES_PER_PAGE   = 10;      // jumlah film per halaman
$SESSION_LIFETIME  = 86400;   // auto-logout setelah sekian detik tanpa aktivitas (24 jam)

// ============ LOGOUT MANUAL ============
// Dipanggil kalau user klik tombol "Logout" di pojok kanan atas.
// Menghancurkan session PHP sepenuhnya, lalu redirect ke halaman bersih
// (tanpa query string) supaya user balik ke state "belum login".
if (isset($_GET['logout']) && $_GET['logout'] === '1') {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header("Location: " . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

// ============ AUTO-LOGOUT: SESSION EXPIRED KARENA INAKTIVITAS ============
// Kalau user udah login tapi terakhir aktif lebih dari $SESSION_LIFETIME detik
// yang lalu, hancurkan session dan lempar ke halaman dengan flag ?expired=1
// biar frontend bisa nampilin notifikasi "sesi kamu berakhir".
if (isset($_SESSION['tg_id'])) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $SESSION_LIFETIME) {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        header("Location: " . strtok($_SERVER['REQUEST_URI'], '?') . "?expired=1");
        exit;
    }
    // Masih dalam batas waktu, update jam aktivitas terakhir
    $_SESSION['last_activity'] = time();
}

// ============ VERIFIKASI LOGIN TELEGRAM ============
// Dipanggil otomatis oleh Telegram Login Widget lewat redirect ke halaman ini
if (isset($_GET['hash'])) {
    $data = $_GET;
    $hash = $data['hash'];
    unset($data['hash']);
    unset($data['page']);    // page bukan bagian dari data yang diverifikasi Telegram
    unset($data['expired']); // expired juga bukan bagian dari data yang diverifikasi Telegram

    $checkArr = [];
    foreach ($data as $key => $value) {
        $checkArr[] = $key . '=' . $value;
    }
    sort($checkArr);
    $checkString = implode("\n", $checkArr);

    $secretKey = hash('sha256', $BOT_TOKEN, true);
    $hmac = hash_hmac('sha256', $checkString, $secretKey);

    if (hash_equals($hmac, $hash) && (time() - (int)$data['auth_date']) < 86400) {
        $_SESSION['tg_id']         = $data['id'];
        $_SESSION['tg_name']       = $data['first_name'] ?? 'User';
        $_SESSION['last_activity'] = time();
        // Redirect biar hash gak nangkring di URL
        header("Location: " . strtok($_SERVER['REQUEST_URI'], '?'));
        exit;
    } else {
        die("Verifikasi login Telegram gagal.");
    }
}

// ============ CEK STATUS FOLLOW KE WORKER ============
$isFollowing = false;
if (isset($_SESSION['tg_id'])) {
    // Kalau user klik tombol "Refresh Status" (?refresh=1), paksa Worker
    // skip cache dan cek ulang langsung ke Telegram API.
    $forceRefresh = isset($_GET['refresh']) && $_GET['refresh'] === '1';

    $checkUrl = $WORKER_URL . "/check-follow?user_id=" . urlencode($_SESSION['tg_id']);
    if ($forceRefresh) {
        $checkUrl .= "&refresh=1";
    }

    $ch = curl_init($checkUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        $result = json_decode($response, true);
        $isFollowing = $result['following'] ?? false;
    }
}

// ============ BACA DAFTAR FILM DARI TXT ============
$movies = [];
if (file_exists($MOVIES_FILE)) {
    $lines = file($MOVIES_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $parts = array_pad(explode('|', $line, 2), 2, '');
        [$title, $url] = $parts;
        if (trim($title) && trim($url)) {
            $movies[] = ['title' => trim($title), 'url' => trim($url)];
        }
    }
}

// ============ PAGINATION ============
$totalMovies = count($movies);
$totalPages  = max(1, (int)ceil($totalMovies / $MOVIES_PER_PAGE));

$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) $currentPage = 1;
if ($currentPage > $totalPages) $currentPage = $totalPages;

$offset = ($currentPage - 1) * $MOVIES_PER_PAGE;
$moviesOnPage = array_slice($movies, $offset, $MOVIES_PER_PAGE);

$basePath = strtok($_SERVER['REQUEST_URI'], '?');
function pageUrl($basePath, $page) {
    return htmlspecialchars($basePath . '?page=' . $page);
}

$sessionExpired = isset($_GET['expired']) && $_GET['expired'] === '1';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Daftar Film</title>
<style>
  :root {
    --bg-1: #0b0d14;
    --bg-2: #121525;
    --card: #161a2c;
    --card-hover: #1c2138;
    --border: #262b45;
    --accent: #4f9dff;
    --accent-2: #7c5cff;
    --text: #eef1fb;
    --text-dim: #9aa1c0;
    --warn-bg: #241c0f;
    --warn-border: #6b4d10;
    --warn-text: #ffcf7a;
    --ok-bg: #10241a;
    --ok-border: #1c5c3b;
    --ok-text: #7ce6ab;
    --danger-bg: #241010;
    --danger-border: #6b1f1f;
    --danger-text: #ff9a9a;
    --expired-bg: #251018;
    --expired-border: #6b1f3f;
    --expired-text: #ff9ac2;
    --radius: 16px;
  }

  * { box-sizing: border-box; }

  html, body {
    margin: 0;
    padding: 0;
    min-height: 100%;
  }

  body {
    font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    color: var(--text);
    background:
      radial-gradient(circle at 15% -10%, rgba(79,157,255,0.18), transparent 45%),
      radial-gradient(circle at 100% 0%, rgba(124,92,255,0.16), transparent 40%),
      var(--bg-1);
    background-attachment: fixed;
    min-height: 100vh;
    padding: 56px 20px 80px;
  }

  .wrap {
    max-width: 780px;
    margin: 0 auto;
  }

  .header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    margin-bottom: 32px;
  }

  .header-left {
    display: flex;
    align-items: center;
    gap: 14px;
    min-width: 0;
  }

  .logo {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--accent), var(--accent-2));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    box-shadow: 0 6px 20px rgba(79,157,255,0.35);
    flex-shrink: 0;
  }

  h1 {
    font-size: 26px;
    font-weight: 700;
    margin: 0;
    letter-spacing: -0.02em;
  }

  .subtitle {
    color: var(--text-dim);
    font-size: 14px;
    margin-top: 2px;
  }

  .logout-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--danger-bg);
    border: 1px solid var(--danger-border);
    color: var(--danger-text);
    padding: 9px 16px;
    border-radius: 10px;
    font-size: 13.5px;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
    flex-shrink: 0;
    transition: background .18s ease, transform .15s ease;
  }

  .logout-btn:hover {
    background: #331515;
    transform: translateY(-1px);
  }

  .notice {
    background: var(--warn-bg);
    border: 1px solid var(--warn-border);
    color: var(--warn-text);
    padding: 20px 22px;
    border-radius: var(--radius);
    margin-bottom: 28px;
    font-size: 14.5px;
    line-height: 1.6;
    display: flex;
    flex-direction: column;
    gap: 14px;
    animation: fadeIn .4s ease;
  }

  .notice.ok {
    background: var(--ok-bg);
    border-color: var(--ok-border);
    color: var(--ok-text);
  }

  .notice.expired {
    background: var(--expired-bg);
    border-color: var(--expired-border);
    color: var(--expired-text);
  }

  .notice-title {
    font-weight: 600;
    font-size: 15.5px;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .notice a.channel-link {
    color: var(--accent);
    font-weight: 600;
    text-decoration: none;
    border-bottom: 1px solid rgba(79,157,255,0.4);
    transition: border-color .2s ease, color .2s ease;
  }

  .notice a.channel-link:hover {
    color: #7fb8ff;
    border-color: #7fb8ff;
  }

  .notice-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
  }

  .refresh-btn {
    align-self: flex-start;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.12);
    color: var(--text);
    padding: 9px 16px;
    border-radius: 10px;
    font-size: 13.5px;
    font-weight: 500;
    cursor: pointer;
    transition: background .2s ease, transform .15s ease;
  }

  .refresh-btn:hover {
    background: rgba(255,255,255,0.12);
    transform: translateY(-1px);
  }

  .tg-login-wrap {
    margin-top: 6px;
  }

  .movie-list {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    box-shadow: 0 20px 50px -25px rgba(0,0,0,0.6);
  }

  .movie {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 22px;
    border-bottom: 1px solid var(--border);
    transition: background .18s ease;
  }

  .movie:last-child {
    border-bottom: none;
  }

  .movie:hover {
    background: var(--card-hover);
  }

  .movie-info {
    display: flex;
    align-items: center;
    gap: 14px;
    min-width: 0;
  }

  .movie-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: linear-gradient(135deg, rgba(79,157,255,0.25), rgba(124,92,255,0.25));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
  }

  .movie-title {
    font-size: 15px;
    font-weight: 500;
    color: var(--text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .btn {
    padding: 9px 18px;
    border-radius: 10px;
    text-decoration: none;
    font-size: 13.5px;
    font-weight: 600;
    letter-spacing: 0.01em;
    white-space: nowrap;
    transition: transform .15s ease, box-shadow .15s ease, opacity .15s ease;
  }

  .btn-active {
    background: linear-gradient(135deg, var(--accent), var(--accent-2));
    color: #fff;
    box-shadow: 0 8px 20px -6px rgba(79,157,255,0.5);
  }

  .btn-active:hover {
    transform: translateY(-1px);
    box-shadow: 0 10px 24px -6px rgba(79,157,255,0.65);
  }

  .btn-disabled {
    background: rgba(255,255,255,0.05);
    color: var(--text-dim);
    border: 1px solid var(--border);
    cursor: not-allowed;
  }

  .empty {
    padding: 40px 22px;
    text-align: center;
    color: var(--text-dim);
    font-size: 14.5px;
  }

  /* ===== Pagination ===== */
  .pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-top: 22px;
  }

  .page-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 18px;
    border-radius: 10px;
    background: var(--card);
    border: 1px solid var(--border);
    color: var(--text);
    text-decoration: none;
    font-size: 13.5px;
    font-weight: 600;
    transition: background .18s ease, transform .15s ease, border-color .18s ease;
  }

  .page-btn:hover {
    background: var(--card-hover);
    border-color: rgba(79,157,255,0.4);
    transform: translateY(-1px);
  }

  .page-btn.disabled {
    color: var(--text-dim);
    background: rgba(255,255,255,0.03);
    border-color: var(--border);
    pointer-events: none;
    opacity: 0.5;
  }

  .page-info {
    color: var(--text-dim);
    font-size: 13.5px;
    font-weight: 500;
    text-align: center;
    flex: 1;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(-6px); }
    to { opacity: 1; transform: translateY(0); }
  }

  @media (max-width: 480px) {
    body { padding: 36px 14px 60px; }
    .movie-title { max-width: 160px; }
    .page-btn span.label { display: none; }
    .header { flex-wrap: wrap; }
    .logout-btn span.label { display: none; }
  }
</style>
</head>
<body>
<div class="wrap">

  <div class="header">
    <div class="header-left">
      <div class="logo">🎬</div>
      <div>
        <h1>Daftar Film</h1>
        <div class="subtitle">Koleksi film pilihan, khusus member channel</div>
      </div>
    </div>
    <?php if (isset($_SESSION['tg_id'])): ?>
      <a class="logout-btn" href="<?= htmlspecialchars($basePath) ?>?logout=1" onclick="return confirm('Yakin mau logout?');">
        <span>⎋</span><span class="label">Logout</span>
      </a>
    <?php endif; ?>
  </div>

  <?php if (!isset($_SESSION['tg_id'])): ?>

    <?php if ($sessionExpired): ?>
    <div class="notice expired">
      <div class="notice-title">⏰ Sesi kamu udah berakhir</div>
      <div>Kamu otomatis di-logout karena gak ada aktivitas selama 24 jam. Demi keamanan akun, silakan login ulang pake Telegram di bawah ini.</div>
    </div>
    <?php endif; ?>

    <div class="notice">
      <div class="notice-title">🔐 Login diperlukan</div>
      <div>Login pake Telegram dulu buat lanjut nonton koleksi film di bawah.</div>
      <div class="tg-login-wrap">
        <script async src="https://telegram.org/js/telegram-widget.js?22"
          data-telegram-login="<?= htmlspecialchars($BOT_USERNAME) ?>"
          data-size="large"
          data-radius="10"
          data-auth-url="<?= htmlspecialchars($basePath) ?>"
          data-request-access="write"></script>
      </div>
    </div>

  <?php elseif (!$isFollowing): ?>
    <div class="notice">
      <div class="notice-title">👋 Halo, <?= htmlspecialchars($_SESSION['tg_name']) ?>!</div>
      <div>
        Join channel Telegram
        <a class="channel-link" href="https://t.me/<?= htmlspecialchars($CHANNEL_USERNAME) ?>" target="_blank" rel="noopener">
          @<?= htmlspecialchars($CHANNEL_USERNAME) ?>
        </a>
        dulu buat bisa nonton, terus refresh halaman ini.
      </div>
      <div class="notice-actions">
        <button class="refresh-btn" onclick="location.href = '<?= htmlspecialchars($basePath) ?>?page=<?= $currentPage ?>&refresh=1'">↻ Refresh Status</button>
      </div>
    </div>

  <?php else: ?>
    <div class="notice ok">
      <div class="notice-title">✅ Akses aktif</div>
      <div>Makasih udah follow, <?= htmlspecialchars($_SESSION['tg_name']) ?>! Selamat nonton.</div>
    </div>
  <?php endif; ?>

  <div class="movie-list">
    <?php if (empty($moviesOnPage)): ?>
      <div class="empty">Belum ada film yang tersedia.</div>
    <?php else: ?>
      <?php foreach ($moviesOnPage as $movie): ?>
        <div class="movie">
          <div class="movie-info">
            <div class="movie-icon">🎞️</div>
            <span class="movie-title"><?= htmlspecialchars($movie['title']) ?></span>
          </div>
          <?php if ($isFollowing): ?>
            <a class="btn btn-active" href="<?= htmlspecialchars($movie['url']) ?>" target="_blank" rel="noopener">Tonton</a>
          <?php else: ?>
            <span class="btn btn-disabled">Tonton</span>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php if ($currentPage > 1): ?>
        <a class="page-btn" href="<?= pageUrl($basePath, $currentPage - 1) ?>">
          <span>←</span><span class="label">Prev</span>
        </a>
      <?php else: ?>
        <span class="page-btn disabled"><span>←</span><span class="label">Prev</span></span>
      <?php endif; ?>

      <span class="page-info">Halaman <?= $currentPage ?> dari <?= $totalPages ?></span>

      <?php if ($currentPage < $totalPages): ?>
        <a class="page-btn" href="<?= pageUrl($basePath, $currentPage + 1) ?>">
          <span class="label">Next</span><span>→</span>
        </a>
      <?php else: ?>
        <span class="page-btn disabled"><span class="label">Next</span><span>→</span></span>
      <?php endif; ?>
    </div>
  <?php endif; ?>

</div>
</body>
</html>
