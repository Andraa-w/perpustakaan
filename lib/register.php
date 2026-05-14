<?php
include "koneksi.php";

$alert_script = ""; 

if (isset($_POST['registrasi'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['name']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $no_telepon = mysqli_real_escape_string($koneksi, $_POST['no_telepon']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    
    // GUNAKAN PASSWORD_HASH (Bukan MD5 lagi agar sinkron dengan login)
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $level = $_POST['level'];

    // Cek apakah username sudah ada
    $cek_user = mysqli_query($koneksi, "SELECT * FROM user WHERE username='$username'");
    
    if (mysqli_num_rows($cek_user) > 0) {
        $alert_script = "
            Swal.fire({
                icon: 'warning',
                title: 'Username Sudah Ada',
                text: 'Silakan gunakan username lain yang lebih unik.',
                background: '#1e272e',
                color: '#fff',
                confirmButtonColor: '#6c5ce7'
            });
        ";
    } else {
        $insert = mysqli_query($koneksi, "INSERT INTO user(name, email, alamat, no_telepon, username, password, level) 
                                         VALUES('$nama', '$email', '$alamat', '$no_telepon', '$username', '$password', '$level')");

        if ($insert) {
            $alert_script = "
                Swal.fire({
                    icon: 'success',
                    title: 'Registrasi Berhasil!',
                    text: 'Akun Anda telah terdaftar. Mengalihkan ke halaman login...',
                    timer: 2500,
                    showConfirmButton: false,
                    background: '#1e272e',
                    color: '#fff',
                    iconColor: '#a29bfe'
                }).then(() => {
                    window.location.href = 'login.php';
                });
            ";
        } else {
            $alert_script = "
                Swal.fire({
                    icon: 'error',
                    title: 'Registrasi Gagal',
                    text: 'Terjadi kesalahan sistem, silakan coba lagi.',
                    background: '#1e272e',
                    color: '#fff'
                });
            ";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Register - Lib Digital-X</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --primary-color: #6c5ce7;
            --accent-color: #a29bfe;
            --library-dark: #1e272e;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: url('https://images.unsplash.com/photo-1507842217343-583bb7270b66?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            position: relative;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(135deg, rgba(30, 39, 46, 0.9), rgba(47, 53, 66, 0.8));
            z-index: 0;
        }

        .container { position: relative; z-index: 1; }

        .card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            transition: all 0.3s ease;
        }

        .card-header h3 {
            font-family: 'Playfair Display', serif;
            color: #fff;
            font-size: 1.8rem;
        }

        .form-label { color: rgba(255, 255, 255, 0.8); font-size: 0.85rem; }

        .input-group {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .input-group-text { background: transparent; border: none; color: var(--accent-color); }

        .form-control, .form-select {
            background: transparent !important;
            border: none !important;
            color: #fff !important;
        }

        .form-select option { background: var(--library-dark); color: #fff; }

        .btn-register {
            background: linear-gradient(45deg, var(--primary-color), #8e44ad);
            border: none; border-radius: 12px; padding: 12px; font-weight: 600; color: white;
            transition: all 0.3s ease;
        }

        .btn-register:hover {
            transform: scale(1.02);
            box-shadow: 0 5px 15px rgba(108, 92, 231, 0.4);
        }

        .login-link { color: rgba(255, 255, 255, 0.6); text-decoration: none; font-size: 0.85rem; }
        .login-link:hover { color: var(--accent-color); }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-xl-5">
                <div class="card p-2 p-md-4">
                    <div class="card-header text-center">
                        <div class="mb-2"><i class="fas fa-book-reader fa-3x text-white"></i></div>
                        <h3>Lib Digital-X</h3>
                        <span class="text-white-50 small">Wujudkan imajinasi melalui jendela dunia</span>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nama Lengkap</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                        <input class="form-control" type="text" name="name" placeholder="Nama Anda" required />
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Username</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input class="form-control" type="text" name="username" placeholder="Username" required />
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email Aktif</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input class="form-control" type="email" name="email" placeholder="contoh@mail.com" required />
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Alamat Lengkap</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                    <input class="form-control" name="alamat" placeholder="Alamat tinggal" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">No. Telepon</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                        <input class="form-control" type="text" name="no_telepon" placeholder="08..." required />
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Daftar Sebagai</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                                        <select name="level" class="form-select" required>
                                            <option value="peminjam">Peminjam</option>
                                            <option value="petugas">Petugas</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Kata Sandi</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    <input class="form-control" type="password" name="password" placeholder="••••••••" required />
                                </div>
                            </div>

                            <div class="d-grid">
                                <button class="btn btn-register mb-3" type="submit" name="registrasi">BUAT AKUN SEKARANG</button>
                            </div>
                            
                            <div class="text-center">
                                <a class="login-link" href="login.php">Sudah punya akun? Masuk di sini</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        <?php echo $alert_script; ?>
    </script>
</body>
</html>