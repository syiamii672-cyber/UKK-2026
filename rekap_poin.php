
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

$role = strtolower(trim($_SESSION['role'] ?? ''));

// ADMIN DAN SEMUA GURU BOLEH MELIHAT REKAP SELURUH KELAS
if (!in_array($role, ['admin', 'guru'], true)) {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "rekap_poin";

// ==========================================
// FUNGSI BANTU
// ==========================================

function e($nilai) {
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

function statusPoin($poin) {
    if ($poin >= 80) {
        return ['Baik', 'success', 'poin-baik'];
    } elseif ($poin >= 60) {
        return ['Perlu Perhatian', 'info', 'poin-perhatian'];
    } elseif ($poin >= 40) {
        return ['Pembinaan', 'warning', 'poin-pembinaan'];
    } elseif ($poin >= 20) {
        return ['Peringatan', 'warning', 'poin-peringatan'];
    }

    return ['Pembinaan Khusus', 'danger', 'poin-khusus'];
}

// ==========================================
// AMBIL TAHUN AJARAN AKTIF
// ==========================================

$queryTahun = mysqli_query($koneksi, "
    SELECT id, nama
    FROM t_tahun_ajaran
    WHERE status_aktif = 1
    LIMIT 1
");

if (!$queryTahun) {
    die("Gagal mengambil tahun ajaran: " . mysqli_error($koneksi));
}

$tahunAktif = mysqli_fetch_assoc($queryTahun);

if (!$tahunAktif) {
    die("Belum ada tahun ajaran aktif.");
}

$tahunId = (int)$tahunAktif['id'];
$namaTahun = $tahunAktif['nama'];

// ==========================================
// PENCARIAN
// ==========================================

$cari = isset($_GET['cari']) && is_scalar($_GET['cari'])
    ? trim((string)$_GET['cari'])
    : '';

$whereCari = '';

if ($cari !== '') {
    $kata = mysqli_real_escape_string($koneksi, $cari);

    $whereCari = "
        AND (
            s.nis LIKE '%$kata%'
            OR s.nama LIKE '%$kata%'
            OR k.nama LIKE '%$kata%'
        )
    ";
}

// ==========================================
// QUERY REKAP SELURUH KELAS
// ==========================================

$sqlRekap = "
    SELECT
        ks.tahun_ajaran_id,
        ks.siswa_id,
        s.nis,
        s.nama AS nama_siswa,
        ks.kelas_id,
        k.nama AS nama_kelas,
        ta.nama AS tahun_ajaran,

        COALESCE(p.jumlah_pelanggaran, 0) AS jumlah_pelanggaran,

        COALESCE(p.total_pengurangan, 0) AS total_pengurangan,

        GREATEST(
            0,
            100 - COALESCE(p.total_pengurangan, 0)
        ) AS sisa_poin

    FROM t_kelas_siswa ks

    JOIN t_siswa s
        ON s.id = ks.siswa_id

    JOIN t_kelas k
        ON k.id = ks.kelas_id

    JOIN t_tahun_ajaran ta
        ON ta.id = ks.tahun_ajaran_id

    LEFT JOIN (
        SELECT
            tahun_ajaran_id,
            siswa_id,
            kelas_id,
            COUNT(*) AS jumlah_pelanggaran,
            SUM(poin) AS total_pengurangan

        FROM t_pelanggaran_siswa

        GROUP BY
            tahun_ajaran_id,
            siswa_id,
            kelas_id

    ) p
        ON p.siswa_id = ks.siswa_id
        AND p.kelas_id = ks.kelas_id
        AND p.tahun_ajaran_id = ks.tahun_ajaran_id

    WHERE ks.status_aktif = 1
      AND s.status_aktif = 1
      AND k.status_aktif = 1
      AND ks.tahun_ajaran_id = $tahunId

    $whereCari
";

// ==========================================
// HITUNG JUMLAH DATA
// ==========================================

$qJumlah = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total
    FROM ($sqlRekap) AS rekap
");

if (!$qJumlah) {
    die("Gagal menghitung rekap poin: " . mysqli_error($koneksi));
}

$dataJumlah = mysqli_fetch_assoc($qJumlah);
$totalData = (int)($dataJumlah['total'] ?? 0);

// ==========================================
// PAGINATION
// ==========================================

$batas = 10;

$totalHalaman = max(
    1,
    (int)ceil($totalData / $batas)
);

$halaman = max(
    1,
    (int)($_GET['halaman'] ?? 1)
);

$halaman = min($halaman, $totalHalaman);

$offset = ($halaman - 1) * $batas;

// ==========================================
// AMBIL DATA REKAP
// ==========================================

$query = mysqli_query($koneksi, "
    SELECT *
    FROM ($sqlRekap) AS rekap

    ORDER BY
        rekap.sisa_poin ASC,
        rekap.nama_siswa ASC

    LIMIT $batas OFFSET $offset
");

if (!$query) {
    die("Gagal mengambil data rekap poin: " . mysqli_error($koneksi));
}

// ==========================================
// LINK PAGINATION
// ==========================================

function urlHalamanRekapPoin($nomor, $cari) {
    return "rekap_poin.php?" . http_build_query([
        'cari' => $cari,
        'halaman' => $nomor
    ]);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Rekap Poin Siswa</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            margin: 0;
            background: #F1F5FB;
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

        .judul-halaman {
            color: #173B67;
            font-weight: 700;
        }

        .deskripsi-halaman,
        .info-data {
            color: #718096;
            font-size: 13px;
        }

        .card-data {
            background: #fff;
            border: 1px solid #E5EAF2;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(23,59,103,.06);
        }

        .form-control {
            min-height: 42px;
            border: 1px solid #D8E1ED;
            border-radius: 8px;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: #3779C5;
            box-shadow: 0 0 0 3px rgba(55,121,197,.12);
        }

        .btn-cari,
        .btn-detail {
            background: #245B96;
            border: 1px solid #245B96;
            color: #fff;
            border-radius: 8px;
            font-weight: 600;
        }

        .btn-cari {
            padding: 9px 17px;
        }

        .btn-detail {
            padding: 6px 12px;
            font-size: 12px;
        }

        .btn-cari:hover,
        .btn-detail:hover {
            background: #173B67;
            border-color: #173B67;
            color: #fff;
        }

        .btn-reset {
            background: #E9EEF5;
            border: 1px solid #E9EEF5;
            color: #40546B;
            border-radius: 8px;
            padding: 9px 17px;
        }

        .info-tahun {
            background: #EAF2FB;
            border: 1px solid #D7E6F7;
            color: #245B96;
            border-radius: 8px;
            padding: 9px 13px;
            font-size: 13px;
            font-weight: 600;
        }

        .table {
            margin-bottom: 0;
            font-size: 14px;
            white-space: nowrap;
        }

        .table thead th {
            background: #173B67;
            color: #fff;
            font-weight: 600;
            padding: 13px 12px;
            border-color: #31577F;
        }

        .table tbody td {
            padding: 12px;
            color: #40546B;
            border-color: #E8EDF4;
        }

        .badge-poin {
            padding: 7px 11px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .poin-baik {
            background: #E3F5EA;
            color: #21834B;
        }

        .poin-perhatian {
            background: #E5F0FC;
            color: #245B96;
        }

        .poin-pembinaan {
            background: #FFF3D6;
            color: #946200;
        }

        .poin-peringatan {
            background: #FCE8D5;
            color: #A65313;
        }

        .poin-khusus {
            background: #FCE8E8;
            color: #C03939;
        }

        .pagination {
            gap: 4px;
        }

        .page-link {
            border: 1px solid #DCE5F0;
            color: #245B96;
            border-radius: 6px !important;
            font-size: 13px;
        }

        .page-item.active .page-link {
            background: #245B96;
            border-color: #245B96;
        }

        @media (max-width: 767.98px) {
            .konten-utama {
                padding: 100px 15px 25px;
            }

            .judul-halaman {
                font-size: 22px;
            }
        }
    </style>
</head>

<body>

<?php include "includes/sidebar.php"; ?>
<?php include "includes/navbar.php"; ?>

<main class="konten-utama">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>
            <h2 class="judul-halaman mb-1">
                Rekap Poin Siswa
            </h2>

            <p class="deskripsi-halaman mb-0">
                Pantau jumlah pelanggaran, pengurangan, dan sisa poin seluruh siswa.
            </p>
        </div>

        <div class="info-tahun">
            Seluruh Kelas · Tahun Ajaran <?= e($namaTahun); ?>
        </div>

    </div>

    <div class="card card-data">

        <div class="card-body p-3 p-md-4">

            <!-- PENCARIAN -->

            <form method="GET" class="row g-2 mb-4">

                <div class="col-md-6 col-lg-5">

                    <input
                        type="search"
                        name="cari"
                        class="form-control"
                        placeholder="Cari NIS, nama siswa, atau kelas..."
                        value="<?= e($cari); ?>"
                        autocomplete="off"
                    >

                </div>

                <div class="col-auto">

                    <button type="submit" class="btn btn-cari">
                        Cari
                    </button>

                </div>

                <?php if ($cari !== ''): ?>

                    <div class="col-auto">

                        <a href="rekap_poin.php" class="btn btn-reset">
                            Reset
                        </a>

                    </div>

                <?php endif; ?>

            </form>

            <!-- INFORMASI DATA -->

            <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">

                <small class="info-data">

                    Menampilkan
                    <?= $totalData > 0 ? $offset + 1 : 0; ?>–<?= min($offset + $batas, $totalData); ?>
                    dari <?= $totalData; ?> siswa

                </small>

                <small class="info-data">

                    Halaman <?= $halaman; ?> dari <?= $totalHalaman; ?>

                </small>

            </div>

            <!-- TABEL -->

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Jumlah Pelanggaran</th>
                            <th>Total Pengurangan</th>
                            <th>Sisa Poin</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (mysqli_num_rows($query) > 0): ?>

                        <?php $no = $offset + 1; ?>

                        <?php while ($data = mysqli_fetch_assoc($query)): ?>

                            <?php
                            $sisaPoin = (int)$data['sisa_poin'];

                            [$namaStatus, $warnaStatus, $kelasStatus]
                                = statusPoin($sisaPoin);
                            ?>

                            <tr>

                                <td>
                                    <?= $no++; ?>
                                </td>

                                <td>
                                    <?= e($data['nis']); ?>
                                </td>

                                <td>
                                    <?= e($data['nama_siswa']); ?>
                                </td>

                                <td>
                                    <?= e($data['nama_kelas']); ?>
                                </td>

                                <td>
                                    <?= (int)$data['jumlah_pelanggaran']; ?> kali
                                </td>

                                <td>
                                    -<?= (int)$data['total_pengurangan']; ?> poin
                                </td>

                                <td>

                                    <span class="badge badge-poin <?= $kelasStatus; ?>">
                                        <?= $sisaPoin; ?> poin
                                    </span>

                                </td>

                                <td>

                                    <span class="badge text-bg-<?= $warnaStatus; ?>">
                                        <?= e($namaStatus); ?>
                                    </span>

                                </td>

                                <td>

                                    <a
                                        class="btn btn-detail"
                                        href="detail_rekap_poin.php?siswa_id=<?= (int)$data['siswa_id']; ?>&kelas_id=<?= (int)$data['kelas_id']; ?>&tahun_ajaran_id=<?= (int)$data['tahun_ajaran_id']; ?>"
                                    >
                                        Detail
                                    </a>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="9" class="text-center text-muted py-4">

                                Data siswa atau rekap pelanggaran tidak ditemukan.

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

            <!-- PAGINATION -->

            <?php if ($totalHalaman > 1): ?>

                <nav class="mt-4">

                    <ul class="pagination pagination-sm justify-content-end mb-0">

                        <li class="page-item <?= $halaman <= 1 ? 'disabled' : ''; ?>">

                            <a
                                class="page-link"
                                href="<?= e(urlHalamanRekapPoin(max(1, $halaman - 1), $cari)); ?>"
                            >
                                Sebelumnya
                            </a>

                        </li>

                        <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>

                            <li class="page-item <?= $halaman === $i ? 'active' : ''; ?>">

                                <a
                                    class="page-link"
                                    href="<?= e(urlHalamanRekapPoin($i, $cari)); ?>"
                                >
                                    <?= $i; ?>
                                </a>

                            </li>

                        <?php endfor; ?>

                        <li class="page-item <?= $halaman >= $totalHalaman ? 'disabled' : ''; ?>">

                            <a
                                class="page-link"
                                href="<?= e(urlHalamanRekapPoin(min($totalHalaman, $halaman + 1), $cari)); ?>"
                            >
                                Selanjutnya
                            </a>

                        </li>

                    </ul>

                </nav>

            <?php endif; ?>

        </div>

    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
