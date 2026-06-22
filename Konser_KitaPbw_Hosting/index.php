<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';

if (isset($_SESSION['user']) && $_SESSION['user']['ROLE'] === 'admin') {
    header("Location: admin/dashboard.php");
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$id_user_login = isset($_SESSION['user']['id_user']) ? (int)$_SESSION['user']['id_user'] : 0;

$query_string = "
    SELECT * FROM event 
    WHERE id_event NOT IN (
        SELECT DISTINCT kategori.id_event 
        FROM transaksi
        JOIN e_tiket ON transaksi.id_transaksi = e_tiket.id_transaksi
        JOIN kategori ON e_tiket.id_kategori = kategori.id_kategori
        WHERE transaksi.id_user = ? 
        AND transaksi.status IN ('paid', 'pending')
    )
    LIMIT 6
";

$stmt = mysqli_prepare($conn, $query_string);
mysqli_stmt_bind_param($stmt, "i", $id_user_login);
mysqli_stmt_execute($stmt);
$query = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KonserKita - Platform Tiket Concert</title>
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
    <a href="index.php" class="text-2xl md:text-3xl font-extrabold tracking-tight text-[#1A2E26]">
        Konser<span class="text-[#D4A373]">Kita</span>
    </a>

    <?php if(isset($_SESSION['user'])) { ?>
        <div class="hidden md:flex items-center gap-8 text-sm font-bold uppercase tracking-wider">
            <a href="index.php" class="text-[#D4A373] transition-colors duration-300">Beranda</a>
            <a href="user/tiket_saya.php" class="text-[#1A2E26]/60 hover:text-[#D4A373] transition-colors duration-300">Tiket Saya</a>
            <a href="user/profil.php" class="text-[#1A2E26]/60 hover:text-[#D4A373] transition-colors duration-300">Profil Saya</a>
            <?php if($_SESSION['user']['ROLE'] === 'admin') { ?>
                <a href="admin/dashboard.php" class="text-rose-600 hover:text-rose-700 transition-colors duration-300">Panel Admin</a>
            <?php } ?>
        </div>
    <?php } ?>

    <div class="flex items-center gap-4">
        <div class="hidden md:flex items-center gap-4">
            <?php if(isset($_SESSION['user'])) { ?>
                <span class="text-[#1A2E26]/70 font-medium text-sm">Halo, <strong><?php echo htmlspecialchars($_SESSION['user']['nama']); ?></strong></span>
                <a href="logout.php" class="text-rose-600 hover:text-rose-700 font-semibold text-sm transition-all duration-300">Logout</a>
            <?php } else { ?>
                <a href="login.php" class="text-[#1A2E26]/70 hover:text-[#1A2E26] px-4 py-2.5 font-semibold text-sm transition-all duration-300">Masuk</a>
                <a href="registrasi.php" class="bg-[#D4A373] hover:bg-[#C29262] text-white px-5 py-2.5 rounded-xl font-bold text-sm transition-all duration-300 shadow-md shadow-[#D4A373]/10">Daftar</a>
            <?php } ?>
        </div>

        <button id="hamburgerBtn" onclick="toggleMobileMenu()" class="block md:hidden text-2xl text-[#1A2E26] focus:outline-none p-1.5 hover:bg-[#F7F4EB] rounded-lg transition-all" aria-label="Toggle Menu">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>
</nav>

<div id="mobileMenu" class="hidden fixed top-[69px] left-0 w-full bg-[#FDFBF7] border-b border-[#EAE3D2] shadow-xl z-40 transition-all duration-300 flex-col px-6 py-6 gap-5">
    <?php if(isset($_SESSION['user'])) { ?>
        <div class="border-b border-[#EAE3D2] pb-3 mb-1">
            <p class="text-xs text-[#1A2E26]/50">Akun Login</p>
            <p class="text-base font-bold text-[#1A2E26] mt-0.5"><?php echo htmlspecialchars($_SESSION['user']['nama']); ?></p>
        </div>
        <a href="index.php" class="text-sm font-bold uppercase tracking-wider text-[#D4A373]">Beranda</a>
        <a href="user/tiket_saya.php" class="text-sm font-bold uppercase tracking-wider text-[#1A2E26]/70 hover:text-[#D4A373]">Tiket Saya</a>
        <a href="user/profil.php" class="text-sm font-bold uppercase tracking-wider text-[#1A2E26]/70 hover:text-[#D4A373]">Profil Saya</a>
        <?php if($_SESSION['user']['ROLE'] === 'admin') { ?>
            <a href="admin/dashboard.php" class="text-sm font-bold uppercase tracking-wider text-rose-600 hover:text-rose-700">Panel Admin</a>
        <?php } ?>
        <div class="border-t border-[#EAE3D2] pt-4 mt-2">
            <a href="logout.php" class="w-full text-center block bg-rose-50 text-rose-600 border border-rose-200 py-3 rounded-xl font-bold text-sm">Logout / Keluar</a>
        </div>
    <?php } else { ?>
        <a href="login.php" class="w-full text-center block bg-[#F7F4EB] text-[#1A2E26] border border-[#EAE3D2] py-3 rounded-xl font-bold text-sm">Masuk ke Akun</a>
        <a href="registrasi.php" class="w-full text-center block bg-[#D4A373] text-white py-3 rounded-xl font-bold text-sm shadow-md shadow-[#D4A373]/10">Daftar Sekarang</a>
    <?php } ?>
</div>

<main class="flex-1">
    <section class="max-w-7xl mx-auto p-6">
        <div class="relative overflow-hidden rounded-3xl border border-[#EAE3D2]/50 shadow-md bg-[#F7F4EB]">
            <div id="carouselWrapper" class="flex transition-transform duration-700 ease-out w-full h-[240px] md:h-[420px]">
                <div class="w-full h-full flex-shrink-0 relative">
                    <img src="assets/uzi.png" class="w-full h-full object-top" alt="Slide 1">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#1A2E26]/50 via-transparent to-transparent"></div>
                </div>
                <div class="w-full h-full flex-shrink-0 relative">
                    <img src="assets/fot2.png" class="w-full h-full object-top" alt="Slide 2">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#1A2E26]/50 via-transparent to-transparent"></div>
                </div>
                <div class="w-full h-full flex-shrink-0 relative">
                    <img src="assets/fot3.png" class="w-full h-full object-top" alt="Slide 3">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#1A2E26]/50 via-transparent to-transparent"></div>
                </div>
                <div class="w-full h-full flex-shrink-0 relative">
                    <img src="assets/fot4.png" class="w-full h-full object-top" alt="Slide 4">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#1A2E26]/50 via-transparent to-transparent"></div>
                </div>
                <div class="w-full h-full flex-shrink-0 relative">
                    <img src="assets/fot5.png" class="w-full h-full object-top" alt="Slide 5">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#1A2E26]/50 via-transparent to-transparent"></div>
                </div>
                <div class="w-full h-full flex-shrink-0 relative">
                    <img src="assets/fot1.png" class="w-full h-full object-top" alt="Slide 6">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#1A2E26]/50 via-transparent to-transparent"></div>
                </div>
            </div>

            <div class="absolute bottom-5 left-1/2 -translate-x-1/2 flex gap-2 z-10">
                <button onclick="goToSlide(0)" class="carousel-dot w-2.5 h-2.5 rounded-full bg-white/50 transition-all duration-300" aria-label="Slide 1"></button>
                <button onclick="goToSlide(1)" class="carousel-dot w-2.5 h-2.5 rounded-full bg-white/50 transition-all duration-300" aria-label="Slide 2"></button>
                <button onclick="goToSlide(2)" class="carousel-dot w-2.5 h-2.5 rounded-full bg-white/50 transition-all duration-300" aria-label="Slide 3"></button>
                <button onclick="goToSlide(3)" class="carousel-dot w-2.5 h-2.5 rounded-full bg-white/50 transition-all duration-300" aria-label="Slide 4"></button>
                <button onclick="goToSlide(4)" class="carousel-dot w-2.5 h-2.5 rounded-full bg-white/50 transition-all duration-300" aria-label="Slide 5"></button>
                <button onclick="goToSlide(5)" class="carousel-dot w-2.5 h-2.5 rounded-full bg-white/50 transition-all duration-300" aria-label="Slide 6"></button>
            </div>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-6 flex justify-between items-center my-8">
        <div>
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-[#1A2E26]">Jelajahi Event</h1>
            <p class="text-sm text-[#1A2E26]/60 mt-1 hidden md:block">Temukan pertunjukan musik terbaik dan amankan tiketmu.</p>
        </div>
        <button onclick="openFilter()" class="bg-[#F7F4EB] hover:bg-[#EAE3D2] text-[#1A2E26] border border-[#EAE3D2] px-5 py-3 rounded-xl flex items-center gap-2.5 font-semibold text-sm transition-all duration-300">
            <i class="fa-solid fa-sliders text-[#D4A373]"></i> Filter Event
        </button>
    </section>

    <div id="filterPopup" class="fixed inset-0 bg-[#1A2E26]/40 backdrop-blur-sm hidden justify-center items-center z-50 p-4 transition-all duration-300">
        <div class="bg-[#FDFBF7] border border-[#EAE3D2] p-6 rounded-3xl w-full max-w-md shadow-xl max-h-[85vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-[#1A2E26]">Filter Event</h2>
                <button onclick="closeFilter()" class="w-8 h-8 flex items-center justify-center rounded-full bg-[#F7F4EB] text-[#1A2E26]/60 hover:bg-[#EAE3D2] hover:text-[#1A2E26] transition-all">×</button>
            </div>
            <div>
                <label class="text-sm font-semibold text-[#1A2E26]/80 block mb-2">Genre Musik</label>
                <select id="filterGenre" class="w-full border border-[#EAE3D2] rounded-xl p-3 mb-5 bg-[#F7F4EB] text-[#1A2E26] focus:outline-none focus:border-[#D4A373] transition-all text-sm">
                    <option value="">Semua Genre</option>
                    <option value="Pop">Pop</option>
                    <option value="Rock">Rock</option>
                    <option value="Metal">Metal</option>
                    <option value="Jazz">Jazz</option>
                    <option value="EDM">EDM</option>
                </select>
                
                <label class="text-sm font-semibold text-[#1A2E26]/80 block mb-2">Bulan Pelaksanaan</label>
                <select id="filterBulan" class="w-full border border-[#EAE3D2] rounded-xl p-3 mb-6 bg-[#F7F4EB] text-[#1A2E26] focus:outline-none focus:border-[#D4A373] transition-all text-sm">
                    <option value="">Semua Bulan</option>
                    <option value="1">Januari</option>
                    <option value="2">Februari</option>
                    <option value="3">Maret</option>
                    <option value="4">April</option>
                    <option value="5">Mei</option>
                    <option value="6">Juni</option>
                    <option value="7">Juli</option>
                    <option value="8">Agustus</option>
                    <option value="9">September</option>
                    <option value="10">Oktober</option>
                    <option value="11">November</option>
                    <option value="12">Desember</option>
                </select>
                
                <button onclick="applyFilter()" class="w-full bg-[#D4A373] hover:bg-[#C29262] text-white py-3.5 rounded-xl font-bold text-sm transition-all duration-300 shadow-lg shadow-[#D4A373]/20">Terapkan Filter</button>
            </div>
        </div>
    </div>

    <section class="max-w-7xl mx-auto px-6 pb-24">
        <div id="eventContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php 
            if (mysqli_num_rows($query) > 0) {
                while($event = mysqli_fetch_assoc($query)) { 
                    $id_event = $event['id_event'];
                    $id_event_clean = (int)$id_event;
                    $query_stok = mysqli_query($conn, "SELECT SUM(kuota_sisa) as total_stok FROM kategori WHERE id_event = $id_event_clean");
                    $stok_data = mysqli_fetch_assoc($query_stok);
                    $is_sold_out = ($stok_data['total_stok'] <= 0);
                ?>
                <div class="bg-[#F7F4EB] rounded-2xl overflow-hidden border border-[#EAE3D2]/60 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex flex-col h-full group">
                    <div class="relative overflow-hidden">
                        <img src="assets/<?php echo htmlspecialchars($event['poster'], ENT_QUOTES, 'UTF-8'); ?>" class="w-full h-[240px] object-cover object-bottom  group-hover:scale-105 duration-500 <?php echo $is_sold_out ? 'grayscale brightness-75' : ''; ?>" alt="Poster Event">
                        <?php if ($is_sold_out) { ?>
                            <div class="absolute top-3 right-3 bg-rose-600 text-white text-xs font-bold px-3 py-1.5 rounded-full border border-rose-700 shadow-sm">
                                Sold Out
                            </div>
                        <?php } else { ?>
                            <div class="absolute top-3 right-3 bg-gradient-to-r bg-[#FDFBF7]/90 backdrop-blur-md text-[#D4A373] text-xs font-bold px-3 py-1.5 rounded-full border border-[#EAE3D2]">
                                Available
                            </div>
                        <?php } ?>
                    </div>
                    <div class="p-5 flex flex-col flex-grow">
                        <h2 class="text-xl font-bold text-[#1A2E26] mb-4 line-clamp-1 group-hover:text-[#D4A373] transition-colors duration-300"><?php echo htmlspecialchars($event['nama_event'], ENT_QUOTES, 'UTF-8'); ?></h2>
                        <div class="space-y-2.5 mb-6 text-sm text-[#1A2E26]/70">
                            <p class="flex items-center gap-2.5"><i class="fa-regular fa-calendar-days text-[#D4A373] text-base w-4"></i> <?php echo date('d F Y', strtotime($event['tanggal'])); ?></p>
                            <p class="flex items-center gap-2.5"><i class="fa-solid fa-location-dot text-[#D4A373] text-base w-4"></i> <?php echo htmlspecialchars($event['lokasi'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <div class="mt-auto">
                            <?php if ($is_sold_out) { ?>
                                <a href="user/detail_event.php?id=<?php echo (int)$event['id_event']; ?>" class="w-full text-center bg-rose-600 hover:bg-rose-700 text-white px-4 py-3 rounded-xl font-bold text-sm inline-block transition-all duration-300">Tiket Habis (Detail)</a>
                            <?php } else { ?>
                                <a href="user/detail_event.php?id=<?php echo (int)$event['id_event']; ?>" class="w-full text-center bg-[#1A2E26] hover:bg-[#1A2E26]/90 text-white px-4 py-3 rounded-xl font-bold text-sm inline-block transition-all duration-300">Lihat Detail</a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <?php 
                }
            } else { ?>
                <div class="col-span-full text-center py-12 text-gray-400 font-medium text-sm">
                    Tidak ada konser baru yang tersedia untuk dibeli saat ini.
                </div>
            <?php } ?>
        </div>

        <div class="text-center mt-16">
            <button
                id="btnLoadMore"
                onclick="toggleEvents()"
                class="bg-[#F7F4EB] hover:bg-[#EAE3D2] text-[#1A2E26] border border-[#EAE3D2] px-8 py-3.5 rounded-xl font-bold text-sm transition-all duration-300">
                Tampilkan Lebih Banyak
            </button>
        </div>
    </section>
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
                        <a href="index.php" class="hover:text-white transition-colors duration-300 flex items-center gap-2">
                            <i class="fa-solid fa-chevron-right text-[10px] opacity-50"></i> Beranda
                        </a>
                    </li>
                    <?php if(isset($_SESSION['user'])) { ?>
                        <li>
                            <a href="user/tiket_saya.php" class="hover:text-white transition-colors duration-300 flex items-center gap-2">
                                <i class="fa-solid fa-chevron-right text-[10px] opacity-50"></i> Tiket Saya
                            </a>
                        </li>
                        <li>
                            <?php if($_SESSION['user']['ROLE'] === 'admin') { ?>
                                <a href="admin/dashboard.php" class="text-rose-400 hover:text-rose-300 font-bold transition-colors duration-300 flex items-center gap-2">
                                    <i class="fa-solid fa-gauge text-[10px]"></i> Panel Admin (Dashboard)
                                </a>
                            <?php } else { ?>
                                <a href="user/tiket_saya.php" class="hover:text-white transition-colors duration-300 flex items-center gap-2">
                                    <i class="fa-solid fa-chevron-right text-[10px] opacity-50"></i> Dashboard Saya
                                </a>
                            <?php } ?>
                        </li>
                    <?php } else { ?>
                        <li>
                            <a href="#eventContainer" class="hover:text-white transition-colors duration-300 flex items-center gap-2">
                                <i class="fa-solid fa-chevron-right text-[10px] opacity-50"></i> Jelajahi Event
                            </a>
                        </li>
                    <?php } ?>
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

function openFilter(){
    document.getElementById('filterPopup').classList.remove('hidden');
    document.getElementById('filterPopup').classList.add('flex');
}
function closeFilter(){
    document.getElementById('filterPopup').classList.remove('flex');
    document.getElementById('filterPopup').classList.add('hidden');
}
let limit = 6;
let isAllShowed = false;
let currentGenre = '';
let currentBulan = '';

function applyFilter() {
    currentGenre = document.getElementById('filterGenre').value;
    currentBulan = document.getElementById('filterBulan').value;
    limit = 6; 
    isAllShowed = false;
    fetchData();
    closeFilter();
}
function toggleEvents() {
    const btn = document.getElementById('btnLoadMore');
    if (isAllShowed) {
        limit = 6;
        isAllShowed = false;
        btn.innerText = "Tampilkan Lebih Banyak";
        fetchData();
    } else {
        limit += 6;
        fetchData();
    }
}
function fetchData() {
    const btn = document.getElementById('btnLoadMore');
    fetch(`load_more.php?limit=${limit}&genre=${encodeURIComponent(currentGenre)}&bulan=${encodeURIComponent(currentBulan)}`)
    .then(response => response.json())
    .then(data => {
        document.getElementById('eventContainer').innerHTML = data.html;
        if (data.is_all_loaded) {
            isAllShowed = true;
            btn.innerText = "Tampilkan Lebih Sedikit";
        } else {
            isAllShowed = false;
            btn.innerText = "Tampilkan Lebih Banyak";
        }
    })
    .catch(err => console.error("Gagal memuat data:", err));
}
let currentSlide = 0;
const totalSlides = 6;
const carouselWrapper = document.getElementById('carouselWrapper');
const dots = document.querySelectorAll('.carousel-dot');

function updateCarousel() {
    carouselWrapper.style.transform = `translateX(-${currentSlide * 100}%)`;
    dots.forEach((dot, index) => {
        if (index === currentSlide) {
            dot.classList.remove('bg-white/50', 'w-2.5');
            dot.classList.add('bg-[#D4A373]', 'w-6');
        } else {
            dot.classList.remove('bg-[#D4A373]', 'w-6');
            dot.classList.add('bg-white/50', 'w-2.5');
        }
    });
}
function nextSlide() {
    currentSlide = (currentSlide + 1) % totalSlides;
    updateCarousel();
}
function goToSlide(slideIndex) {
    currentSlide = slideIndex;
    updateCarousel();
    resetTimer();
}
let autoSlideTimer = setInterval(nextSlide, 4000);
function resetTimer() {
    clearInterval(autoSlideTimer);
    autoSlideTimer = setInterval(nextSlide, 4000);
}
document.addEventListener("DOMContentLoaded", () => {
    updateCarousel();
});
</script>
</body>
</html>