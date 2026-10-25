<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$pesan = "";

// ==========================================
// LOGIKA BARU: PROSES HAPUS MOBIL DARI SISTEM
// ==========================================
if (isset($_GET['hapus_mobil'])) {
    $id_mobil_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus_mobil']);
    
    // 1. Ambil nama file foto mobil terlebih dahulu dari database sebelum dihapus
    $cek_foto = mysqli_query($koneksi, "SELECT foto_mobil FROM mobil WHERE id_mobil = '$id_mobil_hapus'");
    $foto = mysqli_fetch_assoc($cek_foto);
    
    if ($foto) {
        // 2. Hapus file gambar fisik mobil dari dalam folder assets agar hemat penyimpanan
        @unlink("../assets/" . $foto['foto_mobil']);
    }
    
    // 3. Eksekusi perintah hapus mobil dari database MySQL
    $query_hapus = mysqli_query($koneksi, "DELETE FROM mobil WHERE id_mobil = '$id_mobil_hapus'");
    
    if ($query_hapus) {
        header("Location: mobil-kelola.php?status=terhapus");
        exit();
    }
}

// Menampilkan pesan pemberitahuan sukses jika data berhasil diolah
if (isset($_GET['status']) && $_GET['status'] == 'terhapus') {
    $pesan = "<div class='alert-sukses'>✅ Mobil berhasil dihapus! Otomatis hilang dari menu booking pelanggan.</div>";
}

if (isset($_POST['tambah_mobil'])) {
    $merk = mysqli_real_escape_string($koneksi, $_POST['merk']);
    $plat = mysqli_real_escape_string($koneksi, $_POST['plat']);
    $harga = mysqli_real_escape_string($koneksi, $_POST['harga']);

    $target_dir = "../assets/";
    $foto_nama = time() . "_mobil_" . $_FILES["foto_mobil"]["name"];
    
    if(move_uploaded_file($_FILES["foto_mobil"]["tmp_name"], $target_dir . $foto_nama)) {
        $query_tambah = mysqli_query($koneksi, "INSERT INTO mobil (merk_mobil, plat_nomor, harga_sewa, foto_mobil, status_mobil) VALUES ('$merk', '$plat', '$harga', '$foto_nama', 'Tersedia')");
        if ($query_tambah) {
            $pesan = "<div class='alert-sukses'>✅ Mobil baru berhasil ditambahkan ke katalog sewa!</div>";
        }
    }
}

$ambil_mobil = mysqli_query($koneksi, "SELECT * FROM mobil");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Kelola Mobil</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; padding: 20px; }
        .box { max-width: 850px; margin: 0 auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        input { width: 100%; padding: 8px; margin-bottom: 10px; box-sizing: border-box; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f8f9fa; color: #333; }
        
        /* Desain Alert dan Tombol Hapus Baru */
        .alert-sukses { background: #d4edda; color: #155724; padding: 12px; margin-bottom: 15px; font-weight: bold; border-radius: 4px; border: 1px solid #c3e6cb; }
        .btn-hapus { color: #dc3545; text-decoration: none; font-weight: bold; border: 1px solid #dc3545; padding: 4px 8px; border-radius: 4px; background: white; font-size: 12px; }
        .btn-hapus:hover { background: #dc3545; color: white; transition: 0.2s; }
    </style>
</head>
<body>
<div class="box">
    <a href="admin.php" style="text-decoration: none; font-weight: bold; color: #007bff;"><- Kembali ke Dashboard</a>
    <h2>Kelola Armada Mobil</h2>
    
    <?php echo $pesan; ?>

    <form method="POST" action="" enctype="multipart/form-data" style="background:#f8f9fa; padding:15px; margin-bottom:20px; border-radius: 6px; border: 1px solid #eee;">
        <h3 style="margin-top: 0; color: #333;">Tambah Mobil Baru</h3>
        <input type="text" name="merk" placeholder="Merk Mobil (Contoh: Toyota Avanza)" required>
        <input type="text" name="plat" placeholder="Plat Nomor (Contoh: AB 1234 CD)" required>
        <input type="number" name="harga" placeholder="Harga Sewa / Hari (Rupiah)" required>
        <input type="file" name="foto_mobil" accept="image/*" required>
        <button type="submit" name="tambah_mobil" style="padding:10px; background:#007bff; color:white; border:none; width:100%; font-weight:bold; cursor:pointer; border-radius: 4px;">SIMPAN MOBIL</button>
    </form>

    <h3>Daftar Armada Saat Ini</h3>
    <table>
        <tr>
            <th>Foto</th>
            <th>Merk</th>
            <th>Plat Nomor</th>
            <th>Harga Sewa</th>
            <th>Status</th>
            <th>Aksi Sistem</th>
        </tr>
        <?php while($m = mysqli_fetch_assoc($ambil_mobil)): ?>
        <tr>
            <td><img src="../assets/<?php echo $m['foto_mobil']; ?>" width="70" style="border-radius: 4px;"></td>
            <td><b><?php echo htmlspecialchars($m['merk_mobil']); ?></b></td>
            <td><code><?php echo $m['plat_nomor']; ?></code></td>
            <td>Rp <?php echo number_format($m['harga_sewa'],0,',','.'); ?>/hari</td>
            <td><em><?php echo $m['status_mobil']; ?></em></td>
            <td>
                <!-- TOMBOL AKSI BARU: Untuk menghapus kendaraan secara permanen -->
                <a href="mobil-kelola.php?hapus_mobil=<?php echo $m['id_mobil']; ?>" class="btn-hapus" onclick="return confirm('Hapus mobil <?php echo $m['merk_mobil']; ?> dari katalog dan seluruh sistem?')">🗑️ Hapus Mobil</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
