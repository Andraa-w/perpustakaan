<?php
session_start();
include "koneksi.php";

$user = $_SESSION['user'];
$id   = $user['id_user'];

// ================= AMBIL DATA USER =================
$data = mysqli_fetch_array(mysqli_query($koneksi, "SELECT * FROM user WHERE id_user='$id'"));

// ================= UPDATE =================
if (isset($_POST['simpan'])) {

    $nama     = $_POST['nama'];
    $password = $_POST['password'];

    // FOTO
    $foto = $_FILES['foto']['name'];
    $tmp  = $_FILES['foto']['tmp_name'];

    if ($foto != '') {
        move_uploaded_file($tmp, "upload/user/" . $foto);
        $foto_query = ", foto='$foto'";
    } else {
        $foto_query = "";
    }

    // PASSWORD (opsional)
    if ($password != '') {
        $pass_query = ", password='" . md5($password) . "'";
    } else {
        $pass_query = "";
    }

    mysqli_query($koneksi, "UPDATE user SET
        name='$name'
        $pass_query
        $foto_query
        WHERE id_user='$id'
    ");

    // UPDATE SESSION
    $_SESSION['user']['nama'] = $name;
    if ($foto != '') {
        $_SESSION['user']['foto'] = $foto;
    }

    echo "<script>alert('Profil berhasil diupdate'); location='?';</script>";
}
?>

<div class="container-fluid px-4">

<h3 class="mt-4 mb-4">⚙️ Pengaturan Profil</h3>

<div class="card">
<div class="card-body">

<form method="post" enctype="multipart/form-data">

<div class="row">

    <!-- FOTO -->
    <div class="col-md-3 text-center">
        <img src="upload/user/<?= $data['foto'] ?: 'default.png'; ?>" 
             width="120"
             style="border-radius:50%; object-fit:cover;"
             onerror="this.src='upload/user/default.png'">

        <input type="file" name="foto" class="form-control mt-2">
    </div>

    <!-- FORM -->
    <div class="col-md-9">

        <div class="mb-3">
            <label>name</label>
            <input type="text" name="name" class="form-control"
                   value="<?= htmlspecialchars($data['nama']); ?>" required>
        </div>

        <div class="mb-3">
            <label>Password Baru</label>
            <input type="password" name="password" class="form-control"
                   placeholder="Kosongkan jika tidak ingin ganti">
        </div>

        <button class="btn btn-primary" name="simpan">
            💾 Simpan Perubahan
        </button>

    </div>

</div>

</form>

</div>
</div>

</div>