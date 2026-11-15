# 📚 E-Library — Sistem Perpustakaan Digital & Peminjaman E-Book

> **Tugas Ujian Tengah Semester (UTS) Pemrograman Web — Semester Ganjil 2026/2027**  
> Program Studi Informatika, Fakultas Teknik, Universitas Muhammadiyah Malang  
> **Kelas:** Pemrograman Web 5D  
> **Teknologi Backend:** PHP 8.x Berorientasi Objek (OOP Murni Modern Tanpa Framework)  
> **Teknologi Frontend:** HTML5 Semantik, CSS3 Responsif (Custom Design System), JavaScript Vanilla  
> **Persistensi Data:** Flat-file JSON Storage Lokal Berbasis Objek Model  

---

## 👥 Tim Pengembang (Kelompok 5D)

| No | Nama Mahasiswa | NIM | Peran Utama & Modul Tanggung Jawab | Branch Git |
|:---:|---|:---:|---|:---:|
| 1 | **Muhamad Robi Ardita** | **202410370110002** | *Front-End Designer & UI/UX Engineer*<br>Design System, CSS3 responsif (`assets/css/style.css`), interaktivitas DOM vanilla JS (`assets/js/app.js`), validasi form, modal dialog konfirmasi aksesibel, menu navigasi hamburger mobile | `feature/frontend` |
| 2 | **Syahrial Nur Faturrahman** | **202410370110009** | *OOP Foundation & Authentication Engineer*<br>Fondasi arsitektur OOP, model `User`, `Member`, `Admin`, `AuthService`, `SessionManager`, alur autentikasi (`login.php`, `register.php`, `logout.php`), role guards, dan suite pengujian `AuthTestSuite.php` | `feature/auth` |
| 3 | **Ismail Dwi Muh. Anugerah** | **202410370110013** | *Catalog & Content Management Developer*<br>Model `Book`, `Category`, `BorrowableInterface`, `BookService`, antarmuka katalog publik (`catalog.php`, `detail.php`), manajemen CRUD buku & kategori Admin (`admin/books.php`, `admin/categories.php`), dan `CatalogTestSuite.php` | `feature/catalog` |
| 4 | **Farid Al Farizi** | **202410370110017** | *Transaction Engine & Business Logic Specialist*<br>Model `Transaction`, `LoanService`, aturan peminjaman & pengembalian 7 hari, batas kuota simultan, pencegahan konflik buku, halaman transaksi Member & Admin (`member/loans.php`, `member/history.php`, `admin/transactions.php`), dan `LoanTestSuite.php` | `feature/transaction` |
| 5 | **Naufal Arkaan** | **202410370110020** | *Lead Developer, Integrator, Dashboard & QA*<br>Autoloader PSR-4, `bootstrap.php`, perbaikan portabilitas storage cross-platform, integrasi modul lintas branch, dashboard Member & Admin (`member/dashboard.php`, `admin/dashboard.php`), `admin/members.php`, `member/profile.php`, suite integrasi `IntegrationTestSuite.php`, dan dokumentasi resmi | `feature/integration` |

---

## 🚀 PANDUAN CEPAT MENJALANKAN APLIKASI (UNTUK DOSEN PENGUJI)

Aplikasi **E-Library** dibangun murni tanpa framework eksternal (*zero external dependency*), **tidak membutuhkan instalasi database SQL** (MySQL/MariaDB/PostgreSQL), dan **tidak memerlukan Composer maupun npm**. Cukup dengan runtime PHP versi 8.1 ke atas.

```
┌────────────────────────────────────────────────────────────────────────┐
│                                                                        │
│   CARA TERCEPAT MENJALANKAN (HANYA 1 MENIT):                           │
│   1. Buka Terminal / PowerShell di folder proyek ini.                  │
│   2. Jalankan perintah:   php -S localhost:8000                        │
│   3. Buka browser:        http://localhost:8000                        │
│                                                                        │
└────────────────────────────────────────────────────────────────────────┘
```

---

### METODE 1: Menggunakan PHP Built-in Server (Sangat Direkomendasikan)
Metode ini adalah cara paling praktis dan bersih untuk menguji aplikasi:

1. **Buka Terminal / Command Prompt / PowerShell** pada direktori utama proyek:
   ```bash
   cd "C:\Pelajaran-Kuliah-UMM\Pemrograman Web\Kelas Teori\uts-web\UTS-PEMROGRAMAN-WEB-5D"
   ```
2. **Jalankan web server bawaan PHP**:
   ```bash
   php -S localhost:8000
   ```
