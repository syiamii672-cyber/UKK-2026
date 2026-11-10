
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

function e($nilai) {
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

// CEK WALI KELAS
$isWaliKelas = false;
$kelasWali = 0;
$tahunWali = 0;
$namaKelasWali = '';

if ($role === 'guru') {
    $qWali = mysqli_query($koneksi, "
        SELECT w.kelas_id, w.tahun_ajaran_id, k.nama AS nama_kelas
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

// FILTER
$cari = trim($_GET['cari'] ?? '');
$tanggalMulai = trim($_GET['tanggal_mulai'] ?? '');
$tanggalSelesai = trim($_GET['tanggal_selesai'] ?? '');

function validTanggal($tanggal) {
    $d = DateTime::createFromFormat('!Y-m-d', $tanggal);
    return $d && $d->format('Y-m-d') === $tanggal;
}

if ($tanggalMulai !== '' && !validTanggal($tanggalMulai)) {
    $tanggalMulai = '';
}

if ($tanggalSelesai !== '' && !validTanggal($tanggalSelesai)) {
    $tanggalSelesai = '';
}

$pesanTanggal = '';

if ($tanggalMulai && $tanggalSelesai && $tanggalMulai > $tanggalSelesai) {
    $pesanTanggal = "Tanggal mulai tidak boleh melebihi tanggal selesai.";
}

$cariSQL = mysqli_real_escape_string($koneksi, $cari);

// TAHUN AJARAN
$qAktif = mysqli_query($koneksi, "
    SELECT id FROM t_tahun_ajaran
    WHERE status_aktif = 1
    LIMIT 1
");

if (!$qAktif) {
    die("Gagal mengambil tahun ajaran: " . mysqli_error($koneksi));
}

$tahunAktif = (int)(mysqli_fetch_assoc($qAktif)['id'] ?? 0);

if ($isWaliKelas) {
    $tahun = $tahunWali;
} else {
    $tahunInput = $_GET['tahun'] ?? (string)$tahunAktif;

    $tahun = $tahunInput === 'all'
        ? 'all'
        : (ctype_digit((string)$tahunInput) ? (int)$tahunInput : $tahunAktif);
}

// KONDISI DATA
$where = " WHERE ps.status = 'Selesai' ";

if ($isWaliKelas) {
    $where .= " AND ps.kelas_id = $kelasWali
                AND ps.tahun_ajaran_id = $tahunWali ";
} elseif ($tahun !== 'all') {
    $where .= " AND ps.tahun_ajaran_id = $tahun ";
}

if ($pesanTanggal) {
    $where .= " AND 1 = 0 ";
} else {
    if ($tanggalMulai) {
        $mulai = mysqli_real_escape_string($koneksi, $tanggalMulai);
        $where .= " AND ps.tanggal >= '$mulai' ";
    }

    if ($tanggalSelesai) {
        $selesai = date('Y-m-d', strtotime($tanggalSelesai . ' +1 day'));
        $selesai = mysqli_real_escape_string($koneksi, $selesai);
        $where .= " AND ps.tanggal < '$selesai' ";
    }
}

if ($cari !== '') {
    $where .= " AND (
        ps.nama_siswa LIKE '$cariSQL%'
        OR s.nis LIKE '$cariSQL%'
        OR ps.nama_kelas LIKE '$cariSQL%'
        OR ps.nama_pelanggaran LIKE '$cariSQL%'
        OR ps.nama_guru LIKE '$cariSQL%'
        OR ps.keterangan LIKE '$cariSQL%'
    ) ";
}

$sqlData = "
    SELECT ps.*, s.nis
    FROM t_pelanggaran_siswa ps
    LEFT JOIN t_siswa s ON ps.siswa_id = s.id
    $where
    ORDER BY ps.tanggal DESC, ps.id DESC
";

// PARAMETER FILTER
$parameter = [
    'cari' => $cari,
    'tanggal_mulai' => $tanggalMulai,
    'tanggal_selesai' => $tanggalSelesai
];

if (!$isWaliKelas) {
    $parameter['tahun'] = (string)$tahun;
}

$linkExcel = 'laporan.php?' . http_build_query(
    array_merge($parameter, ['export' => 'excel'])
);

$linkCetak = 'laporan.php?' . http_build_query(
    array_merge($parameter, ['cetak' => '1'])
);

// EKSPOR EXCEL (.XLS)
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
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            table {
                border-collapse: collapse;
                font-family: Arial, sans-serif;
            }

            th {
                background-color: #17365d;
                color: white;
                font-weight: bold;
                text-align: center;
            }

            th, td {
                border: 1px solid #999;
                padding: 7px;
            }

            td {
                vertical-align: middle;
            }
        </style>
    </head>
    <body>
        <h3>LAPORAN PELANGGARAN SISWA</h3>
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

// HITUNG DATA
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

// PAGINATION
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

// DAFTAR TAHUN
$queryTahun = mysqli_query($koneksi, "
    SELECT id, nama FROM t_tahun_ajaran
    ORDER BY tanggal_mulai DESC
");

if (!$queryTahun) {
    die("Gagal mengambil tahun ajaran: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= $modeCetak ? 'Cetak Laporan' : 'Laporan Pelanggaran'; ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

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

        input[type="date"] {
            cursor: pointer;
        }

        @media (max-width: 575.98px) {
            .konten-utama {
                margin-left: 180px;
                width: calc(100% - 180px);
                padding: 15px;
            }
        }

/* TAMPILAN CETAK */
@media print {
    body {
        background: white !important;
    }

    /* Sembunyikan sidebar saat dicetak */
    .sidebar-ukk {
        display: none !important;
        visibility: hidden !important;
    }

    /* Konten memenuhi seluruh halaman */
    .konten-utama {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        min-height: auto !important;
    }

    /* Sembunyikan bagian yang tidak perlu dicetak */
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

<?php include "includes/sidebar.php"; ?>

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

            <?php if ($pesanTanggal): ?>
                <div class="alert alert-warning">
                    <?= e($pesanTanggal); ?>
                </div>
            <?php endif; ?>

            <form method="GET" action="laporan.php"
                  id="filterLaporan"
                  class="row g-2 mb-3 bagian-filter">

                <div class="col-md-4">
                    <input type="text"
                           name="cari"
                           id="inputCari"
                           class="form-control"
                           placeholder="Cari NIS, siswa, kelas, pelanggaran..."
                           value="<?= e($cari); ?>"
                           autocomplete="off">
                </div>

                <div class="col-md-2">
                    <input type="date"
                           name="tanggal_mulai"
                           id="tanggalMulai"
                           class="form-control"
                           value="<?= e($tanggalMulai); ?>"
                           max="<?= e($tanggalSelesai); ?>"
                           title="Tanggal mulai">
                </div>

                <div class="col-md-2">
                    <input type="date"
                           name="tanggal_selesai"
                           id="tanggalSelesai"
                           class="form-control"
                           value="<?= e($tanggalSelesai); ?>"
                           min="<?= e($tanggalMulai); ?>"
                           title="Tanggal selesai">
                </div>

                <?php if (!$isWaliKelas): ?>
                    <div class="col-md-2">
                        <select name="tahun" class="form-select">
                            <option value="all" <?= $tahun === 'all' ? 'selected' : ''; ?>>
                                Semua Tahun
                            </option>

                            <?php while ($ta = mysqli_fetch_assoc($queryTahun)): ?>
                                <option value="<?= (int)$ta['id']; ?>"
                                    <?= (string)$tahun === (string)$ta['id'] ? 'selected' : ''; ?>>
                                    <?= e($ta['nama']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                <?php else: ?>
                    <div class="col-md-2">
                        <input type="text"
                               class="form-control"
                               value="Kelas <?= e($namaKelasWali); ?>"
                               disabled>
                    </div>
                <?php endif; ?>

                <div class="col-md-2">
                    <a href="laporan.php" class="btn btn-secondary w-100">
                        Reset Filter
                    </a>
                </div>
            </form>

            <!-- TOMBOL -->
            <div class="d-flex flex-wrap gap-2 mb-3 bagian-tombol">
                <a href="<?= e($linkCetak); ?>" class="btn btn-primary">
                    Cetak Laporan
                </a>

                <a href="<?= e($linkExcel); ?>" class="btn btn-success">
                    Ekspor Excel
                </a>
            </div>

            <?php if (!$modeCetak): ?>
                <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
                    <small class="text-secondary">
                        Menampilkan <?= $totalData ? $offset + 1 : 0; ?> -
                        <?= min($offset + $batas, $totalData); ?>
                        dari <?= $totalData; ?> laporan
                    </small>

                    <small class="text-secondary">
                        Halaman <?= $halaman; ?> dari <?= $totalHalaman; ?>
                    </small>
                </div>
            <?php else: ?>
                <p class="mb-3">
                    Tanggal cetak: <?= date('d-m-Y H:i:s'); ?> WIB<br>
                    Total data: <?= $totalData; ?>
                </p>
            <?php endif; ?>

            <!-- TABEL LAPORAN -->
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
                                <tr>
                                    <td><?= $no++; ?></td>

                                    <td>
                                        <?= !empty($data['tanggal'])
                                            ? date('d-m-Y', strtotime($data['tanggal']))
                                            : '-'; ?>
                                    </td>

                                    <td><?= e($data['nis'] ?? '-'); ?></td>
                                    <td><?= e($data['nama_siswa']); ?></td>
                                    <td><?= e($data['nama_kelas']); ?></td>
                                    <td><?= e($data['nama_pelanggaran']); ?></td>
                                    <td><?= (int)$data['poin']; ?> poin</td>
                                    <td><?= e($data['nama_guru']); ?></td>
                                    <td><?= e($data['tindakan'] ?: '-'); ?></td>
                                    <td><?= e($data['keterangan'] ?: '-'); ?></td>
                                    <td>
                                        <span class="badge bg-success">Selesai</span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>

                        <?php else: ?>
                            <tr>
                                <td colspan="11"
                                    class="text-center text-secondary py-4">
                                    Belum ada laporan pelanggaran yang sesuai dengan filter.
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

                                <?php
                                $linkHalaman = function ($nomor) use ($parameter) {
                                    return '?' . http_build_query(
                                        array_merge($parameter, ['halaman' => $nomor])
                                    );
                                };
                                ?>

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
                    window.onload = function () {
                        window.print();
                    };
                </script>
            <?php endif; ?>

        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('filterLaporan');
    let timer;

    // PENCARIAN OTOMATIS
    document.getElementById('inputCari').addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(() => form.requestSubmit(), 400);
    });

    // FILTER TANGGAL DAN TAHUN
    form.querySelectorAll('input[type="date"], select').forEach(input => {
        input.addEventListener('change', function () {
            const mulai = document.getElementById('tanggalMulai').value;
            const selesai = document.getElementById('tanggalSelesai').value;

            if (mulai && selesai && mulai > selesai) {
                alert('Tanggal mulai tidak boleh melebihi tanggal selesai.');
                return;
            }

            form.requestSubmit();
        });
    });
});
</script>

</body>
</html>
