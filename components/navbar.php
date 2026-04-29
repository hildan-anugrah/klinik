<?php
$roleBadgeMap = [
    'admin'  => 'badge-admin',
    'dokter' => 'badge-dokter',
    'pasien' => 'badge-pasien',
];
$roleLabel = [
    'admin'  => 'Admin',
    'dokter' => 'Dokter',
    'pasien' => 'Pasien',
];
$role = $_SESSION['role'] ?? 'pasien';
?>
<header class="navbar">
    <button class="navbar-toggle" id="sidebarToggle">☰</button>
    <div class="navbar-title"><?= htmlspecialchars($pageTitle ?? '') ?></div>
    <div class="navbar-user">
        <span class="badge <?= $roleBadgeMap[$role] ?>"><?= $roleLabel[$role] ?></span>
        <span class="navbar-username"><?= htmlspecialchars($_SESSION['nama'] ?? '') ?></span>
        <div class="navbar-avatar"><?= mb_strtoupper(mb_substr($_SESSION['nama'] ?? 'U', 0, 1)) ?></div>
    </div>
</header>
