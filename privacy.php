<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Privacy Policy — TeleCard';
$metaDesc  = 'Kebijakan Privasi TeleCard. Pelajari bagaimana kami mengumpulkan, menggunakan, dan melindungi data kamu.';
include __DIR__ . '/includes/header.php';
?>

<style>
.pp-wrap {
  max-width: 780px;
  margin: 0 auto;
  padding: 40px 16px 80px;
}

/* ── Hero ── */
.pp-hero {
  text-align: center;
  margin-bottom: 48px;
}
.pp-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(42,171,238,0.1);
  border: 1px solid rgba(42,171,238,0.3);
  color: #2AABEE;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 2px;
  text-transform: uppercase;
  padding: 5px 14px;
  border-radius: 99px;
  margin-bottom: 18px;
}
.pp-badge svg { width: 12px; height: 12px; }
.pp-hero h1 {
  font-size: clamp(26px, 6vw, 38px);
  font-weight: 800;
  letter-spacing: -0.5px;
  line-height: 1.2;
  margin-bottom: 12px;
  color: var(--text, #fff);
}
.pp-hero h1 span { color: #2AABEE; }
.pp-hero p {
  color: var(--text-dim, rgba(255,255,255,0.45));
  font-size: 14px;
  line-height: 1.7;
  max-width: 480px;
  margin: 0 auto 16px;
}
.pp-meta {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  color: rgba(255,255,255,0.35);
  font-size: 12px;
  padding: 6px 14px;
  border-radius: 99px;
}
.pp-meta svg { width: 12px; height: 12px; }
.pp-hero-line {
  width: 60px;
  height: 3px;
  background: linear-gradient(90deg, #2AABEE, #1d8fd1);
  border-radius: 99px;
  margin: 20px auto 0;
}

/* ── TOC ── */
.pp-toc {
  background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 16px;
  padding: 24px 20px;
  margin-bottom: 44px;
}
.pp-toc h4 {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: rgba(255,255,255,0.25);
  margin-bottom: 16px;
}
.pp-toc ol {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 4px;
  counter-reset: toc;
}
.pp-toc ol li { counter-increment: toc; }
.pp-toc ol li a {
  display: flex;
  align-items: center;
  gap: 12px;
  color: rgba(255,255,255,0.5);
  text-decoration: none;
  font-size: 13.5px;
  padding: 7px 6px;
  border-radius: 8px;
  transition: all 0.18s;
}
.pp-toc ol li a:hover {
  background: rgba(42,171,238,0.07);
  color: #2AABEE;
  padding-left: 10px;
}
.pp-toc-num {
  flex-shrink: 0;
  font-size: 11px;
  font-weight: 800;
  color: #2AABEE;
  opacity: 0.8;
  width: 22px;
}

/* ── Section ── */
.pp-section {
  margin-bottom: 44px;
  scroll-margin-top: 80px;
}
.pp-section-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 18px;
}
.pp-section-num {
  flex-shrink: 0;
  width: 34px;
  height: 34px;
  background: rgba(42,171,238,0.12);
  border: 1px solid rgba(42,171,238,0.25);
  border-radius: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 800;
  color: #2AABEE;
}
.pp-section h2 {
  font-size: 17px;
  font-weight: 700;
  color: #fff;
  flex: 1;
}
.pp-section-line {
  height: 1px;
  background: linear-gradient(90deg, rgba(255,255,255,0.07), transparent);
  flex: 1;
}
.pp-section p {
  color: rgba(255,255,255,0.48);
  font-size: 14px;
  line-height: 1.8;
  margin-bottom: 14px;
}
.pp-section p:last-child { margin-bottom: 0; }

/* ── List ── */
.pp-list {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: 10px;
}
.pp-list li {
  display: flex;
  align-items: flex-start;
  gap: 11px;
  background: rgba(255,255,255,0.025);
  border: 1px solid rgba(255,255,255,0.07);
  border-radius: 11px;
  padding: 12px 14px;
  color: rgba(255,255,255,0.5);
  font-size: 13.5px;
  line-height: 1.6;
}
.pp-list li .licon {
  flex-shrink: 0;
  margin-top: 2px;
  width: 16px;
  height: 16px;
  color: #2AABEE;
}
.pp-list li strong { color: rgba(255,255,255,0.8); }

/* ── Boxes ── */
.pp-box {
  border-radius: 13px;
  padding: 14px 16px;
  margin: 16px 0;
  display: flex;
  gap: 12px;
  align-items: flex-start;
  font-size: 13.5px;
  line-height: 1.7;
}
.pp-box.blue {
  background: rgba(42,171,238,0.07);
  border: 1px solid rgba(42,171,238,0.2);
  color: rgba(255,255,255,0.5);
}
.pp-box.red {
  background: rgba(239,68,68,0.07);
  border: 1px solid rgba(239,68,68,0.22);
  color: rgba(255,255,255,0.5);
}
.pp-box.yellow {
  background: rgba(245,197,24,0.07);
  border: 1px solid rgba(245,197,24,0.22);
  color: rgba(255,255,255,0.5);
}
.pp-box svg { flex-shrink: 0; width: 18px; height: 18px; margin-top: 2px; }
.pp-box.blue svg  { color: #2AABEE; }
.pp-box.red svg   { color: #ef4444; }
.pp-box.yellow svg { color: #f5c518; }
.pp-box strong { color: rgba(255,255,255,0.75); }

/* ── Divider ── */
.pp-divider {
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.07), transparent);
  margin: 44px 0;
}

/* ── Contact ── */
.pp-contact {
  background: rgba(42,171,238,0.06);
  border: 1px solid rgba(42,171,238,0.2);
  border-radius: 18px;
  padding: 32px 20px;
  text-align: center;
}
.pp-contact h3 {
  font-size: 18px;
  font-weight: 800;
  color: #fff;
  margin-bottom: 10px;
}
.pp-contact p {
  color: rgba(255,255,255,0.42);
  font-size: 13.5px;
  line-height: 1.7;
  margin-bottom: 22px;
  max-width: 400px;
  margin-left: auto;
  margin-right: auto;
}
.pp-contact-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  background: linear-gradient(135deg, #2AABEE, #1d8fd1);
  color: #fff;
  font-weight: 700;
  font-size: 14px;
  padding: 13px 28px;
  border-radius: 12px;
  text-decoration: none;
  box-shadow: 0 4px 20px rgba(42,171,238,0.3);
  transition: all 0.2s;
  width: 100%;
  max-width: 280px;
}
.pp-contact-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 32px rgba(42,171,238,0.45);
}
.pp-contact-btn svg { width: 17px; height: 17px; }
</style>

<div class="pp-wrap">

  <!-- HERO -->
  <div class="pp-hero">
    <div class="pp-badge">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
      </svg>
      Legal Document
    </div>
    <h1>Kebijakan <span>Privasi</span></h1>
    <p>Kami serius soal privasi kamu. Pelajari data apa yang kami kumpulkan, bagaimana kami menggunakannya, dan hak-hak kamu sebagai pengguna.</p>
    <div class="pp-meta">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
      </svg>
      Terakhir diperbarui: 30 Juni 2026
    </div>
    <div class="pp-hero-line"></div>
  </div>

  <!-- TOC -->
  <div class="pp-toc">
    <h4>Daftar Isi</h4>
    <ol>
      <li><a href="#s1"><span class="pp-toc-num">01</span>Informasi yang Kami Kumpulkan</a></li>
      <li><a href="#s2"><span class="pp-toc-num">02</span>Persyaratan Pengguna Nyata</a></li>
      <li><a href="#s3"><span class="pp-toc-num">03</span>Cara Kami Menggunakan Data</a></li>
      <li><a href="#s4"><span class="pp-toc-num">04</span>Keamanan &amp; Perlindungan IP</a></li>
      <li><a href="#s5"><span class="pp-toc-num">05</span>Cookie &amp; Sesi</a></li>
      <li><a href="#s6"><span class="pp-toc-num">06</span>Data Pihak Ketiga</a></li>
      <li><a href="#s7"><span class="pp-toc-num">07</span>Hak-Hak Kamu</a></li>
      <li><a href="#s8"><span class="pp-toc-num">08</span>Perubahan Kebijakan</a></li>
      <li><a href="#s9"><span class="pp-toc-num">09</span>Hubungi Kami</a></li>
    </ol>
  </div>

  <!-- S1 -->
  <div class="pp-section" id="s1">
    <div class="pp-section-header">
      <div class="pp-section-num">01</div>
      <h2>Informasi yang Kami Kumpulkan</h2>
      <div class="pp-section-line"></div>
    </div>
    <p>Ketika kamu menggunakan TeleCard, kami mengumpulkan beberapa jenis informasi untuk menjalankan layanan dengan baik dan memastikan keamanan platform.</p>
    <ul class="pp-list">
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        <div><strong>Data Akun</strong> — nama, alamat email, dan password (terenkripsi) saat kamu mendaftar.</div>
      </li>
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
        </svg>
        <div><strong>Data Card</strong> — nama channel/grup/user, deskripsi, link Telegram, tag, dan gambar yang kamu upload.</div>
      </li>
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
        </svg>
        <div><strong>Data Teknis</strong> — alamat IP, negara asal, jenis browser, dan waktu akses untuk keperluan keamanan.</div>
      </li>
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        <div><strong>Komentar &amp; Rating</strong> — nama dan pesan yang kamu kirimkan di halaman komentar.</div>
      </li>
    </ul>
  </div>

  <!-- S2 -->
  <div class="pp-section" id="s2">
    <div class="pp-section-header">
      <div class="pp-section-num">02</div>
      <h2>Persyaratan Pengguna Nyata</h2>
      <div class="pp-section-line"></div>
    </div>
    <div class="pp-box red">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
      </svg>
      <div><strong>Penting:</strong> TeleCard hanya terbuka untuk pengguna nyata (real user). Akun bot, fake account, atau pendaftaran massal otomatis dilarang keras dan akan dihapus tanpa pemberitahuan.</div>
    </div>
    <p>Dengan mendaftar ke TeleCard, kamu menyatakan dan menjamin bahwa:</p>
    <ul class="pp-list">
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>Kamu adalah <strong>manusia nyata</strong>, bukan bot, skrip otomatis, atau akun palsu.</div>
      </li>
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>Card yang kamu daftarkan adalah <strong>milik kamu sendiri</strong> atau kamu memiliki izin resmi dari pemilik.</div>
      </li>
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>Informasi yang kamu berikan adalah <strong>benar dan akurat</strong> — bukan data fiktif atau menyesatkan.</div>
      </li>
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>Kamu <strong>tidak mendaftarkan channel atau grup</strong> yang melanggar ketentuan Telegram maupun hukum yang berlaku.</div>
      </li>
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>Kamu berusia <strong>minimal 13 tahun</strong> atau sesuai usia minimum yang berlaku di negara kamu.</div>
      </li>
    </ul>
    <div class="pp-box yellow">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/>
      </svg>
      <div>Pelanggaran dapat mengakibatkan <strong>penghapusan akun dan pemblokiran IP permanen</strong> tanpa pemberitahuan sebelumnya.</div>
    </div>
  </div>

  <!-- S3 -->
  <div class="pp-section" id="s3">
    <div class="pp-section-header">
      <div class="pp-section-num">03</div>
      <h2>Cara Kami Menggunakan Data</h2>
      <div class="pp-section-line"></div>
    </div>
    <p>Data yang kami kumpulkan digunakan semata-mata untuk menjalankan dan meningkatkan layanan TeleCard. Kami <strong style="color:rgba(255,255,255,0.75)">tidak menjual data kamu</strong> kepada pihak ketiga manapun.</p>
    <ul class="pp-list">
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
        Menampilkan card kamu di direktori publik TeleCard.
      </li>
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
        Mengirim notifikasi terkait status card (disetujui / ditolak).
      </li>
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
        Mendeteksi dan mencegah penyalahgunaan, spam, dan akses tidak sah.
      </li>
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
        Menganalisis tren penggunaan untuk meningkatkan fitur platform.
      </li>
    </ul>
  </div>

  <!-- S4 -->
  <div class="pp-section" id="s4">
    <div class="pp-section-header">
      <div class="pp-section-num">04</div>
      <h2>Keamanan &amp; Perlindungan IP</h2>
      <div class="pp-section-line"></div>
    </div>
    <p>TeleCard menggunakan sistem IPGuard yang secara otomatis mendeteksi dan memblokir akses dari VPN, Proxy, dan Tor untuk melindungi komunitas dari penyalahgunaan.</p>
    <div class="pp-box blue">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
      </svg>
      <div>Alamat IP dan negara asal dicatat dalam log internal untuk keamanan. Data ini <strong>tidak dibagikan ke publik</strong> dan hanya diakses oleh admin.</div>
    </div>
    <p>Semua koneksi dilindungi dengan enkripsi SSL/TLS dan password akun disimpan dalam bentuk hash yang tidak dapat dibaca balik.</p>
  </div>

  <!-- S5 -->
  <div class="pp-section" id="s5">
    <div class="pp-section-header">
      <div class="pp-section-num">05</div>
      <h2>Cookie &amp; Sesi</h2>
      <div class="pp-section-line"></div>
    </div>
    <p>TeleCard menggunakan cookie sesi standar untuk menjaga status login kamu. Kami tidak menggunakan cookie pelacak pihak ketiga atau cookie iklan.</p>
    <ul class="pp-list">
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
        Cookie sesi dihapus otomatis saat kamu logout atau menutup browser.
      </li>
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
        Tidak ada tracking pixel, cookie iklan, atau script analitik eksternal.
      </li>
    </ul>
  </div>

  <!-- S6 -->
  <div class="pp-section" id="s6">
    <div class="pp-section-header">
      <div class="pp-section-num">06</div>
      <h2>Data Pihak Ketiga</h2>
      <div class="pp-section-line"></div>
    </div>
    <p>Kami menggunakan layanan pihak ketiga berikut dalam operasional TeleCard:</p>
    <ul class="pp-list">
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        <div><strong>Gmail SMTP</strong> — untuk pengiriman email notifikasi. Email kamu hanya digunakan untuk keperluan ini.</div>
      </li>
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
        </svg>
        <div><strong>VPNAPI.io</strong> — untuk deteksi VPN/Proxy. IP kamu dikirim ke API mereka untuk diverifikasi sesuai kebijakan privasi vpnapi.io.</div>
      </li>
    </ul>
  </div>

  <!-- S7 -->
  <div class="pp-section" id="s7">
    <div class="pp-section-header">
      <div class="pp-section-num">07</div>
      <h2>Hak-Hak Kamu</h2>
      <div class="pp-section-line"></div>
    </div>
    <p>Sebagai pengguna TeleCard, kamu memiliki hak penuh atas data yang kamu berikan kepada kami:</p>
    <ul class="pp-list">
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
          <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
        <div><strong>Hak Akses</strong> — kamu bisa melihat semua data yang kami simpan tentang kamu melalui akun kamu.</div>
      </li>
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
        <div><strong>Hak Koreksi</strong> — kamu bisa mengedit informasi akun dan card kamu kapan saja.</div>
      </li>
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
        </svg>
        <div><strong>Hak Hapus</strong> — kamu bisa meminta penghapusan akun dan seluruh data kamu dengan menghubungi kami.</div>
      </li>
    </ul>
  </div>

  <!-- S8 -->
  <div class="pp-section" id="s8">
    <div class="pp-section-header">
      <div class="pp-section-num">08</div>
      <h2>Perubahan Kebijakan</h2>
      <div class="pp-section-line"></div>
    </div>
    <p>Kami berhak memperbarui kebijakan privasi ini sewaktu-waktu. Perubahan signifikan akan diberitahukan melalui notifikasi di platform. Tanggal pembaruan selalu dicantumkan di bagian atas halaman ini.</p>
    <p>Dengan terus menggunakan TeleCard setelah perubahan diterbitkan, kamu dianggap menyetujui kebijakan yang diperbarui.</p>
  </div>

  <div class="pp-divider"></div>

  <!-- S9 CONTACT -->
  <div class="pp-section" id="s9">
    <div class="pp-contact">
      <h3>Ada Pertanyaan Soal Privasi?</h3>
      <p>Kalau kamu punya pertanyaan, ingin meminta penghapusan data, atau menemukan masalah terkait privasi, hubungi kami langsung via Telegram.</p>
      <a href="https://t.me/bitcoinbim" target="_blank" rel="noopener" class="pp-contact-btn">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
        </svg>
        Hubungi via Telegram
      </a>
    </div>
  </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
