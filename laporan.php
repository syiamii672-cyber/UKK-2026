
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if (!in_array($_SESSION['role'] ?? '', ['admin', 'guru'], true)) {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "laporan";

function e($nilai) {
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

// Filter laporan
$tanggalMulai = trim($_GET['tanggal_mulai'] ?? '');
$tanggalSelesai = trim($_GET['tanggal_selesai'] ?? '');
$kelasId = (int)($_GET['kelas_id'] ?? 0);

$pesanFilter = '';

if (
    ($tanggalMulai !== '' &&
        (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalMulai) ||
         !checkdate((int)substr($tanggalMulai, 5, 2),
                    (int)substr($tanggalMulai, 8, 2),
                    (int)substr($tanggalMulai, 0, 4))))
    ||
    ($tanggalSelesai !== '' &&
        (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalSelesai) ||
         !checkdate((int)substr($tanggalSelesai, 5, 2),
                    (int)substr($tanggalSelesai, 8, 2),
                    (int)substr($tanggalSelesai, 0, 4))))
) {
    $pesanFilter = "Format tanggal tidak valid.";
} elseif (
    $tanggalMulai !== '' &&
    $tanggalSelesai !== '' &&
    $tanggalMulai > $tanggalSelesai
) {
    $pesanFilter = "Tanggal mulai tidak boleh melebihi tanggal selesai.";
}

$daftarKelas = mysqli_query(
    $koneksi,
    "SELECT id, nama FROM t_kelas ORDER BY nama ASC"
);

if (!$daftarKelas) {
    die("Gagal mengambil data kelas.");
}

// Susun filter SQL
$where = [];
$parameter = [];
$tipe = '';

if ($tanggalMulai !== '') {
    $where[] = "tanggal >= ?";
    $parameter[] = $tanggalMulai;
    $tipe .= 's';
}

if ($tanggalSelesai !== '') {
    $where[] = "tanggal <= ?";
    $parameter[] = $tanggalSelesai;
    $tipe .= 's';
}

if ($kelasId > 0) {
    $where[] = "kelas_id = ?";
    $parameter[] = $kelasId;
    $tipe .= 'i';
}

$sqlWhere = $where ? " WHERE " . implode(" AND ", $where) : "";

// Filter tanggal tidak valid: jangan tampilkan data
if ($pesanFilter !== '') {
    $sqlWhere = " WHERE 1 = 0";
    $parameter = [];
    $tipe = '';
}

// Hitung total seluruh data hasil filter
$sqlRingkasan = "
    SELECT COUNT(*) AS total,
           COALESCE(SUM(poin), 0) AS total_poin
    FROM t_pelanggaran_siswa
    $sqlWhere
";

$stmtRingkasan = mysqli_prepare($koneksi, $sqlRingkasan);

if ($tipe !== '') {
    mysqli_stmt_bind_param($stmtRingkasan, $tipe, ...$parameter);
}

mysqli_stmt_execute($stmtRingkasan);
$ringkasan = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmtRingkasan)
);
mysqli_stmt_close($stmtRingkasan);

$totalData = (int)$ringkasan['total'];
$totalPoin = (int)$ringkasan['total_poin'];

// Pagination: 10 data per halaman
$batas = 10;
$totalHalaman = max(1, (int)ceil($totalData / $batas));
$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$halaman = min($halaman, $totalHalaman);
$offset = ($halaman - 1) * $batas;

// Ambil data sesuai filter
$sqlData = "
    SELECT id, nama_siswa, nama_kelas, nama_pelanggaran,
           nama_guru, tanggal, keterangan, poin, tindakan, status
    FROM t_pelanggaran_siswa
    $sqlWhere
    ORDER BY tanggal DESC, id DESC
";

$isExcel = isset($_GET['export']) && $_GET['export'] === 'excel';
$isCetak = isset($_GET['cetak']) && $_GET['cetak'] === '1';

// Excel dan cetak mengambil semua data hasil filter.
// Tampilan biasa hanya mengambil 10 data per halaman.
if (!$isExcel && !$isCetak) {
    $sqlData .= " LIMIT ? OFFSET ?";
    $tipeData = $tipe . 'ii';
    $parameterData = $parameter;
    $parameterData[] = $batas;
    $parameterData[] = $offset;
} else {
    $tipeData = $tipe;
    $parameterData = $parameter;
}

