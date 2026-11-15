# Panduan Design System & Komponen UI E-Library
**UTS Pemrograman Web — Semester Ganjil 2026/2027**  
**Informatika Universitas Muhammadiyah Malang**  
**Penanggung Jawab Front-End & UI/UX:** Robi (`feature/frontend`)

---

## 1. Pendahuluan
Design system ini dirancang khusus untuk proyek akademik **E-Library**, mengusung tema perpustakaan digital modern, bersih, profesional, dan mudah diakses (berstandar WCAG 2.1 AA untuk kontras dan navigasi keyboard). Seluruh komponen dibangun murni menggunakan **HTML5 semantik**, **CSS3 vanilla modern** (CSS Custom Properties & Grid/Flexbox), dan **JavaScript vanilla** tanpa bergantung pada framework pihak ketiga (tanpa Bootstrap, Tailwind, jQuery, atau React).

Dokumen ini ditujukan sebagai acuan bagi seluruh anggota tim (**Syahrial, Mail, Farid, Arkaan**) dalam menuliskan markup server-rendered PHP agar konsisten secara visual dan fungsional.

---

## 2. Token Desain (CSS Custom Properties)

Token desain didefinisikan pada root stylesheet (`assets/css/style.css`):

| Variabel CSS | Nilai Hex / Rem | Penggunaan Utama |
|---|---|---|
| `--primary` | `#2563eb` | Warna primer brand, tombol aksi utama, link aktif |
| `--primary-hover` | `#1d4ed8` | Status hover tombol primer |
| `--primary-light` | `#eff6ff` | Latar belakang badge pengguna, hover navigasi |
| `--primary-border`| `#93c5fd` | Border aksen primer |
| `--secondary` | `#475569` | Teks sekunder, label netral, ikon pendukung |
| `--text-main` | `#0f172a` | Warna teks utama, judul, paragraf tebal |
| `--text-muted` | `#64748b` | Deskripsi, petunjuk bantuan, footer |
| `--background` | `#f8fafc` | Latar belakang aplikasi / halaman |
| `--card-bg` | `#ffffff` | Latar belakang kartu, form, navbar, tabel |
| `--border` | `#e2e8f0` | Garis batas standar, pemisah kolom |
| `--border-strong`| `#cbd5e1` | Garis batas input saat hover |
| `--success` | `#16a34a` | Status buku tersedia, transaksi sukses |
| `--danger` | `#dc2626` | Status buku dipinjam, terlambat, hapus, error |
| `--warning` | `#d97706` | Peringatan jatuh tempo, stok terbatas |
| `--info` | `#0284c7` | Informasi sistem, polimorfisme, riwayat |
| `--radius-sm` | `6px` | Radius sudut tombol, input, badge |
| `--radius-md` | `10px` | Radius sudut kartu e-book, thumbnail cover |
| `--radius-lg` | `16px` | Radius sudut kartu utama, dialog modal |
| `--radius-full` | `9999px` | Bentuk pil untuk badge dan filter chips |
| `--shadow-xs` | *elevasi halus* | Bayangan dasar tabel dan kartu stat |
| `--shadow-md` | *elevasi medium* | Bayangan kartu utama saat diam |
| `--shadow-lg` | *elevasi tinggi* | Bayangan kartu e-book saat di-hover |

---

## 3. Komponen Layout Global

### 3.1 Kontainer Utama
Gunakan kelas `.main-container` untuk membungkus konten utama halaman:
```html
<main class="main-container">
    <!-- Konten Halaman -->
</main>
```

### 3.2 Kartu Konten (`.card`)
```html
<div class="card">
    <div class="card-header">
        <h1 class="card-title">Judul Panel</h1>
        <p class="card-subtitle">Deskripsi ringkas mengenai tujuan formulir atau panel.</p>
    </div>
    <div class="card-body">
        <!-- Isi konten -->
    </div>
</div>
```

