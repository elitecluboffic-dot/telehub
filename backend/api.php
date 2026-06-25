<?php
/**
 * TeleBoard – backend/api.php (FIXED: Admin Auth + Avatar Base64 + Visitor Analytics)
 */

define('DB_HOST',     'sql101.infinityfree.com');
define('DB_NAME',     'if0_42251940_telehub_telegram');
define('DB_USER',     'if0_42251940');
define('DB_PASS',     'Labibganteng11');
define('DB_CHARSET',  'utf8mb4');

define('JWT_SECRET',  'BUSTANUL_LABIB_ALWASI');
define('JWT_EXPIRE',  86400 * 30);

define('OTP_EXPIRE',  600);

define('MAIL_HOST',   'smtp.gmail.com');
define('MAIL_PORT',   587);
define('MAIL_USER',   'instainstagramofficial99@gmail.com');
define('MAIL_PASS',   'yjbfgvptyiaivsov');
define('MAIL_FROM',   'instainstagramofficial99@gmail.com');
define('MAIL_NAME',   'TeleBoard');

define('ADMIN_EMAIL', 'alwasilabib153@gmail.com');
define('ADMIN_PASS_HASH', '$2y$10$OtvZ.HWXlB0whuzNRYbFTu1O.11OV6Fq.1ZR.Fotx6O7dmLlS8wiy');

define('UPLOAD_DIR',  __DIR__ . '/uploads/');
define('UPLOAD_URL',  '/backend/uploads/');
define('MAX_IMG_MB',  2);

header('Content-Type: application/json; charset=utf-8');

// Handle CORS & Origin check
$allowed = 'https://telehub.nfy.fyi';
$origin  = $_SERVER['HTTP_ORIGIN'] ?? '';

// OPTIONS preflight harus selalu diizinkan dulu
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    if ($origin === $allowed) {
        header("Access-Control-Allow-Origin: $allowed");
    }
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    http_response_code(204);
    exit;
}

// Cek origin ATAU referer (untuk request non-browser seperti curl)
$referer     = $_SERVER['HTTP_REFERER'] ?? '';
$validOrigin = $origin === $allowed;
$validRef    = str_starts_with($referer, $allowed);

if (!$validOrigin && !$validRef) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

// Baru set header CORS kalau lolos
header("Access-Control-Allow-Origin: $allowed");
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if (!is_dir(UPLOAD_DIR)) @mkdir(UPLOAD_DIR, 0775, true);

