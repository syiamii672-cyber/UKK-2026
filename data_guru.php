
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] != "admin") {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "guru";

$kataKunci = trim($_GET['cari'] ?? '');
$batas = 10;
$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$keyword = "%" . $kataKunci . "%";

// Hitung jumlah data guru
$sqlJumlah = "SELECT COUNT(*) AS total
              FROM t_guru
              WHERE nip LIKE ?
                 OR nama LIKE ?
                 OR email LIKE ?";

$stmtJumlah = mysqli_prepare($koneksi, $sqlJumlah);
mysqli_stmt_bind_param($stmtJumlah, "sss", $keyword, $keyword, $keyword);
mysqli_stmt_execute($stmtJumlah);

$hasilJumlah = mysqli_stmt_get_result($stmtJumlah);
$dataJumlah = mysqli_fetch_assoc($hasilJumlah);
$totalData = (int)$dataJumlah['total'];

mysqli_stmt_close($stmtJumlah);

// Pagination
$totalHalaman = max(1, (int)ceil($totalData / $batas));
$halaman = min($halaman, $totalHalaman);
$offset = ($halaman - 1) * $batas;

// Ambil data guru
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

function urlHalamanGuru($nomorHalaman, $kataKunci)
{
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
    <title>Kelola Guru</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        /* CSS HALAMAN */
        body {
            margin: 0;
            background-color: #F1F5FB;
            font-family: Arial, sans-serif;
        }

        /* CSS HANYA BERLAKU PADA KONTEN UTAMA */
        .konten-utama,
        .konten-utama * {
            box-sizing: border-box;
        }

        .konten-utama {
            position: relative;
            display: block;
            margin-left: 220px !important;
            width: calc(100% - 220px) !important;
            min-width: 0;
            min-height: 100vh;
            padding: 100px 24px 30px;
        }

        .judul-halaman h2 {
            color: #173B67;
            font-size: 25px;
            font-weight: 700;
        }

        .judul-halaman p {
            color: #718096;
            font-size: 14px;
        }

        .btn-tambah {
            background-color: #245B96;
            border-color: #245B96;
            border-radius: 8px;
            padding: 9px 16px;
            font-size: 14px;
            font-weight: 600;
        }

        .btn-tambah:hover {
            background-color: #173B67;
            border-color: #173B67;
        }

        .card-data {
            border: none;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(23, 59, 103, 0.07);
        }

        .card-data .card-body {
            padding: 22px;
        }

        .form-control {
            border-radius: 8px;
            border-color: #D8E1EE;
            font-size: 14px;
            min-height: 39px;
        }

        .form-control:focus {
            border-color: #3779C5;
            box-shadow: 0 0 0 0.2rem rgba(55, 121, 197, 0.12);
        }

        .btn-cari {
            background-color: #245B96;
            border-color: #245B96;
            border-radius: 8px;
            font-size: 14px;
            padding: 8px 17px;
        }

        .btn-reset {
            border-radius: 8px;
            font-size: 14px;
            padding: 8px 17px;
        }

        .table {
            margin-bottom: 0;
            font-size: 13px;
        }

        .table thead th {
            background-color: #173B67;
            color: #FFFFFF;
            font-weight: 600;
            padding: 13px 12px;
            white-space: nowrap;
            border-color: #31577F;
        }

        .table tbody td {
            padding: 12px;
            color: #35465C;
            border-color: #E5EAF1;
        }

        .table tbody tr:hover {
            background-color: #F4F8FD;
        }

        .badge {
            font-size: 11px;
            font-weight: 600;
            padding: 6px 10px;
            border-radius: 6px;
        }

        .btn-aksi {
            border-radius: 6px;
            font-size: 12px;
            padding: 5px 11px;
            font-weight: 600;
        }

        .pagination {
            gap: 4px;
        }

        .pagination .page-link {
            border: 1px solid #D8E1EE;
            border-radius: 6px;
            color: #245B96;
            font-size: 13px;
            padding: 7px 12px;
        }

        .pagination .page-item.active .page-link {
            background-color: #245B96;
            border-color: #245B96;
            color: #FFFFFF;
        }

        .pagination .page-item.disabled .page-link {
            color: #A0AEC0;
        }

        .alert {
            border: none;
            border-radius: 9px;
            font-size: 14px;
        }

        @media (max-width: 767.98px) {
            .konten-utama {
                margin-left: 220px !important;
                width: calc(100% - 220px) !important;
                padding: 100px 12px 20px;
            }

            .card-data .card-body {
                padding: 14px;
            }

            .judul-halaman h2 {
                font-size: 21px;
            }
        }
    </style>
</head>

<body>

<?php include "includes/sidebar.php"; ?>
<?php include "includes/navbar.php"; ?>

<main class="konten-utama">

    <!-- JUDUL HALAMAN -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="judul-halaman">
            <h2 class="mb-1">Kelola Guru</h2>
            <p class="mb-0">Kelola data guru dan akun guru yang terdaftar.</p>
        </div>

        <a href="tambah_guru.php" class="btn btn-primary btn-tambah">
            + Tambah Guru
        </a>
    </div>

    <!-- NOTIFIKASI -->
    <?php if (isset($_GET['pesan'])): ?>

        <?php if ($_GET['pesan'] == 'tambah'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Data guru berhasil ditambahkan.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

        <?php elseif ($_GET['pesan'] == 'edit'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Data guru berhasil diperbarui.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

        <?php elseif ($_GET['pesan'] == 'hapus'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Data guru berhasil dihapus.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

        <?php elseif ($_GET['pesan'] == 'gagal_hapus'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                Data guru gagal dihapus.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

        <?php elseif ($_GET['pesan'] == 'guru_digunakan'): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                Data guru tidak dapat dihapus karena masih memiliki riwayat
                pelanggaran atau masih terhubung dengan data lain.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

    <?php endif; ?>

    <!-- DATA GURU -->
    <div class="card card-data">
        <div class="card-body">

            <!-- PENCARIAN -->
            <form method="GET" action="data_guru.php" class="row g-2 mb-3">

                <div class="col-md-6 col-lg-5">
                    <input
                        type="search"
                        name="cari"
                        class="form-control"
                        placeholder="Cari NIP, nama, atau email guru..."
                        value="<?= htmlspecialchars($kataKunci, ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-cari">
                        Cari
                    </button>
                </div>

                <?php if ($kataKunci !== ''): ?>
                    <div class="col-auto">
                        <a href="data_guru.php" class="btn btn-secondary btn-reset">
                            Reset
                        </a>
                    </div>
                <?php endif; ?>

            </form>

            <!-- JUMLAH DATA -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">

                <small class="text-muted">
                    <?php if ($totalData > 0): ?>
                        Menampilkan
                        <strong><?= $nomorAwal; ?>–<?= $nomorAkhir; ?></strong>
                        dari <strong><?= $totalData; ?></strong> data guru
                    <?php else: ?>
                        Tidak ada data guru yang ditemukan.
                    <?php endif; ?>
                </small>

                <small class="text-muted">
                    Maksimal <?= $batas; ?> data per halaman
                </small>

            </div>

            <!-- TABEL -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">

                    <thead>
                        <tr>
                            <th width="55">No</th>
                            <th>NIP</th>
                            <th>Nama Guru</th>
                            <th>Email</th>
                            <th width="120">Status</th>
                            <th width="145">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if ($totalData > 0): ?>

                        <?php while ($guru = mysqli_fetch_assoc($result)): ?>

                            <tr>
                                <td><?= $nomorAwal++; ?></td>

                                <td>
                                    <?= htmlspecialchars($guru['nip'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td class="fw-semibold">
                                    <?= htmlspecialchars($guru['nama'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($guru['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    <?php if ($guru['status_aktif'] == 1): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <div class="d-flex flex-wrap gap-1">

                                        <a
                                            href="edit_guru.php?id=<?= (int)$guru['id']; ?>"
                                            class="btn btn-warning btn-aksi">
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
                                                class="btn btn-danger btn-aksi">
                                                Hapus
                                            </button>

                                        </form>

                                    </div>
                                </td>
                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <?= $kataKunci !== ''
                                    ? 'Data guru tidak ditemukan.'
                                    : 'Belum ada data guru.'; ?>
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            <?php if ($totalHalaman > 1): ?>

                <nav class="mt-4" aria-label="Navigasi halaman data guru">
                    <ul class="pagination justify-content-center flex-wrap">

                        <li class="page-item <?= $halaman <= 1 ? 'disabled' : ''; ?>">
                            <a
                                class="page-link"
                                href="<?= htmlspecialchars(urlHalamanGuru(max(1, $halaman - 1), $kataKunci), ENT_QUOTES, 'UTF-8'); ?>">
                                Sebelumnya
                            </a>
                        </li>

                        <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>

                            <li class="page-item <?= $i == $halaman ? 'active' : ''; ?>">
                                <a
                                    class="page-link"
                                    href="<?= htmlspecialchars(urlHalamanGuru($i, $kataKunci), ENT_QUOTES, 'UTF-8'); ?>">
                                    <?= $i; ?>
                                </a>
                            </li>

                        <?php endfor; ?>

                        <li class="page-item <?= $halaman >= $totalHalaman ? 'disabled' : ''; ?>">
                            <a
                                class="page-link"
                                href="<?= htmlspecialchars(urlHalamanGuru(min($totalHalaman, $halaman + 1), $kataKunci), ENT_QUOTES, 'UTF-8'); ?>">
                                Berikutnya
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
