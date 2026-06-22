<?php
include '../auth.php';
proteksiAdmin();
include '../koneksi.php';

$error = "";
$sukses = "";

if (!isset($_GET['id'])) {
    header("Location: event_tampil.php");
    exit;
}

$id_event = $_GET['id'];

$query_event = mysqli_query($conn, "SELECT * FROM event WHERE id_event = '$id_event'");
$data_event = mysqli_fetch_assoc($query_event);

if (!$data_event) {
    header("Location: event_tampil.php");
    exit;
}

if (isset($_POST['update'])) {
    $nama_event = mysqli_real_escape_string($conn, $_POST['nama_event']);
    $lokasi = mysqli_real_escape_string($conn, $_POST['lokasi']);
    $tanggal = $_POST['tanggal'];
    $genre = mysqli_real_escape_string($conn, $_POST['genre']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);

    // Mengambil array input kategori
    $arr_id_kategori = isset($_POST['id_kategori']) ? $_POST['id_kategori'] : [];
    $arr_nama_kategori = $_POST['nama_kategori'];
    $arr_harga = $_POST['harga'];
    $arr_kuota_total = $_POST['kuota_total'];

    $nama_file = $_FILES['poster']['name'];
    $tmp_file = $_FILES['poster']['tmp_name'];
    $ukuran_file = $_FILES['poster']['size'];
    
    $poster_final = $data_event['poster'];

    if (!empty($nama_file)) {
        $ekstensi_diperbolehkan = ['jpg', 'jpeg', 'png', 'webp'];
        $x = explode('.', $nama_file);
        $ekstensi = strtolower(end($x));
        
        if (in_array($ekstensi, $ekstensi_diperbolehkan) === true) {
            if ($ukuran_file < 5242880) {
                $nama_file_baru = time() . '_' . uniqid() . '.' . $ekstensi;
                if (move_uploaded_file($tmp_file, '../assets/' . $nama_file_baru)) {
                    if (!empty($data_event['poster'])) {
                        $path_lama = "../assets/" . $data_event['poster'];
                        if (file_exists($path_lama)) {
                            unlink($path_lama);
                        }
                    }
                    $poster_final = $nama_file_baru;
                } else {
                    $error = "Gagal mengunggah file gambar poster baru.";
                }
            } else {
                $error = "Ukuran file terlalu besar, maksimal berukuran 5MB.";
            }
        } else {
            $error = "Ekstensi file tidak valid. Hanya JPG, JPEG, PNG, dan WEBP.";
        }
    }

    if (empty($error)) {
        $update_event = mysqli_query($conn, "
            UPDATE event 
            SET nama_event = '$nama_event', deskripsi = '$deskripsi', tanggal = '$tanggal', genre = '$genre', lokasi = '$lokasi', poster = '$poster_final'
            WHERE id_event = '$id_event'
        ");

        if ($update_event) {
            
            
            $id_dipertahankan = [];
            foreach ($arr_id_kategori as $id_kat_form) {
                if (!empty($id_kat_form)) {
                    $id_dipertahankan[] = "'".mysqli_real_escape_string($conn, $id_kat_form)."'";
                }
            }

            
            if (!empty($id_dipertahankan)) {
                $list_id_aman = implode(',', $id_dipertahankan);
                try {
                    mysqli_query($conn, "DELETE FROM kategori WHERE id_event = '$id_event' AND id_kategori NOT IN ($list_id_aman)");
                } catch (mysqli_sql_exception $e) {
                    $error = "Beberapa kategori tidak bisa dihapus karena tiketnya sudah terjual ke pembeli!";
                }
            }

            
            foreach ($arr_nama_kategori as $index => $nama_kat) {
                $nama_kat_clean = mysqli_real_escape_string($conn, $nama_kat);
                $harga_clean = intval($arr_harga[$index]);
                $kuota_clean = intval($arr_kuota_total[$index]);
                $id_kat_saat_ini = isset($arr_id_kategori[$index]) ? mysqli_real_escape_string($conn, $arr_id_kategori[$index]) : '';
                
                if (!empty($nama_kat_clean)) {
                    if (!empty($id_kat_saat_ini)) {
                        
                        $query_cek_kuota = mysqli_query($conn, "SELECT kuota_total, kuota_sisa FROM kategori WHERE id_kategori = '$id_kat_saat_ini'");
                        $data_kuota_lama = mysqli_fetch_assoc($query_cek_kuota);
                        
                        $terjual = $data_kuota_lama['kuota_total'] - $data_kuota_lama['kuota_sisa'];
                        $kuota_sisa_baru = $kuota_clean - $terjual;
                        if($kuota_sisa_baru < 0) $kuota_sisa_baru = 0; 

                        mysqli_query($conn, "
                            UPDATE kategori 
                            SET nama_kategori = '$nama_kat_clean', harga = '$harga_clean', kuota_total = '$kuota_clean', kuota_sisa = '$kuota_sisa_baru'
                            WHERE id_kategori = '$id_kat_saat_ini'
                        ");
                    } else {
                        
                        mysqli_query($conn, "
                            INSERT INTO kategori (id_event, nama_kategori, harga, kuota_total, kuota_sisa)
                            VALUES ('$id_event', '$nama_kat_clean', '$harga_clean', '$kuota_clean', '$kuota_clean')
                        ");
                    }
                }
            }

            if (empty($error)) {
                $sukses = "Seluruh perubahan data event dan kategori berhasil diperbarui.";
            }
            
            $query_event = mysqli_query($conn, "SELECT * FROM event WHERE id_event = '$id_event'");
            $data_event = mysqli_fetch_assoc($query_event);
        } else {
            $error = "Gagal memperbarui data event ke database.";
        }
    }
}

$query_kategori = mysqli_query($conn, "SELECT * FROM kategori WHERE id_event = '$id_event'");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event - KonserKita</title>
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
        <div>
            <a href="event_tampil.php" class="inline-block text-xs font-bold text-[#1A2E26]/50 hover:text-[#1A2E26] mb-3 transition-colors">
                <i class="fa-solid fa-arrow-left text-[#D4A373] mr-1"></i> Kembali ke Daftar Event
            </a>
            <h2 class="text-2xl md:text-3xl font-black">Edit Perubahan Event</h2>
            <p class="text-xs md:text-sm text-[#1A2E26]/60">Modifikasi informasi pertunjukan konser musik beserta daftar kategori tiketnya.</p>
        </div>

        <div class="bg-white border border-[#EAE3D2] p-5 md:p-8 rounded-2xl md:rounded-3xl shadow-sm">
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

            <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6">
                    <div class="space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-[#1A2E26]/60">Nama Event / Konser</label>
                        <input type="text" name="nama_event" required value="<?php echo htmlspecialchars($data_event['nama_event']); ?>" class="w-full bg-[#FDFBF7] border border-[#EAE3D2] rounded-xl px-4 py-3 text-sm font-medium focus:outline-none focus:border-[#D4A373]">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-[#1A2E26]/60">Lokasi / Venue</label>
                        <input type="text" name="lokasi" required value="<?php echo htmlspecialchars($data_event['lokasi']); ?>" class="w-full bg-[#FDFBF7] border border-[#EAE3D2] rounded-xl px-4 py-3 text-sm font-medium focus:outline-none focus:border-[#D4A373]">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-[#1A2E26]/60">Tanggal Pelaksanaan</label>
                        <input type="date" name="tanggal" required value="<?php echo htmlspecialchars($data_event['tanggal']); ?>" class="w-full bg-[#FDFBF7] border border-[#EAE3D2] rounded-xl px-4 py-3 text-sm font-medium focus:outline-none focus:border-[#D4A373]">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-[#1A2E26]/60">Genre Musik</label>
                        <select name="genre" required class="w-full bg-[#FDFBF7] border border-[#EAE3D2] rounded-xl px-4 py-3 text-sm font-medium focus:outline-none focus:border-[#D4A373]">
                            <option value="Pop" <?php echo $data_event['genre'] == 'Pop' ? 'selected' : ''; ?>>Pop</option>
                            <option value="Rock" <?php echo $data_event['genre'] == 'Rock' ? 'selected' : ''; ?>>Rock</option>
                            <option value="Metal" <?php echo $data_event['genre'] == 'Metal' ? 'selected' : ''; ?>>Metal</option>
                            <option value="Jazz" <?php echo $data_event['genre'] == 'Jazz' ? 'selected' : ''; ?>>Jazz</option>
                            <option value="EDM" <?php echo $data_event['genre'] == 'EDM' ? 'selected' : ''; ?>>EDM</option>
                        </select>
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-[#1A2E26]/60">Poster Saat Ini</label>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 bg-[#FDFBF7] p-3 border border-[#EAE3D2] rounded-xl">
                            <img src="../assets/<?php echo !empty($data_event['poster']) ? htmlspecialchars($data_event['poster']) : 'default-poster.jpg'; ?>" class="w-16 h-20 object-cover rounded-lg border border-[#EAE3D2] shadow-sm shrink-0 mx-auto sm:mx-0">
                            <input type="file" name="poster" class="w-full flex-1 text-xs font-medium focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#1A2E26] file:text-white file:cursor-pointer file:hover:bg-[#1A2E26]/90">
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-[#1A2E26]/60">Deskripsi Event</label>
                    <textarea name="deskripsi" rows="4" required class="w-full bg-[#FDFBF7] border border-[#EAE3D2] rounded-xl px-4 py-3 text-sm font-medium focus:outline-none focus:border-[#D4A373] resize-none"><?php echo htmlspecialchars($data_event['deskripsi']); ?></textarea>
                </div>

                <div class="border-t border-[#EAE3D2]/60 pt-6 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                        <h3 class="font-extrabold text-base text-[#1A2E26] flex items-center gap-2"><i class="fa-solid fa-ticket text-[#D4A373]"></i> Kelola Kategori Tiket</h3>
                        <button type="button" id="btn-tambah-kategori" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl flex items-center justify-center gap-1 transition-all w-full sm:w-auto">
                            <i class="fa-solid fa-plus"></i> Tambah Kategori
                        </button>
                    </div>

                    <div id="container-kategori" class="space-y-4">
                        <?php 
                        $is_first = true;
                        while ($kat = mysqli_fetch_assoc($query_kategori)) { 
                        ?>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-[#FDFBF7] p-4 rounded-2xl border border-[#EAE3D2] items-end relative row-kategori pt-12 md:pt-4">
                            
                            <input type="hidden" name="id_kategori[]" value="<?php echo $kat['id_kategori']; ?>">

                            <div class="space-y-2">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-[#1A2E26]/60">Nama Kategori</label>
                                <input type="text" name="nama_kategori[]" required value="<?php echo htmlspecialchars($kat['nama_kategori']); ?>" class="w-full bg-white border border-[#EAE3D2] rounded-xl px-3 py-2.5 text-xs font-medium focus:outline-none focus:border-[#D4A373]">
                            </div>
                            <div class="space-y-2">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-[#1A2E26]/60">Harga Tiket (Rp)</label>
                                <input type="number" name="harga[]" required min="0" value="<?php echo htmlspecialchars($kat['harga']); ?>" class="w-full bg-white border border-[#EAE3D2] rounded-xl px-3 py-2.5 text-xs font-medium focus:outline-none focus:border-[#D4A373]">
                            </div>
                            <div class="space-y-2 relative">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-[#1A2E26]/60">Kuota Total</label>
                                <input type="number" name="kuota_total[]" required min="0" value="<?php echo htmlspecialchars($kat['kuota_total']); ?>" class="w-full bg-white border border-[#EAE3D2] rounded-xl px-3 py-2.5 text-xs font-medium focus:outline-none focus:border-[#D4A373]">
                                
                                <?php if (!$is_first) { ?>
                                <button type="button" class="btn-hapus-row absolute top-[-215px] right-0 md:top-2.5 md:-right-2 w-8 h-8 bg-rose-50 text-rose-600 border border-rose-200 rounded-lg flex items-center justify-center hover:bg-rose-100 transition-colors shadow-sm">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                                <?php } ?>
                            </div>
                        </div>
                        <?php 
                            $is_first = false;
                        } 
                        ?>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" name="update" class="w-full md:w-auto md:float-right bg-[#1A2E26] hover:bg-[#1A2E26]/90 text-white px-8 py-3.5 rounded-xl font-bold text-sm tracking-wide transition-all shadow-md">
                        Simpan Perubahan Event
                    </button>
                    <div class="clear-both"></div>
                </div>
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

        
        document.getElementById('btn-tambah-kategori').addEventListener('click', function() {
            const container = document.getElementById('container-kategori');
            const newRow = document.createElement('div');
            newRow.className = 'grid grid-cols-1 md:grid-cols-3 gap-4 bg-[#FDFBF7] p-4 rounded-2xl border border-[#EAE3D2] items-end relative row-kategori pt-12 md:pt-4';
            newRow.innerHTML = `
                <input type="hidden" name="id_kategori[]" value="">
                
                <div class="space-y-2">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-[#1A2E26]/60">Nama Kategori</label>
                    <input type="text" name="nama_kategori[]" required placeholder="Contoh: Festival" class="w-full bg-white border border-[#EAE3D2] rounded-xl px-3 py-2.5 text-xs font-medium focus:outline-none focus:border-[#D4A373]">
                </div>
                <div class="space-y-2">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-[#1A2E26]/60">Harga Tiket (Rp)</label>
                    <input type="number" name="harga[]" required min="0" placeholder="Contoh: 350000" class="w-full bg-white border border-[#EAE3D2] rounded-xl px-3 py-2.5 text-xs font-medium focus:outline-none focus:border-[#D4A373]">
                </div>
                <div class="space-y-2 relative">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-[#1A2E26]/60">Kuota Total</label>
                    <input type="number" name="kuota_total[]" required min="0" placeholder="Contoh: 150" class="w-full bg-white border border-[#EAE3D2] rounded-xl px-3 py-2.5 text-xs font-medium focus:outline-none focus:border-[#D4A373]">
                    <button type="button" class="btn-hapus-row absolute top-[-215px] right-0 md:top-2.5 md:-right-2 w-8 h-8 bg-rose-50 text-rose-600 border border-rose-200 rounded-lg flex items-center justify-center hover:bg-rose-100 transition-colors shadow-sm">
                        <i class="fa-solid fa-trash-can text-xs"></i>
                    </button>
                </div>
            `;
            container.appendChild(newRow);

            newRow.querySelector('.btn-hapus-row').addEventListener('click', function() {
                newRow.remove();
            });
        });

        
        document.querySelectorAll('.btn-hapus-row').forEach(button => {
            button.addEventListener('click', function() {
                button.closest('.row-kategori').remove();
            });
        });
    </script>
</body>
</html>