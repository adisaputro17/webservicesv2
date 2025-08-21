<?php
header('Content-Type: application/json');
require_once 'koneksi.php';
require_once "constants.php";

// Ambil input GET
$bulan_awal  = isset($_GET['bulan_awal']) ? intval($_GET['bulan_awal']) : date("m");
$bulan_akhir = isset($_GET['bulan_akhir']) ? intval($_GET['bulan_akhir']) : date("m");
// $tahun       = isset($_GET['tahun']) ? intval($_GET['tahun']) : date("Y");
$skpd_id     = isset($_GET['skpd_id']) ? $_GET['skpd_id'] : "";

// Validasi
if ($skpd_id == "") {
    echo json_encode([
        "error" => "skpd_id harus diisi"
    ]);
    exit;
}

// 1. Ambil SKPD dengan LIKE
$sql_skpd = "SELECT id FROM $database_skp.skpd WHERE id LIKE '" . mysqli_real_escape_string($koneksi, $skpd_id) . "%'";
$res_skpd = mysqli_query($koneksi, $sql_skpd);

$skpd_list = [];
while ($row = mysqli_fetch_assoc($res_skpd)) {
    $skpd_list[] = $row['id'];
}

if (count($skpd_list) == 0) {
    echo json_encode([
        "status" => "ok",
        "data" => []
    ]);
    exit;
}

// 2. Ambil biodata pegawai berdasarkan SKPD
$skpd_in = "'" . implode("','", $skpd_list) . "'";
$sql_biodata = "SELECT nip_baru, nama, skpd_id FROM biodata WHERE skpd_id IN ($skpd_in)";
$res_biodata = mysqli_query($koneksi, $sql_biodata);

$biodata_list = [];
$nip_list = [];
while ($row = mysqli_fetch_assoc($res_biodata)) {
    $biodata_list[$row['nip_baru']] = [
        "nip" => $row['nip_baru'],
        "nama" => $row['nama'],
        "skpd_id" => $row['skpd_id'],
        "absen" => [],
        "persentase_absen" => 0
    ];
    $nip_list[] = $row['nip_baru'];
}

if (count($nip_list) == 0) {
    echo json_encode([
        "status" => "ok",
        "data" => []
    ]);
    exit;
}

// 3. Ambil data absen
$nip_in = "'" . implode("','", $nip_list) . "'";
$sql_absen = "
    SELECT nip_baru, bulan, tahun, prosentase
    FROM $database_skp.absen
    WHERE nip_baru IN ($nip_in)
      AND tahun = $tahun
      AND bulan BETWEEN $bulan_awal AND $bulan_akhir
";
$res_absen = mysqli_query($koneksi, $sql_absen);

while ($row = mysqli_fetch_assoc($res_absen)) {
    $nip    = $row['nip_baru'];
    $bulan  = intval($row['bulan']);
    $prosen = floatval($row['prosentase']);
    $biodata_list[$nip]["absen"][$bulan] = $prosen;
}

// 4. hitung rata-rata per pegawai
foreach ($biodata_list as &$pegawai) {
    if (count($pegawai["absen"]) > 0) {
        $pegawai["persentase_absen"] = array_sum($pegawai["absen"]) / count($pegawai["absen"]);
    }
}

// 5. response
echo json_encode([
    "status"      => "ok",
    "bulan_awal"  => $bulan_awal,
    "bulan_akhir" => $bulan_akhir,
    "tahun"       => $tahun,
    "data"        => array_values($biodata_list)
], JSON_PRETTY_PRINT);

?>