<?php
include '../auth.php';
proteksiAdmin();
include '../koneksi.php';

$tahun_filter = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

$query_stats = mysqli_query($conn, "
    SELECT 
        SUM(CASE WHEN STATUS = 'paid' THEN total_bayar ELSE 0 END) as total_pendapatan,
        COUNT(CASE WHEN STATUS = 'paid' THEN 1 END) as transaksi_lunas,
        COUNT(CASE WHEN STATUS = 'pending' THEN 1 END) as transaksi_pending
    FROM transaksi
");
$stats = mysqli_fetch_assoc($query_stats);

$query_tahun = mysqli_query($conn, "
    SELECT DISTINCT YEAR(tanggal_daftar) as tahun 
    FROM transaksi 
    ORDER BY tahun DESC
");

$query_grafik = mysqli_query($conn, "
    SELECT DATE_FORMAT(MIN(tanggal_daftar), '%M %Y') as bulan, SUM(total_bayar) as total 
    FROM transaksi 
    WHERE STATUS = 'paid' AND YEAR(tanggal_daftar) = '$tahun_filter'
    GROUP BY YEAR(tanggal_daftar), MONTH(tanggal_daftar)
    ORDER BY MONTH(tanggal_daftar) DESC
");

$query_transaksi = mysqli_query($conn, "
    SELECT t.id_transaksi, t.tanggal_daftar, t.STATUS, t.total_bayar, t.nomor_va,
           u.nama AS nama_pembeli, u.email
    FROM transaksi t
    JOIN user u ON t.id_user = u.id_user
    ORDER BY t.tanggal_daftar DESC
");

// FIX ERROR: Menggunakan value_bulan di ORDER BY agar kompatibel dengan DISTINCT
$query_opsi_bulan = mysqli_query($conn, "
    SELECT DISTINCT DATE_FORMAT(tanggal_daftar, '%Y-%m') as value_bulan, 
                    DATE_FORMAT(tanggal_daftar, '%M %Y') as label_bulan
    FROM transaksi
    ORDER BY value_bulan DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - KonserKita</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-[#FDFBF7] text-[#1A2E26] antialiased min-h-screen flex flex-col md:flex-row">

    <header class="bg-[#1A2E26] text-white p-4 flex justify-between items-center md:hidden border-b border-white/10 sticky top-0 z-50">
        <h1 class="text-xl font-extrabold tracking-tight">Konser<span class="text-[#D4A373]">Kita</span> <span class="text-[10px] bg-[#D4A373] text-[#1A2E26] px-1.5 py-0.5 rounded font-bold">ADMIN</span></h1>
        <button id="menuBtn" class="text-white focus:outline-none p-2 text-xl">
            <i class="fa-solid fa-bars" id="menuIcon"></i>
        </button>
    </header>

    <aside id="sidebar" class="hidden md:flex fixed md:sticky top-[61px] md:top-0 left-0 z-40 w-full md:w-64 bg-[#1A2E26] text-white p-6 h-auto md:h-screen flex-col justify-start md:justify-between border-b border-white/10 md:border-none">
        <div class="space-y-6 md:space-y-8 w-full">
            <h1 class="text-2xl font-extrabold tracking-tight hidden md:block">Konser<span class="text-[#D4A373]">Kita</span> <span class="text-xs bg-[#D4A373] text-[#1A2E26] px-2 py-0.5 rounded font-bold">ADMIN</span></h1>
            <nav class="space-y-2">
                <a href="dashboard.php" class="flex items-center gap-3 bg-[#D4A373] text-[#1A2E26] px-4 py-3 rounded-xl font-bold transition-all"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
                <a href="event_tampil.php" class="flex items-center gap-3 text-white/70 hover:text-white hover:bg-white/10 px-4 py-3 rounded-xl font-bold transition-all"><i class="fa-solid fa-calendar-days"></i> Manajemen Event</a>
                <a href="user_tampil.php" class="flex items-center gap-3 text-white/70 hover:text-white hover:bg-white/10 px-4 py-3 rounded-xl font-bold transition-all"><i class="fa-solid fa-users"></i> Kelola Pengguna</a>
            </nav>
        </div>
        <a href="../logout.php" class="flex items-center gap-3 text-rose-400 hover:text-rose-500 font-bold mt-4 md:mt-8 pt-4 border-t border-white/10 w-full"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </aside>

    <main class="flex-1 p-4 md:p-10 space-y-8 overflow-x-hidden">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 border-b border-[#EAE3D2]/40 pb-6">
            <div>
                <h2 class="text-2xl md:text-3xl font-black">Dashboard Finansial</h2>
                <p class="text-xs md:text-sm text-[#1A2E26]/60">Pantau performa penjualan uang masuk dan log transaksi tiket.</p>
            </div>
            
            <form action="cetak_pdf.php" method="GET" target="_blank" class="bg-white border border-[#EAE3D2] p-4 rounded-2xl flex flex-col sm:flex-row items-end gap-3 shadow-sm w-full lg:w-auto">
                <div class="w-full sm:w-56">
                    <label class="block text-[10px] font-bold uppercase text-[#1A2E26]/50 mb-1">Pilih Bulan Laporan Keuangan</label>
                    <select name="bulan_pilihan" required class="w-full text-xs bg-[#FDFBF7] border border-[#EAE3D2] rounded-lg px-3 py-2 focus:outline-none focus:border-[#D4A373] font-bold text-[#1A2E26] h-[34px]">
                        <?php 
                        if(mysqli_num_rows($query_opsi_bulan) == 0) {
                            echo "<option value='".date('Y-m')."'>".date('F Y')."</option>";
                        }
                        while($opsi = mysqli_fetch_assoc($query_opsi_bulan)) { 
                            echo "<option value='".$opsi['value_bulan']."'>".$opsi['label_bulan']."</option>";
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" class="w-full sm:w-auto bg-[#1A2E26] hover:bg-[#D4A373] hover:text-[#1A2E26] text-white text-xs font-bold px-5 py-2.5 rounded-lg transition-all flex items-center justify-center gap-2 h-[34px]">
                    <i class="fa-solid fa-file-pdf"></i> Cetak Laporan PDF
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
            <div class="bg-white border border-[#EAE3D2] p-5 md:p-6 rounded-2xl md:rounded-3xl shadow-sm space-y-2">
                <div class="flex justify-between items-center"><span class="text-[10px] md:text-xs font-bold text-[#1A2E26]/50 uppercase tracking-wider">Total Pendapatan</span><i class="fa-solid fa-money-bill-wave text-emerald-600 text-lg"></i></div>
                <p class="text-xl md:text-2xl font-extrabold text-[#1A2E26]">Rp <?php echo number_format($stats['total_pendapatan'] ?? 0, 0, ',', '.'); ?></p>
            </div>
            <div class="bg-white border border-[#EAE3D2] p-5 md:p-6 rounded-2xl md:rounded-3xl shadow-sm space-y-2">
                <div class="flex justify-between items-center"><span class="text-[10px] md:text-xs font-bold text-[#1A2E26]/50 uppercase tracking-wider">Tiket Terjual Lunas</span><i class="fa-solid fa-ticket text-[#D4A373] text-lg"></i></div>
                <p class="text-xl md:text-2xl font-extrabold text-[#1A2E26]"><?php echo $stats['transaksi_lunas']; ?> Transaksi</p>
            </div>
            <div class="bg-white border border-[#EAE3D2] p-5 md:p-6 rounded-2xl md:rounded-3xl shadow-sm space-y-2 sm:col-span-2 lg:col-span-1">
                <div class="flex justify-between items-center"><span class="text-[10px] md:text-xs font-bold text-[#1A2E26]/50 uppercase tracking-wider">Menunggu Bayar</span><i class="fa-solid fa-clock text-amber-500 text-lg"></i></div>
                <p class="text-xl md:text-2xl font-extrabold text-[#1A2E26]"><?php echo $stats['transaksi_pending']; ?> Transaksi</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
            <div class="bg-white border border-[#EAE3D2] p-5 md:p-6 rounded-2xl md:rounded-3xl shadow-sm space-y-4 flex flex-col h-[350px] md:h-[400px]">
                <div class="flex justify-between items-center gap-2 shrink-0">
                    <h3 class="font-extrabold text-base md:text-lg text-[#1A2E26]">Omzet Bulanan</h3>
                    <form method="GET" action="" class="w-auto">
                        <select name="tahun" onchange="this.form.submit()" class="text-[10px] md:text-xs font-bold bg-[#FDFBF7] border border-[#EAE3D2] rounded-lg px-2 py-1.5 focus:outline-none focus:border-[#D4A373] text-[#1A2E26]">
                            <?php 
                            if(mysqli_num_rows($query_tahun) == 0) {
                                echo "<option value='".date('Y')."'>".date('Y')."</option>";
                            }
                            while($t = mysqli_fetch_assoc($query_tahun)) { 
                                $selected = ($t['tahun'] == $tahun_filter) ? 'selected' : '';
                                echo "<option value='".$t['tahun']."' $selected>".$t['tahun']."</option>";
                            } 
                            ?>
                        </select>
                    </form>
                </div>
                <div class="overflow-y-auto flex-1 pr-1">
                    <table class="w-full text-left text-sm">
                        <tbody class="divide-y divide-[#EAE3D2]/40 font-semibold text-[#1A2E26]">
                            <?php while($row = mysqli_fetch_assoc($query_grafik)) { ?>
                            <tr>
                                <td class="py-3 text-xs"><?php echo $row['bulan']; ?></td>
                                <td class="py-3 text-right text-emerald-600 text-xs">Rp <?php echo number_format($row['total'], 0, ',', '.'); ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="lg:col-span-2 bg-white border border-[#EAE3D2] p-5 md:p-6 rounded-2xl md:rounded-3xl shadow-sm space-y-4 flex flex-col h-[500px] md:h-[400px]">
                <h3 class="font-extrabold text-base md:text-lg text-[#1A2E26] shrink-0">Tampilan Transaksi</h3>
                
                <div class="overflow-y-auto flex-1 pr-1">
                    <table class="w-full text-left text-xs hidden md:table">
                        <thead class="sticky top-0 bg-white z-10">
                            <tr class="border-b border-[#EAE3D2] text-[#1A2E26]/40 uppercase font-bold">
                                <th class="pb-3 w-[20%]">Waktu</th>
                                <th class="pb-3 w-[35%]">Pembeli</th>
                                <th class="pb-3 w-[20%]">Nomor VA</th>
                                <th class="pb-3 w-[15%]">Total</th>
                                <th class="pb-3 text-center w-[10%]">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#EAE3D2]/40 font-semibold text-[#1A2E26]">
                            <?php 
                            mysqli_data_seek($query_transaksi, 0); 
                            while($trans = mysqli_fetch_assoc($query_transaksi)) { 
                            ?>
                            <tr>
                                <td class="py-3 text-gray-400 text-[10px]"><?php echo date('d/m H:i', strtotime($trans['tanggal_daftar'])); ?></td>
                                <td class="py-3">
                                    <div class="font-bold"><?php echo htmlspecialchars($trans['nama_pembeli']); ?></div>
                                    <div class="text-[10px] text-gray-400"><?php echo htmlspecialchars($trans['email']); ?></div>
                                </td>
                                <td class="py-3 font-mono text-gray-600"><?php echo htmlspecialchars($trans['nomor_va'] ?? '-'); ?></td>
                                <td class="py-3 text-emerald-600">Rp <?php echo number_format($trans['total_bayar'], 0, ',', '.'); ?></td>
                                <td class="py-3 text-center">
                                    <?php if($trans['STATUS'] === 'paid') { ?>
                                        <span class="text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded text-[10px] uppercase font-bold border border-emerald-200">Paid</span>
                                    <?php } elseif($trans['STATUS'] === 'pending') { ?>
                                        <span class="text-amber-600 bg-amber-50 px-2 py-0.5 rounded text-[10px] uppercase font-bold border border-amber-200">Pending</span>
                                    <?php } else { ?>
                                        <span class="text-rose-600 bg-rose-50 px-2 py-0.5 rounded text-[10px] uppercase font-bold border border-rose-200">Cancelled</span>
                                    <?php } ?>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>

                    <div class="space-y-3 md:hidden">
                        <?php 
                        mysqli_data_seek($query_transaksi, 0); 
                        while($trans = mysqli_fetch_assoc($query_transaksi)) { 
                        ?>
                        <div class="bg-[#FDFBF7]/50 border border-[#EAE3D2]/60 p-4 rounded-xl space-y-2 text-xs font-semibold">
                            <div class="flex justify-between items-center border-b border-[#EAE3D2]/40 pb-2">
                                <span class="text-[10px] text-gray-400"><i class="fa-regular fa-clock mr-1"></i><?php echo date('d M Y, H:i', strtotime($trans['tanggal_daftar'])); ?></span>
                                <div>
                                    <?php if($trans['STATUS'] === 'paid') { ?>
                                        <span class="text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded text-[9px] uppercase font-bold border border-emerald-200">Paid</span>
                                    <?php } elseif($trans['STATUS'] === 'pending') { ?>
                                        <span class="text-amber-600 bg-amber-50 px-2 py-0.5 rounded text-[9px] uppercase font-bold border border-amber-200">Pending</span>
                                    <?php } else { ?>
                                        <span class="text-rose-600 bg-rose-50 px-2 py-0.5 rounded text-[9px] uppercase font-bold border border-rose-200">Cancelled</span>
                                    <?php } ?>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-2 pt-1 text-[11px]">
                                <div>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Pembeli</p>
                                    <p class="text-[#1A2E26] font-bold truncate max-w-[130px]"><?php echo htmlspecialchars($trans['nama_pembeli']); ?></p>
                                    <p class="text-[10px] text-gray-400 truncate max-w-[130px]"><?php echo htmlspecialchars($trans['email']); ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Total Bayar</p>
                                    <p class="text-emerald-600 font-extrabold text-sm">Rp <?php echo number_format($trans['total_bayar'], 0, ',', '.'); ?></p>
                                </div>
                            </div>

                            <div class="bg-white px-2.5 py-1.5 rounded-lg flex justify-between items-center border border-[#EAE3D2]/40 text-[11px]">
                                <span class="text-gray-400 text-[10px]">No. Virtual Account:</span>
                                <span class="font-mono text-[#1A2E26] font-bold"><?php echo htmlspecialchars($trans['nomor_va'] ?? '-'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        const menuBtn = document.getElementById('menuBtn');
        const sidebar = document.getElementById('sidebar');
        const menuIcon = document.getElementById('menuIcon');

        menuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('hidden');
            sidebar.classList.toggle('flex');
            
            if(sidebar.classList.contains('hidden')) {
                menuIcon.className = 'fa-solid fa-bars';
            } else {
                menuIcon.className = 'fa-solid fa-xmark';
            }
        });
    </script>
</body>
</html>