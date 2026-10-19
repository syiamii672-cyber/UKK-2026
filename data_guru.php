
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] != "admin") {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "guru";

// Pengaturan pencarian dan pagination
$kataKunci = trim($_GET['cari'] ?? '');
$batas = 20;
$halaman = max(1, (int)($_GET['halaman'] ?? 1));

// Hitung jumlah data sesuai pencarian
$sqlJumlah = "SELECT COUNT(*) AS total
              FROM t_guru
              WHERE nip LIKE ?
                 OR nama LIKE ?
                 OR email LIKE ?";

$stmtJumlah = mysqli_prepare($koneksi, $sqlJumlah);

$keyword = "%" . $kataKunci . "%";

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

// Pastikan halaman tidak melebihi jumlah halaman
if ($halaman > $totalHalaman) {
    $halaman = $totalHalaman;
}

$offset = ($halaman - 1) * $batas;

// Ambil data untuk halaman yang sedang dibuka
$sqlData = "SELECT id, nip, nama, email, status_aktif
            FROM t_guru
            WHERE nip LIKE ?
               OR nama LIKE ?
               OR email LIKE ?
            ORDER BY id DESC
            LIMIT ? OFFSET ?";

$stmtData = mysqli_prepare($koneksi, $sqlData);

mysqli_stmt_bind_param(
    $stmtData,
    "sssii",
    $keyword,
    $keyword,
    $keyword,
    $batas,
    $offset
);

mysqli_stmt_execute($stmtData);
$result = mysqli_stmt_get_result($stmtData);

$nomorAwal = $offset + 1;
$nomorAkhir = min($offset + $batas, $totalData);

// Membuat URL pagination sambil mempertahankan pencarian
function urlHalamanGuru($nomorHalaman, $kataKunci) {
    return '?' . http_build_query([
        'cari' => $kataKunci,
        'halaman' => $nomorHalaman
    ]);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Data Guru</title>

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
            <h2>Data Guru</h2>
            <p class="text-muted mb-0">Kelola data guru</p>
        </div>

        <a href="tambah_guru.php" class="btn btn-primary">
            + Tambah Guru
        </a>

    </div>

    <?php if (isset($_GET['pesan'])) { ?>

        <?php if ($_GET['pesan'] == 'tambah') { ?>
            <div class="alert alert-success">
                Data guru berhasil ditambahkan.
            </div>

        <?php } elseif ($_GET['pesan'] == 'edit') { ?>
            <div class="alert alert-success">
                Data guru berhasil diperbarui.
            </div>

        <?php } elseif ($_GET['pesan'] == 'hapus') { ?>
            <div class="alert alert-success">
                Data guru berhasil dihapus.
            </div>

        <?php } elseif ($_GET['pesan'] == 'gagal_hapus') { ?>
            <div class="alert alert-danger">
                Data guru gagal dihapus. Data mungkin masih digunakan
                oleh tabel lain.
            </div>
        <?php } ?>

    <?php } ?>

    <div class="card shadow-sm border-0">
        <div class="card-body">

            <!-- Form pencarian -->
            <form method="GET" action="data_guru.php"
                  class="row g-2 mb-3">

                <div class="col-md-6 col-lg-5">
                    <input
                        type="search"
                        name="cari"
                        class="form-control"
                        placeholder="Cari NIP, nama, atau email guru..."
                        value="<?= htmlspecialchars($kataKunci, ENT_QUOTES, 'UTF-8'); ?>"
                    >
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Cari
                    </button>
                </div>

                <?php if ($kataKunci !== '') { ?>
                    <div class="col-auto">
                        <a href="data_guru.php"
                           class="btn btn-secondary">
                            Reset
                        </a>
                    </div>
                <?php } ?>

            </form>

            <!-- Informasi jumlah data -->
            <div class="d-flex flex-wrap justify-content-between
                        align-items-center gap-2 mb-3">

                <small class="text-muted">
                    <?php if ($totalData > 0) { ?>
                        Menampilkan <?= $nomorAwal; ?>–<?= $nomorAkhir; ?>
                        dari <?= $totalData; ?> data guru
                    <?php } else { ?>
                        Tidak ada data guru yang ditemukan.
                    <?php } ?>
                </small>

                <small class="text-muted">
                    Maksimal <?= $batas; ?> data per halaman
                </small>

            </div>

            <!-- Tabel data guru -->
            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>NIP</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if ($totalData > 0) { ?>

                        <?php while ($guru = mysqli_fetch_assoc($result)) { ?>

                            <tr>
                                <td><?= $nomorAwal++; ?></td>

                                <td>
                                    <?= htmlspecialchars($guru['nip'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($guru['nama'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($guru['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    <?php if ($guru['status_aktif'] == 1) { ?>
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
                                        href="edit_guru.php?id=<?= (int)$guru['id']; ?>"
                                        class="btn btn-sm btn-warning mb-1">
                                        Edit
                                    </a>

                                    <form
                                        action="hapus_guru.php"
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Yakin ingin menghapus data guru ini?');">

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int)$guru['id']; ?>">

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-danger mb-1">
                                            Hapus
                                        </button>

                                    </form>
                                </td>
                            </tr>

                        <?php } ?>

                    <?php } else { ?>

                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <?php if ($kataKunci !== '') { ?>
                                    Data guru tidak ditemukan.
                                <?php } else { ?>
                                    Belum ada data guru.
                                <?php } ?>
                            </td>
                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

            <!-- Pagination -->
            <?php if ($totalHalaman > 1) { ?>

                <nav aria-label="Navigasi halaman data guru"
                     class="mt-3">

                    <ul class="pagination justify-content-center flex-wrap">

                        <!-- Tombol sebelumnya -->
                        <li class="page-item <?= $halaman <= 1 ? 'disabled' : ''; ?>">

                            <a class="page-link"
                               href="<?= htmlspecialchars(urlHalamanGuru(max(1, $halaman - 1), $kataKunci), ENT_QUOTES, 'UTF-8'); ?>">
                                Sebelumnya
                            </a>

                        </li>

                        <!-- Nomor halaman -->
                        <?php for ($i = 1; $i <= $totalHalaman; $i++) { ?>

                            <li class="page-item <?= $i == $halaman ? 'active' : ''; ?>">

                                <a class="page-link"
                                   href="<?= htmlspecialchars(urlHalamanGuru($i, $kataKunci), ENT_QUOTES, 'UTF-8'); ?>">
                                    <?= $i; ?>
                                </a>

                            </li>

                        <?php } ?>

                        <!-- Tombol berikutnya -->
                        <li class="page-item <?= $halaman >= $totalHalaman ? 'disabled' : ''; ?>">

                            <a class="page-link"
                               href="<?= htmlspecialchars(urlHalamanGuru(min($totalHalaman, $halaman + 1), $kataKunci), ENT_QUOTES, 'UTF-8'); ?>">
                                Berikutnya
                            </a>

                        </li>

                    </ul>

                </nav>

            <?php } ?>

        </div>
    </div>

</main>

</body>
</html>
