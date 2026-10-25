<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_POST['id_booking']) && isset($_POST['aksi'])) {
    $id_booking = mysqli_real_escape_string($koneksi, $_POST['id_booking']);
    $id_mobil = mysqli_real_escape_string($koneksi, $_POST['id_mobil']);
    $aksi = $_POST['aksi'];

    if ($aksi == 'setuju') {
        mysqli_query($koneksi, "UPDATE booking SET status_transaksi = 'Telah Dikonfirmasi' WHERE id_booking = '$id_booking'");
        mysqli_query($koneksi, "UPDATE mobil SET status_mobil = 'Sedang Disewa' WHERE id_mobil = '$id_mobil'");
    } else if ($aksi == 'tolak') {
        mysqli_query($koneksi, "UPDATE booking SET status_transaksi = 'Ditolak' WHERE id_booking = '$id_booking'");
    }
    
    // Karena admin.php berada di dalam satu folder yang sama dengan verifikasi.php, biarkan seperti ini
    header("Location: admin.php");
    exit();
}
?>
