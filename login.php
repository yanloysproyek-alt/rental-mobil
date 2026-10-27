<?php
session_start();
include 'koneksi.php';
$error = "";

if (isset($_POST['login'])) {
    $input_user = mysqli_real_escape_string($koneksi, $_POST['username_email']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);

    // 1. CEK DULU KE TABEL USERS (Admin / Owner)
    $query_users = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$input_user' AND password='$password'");
    
    if (mysqli_num_rows($query_users) > 0) {
        $data_user = mysqli_fetch_assoc($query_users);
        
        // Simpan data ke session internal
        $_SESSION['id_user'] = $data_user['id_user'];
        $_SESSION['nama']    = $data_user['nama'];
        $_SESSION['role']    = $data_user['role'];

        // Alihkan sesuai level/role
        if ($data_user['role'] == 'admin') {
            header("Location: dashboard/admin.php");
        } else if ($data_user['role'] == 'owner') {
            header("Location: dashboard/pemilik.php");
        }
        exit();
    } 
    
    // 2. JIKA TIDAK ADA DI TABEL USERS, CEK KE TABEL PELANGGAN
    $query_pelanggan = mysqli_query($koneksi, "SELECT * FROM pelanggan WHERE email='$input_user' AND password='$password'");
    
    if (mysqli_num_rows($query_pelanggan) > 0) {
        $data_pelanggan = mysqli_fetch_assoc($query_pelanggan);
        
        // Simpan data ke session pelanggan
        $_SESSION['id_pelanggan'] = $data_pelanggan['id_pelanggan'];
        $_SESSION['nama']         = $data_pelanggan['nama'];
        $_SESSION['role']         = 'pelanggan';

        // Langsung lempar ke menu booking
        header("Location: booking.php");
        exit();
    }

    $error = "<div style='color:red; text-align:center; margin-bottom:10px;'>Username/Email atau Password salah!</div>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Rental Mobil</title>
    <style>
        body { font-family: Arial, sans-serif; background: #e9ecef; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0px 0px 15px rgba(0,0,0,0.1); width: 320px; }
        h2 { text-align: center; margin-bottom: 20px; color: #333; }
        input { width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        button:hover { background: #0056b3; }
        .link { text-align: center; margin-top: 15px; font-size: 14px; }
    </style>
</head>
<body>
<div class="login-box">
    <h2>Login Sistem</h2>
    <?php echo $error; ?>
    <form method="POST" action="">
        <input type="text" name="username_email" placeholder="Username (Admin/Owner) atau Email (Pelanggan)" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login">MASUK</button>
    </form>
    <div class="link">Pelanggan baru? <a href="registrasi.php">Daftar Akun</a></div>
</div>
</body>
</html>
