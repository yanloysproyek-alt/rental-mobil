<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['id_pelanggan']) || $_SESSION['role'] !== 'pelanggan') {
    header("Location: login.php");
    exit();
}

$id_pelanggan = $_SESSION['id_pelanggan'];
$pesan = "";

if (isset($_POST['kirim_booking'])) {
    $id_mobil = mysqli_real_escape_string($koneksi, $_POST['id_mobil']);
    $nik = mysqli_real_escape_string($koneksi, $_POST['nik']);
    $no_telp = mysqli_real_escape_string($koneksi, $_POST['no_telp']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $tgl_ambil = mysqli_real_escape_string($koneksi, $_POST['tgl_ambil']);
    $tgl_kembali = mysqli_real_escape_string($koneksi, $_POST['tgl_kembali']);
    $jam_ambil = mysqli_real_escape_string($koneksi, $_POST['jam_ambil']);
    $jam_kembali = $jam_ambil; 

    $query_mobil = mysqli_query($koneksi, "SELECT harga_sewa FROM mobil WHERE id_mobil = '$id_mobil'");
    $data_mobil = mysqli_fetch_assoc($query_mobil);
    $harga_sewa = $data_mobil['harga_sewa'];

    $tgl1 = new DateTime($tgl_ambil);
    $tgl2 = new DateTime($tgl_kembali);
    $jarak = $tgl1->diff($tgl2);
    $jumlah_hari = $jarak->days;
    if ($jumlah_hari <= 0) { $jumlah_hari = 1; }

    $total_biaya = $jumlah_hari * $harga_sewa;
    $target_dir = "uploads/";
    
    $foto_ktp_nama = time() . "_ktp_" . $_FILES["foto_ktp"]["name"];
    move_uploaded_file($_FILES["foto_ktp"]["tmp_name"], $target_dir . $foto_ktp_nama);

    $bukti_tf_nama = time() . "_tf_" . $_FILES["bukti_transfer"]["name"];
    move_uploaded_file($_FILES["bukti_transfer"]["tmp_name"], $target_dir . $bukti_tf_nama);

    $sql_booking = "INSERT INTO booking (id_pelanggan, id_mobil, nik_booking, no_telp_pesanan, alamat_lengkap, tanggal_pengambilan, tanggal_pengembalian, jam_pengambilan, jam_pengembalian, total_biaya, foto_ktp, bukti_transfer, status_transaksi) 
                    VALUES ('$id_pelanggan', '$id_mobil', '$nik', '$no_telp', '$alamat', '$tgl_ambil', '$tgl_kembali', '$jam_ambil', '$jam_kembali', '$total_biaya', '$foto_ktp_nama', '$bukti_tf_nama', 'Menunggu Konfirmasi')";

    if (mysqli_query($koneksi, $sql_booking)) {
        $id_booking_baru = mysqli_insert_id($koneksi);
        header("Location: status.php?id=" . $id_booking_baru);
        exit();
    } else {
        $pesan = "<div class='alert error'>Gagal memproses sewa.</div>";
    }
}

$daftar_mobil = mysqli_query($koneksi, "SELECT * FROM mobil WHERE status_mobil = 'Tersedia'");

// LOGIKA BARU: Mengambil data transaksi terakhir yang dilakukan pelanggan ini untuk tombol cek status
$cek_transaksi = mysqli_query($koneksi, "SELECT id_booking FROM booking WHERE id_pelanggan = '$id_pelanggan' ORDER BY id_booking DESC LIMIT 1");
$data_transaksi = mysqli_fetch_assoc($cek_transaksi);
$id_booking_terakhir = $data_transaksi['id_booking'] ?? null;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Booking Mobil</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; padding: 20px; margin: 0; }
        .container { max-width: 1100px; margin: 0 auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        h2 { margin-top: 0; color: #333; font-size: 24px; border-bottom: 2px solid #f0f2f5; padding-bottom: 10px; margin-bottom: 20px; }
        
        .flex-box { display: flex; gap: 25px; }
        .kolom-katalog { flex: 1.2; }
        .kolom-form { flex: 1; background: #f8f9fa; padding: 20px; border-radius: 6px; border: 1px solid #e9ecef; height: fit-content; }
        
        .form-group { margin-bottom: 12px; }
        label { display: block; font-weight: bold; margin-bottom: 4px; font-size: 13px; color: #495057; }
        input, select, textarea { width: 100%; padding: 8px 10px; border: 1px solid #ced4da; border-radius: 4px; box-sizing: border-box; font-size: 13px; }
        textarea { resize: none; }
        input[type="file"] { padding: 4px 0; background: none; border: none; }
        
        button { width: 100%; padding: 12px; background: #007bff; color: white; border: none; font-weight: bold; cursor: pointer; border-radius: 4px; font-size: 15px; margin-top: 10px; box-shadow: 0 3px 6px rgba(0,123,255,0.15); transition: 0.2s; }
        button:hover { background: #0056b3; }
        
        /* CSS BARU UNTUK TOMBOL NAVIGASI DI ATAS */
        .logout-btn { background: #dc3545; color: white; padding: 6px 12px; text-decoration: none; float: right; border-radius: 4px; font-size: 12px; font-weight: bold; margin-left: 10px; }
        .status-btn { background: #ffc107; color: #212529; padding: 6px 12px; text-decoration: none; float: right; border-radius: 4px; font-size: 12px; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.08); transition: 0.2s; }
        .status-btn:hover { background: #e0a800; }
        
        .grid-mobil { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 15px; margin-top: 12px; }
        .card-mobil { background: white; border-radius: 6px; overflow: hidden; border: 1px solid #dee2e6; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.03); }
        .card-mobil img { width: 100%; height: 110px; object-fit: cover; background: #eaedf1; }
        
        .card-body-katalog { padding: 10px; background: #fff; border-top: 1px solid #eee; text-align: left; }
        .card-info-nama { font-size: 13px; font-weight: bold; color: #333; margin-bottom: 3px; }
        .card-info-plat { font-size: 11px; color: #666; background: #e9ecef; padding: 2px 5px; border-radius: 3px; display: inline-block; margin-bottom: 5px; font-weight: bold; }
        .card-info-harga { font-size: 12px; font-weight: bold; color: #28a745; }
        
        .alert { padding: 10px; border-radius: 4px; margin-bottom: 15px; font-weight: bold; background: #f8d7da; color: #721c24; font-size: 13px; }
    </style>
</head>
<body>
<div class="container">
    <a href="logout.php" class="logout-btn">Logout</a>
    
    <!-- TOMBOL BARU: Akan muncul otomatis jika pelanggan sudah pernah mengirim booking sebelumnya -->
    <?php if ($id_booking_terakhir): ?>
        <a href="status.php?id=<?php echo $id_booking_terakhir; ?>" class="status-btn">⏳ Cek Status Booking</a>
    <?php endif; ?>
    
    <h2>Formulir Sewa Mobil</h2>
    <?php echo $pesan; ?>
    
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="flex-box">
            
            <div class="kolom-katalog">
                <div class="form-group">
                    <label>Pilih Mobil</label>
                    <select name="id_mobil" required>
                        <option value="">-- Pilih Mobil --</option>
                        <?php while($row = mysqli_fetch_assoc($daftar_mobil)): ?>
                            <option value="<?php echo $row['id_mobil']; ?>"><?php echo $row['merk_mobil']; ?> - Rp <?php echo number_format($row['harga_sewa'],0,',','.'); ?>/hari</option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <label style="margin-top: 20px; display: block; font-weight: bold;">Katalog Foto Mobil Tersedia:</label>
                <div class="grid-mobil">
                    <?php 
                    if (mysqli_num_rows($daftar_mobil) > 0) {
                        mysqli_data_seek($daftar_mobil, 0); 
                        while($img = mysqli_fetch_assoc($daftar_mobil)): 
                    ?>
                        <div class="card-mobil">
                            <img src="assets/<?php echo $img['foto_mobil']; ?>" alt="Mobil">
                            
                            <div class="card-body-katalog">
                                <div class="card-info-nama"><?php echo htmlspecialchars($img['merk_mobil']); ?></div>
                                <div class="card-info-plat"><?php echo htmlspecialchars($img['plat_nomor']); ?></div>
                                <div class="card-info-harga">Rp <?php echo number_format($img['harga_sewa'], 0, ',', '.'); ?> / hari</div>
                            </div>
                            
                        </div>
                    <?php 
                        endwhile; 
                    } 

                    ?>
                </div>
            </div>

            <div class="kolom-form">
                <div class="form-group"><label>NIK KTP</label><input type="text" name="nik" placeholder="16 digit NIK" required></div>
                <div class="form-group"><label>No HP Pesanan</label><input type="text" name="no_telp" placeholder="Contoh: 08123xxx" required></div>
                <div class="form-group"><label>Alamat Lengkap</label><textarea name="alamat" rows="2" placeholder="Sesuai KTP..." required></textarea></div>
                <div class="form-group"><label>Tanggal Ambil</label><input type="date" name="tgl_ambil" required></div>
                <div class="form-group"><label>Tanggal Kembali</label><input type="date" name="tgl_kembali" required></div>
                <div class="form-group"><label>Jam Pengambilan</label><input type="time" name="jam_ambil" required></div>
                <div class="form-group"><label>Foto KTP</label><input type="file" name="foto_ktp" accept="image/*" required></div>
                
                <div class="form-group">
                    <label>Informasi Rekening Pembayaran</label>
                    <div style="background: #e2f0d9; border: 1px solid #a9d18e; color: #385723; padding: 10px; border-radius: 4px; font-size: 13px; margin-bottom: 8px; font-weight: bold; line-height: 1.4;">
                        No. Rekening: BRI 357301038828537<br>
                        Nama : Yosef Ratzinger Yanloys Lado
                    </div>
                    <label>Bukti Transfer</label>
                    <input type="file" name="bukti_transfer" accept="image/*" required>
                </div>
                
                <button type="submit" name="kirim_booking">Kirim Booking</button>
            </div>
            
        </div>
    </form>
</div>
</body>
</html>
