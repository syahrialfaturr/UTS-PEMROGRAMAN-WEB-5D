# Dokumen Spesifikasi dan Prompt Pengembangan
## UTS Pemrograman Web — Semester Ganjil 2026/2027

**Program Studi:** Informatika, Fakultas Teknik, Universitas Muhammadiyah Malang  
**Kelas:** C/D/E  
**Teknologi wajib dari soal:** HTML, CSS, JavaScript, PHP 8.x OOP tanpa framework  
**Konsep proyek yang diusulkan:** aplikasi perpustakaan digital dan peminjaman e-book

---

# BAGIAN I — DASAR DAN INTERPRETASI TUGAS

## 1. Acuan tugas

Dokumen ini disusun berdasarkan PDF *UTS Pemrograman Web Semester Ganjil 2026/2027*, halaman 1.

Instruksi inti pada soal:
- Mengembangkan prototipe aplikasi web interaktif.
- Mengintegrasikan antarmuka front-end (HTML, CSS, JavaScript) dengan backend engine berbasis PHP OOP Modern (PHP 8.x), tanpa framework.
- Sistem mengelola katalog media/layanan, penanganan pengguna (User/Member), serta simulasi transaksi atau transaksi penyewaan/peminjaman e-book.
- Penyimpanan e-book diperbolehkan menggunakan XML/JSON atau Object Class PHP.

Rubrik pada halaman 1:
| Komponen | Indikator | Bobot maksimal |
|---|---|---:|
| Front-End (HTML/CSS/JS) | Validasi form, elemen semantik, layout responsif, interaktivitas JS | 30 |
| Penerapan PHP OOP | Class, Object, Encapsulation, Inheritance, Polymorphism, Namespace | 40 |
| Kerapian & Struktur Berkas | Folder `src/`, modularitas berkas, sistem berjalan tanpa error | 30 |
| **Total** | | **100** |

Catatan: soal tidak menetapkan nama aplikasi, desain UI, jumlah halaman, database, framework CSS, maupun pembagian anggota. Keputusan teknis di bawah adalah rancangan yang direkomendasikan agar tugas dapat dikerjakan secara terarah, bukan kutipan persyaratan tambahan dari dosen.

## 2. Konsep aplikasi

**Nama sementara:** E-Library  
**Deskripsi:** prototipe perpustakaan digital yang memungkinkan pengunjung melihat katalog e-book, pengguna mendaftar dan masuk, member meminjam e-book, serta melihat daftar pinjaman dan mengembalikan e-book melalui simulasi transaksi.

Aplikasi ini dipilih karena secara langsung mencakup tiga domain yang disebut dalam soal:
1. **Katalog media:** daftar e-book, detail, kategori, pencarian, dan ketersediaan.
2. **Pengguna:** registrasi, login, profil, serta peran User/Member dan Admin.
3. **Transaksi:** peminjaman dan pengembalian e-book dengan status dan riwayat.

### 2.1 Batasan prototipe
- Tidak menggunakan framework backend seperti Laravel, CodeIgniter, Symfony, atau framework PHP lainnya.
- Tidak menggunakan framework front-end seperti React, Vue, atau Angular. Gunakan HTML, CSS, dan JavaScript biasa.
- PHP minimal versi 8.x.
- Penyimpanan data dapat memakai JSON, sesuai opsi yang diizinkan soal. Gunakan file JSON lokal sebagai penyimpanan prototipe agar mudah dijalankan tanpa konfigurasi database.
- Password harus disimpan sebagai hash menggunakan `password_hash()` dan diverifikasi dengan `password_verify()`.
- Login menggunakan PHP Session.
- Peminjaman merupakan simulasi dalam aplikasi, bukan pembayaran atau integrasi layanan eksternal.
- File e-book asli/PDF tidak wajib disediakan. Katalog dapat menampilkan metadata dan tombol baca/lihat detail. Jika file demo disediakan, gunakan file yang memang tersedia di proyek.
- Jangan mengklaim fitur keamanan atau transaksi produksi. Ini adalah prototipe akademik.

## 3. Tujuan sistem

1. Menyajikan katalog e-book yang dapat dicari dan difilter.
2. Memungkinkan pengunjung membuat akun dan login.
3. Membedakan hak akses Admin dan Member.
4. Memungkinkan Admin mengelola data e-book dan memantau transaksi.
5. Memungkinkan Member meminjam dan mengembalikan e-book.
6. Menampilkan status ketersediaan serta riwayat peminjaman.
7. Memperlihatkan penerapan konsep OOP PHP secara eksplisit dan mudah diperiksa saat demonstrasi.

## 4. Aktor dan hak akses

| Aktor | Hak akses |
|---|---|
| Pengunjung (Guest) | Melihat katalog, mencari e-book, melihat detail, registrasi, login |
| Member | Seluruh akses Guest setelah login, meminjam e-book, melihat pinjaman aktif dan riwayat, mengembalikan e-book, melihat profil |
| Admin | Mengelola e-book dan kategori, melihat daftar member, melihat seluruh transaksi, mengelola status transaksi bila diperlukan |

**Aturan akses:** halaman dan aksi khusus Member/Admin harus memeriksa session dan role di sisi PHP. Menyembunyikan tombol dengan JavaScript saja tidak cukup.

## 5. Ruang lingkup fitur

### 5.1 Halaman publik
- Beranda: ringkasan aplikasi, e-book unggulan, kategori, dan tombol menuju katalog.
- Katalog: daftar e-book, pencarian berdasarkan judul/penulis, filter kategori, dan label ketersediaan.
- Detail e-book: sampul, judul, penulis, kategori, tahun, deskripsi, dan status.
- Registrasi dan login.

### 5.2 Area Member
- Dashboard member: ringkasan jumlah pinjaman aktif dan riwayat.
- Peminjaman: memilih e-book yang tersedia dan mengonfirmasi peminjaman.
- Pinjaman saya: daftar pinjaman aktif, tanggal pinjam, tenggat, dan status.
- Pengembalian: aksi pengembalian untuk pinjaman aktif.
- Riwayat: daftar transaksi yang sudah selesai.
- Profil: melihat dan memperbarui data dasar bila waktu memungkinkan.

### 5.3 Area Admin
- Dashboard admin: ringkasan jumlah e-book, member, dan transaksi.
- Manajemen e-book: tambah, lihat, ubah, dan hapus data.
- Manajemen kategori: tambah dan ubah kategori; penghapusan harus mempertimbangkan e-book yang masih memakai kategori tersebut.
- Data member: melihat daftar pengguna.
- Manajemen transaksi: melihat seluruh transaksi dan status peminjaman/pengembalian.

### 5.4 Aturan bisnis transaksi
1. Hanya Member yang sudah login dapat meminjam.
2. E-book hanya dapat dipinjam jika statusnya tersedia.
3. Satu transaksi menghubungkan satu member dengan satu e-book.
4. Saat peminjaman berhasil, sistem membuat ID transaksi, tanggal pinjam, tanggal jatuh tempo, dan status `borrowed`.
5. Saat pengembalian berhasil, status transaksi berubah menjadi `returned` dan ketersediaan e-book diperbarui.
6. Member tidak boleh mengembalikan transaksi milik member lain.
7. Member tidak boleh meminjam e-book yang sedang tidak tersedia.
8. Perubahan data transaksi dan ketersediaan harus konsisten. Validasi dilakukan kembali di backend, bukan hanya di browser.
9. Lama peminjaman dapat ditetapkan **7 hari** sebagai keputusan rancangan prototipe. Nilai ini harus dibuat terpusat agar mudah diubah.
10. Jika sistem memakai JSON, proses baca-tulis file perlu menangani kegagalan file dan menghindari penulisan data parsial sebisa mungkin.

