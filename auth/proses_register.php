<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Pasien.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /klinik/register.php');
    exit;
}

$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$tanggalLahir = $_POST['tanggal_lahir'] ?? '';
$jenisKelamin = $_POST['jenis_kelamin'] ?? '';
$noTelp = trim($_POST['no_telp'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');

if (empty($nama) || empty($email) || empty($password) || empty($tanggalLahir) || empty($jenisKelamin)) {
    $_SESSION['error'] = 'Semua field wajib diisi.';
    header('Location: /klinik/register.php');
    exit;
}

if (strlen($password) < 6) {
    $_SESSION['error'] = 'Kata sandi minimal 6 karakter.';
    header('Location: /klinik/register.php');
    exit;
}

$db = (new Database())->connect();
$userModel = new User($db);
$pasienModel = new Pasien($db);

$userId = $userModel->register($nama, $email, $password);

if (!$userId) {
    $_SESSION['error'] = 'Email sudah terdaftar. Gunakan email lain.';
    header('Location: /klinik/register.php');
    exit;
}

$pasienModel->create($userId, $nama, $tanggalLahir, $jenisKelamin, $alamat, $noTelp);

$_SESSION['success'] = 'Pendaftaran berhasil! Silakan masuk.';
header('Location: /klinik/login.php');
exit;
