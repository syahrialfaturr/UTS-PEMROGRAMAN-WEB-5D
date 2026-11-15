# 📚 E-Library — Sistem Perpustakaan Digital & Peminjaman E-Book

> **Tugas Ujian Tengah Semester (UTS) Pemrograman Web — Semester Ganjil 2026/2027**  
> Program Studi Informatika, Fakultas Teknik, Universitas Muhammadiyah Malang  
> **Teknologi Backend:** PHP 8.x Berorientasi Objek (OOP Murni Tanpa Framework)  
> **Teknologi Frontend:** HTML5 Semantik, CSS3 Responsif (Custom Design System), JavaScript Vanilla  
> **Persistensi Data:** Flat-file JSON Storage Lokal Berbasis Objek Model  

---

## 1. 📖 Deskripsi Proyek

**E-Library** adalah prototipe aplikasi perpustakaan digital interaktif yang dirancang untuk mengelola katalog buku elektronik, keanggotaan pengguna (*User / Member / Admin*), serta simulasi transaksi peminjaman dan pengembalian e-book secara aman, modular, dan konsisten.

Proyek ini dibangun secara kolaboratif oleh tim mahasiswa dengan menerapkan standar arsitektur perangkat lunak berbasis objek (*Object-Oriented Programming* / OOP), pemisahan tanggung jawab (*Separation of Concerns*), penanganan persistensi file atomik (*flock*), serta validasi berlapis di sisi klien dan server tanpa menggunakan framework PHP eksternal (seperti Laravel/CodeIgniter) maupun framework JavaScript (seperti React/Vue).

---

## 2. 👥 Pembagian Peran dan Tanggung Jawab Tim

| Anggota Tim | Peran Utama | Modul / Berkas yang Dikerjakan | Branch Git |
|---|---|---|---|
| **Syahrial Fathur** | *OOP Foundation & Authentication Engineer* | Fondasi `User`, `Member`, `Admin`, `AuthService`, `SessionManager`, `login.php`, `register.php`, `logout.php`, `actions/login-process.php`, `actions/register-process.php`, `tests/AuthTestSuite.php` | `feature/auth` |
| **Mail** | *Catalog & Content Management Developer* | `Book`, `Category`, `BorrowableInterface`, `BookService`, `catalog.php`, `detail.php`, `admin/books.php`, `admin/book-form.php`, `admin/categories.php`, `actions/book-save.php`, `actions/book-delete.php`, `actions/category-save.php`, `actions/category-delete.php`, `tests/CatalogTestSuite.php` | `feature/catalog` |
| **Farid** | *Transaction Engine & Business Logic Specialist* | `Transaction`, `LoanService`, `member/loans.php`, `member/history.php`, `admin/transactions.php`, `actions/borrow.php`, `actions/return.php`, `tests/LoanTestSuite.php` | `feature/transaction` |
| **Robi** | *Front-End Designer & UI/UX Engineer* | Design System, `assets/css/style.css`, interaktivitas klien `assets/js/app.js`, modal dialog aksesibel, toggle password, validasi form, responsivitas mobile/tablet | `feature/frontend` |
| **Naufal Arkaan** | *Lead Developer, Integrator, Dashboard & QA* | Arsitektur integrasi sistem, `bootstrap.php`, autoloader PSR-4, perbaikan dependensi cross-module, `member/dashboard.php`, `member/profile.php`, `admin/dashboard.php`, `admin/members.php`, `tests/IntegrationTestSuite.php`, dokumentasi `README.md` | `feature/integration` |

---

## 3. 🎯 Fitur Sistem Berdasarkan Hak Akses

### A. Pengunjung (Guest / Publik)
- **Beranda Interaktif (`index.php`):** Menampilkan banner hero, navigasi dinamis, statistik koleksi, kategori populer, dan e-book unggulan.
- **Katalog E-Book (`catalog.php`):** Pencarian instan berdasarkan judul/penulis, filter multi-kategori, filter ketersediaan, serta pagination/status count.
- **Detail E-Book (`detail.php`):** Informasi metadata lengkap (judul, penulis, tahun, kategori, status ketersediaan, deskripsi).
- **Pendaftaran Akun (`register.php`):** Validasi form sisi browser (JavaScript) dan validasi ketat di sisi server (PHP), pencegahan email duplikat, dan enkripsi password aman.
- **Masuk Sistem (`login.php`):** Autentikasi dengan verifikasi hash password, proteksi user enumeration, dan regenerasi ID sesi.

