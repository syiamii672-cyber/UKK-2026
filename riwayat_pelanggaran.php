
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] !== "guru") {
    header("Location: dashboard.php");
    exit;
}

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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        <div class="card-body">

            <form method="GET" class="row g-2 mb-3">

                <div class="col-md-5">
                    <input
                        type="text"
                        name="cari"
                        class="form-control"
                        placeholder="Cari NIS, siswa, kelas, pelanggaran..."
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
                    </select>
                </div>

                <div class="col-md-1">
                    <a href="riwayat_pelanggaran.php" class="btn btn-secondary w-100">Reset</a>
                </div>

            </form>

            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
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
                    </tbody>
                </table>
            </div>

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
            </div>

        </div>
    </div>
</div>

</body>
</html>
