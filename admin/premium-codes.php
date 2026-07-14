<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';

if (!isAdminLoggedIn()) {
    header('Location: ' . SITE_URL . '/admin/includes/login.php');
    exit;
}

/**
 * Pakai token & base URL Worker yang sama dengan admin/photos.php.
 * Kalau kamu udah pindahin WORKER_ADMIN_TOKEN ke file config terpisah
 * yang di-require di photos.php, require file yang sama di sini juga
 * biar tokennya konsisten satu sumber.
 */
const WORKER_ADMIN_TOKEN = 'd0d942c6492c409ba28cc5901a59dee1827a15740bd096bdd73e47e8b1a8ce55';
const WORKER_API_BASE = 'https://api.telehub.web.id';

$pageTitle = 'Kelola Kode Premium';
require __DIR__ . '/includes/admin_header.php';
?>

<style>
.prem-wrap{max-width:1000px}
.prem-info{background:rgba(124,156,255,.1);border:1px solid rgba(124,156,255,.25);border-radius:10px;padding:16px 18px;margin:18px 0;font-size:13.5px;color:#cdd6ff;line-height:1.6}
.prem-info b{color:#fff}
.prem-generate{display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;background:#1c1d22;border:1px solid rgba(255,255,255,.1);border-radius:10px;padding:18px;margin-bottom:22px}
.prem-generate .field{display:flex;flex-direction:column;gap:6px}
.prem-generate label{font-size:11.5px;color:rgba(255,255,255,.55);text-transform:uppercase;letter-spacing:.05em}
.prem-generate input{background:#101114;border:1px solid rgba(255,255,255,.15);color:#fff;padding:9px 12px;border-radius:6px;font-size:14px;width:140px}
.prem-generate button{background:#7c9cff;color:#111;border:none;padding:10px 20px;border-radius:6px;font-weight:700;font-size:13.5px;cursor:pointer}
.prem-generate button:hover{filter:brightness(1.08)}
.prem-generated-box{display:none;margin-top:14px;background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.3);border-radius:8px;padding:14px 16px}
.prem-generated-box.show{display:block}
.prem-generated-box .code{font-family:monospace;font-size:20px;font-weight:700;color:#4ade80;letter-spacing:.03em}
.prem-generated-box .meta{font-size:12px;color:rgba(255,255,255,.6);margin-top:4px}
.prem-generated-box button{margin-top:10px;background:transparent;border:1px solid rgba(74,222,128,.4);color:#4ade80;padding:6px 14px;border-radius:6px;font-size:12px;cursor:pointer}

table.prem-table{width:100%;border-collapse:collapse;margin-top:10px;font-size:13px}
table.prem-table th,table.prem-table td{padding:10px 12px;border-bottom:1px solid rgba(255,255,255,.08);text-align:left}
table.prem-table th{color:rgba(255,255,255,.5);font-weight:600;font-size:11.5px;text-transform:uppercase;letter-spacing:.04em}
table.prem-table td.code-cell{font-family:monospace;font-weight:600;color:#fff}
.prem-badge{display:inline-block;font-size:10.5px;padding:2px 9px;border-radius:999px;font-weight:600}
.prem-badge.unused{background:rgba(124,156,255,.18);color:#a9bcff}
.prem-badge.used{background:rgba(13,122,69,.25);color:#4ade80}
.prem-badge.revoked{background:rgba(179,38,30,.25);color:#f87171}
.prem-actions button{border:none;border-radius:6px;padding:6px 12px;font-size:11.5px;font-weight:600;cursor:pointer;margin-right:6px}
.prem-actions .revoke{background:transparent;color:#ffcf4d;border:1px solid rgba(255,207,77,.4)}
.prem-actions .delete{background:transparent;color:rgba(255,255,255,.6);border:1px solid rgba(255,255,255,.2)}
.prem-empty{color:rgba(255,255,255,.4);padding:40px 0;text-align:center;font-size:14px}
</style>

<div class="prem-wrap">
  <h1>Kelola Kode Premium</h1>
  <p style="color:#777;font-size:14px">Generate kode buat user yang udah bayar premium (Rp50.000/bulan). Kode cuma bisa dipakai sekali, dan otomatis aktifin masa premium buat IP yang redeem.</p>

  <div class="prem-info">
    <b>Alur jualan:</b> user chat WA ke <b>+1 703 618 7872</b> buat beli premium →
    kamu terima pembayaran → generate kode di bawah dengan durasi (hari) sesuai paket →
    kirim kode itu ke user via WA → user masukin kode di tombol "Premium" di halaman publik →
    masa aktif otomatis mulai dari saat itu.
  </div>

  <div class="prem-generate">
    <div class="field">
      <label>Durasi (hari)</label>
      <input type="number" id="durationInput" value="30" min="1">
    </div>
    <button id="generateBtn">Generate Kode Baru</button>
  </div>

  <div class="prem-generated-box" id="generatedBox">
    <div class="code" id="generatedCode"></div>
    <div class="meta" id="generatedMeta"></div>
    <button id="copyGeneratedBtn">Salin Kode</button>
  </div>

  <table class="prem-table">
    <thead>
      <tr>
        <th>Kode</th>
        <th>Durasi</th>
        <th>Dibuat</th>
        <th>Status</th>
        <th>Dipakai Oleh</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody id="codesTableBody"></tbody>
  </table>
  <div class="prem-empty" id="codesEmpty" style="display:none">Belum ada kode yang dibuat.</div>
</div>

<script>
const API_BASE = <?= json_encode(WORKER_API_BASE) ?>;
const ADMIN_TOKEN = <?= json_encode(WORKER_ADMIN_TOKEN) ?>;

function authHeaders(extra){
  return Object.assign({ 'X-Admin-Token': ADMIN_TOKEN }, extra || {});
}

function fmtDate(iso){
  if (!iso) return '-';
  const d = new Date(iso);
  return d.toLocaleString('id-ID', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' });
}

async function loadCodes(){
  const tbody = document.getElementById('codesTableBody');
  const empty = document.getElementById('codesEmpty');
  tbody.innerHTML = '';
  const res = await fetch(`${API_BASE}/api/admin/codes`, { headers: authHeaders() });
  const data = await res.json();
  const codes = data.codes || [];
  if (!codes.length){
    empty.style.display = 'block';
    return;
  }
  empty.style.display = 'none';
  codes.forEach(c => tbody.appendChild(renderRow(c)));
}

function renderRow(c){
  const tr = document.createElement('tr');
  let statusLabel, statusClass;
  if (c.revoked){ statusLabel = 'Dinonaktifkan'; statusClass = 'revoked'; }
  else if (c.used){ statusLabel = 'Sudah Dipakai'; statusClass = 'used'; }
  else { statusLabel = 'Belum Dipakai'; statusClass = 'unused'; }

  tr.innerHTML = `
    <td class="code-cell">${c.code}</td>
    <td>${c.duration_days} hari</td>
    <td>${fmtDate(c.created_at)}</td>
    <td><span class="prem-badge ${statusClass}">${statusLabel}</span></td>
    <td>${c.used ? (c.used_ip || '-') + ' · ' + fmtDate(c.used_at) : '-'}</td>
    <td class="prem-actions"></td>
  `;

  const actions = tr.querySelector('.prem-actions');
  if (!c.used && !c.revoked){
    const revokeBtn = document.createElement('button');
    revokeBtn.className = 'revoke';
    revokeBtn.textContent = 'Nonaktifkan';
    revokeBtn.addEventListener('click', () => doCodeAction(c.code, 'revoke'));
    actions.appendChild(revokeBtn);
  }
  const deleteBtn = document.createElement('button');
  deleteBtn.className = 'delete';
  deleteBtn.textContent = 'Hapus';
  deleteBtn.addEventListener('click', () => doCodeAction(c.code, 'delete'));
  actions.appendChild(deleteBtn);

  return tr;
}

async function doCodeAction(code, action){
  if (action === 'delete' && !confirm('Hapus kode ini dari histori?')) return;
  const res = await fetch(`${API_BASE}/api/admin/codes/${encodeURIComponent(code)}/${action}`, {
    method: 'POST',
    headers: authHeaders(),
  });
  const data = await res.json();
  if (data.ok) loadCodes();
  else alert(data.error || 'Gagal memproses.');
}

document.getElementById('generateBtn').addEventListener('click', async () => {
  const durationDays = Math.max(1, parseInt(document.getElementById('durationInput').value, 10) || 30);
  const res = await fetch(`${API_BASE}/api/admin/codes/generate`, {
    method: 'POST',
    headers: authHeaders({ 'Content-Type': 'application/json' }),
    body: JSON.stringify({ duration_days: durationDays }),
  });
  const data = await res.json();
  if (data.ok){
    const box = document.getElementById('generatedBox');
    document.getElementById('generatedCode').textContent = data.code.code;
    document.getElementById('generatedMeta').textContent = `Berlaku ${data.code.duration_days} hari sejak pertama kali diredeem user. Kirim kode ini ke user via WA.`;
    box.classList.add('show');
    loadCodes();
  } else {
    alert(data.error || 'Gagal generate kode.');
  }
});

document.getElementById('copyGeneratedBtn').addEventListener('click', () => {
  const code = document.getElementById('generatedCode').textContent;
  navigator.clipboard.writeText(code).then(() => {
    const btn = document.getElementById('copyGeneratedBtn');
    const orig = btn.textContent;
    btn.textContent = 'Tersalin!';
    setTimeout(() => { btn.textContent = orig; }, 1500);
  });
});

loadCodes();
</script>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
