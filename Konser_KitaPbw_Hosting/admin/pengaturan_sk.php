<?php
include '../auth.php';
proteksiAdmin();
include '../koneksi.php';

$error = "";
$sukses = "";

// === PROSES UPLOAD S&K GLOBAL ===
if (isset($_POST['upload_sk'])) {
    if (!empty($_FILES['sk_file']['name'])) {
        $sk_name = $_FILES['sk_file']['name'];
        $sk_tmp  = $_FILES['sk_file']['tmp_name'];
        $sk_ext  = strtolower(pathinfo($sk_name, PATHINFO_EXTENSION));
        
        // Validasi ekstensi harus PDF
        if ($sk_ext === 'pdf') {
            // File langsung ditimpa (overwrite) ke folder assets dengan nama tetap
            if (move_uploaded_file($sk_tmp, '../assets/syarat_ketentuan_konser.pdf')) {
                $sukses = "Dokumen Syarat & Ketentuan Global berhasil diperbarui!";
            } else {
                $error = "Gagal memindahkan file dokumen ke server.";
            }
        } else {
            $error = "Format file salah! Hanya dokumen berekstensi .PDF yang diperbolehkan.";
        }
    } else {
        $error = "Silakan pilih file PDF terlebih dahulu.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan S&K - KonserKita</title>
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
                <a href="user_tampil.php" class="flex items-center gap-3 text-white/70 hover:text-white hover:bg-white/10 px-4 py-3 rounded-xl font-bold transition-all"><i class="fa-solid fa-users"></i> Kelola Pengguna</a>
            </nav>
        </div>
        <a href="../logout.php" class="flex items-center gap-3 text-rose-400 hover:text-rose-500 font-bold mt-4 md:mt-8 pt-4 border-t border-white/10 w-full"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </aside>

    <main class="flex-1 p-4 md:p-10 space-y-6 overflow-x-hidden">
        
        <div>
            <a href="event_tampil.php" class="inline-flex items-center gap-2 bg-white border border-[#EAE3D2] hover:bg-gray-50 text-[#1A2E26] px-4 py-2.5 rounded-xl font-bold text-xs transition-all shadow-sm">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Manajemen Event
            </a>
        </div>

        <div>
            <h2 class="text-2xl md:text-3xl font-black">Pengaturan Dokumen S&K</h2>
            <p class="text-xs md:text-sm text-[#1A2E26]/60">Upload satu file dokumen Syarat & Ketentuan di sini untuk diterapkan ke seluruh konser musik KonserKita.</p>
        </div>

        <div class="bg-white border border-[#EAE3D2] p-5 md:p-8 rounded-2xl md:rounded-3xl shadow-sm max-w-2xl">
            <?php if (!empty($error)) { ?>
                <div class="bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2 mb-6">
                    <i class="fa-solid fa-circle-exclamation text-sm"></i> <?php echo $error; ?>
                </div>
            <?php } ?>

            <?php if (!empty($sukses)) { ?>
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-600 px-4 py-3 rounded-xl text-xs font-bold flex items-center gap-2 mb-6">
                    <i class="fa-solid fa-circle-check text-sm"></i> <?php echo $sukses; ?>
                </div>
            <?php } ?>

            <div class="mb-6 p-4 bg-[#FDFBF7] border border-[#EAE3D2] rounded-xl">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#1A2E26]/60 mb-2">Status Dokumen S&K Saat Ini:</h3>
                <?php if (file_exists('../assets/syarat_ketentuan_konser.pdf')) { ?>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-emerald-600 flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-check"></i> Dokumen S&K Global Aktif (`syarat_ketentuan_konser.pdf`)
                        </span>
                        <a href="../assets/syarat_ketentuan_konser.pdf" target="_blank" class="text-xs font-bold text-[#D4A373] hover:underline">
                            <i class="fa-solid fa-eye"></i> Cek File S&K
                        </a>
                    </div>
                <?php } else { ?>
                    <span class="text-xs font-semibold text-rose-500 flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-xmark"></i> Belum ada file S&K global yang diupload.
                    </span>
                <?php } ?>
            </div>

            <form method="POST" action="" enctype="multipart/form-data" class="space-y-4">
                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-[#1A2E26]/60">Pilih File S&K Baru (.PDF)</label>
                    <input type="file" name="sk_file" accept=".pdf" required class="w-full bg-[#FDFBF7] border border-[#EAE3D2] rounded-xl px-4 py-2.5 text-xs font-medium focus:outline-none focus:border-[#D4A373] file:mr-4 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#1A2E26] file:text-white file:cursor-pointer file:hover:bg-[#1A2E26]/90">
                </div>

                <button type="submit" name="upload_sk" class="w-full sm:w-auto bg-[#1A2E26] hover:bg-[#1A2E26]/90 text-white px-6 py-3 rounded-xl font-bold text-xs tracking-wide transition-all shadow-md flex items-center justify-center gap-2">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Perbarui Dokumen Global
                </button>
            </form>
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