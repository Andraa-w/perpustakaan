<?php
include "koneksi.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user    = $_SESSION['user'];
$id_user = $user['id_user'];
$level   = strtolower($user['level']); 

$alert = '';

// ================= PROSES TAMBAH =================
if (isset($_POST['tambah']) && $level == 'peminjam') {
    $id_buku  = $_POST['id_buku'];
    $rating   = intval($_POST['rating']);
    $komentar = trim(htmlspecialchars($_POST['komentar']));

    $insert_stmt = mysqli_prepare($koneksi, "INSERT INTO ulasan (id_user, id_buku, rating, komentar, tanggal) VALUES (?, ?, ?, ?, NOW())");
    mysqli_stmt_bind_param($insert_stmt, "iiis", $id_user, $id_buku, $rating, $komentar);
    if (mysqli_stmt_execute($insert_stmt)) {
        $alert = "Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Ulasan Anda telah dikirim.', timer: 1800, showConfirmButton: false }).then(() => { window.location.href='?page=ulasan'; });";
    }
}

// ================= PROSES EDIT =================
if (isset($_POST['edit']) && $level == 'peminjam') {
    $id_ulasan = $_POST['id_ulasan'];
    $rating    = intval($_POST['rating']);
    $komentar  = trim(htmlspecialchars($_POST['komentar']));

    $edit_stmt = mysqli_prepare($koneksi, "UPDATE ulasan SET rating = ?, komentar = ? WHERE id_ulasan = ? AND id_user = ?");
    mysqli_stmt_bind_param($edit_stmt, "isii", $rating, $komentar, $id_ulasan, $id_user);
    
    if (mysqli_stmt_execute($edit_stmt)) {
        $alert = "Swal.fire({ icon: 'success', title: 'Diperbarui!', text: 'Ulasan Anda telah diubah.', timer: 1800, showConfirmButton: false }).then(() => { window.location.href='?page=ulasan'; });";
    }
}

// ================= PROSES HAPUS =================
if (isset($_GET['hapus'])) {
    $id_hapus = intval($_GET['hapus']);
    
    if ($level == 'peminjam') {
        $del_stmt = mysqli_prepare($koneksi, "DELETE FROM ulasan WHERE id_ulasan = ? AND id_user = ?");
        mysqli_stmt_bind_param($del_stmt, "ii", $id_hapus, $id_user);
    } else {
        $del_stmt = mysqli_prepare($koneksi, "DELETE FROM ulasan WHERE id_ulasan = ?");
        mysqli_stmt_bind_param($del_stmt, "i", $id_hapus);
    }
    
    if (mysqli_stmt_execute($del_stmt)) {
        $_SESSION['hapus_sukses'] = true;
        header("Location: ?page=ulasan");
        exit();
    }
}