## 6. Alur penggunaan utama

### 6.1 Registrasi dan login
1. Pengunjung membuka halaman registrasi.
2. Mengisi nama, email, dan password.
3. Front-end memvalidasi input dasar.
4. Backend memvalidasi ulang, memeriksa email duplikat, melakukan hash password, lalu menyimpan akun.
5. Pengguna login dengan email dan password.
6. Backend memverifikasi password dan membuat session.
7. Sistem mengarahkan pengguna sesuai role.

### 6.2 Peminjaman e-book
1. Member membuka katalog atau detail e-book.
2. Member menekan tombol Pinjam.
3. Sistem meminta konfirmasi.
4. Backend memeriksa session, role, keberadaan e-book, dan ketersediaannya.
5. Sistem membuat transaksi dan memperbarui status e-book.
6. Member menerima notifikasi dan dapat melihat transaksi di halaman Pinjaman Saya.

### 6.3 Pengembalian e-book
1. Member membuka Pinjaman Saya.
2. Member memilih transaksi berstatus `borrowed`.
3. Member mengonfirmasi pengembalian.
4. Backend memverifikasi kepemilikan transaksi.
5. Status transaksi berubah menjadi `returned`; e-book tersedia kembali.
6. Transaksi berpindah/terlihat di riwayat.

### 6.4 Pengelolaan e-book oleh Admin
1. Admin login.
2. Admin membuka halaman manajemen katalog.
3. Admin menambah atau mengubah metadata e-book.
4. Backend memvalidasi input dan menyimpan perubahan.
5. Katalog publik menampilkan data terbaru.

## 7. Rancangan halaman dan komponen UI

| Halaman | Komponen utama | Akses |
|---|---|---|
| `index.php` | Navbar, hero, e-book unggulan, kategori, footer | Publik |
| `catalog.php` | Search, filter kategori, grid/list e-book, status | Publik |
| `detail.php` | Cover, metadata, deskripsi, tombol pinjam | Publik/Member |
| `register.php` | Form registrasi | Guest |
| `login.php` | Form login | Guest |
| `member/dashboard.php` | Statistik ringkas, pinjaman terbaru | Member |
| `member/loans.php` | Tabel/kartu pinjaman, status, tombol kembali | Member |
| `member/history.php` | Riwayat transaksi | Member |
| `member/profile.php` | Informasi profil | Member |
| `admin/dashboard.php` | Statistik sistem | Admin |
| `admin/books.php` | Tabel katalog dan aksi CRUD | Admin |
| `admin/book-form.php` | Form tambah/edit e-book | Admin |
| `admin/categories.php` | Pengelolaan kategori | Admin |
| `admin/members.php` | Daftar member | Admin |
| `admin/transactions.php` | Seluruh transaksi | Admin |

Nama file dapat disesuaikan selama struktur dan alur konsisten.

## 8. Desain data JSON

Gunakan file terpisah di `storage/`. Bentuk data yang disarankan:

### `storage/users.json`
```json
[
  {
    "id": "USR-001",
    "name": "Admin",
    "email": "admin@example.com",
    "password": "$2y$...",
    "role": "admin",
    "created_at": "2026-11-15 10:00:00"
  }
]
```

### `storage/books.json`
```json
[
  {
    "id": "BOOK-001",
    "title": "Dasar Pemrograman Web",
    "author": "Tim Penulis",
    "category_id": "CAT-001",
    "year": 2025,
    "description": "Contoh deskripsi e-book.",
    "cover": "assets/images/default-book.png",
    "available": true
  }
]
```

### `storage/categories.json`
```json
[
  {
    "id": "CAT-001",
    "name": "Teknologi"
  }
]
```

### `storage/transactions.json`
```json
[
  {
    "id": "TRX-001",
    "user_id": "USR-002",
    "book_id": "BOOK-001",
    "borrowed_at": "2026-11-15",
    "due_at": "2026-11-22",
    "returned_at": null,
    "status": "borrowed"
  }
]
```

Catatan implementasi:
- Data contoh hanya seed/demo, bukan data wajib dari soal.
- Password pada JSON harus berupa hash, bukan password asli.
- Gunakan ID unik yang stabil; jangan mengandalkan indeks array sebagai ID.
- Terapkan helper penyimpanan JSON yang konsisten untuk membaca, memvalidasi, dan menulis data.
- Jika memakai data JSON, pastikan folder dan file dapat ditulis oleh PHP pada lingkungan lokal.

## 9. Rancangan arsitektur dan struktur folder

```text
e-library/
├── index.php
├── catalog.php
├── detail.php
├── login.php
├── register.php
├── logout.php
├── bootstrap.php
├── config/
│   └── app.php
├── src/
│   ├── Core/
│   │   ├── JsonStorage.php
│   │   └── SessionManager.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Member.php
│   │   ├── Admin.php
│   │   ├── Book.php
│   │   ├── Category.php
│   │   └── Transaction.php
│   ├── Services/
│   │   ├── AuthService.php
│   │   ├── BookService.php
│   │   └── LoanService.php
│   └── Interfaces/
│       └── BorrowableInterface.php
├── member/
│   ├── dashboard.php
│   ├── loans.php
│   ├── history.php
│   └── profile.php
├── admin/
│   ├── dashboard.php
│   ├── books.php
│   ├── book-form.php
│   ├── categories.php
│   ├── members.php
│   └── transactions.php
├── actions/
│   ├── login-process.php
│   ├── register-process.php
│   ├── borrow.php
│   ├── return.php
│   └── book-save.php
├── storage/
│   ├── users.json
│   ├── books.json
│   ├── categories.json
│   └── transactions.json
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── app.js
│   └── images/
└── README.md
```

Struktur dapat disederhanakan sesuai kebutuhan, tetapi folder `src/` harus benar-benar berisi class PHP yang digunakan aplikasi. Jangan menaruh seluruh logika OOP di satu file.

### 9.1 Namespace dan autoload
Gunakan namespace konsisten, misalnya:
- `App\Core`
- `App\Models`
- `App\Services`
- `App\Interfaces`

Gunakan autoload sederhana dengan `spl_autoload_register()` di `bootstrap.php`; Composer tidak wajib. Pastikan pemetaan namespace ke direktori benar. File halaman memanggil `bootstrap.php` agar konfigurasi, autoload, dan session konsisten.

## 10. Pemetaan class, atribut, method, dan konsep OOP

