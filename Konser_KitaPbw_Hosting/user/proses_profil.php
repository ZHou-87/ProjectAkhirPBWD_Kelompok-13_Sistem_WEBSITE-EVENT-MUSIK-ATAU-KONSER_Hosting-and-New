<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['user']['id_user'];

if (isset($_POST['ubah_username'])) {
    $nama_baru = mysqli_real_escape_string($conn, $_POST['nama_baru']);
    $password_konfirmasi = $_POST['password_konfirmasi'];

    $query_cek = mysqli_query($conn, "SELECT password FROM user WHERE id_user = '$id_user'");
    $data_user = mysqli_fetch_assoc($query_cek);

    if (md5($password_konfirmasi) !== $data_user['password']) {
        echo "<script>
                alert('Verifikasi Gagal: Password konfirmasi yang Anda masukkan salah!');
                window.location.href = 'profil.php';
              </script>";
        exit;
    }

    $query_update = "UPDATE user SET nama = '$nama_baru' WHERE id_user = '$id_user'";
    if (mysqli_query($conn, $query_update)) {
        $_SESSION['user']['nama'] = $nama_baru;
        echo "<script>
                alert('Username Anda berhasil diperbarui!');
                window.location.href = 'profil.php';
              </script>";
    } else {
        echo "<script>
                alert('Gagal memperbarui username. Terjadi kesalahan sistem.');
                window.location.href = 'profil.php';
              </script>";
    }
    exit;
}

if (isset($_POST['ubah_password'])) {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];

    if (strlen($password_baru) < 8) {
        echo "<script>
                alert('Password baru minimal harus terdiri dari 8 karakter.');
                window.location.href = 'profil.php';
              </script>";
        exit;
    }

    $query_cek = mysqli_query($conn, "SELECT password FROM user WHERE id_user = '$id_user'");
    $data_user = mysqli_fetch_assoc($query_cek);

    if (md5($password_lama) !== $data_user['password']) {
        echo "<script>
                alert('Verifikasi Gagal: Password lama Anda salah!');
                window.location.href = 'profil.php';
              </script>";
        exit;
    }

    $password_baru_md5 = md5($password_baru);
    $query_update = "UPDATE user SET password = '$password_baru_md5' WHERE id_user = '$id_user'";
    
    if (mysqli_query($conn, $query_update)) {
        echo "<script>
                alert('Password Anda berhasil diperbarui!');
                window.location.href = 'profil.php';
              </script>";
    } else {
        echo "<script>
                alert('Gagal memperbarui password. Terjadi kesalahan sistem.');
                window.location.href = 'profil.php';
              </script>";
    }
    exit;
}

if (isset($_POST['hapus_akun'])) {
    $query_transaksi = mysqli_query($conn, "SELECT id_transaksi FROM transaksi WHERE id_user = '$id_user'");
    
    while ($transaksi = mysqli_fetch_assoc($query_transaksi)) {
        $id_transaksi = $transaksi['id_transaksi'];
        mysqli_query($conn, "DELETE FROM e_tiket WHERE id_transaksi = '$id_transaksi'");
    }
    
    mysqli_query($conn, "DELETE FROM transaksi WHERE id_user = '$id_user'");
    
    $query_delete_user = mysqli_query($conn, "DELETE FROM user WHERE id_user = '$id_user'");

    if ($query_delete_user) {
        session_unset();
        session_destroy();
        echo "<script>
                alert('Akun Anda telah berhasil dihapus permanen dari sistem.');
                window.location.href = '../index.php';
              </script>";
    } else {
        echo "<script>
                alert('Gagal menghapus akun. Terjadi kesalahan sistem.');
                window.location.href = 'profil.php';
              </script>";
    }
    exit;
}

header("Location: profil.php");
exit;
?>