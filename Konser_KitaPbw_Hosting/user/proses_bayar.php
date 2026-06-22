<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$id_user = (int)$_SESSION['user']['id_user'];


if (isset($_POST['id_transaksi_selesai'])) {
    $id_transaksi = (int)$_POST['id_transaksi_selesai'];
    
    $stmt_update_status = mysqli_prepare($conn, "UPDATE transaksi SET STATUS = 'paid' WHERE id_transaksi = ? AND id_user = ?");
    mysqli_stmt_bind_param($stmt_update_status, "ii", $id_transaksi, $id_user);
    mysqli_stmt_execute($stmt_update_status);
    

    header("Location: detail_tiket_saya.php?id=" . $id_transaksi);
    exit;
}


if (isset($_POST['id_kategori'])) {
    $id_kategori = (int)$_POST['id_kategori'];
    $jumlah = intval($_POST['jumlah']);
    $total_bayar = (int)$_POST['total_bayar']; 
    $metode = $_POST['metode_pembayaran'];
    $aksi = $_POST['aksi'];

    if ($aksi === 'bayar_sekarang') {
        $status_final = 'paid';
    } elseif ($aksi === 'bayar_nanti') {
        $status_final = 'pending';
    } else {
        $status_final = 'cancel';
    }

    if ($metode === 'QRIS') {
        $nomor_va = 'QRIS_' . rand(100000, 999999);
    } elseif ($metode === 'BCA') {
        $nomor_va = '880128' . rand(100000, 999999);
    } elseif ($metode === 'Mandiri') {
        $nomor_va = '70012' . rand(1000000, 9999999);
    } else {
        $nomor_va = '988123' . rand(100000, 999999);
    }

    $tanggal_sekarang = date('Y-m-d H:i:s');

    $stmt_insert_tx = mysqli_prepare($conn, "INSERT INTO transaksi (id_user, total_bayar, nomor_va, STATUS, tanggal_daftar) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt_insert_tx, "issss", $id_user, $total_bayar, $nomor_va, $status_final, $tanggal_sekarang);
    mysqli_stmt_execute($stmt_insert_tx);
    $id_transaksi = mysqli_insert_id($conn);

    $stmt_insert_tiket = mysqli_prepare($conn, "INSERT INTO e_tiket (id_transaksi, id_kategori) VALUES (?, ?)");
    for ($i = 0; $i < $jumlah; $i++) {
        mysqli_stmt_bind_param($stmt_insert_tiket, "ii", $id_transaksi, $id_kategori);
        mysqli_stmt_execute($stmt_insert_tiket);
    }

    if ($status_final !== 'cancel') {
        $stmt_update_kuota = mysqli_prepare($conn, "UPDATE kategori SET kuota_sisa = kuota_sisa - ? WHERE id_kategori = ?");
        mysqli_stmt_bind_param($stmt_update_kuota, "ii", $jumlah, $id_kategori);
        mysqli_stmt_execute($stmt_update_kuota);
    }
    
   
    if ($status_final === 'paid') {
        header("Location: detail_tiket_saya.php?id=" . $id_transaksi);
    } else {
        header("Location: tiket_saya.php");
    }
    exit;
}

header("Location: ../index.php");
exit;
?>