---

## 4. Navigasi Global (`.navbar`)

Mendukung navigasi responsif otomatis dengan hamburger toggle pada tampilan mobile (< 768px):

```html
<header>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">
            📚 <span>E-Library</span>
        </a>
        <ul class="navbar-nav">
            <li><a href="index.php" class="nav-link active">Beranda</a></li>
            <li><a href="catalog.php" class="nav-link">Katalog</a></li>
            <li>
                <span class="user-badge user-badge--member">
                    👤 Nama Pengguna (MEMBER)
                </span>
            </li>
            <li><a href="logout.php" class="btn btn-outline btn-sm">Keluar</a></li>
        </ul>
    </nav>
</header>
```

---

## 5. Tombol (`.btn`)

Tersedia beberapa varian tombol dengan efek hover, active, dan status disabled:

```html
<!-- Tombol Primer -->
<button type="submit" class="btn btn-primary">Simpan Data</button>

<!-- Tombol Outline (Batal / Kembali) -->
<a href="catalog.php" class="btn btn-outline">Batal</a>

<!-- Tombol Bahaya / Hapus -->
<button type="submit" class="btn btn-danger">Hapus E-Book</button>

<!-- Ukuran Kecil (pada Tabel / Navigasi) -->
<button type="button" class="btn btn-outline btn-sm">Detail &rarr;</button>

<!-- Tombol Lebar Penuh -->
<button type="submit" class="btn btn-primary btn-block">Masuk Sekarang</button>
```

---

## 6. Formulir & Input

Formulir dirancang dengan tata letak rapi, pesan bantuan, dan validasi visual:

```html
<div class="form-group">
    <label for="email" class="form-label">
        Alamat Email <span class="required-mark">*</span>
    </label>
    <input 
        type="email" 
        id="email" 
        name="email" 
        class="form-input" 
        placeholder="nama@email.com" 
        required
    >
    <span class="form-helper">Email digunakan untuk autentikasi sistem.</span>
    <span class="form-feedback" id="emailFeedback"></span>
</div>

<!-- Input Password dengan Tombol Toggle Mata -->
<div class="form-group">
    <label for="password" class="form-label">Kata Sandi</label>
    <div class="input-password-wrapper">
        <input 
            type="password" 
            id="password" 
            name="password" 
            class="form-input" 
            placeholder="Minimal 6 karakter" 
            required
        >
        <button type="button" class="toggle-password-btn" data-target="password" aria-label="Tampilkan password">
            Tampilkan
        </button>
    </div>
</div>

<!-- Tata Letak Dua Kolom -->
<div class="form-row">
    <div class="form-group">
        <!-- Kolom Kiri -->
    </div>
    <div class="form-group">
        <!-- Kolom Kanan -->
    </div>
</div>
```

---

## 7. Badge & Status Indikator

Gunakan komponen badge untuk menandai status ketersediaan e-book dan transaksi:

```html
<!-- Status Tersedia -->
<span class="badge badge-available">● Tersedia</span>

<!-- Status Dipinjam -->
<span class="badge badge-borrowed">● Dipinjam</span>

<!-- Status Dikembalikan -->
<span class="badge badge-returned">✓ Dikembalikan</span>

<!-- Kategori E-Book -->
<span class="badge badge-category">📁 Rekayasa Web</span>

<!-- Tahun Terbit -->
<span class="badge badge-year">🗓️ 2026</span>
```

---

## 8. Kartu E-Book & Katalog Grid (`.book-grid`, `.book-card`)

