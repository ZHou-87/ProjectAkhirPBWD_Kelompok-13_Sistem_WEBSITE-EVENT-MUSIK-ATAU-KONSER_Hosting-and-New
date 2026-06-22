<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function proteksiAdmin() {
    if (!isset($_SESSION['user'])) {
        header("Location: /konser_kita/login.php");
        exit();
    }
    if ($_SESSION['user']['ROLE'] !== 'admin') {
        header("Location: /konser_kita/index.php");
        exit();
    }
}
?>