$stmtData = mysqli_prepare($koneksi, $sqlData);

if ($tipeData !== '') {
    mysqli_stmt_bind_param($stmtData, $tipeData, ...$parameterData);
}

mysqli_stmt_execute($stmtData);
$result = mysqli_stmt_get_result($stmtData);

// Nama kelas untuk judul filter
$namaKelasFilter = "Semua Kelas";

if ($kelasId > 0) {
    mysqli_data_seek($daftarKelas, 0);

    while ($kelas = mysqli_fetch_assoc($daftarKelas)) {
        if ((int)$kelas['id'] === $kelasId) {
            $namaKelasFilter = $kelas['nama'];
            break;
        }
    }
}

// Export Excel dalam format yang dapat dibuka Microsoft Excel
if ($isExcel) {
    header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
    header("Content-Disposition: attachment; filename=laporan_pelanggaran.xls");
    header("Cache-Control: no-store, no-cache, must-revalidate");

    echo "\xEF\xBB\xBF";
    ?>
    <table border="1">
        <tr>
            <th colspan="10">LAPORAN PELANGGARAN SISWA</th>
        </tr>
        <tr>
            <td colspan="10">
                Periode: <?= e($tanggalMulai ?: 'Semua tanggal') ?>
                s.d. <?= e($tanggalSelesai ?: 'Semua tanggal') ?>
                | Kelas: <?= e($namaKelasFilter) ?>
            </td>
        </tr>
        <tr>
            <th>No</th>
            <th>Nama Siswa</th>
            <th>Kelas</th>
            <th>Jenis Pelanggaran</th>
            <th>Guru</th>
            <th>Tanggal</th>
            <th>Keterangan</th>
            <th>Poin</th>
            <th>Tindakan</th>
            <th>Status</th>
        </tr>
        <?php $no = 1; while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= e($row['nama_siswa']) ?></td>
            <td><?= e($row['nama_kelas']) ?></td>
            <td><?= e($row['nama_pelanggaran']) ?></td>
            <td><?= e($row['nama_guru']) ?></td>
            <td><?= e($row['tanggal']) ?></td>
            <td><?= e($row['keterangan']) ?></td>
            <td><?= (int)$row['poin'] ?></td>
            <td><?= e($row['tindakan']) ?></td>
            <td><?= e($row['status']) ?></td>
        </tr>
        <?php } ?>
        <tr>
            <th colspan="7">Total</th>
            <th><?= $totalPoin ?></th>
            <th colspan="2">Jumlah catatan: <?= $totalData ?></th>
        </tr>
    </table>
    <?php
    mysqli_stmt_close($stmtData);
    exit;
}

