
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

$role = strtolower(trim($_SESSION['role'] ?? ''));

if (!in_array($role, ['admin', 'guru'], true)) {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "rekap_poin";

function e($nilai) {
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

// FILTER
$cari = isset($_GET['cari']) && is_scalar($_GET['cari'])
    ? trim((string)$_GET['cari']) : '';

$tahunFilter = isset($_GET['tahun']) && is_scalar($_GET['tahun'])
    ? trim((string)$_GET['tahun']) : '';

// TAHUN AJARAN AKTIF
$queryAktif = mysqli_query($koneksi, "
    SELECT id FROM t_tahun_ajaran
    WHERE status_aktif = 1
    LIMIT 1
");

if (!$queryAktif) {
    die("Gagal mengambil tahun ajaran: " . mysqli_error($koneksi));
}

$dataAktif = mysqli_fetch_assoc($queryAktif);
$tahunDefault = $dataAktif ? (string)$dataAktif['id'] : 'semua';

if ($tahunFilter === '') {
    $tahunFilter = $tahunDefault;
}

if ($tahunFilter !== 'semua' && !ctype_digit($tahunFilter)) {
    $tahunFilter = $tahunDefault;
}

// DAFTAR TAHUN
$queryTahun = mysqli_query($koneksi, "
    SELECT id, nama
    FROM t_tahun_ajaran
    ORDER BY tanggal_mulai DESC
");

// QUERY REKAP SELURUH SISWA
$sqlRekap = "
    SELECT
        ks.tahun_ajaran_id,
        ta.nama AS tahun_ajaran,
        ks.siswa_id,
        s.nis,
        s.nama AS nama_siswa,
        ks.kelas_id,
        k.nama AS nama_kelas,
        COALESCE(p.jumlah_pelanggaran, 0) AS jumlah_pelanggaran,
        COALESCE(p.total_pengurangan, 0) AS total_pengurangan,
        GREATEST(
            0,
            100 - COALESCE(p.total_pengurangan, 0)
        ) AS sisa_poin

    FROM t_kelas_siswa ks

    JOIN t_siswa s ON ks.siswa_id = s.id
    JOIN t_kelas k ON ks.kelas_id = k.id
    JOIN t_tahun_ajaran ta ON ks.tahun_ajaran_id = ta.id

    LEFT JOIN (
        SELECT
            tahun_ajaran_id,
            siswa_id,
            kelas_id,
            COUNT(*) AS jumlah_pelanggaran,
            SUM(poin) AS total_pengurangan
        FROM t_pelanggaran_siswa
        GROUP BY tahun_ajaran_id, siswa_id, kelas_id
    ) p
        ON p.siswa_id = ks.siswa_id
        AND p.kelas_id = ks.kelas_id
        AND p.tahun_ajaran_id = ks.tahun_ajaran_id

    WHERE
        ks.status_aktif = 1
        AND s.status_aktif = 1
        AND k.status_aktif = 1
";

// FILTER TAHUN
if ($tahunFilter !== 'semua') {
    $tahunId = (int)$tahunFilter;
    $sqlRekap .= " AND ks.tahun_ajaran_id = $tahunId";
}

// FILTER PENCARIAN
if ($cari !== '') {
    $kata = mysqli_real_escape_string($koneksi, $cari);

    $sqlRekap .= "
        AND (
            s.nis LIKE '%$kata%'
            OR s.nama LIKE '%$kata%'
            OR k.nama LIKE '%$kata%'
        )
    ";
}

// HITUNG DATA
$queryJumlah = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total
    FROM ($sqlRekap) AS rekap
");

if (!$queryJumlah) {
    die("Gagal menghitung data: " . mysqli_error($koneksi));
}

$dataJumlah = mysqli_fetch_assoc($queryJumlah);
$totalData = (int)$dataJumlah['total'];

// PAGINATION
$batas = 10;
$totalHalaman = max(1, (int)ceil($totalData / $batas));

$halaman = isset($_GET['halaman']) && is_scalar($_GET['halaman'])
    ? max(1, (int)$_GET['halaman']) : 1;

$halaman = min($halaman, $totalHalaman);
$offset = ($halaman - 1) * $batas;

// DATA REKAP
$query = mysqli_query($koneksi, "
    SELECT *
    FROM ($sqlRekap) AS rekap
    ORDER BY rekap.sisa_poin ASC, rekap.nama_siswa ASC
    LIMIT $batas OFFSET $offset
");

if (!$query) {
    die("Gagal mengambil rekap poin: " . mysqli_error($koneksi));
}

$parameter = [
    'cari' => $cari,
    'tahun' => $tahunFilter
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Poin Seluruh Siswa</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            margin: 0;
            background: #f8f9fa;
        }

        .konten-utama {
            margin-left: 220px;
            width: calc(100% - 220px);
            min-height: 100vh;
            padding: 30px;
            box-sizing: border-box;
        }

        .kartu {
            border: none;
            border-radius: 10px;
        }

        @media (max-width: 575.98px) {
            .konten-utama {
                padding: 15px;
            }
        }
    </style>
</head>

<body>

<?php include "includes/sidebar.php"; ?>

<main class="konten-utama">

    <div class="mb-4">
        <h3 class="fw-bold">Rekap Poin Seluruh Siswa</h3>
        <p class="text-muted mb-0">
            Menampilkan jumlah pelanggaran, total pengurangan,
            dan sisa poin seluruh siswa di sekolah.
        </p>
    </div>

    <div class="card kartu shadow-sm">
        <div class="card-body">

            <form method="GET" class="row g-2 mb-3">

                <div class="col-md-7">
                    <input
                        type="text"
                        name="cari"
                        class="form-control"
                        placeholder="Cari NIS, nama siswa, atau kelas..."
                        value="<?= e($cari); ?>"
                    >
                </div>

                <div class="col-md-3">
                    <select name="tahun" class="form-select">
                        <option value="semua"
                            <?= $tahunFilter === 'semua' ? 'selected' : ''; ?>>
                            Semua Tahun Ajaran
                        </option>

                        <?php if ($queryTahun): ?>
                            <?php while ($ta = mysqli_fetch_assoc($queryTahun)): ?>
                                <option
                                    value="<?= (int)$ta['id']; ?>"
                                    <?= $tahunFilter == $ta['id'] ? 'selected' : ''; ?>
                                >
                                    <?= e($ta['nama']); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        Cari
                    </button>
                </div>

                <div class="col-12">
                    <a href="rekap_poin.php" class="btn btn-secondary btn-sm">
                        Reset
                    </a>
                </div>

            </form>

            <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
                <small class="text-muted">
                    Menampilkan
                    <?= $totalData > 0 ? $offset + 1 : 0; ?> -
                    <?= min($offset + $batas, $totalData); ?>
                    dari <?= $totalData; ?> siswa
                </small>

                <small class="text-muted">
                    Halaman <?= $halaman; ?> dari <?= $totalHalaman; ?>
                </small>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-primary">
                        <tr>
                            <th width="50">No</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Tahun Ajaran</th>
                            <th>Jumlah Pelanggaran</th>
                            <th>Total Pengurangan</th>
                            <th>Sisa Poin</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (mysqli_num_rows($query) > 0): ?>

                            <?php $no = $offset + 1; ?>

                            <?php while ($data = mysqli_fetch_assoc($query)): ?>

                                <?php
                                $sisaPoin = (int)$data['sisa_poin'];

                                if ($sisaPoin >= 80) {
                                    $warnaPoin = 'bg-success';
                                } elseif ($sisaPoin >= 60) {
                                    $warnaPoin = 'bg-primary';
                                } elseif ($sisaPoin >= 40) {
                                    $warnaPoin = 'bg-warning text-dark';
                                } else {
                                    $warnaPoin = 'bg-danger';
                                }
                                ?>

                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= e($data['nis']); ?></td>
                                    <td><?= e($data['nama_siswa']); ?></td>
                                    <td><?= e($data['nama_kelas']); ?></td>
                                    <td><?= e($data['tahun_ajaran']); ?></td>
                                    <td><?= (int)$data['jumlah_pelanggaran']; ?> kali</td>
                                    <td>-<?= (int)$data['total_pengurangan']; ?> poin</td>
                                    <td>
                                        <span class="badge <?= $warnaPoin; ?>">
                                            <?= $sisaPoin; ?> poin
                                        </span>
                                    </td>
                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    Data rekap poin tidak ditemukan.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>
                </table>
            </div>

            <?php if ($totalHalaman > 1): ?>
                <nav class="mt-3">
                    <ul class="pagination pagination-sm justify-content-end mb-0">

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
    </div>

</main>

</body>
</html>
