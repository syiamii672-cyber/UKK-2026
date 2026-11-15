
<?php
require_once 'config/koneksi.php';
require_once 'includes/cek_session.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ==============================
   CEK HAK AKSES
============================== */
$role = strtolower(trim($_SESSION['role'] ?? ''));

if (!in_array($role, ['admin', 'guru'], true)) {
    header('Location: login.php');
    exit;
}

/* ==============================
   AMBIL PARAMETER
============================== */
$siswaId = (int) ($_GET['siswa_id'] ?? 0);
$kelasId = (int) ($_GET['kelas_id'] ?? 0);
$tahunId = (int) ($_GET['tahun_ajaran_id'] ?? 0);

if ($siswaId <= 0 || $kelasId <= 0 || $tahunId <= 0) {
    echo "<script>alert('Parameter siswa tidak lengkap.');window.location='rekap_poin.php';</script>";
    exit;
}

/* ==============================
   DATA SISWA
============================== */
$querySiswa = mysqli_query($koneksi, "
    SELECT
        s.id AS siswa_id,
        s.nis,
        s.nama,
        k.id AS kelas_id,
        k.nama AS nama_kelas,
        ta.id AS tahun_ajaran_id
    FROM t_siswa s
    INNER JOIN t_kelas_siswa ks ON ks.siswa_id = s.id
    INNER JOIN t_kelas k ON k.id = ks.kelas_id
    INNER JOIN t_tahun_ajaran ta ON ta.id = ks.tahun_ajaran_id
    WHERE s.id = $siswaId
      AND k.id = $kelasId
      AND ta.id = $tahunId
      AND ks.status_aktif = 1
    LIMIT 1
");

if (!$querySiswa) {
    die('Kesalahan data siswa: ' . htmlspecialchars(mysqli_error($koneksi)));
}

if (mysqli_num_rows($querySiswa) === 0) {
    echo "<script>alert('Data siswa tidak ditemukan.');window.location='rekap_poin.php';</script>";
    exit;
}

$siswa = mysqli_fetch_assoc($querySiswa);

/* ==============================
   REKAP POIN
============================== */
$queryRekap = mysqli_query($koneksi, "
    SELECT
        COUNT(*) AS total_pelanggaran,
        COALESCE(SUM(poin), 0) AS total_pengurangan
    FROM t_pelanggaran_siswa
    WHERE siswa_id = $siswaId
      AND kelas_id = $kelasId
      AND tahun_ajaran_id = $tahunId
");

if (!$queryRekap) {
    die('Kesalahan rekap poin: ' . htmlspecialchars(mysqli_error($koneksi)));
}

$rekap = mysqli_fetch_assoc($queryRekap);

$totalPelanggaran = (int) $rekap['total_pelanggaran'];
$totalPengurangan = (int) $rekap['total_pengurangan'];

$poinAwal = 100;
$sisaPoin = max(0, $poinAwal - $totalPengurangan);

/* ==============================
   STATUS SISWA
============================== */
if ($sisaPoin >= 80) {
    $status = 'Baik';
    $badgeStatus = 'success';
} elseif ($sisaPoin >= 60) {
    $status = 'Perlu Perhatian';
    $badgeStatus = 'info';
} elseif ($sisaPoin >= 40) {
    $status = 'Pembinaan';
    $badgeStatus = 'warning';
} elseif ($sisaPoin >= 20) {
    $status = 'Peringatan';
    $badgeStatus = 'danger';
} else {
    $status = 'Pembinaan Khusus';
    $badgeStatus = 'danger';
}

/* ==============================
   RIWAYAT PELANGGARAN
============================== */
$queryRiwayat = mysqli_query($koneksi, "
    SELECT
        id,
        tanggal,
        nama_pelanggaran,
        poin,
        keterangan,
        nama_guru,
        tindakan,
        status
    FROM t_pelanggaran_siswa
    WHERE siswa_id = $siswaId
      AND kelas_id = $kelasId
      AND tahun_ajaran_id = $tahunId
    ORDER BY tanggal DESC, id DESC
");

if (!$queryRiwayat) {
    die('Kesalahan riwayat: ' . htmlspecialchars(mysqli_error($koneksi)));
}

/* ==============================
   FORMAT TANGGAL
============================== */
function tanggalIndonesia($tanggal)
{
    if (empty($tanggal)) {
        return '-';
    }

    $bulan = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    ];

    $pecah = explode('-', $tanggal);

    if (count($pecah) !== 3) {
        return $tanggal;
    }

    $nomorBulan = (int) $pecah[1];

    if (!isset($bulan[$nomorBulan])) {
        return $tanggal;
    }

    return (int) $pecah[2] . ' ' .
        $bulan[$nomorBulan] . ' ' .
        $pecah[0];
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Detail Rekap Poin - SIPENA MU</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #F1F5FB;
            font-family: Arial, sans-serif;
        }

        /*
         * Sidebar diasumsikan fixed dengan lebar 220px.
         * Konten dan navbar ditempatkan di area kanan sidebar.
         */
        .content-wrapper {
            margin-left: 220px;
            width: calc(100% - 220px);
            min-height: 100vh;
            padding: 100px 28px 30px;
            overflow-x: hidden;
        }

        .content-wrapper .container-fluid {
            width: 100%;
            padding: 0;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(23, 59, 103, 0.07);
        }

        .section-title {
            color: #173B67;
            font-weight: 700;
        }

        .summary-number {
            color: #173B67;
            font-size: 30px;
            font-weight: 700;
        }

        .progress {
            height: 14px;
            border-radius: 20px;
            background: #E5EAF2;
        }

        .progress-bar {
            border-radius: 20px;
        }

        .table thead th {
            background: #173B67;
            color: white;
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
        }

        @media (max-width: 768px) {
            .content-wrapper {
                margin-left: 220px;
                width: calc(100% - 220px);
                padding: 95px 15px 20px;
            }
        }
    </style>
</head>

<body>

<?php
if (file_exists('includes/sidebar.php')) {
    include 'includes/sidebar.php';
}
?>

<div class="content-wrapper">

    <?php
    if (file_exists('includes/navbar.php')) {
        include 'includes/navbar.php';
    }
    ?>

    <div class="container-fluid">

        <!-- HEADER -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h4 class="fw-bold mb-1" style="color:#173B67;">
                    Detail Rekap Poin Siswa
                </h4>
                <p class="text-muted mb-0">
                    Informasi lengkap poin dan riwayat pelanggaran siswa
                </p>
            </div>

            <a href="rekap_poin.php" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left"></i>
                Kembali
            </a>
        </div>

        <!-- IDENTITAS SISWA -->
        <div class="card mb-4">
            <div class="card-body p-4">

                <h5 class="section-title mb-3">
                    Identitas Siswa
                </h5>

                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="text-muted small">Nama Siswa</div>
                        <div class="fw-semibold">
                            <?= htmlspecialchars($siswa['nama']); ?>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="text-muted small">NIS</div>
                        <div class="fw-semibold">
                            <?= htmlspecialchars($siswa['nis']); ?>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="text-muted small">Kelas</div>
                        <div class="fw-semibold">
                            <?= htmlspecialchars($siswa['nama_kelas']); ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- RINGKASAN -->
        <div class="row g-3 mb-4">

            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-2">Poin Awal</div>
                        <div class="summary-number">100</div>
                        <small class="text-muted">Poin awal tahun ajaran</small>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-2">Total Pengurangan</div>
                        <div class="summary-number text-danger">
                            <?= $totalPengurangan; ?>
                        </div>
                        <small class="text-muted">
                            <?= $totalPelanggaran; ?> kali pelanggaran
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-2">Sisa Poin</div>
                        <div class="summary-number">
                            <?= $sisaPoin; ?><small class="fs-6 text-muted">/100</small>
                        </div>
                        <span class="badge bg-<?= $badgeStatus; ?>">
                            <?= htmlspecialchars($status); ?>
                        </span>
                    </div>
                </div>
            </div>

        </div>

        <!-- PROGRESS -->
        <div class="card mb-4">
            <div class="card-body p-4">

                <div class="d-flex justify-content-between mb-2">
                    <h5 class="section-title mb-0">
                        Persentase Sisa Poin
                    </h5>
                    <strong><?= $sisaPoin; ?>%</strong>
                </div>

                <div class="progress">
                    <div
                        class="progress-bar bg-<?= $badgeStatus; ?>"
                        style="width:<?= $sisaPoin; ?>%;"
                        role="progressbar"
                        aria-valuenow="<?= $sisaPoin; ?>"
                        aria-valuemin="0"
                        aria-valuemax="100">
                    </div>
                </div>

                <small class="text-muted d-block mt-2">
                    Poin berkurang sesuai jumlah poin dari setiap pelanggaran.
                </small>

            </div>
        </div>

        <!-- SURAT PEMANGGILAN -->
        <?php if ($sisaPoin <= 18): ?>

            <div class="alert alert-danger mb-4">

                <h5 class="fw-bold">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    Siswa Memerlukan Pembinaan Khusus
                </h5>

                <p>
                    Sisa poin siswa telah mencapai
                    <strong><?= $sisaPoin; ?> poin</strong>.
                    Orang tua/wali perlu dipanggil untuk pembinaan.
                </p>

                <a
                    href="surat_pemanggilan.php?siswa_id=<?= $siswaId; ?>&kelas_id=<?= $kelasId; ?>&tahun_ajaran_id=<?= $tahunId; ?>"
                    target="_blank"
                    class="btn btn-danger">

                    <i class="bi bi-printer-fill"></i>
                    Buat Surat Pemanggilan Orang Tua
                </a>

            </div>

        <?php endif; ?>

        <!-- RIWAYAT -->
        <div class="card mb-4">
            <div class="card-body p-4">

                <h5 class="section-title mb-3">
                    Riwayat Pelanggaran
                </h5>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">

                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Tanggal</th>
                                <th>Jenis Pelanggaran</th>
                                <th>Poin</th>
                                <th>Keterangan</th>
                                <th>Guru Pelapor</th>
                                <th>Tindakan</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if (mysqli_num_rows($queryRiwayat) > 0): ?>

                                <?php $no = 1; ?>

                                <?php while ($row = mysqli_fetch_assoc($queryRiwayat)): ?>

                                    <tr>
                                        <td><?= $no++; ?></td>

                                        <td>
                                            <?= htmlspecialchars(tanggalIndonesia($row['tanggal'])); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['nama_pelanggaran']); ?>
                                        </td>

                                        <td>
                                            <span class="badge bg-danger">
                                                -<?= (int) $row['poin']; ?>
                                            </span>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['keterangan'] ?: '-'); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['nama_guru'] ?: '-'); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['tindakan'] ?: '-'); ?>
                                        </td>

                                        <td>
                                            <?php
                                            $statusRiwayat = $row['status'] ?: 'Belum Ditindak';

                                            if ($statusRiwayat === 'Selesai') {
                                                $badgeRiwayat = 'success';
                                            } elseif (
                                                $statusRiwayat === 'Diproses' ||
                                                $statusRiwayat === 'Sedang diproses'
                                            ) {
                                                $badgeRiwayat = 'warning';
                                            } else {
                                                $badgeRiwayat = 'secondary';
                                            }
                                            ?>

                                            <span class="badge bg-<?= $badgeRiwayat; ?>">
                                                <?= htmlspecialchars($statusRiwayat); ?>
                                            </span>
                                        </td>
                                    </tr>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        Belum ada riwayat pelanggaran.
                                    </td>
                                </tr>

                            <?php endif; ?>

                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        <!-- FOOTER -->
        <div class="text-center text-muted small pb-3">
            SIPENA MU - Sistem Informasi Pelanggaran dan Pembinaan Siswa Muhammadiyah
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
