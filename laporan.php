
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

$role = $_SESSION['role'] ?? '';

if (!in_array($role, ["admin", "guru"], true)) {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "laporan";

<<<<<<< HEAD
$idUser = (int) $_SESSION['id'];

$isWaliKelas = false;
$kelasWali = 0;
$tahunWali = 0;

if ($_SESSION['role'] === "guru") {
    $queryWali = mysqli_query($koneksi, "
        SELECT w.kelas_id, w.tahun_ajaran_id
        FROM t_guru g
        JOIN t_wali_kelas w ON g.id = w.guru_id
        JOIN t_kelas k ON w.kelas_id = k.id
        JOIN t_tahun_ajaran ta ON w.tahun_ajaran_id = ta.id
        WHERE g.user_id = $idUser
        AND w.status_aktif = 1
        AND k.status_aktif = 1
        AND ta.status_aktif = 1
        LIMIT 1
    ");

    if ($queryWali && mysqli_num_rows($queryWali) > 0) {
        $dataWali = mysqli_fetch_assoc($queryWali);
        $isWaliKelas = true;
        $kelasWali = (int) $dataWali['kelas_id'];
        $tahunWali = (int) $dataWali['tahun_ajaran_id'];
    }
=======
$cari = trim($_GET['cari'] ?? '');
$tanggalAwal = $_GET['tanggal_awal'] ?? '';
$tanggalAkhir = $_GET['tanggal_akhir'] ?? '';
$kelasFilter = $_GET['kelas'] ?? '';
$statusFilter = $_GET['status'] ?? '';

$queryAktif = mysqli_query($koneksi, "
    SELECT id FROM t_tahun_ajaran
    WHERE status_aktif = 1 LIMIT 1
");
$tahunAktif = mysqli_fetch_assoc($queryAktif);
$tahunDefault = $tahunAktif ? (string)$tahunAktif['id'] : "semua";

$tahunFilter = $_GET['tahun'] ?? $tahunDefault;

if ($tahunFilter !== "semua" && !ctype_digit((string)$tahunFilter)) {
    $tahunFilter = $tahunDefault;
}

$queryTahun = mysqli_query($koneksi, "
    SELECT id, nama FROM t_tahun_ajaran
    ORDER BY tanggal_mulai DESC
");

$queryKelas = mysqli_query($koneksi, "
    SELECT id, nama FROM t_kelas
    ORDER BY nama ASC
");

$where = "WHERE 1=1";

if ($tahunFilter !== "semua") {
    $tahunId = (int)$tahunFilter;
    $where .= " AND ps.tahun_ajaran_id = $tahunId";
}

if ($kelasFilter !== "" && ctype_digit((string)$kelasFilter)) {
    $kelasId = (int)$kelasFilter;
    $where .= " AND ps.kelas_id = $kelasId";
} else {
    $kelasFilter = "";
}

if ($tanggalAwal !== "" && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalAwal)) {
    $awal = mysqli_real_escape_string($koneksi, $tanggalAwal);
    $where .= " AND ps.tanggal >= '$awal'";
} else {
    $tanggalAwal = "";
}

if ($tanggalAkhir !== "" && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalAkhir)) {
    $akhir = mysqli_real_escape_string($koneksi, $tanggalAkhir);
    $where .= " AND ps.tanggal <= '$akhir'";
} else {
    $tanggalAkhir = "";
}

$statusValid = ["Belum ditangani", "Sedang diproses", "Selesai"];

if (in_array($statusFilter, $statusValid, true)) {
    $statusAman = mysqli_real_escape_string($koneksi, $statusFilter);
    $where .= " AND ps.status = '$statusAman'";
} else {
    $statusFilter = "";
}

if ($cari !== "") {
    $kata = mysqli_real_escape_string($koneksi, $cari);

    $where .= " AND (
        ps.nama_siswa LIKE '%$kata%'
        OR ps.nama_kelas LIKE '%$kata%'
        OR ps.nama_pelanggaran LIKE '%$kata%'
        OR ps.nama_guru LIKE '%$kata%'
        OR ps.siswa_id IN (
            SELECT id FROM t_siswa
            WHERE nis LIKE '%$kata%'
        )
    )";
}

// Ringkasan laporan berdasarkan seluruh hasil filter
$queryRingkasan = mysqli_query($koneksi, "
    SELECT
        COUNT(*) AS jumlah,
        COALESCE(SUM(ps.poin), 0) AS total_poin
    FROM t_pelanggaran_siswa ps
    $where
");

$ringkasan = mysqli_fetch_assoc($queryRingkasan);
$jumlahPelanggaran = (int)$ringkasan['jumlah'];
$totalPoin = (int)$ringkasan['total_poin'];

// Export Excel (CSV)
if (isset($_GET['export']) && $_GET['export'] === "excel") {

    $queryExport = mysqli_query($koneksi, "
        SELECT ps.*, s.nis
        FROM t_pelanggaran_siswa ps
        LEFT JOIN t_siswa s ON ps.siswa_id = s.id
        $where
        ORDER BY ps.tanggal DESC, ps.id DESC
    ");

    if (!$queryExport) {
        die("Gagal mengambil data export: " . mysqli_error($koneksi));
    }

    header("Content-Type: text/csv; charset=UTF-8");
    header("Content-Disposition: attachment; filename=laporan_pelanggaran.csv");

    echo "\xEF\xBB\xBF";

    $output = fopen("php://output", "w");

    fputcsv($output, [
        "No", "Tanggal", "Tahun Ajaran", "NIS",
        "Nama Siswa", "Kelas", "Pelanggaran",
        "Poin", "Guru", "Keterangan", "Tindakan", "Status"
    ], ";");

    $no = 1;

    while ($data = mysqli_fetch_assoc($queryExport)) {
        $tahunNama = "";

        $idTahun = (int)$data['tahun_ajaran_id'];
        $qTahunNama = mysqli_query($koneksi, "
            SELECT nama FROM t_tahun_ajaran
            WHERE id = $idTahun LIMIT 1
        ");

        if ($qTahunNama && mysqli_num_rows($qTahunNama) > 0) {
            $tahunNama = mysqli_fetch_assoc($qTahunNama)['nama'];
        }

        fputcsv($output, [
            $no++,
            $data['tanggal'],
            $tahunNama,
            $data['nis'],
            $data['nama_siswa'],
            $data['nama_kelas'],
            $data['nama_pelanggaran'],
            $data['poin'],
            $data['nama_guru'],
            $data['keterangan'],
            $data['tindakan'],
            $data['status']
        ], ";");
    }

    fclose($output);
    exit;
}

// Pagination
$batas = 10;
$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$totalHalaman = max(1, (int)ceil($jumlahPelanggaran / $batas));

if ($halaman > $totalHalaman) {
    $halaman = $totalHalaman;
}

$offset = ($halaman - 1) * $batas;

$query = mysqli_query($koneksi, "
    SELECT ps.*, s.nis
    FROM t_pelanggaran_siswa ps
    LEFT JOIN t_siswa s ON ps.siswa_id = s.id
    $where
    ORDER BY ps.tanggal DESC, ps.id DESC
    LIMIT $batas OFFSET $offset
");

if (!$query) {
    die("Gagal mengambil laporan: " . mysqli_error($koneksi));
>>>>>>> 553d5e7384eebb77e97b9d0d9edfc333d5e83b0f
}

function e($nilai) {
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

// Parameter filter untuk tombol export dan pagination
$parameter = [
    "cari" => $cari,
    "tahun" => $tahunFilter,
    "kelas" => $kelasFilter,
    "tanggal_awal" => $tanggalAwal,
    "tanggal_akhir" => $tanggalAkhir,
    "status" => $statusFilter
];

<<<<<<< HEAD
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

// $daftarKelas = mysqli_query(
//     $koneksi,
//     "SELECT id, nama FROM t_kelas ORDER BY nama ASC"
// );

if ($isWaliKelas) {
    $daftarKelas = mysqli_query($koneksi, "
        SELECT id, nama
        FROM t_kelas
        WHERE id = $kelasWali
    ");

    // Wali Kelas tidak boleh memilih kelas lain
    $kelasId = $kelasWali;
} else {
    $daftarKelas = mysqli_query(
        $koneksi,
        "SELECT id, nama FROM t_kelas ORDER BY nama ASC"
    );
}

if (!$daftarKelas) {
    die("Gagal mengambil data kelas.");
}

// // Susun filter SQL
// $where = [];
// $parameter = [];
// $tipe = '';

// Susun filter SQL
$where = [];
$parameter = [];
$tipe = '';

// Pembatasan khusus Wali Kelas
if ($isWaliKelas) {
    $where[] = "kelas_id = ?";
    $parameter[] = $kelasWali;
    $tipe .= 'i';

    $where[] = "tahun_ajaran_id = ?";
    $parameter[] = $tahunWali;
    $tipe .= 'i';
}

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

if (!$isWaliKelas && $kelasId > 0) {
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
=======
$linkExport = "?" . http_build_query(array_merge($parameter, ["export" => "excel"]));
>>>>>>> 553d5e7384eebb77e97b9d0d9edfc333d5e83b0f
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pelanggaran</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        @media print {
            .sidebar-ukk,
            .filter-area,
            .tombol-aksi,
            .pagination,
            .footer-info {
                display: none !important;
            }

            body {
                background: white !important;
            }

            .container-fluid {
                margin: 0 !important;
                width: 100% !important;
                padding: 0 !important;
            }

            .card {
                box-shadow: none !important;
                border: none !important;
            }

            table {
                font-size: 10px;
            }

            @page {
                size: landscape;
                margin: 1cm;
            }
        }
    </style>
</head>

<body class="bg-light">

<?php include "includes/sidebar.php"; ?>

<div class="container-fluid"
     style="margin-left:220px; width:calc(100% - 220px); padding:30px;">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1">Laporan Pelanggaran</h3>
            <p class="text-muted mb-0">Rekap data pelanggaran siswa.</p>
        </div>

        <div class="d-flex gap-2 tombol-aksi">
            <button onclick="window.print()" class="btn btn-primary">
                Cetak Laporan
            </button>

            <a href="<?= e($linkExport) ?>" class="btn btn-success">
                Export Excel
            </a>
        </div>
    </div>

    <!-- Ringkasan -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted mb-1">Jumlah Pelanggaran</p>
                    <h3 class="fw-bold mb-0">
                        <?= number_format($jumlahPelanggaran) ?>
                    </h3>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Poin Pelanggaran</p>
                    <h3 class="fw-bold mb-0">
                        <?= number_format($totalPoin) ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="card border-0 shadow-sm mb-4 filter-area">
        <div class="card-body">
            <form method="GET" class="row g-3">

                <div class="col-md-4">
                    <label class="form-label">Pencarian</label>
                    <input type="text" name="cari" class="form-control"
                           placeholder="NIS, nama siswa, pelanggaran, guru..."
                           value="<?= e($cari) ?>">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Tahun Ajaran</label>
                    <select name="tahun" class="form-select">
                        <option value="semua" <?= $tahunFilter === "semua" ? "selected" : "" ?>>
                            Semua
                        </option>
                        <?php while ($ta = mysqli_fetch_assoc($queryTahun)): ?>
                            <option value="<?= $ta['id'] ?>"
                                <?= $tahunFilter == $ta['id'] ? "selected" : "" ?>>
                                <?= e($ta['nama']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Kelas</label>
                    <select name="kelas" class="form-select">
                        <option value="">Semua Kelas</option>
                        <?php while ($k = mysqli_fetch_assoc($queryKelas)): ?>
                            <option value="<?= $k['id'] ?>"
                                <?= $kelasFilter == $k['id'] ? "selected" : "" ?>>
                                <?= e($k['nama']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Tanggal Awal</label>
                    <input type="date" name="tanggal_awal"
                           class="form-control" value="<?= e($tanggalAwal) ?>">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Tanggal Akhir</label>
                    <input type="date" name="tanggal_akhir"
                           class="form-control" value="<?= e($tanggalAkhir) ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Status Penanganan</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <?php foreach ($statusValid as $st): ?>
                            <option value="<?= e($st) ?>"
                                <?= $statusFilter === $st ? "selected" : "" ?>>
                                <?= e($st) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-9 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        Terapkan Filter
                    </button>
                    <a href="laporan.php" class="btn btn-secondary">
                        Reset
                    </a>
                </div>

            </form>
        </div>
    </div>

    <!-- Tabel laporan -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <h5 class="fw-bold mb-3">Data Laporan</h5>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Pelanggaran</th>
                            <th>Poin</th>
                            <th>Guru</th>
                            <th>Tindakan</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (mysqli_num_rows($query) > 0): ?>
                        <?php $no = $offset + 1; ?>
                        <?php while ($data = mysqli_fetch_assoc($query)): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= e(date('d-m-Y', strtotime($data['tanggal']))) ?></td>
                                <td><?= e($data['nis']) ?></td>
                                <td><?= e($data['nama_siswa']) ?></td>
                                <td><?= e($data['nama_kelas']) ?></td>
                                <td><?= e($data['nama_pelanggaran']) ?></td>
                                <td class="text-center"><?= e($data['poin']) ?></td>
                                <td><?= e($data['nama_guru']) ?></td>
                                <td><?= e($data['tindakan']) ?></td>
                                <td><?= e($data['status']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                Tidak ada data laporan.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 footer-info">
                <small class="text-muted">
                    Menampilkan <?= mysqli_num_rows($query) ?>
                    dari <?= $jumlahPelanggaran ?> data
                </small>

                <nav class="pagination">
                    <ul class="pagination pagination-sm mb-0">
                        <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>
                            <li class="page-item <?= $halaman == $i ? 'active' : '' ?>">
                                <a class="page-link"
                                   href="?<?= e(http_build_query(array_merge($parameter, ["halaman" => $i]))) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>

        </div>
    </div>

</div>

</body>
</html>
