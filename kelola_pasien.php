<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Pasien.php';

cekLogin();
cekRole(['admin']);

$db = (new Database())->connect();
$pasienModel = new Pasien($db);

$action = $_POST['action'] ?? '';
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $tanggalLahir = $_POST['tanggal_lahir'] ?? '';
    $jenisKelamin = $_POST['jenis_kelamin'] ?? '';
    $noTelp = trim($_POST['no_telp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');

    if ($action === 'tambah') {
        if (empty($nama) || empty($tanggalLahir) || empty($jenisKelamin)) {
            $error = 'Field nama, tanggal lahir, dan jenis kelamin wajib diisi.';
        } else {
            $result = $pasienModel->create(null, $nama, $tanggalLahir, $jenisKelamin, $alamat, $noTelp);
            if ($result) {
                $success = 'Data pasien berhasil ditambahkan.';
            } else {
                $error = 'Gagal menambahkan pasien.';
            }
        }
    } elseif ($action === 'edit') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0 && !empty($nama)) {
            $pasienModel->update($id, $nama, $tanggalLahir, $jenisKelamin, $alamat, $noTelp);
            $success = 'Data pasien berhasil diperbarui.';
        } else {
            $error = 'Data tidak valid.';
        }
    } elseif ($action === 'hapus') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $pasienModel->delete($id);
            $success = 'Data pasien berhasil dihapus.';
        }
    }
}

$pasienList = $pasienModel->getAll();
$pageTitle = 'Kelola Pasien';
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
                <h2>Kelola Pasien</h2>
                <button class="btn btn-primary" data-modal-open="modalTambahPasien">
                    ➕ Tambah Pasien
                </button>
            </div>

            <div class="card">
                <div class="table-wrapper">
                    <?php if (empty($pasienList)): ?>
                    <div class="empty-state">
                        <span class="empty-state-icon">🧑‍⚕️</span>
                        <h3>Belum ada data pasien</h3>
                        <p>Belum ada data pasien yang terdaftar.</p>
                    </div>
                    <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama</th>
                                <th>Jenis Kelamin</th>
                                <th>Tanggal Lahir</th>
                                <th>No. Telepon</th>
                                <th>Alamat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pasienList as $i => $p): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($p['nama']) ?></td>
                                <td><?= $p['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                                <td><?= $p['tanggal_lahir'] ? date('d M Y', strtotime($p['tanggal_lahir'])) : '-' ?></td>
                                <td><?= htmlspecialchars($p['no_telp'] ?? '-') ?></td>
                                <td><?= htmlspecialchars(mb_strimwidth($p['alamat'] ?? '', 0, 30, '...')) ?></td>
                                <td style="white-space:nowrap">
                                    <button class="btn btn-warning btn-sm"
                                        onclick="editPasien(<?= htmlspecialchars(json_encode($p)) ?>)">
                                        ✏️ Edit
                                    </button>
                                    <form method="POST" style="display:inline">
                                        <input type="hidden" name="action" value="hapus">
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm no-loading"
                                            data-confirm="Yakin ingin menghapus pasien ini? Semua rekam medis terkait juga akan terhapus.">
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

<div class="modal-overlay" id="modalTambahPasien">
    <div class="modal">
        <div class="modal-header">
            <h3>Tambah Pasien</h3>
            <button class="modal-close">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="tambah">
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" placeholder="Nama pasien" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" required>
                    </div>
                    <div class="form-group">
                        <label>Jenis Kelamin</label>
                        <select name="jenis_kelamin" required>
                            <option value="">-- Pilih --</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Nomor Telepon</label>
                    <input type="tel" name="no_telp" placeholder="08xxxxxxxxxx">
                </div>
                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="alamat" placeholder="Alamat lengkap"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-close">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalEditPasien">
    <div class="modal">
        <div class="modal-header">
            <h3>Edit Pasien</h3>
            <button class="modal-close">✕</button>
        </div>
        <form method="POST" id="formEditPasien">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="editPasienId">
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" id="editNama" placeholder="Nama pasien" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" id="editTanggalLahir" required>
                    </div>
                    <div class="form-group">
                        <label>Jenis Kelamin</label>
                        <select name="jenis_kelamin" id="editJenisKelamin" required>
                            <option value="">-- Pilih --</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Nomor Telepon</label>
                    <input type="tel" name="no_telp" id="editNoTelp" placeholder="08xxxxxxxxxx">
                </div>
                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="alamat" id="editAlamat" placeholder="Alamat lengkap"></textarea>
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
function editPasien(data) {
    document.getElementById('editPasienId').value = data.id;
    document.getElementById('editNama').value = data.nama;
    document.getElementById('editTanggalLahir').value = data.tanggal_lahir;
    document.getElementById('editJenisKelamin').value = data.jenis_kelamin;
    document.getElementById('editNoTelp').value = data.no_telp || '';
    document.getElementById('editAlamat').value = data.alamat || '';
    openModalById('modalEditPasien');
}
</script>

<?php include __DIR__ . '/components/footer.php'; ?>