### B. Anggota (Member)
- **Dashboard Member (`member/dashboard.php`):** Ringkasan kuota peminjaman aktif, riwayat transaksi selesai, demonstrasi polimorfisme OOP, pintasan navigasi cepat, dan tabel peminjaman terkini.
- **Peminjaman E-Book (`actions/borrow.php`):** Member dapat meminjam e-book yang berstatus tersedia. Otomatis menetapkan tenggat jatuh tempo 7 hari ke depan. Dibatasi maksimal 3 buku simultan.
- **Pinjaman Saya (`member/loans.php`):** Daftar seluruh buku yang sedang aktif dipinjam beserta indikator peringatan jatuh tempo (*overdue*).
- **Pengembalian E-Book (`actions/return.php`):** Mengembalikan buku yang sedang dipinjam dengan verifikasi kepemilikan transaksi di sisi backend. E-book otomatis kembali berstatus tersedia.
- **Riwayat Transaksi (`member/history.php`):** Catatan seluruh transaksi masa lalu lengkap dengan stempel waktu peminjaman dan tanggal pengembalian.
- **Profil Anggota (`member/profile.php`):** Informasi identitas member, batas kuota pinjam, dan status hak meminjam buku baru.

### C. Administrator (Admin)
- **Dashboard Admin (`admin/dashboard.php`):** Metrik ringkasan sistem (total e-book, total kategori, jumlah member terdaftar, total transaksi, pinjaman aktif) dan tabel 5 transaksi terbaru.
- **Manajemen E-Book (`admin/books.php` & `admin/book-form.php`):** Operasi CRUD (*Create, Read, Update, Delete*) katalog buku. Dilengkapi proteksi integritas: e-book yang sedang dipinjam tidak dapat dihapus.
- **Manajemen Kategori (`admin/categories.php`):** Tambah dan ubah nama kategori. Kategori yang masih digunakan oleh e-book di katalog dilindungi agar tidak dapat dihapus sembarangan.
- **Daftar Member (`admin/members.php`):** Pemantauan seluruh akun terdaftar, batas kuota, dan jumlah transaksi aktif masing-masing anggota.
- **Pantau Transaksi (`admin/transactions.php`):** Inspeksi seluruh transaksi peminjaman di sistem dengan filter status (*borrowed* / *returned*).

---

## 4. 🧱 Penerapan Prinsip Pemrograman Berorientasi Objek (PHP OOP)

Sesuai rubrik penilaian UTS (bobot 40 poin), seluruh implementasi backend menggunakan paradigma OOP murni:

1. **Class & Object:**
   Setiap entitas direpresentasikan sebagai class mandiri di dalam folder `src/`. Instansiasi objek digunakan aktif untuk manipulasi data (misalnya objek `Book`, `Category`, `Transaction`, `Member`, `Admin`).
2. **Encapsulation (Enkapsulasi):**
   Seluruh atribut pada class model dideklarasikan dengan visibilitas `private` atau `protected`. Akses dan modifikasi data dilakukan melalui metode pengakses (*getter*) dan metode operasi bisnis (misalnya `Book::markBorrowed()`, `Transaction::markReturned()`, `User::verifyPassword()`).
3. **Inheritance (Pewarisan):**
   Class abstrak `App\Models\User` bertindak sebagai *parent class* yang mewariskan atribut umum (`id`, `name`, `email`, `passwordHash`, `role`) kepada dua *subclass* turunan: `App\Models\Member` dan `App\Models\Admin`.
4. **Polymorphism (Polimorfisme):**
   - **Method Overriding:** Method abstrak `User::getRoleDescription()` di-override secara spesifik oleh `Member` (menjelaskan kuota peminjaman) dan `Admin` (menjelaskan wewenang tata kelola). Ketika dipanggil melalui referensi polimorfik bertipe `User`, metode ini mengeksekusi perilaku yang relevan sesuai kelas konkretnya.
   - **Interface Implementation:** Class `Book` mengimplementasikan `BorrowableInterface` yang memuat kontrak `isAvailable()`, `markBorrowed()`, dan `markReturned()`.
