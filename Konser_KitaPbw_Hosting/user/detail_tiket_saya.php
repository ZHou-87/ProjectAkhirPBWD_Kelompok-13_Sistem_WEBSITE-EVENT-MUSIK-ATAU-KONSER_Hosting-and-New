<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    include '../koneksi.php';

    if (!isset($_SESSION['user'])) {
        header("Location: ../login.php");
        exit();
    }

    $id_transaksi = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $id_user_login = (int)$_SESSION['user']['id_user'];

    $query_string = "
        SELECT 
            t.id_transaksi, t.tanggal_daftar, t.total_bayar, t.status, t.nomor_va,
            u.nama AS nama_pembeli, u.email AS email_pembeli,
            e.nama_event, e.tanggal AS tanggal_event, e.lokasi, e.poster,
            k.nama_kategori, k.harga,
            et.id_tiket
        FROM transaksi t
        JOIN user u ON t.id_user = u.id_user
        JOIN e_tiket et ON t.id_transaksi = et.id_transaksi
        JOIN kategori k ON et.id_kategori = k.id_kategori
        JOIN event e ON k.id_event = e.id_event
        WHERE t.id_transaksi = ? AND t.id_user = ?
    ";

    $stmt = mysqli_prepare($conn, $query_string);
    mysqli_stmt_bind_param($stmt, "ii", $id_transaksi, $id_user_login);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 0) {
        echo "Transaksi tidak ditemukan atau Anda tidak memiliki akses.";
        exit();
    }

    $data = mysqli_fetch_assoc($result);
    $kode_tiket_unik = "ETK-" . str_pad($data['id_tiket'], 5, '0', STR_PAD_LEFT);
    ?>

    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Detail Tiket Resmi - KonserKita</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght=400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <style>
            body { font-family: 'Plus Jakarta Sans', sans-serif; }
            @media print {
                body { background: white; color: black; padding: 0; }
                .no-print { display: none !important; }
                .print-card { border: 2px dashed #1A2E26; box-shadow: none; border-radius: 0; }
            }
        </style>
    </head>
    <body class="bg-[#FDFBF7] text-[#1A2E26] antialiased p-4 md:p-8">

    <div class="max-w-3xl mx-auto space-y-6">
        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 no-print">
            <a href="../index.php" class="text-sm font-bold text-[#1A2E26]/60 hover:text-[#D4A373] transition-colors py-1">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
            </a>
            
            <?php if (strtolower($data['status']) === 'paid') { ?>
                <button onclick="window.print()" class="w-full sm:w-auto bg-[#D4A373] hover:bg-[#C29262] text-white px-5 py-2.5 rounded-xl font-bold text-sm transition-all shadow-md flex items-center justify-center gap-2">
                    <i class="fa-solid fa-print"></i> Cetak Tiket & Struk
                </button>
            <?php } ?>
        </div>

        <div class="bg-white border border-[#EAE3D2] rounded-3xl shadow-sm overflow-hidden print-card">
            
            <div class="bg-[#1A2E26] text-white p-6 flex flex-col sm:flex-row justify-between gap-4 items-start sm:items-center">
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight">Konser<span class="text-[#D4A373]">Kita</span></h1>
                    <p class="text-xs text-white/60 mt-0.5">
                        <?php echo (strtolower($data['status']) === 'paid') ? 'E-Tiket Resmi & Bukti Pembayaran' : 'Invoice Pembayaran Tiket'; ?>
                    </p>
                </div>
                <div class="text-left sm:text-right">
                    <p class="text-xs text-white/60 uppercase font-bold tracking-wider">Kode Unik Transaksi</p>
                    <p class="text-lg font-mono font-bold text-[#D4A373]"><?php echo $kode_tiket_unik; ?></p>
                </div>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 border-b border-[#EAE3D2]/60">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#1A2E26]/40 mb-2">Detail Pembeli</h3>
                    <p class="text-sm font-bold text-[#1A2E26]"><?php echo htmlspecialchars($data['nama_pembeli'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="text-xs text-[#1A2E26]/60"><?php echo htmlspecialchars($data['email_pembeli'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="text-xs text-[#1A2E26]/60 mt-3">Waktu Transaksi: <span class="font-medium text-[#1A2E26]"><?php echo date('d M Y, H:i', strtotime($data['tanggal_daftar'])); ?> WIB</span></p>
                </div>
                <div class="flex flex-col md:items-end justify-between gap-3">
                    <div class="md:text-right">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#1A2E26]/40 mb-2">Status Tiket</h3>
                        <?php if (strtolower($data['status']) === 'paid') { ?>
                            <span class="inline-block bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1 rounded-full border border-emerald-200">Lunas / Terverifikasi</span>
                        <?php } elseif (strtolower($data['status']) === 'pending') { ?>
                            <span class="inline-block bg-amber-100 text-amber-800 text-xs font-bold px-3 py-1 rounded-full border border-amber-200">Menunggu Pembayaran</span>
                        <?php } else { ?>
                            <span class="inline-block bg-rose-100 text-rose-800 text-xs font-bold px-3 py-1 rounded-full border border-rose-200">Gagal / Batal</span>
                        <?php } ?>
                    </div>
                    <p class="text-xs text-[#1A2E26]/60">Metode: <span class="font-bold uppercase"><?php echo htmlspecialchars($data['nomor_va'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                </div>
            </div>

            <div class="p-6 border-b border-[#EAE3D2]/60 bg-[#FDFBF7]/50">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#1A2E26]/40 mb-4">Item Tiket yang Dibeli</h3>
                <div class="flex flex-col sm:flex-row gap-4 items-start">
                    <img src="../assets/<?php echo htmlspecialchars($data['poster'], ENT_QUOTES, 'UTF-8'); ?>" class="w-20 h-28 object-cover rounded-xl border border-[#EAE3D2] self-center sm:self-start shrink-0" alt="Poster">
                    <div class="space-y-1 flex-1 w-full">
                        <h2 class="text-lg font-bold text-[#1A2E26]"><?php echo htmlspecialchars($data['nama_event'], ENT_QUOTES, 'UTF-8'); ?></h2>
                        <p class="text-xs text-[#1A2E26]/70 flex items-center gap-1.5"><i class="fa-regular fa-calendar text-[#D4A373]"></i> <?php echo date('d F Y', strtotime($data['tanggal_event'])); ?></p>
                        <p class="text-xs text-[#1A2E26]/70 flex items-center gap-1.5"><i class="fa-solid fa-location-dot text-[#D4A373]"></i> <?php echo htmlspecialchars($data['lokasi'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <div class="pt-2">
                            <span class="inline-block bg-[#1A2E26] text-white text-[11px] font-extrabold px-2.5 py-1 rounded-md uppercase tracking-wide">Kategori: <?php echo htmlspecialchars($data['nama_kategori'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-[#F7F4EB]/30 grid grid-cols-1 sm:grid-cols-2 gap-6 items-center border-b border-[#EAE3D2]/60">
                
                <?php if (strtolower($data['status']) === 'paid') { ?>
                    <div class="text-center sm:text-left">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#1A2E26]/40 mb-1">Kode E-Tiket Anda</h3>
                        <p class="text-xl font-mono font-extrabold tracking-widest text-[#1A2E26]"><?php echo $kode_tiket_unik; ?></p>
                        <p class="text-[10px] text-[#1A2E26]/50 mt-1">*Bawa halaman ini atau cetak sebagai akses masuk/penukaran wristband di lokasi.</p>
                    </div>
                    <div class="flex justify-center sm:justify-end">
                        <div class="p-2 bg-white border border-[#EAE3D2] rounded-2xl inline-block shadow-sm">
                            <img src="../assets/barcode.png" class="w-28 h-28 object-contain" alt="Barcode Masuk">
                        </div>
                    </div>

                <?php } elseif (strtolower($data['status']) === 'pending') { ?>
                    <div class="no-print text-center sm:text-left">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#1A2E26]/40 mb-1">Selesaikan Pembayaran</h3>
                        <p class="text-sm text-[#1A2E26]/70 leading-relaxed">Klik tombol bayar untuk memunculkan instruksi Virtual Account atau QRIS.</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2 sm:justify-end no-print w-full">
                        <button onclick="bayarModal('<?php echo $data['id_transaksi']; ?>', '<?php echo $data['nomor_va']; ?>')" class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-xl font-bold text-xs uppercase tracking-wider transition-colors shadow-sm flex items-center justify-center">
                            <i class="fa-solid fa-credit-card mr-1.5"></i> Bayar Sekarang
                        </button>
                        <a href="proses_batal.php?id_transaksi=<?php echo $data['id_transaksi']; ?>" onclick="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')" class="w-full sm:w-auto bg-rose-600 hover:bg-rose-700 text-white px-6 py-3 rounded-xl font-bold text-xs uppercase tracking-wider transition-colors shadow-sm text-center flex items-center justify-center">
                            <i class="fa-solid fa-trash mr-1.5"></i> Batalkan
                        </a>
                    </div>
                    
                    <div class="hidden print:block col-span-2">
                        <p class="text-xs font-bold text-amber-600">STATUS: MENUNGGU PEMBAYARAN (<?php echo htmlspecialchars($data['nomor_va']); ?>)</p>
                    </div>

                <?php } else { ?>
                    <div class="col-span-2 py-2 text-center">
                        <p class="text-sm font-bold text-rose-600"><i class="fa-solid fa-circle-xmark mr-1"></i> Transaksi ini telah kedaluwarsa atau dibatalkan.</p>
                    </div>
                <?php } ?>

            </div>

            <div class="p-6 bg-[#F7F4EB]/50 flex justify-between items-center">
                <span class="text-sm font-bold text-[#1A2E26]">Total Pembayaran</span>
                <span class="text-xl font-extrabold text-[#D4A373]">Rp <?php echo number_format($data['total_bayar'], 0, ',', '.'); ?></span>
            </div>
        </div>

        <p class="text-center text-[11px] text-[#1A2E26]/40 no-print">Hak Cipta Terpelihara © KonserKita. Hubungi CS jika ada kendala masuk.</p>
    </div>

    <div id="modalBayar" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden no-print">
        <div class="bg-[#FDFBF7] p-6 rounded-2xl max-w-sm w-full text-center space-y-4 border border-[#EAE3D2]">
            <h3 class="font-bold text-lg text-[#1A2E26]">Detail Instruksi</h3>
            
            <div id="box-va" class="hidden space-y-2">
                <p class="text-xs text-gray-500">Nomor Virtual Account Anda:</p>
                <div id="kode-va" class="bg-white border border-[#EAE3D2] p-3 rounded-xl font-mono font-bold tracking-wider text-lg text-center text-[#1A2E26]"></div>
            </div>

            <div id="box-qris" class="hidden space-y-2">
                <p class="text-xs text-gray-500">Scan QRIS Menggunakan Aplikasi Bank/E-Wallet:</p>
                <div class="flex justify-center p-4 bg-white border border-[#EAE3D2] rounded-3xl max-w-[180px] mx-auto">
                    <img src="../assets/barcode.png" class="w-36 h-36 object-contain mx-auto" alt="Aset QRIS">
                </div>
            </div>

            <form action="proses_bayar.php" method="POST">
                <input type="hidden" name="id_transaksi_selesai" id="modal-id">
                <button type="submit" class="w-full bg-[#1A2E26] hover:bg-[#1A2E26]/90 text-white py-3 rounded-xl font-bold text-sm transition-colors duration-300">Saya Sudah Selesai Bayar</button>
            </form>
            <button onclick="tutupModal()" class="text-xs text-gray-400 hover:text-gray-600 font-bold block mx-auto transition-colors">Tutup</button>
        </div>
    </div>

    <script>
    function bayarModal(id, va) {
        document.getElementById('modal-id').value = id;
        if (va.includes('QRIS')) {
            document.getElementById('box-qris').classList.remove('hidden');
            document.getElementById('box-va').classList.add('hidden');
        } else {
            document.getElementById('kode-va').innerText = va;
            document.getElementById('box-va').classList.remove('hidden');
            document.getElementById('box-qris').classList.add('hidden');
        }
        document.getElementById('modalBayar').classList.remove('hidden');
    }
    
    function tutupModal() {
        document.getElementById('modalBayar').classList.add('hidden');
    }
    </script>
    </body>
    </html>