<?php
include "koneksi.php"; // Menghubungkan ke database

$edit = false; // Flag untuk mode edit

// ================== LOGIKA AMBIL DATA (EDIT MODE) ==================
if (isset($_GET['edit'])) {
    $edit = true;
    $id = mysqli_real_escape_string($koneksi, $_GET['edit']);
    $data_edit = mysqli_fetch_array(mysqli_query($koneksi, "SELECT * FROM user WHERE id_user='$id'"));
}

// ================== PROSES TAMBAH USER (CREATE) ==================
if (isset($_POST['tambah'])) {
    $nama     = mysqli_real_escape_string($koneksi, $_POST['name']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $level    = $_POST['level'];

    $cek_tambah = mysqli_query($koneksi, "SELECT username FROM user WHERE username='$username'");
    if (mysqli_num_rows($cek_tambah) > 0) {
        $msg_type = "error";
        $msg_title = "Username Duplikat!";
        $msg_text = "Username [@$username] sudah ada yang punya. Cari yang lain ya!";
    } else {
        $foto = $_FILES['foto']['name'];
        $tmp  = $_FILES['foto']['tmp_name'];
        $nama_foto = ($foto != "") ? time() . "_" . $foto : "default.png";
        if ($foto != "") move_uploaded_file($tmp, "upload/user/" . $nama_foto);

        $query_tambah = "INSERT INTO user (nama, username, password, level, foto) 
                         VALUES ('$nama', '$username', '$password', '$level', '$nama_foto')";
        
        if (mysqli_query($koneksi, $query_tambah)) {
            $msg_type = "success";
            $msg_title = "Berhasil!";
            $msg_text = "User baru $nama sudah resmi terdaftar.";
            $redirect = "?page=user";
        }
    }
}

// ================== PROSES UPDATE USER (UPDATE) ==================
if (isset($_POST['update'])) {
    $id       = mysqli_real_escape_string($koneksi, $_GET['edit']);
    $nama     = mysqli_real_escape_string($koneksi, $_POST['name']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $level    = $_POST['level'];

    $cek_update = mysqli_query($koneksi, "SELECT username FROM user WHERE username='$username' AND id_user != '$id'");
    if (mysqli_num_rows($cek_update) > 0) {
        $msg_type = "warning";
        $msg_title = "Waduh!";
        $msg_text = "Username [$username] gagal dipakai karena sudah ada pemiliknya.";
    } else {
        $pass_query = (!empty($_POST['password'])) ? ", password='".password_hash($_POST['password'], PASSWORD_BCRYPT)."'" : "";
        
        $foto = $_FILES['foto']['name'];
        $tmp  = $_FILES['foto']['tmp_name'];
        $foto_query = "";
        if ($foto != "") {
            $nama_foto = time() . "_" . $foto;
            if (move_uploaded_file($tmp, "upload/user/" . $nama_foto)) {
                if ($data_edit['foto'] != "default.png" && file_exists("upload/user/" . $data_edit['foto'])) {
                    unlink("upload/user/" . $data_edit['foto']);
                }
                $foto_query = ", foto='$nama_foto'";
            }
        }

        if (mysqli_query($koneksi, "UPDATE user SET nama='$nama', username='$username', level='$level' $pass_query $foto_query WHERE id_user='$id'")) {
            $msg_type = "success";
            $msg_title = "Terupdate!";
            $msg_text = "Data profil $nama berhasil diperbarui.";
            $redirect = "?page=user";
        }
    }
}

// ================== PROSES HAPUS USER (DELETE) ==================
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    $data_hapus = mysqli_fetch_array(mysqli_query($koneksi, "SELECT foto FROM user WHERE id_user='$id_hapus'"));
    if ($data_hapus['foto'] != "default.png" && file_exists("upload/user/" . $data_hapus['foto'])) {
        unlink("upload/user/" . $data_hapus['foto']);
    }
    mysqli_query($koneksi, "DELETE FROM user WHERE id_user='$id_hapus'");
    header("Location: ?page=user&status=deleted");
    exit();
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-fluid px-4">
    <h3 class="mt-4 mb-4">👥 Manajemen User</h3>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-header bg-success text-white fw-bold">
            <?= $edit ? '✏️ Edit Pengguna' : '➕ Tambah Pengguna Baru' ?>
        </div>
        <div class="card-body bg-light">
            <form method="post" enctype="multipart/form-data">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="small fw-bold">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" value="<?= $edit ? $data_edit['nama'] : '' ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="small fw-bold">Username</label>
                        <input type="text" name="username" class="form-control" value="<?= $edit ? $data_edit['username'] : '' ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="small fw-bold">Level Akses</label>
                        <select name="level" class="form-control" required>
                            <option value="">-- Pilih Level --</option>
                            <option value="admin" <?= ($edit && $data_edit['level'] == 'admin') ? 'selected' : '' ?>>Admin</option>
                            <option value="petugas" <?= ($edit && $data_edit['level'] == 'petugas') ? 'selected' : '' ?>>Petugas</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="small fw-bold">Password <?= $edit ? '<small class="text-danger">(Kosongkan jika tidak diubah)</small>' : '' ?></label>
                        <input type="password" name="password" class="form-control" <?= $edit ? '' : 'required' ?>>
                    </div>
                    <div class="col-md-6">
                        <label class="small fw-bold">Foto Profil</label>
                        <input type="file" name="foto" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="mt-4">
                    <button class="btn btn-success px-4 shadow-sm fw-bold" name="<?= $edit ? 'update' : 'tambah' ?>">
                        <?= $edit ? '💾 Update User' : '➕ Simpan User' ?>
                    </button>
                    <a href="?page=user" class="btn btn-secondary px-4 shadow-sm">⬅️ Kembali</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-dark text-white fw-bold">📋 Daftar Pengguna Sistem</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="table-secondary text-uppercase small">
                        <tr>
                            <th width="50">No</th>
                            <th>Foto</th>
                            <th class="text-start">Nama</th>
                            <th>Username</th>
                            <th>Level</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $no = 1;
                    $data = mysqli_query($koneksi, "SELECT * FROM user ORDER BY id_user DESC");
                    while ($d = mysqli_fetch_array($data)) {
                    ?>
                    <tr>
                        <td class="fw-bold"><?= $no++; ?></td>
                        <td><img src="upload/user/<?= $d['foto'] ?>" width="40" height="40" class="rounded-circle border shadow-sm" style="object-fit: cover;"></td>
                        <td class="text-start"><?= htmlspecialchars($d['nama']); ?></td>
                        <td><?= htmlspecialchars($d['username']); ?></td>
                        <td><span class="badge <?= $d['level'] == 'admin' ? 'bg-primary' : 'bg-info text-dark' ?>"><?= strtoupper($d['level']); ?></span></td>
                        <td>
                            <a href="?page=user&edit=<?= $d['id_user']; ?>" class="btn btn-warning btn-sm">✏️</a>
                            <button onclick="hapusUser('<?= $d['id_user'] ?>')" class="btn btn-danger btn-sm">🗑</button>
                        </td>
                    </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Munculkan notifikasi jika ada pesan dari PHP
<?php if (isset($msg_type)): ?>
Swal.fire({
    icon: '<?= $msg_type ?>',
    title: '<?= $msg_title ?>',
    text: '<?= $msg_text ?>',
    confirmButtonColor: '#198754'
}).then((result) => {
    <?php if (isset($redirect)): ?>
    window.location.href = '<?= $redirect ?>';
    <?php endif; ?>
});
<?php endif; ?>

// Notifikasi khusus hapus
<?php if (isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
Swal.fire('Terhapus!', 'Data user telah dibuang.', 'success');
<?php endif; ?>

// Fungsi Konfirmasi Hapus Unik
function hapusUser(id) {
    Swal.fire({
        title: 'Yakin mau hapus?',
        text: "Data yang sudah dibuang tidak bisa balik lagi!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6e7881',
        confirmButtonText: 'Ya, Hapus Saja!',
        cancelButtonText: 'Jangan'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '?page=user&hapus=' + id;
        }
    })
}
</script>