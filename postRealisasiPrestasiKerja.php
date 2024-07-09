<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    require_once "koneksi.php";
    require_once "constants.php";
    require_once "library.php";
    require_once "functions.php";

    $module = "kinerja_bulan";
    $act = "input";
    $field = "bulan" . $_POST['bln'];
    $lokasi_file = $_FILES['fskp']['tmp_name'];
    if (empty($lokasi_file)) {
        $cek_sql = mysqli_query($koneksi, "SELECT * FROM $database_skp.rwyt_realisasi_kuantitas WHERE pegawai_id='$_POST[pegawai_id]' 
                        AND breakdown_id='$_POST[breakdown_id]' AND target_id='$_POST[target_id]' AND informasi_id='$_POST[informasi_id]'");
        $ketemu = mysqli_num_rows($cek_sql);
        if ($ketemu > 0) {
            $tampil_file = mysqli_query($koneksi, "SELECT $field FROM $database_skp.rwyt_realisasi_file WHERE pegawai_id='$_POST[pegawai_id]' AND breakdown_id='$_POST[breakdown_id]'");
            $s = mysqli_fetch_array($tampil_file);
            $cek = mysqli_num_rows($tampil_file);
            if ($cek > 0) {
                if ($s[$field] == '') {
                    echo json_encode([
                        "success" => false,
                        "error" => "File belum ditambahkan"
                    ]);
                } else {
                    mysqli_query($koneksi, "UPDATE $database_skp.rwyt_realisasi_kuantitas SET 
                                    $field = '$_POST[kuantitas]',
                                    tgl_edit = '$tgl_sekarang',
                                    jam_edit = '$jam_sekarang' 
                                    WHERE pegawai_id='$_POST[pegawai_id]' AND target_id='$_POST[target_id]' AND informasi_id='$_POST[informasi_id]'");

                    mysqli_query($koneksi, "UPDATE $database_skp.rwyt_realisasi_kualitas SET 
                                    $field = '$_POST[kualitas]',
                                    tgl_edit = '$tgl_sekarang',
                                    jam_edit = '$jam_sekarang' 
                                    WHERE pegawai_id='$_POST[pegawai_id]' AND target_id='$_POST[target_id]' AND informasi_id='$_POST[informasi_id]'");

                    mysqli_query($koneksi, "UPDATE $database_skp.rwyt_realisasi_biaya SET 
                                    $field = '$_POST[biaya]',
                                    tgl_edit = '$tgl_sekarang',
                                    jam_edit = '$jam_sekarang' 
                                    WHERE pegawai_id='$_POST[pegawai_id]' AND target_id='$_POST[target_id]' AND informasi_id='$_POST[informasi_id]'");

                    mysqli_query($koneksi, "UPDATE $database_skp.rwyt_realisasi_link_bukti SET 
                                    $field = '$_POST[link]',
                                    tgl_edit = '$tgl_sekarang',
                                    jam_edit = '$jam_sekarang'
                                    WHERE pegawai_id='$_POST[pegawai_id]' AND target_id='$_POST[target_id]' AND informasi_id='$_POST[informasi_id]'");

                    echo json_encode([
                        "success" => true,
                        "error" => ""
                    ]);
                }
            }
        } else {
            echo json_encode([
                "success" => false,
                "error" => "No informasi found"
            ]);
        }
    }

    if (!empty($lokasi_file)) {
        $str = $module;
        $lokasi_file = $_FILES['fskp']['tmp_name'];
        $nama_ok = str_replace("'", "", $_FILES['fskp']['name']);
        $nama_file = $_POST['pegawai_id'] . "-" . $str . "-" . $tgl_sekarang . "-" . rand(1, 1000) . "-" . $file . preg_replace("/\s+/", "_", $nama_ok);
        //echo "$nama_file";
        $extractFile = pathinfo($nama_file['name']);  // Extract nama file
        $size = $_FILES['fskp']['size']; //untuk mengetahui ukuran file
        $tipe = $_FILES['fskp']['type'];// untuk mengetahui tipe file
        $exts = array('application/pdf');
        if (!in_array(($tipe), $exts)) {
            echo json_encode([
                "success" => false,
                "error" => "Format file tidak diizinkan"
            ]);
        }
        if (($size != 0) && ($size > 1000000)) {
            echo json_encode([
                "success" => false,
                "error" => "Ukuran file terlalu besar"
            ]);
        }

        $tampil_file = mysqli_query($koneksi, "SELECT $field FROM $database_skp.rwyt_realisasi_file WHERE pegawai_id='$_POST[pegawai_id]' AND breakdown_id='$_POST[breakdown_id]'");
        $s = mysqli_fetch_array($tampil_file);
        $cek = mysqli_num_rows($tampil_file);
        if ($cek > 0) {
            if ($s[$field] <> '') {
                unlink("../$folder_files_skp/$s[$field]");
            }
            uploadSKP($folder_files_skp, $nama_file);

            mysqli_query($koneksi, "UPDATE $database_skp.rwyt_realisasi_file SET 
                            $field = '$nama_file', 
                            tgl_edit = '$tgl_sekarang', 
                            jam_edit = '$jam_sekarang' 
                            WHERE pegawai_id='$_POST[pegawai_id]' AND target_id='$_POST[target_id]' AND informasi_id='$_POST[informasi_id]' AND breakdown_id='$_POST[breakdown_id]'");
        } else {
            uploadSKP($folder_files_skp, $nama_file);
            mysqli_query($koneksi, "INSERT INTO $database_skp.rwyt_realisasi_file(pegawai_id, 
                            breakdown_id,
                            target_id,
                            informasi_id,
                            $field,
                            tgl_edit,
                            jam_edit) 
                        VALUES ('$_POST[pegawai_id]',
                            '$_POST[breakdown_id]',
                            '$_POST[target_id]',
                            '$_POST[informasi_id]',
                            '$nama_file',
                            '$tgl_sekarang',
                            '$jam_sekarang')");

        }

        $cek_sql = mysqli_query($koneksi, "SELECT * FROM $database_skp.rwyt_realisasi_kuantitas WHERE pegawai_id='$_POST[pegawai_id]' 
                        AND breakdown_id='$_POST[breakdown_id]' AND target_id='$_POST[target_id]' AND informasi_id='$_POST[informasi_id]'");
        $ketemu = mysqli_num_rows($cek_sql);
        if ($ketemu > 0) {

            mysqli_query($koneksi, "UPDATE $database_skp.rwyt_realisasi_kuantitas SET 
                            $field = '$_POST[kuantitas]', 
                            tgl_edit = '$tgl_sekarang', 
                            jam_edit = '$jam_sekarang' 
                            WHERE pegawai_id='$_POST[pegawai_id]' AND target_id='$_POST[target_id]' AND informasi_id='$_POST[informasi_id]'");

            mysqli_query($koneksi, "UPDATE $database_skp.rwyt_realisasi_kualitas SET 
                            $field = '$_POST[kualitas]',
                            tgl_edit = '$tgl_sekarang',
                            jam_edit = '$jam_sekarang' 
                            WHERE pegawai_id='$_POST[pegawai_id]' AND target_id='$_POST[target_id]' AND informasi_id='$_POST[informasi_id]'");

            mysqli_query($koneksi, "UPDATE $database_skp.rwyt_realisasi_biaya SET 
                            $field = '$_POST[biaya]', 
                            tgl_edit = '$tgl_sekarang', 
                            jam_edit = '$jam_sekarang' 
                            WHERE pegawai_id='$_POST[pegawai_id]' AND target_id='$_POST[target_id]' AND informasi_id='$_POST[informasi_id]'");

            mysqli_query($koneksi, "UPDATE $database_skp.rwyt_realisasi_link_bukti SET 
                            $field = '$_POST[link]', 
                            tgl_edit = '$tgl_sekarang', 
                            jam_edit = '$jam_sekarang' 
                            WHERE pegawai_id='$_POST[pegawai_id]' AND target_id='$_POST[target_id]' AND informasi_id='$_POST[informasi_id]'");

            echo json_encode([
                "success" => true,
                "error" => ""
            ]);
        } else {
            mysqli_query($koneksi, "INSERT INTO $database_skp.rwyt_realisasi_kuantitas(pegawai_id, 
                            breakdown_id, 
                            target_id, 
                            informasi_id, 
                            $field, 
                            tgl_edit, 
                            jam_edit) 
                        VALUES ('$_POST[pegawai_id]',
                            '$_POST[breakdown_id]',
                            '$_POST[target_id]',
                            '$_POST[informasi_id]',
                            '$_POST[kuantitas]',
                            '$tgl_sekarang',
                            '$jam_sekarang')");

            mysqli_query($koneksi, "INSERT INTO $database_skp.rwyt_realisasi_kualitas(pegawai_id,
                            breakdown_id,
                            target_id,
                            informasi_id,
                            $field,
                            tgl_edit,
                            jam_edit) 
                        VALUES ('$_POST[pegawai_id]',
                            '$_POST[breakdown_id]',
                            '$_POST[target_id]',
                            '$_POST[informasi_id]',
                            '$_POST[kualitas]',
                            '$tgl_sekarang',
                            '$jam_sekarang')");

            mysqli_query($koneksi, "INSERT INTO $database_skp.rwyt_realisasi_biaya(pegawai_id,
                            breakdown_id,
                            target_id,
                            informasi_id,
                            $field,
                            tgl_edit,
                            jam_edit) 
                        VALUES ('$_POST[pegawai_id]',
                            '$_POST[breakdown_id]',
                            '$_POST[target_id]',
                            '$_POST[informasi_id]',
                            '$_POST[biaya]',
                            '$tgl_sekarang',
                            '$jam_sekarang')");

            mysqli_query($koneksi, "INSERT INTO $database_skp.rwyt_realisasi_link_bukti(pegawai_id,
                            breakdown_id,
                            target_id,
                            informasi_id,
                            $field,
                            tgl_edit,
                            jam_edit) 
                        VALUES ('$_POST[pegawai_id]',
                            '$_POST[breakdown_id]',
                            '$_POST[target_id]',
                            '$_POST[informasi_id]',
                            '$_POST[link]',
                            '$tgl_sekarang',
                            '$jam_sekarang')");

            echo json_encode([
                "success" => true,
                "error" => ""
            ]);
        }
    }

    //log
    mysqli_query($koneksi, "INSERT INTO $database_skp.log_skp (pegawai_id, 
                    modul, 
                    aksi, 
                    tgl, 
                    jam)
                VALUES('$_POST[pegawai_id]',
                    '$module',
                    '$act',
                    '$tgl_sekarang',
                    '$jam_sekarang')");

} else {
    echo json_encode([
        "success" => false,
        "error" => "Metode request tidak valid"
    ]);
}
?>
