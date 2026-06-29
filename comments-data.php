<?php
require_once __DIR__ . '/includes/functions.php';

$perPage = 10; // HARUS sama dengan nilai $perPage di comments.php

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

include __DIR__ . '/comments-render.php';
