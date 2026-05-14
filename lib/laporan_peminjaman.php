<?php
include "koneksi.php";

// ================= LOGIKA PROSES (BACKEND) =================
$msg = '';

// 1. SETUJUI SEMUA BUKU PER USER (Tombol di Header)
if(isset($_POST['setuju_semua_user'])){
    $id_u = intval($_POST['setuju_semua_user']);
    $query = mysqli_query($koneksi, "UPDATE peminjaman SET status_peminjaman='Dipinjam' 
                                    WHERE id_user='$id_u' AND status_peminjaman='menunggu persetujuan'");
    if($query) $msg = "Semua pengajuan user berhasil disetujui.";
}

// 2. KEMBALIKAN BUKU (SATUAN)
if(isset($_POST['kembalikan_id'])){
    $id = intval($_POST['kembalikan_id']);
    $q_info = mysqli_query($koneksi, "SELECT id_buku FROM peminjaman WHERE id_peminjaman='$id'");
    $d_info = mysqli_fetch_assoc($q_info);
    $id_buku = $d_info['id_buku'];
    
    mysqli_query($koneksi, "UPDATE peminjaman SET status_peminjaman='Dikembalikan', tanggal_pengembalian=NOW() WHERE id_peminjaman='$id'");
    mysqli_query($koneksi, "UPDATE buku SET stok = stok + 1 WHERE id_buku = '$id_buku'");
    $msg = "Buku telah dikembalikan.";
}

// 3. TOLAK PEMINJAMAN (SATUAN)
if(isset($_POST['tolak_id'])){
    $id = intval($_POST['tolak_id']);
    mysqli_query($koneksi, "UPDATE peminjaman SET status_peminjaman='Ditolak' WHERE id_peminjaman='$id'");
    $msg = "Peminjaman ditolak.";
}

// 4. HAPUS MASSAL (CHECKBOX)
if(isset($_POST['hapus_massal']) && !empty($_POST['pilih_id'])){
    $daftar_id = $_POST['pilih_id']; 
    $ids = implode(",", array_map('intval', $daftar_id));
    $query = mysqli_query($koneksi, "DELETE FROM peminjaman WHERE id_peminjaman IN ($ids)");
    if($query) $msg = count($daftar_id) . " data laporan berhasil dihapus.";
}

// ================= LOGIKA FILTER & DATA =================
$tahun_filter = $_GET['tahun'] ?? '';
$bulan_filter = $_GET['bulan'] ?? '';
$sort_filter = $_GET['sort'] ?? 'ASC';

$years = mysqli_query($koneksi, "SELECT DISTINCT YEAR(tanggal_peminjaman) AS tahun FROM peminjaman ORDER BY tahun DESC");
$bulan_list = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];

$where = [];
if($tahun_filter) $where[] = "YEAR(p.tanggal_peminjaman)='$tahun_filter'";
if($bulan_filter) $where[] = "MONTH(p.tanggal_peminjaman)='$bulan_filter'";
$where_sql = $where ? "WHERE ".implode(" AND ", $where) : "";

