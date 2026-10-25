<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['id_pelanggan'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "ID Transaksi tidak ditemukan.";
    exit();
}

$id_booking = mysqli_real_escape_string($koneksi, $_GET['id']);
$id_pelanggan = $_SESSION['id_pelanggan'];

// Join query untuk menyusun detail nota
$query = mysqli_query($koneksi, "SELECT booking.*, pelanggan.nama, pelanggan.email, mobil.merk_mobil, mobil.plat_nomor 
                                 FROM booking 
                                 JOIN pelanggan ON booking.id_pelanggan = pelanggan.id_pelanggan
                                 JOIN mobil ON booking.id_mobil = mobil.id_mobil 
                                 WHERE booking.id_booking = '$id_booking' AND booking.id_pelanggan = '$id_pelanggan'");

$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "Transaksi tidak valid.";
    exit();
}

if ($data['status_transaksi'] != 'Telah Dikonfirmasi') {
    echo "Nota belum diterbitkan karena menunggu konfirmasi Admin.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Resmi Rental Mobil #<?php echo $data['id_booking']; ?></title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; background-color: #f4f4f4; padding: 20px; color: #333; }
        .nota-box { max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border: 2px dashed #333; }
        .header { text-align: center; border-bottom: 2px double #333; padding-bottom: 15px; margin-bottom: 20px; }
        .header h2 { margin: 0; text-transform: uppercase; letter-spacing: 2px; }
        .info-table, .detail-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px; }
        .info-table td { padding: 4px 0; }
        .detail-table th { border-bottom: 1px solid #333; border-top: 1px solid #333; padding: 8px; text-align: left; }
        .detail-table td { padding: 10px 8px; border-bottom: 1px dashed #ddd; }
        .total-row { font-size: 18px; font-weight: bold; text-align: right; margin-top: 20px; border-top: 1px solid #333; padding-top: 10px; }
        .status-badge { background-color: #28a745; color: white; padding: 5px 10px; font-weight: bold; display: inline-block; margin-bottom: 20px; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; border-top: 1px dashed #333; padding-top: 15px; }
        .btn-cetak { display: block; width: 100%; text-align: center; background: #333; color: #fff; padding: 10px; text-decoration: none; font-weight: bold; margin-top: 20px; border: none; cursor: pointer; }
        @media print { .btn-cetak { display: none; } body { background: #fff; padding: 0; } .nota-box { border: none; } }
    </style>
</head>
<body>
<div class="nota-box">
    <div class="header">
        <h2>RENTAL MOBIL JAYA</h2>
        <p>Jl. Raya Utama No. 123, Sleman, Yogyakarta | Telp: 08123456789</p>
    </div>

    <center>
        <div class="status-badge">TRANSAKSI DIKONFIRMASI</div>
    </center>

    <table class="info-table">
        <tr>
            <td style="width: 35%;">No. Nota</td>
            <td style="width: 5%;">:</td>
            <td><strong>#RM-<?php echo str_pad($data['id_booking'], 5, '0', STR_PAD_LEFT); ?></strong></td>
        </tr>
        <tr>
            <td>Waktu Transaksi</td>
            <td>:</td>
            <td><?php echo date('d-m-Y H:i', strtotime($data['waktu_booking'])); ?> WIB</td>
        </tr>
        <tr>
            <td>Nama Penyewa</td>
            <td>:</td>
            <td><?php echo strtoupper($data['nama']); ?></td>
        </tr>
        <tr>
            <td>NIK KTP</td>
            <td>:</td>
            <td><?php echo $data['nik_booking']; ?></td>
        </tr>
    </table>

    <table class="detail-table">
        <thead>
            <tr>
                <th>Item & Waktu Durasi Sewa</th>
                <th style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>🚗 <?php echo $data['merk_mobil']; ?> (<?php echo $data['plat_nomor']; ?>)</strong><br>
                    <small>
                        Ambil: <?php echo date('d-m-Y', strtotime($data['tanggal_pengambilan'])); ?> Jam <?php echo date('H:i', strtotime($data['jam_pengambilan'])); ?> WIB<br>
                        Kembali: <?php echo date('d-m-Y', strtotime($data['tanggal_pengembalian'])); ?> Jam <?php echo date('H:i', strtotime($data['jam_pengembalian'])); ?> WIB
                    </small>
                </td>
                <td style="text-align: right; vertical-align: top;">
                    Rp <?php echo number_format($data['total_biaya'], 0, ',', '.'); ?>
                </td>
            </tr>
        </tbody>
    </table>

    <div class="total-row">
        TOTAL LUNAS: Rp <?php echo number_format($data['total_biaya'], 0, ',', '.'); ?>
    </div>

    <div class="footer">
        <p>Terima kasih atas transaksi Anda.</p>
        <p>Silakan bawa bukti cetak nota ini pada saat melakukan penjemputan armada mobil.</p>
    </div>

    <button class="btn-cetak" onclick="window.print()">PRINT NOTA</button>
</div>
</body>
</html>
