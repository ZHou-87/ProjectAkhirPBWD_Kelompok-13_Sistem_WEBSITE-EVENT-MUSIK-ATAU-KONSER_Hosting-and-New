<?php
session_start();
include 'koneksi.php';

$error_password = "";
$error_email = "";

if(isset($_POST['register'])){

    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if(strlen($password) < 8){

        $error_password = "Pendaftaran gagal. Password wajib minimal 8 karakter.";

    } else {

        $stmt_cek = mysqli_prepare($conn, "SELECT * FROM user WHERE email = ?");
        mysqli_stmt_bind_param($stmt_cek, "s", $email);
        mysqli_stmt_execute($stmt_cek);
        $result_cek = mysqli_stmt_get_result($stmt_cek);

        if(mysqli_num_rows($result_cek) > 0){

            $error_email = "Email sudah terdaftar di sistem kami.";

        } else {

            $password_md5 = md5($password);

            $stmt_insert = mysqli_prepare($conn, "INSERT INTO user (nama, email, PASSWORD, ROLE) VALUES (?, ?, ?, 'user')");
            mysqli_stmt_bind_param($stmt_insert, "sss", $nama, $email, $password_md5);
            
            if(mysqli_stmt_execute($stmt_insert)){
                header("Location: login.php");
                exit;
            } else {
                $error_password = "Terjadi kesalahan sistem, silakan coba lagi.";
            }
            
            mysqli_stmt_close($stmt_insert);
        }

        mysqli_stmt_close($stmt_cek);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - KonserKita</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#FDFBF7] min-h-screen flex items-center justify-center p-6 text-[#1A2E26] antialiased">

<div class="bg-[#F7F4EB] w-full max-w-md rounded-3xl p-8 border border-[#EAE3D2]/60 shadow-md">
    
    <div class="mb-8">
        <a href="index.php" class="inline-block text-xs font-bold text-[#1A2E26]/50 hover:text-[#1A2E26] mb-6 transition-colors duration-300">
            <i class="fa-solid fa-arrow-left text-[#D4A373] mr-1"></i> Kembali ke Beranda
        </a>
        <h1 class="text-3xl font-black tracking-tight text-[#1A2E26] mb-1.5">
            Konser<span class="text-[#D4A373]">Kita</span>
        </h1>
        <p class="text-sm text-[#1A2E26]/60">
            Buat akun baru untuk mulai menjelajahi konser musik impianmu.
        </p>
    </div>

    <form method="POST" class="space-y-5">

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-[#1A2E26]/60 mb-2">
                Username
            </label>
            <input type="text"
                   name="nama"
                   required
                   value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>"
                   placeholder="Masukkan username Anda"
                   class="w-full bg-[#FDFBF7] border border-[#EAE3D2] focus:border-[#D4A373] rounded-xl px-4 py-3.5 text-sm focus:outline-none text-[#1A2E26] font-medium transition-all duration-300">
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-[#1A2E26]/60 mb-2">
                Alamat Email
            </label>
            <input type="email"
                   name="email"
                   required
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                   placeholder="nama@email.com"
                   class="w-full bg-[#FDFBF7] border <?php echo !empty($error_email) ? 'border-rose-400 focus:border-rose-500' : 'border-[#EAE3D2] focus:border-[#D4A373]'; ?> rounded-xl px-4 py-3.5 text-sm focus:outline-none text-[#1A2E26] font-medium transition-all duration-300">
            <?php if(!empty($error_email)) { ?>
                <p class="text-rose-600 text-xs font-semibold mt-1.5 flex items-center gap-1.5 animate-pulse">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error_email; ?>
                </p>
            <?php } ?>
        </div>

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-[#1A2E26]/60 mb-2">
                Kata Sandi
            </label>
            <input type="password"
                   name="password"
                   required
                   minlength="8"
                   class="w-full bg-[#FDFBF7] border <?php echo !empty($error_password) ? 'border-rose-400 focus:border-rose-500' : 'border-[#EAE3D2] focus:border-[#D4A373]'; ?> rounded-xl px-4 py-3.5 text-sm focus:outline-none text-[#1A2E26] font-medium transition-all duration-300">
            <?php if(!empty($error_password)) { ?>
                <p class="text-rose-600 text-xs font-semibold mt-1.5 flex items-center gap-1.5 animate-pulse">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error_password; ?>
                </p>
            <?php } ?>
        </div>

        <div class="pt-2">
            <button type="submit"
                    name="register"
                    class="w-full bg-[#1A2E26] hover:bg-[#1A2E26]/90 text-white py-4 rounded-xl font-bold text-sm tracking-wide transition-all duration-300 shadow-md shadow-[#1A2E26]/10">
                Daftar Akun Baru
            </button>
        </div>

    </form>

    <div class="text-center mt-8 pt-6 border-t border-[#EAE3D2]/40 text-sm">
        <p class="text-[#1A2E26]/60 font-medium">
            Sudah memiliki akun? 
            <a href="login.php" class="text-[#D4A373] hover:text-[#C29262] font-bold transition-colors duration-300 ml-1">
                Masuk Di Sini
            </a>
        </p>
    </div>

</div>

</body>
</html>