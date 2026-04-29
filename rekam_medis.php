<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/RekamMedis.php';
require_once __DIR__ . '/models/Pasien.php';

cekLogin();

$db = (new Database())->connect();
$rekamModel = new RekamMedis($db);
$pasienModel = new Pasien($db);

$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];

$action = $_POST['action'] ?? '';
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($role, ['dokter', 'admin'])) {
    if ($action === 'tambah' && $role === 'dokter') {
        $pasienId = (int) ($_POST['pasien_id'] ?? 0);
        $keluhan = trim($_POST['keluhan'] ?? '');
        $diagnosa = trim($_POST['diagnosa'] ?? '');
        $obat = trim($_POST['obat'] ?? '');
        $catatan = trim($_POST['catatan'] ?? '');
        if ($pasienId > 0 && !empty($keluhan)) {
            $result = $rekamModel->create($pasienId, $userId, $keluhan, $diagnosa, $obat, $catatan);
            if ($result) {
                $success = 'Rekam medis berhasil ditambahkan.';
            } else {
                $error = 'Gagal menambahkan rekam medis.';
            }
        } else {
            $error = 'Pasien dan keluhan wajib diisi.';
        }
    } elseif ($action === 'edit' && $role === 'dokter') {
        $id = (int) ($_POST['id'] ?? 0);
        $rekam = $id > 0 ? $rekamModel->getById($id) : null;
        if ($rekam && (int) $rekam['dokter_id'] === $userId) {
            $rekamModel->update(
                $id,
                trim($_POST['keluhan'] ?? ''),
                trim($_POST['diagnosa'] ?? ''),
                trim($_POST['obat'] ?? ''),
                trim($_POST['catatan'] ?? '')
            );
            $success = 'Rekam medis berhasil diperbarui.';
        } else {
            $error = 'Anda tidak berhak mengubah rekam medis ini.';
        }
    } elseif ($action === 'hapus') {
        $id = (int) ($_POST['id'] ?? 0);
        $rekam = $id > 0 ? $rekamModel->getById($id) : null;
        if ($rekam) {
            $canDelete = $role === 'admin' || ($role === 'dokter' && (int) $rekam['dokter_id'] === $userId);
            if ($canDelete) {
                $rekamModel->softDelete($id);
                $success = 'Rekam medis berhasil dihapus.';
            } else {
                $error = 'Anda tidak berhak menghapus rekam medis ini.';
            }
        }
    }
}

if ($role === 'admin') {
    $rekamList = $rekamModel->getAll();
    $pasienList = $pasienModel->getAll();
} elseif ($role === 'dokter') {
    $rekamList = $rekamModel->getByDokter($userId);
    $pasienList = $pasienModel->getAll();
} else {
    $pasien = $pasienModel->getByUserId($userId);
    $rekamList = $pasien ? $rekamModel->getByPasien($pasien['id']) : [];
    $pasienList = [];
}