| Class/Interface | Atribut utama | Method utama | Konsep yang ditunjukkan |
|---|---|---|---|
| `User` (abstract atau base class) | `id`, `name`, `email`, `passwordHash`, `role` | `getId()`, `getName()`, `getEmail()`, `verifyPassword()`, `getRole()` | Encapsulation, inheritance sebagai parent |
| `Member extends User` | `loanLimit` | `canBorrow()`, `getLoanLimit()` | Inheritance |
| `Admin extends User` | — | `canManageBooks()`, `canManageMembers()` | Inheritance, polymorphism bila meng-override method role |
| `Book` | `id`, `title`, `author`, `categoryId`, `year`, `description`, `cover`, `available` | getter, `isAvailable()`, `markBorrowed()`, `markReturned()`, `updateDetails()` | Encapsulation |
| `Category` | `id`, `name` | getter, `rename()` | Class dan object |
| `Transaction` | `id`, `userId`, `bookId`, `borrowedAt`, `dueAt`, `returnedAt`, `status` | `markReturned()`, `isActive()`, getter | Encapsulation |
| `BorrowableInterface` | — | `borrow()`, `returnItem()` | Interface dan polymorphism |
| `JsonStorage` | `filePath` | `all()`, `findById()`, `saveAll()`, `insert()`, `update()`, `delete()` | Modularitas, abstraksi penyimpanan |
| `SessionManager` | — | `start()`, `login()`, `logout()`, `user()`, `requireLogin()`, `requireRole()` | Pengelolaan session |
| `AuthService` | dependency storage | `register()`, `login()`, `logout()` | Pemisahan tanggung jawab |
| `BookService` | dependency storage | `getAll()`, `getById()`, `create()`, `update()`, `delete()` | Business logic |
| `LoanService` | dependency storage | `borrow()`, `returnBook()`, `getMemberLoans()`, `getAllTransactions()` | Business logic dan validasi transaksi |

**Penerapan OOP yang harus terlihat saat demo:**
- **Class/Object:** class `Book` dan objek hasil instansiasi yang digunakan untuk menampilkan/mengelola e-book.
- **Encapsulation:** atribut dibuat `private`/`protected`, akses melalui getter/setter atau method yang sesuai.
- **Inheritance:** `Member` dan `Admin` mewarisi `User`.
- **Polymorphism:** contoh yang mudah diperiksa adalah method `getDashboardLabel()` atau `getRoleDescription()` pada `User` yang di-override oleh `Member` dan `Admin`, atau implementasi interface `BorrowableInterface` oleh objek yang relevan. Jangan sekadar menyebut polymorphism tanpa kode pemanggilan yang menunjukkan perilaku berbeda.
- **Namespace:** class menggunakan namespace dan di-import dengan `use`.
- **Interface/abstract class:** nilai tambah yang mendukung desain, tetapi jangan sampai membuat implementasi berlebihan.

Hindari memaksakan interface pada class yang tidak memiliki perilaku yang masuk akal. Bila interface `BorrowableInterface` digunakan, jelaskan objek mana yang dapat dipinjam dan implementasinya.

## 11. Kontrak integrasi antarbagian

Semua anggota harus mengikuti kontrak berikut:
1. Gunakan satu nama proyek, satu struktur folder, satu konvensi namespace, dan satu format JSON.
2. Jangan membuat class dengan nama sama tetapi implementasi berbeda di cabang masing-masing.
3. Semua halaman memanggil `require_once __DIR__ . '/../bootstrap.php'` atau path relatif yang benar.
4. Semua proses tulis data dilakukan melalui service/repository yang disepakati, bukan menulis JSON sendiri-sendiri dari banyak halaman.
5. Gunakan key data yang sama persis: `id`, `user_id`, `book_id`, `borrowed_at`, `due_at`, `returned_at`, `status`.
6. Role hanya `admin` atau `member`; status transaksi hanya `borrowed` atau `returned`.
7. Aksi form menggunakan method POST untuk perubahan data. GET digunakan untuk membaca halaman/data.
8. Setiap aksi POST memvalidasi input di backend dan mengarahkan kembali dengan pesan sukses/gagal.
9. Output teks dari input pengguna harus di-escape saat ditampilkan, misalnya dengan `htmlspecialchars()`.
10. Setiap anggota wajib menguji file yang dibuat dan memberi tahu anggota integrasi tentang file, class, method, dan dependensi yang ditambahkan.

## 12. Validasi dan keamanan dasar

### Front-end
- Field wajib tidak boleh kosong.
- Email menggunakan format email.
- Password dan konfirmasi password harus cocok saat registrasi.
- Input angka/tahun harus sesuai rentang yang masuk akal.
- Tampilkan pesan kesalahan yang jelas dekat field.
- Gunakan atribut HTML seperti `required`, `type="email"`, `minlength`, dan `maxlength` bila relevan.
- JavaScript meningkatkan pengalaman pengguna, tetapi bukan pengganti validasi backend.

### Backend
- Validasi ulang seluruh data yang diterima.
- Gunakan `password_hash()` dan `password_verify()`.
- Gunakan session untuk autentikasi dan pemeriksaan role.
- Regenerasi session ID setelah login.
- Gunakan `htmlspecialchars()` saat output.
- Tolak akses tanpa hak pada setiap endpoint/halaman privat.
- Periksa kepemilikan transaksi sebelum pengembalian.
- Tangani file JSON yang tidak ditemukan atau tidak valid dengan aman.
- Hindari menampilkan stack trace atau detail sensitif kepada pengguna.

## 13. Standar UI/UX

Arah visual yang disarankan:
- Tampilan modern, bersih, konsisten, dan berorientasi pada katalog buku.
- Navbar dengan identitas E-Library dan menu sesuai role.
- Layout responsif untuk desktop, tablet, dan ponsel.
- Kartu e-book memuat cover, judul, penulis, kategori, dan status.
- Gunakan badge berbeda untuk status tersedia, dipinjam, dan dikembalikan.
- Form memiliki label yang jelas, pesan validasi, dan tombol dengan state yang mudah dipahami.
- Tabel admin memiliki empty state jika data kosong.
- Gunakan elemen semantik: `header`, `nav`, `main`, `section`, `article`, `form`, `label`, `button`, `footer`.
- Jaga kontras warna dan fokus keyboard.

## 14. Kriteria penerimaan / checklist selesai

### Fungsional
- [ ] Beranda dan katalog dapat dibuka.
- [ ] Pencarian dan filter katalog bekerja.
- [ ] Detail e-book menampilkan data yang benar.
- [ ] Registrasi menyimpan user dengan password hash.
- [ ] Login dan logout bekerja.
- [ ] Role Admin dan Member dibedakan.
- [ ] Admin dapat menambah, melihat, mengubah, dan menghapus e-book.
- [ ] Member dapat meminjam e-book tersedia.
- [ ] Member tidak dapat meminjam e-book yang tidak tersedia.
- [ ] Member dapat melihat pinjaman dan riwayat.
- [ ] Member dapat mengembalikan pinjaman miliknya.
- [ ] E-book kembali tersedia setelah pengembalian.
- [ ] Admin dapat melihat daftar transaksi.

### Rubrik Front-End (30)
- [ ] Form memiliki validasi HTML dan JavaScript.
- [ ] Elemen semantik digunakan secara tepat.
- [ ] Tampilan responsif.
- [ ] Interaksi JavaScript berjalan dan memberi umpan balik.

