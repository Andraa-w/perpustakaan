<?php
include "koneksi.php";

// Ambil ID dari URL
$id = intval($_GET['id']);

// Ambil data user yang akan diedit
$query = mysqli_query($koneksi, "SELECT * FROM user WHERE id_user='$id'");
$data  = mysqli_fetch_array($query);

// Jika data tidak ditemukan
if (!$data) {
    echo "<script>alert('Data tidak ditemukan!'); location='?page=user';</script>";
    exit;
}

// ================== PROSES UPDATE ==================
if (isset($_POST['update'])) {
    $nama     = $_POST['nama'];
    $username = $_POST['username'];
    $level    = $_POST['level'];

    // 1. Logika Update Password (Hanya jika diisi)
    $pass_query = "";
    if (!empty($_POST['password'])) {
        $pass_baru  = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $pass_query = ", password='$pass_baru'";
    }

    // 2. Logika Update Foto Profil
    $foto = $_FILES['foto']['name'];
    $tmp  = $_FILES['foto']['tmp_name'];
    $foto_query = "";

    if ($foto != "") {
        $name_foto = time() . "_" . $foto;
        if (move_uploaded_file($tmp, "upload/user/" . $name_foto)) {
            // Hapus foto lama dari folder (kecuali default.png)
            if ($data['foto'] != "default.png" && file_exists("upload/user/" . $data['foto'])) {
                @unlink("upload/user/" . $data['foto']);
            }
            $foto_query = ", foto='$name_foto'";
        }
    }

    // 3. Jalankan Query Update
    $update = mysqli_query($koneksi, "UPDATE user SET 
        nama     = '$nama', 
        username = '$username', 
        level    = '$level' 
        $pass_query 
        $foto_query 
        WHERE id_user = '$id'");

    if ($update) {
        echo "<script>alert('Data berhasil diperbarui'); location='?page=user';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data');</script>";
    }
}
?>

<div class="container-fluid px-4">
    <h3 class="mt-4 mb-4">✏️ Edit Pengguna</h3>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body bg-light">
            <form method="post" enctype="multipart/form-data">
                <div class="row">
                    <!-- Sisi Kiri: Preview Foto -->
                    <div class="col-md-3 text-center mb-3">
                        <label class="fw-bold d-block mb-2">Foto Profil Saat Ini</label>
                        <img src="upload/user/<?= !empty($data['foto']) ? $data['foto'] : 'default.png'; ?>" 
                             class="img-thumbnail rounded-circle shadow-sm" 
                             style="width: 150px; height: 150px; object-fit: cover;">
                        <div class="mt-3">
                            <input type="file" name="foto" class="form-control form-control-sm" accept="image/*">
                            <small class="text-muted">Pilih file jika ingin ganti foto</small>
                        </div>
                    </div>

                    <!-- Sisi Kanan: Form Data -->
                    <div class="col-md-9">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="small fw-bold">Nama Lengkap</label>
                                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($data['nama']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold">Username</label>
                                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($data['username']); ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="small fw-bold">Level Akses</label>
                                <select name="level" class="form-control" required>
                                    <option value="admin" <?= ($data['level'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                    <option value="petugas" <?= ($data['level'] == 'petugas') ? 'selected' : ''; ?>>Petugas</option>
                                    <option value="peminjam" <?= ($data['level'] == 'peminjam') ? 'selected' : ''; ?>>Peminjam</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold">Ganti Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diubah">
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" name="update" class="btn btn-primary px-4 fw-bold shadow-sm">
                                💾 Simpan Perubahan
                            </button>
                            <a href="?page=user" class="btn btn-secondary px-4 shadow-sm">Batal</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>