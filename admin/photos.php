<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    header('Location: ' . SITE_URL . '/admin/includes/login.php');
    exit;
}

/**
 * ADMIN_TOKEN ini HARUS sama persis dengan yang kamu set di Worker
 * lewat: wrangler secret put ADMIN_TOKEN
 * Taruh di file config yang tidak ke-expose publik (mis. admin/config-worker.php,
 * di luar folder yang bisa diakses browser), lalu require di sini.
 * Untuk sekarang aku taruh langsung di sini biar gampang — GANTI sebelum production.
 */
const WORKER_ADMIN_TOKEN = 'd0d942c6492c409ba28cc5901a59dee1827a15740bd096bdd73e47e8b1a8ce55';
const WORKER_API_BASE = 'https://api.telehub.web.id';

$pageTitle = 'Kelola Foto';
require __DIR__ . '/includes/admin_header.php';
?>

<style>
.photo-admin-tabs{display:flex;gap:8px;margin:20px 0;border-bottom:1px solid rgba(255,255,255,.12)}
.photo-admin-tab{padding:10px 16px;font-size:14px;color:rgba(255,255,255,.55);cursor:pointer;border-bottom:2px solid transparent;user-select:none;transition:color .15s}
.photo-admin-tab:hover{color:rgba(255,255,255,.85)}
.photo-admin-tab.active{color:#fff;border-color:#7c9cff;font-weight:600}
.photo-admin-tab .n{font-weight:700;margin-left:4px;color:inherit}
.photo-admin-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;margin-top:16px}
.photo-admin-card{background:#1c1d22;border:1px solid rgba(255,255,255,.1);border-radius:10px;overflow:hidden}
.photo-admin-card img{width:100%;height:170px;object-fit:cover;display:block;background:#000;cursor:zoom-in;transition:opacity .15s}
.photo-admin-card img:hover{opacity:.85}
.photo-admin-body{padding:12px}
.photo-admin-body .t{font-size:13px;font-weight:600;margin-bottom:2px;color:#f2f2f2}
.photo-admin-body .u{font-size:11px;color:rgba(255,255,255,.5);font-family:monospace}
.photo-admin-body .d{font-size:11px;color:rgba(255,255,255,.35);margin-top:4px}
.photo-admin-badge{display:inline-block;font-size:10px;padding:2px 8px;border-radius:999px;margin-bottom:8px;font-weight:600}
.photo-admin-badge.pending{background:rgba(255,193,7,.18);color:#ffcf4d}
.photo-admin-badge.approved{background:rgba(13,122,69,.25);color:#4ade80}
.photo-admin-badge.rejected{background:rgba(179,38,30,.25);color:#f87171}
.photo-admin-actions{display:flex;gap:6px;margin-top:10px}
.photo-admin-actions button{flex:1;border:none;border-radius:6px;padding:8px 0;font-size:12px;font-weight:600;cursor:pointer;transition:filter .15s}
.photo-admin-actions button:hover{filter:brightness(1.15)}
.photo-admin-actions .approve{background:#0d7a45;color:#fff}
.photo-admin-actions .reject{background:transparent;color:#f87171;border:1px solid rgba(248,113,113,.4)}
.photo-admin-actions .delete{background:transparent;color:rgba(255,255,255,.6);border:1px solid rgba(255,255,255,.2)}
.photo-admin-empty{color:rgba(255,255,255,.4);padding:50px 0;text-align:center;font-size:14px}

/* ==== Preview Modal (Lightbox) ==== */
.photo-preview-overlay{
  display:none;position:fixed;inset:0;background:rgba(0,0,0,.85);
  z-index:9999;align-items:center;justify-content:center;padding:30px;
  cursor:zoom-out;
}
.photo-preview-overlay.open{display:flex}
.photo-preview-inner{
  position:relative;max-width:90vw;max-height:90vh;
  display:flex;flex-direction:column;align-items:center;cursor:default;
}
.photo-preview-inner img{
  max-width:90vw;max-height:80vh;object-fit:contain;
  border-radius:8px;background:#000;box-shadow:0 10px 40px rgba(0,0,0,.5);
}
.photo-preview-caption{
  color:#eee;font-size:13px;margin-top:12px;text-align:center;max-width:600px;
}
.photo-preview-caption .t{font-weight:600;font-size:14px;margin-bottom:4px}
.photo-preview-caption .u{color:#aaa;font-family:monospace;font-size:11px}
.photo-preview-close{
  position:absolute;top:-18px;right:-18px;width:36px;height:36px;border-radius:50%;
  background:#fff;color:#111;border:none;font-size:18px;font-weight:700;cursor:pointer;
  display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,.3);
}
.photo-preview-nav{
  position:absolute;top:50%;transform:translateY(-50%);
  background:rgba(255,255,255,.15);border:none;color:#fff;font-size:22px;
  width:44px;height:44px;border-radius:50%;cursor:pointer;
  display:flex;align-items:center;justify-content:center;
}
.photo-preview-nav:hover{background:rgba(255,255,255,.3)}
.photo-preview-nav.prev{left:-60px}
.photo-preview-nav.next{right:-60px}
@media (max-width:768px){
  .photo-preview-nav.prev{left:6px}
  .photo-preview-nav.next{right:6px}
  .photo-preview-close{top:6px;right:6px}
}
</style>

<h1>Kelola Foto</h1>
<p style="color:#777;font-size:14px">Setujui atau tolak foto sebelum tampil di halaman publik.</p>

<div class="photo-admin-tabs">
  <div class="photo-admin-tab active" data-status="pending">Menunggu <span class="n" id="cnt-pending">0</span></div>
  <div class="photo-admin-tab" data-status="approved">Disetujui <span class="n" id="cnt-approved">0</span></div>
  <div class="photo-admin-tab" data-status="rejected">Ditolak <span class="n" id="cnt-rejected">0</span></div>
</div>

<div class="photo-admin-grid" id="photoGrid"></div>
<div class="photo-admin-empty" id="photoEmpty" style="display:none">Tidak ada foto di kategori ini.</div>

<!-- Preview Modal -->
<div class="photo-preview-overlay" id="previewOverlay">
  <div class="photo-preview-inner" id="previewInner">
    <button class="photo-preview-close" id="previewClose" title="Tutup (Esc)">&times;</button>
    <button class="photo-preview-nav prev" id="previewPrev" title="Sebelumnya">&#8249;</button>
    <img id="previewImg" src="" alt="">
    <div class="photo-preview-caption">
      <div class="t" id="previewTitle"></div>
      <div class="u" id="previewMeta"></div>
    </div>
    <button class="photo-preview-nav next" id="previewNext" title="Berikutnya">&#8250;</button>
  </div>
</div>

<script>
// Token ini di-render server-side, hanya sampai ke browser admin yang sudah login PHP.
const API_BASE = <?= json_encode(WORKER_API_BASE) ?>;
const ADMIN_TOKEN = <?= json_encode(WORKER_ADMIN_TOKEN) ?>;
let currentStatus = 'pending';
let currentPhotos = [];   // daftar foto yang sedang ditampilkan (untuk navigasi preview)
let previewIndex = -1;

function authHeaders(){
  return { 'X-Admin-Token': ADMIN_TOKEN };
}

async function loadPhotos(status){
  currentStatus = status;
  document.querySelectorAll('.photo-admin-tab').forEach(t => t.classList.toggle('active', t.dataset.status === status));
  const grid = document.getElementById('photoGrid');
  const empty = document.getElementById('photoEmpty');
  grid.innerHTML = '';
  const res = await fetch(`${API_BASE}/api/admin/photos?status=${status}`, { headers: authHeaders() });
  const data = await res.json();
  currentPhotos = data.photos || [];
  if (!currentPhotos.length){
    empty.style.display = 'block';
    return;
  }
  empty.style.display = 'none';
  currentPhotos.forEach((p, idx) => grid.appendChild(renderCard(p, idx)));
}

function renderCard(p, idx){
  const div = document.createElement('div');
  div.className = 'photo-admin-card';
  const badgeLabel = { pending: 'Menunggu', approved: 'Disetujui', rejected: 'Ditolak' }[p.status];
  div.innerHTML = `
    <img src="${API_BASE}/photos/${p.filename}" alt="" data-idx="${idx}">
    <div class="photo-admin-body">
      <span class="photo-admin-badge ${p.status}">${badgeLabel}</span>
      <div class="t">${p.title ? p.title : 'Tanpa judul'}</div>
      <div class="u">${p.uploader_name ? '@'+p.uploader_name : 'anonim'} · ${p.ip || '-'}</div>
      <div class="d">${p.uploaded_at}</div>
      <div class="photo-admin-actions"></div>
    </div>`;

  // klik gambar untuk preview fullscreen
  div.querySelector('img').addEventListener('click', () => openPreview(idx));

  const actions = div.querySelector('.photo-admin-actions');
  if (p.status !== 'approved') actions.appendChild(makeBtn('Setujui', 'approve', () => doAction(p.id, 'approve')));
  if (p.status !== 'rejected') actions.appendChild(makeBtn('Tolak', 'reject', () => doAction(p.id, 'reject')));
  actions.appendChild(makeBtn('Hapus', 'delete', () => doAction(p.id, 'delete')));
  return div;
}

function makeBtn(label, cls, onClick){
  const b = document.createElement('button');
  b.className = cls;
  b.textContent = label;
  b.addEventListener('click', onClick);
  return b;
}

async function doAction(id, action){
  if (action === 'delete' && !confirm('Hapus foto ini permanen?')) return;
  const res = await fetch(`${API_BASE}/api/admin/photos/${id}/${action}`, { method: 'POST', headers: authHeaders() });
  const data = await res.json();
  if (data.ok){
    refreshCounts();
    loadPhotos(currentStatus);
  } else {
    alert(data.error || 'Gagal memproses.');
  }
}

async function refreshCounts(){
  const res = await fetch(`${API_BASE}/api/admin/counts`, { headers: authHeaders() });
  const data = await res.json();
  if (data.counts){
    document.getElementById('cnt-pending').textContent = data.counts.pending || 0;
    document.getElementById('cnt-approved').textContent = data.counts.approved || 0;
    document.getElementById('cnt-rejected').textContent = data.counts.rejected || 0;
  }
}

/* ===== Preview Modal Logic ===== */
const previewOverlay = document.getElementById('previewOverlay');
const previewImg     = document.getElementById('previewImg');
const previewTitle   = document.getElementById('previewTitle');
const previewMeta    = document.getElementById('previewMeta');
const previewClose   = document.getElementById('previewClose');
const previewPrev    = document.getElementById('previewPrev');
const previewNext    = document.getElementById('previewNext');
const previewInner   = document.getElementById('previewInner');

function openPreview(idx){
  if (!currentPhotos.length) return;
  previewIndex = idx;
  renderPreview();
  previewOverlay.classList.add('open');
}

function renderPreview(){
  const p = currentPhotos[previewIndex];
  if (!p) return;
  previewImg.src = `${API_BASE}/photos/${p.filename}`;
  previewTitle.textContent = p.title ? p.title : 'Tanpa judul';
  previewMeta.textContent = `${p.uploader_name ? '@'+p.uploader_name : 'anonim'} · ${p.ip || '-'} · ${p.uploaded_at}`;
  previewPrev.style.display = currentPhotos.length > 1 ? 'flex' : 'none';
  previewNext.style.display = currentPhotos.length > 1 ? 'flex' : 'none';
}

function closePreview(){
  previewOverlay.classList.remove('open');
  previewImg.src = '';
  previewIndex = -1;
}

function showPrev(){
  if (!currentPhotos.length) return;
  previewIndex = (previewIndex - 1 + currentPhotos.length) % currentPhotos.length;
  renderPreview();
}

function showNext(){
  if (!currentPhotos.length) return;
  previewIndex = (previewIndex + 1) % currentPhotos.length;
  renderPreview();
}

previewClose.addEventListener('click', closePreview);
previewPrev.addEventListener('click', showPrev);
previewNext.addEventListener('click', showNext);

// klik di luar gambar (area gelap) menutup modal
previewOverlay.addEventListener('click', (e) => {
  if (e.target === previewOverlay) closePreview();
});

// dukungan keyboard: Esc untuk tutup, panah kiri/kanan untuk navigasi
document.addEventListener('keydown', (e) => {
  if (!previewOverlay.classList.contains('open')) return;
  if (e.key === 'Escape') closePreview();
  if (e.key === 'ArrowLeft') showPrev();
  if (e.key === 'ArrowRight') showNext();
});

document.querySelectorAll('.photo-admin-tab').forEach(t => t.addEventListener('click', () => loadPhotos(t.dataset.status)));
refreshCounts();
loadPhotos('pending');
</script>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
