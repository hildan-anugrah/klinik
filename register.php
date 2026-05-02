<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['user_id'])) {
    header('Location: /dashboard.php');
    exit;
}
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar | Sistem Manajemen Klinik</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <span class="logo-icon-lg">🏥</span>
            <h1>MediKlinik</h1>
            <p>Buat akun baru sebagai pasien</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="/auth/proses_register.php" method="POST">
            <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" placeholder="Nama lengkap Anda" required>
            </div>
            <div class="form-group">
                <label for="email">Alamat Email</label>
                <input type="email" id="email" name="email" placeholder="contoh@email.com" required>
            </div>
            <div class="form-group">
                <label for="password">Kata Sandi</label>
                <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required minlength="6">
            </div>
            <div class="form-group">
                <label for="tanggal_lahir">Tanggal Lahir</label>
                <input type="date" id="tanggal_lahir" name="tanggal_lahir" required>
            </div>
            <div class="form-group">
                <label for="jenis_kelamin">Jenis Kelamin</label>
                <select id="jenis_kelamin" name="jenis_kelamin" required>
                    <option value="">-- Pilih --</option>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>
            </div>
            <div class="form-group">
                <label for="no_telp">Nomor Telepon</label>
                <input type="tel" id="no_telp" name="no_telp" placeholder="08xxxxxxxxxx" required>
            </div>
            <div class="form-group">
                <label for="alamat">Alamat</label>
                <textarea id="alamat" name="alamat" placeholder="Alamat lengkap Anda" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:11px;">
                Daftar
            </button>
        </form>

        <p class="auth-footer-text">
            Sudah punya akun? <a href="/login.php">Masuk di sini</a>
        </p>
    </div>
</div>
<script src="/assets/js/main.js"></script>
</body>
</html>
