
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

$role = strtolower(trim($_SESSION['role'] ?? ''));

if ($role !== "guru") {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "tindakan";

// Pengaturan pencarian dan pagination
$cari = trim($_GET['cari'] ?? '');
$statusFilter = $_GET['status'] ?? '';
$batas = 10;
$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$offset = ($halaman - 1) * $batas;

$pesan = "";

// Proses simpan tindakan
if (isset($_POST['simpan'])) {

    $id = (int)($_POST['id'] ?? 0);
    $tindakan = trim($_POST['tindakan'] ?? '');
    $status = $_POST['status'] ?? '';

    $statusValid = [
        "Belum ditangani",
        "Sedang diproses",
        "Selesai"
    ];

    if ($id <= 0 || $tindakan === "" ||
        !in_array($status, $statusValid, true)) {

        $pesan = "Data tindakan tidak valid.";

    } else {

        // Pastikan data masih berstatus belum selesai
        $stmt = mysqli_prepare($koneksi, "
            UPDATE t_pelanggaran_siswa
            SET tindakan = ?, status = ?
            WHERE id = ?
            AND status IN ('Belum ditangani', 'Sedang diproses')
        ");

        mysqli_stmt_bind_param(
            $stmt,
            "ssi",
            $tindakan,
            $status,
            $id
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);

            header("Location: tindakan.php?berhasil=1");
            exit;

        } else {
            $pesan = "Gagal memperbarui tindakan.";
            mysqli_stmt_close($stmt);
        }
    }
}

// Halaman Tindakan hanya menampilkan data yang belum selesai
$where = "
    WHERE status IN ('Belum ditangani', 'Sedang diproses')
";

// Pencarian
if ($cari !== '') {

    $kata = mysqli_real_escape_string($koneksi, $cari);

    $where .= " AND (
        nama_siswa LIKE '%$kata%'
        OR nama_kelas LIKE '%$kata%'
        OR nama_pelanggaran LIKE '%$kata%'
        OR nama_guru LIKE '%$kata%'
        OR siswa_id IN (
            SELECT id
            FROM t_siswa
            WHERE nis LIKE '%$kata%'
        )
    )";
}

// Filter status yang masih aktif
if (in_array($statusFilter, [
    "Belum ditangani",
    "Sedang diproses"
], true)) {

    $statusAman = mysqli_real_escape_string(
        $koneksi,
        $statusFilter
    );

    $where .= " AND status = '$statusAman'";
}

// Hitung seluruh data
$queryJumlah = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total
    FROM t_pelanggaran_siswa
    $where
");

if (!$queryJumlah) {
    die("Gagal menghitung data: " . mysqli_error($koneksi));
}

$totalData = (int)mysqli_fetch_assoc($queryJumlah)['total'];
$totalHalaman = max(1, (int)ceil($totalData / $batas));

if ($halaman > $totalHalaman) {
    $halaman = $totalHalaman;
}

$offset = ($halaman - 1) * $batas;

