<?php
// Memulai session untuk menyimpan data login pengguna
session_start();

// Cek apakah user sudah login, jika belum maka arahkan ke halaman login
if(!isset($_SESSION['user'])){
    header("location:login.php");
    exit();
}

// Menyertakan file koneksi database
include "koneksi.php";

// ================= USER DATA =================
// ================= DATA PENGGUNA =================
$user  = $_SESSION['user'];
// Gunakan null coalescing agar aman
$nama  = htmlspecialchars($user['nama'] ?? $user['nama'] ?? 'User'); 
// Mengambil informasi user dari session dengan fallback (nilai default) jika data kosong
$nama  = htmlspecialchars($user['nama'] ?? 'User'); 
$level = htmlspecialchars($user['level'] ?? 'Peminjam');
$foto  = !empty($user['foto']) ? $user['foto'] : 'default.png';

// ================= UPDATE PROFILE =================
// ================= PROSES UPDATE PROFIL =================
// Logika ini berjalan jika pengguna menekan tombol 'simpan' pada halaman pengaturan
if (isset($_POST['simpan'])) {
    $id       = $user['id_user'];
    $nama_bg  = mysqli_real_escape_string($koneksi, $_POST['name']);
    $password = $_POST['password'];

    $foto_new = $_FILES['foto']['name'];
    $tmp      = $_FILES['foto']['tmp_name'];

    // Logika jika pengguna mengunggah foto baru
    if ($foto_new != '') {
        $ext = pathinfo($foto_new, PATHINFO_EXTENSION);
        $foto_db = "user_" . $id . "_" . time() . "." . $ext;
        move_uploaded_file($tmp, "upload/user/" . $foto_db);
        $foto_query = ", foto='$foto_db'";
        $_SESSION['user']['foto'] = $foto_db;
    } else {
        $foto_query = "";
    }

    // Gunakan MD5 untuk password (disarankan ganti ke password_hash di masa depan)
    $pass_query = ($password != '') ? ", password='" . md5($password) . "'" : "";

    // Update ke Database (Pastikan nama kolom sesuai: 'nama')
    // Update data ke tabel user berdasarkan ID
    mysqli_query($koneksi, "UPDATE user SET nama='$nama_bg' $pass_query $foto_query WHERE id_user='$id'");
    $_SESSION['user']['nama'] = $nama_bg;

    // Notifikasi berhasil dan segarkan halaman
    echo "<script>alert('Profil berhasil diperbarui!'); location='index.php?page=setelan';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Lib Digital-X</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js"></script>
    <style>
        :root {
            --primary-dark: #1a1c23;
            --accent-color: #6366f1;
            --bg-light: #f9fafb;
        }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-light); }
        
        /* Navbar Styling */
        /* Gaya Navbar */
        .sb-topnav { background-color: var(--primary-dark) !important; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .navbar-brand { font-weight: 700; letter-spacing: -0.5px; color: var(--accent-color) !important; }

        /* Sidebar Styling */
        /* Gaya Sidebar */
        .sb-sidenav-dark { background-color: var(--primary-dark); }
        .sb-sidenav-dark .sb-sidenav-menu .nav-link { font-weight: 500; color: rgba(255,255,255,0.6); transition: 0.3s; }
        .sb-sidenav-dark .sb-sidenav-menu .nav-link:hover { color: #fff; background: rgba(99, 102, 241, 0.1); }
        .sb-sidenav-dark .sb-sidenav-menu .nav-link.active { color: var(--accent-color); background: rgba(99, 102, 241, 0.15); }
        .sb-sidenav-footer { background-color: rgba(0,0,0,0.2) !important; border-top: 1px solid rgba(255,255,255,0.05); }

        /* Profile Image Styles */
        /* Gaya Gambar Profil */
        .profile-img { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 1px solid rgba(255,255,255,0.2); }
        .avatar-preview { width: 120px; height: 120px; border-radius: 20px; object-fit: cover; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border: 4px solid #fff; }

        /* Card Customization */
        /* Kustomisasi Card */
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06); }
        .card-header { background-color: #fff; border-bottom: 1px solid #f3f4f6; font-weight: 600; }
        
        /* Responsive Fix */
        /* Perbaikan Responsif Sidebar */
        #layoutSidenav_content { padding-left: 0; transition: 0.3s; }
        @media (min-width: 992px) { .sb-nav-fixed #layoutSidenav_content { padding-left: 225px; } }
        
        .btn-primary { background-color: var(--accent-color); border: none; padding: 10px 20px; border-radius: 10px; font-weight: 600; }
        .btn-primary:hover { background-color: #4f46e5; transform: translateY(-1px); }
    </style>
</head>
<body class="sb-nav-fixed">

<!-- Navbar Atas -->
<nav class="sb-topnav navbar navbar-expand navbar-dark">
    <a class="navbar-brand ps-3" href="index.php"><i class="fas fa-book-open me-2"></i>DIGITAL-X</a>
    <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0 text-white" id="sidebarToggle"><i class="fas fa-align-left"></i></button>
    
    <ul class="navbar-nav ms-auto me-3">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown">
                <img src="upload/user/<?= $foto; ?>" class="profile-img" onerror="this.src='upload/user/default.png'">
                <span class="d-none d-md-inline"><?= $nama; ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-3" style="border-radius: 12px;">
                <li class="p-3 text-center">
                    <img src="upload/user/<?= $foto; ?>" style="width:50px;height:50px;border-radius:50%;object-fit:cover;" class="mb-2" onerror="this.src='upload/user/default.png'">
                    <div class="fw-bold small"><?= $nama; ?></div>
                    <span class="badge bg-soft-primary text-primary" style="background: #eef2ff; font-size: 10px;"><?= strtoupper($level); ?></span>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item py-2" href="?page=setelan"><i class="fas fa-cog me-2 opacity-50"></i> Pengaturan</a></li>
                <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2 opacity-50"></i> Logout</a></li>
            </ul>
        </li>
    </ul>
</nav>

<div id="layoutSidenav">
    <!-- Sidebar Navigasi -->
    <div id="layoutSidenav_nav">
        <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
            <div class="sb-sidenav-menu">
                <div class="nav mt-3">
                    <div class="sb-sidenav-menu-heading opacity-50 small">UTAMA</div>
                    <a class="nav-link" href="index.php">
                        <div class="sb-nav-link-icon"><i class="fas fa-home"></i></div> Beranda
                    </a>

                    <div class="sb-sidenav-menu-heading opacity-50 small">PERPUSTAKAAN</div>
                    <!-- Menu untuk Admin dan Petugas -->
                    <?php if ($level == 'Admin' || $level == 'Petugas') : ?>
                        <a class="nav-link" href="?page=buku"><div class="sb-nav-link-icon"><i class="fas fa-book"></i></div> Kelola Buku</a>
                        <a class="nav-link" href="?page=katalog_favorit"><div class="sb-nav-link-icon"><i class="fas fa-star"></i></div> Katalog Buku</a>
                        <a class="nav-link" href="?page=laporan_peminjaman"><div class="sb-nav-link-icon"><i class="fas fa-print"></i></div> Laporan Pinjaman</a>
                        <a class="nav-link" href="?page=kategori"><div class="sb-nav-link-icon"><i class="fas fa-tags"></i></div> Kategori</a>
                    <?php endif; ?>

                    <!-- Menu khusus Admin -->
                    <?php if ($level == 'Admin') : ?>
                        <a class="nav-link" href="?page=user"><div class="sb-nav-link-icon"><i class="fas fa-user-shield"></i></div> Manajemen User</a>
                    <?php endif; ?>

                    <!-- Menu khusus Peminjam -->
                    <?php if ($level == 'Peminjam') : ?>
                        <a class="nav-link" href="?page=buku_daftar"><div class="sb-nav-link-icon"><i class="fas fa-search"></i></div> Jelajah Buku</a>
                        <a class="nav-link" href="?page=peminjaman"><div class="sb-nav-link-icon"><i class="fas fa-clock"></i></div> Riwayat Peminjaman</a>
                    <?php endif; ?>
                    
                    <a class="nav-link" href="?page=ulasan"><div class="sb-nav-link-icon"><i class="fas fa-comment-dots"></i></div> Ulasan Buku</a>
                </div>
            </div>
            <!-- Bagian Bawah Sidebar -->
            <div class="sb-sidenav-footer d-flex align-items-center gap-3">
                <img src="upload/user/<?= $foto; ?>" style="width:30px;height:30px;border-radius:50%;object-fit:cover;" onerror="this.src='upload/user/default.png'">
                <div>
                    <div class="small opacity-50">Sistem Aktif</div>
                    <div style="font-size: 13px; font-weight: 600;"><?= $nama; ?></div>
                </div>
            </div>
        </nav>
    </div>

    <!-- Area Konten Utama -->
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <?php
                // Mengambil parameter 'page' dari URL (routing sederhana)
                $page = isset($_GET['page']) ? $_GET['page'] : 'dash';

                // Jika halaman yang diminta adalah setelan profil
                if ($page == 'setelan') {
                    $q_data = mysqli_query($koneksi, "SELECT * FROM user WHERE id_user='".$user['id_user']."'");
                    $data = mysqli_fetch_array($q_data);
                ?>
                    <h3 class="mt-4 fw-bold">Pengaturan Akun</h3>
                    <p class="text-muted">Kelola informasi profil dan keamanan akun Anda.</p>
                    
                    <div class="card col-xl-10 mt-4">
                        <div class="card-body p-4">
                            <form method="post" enctype="multipart/form-data">
                                <div class="row align-items-center">
                                    <div class="col-md-4 text-center border-end py-3">
                                        <img src="upload/user/<?= $data['foto'] ?: 'default.png'; ?>" class="avatar-preview mb-3" id="img-preview" onerror="this.src='upload/user/default.png'">
                                        <div class="px-4">
                                            <input type="file" name="foto" class="form-control form-control-sm" id="foto-input" onchange="previewImage()">
                                            <label class="small text-muted mt-2">Format: JPG, PNG. Max 2MB</label>
                                        </div>
                                    </div>
                                    <div class="col-md-8 ps-md-5 py-3">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Nama Lengkap</label>
                                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($data['nama'] ?? $data['name'] ?? ''); ?>" required placeholder="Masukkan nama lengkap">
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label fw-bold small">Password Baru <span class="text-muted fw-normal">(Opsional)</span></label>
                                            <input type="password" name="password" class="form-control" placeholder="Biarkan kosong jika tidak diganti">
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-primary" name="simpan"><i class="fas fa-check-circle me-2"></i>Simpan Perubahan</button>
                                            <a href="index.php" class="btn btn-light text-muted">Batal</a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php 
                } else {
                    // Mencari file PHP sesuai nama page, jika tidak ada tampilkan 404
                    if (file_exists($page . '.php')) {
                        include $page . '.php';
                    } else {
                        include '404.php';
                    }
                }
                ?>
            </div>
        </main>
        <!-- Footer Sistem -->
        <footer class="py-4 bg-white mt-auto border-top">
            <div class="container-fluid px-4">
                <div class="d-flex align-items-center justify-content-between small text-muted">
                    <div>&copy; 2026 <b>Lib Digital-X</b>. All Rights Reserved.</div>
                    <div>Ver 2.0.1</div>
                </div>
            </div>
        </footer>
    </div>
</div>

<script>
    // Fungsi untuk menampilkan preview gambar secara langsung saat memilih file
    function previewImage() {
        const input = document.getElementById('foto-input');
        const preview = document.getElementById('img-preview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) { preview.src = e.target.result; }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/scripts.js"></script>
</body>
</html>