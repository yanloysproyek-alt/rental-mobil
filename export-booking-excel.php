<?php
session_start();
include '../koneksi.php';

// Pastikan yang mendownload adalah Admin yang sah
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    echo "Akses ditolak.";
    exit();
}

// PERINTAH UTAMA HEADER UNTUK MERUBAH OUTPUT PHP MENJADI FILE EXCEL DENGAN JUDUL DATA-BOOKING
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Laporan-Booking-Rental.xls");

$query = mysqli_query($koneksi, "SELECT booking.*, pelanggan.nama AS nama_penyewa, mobil.merk_mobil, mobil.plat_nomor 
                                 FROM booking 
                                 JOIN pelanggan ON booking.id_pelanggan = pelanggan.id_pelanggan
                                 JOIN mobil ON booking.id_mobil = mobil.id_mobil 
                                 ORDER BY booking.id_booking DESC");
?>
<h3>LAPORAN TRANSAKSI RENTAL MOBIL</h3>
<table border="1">
    <thead>
        <tr style="background-color: #f2f2f2; font-weight: bold;">
            <th>No</th>
            <th>ID Booking</th>
            <th>Nama Penyewa</th>
            <th>NIK KTP</th>
            <th>No Telp Pesanan</th>
            <th>Alamat Lengkap</th>
            <th>Mobil</th>
            <th>Plat Nomor</th>
            <th>Tanggal Ambil</th>
            <th>Tanggal Kembali</th>
            <th>Jam Sewa</th>
            <th>Total Biaya (Rp)</th>
            <th>Status Transaksi</th>
            <th>Waktu Kirim Form</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        while($row = mysqli_fetch_assoc($query)): 
        ?>
        <tr>
            <td><?php echo $no++; ?></td>
            <td>#RM-<?php echo $row['id_booking']; ?></td>
            <td><?php echo strtoupper($row['nama_penyewa']); ?></td>
            <!-- Menggunakan tanda petik satu didepan agar nomor NIK dan HP tidak terpotong atau dibulatkan oleh sistem excel -->
            <td>'<?php echo $row['nik_booking']; ?></td>
            <td>'<?php echo $row['no_telp_pesanan']; ?></td>
            <td><?php echo $row['alamat_lengkap']; ?></td>
            <td><?php echo $row['merk_mobil']; ?></td>
            <td><?php echo $row['plat_nomor']; ?></td>
            <td><?php echo date('d-m-Y', strtotime($row['tanggal_pengambilan'])); ?></td>
            <td><?php echo date('d-m-Y', strtotime($row['tanggal_pengembalian'])); ?></td>
            <td><?php echo date('H:i', strtotime($row['jam_pengambilan'])); ?> WIB</td>
            <td><?php echo $row['total_biaya']; ?></td>
            <td><?php echo $row['status_transaksi']; ?></td>
            <td><?php echo $row['waktu_booking']; ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
