<?php
$role = $_SESSION['role'] ?? '';
$currentPage = basename($_SERVER['PHP_SELF']);

function sidebarLink(string $href, string $label, string $currentPage): string
{
    $page = basename($href);
    $activeClass = $currentPage === $page ? 'active' : '';
    return "<a href=\"{$href}\" class=\"sidebar-link {$activeClass}\"><span>{$label}</span></a>";
}
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <span class="logo-icon">➕</span>
            <span class="logo-text">MediKlinik</span>
        </div>
        <button class="sidebar-close" id="sidebarClose">✕</button>
    </div>
    <nav class="sidebar-nav">
        <?= sidebarLink('/klinik/dashboard.php',  'Dashboard', $currentPage) ?>

        <?php if ($role === 'admin'): ?>
            <?= sidebarLink('/klinik/kelola_pasien.php',  'Kelola Pasien', $currentPage) ?>
            <?= sidebarLink('/klinik/kelola_dokter.php',  'Kelola Dokter', $currentPage) ?>
            <?= sidebarLink('/klinik/kelola_admin.php',  'Kelola Admin', $currentPage) ?>
            <?= sidebarLink('/klinik/rekam_medis.php',  'Rekam Medis', $currentPage) ?>
        <?php elseif ($role === 'dokter'): ?>
            <?= sidebarLink('/klinik/rekam_medis.php',  'Rekam Medis', $currentPage) ?>
        <?php elseif ($role === 'pasien'): ?>
            <?= sidebarLink('/klinik/rekam_medis.php',  'Rekam Medis Saya', $currentPage) ?>
        <?php endif; ?>

        <?= sidebarLink('/klinik/profil.php',  'Profil Saya', $currentPage) ?>
    </nav>
    <div class="sidebar-footer">
        <a href="/klinik/logout.php" class="sidebar-link logout-link"><span>Keluar</span></a>
    </div>
</aside>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
