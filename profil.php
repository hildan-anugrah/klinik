<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Pasien.php';

cekLogin();

$db        = (new Database())->connect();
$userModel  = new User($db);
$pasienModel = new Pasien($db);

$userId  = $_SESSION['user_id'];
$role    = $_SESSION['role'];
$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_akun') {
        $nama     = trim($_POST['nama']  ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password']   ?? '';

        if (empty($nama) || empty($email)) {
            $error = 'Nama dan email wajib diisi.';
        } else {
            $updated = $userModel->update(
                $userId,
                $nama,
                $email,
                !empty($password) ? $password : null
            );
            if ($updated) {
                $_SESSION['nama'] = $nama;
                $success = 'Data akun berhasil diperbarui.';
            } else {
                $error = 'Gagal memperbarui data. Email mungkin sudah digunakan.';
            }
        }

    } elseif ($action === 'update_profil_pasien' && $role === 'pasien') {
        $namaPasien   = trim($_POST['nama']          ?? '');
        $tanggalLahir = trim($_POST['tanggal_lahir'] ?? '');
        $jenisKelamin = trim($_POST['jenis_kelamin'] ?? '');
        $alamat       = trim($_POST['alamat']        ?? '');
        $noTelp       = trim($_POST['no_telp']       ?? '');

        if (empty($namaPasien) || empty($tanggalLahir) || empty($jenisKelamin)) {
            $error = 'Nama, tanggal lahir, dan jenis kelamin wajib diisi.';
        } else {
            $pasien = $pasienModel->getByUserId($userId);

            if ($pasien) {
                $pasienModel->update(
                    $pasien['id'],
                    $namaPasien,
                    $tanggalLahir,
                    $jenisKelamin,
                    $alamat,
                    $noTelp
                );
            } else {
                $pasienModel->create(
                    $userId,
                    $namaPasien,
                    $tanggalLahir,
                    $jenisKelamin,
                    $alamat,
                    $noTelp
                );
            }
            $success = 'Profil pasien berhasil diperbarui.';
        }
    }
}

$user   = $userModel->getById($userId);
$pasien = ($role === 'pasien') ? $pasienModel->getByUserId($userId) : null;

$pageTitle = 'Profil Saya';
?>
<?php include __DIR__ . '/components/head.php'; ?>
<div class="layout">
    <?php include __DIR__ . '/components/sidebar.php'; ?>
    <div class="main-content">
        <?php include __DIR__ . '/components/navbar.php'; ?>
        <main>

            <?php if ($success): ?>
                <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="page-header">
                <h2>Profil Saya</h2>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:24px;">

                <div class="card">
                    <div class="card-header">Informasi Akun</div>
                    <div class="card-body">
                        <div class="profile-header">
                            <div class="profile-avatar">
                                <?= mb_strtoupper(mb_substr($user['nama'] ?? 'U', 0, 1)) ?>
                            </div>
                            <div class="profile-info">
                                <h2><?= htmlspecialchars($user['nama']  ?? '') ?></h2>
                                <p><?= htmlspecialchars($user['email'] ?? '') ?></p>
                                <span class="badge badge-<?= $role ?>">
                                    <?= ucfirst($role) ?>
                                </span>
                            </div>
                        </div>

                        <form method="POST">
                            <input type="hidden" name="action" value="update_akun">
                            <div class="form-group">
                                <label>Nama Lengkap</label>
                                <input type="text" name="nama"
                                       value="<?= htmlspecialchars($user['nama'] ?? '') ?>"
                                       placeholder="Nama lengkap Anda" required>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email"
                                       value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                                       placeholder="contoh@email.com" required>
                            </div>
                            <div class="form-group">
                                <label>
                                    Kata Sandi Baru
                                    <span style="color:var(--text-muted);font-weight:400">
                                        (kosongkan jika tidak diganti)
                                    </span>
                                </label>
                                <input type="password" name="password"
                                       placeholder="Minimal 6 karakter" minlength="6">
                            </div>
                            <button type="submit" class="btn btn-primary">
                                💾 Simpan Perubahan
                            </button>
                        </form>
                    </div>
                </div>

                <?php if ($role === 'pasien'): ?>
                <div class="card">
                    <div class="card-header">Data Pasien</div>
                    <div class="card-body">

                        <?php if (!$pasien): ?>
                        <div class="alert alert-warning">
                            ⚠️ Data profil pasien Anda belum lengkap. Silakan isi form di bawah ini.
                        </div>
                        <?php endif; ?>

                        <form method="POST">
                            <input type="hidden" name="action" value="update_profil_pasien">

                            <div class="form-group">
                                <label>Nama Lengkap</label>
                                <input type="text" name="nama"
                                       value="<?= htmlspecialchars($pasien['nama'] ?? $user['nama'] ?? '') ?>"
                                       placeholder="Nama lengkap Anda" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Tanggal Lahir</label>
                                    <input type="date" name="tanggal_lahir"
                                           value="<?= htmlspecialchars($pasien['tanggal_lahir'] ?? '') ?>"
                                           required>
                                </div>
                                <div class="form-group">
                                    <label>Jenis Kelamin</label>
                                    <select name="jenis_kelamin" required>
                                        <option value="">-- Pilih --</option>
                                        <option value="L"
                                            <?= (($pasien['jenis_kelamin'] ?? '') === 'L') ? 'selected' : '' ?>>
                                            Laki-laki
                                        </option>
                                        <option value="P"
                                            <?= (($pasien['jenis_kelamin'] ?? '') === 'P') ? 'selected' : '' ?>>
                                            Perempuan
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Nomor Telepon</label>
                                <input type="tel" name="no_telp"
                                       value="<?= htmlspecialchars($pasien['no_telp'] ?? '') ?>"
                                       placeholder="08xxxxxxxxxx">
                            </div>

                            <div class="form-group">
                                <label>Alamat</label>
                                <textarea name="alamat"
                                          placeholder="Alamat lengkap Anda"><?= htmlspecialchars($pasien['alamat'] ?? '') ?></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                💾 <?= $pasien ? 'Simpan Data Pasien' : 'Lengkapi Profil' ?>
                            </button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (in_array($role, ['admin', 'dokter'])): ?>
                <div class="card">
                    <div class="card-header">Informasi Tambahan</div>
                    <div class="card-body">
                        <table class="detail-table">
                            <tr>
                                <td>Peran</td>
                                <td>
                                    <span class="badge badge-<?= $role ?>">
                                        <?= ucfirst($role) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>Bergabung Sejak</td>
                                <td><?= !empty($user['created_at']) ? date('d M Y', strtotime($user['created_at'])) : '-' ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </main>
    </div>
</div>
<?php include __DIR__ . '/components/footer.php'; ?>