### Rubrik PHP OOP (40)
- [ ] Ada class dan object yang benar-benar digunakan.
- [ ] Encapsulation terlihat dari atribut dan method.
- [ ] Inheritance terlihat dari parent dan child class.
- [ ] Polymorphism terlihat dari method override/interface dan pemanggilannya.
- [ ] Namespace digunakan konsisten.
- [ ] PHP 8.x tanpa framework.

### Rubrik struktur (30)
- [ ] Folder `src/` tertata dan modular.
- [ ] Pemisahan model, service, core, halaman, dan aset jelas.
- [ ] Tidak ada fatal error/warning yang mengganggu.
- [ ] README menjelaskan kebutuhan dan cara menjalankan aplikasi.
- [ ] Semua fitur dapat didemonstrasikan dari satu proyek yang terintegrasi.

## 15. Skenario pengujian manual

| No. | Skenario | Hasil yang diharapkan |
|---:|---|---|
| 1 | Buka katalog sebagai Guest | Katalog tampil |
| 2 | Cari judul e-book | Hasil sesuai kata kunci |
| 3 | Registrasi dengan email baru | Akun berhasil dibuat |
| 4 | Registrasi dengan email terdaftar | Ditolak dengan pesan jelas |
| 5 | Login dengan password salah | Login ditolak |
| 6 | Login sebagai Member | Masuk ke dashboard Member |
| 7 | Member meminjam e-book tersedia | Transaksi dibuat dan e-book menjadi tidak tersedia |
| 8 | Member mencoba meminjam e-book yang sama | Ditolak |
| 9 | Member mengembalikan pinjaman sendiri | Status menjadi returned dan e-book tersedia |
| 10 | Member mencoba mengakses halaman Admin | Akses ditolak/dialihkan |
| 11 | Admin menambah e-book | E-book muncul di katalog |
| 12 | Admin mengubah e-book | Perubahan tampil |
| 13 | Admin menghapus e-book yang aman dihapus | Data terhapus |
| 14 | Admin melihat transaksi | Daftar transaksi tampil |
| 15 | Refresh halaman setelah perubahan | Data tetap tersimpan di JSON |

## 16. Cara menjalankan (rancangan)

1. Pasang PHP 8.x dan web server lokal (misalnya XAMPP atau Laragon).
2. Letakkan folder proyek di direktori web server.
3. Pastikan folder `storage/` dan file JSON dapat dibaca/ditulis oleh PHP.
4. Periksa konfigurasi dasar di `config/app.php`.
5. Jalankan melalui web server lokal, bukan membuka file PHP langsung dari file explorer.
6. Buka `http://localhost/e-library/` (sesuaikan nama folder).
7. Buat akun Admin awal melalui seed yang disediakan atau prosedur setup yang dijelaskan di README. Jangan menyimpan password default dalam bentuk teks biasa.

---

# BAGIAN II — PEMBAGIAN TUGAS TIM

Pembagian ini adalah usulan agar lima anggota dapat bekerja paralel. Satu orang bertanggung jawab atas satu modul utama, tetapi semua anggota tetap harus mengikuti kontrak integrasi di atas.

| Anggota | Tanggung jawab utama | Hasil kerja |
|---|---|---|
| Syahrial | Fondasi PHP OOP, autentikasi, user dan role | `User`, `Member`, `Admin`, `AuthService`, login/register/session |
| Mail | Katalog publik dan manajemen e-book | Model/service e-book, halaman katalog/detail, CRUD Admin |
| Farid | Transaksi peminjaman dan pengembalian | `Transaction`, `LoanService`, halaman pinjaman dan riwayat |
| Robi | Front-end, desain sistem, komponen UI dan JavaScript | CSS responsif, komponen layout, validasi/interaksi JS |
| Arkaan | Arsitektur, integrasi, kategori/dashboard, quality assurance dan dokumentasi | `bootstrap.php`, autoload, integrasi modul, pengujian, README dan final review |

**Catatan pembagian:** Arkaan berperan sebagai integrator/koordinator teknis, bukan berarti harus menulis ulang semua modul. Setiap anggota wajib menghasilkan kode yang dapat digabung dan diuji. Bila anggota lain mengambil alih sebagian halaman, pembagian dapat disesuaikan tanpa mengubah kontrak teknis.

---

# BAGIAN III — PROMPT UNTUK ANTIGRAVITY

> Cara penggunaan: berikan **Prompt Spesifikasi** terlebih dahulu sebagai konteks proyek. Setelah itu, setiap anggota menjalankan prompt individunya pada workspace/repository proyek yang sama. Jika bekerja di workspace terpisah, minta anggota mengirim file dan daftar perubahan, lalu integrasikan mengikuti kontrak.

## PROMPT 1 — PROMPT SPESIFIKASI PROYEK

```text
Bertindaklah sebagai Senior Web Developer, Software Architect, dan PHP OOP Engineer.

Saya sedang mengerjakan UTS Pemrograman Web Semester Ganjil 2026/2027. Tugas mewajibkan prototipe aplikasi web interaktif yang mengintegrasikan:
- Front-end HTML, CSS, dan JavaScript.
- Backend PHP 8.x dengan OOP modern.
- PHP tanpa framework.
- Pengelolaan katalog media/layanan.
- Pengelolaan User/Member.
- Simulasi transaksi atau transaksi penyewaan/peminjaman e-book.
- Penyimpanan data e-book boleh menggunakan XML, JSON, atau Object Class PHP.

Rubrik penilaian:
1. Front-End 30 poin: validasi form, elemen semantik, responsivitas, interaktivitas JavaScript.
2. PHP OOP 40 poin: Class, Object, Encapsulation, Inheritance, Polymorphism, Namespace.
3. Struktur berkas 30 poin: folder src/, modularitas, dan aplikasi berjalan tanpa error.

Kita akan membuat aplikasi bernama E-Library, yaitu prototipe perpustakaan digital dengan katalog e-book, autentikasi Admin/Member, serta peminjaman dan pengembalian e-book. Gunakan PHP 8.x tanpa Laravel/CodeIgniter/framework PHP lain, HTML, CSS, JavaScript vanilla, dan penyimpanan JSON lokal.

TUGAS ANDA:
1. Analisis kebutuhan berdasarkan instruksi di atas.
2. Buat arsitektur aplikasi dan struktur folder modular.
3. Rancang class diagram dalam bentuk tabel atau Mermaid.
4. Tentukan atribut dan method setiap class.
5. Pastikan konsep Class/Object, Encapsulation, Inheritance, Polymorphism, dan Namespace benar-benar diimplementasikan dan dapat didemonstrasikan.
6. Tentukan format JSON untuk users, books, categories, dan transactions.
7. Tetapkan alur registrasi, login, katalog, peminjaman, pengembalian, dan manajemen Admin.
8. Tetapkan kontrak integrasi agar lima developer dapat bekerja paralel.
9. Buat checklist pengujian dan kriteria penerimaan berdasarkan rubrik.
10. Buat README yang menjelaskan cara instalasi dan menjalankan proyek.

ATURAN TEKNIS:
- Jangan gunakan framework PHP atau framework JavaScript.
- Gunakan PHP 8.x, typed properties, type declaration, return type, constructor, visibility, namespace, dan autoload sederhana dengan spl_autoload_register().
- Gunakan session untuk login dan role.
- Password wajib menggunakan password_hash() dan password_verify().
- Validasi harus dilakukan di frontend dan backend.
- Semua perubahan data dilakukan dengan POST.
- Escape output menggunakan htmlspecialchars().
- Setiap halaman privat harus memeriksa session dan role di backend.
- Jangan menaruh semua kode dalam satu file.
- Gunakan service untuk business logic dan class storage/repository untuk pengelolaan JSON.
- Jangan membuat fitur yang tidak relevan atau terlalu kompleks.
- Jangan mengarang bahwa aplikasi sudah berjalan atau diuji jika belum benar-benar dijalankan.

FITUR:
Guest: beranda, katalog, pencarian/filter, detail e-book, registrasi, login.
Member: dashboard, pinjam e-book tersedia, lihat pinjaman aktif, kembalikan pinjaman sendiri, lihat riwayat, profil.
Admin: dashboard, CRUD e-book, kategori, daftar member, seluruh transaksi.

ATURAN TRANSAKSI:
- Hanya Member login yang dapat meminjam.
- E-book harus tersedia.
- Saat pinjam, buat ID transaksi, tanggal pinjam, jatuh tempo 7 hari, dan status borrowed.
- Saat dikembalikan, status menjadi returned dan e-book tersedia kembali.
- Member tidak boleh mengembalikan transaksi orang lain.
- Validasi transaksi harus dilakukan di backend.

STRUKTUR AWAL YANG DIHARAPKAN:
- src/Core
- src/Models
- src/Services
- src/Interfaces
- config
- storage
- assets/css
- assets/js
- member
- admin
- actions

HASIL YANG SAYA INGINKAN:
A. Dokumen spesifikasi ringkas tetapi lengkap.
B. Struktur folder final.
C. Tabel class, atribut, method, dan konsep OOP.
D. Diagram alur utama.
E. Format JSON beserta seed data.
F. Kontrak integrasi antaranggota.
G. Checklist pengujian.
H. Implementasi awal fondasi proyek jika workspace memang sudah tersedia.

PENTING:
Sebelum mengubah atau membuat file, periksa dahulu isi workspace. Jangan menimpa kode yang sudah ada tanpa memeriksanya. Jika ada keputusan yang belum ditentukan, gunakan rancangan paling sederhana yang memenuhi soal dan dokumentasikan asumsi tersebut. Jangan membuat duplikasi class atau file. Setelah selesai, jelaskan file yang dibuat, cara menjalankan, dan hal yang belum selesai.
```