// Ambil data sesuai halaman
$query = mysqli_query($koneksi, "
    SELECT *
    FROM t_pelanggaran_siswa
    $where
    ORDER BY tanggal DESC, id DESC
    LIMIT $batas OFFSET $offset
");

if (!$query) {
    die("Gagal mengambil data: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tindakan Pelanggaran</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">

<?php include "includes/sidebar.php"; ?>

<div style="margin-left:220px; padding:30px;">

    <div class="mb-4">
        <h3 class="fw-bold">Tindakan Pelanggaran</h3>
        <p class="text-muted">
            Kelola tindak lanjut pelanggaran siswa yang belum selesai ditangani.
        </p>
    </div>

    <?php if (isset($_GET['berhasil'])): ?>
        <div class="alert alert-success">
            Tindakan berhasil diperbarui!
        </div>
    <?php endif; ?>

    <?php if ($pesan !== ''): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($pesan) ?>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <div class="row g-2 mb-3">

                <div class="col-md-7">
                    <form method="GET">
                        <input
                            type="text"
                            name="cari"
                            class="form-control"
                            placeholder="Cari nama siswa, NIS, kelas, pelanggaran, atau guru..."
                            value="<?= htmlspecialchars($cari) ?>"
                        >

                        <input type="hidden" name="status"
                               value="<?= htmlspecialchars($statusFilter) ?>">
                    </form>
                </div>

                <div class="col-md-5">
                    <form method="GET">

                        <input type="hidden" name="cari"
                               value="<?= htmlspecialchars($cari) ?>">

                        <select name="status"
                                class="form-select"
                                onchange="this.form.submit()">

                            <option value="">Semua Status Aktif</option>

                            <option value="Belum ditangani"
                                <?= $statusFilter === "Belum ditangani" ? "selected" : "" ?>>
                                Belum ditangani
                            </option>

                            <option value="Sedang diproses"
                                <?= $statusFilter === "Sedang diproses" ? "selected" : "" ?>>
                                Sedang diproses
                            </option>

                        </select>
                    </form>
                </div>

            </div>

            <div class="d-flex justify-content-between mb-3">
                <small class="text-muted">
                    Menampilkan <?= $totalData > 0 ? $offset + 1 : 0 ?>
                    - <?= min($offset + $batas, $totalData) ?>
                    dari <?= $totalData ?> data
                </small>

                <small class="text-muted">
                    <?= $halaman ?> / <?= $totalHalaman ?> halaman
                </small>
            </div>

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th>Pelanggaran</th>
                            <th>Poin</th>
                            <th>Guru Pencatat</th>
                            <th>Tindakan dan Status</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (mysqli_num_rows($query) > 0): ?>

                        <?php $no = $offset + 1; ?>

                        <?php while ($data = mysqli_fetch_assoc($query)): ?>

                            <tr>
                                <td><?= $no++ ?></td>

                                <td>
                                    <?= date('d-m-Y', strtotime($data['tanggal'])) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($data['nama_siswa']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($data['nama_kelas']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($data['nama_pelanggaran']) ?>
                                </td>

                                <td>
                                    <span class="badge bg-primary">
                                        <?= (int)$data['poin'] ?> Poin
                                    </span>
                                </td>

                                <td>
                                    <?= htmlspecialchars($data['nama_guru']) ?>
                                </td>

                                <td style="min-width:240px;">

                                    <form method="POST">

                                        <input type="hidden" name="id"
                                               value="<?= (int)$data['id'] ?>">

                                        <textarea
                                            name="tindakan"
                                            class="form-control form-control-sm mb-2"
                                            rows="2"
                                            placeholder="Isi tindakan..."
                                            required><?= htmlspecialchars($data['tindakan']) ?></textarea>

                                        <select name="status"
                                                class="form-select form-select-sm mb-2"
                                                required>

                                            <option value="Belum ditangani"
                                                <?= $data['status'] === "Belum ditangani" ? "selected" : "" ?>>
                                                Belum ditangani
                                            </option>

                                            <option value="Sedang diproses"
                                                <?= $data['status'] === "Sedang diproses" ? "selected" : "" ?>>
                                                Sedang diproses
                                            </option>

                                            <option value="Selesai">
                                                Selesai
                                            </option>

                                        </select>

                                        <button type="submit"
                                                name="simpan"
                                                class="btn btn-sm btn-primary w-100">
                                            Simpan Tindakan
                                        </button>

                                    </form>

                                </td>
                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="8"
                                class="text-center text-muted py-4">
                                Tidak ada pelanggaran yang perlu ditangani.
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>
                </table>
            </div>

            <?php if ($totalHalaman > 1): ?>

                <nav class="mt-3">
                    <ul class="pagination justify-content-end mb-0">

                        <li class="page-item <?= $halaman <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link"
                               href="?cari=<?= urlencode($cari) ?>&status=<?= urlencode($statusFilter) ?>&halaman=<?= max(1, $halaman - 1) ?>">
                                Sebelumnya
                            </a>
                        </li>

                        <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>

                            <li class="page-item <?= $halaman === $i ? 'active' : '' ?>">
                                <a class="page-link"
                                   href="?cari=<?= urlencode($cari) ?>&status=<?= urlencode($statusFilter) ?>&halaman=<?= $i ?>">
                                    <?= $i ?>
                                </a>
                            </li>

                        <?php endfor; ?>

                        <li class="page-item <?= $halaman >= $totalHalaman ? 'disabled' : '' ?>">
                            <a class="page-link"
                               href="?cari=<?= urlencode($cari) ?>&status=<?= urlencode($statusFilter) ?>&halaman=<?= min($totalHalaman, $halaman + 1) ?>">
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
