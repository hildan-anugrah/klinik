<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Pasien.php';
require_once __DIR__ . '/models/RekamMedis.php';

cekLogin();

$db = (new Database())->connect();
$pasienModel = new Pasien($db);
$rekamModel = new RekamMedis($db);

$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];

$stats = [];

if ($role === 'admin') {
    $totalPasien = $pasienModel->countAll();
    $totalDokter = (int) $db->query("SELECT COUNT(*) FROM users WHERE role='dokter'")->fetchColumn();
    $totalRekam = $rekamModel->countAll();
    $stats = [
        ['label' => 'Total Pasien',      'value' => $totalPasien, 'icon' => '👤',  'color' => 'stat-icon-blue'],
        ['label' => 'Total Dokter',       'value' => $totalDokter, 'icon' => '👨‍⚕️', 'color' => 'stat-icon-green'],
        ['label' => 'Total Rekam Medis',  'value' => $totalRekam,  'icon' => '📋',  'color' => 'stat-icon-orange'],
    ];

} elseif ($role === 'dokter') {
    $totalRekam = $rekamModel->countByDokter($userId);
    $stmtPasien = $db->prepare("SELECT COUNT(DISTINCT pasien_id) FROM rekam_medis WHERE dokter_id = ? AND is_deleted = 0");
    $stmtPasien->execute([$userId]);
    $pasienDitangani = (int) $stmtPasien->fetchColumn();

    $stats = [
        ['label' => 'Pasien Ditangani',   'value' => $pasienDitangani, 'icon' => '👤', 'color' => 'stat-icon-blue'],
        ['label' => 'Rekam Medis Dibuat', 'value' => $totalRekam,      'icon' => '📋', 'color' => 'stat-icon-orange'],
    ];

} elseif ($role === 'pasien') {
    $pasien = $pasienModel->getByUserId($userId);
    $totalRekam = $pasien ? $rekamModel->countByPasien($pasien['id']) : 0;
    $stats = [
        ['label' => 'Rekam Medis Saya', 'value' => $totalRekam, 'icon' => '📋', 'color' => 'stat-icon-blue'],
    ];
}

$pageTitle = 'Dashboard';
?>
<?php include __DIR__ . '/components/head.php'; ?>
<div class="layout">
    <?php include __DIR__ . '/components/sidebar.php'; ?>
    <div class="main-content">
        <?php include __DIR__ . '/components/navbar.php'; ?>
        <main>
            <div class="page-header">
                <h2>Selamat datang, <?= htmlspecialchars($_SESSION['nama']) ?>! 👋</h2>
            </div>

            <div class="stats-grid">
                <?php foreach ($stats as $stat): ?>
                <div class="stat-card">
                    <div class="stat-icon <?= $stat['color'] ?>"><?= $stat['icon'] ?></div>
                    <div class="stat-info">
                        <h3><?= $stat['value'] ?></h3>
                        <p><?= $stat['label'] ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if ($role === 'admin'): ?>
            <div class="card">
                <div class="card-header">Pengguna Terbaru</div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Peran</th>
                                <th>Tanggal Daftar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $recentUsers = $db->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();
                            foreach ($recentUsers as $u):
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($u['nama'] ?? '') ?></td>
                                <td><?= htmlspecialchars($u['email'] ?? '') ?></td>
                                <td><span class="badge badge-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                                <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php elseif ($role === 'dokter'): ?>
            <div class="card">
                <div class="card-header">Rekam Medis Terbaru</div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Pasien</th>
                                <th>Keluhan</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $recent = array_slice($rekamModel->getByDokter($userId), 0, 5);
                            if (empty($recent)):
                            ?>
                            <tr><td colspan="3">
                                <div class="empty-state">
                                    <span class="empty-state-icon">📋</span>
                                    <h3>Belum ada rekam medis</h3>
                                </div>
                            </td></tr>
                            <?php else: foreach ($recent as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['nama_pasien'] ?? '-') ?></td>
                                <td><?= htmlspecialchars(mb_strimwidth($r['keluhan'] ?? '', 0, 50, '...')) ?></td>
                                <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php elseif ($role === 'pasien'): ?>
            <div class="card">
                <div class="card-body">
                    <?php if ($pasien): ?>
                    <div class="profile-header">
                        <div class="profile-avatar"><?= mb_strtoupper(mb_substr($_SESSION['nama'] ?? 'U', 0, 1)) ?></div>
                        <div class="profile-info">
                            <h2><?= htmlspecialchars($pasien['nama'] ?? '') ?></h2>
                            <p><?= htmlspecialchars($pasien['email'] ?? '') ?></p>
                        </div>
                    </div>
                    <table class="detail-table">
                        <tr>
                            <td>Tanggal Lahir</td>
                            <td><?= !empty($pasien['tanggal_lahir']) ? date('d M Y', strtotime($pasien['tanggal_lahir'])) : '-' ?></td>
                        </tr>
                        <tr>
                            <td>Jenis Kelamin</td>
                            <td>
                                <?php
                                if (isset($pasien['jenis_kelamin'])) {
                                    echo $pasien['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan';
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>No. Telepon</td>
                            <td><?= htmlspecialchars($pasien['no_telp'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <td>Alamat</td>
                            <td><?= htmlspecialchars($pasien['alamat'] ?? '-') ?></td>
                        </tr>
                    </table>
                    <?php else: ?>
                    <div class="empty-state">
                        <span class="empty-state-icon">👤</span>
                        <h3>Data profil belum lengkap</h3>
                        <p>Silakan lengkapi data profil Anda terlebih dahulu.</p>
                        <a href="/klinik/profil.php" class="btn btn-primary" style="margin-top:12px;">Lengkapi Profil</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

        </main>
    </div>
</div>
<?php include __DIR__ . '/components/footer.php'; ?>