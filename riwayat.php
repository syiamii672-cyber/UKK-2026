
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

$role = $_SESSION['role'] ?? '';
$idUser = (int)($_SESSION['id'] ?? 0);
$menuAktif = "riwayat";

// ==========================================
// HAK AKSES
// ==========================================

if (!in_array($role, ['admin', 'guru'], true)) {
    header("Location: dashboard.php");
    exit;
}

// ==========================================
// FUNGSI KEAMANAN OUTPUT
// ==========================================

function e($nilai) {
    return htmlspecialchars(
        (string)($nilai ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

// ==========================================
// IDENTITAS GURU
// ==========================================

$idGuru = 0;

if ($role === 'guru') {

    $queryGuru = mysqli_query($koneksi, "
        SELECT id
        FROM t_guru
        WHERE user_id = $idUser
          AND status_aktif = 1
        LIMIT 1
    ");

    if (!$queryGuru) {
        die("Gagal mengambil identitas guru: " . mysqli_error($koneksi));
    }

    $dataGuru = mysqli_fetch_assoc($queryGuru);
    $idGuru = (int)($dataGuru['id'] ?? 0);
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

// ==========================================
// KONDISI QUERY
// ==========================================

$where = " WHERE 1=1 ";

// Guru hanya melihat laporan yang dibuat sendiri
if ($role === 'guru') {
    $where .= " AND ps.guru_id = $idGuru ";
}

// Filter tahun ajaran
if ($tahun !== 'all') {
    $where .= " AND ps.tahun_ajaran_id = $tahun ";
}

// ==========================================
// PENCARIAN
// ==========================================

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
// PAGINATION
// ==========================================

$batas = 10;

$halaman = isset($_GET['halaman']) && is_scalar($_GET['halaman'])
    ? max(1, (int)$_GET['halaman'])
    : 1;

// ==========================================
// HITUNG JUMLAH DATA
// ==========================================

$queryJumlah = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total
    FROM t_pelanggaran_siswa ps
    LEFT JOIN t_siswa s
        ON ps.siswa_id = s.id
    $where
");

if (!$queryJumlah) {
    die("Gagal menghitung riwayat: " . mysqli_error($koneksi));
}

$dataJumlah = mysqli_fetch_assoc($queryJumlah);
$totalData = (int)($dataJumlah['total'] ?? 0);

$totalHalaman = max(
    1,
    (int)ceil($totalData / $batas)
);

$halaman = min($halaman, $totalHalaman);
$offset = ($halaman - 1) * $batas;

// ==========================================
// AMBIL DATA RIWAYAT
// ==========================================

$queryRiwayat = mysqli_query($koneksi, "
    SELECT
        ps.*,
        s.nis
    FROM t_pelanggaran_siswa ps
    LEFT JOIN t_siswa s
        ON ps.siswa_id = s.id
    $where
    ORDER BY
        ps.tanggal DESC,
        ps.id DESC
    LIMIT $offset, $batas
");

if (!$queryRiwayat) {
    die("Gagal mengambil riwayat: " . mysqli_error($koneksi));
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
// PARAMETER PAGINATION
// ==========================================

$parameter = [
    'cari' => $cari,
    'tahun' => (string)$tahun
];

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Riwayat Pelanggaran</title>

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
    </style>

</head>

<body>

<?php include "includes/sidebar.php"; ?>

<main class="konten-utama">

    <div class="mb-4">

        <h2 class="fw-bold mb-1">
            Riwayat Pelanggaran
        </h2>

        <p class="text-secondary mb-0">
            Daftar pelanggaran yang pernah dicatat oleh akun Anda, termasuk dari kelas mana pun.
        </p>

    </div>

    <div class="card kartu shadow-sm">

        <div class="card-body">

            <!-- FILTER DAN PENCARIAN -->

            <form method="GET" action="riwayat.php" class="row g-2 mb-3">

                <div class="col-md-5">

                    <input
                        type="text"
                        name="cari"
                        class="form-control"
                        placeholder="Cari NIS, siswa, kelas, pelanggaran..."
                        value="<?= e($cari); ?>"
                    >

                </div>

                <div class="col-md-4">

                    <select name="tahun" class="form-select">

                        <option
                            value="all"
                            <?= $tahun === 'all' ? 'selected' : ''; ?>
                        >
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

                <div class="col-md-3 d-flex gap-2">

                    <button
                        type="submit"
                        class="btn btn-primary w-100"
                    >
                        Cari
                    </button>

                    <a
                        href="riwayat.php"
                        class="btn btn-secondary w-100"
                    >
                        Reset
                    </a>

                </div>

            </form>

            <!-- INFORMASI JUMLAH DATA -->

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">

                <small class="text-secondary">

                    Menampilkan
                    <?= $totalData > 0 ? $offset + 1 : 0; ?>
                    -
                    <?= min($offset + $batas, $totalData); ?>
                    dari <?= $totalData; ?> riwayat

                </small>

                <small class="text-secondary">
                    Halaman <?= $halaman; ?> dari <?= $totalHalaman; ?>
                </small>

            </div>

            <!-- TABEL RIWAYAT -->

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
                            <th>Guru</th>
                            <th>Tindakan</th>
                            <th>Status</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php if (mysqli_num_rows($queryRiwayat) > 0): ?>

                            <?php $no = $offset + 1; ?>

                            <?php while ($data = mysqli_fetch_assoc($queryRiwayat)): ?>

                                <?php

                                // Format tanggal
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

                                // Status tindakan
                                $status = trim($data['status'] ?? '');

                                if ($status === 'Belum Ditindak' || $status === '') {
                                    $status = 'Belum Ditindak';
                                    $warnaStatus = 'secondary';
                                } elseif ($status === 'Diproses') {
                                    $warnaStatus = 'warning';
                                } elseif ($status === 'Selesai') {
                                    $warnaStatus = 'success';
                                } else {
                                    $warnaStatus = 'secondary';
                                }

                                ?>

                                <tr>

                                    <td><?= $no++; ?></td>

                                    <td><?= e($tanggal); ?></td>

                                    <td><?= e($data['nis'] ?? '-'); ?></td>

                                    <td><?= e($data['nama_siswa']); ?></td>

                                    <td><?= e($data['nama_kelas']); ?></td>

                                    <td><?= e($data['nama_pelanggaran']); ?></td>

                                    <td>
                                        <span class="badge bg-primary">
                                            -<?= (int)$data['poin']; ?> poin
                                        </span>
                                    </td>

                                    <td><?= e($data['nama_guru']); ?></td>

                                    <td>
                                        <?= e($data['tindakan'] ?: '-'); ?>
                                    </td>

                                    <td>
                                        <span class="badge text-bg-<?= $warnaStatus ?>">
                                            <?= e($status); ?>
                                        </span>
                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="10" class="text-center text-secondary py-4">
                                    Tidak ada riwayat pelanggaran ditemukan.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

            <!-- PAGINATION -->

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                <small class="text-secondary">
                    Total <?= $totalData; ?> riwayat pelanggaran
                </small>

                <?php if ($totalHalaman > 1): ?>

                    <nav>

                        <ul class="pagination pagination-sm mb-0">

                            <li class="page-item <?= $halaman <= 1 ? 'disabled' : ''; ?>">

                                <a
                                    class="page-link"
                                    href="?<?= e(http_build_query(array_merge(
                                        $parameter,
                                        ['halaman' => max(1, $halaman - 1)]
                                    ))); ?>"
                                >
                                    Sebelumnya
                                </a>

                            </li>

                            <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>

                                <li class="page-item <?= $halaman == $i ? 'active' : ''; ?>">

                                    <a
                                        class="page-link"
                                        href="?<?= e(http_build_query(array_merge(
                                            $parameter,
                                            ['halaman' => $i]
                                        ))); ?>"
                                    >
                                        <?= $i; ?>
                                    </a>

                                </li>

                            <?php endfor; ?>

                            <li class="page-item <?= $halaman >= $totalHalaman ? 'disabled' : ''; ?>">

                                <a
                                    class="page-link"
                                    href="?<?= e(http_build_query(array_merge(
                                        $parameter,
                                        ['halaman' => min($totalHalaman, $halaman + 1)]
                                    ))); ?>"
                                >
                                    Selanjutnya
                                </a>

                            </li>

                        </ul>

                    </nav>

                <?php endif; ?>

            </div>

        </div>

    </div>

</main>

</body>
</html>
