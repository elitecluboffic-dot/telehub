<?php
require_once __DIR__ . '/../../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? clean($pageTitle) . ' - Admin' : 'Admin Panel' ?></title>
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="navbar container">
  <a href="<?= SITE_URL ?>/admin/dashboard.php" class="brand">
    <span class="brand-icon">⚙️</span> Admin Panel
  </a>
  <nav>
    <a href="<?= SITE_URL ?>/admin/articles.php">Artikel</a>
    <a href="<?= SITE_URL ?>/admin/logout.php" class="btn btn-sm" style="background:linear-gradient(90deg,#ff0000,#ff7700,#ffff00,#00ff00,#0000ff,#8b00ff);color:white;border:none">Logout</a>
  </nav>
</div>
<div class="container">
