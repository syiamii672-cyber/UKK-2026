
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

$role = strtolower(trim((string)($_SESSION['role'] ?? '')));
$idUser = (int)($_SESSION['id'] ?? 0);
$menuAktif = "laporan";

if (!in_array($role, ['admin', 'guru'], true)) {
    header("Location: dashboard.php");
    exit;
}

function e($nilai) {
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

// ==========================================
// CEK WALI KELAS
// ==========================================

$isWaliKelas = false;
$kelasWali = 0;
$tahunWali = 0;
$namaKelasWali = '';

if ($role === 'guru') {
    $queryWali = mysqli_query($koneksi, "
        SELECT
            w.kelas_id,
            w.tahun_ajaran_id,
            k.nama AS nama_kelas
        FROM t_guru g
        JOIN t_wali_kelas w ON g.id = w.guru_id
        JOIN t_kelas k ON w.kelas_id = k.id
        JOIN t_tahun_ajaran ta ON w.tahun_ajaran_id = ta.id
        WHERE g.user_id = $idUser
            AND g.status_aktif = 1
            AND w.status_aktif = 1
            AND k.status_aktif = 1
            AND ta.status_aktif = 1
        LIMIT 1
    ");

    if (!$queryWali) {
        die("Gagal memeriksa wali kelas: " . mysqli_error($koneksi));
    }

    if (mysqli_num_rows($queryWali) > 0) {
        $dataWali = mysqli_fetch_assoc($queryWali);

        $isWaliKelas = true;
        $kelasWali = (int)$dataWali['kelas_id'];
        $tahunWali = (int)$dataWali['tahun_ajaran_id'];
        $namaKelasWali = $dataWali['nama_kelas'];
    }
}

// ==========================================
// FILTER PENCARIAN
// ==========================================

$cari = isset($_GET['cari']) && is_scalar($_GET['cari'])
    ? trim((string)$_GET['cari'])
    : '';

$cariSQL = mysqli_real_escape_string($koneksi, $cari);

// ==========================================
// TAHUN AJARAN AKTIF
// ==========================================

$queryAktif = mysqli_query($koneksi, "
    SELECT id
    FROM t_tahun_ajaran
    WHERE status_aktif = 1
    LIMIT 1
");

if (!$queryAktif) {
    die("Gagal mengambil tahun ajaran aktif: " . mysqli_error($koneksi));
}

$dataAktif = mysqli_fetch_assoc($queryAktif);
$tahunAktif = (int)($dataAktif['id'] ?? 0);

// ==========================================
// FILTER TAHUN AJARAN
// ==========================================

if ($isWaliKelas) {
    $tahun = $tahunWali;
} else {
    $tahunInput = isset($_GET['tahun']) && is_scalar($_GET['tahun'])
        ? trim((string)$_GET['tahun'])
        : (string)$tahunAktif;

    if ($tahunInput === 'all') {
        $tahun = 'all';
    } elseif (ctype_digit($tahunInput)) {
        $tahun = (int)$tahunInput;
    } else {
        $tahun = $tahunAktif;
    }
}

// ==========================================
// KONDISI DATA LAPORAN
// ==========================================

$where = " WHERE ps.status = 'Selesai' ";

if ($isWaliKelas) {
    $where .= "
        AND ps.kelas_id = $kelasWali
        AND ps.tahun_ajaran_id = $tahunWali
    ";
} elseif ($tahun !== 'all') {
    $where .= " AND ps.tahun_ajaran_id = $tahun ";
}

if ($cari !== '') {
    $where .= "
        AND (
            ps.nama_siswa LIKE '%$cariSQL%'
            OR s.nis LIKE '%$cariSQL%'
            OR ps.nama_kelas LIKE '%$cariSQL%'
            OR ps.nama_pelanggaran LIKE '%$cariSQL%'
            OR ps.nama_guru LIKE '%$cariSQL%'
            OR ps.keterangan LIKE '%$cariSQL%'
        )
    ";
}

// ==========================================
// QUERY DATA LAPORAN
// ==========================================

$sqlData = "
    SELECT
        ps.*,
        s.nis
    FROM t_pelanggaran_siswa ps
    LEFT JOIN t_siswa s ON ps.siswa_id = s.id
    $where
    ORDER BY ps.tanggal DESC, ps.id DESC
";

// ==========================================
// EKSPOR EXCEL (CSV)
// ==========================================

if (isset($_GET['export']) && $_GET['export'] === 'excel') {

    $queryExport = mysqli_query($koneksi, $sqlData);

    if (!$queryExport) {
        die("Gagal mengekspor laporan: " . mysqli_error($koneksi));
    }

    $namaFile = "laporan_pelanggaran_" . date('Ymd_His') . ".csv";

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $namaFile . '"');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    // BOM UTF-8
    echo "\xEF\xBB\xBF";

    $output = fopen('php://output', 'w');

    fputcsv($output, [
        'No',
        'Tanggal',
        'NIS',
        'Nama Siswa',
        'Kelas',
        'Pelanggaran',
        'Pengurangan Poin',
        'Guru Pencatat',
        'Tindakan',
        'Keterangan',
        'Status'
    ], ';', '"', '');

    $no = 1;

    while ($data = mysqli_fetch_assoc($queryExport)) {

        $tanggal = '-';

        if (
            !empty($data['tanggal']) &&
            strtotime($data['tanggal']) !== false
        ) {
            $tanggal = date('d-m-Y', strtotime($data['tanggal']));
        }

        $baris = [
            $no++,
            $tanggal,
            $data['nis'] ?? '-',
            $data['nama_siswa'] ?? '-',
            $data['nama_kelas'] ?? '-',
            $data['nama_pelanggaran'] ?? '-',
            (int)($data['poin'] ?? 0),
            $data['nama_guru'] ?? '-',
            $data['tindakan'] ?: '-',
            $data['keterangan'] ?: '-',
            'Selesai'
        ];

        fputcsv($output, $baris, ';', '"', '');
    }

    fclose($output);
    exit;
}

// ==========================================
// HITUNG JUMLAH DATA
// ==========================================

$queryJumlah = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total
    FROM t_pelanggaran_siswa ps
    LEFT JOIN t_siswa s ON ps.siswa_id = s.id
    $where
");

if (!$queryJumlah) {
    die("Gagal menghitung laporan: " . mysqli_error($koneksi));
}

$dataJumlah = mysqli_fetch_assoc($queryJumlah);
$totalData = (int)$dataJumlah['total'];

// ==========================================
// MODE CETAK
// ==========================================

$modeCetak = isset($_GET['cetak']) && $_GET['cetak'] === '1';

// ==========================================
// PAGINATION
// ==========================================

$batas = 10;

$halaman = isset($_GET['halaman']) && is_scalar($_GET['halaman'])
    ? max(1, (int)$_GET['halaman'])
    : 1;

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

// ==========================================
// DAFTAR TAHUN AJARAN
// ==========================================

$queryTahun = mysqli_query($koneksi, "
    SELECT id, nama
    FROM t_tahun_ajaran
    ORDER BY tanggal_mulai DESC
");

if (!$queryTahun) {
    die("Gagal mengambil daftar tahun ajaran: " . mysqli_error($koneksi));
}

// ==========================================
// LINK FILTER
// ==========================================

$parameter = ['cari' => $cari];

if (!$isWaliKelas) {
    $parameter['tahun'] = (string)$tahun;
}

$linkExcel = 'laporan.php?' . http_build_query(
    array_merge($parameter, ['export' => 'excel'])
);

$linkCetak = 'laporan.php?' . http_build_query(
    array_merge($parameter, ['cetak' => '1'])
);

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= $modeCetak ? 'Cetak Laporan Pelanggaran' : 'Laporan Pelanggaran'; ?></title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            margin: 0;
            background: #f5f7fb;
        }

        .konten-utama {
            margin-left: 220px;
            width: calc(100% - 220px);
            min-height: 100vh;
            padding: 25px;
            box-sizing: border-box;
        }

        .kartu {
            border: none;
            border-radius: 12px;
        }

        .table th {
            white-space: nowrap;
        }

        @media (max-width: 575.98px) {
            .konten-utama {
                margin-left: 180px;
                width: calc(100% - 180px);
                padding: 15px;
            }
        }

        @media print {
            body {
                background: white !important;
            }

            .konten-utama {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }

            .sidebar,
            .bagian-filter,
            .bagian-tombol,
            .bagian-pagination,
            .alert {
                display: none !important;
            }

            .card {
                box-shadow: none !important;
                border: none !important;
            }

            .table {
                font-size: 10px;
            }

            .table th,
            .table td {
                padding: 5px !important;
            }

            @page {
                size: landscape;
                margin: 12mm;
            }
        }
    </style>
</head>

<body>

<?php if (!$modeCetak): ?>
    <?php include "includes/sidebar.php"; ?>
<?php endif; ?>

<main class="konten-utama">

    <div class="mb-4">
        <h2 class="fw-bold mb-1">
            <?= $modeCetak ? 'Laporan Pelanggaran Siswa' : 'Laporan Pelanggaran'; ?>
        </h2>

        <p class="text-secondary mb-0">
            Rekap data pelanggaran siswa yang telah selesai ditangani.
        </p>
    </div>

    <?php if ($isWaliKelas): ?>
        <div class="alert alert-primary">
            <strong>Wali Kelas</strong> |
            Kelas: <?= e($namaKelasWali); ?>
        </div>
    <?php endif; ?>

    <div class="card kartu shadow-sm">
        <div class="card-body">

            <!-- FILTER -->
            <form method="GET" action="laporan.php"
                  class="row g-2 mb-3 bagian-filter">

                <div class="col-md-4">
                    <input
                        type="text"
                        name="cari"
                        class="form-control"
                        placeholder="Cari NIS, siswa, kelas, pelanggaran..."
                        value="<?= e($cari); ?>"
                    >
                </div>

                <?php if (!$isWaliKelas): ?>
                    <div class="col-md-3">
                        <select name="tahun" class="form-select">
                            <option value="all"
                                <?= $tahun === 'all' ? 'selected' : ''; ?>>
                                Semua Tahun Ajaran
                            </option>

                            <?php while ($ta = mysqli_fetch_assoc($queryTahun)): ?>
                                <option
                                    value="<?= (int)$ta['id']; ?>"
                                    <?= (string)$tahun === (string)$ta['id'] ? 'selected' : ''; ?>
                                >
                                    <?= e($ta['nama']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                <?php else: ?>
                    <div class="col-md-3">
                        <input
                            type="text"
                            class="form-control"
                            value="Kelas <?= e($namaKelasWali); ?>"
                            disabled
                        >
                    </div>
                <?php endif; ?>

                <div class="col-md-2">
                    <input
                        type="text"
                        class="form-control"
                        value="Selesai"
                        disabled
                    >
                </div>

                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        Cari
                    </button>
                </div>

                <div class="col-md-2">
                    <a href="laporan.php" class="btn btn-secondary w-100">
                        Reset
                    </a>
                </div>
            </form>

            <!-- TOMBOL CETAK DAN EKSPOR -->
            <div class="d-flex flex-wrap gap-2 mb-3 bagian-tombol">

                <button type="button"
                onclick="window.print()"
                class="btn btn-primary">
            Cetak Laporan
                </button>

                <a href="<?= e($linkExcel); ?>"
                   class="btn btn-success">
                    Ekspor Excel
                </a>

            </div>

            <!-- INFORMASI JUMLAH -->
            <?php if (!$modeCetak): ?>
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <small class="text-secondary">
                        Menampilkan
                        <?= $totalData > 0 ? $offset + 1 : 0; ?> -
                        <?= min($offset + $batas, $totalData); ?>
                        dari <?= $totalData; ?> laporan
                    </small>

                    <small class="text-secondary">
                        Halaman <?= $halaman; ?> dari <?= $totalHalaman; ?>
                    </small>
                </div>
            <?php else: ?>
                <p class="mb-3">
                    Tanggal cetak: <?= date('d-m-Y H:i'); ?>
                    <br>
                    Total data: <?= $totalData; ?>
                </p>
            <?php endif; ?>

            <!-- TABEL -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-light">
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

                                <?php
                                $tanggal = '-';

                                if (
                                    !empty($data['tanggal']) &&
                                    strtotime($data['tanggal']) !== false
                                ) {
                                    $tanggal = date(
                                        'd-m-Y',
                                        strtotime($data['tanggal'])
                                    );
                                }
                                ?>

                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= e($tanggal); ?></td>
                                    <td><?= e($data['nis'] ?? '-'); ?></td>
                                    <td><?= e($data['nama_siswa']); ?></td>
                                    <td><?= e($data['nama_kelas']); ?></td>
                                    <td><?= e($data['nama_pelanggaran']); ?></td>
                                    <td><?= (int)$data['poin']; ?> poin</td>
                                    <td><?= e($data['nama_guru']); ?></td>
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
                                <td colspan="11"
                                    class="text-center text-secondary py-4">
                                    Belum ada laporan pelanggaran yang selesai ditangani.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>

                </table>
            </div>

            <!-- PAGINATION -->
            <?php if (!$modeCetak): ?>

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 bagian-pagination">

                    <small class="text-secondary">
                        Total <?= $totalData; ?> laporan pelanggaran
                    </small>

                    <?php if ($totalHalaman > 1): ?>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">

                                <li class="page-item <?= $halaman <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link"
                                       href="?<?= e(http_build_query(array_merge(
                                           $parameter,
                                           ['halaman' => max(1, $halaman - 1)]
                                       ))); ?>">
                                        Sebelumnya
                                    </a>
                                </li>

                                <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>
                                    <li class="page-item <?= $halaman == $i ? 'active' : ''; ?>">
                                        <a class="page-link"
                                           href="?<?= e(http_build_query(array_merge(
                                               $parameter,
                                               ['halaman' => $i]
                                           ))); ?>">
                                            <?= $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <li class="page-item <?= $halaman >= $totalHalaman ? 'disabled' : ''; ?>">
                                    <a class="page-link"
                                       href="?<?= e(http_build_query(array_merge(
                                           $parameter,
                                           ['halaman' => min($totalHalaman, $halaman + 1)]
                                       ))); ?>">
                                        Selanjutnya
                                    </a>
                                </li>

                            </ul>
                        </nav>
                    <?php endif; ?>

                </div>

            <?php else: ?>

                <script>
                    window.onload = function () {
                        window.print();
                    };
                </script>

            <?php endif; ?>

        </div>
    </div>

</main>

</body>
</html>
