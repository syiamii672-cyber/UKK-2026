
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

$role = $_SESSION['role'] ?? '';
$idUser = (int) ($_SESSION['id'] ?? 0);
$menuAktif = "riwayat";

// Hanya Admin dan Guru yang boleh masuk
if (!in_array($role, ['admin', 'guru'], true)) {
    header("Location: dashboard.php");
    exit;
}


// Deteksi Wali Kelas
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
    }
}


// Fungsi keamanan output
function e($nilai) {
    return htmlspecialchars((string) ($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

// Filter pencarian
$cari = trim($_GET['cari'] ?? '');
$cariSQL = mysqli_real_escape_string($koneksi, $cari);

// Filter status
$status = $_GET['status'] ?? '';
$statusValid = [
    "Belum ditangani",
    "Sedang diproses",
    "Selesai"
];

if (!in_array($status, $statusValid, true)) {
    $status = '';
}

// Tahun ajaran aktif
$queryAktif = mysqli_query($koneksi, "
    SELECT id
    FROM t_tahun_ajaran
    WHERE status_aktif = 1
    LIMIT 1
");

$dataAktif = $queryAktif
    ? mysqli_fetch_assoc($queryAktif)
    : null;

$tahunAktif = (int) ($dataAktif['id'] ?? 0);

// Filter tahun ajaran
if ($isWaliKelas) {
    $tahun = $tahunWali;
} else {
    $tahun = $_GET['tahun'] ?? (string) $tahunAktif;

    if ($tahun !== "all" && !ctype_digit((string) $tahun)) {
        $tahun = (string) $tahunAktif;
    }

    $tahun = $tahun === "all" ? "all" : (int) $tahun;
}

// Kondisi SQL
$where = " WHERE 1=1 ";

if ($isWaliKelas) {
    $where .= " AND ps.kelas_id = $kelasWali
                AND ps.tahun_ajaran_id = $tahunWali ";
} elseif ($tahun !== "all") {
    $where .= " AND ps.tahun_ajaran_id = $tahun ";
}

if ($cari !== '') {
    $where .= " AND (
        ps.nama_siswa LIKE '%$cariSQL%'
        OR s.nis LIKE '%$cariSQL%'
        OR ps.nama_kelas LIKE '%$cariSQL%'
        OR ps.nama_pelanggaran LIKE '%$cariSQL%'
        OR ps.nama_guru LIKE '%$cariSQL%'
        OR ps.keterangan LIKE '%$cariSQL%'
    )";
}

if ($status !== '') {
    $statusSQL = mysqli_real_escape_string($koneksi, $status);
    $where .= " AND ps.status = '$statusSQL' ";
}

// Pagination
$batas = 10;
$halaman = max(1, (int) ($_GET['halaman'] ?? 1));

// Hitung jumlah data
$queryJumlah = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total
    FROM t_pelanggaran_siswa ps
    LEFT JOIN t_siswa s ON ps.siswa_id = s.id
    $where
");

if (!$queryJumlah) {
    die("Gagal menghitung riwayat: " . mysqli_error($koneksi));
}

$totalData = (int) mysqli_fetch_assoc($queryJumlah)['total'];

$totalHalaman = max(1, (int) ceil($totalData / $batas));
$halaman = min($halaman, $totalHalaman);
$offset = ($halaman - 1) * $batas;

// Ambil data riwayat
$queryRiwayat = mysqli_query($koneksi, "
    SELECT ps.*, s.nis
    FROM t_pelanggaran_siswa ps
    LEFT JOIN t_siswa s ON ps.siswa_id = s.id
    $where
    ORDER BY ps.tanggal DESC, ps.id DESC
    LIMIT $offset, $batas
");

if (!$queryRiwayat) {
    die("Gagal mengambil riwayat: " . mysqli_error($koneksi));
}

// Daftar tahun ajaran
$queryTahun = mysqli_query($koneksi, "
    SELECT id, nama
    FROM t_tahun_ajaran
    ORDER BY tanggal_mulai DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pelanggaran</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

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

    <h2 class="fw-bold mb-1">Riwayat Pelanggaran</h2>

    <p class="text-secondary mb-4">
        Daftar catatan pelanggaran siswa yang telah tercatat dalam sistem.
    </p>

    <?php if ($isWaliKelas): ?>
        <div class="alert alert-primary">
            <strong>Wali Kelas</strong> |
            Kelas:
            <?= e(mysqli_fetch_assoc(mysqli_query($koneksi, "
                SELECT nama FROM t_kelas WHERE id = $kelasWali
            "))['nama'] ?? '-') ?>
        </div>
    <?php endif; ?>

    <div class="card kartu shadow-sm">
        <div class="card-body">

            <form method="GET" class="row g-2 mb-3">

                <div class="col-md-4">
                    <input
                        type="text"
                        name="cari"
                        class="form-control"
                        placeholder="Cari NIS, siswa, kelas, pelanggaran..."
                        value="<?= e($cari) ?>"
                    >
                </div>

                <?php if (!$isWaliKelas): ?>
                    <div class="col-md-3">
                        <select name="tahun" class="form-select">
                            <option value="all"
                                <?= $tahun === "all" ? "selected" : "" ?>>
                                Semua Tahun Ajaran
                            </option>

                            <?php while ($ta = mysqli_fetch_assoc($queryTahun)): ?>
                                <option
                                    value="<?= $ta['id'] ?>"
                                    <?= (string) $tahun === (string) $ta['id'] ? "selected" : "" ?>
                                >
                                    <?= e($ta['nama']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                <?php else: ?>
                    <div class="col-md-3">
                        <input
                            type="text"
                            class="form-control"
                            value="Tahun Ajaran Wali Kelas"
                            disabled
                        >
                    </div>
                <?php endif; ?>

                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>

                        <?php foreach ($statusValid as $st): ?>
                            <option value="<?= e($st) ?>"
                                <?= $status === $st ? "selected" : "" ?>>
                                <?= e($st) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        Cari
                    </button>
                </div>

                <div class="col-md-1">
                    <a href="riwayat_pelanggaran.php"
                       class="btn btn-secondary w-100">
                        Reset
                    </a>
                </div>

            </form>

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
                            <th>Poin</th>
                            <th>Guru</th>
                            <th>Tindakan</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (mysqli_num_rows($queryRiwayat) > 0): ?>

                            <?php $no = $offset + 1; ?>

                            <?php while ($data = mysqli_fetch_assoc($queryRiwayat)): ?>

                                <tr>
                                    <td><?= $no++ ?></td>

                                    <td>
                                        <?= $data['tanggal']
                                            ? date('d-m-Y', strtotime($data['tanggal']))
                                            : '-' ?>
                                    </td>

                                    <td><?= e($data['nis']) ?></td>
                                    <td><?= e($data['nama_siswa']) ?></td>
                                    <td><?= e($data['nama_kelas']) ?></td>
                                    <td><?= e($data['nama_pelanggaran']) ?></td>

                                    <td>
                                        <span class="badge bg-primary">
                                            <?= (int) $data['poin'] ?> Poin
                                        </span>
                                    </td>

                                    <td><?= e($data['nama_guru']) ?></td>
                                    <td><?= e($data['tindakan']) ?></td>

                                    <td>
                                        <?php
                                        $warna = "secondary";

                                        if ($data['status'] === "Selesai") {
                                            $warna = "success";
                                        } elseif ($data['status'] === "Sedang diproses") {
                                            $warna = "warning";
                                        } elseif ($data['status'] === "Belum ditangani") {
                                            $warna = "danger";
                                        }
                                        ?>

                                        <span class="badge bg-<?= $warna ?>">
                                            <?= e($data['status']) ?>
                                        </span>
                                    </td>
                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="10"
                                    class="text-center text-secondary py-4">
                                    Tidak ada riwayat pelanggaran ditemukan.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>
                </table>

            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                <small class="text-secondary">
                    Total <?= $totalData ?> riwayat pelanggaran
                </small>

                <?php
                $parameter = "&cari=" . urlencode($cari)
                           . "&status=" . urlencode($status);

                if (!$isWaliKelas) {
                    $parameter .= "&tahun=" . urlencode((string) $tahun);
                }
                ?>

                <nav>
                    <ul class="pagination pagination-sm mb-0">

                        <li class="page-item <?= $halaman <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link"
                               href="?halaman=<?= max(1, $halaman - 1) . $parameter ?>">
                                Sebelumnya
                            </a>
                        </li>

                        <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>
                            <li class="page-item <?= $halaman == $i ? 'active' : '' ?>">
                                <a class="page-link"
                                   href="?halaman=<?= $i . $parameter ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?= $halaman >= $totalHalaman ? 'disabled' : '' ?>">
                            <a class="page-link"
                               href="?halaman=<?= min($totalHalaman, $halaman + 1) . $parameter ?>">
                                Selanjutnya
                            </a>
                        </li>

                    </ul>
                </nav>

            </div>

        </div>
    </div>

</main>

</body>
</html>
