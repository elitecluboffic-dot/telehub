<?php
require_once __DIR__ . '/functions.php';
$user = isLoggedIn() ? currentUser() : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? clean($pageTitle) . ' - ' . SITE_NAME : SITE_NAME ?></title>

<!-- SEO -->
<meta name="description" content="<?= isset($metaDesc) ? clean($metaDesc) : 'TeleCard - Direktori card custom untuk channel, grup & user Telegram.' ?>">
<meta name="keywords" content="<?= isset($metaKeywords) ? clean($metaKeywords) : 'telegram, channel, grup, direktori, telecard' ?>">
<meta name="robots" content="index, follow">
<link rel="canonical" href="<?= SITE_URL . strtok($_SERVER['REQUEST_URI'], '?') ?>">

<!-- Open Graph -->
<meta property="og:title" content="<?= isset($pageTitle) ? clean($pageTitle) . ' - ' . SITE_NAME : SITE_NAME ?>">
<meta property="og:description" content="<?= isset($metaDesc) ? clean($metaDesc) : 'TeleCard - Direktori card custom untuk channel, grup & user Telegram.' ?>">
<meta property="og:url" content="<?= SITE_URL . $_SERVER['REQUEST_URI'] ?>">
<meta property="og:type" content="website">
<meta property="og:image" content="<?= SITE_URL ?>/assets/img/og-image.png">
<meta property="og:site_name" content="<?= SITE_NAME ?>">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= isset($pageTitle) ? clean($pageTitle) . ' - ' . SITE_NAME : SITE_NAME ?>">
<meta name="twitter:description" content="<?= isset($metaDesc) ? clean($metaDesc) : 'TeleCard - Direktori card custom untuk channel, grup & user Telegram.' ?>">
<meta name="twitter:image" content="<?= SITE_URL ?>/assets/img/og-image.png">

<!-- Favicon -->
<link rel="icon" type="image/png" href="<?= SITE_URL ?>/assets/img/favicon.png">
<link rel="apple-touch-icon" href="<?= SITE_URL ?>/assets/img/favicon.png">

<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="navbar container">
  <a href="<?= SITE_URL ?>/index.php" class="brand">
    <span class="brand-icon">✈️</span> <?= SITE_NAME ?>
  </a>
  <nav>
    <a href="<?= SITE_URL ?>/index.php">Beranda</a>
    <a href="<?= SITE_URL ?>/cards.php">Jelajahi Card</a>
    <?php if ($user): ?>
      <a href="<?= SITE_URL ?>/dashboard.php">Dashboard</a>
      <a href="<?= SITE_URL ?>/logout.php" class="btn btn-outline btn-sm">Logout (<?= clean($user['username']) ?>)</a>
    <?php else: ?>
      <a href="<?= SITE_URL ?>/login.php" class="btn btn-outline btn-sm">Login</a>
      <a href="<?= SITE_URL ?>/register.php" class="btn btn-primary btn-sm">Daftar</a>
    <?php endif; ?>
  </nav>
</div>
<div class="container">
