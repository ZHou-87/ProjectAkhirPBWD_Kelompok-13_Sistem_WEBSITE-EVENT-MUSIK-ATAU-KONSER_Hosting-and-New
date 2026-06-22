<?php
include '../auth.php';
proteksiAdmin();
include '../koneksi.php';

// Tangkap filter satu bulan dari dashboard (Format input dari select: YYYY-MM)
$bulan_pilihan = isset($_GET['bulan_pilihan']) ? $_GET['bulan_pilihan'] : '';

if (empty($bulan_pilihan)) {
    die("Silakan pilih bulan laporan terlebih dahulu.");
}

// Pecah data tahun dan bulan untuk query SQL
$tahun = date('Y', strtotime($bulan_pilihan));
$bulan = date('m', strtotime($bulan_pilihan));

// FIX AKURASI: Otomatis mendeteksi tanggal terakhir bulan (bisa 28, 29, 30, atau 31)
$hari_terakhir = date('t', strtotime($bulan_pilihan));

$tanggal_mulai = $bulan_pilihan . "-01 00:00:00";
$tanggal_sampai = $bulan_pilihan . "-" . $hari_terakhir . " 23:59:59";

// Query total pendapatan & statistik berdasarkan 1 bulan yang dipilih
$query_stats = mysqli_query($conn, "
    SELECT 
        SUM(CASE WHEN STATUS = 'paid' THEN total_bayar ELSE 0 END) as total_pendapatan,
        COUNT(CASE WHEN STATUS = 'paid' THEN 1 END) as transaksi_lunas,
        COUNT(CASE WHEN STATUS = 'pending' THEN 1 END) as transaksi_pending
    FROM transaksi
    WHERE tanggal_daftar BETWEEN '$tanggal_mulai' AND '$tanggal_sampai'
");
$stats = mysqli_fetch_assoc($query_stats);

// Query list transaksi berdasarkan 1 bulan yang dipilih
$query_transaksi = mysqli_query($conn, "
    SELECT t.id_transaksi, t.tanggal_daftar, t.STATUS, t.total_bayar, t.nomor_va,
           u.nama AS nama_pembeli, u.email
    FROM transaksi t
    JOIN user u ON t.id_user = u.id_user
    WHERE YEAR(t.tanggal_daftar) = '$tahun' AND MONTH(t.tanggal_daftar) = '$bulan'
    ORDER BY t.tanggal_daftar DESC
");

// Format tampilan bulan untuk teks judul laporan (Contoh: "June 2026")
$bulan_format_judul = date('F Y', strtotime($tanggal_mulai));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi Finansial KonserKita - <?php echo $bulan_format_judul; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Mengembalikan style dokumen ke teks biasa formal hitam putih */
        .laporan-body {
            font-family: Arial, sans-serif !important;
            font-size: 12px !important;
            color: #000000 !important;
            background-color: #ffffff !important;
            line-height: 1.4;
        }
        .kop-laporan {
            text-align: center;
            border-b: 2px solid #000000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .kop-laporan h1 {
            margin: 0;
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .kop-laporan p {
            margin: 4px 0 0 0;
            font-size: 12px;
        }
        .tabel-ringkasan {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .tabel-ringkasan td {
            border: 1px solid #000000;
            padding: 8px;
            width: 33.33%;
        }
        .label-ringkasan {
            font-size: 10px;
            text-transform: uppercase;
            display: block;
            color: #333;
        }
        .nilai-ringkasan {
            font-size: 14px;
            font-weight: bold;
        }
        .tabel-data {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        .tabel-data th, .tabel-data td {
            border: 1px solid #000000;
            padding: 6px;
            text-align: left;
            color: #000000 !important;
        }
        .tabel-data th {
            background-color: #f2f2f2;
            text-transform: uppercase;
            font-weight: bold;
        }
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        
        @media print {
            .no-print { display: none !important; }
            body { padding: 0 !important; }
        }
    </style>
</head>
<body class="p-8 bg-white laporan-body">

    <div class="no-print flex gap-2 mb-6 justify-end">
        <button onclick="window.print()" class="bg-[#1A2E26] text-white px-4 py-2 rounded-lg font-bold text-sm shadow hover:bg-[#D4A373] hover:text-[#1A2E26] transition-all">
            Cetak Sekarang
        </button>
        <button onclick="window.close()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-bold text-sm hover:bg-gray-300 transition-all">
            Tutup
        </button>
    </div>

    <div class="kop-laporan" style="border-bottom: 2px solid #000;">
        <h1>KONSERKITA</h1>
        <p><strong>LAPORAN PENDAPATAN & TRANSAKSI FINANSIAL</strong></p>
        <p>Periode Bulan: <?php echo $bulan_format_judul; ?></p>
    </div>

    <table class="tabel-ringkasan">
        <tr>
            <td>
                <span class="label-ringkasan">Total Pendapatan</span>
                <span class="nilai-ringkasan">Rp <?php echo number_format($stats['total_pendapatan'] ?? 0, 0, ',', '.'); ?></span>
            </td>
            <td>
                <span class="label-ringkasan">Transaksi Lunas</span>
                <span class="nilai-ringkasan"><?php echo $stats['transaksi_lunas']; ?> Transaksi</span>
            </td>
            <td>
                <span class="label-ringkasan">Menunggu Pembayaran</span>
                <span class="nilai-ringkasan"><?php echo $stats['transaksi_pending']; ?> Transaksi</span>
            </td>
        </tr>
    </table>

    <table class="tabel-data">
        <thead>
            <tr>
                <th style="width: 15%;">Waktu</th>
                <th style="width: 25%;">Nama Pembeli</th>
                <th style="width: 25%;">Email</th>
                <th style="width: 15%;">Nomor VA</th>
                <th style="width: 12%;">Total Bayar</th>
                <th style="width: 8%; text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if(mysqli_num_rows($query_transaksi) == 0) {
                echo "<tr><td colspan='6' class='text-center'>Tidak ada data transaksi pada bulan ini.</td></tr>";
            }
            while($trans = mysqli_fetch_assoc($query_transaksi)) { 
            ?>
            <tr>
                <td><?php echo date('d/m/Y H:i', strtotime($trans['tanggal_daftar'])); ?></td>
                <td><strong><?php echo htmlspecialchars($trans['nama_pembeli']); ?></strong></td>
                <td><?php echo htmlspecialchars($trans['email']); ?></td>
                <td style="font-family: monospace;"><?php echo htmlspecialchars($trans['nomor_va'] ?? '-'); ?></td>
                <td class="text-right">Rp <?php echo number_format($trans['total_bayar'], 0, ',', '.'); ?></td>
                <td class="text-center" style="text-transform: uppercase; font-weight: bold;">
                    <?php echo $trans['STATUS']; ?>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>