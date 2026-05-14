<?php
// ================= KEAMANAN SESSION =================
// Cek apakah user sudah login, jika tidak ada session maka dilempar ke login.php
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Mengambil level user (admin/petugas/peminjam) dan mengubahnya ke huruf kecil semua
$level = strtolower($_SESSION['user']['level']); 

// ================= GREETING OTOMATIS =================
// Mengatur zona waktu ke Jakarta
date_default_timezone_set('Asia/Jakarta');
$jam = date('H'); // Ambil angka jam format 24 jam

// Menentukan teks sapaan berdasarkan jam saat ini
if ($jam >= 5 && $jam < 11) {
    $greeting = "Selamat Pagi";
} elseif ($jam >= 11 && $jam < 15) {
    $greeting = "Selamat Siang";
} elseif ($jam >= 15 && $jam < 18) {
    $greeting = "Selamat Sore";
} else {
    $greeting = "Selamat Malam";
}

// ================= FUNGSI HELPER =================
/**
 * Fungsi untuk menghitung total baris data dalam sebuah tabel
 * @param mysqli $koneksi : Resource koneksi database
 * @param string $table   : Nama tabel atau query tambahan
 */
function countData($koneksi, $table) {
    $q = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM $table");
    if(!$q) return 0; // Mengembalikan 0 jika query gagal/tabel tidak ditemukan
    $data = mysqli_fetch_assoc($q);
    return $data['total'];
}

// Mengambil nama user dari session untuk ditampilkan di dashboard
$nama_user = $_SESSION['user']['nama'] ?? 'User';
?>

<h1 class="mt-4">
    <?= $greeting; ?>, <?= htmlspecialchars($nama_user); ?> 👋
</h1>

<p class="lead">
    Kamu login sebagai <strong><?= ucfirst($level); ?></strong>
</p>

<div class="mb-4 p-3 rounded shadow-sm" style="background:#f8f9fa; border-left:5px solid #0d6efd;">
    <div class="row">
        <div class="col-md-6">
            <div><strong>Nama:</strong> <?= htmlspecialchars($nama_user); ?></div>
            <div><strong>Level:</strong> <?= ucfirst($level); ?></div>
        </div>
        <div class="col-md-6 text-md-end">
            <strong>Waktu:</strong> <span id="realtime-clock" class="badge bg-dark"></span>
        </div>
    </div>
</div>

<div class="row">

<?php if ($level == 'admin' || $level == 'petugas') : ?>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-primary text-white mb-4 shadow">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0"><?= countData($koneksi, 'buku'); ?></h3>
                    <span>Total Buku</span>
                </div>
                <i class="fas fa-book fa-2x opacity-50"></i>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a class="small text-white stretched-link" href="?page=buku">View Details</a>
                <i class="fas fa-angle-right"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card bg-success text-white mb-4 shadow">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0"><?= countData($koneksi, 'ulasan'); ?></h3>
                    <span>Total Ulasan</span>
                </div>
                <i class="fas fa-star fa-2x opacity-50"></i>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a class="small text-white stretched-link" href="?page=ulasan">View Details</a>
                <i class="fas fa-angle-right"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card bg-secondary text-white mb-4 shadow">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0"><?= countData($koneksi, 'peminjaman'); ?></h3>
                    <span>Total Pinjam</span>
                </div>
                <i class="fas fa-hand-holding fa-2x opacity-50"></i>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a class="small text-white stretched-link" href="?page=laporan_peminjaman">View Details</a>
                <i class="fas fa-angle-right"></i>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($level == 'admin') : ?>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-danger text-white mb-4 shadow">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0"><?= countData($koneksi, 'user'); ?></h3>
                    <span>Total User</span>
                </div>
                <i class="fas fa-users fa-2x opacity-50"></i>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a class="small text-white stretched-link" href="?page=user">View Details</a>
                <i class="fas fa-angle-right"></i>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($level == 'peminjam') : ?>
    <div class="col-xl-4 col-md-6">
        <div class="card bg-warning text-dark mb-4 shadow">
            <div class="card-body">
                <h5>Cari Buku</h5>
                <p>Temukan buku favoritmu di sini.</p>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a class="small text-dark stretched-link" href="?page=buku_daftar">Buka Katalog</a>
                <i class="fas fa-angle-right"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6">
        <div class="card bg-info text-white mb-4 shadow">
            <div class="card-body">
                <h5>Peminjamanku</h5>
                <p>Kamu meminjam <?= countData($koneksi, "peminjaman WHERE id_user='".$_SESSION['user']['id_user']."'"); ?> buku.</p>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a class="small text-white stretched-link" href="?page=peminjaman">Riwayat Pinjam</a>
                <i class="fas fa-angle-right"></i>
            </div>
        </div>
    </div>
<?php endif; ?>

</div>

<script>
function updateClock() {
    const el = document.getElementById('realtime-clock');
    if (!el) return;
    const now = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    
    // Format tanggal versi Indonesia
    const tanggal = now.toLocaleDateString('id-ID', options);
    // Format waktu versi Indonesia
    const waktu = now.toLocaleTimeString('id-ID');
    
    el.innerHTML = tanggal + " - " + waktu;
}
// Jalankan fungsi updateClock setiap 1000 milidetik (1 detik)
setInterval(updateClock, 1000);
// Panggil pertama kali agar jam langsung muncul saat halaman diload
updateClock();
</script>