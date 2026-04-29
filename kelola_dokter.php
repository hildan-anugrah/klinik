<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/User.php';

cekLogin();
cekRole(['admin']);

$db = (new Database())->connect();
$userModel = new User($db);

$action = $_POST['action'] ?? '';
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'tambah') {
        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if (empty($nama) || empty($email) || empty($password)) {
            $error = 'Semua field wajib diisi.';
        } elseif (strlen($password) < 6) {
            $error = 'Kata sandi minimal 6 karakter.';
        } else {
            $result = $userModel->tambahDokter($nama, $email, $password);
            if ($result) {
                $success = 'Akun dokter berhasil ditambahkan.';
            } else {
                $error = 'Email sudah terdaftar.';
            }
        }
    } elseif ($action === 'edit') {
        $id = (int) ($_POST['id'] ?? 0);
        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($id > 0 && !empty($nama) && !empty($email)) {
            $userModel->update($id, $nama, $email, !empty($password) ? $password : null);
            $success = 'Data dokter berhasil diperbarui.';
        } else {
            $error = 'Nama dan email wajib diisi.';
        }
    } elseif ($action === 'hapus') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $userModel->delete($id);
            $success = 'Akun dokter berhasil dihapus.';
        }
    }
}

$stmt = $db->query("SELECT id, nama, email, role, created_at FROM users WHERE role = 'dokter' ORDER BY created_at DESC");
$dokterList = $stmt->fetchAll();

$pageTitle = 'Kelola Dokter';
?>
<?php include __DIR__ . '/components/head.php'; ?>
<div class="layout">
    <?php include __DIR__ . '/components/sidebar.php'; ?>
    <div class="main-content">
        <?php include __DIR__ . '/components/navbar.php'; ?>
        <main>
            <?php if ($success): ?><div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>

            <div class="page-header">
                <h2>Kelola Dokter</h2>
                <button class="btn btn-primary" data-modal-open="modalTambahDokter">
                    ➕ Tambah Dokter
                </button>
            </div>

            <div class="card">
                <div class="table-wrapper">
                    <?php if (empty($dokterList)): ?>
                    <div class="empty-state">
                        <span class="empty-state-icon">👨‍⚕️</span>
                        <h3>Belum ada dokter</h3>
                        <p>Klik "Tambah Dokter" untuk menambahkan akun dokter baru.</p>
                    </div>
                    <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Tanggal Daftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dokterList as $i => $d): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($d['nama']) ?></td>
                                <td><?= htmlspecialchars($d['email']) ?></td>
                                <td><?= date('d M Y', strtotime($d['created_at'])) ?></td>
                                <td style="white-space:nowrap">
                                    <button class="btn btn-warning btn-sm"
                                        onclick="editDokter(<?= htmlspecialchars(json_encode($d)) ?>)">
                                        ✏️ Edit
                                    </button>
                                    <form method="POST" style="display:inline">
                                        <input type="hidden" name="action" value="hapus">
                                        <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm no-loading"
                                            data-confirm="Yakin ingin menghapus dokter ini?">
                                            🗑️ Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<div class="modal-overlay" id="modalTambahDokter">
    <div class="modal">
        <div class="modal-header">
            <h3>Tambah Akun Dokter</h3>
            <button class="modal-close">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="tambah">
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" placeholder="Nama dokter" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="email@klinik.com" required>
                </div>
                <div class="form-group">
                    <label>Kata Sandi</label>
                    <input type="password" name="password" placeholder="Minimal 6 karakter" required minlength="6">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-close">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalEditDokter">
    <div class="modal">
        <div class="modal-header">
            <h3>Edit Akun Dokter</h3>
            <button class="modal-close">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="editDokterId">

            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" id="editNama" placeholder="Nama dokter" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="editEmail" placeholder="email@klinik.com" required>
                </div>
                <div class="form-group">
                    <label>Kata Sandi Baru <span style="color:var(--text-muted);font-weight:400">(kosongkan jika tidak diganti)</span></label>
                    <input type="password" name="password" placeholder="Minimal 6 karakter" minlength="6">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-close">Batal</button>
                <button type="submit" class="btn btn-primary">Perbarui</button>
            </div>
        </form>
    </div>
</div>

<script>
function editDokter(data) {
    document.getElementById('editDokterId').value = data.id;
    document.getElementById('editNama').value = data.nama;
    document.getElementById('editEmail').value = data.email;
    openModalById('modalEditDokter');
}
</script>
<?php include __DIR__ . '/components/footer.php'; ?>