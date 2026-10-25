<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_rental_mobil"; // Mengikuti nama database di phpMyAdmin Anda

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>