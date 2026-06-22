<?php
include '../auth.php';
proteksiAdmin();
include '../koneksi.php';

if (isset($_GET['hapus'])) {
    $id_event = $_GET['hapus'];
    $query_file = mysqli_query($conn, "SELECT poster FROM event WHERE id_event = '$id_event'");
    $data_file = mysqli_fetch_assoc($query_file);
    if ($data_file && !empty($data_file['poster'])) {
        $path = "../assets/" . $data_file['poster'];
        if (file_exists($path)) {
            unlink($path);
        }
    }
    mysqli_query($conn, "DELETE FROM kategori WHERE id_event = '$id_event'");
    mysqli_query($conn, "DELETE FROM event WHERE id_event = '$id_event'");
    header("Location: event_tampil.php");
    exit;
}

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$query_where = !empty($search) ? "WHERE e.nama_event LIKE '%$search%' OR e.lokasi LIKE '%$search%' OR e.genre LIKE '%$search%'" : "";

$query_event = mysqli_query($conn, "
    SELECT e.*, 
           GROUP_CONCAT(CONCAT(k.nama_kategori, ' (Rp ', FORMAT(k.harga, 0, 'id_ID'), ')') SEPARATOR '<br>') as info_kategori,
           SUM(k.kuota_total) as total_kuota
    FROM event e
    LEFT JOIN kategori k ON e.id_event = k.id_event
    $query_where
    GROUP BY e.id_event
    ORDER BY e.id_event DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Event - KonserKita</title>
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
                <a href="dashboard.php" class="flex items-center gap-3 text-white/70 hover:text-white hover:bg-white/10 px-4 py-3 rounded-xl font-bold transition-all"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
                <a href="event_tampil.php" class="flex items-center gap-3 bg-[#D4A373] text-[#1A2E26] px-4 py-3 rounded-xl font-bold transition-all"><i class="fa-solid fa-calendar-days"></i> Manajemen Event</a>
                <a href="user_tampil.php" class="flex items-center gap-3 text-white/70 hover:text-white hover:bg-white/10 px-4 py-3 rounded-xl font-bold transition-all"><i class="fa-solid fa-users"></i> Kelola Pengguna</a>
            </nav>
        </div>
        <a href="../logout.php" class="flex items-center gap-3 text-rose-400 hover:text-rose-500 font-bold mt-4 md:mt-8 pt-4 border-t border-white/10 w-full"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </aside>

    <main class="flex-1 p-4 md:p-10 space-y-6 overflow-x-hidden">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div>
                <h2 class="text-2xl md:text-3xl font-black">Manajemen Event</h2>
                <p class="text-xs md:text-sm text-[#1A2E26]/60">Kelola pertunjukan, kuota tiket, dan unggah poster konser musik.</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
                <a href="event_tambah.php" class="w-full sm:w-auto bg-[#1A2E26] hover:bg-[#1A2E26]/90 text-white px-5 py-3 rounded-xl font-bold text-xs flex items-center justify-center gap-2 transition-all shadow-md">
                    <i class="fa-solid fa-plus"></i> Tambah Event Baru
                </a>
                <a href="pengaturan_sk.php" class="w-full sm:w-auto bg-amber-600 hover:bg-amber-700 text-white px-5 py-3 rounded-xl font-bold text-xs flex items-center justify-center gap-2 transition-all shadow-md">
                    <i class="fa-solid fa-file-shield"></i> Kelola S&K Global
                </a>
            </div>
        </div>

        <div class="bg-white border border-[#EAE3D2] p-4 md:p-6 rounded-2xl md:rounded-3xl shadow-sm space-y-4 flex flex-col">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 shrink-0">
                <h3 class="font-extrabold text-base md:text-lg">Daftar Pertunjukan</h3>
                <form method="GET" action="" class="w-full sm:w-auto relative">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari nama, lokasi, atau genre..." class="w-full sm:w-64 bg-[#FDFBF7] border border-[#EAE3D2] rounded-xl pl-10 pr-4 py-2.5 sm:py-2 text-xs font-medium focus:outline-none focus:border-[#D4A373]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3.5 sm:top-3 text-[#1A2E26]/30 text-xs"></i>
                </form>
            </div>

            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-[#EAE3D2] text-[#1A2E26]/40 uppercase font-bold">
                            <th class="pb-3 w-16">Poster</th>
                            <th class="pb-3">Detail Pertunjukan</th>
                            <th class="pb-3">Genre</th>
                            <th class="pb-3">Tanggal Pelaksanaan</th>
                            <th class="pb-3">Kategori & Harga</th>
                            <th class="pb-3 text-center">Total Kuota</th>
                            <th class="pb-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EAE3D2]/40 font-semibold text-[#1A2E26]">
                        <?php if (mysqli_num_rows($query_event) == 0) { ?>
                            <tr>
                                <td colspan="7" class="py-8 text-center text-gray-400 font-medium">Tidak ada data event ditemukan.</td>
                            </tr>
                        <?php } ?>
                        <?php 
                        mysqli_data_seek($query_event, 0); 
                        while ($row = mysqli_fetch_assoc($query_event)) { 
                        ?>
                        <tr>
                            <td class="py-4">
                                <img src="../assets/<?php echo !empty($row['poster']) ? htmlspecialchars($row['poster']) : 'default-poster.jpg'; ?>" class="w-12 h-16 object-cover rounded-lg shadow-sm border border-[#EAE3D2]">
                            </td>
                            <td class="py-4 pr-4">
                                <div class="font-bold text-sm text-[#1A2E26]"><?php echo htmlspecialchars($row['nama_event']); ?></div>
                                <div class="text-[10px] text-gray-400 mt-0.5 flex items-center gap-1"><i class="fa-solid fa-location-dot text-[#D4A373]"></i> <?php echo htmlspecialchars($row['lokasi']); ?></div>
                            </td>
                            <td class="py-4">
                                <span class="bg-[#FDFBF7] border border-[#EAE3D2] text-[#1A2E26]/70 px-2 py-1 rounded-md text-[10px] uppercase font-bold"><?php echo htmlspecialchars($row['genre']); ?></span>
                            </td>
                            <td class="py-4 text-gray-600">
                                <div><?php echo date('d M Y', strtotime($row['tanggal'])); ?></div>
                            </td>
                            <td class="py-4 text-[11px] leading-relaxed text-gray-600">
                                <?php echo $row['info_kategori'] ?? '<span class="text-rose-500">Belum ada kategori</span>'; ?>
                            </td>
                            <td class="py-4 text-center font-mono"><?php echo number_format($row['total_kuota'] ?? 0, 0, ',', '.'); ?></td>
                            <td class="py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="event_edit.php?id=<?php echo $row['id_event']; ?>" class="w-8 h-8 bg-amber-50 text-amber-600 border border-amber-200 rounded-lg flex items-center justify-center hover:bg-amber-100 transition-colors" title="Edit Event">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </a>
                                    <a href="event_tampil.php?hapus=<?php echo $row['id_event']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus event ini? Semua kategori terkait juga akan dihapus.')" class="w-8 h-8 bg-rose-50 text-rose-600 border border-rose-200 rounded-lg flex items-center justify-center hover:bg-rose-100 transition-colors" title="Hapus Event">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="block md:hidden space-y-4">
                <?php 
                if (mysqli_num_rows($query_event) == 0) { 
                    echo '<div class="py-8 text-center text-gray-400 font-medium text-xs">Tidak ada data event ditemukan.</div>';
                }
                mysqli_data_seek($query_event, 0); 
                while ($row = mysqli_fetch_assoc($query_event)) { 
                ?>
                <div class="bg-[#FDFBF7] border border-[#EAE3D2] rounded-2xl p-4 space-y-3 relative">
                    <div class="flex gap-3">
                        <img src="../assets/<?php echo !empty($row['poster']) ? htmlspecialchars($row['poster']) : 'default-poster.jpg'; ?>" class="w-14 h-20 object-cover rounded-xl shadow-sm border border-[#EAE3D2] shrink-0">
                        <div class="space-y-1">
                            <span class="bg-white border border-[#EAE3D2] text-[#1A2E26]/70 px-2 py-0.5 rounded-md text-[9px] uppercase font-bold inline-block"><?php echo htmlspecialchars($row['genre']); ?></span>
                            <h4 class="font-bold text-sm text-[#1A2E26] leading-snug"><?php echo htmlspecialchars($row['nama_event']); ?></h4>
                            <div class="text-[10px] text-gray-400 flex items-center gap-1"><i class="fa-solid fa-location-dot text-[#D4A373]"></i> <?php echo htmlspecialchars($row['lokasi']); ?></div>
                            <div class="text-[10px] text-gray-500 flex items-center gap-1"><i class="fa-solid fa-calendar-days text-[#D4A373]"></i> <?php echo date('d M Y', strtotime($row['tanggal'])); ?></div>
                        </div>
                    </div>
                    
                    <div class="border-t border-[#EAE3D2]/60 pt-2.5">
                        <div class="text-[10px] text-[#1A2E26]/40 uppercase font-bold tracking-wider mb-1">Kategori Tiket:</div>
                        <div class="text-[11px] space-y-1 text-gray-600 bg-white p-2.5 rounded-xl border border-[#EAE3D2]/50 leading-relaxed">
                            <?php echo $row['info_kategori'] ?? '<span class="text-rose-500">Belum ada kategori</span>'; ?>
                        </div>
                    </div>

                    <div class="flex justify-between items-center border-t border-[#EAE3D2]/60 pt-3">
                        <div class="text-xs">
                            <span class="text-gray-400 font-medium">Total Kuota:</span> 
                            <span class="font-bold font-mono text-[#1A2E26]"><?php echo number_format($row['total_kuota'] ?? 0, 0, ',', '.'); ?></span>
                        </div>
                        <div class="flex gap-2">
                            <a href="event_edit.php?id=<?php echo $row['id_event']; ?>" class="w-9 h-9 bg-amber-50 text-amber-600 border border-amber-200 rounded-xl flex items-center justify-center transition-colors">
                                <i class="fa-solid fa-pen-to-square text-sm"></i>
                            </a>
                            <a href="event_tampil.php?hapus=<?php echo $row['id_event']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus event ini? Semua kategori terkait juga akan dihapus.')" class="w-9 h-9 bg-rose-50 text-rose-600 border border-rose-200 rounded-xl flex items-center justify-center transition-colors">
                                <i class="fa-solid fa-trash-can text-sm"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php } ?>
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