## PROMPT 2 — ANGGOTA SYAHRIAL
### Fokus: PHP OOP, User, Member, Admin, autentikasi dan session

```text
Bertindaklah sebagai Senior PHP OOP Developer. Anda bertanggung jawab mengembangkan modul autentikasi dan manajemen pengguna untuk proyek UTS Pemrograman Web bernama E-Library.

KONTEKS TUGAS:
Aplikasi wajib memakai PHP 8.x OOP tanpa framework, HTML/CSS/JS vanilla, dan penyimpanan JSON. Rubrik OOP menilai Class, Object, Encapsulation, Inheritance, Polymorphism, dan Namespace.

TANGGUNG JAWAB ANDA:
Mengembangkan fondasi model User, Member, Admin, autentikasi, registrasi, login, logout, dan pemeriksaan hak akses.

CLASS YANG MENJADI TANGGUNG JAWAB:
1. App\Models\User
   - Menjadi parent class atau abstract class.
   - Atribut private/protected: id, name, email, passwordHash, role.
   - Method: constructor, getter, verifyPassword(), getRoleDescription() atau method sejenis.
2. App\Models\Member extends User
   - Atribut tambahan: loanLimit.
   - Method: canBorrow(), getRoleDescription() dengan override jika menggunakan polymorphism.
3. App\Models\Admin extends User
   - Method: canManageBooks(), canManageMembers(), getRoleDescription() dengan override.
4. App\Services\AuthService
   - register(), login(), logout(), findUserByEmail().
5. App\Core\SessionManager
   - start(), login(), logout(), user(), requireLogin(), requireRole().

KEWAJIBAN IMPLEMENTASI:
- Gunakan namespace sesuai struktur proyek.
- Gunakan typed properties dan type declaration PHP 8.x.
- Terapkan encapsulation: atribut tidak boleh diakses langsung dari luar class.
- Terapkan inheritance melalui Member dan Admin yang mewarisi User.
- Terapkan polymorphism secara nyata, misalnya method getRoleDescription() yang hasilnya berbeda pada Member dan Admin dan dipanggil melalui referensi bertipe parent User.
- Password disimpan menggunakan password_hash() dan diverifikasi dengan password_verify().
- Email harus unik.
- Validasi data dilakukan kembali di backend.
- Setelah login, regenerasi session ID.
- Session menyimpan informasi minimum yang diperlukan, seperti id, name, email, dan role; jangan simpan password.
- Buat proteksi halaman berdasarkan role.
- Escape output di halaman.
- Gunakan JSON storage/service yang telah disepakati. Jangan membuat format users.json sendiri tanpa menyelaraskannya dengan kontrak proyek.

HALAMAN/FILE:
- register.php atau halaman registrasi yang telah disepakati.
- login.php.
- logout.php.
- actions/register-process.php.
- actions/login-process.php.
- File class hanya ditempatkan pada folder src yang sesuai.

ATURAN INTEGRASI:
- Periksa workspace sebelum membuat file.
- Gunakan bootstrap.php dan autoload proyek yang sudah tersedia.
- Jangan mengubah file milik anggota lain tanpa koordinasi.
- Jangan membuat class JsonStorage kedua jika class tersebut sudah tersedia.
- Ikuti struktur data users.json:
  id, name, email, password, role, created_at.
- Role hanya admin dan member.
- Berikan pesan error/sukses yang dapat ditampilkan halaman.

PENGUJIAN MINIMAL:
1. Registrasi berhasil.
2. Registrasi dengan email duplikat ditolak.
3. Password tersimpan sebagai hash.
4. Login benar berhasil.
5. Login salah ditolak.
6. Logout mengakhiri session.
7. Member tidak dapat membuka halaman Admin.
8. Guest tidak dapat membuka halaman Member.
9. Method polymorphism menunjukkan hasil berbeda untuk Member dan Admin.

OUTPUT:
- Implementasi kode lengkap untuk modul Anda.
- Daftar file yang dibuat/diubah.
- Penjelasan class, atribut, method, dan konsep OOP yang diterapkan.
- Instruksi pengujian.
- Daftar dependensi dari modul lain.
Jangan mengklaim pengujian berhasil sebelum menjalankannya.
```

## PROMPT 3 — ANGGOTA MAIL
### Fokus: katalog e-book, model Book, Category, CRUD Admin