3. **Buka peramban (*web browser*)** seperti Google Chrome, Microsoft Edge, atau Mozilla Firefox, lalu akses:
   ```text
   http://localhost:8000
   ```
4. Aplikasi langsung berjalan dengan seluruh seed data buku, kategori, pengguna, dan transaksi awal siap pakai!

---

### METODE 2: Menggunakan XAMPP (Apache)
Bila Bapak/Ibu Dosen lebih nyaman menggunakan XAMPP:

1. Salin (*copy*) seluruh folder proyek ini ke dalam folder `htdocs` XAMPP Anda:
   ```text
   C:\xampp\htdocs\UTS-PEMROGRAMAN-WEB-5D
   ```
2. Buka **XAMPP Control Panel**, lalu klik tombol **Start** pada modul **Apache** (modul MySQL tidak perlu dinyalakan karena sistem menggunakan penyimpanan JSON lokal).
3. Buka browser dan buka tautan berikut:
   ```text
   http://localhost/UTS-PEMROGRAMAN-WEB-5D/
   ```

---

### METODE 3: Menggunakan Laragon
Bila menggunakan web server Laragon:

1. Salin folder proyek ke dalam direktori:
   ```text
   C:\laragon\www\UTS-PEMROGRAMAN-WEB-5D
   ```
2. Pastikan servis Apache menyala pada Laragon.
3. Buka browser dan kunjungi:
   ```text
   http://localhost/UTS-PEMROGRAMAN-WEB-5D/
   ```

---

## 🔑 Kredensial Akun Bawaan Siap Uji

Sistem telah dilengkapi akun uji coba bawaan (*pre-seeded account*) yang otomatis diinisialisasi melalui `bootstrap.php`:

| Tipe Akun | Email Login | Password | Hak Akses Utama |
|---|---|:---:|---|
| **Administrator** | `admin@example.com` | `admin123` | Dashboard Admin, CRUD E-Book, Kelola Kategori, Daftar Member, Pantau Seluruh Transaksi |
| **Member (Anggota)** | `member@example.com` | `member123` | Dashboard Member, Pinjam E-Book, Pinjaman Saya, Kembalikan Buku, Riwayat Transaksi, Profil |

> **Catatan Registrasi:** Penguji juga dapat mendaftarkan akun baru secara langsung melalui menu **Daftar Akun** (`register.php`). Setiap pendaftaran publik otomatis terdaftar sebagai peran **Member**.

---

## 🧪 Skenario Demonstrasi Alur Fitur (Panduan Uji Coba 5 Menit)

Berikut adalah panduan langkah demi langkah untuk menguji seluruh fitur yang dipersyaratkan soal UTS:

### 1. Eksplorasi Publik (Guest)
1. Buka halaman utama: `http://localhost:8000/`.
2. Klik menu **Katalog** (`catalog.php`).
3. Coba lakukan pencarian pada kolom search (contoh: ketik `PHP` atau `Andrew Ng`) lalu tekan Enter.
4. Coba filter berdasarkan Kategori (contoh: *Kecerdasan Buatan & Data Science*).
5. Klik tombol **Lihat Detail** pada salah satu buku untuk melihat informasi bibliografi lengkap.

### 2. Pengujian Alur Member (Peminjaman & Pengembalian)
1. Klik menu **Masuk** (`login.php`), masukkan akun Member:
   - Email: `member@example.com` | Password: `member123`
2. Sistem akan mengarahkan ke **Dashboard Member** (`member/dashboard.php`). Perhatikan kartu kuota pinjaman aktif dan demonstrasi Polimorfisme OOP (`$userModel->getRoleDescription()`).
3. Klik tombol **Jelajahi Katalog E-Book**, pilih buku yang berstatus **Tersedia** (berbadge hijau), klik **Lihat Detail**, lalu klik tombol **Pinjam E-Book Ini**.
4. Muncul dialog modal konfirmasi modern: pilih **Ya, Konfirmasi Pinjam**.
5. Sistem memproses transaksi di backend:
   - ID transaksi otomatis digenerate dengan format `TRX-YYYYMMDD-XXXXXX`.
   - Tanggal jatuh tempo otomatis dihitung **7 hari** ke depan.
   - Status buku otomatis berubah menjadi **Tidak Tersedia** di katalog.
   - Pengguna diarahkan ke halaman **Pinjaman Saya** (`member/loans.php`).
6. Uji Pengembalian Buku: Pada halaman Pinjaman Saya, klik tombol **Kembalikan** &rarr; Konfirmasi &rarr; E-book sukses dikembalikan, berpindah ke **Riwayat Transaksi** (`member/history.php`), dan status buku di katalog otomatis kembali **Tersedia**.
7. Buka halaman **Profil** (`member/profile.php`) untuk melihat rincian keanggotaan dan kuota pinjam.

