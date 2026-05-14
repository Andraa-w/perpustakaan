<?php
// Ambil ID dari URL
$id_buku = mysqli_real_escape_string($koneksi, $_GET['id'] ?? '');
$id_user = $_SESSION['user']['id_user'];

// Cek apakah buku ada
$query_buku = mysqli_query($koneksi, "SELECT * FROM buku WHERE id_buku = '$id_buku'");
$data = mysqli_fetch_assoc($query_buku);

if (!$data) {
    echo "<script>alert('Buku tidak ditemukan!'); location.href='index.php?page=buku_daftar';</script>";
    exit();
}

// --- LOGIKA CEK DOUBLE PINJAM ---
$cek_pinjam = mysqli_query($koneksi, "SELECT * FROM peminjaman 
    WHERE id_buku = '$id_buku' 
    AND id_user = '$id_user' 
    AND (status_peminjaman = 'Menunggu persetujuan' OR status_peminjaman = 'dipinjam')");

$sudah_pinjam = mysqli_num_rows($cek_pinjam) > 0;
$stok_habis = ($data['stok'] < 1);

// PROSES INPUT
if (isset($_POST['pinjam'])) { 
    if ($sudah_pinjam || $stok_habis) {
        echo "<script>location.href='index.php?page=buku_daftar';</script>";
        exit();
    }

    // LOGIKA OTOMATIS 2 HARI
    $tgl_pinjam = date('Y-m-d H:i:s'); 
    // Menambah 2 hari dari tanggal sekarang
    $tgl_kembali = date('Y-m-d H:i:s', strtotime('+2 days')); 
    $status = 'Menunggu persetujuan'; 

    $insert = mysqli_query($koneksi, "INSERT INTO peminjaman (id_buku, id_user, tanggal_peminjaman, tanggal_pengembalian, status_peminjaman) 
              VALUES ('$id_buku', '$id_user', '$tgl_pinjam', '$tgl_kembali', '$status')");

    if ($insert) {
        mysqli_query($koneksi, "UPDATE buku SET stok = stok - 1 WHERE id_buku = '$id_buku'");
        echo "<script>
                alert('Berhasil diajukan! Durasi peminjaman adalah 2 hari.');
                location.href='index.php?page=peminjaman'; 
              </script>";
    }
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6"> <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="p-4">
                    <div class="text-center mb-4">
                        <?php $cover = !empty($data['cover']) ? $data['cover'] : 'default.png'; ?>
                        <img src="upload/cover/<?= $cover; ?>" class="rounded shadow-sm mb-3" style="width: 120px; height: 170px; object-fit: cover;" onerror="this.src='upload/cover/default.png'">
                        <h4 class="fw-bold mb-0"><?= htmlspecialchars($data['judul']); ?></h4>
                        <p class="text-muted small">✍️ <?= htmlspecialchars($data['penulis']); ?></p>
                    </div>

                    <form method="post">
                        <div class="bg-light p-3 rounded-3 mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Tanggal Pinjam</span>
                                <span class="fw-bold small"><?= date('d M Y'); ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted small">Durasi Pinjam</span>
                                <span class="badge bg-primary">2 Hari</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted small">Estimasi Kembali</span>
                                <span class="fw-bold text-danger small"><?= date('d M Y', strtotime('+2 days')); ?></span>
                            </div>
                        </div>

                        <?php if($stok_habis): ?>
                            <div class="alert alert-danger py-2 text-center" style="font-size: 0.8rem;">
                                🚫 Stok buku sedang habis.
                            </div>
                        <?php elseif($sudah_pinjam): ?>
                            <div class="alert alert-warning py-2 text-center" style="font-size: 0.8rem;">
                                ⚠️ Anda sudah meminjam buku ini.
                            </div>
                        <?php endif; ?>

                        <div class="d-grid gap-2">
                            <?php if(!$stok_habis && !$sudah_pinjam): ?>
                                <button type="submit" name="pinjam" class="btn btn-success fw-bold py-2 shadow-sm">
                                    📥 KONFIRMASI PINJAM
                                </button>
                            <?php endif; ?>
                            <a href="index.php?page=buku_daftar" class="btn btn-link text-muted btn-sm text-decoration-none">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>