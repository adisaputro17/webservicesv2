<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    require_once "koneksi.php";
    require_once "constants.php";
    require_once "library.php";

    $bulan = "bulan" . $_POST['bln'];

    // ubah status_realisasi_kinerja ke Persetujuan Atasan, Setuju, Menerima
    $cek_sql = mysqli_query($koneksi, "SELECT * FROM $database_skp.status_realisasi_kinerja WHERE pegawai_id='$_POST[pegawai_id]' 
                    AND informasi_id='$_POST[informasi_id]' AND bulan='$_POST[bln]'");
    $ketemu = mysqli_num_rows($cek_sql);
    $w = mysqli_fetch_array($cek_sql);
    if ($ketemu > 0) {
        mysqli_query($koneksi, "UPDATE $database_skp.status_realisasi_kinerja SET 
                        status_realisasi='Persetujuan Atasan',
                        verifikasi='Setuju',
                        banding='Menerima',
                        tgl_edit_atasan='$tgl_sekarang',
                        jam_edit_atasan='$jam_sekarang' 
                    WHERE pegawai_id = '$_POST[pegawai_id]' AND informasi_id='$_POST[informasi_id]' AND bulan='$_POST[bln]'");
    } else {
        mysqli_query($koneksi, "INSERT INTO $database_skp.status_realisasi_kinerja(
                        pegawai_id,
                        informasi_id,
                        bulan,
                        status_realisasi,
                        verifikasi,
                        banding,
                        tgl_kirim,
                        jam_kirim,
                        tgl_edit_atasan,
                        jam_edit_atasan) 
                    VALUES (
                        '$_POST[pegawai_id]',
                        '$_POST[informasi_id]',
                        '$_POST[bln]',
                        'Persetujuan Atasan',
                        'Setuju',
                        'Menerima',
                        '$tgl_sekarang',
                        '$jam_sekarang',
                        '$tgl_sekarang',
                        '$jam_sekarang')");
    }

    // isi rwyt_hasil_kinerja_bulan_wpt, rwyt_hasil_kinerja_bulan_ak, status_hasil_kinerja_bulan
    $cek = mysqli_query($koneksi, "SELECT * FROM $database_skp.rwyt_hasil_kinerja_bulan_wpt 
                    WHERE pegawai_id='$_POST[pegawai_id]' AND informasi_id='$_POST[informasi_id]'");
    $ketemu1 = mysqli_num_rows($cek);
    if ($ketemu1 > 0) {
        mysqli_query($koneksi, "UPDATE $database_skp.rwyt_hasil_kinerja_bulan_wpt SET 
                        $bulan = '$_POST[total_wpt]' 
                    WHERE pegawai_id='$_POST[pegawai_id]' AND informasi_id='$_POST[informasi_id]' AND tahun='$tahun'");

        mysqli_query($koneksi, "UPDATE $database_skp.rwyt_hasil_kinerja_bulan_ak SET 
                        $bulan = '$_POST[total_angka_kredit]' 
                    WHERE pegawai_id='$_POST[pegawai_id]' AND informasi_id='$_POST[informasi_id]' AND tahun='$tahun'");

        mysqli_query($koneksi, "UPDATE $database_skp.status_hasil_kinerja_bulan SET 
                        $bulan = 'Y' 
                    WHERE pegawai_id='$_POST[pegawai_id]' AND informasi_id='$_POST[informasi_id]' AND tahun='$tahun'");
    } else {
        mysqli_query($koneksi, "INSERT INTO $database_skp.rwyt_hasil_kinerja_bulan_wpt (
                        pegawai_id,
                        informasi_id,
                        tahun,
                        $bulan)
                    VALUES(
                        '$_POST[pegawai_id]',
                        '$_POST[informasi_id]',
                        '$tahun',
                        '$_POST[total_wpt]')");

        mysqli_query($koneksi, "INSERT INTO $database_skp.rwyt_hasil_kinerja_bulan_ak (
                        pegawai_id,
                        informasi_id,
                        tahun,
                        $bulan)
                    VALUES(
                        '$_POST[pegawai_id]',
                        '$_POST[informasi_id]',
                        '$tahun',
                        '$_POST[total_angka_kredit]')");
    }

    // isi rwyt_hasil_kinerja_bulan, status_hasil_kinerja_bulan
    $cek = mysqli_query($koneksi, "SELECT * FROM $database_skp.rwyt_hasil_kinerja_bulan WHERE pegawai_id='$_POST[pegawai_id]' AND informasi_id='$_POST[informasi_id]'");
    $ketemu = mysqli_num_rows($cek);
    if ($ketemu > 0) {
        mysqli_query($koneksi, "UPDATE $database_skp.rwyt_hasil_kinerja_bulan SET 
                        $bulan = '$_POST[total_nilai_rata_rata_kinerja]'
                    WHERE pegawai_id='$_POST[pegawai_id]' AND informasi_id='$_POST[informasi_id]' AND tahun='$tahun'");
    } else {
        mysqli_query($koneksi, "INSERT INTO $database_skp.rwyt_hasil_kinerja_bulan (
                        pegawai_id,
                        informasi_id,
                        tahun,
                        $bulan)
                    VALUES(
                        '$_POST[pegawai_id]',
                        '$_POST[informasi_id]',
                        '$tahun',
                        '$_POST[total_nilai_rata_rata_kinerja]')");

        mysqli_query($koneksi, "INSERT INTO $database_skp.status_hasil_kinerja_bulan (
                        pegawai_id,
                        informasi_id,
                        tahun,
                        $bulan)
                    VALUES(
                        '$_POST[pegawai_id]',
                        '$_POST[informasi_id]',
                        '$tahun',
                        'Y')");
    }

    // isi rwyt_predikat_asn
    $cek = mysqli_query($koneksi, "SELECT * FROM $database_skp.rwyt_predikat_asn 
                    WHERE pegawai_id='$_POST[pegawai_id]' AND informasi_id='$_POST[informasi_id]' AND bulan='$_POST[bln]'");
    $ketemu = mysqli_num_rows($cek);

    if (($_POST['nilai_perilaku'] > 120) or ($_POST['nilai_hasil_kerja'] > 120)) {
        echo json_encode([
            "success" => false,
            "error" => "Nilai perilaku atau nilai hasil kerja terlalu besar"
        ]);
    }
    if (($_POST['nilai_perilaku'] >= 110) and ($_POST['nilai_perilaku'] <= 120)) {
        $perilaku = 'Diatas Ekspetasi';
    }
    if (($_POST['nilai_perilaku'] >= 90) and ($_POST['nilai_perilaku'] <= 109)) {
        $perilaku = 'Sesuai Ekspetasi';
    }
    if ($_POST['nilai_perilaku'] < 90) {
        $perilaku = 'Dibawah Ekspetasi';
    }

    if (($_POST['nilai_hasil_kerja'] >= 110) and ($_POST['nilai_hasil_kerja'] <= 120)) {
        $kerja = 'Diatas Ekspetasi';
    }
    if (($_POST['nilai_hasil_kerja'] >= 90) and ($_POST['nilai_hasil_kerja'] <= 109)) {
        $kerja = 'Sesuai Ekspetasi';
    }
    if ($_POST['nilai_hasil_kerja'] < 90) {
        $kerja = 'Dibawah Ekspetasi';
    }

    $nilai = ($_POST['nilai_perilaku'] + $_POST['nilai_hasil_kerja']) / 2;
    if ($nilai >= 110) {
        $predikat = 'Sangat Baik';
    }
    if (($nilai >= 90) and ($nilai <= 109)) {
        $predikat = 'Baik';
    }
    if ($nilai < 90) {
        $predikat = 'Butuh Perbaikan';
    }


    if ($ketemu > 0) {
        mysqli_query($koneksi, "UPDATE $database_skp.rwyt_predikat_asn SET 
                        perilaku = '$_POST[nilai_perilaku]',
                        ekspetasi_perilaku = '$perilaku',
                        hasil_kerja = '$_POST[nilai_hasil_kerja]',
                        ekspetasi_hasil_kerja = '$kerja',
                        nilai_predikat = '$nilai',
                        predikat = '$predikat',
                        tgl_nilai = '$tgl_sekarang',
                        jam_nilai = '$jam_sekarang',
                        aktif='N'
                    WHERE pegawai_id='$_POST[pegawai_id]' AND informasi_id='$_POST[informasi_id]' AND bulan='$_POST[bln]'");

    } else {
        mysqli_query($koneksi, "INSERT INTO $database_skp.rwyt_predikat_asn(
                        pegawai_id,
                        informasi_id,
                        bulan,
                        perilaku,
                        ekspetasi_perilaku,
                        hasil_kerja,
                        ekspetasi_hasil_kerja,
                        nilai_predikat,
                        predikat,
                        tgl_nilai,
                        jam_nilai)
                    VALUES(
                        '$_POST[pegawai_id]',
                        '$_POST[informasi_id]',
                        '$_POST[bln]',
                        '$_POST[nilai_perilaku]',
                        '$perilaku',
                        '$_POST[nilai_hasil_kerja]',
                        '$kerja',
                        '$nilai',
                        '$predikat',
                        '$tgl_sekarang',
                        '$jam_sekarang')");
    }

    echo json_encode([
        "success" => true,
        "error" => ""
    ]);

} else {
    echo json_encode([
        "success" => false,
        "error" => "Metode request tidak valid"
    ]);
}


?>