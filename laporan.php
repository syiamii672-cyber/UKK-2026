
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

date_default_timezone_set('Asia/Jakarta');

$role = strtolower(trim($_SESSION['role'] ?? ''));
$idUser = (int)($_SESSION['id'] ?? 0);
$menuAktif = "laporan";

if (!in_array($role, ['admin', 'guru'], true)) {
    header("Location: dashboard.php");
    exit;
}

function e($nilai)
{
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

function validTanggal($tanggal)
{
    $d = DateTime::createFromFormat('!Y-m-d', $tanggal);
    return $d && $d->format('Y-m-d') === $tanggal;
}

// =====================================================
// TAHUN AJARAN AKTIF
// =====================================================

$qAktif = mysqli_query($koneksi, "
    SELECT id, nama
    FROM t_tahun_ajaran
    WHERE status_aktif = 1
    ORDER BY tanggal_mulai DESC
    LIMIT 1
");

if (!$qAktif) {
    die("Gagal mengambil tahun ajaran: " . mysqli_error($koneksi));
}

$dataTahunAktif = mysqli_fetch_assoc($qAktif);

$tahunAktif = (int)($dataTahunAktif['id'] ?? 0);
$namaTahunAktif = $dataTahunAktif['nama'] ?? 'Belum ada tahun ajaran aktif';

// =====================================================
// CEK WALI KELAS
// =====================================================

$isWaliKelas = false;
$kelasWali = 0;
$tahunWali = 0;
$namaKelasWali = '';

if ($role === 'guru' && $tahunAktif > 0) {

    $qWali = mysqli_query($koneksi, "
        SELECT
            w.kelas_id,
            w.tahun_ajaran_id,
            k.nama AS nama_kelas
        FROM t_guru g
        JOIN t_wali_kelas w ON g.id = w.guru_id
        JOIN t_kelas k ON w.kelas_id = k.id
        WHERE g.user_id = $idUser
          AND g.status_aktif = 1
          AND w.status_aktif = 1
          AND w.tahun_ajaran_id = $tahunAktif
          AND k.status_aktif = 1
        LIMIT 1
    ");

    if (!$qWali) {
        die("Gagal memeriksa wali kelas: " . mysqli_error($koneksi));
    }

    if (mysqli_num_rows($qWali) > 0) {
        $wali = mysqli_fetch_assoc($qWali);

        $isWaliKelas = true;
        $kelasWali = (int)$wali['kelas_id'];
        $tahunWali = (int)$wali['tahun_ajaran_id'];
        $namaKelasWali = $wali['nama_kelas'];
    }
}

// =====================================================
// FILTER
// =====================================================

$cari = trim($_GET['cari'] ?? '');
$tanggalMulai = trim($_GET['tanggal_mulai'] ?? '');
$tanggalSelesai = trim($_GET['tanggal_selesai'] ?? '');

if ($tanggalMulai !== '' && !validTanggal($tanggalMulai)) {
    $tanggalMulai = '';
}

if ($tanggalSelesai !== '' && !validTanggal($tanggalSelesai)) {
    $tanggalSelesai = '';
}

$pesanTanggal = '';

if (
    $tanggalMulai !== '' &&
    $tanggalSelesai !== '' &&
    $tanggalMulai > $tanggalSelesai
) {
    $pesanTanggal = "Tanggal mulai tidak boleh melebihi tanggal selesai.";
}

$cariSQL = mysqli_real_escape_string($koneksi, $cari);

// =====================================================
// KONDISI DATA
// =====================================================

$where = " WHERE ps.status = 'Selesai' ";

if ($tahunAktif > 0) {
    $where .= " AND ps.tahun_ajaran_id = $tahunAktif ";
} else {
    $where .= " AND 1 = 0 ";
}

// Wali kelas hanya melihat kelasnya sendiri
if ($isWaliKelas) {
    $where .= "
        AND ps.kelas_id = $kelasWali
        AND ps.tahun_ajaran_id = $tahunWali
    ";
}

// =====================================================
// FILTER TANGGAL
// =====================================================

if ($pesanTanggal !== '') {
    $where .= " AND 1 = 0 ";
} else {

    if ($tanggalMulai !== '') {
        $mulaiSQL = mysqli_real_escape_string($koneksi, $tanggalMulai);

        $where .= "
            AND ps.tanggal >= '$mulaiSQL 00:00:00'
        ";
    }

    if ($tanggalSelesai !== '') {
        $tanggalBerikutnya = date(
            'Y-m-d',
            strtotime($tanggalSelesai . ' +1 day')
        );

        $selesaiSQL = mysqli_real_escape_string(
            $koneksi,
            $tanggalBerikutnya
        );

        $where .= "
            AND ps.tanggal < '$selesaiSQL 00:00:00'
        ";
    }
}

// =====================================================
// PENCARIAN
// =====================================================

if ($cari !== '') {

    $polaCari = '%' . $cariSQL . '%';

    $where .= "
        AND (
            ps.nama_siswa LIKE '$polaCari'
            OR s.nis LIKE '$polaCari'
            OR ps.nama_kelas LIKE '$polaCari'
            OR ps.nama_pelanggaran LIKE '$polaCari'
            OR ps.nama_guru LIKE '$polaCari'
            OR ps.keterangan LIKE '$polaCari'
            OR ps.tindakan LIKE '$polaCari'
        )
    ";
}

// =====================================================
// QUERY DATA
// =====================================================

$sqlData = "
    SELECT
        ps.*,
        s.nis
    FROM t_pelanggaran_siswa ps
    LEFT JOIN t_siswa s ON ps.siswa_id = s.id
    $where
    ORDER BY ps.tanggal DESC, ps.id DESC
";

// =====================================================
// PARAMETER FILTER
// =====================================================

$parameter = [
    'cari' => $cari,
    'tanggal_mulai' => $tanggalMulai,
    'tanggal_selesai' => $tanggalSelesai
];

// =====================================================
// LINK CETAK DAN EXCEL
// =====================================================

$linkExcel = 'laporan.php?' . http_build_query(
    array_merge($parameter, ['export' => 'excel'])
);

$linkCetak = 'laporan.php?' . http_build_query(
    array_merge($parameter, ['cetak' => '1'])
);

// =====================================================
// EKSPOR EXCEL
// =====================================================

if (($_GET['export'] ?? '') === 'excel') {

    $queryExport = mysqli_query($koneksi, $sqlData);

    if (!$queryExport) {
        die("Gagal mengekspor laporan: " . mysqli_error($koneksi));
    }

    while (ob_get_level()) {
        ob_end_clean();
    }

    $namaFile = 'laporan_pelanggaran_' . date('Ymd_His') . '.xls';

    header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
    header("Content-Disposition: attachment; filename=\"$namaFile\"");
    header("Cache-Control: max-age=0");

    echo "\xEF\xBB\xBF";
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <style>
           
body {
    margin: 0;
    background: #F1F5FB;
    color: #26374A;
}

.konten-utama {
    margin-left: 220px !important;
    width: calc(100% - 220px) !important;
    min-height: 100vh;
    padding: 100px 24px 30px;
    box-sizing: border-box;
}

.konten-utama,
.konten-utama * {
    box-sizing: border-box;
}

.konten-utama .judul-halaman {
    color: #173B67;
    font-weight: 700;
}

.konten-utama .kartu {
    background: #fff;
    border: 1px solid #E5EAF2;
    border-radius: 12px;
    box-shadow: 0 3px 12px rgba(23,59,103,.06);
}

.konten-utama .card-body {
    padding: 24px;
}

.konten-utama .form-control,
.konten-utama .form-select {
    min-height: 42px;
    border: 1px solid #D8E1ED;
    border-radius: 8px;
    font-size: 14px;
}

.konten-utama .form-control:focus,
.konten-utama .form-select:focus {
    border-color: #3779C5;
    box-shadow: 0 0 0 3px rgba(55,121,197,.12);
}

.konten-utama .btn {
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    padding: 9px 15px;
}

.konten-utama .btn-primary {
    background: #245B96;
    border-color: #245B96;
    color: #fff;
}

.konten-utama .btn-primary:hover {
    background: #173B67;
    border-color: #173B67;
    color: #fff;
}

.konten-utama .btn-outline-primary {
    color: #245B96;
    border-color: #245B96;
}

.konten-utama .btn-outline-primary:hover {
    color: #fff;
    background: #173B67;
    border-color: #173B67;
}

.konten-utama .btn-outline-secondary {
    background: #E9EEF5;
    border-color: #E9EEF5;
    color: #40546B;
}

.konten-utama .btn-outline-secondary:hover {
    background: #DCE5F0;
    border-color: #DCE5F0;
    color: #173B67;
}

.konten-utama .table {
    margin-bottom: 0;
    font-size: 13px;
    vertical-align: middle;
}

.konten-utama .table thead th {
    background: #173B67;
    color: #fff;
    font-weight: 600;
    white-space: nowrap;
    padding: 13px 12px;
    border-color: #31577F;
}

.konten-utama .table tbody td {
    padding: 12px;
    color: #40546B;
    border-color: #E8EDF4;
}

.konten-utama .table tbody tr:hover {
    background: #F7FAFE;
}

.konten-utama .badge {
    font-size: 12px;
    font-weight: 600;
    padding: 6px 10px;
    border-radius: 20px;
}

.konten-utama .pagination {
    gap: 4px;
}

.konten-utama .page-link {
    border: 1px solid #DCE5F0;
    border-radius: 6px !important;
    color: #245B96;
    font-size: 13px;
}

.konten-utama .page-link:hover {
    background: #EAF2FB;
    color: #173B67;
}

.konten-utama .page-item.active .page-link {
    background: #245B96;
    border-color: #245B96;
    color: #fff;
}

.konten-utama .page-item.disabled .page-link {
    color: #AAB6C5;
}

.konten-utama input[type="date"] {
    cursor: pointer;
}

.konten-utama .info-tahun {
    color: #245B96;
    background: #EAF2FB;
    border: 1px solid #D7E6F7;
    border-radius: 8px;
    padding: 9px 13px;
    font-size: 13px;
    font-weight: 600;
}

@media (max-width: 767.98px) {
    .konten-utama {
        padding: 100px 15px 25px;
    }

    .konten-utama .card-body {
        padding: 16px;
    }

    .konten-utama .judul-halaman {
        font-size: 22px;
    }
}

@media print {
    @page {
        size: landscape;
        margin: 12mm;
    }

    html,
    body {
        background: #fff !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    body > :not(.konten-utama) {
        display: none !important;
    }

    .konten-utama {
        display: block !important;
        position: static !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        min-height: auto !important;
    }

    .bagian-filter,
    .bagian-tombol,
    .bagian-pagination,
    .alert,
    .no-print {
        display: none !important;
    }

    .konten-utama .kartu,
    .konten-utama .card {
        box-shadow: none !important;
        border: none !important;
    }

    .konten-utama .card-body {
        padding: 0 !important;
    }

    .konten-utama .table-responsive {
        overflow: visible !important;
    }

    .konten-utama .table {
        width: 100% !important;
        font-size: 9px !important;
    }

    .konten-utama .table th,
    .konten-utama .table td {
        padding: 5px !important;
        color: #000 !important;
        border: 1px solid #999 !important;
    }

    .konten-utama .table thead th {
        background: #173B67 !important;
        color: #fff !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .konten-utama .judul-halaman {
        color: #173B67 !important;
    }
}

        </style>
    </head>
    <body>
        <h3>LAPORAN PELANGGARAN SISWA</h3>
        <p>Tahun Ajaran: <?= e($namaTahunAktif); ?></p>
        <p>Tanggal ekspor: <?= date('d-m-Y H:i:s'); ?> WIB</p>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>NIS</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Pelanggaran</th>
                    <th>Pengurangan Poin</th>
                    <th>Guru Pencatat</th>
                    <th>Tindakan</th>
                    <th>Keterangan</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; ?>

                <?php while ($data = mysqli_fetch_assoc($queryExport)): ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td>
                            <?= !empty($data['tanggal'])
                                ? date('d-m-Y', strtotime($data['tanggal']))
                                : '-'; ?>
                        </td>
                        <td style="mso-number-format:'\@';">
                            <?= e($data['nis'] ?? '-'); ?>
                        </td>
                        <td><?= e($data['nama_siswa']); ?></td>
                        <td><?= e($data['nama_kelas']); ?></td>
                        <td><?= e($data['nama_pelanggaran']); ?></td>
                        <td><?= (int)($data['poin'] ?? 0); ?></td>
                        <td><?= e($data['nama_guru'] ?: '-'); ?></td>
                        <td><?= e($data['tindakan'] ?: '-'); ?></td>
                        <td><?= e($data['keterangan'] ?: '-'); ?></td>
                        <td>Selesai</td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </body>
    </html>
    <?php
    exit;
}

// =====================================================
// HITUNG DATA
// =====================================================

$qJumlah = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total
    FROM t_pelanggaran_siswa ps
    LEFT JOIN t_siswa s ON ps.siswa_id = s.id
    $where
");

if (!$qJumlah) {
    die("Gagal menghitung laporan: " . mysqli_error($koneksi));
}

$totalData = (int)(mysqli_fetch_assoc($qJumlah)['total'] ?? 0);

// =====================================================
// MODE CETAK DAN PAGINATION
// =====================================================

$modeCetak = ($_GET['cetak'] ?? '') === '1';

$batas = 10;
$halaman = max(1, (int)($_GET['halaman'] ?? 1));

$totalHalaman = max(1, (int)ceil($totalData / $batas));

$halaman = min($halaman, $totalHalaman);
$offset = ($halaman - 1) * $batas;

if (!$modeCetak) {
    $sqlData .= " LIMIT $offset, $batas";
}

$queryLaporan = mysqli_query($koneksi, $sqlData);

if (!$queryLaporan) {
    die("Gagal mengambil laporan: " . mysqli_error($koneksi));
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= $modeCetak ? 'Cetak Laporan' : 'Laporan Pelanggaran'; ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            background: #F1F5FB;
            color: #26374A;
        }

        .konten-utama {
            margin-left: 220px !important;
            width: calc(100% - 220px) !important;
            min-height: 100vh;
            padding: 100px 24px 30px;
            box-sizing: border-box;
        }

        .konten-utama .judul-halaman {
            color: #173B67;
            font-weight: 700;
        }

        .konten-utama .kartu {
            border: 1px solid #E2EAF4;
            border-radius: 14px;
            background: #fff;
        }

        .konten-utama .card-body {
            padding: 24px;
        }

        .konten-utama .form-control,
        .konten-utama .form-select {
            min-height: 42px;
            border: 1px solid #D5DFEC;
            border-radius: 8px;
            font-size: 14px;
        }

        .konten-utama .form-control:focus,
        .konten-utama .form-select:focus {
            border-color: #3779C5;
            box-shadow: 0 0 0 3px rgba(55, 121, 197, .12);
        }

        .konten-utama .btn {
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            padding: 9px 15px;
        }

        .konten-utama .btn-primary {
            background: #245B96;
            border-color: #245B96;
        }

        .konten-utama .btn-primary:hover {
            background: #173B67;
            border-color: #173B67;
        }

        .konten-utama .btn-outline-primary {
            color: #245B96;
            border-color: #245B96;
        }

        .konten-utama .btn-outline-primary:hover {
            color: #fff;
            background: #245B96;
        }

        .konten-utama .table {
            margin-bottom: 0;
            font-size: 13px;
            vertical-align: middle;
        }

        .konten-utama .table thead th {
            background: #EAF1F9;
            color: #173B67;
            font-weight: 700;
            white-space: nowrap;
            padding: 13px 11px;
            border-bottom: 1px solid #D5DFEC;
        }

        .konten-utama .table tbody td {
            padding: 12px 11px;
            border-color: #E8EDF4;
        }

        .konten-utama .table tbody tr:hover {
            background: #F7FAFE;
        }

        .konten-utama .badge {
            font-size: 11px;
            font-weight: 600;
            padding: 6px 10px;
            border-radius: 20px;
        }

        .konten-utama .pagination {
            gap: 4px;
        }

        .konten-utama .page-link {
            border: 1px solid #D5DFEC;
            border-radius: 7px !important;
            color: #245B96;
        }

        .konten-utama .page-item.active .page-link {
            background: #245B96;
            border-color: #245B96;
        }

        .konten-utama .page-item.disabled .page-link {
            color: #AAB6C5;
        }

        .konten-utama input[type="date"] {
            cursor: pointer;
        }

        .konten-utama .info-tahun {
            color: #245B96;
            background: #EAF2FC;
            border: 1px solid #D7E6F8;
            border-radius: 8px;
            padding: 10px 13px;
            font-size: 13px;
        }

        @media (max-width: 767.98px) {
            .konten-utama {
                margin-left: 220px !important;
                width: calc(100% - 220px) !important;
                padding: 90px 15px 20px;
            }

            .konten-utama .card-body {
                padding: 16px;
            }
        }

        @media print {
            @page {
                size: landscape;
                margin: 12mm;
            }

            html,
            body {
                background: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            /* Sembunyikan semua elemen body selain isi laporan.
               Sidebar dan navbar tidak perlu diubah file aslinya. */
            body > :not(.konten-utama) {
                display: none !important;
            }

            .konten-utama {
                display: block !important;
                position: static !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                min-height: auto !important;
            }

            .bagian-filter,
            .bagian-tombol,
            .bagian-pagination,
            .alert,
            .no-print {
                display: none !important;
            }

            .konten-utama .kartu,
            .konten-utama .card {
                box-shadow: none !important;
                border: none !important;
            }

            .konten-utama .card-body {
                padding: 0 !important;
            }

            .konten-utama .table-responsive {
                overflow: visible !important;
            }

            .konten-utama .table {
                width: 100% !important;
                font-size: 9px !important;
            }

            .konten-utama .table th,
            .konten-utama .table td {
                padding: 5px !important;
                color: #000 !important;
                border: 1px solid #999 !important;
            }

            .konten-utama .table thead th {
                background: #EAF1F9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .konten-utama .judul-halaman {
                color: #173B67 !important;
            }
        }
    </style>
</head>

<body>

<?php include "includes/sidebar.php"; ?>
<?php include "includes/navbar.php"; ?>

<main class="konten-utama">

    <div class="mb-4">
        <h2 class="judul-halaman mb-1">
            <?= $modeCetak ? 'Laporan Pelanggaran Siswa' : 'Laporan Pelanggaran'; ?>
        </h2>

        <p class="text-secondary mb-2">
            Rekap data pelanggaran siswa yang telah selesai ditangani.
        </p>

        <div class="info-tahun d-inline-block">
            <strong>Tahun Ajaran Aktif:</strong>
            <?= e($namaTahunAktif); ?>
        </div>
    </div>

    <?php if ($isWaliKelas): ?>
        <div class="alert alert-primary">
            <strong>Wali Kelas</strong> |
            Kelas: <?= e($namaKelasWali); ?>
        </div>
    <?php endif; ?>

    <div class="card kartu shadow-sm">
        <div class="card-body">

            <?php if ($pesanTanggal): ?>
                <div class="alert alert-warning">
                    <?= e($pesanTanggal); ?>
                </div>
            <?php endif; ?>

            <form method="GET"
                  action="laporan.php"
                  id="filterLaporan"
                  class="row g-2 mb-3 bagian-filter">

                <div class="col-lg-4 col-md-12">
                    <input
                        type="text"
                        name="cari"
                        id="inputCari"
                        class="form-control"
                        placeholder="Cari NIS, nama siswa, kelas, pelanggaran..."
                        value="<?= e($cari); ?>"
                        autocomplete="off"
                    >
                </div>

                <div class="col-lg-2 col-md-6">
                    <input
                        type="date"
                        name="tanggal_mulai"
                        id="tanggalMulai"
                        class="form-control"
                        value="<?= e($tanggalMulai); ?>"
                        max="<?= e($tanggalSelesai); ?>"
                        title="Tanggal mulai"
                    >
                </div>

                <div class="col-lg-2 col-md-6">
                    <input
                        type="date"
                        name="tanggal_selesai"
                        id="tanggalSelesai"
                        class="form-control"
                        value="<?= e($tanggalSelesai); ?>"
                        min="<?= e($tanggalMulai); ?>"
                        title="Tanggal selesai"
                    >
                </div>

                <div class="col-lg-2 col-md-6">
                    <button type="submit" class="btn btn-primary w-100">
                        Cari Data
                    </button>
                </div>

                <div class="col-lg-2 col-md-6">
                    <a href="laporan.php" class="btn btn-outline-secondary w-100">
                        Reset Filter
                    </a>
                </div>

            </form>

            <div class="d-flex flex-wrap gap-2 mb-3 bagian-tombol">

                <a href="<?= e($linkCetak); ?>" class="btn btn-primary">
                    <i class="bi bi-printer"></i>
                    Cetak Laporan
                </a>

                <a href="<?= e($linkExcel); ?>" class="btn btn-outline-primary">
                    Ekspor Excel
                </a>

            </div>

            <?php if (!$modeCetak): ?>

                <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
                    <small class="text-secondary">
                        Menampilkan
                        <?= $totalData ? $offset + 1 : 0; ?> -
                        <?= min($offset + $batas, $totalData); ?>
                        dari <?= $totalData; ?> laporan
                    </small>

                    <small class="text-secondary">
                        Halaman <?= $halaman; ?> dari <?= $totalHalaman; ?>
                    </small>
                </div>

            <?php else: ?>

                <div class="mb-3 small">
                    <div>Tanggal cetak: <?= date('d-m-Y H:i:s'); ?> WIB</div>
                    <div>Total data: <?= $totalData; ?> laporan</div>
                </div>

            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Pelanggaran</th>
                            <th>Pengurangan Poin</th>
                            <th>Guru Pencatat</th>
                            <th>Tindakan</th>
                            <th>Keterangan</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (mysqli_num_rows($queryLaporan) > 0): ?>

                            <?php $no = $modeCetak ? 1 : $offset + 1; ?>

                            <?php while ($data = mysqli_fetch_assoc($queryLaporan)): ?>

                                <tr>
                                    <td><?= $no++; ?></td>

                                    <td>
                                        <?= !empty($data['tanggal'])
                                            ? date('d-m-Y', strtotime($data['tanggal']))
                                            : '-'; ?>
                                    </td>

                                    <td><?= e($data['nis'] ?? '-'); ?></td>
                                    <td class="fw-semibold"><?= e($data['nama_siswa']); ?></td>
                                    <td><?= e($data['nama_kelas']); ?></td>
                                    <td><?= e($data['nama_pelanggaran']); ?></td>
                                    <td><?= (int)($data['poin'] ?? 0); ?> poin</td>
                                    <td><?= e($data['nama_guru'] ?: '-'); ?></td>
                                    <td><?= e($data['tindakan'] ?: '-'); ?></td>
                                    <td><?= e($data['keterangan'] ?: '-'); ?></td>

                                    <td>
                                        <span class="badge bg-success">
                                            Selesai
                                        </span>
                                    </td>
                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="11" class="text-center text-secondary py-5">
                                    <div class="fw-semibold mb-1">
                                        Tidak ada data laporan
                                    </div>
                                    <small>
                                        Coba periksa kembali kata pencarian atau rentang tanggal.
                                    </small>
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>
                </table>
            </div>

            <?php if (!$modeCetak): ?>

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 bagian-pagination">

                    <small class="text-secondary">
                        Total <?= $totalData; ?> laporan pelanggaran
                    </small>

                    <?php if ($totalHalaman > 1): ?>

                        <?php
                        $linkHalaman = function ($nomor) use ($parameter) {
                            return '?' . http_build_query(
                                array_merge($parameter, ['halaman' => $nomor])
                            );
                        };
                        ?>

                        <nav>
                            <ul class="pagination pagination-sm mb-0">

                                <li class="page-item <?= $halaman <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link"
                                       href="<?= e($linkHalaman(max(1, $halaman - 1))); ?>">
                                        Sebelumnya
                                    </a>
                                </li>

                                <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>

                                    <li class="page-item <?= $halaman == $i ? 'active' : ''; ?>">
                                        <a class="page-link"
                                           href="<?= e($linkHalaman($i)); ?>">
                                            <?= $i; ?>
                                        </a>
                                    </li>

                                <?php endfor; ?>

                                <li class="page-item <?= $halaman >= $totalHalaman ? 'disabled' : ''; ?>">
                                    <a class="page-link"
                                       href="<?= e($linkHalaman(min($totalHalaman, $halaman + 1))); ?>">
                                        Selanjutnya
                                    </a>
                                </li>

                            </ul>
                        </nav>

                    <?php endif; ?>

                </div>

            <?php else: ?>

                <script>
                    window.addEventListener('load', function () {
                        window.print();
                    });
                </script>

            <?php endif; ?>

        </div>
    </div>

</main>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('filterLaporan');
    const inputCari = document.getElementById('inputCari');
    const tanggalMulai = document.getElementById('tanggalMulai');
    const tanggalSelesai = document.getElementById('tanggalSelesai');

    let timer = null;

    // PENCARIAN OTOMATIS
    if (inputCari) {

        inputCari.addEventListener('input', function () {

            clearTimeout(timer);

            timer = setTimeout(function () {
                form.requestSubmit();
            }, 400);

        });

        inputCari.addEventListener('keydown', function (event) {

            if (event.key === 'Enter') {
                clearTimeout(timer);
                form.requestSubmit();
            }

        });
    }

    // FILTER TANGGAL
    [tanggalMulai, tanggalSelesai].forEach(function (input) {

        if (!input) return;

        input.addEventListener('change', function () {

            const mulai = tanggalMulai.value;
            const selesai = tanggalSelesai.value;

            if (mulai && selesai && mulai > selesai) {
                alert('Tanggal mulai tidak boleh melebihi tanggal selesai.');
                return;
            }

            form.requestSubmit();

        });

    });

});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
