<?php
header('Content-Type: application/json');
require_once 'koneksi.php';
require_once "constants.php";

// Ambil input JSON
$input = json_decode(file_get_contents('php://input'), true);

// Validasi input
if (
    !isset($input['nip']) || !is_array($input['nip']) || empty($input['nip']) ||
    !isset($input['bulan']) || !isset($input['tahun'])
) {
    http_response_code(400);
    echo json_encode(['error' => 'Parameter tidak lengkap']);
    exit;
}

$nipList = $input['nip'];
$bulan = (int)$input['bulan'];
$tahun = (int)$input['tahun'];

// Escape semua NIP
$escapedNip = array_map(function ($nip) use ($koneksi) {
    return "'" . mysqli_real_escape_string($koneksi, $nip) . "'";
}, $nipList);

// Gabungkan untuk klausa IN
$nipInClause = implode(',', $escapedNip);

// Bangun query
$query = "
    SELECT absen_id, nip_baru, bulan, tahun, prosentase, tgl_impor, jam_impor
    FROM $database_skp.absen
    WHERE nip_baru IN ($nipInClause)
      AND bulan = $bulan
      AND tahun = $tahun
";

// Eksekusi query
$result = mysqli_query($koneksi, $query);

// Cek error query
if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Query gagal: ' . mysqli_error($koneksi)]);
    exit;
}

// Ambil data
$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

// Output JSON
echo json_encode([
    'success' => true,
    'data' => $data
]);
