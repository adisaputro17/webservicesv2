<?php
header('Content-Type: application/json');

$nip = $_GET["nip"];
// $bulan = $_GET["bulan"];
$bulan = "bulan" . $_GET["bulan"];

require_once "koneksi.php";
require_once "constants.php";

$response = [];

$sql = mysqli_query($koneksi, "SELECT * FROM biodata WHERE nip_baru='$nip'");
$j = mysqli_fetch_array($sql);

if (!$j) {
    $response['error'] = 'No biodata found';
    echo json_encode($response);
    return;
}

$sql = mysqli_query($koneksi, "SELECT * FROM $database_skp.rwyt_informasi WHERE pegawai_id='$j[pegawai_id]' AND tahun=$tahun AND aktif='Y' AND valid='Y'");
$l = mysqli_fetch_array($sql);

if (!$l) {
    $response['error'] = 'No informasi found';
    echo json_encode($response);
    return;
}

$response['pegawai_id'] = $j['pegawai_id'];
$response['nip'] = $j['nip_baru'];
$response['nama'] = $j['nama'];
$response['bulan'] = $_GET['bulan'];
$response['tahun'] = $tahun;

$status_sql = mysqli_query($koneksi, "SELECT * FROM $database_skp.status_realisasi_kinerja WHERE pegawai_id='$j[pegawai_id]' AND informasi_id='$l[informasi_id]' AND bulan='$_GET[bulan]'");
$status = mysqli_fetch_array($status_sql);
$ketemu = mysqli_num_rows($status_sql);

if ($ketemu > 0) {
    $response['status_realisasi'] = $status['status_realisasi'];
    $response['verifikasi_atasan'] = $status['verifikasi'];
    $response['status_asn_yang_dinilai'] = $status['banding'];
} else {
    $response['status_target'] = 'Draft';
}

$status_sql = mysqli_query($koneksi, "SELECT * FROM $database_skp.status_breakdown_target_kerja WHERE pegawai_id='$j[pegawai_id]' AND informasi_id='$l[informasi_id]' AND verifikasi='Setuju'");
$ketemu = mysqli_num_rows($status_sql);

if ($ketemu > 0) {
    include("getPrestasiKerjaDetail.php");
}

$response['detail'] = $kegiatan_tugas_jabatan;

$total_wp_bulan = $wp_bulan_utama1 + $wp_bulan_limpahan1 + $wp_bulan_penunjang1;
$total_ak_bulan = $ak_bulan_utama1 + $ak_bulan_limpahan1 + $ak_bulan_penunjang1;

$tampil_wp = mysqli_query($koneksi, "SELECT SUM(total_wp),SUM(angka_kredit) FROM $database_skp.rwyt_target_kerja_tahunan WHERE pegawai_id='$j[pegawai_id]' AND informasi_id='$l[informasi_id]'");
$wp = mysqli_fetch_array($tampil_wp);

if ($l['jabatan_id'] == '1') {
    $total4 = number_format($total_wp_bulan);
    $persen = ($total_wp_bulan / $wp[0]) * 100;
    $persen1 = round($persen, 2);
    $response['total_wpt_jenis_kegiatan_tupoksi'] = "$total4 Menit ($persen1%)";

    $total5 = number_format($biaya1);
    $response['total_biaya'] = "Rp. $total5";

    $totalak = $total_ak_bulan;
    $response['total_angka_kredit'] = $total_ak_bulan;
} else {
    $total4 = number_format($total_wp_bulan);
    $persen = ($total_wp_bulan / $wp[0]) * 100;
    $persen1 = round($persen, 2);
    $response['total_wpt_jenis_kegiatan_tupoksi'] = "$total4 Menit ($persen1%)";

    $totalak = $total_ak_bulan;
    $response['total_angka_kredit'] = $total_ak_bulan;
}

echo json_encode($response);
?>