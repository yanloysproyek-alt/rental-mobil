<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// ========================================================
// LOGIKA PERBAIKAN: HAPUS TRANSAKSI & KEMBALIKAN MOBIL JADI TERSEDIA
// ========================================================
if (isset($_GET['hapus_booking'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus_booking']);
    
    // 1. Cari tahu ID Mobil dan berkas gambar dari data booking yang mau dihapus
    $cek_data = mysqli_query($koneksi, "SELECT id_mobil, foto_ktp, bukti_transfer FROM booking WHERE id_booking = '$id_hapus'");
    $data_transaksi = mysqli_fetch_assoc($cek_data);
    
    if ($data_transaksi) {
        $id_mobil_kembali = $data_transaksi['id_mobil'];
        
        // 2. KEMBALIKAN STATUS MOBIL MENJADI 'Tersedia' AGAR MUNCUL LAGI DI MENU BOOKING
        mysqli_query($koneksi, "UPDATE mobil SET status_mobil = 'Tersedia' WHERE id_mobil = '$id_mobil_kembali'");
        
        // 3. Hapus file fisik gambar KTP & Bukti Transfer di folder uploads laptop
        @unlink("../uploads/" . $data_transaksi['foto_ktp']);
        @unlink("../uploads/" . $data_transaksi['bukti_transfer']);
    }

    // 4. Baru eksekusi hapus data booking dari database
    mysqli_query($koneksi, "DELETE FROM booking WHERE id_booking = '$id_hapus'");
    header("Location: admin.php?pesan=terhapus");
    exit();
}

$hitung = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM booking WHERE status_transaksi = 'Menunggu Konfirmasi'"));
$jumlah_baru = $hitung['total'];

$query_booking = mysqli_query($koneksi, "SELECT booking.*, pelanggan.nama AS nama_penyewa, mobil.merk_mobil, mobil.plat_nomor FROM booking JOIN pelanggan ON booking.id_pelanggan = pelanggan.id_pelanggan JOIN mobil ON booking.id_mobil = mobil.id_mobil ORDER BY booking.id_booking DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Panel Admin</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; padding: 20px; }
        .wrapper { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
        .notif { background: #fff3cd; color: #856404; padding: 12px; margin-bottom: 15px; font-weight: bold; border-radius: 4px; }
        .alert-sukses { background: #d4edda; color: #155724; padding: 12px; margin-bottom: 15px; font-weight: bold; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 13px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #f8f9fa; }
        .btn { padding: 5px 10px; text-decoration: none; color: white; border-radius: 4px; font-weight: bold; font-size: 12px; display: inline-block; text-align: center;}
        
        .btn-setuju { background: #28a745; border:none; cursor:pointer; color: white; }
        .btn-tolak { background: #dc3545; border:none; cursor:pointer; color: white; }
        .btn-hapus { background: #db3545; color: white; margin-top: 5px; }
        .img-zoom { width: 60px; height: auto; }
    </style>
</head>
<body>
<div class="wrapper">
    <h2>Panel Kendali Admin</h2>
    <a href="mobil-kelola.php" style="background:#17a2b8; color:white; padding:8px 12px; text-decoration:none; border-radius:4px; font-weight:bold;">🚗 Kelola Mobil</a> | 
    <a href="../export-booking-excel.php" style="background:#28a745; color:white; padding:8px 12px; text-decoration:none; border-radius:4px; font-weight:bold;">📥 Laporan Excel</a> | 
    <a href="../logout.php" style="background:#dc3545; color:white; padding:8px 12px; text-decoration:none; border-radius:4px; font-weight:bold;">Keluar</a>
    <br><br>

    <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'terhapus'): ?>
        <div class="alert-sukses">✅ Data transaksi berhasil dihapus, armada otomatis kembali berstatus TERSEDIA!</div>
    <?php endif; ?>

    <?php if ($jumlah_baru > 0): ?><div class="notif">🔔 Pemberitahuan: Ada <?php echo $jumlah_baru; ?> data booking baru belum divalidasi!</div><?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>No</th><th>Penyewa</th><th>Identitas</th><th>Mobil</th><th>Jadwal Sewa</th><th>Total</th><th>KTP</th><th>Bukti TF</th><th>Status</th><th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $n=1; while($row = mysqli_fetch_assoc($query_booking)): ?>
            <tr>
                <td><?php echo $n++; ?></td>
                <td><b><?php echo htmlspecialchars($row['nama_penyewa']); ?></b><br><?php echo $row['no_telp_pesanan']; ?></td>
                <td>NIK: <?php echo $row['nik_booking']; ?><br><small><?php echo $row['alamat_lengkap']; ?></small></td>
                <td><?php echo $row['merk_mobil']; ?><br><small><?php echo $row['plat_nomor']; ?></small></td>
                <td><small>A: <?php echo $row['tanggal_pengambilan']; ?><br>K: <?php echo $row['tanggal_pengembalian']; ?></small></td>
                <td><b>Rp <?php echo number_format($row['total_biaya'],0,',','.'); ?></b></td>
                <td><a href="../uploads/<?php echo $row['foto_ktp']; ?>" target="_blank"><img src="../uploads/<?php echo $row['foto_ktp']; ?>" class="img-zoom"></a></td>
                <td><a href="../uploads/<?php echo $row['bukti_transfer']; ?>" target="_blank"><img src="../uploads/<?php echo $row['bukti_transfer']; ?>" class="img-zoom"></a></td>
                <td><?php echo $row['status_transaksi']; ?></td>
                <td>
                    <?php if($row['status_transaksi'] == 'Menunggu Konfirmasi'): ?>
                        <form method="POST" action="verifikasi.php">
                            <input type="hidden" name="id_booking" value="<?php echo $row['id_booking']; ?>">
                            <input type="hidden" name="id_mobil" value="<?php echo $row['id_mobil']; ?>">
                            <button type="submit" name="aksi" value="setuju" class="btn btn-setuju">✔️ Setuju</button>
                            <button type="submit" name="aksi" value="tolak" class="btn btn-tolak">❌ Tolak</button>
                        </form>
                    <?php else: ?>
                        <span style="color:#aaa; font-style:italic;">Selesai</span>
                    <?php endif; ?>
                    
                    <a href="admin.php?hapus_booking=<?php echo $row['id_booking']; ?>" class="btn btn-hapus" onclick="return confirm('Hapus seluruh riwayat transaksi pelanggan ini?')">🗑️ Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
