<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_POST['tambah_mobil'])) {
    $merk = mysqli_real_escape_string($koneksi, $_POST['merk']);
    $plat = mysqli_real_escape_string($koneksi, $_POST['plat']);
    $harga = mysqli_real_escape_string($koneksi, $_POST['harga']);

    $target_dir = "../assets/";
    $foto_nama = time() . "_mobil_" . $_FILES["foto_mobil"]["name"];
    
    if(move_uploaded_file($_FILES["foto_mobil"]["tmp_name"], $target_dir . $foto_nama)) {
        mysqli_query($koneksi, "INSERT INTO mobil (merk_mobil, plat_nomor, harga_sewa, foto_mobil, status_mobil) VALUES ('$merk', '$plat', '$harga', '$foto_nama', 'Tersedia')");
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
        .box { max-width: 800px; margin: 0 auto; background: white; padding: 25px; border-radius: 8px; }
        input { width: 100%; padding: 8px; margin-bottom: 10px; box-sizing: border-box; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
    </style>
</head>
<body>
<div class="box">
    <a href="admin.php"><- Kembali</a>
    <h2>Kelola Armada</h2>
    <form method="POST" action="" enctype="multipart/form-data" style="background:#f8f9fa; padding:15px; margin-bottom:20px;">
        <input type="text" name="merk" placeholder="Merk Mobil" required>
        <input type="text" name="plat" placeholder="Plat Nomor" required>
        <input type="number" name="harga" placeholder="Harga Sewa / Hari" required>
        <input type="file" name="foto_mobil" accept="image/*" required>
        <button type="submit" name="tambah_mobil" style="padding:10px; background:#007bff; color:white; border:none; width:100%; font-weight:bold; cursor:pointer;">SIMPAN MOBIL</button>
    </form>
    <table>
        <tr><th>Foto</th><th>Merk</th><th>Plat</th><th>Harga</th><th>Status</th></tr>
        <?php while($m = mysqli_fetch_assoc($ambil_mobil)): ?>
        <tr>
            <td><img src="../assets/<?php echo $m['foto_mobil']; ?>" width="60"></td>
            <td><b><?php echo $m['merk_mobil']; ?></b></td>
            <td><?php echo $m['plat_nomor']; ?></td>
            <td>Rp <?php echo number_format($m['harga_sewa'],0,',','.'); ?></td>
            <td><?php echo $m['status_mobil']; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
