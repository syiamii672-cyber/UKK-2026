<?php
include "includes/cek_session.php";
include "config/koneksi.php";
require_once "includes/cek_wali_kelas.php";

$role = strtolower(trim($_SESSION['role'] ?? ''));

if (!in_array($role, ['admin', 'guru'], true)) {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "rekap_poin";

function e($nilai) {
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

function statusPoin($poin) {
    if ($poin >= 80) {
        return [
            'Baik',
            'success',
            'poin-baik',
            'Pertahankan perilaku baik dan kedisiplinan.'
        ];
    } elseif ($poin >= 60) {
        return [
            'Perlu Perhatian',
            'info',
            'poin-perhatian',
            'Siswa perlu mendapatkan perhatian dan pemantauan dari guru.'
        ];
    } elseif ($poin >= 40) {
        return [
            'Pembinaan',
            'warning',
            'poin-pembinaan',
            'Siswa perlu mendapatkan pembinaan dari guru.'
        ];
    } elseif ($poin >= 20) {
        return [
            'Peringatan',
            'warning',
            'poin-peringatan',
            'Siswa telah memasuki tahap peringatan. Diperlukan bimbingan dan perhatian lebih lanjut dari guru.'
        ];
    }

    return [
        'Pembinaan Khusus',
        'danger',
        'poin-khusus',
        'Siswa memerlukan pembinaan khusus dan pemanggilan orang tua.'
    ];
}

/* PARAMETER */
$siswaId = (int)($_GET['siswa_id'] ?? 0);
$kelasId = (int)($_GET['kelas_id'] ?? 0);
$tahunId = (int)($_GET['tahun_ajaran_id'] ?? 0);

if ($siswaId <= 0 || $kelasId <= 0 || $tahunId <= 0) {
    die("Parameter detail siswa tidak valid.");
}

/* PEMERIKSAAN AKSES WALI KELAS */
$isWaliKelas = (bool)($isWaliKelas ?? false);
$kelasWali = (int)($kelasWali ?? 0);
$tahunWali = (int)($tahunWali ?? 0);

if ($role === 'guru' && $isWaliKelas) {
    if ($kelasId !== $kelasWali || $tahunId !== $tahunWali) {
        http_response_code(403);
        die("Anda tidak memiliki akses untuk melihat detail siswa dari kelas lain.");
    }
}

/* AMBIL IDENTITAS SISWA */
$qSiswa = mysqli_query($koneksi, "
    SELECT
        ks.siswa_id,
        ks.kelas_id,
        ks.tahun_ajaran_id,
        s.nis,
        s.nama AS nama_siswa,
        k.nama AS nama_kelas,
        ta.nama AS tahun_ajaran
    FROM t_kelas_siswa ks
    JOIN t_siswa s ON s.id = ks.siswa_id
    JOIN t_kelas k ON k.id = ks.kelas_id
    JOIN t_tahun_ajaran ta ON ta.id = ks.tahun_ajaran_id
    WHERE ks.siswa_id = $siswaId
      AND ks.kelas_id = $kelasId
      AND ks.tahun_ajaran_id = $tahunId
      AND ks.status_aktif = 1
      AND s.status_aktif = 1
      AND k.status_aktif = 1
    LIMIT 1
");

if (!$qSiswa) {
    die("Gagal mengambil data siswa: " . mysqli_error($koneksi));
}

$siswa = mysqli_fetch_assoc($qSiswa);

if (!$siswa) {
    http_response_code(404);
    die("Data siswa tidak ditemukan atau sudah tidak aktif pada penempatan tersebut.");
}

/* HITUNG REKAP POIN */
$qRekap = mysqli_query($koneksi, "
    SELECT
        COUNT(*) AS jumlah_pelanggaran,
        COALESCE(SUM(poin), 0) AS total_pengurangan
    FROM t_pelanggaran_siswa
    WHERE siswa_id = $siswaId
      AND kelas_id = $kelasId
      AND tahun_ajaran_id = $tahunId
");

if (!$qRekap) {
    die("Gagal menghitung poin: " . mysqli_error($koneksi));
}

$rekap = mysqli_fetch_assoc($qRekap);

$totalPengurangan = (int)$rekap['total_pengurangan'];
$jumlahPelanggaran = (int)$rekap['jumlah_pelanggaran'];
$sisaPoin = max(0, 100 - $totalPengurangan);

[$namaStatus, $warnaStatus, $kelasStatus, $pesanStatus] = statusPoin($sisaPoin);

/* RIWAYAT PELANGGARAN */
$qRiwayat = mysqli_query($koneksi, "
    SELECT
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

if (!$qRiwayat) {
    die("Gagal mengambil riwayat pelanggaran: " . mysqli_error($koneksi));
}

/* TENTUKAN TOMBOL KEMBALI */
$urlKembali = ($role === 'guru' && $isWaliKelas)
    ? 'rekap_kelas.php'
    : 'rekap_poin.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Rekap Poin Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { margin: 0; background: #F1F5FB; }

        .konten-utama {
            margin-left: 220px !important;
            width: calc(100% - 220px) !important;
            min-height: 100vh;
            padding: 100px 24px 30px;
            box-sizing: border-box;
        }

        .konten-utama, .konten-utama * { box-sizing: border-box; }

        .judul-halaman { color: #173B67; font-weight: 700; }

        .deskripsi-halaman, .info-data {
            color: #718096;
            font-size: 13px;
        }

        .card-data {
            background: #fff;
            border: 1px solid #E5EAF2;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(23,59,103,.06);
        }

        .kartu-poin {
            background: #F8FAFD;
            border: 1px solid #E5EAF2;
            border-radius: 12px;
            padding: 20px;
        }

        .angka-poin {
            color: #173B67;
            font-size: 34px;
            font-weight: 750;
            line-height: 1.2;
        }

        .progress-poin {
            height: 11px;
            border-radius: 20px;
            background: #E1E8F0;
        }

        .progress-poin .progress-bar { border-radius: 20px; }

        .badge-poin {
            padding: 7px 11px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .poin-baik { background: #E3F5EA; color: #21834B; }
        .poin-perhatian { background: #E5F0FC; color: #245B96; }
        .poin-pembinaan { background: #FFF3D6; color: #946200; }
        .poin-peringatan { background: #FCE8D5; color: #A65313; }
        .poin-khusus { background: #FCE8E8; color: #C03939; }

        .kotak-peringatan {
            border-radius: 10px;
            padding: 16px;
            border: 1px solid;
        }

        .peringatan-success {
            background: #EAF7EF;
            border-color: #CBE9D5;
            color: #21834B;
        }

        .peringatan-info {
            background: #EAF2FB;
            border-color: #D7E6F7;
            color: #245B96;
        }

        .peringatan-warning {
            background: #FFF7E6;
            border-color: #F5E2B9;
            color: #946200;
        }

        .peringatan-danger {
            background: #FCEEEE;
            border-color: #F2CCCC;
            color: #B83232;
        }

        .btn-kembali {
            background: #245B96;
            border: 1px solid #245B96;
            color: #fff;
            border-radius: 8px;
            padding: 9px 16px;
            font-weight: 600;
        }

        .btn-kembali:hover {
            background: #173B67;
            border-color: #173B67;
            color: #fff;
        }

        .table { margin-bottom: 0; font-size: 14px; }

        .table thead th {
            background: #173B67;
            color: #fff;
            padding: 13px 12px;
            border-color: #31577F;
        }

        .table tbody td {
            padding: 12px;
            color: #40546B;
            border-color: #E8EDF4;
        }

        @media (max-width: 767.98px) {
            .konten-utama { padding: 100px 15px 25px; }
            .judul-halaman { font-size: 22px; }
        }
    </style>
</head>

<body>

<?php include "includes/sidebar.php"; ?>
<?php include "includes/navbar.php"; ?>

<main class="konten-utama">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="judul-halaman mb-1">Detail Rekap Poin Siswa</h2>
            <p class="deskripsi-halaman mb-0">
                Informasi saldo poin dan riwayat pelanggaran siswa.
            </p>
        </div>

        <a href="<?= e($urlKembali); ?>" class="btn btn-kembali">
            Kembali ke Rekap
        </a>
    </div>

    <div class="card card-data mb-4">
        <div class="card-body p-3 p-md-4">

            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
                <div>
                    <h4 class="fw-bold mb-1" style="color:#173B67;">
                        <?= e($siswa['nama_siswa']); ?>
                    </h4>
                    <div class="deskripsi-halaman">
                        NIS: <?= e($siswa['nis']); ?> ·
                        <?= e($siswa['nama_kelas']); ?> ·
                        Tahun Ajaran <?= e($siswa['tahun_ajaran']); ?>
                    </div>
                </div>

                <span class="badge text-bg-<?= e($warnaStatus); ?> px-3 py-2">
                    <?= e($namaStatus); ?>
                </span>
            </div>

            <div class="kartu-poin mb-4">
                <div class="info-data mb-2">Sisa Poin Siswa</div>

                <div class="angka-poin mb-3">
                    <?= $sisaPoin; ?>
                    <span class="fs-5 fw-normal text-secondary">/ 100 poin</span>
                </div>

                <div class="progress progress-poin mb-3">
                    <div
                        class="progress-bar bg-<?= e($warnaStatus); ?>"
                        role="progressbar"
                        style="width: <?= $sisaPoin; ?>%;"
                        aria-valuenow="<?= $sisaPoin; ?>"
                        aria-valuemin="0"
                        aria-valuemax="100">
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-between gap-2">
                    <small class="text-secondary">
                        Total poin awal: <strong>100</strong>
                    </small>
                    <small class="text-secondary">
                        Poin berkurang: <strong><?= $totalPengurangan; ?></strong>
                    </small>
                </div>
            </div>

            <div class="kotak-peringatan peringatan-<?= e($warnaStatus); ?>">
                <div class="fw-bold mb-1">Informasi <?= $sisaPoin < 40 ? 'Peringatan' : 'Status Poin'; ?></div>
                <?= e($pesanStatus); ?>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-md-6">
                    <div class="p-3 rounded-3 bg-light h-100">
                        <div class="info-data">Jumlah Pelanggaran</div>
                        <div class="fs-4 fw-bold" style="color:#173B67;">
                            <?= $jumlahPelanggaran; ?> kali
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 rounded-3 bg-light h-100">
                        <div class="info-data">Total Pengurangan Poin</div>
                        <div class="fs-4 fw-bold" style="color:#173B67;">
                            <?= $totalPengurangan; ?> poin
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="card card-data">
        <div class="card-body p-3 p-md-4">

            <div class="mb-3">
                <h5 class="fw-bold mb-1" style="color:#173B67;">
                    Riwayat Pengurangan Poin
                </h5>
                <p class="deskripsi-halaman mb-0">
                    Daftar pelanggaran yang mengurangi poin siswa pada tahun ajaran ini.
                </p>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Tanggal</th>
                            <th>Pelanggaran</th>
                            <th>Keterangan</th>
                            <th>Pengurangan</th>
                            <th>Guru Pelapor</th>
                            <th>Tindakan</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (mysqli_num_rows($qRiwayat) > 0): ?>
                        <?php $no = 1; ?>
                        <?php while ($riwayat = mysqli_fetch_assoc($qRiwayat)): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td>
                                    <?= $riwayat['tanggal']
                                        ? date('d-m-Y', strtotime($riwayat['tanggal']))
                                        : '-'; ?>
                                </td>
                                <td><?= e($riwayat['nama_pelanggaran']); ?></td>
                                <td><?= e($riwayat['keterangan'] ?: '-'); ?></td>
                                <td class="text-danger fw-semibold">
                                    -<?= (int)$riwayat['poin']; ?> poin
                                </td>
                                <td><?= e($riwayat['nama_guru'] ?: '-'); ?></td>
                                <td><?= e($riwayat['tindakan'] ?: 'Belum ada tindakan'); ?></td>
                                <td>
                                    <?php
                                    $status = trim($riwayat['status'] ?? '');
                                    $warna = 'secondary';

                                    if (strcasecmp($status, 'Selesai') === 0) {
                                        $warna = 'success';
                                    } elseif (
                                        strcasecmp($status, 'Diproses') === 0 ||
                                        strcasecmp($status, 'Sedang diproses') === 0
                                    ) {
                                        $warna = 'warning';
                                    } elseif (
                                        strcasecmp($status, 'Belum ditangani') === 0 ||
                                        strcasecmp($status, 'Belum Ditindak') === 0
                                    ) {
                                        $warna = 'danger';
                                    }
                                    ?>
                                    <span class="badge text-bg-<?= $warna; ?>">
                                        <?= e($status ?: 'Belum ditangani'); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Belum ada riwayat pelanggaran untuk siswa ini pada tahun ajaran tersebut.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>