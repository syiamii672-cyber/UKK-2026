
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

$role = $_SESSION['role'] ?? '';

if (!in_array($role, ["admin", "guru"], true)) {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "laporan";
$idUser = (int)($_SESSION['id'] ?? 0);

// Fungsi keamanan output
function e($nilai) {
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}


 // ==========================================
 // CEK WALI KELAS
 // ==========================================

$isWaliKelas = false;
$kelasWali = 0;
$tahunWali = 0;

if ($role === "guru") {
    $queryWali = mysqli_query($koneksi, "
        SELECT w.kelas_id, w.tahun_ajaran_id
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

    if ($queryWali && mysqli_num_rows($queryWali) > 0) {
        $dataWali = mysqli_fetch_assoc($queryWali);

        $isWaliKelas = true;
        $kelasWali = (int) $dataWali['kelas_id'];
        $tahunWali = (int) $dataWali['tahun_ajaran_id'];
    } else {
        // Guru biasa tidak memiliki akses laporan
        header("Location: dashboard.php");
        exit;
    }
}

// ==========================================
// FILTER LAPORAN
// ==========================================

$cari = trim($_GET['cari'] ?? '');
$tanggalAwal = $_GET['tanggal_awal'] ?? '';
$tanggalAkhir = $_GET['tanggal_akhir'] ?? '';
$kelasFilter = $_GET['kelas'] ?? '';

$statusValid = [
    "Belum ditangani",
    "Sedang diproses",
    "Selesai"
];

$statusFilter = $_GET['status'] ?? '';

// Tahun ajaran aktif sebagai pilihan default admin
$queryAktif = mysqli_query($koneksi, "
    SELECT id
    FROM t_tahun_ajaran
    WHERE status_aktif = 1
    LIMIT 1
");

$tahunAktif = $queryAktif
    ? mysqli_fetch_assoc($queryAktif)
    : null;

$tahunDefault = $tahunAktif
    ? (string)$tahunAktif['id']
    : "semua";

$tahunFilter = $_GET['tahun'] ?? $tahunDefault;

// Wali kelas dikunci pada kelas dan tahun ajarannya sendiri
if ($isWaliKelas) {
    $kelasFilter = (string)$kelasWali;
    $tahunFilter = (string)$tahunWali;
} else {
    if (
        $tahunFilter !== "semua" &&
        !ctype_digit((string)$tahunFilter)
    ) {
        $tahunFilter = $tahunDefault;
    }

    if (
        $kelasFilter !== "" &&
        !ctype_digit((string)$kelasFilter)
    ) {
        $kelasFilter = "";
    }
}

// Validasi tanggal
$pesanFilter = '';

foreach (['tanggalAwal', 'tanggalAkhir'] as $namaTanggal) {
    if ($$namaTanggal !== '') {
        $tanggal = $$namaTanggal;

        $valid = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)
            && checkdate(
                (int)substr($tanggal, 5, 2),
                (int)substr($tanggal, 8, 2),
                (int)substr($tanggal, 0, 4)
            );

        if (!$valid) {
            $$namaTanggal = '';
            $pesanFilter = "Format tanggal tidak valid.";
        }
    }
}

if (
    $tanggalAwal !== '' &&
    $tanggalAkhir !== '' &&
    $tanggalAwal > $tanggalAkhir
) {
    $pesanFilter = "Tanggal awal tidak boleh melebihi tanggal akhir.";
}

// Validasi status
if (!in_array($statusFilter, $statusValid, true)) {
    $statusFilter = '';
}

// ==========================================
// DATA PILIHAN FILTER
// ==========================================

if ($isWaliKelas) {
    $queryTahun = mysqli_query($koneksi, "
        SELECT id, nama
        FROM t_tahun_ajaran
        WHERE id = $tahunWali
    ");

    $queryKelas = mysqli_query($koneksi, "
        SELECT id, nama
        FROM t_kelas
        WHERE id = $kelasWali
    ");
} else {
    $queryTahun = mysqli_query($koneksi, "
        SELECT id, nama
        FROM t_tahun_ajaran
        ORDER BY tanggal_mulai DESC
    ");

    $queryKelas = mysqli_query($koneksi, "
        SELECT id, nama
        FROM t_kelas
        ORDER BY nama ASC
    ");
}

// ==========================================
// SUSUN FILTER SQL
// ==========================================

$where = "WHERE 1=1";

if ($isWaliKelas) {
    // Pembatasan wajib untuk Wali Kelas
    $where .= " AND ps.kelas_id = $kelasWali";
    $where .= " AND ps.tahun_ajaran_id = $tahunWali";
} else {
    if ($tahunFilter !== "semua") {
        $tahunId = (int)$tahunFilter;
        $where .= " AND ps.tahun_ajaran_id = $tahunId";
    }

    if ($kelasFilter !== '') {
        $kelasId = (int)$kelasFilter;
        $where .= " AND ps.kelas_id = $kelasId";
    }
}

if ($tanggalAwal !== '') {
    $awal = mysqli_real_escape_string($koneksi, $tanggalAwal);
    $where .= " AND ps.tanggal >= '$awal'";
}

if ($tanggalAkhir !== '') {
    $akhir = mysqli_real_escape_string($koneksi, $tanggalAkhir);
    $where .= " AND ps.tanggal <= '$akhir'";
}

if ($statusFilter !== '') {
    $statusAman = mysqli_real_escape_string($koneksi, $statusFilter);
    $where .= " AND ps.status = '$statusAman'";
}

if ($cari !== '') {
    $kata = mysqli_real_escape_string($koneksi, $cari);

    $where .= " AND (
        ps.nama_siswa LIKE '%$kata%'
        OR ps.nama_kelas LIKE '%$kata%'
        OR ps.nama_pelanggaran LIKE '%$kata%'
        OR ps.nama_guru LIKE '%$kata%'
        OR ps.siswa_id IN (
            SELECT id
            FROM t_siswa
            WHERE nis LIKE '%$kata%'
        )
    )";
}

// Jika tanggal tidak valid, jangan tampilkan data
if ($pesanFilter !== '') {
    $where .= " AND 1=0";
}

// ==========================================
// RINGKASAN LAPORAN
// ==========================================

$queryRingkasan = mysqli_query($koneksi, "
    SELECT
        COUNT(*) AS jumlah,
        COALESCE(SUM(ps.poin), 0) AS total_poin
    FROM t_pelanggaran_siswa ps
    $where
");

if (!$queryRingkasan) {
    die("Gagal mengambil ringkasan: " . mysqli_error($koneksi));
}

$ringkasan = mysqli_fetch_assoc($queryRingkasan);

$jumlahPelanggaran = (int)$ringkasan['jumlah'];
$totalPoin = (int)$ringkasan['total_poin'];

// ==========================================
// PAGINATION DAN DATA LAPORAN
// ==========================================

$batas = 10;
$halaman = max(1, (int)($_GET['halaman'] ?? 1));

$totalHalaman = max(
    1,
    (int)ceil($jumlahPelanggaran / $batas)
);

$halaman = min($halaman, $totalHalaman);
$offset = ($halaman - 1) * $batas;

$isExcel = isset($_GET['export']) && $_GET['export'] === 'excel';
$isCetak = isset($_GET['cetak']) && $_GET['cetak'] === '1';

// Cetak dan Excel mengambil semua data hasil filter.
// Tampilan biasa hanya mengambil 10 data per halaman.
$sqlData = "
    SELECT ps.*, s.nis
    FROM t_pelanggaran_siswa ps
    LEFT JOIN t_siswa s ON ps.siswa_id = s.id
    $where
    ORDER BY ps.tanggal DESC, ps.id DESC
";

if (!$isExcel && !$isCetak) {
    $sqlData .= " LIMIT $batas OFFSET $offset";
}

$query = mysqli_query($koneksi, $sqlData);

if (!$query) {
    die("Gagal mengambil laporan: " . mysqli_error($koneksi));
}

$jumlahDitampilkan = mysqli_num_rows($query);

// ==========================================
// EXPORT EXCEL
// ==========================================

if ($isExcel) {
    header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
    header("Content-Disposition: attachment; filename=laporan_pelanggaran.xls");
    header("Cache-Control: no-store, no-cache, must-revalidate");

    echo "\xEF\xBB\xBF";
    ?>
    <table border="1">
        <tr>
            <th colspan="11">LAPORAN PELANGGARAN SISWA</th>
        </tr>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>NIS</th>
            <th>Nama Siswa</th>
            <th>Kelas</th>
            <th>Pelanggaran</th>
            <th>Poin</th>
            <th>Guru</th>
            <th>Keterangan</th>
            <th>Tindakan</th>
            <th>Status</th>
        </tr>

        <?php $no = 1; ?>
        <?php while ($data = mysqli_fetch_assoc($query)): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= e($data['tanggal']) ?></td>
                <td><?= e($data['nis']) ?></td>
                <td><?= e($data['nama_siswa']) ?></td>
                <td><?= e($data['nama_kelas']) ?></td>
                <td><?= e($data['nama_pelanggaran']) ?></td>
                <td><?= (int)$data['poin'] ?></td>
                <td><?= e($data['nama_guru']) ?></td>
                <td><?= e($data['keterangan']) ?></td>
                <td><?= e($data['tindakan']) ?></td>
                <td><?= e($data['status']) ?></td>
            </tr>
        <?php endwhile; ?>

        <tr>
            <th colspan="6">Total</th>
            <th><?= $totalPoin ?></th>
            <th colspan="4">
                Jumlah pelanggaran: <?= $jumlahPelanggaran ?>
            </th>
        </tr>
    </table>
    <?php
    exit;
}

// ==========================================
// LINK FILTER, EXPORT, DAN CETAK
// ==========================================

$parameter = [
    "cari" => $cari,
    "tahun" => $tahunFilter,
    "kelas" => $kelasFilter,
    "tanggal_awal" => $tanggalAwal,
    "tanggal_akhir" => $tanggalAkhir,
    "status" => $statusFilter
];

$linkExport = "?" . http_build_query(
    array_merge($parameter, ["export" => "excel"])
);

$linkCetak = "?" . http_build_query(
    array_merge($parameter, ["cetak" => "1"])
);

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
            <p class="text-muted mb-0">
                Rekap data pelanggaran siswa.
                <?php if ($isWaliKelas): ?>
                    <br><small>Data kelas yang menjadi tanggung jawab Anda.</small>
                <?php endif; ?>
            </p>
        </div>

        <div class="d-flex gap-2 tombol-aksi">
            <a href="<?= e($linkCetak) ?>" target="_blank" class="btn btn-primary">
                Cetak Laporan
            </a>

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

            <?php if ($pesanFilter !== ''): ?>
                <div class="alert alert-warning">
                    <?= e($pesanFilter) ?>
                </div>
            <?php endif; ?>

            <form method="GET" class="row g-3">

                <div class="col-md-4">
                    <label class="form-label">Pencarian</label>
                    <input type="text" name="cari" class="form-control"
                           placeholder="NIS, nama siswa, pelanggaran, guru..."
                           value="<?= e($cari) ?>">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Tahun Ajaran</label>

                    <?php if ($isWaliKelas): ?>
                        <?php $taWali = mysqli_fetch_assoc($queryTahun); ?>
                        <input type="text" class="form-control"
                               value="<?= e($taWali['nama'] ?? '-') ?>" disabled>
                        <input type="hidden" name="tahun" value="<?= $tahunWali ?>">
                    <?php else: ?>
                        <select name="tahun" class="form-select">
                            <option value="semua"
                                <?= $tahunFilter === "semua" ? "selected" : "" ?>>
                                Semua
                            </option>

                            <?php while ($ta = mysqli_fetch_assoc($queryTahun)): ?>
                                <option value="<?= $ta['id'] ?>"
                                    <?= $tahunFilter == $ta['id'] ? "selected" : "" ?>>
                                    <?= e($ta['nama']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    <?php endif; ?>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Kelas</label>

                    <?php if ($isWaliKelas): ?>
                        <?php $kelasWaliData = mysqli_fetch_assoc($queryKelas); ?>
                        <input type="text" class="form-control"
                               value="<?= e($kelasWaliData['nama'] ?? '-') ?>" disabled>
                        <input type="hidden" name="kelas" value="<?= $kelasWali ?>">
                    <?php else: ?>
                        <select name="kelas" class="form-select">
                            <option value="">Semua Kelas</option>

                            <?php while ($k = mysqli_fetch_assoc($queryKelas)): ?>
                                <option value="<?= $k['id'] ?>"
                                    <?= $kelasFilter == $k['id'] ? "selected" : "" ?>>
                                    <?= e($k['nama']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    <?php endif; ?>
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
                    <?php if ($jumlahDitampilkan > 0): ?>
                        <?php $no = $isCetak ? 1 : $offset + 1; ?>

                        <?php while ($data = mysqli_fetch_assoc($query)): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <?= e(date('d-m-Y', strtotime($data['tanggal']))) ?>
                                </td>
                                <td><?= e($data['nis']) ?></td>
                                <td><?= e($data['nama_siswa']) ?></td>
                                <td><?= e($data['nama_kelas']) ?></td>
                                <td><?= e($data['nama_pelanggaran']) ?></td>
                                <td class="text-center">
                                    <?= (int)$data['poin'] ?>
                                </td>
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
                    Menampilkan <?= $jumlahDitampilkan ?>
                    dari <?= number_format($jumlahPelanggaran) ?> data
                </small>

                <?php if (!$isCetak && $totalHalaman > 1): ?>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">

                            <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>
                                <li class="page-item <?= $halaman == $i ? 'active' : '' ?>">
                                    <a class="page-link"
                                       href="?<?= e(http_build_query(array_merge(
                                           $parameter,
                                           ["halaman" => $i]
                                       ))) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                        </ul>
                    </nav>
                <?php endif; ?>
            </div>

        </div>
    </div>

</div>

<?php if ($isCetak): ?>
<script>
    window.onload = function () {
        window.print();
    };
</script>
<?php endif; ?>

</body>
</html>
