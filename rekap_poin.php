
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] !== "guru") {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "rekap_poin";

// Ambil tahun ajaran aktif
$queryAktif = mysqli_query($koneksi, "
    SELECT id FROM t_tahun_ajaran
    WHERE status_aktif = 1
    LIMIT 1
");

$tahunAktif = mysqli_fetch_assoc($queryAktif);
$tahunDefault = $tahunAktif ? (string)$tahunAktif['id'] : "semua";

// Pengaturan filter
$tahunFilter = $_GET['tahun'] ?? $tahunDefault;
$cari = trim($_GET['cari'] ?? '');

if ($tahunFilter !== "semua" && !ctype_digit((string)$tahunFilter)) {
    $tahunFilter = $tahunDefault;
}

$batas = 10;
$halaman = max(1, (int)($_GET['halaman'] ?? 1));

// Ambil daftar tahun ajaran
$queryTahun = mysqli_query($koneksi, "
    SELECT id, nama
    FROM t_tahun_ajaran
    ORDER BY tanggal_mulai DESC
");

// Query dasar rekap
$sqlRekap = "
    SELECT
        ps.tahun_ajaran_id,
        ta.nama AS tahun_ajaran,
        ps.siswa_id,
        s.nis,
        ps.nama_siswa,
        ps.kelas_id,
        ps.nama_kelas,
        COUNT(ps.id) AS jumlah_pelanggaran,
        SUM(ps.poin) AS total_poin
    FROM t_pelanggaran_siswa ps
    LEFT JOIN t_siswa s ON ps.siswa_id = s.id
    LEFT JOIN t_tahun_ajaran ta ON ps.tahun_ajaran_id = ta.id
    GROUP BY
        ps.tahun_ajaran_id,
        ta.nama,
        ps.siswa_id,
        s.nis,
        ps.nama_siswa,
        ps.kelas_id,
        ps.nama_kelas
";

// Kondisi pencarian
$where = "WHERE 1=1";

if ($cari !== '') {
    $kata = mysqli_real_escape_string($koneksi, $cari);

    $where .= " AND (
        rekap.nama_siswa LIKE '%$kata%'
        OR rekap.nis LIKE '%$kata%'
        OR rekap.nama_kelas LIKE '%$kata%'
    )";
}

// Filter tahun ajaran
if ($tahunFilter !== "semua") {
    $tahunId = (int)$tahunFilter;
    $where .= " AND rekap.tahun_ajaran_id = $tahunId";
}

// Hitung jumlah siswa dalam rekap
$queryJumlah = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total
    FROM ($sqlRekap) AS rekap
    $where
");

if (!$queryJumlah) {
    die("Gagal menghitung rekap: " . mysqli_error($koneksi));
}

$totalData = (int)mysqli_fetch_assoc($queryJumlah)['total'];
$totalHalaman = max(1, (int)ceil($totalData / $batas));

if ($halaman > $totalHalaman) {
    $halaman = $totalHalaman;
}

$offset = ($halaman - 1) * $batas;

// Ambil data rekap
$query = mysqli_query($koneksi, "
    SELECT *
    FROM ($sqlRekap) AS rekap
    $where
    ORDER BY rekap.total_poin DESC, rekap.nama_siswa ASC
    LIMIT $batas OFFSET $offset
");

if (!$query) {
    die("Gagal mengambil rekap: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rekap Poin Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<?php include "includes/sidebar.php"; ?>

<div style="margin-left:220px; padding:30px;">

    <div class="mb-4">
        <h3 class="fw-bold">Rekap Poin Siswa</h3>
        <p class="text-muted">
            Ringkasan akumulasi poin pelanggaran siswa.
        </p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <!-- Pencarian dan filter -->
            <form method="GET" class="row g-2 mb-3">

                <div class="col-md-7">
                    <input
                        type="text"
                        name="cari"
                        class="form-control"
                        placeholder="Cari NIS, nama siswa, atau kelas..."
                        value="<?= htmlspecialchars($cari) ?>"
                        oninput="this.form.submit()"
                    >
                </div>

                <div class="col-md-5">
                    <select name="tahun" class="form-select"
                        onchange="this.form.submit()">

                        <option value="semua"
                            <?= $tahunFilter === "semua" ? "selected" : "" ?>>
                            Semua Tahun Ajaran
                        </option>

                        <?php while ($ta = mysqli_fetch_assoc($queryTahun)): ?>
                            <option value="<?= $ta['id'] ?>"
                                <?= $tahunFilter == $ta['id'] ? "selected" : "" ?>>
                                <?= htmlspecialchars($ta['nama']) ?>
                            </option>
                        <?php endwhile; ?>

                    </select>
                </div>

            </form>

            <div class="d-flex justify-content-between mb-3">
                <small class="text-muted">
                    Menampilkan <?= $totalData > 0 ? $offset + 1 : 0 ?>
                    - <?= min($offset + $batas, $totalData) ?>
                    dari <?= $totalData ?> siswa
                </small>

                <small class="text-muted">
                    Halaman <?= $halaman ?> dari <?= $totalHalaman ?>
                </small>
            </div>

            <!-- Tabel rekap -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Tahun Ajaran</th>
                            <th>Jumlah Pelanggaran</th>
                            <th>Total Poin</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (mysqli_num_rows($query) > 0): ?>

                        <?php $no = $offset + 1; ?>

                        <?php while ($data = mysqli_fetch_assoc($query)): ?>

                            <tr>
                                <td><?= $no++ ?></td>

                                <td>
                                    <?= htmlspecialchars($data['nis'] ?? '-') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($data['nama_siswa']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($data['nama_kelas']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($data['tahun_ajaran'] ?? '-') ?>
                                </td>

                                <td>
                                    <?= (int)$data['jumlah_pelanggaran'] ?> kali
                                </td>

                                <td>
                                    <span class="badge bg-primary">
                                        <?= (int)$data['total_poin'] ?> Poin
                                    </span>
                                </td>
                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Data rekap poin tidak ditemukan.
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalHalaman > 1): ?>

                <nav class="mt-3">
                    <ul class="pagination justify-content-end mb-0">

                        <li class="page-item <?= $halaman <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link"
                                href="?cari=<?= urlencode($cari) ?>&tahun=<?= urlencode($tahunFilter) ?>&halaman=<?= $halaman - 1 ?>">
                                Sebelumnya
                            </a>
                        </li>

                        <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>

                            <li class="page-item <?= $halaman == $i ? 'active' : '' ?>">
                                <a class="page-link"
                                    href="?cari=<?= urlencode($cari) ?>&tahun=<?= urlencode($tahunFilter) ?>&halaman=<?= $i ?>">
                                    <?= $i ?>
                                </a>
                            </li>

                        <?php endfor; ?>

                        <li class="page-item <?= $halaman >= $totalHalaman ? 'disabled' : '' ?>">
                            <a class="page-link"
                                href="?cari=<?= urlencode($cari) ?>&tahun=<?= urlencode($tahunFilter) ?>&halaman=<?= $halaman + 1 ?>">
                                Selanjutnya
                            </a>
                        </li>

                    </ul>
                </nav>

            <?php endif; ?>

        </div>
    </div>

</div>

</body>
</html>
