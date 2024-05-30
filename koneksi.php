<?php
$server = "localhost";
$username = "root";
$password = "";
$database = "kotakediri_master";

$database_skp = "kotakediri_skp_2024";
$tahun = 2024;
$folder_files_skp = "files_skp_2023";
$url_pusdasip = "https://pusdasip.kedirikota.go.id";

// Koneksi dan memilih database di server
$koneksi = mysqli_connect($server,$username,$password) or die("Koneksi gagal");
mysqli_select_db($koneksi, $database) or die("Database tidak bisa dibuka");
?>