### 3. Pengujian Alur Administrator (CRUD & Monitoring)
1. Klik tombol **Keluar** (*Logout*) &rarr; Masuk kembali dengan akun Admin:
   - Email: `admin@example.com` | Password: `admin123`
2. Sistem mengarahkan ke **Dashboard Admin** (`admin/dashboard.php`) yang menampilkan 5 kartu statistik agregasi (*Total E-Book, Jumlah Member, Total Transaksi, Pinjaman Aktif, Kategori*) serta tabel 5 transaksi terbaru.
3. Buka menu **Kelola Buku** (`admin/books.php`):
   - Klik **Tambah E-Book Baru** (`admin/book-form.php`) &rarr; Isi form data buku baru &rarr; Simpan &rarr; Buku baru langsung tampil di katalog.
   - Klik tombol **Edit** pada salah satu buku &rarr; Ubah data &rarr; Simpan.
   - Coba tombol **Hapus**: Sistem memiliki proteksi integritas data, buku yang sedang aktif dipinjam oleh member **ditolak untuk dihapus**.
4. Buka menu **Kategori** (`admin/categories.php`):
   - Tambah kategori baru atau ubah nama kategori yang ada.
   - Sistem memproteksi: kategori yang masih memiliki relasi buku aktif tidak dapat dihapus sembarangan.
5. Buka menu **Daftar Member** (`admin/members.php`):
   - Admin dapat melihat seluruh akun pengguna terdaftar, peran, tanggal registrasi, serta jumlah transaksi aktifnya.
6. Buka menu **Transaksi** (`admin/transactions.php`):
   - Admin dapat memantau seluruh riwayat transaksi perpustakaan dengan filter status (*Dipinjam* / *Dikembalikan*).

---

## 📱 Verifikasi Responsivitas Antarmuka (Mobile & Tablet)

Aplikasi telah diuji dan dirancang responsif secara menyeluruh untuk berbagai resolusi layar:
- **Desktop / Laptop (> 1024px):** Layout grid penuh multi-kolom dengan navigasi horisontal.
- **Tablet (768px – 1024px):** Form filter dan kartu statistik beradaptasi secara otomatis (*fluid grid layout*).
- **Smartphone (< 768px):** 
  - Seluruh tabel otomatis dibungkus dengan kontainer `.table-responsive` (`overflow-x: auto`), mencegah kerusakan tampilan.
  - Menu navigasi otomatis menciut menjadi **Hamburger Toggle Menu (`☰`)** yang dapat dibuka/tutup dengan klik atau tombol keyboard `Escape`.
  - Formulir dan kartu profil menumpuk vertikal (*single-column stack*) sehingga nyaman digunakan dengan satu tangan.

> **Cara Menguji Responsivitas di Browser:**  
> Tekan `F12` pada browser &rarr; Klik ikon **Toggle Device Toolbar** (`Ctrl + Shift + M`) &rarr; Pilih perangkat *iPhone 14, Samsung Galaxy S20,* atau *iPad Air*. Seluruh komponen tetap rapi dan tidak ada elemen yang meluap (*no horizontal scroll overflow*).

---

## 🧱 Bukti Penerapan 5 Pilar PHP OOP Modern

Sesuai rubrik penilaian backend (bobot 40 poin), berikut letak implementasi konkret konsep OOP:

### 1. Class & Object
- Seluruh model ditempatkan modular pada `src/Models/`: `Book.php`, `Category.php`, `Transaction.php`, `User.php`, `Member.php`, `Admin.php`.
- Contoh di kode: data flat-file JSON tidak dikelola secara array mentah di halaman presentasi, melainkan dihidrasi menjadi objek:
  ```php
  $book = new Book($id, $title, $author, $categoryId, $year, $description, $cover, $available);
  ```

### 2. Encapsulation (Enkapsulasi)
- Semua atribut model dideklarasikan dengan visibilitas `private` atau `protected`.
- Tidak ada properti yang dapat diakses langsung dari luar tanpa metode pengakses (*getter*) atau metode pengendali status:
  - Contoh pada `src/Models/Transaction.php`: Perubahan status transaksi hanya dapat terjadi melalui method `markReturned()` yang memvalidasi kondisi status sebelumnya.
  - Contoh pada `src/Models/User.php`: Password tidak pernah dapat dibaca langsung, melainkan diverifikasi melalui method `verifyPassword(string $plainPassword): bool`.