5. **Namespace & Autoloading:**
   Pengelompokan class terstruktur rapi ke dalam namespace:
   - `App\Core`: Komponen fondasi (`JsonStorage`, `SessionManager`).
   - `App\Models`: Model entitas domain (`User`, `Member`, `Admin`, `Book`, `Category`, `Transaction`).
   - `App\Services`: Layanan logika bisnis (`AuthService`, `BookService`, `LoanService`).
   - `App\Interfaces`: Kontrak polimorfik (`BorrowableInterface`).
   Pemuatan kelas dilakukan secara otomatis menggunakan `spl_autoload_register()` pada `bootstrap.php` tanpa perlu memanggil `require` manual di setiap class.

---

## 5. 🛠️ Persyaratan Sistem & Spesifikasi

- **PHP Runtime:** PHP versi 8.1 atau lebih tinggi (Direkomendasikan PHP 8.2+).
- **Ekstensi PHP Wajib:** `json`, `mbstring`, `session`, `pcre`.
- **Web Server:** Server lokal bawaan PHP (*CLI built-in server*), Apache (XAMPP / Laragon / WampServer), atau Nginx.
- **Sistem Operasi:** Kompatibel dengan Windows, Linux, dan macOS.

---

## 6. 📂 Struktur Direktori Proyek

```text
UTS-PEMROGRAMAN-WEB-5D/
├── index.php                     # Halaman beranda publik
├── catalog.php                   # Katalog e-book, pencarian & filter
├── detail.php                    # Detail e-book & tombol peminjaman
├── login.php                     # Halaman masuk sistem
├── register.php                  # Halaman pendaftaran member baru
├── logout.php                    # Skrip penghentian sesi login
├── bootstrap.php                 # Autoloader PSR-4, inisialisasi sesi, helper global & seed
├── README.md                     # Dokumentasi teknis & panduan lengkap
│
├── config/
│   └── app.php                   # Konfigurasi nama aplikasi, durasi pinjam, batas kuota
│
├── src/                          # Backend Engine Berbasis PHP OOP Modern
│   ├── Core/
│   │   ├── JsonStorage.php       # Utilitas abstraksi persistensi berkas JSON atomik
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
├── storage/                      # Penyimpanan Data Lokal Berbasis Flat-file JSON
│   ├── users.json                # Data pengguna terdaftar (password dalam format hash)
│   ├── books.json                # Metadata katalog e-book
│   ├── categories.json           # Data taksonomi kategori
│   └── transactions.json         # Data transaksi peminjaman & pengembalian
│
├── assets/                       # Aset Statis Front-End
│   ├── css/
│   │   └── style.css             # Desain antarmuka responsif tanpa framework CSS
│   ├── js/
│   │   └── app.js                # Interaktivitas DOM, validasi & modal dialog vanilla JS
│   └── images/
│       ├── default-book.png      # Cover buku cadangan
│       └── default-book.svg      # Vektor sampul buku default
│
└── tests/                        # Suite Pengujian Otomatis (99 Skenario Pengujian)
    ├── AuthTestSuite.php         # Pengujian modul autentikasi (10 skenario)
    ├── CatalogTestSuite.php      # Pengujian modul katalog & CRUD (12 skenario)
    ├── LoanTestSuite.php         # Pengujian modul transaksi & aturan bisnis (32 skenario)
    └── IntegrationTestSuite.php  # Pengujian integrasi E2E lengkap (45 skenario)
```

---

## 7. 🚀 Panduan Instalasi & Menjalankan Aplikasi

Aplikasi tidak memerlukan instalasi Composer maupun dependensi npm karena dibangun dengan vanilla stack murni.

### Opsi A: Menggunakan PHP Built-in Server (Paling Cepat & Direkomendasikan)
1. Buka terminal (PowerShell, Command Prompt, atau Terminal bash).
2. Pindah ke direktori proyek:
   ```bash
   cd "c:/Pelajaran-Kuliah-UMM/Pemrograman Web/Kelas Teori/uts-web/UTS-PEMROGRAMAN-WEB-5D"
   ```
