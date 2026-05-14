<?php
include "koneksi.php";

// ================= FILTER =================
$kategori_filter = $_GET['kategori'] ?? '';
$search = $_GET['search'] ?? '';

$where = "1=1";
if ($kategori_filter) {
    $where .= " AND kategori_buku_relasi.id_kategori = '".mysqli_real_escape_string($koneksi, $kategori_filter)."'";
}
if ($search) {
    $where .= " AND buku.judul LIKE '%".mysqli_real_escape_string($koneksi, $search)."%'";
}

// ================= QUERY KATALOG =================
$sql = "SELECT buku.*, 
               GROUP_CONCAT(kategori.kategori SEPARATOR ', ') AS nama_kategori,
               (SELECT COUNT(*) FROM peminjaman WHERE peminjaman.id_buku = buku.id_buku) as total_pinjam
        FROM buku 
        LEFT JOIN kategori_buku_relasi ON buku.id_buku = kategori_buku_relasi.id_buku 
        LEFT JOIN kategori ON kategori_buku_relasi.id_kategori = kategori.id_kategori 
        WHERE $where
        GROUP BY buku.id_buku";

$res_data = mysqli_query($koneksi, $sql);
$kategori_all = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY kategori ASC");

function find_cover_file($filename) {
    $folder = "upload/cover/";
    if (empty($filename)) return $folder . 'default.png';
    $path = $folder . $filename;
    return file_exists($path) ? $path : $folder . 'default.png';
}
?>

<h3 class="mt-4 mb-4 fw-bold text-primary">❤️ Katalog Favorit</h3>

<form method="get" class="row mb-4 g-2">
    <input type="hidden" name="page" value="katalog_favorit">
    <div class="col-md-4">
        <select name="kategori" class="form-select shadow-sm">
            <option value="">-- Semua Kategori --</option>
            <?php while($k = mysqli_fetch_assoc($kategori_all)) { ?>
                <option value="<?= $k['id_kategori']; ?>" <?= $kategori_filter == $k['id_kategori'] ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($k['kategori']); ?>
                </option>
            <?php } ?>
        </select>
    </div>
    <div class="col-md-6">
        <input type="text" name="search" class="form-control shadow-sm" placeholder="Cari judul buku..." value="<?= htmlspecialchars($search); ?>">
    </div>
    <div class="col-md-2">
        <button class="btn btn-primary w-100 fw-bold shadow-sm">Cari</button>
    </div>
</form>

<div class="row">
<?php 
while($row = mysqli_fetch_assoc($res_data)) { 
    $cover_path = find_cover_file($row['cover']);
?>
    <div class="col-md-3 mb-4">
        <div class="card h-100 shadow-sm border-0" style="transition: 0.3s;">
            <img src="<?= htmlspecialchars($cover_path); ?>" 
                 class="card-img-top" 
                 style="height:250px; object-fit:cover;"
                 onerror="this.src='upload/cover/default.png'">

            <div class="card-body d-flex flex-column">
                <h6 class="card-title fw-bold text-truncate" title="<?= htmlspecialchars($row['judul']); ?>">
                    <?= htmlspecialchars($row['judul']); ?>
                </h6>
                <p class="text-muted mb-1 small">✍️ <?= htmlspecialchars($row['penulis']); ?></p>
                <p class="text-muted mb-1 small text-truncate">🏷️ <?= htmlspecialchars($row['nama_kategori'] ?: '-'); ?></p>
                
                <p class="text-warning mb-2 small fw-bold">⭐ Dipinjam <?= $row['total_pinjam']; ?> kali</p>

                <div class="mt-auto">
                    <button type="button" class="btn btn-sm btn-outline-primary w-100 mb-2 fw-bold" 
                            data-bs-toggle="modal" 
                            data-bs-target="#modalDetail<?= $row['id_buku']; ?>">
                        🔍 Detail
                    </button>

                    <?php if(isset($_SESSION['user']['level']) && $_SESSION['user']['level'] === 'Peminjam') { ?>
                        <a href="?page=pinjam&id=<?= $row['id_buku']; ?>" 
                           class="btn btn-sm btn-success w-100 fw-bold">
                           📥 Pinjam
                        </a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetail<?= $row['id_buku']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 bg-light">
                    <h5 class="modal-title fw-bold text-primary">📖 Informasi Lengkap</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-5 mb-3 mb-md-0 text-center">
                            <img src="<?= htmlspecialchars($cover_path); ?>" class="img-fluid rounded shadow" style="max-height: 380px;" onerror="this.src='upload/cover/default.png'">
                        </div>
                        <div class="col-md-7">
                            <h4 class="fw-bold text-dark"><?= htmlspecialchars($row['judul']); ?></h4>
                            <hr>
                            <table class="table table-sm table-borderless small">
                                <tr><td width="35%"><strong>Penulis</strong></td><td>: <?= htmlspecialchars($row['penulis']); ?></td></tr>
                                <tr><td><strong>Penerbit</strong></td><td>: <?= htmlspecialchars($row['penerbit']); ?></td></tr>
                                <tr><td><strong>Tahun</strong></td><td>: <?= htmlspecialchars($row['tahun_terbit']); ?></td></tr>
                                <tr><td><strong>Kategori</strong></td><td>: <span class="badge bg-info text-dark"><?= htmlspecialchars($row['nama_kategori'] ?: '-'); ?></span></td></tr>
                                <tr><td><strong>Stok</strong></td><td>: <span class="badge <?= $row['stok'] > 0 ? 'bg-success' : 'bg-danger' ?>"><?= $row['stok']; ?> unit</span></td></tr>
                            </table>
                            
                            <div class="mt-3">
                                <strong class="d-block mb-2 text-dark">Sinopsis / Deskripsi:</strong>
                                <div class="p-3 bg-light rounded" 
                                     style="max-height: 180px; overflow-y: auto; border: 1px solid #eee; line-height: 1.5;">
                                    <p class="small text-secondary mb-0">
                                        <?= nl2br(htmlspecialchars($row['deskripsi'] ?: 'Tidak ada deskripsi tersedia untuk buku ini.')); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Tutup</button>
                    <?php if(isset($_SESSION['user']['level']) && $_SESSION['user']['level'] === 'Peminjam' && $row['stok'] > 0) { ?>
                        <a href="?page=pinjam&id=<?= $row['id_buku']; ?>" class="btn btn-success px-4 fw-bold">Pinjam Sekarang</a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
</div>