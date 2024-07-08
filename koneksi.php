<?php
$server = "localhost";
$username = "root";
$password = "";
$database = "kotakediri_master";

// Koneksi dan memilih database di server
$koneksi = mysqli_connect($server, $username, $password) or die("Koneksi gagal");
mysqli_select_db($koneksi, $database) or die("Database tidak bisa dibuka");
?>
