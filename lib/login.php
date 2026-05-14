<?php
// WAJIB: Session harus dimulai di baris paling atas
session_start(); 
include "koneksi.php";

$alert_script = ""; 

// Cek jika tombol login ditekan
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password_input = $_POST['password']; // Password mentah dari form

    // 1. Ambil data user berdasarkan username (Prepared Statement)
    $stmt = $koneksi->prepare("SELECT * FROM user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        
        // 2. Verifikasi Password menggunakan password_verify (Sinkron dengan edit_user)
        if (password_verify($password_input, $user_data['password'])) {
            
            $_SESSION['user'] = $user_data;
            
            $alert_script = "
                Swal.fire({
                    icon: 'success',
                    title: 'Login Berhasil!',
                    text: 'Selamat datang kembali, " . $user_data['nama'] . "!',
                    timer: 2000,
                    showConfirmButton: false,
                    background: '#1e272e',
                    color: '#fff',
                    iconColor: '#a29bfe'
                }).then(() => {
                    window.location.href = 'index.php';
                });
            ";
        } else {
            // Password tidak cocok
            $alert_script = "
                Swal.fire({
                    icon: 'error',
                    title: 'Login Gagal',
                    text: 'Kata sandi yang Anda masukkan salah!',
                    confirmButtonColor: '#6c5ce7',
                    background: '#1e272e',
                    color: '#fff'
                });
            ";
        }
    } else {
        // Username tidak ditemukan
        $alert_script = "
            Swal.fire({
                icon: 'error',
                title: 'User Tidak Ditemukan',
                text: 'Username belum terdaftar!',
                confirmButtonColor: '#6c5ce7',
                background: '#1e272e',
                color: '#fff'
            });
        ";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Masuk - Lib Digital-X</title>
    
    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    
    <!-- SweetAlert2 JS -->
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
            display: flex;
            align-items: center;
            position: relative;
            margin: 0;
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
            transition: all 0.4s ease;
        }

        .card:hover { transform: translateY(-5px); }

        .card-header h3 {
            font-family: 'Playfair Display', serif;
            color: #fff;
            font-size: 1.8rem;
            text-shadow: 0 0 10px rgba(162, 155, 254, 0.5);
        }

        .form-label {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.85rem;
            margin-bottom: 8px;
        }

        .input-group {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow: hidden;
        }

        .input-group-text {
            background: transparent;
            border: none;
            color: var(--accent-color);
            padding-left: 15px;
        }

        .form-control {
            background: transparent !important;
            border: none !important;
            color: #fff !important;
            padding: 12px 15px;
        }

        .form-control:focus { box-shadow: none; }
        .form-control::placeholder { color: rgba(255, 255, 255, 0.4); }

        .btn-login {
            background: linear-gradient(45deg, var(--primary-color), #8e44ad);
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: scale(1.02);
            filter: brightness(1.1);
            box-shadow: 0 5px 15px rgba(108, 92, 231, 0.4);
        }

        .btn-outline-register {
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            border-radius: 12px;
            padding: 12px;
            transition: 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-outline-register:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--accent-color);
            border-color: var(--accent-color);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="card p-4">
                    <div class="card-header text-center border-0">
                        <div class="mb-3">
                            <i class="fas fa-book-reader fa-3x text-white"></i>
                        </div>
                        <h3>Lib Digital-X</h3>
                        <p class="text-white-50 small">Silahkan masuk untuk mengakses koleksi digital kami</p>
                    </div>

                    <div class="card-body">
                        <form method="post">
                            <div class="mb-4">
                                <label class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input class="form-control" type="text" name="username" placeholder="Masukan username" required />
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Kata Sandi</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input class="form-control" type="password" name="password" placeholder="Masukan password" required />
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-2">
                                <button class="btn btn-login" type="submit" name="login">MASUK SEKARANG</button>
                                <div class="text-center my-2 text-white-50 small">Atau</div>
                                <a href="register.php" class="btn btn-outline-register">BUAT AKUN BARU</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JS Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Trigger SweetAlert dari PHP -->
    <script>
        <?php echo $alert_script; ?>
    </script>
</body>
</html>