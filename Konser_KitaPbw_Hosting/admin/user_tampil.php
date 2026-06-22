<?php
include '../auth.php';
proteksiAdmin();
include '../koneksi.php';

$sukses = "";
$error = "";

if (isset($_GET['aksi']) && isset($_GET['id'])) {
    $id_user = intval($_GET['id']);
    
    if ($id_user == $_SESSION['user']['id_user']) {
        $error = "Gagal! Anda tidak bisa menghapus akun Anda sendiri yang sedang aktif digunakan.";
    } else {
        if ($_GET['aksi'] === 'hapus') {
            $delete = mysqli_query($conn, "DELETE FROM user WHERE id_user = '$id_user'");
            if ($delete) {
                $sukses = "Akun pengguna berhasil dihapus secara permanen dari sistem.";
            } else {
                $error = "Gagal menghapus pengguna. Data kemungkinan terikat relasi transaksi di tabel lain.";
            }
        }
    }
}

$query_tabel = mysqli_query($conn, "SELECT * FROM user ORDER BY id_user DESC");
$query_log = mysqli_query($conn, "SELECT nama, email, role, waktu_login FROM log_login ORDER BY id_log DESC LIMIT 50");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - KonserKita</title>
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
                <a href="event_tampil.php" class="flex items-center gap-3 text-white/70 hover:text-white hover:bg-white/10 px-4 py-3 rounded-xl font-bold transition-all"><i class="fa-solid fa-calendar-days"></i> Manajemen Event</a>
                <a href="user_tampil.php" class="flex items-center gap-3 bg-[#D4A373] text-[#1A2E26] px-4 py-3 rounded-xl font-bold transition-all"><i class="fa-solid fa-users"></i> Kelola Pengguna</a>
            </nav>
        </div>
        <a href="../logout.php" class="flex items-center gap-3 text-rose-400 hover:text-rose-500 font-bold mt-4 md:mt-8 pt-4 border-t border-white/10 w-full"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </aside>

    <main class="flex-1 p-4 md:p-10 space-y-6 overflow-x-hidden">
        <div>
            <h2 class="text-2xl md:text-3xl font-black">Kelola Pengguna</h2>
            <p class="text-xs md:text-sm text-[#1A2E26]/60">Pantau aktivitas sistem serta eliminasi akun pengguna yang melanggar ketentuan.</p>
        </div>

        <?php if (!empty($error)) { ?>
            <div class="bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-sm"></i> <?php echo $error; ?>
            </div>
        <?php } ?>

        <?php if (!empty($sukses)) { ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-sm"></i> <?php echo $sukses; ?>
            </div>
        <?php } ?>

        <div class="bg-white border border-[#EAE3D2] rounded-2xl md:rounded-3xl p-4 md:p-6 shadow-sm space-y-4">
            <div class="flex items-center gap-2 text-[#1A2E26]">
                <i class="fa-solid fa-clock-rotate-left text-amber-600"></i>
                <h3 class="font-extrabold text-base">Log Login Terbaru Sistem</h3>
            </div>
            
            <div class="h-[220px] overflow-y-auto pr-2 space-y-2 border border-[#EAE3D2]/60 rounded-xl p-3 bg-[#FDFBF7]">
                <?php if(mysqli_num_rows($query_log) > 0) { ?>
                    <?php while($log = mysqli_fetch_assoc($query_log)) { ?>
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between p-3 rounded-xl hover:bg-[#F7F4EB]/50 border border-transparent hover:border-[#EAE3D2]/40 text-xs font-medium transition-all gap-2 lg:gap-4">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="w-2 h-2 rounded-full <?php echo ($log['role'] === 'admin') ? 'bg-amber-500' : 'bg-emerald-500'; ?> shrink-0"></span>
                                <span class="font-bold text-[#1A2E26]"><?php echo htmlspecialchars($log['nama']); ?></span>
                                <span class="text-[#1A2E26]/40 text-[11px] break-all">(<?php echo htmlspecialchars($log['email']); ?>)</span>
                                <span class="text-[9px] uppercase font-bold px-1.5 py-0.5 rounded tracking-wider <?php echo ($log['role'] === 'admin') ? 'bg-amber-50 border border-amber-200 text-amber-700' : 'bg-slate-50 border border-slate-200 text-slate-600'; ?> shrink-0">
                                    <?php echo htmlspecialchars($log['role']); ?>
                                </span>
                            </div>
                            <div class="text-[#1A2E26]/50 font-mono text-[11px] bg-white lg:bg-transparent border lg:border-none border-[#EAE3D2]/40 p-2 lg:p-0 rounded-lg flex items-center gap-1.5 shrink-0 ml-auto lg:ml-0">
                                <i class="fa-regular fa-clock text-[#D4A373]"></i>
                                <span><?php echo date('d M Y - H:i:s', strtotime($log['waktu_login'])); ?> WIB</span>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <p class="text-xs text-[#1A2E26]/40 italic text-center py-4">Belum ada riwayat aktivitas log login.</p>
                <?php } ?>
            </div>
        </div>

        <div class="bg-white border border-[#EAE3D2] rounded-2xl md:rounded-3xl shadow-sm overflow-hidden p-4 md:p-0">
            <h3 class="font-extrabold text-base p-2 md:p-6 md:pb-3 block md:hidden">Daftar Akun Pengguna</h3>
            
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#F7F4EB] border-b border-[#EAE3D2] text-[#1A2E26]/70 text-xs font-bold uppercase tracking-wider">
                            <th class="p-5">Nama Pengguna</th>
                            <th class="p-5">Kontak & Email</th>
                            <th class="p-5">Status Hak Akses</th>
                            <th class="p-5 text-center">Aksi Manajemen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EAE3D2]/40 text-sm font-medium">
                        <?php 
                        mysqli_data_seek($query_tabel, 0);
                        while($row = mysqli_fetch_assoc($query_tabel)) { 
                        ?>
                            <tr class="hover:bg-[#FDFBF7]/50 transition-colors">
                                <td class="p-5">
                                    <div>
                                        <span class="block font-bold text-[#1A2E26]"><?php echo htmlspecialchars($row['nama']); ?></span>
                                        <span class="block text-[11px] text-[#1A2E26]/40">ID User: #<?php echo $row['id_user']; ?></span>
                                    </div>
                                </td>
                                <td class="p-5">
                                    <span class="block text-[#1A2E26]"><?php echo htmlspecialchars($row['email']); ?></span>
                                </td>
                                <td class="p-5">
                                    <?php if ($row['ROLE'] === 'admin') { ?>
                                        <span class="inline-flex items-center gap-1 bg-amber-50 border border-amber-200 text-amber-700 px-2.5 py-1 rounded-lg text-xs font-bold">
                                            <i class="fa-solid fa-user-shield text-[10px]"></i> Admin Sistem
                                        </span>
                                    <?php } else { ?>
                                        <span class="inline-flex items-center gap-1 bg-slate-50 border border-slate-200 text-slate-600 px-2.5 py-1 rounded-lg text-xs font-bold">
                                            <i class="fa-solid fa-user text-[10px]"></i> Pengunjung / User
                                        </span>
                                    <?php } ?>
                                </td>
                                <td class="p-5">
                                    <div class="flex items-center justify-center gap-2">
                                        <?php if ($row['id_user'] != $_SESSION['user']['id_user']) { ?>
                                            <a href="user_tampil.php?aksi=hapus&id=<?php echo $row['id_user']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus permanen akun pengguna ini?')" class="bg-rose-50 text-rose-600 border border-rose-200 hover:bg-rose-100 text-xs font-bold px-4 py-2 rounded-xl transition-all flex items-center gap-1">
                                                <i class="fa-solid fa-trash-can"></i> Hapus Akun
                                            </a>
                                        <?php } else { ?>
                                            <span class="text-xs font-bold text-[#1A2E26]/30 bg-[#FDFBF7] px-4 py-2 rounded-xl border border-[#EAE3D2]/40 italic">
                                                Sesi Anda saat ini
                                            </span>
                                        <?php } ?>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="block md:hidden space-y-3">
                <?php 
                mysqli_data_seek($query_tabel, 0);
                while($row = mysqli_fetch_assoc($query_tabel)) { 
                ?>
                    <div class="bg-[#FDFBF7] border border-[#EAE3D2] rounded-xl p-4 space-y-3">
                        <div class="flex justify-between items-start border-b border-[#EAE3D2]/60 pb-2">
                            <div>
                                <span class="block font-bold text-sm text-[#1A2E26]"><?php echo htmlspecialchars($row['nama']); ?></span>
                                <span class="text-[10px] text-[#1A2E26]/40">ID Pengguna: #<?php echo $row['id_user']; ?></span>
                            </div>
                            <?php if ($row['ROLE'] === 'admin') { ?>
                                <span class="bg-amber-50 border border-amber-200 text-amber-700 px-2 py-0.5 rounded-md text-[10px] font-bold">Admin</span>
                            <?php } else { ?>
                                <span class="bg-slate-50 border border-slate-200 text-slate-600 px-2 py-0.5 rounded-md text-[10px] font-bold">User</span>
                            <?php } ?>
                        </div>
                        
                        <div class="text-xs space-y-1">
                            <span class="text-gray-400 font-medium block">Alamat Email:</span>
                            <span class="text-[#1A2E26] font-semibold break-all"><?php echo htmlspecialchars($row['email']); ?></span>
                        </div>

                        <div class="pt-1">
                            <?php if ($row['id_user'] != $_SESSION['user']['id_user']) { ?>
                                <a href="user_tampil.php?aksi=hapus&id=<?php echo $row['id_user']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus permanen akun pengguna ini?')" class="w-full justify-center bg-rose-50 text-rose-600 border border-rose-200 text-xs font-bold py-2.5 rounded-xl flex items-center gap-1.5 transition-all">
                                    <i class="fa-solid fa-trash-can"></i> Hapus Akun Pengguna
                                </a>
                            <?php } else { ?>
                                <div class="w-full text-center text-xs font-bold text-[#1A2E26]/40 bg-white border border-[#EAE3D2]/60 py-2 rounded-xl italic">
                                    Sesi Anda Aktif Saat Ini
                                </div>
                            <?php } ?>
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