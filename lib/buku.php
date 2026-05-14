<?php
include "koneksi.php";

$edit = false;
$res_icon = ""; 
$res_msg = "";

// ================== EDIT MODE (TAMPIL DATA DI FORM) ==================
if (isset($_GET['edit'])) {
    $edit = true;
    $id = mysqli_real_escape_string($koneksi, $_GET['edit']);
    $query_edit = mysqli_query($koneksi, "SELECT * FROM buku WHERE id_buku='$id'");
    $data_edit = mysqli_fetch_array($query_edit);

    // Ambil kategori yang sudah terpilih untuk buku ini
    $terpilih = [];
    $q_kat = mysqli_query($koneksi, "SELECT id_kategori FROM kategori_buku_relasi WHERE id_buku='$id'");
    while($rk = mysqli_fetch_assoc($q_kat)) $terpilih[] = $rk['id_kategori'];
}

// ================== PROSES TAMBAH & UPDATE ==================
if (isset($_POST['tambah']) || isset($_POST['update'])) {
    $judul     = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $penulis   = mysqli_real_escape_string($koneksi, $_POST['penulis']);
    $penerbit  = mysqli_real_escape_string($koneksi, $_POST['penerbit']);
    $tahun     = mysqli_real_escape_string($koneksi, $_POST['tahun']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $stok      = mysqli_real_escape_string($koneksi, $_POST['jumlah']);
    $kategori_ids = $_POST['kategori'] ?? []; // Array dari checkbox

    $cover = $_FILES['cover']['name'];
    $tmp   = $_FILES['cover']['tmp_name'];
    $cover_db = "";

    if ($cover != '') {
        $ekstensi_boleh = array('png', 'jpg', 'jpeg');
        $x = explode('.', $cover);
        $ekstensi = strtolower(end($x));
        
        if (in_array($ekstensi, $ekstensi_boleh)) {
            $nama_file_baru = time() . "_" . $cover;
            if (move_uploaded_file($tmp, "upload/cover/" . $nama_file_baru)) {
                $cover_db = $nama_file_baru;
                if (isset($_POST['update'])) {
                    $id_upd = mysqli_real_escape_string($koneksi, $_GET['edit']);
                    $cek_lama = mysqli_query($koneksi, "SELECT cover FROM buku WHERE id_buku='$id_upd'");
                    $data_lama = mysqli_fetch_array($cek_lama);
                    if (!empty($data_lama['cover']) && $data_lama['cover'] != 'default.png') {
                        @unlink("upload/cover/" . $data_lama['cover']);
                    }
                }
            }
        }
    }

    if (isset($_POST['tambah'])) {
        $final_cover = ($cover_db != '') ? $cover_db : 'default.png';
        mysqli_query($koneksi, "INSERT INTO buku (judul, penulis, penerbit, tahun_terbit, deskripsi, cover, stok) 
                  VALUES ('$judul', '$penulis', '$penerbit', '$tahun', '$deskripsi', '$final_cover', '$stok')");
        $id_buku = mysqli_insert_id($koneksi);
        $res_msg = "Buku berhasil ditambah!";
        $res_icon = "success";
    } else {
        $id_buku = mysqli_real_escape_string($koneksi, $_GET['edit']);
        $cover_query = ($cover_db != '') ? ", cover='$cover_db'" : "";
        mysqli_query($koneksi, "UPDATE buku SET judul='$judul', penulis='$penulis', 
                  penerbit='$penerbit', tahun_terbit='$tahun', deskripsi='$deskripsi', stok='$stok' $cover_query 
                  WHERE id_buku='$id_buku'");
        
        // Hapus relasi kategori lama jika update
        mysqli_query($koneksi, "DELETE FROM kategori_buku_relasi WHERE id_buku='$id_buku'");
        $res_msg = "Perubahan berhasil disimpan!";
        $res_icon = "info";
    }

    // Simpan relasi kategori baru
    foreach ($kategori_ids as $kid) {
        $kid = intval($kid);
        mysqli_query($koneksi, "INSERT INTO kategori_buku_relasi (id_buku, id_kategori) VALUES ('$id_buku', '$kid')");
    }
}

// ================== HAPUS ==================
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    $dt = mysqli_fetch_array(mysqli_query($koneksi, "SELECT cover FROM buku WHERE id_buku='$id_hapus'"));
    
    if(!empty($dt['cover']) && $dt['cover'] != 'default.png'){
        @unlink("upload/cover/".$dt['cover']);
    }
    
    mysqli_query($koneksi, "DELETE FROM buku WHERE id_buku='$id_hapus'");
    echo "<script>window.location.href='?page=buku&msg=deleted';</script>";
    exit();
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-fluid px-4">
    <h3 class="mt-4 mb-4">📚 Data Buku</h3>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-header bg-primary text-white fw-bold">
            <?= $edit ? '✏️ Edit Buku' : '➕ Tambah Buku Baru' ?>
        </div>
        <div class="card-body bg-light">
            <form method="post" enctype="multipart/form-data">
                <div class="row mb-3">
                    <div class="col-md-12 mb-3">
                        <label class="small fw-bold">Pilih Kategori (Bisa lebih dari satu)</label>
                        <div class="d-flex flex-wrap gap-3 p-2 border rounded bg-white">
                            <?php
                            $k = mysqli_query($koneksi, "SELECT * FROM kategori");
                            while ($kat = mysqli_fetch_array($k)) {
                                $checked = ($edit && in_array($kat['id_kategori'], $terpilih)) ? 'checked' : '';
                                echo "
                                <div class='form-check'>
                                    <input class='form-check-input' type='checkbox' name='kategori[]' value='$kat[id_kategori]' id='kat$kat[id_kategori]' $checked>
                                    <label class='form-check-label small' for='kat$kat[id_kategori]'>$kat[kategori]</label>
                                </div>";
                            }
                            ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="small fw-bold">Judul Buku</label>
                        <input type="text" name="judul" placeholder="Judul Buku" class="form-control" value="<?= $edit ? htmlspecialchars($data_edit['judul']) : '' ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="small fw-bold">Penulis</label>
                        <input type="text" name="penulis" placeholder="Nama Penulis" class="form-control" value="<?= $edit ? htmlspecialchars($data_edit['penulis']) : '' ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="small fw-bold">Penerbit</label>
                        <input type="text" name="penerbit" placeholder="Nama Penerbit" class="form-control" value="<?= $edit ? htmlspecialchars($data_edit['penerbit']) : '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold">Tahun Rilis</label>
                        <input type="number" name="tahun" placeholder="2000" class="form-control" value="<?= $edit ? $data_edit['tahun_terbit'] : '' ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="small fw-bold">Stok</label>
                        <input type="number" name="jumlah" placeholder="0" class="form-control" value="<?= $edit ? $data_edit['stok'] : '' ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="small fw-bold">Upload Cover</label>
                        <input type="file" name="cover" class="form-control">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="small fw-bold">Deskripsi Buku</label>
                        <textarea name="deskripsi" placeholder="Tuliskan sinopsis atau deskripsi buku yang ditambahkan.." class="form-control" rows="3"><?= $edit ? htmlspecialchars($data_edit['deskripsi']) : '' ?></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary px-4 fw-bold" name="<?= $edit ? 'update' : 'tambah' ?>">
                    <?= $edit ? '💾 Simpan' : '➕ Tambah' ?>
                </button>
                <a href="?page=buku" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-dark text-white fw-bold">📋 Koleksi Buku</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="table-secondary small">
                        <tr>
                            <th>No</th>
                            <th>Cover</th>
                            <th class="text-start">Detail</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $no = 1;
                    // Query GROUP_CONCAT untuk menggabungkan banyak kategori jadi satu string
                    $q_buku = "SELECT buku.*, GROUP_CONCAT(kategori.kategori SEPARATOR ', ') AS daftar_kategori 
                               FROM buku 
                               LEFT JOIN kategori_buku_relasi ON buku.id_buku = kategori_buku_relasi.id_buku 
                               LEFT JOIN kategori ON kategori_buku_relasi.id_kategori = kategori.id_kategori 
                               GROUP BY buku.id_buku ORDER BY id_buku DESC";
                    $data = mysqli_query($koneksi, $q_buku);
                    while ($d = mysqli_fetch_array($data)) {
                        $img_src = (!empty($d['cover']) && file_exists("upload/cover/".$d['cover'])) ? "upload/cover/".$d['cover'] : "upload/cover/default.png";
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><img src="<?= $img_src; ?>" width="50" height="65" class="rounded shadow-sm border"></td>
                        <td class="text-start">
                            <div class="fw-bold"><?= htmlspecialchars($d['judul']); ?></div>
                            <small class="text-muted"><?= htmlspecialchars($d['penulis']); ?></small>
                        </td>
                        <td>
                            <div style="max-width: 200px; margin: auto;">
                                <?php 
                                $kats = explode(', ', $d['daftar_kategori']);
                                foreach($kats as $kat_name){
                                    if(!empty($kat_name)) echo "<span class='badge bg-info text-light me-1'>$kat_name</span>";
                                }
                                ?>
                            </div>
                        </td>
                        <td><span class="badge rounded-pill <?= $d['stok'] > 0 ? 'bg-success' : 'bg-danger' ?>"><?= $d['stok']; ?></span></td>
                        <td>
                            <a href="?page=buku&edit=<?= $d['id_buku']; ?>" class="btn btn-warning btn-sm">✏️</a>
                            <button onclick="confirmHapus('<?= $d['id_buku'] ?>')" class="btn btn-danger btn-sm">🗑</button>
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
// Logic Alert Tetap Sama...
const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000, timerProgressBar: true });
<?php if ($res_icon != ""): ?>
    Toast.fire({ icon: '<?= $res_icon ?>', title: '<?= $res_msg ?>' }).then(() => { window.location.href = '?page=buku'; });
<?php endif; ?>
function confirmHapus(id) {
    Swal.fire({ title: 'Hapus Buku?', text: "Data akan hilang!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Hapus' }).then((result) => {
        if (result.isConfirmed) { window.location.href = '?page=buku&hapus=' + id; }
    })
}
</script>