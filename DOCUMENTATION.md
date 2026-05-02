# Dokumentasi Teknis Aplikasi Klinik

Dokumen ini berisi rincian teknis mengenai struktur program, kelas, variabel, dan metode yang digunakan dalam aplikasi Klinik secara menyeluruh.

---

## 1. Konfigurasi Database (`config/db.php`)

### Class: `Database`
Digunakan untuk mengelola koneksi ke database MySQL menggunakan PDO.

**Variabel Private:**
- `string $host`: Host database (default: `'localhost'`).
- `string $dbname`: Nama database (default: `'klinik_db'`).
- `string $username`: Username database (default: `'root'`).
- `string $password`: Password database (default: `''`).
- `?PDO $connection`: Menyimpan objek PDO (singleton pattern).

**Metode:**
- `public function connect(): PDO`: 
    - **Fungsi**: Menginisialisasi dan mengembalikan objek koneksi PDO.
    - **Return**: Instansi `PDO`.

---

## 2. Autentikasi dan Keamanan (`config/auth.php`)

Berisi fungsi-fungsi global untuk manajemen sesi, enkripsi, dan otorisasi.

**Metode (Fungsi Global):**
- `hashPassword(string $password): string`: Mengenkripsi password menggunakan algoritma `BCRYPT`.
- `verifyPassword(string $password, string $hash): bool`: Memvalidasi apakah password cocok dengan hash.
- `cekLogin(): void`: Memastikan pengguna telah memiliki sesi `user_id`. Jika tidak, dialihkan ke `login.php`.
- `cekRole(array $roles): void`: Memeriksa apakah `$_SESSION['role']` ada di dalam daftar `$roles`. Jika tidak, dialihkan ke dashboard.

---

## 3. Model Pengguna (`models/User.php`)

### Class: `User`
Berinteraksi dengan tabel `users` untuk manajemen akun.

**Variabel Private:**
- `PDO $db`: Instansi koneksi database.

**Metode:**
- `public function __construct(PDO $db)`: Inisialisasi model dengan koneksi database.
- `public function login(string $email, string $password): array|false`: Mencari user berdasarkan email dan memverifikasi password.
- `public function register(string $nama, string $email, string $password): int|false`: Menambahkan user baru dengan role `'pasien'`.
- `public function tambahDokter(string $nama, string $email, string $password): int|false`: Menambahkan user baru dengan role `'dokter'`.
- `public function getAll(): array`: Mengambil semua kolom user kecuali password, diurutkan berdasarkan `created_at`.
- `public function getById(int $id): array|false`: Mengambil data satu user berdasarkan ID.
- `public function update(int $id, string $nama, string $email, ?string $password = null): bool`: Memperbarui data user. Password hanya diupdate jika diisi.
- `public function delete(int $id): bool`: Menghapus baris user dari database.

---

## 4. Model Pasien (`models/Pasien.php`)

### Class: `Pasien`
Mengelola data profil pasien di tabel `pasien`.

**Variabel Private:**
- `PDO $db`: Instansi koneksi database.

**Metode:**
- `public function __construct(PDO $db)`: Inisialisasi model.
- `public function getAll(): array`: Mengambil semua data pasien beserta email akunnya via `LEFT JOIN users`.
- `public function getByDokter(int $dokterId): array`: Mengambil daftar pasien unik yang memiliki rekam medis dari dokter tertentu.
- `public function getByUserId(int $userId): array|false`: Mencari profil pasien berdasarkan ID akun user.
- `public function getById(int $id): array|false`: Mencari profil pasien berdasarkan ID pasien.
- `public function create(?int $userId, string $nama, string $tanggalLahir, string $jenisKelamin, string $alamat, string $noTelp): int|false`: Membuat record pasien baru.
- `public function update(int $id, string $nama, string $tanggalLahir, string $jenisKelamin, string $alamat, string $noTelp): bool`: Memperbarui data profil pasien.
- `public function delete(int $id): bool`: Menghapus record pasien.
- `public function countAll(): int`: Mengembalikan jumlah total record di tabel pasien.

---

## 5. Model Rekam Medis (`models/RekamMedis.php`)

### Class: `RekamMedis`
Mengelola catatan kesehatan di tabel `rekam_medis`.

**Variabel Private:**
- `PDO $db`: Instansi koneksi database.

**Metode:**
- `public function __construct(PDO $db)`: Inisialisasi model.
- `public function getAll(): array`: Mengambil semua rekam medis (yang `is_deleted = 0`) beserta nama pasien dan dokter.
- `public function getByDokter(int $dokterId): array`: Filter rekam medis berdasarkan dokter pembuat.
- `public function getByPasien(int $pasienId): array`: Filter rekam medis milik pasien tertentu.
- `public function getById(int $id): array|false`: Mengambil detail satu rekam medis.
- `public function create(int $pasienId, ?int $dokterId, string $keluhan, string $diagnosa, string $obat, string $catatan): int|false`: Menambah rekam medis baru.
- `public function update(int $id, string $keluhan, string $diagnosa, string $obat, string $catatan): bool`: Mengubah data rekam medis.
- `public function softDelete(int $id): bool`: Mengubah `is_deleted` menjadi 1 dan mengisi `deleted_at`.
- `public function countAll(): int`: Total rekam medis yang aktif.
- `public function countByDokter(int $dokterId): int`: Jumlah rekam medis yang dibuat dokter tertentu.
- `public function countByPasien(int $pasienId): int`: Jumlah rekam medis yang dimiliki pasien tertentu.

---

## 6. Ringkasan File Utama Lainnya

- `index.php`: Router sederhana untuk mengarahkan pengguna ke login atau dashboard.
- `dashboard.php`: Menampilkan ringkasan statistik (total pasien, rekam medis, dll) sesuai role.
- `login.php` & `register.php`: Form input untuk autentikasi pengguna.
- `profil.php`: Mengelola data diri pengguna dan perubahan password.
- `kelola_admin.php`: CRUD User untuk administrator.
- `kelola_dokter.php`: Antarmuka manajemen data dokter.
- `kelola_pasien.php`: Antarmuka manajemen data pasien.
- `rekam_medis.php`: Antarmuka manajemen catatan medis.
- `logout.php`: Menghancurkan sesi dan kembali ke halaman login.
- `auth/proses_login.php`: Logika pemrosesan form login.
- `auth/proses_register.php`: Logika pendaftaran akun pasien baru.