### 3. Inheritance (Pewarisan)
- Class abstrak `App\Models\User` mewariskan atribut `id`, `name`, `email`, `passwordHash`, dan `role` kepada dua *subclass*:
  - `class Member extends User`: Menambahkan atribut privat `$loanLimit = 3` dan method evaluasi kuota `canBorrow(int $currentLoans)`.
  - `class Admin extends User`: Menambahkan wewenang tata kelola sistem `canManageBooks()` dan `canManageMembers()`.

### 4. Polymorphism (Polimorfisme)
- **Method Overriding:** Method abstrak `User::getRoleDescription(): string` di-override oleh class `Member` dan `Admin` dengan hasil yang berbeda:
  - Pada `Member`: *"Member E-Library: Anggota terdaftar dengan hak peminjaman e-book aktif maksimal 3 item sekaligus."*
  - Pada `Admin`: *"Administrator E-Library: Pengelola sistem dengan wewenang penuh atas katalog e-book, kategori, data pengguna, dan seluruh transaksi."*
  - Saat dipanggil di dashboard (`member/dashboard.php` dan `admin/dashboard.php`), pemanggilan method dieksekusi secara polimorfik melalui referensi bertipe parent `User`:
    ```php
    /** @var User|null $userModel */
    $userModel = $authService->findUserById($currentUserId);
    echo $userModel->getRoleDescription();
    ```
- **Interface Implementation:** Class `Book` mengimplementasikan interface `BorrowableInterface` yang memuat kontrak polimorfik: `isAvailable()`, `markBorrowed()`, dan `markReturned()`.

### 5. Namespace & Autoloader PSR-4
- Terstruktur rapi dalam namespace `App\Core`, `App\Models`, `App\Services`, dan `App\Interfaces`.
- Dimuat otomatis oleh autoloader bawaan tanpa Composer pada `bootstrap.php`:
  ```php
  spl_autoload_register(static function (string $class): void {
      $prefix = 'App\\';
      $baseDir = __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
      // Pemetaan namespace PSR-4 ke struktur direktori fisik src/
  });
  ```

---

## 🧪 Eksekusi Pengujian Otomatis (99 Skenario — 100% Lulus)

Sistem telah diuji secara komprehensif melalui 4 berkas test suite mandiri. Untuk membuktikannya, jalankan perintah berikut di terminal:

```bash
php tests/AuthTestSuite.php
php tests/CatalogTestSuite.php
php tests/LoanTestSuite.php
php tests/IntegrationTestSuite.php
```

### Hasil Uji:
- **`AuthTestSuite.php`:** **10 Lulus, 0 Gagal** (Registrasi, password hash, verifikasi kredensial, proteksi role, session ID regeneration).
- **`CatalogTestSuite.php`:** **12 Lulus, 0 Gagal** (Enkapsulasi Book/Category, pencarian judul/penulis, filter kategori, CRUD admin, proteksi integritas).
- **`LoanTestSuite.php`:** **32 Lulus, 0 Gagal** (Logika peminjaman, format ID transaksi `TRX-YYYYMMDD-XXXXXX`, jatuh tempo 7 hari, verifikasi hak milik saat pengembalian, batas kuota simultan 3 buku, ketahanan berkas rusak).
- **`IntegrationTestSuite.php`:** **45 Lulus, 0 Gagal** (Integrasi end-to-end antar-modul, lima pilar OOP, helper sanitasi XSS `e()`, agregasi metrik dashboard, konsistensi skema JSON).
- **Total Keseluruhan:** **99 SKENARIO LULUS (100% SUKSES)**.

---

## 📂 Struktur Berkas Proyek