```text
Bertindaklah sebagai Senior PHP OOP Web Developer. Anda bertanggung jawab atas modul katalog e-book dan manajemen data buku pada proyek E-Library untuk UTS Pemrograman Web.

KONTEKS:
- PHP 8.x OOP tanpa framework.
- Front-end HTML, CSS, dan JavaScript vanilla.
- Penyimpanan JSON lokal.
- Aplikasi mengelola katalog e-book, User/Member, dan transaksi peminjaman.
- Rubrik menilai Front-End 30, PHP OOP 40, dan struktur berkas 30.

TANGGUNG JAWAB:
Membuat modul katalog publik dan manajemen e-book oleh Admin.

CLASS:
1. App\Models\Book
   Atribut private: id, title, author, categoryId, year, description, cover, available.
   Method: constructor, getter, isAvailable(), markBorrowed(), markReturned(), updateDetails().
2. App\Models\Category
   Atribut private: id, name.
   Method: constructor, getter, rename().
3. App\Services\BookService
   Method: getAll(), getById(), search(), filterByCategory(), create(), update(), delete().
   Gunakan class penyimpanan JSON yang sudah tersedia dalam proyek.

FITUR KATALOG PUBLIK:
- Menampilkan daftar e-book.
- Pencarian berdasarkan judul atau penulis.
- Filter berdasarkan kategori.
- Menampilkan cover, judul, penulis, kategori, tahun, deskripsi singkat, dan status ketersediaan.
- Detail e-book.
- Empty state jika tidak ada hasil.
- Tombol Pinjam hanya ditampilkan sesuai status login dan ketersediaan, tetapi backend tetap wajib memvalidasi hak akses.

FITUR ADMIN:
- Menampilkan daftar e-book.
- Tambah e-book.
- Edit metadata e-book.
- Hapus e-book dengan pemeriksaan yang sesuai.
- Mengelola kategori jika disepakati dalam integrasi.
- Semua halaman dan aksi Admin wajib dilindungi pemeriksaan role di PHP.

VALIDASI:
- Judul, penulis, kategori, tahun, dan deskripsi divalidasi.
- Tahun harus angka dan masuk akal.
- Kategori harus mengacu pada kategori yang tersedia.
- ID e-book harus unik.
- Jangan menghapus e-book yang masih memiliki transaksi aktif, kecuali sistem memiliki aturan penanganan yang aman.
- Semua perubahan data menggunakan POST.
- Escape seluruh output dengan htmlspecialchars().
- Validasi backend wajib ada, JavaScript hanya membantu UX.

FORMAT DATA books.json:
id, title, author, category_id, year, description, cover, available.

FORMAT categories.json:
id, name.

DESAIN:
- Gunakan elemen semantik HTML.
- Kartu e-book konsisten dan responsif.
- Status tersedia/tidak tersedia ditampilkan dengan jelas.
- Form tambah/edit memiliki label dan pesan validasi.
- Jangan memakai framework front-end.

ATURAN INTEGRASI:
- Periksa workspace sebelum membuat file.
- Gunakan bootstrap.php, namespace, autoload, dan JsonStorage yang sudah disediakan.
- Jangan menduplikasi class storage.
- Jangan mengubah format JSON tanpa persetujuan tim.
- Gunakan struktur folder dan nama file yang sudah disepakati.
- Jangan menulis business logic besar langsung di halaman PHP.

PENGUJIAN:
1. Katalog tampil dari JSON.
2. Pencarian judul/penulis bekerja.
3. Filter kategori bekerja.
4. Detail e-book benar.
5. Admin dapat menambah, mengedit, dan menghapus data sesuai aturan.
6. Member/Guest tidak dapat menjalankan aksi Admin.
7. E-book yang dipinjam tidak dapat dipinjam ulang.
8. Data tetap tersimpan setelah halaman di-refresh.

OUTPUT:
- Kode lengkap modul katalog.
- Daftar file yang dibuat/diubah.
- Tabel class, atribut, method.
- Cara menguji fitur.
- Dependensi yang diperlukan dari modul autentikasi dan transaksi.
Jangan mengklaim fitur selesai jika belum diuji.
```

## PROMPT 4 — ANGGOTA FARID
### Fokus: transaksi peminjaman dan pengembalian

```text
Bertindaklah sebagai Senior PHP OOP Developer yang berpengalaman membangun sistem transaksi. Anda bertanggung jawab atas modul peminjaman dan pengembalian e-book pada proyek E-Library untuk UTS Pemrograman Web.

KONTEKS:
Aplikasi menggunakan PHP 8.x OOP tanpa framework, HTML/CSS/JavaScript vanilla, dan penyimpanan JSON. Sistem mengelola katalog e-book, User/Member, serta simulasi transaksi peminjaman.

TANGGUNG JAWAB:
Mengembangkan proses peminjaman, pengembalian, daftar pinjaman aktif, riwayat transaksi, dan tampilan transaksi Admin.

CLASS:
1. App\Models\Transaction
   Atribut private: id, userId, bookId, borrowedAt, dueAt, returnedAt, status.
   Method: constructor, getter, isActive(), markReturned().
2. App\Services\LoanService
   Method: borrow(), returnBook(), getMemberLoans(), getMemberHistory(), getAllTransactions().
3. Gunakan Book/BookService dan SessionManager/AuthService yang telah dibuat anggota lain. Jangan menduplikasi class tersebut.

ATURAN BISNIS:
- Hanya Member yang sudah login dapat meminjam.
- Buku harus tersedia.
- Saat peminjaman, buat ID transaksi unik.
- Catat tanggal pinjam.
- Tenggat peminjaman adalah 7 hari setelah tanggal pinjam.
- Status awal transaksi adalah borrowed.
- Setelah peminjaman berhasil, status ketersediaan buku berubah menjadi tidak tersedia.
- Member hanya boleh mengembalikan transaksi miliknya.
- Hanya transaksi borrowed yang dapat dikembalikan.
- Saat pengembalian, isi returned_at dan ubah status menjadi returned.
- Setelah pengembalian berhasil, buku tersedia kembali.
- Admin dapat melihat seluruh transaksi.
- Validasi dilakukan di backend; jangan percaya book_id atau user_id dari form begitu saja.

FORMAT transactions.json:
id, user_id, book_id, borrowed_at, due_at, returned_at, status.

HALAMAN:
- member/loans.php: pinjaman aktif dan tombol kembalikan.
- member/history.php: riwayat transaksi.
- admin/transactions.php: seluruh transaksi.
- actions/borrow.php: proses peminjaman.
- actions/return.php: proses pengembalian.
Sesuaikan nama file dengan struktur proyek jika sudah ditetapkan.

PENANGANAN ERROR:
- Buku tidak ditemukan.
- Buku sedang tidak tersedia.
- Pengguna tidak login.
- Role bukan Member.
- Transaksi tidak ditemukan.
- Transaksi bukan milik pengguna.
- Transaksi sudah dikembalikan.
- Gagal membaca/menulis JSON.

ATURAN TEKNIS:
- Gunakan namespace App\Models dan App\Services.
- Gunakan PHP 8.x typed properties dan return type.
- Gunakan service untuk aturan bisnis.
- Gunakan penyimpanan JSON bersama yang sudah tersedia.
- Semua aksi mutasi menggunakan POST.
- Gunakan session dan role guard.
- Escape output.
- Hindari duplikasi proses update status buku.
- Pastikan update transaksi dan status buku konsisten.
- Jangan menggunakan framework.

PENGUJIAN:
1. Member dapat meminjam buku tersedia.
2. Buku berubah menjadi tidak tersedia.
3. Member lain tidak dapat meminjam buku yang sama.
4. Guest tidak dapat meminjam.
5. Admin tidak diperlakukan sebagai Member jika kebijakan aplikasi membatasi peminjaman hanya untuk Member.
6. Member dapat melihat pinjaman miliknya.
7. Member dapat mengembalikan pinjaman miliknya.
8. Member tidak dapat mengembalikan transaksi orang lain.
9. Transaksi returned tidak dapat dikembalikan dua kali.
10. Buku tersedia kembali setelah pengembalian.
11. Admin dapat melihat semua transaksi.

OUTPUT:
- Kode lengkap modul transaksi.
- Tabel atribut dan method class.
- Daftar file yang dibuat/diubah.
- Dependensi dan kontrak integrasi.
- Bukti/skenario pengujian yang benar-benar dijalankan.
```

