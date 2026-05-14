<?php
include "koneksi.php";

// ================= SESSION CHECK =================
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Cek apakah ada isi di keranjang session
$is_empty = empty($_SESSION['keranjang_buku']);

// ================= LOGIKA HAPUS DARI KERANJANG =================
if (isset($_GET['hapus_id'])) {
    $id_hapus = $_GET['hapus_id'];
    if (($key = array_search($id_hapus, $_SESSION['keranjang_buku'])) !== false) {
        unset($_SESSION['keranjang_buku'][$key]);
        $_SESSION['keranjang_buku'] = array_values($_SESSION['keranjang_buku']); // Reset index
    }
    header("Location: index.php?page=keranjang"); // Refresh halaman
    exit();
}

?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">🛒 Keranjang Koleksi</h3>
            <p class="text-muted small mb-0">Daftar buku yang Anda tandai untuk dibaca nanti.</p>
        </div>
        <a href="index.php?page=buku_daftar" class="btn btn-outline-primary btn-sm rounded-pill">
            <i class="fas fa-arrow-left me-2"></i> Kembali ke Katalog
        </a>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <?php if ($is_empty): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-basket fa-4x text-light mb-3"></i>
                            <h5 class="text-muted">Keranjang Anda masih kosong.</h5>
                            <p class="small text-muted">Silakan pilih buku di katalog terlebih dahulu.</p>
                            <a href="index.php?page=buku_daftar" class="btn btn-primary mt-3 px-4 rounded-pill">Cari Buku</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">No</th>
                                        <th width="100">Cover</th>
                                        <th>Judul Buku</th>
                                        <th>Penulis</th>
                                        <th>Stok</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    // Ambil semua ID dari session dan gabungkan jadi string (misal: '1','4','7')
                                    $ids = implode("','", $_SESSION['keranjang_buku']);
                                    
                                    // Query ambil data buku yang ID-nya ada di dalam session
                                    $query = mysqli_query($koneksi, "SELECT * FROM buku WHERE id_buku IN ('$ids')");
                                    
                                    while($row = mysqli_fetch_assoc($query)):
                                        $cover = !empty($row['cover']) ? $row['cover'] : 'default.png';
                                    ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td>
                                            <img src="upload/cover/<?= $cover; ?>" class="rounded" width="60" height="80" style="object-fit: cover;">
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($row['judul']); ?></div>
                                        </td>
                                        <td><?= htmlspecialchars($row['penulis']); ?></td>
                                        <td>
                                            <?php if($row['stok'] > 0): ?>
                                                <span class="badge bg-success-subtle text-success"><?= $row['stok']; ?> Tersedia</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-subtle text-danger">Habis</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group gap-2">
                                                <a href="index.php?page=pinjam&id=<?= $row['id_buku']; ?>" 
                                                   class="btn btn-sm btn-primary rounded-pill px-3 <?= ($row['stok'] <= 0) ? 'disabled' : ''; ?>">
                                                    Pinjam
                                                </a>
                                                <a href="index.php?page=keranjang&hapus_id=<?= $row['id_buku']; ?>" 
                                                   class="btn btn-sm btn-outline-danger rounded-circle" 
                                                   onclick="return confirm('Hapus dari koleksi?')" 
                                                   title="Hapus">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4 p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                            <span class="small text-muted">Total: <strong><?= $total_keranjang; ?></strong> buku dipilih.</span>
                            <span class="small text-warning italic">*Koleksi ini bersifat sementara selama Anda login.</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .table thead th { border: none; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; }
    .table tbody td { border-bottom: 1px solid #f8f9fa; font-size: 0.95rem; padding: 15px 10px; }
    .bg-success-subtle { background-color: #d1e7dd; }
    .bg-danger-subtle { background-color: #f8d7da; }
</style>