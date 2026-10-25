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
        $pesan = "<div style='color:red;'>Username sudah ada!</div>";
    } else {
        mysqli_query($koneksi, "INSERT INTO users (nama, username, password, role) VALUES ('$nama', '$username', '$password', 'admin')");
        $pesan = "<div style='color:green;'>Admin baru berhasil disimpan ke database!</div>";
    }
}

if (isset($_GET['hapus_admin'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['hapus_admin']);
    mysqli_query($koneksi, "DELETE FROM users WHERE id_user='$id' AND role='admin'");
    header("Location: pemilik.php");
    exit();
}

$data_untung = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(total_biaya) AS total FROM booking WHERE status_transaksi = 'Telah Dikonfirmasi'"));
$total_keuntungan = $data_untung['total'] ?? 0;
$daftar_admin = mysqli_query($koneksi, "SELECT * FROM users WHERE role = 'admin'");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Panel Owner</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; padding: 20px; }
        .wrapper { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        .untung-box { background: #28a745; color: white; padding: 20px; text-align: center; border-radius: 4px; margin-bottom: 20px; font-size: 24px; font-weight: bold; }
        .flex { display: flex; gap: 20px; }
        input { width: 100%; padding: 8px; margin-bottom: 10px; box-sizing: border-box; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
    </style>
</head>
<body>
<div class="wrapper">
    <h2>Panel Owner</h2>
    <a href="../logout.php" style="color:red; font-weight:bold; float:right;">Logout</a>
    <div class="untung-box">TOTAL KEUNTUNGAN: Rp <?php echo number_format($total_keuntungan,0,',','.'); ?></div>
    <?php echo $pesan; ?>
    <div class="flex">
        <div style="flex:1;">
            <h3>Daftar Staf Admin</h3>
            <table>
                <tr><th>Nama</th><th>Username</th><th>Aksi</th></tr>
                <?php while($a = mysqli_fetch_assoc($daftar_admin)): ?>
                <tr>
                    <td><?php echo $a['nama']; ?></td>
                    <td><?php echo $a['username']; ?></td>
                    <td><a href="pemilik.php?hapus_admin=<?php echo $a['id_user']; ?>" style="color:red;" onclick="return confirm('Hapus admin ini dari database?')">Hapus</a></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
        <div style="flex:1; background:#f8f9fa; padding:15px; border-radius:4px;">
            <h3>Tambah Admin</h3>
            <form method="POST" action="">
                <input type="text" name="nama" placeholder="Nama Lengkap" required>
                <input type="text" name="username" placeholder="Username Baru" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="tambah_admin" style="width:100%; padding:10px; background:#007bff; color:white; border:none; cursor:pointer; font-weight:bold;">SIMPAN KE DB</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
