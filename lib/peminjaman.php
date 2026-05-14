<?php
include "koneksi.php";

// Set zona waktu ke Jakarta agar sinkron dengan waktu Indonesia
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();    
}

$id_user_login = $_SESSION['user']['id_user'];
$level_user    = $_SESSION['user']['level']; 

// Filter: Jika bukan admin/petugas, hanya lihat data sendiri
$filter_privasi = ($level_user != 'admin' && $level_user != 'petugas') ? "WHERE p.id_user = '$id_user_login'" : "";

$query = mysqli_query($koneksi, "
    SELECT 
        p.*, 
        COALESCE(b.judul, 'Buku Terhapus') AS buku, 
        COALESCE(u.nama, 'User Anonim') AS peminjam,
        u.foto AS foto_user
    FROM peminjaman p
    LEFT JOIN buku b ON p.id_buku = b.id_buku
    LEFT JOIN user u ON p.id_user = u.id_user
    $filter_privasi
    ORDER BY 
        CASE WHEN p.status_peminjaman = 'menunggu persetujuan' THEN 0 ELSE 1 END, 
        p.id_peminjaman DESC
");

if (!$query) {
    die("Gagal mengambil data: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Peminjaman</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --success: #22c55e;
            --danger: #ef4444;
            --warning: #f59e0b;
            --dark: #1e293b;
            --gray: #94a3b8;
            --light: #f8fafc;
        }

        .main-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-top: 20px;
        }

        .table thead th {
            background: var(--light);
            color: var(--gray);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 1.2rem 1rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .table tbody tr:hover { background-color: #f8fafc; }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            color: var(--dark);
            border-bottom: 1px solid #f1f5f9;
        }

        /* Avatar Styling */
        .avatar-wrapper { display: flex; align-items: center; gap: 12px; }
        .avatar-container {
            width: 45px; height: 45px;
            border-radius: 12px;
            overflow: hidden;
            background: #f1f5f9;
            flex-shrink: 0;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .avatar-img { width: 100%; height: 100%; object-fit: cover; }

        /* Time Display */
        .time-stack { display: flex; flex-direction: column; gap: 6px; }
        .time-row { 
            display: flex; 
            align-items: center; 
            gap: 10px; 
            font-size: 0.8rem; 
            padding: 6px 12px; 
            background: var(--light); 
            border-radius: 8px;
            border-left: 4px solid #cbd5e1;
        }
        .time-label { font-weight: 800; font-size: 0.6rem; text-transform: uppercase; width: 55px; }
        .label-pinjam { color: var(--success); }
        .label-kembali { color: var(--danger); }

        /* STATUS PILL */
        .status-pill {
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid transparent;
        }
        .st-menunggu { background: #fffbeb; color: #b45309; border-color: #fef3c7; }
        .st-dipinjam { background: #f0fdf4; color: #15803d; border-color: #dcfce7; }
        .st-dikembalikan { background: #eff6ff; color: #1e40af; border-color: #dbeafe; }
        .st-ditolak  { background: #fef2f2; color: #b91c1c; border-color: #fee2e2; }

        .countdown-timer {
            background: #f1f5f9;
            padding: 4px 10px;
            border-radius: 6px;
            font-family: 'Monaco', 'Consolas', monospace;
            font-size: 0.75rem;
            border: 1px solid #e2e8f0;
        }

        .alert-late {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fee2e2;
            padding: 8px;
            border-radius: 8px;
            font-size: 0.7rem;
            margin-top: 8px;
        }

        .btn-circle {
            width: 35px; height: 35px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            transition: 0.3s; text-decoration: none !important;
            border: none;
        }
        .btn-acc { background: var(--success); color: white !important; }
        .btn-deny { background: var(--danger); color: white !important; }
    </style>
</head>
<body>

<div class="container-fluid py-4">
    <div class="mb-4">
        <h3 class="fw-bold text-dark mb-0">
            <i class="fas fa-exchange-alt me-2 text-primary"></i>
            <?= ($level_user == 'admin' || $level_user == 'petugas') ? "Manajemen Peminjaman" : "Riwayat Pinjaman Saya"; ?>
        </h3>
        <p class="text-muted small">Status pengajuan akan dikonfirmasi oleh Admin.</p>
    </div>

    <div class="main-card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th class="text-center" width="60">No</th>
                        <th>Peminjam & Buku</th>
                        <th>Jadwal Durasi</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Denda</th>
                        <?php if($level_user == 'admin' || $level_user == 'petugas'): ?>
                            <th class="text-center">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while($d = mysqli_fetch_array($query)): 
                        $raw_status = $d['status_peminjaman'];
                        
                        // LOGIKA PENTING: Mengambil kata pertama (misal: "menunggu persetujuan" jadi "menunggu")
                        // agar cocok dengan class CSS .st-menunggu yang sudah kamu buat.
                        $status_parts = explode(' ', strtolower(trim($raw_status)));
                        $status_css = $status_parts[0]; 
                        
                        // Logika Denda
                        $tarif_denda = 25000;
                        $denda = 0;
                        $hari_terlambat = 0;

                        $tgl_pinjam = new DateTime($d['tanggal_peminjaman']);
                        $tgl_deadline = clone $tgl_pinjam;
                        $tgl_deadline->modify('+2 days');
                        $tgl_sekarang = new DateTime();

                        if ($raw_status == 'dipinjam' && $tgl_sekarang > $tgl_deadline) {
                            $selisih = $tgl_sekarang->diff($tgl_deadline);
                            $hari_terlambat = $selisih->days + ($selisih->h > 0 ? 1 : 0);
                            $denda = $hari_terlambat * $tarif_denda;
                        }
                    ?>
                    <tr>
                        <td class="text-center fw-bold text-muted"><?= $no++; ?></td>
                        <td>
                            <div class="avatar-wrapper">
                                <div class="avatar-container">
                                    <?php 
                                    $foto_path  = "upload/user/" . $d['foto_user'];
                                    $avatar_api = "https://ui-avatars.com/api/?name=" . urlencode($d['peminjam']) . "&background=6366f1&color=fff&bold=true";
                                    if (!empty($d['foto_user']) && file_exists($foto_path)): ?>
                                        <img src="<?= $foto_path; ?>" class="avatar-img" onerror="this.src='<?= $avatar_api; ?>'">
                                    <?php else: ?>
                                        <img src="<?= $avatar_api; ?>" class="avatar-img">
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div class="fw-bold mb-0" style="font-size: 0.9rem;"><?= htmlspecialchars($d['peminjam']); ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars($d['buku']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="time-stack">
                                <div class="time-row" style="border-left-color: var(--success);">
                                    <span class="time-label label-pinjam">Pinjam</span>
                                    <span class="time-val"><?= $tgl_pinjam->format('d M Y'); ?></span>
                                </div>
                                <div class="time-row" style="border-left-color: var(--danger);">
                                    <span class="time-label label-kembali">Deadline</span>
                                    <span class="time-val"><?= $tgl_deadline->format('d M Y'); ?></span>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="status-pill st-<?= $status_css; ?> mb-2">
                                <i class="fas <?php 
                                    if($raw_status == 'menunggu persetujuan') echo 'fa-hourglass-start';
                                    elseif($raw_status == 'dipinjam') echo 'fa-book-reader';
                                    else echo 'fa-check-double'; 
                                ?>"></i>
                                <?= ucfirst($raw_status); ?>
                            </span>

                            <?php if($raw_status == 'dipinjam'): ?>
                                <div class="countdown-timer fw-bold text-primary mt-1" data-deadline="<?= $tgl_deadline->format('Y-m-d H:i:s'); ?>">
                                     <i class="fas fa-clock me-1"></i> --:--:--
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if($denda > 0): ?>
                                <div class="alert-late">
                                    <div class="fw-bold text-danger">Rp <?= number_format($denda, 0, ',', '.'); ?></div>
                                    <small class="text-muted">Telat <?= $hari_terlambat; ?> Hari</small>
                                </div>
                            <?php else: ?>
                                <span class="text-muted small">-</span>
                            <?php endif; ?>
                        </td>
                        
                        <?php if($level_user == 'admin' || $level_user == 'petugas'): ?>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <?php if ($raw_status == 'menunggu persetujuan'): ?>
                                    <a href="proses_setuju.php?id=<?= $d['id_peminjaman']; ?>" class="btn-circle btn-acc" title="Setujui"><i class="fas fa-check"></i></a>
                                    <a href="proses_tolak.php?id=<?= $d['id_peminjaman']; ?>" class="btn-circle btn-deny" title="Tolak"><i class="fas fa-times"></i></a>
                                <?php elseif ($raw_status == 'dipinjam'): ?>
                                    <a href="proses_kembali.php?id=<?= $d['id_peminjaman']; ?>" class="btn btn-sm btn-outline-primary fw-bold px-3">KEMBALI</a>
                                <?php else: ?>
                                    <i class="fas fa-check-circle text-success"></i>
                                <?php endif; ?>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function startRealtimeCountdown() {
    const timers = document.querySelectorAll('.countdown-timer');
    setInterval(() => {
        const now = new Date().getTime();

        timers.forEach(timer => {
            const deadline = new Date(timer.getAttribute('data-deadline')).getTime();
            const distance = deadline - now;

            if (distance < 0) {
                timer.innerHTML = "<span class='text-danger fw-bold'>WAKTU HABIS</span>";
                return;
            }

            const h = Math.floor(distance / (1000 * 60 * 60)).toString().padStart(2, '0');
            const m = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)).toString().padStart(2, '0');
            const s = Math.floor((distance % (1000 * 60)) / 1000).toString().padStart(2, '0');

            timer.innerHTML = `<i class="fas fa-hourglass-half me-1"></i> ${h}:${m}:${s}`;
        });
    }, 1000);
}
document.addEventListener('DOMContentLoaded', startRealtimeCountdown);
</script>
</body>
</html>