## PROMPT 5 — ANGGOTA ROBI
### Fokus: UI/UX, HTML, CSS responsif, JavaScript dan validasi front-end

```text
Bertindaklah sebagai Senior Front-End Developer dan UI/UX Engineer. Anda bertanggung jawab atas tampilan dan interaktivitas front-end proyek E-Library untuk UTS Pemrograman Web.

KONTEKS TUGAS:
Front-end wajib menggunakan HTML, CSS, dan JavaScript vanilla. Jangan menggunakan React, Vue, Angular, Bootstrap JS, Tailwind build system, atau framework JavaScript lainnya. CSS boleh ditulis manual. Jika tim telah menyepakati library CSS tertentu, konfirmasi terlebih dahulu sebelum menggunakannya.

RUBRIK FRONT-END:
- Validasi form.
- Kelengkapan elemen semantik.
- Estetika layout responsif.
- Interaktivitas JavaScript.
Bobot maksimal 30 poin.

TANGGUNG JAWAB:
1. Menyusun design system sederhana untuk E-Library.
2. Membuat layout global: navbar, sidebar bila diperlukan, footer, container, card, badge, form, tabel, alert, dan modal konfirmasi.
3. Membuat stylesheet responsif.
4. Membuat interaksi JavaScript.
5. Membuat validasi front-end untuk form login, registrasi, dan form e-book.
6. Menguji tampilan pada desktop, tablet, dan mobile.

ARAH DESAIN:
- Modern, bersih, profesional, dan bertema perpustakaan digital.
- Navigasi mudah dipahami.
- Kartu e-book menampilkan cover, judul, penulis, kategori, dan status.
- Dashboard Admin/Member konsisten.
- Gunakan spacing, typography, warna, border, shadow, dan radius secara konsisten.
- Sediakan hover, focus, active, disabled, loading/feedback state jika relevan.
- Jangan membuat desain terlalu ramai.

KOMPONEN:
- Navbar publik dan navbar setelah login.
- Hero section beranda.
- Search bar dan filter kategori.
- Book card dan book detail.
- Form login dan registrasi.
- Dashboard cards.
- Tabel transaksi.
- Badge status available, borrowed, returned.
- Alert sukses/gagal.
- Modal konfirmasi pinjam/kembali/hapus.
- Empty state dan error state.

JAVASCRIPT:
- Validasi field wajib.
- Validasi format email.
- Validasi panjang password.
- Konfirmasi password saat registrasi.
- Konfirmasi aksi pinjam/kembali/hapus.
- Toggle password visibility bila sesuai.
- Pencarian/filter sisi client hanya jika sesuai dengan data yang tersedia; jangan membuat konflik dengan pencarian backend.
- Tampilkan pesan validasi yang jelas.
- Jangan mengandalkan JavaScript sebagai satu-satunya validasi keamanan.

AKSESIBILITAS DAN SEMANTIK:
- Gunakan header, nav, main, section, article, form, label, button, footer secara benar.
- Setiap input memiliki label.
- Tombol memiliki teks/aria-label yang jelas.
- Fokus keyboard terlihat.
- Kontras teks memadai.
- Gambar memiliki alt yang sesuai.
- Jangan menggunakan div untuk semua elemen jika elemen semantik tersedia.

STRUKTUR FILE:
- assets/css/style.css
- assets/js/app.js
- assets/images/
Jika file global sudah ada, periksa dan lanjutkan tanpa membuat duplikasi yang tidak perlu.

ATURAN INTEGRASI:
- Periksa workspace sebelum mengubah file.
- Jangan mengubah business logic PHP.
- Jangan mengganti nama field form tanpa koordinasi dengan anggota backend.
- Selaraskan nama input dengan kontrak backend, misalnya name, email, password, password_confirmation, book_id.
- Jangan menambahkan framework.
- Pastikan komponen dapat digunakan pada halaman PHP yang dirender server.

PENGUJIAN:
1. Layout berfungsi di desktop dan mobile.
2. Form memiliki label dan validasi.
3. Pesan error mudah terlihat.
4. Tombol/modal bekerja.
5. Navigasi dan menu mobile bekerja.
6. Tidak ada elemen yang overflow horizontal pada layar kecil.
7. Semua halaman memiliki gaya visual konsisten.
8. Interaksi keyboard dasar dapat digunakan.

OUTPUT:
- CSS dan JavaScript lengkap.
- Daftar komponen dan halaman yang sudah didukung.
- Panduan penggunaan class CSS di halaman PHP.
- Daftar file yang dibuat/diubah.
- Hasil pengujian tampilan dan interaksi.
```

## PROMPT 6 — ANGGOTA ARKAAN
### Fokus: arsitektur, integrasi, kategori, dashboard, QA dan dokumentasi

