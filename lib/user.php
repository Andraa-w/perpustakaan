<?php
include "koneksi.php";

// ================= HAPUS USER =================
if(isset($_GET['hapus'])) {
    $id_user = intval($_GET['hapus']);
    
    // Ambil info foto dulu agar file fisiknya bisa dihapus dari folder
    $cek = mysqli_query($koneksi, "SELECT foto FROM user WHERE id_user='$id_user'");
    $dt = mysqli_fetch_array($cek);
    
    if(!empty($dt['foto']) && $dt['foto'] != 'default_user.png') {
        @unlink("upload/user/" . $dt['foto']);
    }

    mysqli_query($koneksi, "DELETE FROM user WHERE id_user='$id_user'");
    echo "<script>alert('User berhasil dihapus'); location='?page=user';</script>";
}

// Ambil data user, urutkan berdasarkan name terbaru
$data = mysqli_query($koneksi, "SELECT * FROM user ORDER BY nama ASC");
?>

<div class="container-fluid px-4">
    <h3 class="mt-4 mb-4">👥 Daftar Pengguna Sistem</h3>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <!-- Link ke halaman tambah_user.php -->
            <a href="?page=tambah_user" class="btn btn-primary mb-3 shadow-sm">
                <i class="fas fa-plus"></i> Tambah User Baru
            </a>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th width="50">No</th>
                            <th>Foto</th>
                            <th>Nama Lengkap</th>
                            <th>Username</th>
                            <th>Level</th>
                            <th width="180">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $no = 1;
                    while($d = mysqli_fetch_array($data)) {
                        // Cek apakah file foto ada, jika tidak pakai default
                        $foto_path = "upload/user/" . (!empty($d['foto']) ? $d['foto'] : 'default_user.png');
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td>
                            <img src="<?= $foto_path; ?>" alt="Profile" 
                                 width="45" height="45" 
                                 style="border-radius:50%; object-fit:cover;" class="border shadow-sm">
                        </td>
                        <td class="fw-bold"><?= htmlspecialchars($d['nama']); ?></td>
                        <td><?= htmlspecialchars($d['username']); ?></td>
                        <td>
                            <span class="badge <?= $d['level'] == 'admin' ? 'bg-primary' : 'bg-info text-light'; ?>">
                                <?= strtoupper($d['level']); ?>
                            </span>
                        </td>
                        <td>
                            <!-- Tombol Edit: kirim ID lewat URL -->
                            <a href="?page=edit_user&id=<?= $d['id_user']; ?>" class="btn btn-warning btn-sm shadow-sm">
                                ✏️ Edit
                            </a>
                            <!-- Tombol Hapus: konfirmasi javascript -->
                            <a href="?page=user&hapus=<?= $d['id_user']; ?>" 
                               class="btn btn-danger btn-sm shadow-sm"
                               onclick="return confirm('Yakin ingin menghapus <?= $d['nama']; ?>?')">
                                🗑 Hapus
                            </a>
                        </td>
                    </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>