$data = mysqli_query($koneksi, "
    SELECT p.*, p.id_user, u.nama AS peminjam, b.judul AS buku
    FROM peminjaman p
    LEFT JOIN user u ON p.id_user = u.id_user
    LEFT JOIN buku b ON p.id_buku = b.id_buku
    $where_sql
    ORDER BY u.nama $sort_filter, p.tanggal_peminjaman DESC
");
?>

<div class="laporan-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h3 class="fw-bold mb-0">LAPORAN PEMINJAMAN</h3>
        <?php if($msg): ?>
            <div class="alert alert-success py-1 px-3 mb-0 small"><?= $msg ?></div>
        <?php endif; ?>
    </div>

    <div class="no-print filter-box mb-4">
        <div class="row g-3 align-items-center">
            <div class="col-md-8">
                <form method="get" class="row g-2 align-items-center">
                    <input type="hidden" name="page" value="laporan_peminjaman">
                    <div class="col-auto">
                        <select name="tahun" class="form-select form-select-sm">
                            <option value="">Tahun</option>
                            <?php while($y = mysqli_fetch_array($years)): ?>
                                <option value="<?= $y['tahun']; ?>" <?= $y['tahun']==$tahun_filter?'selected':''; ?>><?= $y['tahun']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <select name="bulan" class="form-select form-select-sm">
                            <option value="">Bulan</option>
                            <?php foreach($bulan_list as $num=>$nama): ?>
                                <option value="<?= $num; ?>" <?= $num==$bulan_filter?'selected':''; ?>><?= $nama; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary btn-sm px-3">Filter</button>
                        <button type="button" onclick="window.print()" class="btn btn-dark btn-sm px-3">Cetak</button>
                    </div>
                </form>
            </div>
            <div class="col-md-4 text-end">
                <button type="submit" form="formCheckbox" name="hapus_massal" class="btn btn-danger btn-sm px-3 shadow-sm" onclick="return confirm('Hapus semua data yang dipilih?')">
                    <i class="fas fa-trash-alt me-1"></i> Hapus Terpilih
                </button>
            </div>
        </div>
    </div>

    <form id="formCheckbox" method="post">
        <table class="tabel-custom">
            <thead>
                <tr>
                    <th class="no-print" style="width: 40px;">
                        <input type="checkbox" id="checkAll" style="cursor:pointer">
                    </th>
                    <th style="width: 50px;">NO</th>
                    <th>DETAIL BUKU & TANGGAL</th>
                    <th style="width: 180px;">STATUS</th>
                    <th class="no-print" style="width: 140px;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1; 
                $current_user = null; 
                if (mysqli_num_rows($data) > 0) {
                    while($d = mysqli_fetch_array($data)): 
                        if ($current_user !== $d['peminjam']): 
                            $current_user = $d['peminjam'];
                            $id_u = $d['id_user'];
                            
                            $cek_status = mysqli_query($koneksi, "SELECT id_peminjaman FROM peminjaman WHERE id_user='$id_u' AND status_peminjaman='menunggu persetujuan'");
                            $ada_yg_menunggu = mysqli_num_rows($cek_status) > 0;
                ?>
                    <tr class="row-user">
                        <td class="no-print bg-light"></td>
                        <td colspan="3" class="nama-user">
                            <i class="fas fa-user me-2"></i> PEMINJAM: <?= strtoupper(htmlspecialchars($current_user)); ?>
                        </td>
                        <td class="no-print bg-light text-center">
                            <?php if($ada_yg_menunggu): ?>
                                <button type="submit" name="setuju_semua_user" value="<?= $id_u; ?>" class="btn-setuju-semua" onclick="return confirm('Setujui semua buku untuk user ini?')">SETUJUI SEMUA</button>
                            <?php else: ?>
                                <small class="text-muted">-</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>

                <tr>
                    <td class="no-print text-center">
                        <input type="checkbox" name="pilih_id[]" value="<?= $d['id_peminjaman']; ?>" class="checkbox-item" style="cursor:pointer">
                    </td>
                    <td class="text-center"><?= $no++; ?></td>
                    <td>
                        <div class="fw-bold text-dark"><?= htmlspecialchars($d['buku']); ?></div>
                        <div class="small text-muted">
                            Pinjam: <?= date('d/m/Y', strtotime($d['tanggal_peminjaman'])); ?> | 
                            Kembali: <?= (!empty($d['tanggal_pengembalian']) && $d['tanggal_pengembalian'] != '0000-00-00 00:00:00') ? date('d/m/Y', strtotime($d['tanggal_pengembalian'])) : '-'; ?>
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="status-badge <?= strtolower(str_replace(' ', '-', $d['status_peminjaman'])); ?>">
                            <?= strtoupper($d['status_peminjaman']); ?>
                        </span>
                    </td>
                    <td class="no-print text-center">
                        <?php if(strtolower($d['status_peminjaman']) == 'dipinjam'): ?>
                            <button type="submit" name="kembalikan_id" value="<?= $d['id_peminjaman']; ?>" class="btn-kembali">Kembalikan</button>
                        <?php elseif(strtolower($d['status_peminjaman']) == 'menunggu persetujuan'): ?>
                            <button type="submit" name="tolak_id" value="<?= $d['id_peminjaman']; ?>" class="btn-aksi tolak">✘ Tolak</button>
                        <?php else: ?>
                            <span class="text-muted small">Selesai</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; 
                } else {
                    echo "<tr><td colspan='5' class='text-center py-4'>Tidak ada data peminjaman ditemukan.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </form>
</div>

<script>
    // Logika Check All
    document.getElementById('checkAll').onclick = function() {
        var checkboxes = document.getElementsByClassName('checkbox-item');
        for (var checkbox of checkboxes) {
            checkbox.checked = this.checked;
        }
    }
</script>

<style>
/* CSS Dasar */
.laporan-wrapper { padding: 10px; font-family: 'Segoe UI', sans-serif; }
.filter-box { background: #fff; padding: 15px; border-radius: 10px; border: 1px solid #eee; }
.tabel-custom { width: 100%; border-collapse: collapse; background: #fff; }
.tabel-custom th { background: #212529; color: #fff; padding: 12px; text-align: center; font-size: 13px; border: 1px solid #333; }
.tabel-custom td { padding: 10px 15px; border: 1px solid #eee; vertical-align: middle; font-size: 14px; }
.nama-user { background: #f8f9fa !important; font-weight: 800; color: #333; border-left: 4px solid #212529 !important; }

/* Status Badges */
.status-badge { padding: 4px 10px; font-size: 10px; font-weight: 700; border-radius: 4px; display: inline-block; min-width: 130px; text-align: center; }
.status-badge.menunggu-persetujuan { background: #fff4e6; color: #d9480f; border: 1px solid #ffd8a8; }
.status-badge.dipinjam { background: #e7f5ff; color: #1971c2; border: 1px solid #a5d8ff; }
.status-badge.dikembalikan { background: #ebfbee; color: #2f9e44; border: 1px solid #b2f2bb; }
.status-badge.ditolak { background: #fff5f5; color: #e03131; border: 1px solid #ffc9c9; }

/* Buttons */
.btn-setuju-semua { background: #000; color: #fff; border: none; padding: 5px; font-size: 10px; font-weight: bold; border-radius: 4px; cursor: pointer; width: 100%; }
.btn-aksi { border: none; padding: 4px 10px; border-radius: 4px; font-size: 11px; cursor: pointer; color: #fff; width: 100%; }
.btn-aksi.tolak { background: #e03131; }
.btn-kembali { background: #fff; color: #2f9e44; border: 1px solid #2f9e44; padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; width: 100%; }

@media print {
    /* 1. Sembunyikan elemen navigasi global (sesuaikan class/id dengan template Anda) */
    .no-print, 
    #sidebar, 
    .sidebar, 
    .navbar, 
    .header-top, 
    .footer,
    nav { 
        display: none !important; 
    }

    /* 2. Pastikan konten laporan memenuhi layar */
    body, .laporan-wrapper, .main-content {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        position: absolute;
        left: 0;
        top: 0;
    }

    /* 3. Atur agar tabel terlihat rapi saat diprint */
    .tabel-custom {
        width: 100% !important;
        border: 1px solid #000 !important;
    }
    
    .tabel-custom th {
        background-color: #eee !important;
        color: #000 !important;
        border: 1px solid #000 !important;
    }

    .tabel-custom td {
        border: 1px solid #000 !important;
    }

    /* 4. Munculkan judul laporan dengan jelas saat print */
    h3 {
        text-align: center;
        margin-bottom: 20px;
    }
}
</style>