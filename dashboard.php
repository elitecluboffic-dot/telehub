<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();
$pageTitle = 'Dashboard';
$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $name = clean($_POST['name'] ?? '');
    $telegram_link = clean($_POST['telegram_link'] ?? '');
    $member_count = clean($_POST['member_count'] ?? '');
    $category = clean($_POST['category'] ?? '');
    $tags = clean($_POST['tags'] ?? '');
    $description = clean($_POST['description'] ?? '');
    $theme_color = clean($_POST['theme_color'] ?? '#2AABEE');

    if (!in_array($type, ['channel','group','user']) || !$name || !$telegram_link) {
        flash('error', 'Tipe, nama, dan link Telegram wajib diisi.');
    } else {
        $imagePath = uploadCardImage('image');
        $stmt = $pdo->prepare("INSERT INTO card_submissions
            (user_id, type, name, telegram_link, member_count, category, tags, description, image_path, theme_color)
            VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$user['id'], $type, $name, $telegram_link, $member_count, $category, $tags, $description, $imagePath, $theme_color]);
        flash('success', 'Card berhasil dikirim! Menunggu persetujuan admin.');
        redirect('dashboard.php');
    }
}

$stmt = $pdo->prepare("SELECT * FROM card_submissions WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user['id']]);
$mySubmissions = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="dash-wrap">
  <?php if ($e = flash('error')): ?><div class="alert alert-error"><?= clean($e) ?></div><?php endif; ?>
  <?php if ($s = flash('success')): ?><div class="alert alert-success"><?= clean($s) ?></div><?php endif; ?>

  <div class="dash-card">
    <h3>Buat Custom Card Baru</h3>
    <form method="post" enctype="multipart/form-data">
      <div class="form-row">
        <div class="form-group">
          <label>Tipe</label>
          <select name="type" required>
            <option value="channel">Channel</option>
            <option value="group">Group</option>
            <option value="user">User</option>
          </select>
        </div>
        <div class="form-group">
          <label>Jumlah Member (opsional)</label>
          <input type="text" name="member_count" placeholder="cth: 1.2K">
        </div>
      </div>

      <div class="form-group">
        <label>Nama Channel/Grup/User</label>
        <input type="text" name="name" required placeholder="cth: Komunitas Belajar Coding">
      </div>

      <div class="form-group">
        <label>Link Telegram</label>
        <input type="url" name="telegram_link" required placeholder="https://t.me/namakamu">
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Kategori</label>
          <input type="text" name="category" placeholder="cth: Edukasi, Jual Beli, Gaming">
        </div>
        <div class="form-group">
          <label>Tags (pisahkan dengan koma)</label>
          <input type="text" name="tags" placeholder="cth: coding, belajar, gratis">
        </div>
      </div>

      <div class="form-group">
        <label>Deskripsi</label>
        <textarea name="description" rows="4" placeholder="Ceritakan tentang channel/grup/user kamu..."></textarea>
      </div>

      <div class="form-group">
        <label>Foto / Logo (jpg, png, webp, max 3MB)</label>
        <input type="file" name="image" accept="image/*">
      </div>

      <div class="form-group">
        <label>Warna Tema Card</label>
        <div class="color-options">
          <?php foreach (['#2AABEE'=>'Biru','#8e44ad'=>'Ungu','#27ae60'=>'Hijau','#e67e22'=>'Oranye','#e35d6a'=>'Merah'] as $hex=>$nm): ?>
            <label>
              <input type="radio" name="theme_color" value="<?= $hex ?>" style="display:none" <?= $hex=='#2AABEE'?'checked':'' ?> onclick="document.querySelectorAll('.color-dot').forEach(d=>d.classList.remove('active'));this.parentElement.classList.add('active')">
              <span class="color-dot" style="background:<?= $hex ?>" title="<?= $nm ?>" onclick="this.previousElementSibling.click()"></span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-block">Kirim Card untuk Direview</button>
    </form>
  </div>

  <div class="dash-card">
    <h3>Card Saya</h3>
    <?php if (empty($mySubmissions)): ?>
      <p style="color:var(--text-dim)">Kamu belum membuat card apapun.</p>
    <?php else: ?>
      <table class="simple">
        <tr><th>Nama</th><th>Tipe</th><th>Status</th><th>Dikirim</th></tr>
        <?php foreach ($mySubmissions as $s): ?>
          <tr>
            <td><?= clean($s['name']) ?></td>
            <td><?= clean($s['type']) ?></td>
            <td><span class="status-pill status-<?= $s['status'] ?>"><?= clean($s['status']) ?></span></td>
            <td><?= date('d M Y', strtotime($s['created_at'])) ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>