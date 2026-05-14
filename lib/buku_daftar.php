<?php
include "koneksi.php";

// ================= SESSION CHECK =================
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['keranjang_buku'])) {
    $_SESSION['keranjang_buku'] = [];
}

$user_level = isset($_SESSION['user']['level']) ? strtolower($_SESSION['user']['level']) : '';

// ================= LOGIK KERANJANG SESSION =================
if (isset($_GET['action_cart'])) {
    $id_bk = $_GET['id_buku'];
    $status_cart = $_GET['status_cart'];

    if ($status_cart == 'tambah') {
        if (!in_array($id_bk, $_SESSION['keranjang_buku'])) {
            $_SESSION['keranjang_buku'][] = $id_bk;
        }
    } else {
        if (($key = array_search($id_bk, $_SESSION['keranjang_buku'])) !== false) {
            unset($_SESSION['keranjang_buku'][$key]);
            $_SESSION['keranjang_buku'] = array_values($_SESSION['keranjang_buku']);
        }
    }
    echo "<script>window.location.href='" . $_SERVER['HTTP_REFERER'] . "';</script>";
    exit();
}

// ================= PROSES AJUKAN PINJAMAN (MENUNGGU PERSETUJUAN) =================
if (isset($_GET['konfirmasi_pinjam'])) {
    if (empty($_SESSION['keranjang_buku'])) {
        echo "<script>alert('Keranjang masih kosong!'); window.location.href='index.php?page=buku_daftar';</script>";
        exit();
    }

    $id_user = $_SESSION['user']['id_user'];
    $tgl_pengajuan = date('Y-m-d');
    
    // Status disamakan dengan pengecekan di peminjaman.php
    $status_awal = 'Menunggu persetujuan'; 

    $success_count = 0;

    foreach ($_SESSION['keranjang_buku'] as $id_buku) {
        // Cek stok tersedia
        $cek_stok = mysqli_query($koneksi, "SELECT stok FROM buku WHERE id_buku = '$id_buku'");
        $s = mysqli_fetch_assoc($cek_stok);

        if ($s['stok'] > 0) {
            // Simpan ke tabel peminjaman
            $insert = mysqli_query($koneksi, "INSERT INTO peminjaman (id_user, id_buku, tanggal_peminjaman, status_peminjaman) 
                                             VALUES ('$id_user', '$id_buku', '$tgl_pengajuan', '$status_awal')");
            if ($insert) {
                $success_count++;
            }
        }
    }

    // Kosongkan keranjang setelah berhasil
    $_SESSION['keranjang_buku'] = [];
    
    echo "<script>
        alert('Pengajuan berhasil! Menunggu persetujuan Admin.');
        window.location.href='index.php?page=peminjaman'; 
    </script>";
    exit();
}

$total_keranjang = count($_SESSION['keranjang_buku']);

// ================= FILTER LOGIC =================
$kategori_filter = $_GET['kategori'] ?? '';
$search = $_GET['search'] ?? '';

$where = "1=1";
if($kategori_filter) {
    $where .= " AND kategori_buku_relasi.id_kategori = '" . mysqli_real_escape_string($koneksi, $kategori_filter) . "'";
}
if($search) {
    $where .= " AND (buku.judul LIKE '%" . mysqli_real_escape_string($koneksi, $search) . "%' OR buku.penulis LIKE '%" . mysqli_real_escape_string($koneksi, $search) . "%')";
}

$query = mysqli_query($koneksi, "
    SELECT buku.*, GROUP_CONCAT(kategori.kategori SEPARATOR ', ') AS daftar_kategori 
    FROM buku 
    LEFT JOIN kategori_buku_relasi ON buku.id_buku = kategori_buku_relasi.id_buku 
    LEFT JOIN kategori ON kategori_buku_relasi.id_kategori = kategori.id_kategori 
    WHERE $where 
    GROUP BY buku.id_buku 
    ORDER BY buku.id_buku DESC
");

$kategori_all = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY kategori ASC");
?>

<style>
    .book-card { border: none; border-radius: 15px; transition: 0.3s; background: #fff; height: 100%; cursor: pointer; position: relative; overflow: hidden; }
    .book-card:hover { transform: translateY(-8px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
    .card-img-container { position: relative; height: 280px; background: #f8f9fa; }
    .card-img-top { height: 100%; width: 100%; object-fit: cover; }
    .category-badge { position: absolute; top: 12px; left: 12px; background: rgba(99, 102, 241, 0.9); color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.7rem; z-index: 5; font-weight: bold; }
    .stok-habis-badge { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; z-index: 4; pointer-events: none; }
    .floating-cart { position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; background: #6366f1; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white !important; font-size: 24px; box-shadow: 0 10px 25px rgba(99, 102, 241, 0.4); z-index: 1000; border: 3px solid white; }
    .cart-badge { position: absolute; top: -5px; right: -5px; background: #ef4444; color: white; font-size: 11px; font-weight: bold; padding: 3px 7px; border-radius: 50%; border: 2px solid white; }
    .search-section { background: white; padding: 20px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
</style>

<div class="container-fluid py-4">
    <div class="mb-4 text-center">
        <h3 class="fw-bold text-dark mb-1">📚 Katalog Perpustakaan</h3>
        <p class="text-muted small">Pilih buku favoritmu dan ajukan pinjaman secara online.</p>
    </div>

    <div class="search-section mb-4">
        <form method="GET" class="row g-3">
            <input type="hidden" name="page" value="buku_daftar"> 
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Cari judul atau penulis..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-4">
                <select name="kategori" class="form-select">
                    <option value="">Semua Kategori</option>
                    <?php while($kat = mysqli_fetch_assoc($kategori_all)): ?>
                        <option value="<?= $kat['id_kategori'] ?>" <?= $kategori_filter == $kat['id_kategori'] ? 'selected' : '' ?>><?= $kat['kategori'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">Filter</button>
                <a href="index.php?page=buku_daftar" class="btn btn-outline-secondary shadow-sm"><i class="fas fa-sync"></i></a>
            </div>
        </form>
    </div>

    <div class="row g-4">
    <?php while($data = mysqli_fetch_assoc($query)): 
        $is_in_session = in_array($data['id_buku'], $_SESSION['keranjang_buku']);
        $stok_kosong = ($data['stok'] <= 0);
    ?>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card book-card shadow-sm" data-bs-toggle="modal" data-bs-target="#detailModal<?= $data['id_buku']; ?>">
                <div class="card-img-container">
                    <span class="category-badge shadow-sm"><?= $data['daftar_kategori'] ?: 'Umum'; ?></span>
                    <?php if ($stok_kosong): ?>
                        <div class="stok-habis-badge text-uppercase">Habis</div>
                    <?php endif; ?>
                    <img src="upload/cover/<?= $data['cover'] ?: 'default.png'; ?>" class="card-img-top" onerror="this.src='upload/cover/default.png'">
                </div>
                <div class="card-body p-3 text-center">
                    <h6 class="fw-bold mb-1 text-truncate"><?= $data['judul']; ?></h6>
                    <small class="text-muted"><?= $data['penulis']; ?></small>
                </div>
            </div>
        </div>

        <div class="modal fade" id="detailModal<?= $data['id_buku']; ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                    <div class="modal-body p-0">
                        <div class="row g-0">
                            <div class="col-md-5">
                                <img src="upload/cover/<?= $data['cover'] ?: 'default.png'; ?>" class="img-fluid h-100" style="object-fit: cover; min-height: 400px; border-radius: 20px 0 0 20px;">
                            </div>
                            <div class="col-md-7 p-4">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h3 class="fw-bold mb-0"><?= $data['judul']; ?></h3>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <span class="badge bg-primary-subtle text-primary mt-2 mb-3 px-3 py-2 rounded-pill"><?= $data['daftar_kategori'] ?: 'Umum'; ?></span>
                                
                                <div class="row mb-3 g-2 text-center small">
                                    <div class="col-6"><div class="p-2 border rounded-3 bg-light"><span class="text-muted d-block small">Penulis</span><strong><?= $data['penulis']; ?></strong></div></div>
                                    <div class="col-6"><div class="p-2 border rounded-3 bg-light"><span class="text-muted d-block small">Stok</span><strong><?= $data['stok']; ?> Buku</strong></div></div>
                                </div>

                                <h6 class="fw-bold small">Sinopsis:</h6>
                                <p class="text-muted small mb-4" style="line-height: 1.6;"><?= nl2br($data['deskripsi'] ?: 'Tidak ada sinopsis untuk buku ini.'); ?></p>
                                
                                <div class="mt-auto pt-3">
                                    <?php if ($user_level === 'peminjam'): ?>
                                        <?php if ($stok_kosong): ?>
                                            <button class="btn btn-secondary btn-lg w-100 rounded-pill fw-bold" disabled>Stok Habis</button>
                                        <?php else: ?>
                                            <div class="d-flex gap-2">
                                                <a href="index.php?page=buku_daftar&action_cart=true&id_buku=<?= $data['id_buku']; ?>&status_cart=<?= $is_in_session ? 'hapus' : 'tambah'; ?>" 
                                                   class="btn <?= $is_in_session ? 'btn-danger' : 'btn-outline-primary'; ?> btn-lg rounded-pill px-4 shadow-sm">
                                                    <i class="fas <?= $is_in_session ? 'fa-minus' : 'fa-plus'; ?>"></i>
                                                </a>
                                                <a href="index.php?page=pinjam&id=<?= $data['id_buku']; ?>" class="btn btn-primary btn-lg w-100 rounded-pill fw-bold shadow-sm">Pinjam Sekarang</a>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
    </div>
</div>

<div class="modal fade" id="modalKeranjang" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold">🛒 Keranjang Pinjaman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <?php if ($total_keranjang > 0): ?>
                    <div class="list-group list-group-flush border-0">
                        <?php
                        $ids = implode("','", $_SESSION['keranjang_buku']);
                        $q_cart = mysqli_query($koneksi, "SELECT * FROM buku WHERE id_buku IN ('$ids')");
                        while($item = mysqli_fetch_assoc($q_cart)): ?>
                            <div class="list-group-item d-flex align-items-center p-3 px-0 border-0">
                                <img src="upload/cover/<?= $item['cover'] ?: 'default.png'; ?>" width="50" height="70" class="rounded me-3 shadow-sm" style="object-fit: cover;">
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 fw-bold small"><?= $item['judul']; ?></h6>
                                    <small class="<?= ($item['stok'] <= 0) ? 'text-danger fw-bold' : 'text-muted'; ?>">
                                        <?= ($item['stok'] <= 0) ? 'Stok Habis' : 'Tersedia: ' . $item['stok']; ?>
                                    </small>
                                </div>
                                <a href="index.php?page=buku_daftar&action_cart=true&id_buku=<?= $item['id_buku']; ?>&status_cart=hapus" class="btn btn-sm text-danger shadow-none">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <div class="mt-4">
                        <a href="index.php?page=buku_daftar&konfirmasi_pinjam=true" class="btn btn-primary w-100 rounded-pill fw-bold py-3 shadow-sm" onclick="return confirm('Ajukan pinjaman untuk buku-buku ini?')">
                            <i class="fas fa-paper-plane me-2"></i> Konfirmasi Pengajuan
                        </a>
                        <p class="text-center text-muted mt-2" style="font-size: 11px;">Admin akan memvalidasi pengajuan Anda dalam 1x24 jam.</p>
                    </div>

                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-basket fa-3x text-light mb-3"></i>
                        <p class="text-muted mb-0">Keranjang Anda masih kosong.</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Lanjut Cari Buku</button>
            </div>
        </div>
    </div>
</div>

<?php if ($user_level === 'peminjam'): ?>
<button class="floating-cart shadow-lg border-0" data-bs-toggle="modal" data-bs-target="#modalKeranjang">
    <i class="fas fa-shopping-basket"></i>
    <?php if($total_keranjang > 0): ?>
        <span class="cart-badge shadow-sm"><?= $total_keranjang; ?></span>
    <?php endif; ?>
</button>
<?php endif; ?>