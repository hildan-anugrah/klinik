<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /klinik/login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    $_SESSION['error'] = 'Email dan kata sandi wajib diisi.';
    header('Location: /klinik/login.php');
    exit;
}

$db = (new Database())->connect();
$userModel = new User($db);
$user = $userModel->login($email, $password);

if (!$user) {
    $_SESSION['error'] = 'Email atau kata sandi salah.';
    header('Location: /klinik/login.php');
    exit;
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['nama'] = $user['nama'];
$_SESSION['role'] = $user['role'];

header('Location: /klinik/dashboard.php');
exit;