$pageTitle = 'Rekam Medis';
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
                <h2>Rekam Medis</h2>
                <?php if ($role === 'dokter'): ?>
                <button class="btn btn-primary" data-modal-open="modalTambahRekam">
                    ➕ Tambah Rekam Medis
                </button>
                <?php endif; ?>
            </div>

            <div class="card">
                <div class="table-wrapper">
                    <?php if (empty($rekamList)): ?>
                    <div class="empty-state">
                        <span class="empty-state-icon">📋</span>
                        <h3>Belum ada rekam medis</h3>
                        <p>
                            <?php if ($role === 'dokter'): ?>
                                Klik "Tambah Rekam Medis" untuk menambahkan data baru.
                            <?php else: ?>
                                Rekam medis Anda akan muncul di sini.
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Pasien</th>
                                <?php if ($role !== 'pasien'): ?><th>Dokter</th><?php endif; ?>
                                <th>Keluhan</th>
                                <th>Diagnosa</th>
                                <th>Obat</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rekamList as $i => $r): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($r['nama_pasien']) ?></td>
                                <?php if ($role !== 'pasien'): ?>
                                <td><?= htmlspecialchars($r['nama_dokter']) ?></td>
                                <?php endif; ?>
                                <td><?= htmlspecialchars(mb_strimwidth($r['keluhan'], 0, 40, '...')) ?></td>
                                <td><?= htmlspecialchars(mb_strimwidth($r['diagnosa'] ?? '', 0, 40, '...')) ?></td>
                                <td><?= htmlspecialchars(mb_strimwidth($r['obat'] ?? '', 0, 30, '...')) ?></td>
                                <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                                <td style="white-space:nowrap">
                                    <button class="btn btn-secondary btn-sm"
                                        onclick="lihatRekam(<?= htmlspecialchars(json_encode($r)) ?>)">
                                        👁️ Lihat
                                    </button>
                                    <?php if ($role === 'dokter' && (int) $r['dokter_id'] === $userId): ?>
                                    <button class="btn btn-warning btn-sm"
                                        onclick="editRekam(<?= htmlspecialchars(json_encode($r)) ?>)">
                                        ✏️ Edit
                                    </button>
                                    <?php endif; ?>
                                    <?php if ($role === 'admin' || ($role === 'dokter' && (int) $r['dokter_id'] === $userId)): ?>
                                    <form method="POST" style="display:inline">
                                        <input type="hidden" name="action" value="hapus">
                                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm no-loading"
                                            data-confirm="Yakin ingin menghapus rekam medis ini?">
                                            🗑️
                                        </button>
                                    </form>
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

<?php if ($role === 'dokter'): ?>
<div class="modal-overlay" id="modalTambahRekam">
    <div class="modal">
        <div class="modal-header">
            <h3>Tambah Rekam Medis</h3>
            <button class="modal-close">✕</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="tambah">
            <input type="hidden" name="pasien_id" id="inputPasienId">
            <div class="modal-body">
                <div class="form-group">
                    <label>Cari Pasien</label>
                    <input type="text" id="searchPasien" placeholder="Ketik nama pasien..." autocomplete="off">
                    <div id="searchResult" style="border:1px solid #e2e8f0;border-top:none;border-radius:0 0 8px 8px;display:none;max-height:180px;overflow-y:auto;background:#fff;position:relative;z-index:10;"></div>
                    <div id="pasienTerpilih" style="display:none;margin-top:8px;padding:8px 12px;background:#dbeafe;border-radius:8px;font-size:13px;display:flex;align-items:center;justify-content:space-between;">
                        <span id="labelPasienTerpilih"></span>
                        <button type="button" onclick="resetPasien()" style="background:none;border:none;cursor:pointer;color:#dc2626;font-size:16px;line-height:1;">✕</button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Keluhan</label>
                    <textarea name="keluhan" placeholder="Keluhan pasien..." required></textarea>
                </div>
                <div class="form-group">
                    <label>Diagnosa</label>
                    <textarea name="diagnosa" placeholder="Diagnosa dokter..."></textarea>
                </div>
                <div class="form-group">
                    <label>Obat</label>
                    <textarea name="obat" placeholder="Resep obat..."></textarea>
                </div>
                <div class="form-group">
                    <label>Catatan</label>
                    <textarea name="catatan" placeholder="Catatan tambahan..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-close">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalEditRekam">
    <div class="modal">
        <div class="modal-header">
            <h3>Edit Rekam Medis</h3>
            <button class="modal-close">✕</button>
        </div>
        <form method="POST" id="formEditRekam">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="editRekamId">
            <div class="modal-body">
                <div class="form-group">
                    <label>Keluhan</label>
                    <textarea name="keluhan" id="editKeluhan" required></textarea>
                </div>
                <div class="form-group">
                    <label>Diagnosa</label>
                    <textarea name="diagnosa" id="editDiagnosa"></textarea>
                </div>
                <div class="form-group">
                    <label>Obat</label>
                    <textarea name="obat" id="editObat"></textarea>
                </div>
                <div class="form-group">
                    <label>Catatan</label>
                    <textarea name="catatan" id="editCatatan"></textarea>
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
const dataPasien = <?= json_encode(array_map(fn($p) => ['id' => $p['id'], 'nama' => $p['nama']], $pasienList)) ?>;

