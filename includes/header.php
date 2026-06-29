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