$jumlahDitampilkan = $isCetak ? 0 : min($offset + $batas, $totalData);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            margin: 0;
            background: #f4f6f9;
        }

        .konten-laporan {
            margin-left: 220px;
            padding: 28px;
            min-height: 100vh;
            box-sizing: border-box;
        }

        .kartu-ringkasan {
            border: 0;
            border-radius: 12px;
        }

        @media (max-width: 767.98px) {
            .konten-laporan {
                margin-left: 180px;
                padding: 15px;
            }
        }

        @media print {
            @page {
                size: landscape;
                margin: 10mm;
            }

            body {
                background: white !important;
                font-size: 10px;
            }

            .sidebar-ukk,
            .filter-laporan,
            .tombol-laporan,
            .pagination,
            .ringkasan-layar {
                display: none !important;
            }

            .konten-laporan {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }

            .card {
                box-shadow: none !important;
                border: 0 !important;
            }

            .table-responsive {
                overflow: visible !important;
            }

            table {
                width: 100% !important;
                border-collapse: collapse !important;
            }

            th, td {
                border: 1px solid #555 !important;
                padding: 5px !important;
                color: #000 !important;
                overflow-wrap: anywhere;
            }

            thead {
                display: table-header-group;
            }

            tr {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>

<?php include "includes/sidebar.php"; ?>

<main class="konten-laporan">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h2 class="mb-1">Laporan Pelanggaran</h2>
            <p class="text-muted mb-0">Rekap data pelanggaran siswa</p>
        </div>

        <div class="d-flex flex-wrap gap-2 tombol-laporan">
            <button type="button" onclick="window.print()" class="btn btn-outline-dark">
                Cetak / PDF
            </button>

            <a href="?<?= e(http_build_query(array_merge($_GET, [
                'export' => 'excel',
                'halaman' => null,
                'cetak' => null
            ]))) ?>" class="btn btn-success">
                Export Excel
            </a>
        </div>
    </div>

    <?php if ($pesanFilter !== '') { ?>
        <div class="alert alert-danger"><?= e($pesanFilter) ?></div>
    <?php } ?>

    <div class="card shadow-sm mb-4 filter-laporan">
        <div class="card-body">
            <form method="GET" action="laporan.php" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" class="form-control"
                           value="<?= e($tanggalMulai) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" class="form-control"
                           value="<?= e($tanggalSelesai) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Kelas</label>
                    <select name="kelas_id" class="form-select">
                        <option value="0">Semua Kelas</option>
                        <?php
                        mysqli_data_seek($daftarKelas, 0);
                        while ($kelas = mysqli_fetch_assoc($daftarKelas)) {
                        ?>
                            <option value="<?= (int)$kelas['id'] ?>"
                                <?= $kelasId === (int)$kelas['id'] ? 'selected' : '' ?>>
                                <?= e($kelas['nama']) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Terapkan Filter</button>
                    <a href="laporan.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4 ringkasan-layar">
        <div class="col-md-6">
            <div class="card kartu-ringkasan shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Total Catatan Pelanggaran</div>
                    <h3 class="mb-0"><?= $totalData ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card kartu-ringkasan shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Total Poin Pelanggaran</div>
                    <h3 class="mb-0"><?= $totalPoin ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="mb-3">
                <h5 class="mb-1">Data Laporan</h5>
                <small class="text-muted">
                    Periode: <?= e($tanggalMulai ?: 'Semua tanggal') ?>
                    s.d. <?= e($tanggalSelesai ?: 'Semua tanggal') ?>
                    | Kelas: <?= e($namaKelasFilter) ?>
                </small>
                <br>
                <small class="text-muted">
                    <?php if ($totalData > 0 && !$isCetak) { ?>
                        Menampilkan <?= $offset + 1 ?>–<?= $jumlahDitampilkan ?>
                        dari <?= $totalData ?> data
                    <?php } else { ?>
                        Total <?= $totalData ?> data laporan
                    <?php } ?>
                </small>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Jenis Pelanggaran</th>
                            <th>Guru</th>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th>Poin</th>
                            <th>Tindakan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $nomor = $isCetak ? 1 : $offset + 1;
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                        <tr>
                            <td><?= $nomor++ ?></td>
                            <td><?= e($row['nama_siswa']) ?></td>
                            <td><?= e($row['nama_kelas']) ?></td>
                            <td><?= e($row['nama_pelanggaran']) ?></td>
                            <td><?= e($row['nama_guru']) ?></td>
                            <td><?= e($row['tanggal']) ?></td>
                            <td><?= e($row['keterangan']) ?></td>
                            <td><?= (int)$row['poin'] ?></td>
                            <td><?= e($row['tindakan']) ?></td>
                            <td><?= e($row['status']) ?></td>
                        </tr>
                    <?php
                        }
                    } else {
                    ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                Belum ada data laporan yang sesuai filter.
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalHalaman > 1 && !$isCetak) { ?>
                <nav class="mt-3" aria-label="Pagination laporan">
                    <ul class="pagination justify-content-center flex-wrap">
                        <?php
                        $parameterHalaman = $_GET;
                        unset($parameterHalaman['export'], $parameterHalaman['cetak']);

                        for ($i = 1; $i <= $totalHalaman; $i++) {
                            $parameterHalaman['halaman'] = $i;
                            $url = '?' . http_build_query($parameterHalaman);
                        ?>
                            <li class="page-item <?= $i === $halaman ? 'active' : '' ?>">
                                <a class="page-link" href="<?= e($url) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </nav>
            <?php } ?>
        </div>
    </div>
</main>

<?php if ($isCetak) { ?>
<script>
    window.addEventListener('load', function () {
        window.print();
    });
</script>
<?php } ?>

</body>
</html>
<?php
mysqli_stmt_close($stmtData);
?>
