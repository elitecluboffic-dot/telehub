<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Terms of Service — TeleCard';
$metaDesc  = 'Syarat dan Ketentuan Layanan TeleCard. Pelajari aturan penggunaan platform, kewajiban pengguna, dan batasan tanggung jawab kami.';
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
          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
      </svg>
      Legal Document
    </div>
    <h1>Syarat &amp; <span>Ketentuan</span></h1>
    <p>Dokumen ini mengatur penggunaan layanan TeleCard. Dengan membuat akun atau mengakses platform kami, kamu dianggap telah membaca, memahami, dan menyetujui seluruh ketentuan di bawah ini.</p>
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
      <li><a href="#s1"><span class="pp-toc-num">01</span>Penerimaan Persyaratan</a></li>
      <li><a href="#s2"><span class="pp-toc-num">02</span>Kelayakan &amp; Akun Pengguna</a></li>
      <li><a href="#s3"><span class="pp-toc-num">03</span>Pengajuan &amp; Moderasi Card</a></li>
      <li><a href="#s4"><span class="pp-toc-num">04</span>Konten &amp; Perilaku yang Dilarang</a></li>
      <li><a href="#s5"><span class="pp-toc-num">05</span>Hak Kekayaan Intelektual</a></li>
      <li><a href="#s6"><span class="pp-toc-num">06</span>Penangguhan &amp; Penghentian Akun</a></li>
      <li><a href="#s7"><span class="pp-toc-num">07</span>Penafian &amp; Batasan Tanggung Jawab</a></li>
      <li><a href="#s8"><span class="pp-toc-num">08</span>Perubahan Layanan &amp; Persyaratan</a></li>
      <li><a href="#s9"><span class="pp-toc-num">09</span>Hukum yang Berlaku</a></li>
      <li><a href="#s10"><span class="pp-toc-num">10</span>Hubungi Kami</a></li>
    </ol>
  </div>

  <!-- S1 -->
  <div class="pp-section" id="s1">
    <div class="pp-section-header">
      <div class="pp-section-num">01</div>
      <h2>Penerimaan Persyaratan</h2>
      <div class="pp-section-line"></div>
    </div>
    <p>Syarat &amp; Ketentuan ("Ketentuan") ini merupakan perjanjian yang mengikat secara hukum antara kamu dan TeleCard. Dengan mengakses situs, membuat akun, atau menggunakan fitur apa pun di platform kami, kamu menyatakan setuju untuk terikat oleh Ketentuan ini secara penuh.</p>
    <p>Jika kamu tidak menyetujui salah satu bagian dari Ketentuan ini, kamu tidak diperkenankan menggunakan layanan TeleCard dalam bentuk apa pun.</p>
    <div class="pp-box blue">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
      </svg>
      <div>Dokumen ini berlaku bersamaan dengan <strong>Kebijakan Privasi</strong> kami. Mohon dibaca keduanya sebelum menggunakan layanan.</div>
    </div>
  </div>

  <!-- S2 -->
  <div class="pp-section" id="s2">
    <div class="pp-section-header">
      <div class="pp-section-num">02</div>
      <h2>Kelayakan &amp; Akun Pengguna</h2>
      <div class="pp-section-line"></div>
    </div>
    <p>Untuk menggunakan TeleCard, kamu wajib memenuhi syarat kelayakan berikut dan bertanggung jawab penuh atas keamanan akun kamu sendiri.</p>
    <ul class="pp-list">
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>Kamu berusia <strong>minimal 13 tahun</strong>, atau usia minimum yang berlaku sesuai hukum di negara kamu.</div>
      </li>
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>Informasi pendaftaran yang kamu berikan (username, email, dsb.) adalah <strong>akurat dan terkini</strong>.</div>
      </li>
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>Kamu bertanggung jawab menjaga kerahasiaan <strong>password</strong> dan seluruh aktivitas yang terjadi di bawah akun kamu.</div>
      </li>
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>Satu orang hanya diperbolehkan memiliki <strong>satu akun</strong>, kecuali mendapat izin tertulis dari kami.</div>
      </li>
    </ul>
    <div class="pp-box red">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
      </svg>
      <div>Akun bot, akun palsu, atau pendaftaran otomatis massal <strong>dilarang keras</strong> dan akan dihapus tanpa pemberitahuan sesuai Kebijakan Privasi kami.</div>
    </div>
  </div>

  <!-- S3 -->
  <div class="pp-section" id="s3">
    <div class="pp-section-header">
      <div class="pp-section-num">03</div>
      <h2>Pengajuan &amp; Moderasi Card</h2>
      <div class="pp-section-line"></div>
    </div>
    <p>TeleCard memungkinkan pengguna mengajukan card (channel, grup, atau akun Telegram) untuk ditampilkan di direktori publik. Pengajuan tunduk pada aturan berikut:</p>
    <ul class="pp-list">
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
        Setiap card yang diajukan akan melalui <strong>proses moderasi</strong> sebelum tampil di direktori publik.
      </li>
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
        Kami berhak <strong>menolak, menunda, atau menghapus</strong> card kapan saja tanpa perlu memberi alasan detail.
      </li>
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
        Kamu wajib memiliki <strong>hak sah</strong> atas channel/grup/akun yang diajukan, baik sebagai pemilik maupun pihak yang diberi izin resmi.
      </li>
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
        Informasi card (deskripsi, tag, gambar) harus <strong>relevan dan tidak menyesatkan</strong> calon pengunjung.
      </li>
    </ul>
  </div>

  <!-- S4 -->
  <div class="pp-section" id="s4">
    <div class="pp-section-header">
      <div class="pp-section-num">04</div>
      <h2>Konten &amp; Perilaku yang Dilarang</h2>
      <div class="pp-section-line"></div>
    </div>
    <p>Untuk menjaga TeleCard sebagai platform yang aman, kamu dilarang mengajukan card, mengirim komentar, atau melakukan aktivitas yang mengandung/berkaitan dengan hal berikut:</p>
    <ul class="pp-list">
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        <div>Konten yang melanggar <strong>Ketentuan Layanan Telegram</strong> atau hukum yang berlaku di Indonesia maupun internasional.</div>
      </li>
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        <div>Materi pornografi, eksploitasi anak, kekerasan ekstrem, atau ujaran kebencian.</div>
      </li>
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        <div>Penipuan, skema investasi bodong, phishing, atau aktivitas ilegal lainnya.</div>
      </li>
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        <div>Spam, pengajuan card berulang, atau upaya memanipulasi sistem rating/komentar.</div>
      </li>
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        <div>Upaya meretas, menyalahgunakan celah keamanan, atau mengganggu operasional platform (termasuk scraping otomatis tanpa izin).</div>
      </li>
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        <div>Pelanggaran hak cipta, merek dagang, atau kekayaan intelektual pihak lain.</div>
      </li>
    </ul>
    <div class="pp-box yellow">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/>
      </svg>
      <div>Kami berhak menghapus konten yang melanggar tanpa pemberitahuan dan melaporkan pelanggaran serius kepada pihak berwenang jika diperlukan.</div>
    </div>
  </div>

  <!-- S5 -->
  <div class="pp-section" id="s5">
    <div class="pp-section-header">
      <div class="pp-section-num">05</div>
      <h2>Hak Kekayaan Intelektual</h2>
      <div class="pp-section-line"></div>
    </div>
    <p>Seluruh elemen desain, logo, tampilan antarmuka, dan kode sumber TeleCard adalah milik TeleCard dan dilindungi hukum kekayaan intelektual yang berlaku, kecuali dinyatakan lain.</p>
    <p>Dengan mengunggah konten (deskripsi card, gambar, komentar) ke TeleCard, kamu memberikan kami <strong style="color:rgba(255,255,255,0.75)">lisensi non-eksklusif</strong> untuk menampilkan, menyimpan, dan mendistribusikan konten tersebut sebatas keperluan operasional platform. Kamu tetap memegang hak kepemilikan atas konten yang kamu unggah.</p>
  </div>

  <!-- S6 -->
  <div class="pp-section" id="s6">
    <div class="pp-section-header">
      <div class="pp-section-num">06</div>
      <h2>Penangguhan &amp; Penghentian Akun</h2>
      <div class="pp-section-line"></div>
    </div>
    <p>Kami berhak menangguhkan atau menghentikan akses akun kamu, dengan atau tanpa pemberitahuan sebelumnya, jika:</p>
    <ul class="pp-list">
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
        Kamu melanggar salah satu ketentuan dalam dokumen ini.
      </li>
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
        Terdeteksi aktivitas mencurigakan, penyalahgunaan sistem, atau penggunaan bot/skrip otomatis.
      </li>
      <li>
        <svg class="licon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
        Diperlukan untuk melindungi keamanan platform atau pengguna lain.
      </li>
    </ul>
    <p>Kamu juga dapat mengajukan <strong style="color:rgba(255,255,255,0.75)">penghapusan akun</strong> secara mandiri kapan saja dengan menghubungi kami, sebagaimana dijelaskan dalam Kebijakan Privasi.</p>
  </div>

  <!-- S7 -->
  <div class="pp-section" id="s7">
    <div class="pp-section-header">
      <div class="pp-section-num">07</div>
      <h2>Penafian &amp; Batasan Tanggung Jawab</h2>
      <div class="pp-section-line"></div>
    </div>
    <p>TeleCard adalah platform direktori yang mempertemukan pengguna dengan channel, grup, dan akun Telegram. Kami tidak memiliki, mengontrol, atau bertanggung jawab atas konten yang dikelola oleh pihak ketiga di dalam channel/grup yang terdaftar.</p>
    <div class="pp-box blue">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
      </svg>
      <div>Layanan disediakan <strong>"sebagaimana adanya" (as-is)</strong> tanpa jaminan tersirat apa pun, termasuk namun tidak terbatas pada ketersediaan layanan tanpa gangguan atau bebas dari kesalahan.</div>
    </div>
    <p>Sepanjang diizinkan oleh hukum yang berlaku, TeleCard tidak bertanggung jawab atas kerugian langsung maupun tidak langsung yang timbul dari penggunaan platform, termasuk namun tidak terbatas pada interaksi kamu dengan channel/grup/akun pihak ketiga yang ditemukan melalui direktori kami.</p>
  </div>

  <!-- S8 -->
  <div class="pp-section" id="s8">
    <div class="pp-section-header">
      <div class="pp-section-num">08</div>
      <h2>Perubahan Layanan &amp; Persyaratan</h2>
      <div class="pp-section-line"></div>
    </div>
    <p>Kami berhak mengubah, menangguhkan, atau menghentikan sebagian maupun seluruh fitur layanan kapan saja tanpa kewajiban memberi kompensasi.</p>
    <p>Ketentuan ini juga dapat diperbarui sewaktu-waktu. Perubahan signifikan akan diinformasikan melalui notifikasi di platform, dan tanggal pembaruan terakhir selalu tercantum di bagian atas halaman ini. Dengan tetap menggunakan TeleCard setelah perubahan diterbitkan, kamu dianggap menyetujui Ketentuan yang telah diperbarui.</p>
  </div>

  <!-- S9 -->
  <div class="pp-section" id="s9">
    <div class="pp-section-header">
      <div class="pp-section-num">09</div>
      <h2>Hukum yang Berlaku</h2>
      <div class="pp-section-line"></div>
    </div>
    <p>Ketentuan ini diatur dan ditafsirkan sesuai dengan hukum yang berlaku di Republik Indonesia, tanpa memperhatikan pertentangan aturan hukum. Setiap perselisihan yang timbul dari atau terkait dengan Ketentuan ini akan diupayakan penyelesaiannya secara musyawarah terlebih dahulu.</p>
  </div>

  <div class="pp-divider"></div>

  <!-- S10 CONTACT -->
  <div class="pp-section" id="s10">
    <div class="pp-contact">
      <h3>Ada Pertanyaan Soal Ketentuan Ini?</h3>
      <p>Kalau kamu punya pertanyaan seputar Syarat & Ketentuan, pengajuan card, atau status akun kamu, hubungi kami langsung via Telegram.</p>
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
