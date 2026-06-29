<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$id = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';
if ($id && in_array($action, ['approve','reject','delete'])) {
    if ($action === 'delete') {
        $pdo->prepare("DELETE FROM articles WHERE id = ?")->execute([$id]);
        flash('success', 'Artikel berhasil dihapus.');
    } else {
        $status = $action === 'approve' ? 'approved' : 'rejected';
        $pdo->prepare("UPDATE articles SET status = ? WHERE id = ?")->execute([$status, $id]);
        flash('success', 'Status artikel berhasil diperbarui.');
    }
}
redirect('admin/articles.php');