// ================= FETCH DATA =================
$query = mysqli_query($koneksi, "
    SELECT u.*, b.judul AS buku, usr.nama AS peminjam, usr.foto 
    FROM ulasan u
    LEFT JOIN buku b ON u.id_buku = b.id_buku
    LEFT JOIN user usr ON u.id_user = usr.id_user
    ORDER BY u.tanggal DESC
");

$buku_query = mysqli_query($koneksi, "
    SELECT id_buku, judul FROM buku 
    WHERE id_buku NOT IN (SELECT id_buku FROM ulasan WHERE id_user = '$id_user')
");
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root { --primary-gradient: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); }
    .review-card { border: none; border-radius: 20px; background: #fff; border: 1px solid rgba(0,0,0,0.05); transition: 0.2s; }
    .review-card:hover { box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important; }
    .user-img { width: 48px; height: 48px; border-radius: 12px; object-fit: cover; }
    .btn-create { background: var(--primary-gradient); border: none; border-radius: 12px; color: white; padding: 12px 24px; font-weight: 600; }
    .badge-book { background: #f5f3ff; color: #4f46e5; border-radius: 8px; padding: 6px 12px; font-size: 0.8rem; font-weight: 600; display: inline-block; }
    .star-rating { color: #facc15; font-size: 0.9rem; }
    .swal2-popup { border-radius: 20px !important; }
</style>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold mb-1">⭐ Ulasan Koleksi</h2>
            <p class="text-muted mb-0">Bagikan pengalaman membaca Anda.</p>
        </div>
        <?php if($level == 'peminjam' && mysqli_num_rows($buku_query) > 0): ?>
            <button class="btn btn-create shadow-sm" data-bs-toggle="collapse" data-bs-target="#formTambah">
                <i class="fas fa-pen-fancy me-2"></i>Tulis Ulasan
            </button>
        <?php endif; ?>
    </div>

    <div class="collapse mb-5" id="formTambah">
        <div class="card card-body border-0 shadow-sm p-4" style="border-radius: 20px; background: #fafbff;">
            <form method="post">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="small fw-bold mb-2">Pilih Buku</label>
                        <select name="id_buku" class="form-select border-0 shadow-sm" style="border-radius: 10px; padding: 12px;" required>
                            <?php while($b = mysqli_fetch_array($buku_query)) echo "<option value='".$b['id_buku']."'>".$b['judul']."</option>"; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="small fw-bold mb-2">Rating</label>
                        <select name="rating" class="form-select border-0 shadow-sm" style="border-radius: 10px; padding: 12px;">
                            <option value="5">⭐⭐⭐⭐⭐</option>
                            <option value="4">⭐⭐⭐⭐</option>
                            <option value="3">⭐⭐⭐</option>
                            <option value="2">⭐⭐</option>
                            <option value="1">⭐</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <textarea name="komentar" class="form-control border-0 shadow-sm" rows="3" style="border-radius: 10px; padding: 15px;" placeholder="Tulis ulasan..." required></textarea>
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" name="tambah" class="btn btn-primary px-5 fw-bold" style="border-radius: 10px; background: #4f46e5; border: none; padding: 12px;">Kirim</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <?php while($row = mysqli_fetch_array($query)): ?>
        <div class="col-md-6">
            <div class="card review-card shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-3">
                        <div class="d-flex align-items-center">
                            <img src="upload/user/<?= $row['foto'] ?: 'default.png'; ?>" class="user-img me-3 border border-2 border-white">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark"><?= $row['peminjam']; ?></h6>
                                <span class="text-muted" style="font-size: 0.75rem;"><?= date('d M Y', strtotime($row['tanggal'])); ?></span>
                            </div>
                        </div>
                        <div class="star-rating">
                            <?php for($i=1; $i<=5; $i++) echo ($i <= $row['rating']) ? '★' : '<span class="opacity-25">★</span>'; ?>
                        </div>
                    </div>
                    
                    <div class="mb-3"><span class="badge-book"><i class="fas fa-book-open me-2"></i><?= $row['buku']; ?></span></div>
                    <p class="text-secondary mb-4" style="line-height: 1.6;">"<?= $row['komentar']; ?>"</p>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <?php if($row['id_user'] == $id_user && $level == 'peminjam'): ?>
                            <button class="btn btn-sm btn-link text-decoration-none fw-bold text-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id_ulasan'] ?>">
                                <i class="fas fa-edit me-1"></i> Edit
                            </button>
                        <?php endif; ?>

                        <?php if($row['id_user'] == $id_user || $level != 'peminjam'): ?>
                            <button type="button" onclick="confirmDelete(<?= $row['id_ulasan']; ?>)" class="btn btn-sm btn-link text-decoration-none fw-bold text-danger">
                                <i class="fas fa-trash-alt me-1"></i> Hapus
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="editModal<?= $row['id_ulasan'] ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 25px;">
                    <div class="modal-header border-0 px-4 pt-4">
                        <h5 class="fw-bold m-0">Edit Ulasan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="post">
                        <div class="modal-body px-4">
                            <input type="hidden" name="id_ulasan" value="<?= $row['id_ulasan'] ?>">
                            <div class="mb-3">
                                <label class="small fw-bold mb-2">Rating</label>
                                <select name="rating" class="form-select border-0 bg-light" style="border-radius: 12px; padding: 12px;">
                                    <?php for($j=5; $j>=1; $j--): ?>
                                        <option value="<?= $j ?>" <?= ($j == $row['rating']) ? 'selected' : '' ?>>
                                            <?= str_repeat('⭐', $j) ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="small fw-bold mb-2">Komentar</label>
                                <textarea name="komentar" class="form-control border-0 bg-light" rows="4" style="border-radius: 12px; padding: 15px;" required><?= $row['komentar'] ?></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-0 px-4 pb-4">
                            <button type="submit" name="edit" class="btn btn-primary w-100 py-3 fw-bold" style="border-radius: 15px; background: #4f46e5;">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
    <?php echo $alert; ?>

    <?php if (isset($_SESSION['hapus_sukses'])): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil Dihapus',
            text: 'Ulasan telah dihapus.',
            showConfirmButton: false,
            timer: 1500
        });
        <?php unset($_SESSION['hapus_sukses']); ?>
    <?php endif; ?>

    function confirmDelete(id) {
        Swal.fire({
            title: 'Hapus ulasan?',
            text: "Data tidak bisa dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '?page=ulasan&hapus=' + id;
            }
        })
    }
</script>