<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['id_pelanggan']) || $_SESSION['role'] !== 'pelanggan') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "ID Transaksi tidak ditemukan.";
    exit();
}

$id_booking = mysqli_real_escape_string($koneksi, $_GET['id']);
$id_pelanggan = $_SESSION['id_pelanggan'];

// Ambil data status transaksi terbaru
$query = mysqli_query($koneksi, "SELECT booking.*, mobil.merk_mobil, mobil.plat_nomor 
                                 FROM booking 
                                 JOIN mobil ON booking.id_mobil = mobil.id_mobil 
                                 WHERE booking.id_booking = '$id_booking' AND booking.id_pelanggan = '$id_pelanggan'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "Data transaksi tidak ditemukan.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <!-- Halaman otomatis refresh setiap 5 detik -->
    <meta http-equiv="refresh" content="5">
    <title>Status Booking Anda</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .status-box { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 450px; text-align: center; }
        h2 { color: #333; margin-bottom: 10px; }
        .car-info { background: #e9ecef; padding: 15px; border-radius: 6px; margin: 20px 0; font-weight: bold; color: #495057; }
        .status-menunggu { background: #fff3cd; color: #856404; padding: 15px; border-radius: 6px; font-weight: bold; border: 1px solid #ffeeba; width: 100%; box-sizing: border-box; }
        .status-sukses { background: #d4edda; color: #155724; padding: 15px; border-radius: 6px; font-weight: bold; border: 1px solid #c3e6cb; width: 100%; box-sizing: border-box; }
        .status-ditolak { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 6px; font-weight: bold; border: 1px solid #f5c6cb; width: 100%; box-sizing: border-box; }
        .btn-nota { display: block; background: #28a745; color: white; padding: 12px; margin-top: 25px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 16px; }
        .btn-nota:hover { background: #218838; }
        .loading-icon { margin: 15px auto; width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #ffc107; border-radius: 50%; animation: spin 1s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
<div class="status-box">
    <h2>Status Transaksi</h2>
    <p>ID Transaksi: <strong>#RM-<?php echo $data['id_booking']; ?></strong></p>
    
    <div class="car-info">
        Mobil: <?php echo $data['merk_mobil']; ?> (<?php echo $data['plat_nomor']; ?>)
    </div>

    <?php if ($data['status_transaksi'] == 'Menunggu Konfirmasi'): ?>
        <div class="status-menunggu">
            ⏳ Menunggu Konfirmasi Booking dari Admin
        </div>
        <div class="loading-icon"></div>
        <p style="font-size: 13px; color: #6c757d;">Memeriksa data transaksi secara berkala. Mohon jangan ditutup...</p>

    <?php elseif ($data['status_transaksi'] == 'Telah Dikonfirmasi'): ?>
        <div class="status-sukses">
            ✅ Booking Telah Dikonfirmasi!
        </div>
        <p>Pembayaran valid. Nota resmi Anda telah terbit.</p>
        <a href="nota.php?id=<?php echo $data['id_booking']; ?>" target="_blank" class="btn-nota">📄 LIHAT & CETAK NOTA RESMI</a>

    <?php else: ?>
        <div class="status-ditolak">
            ❌ Booking Ditolak
        </div>
        <p>Unggahan berkas Anda tidak sesuai atau tidak valid.</p>
    <?php endif; ?>
</div>
</body>
</html>
