<?php
session_start();
include 'koneksi.php';

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 6;
$genre = isset($_GET['genre']) ? $_GET['genre'] : '';
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : '';
$id_user_login = isset($_SESSION['user']['id_user']) ? (int)$_SESSION['user']['id_user'] : 0;

$sql = "
    SELECT * FROM event 
    WHERE id_event NOT IN (
        SELECT DISTINCT kategori.id_event 
        FROM transaksi
        JOIN e_tiket ON transaksi.id_transaksi = e_tiket.id_transaksi
        JOIN kategori ON e_tiket.id_kategori = kategori.id_kategori
        WHERE transaksi.id_user = ? 
        AND transaksi.status IN ('paid', 'pending')
    )
";

$params = [$id_user_login];
$types = "i";

if (!empty($genre)) {
    $sql .= " AND genre = ?";
    $params[] = $genre;
    $types .= "s";
}

if (!empty($bulan)) {
    $sql .= " AND MONTH(tanggal) = ?";
    $params[] = $bulan;
    $types .= "i";
}

$stmt_total = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt_total, $types, ...$params);
mysqli_stmt_execute($stmt_total);
$query_total = mysqli_stmt_get_result($stmt_total);
$total_data = mysqli_num_rows($query_total);

$sql .= " LIMIT ?";
$params[] = $limit;
$types .= "i";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$query = mysqli_stmt_get_result($stmt);

$html = "";

if (mysqli_num_rows($query) > 0) {
    while ($event = mysqli_fetch_assoc($query)) {
        $id_event = (int)$event['id_event'];
        
        $query_stok = mysqli_query($conn, "SELECT SUM(kuota_sisa) as total_stok FROM kategori WHERE id_event = $id_event");
        $stok_data = mysqli_fetch_assoc($query_stok);
        $is_sold_out = ($stok_data['total_stok'] <= 0);
        
        if ($is_sold_out) {
            $img_class = 'grayscale brightness-75';
            $badge = '<div class="absolute top-3 right-3 bg-rose-600 text-white text-xs font-bold px-3 py-1.5 rounded-full border border-rose-700 shadow-sm">Sold Out</div>';
            $button = '<a href="user/detail_event.php?id=' . $id_event . '" class="w-full text-center bg-rose-600 hover:bg-rose-700 text-white px-4 py-3 rounded-xl font-bold text-sm inline-block transition-all duration-300">Tiket Habis (Detail)</a>';
        } else {
            $img_class = '';
            $badge = '<div class="absolute top-3 right-3 bg-[#FDFBF7]/90 backdrop-blur-md text-[#D4A373] text-xs font-bold px-3 py-1.5 rounded-full border border-[#EAE3D2]">Available</div>';
            $button = '<a href="user/detail_event.php?id=' . $id_event . '" class="w-full text-center bg-[#1A2E26] hover:bg-[#1A2E26]/90 text-white px-4 py-3 rounded-xl font-bold text-sm inline-block transition-all duration-300">Lihat Detail</a>';
        }

        $html .= '
        <div class="bg-[#F7F4EB] rounded-2xl overflow-hidden border border-[#EAE3D2]/60 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex flex-col h-full group">
            <div class="relative overflow-hidden">
                <img src="assets/' . htmlspecialchars($event['poster'], ENT_QUOTES, 'UTF-8') . '" class="w-full h-[240px] object-cover object-bottom group-hover:scale-105 duration-500 ' . $img_class . '">
                ' . $badge . '
            </div>
            <div class="p-5 flex flex-col flex-grow">
                <h2 class="text-xl font-bold text-[#1A2E26] mb-4 line-clamp-1 group-hover:text-[#D4A373] transition-colors duration-300">' . htmlspecialchars($event['nama_event'], ENT_QUOTES, 'UTF-8') . '</h2>
                <div class="space-y-2.5 mb-6 text-sm text-[#1A2E26]/70">
                    <p class="flex items-center gap-2.5"><i class="fa-regular fa-calendar-days text-[#D4A373] text-base w-4"></i> ' . date('d F Y', strtotime($event['tanggal'])) . '</p>
                    <p class="flex items-center gap-2.5"><i class="fa-solid fa-location-dot text-[#D4A373] text-base w-4"></i> ' . htmlspecialchars($event['lokasi'], ENT_QUOTES, 'UTF-8') . '</p>
                </div>
                <div class="mt-auto">
                    ' . $button . '
                </div>
            </div>
        </div>';
    }
} else {
    $html = '
    <div class="col-span-1 md:col-span-2 lg:col-span-3 text-center py-12 bg-[#F7F4EB] rounded-3xl border border-[#EAE3D2]/40 p-8">
        <i class="fa-regular fa-calendar-xmark text-4xl text-[#D4A373] mb-3 block"></i>
        <h3 class="text-lg font-bold text-[#1A2E26]">Tidak Ada Event Ditemukan</h3>
        <p class="text-sm text-[#1A2E26]/60 mt-1">Coba sesuaikan kombinasi filter genre atau bulan pelaksanaan pilihanmu.</p>
    </div>';
}

$is_all_loaded = ($limit >= $total_data);

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'html' => $html,
    'is_all_loaded' => $is_all_loaded
]);
?>