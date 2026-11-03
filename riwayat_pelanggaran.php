<<<<<<< HEAD
=======

>>>>>>> 553d5e7384eebb77e97b9d0d9edfc333d5e83b0f
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

<<<<<<< HEAD
$role = $_SESSION['role'];
$idUser = (int) $_SESSION['id'];
$menuAktif = "riwayat";

// Hanya admin dan guru yang boleh mengakses
if ($role != "admin" && $role != "guru") {
=======
if ($_SESSION['role'] !== "guru") {
>>>>>>> 553d5e7384eebb77e97b9d0d9edfc333d5e83b0f
    header("Location: dashboard.php");
    exit;
}

<<<<<<< HEAD
// Deteksi wali kelas
$isWaliKelas = false;
$kelasWali = 0;
$tahunWali = 0;

if ($role == "guru") {
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
}

// Filter pencarian dan status
$cari = mysqli_real_escape_string($koneksi, trim($_GET['cari'] ?? ''));
$status = mysqli_real_escape_string($koneksi, trim($_GET['status'] ?? ''));

// Mengambil tahun ajaran aktif
$queryAktif = mysqli_query($koneksi, "
    SELECT id FROM t_tahun_ajaran
    WHERE status_aktif = 1
    LIMIT 1
");

$dataAktif = $queryAktif ? mysqli_fetch_assoc($queryAktif) : null;
$tahunAktif = (int) ($dataAktif['id'] ?? 0);

// Filter tahun ajaran
if ($isWaliKelas) {
    $tahun = $tahunWali;
} else {
    $tahun = isset($_GET['tahun']) && $_GET['tahun'] != "all"
        ? (int) $_GET['tahun']
        : $tahunAktif;
}

// Kondisi pencarian
$where = " WHERE ps.tahun_ajaran_id = $tahun ";

if ($isWaliKelas) {
    $where .= " AND ps.kelas_id = $kelasWali ";
}

if ($cari != '') {
    $where .= " AND (
        ps.nama_siswa LIKE '%$cari%'
        OR s.nis LIKE '%$cari%'
        OR ps.nama_kelas LIKE '%$cari%'
        OR ps.nama_pelanggaran LIKE '%$cari%'
        OR ps.nama_guru LIKE '%$cari%'
        OR ps.keterangan LIKE '%$cari%'
    )";
}

if ($status != '') {
    $where .= " AND ps.status = '$status' ";
}

// Pagination
$batas = 10;
$halaman = max(1, (int) ($_GET['halaman'] ?? 1));

// Hitung jumlah data
$queryJumlah = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total
    FROM t_pelanggaran_siswa ps
    JOIN t_siswa s ON ps.siswa_id = s.id
    $where
");

$totalData = $queryJumlah
    ? (int) mysqli_fetch_assoc($queryJumlah)['total']
    : 0;

$totalHalaman = max(1, ceil($totalData / $batas));
$halaman = min($halaman, $totalHalaman);
$offset = ($halaman - 1) * $batas;

// Ambil riwayat pelanggaran
$queryRiwayat = mysqli_query($koneksi, "
    SELECT
        ps.*,
        s.nis
    FROM t_pelanggaran_siswa ps
    JOIN t_siswa s ON ps.siswa_id = s.id
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
=======
$menuAktif = "riwayat";

$cari = trim($_GET['cari'] ?? '');
$statusFilter = $_GET['status'] ?? '';

$queryAktif = mysqli_query($koneksi, "
    SELECT id FROM t_tahun_ajaran
    WHERE status_aktif = 1 LIMIT 1
");
$tahunAktif = mysqli_fetch_assoc($queryAktif);
$tahunDefault = $tahunAktif ? (string)$tahunAktif['id'] : "semua";

$tahunFilter = $_GET['tahun'] ?? $tahunDefault;

if (
    $tahunFilter !== "semua" &&
    !ctype_digit((string)$tahunFilter)
) {
    $tahunFilter = $tahunDefault;
}

$batas = 10;
$halaman = max(1, (int)($_GET['halaman'] ?? 1));

$queryTahun = mysqli_query($koneksi, "
    SELECT id, nama FROM t_tahun_ajaran
    ORDER BY tanggal_mulai DESC
");

$where = "WHERE 1=1";

if ($cari !== '') {
    $kata = mysqli_real_escape_string($koneksi, $cari);

    $where .= " AND (
        ps.nama_siswa LIKE '%$kata%'
        OR ps.nama_kelas LIKE '%$kata%'
        OR ps.nama_pelanggaran LIKE '%$kata%'
        OR ps.nama_guru LIKE '%$kata%'
        OR ps.keterangan LIKE '%$kata%'
        OR ps.siswa_id IN (
            SELECT id FROM t_siswa
            WHERE nis LIKE '%$kata%'
        )
    )";
}

if ($tahunFilter !== "semua") {
    $tahunId = (int)$tahunFilter;
    $where .= " AND ps.tahun_ajaran_id = $tahunId";
}

$statusValid = [
    "Belum ditangani",
    "Sedang diproses",
    "Selesai"
];

if (in_array($statusFilter, $statusValid, true)) {
    $statusAman = mysqli_real_escape_string($koneksi, $statusFilter);
    $where .= " AND ps.status = '$statusAman'";
} else {
    $statusFilter = '';
}

$queryJumlah = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total
    FROM t_pelanggaran_siswa ps
    $where
");

$totalData = (int)mysqli_fetch_assoc($queryJumlah)['total'];
$totalHalaman = max(1, (int)ceil($totalData / $batas));

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
    die("Gagal mengambil riwayat: " . mysqli_error($koneksi));
}

function e($nilai) {
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}
>>>>>>> 553d5e7384eebb77e97b9d0d9edfc333d5e83b0f
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<<<<<<< HEAD
    <title>Riwayat Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            margin: 0;
            background-color: #f8f9fa;
        }

        .konten-utama {
            margin-left: 220px;
            width: calc(100% - 220px);
            min-height: 100vh;
            padding: 24px;
            box-sizing: border-box;
        }

        .kartu {
            border: none;
            border-radius: 10px;
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

    <h2 class="mb-1">Riwayat Pelanggaran</h2>

    <p class="text-secondary mb-4">
        Daftar catatan pelanggaran siswa yang telah tercatat dalam sistem.
    </p>

    <div class="card kartu shadow-sm">
=======
    <title>Riwayat Pelanggaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include "includes/sidebar.php"; ?>

<div class="container-fluid" style="margin-left:220px; width:calc(100% - 220px); padding:30px;">

    <h3 class="fw-bold mb-1">Riwayat Pelanggaran</h3>
    <p class="text-muted mb-4">Daftar seluruh catatan pelanggaran siswa.</p>

    <?php if (isset($_GET['berhasil'])): ?>
        <div class="alert alert-success">Data berhasil diperbarui.</div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
>>>>>>> 553d5e7384eebb77e97b9d0d9edfc333d5e83b0f
        <div class="card-body">

            <form method="GET" class="row g-2 mb-3">

<<<<<<< HEAD
                <div class="col-md-4">
=======
                <div class="col-md-5">
>>>>>>> 553d5e7384eebb77e97b9d0d9edfc333d5e83b0f
                    <input
                        type="text"
                        name="cari"
                        class="form-control"
                        placeholder="Cari NIS, siswa, kelas, pelanggaran..."
<<<<<<< HEAD
                        value="<?= htmlspecialchars($cari); ?>"
                    >
                </div>

                <?php if (!$isWaliKelas) { ?>
                    <div class="col-md-3">
                        <select name="tahun" class="form-select">
                            <option value="all">Semua Tahun Ajaran</option>

                            <?php while ($data = mysqli_fetch_assoc($queryTahun)) { ?>
                                <option
                                    value="<?= $data['id']; ?>"
                                    <?= $tahun == $data['id'] ? 'selected' : ''; ?>
                                >
                                    <?= htmlspecialchars($data['nama']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                <?php } else { ?>
                    <div class="col-md-3">
                        <input type="hidden" name="tahun" value="<?= $tahun; ?>">
                        <input
                            type="text"
                            class="form-control"
                            value="Tahun ajaran tugas Wali Kelas"
                            disabled
                        >
                    </div>
                <?php } ?>

                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="Belum ditangani"
                            <?= $status == "Belum ditangani" ? 'selected' : ''; ?>>
                            Belum ditangani
                        </option>
                        <option value="Sedang diproses"
                            <?= $status == "Sedang diproses" ? 'selected' : ''; ?>>
                            Sedang diproses
                        </option>
                        <option value="Selesai"
                            <?= $status == "Selesai" ? 'selected' : ''; ?>>
                            Selesai
                        </option>
=======
                        value="<?= e($cari) ?>"
                        oninput="this.form.submit()"
                    >
                </div>

                <div class="col-md-3">
                    <select name="tahun" class="form-select" onchange="this.form.submit()">
                        <option value="semua" <?= $tahunFilter === "semua" ? "selected" : "" ?>>
                            Semua Tahun Ajaran
                        </option>

                        <?php while ($ta = mysqli_fetch_assoc($queryTahun)): ?>
                            <option
                                value="<?= $ta['id'] ?>"
                                <?= $tahunFilter == $ta['id'] ? "selected" : "" ?>
                            >
                                <?= e($ta['nama']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <?php foreach ($statusValid as $st): ?>
                            <option value="<?= e($st) ?>" <?= $statusFilter === $st ? "selected" : "" ?>>
                                <?= e($st) ?>
                            </option>
                        <?php endforeach; ?>
>>>>>>> 553d5e7384eebb77e97b9d0d9edfc333d5e83b0f
                    </select>
                </div>

                <div class="col-md-1">
<<<<<<< HEAD
                    <button type="submit" class="btn btn-primary w-100">
                        Cari
                    </button>
                </div>

                <div class="col-md-1">
                    <a href="riwayat_pelanggaran.php"
                       class="btn btn-secondary w-100">
                        Reset
                    </a>
=======
                    <a href="riwayat_pelanggaran.php" class="btn btn-secondary w-100">Reset</a>
>>>>>>> 553d5e7384eebb77e97b9d0d9edfc333d5e83b0f
                </div>

            </form>

            <div class="table-responsive">
<<<<<<< HEAD
                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-light">
=======
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-primary">
>>>>>>> 553d5e7384eebb77e97b9d0d9edfc333d5e83b0f
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
<<<<<<< HEAD

                        <?php if (mysqli_num_rows($queryRiwayat) > 0) { ?>

                            <?php $no = $offset + 1; ?>

                            <?php while ($data = mysqli_fetch_assoc($queryRiwayat)) { ?>

                                <tr>
                                    <td><?= $no++; ?></td>

                                    <td>
                                        <?= date('d-m-Y', strtotime($data['tanggal'])); ?>
                                    </td>

                                    <td><?= htmlspecialchars($data['nis']); ?></td>

                                    <td><?= htmlspecialchars($data['nama_siswa']); ?></td>

                                    <td><?= htmlspecialchars($data['nama_kelas']); ?></td>

                                    <td><?= htmlspecialchars($data['nama_pelanggaran']); ?></td>

                                    <td>
                                        <span class="badge bg-primary">
                                            <?= (int) $data['poin']; ?> Poin
                                        </span>
                                    </td>

                                    <td><?= htmlspecialchars($data['nama_guru']); ?></td>

                                    <td><?= htmlspecialchars($data['tindakan']); ?></td>

                                    <td>
                                        <?php
                                        $warna = "secondary";

                                        if ($data['status'] == "Selesai") {
                                            $warna = "success";
                                        } elseif ($data['status'] == "Sedang diproses") {
                                            $warna = "warning";
                                        } elseif ($data['status'] == "Belum ditangani") {
                                            $warna = "danger";
                                        }
                                        ?>

                                        <span class="badge bg-<?= $warna; ?>">
                                            <?= htmlspecialchars($data['status']); ?>
                                        </span>
                                    </td>
                                </tr>

                            <?php } ?>

                        <?php } else { ?>

                            <tr>
                                <td colspan="10" class="text-center text-secondary py-4">
                                    Tidak ada riwayat pelanggaran ditemukan.
                                </td>
                            </tr>

                        <?php } ?>

=======
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
                                <td>
                                    <?php
                                    $warna = "secondary";
                                    if ($data['status'] === "Belum ditangani") {
                                        $warna = "danger";
                                    } elseif ($data['status'] === "Sedang diproses") {
                                        $warna = "warning";
                                    } elseif ($data['status'] === "Selesai") {
                                        $warna = "success";
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
                            <td colspan="10" class="text-center text-muted py-4">
                                Belum ada riwayat pelanggaran.
                            </td>
                        </tr>
                    <?php endif; ?>
>>>>>>> 553d5e7384eebb77e97b9d0d9edfc333d5e83b0f
                    </tbody>
                </table>
            </div>

<<<<<<< HEAD
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                <small class="text-secondary">
                    Total <?= $totalData; ?> riwayat pelanggaran
                </small>

                <?php
                $parameter = "&cari=" . urlencode($cari)
                           . "&status=" . urlencode($status);

                if (!$isWaliKelas) {
                    $parameter .= "&tahun=" . $tahun;
                }
                ?>

                <nav>
                    <ul class="pagination pagination-sm mb-0">

                        <li class="page-item <?= $halaman <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link"
                               href="?halaman=<?= $halaman - 1; ?><?= $parameter; ?>">
                                Sebelumnya
                            </a>
                        </li>

                        <?php for ($i = 1; $i <= $totalHalaman; $i++) { ?>
                            <li class="page-item <?= $halaman == $i ? 'active' : ''; ?>">
                                <a class="page-link"
                                   href="?halaman=<?= $i; ?><?= $parameter; ?>">
                                    <?= $i; ?>
                                </a>
                            </li>
                        <?php } ?>

                        <li class="page-item <?= $halaman >= $totalHalaman ? 'disabled' : ''; ?>">
                            <a class="page-link"
                               href="?halaman=<?= $halaman + 1; ?><?= $parameter; ?>">
                                Selanjutnya
                            </a>
                        </li>

                    </ul>
                </nav>

=======
            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                <small class="text-muted">
                    Menampilkan <?= mysqli_num_rows($query) ?> dari <?= $totalData ?> data
                </small>

                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>
                            <li class="page-item <?= $halaman == $i ? 'active' : '' ?>">
                                <a class="page-link"
                                   href="?cari=<?= urlencode($cari) ?>&tahun=<?= urlencode($tahunFilter) ?>&status=<?= urlencode($statusFilter) ?>&halaman=<?= $i ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
>>>>>>> 553d5e7384eebb77e97b9d0d9edfc333d5e83b0f
            </div>

        </div>
    </div>
<<<<<<< HEAD

</main>

</body>
</html>
=======
</div>

</body>
</html>
>>>>>>> 553d5e7384eebb77e97b9d0d9edfc333d5e83b0f
