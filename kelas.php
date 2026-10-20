```php
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] !== "admin") {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "kelas";
$cari = trim($_GET['cari'] ?? '');

// Pengaturan pagination
$batas = 10;
$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$keyword = "%" . $cari . "%";

// Menghitung jumlah seluruh data kelas
$sqlJumlah = "SELECT COUNT(*) AS total
              FROM t_kelas
              WHERE nama LIKE ?
                 OR tingkat LIKE ?
                 OR jurusan LIKE ?";

$stmtJumlah = mysqli_prepare($koneksi, $sqlJumlah);
mysqli_stmt_bind_param(
    $stmtJumlah,
    "sss",
    $keyword,
    $keyword,
    $keyword
);
mysqli_stmt_execute($stmtJumlah);

$hasilJumlah = mysqli_stmt_get_result($stmtJumlah);
$dataJumlah = mysqli_fetch_assoc($hasilJumlah);

$totalData = (int)$dataJumlah['total'];
$totalHalaman = max(1, (int)ceil($totalData / $batas));

// Jika halaman melebihi jumlah halaman, arahkan ke halaman terakhir
if ($halaman > $totalHalaman) {
    $halaman = $totalHalaman;
}

$offset = ($halaman - 1) * $batas;

// Mengambil data kelas sesuai halaman
$sql = "SELECT id, nama, tingkat, jurusan, status_aktif
        FROM t_kelas
        WHERE nama LIKE ?
           OR tingkat LIKE ?
           OR jurusan LIKE ?
        ORDER BY id DESC
        LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($koneksi, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "sssii",
    $keyword,
    $keyword,
    $keyword,
    $batas,
    $offset
);

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Membuat link halaman sambil mempertahankan pencarian
function urlHalamanKelas($nomorHalaman, $cari)
{
    return "kelas.php?" . http_build_query([
        "cari" => $cari,
        "halaman" => $nomorHalaman
    ]);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Kelola Kelas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            margin: 0;
            background: #f8f9fa;
        }

        .konten-utama {
            margin-left: 220px;
            width: calc(100% - 220px);
            min-height: 100vh;
            padding: 24px;
            box-sizing: border-box;
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

    <div class="d-flex flex-wrap justify-content-between
                align-items-center gap-3 mb-4">

        <div>
            <h2>Kelola Kelas</h2>
            <p class="text-muted mb-0">
                Kelola data kelas siswa
            </p>
        </div>

        <a href="tambah_kelas.php" class="btn btn-primary">
            + Tambah Kelas
        </a>
    </div>

    <?php if (isset($_GET['pesan'])) { ?>

        <?php if ($_GET['pesan'] === 'tambah') { ?>
            <div class="alert alert-success">
                Data kelas berhasil ditambahkan.
            </div>

        <?php } elseif ($_GET['pesan'] === 'edit') { ?>
            <div class="alert alert-success">
                Data kelas berhasil diperbarui.
            </div>

        <?php } elseif ($_GET['pesan'] === 'hapus') { ?>
            <div class="alert alert-success">
                Data kelas berhasil dihapus.
            </div>

        <?php } elseif ($_GET['pesan'] === 'kelas_digunakan') { ?>
            <div class="alert alert-warning">
                Kelas tidak dapat dihapus karena masih terhubung
                dengan data siswa, wali kelas, atau pelanggaran.
            </div>

        <?php } elseif ($_GET['pesan'] === 'gagal_hapus') { ?>
            <div class="alert alert-danger">
                Data kelas gagal dihapus.
            </div>
        <?php } ?>

    <?php } ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <form method="GET" action="kelas.php"
                  class="row g-2 mb-3">

                <div class="col-md-6">
                    <input
                        type="search"
                        name="cari"
                        class="form-control"
                        placeholder="Cari nama, tingkat, atau jurusan..."
                        value="<?= htmlspecialchars($cari, ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        Cari
                    </button>

                    <a href="kelas.php" class="btn btn-secondary">
                        Reset
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Kelas</th>
                            <th>Tingkat</th>
                            <th>Jurusan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php
                    // Nomor data mengikuti halaman yang sedang dibuka
                    $no = $offset + 1;

                    if (mysqli_num_rows($result) > 0) {
                        while ($kelas = mysqli_fetch_assoc($result)) {
                    ?>

                        <tr>
                            <td><?= $no++; ?></td>

                            <td>
                                <?= htmlspecialchars($kelas['nama'], ENT_QUOTES, 'UTF-8'); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($kelas['tingkat'], ENT_QUOTES, 'UTF-8'); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($kelas['jurusan'], ENT_QUOTES, 'UTF-8'); ?>
                            </td>

                            <td>
                                <?php if ($kelas['status_aktif'] == 1) { ?>
                                    <span class="badge bg-success">
                                        Aktif
                                    </span>
                                <?php } else { ?>
                                    <span class="badge bg-secondary">
                                        Tidak Aktif
                                    </span>
                                <?php } ?>
                            </td>

                            <td>
                                <a
                                    href="edit_kelas.php?id=<?= (int)$kelas['id']; ?>"
                                    class="btn btn-sm btn-warning mb-1">
                                    Edit
                                </a>

                                <form
                                    action="hapus_kelas.php"
                                    method="POST"
                                    class="d-inline"
                                    onsubmit="return confirm('Yakin ingin menghapus kelas ini?');">

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int)$kelas['id']; ?>">

                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-danger mb-1">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>

                    <?php
                        }
                    } else {
                    ?>

                        <tr>
                            <td colspan="6" class="text-center py-4">
                                Data kelas tidak ditemukan.
                            </td>
                        </tr>

                    <?php } ?>
                    </tbody>

                </table>
            </div>

            <!-- Informasi jumlah data -->
            <div class="d-flex flex-wrap justify-content-between
                        align-items-center gap-2 mt-3">

                <small class="text-muted">
                    <?php if ($totalData > 0) { ?>
                        Menampilkan <?= $offset + 1; ?>–<?= min($offset + $batas, $totalData); ?>
                        dari <?= $totalData; ?> data kelas
                    <?php } else { ?>
                        Tidak ada data kelas
                    <?php } ?>
                </small>

                <!-- Navigasi halaman -->
                <?php if ($totalHalaman > 1) { ?>
                    <nav aria-label="Navigasi halaman kelas">
                        <ul class="pagination pagination-sm mb-0">

                            <li class="page-item <?= ($halaman <= 1) ? 'disabled' : ''; ?>">
                                <a
                                    class="page-link"
                                    href="<?= htmlspecialchars(urlHalamanKelas(max(1, $halaman - 1), $cari), ENT_QUOTES, 'UTF-8'); ?>">
                                    Sebelumnya
                                </a>
                            </li>

                            <?php for ($i = 1; $i <= $totalHalaman; $i++) { ?>
                                <li class="page-item <?= ($i === $halaman) ? 'active' : ''; ?>">
                                    <a
                                        class="page-link"
                                        href="<?= htmlspecialchars(urlHalamanKelas($i, $cari), ENT_QUOTES, 'UTF-8'); ?>">
                                        <?= $i; ?>
                                    </a>
                                </li>
                            <?php } ?>

                            <li class="page-item <?= ($halaman >= $totalHalaman) ? 'disabled' : ''; ?>">
                                <a
                                    class="page-link"
                                    href="<?= htmlspecialchars(urlHalamanKelas(min($totalHalaman, $halaman + 1), $cari), ENT_QUOTES, 'UTF-8'); ?>">
                                    Berikutnya
                                </a>
                            </li>

                        </ul>
                    </nav>
                <?php } ?>

            </div>

        </div>
    </div>

</main>

</body>
</html>
```