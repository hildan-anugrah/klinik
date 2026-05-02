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
        <?= sidebarLink('/dashboard.php',  'Dashboard', $currentPage) ?>

        <?php if ($role === 'admin'): ?>
            <?= sidebarLink('/kelola_pasien.php',  'Kelola Pasien', $currentPage) ?>
            <?= sidebarLink('/kelola_dokter.php',  'Kelola Dokter', $currentPage) ?>
            <?= sidebarLink('/kelola_admin.php',  'Kelola Admin', $currentPage) ?>
            <?= sidebarLink('/rekam_medis.php',  'Rekam Medis', $currentPage) ?>
        <?php elseif ($role === 'dokter'): ?>
            <?= sidebarLink('/rekam_medis.php',  'Rekam Medis', $currentPage) ?>
        <?php elseif ($role === 'pasien'): ?>
            <?= sidebarLink('/rekam_medis.php',  'Rekam Medis Saya', $currentPage) ?>
        <?php endif; ?>

        <?= sidebarLink('/profil.php',  'Profil Saya', $currentPage) ?>
    </nav>
    <div class="sidebar-footer">
        <a href="/logout.php" class="sidebar-link logout-link"><span>Keluar</span></a>
    </div>
</aside>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
