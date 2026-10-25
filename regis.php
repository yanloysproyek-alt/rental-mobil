<?php
include 'koneksi.php';
$pesan = "";

if (isset($_POST['daftar'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $no_telp = mysqli_real_escape_string($koneksi, $_POST['no_telp']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = $_POST['password']; // Teks biasa sesuai kebutuhan awal tes Anda

    // Cek apakah email sudah terdaftar
    $cek_email = mysqli_query($koneksi, "SELECT * FROM pelanggan WHERE email='$email'");
    if (mysqli_num_rows($cek_email) > 0) {
        $pesan = "<div style='color:red;'>Email sudah terdaftar! Gunakan email lain.</div>";
    } else {
        // Masukkan data ke tabel pelanggan
        $query = mysqli_query($koneksi, "INSERT INTO pelanggan (nama, no_telp_utama, email, password) VALUES ('$nama', '$no_telp', '$email', '$password')");
        if ($query) {
            $pesan = "<div style='color:green;'>Registrasi berhasil! Silakan <a href='login.php'>Login disini</a>.</div>";
        } else {
            $pesan = "<div style='color:red;'>Registrasi gagal, coba lagi.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Registrasi Pelanggan Baru</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .regis-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); width: 320px; }
        h2 { text-align: center; margin-bottom: 20px; }
        input { width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        button:hover { background: #218838; }
        .link { text-align: center; margin-top: 15px; font-size: 14px; }
    </style>
</head>
<body>
<div class="regis-box">
    <h2>Daftar Akun</h2>
    <?php echo $pesan; ?>
    <form method="POST" action="">
        <input type="text" name="nama" placeholder="Nama Lengkap" required>
        <input type="text" name="no_telp" placeholder="Nomor HP Utama" required>
        <input type="email" name="email" placeholder="Email Anda" required>
        <input type="password" name="password" placeholder="Buat Password" required>
        <button type="submit" name="daftar">DAFTAR SEKARANG</button>
    </form>
    <div class="link">Sudah punya akun? <a href="login.php">Login</a></div>
</div>
</body>
</html>