$pdo    = connectDB();
$action = $_GET['action'] ?? '';
$body   = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    switch ($action) {
        case 'check_username':        checkUsername($pdo); break;
        case 'send_otp':              sendOtp($pdo, $body); break;
        case 'register':              register($pdo, $body); break;
        case 'login':                 login($pdo, $body); break;
        case 'admin_login':           adminLogin($pdo, $body); break;
        case 'forgot_password':       forgotPassword($pdo, $body); break;
        case 'verify_reset_otp':      verifyResetOtp($pdo, $body); break;
        case 'reset_password':        resetPassword($pdo, $body); break;

        case 'update_profile':        requireAuth($pdo); updateProfile($pdo, $body); break;
        case 'upload_avatar':         requireAuth($pdo); uploadAvatar($pdo); break;

        case 'get_server':            getServer($pdo); break;
        case 'related_servers':       relatedServers($pdo); break;
        case 'get_servers':           getServers($pdo); break;
        case 'get_stats':             getStats($pdo); break;
        case 'get_my_servers':        requireAuth($pdo); getMyServers($pdo); break;
        case 'add_server':            requireAuth($pdo); addServer($pdo, $body); break;
        case 'bump_server':           requireAuth($pdo); bumpServer($pdo, $body); break;
        case 'vote_server':           requireAuth($pdo); voteServer($pdo, $body); break;
        case 'report_server':         requireAuth($pdo); reportServer($pdo, $body); break;
        case 'submit_review':         requireAuth($pdo); submitReview($pdo, $body); break;

        case 'search_users':          requireAuth($pdo); searchUsers($pdo); break;
        case 'send_friend_request':   requireAuth($pdo); sendFriendRequest($pdo, $body); break;
        case 'get_friend_requests':   requireAuth($pdo); getFriendRequests($pdo); break;
        case 'accept_friend_request': requireAuth($pdo); acceptFriendRequest($pdo, $body); break;
        case 'decline_friend_request':requireAuth($pdo); declineFriendRequest($pdo, $body); break;
        case 'get_friends':           requireAuth($pdo); getFriends($pdo); break;
        case 'remove_friend':         requireAuth($pdo); removeFriend($pdo, $body); break;

        // ── VISITOR ANALYTICS (dipakai oleh statistik.html) ──
        case 'track_visit':           trackVisit($pdo, $body); break;
        case 'get_visit_stats':       getVisitStats($pdo); break;
        case 'get_visit_trend':       getVisitTrend($pdo); break;
        case 'get_device_stats':      getDeviceStats($pdo); break;
        case 'get_top_pages':         getTopPages($pdo); break;
        case 'get_traffic_sources':   getTrafficSources($pdo); break;
        case 'get_visitor_countries': getVisitorCountries($pdo); break;
        case 'get_visitor_log':       getVisitorLog($pdo); break;

        case 'admin_get_pending_servers': requireAdminAuth($pdo); adminGetPendingServers($pdo); break;
        case 'admin_approve_server':      requireAdminAuth($pdo); adminApproveServer($pdo, $body); break;
        case 'admin_reject_server':       requireAdminAuth($pdo); adminRejectServer($pdo, $body); break;
        case 'admin_get_all_users':       requireAdminAuth($pdo); adminGetAllUsers($pdo); break;
        case 'admin_ban_user':            requireAdminAuth($pdo); adminBanUser($pdo, $body); break;
        case 'admin_unban_user':          requireAdminAuth($pdo); adminUnbanUser($pdo, $body); break;
        case 'admin_get_reports':         requireAdminAuth($pdo); adminGetReports($pdo); break;
        case 'admin_delete_server':       requireAdminAuth($pdo); adminDeleteServer($pdo, $body); break;
        case 'admin_get_dashboard':       requireAdminAuth($pdo); adminGetDashboard($pdo); break;

        default:
            jsonResponse(['success' => false, 'message' => 'Action tidak dikenal.'], 404);
    }
} catch (Throwable $e) {
    jsonResponse(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
}

/* ═══════════════════════════════════════════════════
   DATABASE
═══════════════════════════════════════════════════ */
function connectDB(): PDO {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    initDB($pdo);
    return $pdo;
}

function initDB(PDO $pdo): void {
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS users (
        id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        tg_username  VARCHAR(32)  NOT NULL UNIQUE,
        full_name    VARCHAR(60)  NOT NULL,
        email        VARCHAR(120) NOT NULL UNIQUE,
        password     VARCHAR(255) NOT NULL,
        bio          TEXT,
        avatar       VARCHAR(255),
        tg_link      VARCHAR(255),
        role         ENUM('user','admin') DEFAULT 'user',
        is_banned    TINYINT(1)   DEFAULT 0,
        created_at   DATETIME     DEFAULT CURRENT_TIMESTAMP,
        updated_at   DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS otp_codes (
        id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        email      VARCHAR(120) NOT NULL,
        code       VARCHAR(6)   NOT NULL,
        purpose    ENUM('register','reset') DEFAULT 'register',
        expires_at DATETIME     NOT NULL,
        used       TINYINT(1)   DEFAULT 0,
        created_at DATETIME     DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS pending_registrations (
        id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        tg_username  VARCHAR(32)  NOT NULL,
        full_name    VARCHAR(60)  NOT NULL,
        email        VARCHAR(120) NOT NULL UNIQUE,
        password     VARCHAR(255) NOT NULL,
        created_at   DATETIME     DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS servers (
        id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        owner_id       INT UNSIGNED NOT NULL,
        name           VARCHAR(80)  NOT NULL,
        telegram_link  VARCHAR(255) NOT NULL,
        description    TEXT,
        members        INT UNSIGNED DEFAULT 0,
        language       VARCHAR(30)  DEFAULT 'Indonesia',
        avatar         VARCHAR(255),
        tags           VARCHAR(255),
        status         ENUM('pending','active','rejected') DEFAULT 'pending',
        reject_reason  VARCHAR(255),
        votes          INT UNSIGNED DEFAULT 0,
        last_bumped    DATETIME,
        created_at     DATETIME     DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS server_votes (
        id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        server_id  INT UNSIGNED NOT NULL,
        user_id    INT UNSIGNED NOT NULL,
        voted_at   DATETIME     DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_vote (server_id, user_id),
        FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS server_reviews (
        id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        server_id  INT UNSIGNED NOT NULL,
        user_id    INT UNSIGNED NOT NULL,
        rating     TINYINT      NOT NULL,
        review     TEXT,
        created_at DATETIME     DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_review (server_id, user_id),
        FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS server_reports (
        id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        server_id  INT UNSIGNED NOT NULL,
        user_id    INT UNSIGNED NOT NULL,
        reason     VARCHAR(60),
        detail     TEXT,
        created_at DATETIME     DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS friendships (
        id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_a      INT UNSIGNED NOT NULL,
        user_b      INT UNSIGNED NOT NULL,
        status      ENUM('pending','accepted','declined') DEFAULT 'pending',
        created_at  DATETIME     DEFAULT CURRENT_TIMESTAMP,
        updated_at  DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uq_friendship (user_a, user_b),
        FOREIGN KEY (user_a) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (user_b) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS reset_tokens (
        id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        email      VARCHAR(120) NOT NULL,
        token      VARCHAR(64)  NOT NULL,
        expires_at DATETIME     NOT NULL,
        used       TINYINT(1)   DEFAULT 0,
        INDEX idx_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS visits (
        id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        page          VARCHAR(255) NOT NULL,
        referrer      VARCHAR(255) DEFAULT 'direct',
        ip            VARCHAR(45)  NOT NULL,
        country       VARCHAR(60)  DEFAULT 'Unknown',
        country_code  VARCHAR(5),
        device        VARCHAR(20)  DEFAULT 'Desktop',
        user_agent    VARCHAR(500),
        lang          VARCHAR(20),
        screen        VARCHAR(20),
        created_at    DATETIME     DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_visits_created (created_at),
        INDEX idx_visits_ip (ip),
        INDEX idx_visits_page (page)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
}

/* ═══════════════════════════════════════════════════
   AUTH HELPERS
═══════════════════════════════════════════════════ */
function makeJwt(array $payload): string {
    $header  = base64url(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload['exp'] = time() + JWT_EXPIRE;
    $claims  = base64url(json_encode($payload));
    $sig     = base64url(hash_hmac('sha256', "$header.$claims", JWT_SECRET, true));
    return "$header.$claims.$sig";
}

function verifyJwt(string $token): ?array {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;
    [$h, $c, $s] = $parts;
    $expected = base64url(hash_hmac('sha256', "$h.$c", JWT_SECRET, true));
    if (!hash_equals($expected, $s)) return null;
    $payload = json_decode(base64_decode(strtr($c, '-_', '+/')), true);
    if (!$payload || ($payload['exp'] ?? 0) < time()) return null;
    return $payload;
}

function base64url(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

$GLOBALS['__auth_user'] = null;
$GLOBALS['__is_admin']  = false;

function getAuthHeader(): string {
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['HTTP_X_AUTHORIZATION'] ?? '';
    if (!$header && function_exists('getallheaders')) {
        foreach (getallheaders() as $k => $v) {
            if (strtolower($k) === 'authorization') { $header = $v; break; }
        }
    }
    if (!$header && function_exists('apache_request_headers')) {
        foreach (apache_request_headers() as $k => $v) {
            if (strtolower($k) === 'authorization') { $header = $v; break; }
        }
    }
    return $header;
}

// =====================================================================
// FIX UTAMA: requireAuth sekarang bypass DB lookup untuk admin token
// =====================================================================
function requireAuth(PDO $pdo): void {
    $header = getAuthHeader();
    $token  = str_starts_with($header, 'Bearer ') ? substr($header, 7) : $header;
    if (!$token) jsonResponse(['success' => false, 'message' => 'Unauthorized.'], 401);

    $payload = verifyJwt($token);
    if (!$payload) jsonResponse(['success' => false, 'message' => 'Token tidak valid atau sudah kedaluwarsa.'], 401);

    // Admin token — sub = 'admin', tidak perlu lookup ke tabel users
    if (($payload['admin'] ?? false) === true && ($payload['sub'] ?? '') === 'admin') {
        $GLOBALS['__auth_user'] = [
            'id'       => 0,
            'role'     => 'admin',
            'is_banned'=> 0,
            'full_name'=> 'Admin',
            'email'    => ADMIN_EMAIL,
        ];
        $GLOBALS['__is_admin'] = true;
        return;
    }

    $user = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $user->execute([$payload['sub']]);
    $u = $user->fetch();
    if (!$u) jsonResponse(['success' => false, 'message' => 'Pengguna tidak ditemukan.'], 401);
    $GLOBALS['__auth_user'] = $u;
}

function requireAdminAuth(PDO $pdo): void {
    requireAuth($pdo);
    $u = authUser();
    if (($u['role'] ?? '') !== 'admin') {
        jsonResponse(['success' => false, 'message' => 'Akses admin saja.'], 403);
    }
    $GLOBALS['__is_admin'] = true;
}

function authUser(): array { return $GLOBALS['__auth_user']; }
function isAdmin(): bool { return $GLOBALS['__is_admin']; }

/* ═══════════════════════════════════════════════════
   ENDPOINT: AUTH
═══════════════════════════════════════════════════ */
function checkUsername(PDO $pdo): void {
    $username = trim($_GET['username'] ?? '');
    if (!preg_match('/^[a-zA-Z0-9_]{3,32}$/', $username))
        jsonResponse(['available' => false, 'message' => 'Format tidak valid.']);
    $stmt = $pdo->prepare('SELECT id FROM users WHERE tg_username = ?');
    $stmt->execute([$username]);
    jsonResponse(['available' => !$stmt->fetch()]);
}

function sendOtp(PDO $pdo, array $body): void {
    $email    = strtolower(trim($body['email'] ?? ''));
    $tgUser   = trim($body['tg_username'] ?? '');
    $fullName = trim($body['full_name'] ?? '');
    $password = $body['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        jsonResponse(['success' => false, 'message' => 'Format email tidak valid.']);

    $check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $check->execute([$email]);
    if ($check->fetch())
        jsonResponse(['success' => false, 'message' => 'Email sudah terdaftar.']);

    $checkU = $pdo->prepare('SELECT id FROM users WHERE tg_username = ?');
    $checkU->execute([$tgUser]);
    if ($checkU->fetch())
        jsonResponse(['success' => false, 'message' => 'Username Telegram sudah dipakai.']);

    $pdo->prepare('DELETE FROM pending_registrations WHERE email = ?')->execute([$email]);
    $pdo->prepare('INSERT INTO pending_registrations (tg_username, full_name, email, password) VALUES (?,?,?,?)')
        ->execute([$tgUser, $fullName, $email, password_hash($password, PASSWORD_BCRYPT)]);

    $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $pdo->prepare('DELETE FROM otp_codes WHERE email = ? AND purpose = ?')->execute([$email, 'register']);
    $pdo->prepare('INSERT INTO otp_codes (email, code, purpose, expires_at) VALUES (?,?,?,?)')
        ->execute([$email, $code, 'register', date('Y-m-d H:i:s', time() + OTP_EXPIRE)]);

    sendMail($email, 'Kode OTP TeleBoard', "Kode OTP kamu: <b>$code</b><br/>Berlaku 10 menit.");
    jsonResponse(['success' => true, 'message' => 'OTP dikirim ke ' . $email]);
}

function register(PDO $pdo, array $body): void {
    $email = strtolower(trim($body['email'] ?? ''));
    $otp   = trim($body['otp'] ?? '');

    $row = $pdo->prepare('SELECT * FROM otp_codes WHERE email = ? AND purpose = ? AND used = 0 ORDER BY id DESC LIMIT 1');
    $row->execute([$email, 'register']);
    $otpRow = $row->fetch();

    if (!$otpRow || $otpRow['code'] !== $otp)
        jsonResponse(['success' => false, 'message' => 'Kode OTP salah.']);
    if (strtotime($otpRow['expires_at']) < time())
        jsonResponse(['success' => false, 'message' => 'Kode OTP sudah kedaluwarsa.']);

    $pend = $pdo->prepare('SELECT * FROM pending_registrations WHERE email = ?');
    $pend->execute([$email]);
    $p = $pend->fetch();
    if (!$p) jsonResponse(['success' => false, 'message' => 'Data registrasi tidak ditemukan.']);

    $ins = $pdo->prepare('INSERT INTO users (tg_username, full_name, email, password) VALUES (?,?,?,?)');
    $ins->execute([$p['tg_username'], $p['full_name'], $email, $p['password']]);
    $userId = $pdo->lastInsertId();

    $pdo->prepare('UPDATE otp_codes SET used = 1 WHERE id = ?')->execute([$otpRow['id']]);
    $pdo->prepare('DELETE FROM pending_registrations WHERE email = ?')->execute([$email]);

    $token = makeJwt(['sub' => $userId]);
    jsonResponse(['success' => true, 'token' => $token, 'user' => publicUser($p + ['id' => $userId])]);
}

function login(PDO $pdo, array $body): void {
    $identifier = trim($body['identifier'] ?? '');
    $password   = $body['password'] ?? '';
    $isEmail    = str_contains($identifier, '@');
    $col        = $isEmail ? 'email' : 'tg_username';
    $stmt       = $pdo->prepare("SELECT * FROM users WHERE $col = ?");
    $stmt->execute([strtolower($identifier)]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['password']))
        jsonResponse(['success' => false, 'message' => 'Username/email atau password salah.']);
    if ($user['is_banned'])
        jsonResponse(['success' => false, 'message' => 'Akun ini telah di-ban.'], 403);
    $token = makeJwt(['sub' => $user['id']]);
    jsonResponse(['success' => true, 'token' => $token, 'user' => publicUser($user)]);
}

// =====================================================================
// FIX: adminLogin pakai sub='admin' (string), bukan sub=0 (integer)
// =====================================================================
function adminLogin(PDO $pdo, array $body): void {
    $email    = strtolower(trim($body['email'] ?? ''));
    $password = $body['password'] ?? '';

    if ($email !== ADMIN_EMAIL || !password_verify($password, ADMIN_PASS_HASH)) {
        jsonResponse(['success' => false, 'message' => 'Email atau password admin salah.'], 401);
    }

    // sub = 'admin' (string), bukan 0, supaya requireAuth bisa bypass DB lookup
    $token = makeJwt(['sub' => 'admin', 'admin' => true]);
    jsonResponse(['success' => true, 'token' => $token, 'admin' => true]);
}

function forgotPassword(PDO $pdo, array $body): void {
    $email = strtolower(trim($body['email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        jsonResponse(['success' => false, 'message' => 'Format email tidak valid.']);
    $check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $check->execute([$email]);
    if (!$check->fetch())
        jsonResponse(['success' => false, 'message' => 'Email tidak terdaftar.']);
    $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $pdo->prepare('DELETE FROM otp_codes WHERE email = ? AND purpose = ?')->execute([$email, 'reset']);
    $pdo->prepare('INSERT INTO otp_codes (email, code, purpose, expires_at) VALUES (?,?,?,?)')
        ->execute([$email, $code, 'reset', date('Y-m-d H:i:s', time() + OTP_EXPIRE)]);
    sendMail($email, 'Reset Password TeleBoard', "Kode OTP reset password: <b>$code</b><br/>Berlaku 10 menit.");
    jsonResponse(['success' => true]);
}

function verifyResetOtp(PDO $pdo, array $body): void {
    $email = strtolower(trim($body['email'] ?? ''));
    $otp   = trim($body['otp'] ?? '');
    $row   = $pdo->prepare('SELECT * FROM otp_codes WHERE email = ? AND purpose = ? AND used = 0 ORDER BY id DESC LIMIT 1');
    $row->execute([$email, 'reset']);
    $otpRow = $row->fetch();
    if (!$otpRow || $otpRow['code'] !== $otp)
        jsonResponse(['success' => false, 'message' => 'Kode OTP salah.']);
    if (strtotime($otpRow['expires_at']) < time())
        jsonResponse(['success' => false, 'message' => 'Kode OTP sudah kedaluwarsa.']);
    $pdo->prepare('UPDATE otp_codes SET used = 1 WHERE id = ?')->execute([$otpRow['id']]);
    $resetToken = bin2hex(random_bytes(32));
    $pdo->prepare('INSERT INTO reset_tokens (email, token, expires_at) VALUES (?,?,?)')
        ->execute([$email, $resetToken, date('Y-m-d H:i:s', time() + 900)]);
    jsonResponse(['success' => true, 'reset_token' => $resetToken]);
}

function resetPassword(PDO $pdo, array $body): void {
    $email      = strtolower(trim($body['email'] ?? ''));
    $resetToken = trim($body['reset_token'] ?? '');
    $newPw      = $body['password'] ?? '';
    if (strlen($newPw) < 8)
        jsonResponse(['success' => false, 'message' => 'Password minimal 8 karakter.']);
    $row = $pdo->prepare('SELECT * FROM reset_tokens WHERE email = ? AND token = ? AND used = 0');
    $row->execute([$email, $resetToken]);
    $rt = $row->fetch();
    if (!$rt || strtotime($rt['expires_at']) < time())
        jsonResponse(['success' => false, 'message' => 'Token reset tidak valid atau sudah kedaluwarsa.']);
    $pdo->prepare('UPDATE users SET password = ? WHERE email = ?')
        ->execute([password_hash($newPw, PASSWORD_BCRYPT), $email]);
    $pdo->prepare('UPDATE reset_tokens SET used = 1 WHERE id = ?')->execute([$rt['id']]);
    jsonResponse(['success' => true, 'message' => 'Password berhasil direset.']);
}

/* ═══════════════════════════════════════════════════
   ENDPOINT: PROFILE
═══════════════════════════════════════════════════ */
function updateProfile(PDO $pdo, array $body): void {
    $u    = authUser();
    $name = trim($body['full_name'] ?? $u['full_name']);
    $tg   = trim($body['tg_username'] ?? $u['tg_username']);
    $bio  = trim($body['bio'] ?? '');
    $link = trim($body['tg_link'] ?? '');
    $check = $pdo->prepare('SELECT id FROM users WHERE tg_username = ? AND id != ?');
    $check->execute([$tg, $u['id']]);
    if ($check->fetch())
        jsonResponse(['success' => false, 'message' => 'Username sudah dipakai pengguna lain.']);
    $pdo->prepare('UPDATE users SET full_name=?, tg_username=?, bio=?, tg_link=? WHERE id=?')
        ->execute([$name, $tg, $bio, $link, $u['id']]);
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$u['id']]);
    jsonResponse(['success' => true, 'user' => publicUser($stmt->fetch())]);
}

function uploadAvatar(PDO $pdo): void {
    $u = authUser();
    if (empty($_FILES['avatar'])) jsonResponse(['success' => false, 'message' => 'Tidak ada file.']);
    $file    = $_FILES['avatar'];
    $mime    = mime_content_type($file['tmp_name']);
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    if (!in_array($mime, $allowed))
        jsonResponse(['success' => false, 'message' => 'Format gambar tidak didukung.']);
    if ($file['size'] > MAX_IMG_MB * 1024 * 1024)
        jsonResponse(['success' => false, 'message' => 'Ukuran gambar maksimal ' . MAX_IMG_MB . 'MB.']);
    $ext  = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'jpg';
    $name = 'avatar_' . $u['id'] . '_' . time() . '.' . $ext;
    move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $name);
    $url = UPLOAD_URL . $name;
    $pdo->prepare('UPDATE users SET avatar = ? WHERE id = ?')->execute([$url, $u['id']]);
    jsonResponse(['success' => true, 'avatar' => $url]);
}

/* ═══════════════════════════════════════════════════
   ENDPOINT: SERVERS
═══════════════════════════════════════════════════ */
function getServer(PDO $pdo): void {
    $id   = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare('
        SELECT s.*, u.full_name AS owner_name, u.tg_username AS owner_username, u.email AS owner_email, u.avatar AS owner_avatar
        FROM servers s
        JOIN users u ON u.id = s.owner_id
        WHERE s.id = ? AND s.status = "active"
    ');
    $stmt->execute([$id]);
    $server = $stmt->fetch();
    if (!$server) jsonResponse(['success' => false, 'message' => 'Server tidak ditemukan.'], 404);

    $rev = $pdo->prepare('
        SELECT r.*, u.full_name, u.avatar
        FROM server_reviews r
        JOIN users u ON u.id = r.user_id
        WHERE r.server_id = ?
        ORDER BY r.created_at DESC
        LIMIT 20
    ');
    $rev->execute([$id]);
    $reviews = $rev->fetchAll();

    $avgRating = count($reviews)
        ? array_sum(array_column($reviews, 'rating')) / count($reviews)
        : 0;

    $bumped = $server['last_bumped']
        ? humanTime(strtotime($server['last_bumped']))
        : 'Belum di-bump';

    jsonResponse([
        'success' => true,
        'server'  => [
            'id'          => $server['id'],
            'name'        => $server['name'],
            'avatar'      => $server['avatar'] ?: '📡',
            'telegram'    => $server['telegram_link'],
            'members'     => (int)$server['members'],
            'online'      => max(1, (int)($server['members'] * 0.13)),
            'description' => $server['description'],
            'tag'         => $server['tags'],
            'votes'       => (int)$server['votes'],
            'bumped'      => $bumped,
            'rating'      => round($avgRating, 1),
            'reviewCount' => count($reviews),
            'reviews'     => array_map(fn($r) => [
                'name'   => $r['full_name'],
                'rating' => (int)$r['rating'],
                'date'   => humanTime(strtotime($r['created_at'])),
                'text'   => $r['review'],
            ], $reviews),
            'createdAt'   => date('d M Y', strtotime($server['created_at'])),
            'verified'    => (bool)($server['verified'] ?? 0),
            'owner'       => [
                'name'     => $server['owner_name'],
                'username' => $server['owner_username'],
                'email'    => $server['owner_email'],
                'avatar'   => $server['owner_avatar'] ?? null,
            ],
        ],
    ]);
}

function relatedServers(PDO $pdo): void {
    $excludeId = (int)($_GET['id'] ?? 0);
    $tags      = trim($_GET['tags'] ?? '');
    $limit     = min(8, max(1, (int)($_GET['limit'] ?? 4)));

    if (!$tags) { jsonResponse(['success' => true, 'servers' => []]); }

    $tagList = array_values(array_filter(array_map('trim', explode(',', $tags))));
    if (!$tagList) { jsonResponse(['success' => true, 'servers' => []]); }

    $conditions = array_map(fn($t) => "FIND_IN_SET(?, REPLACE(tags, ' ', ''))", $tagList);
    $whereOr = '(' . implode(' OR ', $conditions) . ')';

    $sql = "
        SELECT id, name, avatar, members, votes
        FROM servers
        WHERE status = 'active' AND id != ? AND {$whereOr}
        ORDER BY votes DESC
        LIMIT {$limit}
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_merge([$excludeId], $tagList));
    $servers = $stmt->fetchAll();

    jsonResponse([
        'success' => true,
        'servers' => array_map(fn($s) => [
            'id'      => $s['id'],
            'name'    => $s['name'],
            'avatar'  => $s['avatar'] ?: '📡',
            'members' => (int)$s['members'],
            'votes'   => (int)$s['votes'],
        ], $servers),
    ]);
}

function getServers(PDO $pdo): void {
    $search  = trim($_GET['q'] ?? '');
    $tag     = trim($_GET['tag'] ?? '');
    $sort    = in_array($_GET['sort'] ?? '', ['votes', 'members', 'created_at', 'last_bumped'])
               ? $_GET['sort'] : 'votes';
    $page    = max(1, (int)($_GET['page'] ?? 1));
    $perPage = min(50, max(1, (int)($_GET['per_page'] ?? 20)));
    $offset  = ($page - 1) * $perPage;

    $where  = ["s.status = 'active'"];
    $params = [];

    if ($search !== '') {
        $where[]  = '(s.name LIKE ? OR s.description LIKE ? OR s.tags LIKE ?)';
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    if ($tag !== '') {
        $where[]  = "FIND_IN_SET(?, REPLACE(s.tags, ' ', ''))";
        $params[] = $tag;
    }

    $whereSQL = implode(' AND ', $where);

    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM servers s WHERE $whereSQL");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    $params[] = $perPage;
    $params[] = $offset;
    $stmt = $pdo->prepare("
        SELECT s.id, s.name, s.avatar, s.members, s.votes, s.tags, s.description,
               s.last_bumped, s.created_at,
               COALESCE(AVG(r.rating), 0) AS avg_rating,
               COUNT(r.id) AS review_count
        FROM servers s
        LEFT JOIN server_reviews r ON r.server_id = s.id
        WHERE $whereSQL
        GROUP BY s.id
        ORDER BY s.$sort DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute($params);
    $servers = $stmt->fetchAll();

    jsonResponse([
        'success'  => true,
        'total'    => $total,
        'page'     => $page,
        'per_page' => $perPage,
        'servers'  => array_map(fn($s) => [
            'id'           => $s['id'],
            'name'         => $s['name'],
            'avatar'       => $s['avatar'] ?: '📡',
            'members'      => (int)$s['members'],
            'votes'        => (int)$s['votes'],
            'tags'         => $s['tags'],
            'desc'         => $s['description'],
            'avg_rating'   => round((float)$s['avg_rating'], 1),
            'review_count' => (int)$s['review_count'],
            'bumped'       => $s['last_bumped'] ? humanTime(strtotime($s['last_bumped'])) : 'Belum di-bump',
        ], $servers),
    ]);
}

function getStats(PDO $pdo): void {
    $totalServers = (int)$pdo->query("SELECT COUNT(*) FROM servers WHERE status = 'active'")->fetchColumn();
    $totalMembers = (int)$pdo->query("SELECT COALESCE(SUM(members),0) FROM servers WHERE status = 'active'")->fetchColumn();
    $votesToday   = (int)$pdo->query("SELECT COUNT(*) FROM server_votes WHERE DATE(voted_at) = CURDATE()")->fetchColumn();

    jsonResponse([
        'success'       => true,
        'total_servers' => $totalServers,
        'total_members' => $totalMembers,
        'votes_today'   => $votesToday,
    ]);
}

function getMyServers(PDO $pdo): void {
    $u    = authUser();
    $stmt = $pdo->prepare('SELECT * FROM servers WHERE owner_id = ? ORDER BY created_at DESC');
    $stmt->execute([$u['id']]);
    $servers = $stmt->fetchAll();
    jsonResponse([
        'success' => true,
'servers' => array_map(fn($s) => [
    'id'            => $s['id'],
    'name'          => $s['name'],
    'emoji'         => $s['avatar'],
    'members'       => (int)$s['members'],
    'votes'         => (int)$s['votes'],
    'status'        => $s['status'],
    'reject_reason' => $s['reject_reason'] ?? null,
], $servers),
    ]);
}

function addServer(PDO $pdo, array $body): void {
    $u    = authUser();
    $name = trim($body['name'] ?? '');
    $link = trim($body['telegram_link'] ?? '');
    $desc = trim($body['description'] ?? '');
    $mem  = max(0, (int)($body['members'] ?? 0));
    $lang = trim($body['language'] ?? 'Indonesia');
    $tags = is_array($body['tags']) ? implode(',', array_slice($body['tags'], 0, 5)) : trim($body['tags'] ?? '');
    $avaRaw = trim($body['avatar'] ?? '📡');

    if (strlen($name) < 3 || strlen($name) > 60)
        jsonResponse(['success' => false, 'message' => 'Nama server harus 3-60 karakter.']);
    if (!preg_match('/^https?:\/\/(t\.me|telegram\.me)\/[A-Za-z0-9_+]{3,}/', $link))
        jsonResponse(['success' => false, 'message' => 'Link Telegram tidak valid.']);
    if (strlen($desc) > 1000)
        jsonResponse(['success' => false, 'message' => 'Deskripsi maksimal 1000 karakter.']);
    if (strlen($lang) > 30)
        jsonResponse(['success' => false, 'message' => 'Bahasa maksimal 30 karakter.']);
    if (strlen($tags) > 255)
        jsonResponse(['success' => false, 'message' => 'Tag terlalu banyak/panjang.']);

    $ava = saveAvatarValue($avaRaw, $u['id']);

    $pdo->prepare('
        INSERT INTO servers (owner_id, name, telegram_link, description, members, language, avatar, tags, status)
        VALUES (?,?,?,?,?,?,?,?,?)
    ')->execute([$u['id'], $name, $link, $desc, $mem, $lang, $ava, $tags, 'pending']);

    jsonResponse(['success' => true, 'message' => 'Server berhasil dikirim dan menunggu peninjauan.']);
}

function saveAvatarValue(string $value, int $ownerId): string {
    if (!str_starts_with($value, 'data:image/')) {
        return mb_substr($value, 0, 50);
    }
    if (!preg_match('/^data:image\/(png|jpe?g|gif|webp);base64,(.+)$/i', $value, $m)) {
        return '📡';
    }
    $ext  = strtolower($m[1]) === 'jpeg' ? 'jpg' : strtolower($m[1]);
    $data = base64_decode($m[2]);
    if ($data === false) return '📡';
    if (strlen($data) > MAX_IMG_MB * 1024 * 1024) {
        jsonResponse(['success' => false, 'message' => 'Ukuran gambar avatar maksimal ' . MAX_IMG_MB . 'MB.']);
    }
    $name = 'server_avatar_' . $ownerId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    file_put_contents(UPLOAD_DIR . $name, $data);
    return UPLOAD_URL . $name;
}

function bumpServer(PDO $pdo, array $body): void {
    $u  = authUser();
    $id = (int)($body['server_id'] ?? 0);
    $stmt = $pdo->prepare('SELECT * FROM servers WHERE id = ? AND owner_id = ?');
    $stmt->execute([$id, $u['id']]);
    $server = $stmt->fetch();
    if (!$server) jsonResponse(['success' => false, 'message' => 'Server tidak ditemukan.']);
    if ($server['last_bumped'] && (time() - strtotime($server['last_bumped'])) < 1800)
        jsonResponse(['success' => false, 'message' => 'Bump hanya bisa dilakukan setiap 30 menit.']);
    $pdo->prepare('UPDATE servers SET last_bumped = NOW() WHERE id = ?')->execute([$id]);
    jsonResponse(['success' => true, 'message' => 'Server berhasil di-bump!']);
}

function voteServer(PDO $pdo, array $body): void {
    $u  = authUser();
    $id = (int)($body['server_id'] ?? 0);
    $check = $pdo->prepare('
        SELECT id FROM server_votes
        WHERE server_id = ? AND user_id = ? AND DATE(voted_at) = CURDATE()
    ');
    $check->execute([$id, $u['id']]);
    if ($check->fetch())
        jsonResponse(['success' => false, 'message' => 'Kamu sudah vote server ini hari ini.']);
    $pdo->prepare('INSERT INTO server_votes (server_id, user_id) VALUES (?,?)')->execute([$id, $u['id']]);
    $pdo->prepare('UPDATE servers SET votes = votes + 1 WHERE id = ?')->execute([$id]);
    $votes = $pdo->prepare('SELECT votes FROM servers WHERE id = ?');
    $votes->execute([$id]);
    jsonResponse(['success' => true, 'votes' => (int)$votes->fetchColumn()]);
}

function submitReview(PDO $pdo, array $body): void {
    $u      = authUser();
    $sid    = (int)($body['server_id'] ?? 0);
    $rating = max(1, min(5, (int)($body['rating'] ?? 0)));
    $text   = trim($body['text'] ?? '');
    if (strlen($text) < 5)
        jsonResponse(['success' => false, 'message' => 'Ulasan minimal 5 karakter.']);
    if (strlen($text) > 1000)
        jsonResponse(['success' => false, 'message' => 'Ulasan maksimal 1000 karakter.']);
    $pdo->prepare('
        INSERT INTO server_reviews (server_id, user_id, rating, review)
        VALUES (?,?,?,?)
        ON DUPLICATE KEY UPDATE rating=VALUES(rating), review=VALUES(review), created_at=NOW()
    ')->execute([$sid, $u['id'], $rating, $text]);
    jsonResponse(['success' => true]);
}

function reportServer(PDO $pdo, array $body): void {
    $u      = authUser();
    $sid    = (int)($body['server_id'] ?? 0);
    $reason = trim($body['reason'] ?? 'lain');
    $detail = trim($body['detail'] ?? '');
    $pdo->prepare('INSERT INTO server_reports (server_id, user_id, reason, detail) VALUES (?,?,?,?)')
        ->execute([$sid, $u['id'], $reason, $detail]);
    jsonResponse(['success' => true]);
}

/* ═══════════════════════════════════════════════════
   ENDPOINT: FRIENDS
═══════════════════════════════════════════════════ */
function searchUsers(PDO $pdo): void {
    $u    = authUser();
    $q    = '%' . trim($_GET['q'] ?? '') . '%';
    $stmt = $pdo->prepare('
        SELECT id, full_name, tg_username, avatar FROM users
        WHERE id != ? AND (full_name LIKE ? OR tg_username LIKE ?)
        LIMIT 10
    ');
    $stmt->execute([$u['id'], $q, $q]);
    $users = $stmt->fetchAll();

    $result = array_map(function ($usr) use ($pdo, $u) {
        $f = $pdo->prepare('
            SELECT status FROM friendships
            WHERE (user_a = ? AND user_b = ?) OR (user_a = ? AND user_b = ?)
        ');
        $f->execute([$u['id'], $usr['id'], $usr['id'], $u['id']]);
        $rel = $f->fetch();
        return [
            'id'           => $usr['id'],
            'full_name'    => $usr['full_name'],
            'tg_username'  => $usr['tg_username'],
            'avatar'       => $usr['avatar'],
            'is_friend'    => $rel && $rel['status'] === 'accepted',
            'request_sent' => $rel && $rel['status'] === 'pending' && !isFriendRequester($pdo, $u['id'], $usr['id']),
        ];
    }, $users);

    jsonResponse(['success' => true, 'users' => $result]);
}

function isFriendRequester(PDO $pdo, int $myId, int $otherId): bool {
    $s = $pdo->prepare('SELECT id FROM friendships WHERE user_a = ? AND user_b = ? AND status = "pending"');
    $s->execute([$myId, $otherId]);
    return (bool)$s->fetch();
}

function sendFriendRequest(PDO $pdo, array $body): void {
    $u  = authUser();
    $to = (int)($body['to_user_id'] ?? 0);
    if ($to === $u['id']) jsonResponse(['success' => false, 'message' => 'Tidak bisa menambahkan diri sendiri.']);
    $check = $pdo->prepare('
        SELECT id FROM friendships
        WHERE (user_a = ? AND user_b = ?) OR (user_a = ? AND user_b = ?)
    ');
    $check->execute([$u['id'], $to, $to, $u['id']]);
    if ($check->fetch()) jsonResponse(['success' => false, 'message' => 'Permintaan sudah ada.']);
    $pdo->prepare('INSERT INTO friendships (user_a, user_b) VALUES (?,?)')->execute([$u['id'], $to]);
    jsonResponse(['success' => true]);
}

function getFriendRequests(PDO $pdo): void {
    $u    = authUser();
    $stmt = $pdo->prepare('
        SELECT f.id, f.created_at, u.id AS uid, u.full_name, u.tg_username, u.avatar
        FROM friendships f
        JOIN users u ON u.id = f.user_a
        WHERE f.user_b = ? AND f.status = "pending"
        ORDER BY f.created_at DESC
    ');
    $stmt->execute([$u['id']]);
    $rows = $stmt->fetchAll();
    jsonResponse([
        'success'  => true,
        'requests' => array_map(fn($r) => [
            'id'         => $r['id'],
            'created_at' => humanTime(strtotime($r['created_at'])),
            'from_user'  => [
                'id'          => $r['uid'],
                'full_name'   => $r['full_name'],
                'tg_username' => $r['tg_username'],
                'avatar'      => $r['avatar'],
            ],
        ], $rows),
    ]);
}

function acceptFriendRequest(PDO $pdo, array $body): void {
    $u   = authUser();
    $rid = (int)($body['request_id'] ?? 0);
    $check = $pdo->prepare('SELECT id FROM friendships WHERE id = ? AND user_b = ? AND status = "pending"');
    $check->execute([$rid, $u['id']]);
    if (!$check->fetch()) jsonResponse(['success' => false, 'message' => 'Permintaan tidak ditemukan.']);
    $pdo->prepare('UPDATE friendships SET status = "accepted" WHERE id = ?')->execute([$rid]);
    jsonResponse(['success' => true]);
}

function declineFriendRequest(PDO $pdo, array $body): void {
    $u   = authUser();
    $rid = (int)($body['request_id'] ?? 0);
    $pdo->prepare('UPDATE friendships SET status = "declined" WHERE id = ? AND user_b = ?')->execute([$rid, $u['id']]);
    jsonResponse(['success' => true]);
}

function getFriends(PDO $pdo): void {
    $u    = authUser();
    $stmt = $pdo->prepare('
        SELECT u.id, u.full_name, u.tg_username, u.avatar
        FROM friendships f
        JOIN users u ON u.id = IF(f.user_a = ?, f.user_b, f.user_a)
        WHERE (f.user_a = ? OR f.user_b = ?) AND f.status = "accepted"
    ');
    $stmt->execute([$u['id'], $u['id'], $u['id']]);
    $friends = $stmt->fetchAll();
    jsonResponse([
        'success' => true,
        'friends' => array_map(fn($f) => [
            'id'          => $f['id'],
            'full_name'   => $f['full_name'],
            'tg_username' => $f['tg_username'],
            'avatar'      => $f['avatar'],
            'is_online'   => false,
        ], $friends),
    ]);
}

function removeFriend(PDO $pdo, array $body): void {
    $u   = authUser();
    $fid = (int)($body['friend_id'] ?? 0);
    $pdo->prepare('
        DELETE FROM friendships
        WHERE ((user_a = ? AND user_b = ?) OR (user_a = ? AND user_b = ?)) AND status = "accepted"
    ')->execute([$u['id'], $fid, $fid, $u['id']]);
    jsonResponse(['success' => true]);
}

/* ═══════════════════════════════════════════════════
   ENDPOINT: VISITOR ANALYTICS
═══════════════════════════════════════════════════ */
function getClientIp(): string {
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = trim(explode(',', $_SERVER[$key])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function maskIp(string $ip): string {
    if (str_contains($ip, '.')) {
        $p = explode('.', $ip);
        return ($p[0] ?? '0') . '.' . ($p[1] ?? '0') . '.xxx.xxx';
    }
    return substr($ip, 0, 8) . '::xxxx';
}

function detectDevice(string $ua): string {
    $ua = strtolower($ua);
    if (preg_match('/ipad|tablet/', $ua)) return 'Tablet';
    if (preg_match('/mobile|android|iphone/', $ua)) return 'Mobile';
    return 'Desktop';
}

function geoLookupCountry(string $ip): array {
    if (in_array($ip, ['127.0.0.1', '::1', '0.0.0.0']) || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.')) {
        return ['country' => 'Local', 'code' => ''];
    }
    $ctx = stream_context_create(['http' => ['timeout' => 2]]);
    $res = @file_get_contents("http://ip-api.com/json/{$ip}?fields=country,countryCode", false, $ctx);
    if ($res) {
        $d = json_decode($res, true);
        if (!empty($d['country'])) return ['country' => $d['country'], 'code' => $d['countryCode'] ?? ''];
    }
    return ['country' => 'Unknown', 'code' => ''];
}

function flagFromCode(string $code): string {
    $code = strtoupper(trim($code));
    if (strlen($code) !== 2) return '🌐';
    $offset = 0x1F1E6 - 65;
    return mb_chr(ord($code[0]) + $offset) . mb_chr(ord($code[1]) + $offset);
}

function rangeWhereClause(string $range, string $col = 'created_at'): string {
    switch ($range) {
        case '7d':  return "$col >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        case '30d': return "$col >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        case 'all': return "1=1";
        default:    return "DATE($col) = CURDATE()";
    }
}

function trackVisit(PDO $pdo, array $body): void {
    $page     = mb_substr(trim($body['page'] ?? '/'), 0, 255);
    $referrer = mb_substr(trim($body['referrer'] ?? 'direct'), 0, 255);
    $ua       = mb_substr(trim($body['ua'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? '')), 0, 500);
    $lang     = mb_substr(trim($body['lang'] ?? ''), 0, 20);
    $screen   = mb_substr(trim($body['screen'] ?? ''), 0, 20);

    $ip     = getClientIp();
    $geo    = geoLookupCountry($ip);
    $device = detectDevice($ua);

    $pdo->prepare('
        INSERT INTO visits (page, referrer, ip, country, country_code, device, user_agent, lang, screen)
        VALUES (?,?,?,?,?,?,?,?,?)
    ')->execute([$page, $referrer ?: 'direct', $ip, $geo['country'], $geo['code'], $device, $ua, $lang, $screen]);

    jsonResponse(['success' => true]);
}

function getPreviousPeriodStats(PDO $pdo, string $range): array {
    switch ($range) {
        case '7d':
            $where = "created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY) AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)";
            break;
        case '30d':
            $where = "created_at >= DATE_SUB(NOW(), INTERVAL 60 DAY) AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)";
            break;
        case 'all':
            return [0, 0];
        default:
            $where = "DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
    }
    $total  = (int)$pdo->query("SELECT COUNT(*) FROM visits WHERE $where")->fetchColumn();
    $unique = (int)$pdo->query("SELECT COUNT(DISTINCT ip) FROM visits WHERE $where")->fetchColumn();
    return [$total, $unique];
}

function getVisitStats(PDO $pdo): void {
    $range = $_GET['range'] ?? 'today';
    $where = rangeWhereClause($range);

    $total  = (int)$pdo->query("SELECT COUNT(*) FROM visits WHERE $where")->fetchColumn();
    $unique = (int)$pdo->query("SELECT COUNT(DISTINCT ip) FROM visits WHERE $where")->fetchColumn();

    $pagesPerSession = $unique > 0 ? $total / $unique : 0;

    $singleVisitIps = (int)$pdo->query("
        SELECT COUNT(*) FROM (
            SELECT ip FROM visits WHERE $where GROUP BY ip HAVING COUNT(*) = 1
        ) t
    ")->fetchColumn();
    $bounceRate = $unique > 0 ? ($singleVisitIps / $unique) * 100 : 0;

    [$prevTotal, $prevUnique] = getPreviousPeriodStats($pdo, $range);
    $changeVisits = $prevTotal > 0 ? (($total - $prevTotal) / $prevTotal) * 100 : 0;
    $changeUnique = $prevUnique > 0 ? (($unique - $prevUnique) / $prevUnique) * 100 : 0;

    jsonResponse([
        'success' => true,
        'stats' => [
            'total_visits'      => $total,
            'unique_visitors'   => $unique,
            'pages_per_session' => round($pagesPerSession, 2),
            'avg_duration'      => 0,
            'bounce_rate'       => round($bounceRate, 1),
            'change_visits'     => round($changeVisits, 1),
            'change_unique'     => round($changeUnique, 1),
        ],
    ]);
}

function getVisitTrend(PDO $pdo): void {
    $range = $_GET['range'] ?? 'today';

    if ($range === 'today') {
        $stmt = $pdo->query("
            SELECT HOUR(created_at) AS h, COUNT(*) AS c
            FROM visits WHERE DATE(created_at) = CURDATE()
            GROUP BY h
        ");
        $rows = $stmt->fetchAll();
        $map  = array_fill(0, 24, 0);
        foreach ($rows as $r) $map[(int)$r['h']] = (int)$r['c'];
        $labels = []; $data = [];
        foreach ($map as $h => $c) { $labels[] = sprintf('%02d:00', $h); $data[] = $c; }
    } else {
        $where = $range === '7d'
            ? "created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
            : ($range === '30d'
                ? "created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
                : "1=1");
        $stmt = $pdo->query("
            SELECT DATE(created_at) AS d, COUNT(*) AS c
            FROM visits WHERE $where
            GROUP BY d ORDER BY d
        ");
        $rows   = $stmt->fetchAll();
        $labels = array_map(fn($r) => date('d/m', strtotime($r['d'])), $rows);
        $data   = array_map(fn($r) => (int)$r['c'], $rows);
    }

    jsonResponse(['success' => true, 'labels' => $labels, 'visits' => $data]);
}

function getDeviceStats(PDO $pdo): void {
    $range = $_GET['range'] ?? 'today';
    $where = rangeWhereClause($range);
    $stmt  = $pdo->query("SELECT device, COUNT(*) AS c FROM visits WHERE $where GROUP BY device");
    $rows  = $stmt->fetchAll();
    $total = array_sum(array_column($rows, 'c'));
    $devices = $total > 0
        ? array_map(fn($r) => ['label' => $r['device'] ?: 'Unknown', 'value' => round($r['c'] / $total * 100)], $rows)
        : [];
    jsonResponse(['success' => true, 'devices' => $devices]);
}

function getTopPages(PDO $pdo): void {
    $range = $_GET['range'] ?? 'today';
    $limit = min(20, max(1, (int)($_GET['limit'] ?? 8)));
    $where = rangeWhereClause($range);
    $stmt  = $pdo->prepare("SELECT page, COUNT(*) AS visits FROM visits WHERE $where GROUP BY page ORDER BY visits DESC LIMIT ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    $pages = $stmt->fetchAll();
    jsonResponse(['success' => true, 'pages' => array_map(fn($p) => [
        'page'   => $p['page'],
        'visits' => (int)$p['visits'],
    ], $pages)]);
}

function getTrafficSources(PDO $pdo): void {
    $range = $_GET['range'] ?? 'today';
    $where = rangeWhereClause($range);
    $stmt  = $pdo->query("SELECT referrer, COUNT(DISTINCT ip) AS visitors FROM visits WHERE $where GROUP BY referrer ORDER BY visitors DESC LIMIT 15");
    $rows  = $stmt->fetchAll();
    $sources = array_map(function ($r) {
        $ref = $r['referrer'];
        if ($ref === '' || strtolower($ref) === 'direct') {
            $label = 'Direct';
        } else {
            $host  = @parse_url($ref, PHP_URL_HOST);
            $label = $host ?: $ref;
        }
        return ['source' => $label, 'visitors' => (int)$r['visitors']];
    }, $rows);
    jsonResponse(['success' => true, 'sources' => $sources]);
}

function getVisitorCountries(PDO $pdo): void {
    $range = $_GET['range'] ?? 'today';
    $where = rangeWhereClause($range);
    $stmt  = $pdo->query("SELECT country, country_code, COUNT(DISTINCT ip) AS visitors FROM visits WHERE $where GROUP BY country, country_code ORDER BY visitors DESC LIMIT 15");
    $rows  = $stmt->fetchAll();
    $countries = array_map(fn($r) => [
        'country'  => $r['country'] ?: 'Unknown',
        'visitors' => (int)$r['visitors'],
        'flag'     => $r['country_code'] ? flagFromCode($r['country_code']) : '🌐',
    ], $rows);
    jsonResponse(['success' => true, 'countries' => $countries]);
}

function getVisitorLog(PDO $pdo): void {
    $limit = min(50, max(1, (int)($_GET['limit'] ?? 20)));
    $stmt  = $pdo->prepare("SELECT page, referrer, ip, country, country_code, user_agent, created_at FROM visits ORDER BY created_at DESC LIMIT ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll();
    jsonResponse(['success' => true, 'log' => array_map(fn($r) => [
        'page'                 => $r['page'],
        'referrer'             => $r['referrer'],
        'ip_masked'            => maskIp($r['ip']),
        'country'              => $r['country'] ?: 'Unknown',
        'flag'                 => $r['country_code'] ? flagFromCode($r['country_code']) : '🌐',
        'user_agent'           => $r['user_agent'],
        'created_at'           => date('d M H:i', strtotime($r['created_at'])),
        'created_at_relative'  => humanTime(strtotime($r['created_at'])),
    ], $rows)]);
}

/* ═══════════════════════════════════════════════════
   ENDPOINT: ADMIN
═══════════════════════════════════════════════════ */
function adminGetDashboard(PDO $pdo): void {
    $totalServers   = (int)$pdo->query("SELECT COUNT(*) FROM servers WHERE status = 'active'")->fetchColumn();
    $pendingServers = (int)$pdo->query("SELECT COUNT(*) FROM servers WHERE status = 'pending'")->fetchColumn();
    $totalUsers     = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
    $bannedUsers    = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE is_banned = 1")->fetchColumn();
    $totalReports   = (int)$pdo->query("SELECT COUNT(*) FROM server_reports")->fetchColumn();
    $totalMembers   = (int)$pdo->query("SELECT COALESCE(SUM(members),0) FROM servers WHERE status = 'active'")->fetchColumn();

    jsonResponse([
        'success' => true,
        'stats' => [
            'total_servers'   => $totalServers,
            'pending_servers' => $pendingServers,
            'total_users'     => $totalUsers,
            'banned_users'    => $bannedUsers,
            'total_reports'   => $totalReports,
            'total_members'   => $totalMembers,
        ],
    ]);
}

function adminGetPendingServers(PDO $pdo): void {
    $stmt = $pdo->prepare('
        SELECT s.*, u.full_name AS owner_name, u.email AS owner_email
        FROM servers s
        JOIN users u ON u.id = s.owner_id
        WHERE s.status = "pending"
        ORDER BY s.created_at DESC
    ');
    $stmt->execute();
    $servers = $stmt->fetchAll();

    jsonResponse([
        'success' => true,
        'servers' => array_map(fn($s) => [
            'id'          => $s['id'],
            'name'        => $s['name'],
            'avatar'      => $s['avatar'] ?: '📡',
            'description' => $s['description'],
            'telegram'    => $s['telegram_link'],
            'members'     => (int)$s['members'],
            'tags'        => $s['tags'],
            'language'    => $s['language'],
            'created_at'  => date('d M Y H:i', strtotime($s['created_at'])),
            'owner'       => [
                'name'  => $s['owner_name'],
                'email' => $s['owner_email'],
            ],
        ], $servers),
    ]);
}

function adminApproveServer(PDO $pdo, array $body): void {
    $id = (int)($body['server_id'] ?? 0);
    $stmt = $pdo->prepare('SELECT * FROM servers WHERE id = ? AND status = "pending"');
    $stmt->execute([$id]);
    $server = $stmt->fetch();
    if (!$server) jsonResponse(['success' => false, 'message' => 'Server tidak ditemukan.']);

    $pdo->prepare('UPDATE servers SET status = "active" WHERE id = ?')->execute([$id]);

    $owner = $pdo->prepare('SELECT email, full_name FROM users WHERE id = ?');
    $owner->execute([$server['owner_id']]);
    $o = $owner->fetch();
    if ($o) {
        sendMail($o['email'], 'Server Approved! 🎉', "
            <h3>Halo {$o['full_name']}!</h3>
            <p>Selamat! Server <b>\"{$server['name']}\"</b> kamu telah disetujui dan sekarang aktif di TeleBoard.</p>
            <p>Link: {$server['telegram_link']}</p>
            <p>Terima kasih telah menggunakan TeleBoard!</p>
        ");
    }

    jsonResponse(['success' => true, 'message' => 'Server berhasil disetujui.']);
}

function adminRejectServer(PDO $pdo, array $body): void {
    $id     = (int)($body['server_id'] ?? 0);
    $reason = trim($body['reason'] ?? 'Tidak sesuai dengan kebijakan TeleBoard');

    $stmt = $pdo->prepare('SELECT * FROM servers WHERE id = ? AND status = "pending"');
    $stmt->execute([$id]);
    $server = $stmt->fetch();
    if (!$server) jsonResponse(['success' => false, 'message' => 'Server tidak ditemukan.']);

    $pdo->prepare('UPDATE servers SET status = "rejected", reject_reason = ? WHERE id = ?')
        ->execute([$reason, $id]);

    $owner = $pdo->prepare('SELECT email, full_name FROM users WHERE id = ?');
    $owner->execute([$server['owner_id']]);
    $o = $owner->fetch();
    if ($o) {
        sendMail($o['email'], 'Server Ditolak', "
            <h3>Halo {$o['full_name']}!</h3>
            <p>Server <b>\"{$server['name']}\"</b> kamu ditolak dengan alasan:</p>
            <p><b>$reason</b></p>
            <p>Jika ada pertanyaan, silakan hubungi tim support kami.</p>
        ");
    }

    jsonResponse(['success' => true, 'message' => 'Server berhasil ditolak.']);
}

function adminGetAllUsers(PDO $pdo): void {
    $page    = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 20;
    $offset  = ($page - 1) * $perPage;

    $total = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
    $stmt  = $pdo->prepare('
        SELECT id, full_name, tg_username, email, avatar, is_banned, created_at
        FROM users
        WHERE role = "user"
        ORDER BY created_at DESC
        LIMIT ? OFFSET ?
    ');
    $stmt->execute([$perPage, $offset]);
    $users = $stmt->fetchAll();

    jsonResponse([
        'success'  => true,
        'total'    => $total,
        'page'     => $page,
        'per_page' => $perPage,
        'users'    => array_map(fn($u) => [
            'id'        => $u['id'],
            'name'      => $u['full_name'],
            'username'  => $u['tg_username'],
            'email'     => $u['email'],
            'avatar'    => $u['avatar'],
            'is_banned' => (bool)$u['is_banned'],
            'joined'    => date('d M Y', strtotime($u['created_at'])),
        ], $users),
    ]);
}

function adminBanUser(PDO $pdo, array $body): void {
    $id = (int)($body['user_id'] ?? 0);
    $pdo->prepare('UPDATE users SET is_banned = 1 WHERE id = ? AND role = "user"')->execute([$id]);
    jsonResponse(['success' => true, 'message' => 'User berhasil di-ban.']);
}

function adminUnbanUser(PDO $pdo, array $body): void {
    $id = (int)($body['user_id'] ?? 0);
    $pdo->prepare('UPDATE users SET is_banned = 0 WHERE id = ?')->execute([$id]);
    jsonResponse(['success' => true, 'message' => 'User berhasil di-unban.']);
}

function adminGetReports(PDO $pdo): void {
    $page    = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 20;
    $offset  = ($page - 1) * $perPage;

    $total = (int)$pdo->query("SELECT COUNT(*) FROM server_reports")->fetchColumn();
    $stmt  = $pdo->prepare('
        SELECT r.*, s.name AS server_name, u.full_name AS reporter_name
        FROM server_reports r
        JOIN servers s ON s.id = r.server_id
        JOIN users u ON u.id = r.user_id
        ORDER BY r.created_at DESC
        LIMIT ? OFFSET ?
    ');
    $stmt->execute([$perPage, $offset]);
    $reports = $stmt->fetchAll();

    jsonResponse([
        'success'  => true,
        'total'    => $total,
        'page'     => $page,
        'per_page' => $perPage,
        'reports'  => array_map(fn($r) => [
            'id'        => $r['id'],
            'server_id' => $r['server_id'],
            'server'    => $r['server_name'],
            'reporter'  => $r['reporter_name'],
            'reason'    => $r['reason'],
            'detail'    => $r['detail'],
            'date'      => date('d M Y H:i', strtotime($r['created_at'])),
        ], $reports),
    ]);
}

function adminDeleteServer(PDO $pdo, array $body): void {
    $id = (int)($body['server_id'] ?? 0);
    $stmt = $pdo->prepare('SELECT * FROM servers WHERE id = ?');
    $stmt->execute([$id]);
    $server = $stmt->fetch();
    if (!$server) jsonResponse(['success' => false, 'message' => 'Server tidak ditemukan.']);

    $pdo->prepare('DELETE FROM servers WHERE id = ?')->execute([$id]);

    $owner = $pdo->prepare('SELECT email, full_name FROM users WHERE id = ?');
    $owner->execute([$server['owner_id']]);
    $o = $owner->fetch();
    if ($o) {
        sendMail($o['email'], 'Server Dihapus', "
            <h3>Halo {$o['full_name']}!</h3>
            <p>Server <b>\"{$server['name']}\"</b> kamu telah dihapus dari TeleBoard.</p>
            <p>Jika ada pertanyaan atau keberatan, silakan hubungi tim support kami.</p>
        ");
    }

    jsonResponse(['success' => true, 'message' => 'Server berhasil dihapus.']);
}

/* ═══════════════════════════════════════════════════
   HELPERS
═══════════════════════════════════════════════════ */
function publicUser(array $u): array {
    return [
        'id'          => $u['id'],
        'tg_username' => $u['tg_username'],
        'full_name'   => $u['full_name'],
        'email'       => $u['email'],
        'bio'         => $u['bio'] ?? '',
        'avatar'      => $u['avatar'] ?? null,
        'tg_link'     => $u['tg_link'] ?? '',
    ];
}

function humanTime(int $ts): string {
    $diff = time() - $ts;
    if ($diff < 60)     return 'Baru saja';
    if ($diff < 3600)   return (int)($diff / 60) . ' menit lalu';
    if ($diff < 86400)  return (int)($diff / 3600) . ' jam lalu';
    if ($diff < 604800) return (int)($diff / 86400) . ' hari lalu';
    return date('d M Y', $ts);
}

function jsonResponse(array $data, int $code = 200): never {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function sendMail(string $to, string $subject, string $html): void {
    $host = MAIL_HOST;
    $port = MAIL_PORT;
    $user = MAIL_USER;
    $pass = MAIL_PASS;
    $from = MAIL_FROM;
    $name = MAIL_NAME;

    $errno = 0; $errstr = '';
    $sock = @fsockopen($host, $port, $errno, $errstr, 10);
    if (!$sock) {
        $sock = @fsockopen('ssl://' . $host, 465, $errno, $errstr, 10);
        if (!$sock) return;
    }

    $boundary = md5(uniqid());

    $read = function() use ($sock): string {
        $res = '';
        while ($line = fgets($sock, 515)) {
            $res .= $line;
            if (substr($line, 3, 1) === ' ') break;
        }
        return $res;
    };

    $send = function(string $cmd) use ($sock, $read): string {
        fwrite($sock, $cmd . "\r\n");
        return $read();
    };

    $read();
    $ehlo = $send('EHLO ' . gethostname());
    if (strpos($ehlo, 'STARTTLS') !== false) {
        $send('STARTTLS');
        stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        $send('EHLO ' . gethostname());
    }

    $send('AUTH LOGIN');
    $send(base64_encode($user));
    $send(base64_encode($pass));
    $send('MAIL FROM:<' . $from . '>');
    $send('RCPT TO:<' . $to . '>');
    $send('DATA');

    $msg  = 'From: =?UTF-8?B?' . base64_encode($name) . '?= <' . $from . '>' . "\r\n";
    $msg .= 'To: ' . $to . "\r\n";
    $msg .= 'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=' . "\r\n";
    $msg .= 'MIME-Version: 1.0' . "\r\n";
    $msg .= 'Content-Type: multipart/alternative; boundary="' . $boundary . '"' . "\r\n\r\n";
    $msg .= '--' . $boundary . "\r\n";
    $msg .= 'Content-Type: text/plain; charset=UTF-8' . "\r\n\r\n";
    $msg .= strip_tags($html) . "\r\n";
    $msg .= '--' . $boundary . "\r\n";
    $msg .= 'Content-Type: text/html; charset=UTF-8' . "\r\n\r\n";
    $msg .= $html . "\r\n";
    $msg .= '--' . $boundary . '--' . "\r\n.";

    $send($msg);
    $send('QUIT');
    fclose($sock);
}
