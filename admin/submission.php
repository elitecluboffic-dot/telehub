<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($id && in_array($action, ['approve','reject','delete'])) {
    if ($action === 'delete') {
        $stmt = $pdo->prepare("SELECT image_path FROM card_submissions WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row && $row['image_path'] && file_exists(UPLOAD_DIR . $row['image_path'])) {
            unlink(UPLOAD_DIR . $row['image_path']);
        }
        $pdo->prepare("DELETE FROM card_submissions WHERE id = ?")->execute([$id]);
        flash('success', 'Card berhasil dihapus.');
    } else {
        $status = $action === 'approve' ? 'approved' : 'rejected';
        $pdo->prepare("UPDATE card_submissions SET status = ? WHERE id = ?")->execute([$status, $id]);
        flash('success', 'Status card berhasil diperbarui.');
    }
}

redirect('admin/dashboard.php');
