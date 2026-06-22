<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id_kategori']) || !isset($_GET['jumlah'])) {
    header("Location: ../index.php");
    exit;
}

$id_kategori = $_GET['id_kategori'];
$jumlah = intval($_GET['jumlah']);

$query = mysqli_query($conn, "SELECT kategori.*, event.id_event, event.nama_event, event.tanggal, event.lokasi, event.poster 
                              FROM kategori 
                              JOIN event ON kategori.id_event = event.id_event 
                              WHERE kategori.id_kategori = '$id_kategori'");
$data = mysqli_fetch_assoc($query);

if (!$data || $jumlah > $data['kuota_sisa']) {
    header("Location: ../index.php");
    exit();
}

$id_user_login = $_SESSION['user']['id_user'];
$id_event_baca = $data['id_event'];

$cek_sudah_beli = mysqli_query($conn, "
    SELECT transaksi.id_transaksi 
    FROM transaksi 
    JOIN e_tiket ON transaksi.id_transaksi = e_tiket.id_transaksi
    JOIN kategori ON e_tiket.id_kategori = kategori.id_kategori
    WHERE transaksi.id_user = '$id_user_login' 
    AND kategori.id_event = '$id_event_baca'
    AND transaksi.status IN ('paid', 'pending')
");

if (mysqli_num_rows($cek_sudah_beli) > 0) {
    header("Location: ../index.php?pesan=sudah_beli");
    exit();
}

$total_bayar = $data['harga'] * $jumlah;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Checkout Tiket - KonserKita</title>
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

<nav class="bg-[#FDFBF7]/80 backdrop-blur-md border-b border-[#EAE3D2] px-4 md:px-6 py-4 flex justify-between items-center sticky top-0 z-50">
    <a href="../index.php" class="text-xl md:text-3xl font-extrabold tracking-tight text-[#1A2E26]">
        Konser<span class="text-[#D4A373]">Kita</span>
    </a>
    <div class="flex items-center gap-3 md:gap-6">
        <div class="bg-amber-50 border border-amber-200 px-3 md:px-4 py-1.5 md:py-2 rounded-xl flex items-center gap-1.5 md:gap-2 text-[11px] md:text-xs font-bold text-amber-700 shadow-sm">
            <i class="fa-solid fa-clock animate-pulse text-xs md:text-sm"></i>
            <span>Sisa Waktu: <span id="countdown-timer">15:00</span></span>
        </div>
        <a href="../logout.php" class="text-rose-600 hover:text-rose-700 font-semibold text-xs md:text-sm transition-all">Logout</a>
    </div>
</nav>

<main class="flex-1 max-w-5xl w-full mx-auto px-4 md:px-6 py-6 md:py-8">
    
    <div class="max-w-xl mx-auto mb-8 md:mb-10 px-2">
        <div class="flex items-center justify-between relative">
            <div class="absolute left-4 right-4 top-5 h-0.5 bg-gray-200 -translate-y-1/2 z-0"></div>
            <div id="line-progress" class="absolute left-4 top-5 h-0.5 bg-emerald-600 -translate-y-1/2 z-0 transition-all duration-500" style="width: 0%;"></div>

            <div class="z-10 flex flex-col items-center gap-1.5">
                <div id="step-circle-1" class="w-9 h-9 md:w-10 md:h-10 rounded-full bg-emerald-600 text-white font-black text-xs md:text-sm flex items-center justify-center border-4 border-[#FDFBF7] transition-all duration-300 shadow">1</div>
                <span id="step-text-1" class="text-[10px] md:text-xs font-black text-emerald-600">Isi Data</span>
            </div>
            <div class="z-10 flex flex-col items-center gap-1.5">
                <div id="step-circle-2" class="w-9 h-9 md:w-10 md:h-10 rounded-full bg-gray-200 text-gray-400 font-black text-xs md:text-sm flex items-center justify-center border-4 border-[#FDFBF7] transition-all duration-300 shadow">2</div>
                <span id="step-text-2" class="text-[10px] md:text-xs font-bold text-gray-400">Pembayaran</span>
            </div>
            <div class="z-10 flex flex-col items-center gap-1.5">
                <div id="step-circle-3" class="w-9 h-9 md:w-10 md:h-10 rounded-full bg-gray-200 text-gray-400 font-black text-xs md:text-sm flex items-center justify-center border-4 border-[#FDFBF7] transition-all duration-300 shadow">3</div>
                <span id="step-text-3" class="text-[10px] md:text-xs font-bold text-gray-400">Konfirmasi</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
        
        <div class="grid grid-cols-1 lg:col-span-7 order-2 lg:order-1">
            <form id="formCheckout" action="proses_bayar.php" method="POST" class="space-y-6">
                <input type="hidden" name="id_kategori" value="<?php echo $id_kategori; ?>">
                <input type="hidden" name="jumlah" value="<?php echo $jumlah; ?>">
                <input type="hidden" name="total_bayar" value="<?php echo $total_bayar; ?>">

                <div id="panel-tahap-1" class="bg-[#F7F4EB] border border-[#EAE3D2]/60 rounded-2xl md:rounded-3xl p-5 md:p-6 space-y-4">
                    <h2 class="text-lg md:text-xl font-black text-[#1A2E26] flex items-center gap-2"><i class="fa-solid fa-user text-[#D4A373]"></i> Data Pemesan</h2>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-[10px] md:text-xs font-bold text-[#1A2E26]/60 uppercase tracking-wider mb-1">Nama Lengkap Sesuai KTP</label>
                            <input type="text" name="nama_pemesan" required value="<?php echo htmlspecialchars($_SESSION['user']['nama']); ?>" class="w-full bg-[#FDFBF7] text-[#1A2E26] border border-[#EAE3D2] rounded-xl px-4 py-3 text-sm font-semibold focus:outline-none focus:border-[#D4A373]">
                        </div>
                        <div>
                            <label class="block text-[10px] md:text-xs font-bold text-[#1A2E26]/60 uppercase tracking-wider mb-1">Alamat Email Aktif</label>
                            <input type="email" name="email_pemesan" readonly value="<?php echo htmlspecialchars($_SESSION['user']['email']); ?>" class="w-full bg-gray-200/80 text-gray-400 border border-[#EAE3D2] rounded-xl px-4 py-3 text-sm font-semibold focus:outline-none cursor-not-allowed select-none">
                            <p class="text-[10px] text-gray-400 font-medium mt-1">*Data email dikunci otomatis demi keamanan unik akun.</p>
                        </div>
                        <div>
                            <label class="block text-[10px] md:text-xs font-bold text-[#1A2E26]/60 uppercase tracking-wider mb-1">Nomor WhatsApp</label>
                            <input type="tel" name="telepon_pemesan" required placeholder="Contoh: 08123456789" class="w-full bg-[#FDFBF7] border border-[#EAE3D2] rounded-xl px-4 py-3 text-sm font-semibold focus:outline-none focus:border-[#D4A373]">
                        </div>
                    </div>
                    <button type="button" onclick="pindahKeTahap(2)" class="w-full bg-[#1A2E26] text-white font-bold text-xs md:text-sm uppercase tracking-wider py-3.5 md:py-4 rounded-xl hover:bg-[#D4A373] hover:text-[#1A2E26] transition-all shadow-md">
                        Lanjut ke Pembayaran
                    </button>
                </div>

                <div id="panel-tahap-2" class="bg-[#F7F4EB] border border-[#EAE3D2]/60 rounded-2xl md:rounded-3xl p-5 md:p-6 space-y-4 hidden">
                    <h2 class="text-lg md:text-xl font-black text-[#1A2E26] flex items-center gap-2"><i class="fa-solid fa-credit-card text-[#D4A373]"></i> Metode Pembayaran</h2>
                    <div class="space-y-2.5">
                        <label class="border border-[#EAE3D2] bg-[#FDFBF7] p-3.5 md:p-4 rounded-xl flex items-center justify-between cursor-pointer hover:border-[#D4A373] transition-all block">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="metode_pembayaran" value="BCA" required class="w-4 h-4 accent-[#D4A373]">
                                <span class="text-xs md:text-sm font-bold text-[#1A2E26]">Bank BCA (Virtual Account)</span>
                            </div>
                        </label>
                        <label class="border border-[#EAE3D2] bg-[#FDFBF7] p-3.5 md:p-4 rounded-xl flex items-center justify-between cursor-pointer hover:border-[#D4A373] transition-all block">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="metode_pembayaran" value="Mandiri" class="w-4 h-4 accent-[#D4A373]">
                                <span class="text-xs md:text-sm font-bold text-[#1A2E26]">Bank Mandiri (Livin VA)</span>
                            </div>
                        </label>
                        <label class="border border-[#EAE3D2] bg-[#FDFBF7] p-3.5 md:p-4 rounded-xl flex items-center justify-between cursor-pointer hover:border-[#D4A373] transition-all block">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="metode_pembayaran" value="BNI" class="w-4 h-4 accent-[#D4A373]">
                                <span class="text-xs md:text-sm font-bold text-[#1A2E26]">Bank BNI (Virtual Account)</span>
                            </div>
                        </label>
                        <label class="border border-[#EAE3D2] bg-[#FDFBF7] p-3.5 md:p-4 rounded-xl flex items-center justify-between cursor-pointer hover:border-[#D4A373] transition-all flex gap-2">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="metode_pembayaran" value="QRIS" class="w-4 h-4 accent-[#D4A373]">
                                <span class="text-xs md:text-sm font-bold text-[#1A2E26]">QRIS (Gopay, Dana, OVO)</span>
                            </div>
                            <span class="text-[9px] font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded shrink-0">Otomatis</span>
                        </label>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" onclick="pindahKeTahap(1)" class="flex-1 border border-[#EAE3D2] bg-white font-bold text-xs uppercase py-3.5 rounded-xl text-gray-500">Kembali</button>
                        <button type="button" onclick="pindahKeTahap(3)" class="flex-1 bg-[#1A2E26] text-white font-bold text-xs uppercase py-3.5 rounded-xl hover:bg-[#D4A373]">Lanjut</button>
                    </div>
                </div>

                <div id="panel-tahap-3" class="bg-[#F7F4EB] border border-[#EAE3D2]/60 rounded-2xl md:rounded-3xl p-5 md:p-6 space-y-4 hidden">
                    <h2 class="text-lg md:text-xl font-black text-[#1A2E26] flex items-center gap-2"><i class="fa-solid fa-square-check text-[#D4A373]"></i> Konfirmasi Pembelian</h2>
                    
                    <div class="bg-[#FDFBF7] border border-[#EAE3D2] rounded-xl p-3.5 space-y-2 text-xs font-medium">
                        <div class="flex justify-between border-b border-gray-100 pb-2"><span class="text-gray-400">Nama Pemesan:</span><span id="review-nama" class="font-bold text-right ml-2"></span></div>
                        <div class="flex justify-between border-b border-gray-100 pb-2"><span class="text-gray-400">WhatsApp:</span><span id="review-telp" class="font-bold text-right ml-2"></span></div>
                        <div class="flex justify-between"><span class="text-gray-400">Metode Bayar:</span><span id="review-metode" class="font-bold text-[#D4A373] text-right ml-2"></span></div>
                    </div>

                    <div id="instruksi-pembayaran-langsung" class="bg-white border-2 border-dashed border-[#D4A373] rounded-xl p-4 text-center space-y-3">
                        <div id="box-va" class="hidden">
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Nomor Virtual Account Anda</p>
                            <p id="tampil-va" class="text-xl md:text-2xl font-mono font-black tracking-widest text-[#1A2E26] mt-1 bg-gray-50 py-2 rounded-xl border"></p>
                        </div>
                        <div id="box-qris" class="hidden space-y-2">
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Silakan Scan QRIS Berikut</p>
                            <div class="flex justify-center p-2 bg-white border rounded-xl max-w-[140px] mx-auto shadow-sm">
                                <img src="../assets/barcode.png" class="w-full h-auto" alt="QRIS Barcode">
                            </div>
                        </div>
                        <p class="text-[10px] text-amber-600 font-medium bg-amber-50 py-1.5 px-3 rounded-lg leading-relaxed"><i class="fa-solid fa-circle-info mr-1"></i> Anda bisa memilih bayar langsung sekarang atau simpan sebagai pending (Bayar Nanti).</p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-2 pt-1">
                        <button type="submit" name="aksi" value="bayar_sekarang" class="w-full sm:flex-1 bg-emerald-600 text-white font-bold text-xs uppercase tracking-wider py-3.5 rounded-xl hover:bg-emerald-700 transition-all">
                            Bayar Sekarang
                        </button>
                        <button type="submit" name="aksi" value="bayar_nanti" class="w-full sm:flex-1 bg-[#D4A373] text-white font-bold text-xs uppercase tracking-wider py-3.5 rounded-xl hover:bg-[#C29262] transition-all">
                            Bayar Nanti (Pending)
                        </button>
                        <a href="../index.php" class="w-full sm:w-20 bg-rose-50 border border-rose-200 text-center text-rose-600 font-bold text-xs uppercase tracking-wider py-3.5 rounded-xl hover:bg-rose-600 hover:text-white flex items-center justify-center">
                            Batal
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:col-span-5 order-1 lg:order-2">
            <div class="bg-[#F7F4EB] border border-[#EAE3D2]/60 rounded-2xl md:rounded-3xl p-5 md:p-6 space-y-4 lg:sticky lg:top-28 w-full">
                <h2 class="text-base md:text-lg font-black text-[#1A2E26]">Ringkasan Order</h2>
                <div class="flex gap-3 md:gap-4 border-b border-[#EAE3D2] pb-4">
                    <img src="../assets/<?php echo $data['poster']; ?>" class="w-14 h-16 md:w-16 md:h-20 object-cover rounded-xl border border-[#EAE3D2] shrink-0">
                    <div class="space-y-0.5 min-w-0">
                        <h3 class="text-xs md:text-sm font-bold text-[#1A2E26] truncate"><?php echo htmlspecialchars($data['nama_event']); ?></h3>
                        <p class="text-[11px] md:text-xs text-[#1A2E26]/60 truncate"><i class="fa-solid fa-tags text-[#D4A373] mr-1"></i> Kelas: <?php echo htmlspecialchars($data['nama_kategori']); ?></p>
                        <p class="text-[11px] md:text-xs text-[#1A2E26]/60"><i class="fa-solid fa-ticket text-[#D4A373] mr-1"></i> <?php echo $jumlah; ?> Tiket</p>
                    </div>
                </div>
                <div class="space-y-2 text-[11px] md:text-xs font-medium text-[#1A2E26]/70">
                    <div class="flex justify-between">
                        <span>Harga Satuan</span>
                        <span>Rp <?php echo number_format($data['harga'], 0, ',', '.'); ?></span>
                    </div>
                    <div class="flex justify-between font-black text-xs md:text-sm text-[#1A2E26] pt-2 border-t border-[#EAE3D2]/60">
                        <span>Total Pembayaran</span>
                        <span>Rp <?php echo number_format($total_bayar, 0, ',', '.'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// Logic timer countdown
let durasiWaktu = 15 * 60; 
const hitungMundur = setInterval(function () {
    let menit = Math.floor(durasiWaktu / 60);
    let detik = durasiWaktu % 60;

    detik = detik < 10 ? '0' + detik : detik;
    document.getElementById('countdown-timer').innerHTML = menit + ":" + detik;

    if (durasiWaktu <= 0) {
        clearInterval(hitungMundur);
        alert("Waktu pengerjaan checkout habis! Silakan lakukan order ulang tiket Anda.");
        window.location.href = "detail_event.php?id=<?php echo $data['id_event']; ?>";
    }
    durasiWaktu--;
}, 1000);

function resetWaktuProses() {
    durasiWaktu = 15 * 60;
}


function pindahKeTahap(tahap) {
    if (tahap === 2) {
        const nama = document.querySelector('input[name="nama_pemesan"]').value.trim();
        const email = document.querySelector('input[name="email_pemesan"]').value.trim();
        const telp = document.querySelector('input[name="telepon_pemesan"]').value.trim();
        if (!nama || !email || !telp) {
            alert("Harap lengkapi seluruh data pemesan sebelum melanjutkan!");
            return;
        }
    }

    if (tahap === 3) {
        const metodePilihan = document.querySelector('input[name="metode_pembayaran"]:checked');
        if (!metodePilihan) {
            alert("Silakan pilih salah satu metode pembayaran terlebih dahulu!");
            return;
        }
        
        document.getElementById('review-nama').innerText = document.querySelector('input[name="nama_pemesan"]').value;
        document.getElementById('review-telp').innerText = document.querySelector('input[name="telepon_pemesan"]').value;
        document.getElementById('review-metode').innerText = metodePilihan.value;

        const m = metodePilihan.value;
        const bVa = document.getElementById('box-va');
        const bQris = document.getElementById('box-qris');
        const tVa = document.getElementById('tampil-va');

        if (m === 'QRIS') {
            bQris.classList.remove('hidden');
            bVa.classList.add('hidden');
        } else {
            bVa.classList.remove('hidden');
            bQris.classList.add('hidden');
            
            if (m === 'BCA') {
                tVa.innerText = "880128" + Math.floor(100000 + Math.random() * 900000); 
            } else if (m === 'Mandiri') {
                tVa.innerText = "70012" + Math.floor(1000000 + Math.random() * 9000000);
            } else if (m === 'BNI') {
                tVa.innerText = "988123" + Math.floor(100000 + Math.random() * 900000);
            }
        }
    }

    resetWaktuProses();

    document.getElementById('panel-tahap-1').classList.add('hidden');
    document.getElementById('panel-tahap-2').classList.add('hidden');
    document.getElementById('panel-tahap-3').classList.add('hidden');
    document.getElementById('panel-tahap-' + tahap).classList.remove('hidden');

    const progressLine = document.getElementById('line-progress');
    if (tahap === 1) progressLine.style.width = "0%";
    if (tahap === 2) progressLine.style.width = "50%";
    if (tahap === 3) progressLine.style.width = "100%";

    for (let i = 1; i <= 3; i++) {
        const circle = document.getElementById('step-circle-' + i);
        const text = document.getElementById('step-text-' + i);
        
        if (i <= tahap) {
            circle.classList.remove('bg-gray-200', 'text-gray-400');
            circle.classList.add('bg-emerald-600', 'text-white');
            text.classList.remove('text-gray-400', 'font-bold');
            text.classList.add('text-emerald-600', 'font-black');
        } else {
            circle.classList.remove('bg-emerald-600', 'text-white');
            circle.classList.add('bg-gray-200', 'text-gray-400');
            text.classList.remove('text-emerald-600', 'font-black');
            text.classList.add('text-gray-400', 'font-bold');
        }
    }
    
   
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>
</body>
</html>