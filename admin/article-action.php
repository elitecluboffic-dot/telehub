<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
$id = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';
if ($id && in_array($action, ['approve','reject','delete'])) {
    if ($action === 'delete') {
        $pdo->prepare("DELETE FROM articles WHERE id = ?")->execute([$id]);
        flash('success', 'Artikel berhasil dihapus.');
    } elseif ($action === 'approve') {
        // Set approved_at = waktu sekarang, hanya saat status BENAR-BENAR berubah jadi approved.
        // Ini yang jadi acuan tanggal tayang & urutan artikel di halaman publik,
        // bukan created_at (waktu submit).
        $pdo->prepare("UPDATE articles SET status = 'approved', approved_at = NOW() WHERE id = ?")
            ->execute([$id]);
        flash('success', 'Status artikel berhasil diperbarui.');
    } else {
        // Reject: approved_at dikosongkan lagi. Kalau suatu saat artikel ini
        // di-approve ulang, dia bakal dapat approved_at baru sesuai waktu approve yang baru.
        $pdo->prepare("UPDATE articles SET status = 'rejected', approved_at = NULL WHERE id = ?")
            ->execute([$id]);
        flash('success', 'Status artikel berhasil diperbarui.');
    }
}
redirect('admin/articles.php');
