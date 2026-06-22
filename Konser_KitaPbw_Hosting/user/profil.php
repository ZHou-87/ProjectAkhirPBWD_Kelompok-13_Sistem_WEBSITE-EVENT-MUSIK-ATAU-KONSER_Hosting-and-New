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
$query = mysqli_query($conn, "SELECT nama, email FROM user WHERE id_user = '$id_user'");
$user = mysqli_fetch_assoc($query);

if (!$user) {
    header("Location: ../logout.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - KonserKita</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght=400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#FDFBF7] text-[#1A2E26] selection:bg-[#D4A373] selection:text-white antialiased min-h-screen flex flex-col">

<nav class="bg-[#FDFBF7]/80 backdrop-blur-md border-b border-[#EAE3D2] px-6 py-4 flex justify-between items-center sticky top-0 z-50">
    <a href="../index.php" class="text-2xl md:text-3xl font-extrabold tracking-tight text-[#1A2E26]">
        Konser<span class="text-[#D4A373]">Kita</span>
    </a>
    
    <div class="hidden md:flex items-center gap-8 text-sm font-bold uppercase tracking-wider">
        <a href="../index.php" class="text-[#1A2E26]/60 hover:text-[#D4A373] transition-colors duration-300">Beranda</a>
        <a href="tiket_saya.php" class="text-[#1A2E26]/60 hover:text-[#D4A373] transition-colors duration-300">Tiket Saya</a>
        <a href="profil.php" class="text-[#D4A373] transition-colors duration-300">Profil Saya</a>
        <?php if($_SESSION['user']['ROLE'] === 'admin') { ?>
            <a href="../admin/dashboard.php" class="text-rose-600 hover:text-rose-700 transition-colors duration-300">Panel Admin</a>
        <?php } ?>
    </div>
    
    <div class="flex items-center gap-4">
        <div class="hidden md:flex items-center gap-4">
            <span class="text-[#1A2E26]/70 font-medium text-sm">Halo, <strong><?php echo htmlspecialchars($_SESSION['user']['nama']); ?></strong></span>
            <a href="../logout.php" class="text-rose-600 hover:text-rose-700 font-semibold text-sm transition-all duration-300">Logout</a>
        </div>

        <button id="hamburgerBtn" onclick="toggleMobileMenu()" class="block md:hidden text-2xl text-[#1A2E26] focus:outline-none p-1.5 hover:bg-[#F7F4EB] rounded-lg transition-all" aria-label="Toggle Menu">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>
</nav>

<div id="mobileMenu" class="hidden fixed top-[69px] left-0 w-full bg-[#FDFBF7] border-b border-[#EAE3D2] shadow-xl z-40 transition-all duration-300 flex-col px-6 py-6 gap-5">
    <div class="border-b border-[#EAE3D2] pb-3 mb-1">
        <p class="text-xs text-[#1A2E26]/50">Akun Login</p>
        <p class="text-base font-bold text-[#1A2E26] mt-0.5"><?php echo htmlspecialchars($_SESSION['user']['nama']); ?></p>
    </div>
    <a href="../index.php" class="text-sm font-bold uppercase tracking-wider text-[#1A2E26]/70 hover:text-[#D4A373]">Beranda</a>
    <a href="tiket_saya.php" class="text-sm font-bold uppercase tracking-wider text-[#1A2E26]/70 hover:text-[#D4A373]">Tiket Saya</a>
    <a href="profil.php" class="text-sm font-bold uppercase tracking-wider text-[#D4A373]">Profil Saya</a>
    <?php if($_SESSION['user']['ROLE'] === 'admin') { ?>
        <a href="../admin/dashboard.php" class="text-sm font-bold uppercase tracking-wider text-rose-600 hover:text-rose-700">Panel Admin</a>
    <?php } ?>
    <div class="border-t border-[#EAE3D2] pt-4 mt-2">
        <a href="../logout.php" class="w-full text-center block bg-rose-50 text-rose-600 border border-rose-200 py-3 rounded-xl font-bold text-sm">Logout / Keluar</a>
    </div>
</div>

<main class="flex-1 flex flex-col justify-center items-center px-6 py-12">
    <div class="w-full max-w-md mx-auto space-y-6">
        <a href="tiket_saya.php" class="inline-flex items-center text-xs font-bold text-[#1A2E26]/50 hover:text-[#1A2E26] transition-colors duration-300">
            <i class="fa-solid fa-arrow-left text-[#D4A373] mr-1.5"></i> Kembali ke Tiket Saya
        </a>

        <div class="bg-[#F7F4EB] rounded-3xl border border-[#EAE3D2]/60 shadow-sm p-6 space-y-6">
            <div class="text-center space-y-2">
                <div class="w-20 h-20 bg-[#D4A373]/10 border border-[#D4A373]/30 rounded-full flex items-center justify-center mx-auto text-[#D4A373] text-3xl">
                    <i class="fa-solid fa-user-astronaut"></i>
                </div>
                <h1 class="text-xl font-black tracking-tight text-[#1A2E26]">Pengaturan Profil</h1>
                <p class="text-xs text-[#1A2E26]/50">Kelola informasi kredensial akun Anda</p>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-[#1A2E26]/50 mb-1">Alamat Email</label>
                    <div class="w-full bg-[#EAE3D2]/40 border border-[#EAE3D2] px-4 py-3 rounded-xl text-xs font-semibold text-[#1A2E26]/50 flex items-center gap-3 select-none overflow-x-auto">
                        <i class="fa-solid fa-envelope text-[#1A2E26]/30 shrink-0"></i>
                        <span class="truncate"><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-[#1A2E26]/50 mb-1">Username Saat Ini</label>
                    <div class="w-full bg-[#FDFBF7] border border-[#EAE3D2] px-4 py-3 rounded-xl text-xs font-bold text-[#1A2E26] flex items-center gap-3">
                        <i class="fa-solid fa-id-card text-[#1A2E26]/40 shrink-0"></i>
                        <span class="truncate"><?php echo htmlspecialchars($user['nama']); ?></span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                <button type="button" onclick="bukaModal('modalUsername')" class="w-full bg-[#1A2E26] text-[#FDFBF7] text-[11px] font-extrabold uppercase tracking-wider py-3.5 rounded-xl hover:bg-[#D4A373] hover:text-[#1A2E26] transition-all duration-300 shadow-sm flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-user-pen text-xs"></i> Ubah Username
                </button>
                <button type="button" onclick="bukaModal('modalPassword')" class="w-full bg-[#1A2E26] text-[#FDFBF7] text-[11px] font-extrabold uppercase tracking-wider py-3.5 rounded-xl hover:bg-[#D4A373] hover:text-[#1A2E26] transition-all duration-300 shadow-sm flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-key text-xs"></i> Ubah Password
                </button>
            </div>

            <div class="pt-2">
                <hr class="border-[#EAE3D2] mb-4">
                <button type="button" onclick="bukaModal('modalHapusAkun')" class="w-full bg-rose-50 border border-rose-200/60 text-rose-600 text-xs font-bold py-3 rounded-xl hover:bg-rose-600 hover:text-white transition-all duration-300 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-trash-can text-xs"></i> Hapus Akun Saya
                </button>
            </div>
        </div>
    </div>
</main>

<footer class="bg-[#1A2E26] text-[#FDFBF7]/80 border-t border-[#EAE3D2]/20 mt-auto">
    <div class="max-w-7xl mx-auto px-6 py-12">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-10 pb-8 border-b border-[#FDFBF7]/10">
            
            <div class="md:col-span-5 space-y-4">
                <h1 class="text-3xl font-extrabold tracking-tight text-white">
                    Konser<span class="text-[#D4A373]">Kita</span>
                </h1>
                <p class="text-sm text-[#FDFBF7]/60 leading-relaxed max-w-sm">
                    Platform tepercaya dan aman untuk mengamankan momen konser terbaikmu. Temukan tiket impianmu dengan mudah di sini.
                </p>
            </div>

            <div class="md:col-span-4 space-y-4">
                <h2 class="text-xs font-bold uppercase tracking-widest text-[#D4A373]">Tautan Cepat</h2>
                <ul class="space-y-2.5 text-sm">
                    <li>
                        <a href="../index.php" class="hover:text-white transition-colors duration-300 flex items-center gap-2">
                            <i class="fa-solid fa-chevron-right text-[10px] opacity-50"></i> Beranda
                        </a>
                    </li>
                    <li>
                        <a href="tiket_saya.php" class="hover:text-white transition-colors duration-300 flex items-center gap-2">
                            <i class="fa-solid fa-chevron-right text-[10px] opacity-50"></i> Tiket Saya
                        </a>
                    </li>
                    <li>
                        <?php if($_SESSION['user']['ROLE'] === 'admin') { ?>
                            <a href="../admin/dashboard.php" class="text-rose-400 hover:text-rose-300 font-bold transition-colors duration-300 flex items-center gap-2">
                                <i class="fa-solid fa-gauge text-[10px]"></i> Panel Admin (Dashboard)
                            </a>
                        <?php } else { ?>
                            <a href="tiket_saya.php" class="hover:text-white transition-colors duration-300 flex items-center gap-2">
                                <i class="fa-solid fa-chevron-right text-[10px] opacity-50"></i> Dashboard Saya
                            </a>
                        <?php } ?>
                    </li>
                </ul>
            </div>

            <div class="md:col-span-3 space-y-4">
                <h2 class="text-xs font-bold uppercase tracking-widest text-[#D4A373]">Ikuti Kami</h2>
                <p class="text-sm text-[#FDFBF7]/60 hidden md:block">Dapatkan info konser terbaru setiap hari.</p>
                <div class="flex gap-3 pt-1">
                    <a href="#" class="w-10 h-10 rounded-xl bg-[#FDFBF7]/5 flex items-center justify-center text-[#FDFBF7]/80 hover:text-white hover:bg-[#D4A373] hover:-translate-y-0.5 transition-all duration-300" aria-label="Instagram">
                        <i class="fab fa-instagram text-base"></i>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-xl bg-[#FDFBF7]/5 flex items-center justify-center text-[#FDFBF7]/80 hover:text-white hover:bg-[#D4A373] hover:-translate-y-0.5 transition-all duration-300" aria-label="TikTok">
                        <i class="fab fa-tiktok text-base"></i>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-xl bg-[#FDFBF7]/5 flex items-center justify-center text-[#FDFBF7]/80 hover:text-white hover:bg-[#D4A373] hover:-translate-y-0.5 transition-all duration-300" aria-label="YouTube">
                        <i class="fab fa-youtube text-base"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="pt-8 flex flex-col sm:flex-row justify-between items-center gap-4 text-xs text-[#FDFBF7]/40 font-medium">
            <div>
                &copy; <?php echo date("Y"); ?> KonserKita. Hak Cipta Dilindungi Undang-Undang.
            </div>
            <div class="flex gap-6">
                <a href="#" class="hover:text-[#FDFBF7]/70 transition-colors">Kebijakan Privasi</a>
                <a href="#" class="hover:text-[#FDFBF7]/70 transition-colors">Bantuan / CS</a>
            </div>
        </div>
    </div>
</footer>

<div id="modalUsername" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden opacity-0 transition-opacity duration-300">
    <div class="bg-[#FDFBF7] border border-[#EAE3D2] max-w-sm w-full rounded-2xl p-6 shadow-2xl scale-95 transform transition-transform duration-300 space-y-4">
        <div class="text-center space-y-1">
            <h3 class="text-base font-black text-[#1A2E26]">Ubah Username Baru</h3>
            <p class="text-xs text-[#1A2E26]/60 leading-relaxed">Silakan masukkan nama baru dan ketik password saat ini.</p>
        </div>
        <form action="proses_profil.php" method="POST" class="space-y-3.5">
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-[#1A2E26]/50 mb-1">Username Baru</label>
                <input type="text" name="nama_baru" required placeholder="Masukkan nama baru" class="w-full px-4 py-2.5 text-xs font-bold rounded-xl border border-[#EAE3D2] bg-[#FDFBF7] focus:outline-none focus:border-[#D4A373]">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-[#1A2E26]/50 mb-1">Password Saat Ini</label>
                <input type="password" name="password_konfirmasi" required placeholder="••••••••" class="w-full px-4 py-2.5 text-xs font-mono rounded-xl border border-[#EAE3D2] bg-[#FDFBF7] focus:outline-none focus:border-[#D4A373]">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="tutupModal('modalUsername')" class="flex-1 border border-[#EAE3D2] text-[#1A2E26]/60 text-xs font-bold py-2.5 rounded-xl hover:bg-[#F7F4EB]">Batal</button>
                <button type="submit" name="ubah_username" class="flex-1 bg-[#1A2E26] text-[#FDFBF7] text-xs font-extrabold uppercase py-2.5 rounded-xl hover:bg-[#D4A373] hover:text-[#1A2E26]">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="modalPassword" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden opacity-0 transition-opacity duration-300">
    <div class="bg-[#FDFBF7] border border-[#EAE3D2] max-w-sm w-full rounded-2xl p-6 shadow-2xl scale-95 transform transition-transform duration-300 space-y-4">
        <div class="text-center space-y-1">
            <h3 class="text-base font-black text-[#1A2E26]">Ubah Password Keamanan</h3>
            <p class="text-xs text-[#1A2E26]/60 leading-relaxed">Masukkan password lama Anda diikuti dengan password baru.</p>
        </div>
        <form action="proses_profil.php" method="POST" class="space-y-3.5">
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-[#1A2E26]/50 mb-1">Password Lama</label>
                <input type="password" name="password_lama" required placeholder="••••••••" class="w-full px-4 py-2.5 text-xs font-mono rounded-xl border border-[#EAE3D2] bg-[#FDFBF7] focus:outline-none focus:border-[#D4A373]">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-[#1A2E26]/50 mb-1">Password Baru</label>
                <input type="password" name="password_baru" required placeholder="••••••••" class="w-full px-4 py-2.5 text-xs font-mono rounded-xl border border-[#EAE3D2] bg-[#FDFBF7] focus:outline-none focus:border-[#D4A373]">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="tutupModal('modalPassword')" class="flex-1 border border-[#EAE3D2] text-[#1A2E26]/60 text-xs font-bold py-2.5 rounded-xl hover:bg-[#F7F4EB]">Batal</button>
                <button type="submit" name="ubah_password" class="flex-1 bg-[#1A2E26] text-[#FDFBF7] text-xs font-extrabold uppercase py-2.5 rounded-xl hover:bg-[#D4A373] hover:text-[#1A2E26]">Perbarui</button>
            </div>
        </form>
    </div>
</div>

<div id="modalHapusAkun" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden opacity-0 transition-opacity duration-300">
    <div class="bg-[#FDFBF7] border border-[#EAE3D2] max-w-sm w-full rounded-2xl p-6 shadow-2xl scale-95 transform transition-transform duration-300 space-y-4 text-center">
        <div class="w-14 h-14 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center mx-auto text-xl border border-rose-100">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div class="space-y-1">
            <h3 class="text-base font-black text-[#1A2E26]">Apakah kamu yakin?</h3>
            <p class="text-xs text-[#1A2E26]/60 leading-relaxed">Tindakan ini permanen. Seluruh riwayat transaksi serta berkas e-tiket kamu akan terhapus dari sistem KonserKita.</p>
        </div>
        <div class="flex gap-3 pt-2">
            <button type="button" onclick="tutupModal('modalHapusAkun')" class="flex-1 border border-[#EAE3D2] text-[#1A2E26]/60 text-xs font-bold py-3 rounded-xl hover:bg-[#F7F4EB]">Batal</button>
            <form action="proses_profil.php" method="POST" class="flex-1">
                <button type="submit" name="hapus_akun" class="w-full bg-rose-600 text-white text-xs font-extrabold uppercase tracking-wider py-3 rounded-xl hover:bg-rose-700 shadow-md transition-all">Ya, Hapus</button>
            </form>
        </div>
    </div>
</div>

<script>

function toggleMobileMenu() {
    const mobileMenu = document.getElementById('mobileMenu');
    const hamburgerIcon = document.getElementById('hamburgerBtn').querySelector('i');
    
    if (mobileMenu.classList.contains('hidden')) {
        mobileMenu.classList.remove('hidden');
        mobileMenu.classList.add('flex');
        hamburgerIcon.classList.remove('fa-bars');
        hamburgerIcon.classList.add('fa-xmark');
    } else {
        mobileMenu.classList.remove('flex');
        mobileMenu.classList.add('hidden');
        hamburgerIcon.classList.remove('fa-xmark');
        hamburgerIcon.classList.add('fa-bars');
    }
}


window.addEventListener('resize', () => {
    if (window.innerWidth >= 768) {
        const mobileMenu = document.getElementById('mobileMenu');
        const hamburgerIcon = document.getElementById('hamburgerBtn').querySelector('i');
        mobileMenu.classList.remove('flex');
        mobileMenu.classList.add('hidden');
        hamburgerIcon.classList.remove('fa-xmark');
        hamburgerIcon.classList.add('fa-bars');
    }
});


function bukaModal(idModal) {
    const modal = document.getElementById(idModal);
    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        modal.querySelector('div').classList.remove('scale-95');
    }, 10);
}

function tutupModal(idModal) {
    const modal = document.getElementById(idModal);
    modal.classList.add('opacity-0');
    modal.querySelector('div').classList.add('scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
        const forms = modal.querySelectorAll('input');
        forms.forEach(input => { if(input.type !== 'submit') input.value = ''; });
    }, 300);
}
</script>

</body>
</html>