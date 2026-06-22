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

$query_tiket = mysqli_query($conn, "
    SELECT 
        transaksi.id_transaksi,
        transaksi.id_user,
        transaksi.total_bayar,
        transaksi.nomor_va,
        transaksi.STATUS,
        transaksi.tanggal_daftar,
        event.nama_event, 
        event.tanggal, 
        event.lokasi, 
        event.poster,
        COUNT(e_tiket.id_tiket) AS jumlah_tiket
    FROM transaksi 
    JOIN e_tiket ON transaksi.id_transaksi = e_tiket.id_transaksi
    JOIN kategori ON e_tiket.id_kategori = kategori.id_kategori
    JOIN event ON kategori.id_event = event.id_event 
    WHERE transaksi.id_user = '$id_user' 
    GROUP BY 
        transaksi.id_transaksi,
        transaksi.id_user,
        transaksi.total_bayar,
        transaksi.nomor_va,
        transaksi.STATUS,
        transaksi.tanggal_daftar,
        event.nama_event, 
        event.tanggal, 
        event.lokasi, 
        event.poster
    ORDER BY transaksi.tanggal_daftar DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiket Saya - KonserKita</title>
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
        <a href="tiket_saya.php" class="text-[#D4A373] transition-colors duration-300">Tiket Saya</a>
        <a href="profil.php" class="text-[#1A2E26]/60 hover:text-[#D4A373] transition-colors duration-300">Profil Saya</a>
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
    <a href="tiket_saya.php" class="text-sm font-bold uppercase tracking-wider text-[#D4A373]">Tiket Saya</a>
    <a href="profil.php" class="text-sm font-bold uppercase tracking-wider text-[#1A2E26]/70 hover:text-[#D4A373]">Profil Saya</a>
    <?php if($_SESSION['user']['ROLE'] === 'admin') { ?>
        <a href="../admin/dashboard.php" class="text-sm font-bold uppercase tracking-wider text-rose-600 hover:text-rose-700">Panel Admin</a>
    <?php } ?>
    <div class="border-t border-[#EAE3D2] pt-4 mt-2">
        <a href="../logout.php" class="w-full text-center block bg-rose-50 text-rose-600 border border-rose-200 py-3 rounded-xl font-bold text-sm">Logout / Keluar</a>
    </div>
</div>

<main class="max-w-4xl w-full mx-auto px-6 py-12 flex-1">
    <div class="mb-8">
        <h1 class="text-3xl font-black tracking-tight text-[#1A2E26]">Tiket Saya</h1>
        <p class="text-sm text-[#1A2E26]/60 mt-1">Daftar seluruh e-tiket transaksi pembelian konser musik kamu.</p>
    </div>

    <div class="space-y-6">
        <?php if(mysqli_num_rows($query_tiket) > 0) { ?>
            <?php while($tiket = mysqli_fetch_assoc($query_tiket)) { 
                $status = isset($tiket['STATUS']) ? strtolower($tiket['STATUS']) : '';
                if ($status == 'paid') {
                    $badge_class = "bg-emerald-100 text-emerald-800 border-emerald-200";
                    $status_text = "Success";
                } elseif ($status == 'pending') {
                    $badge_class = "bg-amber-100 text-amber-800 border-amber-200";
                    $status_text = "Menunggu Pembayaran";
                } else {
                    $badge_class = "bg-rose-100 text-rose-800 border-rose-200";
                    $status_text = "Gagal / Batal";
                }
            ?>
                <div class="bg-[#F7F4EB] rounded-2xl border border-[#EAE3D2]/60 p-5 flex flex-col sm:flex-row items-center gap-5 shadow-sm hover:shadow-md transition-all duration-300">
                    <img src="../assets/<?php echo htmlspecialchars($tiket['poster']); ?>" alt="Poster" class="w-full sm:w-24 h-48 sm:h-24 object-cover rounded-xl border border-[#EAE3D2]">
                    
                    <div class="flex-grow space-y-2 text-center sm:text-left w-full">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <span class="text-xs text-[#1A2E26]/50 font-mono tracking-wider">INV/<?php echo $tiket['id_transaksi']; ?>/<?php echo date('Ymd', strtotime($tiket['tanggal_daftar'])); ?></span>
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border inline-block mx-auto sm:mx-0 <?php echo $badge_class; ?>">
                                <?php echo $status_text; ?>
                            </span>
                        </div>
                        <h2 class="text-lg font-extrabold text-[#1A2E26] leading-tight line-clamp-2 sm:line-clamp-1"><?php echo htmlspecialchars($tiket['nama_event']); ?></h2>
                        
                        <div class="flex flex-col sm:flex-row sm:flex-wrap justify-center sm:justify-start gap-y-1.5 sm:gap-x-4 text-xs text-[#1A2E26]/60 font-medium">
                            <p class="flex items-center justify-center sm:justify-start gap-1"><i class="fa-regular fa-calendar-days text-[#D4A373] w-4"></i> <?php echo date('d M Y', strtotime($tiket['tanggal'])); ?></p>
                            <p class="flex items-center justify-center sm:justify-start gap-1"><i class="fa-solid fa-location-dot text-[#D4A373] w-4"></i> <?php echo htmlspecialchars($tiket['lokasi']); ?></p>
                            <p class="flex items-center justify-center sm:justify-start gap-1"><i class="fa-solid fa-ticket text-[#D4A373] w-4"></i> <?php echo $tiket['jumlah_tiket']; ?> Tiket</p>
                        </div>
                    </div>

                    <div class="w-full sm:w-auto text-center pt-2 sm:pt-0">
                        <a href="detail_tiket_saya.php?id=<?php echo $tiket['id_transaksi']; ?>" class="w-full sm:w-auto inline-block text-center bg-[#1A2E26] hover:bg-[#1A2E26]/90 text-white px-5 py-3 rounded-xl font-bold text-xs uppercase tracking-wider transition-colors duration-300 whitespace-nowrap shadow-sm">
                            Lihat Detail Tiket
                        </a>
                    </div>
                </div>
            <?php } ?>
        <?php } else { ?>
            <div class="text-center py-20 border-2 border-dashed border-[#EAE3D2] rounded-3xl px-4">
                <i class="fa-solid fa-ticket text-5xl text-[#D4A373]/30 mb-4"></i>
                <h3 class="text-xl font-bold text-[#1A2E26]">Belum Ada Tiket</h3>
                <p class="text-sm text-[#1A2E26]/50 mt-1 max-w-sm mx-auto mb-6">Kamu belum melakukan pembelian tiket konser musik apa pun saat ini.</p>
                <a href="../index.php" class="bg-[#D4A373] hover:bg-[#C29262] text-white px-6 py-3 rounded-xl font-bold text-sm transition-all duration-300">Cari Konser Musik</a>
            </div>
        <?php } ?>
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
</script>
</body>
</html>