const searchInput = document.getElementById('searchPasien');
const searchResult = document.getElementById('searchResult');
const inputPasienId = document.getElementById('inputPasienId');
const pasienTerpilih = document.getElementById('pasienTerpilih');
const labelPasienTerpilih = document.getElementById('labelPasienTerpilih');

searchInput.addEventListener('input', function () {
    const keyword = this.value.trim().toLowerCase();
    searchResult.innerHTML = '';

    if (keyword.length === 0) {
        searchResult.style.display = 'none';
        return;
    }

    const filtered = dataPasien.filter(p => p.nama.toLowerCase().includes(keyword));

    if (filtered.length === 0) {
        searchResult.innerHTML = '<div style="padding:10px 14px;color:#64748b;font-size:13px;">Pasien tidak ditemukan</div>';
    } else {
        filtered.forEach(p => {
            const item = document.createElement('div');
            item.textContent = p.nama;
            item.style.cssText = 'padding:10px 14px;cursor:pointer;font-size:14px;border-bottom:1px solid #f1f5f9;';
            item.addEventListener('mouseenter', () => item.style.background = '#f1f5f9');
            item.addEventListener('mouseleave', () => item.style.background = '');
            item.addEventListener('click', () => pilihPasien(p.id, p.nama));
            searchResult.appendChild(item);
        });
    }

    searchResult.style.display = 'block';
});

function pilihPasien(id, nama) {
    inputPasienId.value = id;
    labelPasienTerpilih.textContent = '✅ ' + nama;
    pasienTerpilih.style.display = 'flex';
    searchInput.value = '';
    searchInput.style.display = 'none';
    searchResult.style.display = 'none';
}

function resetPasien() {
    inputPasienId.value = '';
    pasienTerpilih.style.display = 'none';
    searchInput.style.display = 'block';
    searchInput.value = '';
    searchInput.focus();
}

document.addEventListener('click', function (e) {
    if (!searchInput.contains(e.target) && !searchResult.contains(e.target)) {
        searchResult.style.display = 'none';
    }
});
</script>
<?php endif; ?>

<div class="modal-overlay" id="modalLihatRekam">
    <div class="modal">
        <div class="modal-header">
            <h3>Detail Rekam Medis</h3>
            <button class="modal-close">✕</button>
        </div>
        <div class="modal-body">
            <table class="detail-table">
                <tr><td>Pasien</td><td id="viewPasien"></td></tr>
                <tr><td>Dokter</td><td id="viewDokter"></td></tr>
                <tr><td>Tanggal</td><td id="viewTanggal"></td></tr>
                <tr><td>Keluhan</td><td id="viewKeluhan"></td></tr>
                <tr><td>Diagnosa</td><td id="viewDiagnosa"></td></tr>
                <tr><td>Obat</td><td id="viewObat"></td></tr>
                <tr><td>Catatan</td><td id="viewCatatan"></td></tr>
            </table>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary modal-close">Tutup</button>
        </div>
    </div>
</div>

<script>
function lihatRekam(data) {
    document.getElementById('viewPasien').textContent = data.nama_pasien || '-';
    document.getElementById('viewDokter').textContent = data.nama_dokter || '-';
    document.getElementById('viewTanggal').textContent = data.created_at ? data.created_at.substring(0, 10) : '-';
    document.getElementById('viewKeluhan').textContent = data.keluhan || '-';
    document.getElementById('viewDiagnosa').textContent = data.diagnosa || '-';
    document.getElementById('viewObat').textContent = data.obat || '-';
    document.getElementById('viewCatatan').textContent = data.catatan || '-';
    openModalById('modalLihatRekam');
}

function editRekam(data) {
    document.getElementById('editRekamId').value = data.id;
    document.getElementById('editKeluhan').value = data.keluhan || '';
    document.getElementById('editDiagnosa').value = data.diagnosa || '';
    document.getElementById('editObat').value = data.obat || '';
    document.getElementById('editCatatan').value = data.catatan || '';
    openModalById('modalEditRekam');
}
</script>
<?php include __DIR__ . '/components/footer.php'; ?>