<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../koneksi.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ../index.php");
    exit();
}

$id_event = (int)$_GET['id'];

$query_event = mysqli_query($conn, "SELECT * FROM event WHERE id_event = '$id_event'");
$event = mysqli_fetch_assoc($query_event);

if (!$event) {
    header("Location: ../index.php?error=notfound");
    exit();
}

$query_kategori = mysqli_query($conn, "SELECT * FROM kategori WHERE id_event = '$id_event'");

$sudah_login = (isset($_SESSION['user']) && ($_SESSION['user']['ROLE'] === 'user' || $_SESSION['user']['ROLE'] === 'admin')) ? 'true' : 'false';

// PROSES DETEKSI FILE S&K GLOBAL SECARA DINAMIS
$file_sk = null;
$nama_file_sk = '';
// Mencari file yang berawalan syarat_ketentuan_konser dengan ekstensi apapun di folder assets
$search_sk = glob("../assets/syarat_ketentuan_konser.*");
if (!empty($search_sk)) {
    $file_sk = $search_sk[0]; // Ambil file pertama yang ditemukan
    $nama_file_sk = basename($file_sk);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Detail Event - <?php echo htmlspecialchars($event['nama_event']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght=400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .toast-active { transform: translate(-50%, 0) scale(1) !important; opacity: 1 !important; }
    </style>
</head>
<body class="bg-[#FDFBF7] text-[#1A2E26] antialiased select-none">

<div id="androidToast" class="fixed bottom-8 left-1/2 -translate-x-1/2 z-[100] bg-[#1A2E26] text-[#FDFBF7] px-5 py-3.5 rounded-2xl shadow-xl border border-[#EAE3D2]/20 flex items-center gap-3 w-[90%] max-w-sm transform scale-90 opacity-0 transition-all duration-300 pointer-events-none">
    <i class="fa-solid fa-circle-exclamation text-[#D4A373] text-lg"></i>
    <p id="toastMessage" class="text-xs font-semibold leading-snug"></p>
</div>

<nav class="bg-[#FDFBF7]/80 backdrop-blur-md border-b border-[#EAE3D2] px-4 md:px-6 py-4 flex justify-between items-center sticky top-0 z-50">
    <a href="../index.php" class="text-xl md:text-3xl font-extrabold tracking-tight text-[#1A2E26]">
        Konser<span class="text-[#D4A373]">Kita</span>
    </a>

    <?php if(isset($_SESSION['user'])) { ?>
        <div class="hidden md:flex items-center gap-8 text-sm font-bold uppercase tracking-wider">
            <a href="../index.php" class="text-[#1A2E26] hover:text-[#D4A373] transition-colors duration-300">Beranda</a>
            <a href="tiket_saya.php" class="text-[#1A2E26]/60 hover:text-[#D4A373] transition-colors duration-300">Tiket Saya</a>
            <a href="profil.php" class="text-[#1A2E26]/60 hover:text-[#D4A373] transition-colors duration-300">Profil Saya</a>
            <?php if($_SESSION['user']['ROLE'] === 'admin') { ?>
                <a href="../admin/dashboard.php" class="text-rose-600 hover:text-rose-700 transition-colors duration-300">Panel Admin</a>
            <?php } ?>
        </div>
    <?php } ?>

    <div class="flex items-center gap-3">
        <?php if(isset($_SESSION['user'])) { ?>
            <span class="text-[#1A2E26]/70 hidden md:inline font-medium text-sm">Halo, <strong><?php echo htmlspecialchars($_SESSION['user']['nama']); ?></strong></span>
            <a href="../logout.php" class="hidden md:inline text-rose-600 hover:text-rose-700 font-semibold text-sm transition-all duration-300">Logout</a>
            
            <button onclick="toggleMobileMenu()" class="md:hidden w-10 h-10 flex items-center justify-center text-[#1A2E26] hover:bg-[#F7F4EB] rounded-xl border border-[#EAE3D2] transition-colors">
                <i id="hamburgerIcon" class="fa-solid fa-bars text-lg"></i>
            </button>
        <?php } else { ?>
            <a href="../login.php" class="bg-[#D4A373] hover:bg-[#C29262] text-white px-4 md:px-5 py-2 rounded-xl font-bold text-sm transition-all duration-300 shadow-md">Masuk</a>
        <?php } ?>
    </div>
</nav>

<div id="mobileMenu" class="hidden fixed top-[73px] left-0 w-full bg-[#FDFBF7] border-b border-[#EAE3D2] shadow-lg z-40 md:hidden animate-fade-in">
    <div class="px-4 py-4 space-y-3 font-semibold text-sm">
        <div class="pb-2 border-b border-[#EAE3D2]/60 text-xs text-[#1A2E26]/50">
            Halo, <strong class="text-[#1A2E26]"><?php echo htmlspecialchars($_SESSION['user']['nama'] ?? ''); ?></strong>
        </div>
        <a href="../index.php" class="block py-2 text-[#1A2E26] hover:text-[#D4A373] transition-colors"><i class="fa-solid fa-house mr-2.5 text-[#D4A373]/70"></i>Beranda</a>
        <a href="tiket_saya.php" class="block py-2 text-[#1A2E26] hover:text-[#D4A373] transition-colors"><i class="fa-solid fa-ticket mr-2.5 text-[#D4A373]/70"></i>Tiket Saya</a>
        <a href="profil.php" class="block py-2 text-[#1A2E26] hover:text-[#D4A373] transition-colors"><i class="fa-solid fa-user mr-2.5 text-[#D4A373]/70"></i>Profil Saya</a>
        
        <?php if(isset($_SESSION['user']) && $_SESSION['user']['ROLE'] === 'admin') { ?>
            <a href="../admin/dashboard.php" class="block py-2 text-rose-600 hover:text-rose-700 transition-colors"><i class="fa-solid fa-gauge mr-2.5"></i>Panel Admin</a>
        <?php } ?>
        
        <div class="pt-2 border-t border-[#EAE3D2]/60">
            <a href="../logout.php" class="block py-2 text-rose-600 font-bold"><i class="fa-solid fa-arrow-right-from-bracket mr-2.5"></i>Logout</a>
        </div>
    </div>
</div>

<main class="max-w-6xl mx-auto px-4 md:px-6 py-6 md:py-10">
    <a href="../index.php" class="text-[#1A2E26]/60 hover:text-[#1A2E26] font-semibold flex items-center gap-2 mb-6 md:mb-8 transition-colors duration-300 text-sm">
        <i class="fa-solid fa-arrow-left text-[#D4A373]"></i> Kembali ke Katalog
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-10">
        <div class="lg:col-span-1">
            <div class="bg-[#F7F4EB] p-3 md:p-4 rounded-3xl border border-[#EAE3D2]/60 shadow-sm md:sticky md:top-28">
                <img src="../assets/<?php echo htmlspecialchars($event['poster']); ?>" alt="Poster <?php echo htmlspecialchars($event['nama_event']); ?>" class="w-full h-auto rounded-2xl object-cover shadow-sm max-h-[450px] lg:max-h-none">
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6 md:space-y-8">
            <div class="bg-[#F7F4EB] p-5 md:p-8 rounded-3xl border border-[#EAE3D2]/60 shadow-sm space-y-5 md:space-y-6">
                <div>
                    <span class="bg-[#EAE3D2] text-[#1A2E26] px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider border border-[#EAE3D2]">
                        <?php echo htmlspecialchars($event['genre']); ?>
                    </span>
                </div>
                
                <h1 class="text-2xl md:text-4xl font-extrabold tracking-tight text-[#1A2E26] leading-tight">
                    <?php echo htmlspecialchars($event['nama_event']); ?>
                </h1>
                
                <div class="w-full h-px bg-[#EAE3D2]"></div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 text-[#1A2E26]/80">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#FDFBF7] border border-[#EAE3D2] flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-calendar-days text-[#D4A373] text-base md:text-lg"></i>
                        </div>
                        <div>
                            <p class="text-[10px] md:text-xs text-[#1A2E26]/40 font-bold tracking-wider uppercase mb-0.5">Tanggal & Waktu</p>
                            <p class="font-bold text-sm md:text-base text-[#1A2E26]"><?php echo date('d F Y', strtotime($event['tanggal'])); ?></p>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#FDFBF7] border border-[#EAE3D2] flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-location-dot text-[#D4A373] text-base md:text-lg"></i>
                        </div>
                        <div>
                            <p class="text-[10px] md:text-xs text-[#1A2E26]/40 font-bold tracking-wider uppercase mb-0.5">Lokasi Venue</p>
                            <p class="font-bold text-sm md:text-base text-[#1A2E26]"><?php echo htmlspecialchars($event['lokasi']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="w-full h-px bg-[#EAE3D2]"></div>

                <div class="space-y-2.5">
                    <h3 class="text-base md:text-lg font-bold text-[#1A2E26]">Deskripsi Event</h3>
                    <p class="text-[#1A2E26]/70 text-xs md:text-sm leading-relaxed whitespace-pre-line"><?php echo nl2br(htmlspecialchars($event['deskripsi'])); ?></p>
                </div>
            </div>

            <div class="bg-[#F7F4EB] p-5 md:p-8 rounded-3xl border border-[#EAE3D2]/60 shadow-sm">
                <h2 class="text-xl md:text-2xl font-bold text-[#1A2E26] mb-4 md:mb-6">Pilih Kategori Tiket</h2>
                
                <form action="checkout.php" method="GET" onsubmit="return cekAksesBeli(event)" class="space-y-5 md:space-y-6">
                    <div class="space-y-3">
                        <?php 
                        $ada_tiket = false;
                        while ($kategori = mysqli_fetch_assoc($query_kategori)) { 
                            $ada_tiket = true;
                            $stok_habis = ($kategori['kuota_sisa'] <= 0);
                        ?>
                            <label class="border <?php echo $stok_habis ? 'border-[#EAE3D2]/40 bg-[#FDFBF7]/40 opacity-50 cursor-not-allowed' : 'border-[#EAE3D2] bg-[#FDFBF7] hover:border-[#D4A373] cursor-pointer'; ?> rounded-2xl p-4 md:p-5 flex justify-between items-center transition-all duration-300 relative group">
                                <div class="flex items-center gap-3 md:gap-4 flex-1 min-w-0">
                                    <input type="radio" name="id_kategori" value="<?php echo $kategori['id_kategori']; ?>" class="w-5 h-5 text-[#D4A373] border-[#EAE3D2] focus:ring-[#D4A373] shrink-0" <?php echo $stok_habis ? 'disabled' : 'required'; ?>>
                                    <div class="truncate">
                                        <p class="font-bold text-base md:text-lg text-[#1A2E26] truncate"><?php echo htmlspecialchars($kategori['nama_kategori']); ?></p>
                                        <p class="text-[11px] md:text-xs text-[#1A2E26]/50 mt-0.5">Sisa Kuota: <span class="font-bold <?php echo $kategori['kuota_sisa'] < 10 ? 'text-rose-600' : 'text-emerald-600'; ?>"><?php echo $kategori['kuota_sisa']; ?> Tiket</span></p>
                                    </div>
                                </div>
                                <div class="text-right shrink-0 ml-2">
                                    <?php if ($stok_habis) { ?>
                                        <span class="bg-rose-600 text-white text-[9px] md:text-[10px] px-2 py-0.5 md:py-1 rounded-md font-bold uppercase tracking-wider">Habis</span>
                                    <?php } else { ?>
                                        <p class="text-base md:text-xl font-black text-[#1A2E26] group-hover:text-[#D4A373] transition-colors duration-300">Rp <?php echo number_format($kategori['harga'], 0, ',', '.'); ?></p>
                                    <?php } ?>
                                </div>
                            </label>
                        <?php } ?>

                        <?php if (!$ada_tiket) { ?>
                            <div class="text-center py-6 text-[#1A2E26]/50 text-sm font-medium">
                                <i class="fa-solid fa-ticket-simple block text-2xl text-[#D4A373]/40 mb-2"></i>
                                Belum ada kategori tiket yang tersedia untuk event ini.
                            </div>
                        <?php } ?>
                    </div>

                    <?php if ($ada_tiket) { ?>
                        <input type="hidden" name="jumlah" value="1">
                        <div class="bg-[#FDFBF7] border border-[#EAE3D2] p-3.5 rounded-2xl flex items-start sm:items-center gap-2.5 text-xs md:text-sm text-[#1A2E26]/80 font-medium shadow-inner">
                            <i class="fa-solid fa-circle-info text-[#D4A373] text-sm md:text-base mt-0.5 sm:mt-0 shrink-0"></i>
                            <span>Sistem Anti-Calo: Pembelian dibatasi maksimal 1 tiket per akun.</span>
                        </div>

                        <?php
                        $query_cek_stok = mysqli_query($conn, "SELECT SUM(kuota_sisa) as total_stok FROM kategori WHERE id_event = '$id_event'");
                        $cek_stok = mysqli_fetch_assoc($query_cek_stok);
                        $semua_habis = ($cek_stok['total_stok'] <= 0);

                        if ($semua_habis) { ?>
                            <button type="button" class="w-full bg-gray-300 text-gray-500 py-3.5 md:py-4 rounded-2xl font-bold text-sm md:text-base cursor-not-allowed" disabled>
                                Maaf, Semua Kategori Tiket Telah Habis
                            </button>
                        <?php } else { ?>
                            <button type="submit" class="w-full bg-[#1A2E26] hover:bg-[#1A2E26]/90 text-white py-3.5 md:py-4 rounded-2xl font-bold text-sm md:text-base transition duration-300 shadow-md shadow-[#1A2E26]/10 tracking-wide mb-3">
                                Beli Tiket Sekarang
                            </button>
                        <?php } ?>

                        <!-- === TOMBOL LIHAT SYARAT & KETENTUAN (S&K) GLOBAL DI SINI === -->
                        <?php if ($file_sk): ?>
                            <div class="pt-2 text-center">
                                <a href="../assets/<?php echo $nama_file_sk; ?>" target="_blank" class="inline-flex items-center gap-2 text-xs font-bold text-[#D4A373] hover:text-[#C29262] underline underline-offset-4 transition-colors duration-200">
                                    <i class="fa-solid fa-file-shield text-sm"></i> Lihat Syarat & Ketentuan Pembelian Tiket
                                </a>
                            </div>
                        <?php endif; ?>
                        
                    <?php } ?>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    const icon = document.getElementById('hamburgerIcon');
    
    if (menu.classList.contains('hidden')) {
        menu.classList.remove('hidden');
        icon.classList.remove('fa-bars');
        icon.classList.add('fa-xmark');
    } else {
        menu.classList.add('hidden');
        icon.classList.remove('fa-xmark');
        icon.classList.add('fa-bars');
    }
}

function showAndroidToast(message, callback) {
    const toast = document.getElementById('androidToast');
    const msgContainer = document.getElementById('toastMessage');
    
    msgContainer.innerText = message;
    toast.classList.add('toast-active');
    
    setTimeout(() => {
        toast.classList.remove('toast-active');
        if(callback) {
            setTimeout(callback, 300);
        }
    }, 2500);
}

function cekAksesBeli(event) {
    const isLogin = <?php echo $sudah_login; ?>;
    
    if (!isLogin) {
        event.preventDefault();
        showAndroidToast("Silakan login terlebih dahulu untuk melakukan pembelian tiket.", () => {
            window.location.href = "../login.php";
        });
        return false;
    }
    return true;
}
</script>

</body>
</html>