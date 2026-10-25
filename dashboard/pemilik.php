<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../login.php");
    exit();
}

$pesan = "";
if (isset($_POST['tambah_admin'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);

    $cek = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($cek) > 0) {
        $pesan = "<div class='alert alert-error'>Username sudah ada!</div>";
    } else {
        mysqli_query($koneksi, "INSERT INTO users (nama, username, password, role) VALUES ('$nama', '$username', '$password', 'admin')");
        $pesan = "<div class='alert alert-sukses'>Admin baru berhasil disimpan ke database!</div>";
    }
}

if (isset($_GET['hapus_admin'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['hapus_admin']);
    mysqli_query($koneksi, "DELETE FROM users WHERE id_user='$id' AND role='admin'");
    header("Location: pemilik.php");
    exit();
}

// 1. HITUNG TOTAL OMZET KEUNTUNGAN KESELURUHAN (DARI TRANSAKSI YANG DIKONFIRMASI)
$data_untung = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(total_biaya) AS total FROM booking WHERE status_transaksi = 'Telah Dikonfirmasi'"));
$total_keuntungan = $data_untung['total'] ?? 0;

// 2. AMBIL DAFTAR STAF ADMIN
$daftar_admin = mysqli_query($koneksi, "SELECT * FROM users WHERE role = 'admin'");

// ========================================================
// LOGIKA BARU: HITUNG PENDAPATAN DAN JUMLAH SEWA TIAP-TIAP MOBIL
// ========================================================
$query_performa_mobil = mysqli_query($koneksi, "
    SELECT 
        mobil.id_mobil,
        mobil.merk_mobil,
        mobil.plat_nomor,
        mobil.foto_mobil,
        COUNT(booking.id_booking) AS total_disewa,
        IFNULL(SUM(booking.total_biaya), 0) AS total_pendapatan_mobil
    FROM mobil
    LEFT JOIN booking ON mobil.id_mobil = booking.id_mobil AND booking.status_transaksi = 'Telah Dikonfirmasi'
    GROUP BY mobil.id_mobil
    ORDER BY total_pendapatan_mobil DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Panel Owner</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; padding: 20px; margin: 0; }
        .wrapper { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .header { border-bottom: 2px solid #f0f2f5; padding-bottom: 10px; margin-bottom: 20px; }
        .untung-box { background: #28a745; color: white; padding: 20px; text-align: center; border-radius: 4px; margin-bottom: 25px; font-size: 26px; font-weight: bold; box-shadow: 0 4px 6px rgba(40,167,69,0.15); }
        .flex { display: flex; gap: 20px; margin-bottom: 30px; }
        .flex-child { flex: 1; }
        input { width: 100%; padding: 8px; margin-bottom: 10px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f8f9fa; color: #333; }
        
        .alert { padding: 10px; border-radius: 4px; margin-bottom: 15px; font-weight: bold; }
        .alert-sukses { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
        
        /* CSS Baru untuk Tabel Performa Mobil */
        .section-title { margin-top: 20px; color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 5px; }
        .img-mobil { width: 50px; height: auto; border-radius: 4px; }
        .txt-sukses { color: #28a745; font-weight: bold; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <a href="../logout.php" style="color:white; background:#dc3545; padding:6px 12px; text-decoration:none; border-radius:4px; font-weight:bold; float:right; font-size:13px;">Logout</a>
        <h2>Panel Utama Owner</h2>
    </div>
    
    <div class="untung-box">TOTAL OMZET KEUNTUNGAN: Rp <?php echo number_format($total_keuntungan,0,',','.'); ?></div>
    
    <?php echo $pesan; ?>
    
    <!-- BLOCK ATAS: PENGELOLAAN STAF ADMIN -->
    <div class="flex">
        <div class="flex-child">
            <h3>Daftar Staf Admin</h3>
            <table>
                <thead>
                    <tr><th>Nama</th><th>Username</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php while($a = mysqli_fetch_assoc($daftar_admin)): ?>
                    <tr>
                        <td><b><?php echo htmlspecialchars($a['nama']); ?></b></td>
                        <td><code><?php echo htmlspecialchars($a['username']); ?></code></td>
                        <td><a href="pemilik.php?hapus_admin=<?php echo $a['id_user']; ?>" style="color:red; font-weight:bold; text-decoration:none;" onclick="return confirm('Hapus admin ini dari database?')">🗑️ Hapus</a></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <div class="flex-child" style="background:#f8f9fa; padding:15px; border-radius:6px; border:1px solid #eee;">
            <h3>Tambah Admin</h3>
            <form method="POST" action="">
                <input type="text" name="nama" placeholder="Nama Lengkap" required>
                <input type="text" name="username" placeholder="Username Baru" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="tambah_admin" style="width:100%; padding:10px; background:#007bff; color:white; border:none; cursor:pointer; font-weight:bold; border-radius:4px;">SIMPAN KE DB</button>
            </form>
        </div>
    </div>

    <!-- BLOCK BAWAH (BARU): RINCIAN PENDAPATAN HASIL RENTAL SETIAP MOBIL -->
    <h3 class="section-title">📊 Laporan Pendapatan Berdasarkan Armada Mobil</h3>
    <table>
        <thead>
            <tr>
                <th>Foto</th>
                <th>Armada Mobil</th>
                <th>Plat Nomor</th>
                <th style="text-align: center;">Frekuensi Rental</th>
                <th style="text-align: right;">Total Hasil Sewa</th>
            </tr>
        </thead>
        <tbody>
            <?php while($mobil = mysqli_fetch_assoc($query_performa_mobil)): ?>
            <tr>
                <td>
                    <img src="../assets/<?php echo $mobil['foto_mobil']; ?>" class="img-mobil" alt="Mobil">
                </td>
                <td><b><?php echo htmlspecialchars($mobil['merk_mobil']); ?></b></td>
                <td><code><?php echo htmlspecialchars($mobil['plat_nomor']); ?></code></td>
                <td style="text-align: center;">
                    <b style="color:#007bff;"><?php echo $mobil['total_disewa']; ?> x</b> disewa
                </td>
                <td style="text-align: right;" class="txt-sukses">
                    Rp <?php echo number_format($mobil['total_pendapatan_mobil'], 0, ',', '.'); ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</div>
</body>
</html>
