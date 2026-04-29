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
            try {
                $stmt = $db->prepare('INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)');
                $stmt->execute([
                    htmlspecialchars($nama),
                    htmlspecialchars($email),
                    password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
                    'admin',
                ]);
                $success = 'Akun admin berhasil ditambahkan.';
            } catch (PDOException) {
                $error = 'Email sudah terdaftar.';
            }
        }
    } elseif ($action === 'edit') {
        $id = (int) ($_POST['id'] ?? 0);
        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($id === (int) $_SESSION['user_id']) {
            $error = 'Gunakan halaman Profil untuk mengubah akun Anda sendiri.';
        } elseif ($id > 0 && !empty($nama) && !empty($email)) {
            $userModel->update($id, $nama, $email, !empty($password) ? $password : null);
            $success = 'Data admin berhasil diperbarui.';
        } else {
            $error = 'Nama dan email wajib diisi.';
        }
    } elseif ($action === 'hapus') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id === (int) $_SESSION['user_id']) {
            $error = 'Anda tidak dapat menghapus akun Anda sendiri.';
        } elseif ($id > 0) {
            $userModel->delete($id);
            $success = 'Akun admin berhasil dihapus.';
        }
    }
}

$stmt = $db->query("SELECT id, nama, email, role, created_at FROM users WHERE role = 'admin' ORDER BY created_at DESC");
$adminList = $stmt->fetchAll();

$pageTitle = 'Kelola Admin';
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
                <h2>Kelola Admin</h2>
                <button class="btn btn-primary" data-modal-open="modalTambahAdmin">
                    ➕ Tambah Admin
                </button>
            </div>

            <div class="card">
                <div class="table-wrapper">
                    <?php if (empty($adminList)): ?>
                    <div class="empty-state">
                        <span class="empty-state-icon">🛡️</span>
                        <h3>Belum ada admin</h3>
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
                            <?php foreach ($adminList as $i => $a): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <?= htmlspecialchars($a['nama']) ?>
                                    <?php if ($a['id'] === (int) $_SESSION['user_id']): ?>
                                    <span style="font-size:11px;color:var(--text-muted)">(Anda)</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($a['email']) ?></td>
                                <td><?= date('d M Y', strtotime($a['created_at'])) ?></td>
                                <td style="white-space:nowrap">
                                    <?php if ($a['id'] !== (int) $_SESSION['user_id']): ?>
                                    <button class="btn btn-warning btn-sm"
                                        onclick="editAdmin(<?= htmlspecialchars(json_encode($a)) ?>)">
                                        ✏️ Edit
                                    </button>
                                    <form method="POST" style="display:inline">
                                        <input type="hidden" name="action" value="hapus">
                                        <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm no-loading"
                                            data-confirm="Yakin ingin menghapus admin ini?">
                                            🗑️ Hapus
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <span style="color:var(--text-muted);font-size:12px">Akun Anda</span>
                                    <?php endif; ?>
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

<div class="modal-overlay" id="modalTambahAdmin">
    <div class="modal">
        <div class="modal-header">
            <h3>Tambah Akun Admin</h3>
            <button class="modal-close">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="tambah">
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" placeholder="Nama admin" required>
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

<div class="modal-overlay" id="modalEditAdmin">
    <div class="modal">
        <div class="modal-header">
            <h3>Edit Akun Admin</h3>
            <button class="modal-close">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="editAdminId">
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" id="editNama" placeholder="Nama admin" required>
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
function editAdmin(data) {
    document.getElementById('editAdminId').value = data.id;
    document.getElementById('editNama').value = data.nama;
    document.getElementById('editEmail').value = data.email;
    openModalById('modalEditAdmin');
}
</script>
<?php include __DIR__ . '/components/footer.php'; ?>