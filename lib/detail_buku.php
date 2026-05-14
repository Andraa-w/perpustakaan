<?php
include "koneksi.php";
header("Location: index.php?page=katalog_favorit");
exit();

// Pastikan ada ID buku
$id_buku = intval($_GET['id'] ?? 0);
if ($id_buku <= 0) {
    echo "<div class='alert alert-danger'>Buku tidak ditemukan.</div>";
    exit();
}

// ================== QUERY PERBAIKAN (Many-to-Many) ==================
// Gunakan GROUP_CONCAT untuk menggabungkan banyak kategori menjadi satu string
$sql = "SELECT buku.*, GROUP_CONCAT(kategori.kategori SEPARATOR ', ') AS nama_kategori
        FROM buku
        LEFT JOIN kategori_buku_relasi ON buku.id_buku = kategori_buku_relasi.id_buku
        LEFT JOIN kategori ON kategori_buku_relasi.id_kategori = kategori.id_kategori
        WHERE buku.id_buku = '$id_buku'
        GROUP BY buku.id_buku";

$query = mysqli_query($koneksi, $sql);
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<div class='alert alert-danger'>Buku tidak ditemukan di database.</div>";
    exit();
}

// Cover default
$cover = !empty($data['cover']) ? $data['cover'] : 'default.png';
?>

<h3 class="mt-4 mb-4">📖 Detail Buku</h3>

<div class="card mb-4 shadow-sm" style="max-width:700px;">
    <div class="row g-0">
        <div class="col-md-4 bg-light d-flex align-items-center justify-content-center">
            <img src="upload/cover/<?= htmlspecialchars($cover); ?>" 
                 class="img-fluid rounded-start p-2" 
                 style="max-height: 350px; object-fit: contain;"
                 onerror="this.src='upload/cover/default.png'">
        </div>
        <div class="col-md-8">
            <div class="card-body">
                <h4 class="card-title fw-bold"><?= htmlspecialchars($data['judul']); ?></h4>
                <hr>
                <p class="card-text mb-1"><strong>Penulis:</strong> <?= htmlspecialchars($data['penulis']); ?></p>
                <p class="card-text mb-1"><strong>Penerbit:</strong> <?= htmlspecialchars($data['penerbit']); ?></p>
                <p class="card-text mb-1"><strong>Kategori:</strong> 
                    <span class="badge bg-info text-dark"><?= htmlspecialchars($data['nama_kategori'] ?: 'Tanpa Kategori'); ?></span>
                </p>
                <p class="card-text mb-1"><strong>Tahun Terbit:</strong> <?= htmlspecialchars($data['tahun_terbit']); ?></p>
                <p class="card-text mb-3"><strong>Stok:</strong> <?= $data['stok']; ?></p>
                
                <p class="card-text"><strong>Deskripsi:</strong><br>
                    <small class="text-muted"><?= nl2br(htmlspecialchars($data['deskripsi'] ?? '-')); ?></small>
                </p>
                
                <?php if(isset($_SESSION['user']['level']) && $_SESSION['user']['level'] === 'peminjam') { ?>
                    <a href="?page=pinjam&id=<?= $data['id_buku']; ?>" class="btn btn-success mt-2">📥 Pinjam Buku</a>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<a href="?page=katalog_favorit" class="btn btn-secondary">⬅ Kembali ke Katalog</a>