```text
UTS-PEMROGRAMAN-WEB-5D/
├── index.php                     # Halaman beranda publik
├── catalog.php                   # Katalog e-book, pencarian & filter
├── detail.php                    # Detail e-book & tombol peminjaman
├── login.php                     # Halaman login
├── register.php                  # Halaman registrasi anggota baru
├── logout.php                    # Handler logout & penghancur sesi
├── bootstrap.php                 # Autoloader PSR-4, inisialisasi sesi, helper global & seed
├── README.md                     # Dokumentasi teknis & panduan lengkap pengujian
│
├── config/
│   └── app.php                   # Konfigurasi nama aplikasi, durasi pinjam, batas kuota
│
├── src/                          # Backend Engine Berbasis PHP 8.x OOP Modern
│   ├── Core/
│   │   ├── JsonStorage.php       # Persistence layer berkas JSON dengan mekanisme penulisan atomik
│   │   └── SessionManager.php    # Pengelola sesi aman & proteksi otorisasi berbasis peran
│   ├── Interfaces/
│   │   └── BorrowableInterface.php # Kontrak objek yang dapat dipinjam
│   ├── Models/
│   │   ├── User.php              # Abstract parent class pengguna
│   │   ├── Member.php            # Subclass User untuk anggota (Inheritance & Polymorphism)
│   │   ├── Admin.php             # Subclass User untuk administrator
│   │   ├── Book.php              # Model koleksi e-book (mengimplementasikan BorrowableInterface)
│   │   ├── Category.php          # Model kategori koleksi e-book
│   │   └── Transaction.php       # Model transaksi peminjaman & pengembalian
│   └── Services/
│       ├── AuthService.php       # Business logic pendaftaran, verifikasi & login
│       ├── BookService.php       # Business logic katalog, pencarian & CRUD buku/kategori
│       └── LoanService.php       # Business logic peminjaman, pengembalian & aturan transaksi
│
├── member/                       # Area Khusus Anggota (Role: member)
│   ├── dashboard.php             # Ringkasan akun & pinjaman aktif terkini
│   ├── loans.php                 # Daftar pinjaman aktif & formulir pengembalian
│   ├── history.php               # Riwayat transaksi peminjaman selesai
│   └── profile.php               # Rincian profil keanggotaan & statistik kuota
│
├── admin/                        # Area Khusus Administrator (Role: admin)
│   ├── dashboard.php             # Metrik statistik sistem & transaksi terbaru
│   ├── books.php                 # Manajemen katalog e-book
│   ├── book-form.php             # Formulir tambah & ubah data e-book
│   ├── categories.php            # Manajemen kategori e-book
│   ├── members.php               # Pemantauan daftar member terdaftar
│   └── transactions.php          # Pemantauan seluruh transaksi sistem
│
├── actions/                      # Endpoint Penanganan Aksi HTTP POST
│   ├── login-process.php         # Pemrosesan login
│   ├── register-process.php      # Pemrosesan registrasi
│   ├── borrow.php                # Pemrosesan transaksi pinjam
│   ├── return.php                # Pemrosesan transaksi pengembalian
│   ├── book-save.php             # Pemrosesan simpan/edit buku
│   ├── book-delete.php           # Pemrosesan hapus buku
│   ├── category-save.php         # Pemrosesan simpan/edit kategori
│   └── category-delete.php       # Pemrosesan hapus kategori
│
├── storage/                      # Penyimpanan Data Flat-file JSON Lokal
│   ├── users.json                # Data pengguna (hash password aman)
│   ├── books.json                # Metadata katalog e-book
│   ├── categories.json           # Taksonomi kategori e-book
│   └── transactions.json         # Log transaksi peminjaman & pengembalian
│
├── assets/                       # Aset Antarmuka Front-End
│   ├── css/
│   │   └── style.css             # Desain antarmuka responsif tanpa framework CSS
│   ├── js/
│   │   └── app.js                # Interaktivitas DOM, validasi & modal dialog vanilla JS
│   └── images/
│       ├── default-book.png      # Cover buku raster cadangan
│       └── default-book.svg      # Cover buku vektor cadangan
│
└── tests/                        # Suite Pengujian Otomatis (99 Skenario Pengujian)
    ├── AuthTestSuite.php         # Pengujian modul autentikasi (10 skenario)
    ├── CatalogTestSuite.php      # Pengujian modul katalog & CRUD (12 skenario)
    ├── LoanTestSuite.php         # Pengujian modul transaksi & aturan bisnis (32 skenario)
    └── IntegrationTestSuite.php  # Pengujian integrasi E2E lengkap (45 skenario)
```

---

## 🛡️ Catatan Keterbatasan Prototipe Akademik

1. **Penyimpanan Berkas JSON:** Penyimpanan data menggunakan file JSON lokal sesuai ketentuan opsi soal UTS (tidak menggunakan RDBMS seperti MySQL) guna mempermudah portabilitas evaluasi tanpa konfigurasi server database.
2. **Simulasi Transaksi:** Peminjaman merupakan simulasi hak akses peminjaman buku elektronik dan penegakan tenggat jatuh tempo di lingkungan perpustakaan digital.
3. **Format Hash Password:** Password disimpan dengan standar enkripsi industri menggunakan `password_hash()` (algoritma `BCRYPT`) dan diverifikasi secara aman dengan `password_verify()`.

---
*Dibuat dan disusun dengan dedikasi penuh untuk Ujian Tengah Semester (UTS) Pemrograman Web 2026/2027 — Program Studi Informatika, Fakultas Teknik, Universitas Muhammadiyah Malang.*
