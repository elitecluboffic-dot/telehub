<?php
// ============================
// KONFIGURASI WEBSITE TELECARD
// ============================
session_start();
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

// --- Database (MySQL) ---
define('DB_HOST', 'sql101.infinityfree.com');
define('DB_NAME', 'if0_42251940_telegram_telehub');
define('DB_USER', 'if0_42251940');
define('DB_PASS', 'Labibganteng11');

// --- Gmail SMTP (App Password) ---
// Buat App Password di: https://myaccount.google.com/apppasswords
define('GMAIL_EMAIL', 'elitecluboffic@gmail.com');
define('GMAIL_APP_PASSWORD', 'qdreohfwjdioffsk'); // 16 digit app password, tanpa spasi

// --- Umum ---
define('SITE_NAME', 'TeleCard');
define('SITE_URL', 'https://telehub.nfy.fyi'); // ganti sesuai domain/folder kamu
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');