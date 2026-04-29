<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['user_id'])) {
    header('Location: /klinik/dashboard.php');
} else {
    header('Location: /klinik/login.php');
}
exit;
