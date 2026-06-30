<?php
/**
 * SITEMAP GENERATOR - TeleHub
 * Generate sitemap.xml secara dinamis dari database (cards + articles)
 * supaya selalu up-to-date tanpa perlu update manual.
 *
 * Cara akses: https://telehub.nfy.fyi/sitemap.xml
 * (lihat instruksi rewrite rule di bagian bawah file ini)
 */

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config.php';

header('Content-Type: application/xml; charset=utf-8');

// Pastikan error PDO bisa ketangkep dengan benar oleh try/catch
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$baseUrl = rtrim(SITE_URL, '/');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// ──────────────────────────────────────────────
// HALAMAN STATIS UTAMA
// ──────────────────────────────────────────────
$staticPages = [
    ['loc' => '/index.php',       'priority' => '1.0', 'changefreq' => 'daily'],
    ['loc' => '/cards.php',       'priority' => '0.9', 'changefreq' => 'daily'],
    ['loc' => '/articles.php',    'priority' => '0.9', 'changefreq' => 'daily'],
    ['loc' => '/register.php',    'priority' => '0.7', 'changefreq' => 'monthly'],
    ['loc' => '/submit-article.php', 'priority' => '0.6', 'changefreq' => 'monthly'],
];

foreach ($staticPages as $page) {
    echo "  <url>\n";
    echo '    <loc>' . htmlspecialchars($baseUrl . $page['loc']) . "</loc>\n";
    echo '    <changefreq>' . $page['changefreq'] . "</changefreq>\n";
    echo '    <priority>' . $page['priority'] . "</priority>\n";
    echo "  </url>\n";
}

// ──────────────────────────────────────────────
// CARD YANG SUDAH APPROVED
// Pakai SELECT * supaya tidak gagal kalau nama kolom beda;
// link dibuat ke /cards.php (halaman listing) per default karena
// tabel ini tidak punya halaman detail individual (langsung link ke Telegram).
// Kalau kamu punya halaman detail card sendiri, kasih tau strukturnya
// biar bagian ini bisa disesuaikan untuk link per-card.
// ──────────────────────────────────────────────
try {
    $stmt = $pdo->query("
        SELECT *
        FROM card_submissions
        WHERE status = 'approved'
        ORDER BY created_at DESC
    ");
    $cards = $stmt ? $stmt->fetchAll() : [];
} catch (\Throwable $e) {
    $cards = [];
}
// Catatan: card individual belum punya halaman detail (slug-based),
// jadi tidak ditambahkan ke sitemap satu-satu.
// (Lihat komentar di atas kalau mau aktifkan per-card URL nanti.)

// ──────────────────────────────────────────────
// ARTIKEL YANG SUDAH APPROVED
// ──────────────────────────────────────────────
try {
    $stmt = $pdo->query("
        SELECT slug, updated_at, created_at
        FROM articles
        WHERE status = 'approved'
        ORDER BY created_at DESC
    ");
    $articles = $stmt ? $stmt->fetchAll() : [];

    foreach ($articles as $article) {
        if (empty($article['slug'])) {
            continue;
        }
        $lastmod = !empty($article['updated_at'])
            ? date('Y-m-d', strtotime($article['updated_at']))
            : date('Y-m-d', strtotime($article['created_at']));

        echo "  <url>\n";
        echo '    <loc>' . htmlspecialchars($baseUrl . '/article.php?slug=' . urlencode($article['slug'])) . "</loc>\n";
        echo '    <lastmod>' . $lastmod . "</lastmod>\n";
        echo "    <changefreq>monthly</changefreq>\n";
        echo "    <priority>0.6</priority>\n";
        echo "  </url>\n";
    }
} catch (\Throwable $e) {
    // Sesuaikan query kalau struktur tabel articles kamu beda.
}

echo '</urlset>';