3. Jalankan server lokal bawaan PHP pada port 8000:
   ```bash
   php -S localhost:8000
   ```
4. Buka peramban (*web browser*) dan akses:
   ```text
   http://localhost:8000
   ```

### Opsi B: Menggunakan Web Server Lokal (XAMPP / Laragon)
1. Salin folder proyek ke direktori `htdocs` (XAMPP) atau `www` (Laragon):
   ```text
   C:\xampp\htdocs\e-library
   ```
2. Pastikan servis Apache sudah berjalan.
3. Buka peramban dan navigasikan ke:
   ```text
   http://localhost/e-library/
   ```

---

## 8. 🔐 Kredensial Akun Pengujian & Pembuatan Akun Awal

Sistem secara otomatis menginisialisasi berkas data awal pada `storage/` melalui pemanggilan fungsi `ensure_initial_seed()` di `bootstrap.php`.

### Kredensial Akun Bawaan (Default Seed):
1. **Akun Administrator:**
   - **Email:** `admin@example.com`
   - **Password:** `admin123`
   - **Peran:** `admin` (Diarahkan ke `admin/dashboard.php`)
2. **Akun Member:**
   - **Email:** `member@example.com`
   - **Password:** `member123`
   - **Peran:** `member` (Diarahkan ke `member/dashboard.php`)

### Prosedur Keamanan Pembuatan Akun Admin Baru:
Untuk alasan keamanan akademik, pembuatan akun bertipe `admin` tidak dibuka pada formulir pendaftaran publik `register.php` (seluruh pendaftaran publik otomatis diberi peran `member`). Untuk membuat akun Admin baru secara aman:
1. Buka berkas `storage/users.json`.
2. Hasilkan hash password aman di terminal:
   ```bash
   php -r "echo password_hash('PasswordAdminBaru123', PASSWORD_DEFAULT);"
   ```
3. Tambahkan objek JSON pengguna dengan nilai `"role": "admin"` dan paste hash password yang dihasilkan.

---

## 9. 📋 Kontrak Format Data JSON

### 1. `storage/users.json`
```json
[
  {
    "id": "USR-001",
    "name": "Administrator E-Library",
    "email": "admin@example.com",
    "password": "$2y$10$y3/OnR.oqkhJzloGwWFSaObPNgnHtz4IVA/9nGFLaDv8RzCMeOrwW",
    "role": "admin",
    "created_at": "2026-11-15 12:14:04"
  }
]
```

### 2. `storage/books.json`
```json
[
  {
    "id": "BOOK-001",
    "title": "Arsitektur PHP Modern: OOP, Design Patterns & Clean Code",
    "author": "Rasmus Lerdorf & Martin Fowler",
    "category_id": "CAT-001",
    "year": 2024,
    "description": "Panduan mendalam arsitektur pemrograman berorientasi objek modern PHP 8.x.",
    "cover": "assets/images/default-book.png",
    "available": true
  }
]
```

### 3. `storage/categories.json`
```json
[
  {
    "id": "CAT-001",
    "name": "Rekayasa Perangkat Lunak & Web"
  }
]
```

### 4. `storage/transactions.json`
```json
[
  {
    "id": "TRX-20261110-A1B2C3",
    "user_id": "USR-002",
    "book_id": "BOOK-003",
    "borrowed_at": "2026-11-12 14:10:47",
    "due_at": "2026-11-19 14:10:47",
    "returned_at": null,
    "status": "borrowed"
  }
]
```

---

## 10. 🧪 Rangkaian Pengujian Otomatis (Test Suite)

Proyek ini dilengkapi dengan 4 berkas pengujian otomatis (*Automated Test Suite*) dengan total **99 skenario pengujian** yang menguji seluruh lapisan sistem tanpa mencemari berkas penyimpanan produksi (menggunakan *isolated storage sandbox*):

```bash
# Menjalankan seluruh pengujian modul & integrasi sekaligus:
php tests/AuthTestSuite.php
php tests/CatalogTestSuite.php
php tests/LoanTestSuite.php
php tests/IntegrationTestSuite.php
```

