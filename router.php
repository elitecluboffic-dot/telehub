<?php
/**
 * router.php – dipakai oleh PHP built-in server (Railway)
 * Menangani static files & routing ke index.html
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve file static kalau ada (js, css, gambar, html, dll)
$filePath = __DIR__ . $uri;
if ($uri !== '/' && file_exists($filePath) && !is_dir($filePath)) {
    return false; // biarkan PHP built-in server handle sendiri
}

// Untuk route yang tidak ada file-nya, serve index.html
$indexFile = __DIR__ . '/index.html';
if (file_exists($indexFile)) {
    header('Content-Type: text/html; charset=utf-8');
    readfile($indexFile);
    exit;
}

http_response_code(404);
echo '404 Not Found';