```text
Bertindaklah sebagai Lead Web Developer, Software Integrator, dan PHP OOP Architect. Anda bertanggung jawab mengintegrasikan seluruh modul dari Syahrial, Mail, Farid, dan Robi ke dalam satu aplikasi E-Library yang stabil untuk UTS Pemrograman Web.

KONTEKS TUGAS:
Aplikasi harus memenuhi instruksi UTS:
- Front-end HTML, CSS, JavaScript.
- Backend PHP 8.x OOP tanpa framework.
- Katalog media/layanan.
- Pengelolaan User/Member.
- Simulasi transaksi/peminjaman e-book.
- Data e-book boleh disimpan dengan JSON/XML/Object Class PHP.

Rubrik:
1. Front-End: 30 poin.
2. PHP OOP: 40 poin.
3. Struktur folder dan keberhasilan eksekusi: 30 poin.

TANGGUNG JAWAB UTAMA:
1. Audit struktur workspace dan seluruh file yang sudah dibuat anggota.
2. Menetapkan/menjaga bootstrap.php, konfigurasi, autoload, session startup, dan namespace.
3. Memastikan class dari semua anggota dapat ditemukan dan digunakan.
4. Memastikan format JSON sama di seluruh modul.
5. Mengintegrasikan halaman publik, Member, Admin, dan actions.
6. Membuat atau merapikan dashboard Admin dan Member jika belum ditangani anggota lain.
7. Mengintegrasikan kategori bila diperlukan.
8. Menjalankan pengujian end-to-end.
9. Memperbaiki bug integrasi tanpa merusak modul anggota lain.
10. Menyusun README dan checklist demonstrasi UTS.

ARSITEKTUR:
- src/Core
- src/Models
- src/Services
- src/Interfaces
- config
- storage
- assets/css
- assets/js
- member
- admin
- actions

NAMESPACE:
- App\Core
- App\Models
- App\Services
- App\Interfaces

AUTOLOAD:
Gunakan spl_autoload_register() sederhana di bootstrap.php. Pastikan mapping namespace ke direktori sesuai struktur folder. Jangan membuat autoloader kedua.

KONTRAK DATA:
users.json:
id, name, email, password, role, created_at.

books.json:
id, title, author, category_id, year, description, cover, available.

categories.json:
id, name.

transactions.json:
id, user_id, book_id, borrowed_at, due_at, returned_at, status.

Role: admin atau member.
Status transaksi: borrowed atau returned.

ATURAN INTEGRASI:
- Jangan mengubah kontrak data tanpa memeriksa seluruh modul.
- Jangan menduplikasi User, Book, Transaction, JsonStorage, SessionManager, atau service yang sudah tersedia.
- Pastikan semua halaman menggunakan bootstrap yang sama.
- Pastikan akses Admin/Member dilindungi di backend.
- Pastikan semua mutasi data menggunakan POST.
- Pastikan output di-escape.
- Pastikan proses peminjaman/pengembalian mengubah transaksi dan ketersediaan buku secara konsisten.
- Pastikan path file bekerja di semua halaman, termasuk subfolder member dan admin.
- Pastikan tidak ada fatal error, undefined class, undefined method, atau warning karena indeks JSON yang tidak tersedia.
- Jika ada konflik antarimplementasi, pilih satu implementasi yang paling konsisten dan dokumentasikan perubahan kepada anggota terkait.

DASHBOARD:
Member:
- Jumlah pinjaman aktif.
- Jumlah riwayat/pengembalian.
- Daftar pinjaman terbaru.
- Link ke katalog dan pinjaman saya.

Admin:
- Jumlah e-book.
- Jumlah member.
- Jumlah transaksi.
- Jumlah pinjaman aktif.
- Transaksi terbaru.

KATEGORI:
Jika belum ada modul kategori, buat pengelolaan sederhana:
- Daftar kategori.
- Tambah kategori.
- Ubah nama kategori.
- Jangan hapus kategori yang masih digunakan e-book tanpa aturan yang aman.

QUALITY ASSURANCE:
Jalankan pemeriksaan:
1. PHP version sesuai 8.x.
2. Semua halaman dapat dibuka.
3. Registrasi dan login berjalan.
4. Role Admin/Member benar.
5. CRUD e-book berjalan.
6. Katalog, pencarian, dan filter berjalan.
7. Peminjaman dan pengembalian berjalan.
8. Riwayat dan dashboard menampilkan data yang sesuai.
9. Akses ilegal ditolak.
10. Data JSON tetap valid setelah operasi.
11. Tidak ada error PHP/JavaScript yang mengganggu.
12. Tampilan responsif.
13. README sesuai kondisi aplikasi yang benar-benar dibuat.

DOKUMENTASI:
README minimal berisi:
- Deskripsi proyek.
- Fitur.
- Teknologi.
- Persyaratan PHP.
- Struktur folder.
- Cara menjalankan.
- Cara membuat akun Admin awal.
- Format/seed JSON.
- Pembagian tanggung jawab anggota.
- Catatan keterbatasan prototipe.

OUTPUT:
A. Aplikasi terintegrasi.
B. Daftar file yang dibuat/diubah.
C. Ringkasan hasil pengujian per skenario.
D. Daftar bug yang ditemukan dan status perbaikannya.
E. Dokumentasi README.
F. Panduan demo yang menunjukkan seluruh kriteria rubrik, terutama lima konsep PHP OOP.

Jangan mengklaim pengujian berhasil jika belum dijalankan. Jangan menghapus atau menimpa kode anggota tanpa memeriksa dampaknya. Prioritaskan aplikasi yang sederhana, konsisten, berjalan, dan memenuhi instruksi UTS daripada menambah fitur yang tidak diperlukan.
```

---

# BAGIAN IV — URUTAN KERJA YANG DIREKOMENDASIKAN

## 1. Sebelum mulai coding
1. Buat satu repository Git bersama.
2. Masukkan dokumen spesifikasi ini ke repository.
3. Tetapkan struktur folder dan kontrak JSON.
4. Tentukan siapa yang membuat file fondasi seperti `bootstrap.php` dan `JsonStorage.php`.
5. Setiap anggota membuat branch masing-masing.
6. Sepakati cara merge dan review sebelum menggabungkan perubahan.

## 2. Urutan implementasi
| Tahap | Aktivitas | Penanggung jawab utama |
|---|---|---|
| 1 | Setup folder, bootstrap, namespace, JSON storage | Arkaan bersama Syahrial |
| 2 | Model User, Member, Admin, autentikasi | Syahrial |
| 3 | Model Book, Category, katalog dan CRUD | Mail |
| 4 | Model Transaction dan proses pinjam/kembali | Farid |
| 5 | Design system, CSS, JavaScript, validasi UI | Robi |
| 6 | Integrasi semua modul dan dashboard | Arkaan |
| 7 | Pengujian end-to-end dan perbaikan | Seluruh anggota |
| 8 | README, persiapan demo, pemeriksaan rubrik | Arkaan dan seluruh anggota |

## 3. Checklist sebelum dikumpulkan/didemokan
- [ ] Semua anggota menyerahkan file dan menjelaskan dependensinya.
- [ ] Aplikasi dapat dijalankan dari satu instruksi di README.
- [ ] Tidak ada framework PHP.
- [ ] PHP menggunakan OOP, bukan hanya kumpulan fungsi prosedural.
- [ ] Class, object, encapsulation, inheritance, polymorphism, dan namespace dapat ditunjukkan.
- [ ] Folder `src/` berisi class modular.
- [ ] Front-end responsif dan memiliki validasi/interaksi.
- [ ] Registrasi, login, role, katalog, peminjaman, dan pengembalian diuji.
- [ ] Data JSON valid dan bertahan setelah refresh.
- [ ] Tidak ada error yang menghalangi eksekusi.
- [ ] Setiap anggota memahami bagian yang dikerjakan dan dapat menjelaskannya saat UTS.

---

# BAGIAN V — CATATAN PENTING UNTUK TIM

1. **Jangan langsung meminta AI membuat seluruh aplikasi dalam satu prompt untuk setiap anggota.** Itu berisiko menghasilkan file duplikat, nama class berbeda, dan format data yang tidak kompatibel.
2. Prompt spesifikasi digunakan sebagai konteks bersama. Prompt anggota digunakan untuk membatasi tanggung jawab masing-masing.
3. Kerjakan fondasi dan kontrak integrasi lebih dahulu. Anggota lain sebaiknya tidak membuat storage/autoload sendiri-sendiri.
4. Pastikan konsep OOP terlihat di kode, bukan hanya ditulis dalam laporan. Siapkan contoh pemanggilan method polymorphism untuk demonstrasi.
5. Prioritaskan seluruh fitur inti yang diminta soal dan kestabilan aplikasi. Fitur tambahan hanya dikerjakan setelah alur utama berjalan.
6. Isi PDF tidak menetapkan database, desain, nama aplikasi, lama peminjaman, atau jumlah fitur halaman. Rancangan E-Library, JSON, dan tenggat 7 hari di dokumen ini adalah keputusan desain yang diusulkan untuk membuat implementasi lebih terarah.