### Ringkasan Cakupan Pengujian:
1. **`tests/AuthTestSuite.php` (10 Skenario - 100% Lulus):**
   - Registrasi akun berhasil, pencegahan email duplikat, verifikasi hash `password_hash()`, verifikasi login sukses/gagal, penghancuran sesi saat logout, role guard (Guest vs Member vs Admin), verifikasi polimorfisme deskripsi peran, dan pertahanan regenerasi Session ID.
2. **`tests/CatalogTestSuite.php` (12 Skenario - 100% Lulus):**
   - Enkapsulasi model `Book` & `Category`, implementasi `BorrowableInterface`, pengambilan seluruh data dari JSON, pencarian judul & penulis *case-insensitive*, penyaringan kategori, pengambilan detail buku, pembuatan buku baru dengan validasi ketat, pembaruan metadata buku, proteksi penghapusan buku yang sedang dipinjam, penghapusan persisten, serta proteksi penghapusan kategori yang masih memiliki relasi buku.
3. **`tests/LoanTestSuite.php` (32 Skenario - 100% Lulus):**
   - Peminjaman buku berstatus tersedia, pembuatan transaksi format `TRX-YYYYMMDD-XXXXXX`, kalkulasi jatuh tempo tepat 7 hari, pengubahan ketersediaan buku menjadi `false`, penolakan peminjaman ganda oleh member lain, penolakan peminjaman oleh Guest / ID tak terdaftar, filter transaksi milik pengguna, verifikasi kepemilikan saat pengembalian (pencegahan *hijacking* oleh member lain), penolakan pengembalian ulang, ketersediaan kembali buku pasca pengembalian, penegakan batas maksimal 3 pinjaman simultan, ketahanan terhadap berkas JSON rusak, dan penanganan berkas transaksi kosong.
4. **`tests/IntegrationTestSuite.php` (45 Skenario - 100% Lulus):**
   - Pengujian integrasi menyeluruh antar-modul (*End-to-End*), verifikasi resolusi autoloader PSR-4, pengujian XSS escaping `e()`, demonstrasi 5 pilar OOP, validasi siklus hidup akun, integritas relasi kategori-buku-transaksi, agregasi metrik dashboard Member & Admin, serta validasi skema kontrak JSON.

---

## 11. 🛡️ Penanganan Keamanan & Keterbatasan Prototipe

### Langkah Keamanan yang Diterapkan:
1. **Password Hashing:** Menggunakan algoritma default PHP (`BCRYPT`) melalui `password_hash()`. Tidak ada password mentah yang tersimpan di berkas JSON maupun sesi.
2. **XSS Protection:** Seluruh output yang berasal dari input pengguna disanitasi menggunakan `e()` (`htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`).
3. **Session Fixation Defense:** Pemanggilan `session_regenerate_id(true)` setiap kali login berhasil.
4. **Backend Role Guards:** Setiap halaman privat (`admin/*` dan `member/*`) serta handler `actions/*` memeriksa otentikasi dan otorisasi sesi di level server, bukan sekadar menyembunyikan tombol.
5. **Strict HTTP Methods:** Seluruh mutasi data (registrasi, login, simpan buku, hapus buku, pinjam, kembalikan) hanya menerima metode `POST`. Permintaan `GET` langsung ditolak.
6. **Data Consistency & File Locking:** Operasi peminjaman dan pengembalian buku mengeksekusi mekanisme kunci file eksklusif (`flock(LOCK_EX)`) untuk mencegah kondisi balapan (*race condition*) antar-pengguna.

### Keterbatasan Prototipe Akademik:
1. **Penyimpanan Flat-File JSON:** Sistem ini dirancang sebagai prototipe akademik berbasis file lokal sesuai spesifikasi soal UTS tanpa database SQL server (MySQL/PostgreSQL).
2. **Simulasi E-Book:** Fitur peminjaman merupakan simulasi hak akses peminjaman e-book dan pencatatan jatuh tempo, bukan sistem DRM pembaca PDF berbasis streaming berlisensi.
3. **Penyimpanan Gambar Sampul:** Unggahan file fisik cover menggunakan path lokal bawaan (`assets/images/default-book.png`).

---
*Dikembangkan dengan bangga untuk Ujian Tengah Semester (UTS) Pemrograman Web 2026/2027 — Program Studi Informatika UMM.*
