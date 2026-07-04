<?php
// includes/visitor_counter.php
//
// Menghitung TOTAL PENGUNJUNG (unik, all-time) berbasis IP address.
// Disimpan di file JSON (bukan database), dengan file locking supaya aman
// kalau ada beberapa request masuk bersamaan (concurrent).
//
// PENTING soal lokasi include:
// File ini idealnya di-require SEKALI per page-load, dan paling baik
// ditaruh di includes/header.php (bukan di index.php saja), supaya
// terhitung di SEMUA halaman situs -- bukan cuma halaman beranda.
// Kalau cuma mau menghitung kunjungan ke index.php saja, require di
// index.php juga tidak masalah.
//
// IP disimpan APA ADANYA (tidak di-hash) karena folder data/ sudah
// diblokir total dari akses langsung via .htaccess (RewriteRule ^data/ - [F,L]),
// jadi file ini tidak bisa dibuka siapapun lewat browser.

$visitorDataPath = __DIR__ . '/../data/visitors.json';
$visitorDataDir = dirname($visitorDataPath);

if (!is_dir($visitorDataDir)) {
    @mkdir($visitorDataDir, 0775, true);
}

function trackVisitor(string $path): int
{
    $fp = @fopen($path, 'c+');
    if (!$fp) {
        return 0; // gagal buka/tulis file -> jangan bikin halaman error, cukup return 0
    }

    flock($fp, LOCK_EX); // exclusive lock: cegah race condition antar request

    $raw = stream_get_contents($fp);
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        $data = ['total' => 0, 'ips' => []];
    }
    if (!isset($data['total'])) {
        $data['total'] = 0;
    }
    if (!isset($data['ips']) || !is_array($data['ips'])) {
        $data['ips'] = [];
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    // Hanya nambah counter kalau IP ini belum pernah tercatat sama sekali.
    if (!isset($data['ips'][$ip])) {
        $data['ips'][$ip] = time();
        $data['total']++;
    }

    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($data));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);

    return $data['total'];
}

$totalVisitors = trackVisitor($visitorDataPath);
