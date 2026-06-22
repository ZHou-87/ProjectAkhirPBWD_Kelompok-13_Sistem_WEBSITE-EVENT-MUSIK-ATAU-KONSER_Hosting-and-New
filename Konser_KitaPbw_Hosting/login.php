<?php
session_start();
include 'koneksi.php';

$error_email = "";
$error_password = "";

if(isset($_POST['login'])){

    $email = $_POST['email'];
    $password = $_POST['password'];

    if(strlen($password) < 8){
        
        $error_password = "Password minimal harus terdiri dari 8 karakter.";
        
    } else {

        $stmt = mysqli_prepare($conn, "SELECT * FROM user WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($result);

        if($data){

            if(md5($password) === $data['PASSWORD']){

                $_SESSION['user'] = $data;

                $nama_log  = $data['nama'];
                $email_log = $data['email'];
                $role_log  = $data['ROLE'];

                $stmt_log = mysqli_prepare($conn, "INSERT INTO log_login (nama, email, role) VALUES (?, ?, ?)");
                mysqli_stmt_bind_param($stmt_log, "sss", $nama_log, $email_log, $role_log);
                mysqli_stmt_execute($stmt_log);
                mysqli_stmt_close($stmt_log);

                if ($data['ROLE'] === 'admin') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit;

            } else {

                $error_password = "Password yang Anda masukkan salah.";

            }

        } else {

            $error_email = "Email tidak terdaftar di sistem kami.";

        }

        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KonserKita</title>
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
            Silakan masuk untuk menjelajahi dan memesan tiket konser musik favoritmu.
        </p>
    </div>

    <form method="POST" class="space-y-5">

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-[#1A2E26]/60 mb-2">
                Alamat Email
            </label>
            <input type="email"
                   name="email"
                   required
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                   placeholder="nama@email.com"
                   class="w-full bg-[#FDFBF7] border border-[#EAE3D2] <?php echo !empty($error_email) ? 'border-rose-400 focus:border-rose-500' : 'border-[#EAE3D2] focus:border-[#D4A373]'; ?> rounded-xl px-4 py-3.5 text-sm focus:outline-none text-[#1A2E26] font-medium transition-all duration-300">
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
                    name="login"
                    class="w-full bg-[#1A2E26] hover:bg-[#1A2E26]/90 text-white py-4 rounded-xl font-bold text-sm tracking-wide transition-all duration-300 shadow-md shadow-[#1A2E26]/10">
                Masuk ke Akun
            </button>
        </div>

    </form>

    <div class="text-center mt-8 pt-6 border-t border-[#EAE3D2]/40 text-sm">
        <p class="text-[#1A2E26]/60 font-medium">
            Belum bergabung? 
            <a href="registrasi.php" class="text-[#D4A373] hover:text-[#C29262] font-bold transition-colors duration-300 ml-1">
                Daftar Sekarang
            </a>
        </p>
    </div>

</div>

</body>
</html>