```html
<div class="book-grid">
    <article class="book-card">
        <div class="book-card-cover-wrapper">
            <img 
                src="assets/images/default-book.png" 
                alt="Sampul Buku" 
                class="book-card-cover" 
                loading="lazy"
            >
        </div>
        <div class="book-card-body">
            <div class="book-card-category">📁 Pemrograman</div>
            <h2 class="book-card-title">
                <a href="detail.php?id=BOOK-001">Arsitektur PHP Modern OOP</a>
            </h2>
            <p class="book-card-author">✍️ Tim Dosen &bull; 🗓️ 2026</p>
            <p class="book-card-desc">Ulasan singkat ringkasan isi buku...</p>
            <div class="book-card-footer">
                <span class="badge badge-available">● Tersedia</span>
                <a href="detail.php?id=BOOK-001" class="btn btn-outline btn-sm">Detail &rarr;</a>
            </div>
        </div>
    </article>
</div>
```

---

## 9. Tabel Responsif & Aksi Transaksi

```html
<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th scope="col">ID Transaksi</th>
                <th scope="col">Judul E-Book</th>
                <th scope="col">Tanggal Pinjam</th>
                <th scope="col">Jatuh Tempo</th>
                <th scope="col" style="text-align: center;">Status</th>
                <th scope="col" style="text-align: right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>TRX-20261115-A1B2</code></td>
                <td><strong>Pemrograman PHP Modern</strong></td>
                <td>2026-11-15</td>
                <td>2026-11-22</td>
                <td style="text-align: center;">
                    <span class="badge badge-available">Dipinjam</span>
                </td>
                <td>
                    <form action="actions/return.php" method="POST" class="table-actions form-confirm-return" data-item-name="Pemrograman PHP Modern" style="justify-content: flex-end;">
                        <input type="hidden" name="transaction_id" value="TRX-20261115-A1B2">
                        <button type="submit" class="btn btn-outline btn-sm">↩️ Kembalikan</button>
                    </form>
                </td>
            </tr>
        </tbody>
    </table>
</div>
```

---

## 10. Dialog Modal Konfirmasi Otomatis

Aplikasi dilengkapi modul JavaScript modal aksesibel. Tidak perlu menulis markup modal secara manual! Cukup tambahkan kelas pemicu pada tag `<form>`:

| Kelas Form | Atribut Opsional | Kegunaan |
|---|---|---|
| `.form-confirm-delete` | `data-item-name="Judul Buku"` | Menampilkan konfirmasi bahaya saat menghapus e-book atau kategori. |
| `.form-confirm-return` | `data-item-name="Judul Buku"` | Menampilkan konfirmasi pengembalian e-book. |
| `form[action*="borrow.php"]`| *(Otomatis membaca `.detail-title`)* | Menampilkan konfirmasi peminjaman pada halaman detail. |

Fitur modal ini mendukung navigasi keyboard (tombol `ESC` untuk membatalkan dan fokus otomatis ke tombol konfirmasi).

---

## 11. Empty State (Kondisi Data Kosong)

```html
<section class="empty-state">
    <div class="empty-state-icon">📭</div>
    <h2 class="empty-state-title">Belum Ada Data Tersedia</h2>
    <p class="empty-state-text">
        Silakan kembali ke katalog untuk melihat e-book yang dapat Anda pinjam.
    </p>
    <a href="catalog.php" class="btn btn-primary" style="width: auto;">
        Buka Katalog E-Book
    </a>
</section>
```

---

## 12. Alert Banners (Pesan Sukses / Error / Info)

```html
<!-- Sukses (Otomatis ditutup setelah 6 detik atau via tombol silang) -->
<div class="alert alert-success" role="alert">
    <span>✓ E-book berhasil dipinjam.</span>
</div>

<!-- Error / Validasi -->
<div class="alert alert-danger" role="alert">
    <span>⚠️ Batas peminjaman e-book aktif telah tercapai.</span>
</div>

<!-- Informasi / Demonstrasi Polimorfisme -->
<div class="alert alert-info" role="alert">
    <span>🔍 Demonstrasi Polimorfisme PHP OOP.</span>
</div>
```

---

*Disusun oleh Robi — Senior Front-End Developer & UI/UX Engineer tim E-Library.*
