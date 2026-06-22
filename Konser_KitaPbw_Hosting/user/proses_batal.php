<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../koneksi.php';

if (isset($_GET['id_transaksi']) && isset($_SESSION['user'])) {
    $id_transaksi = mysqli_real_escape_string($conn, $_GET['id_transaksi']);
    $id_user = $_SESSION['user']['id_user'];
    
    
    $query_tiket = mysqli_query($conn, "SELECT id_kategori, COUNT(*) as qty FROM e_tiket WHERE id_transaksi = '$id_transaksi' GROUP BY id_kategori");
    while ($tkt = mysqli_fetch_assoc($query_tiket)) {
        $id_kat = $tkt['id_kategori'];
        $qty = $tkt['qty'];
        mysqli_query($conn, "UPDATE kategori SET kuota_sisa = kuota_sisa + $qty WHERE id_kategori = '$id_kat'");
    }
    
    
    $status_to_set = 'batal'; 
    $check_enum = mysqli_query($conn, "SHOW COLUMNS FROM transaksi LIKE 'STATUS'");
    if ($check_enum && mysqli_num_rows($check_enum) > 0) {
        $row = mysqli_fetch_assoc($check_enum);
        $enum_type = $row['Type']; 
        
        if (strpos($enum_type, "'cancel'") !== false) {
            $status_to_set = 'cancel';
        } elseif (strpos($enum_type, "'CANCEL'") !== false) {
            $status_to_set = 'CANCEL';
        } elseif (strpos($enum_type, "'batal'") !== false) {
            $status_to_set = 'batal';
        } elseif (strpos($enum_type, "'BATAL'") !== false) {
            $status_to_set = 'BATAL';
        } else {
            
            preg_match_all("/'([^']+)'/", $enum_type, $matches);
            if (isset($matches[1][2])) {
                $status_to_set = $matches[1][2];
            }
        }
    }
    
    
    mysqli_query($conn, "UPDATE transaksi SET STATUS = '$status_to_set' WHERE id_transaksi = '$id_transaksi' AND id_user = '$id_user'");
}

header("Location: tiket_saya.php");
exit;
?>