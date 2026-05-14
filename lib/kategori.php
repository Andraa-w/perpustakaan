<?php
// ==========================================
// LOGIKA PHP (PROSES TAMBAH, EDIT, HAPUS)
// ==========================================

// 1. PROSES TAMBAH KATEGORI
if (isset($_POST['tambah'])) {
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $query = mysqli_query($koneksi, "INSERT INTO kategori(kategori) VALUES('$kategori')");
    if ($query) {
        echo '<script>alert("Tambah data berhasil."); location.href="?page=kategori";</script>';
    }
}

// 2. PROSES EDIT KATEGORI
if (isset($_POST['edit'])) {
    $id_kategori = intval($_POST['id_kategori']);
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $query = mysqli_query($koneksi, "UPDATE kategori SET kategori='$kategori' WHERE id_kategori=$id_kategori");
    if ($query) {
        echo '<script>alert("Ubah data berhasil."); location.href="?page=kategori";</script>';
    }
}

// 3. PROSES HAPUS KATEGORI
if (isset($_GET['hapus_id'])) {
    $id = intval($_GET['hapus_id']);
    $query = mysqli_query($koneksi, "DELETE FROM kategori WHERE id_kategori=$id");
    if ($query) {
        echo '<script>alert("Hapus data berhasil."); location.href="?page=kategori";</script>';
    }
}
?>

<div class="container-fluid">
    <h1 class="mt-4 mb-4 fw-bold">Kategori Buku</h1>
    
    <div class="row">
        <div class="col-md-12 mb-3 text-end">
            <!-- Trigger Modal Tambah (BS5 menggunakan data-bs-toggle) -->
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="fas fa-plus me-1"></i> Tambah Kategori
            </button>
        </div>
       
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <table class="table table-hover align-middle" id="dataTable" width="100%">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" width="10%">No</th>
                                <th>Nama Kategori</th>
                                <th class="text-center" width="25%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            $query = mysqli_query($koneksi, "SELECT * FROM kategori");
                            while($data = mysqli_fetch_array($query)) {
                            ?>
                            <tr>
                                <td class="text-center"><?php echo $i++; ?></td>
                                <td class="fw-semibold"><?php echo htmlspecialchars($data['kategori']); ?></td>
                                <td class="text-center">
                                    <!-- Trigger Modal Edit (BS5 menggunakan data-bs-toggle) -->
                                    <button type="button" class="btn btn-info btn-sm text-white" data-bs-toggle="modal" data-bs-target="#modalEdit<?php echo $data['id_kategori']; ?>">
                                        <i class="fas fa-edit"></i> Ubah
                                    </button>
                                    
                                    <a onclick="return confirm('Yakin ingin menghapus kategori ini?')" 
                                       href="?page=kategori&hapus_id=<?php echo $data['id_kategori']; ?>" 
                                       class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                </td>
                            </tr>

                            <!-- MODAL EDIT KATEGORI -->
                            <div class="modal fade" id="modalEdit<?php echo $data['id_kategori']; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Ubah Kategori</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form method="post">
                                            <div class="modal-body">
                                                <input type="hidden" name="id_kategori" value="<?php echo $data['id_kategori']; ?>">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Nama Kategori</label>
                                                    <input type="text" name="kategori" class="form-control" value="<?php echo htmlspecialchars($data['kategori']); ?>" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" name="edit" class="btn btn-primary">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH KATEGORI -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Kategori Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Kategori</label>
                        <input type="text" name="kategori" class="form-control" placeholder="Contoh: Fiksi, Sains, Sejarah" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>