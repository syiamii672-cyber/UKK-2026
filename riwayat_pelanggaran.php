<?php
include "includes/cek_session.php";
include "config/koneksi.php";

$role = $_SESSION['role'];
$idUser = (int) $_SESSION['id'];
$menuAktif = "riwayat";

// Hanya admin dan guru yang boleh mengakses
if ($role != "admin" && $role != "guru") {
    header("Location: dashboard.php");
    exit;
}

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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        <div class="card-body">

            <form method="GET" class="row g-2 mb-3">

                <div class="col-md-4">
                    <input
                        type="text"
                        name="cari"
                        class="form-control"
                        placeholder="Cari NIS, siswa, kelas, pelanggaran..."
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

                    </tbody>
                </table>
            </div>

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

            </div>

        </div>
    </div>

</main>

</body>
</html>