
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] !== "admin") {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "kelas";
$cari = trim($_GET['cari'] ?? '');

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

$dataJumlah = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmtJumlah)
);

$totalData = (int)$dataJumlah['total'];
$totalHalaman = max(1, (int)ceil($totalData / $batas));

if ($halaman > $totalHalaman) {
    $halaman = $totalHalaman;
}

$offset = ($halaman - 1) * $batas;

// Mengambil data kelas
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

// Link pagination
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
            background: #F1F5FB;
        }

        .konten-utama {
            margin-left: 220px !important;
            width: calc(100% - 220px) !important;
            min-height: 100vh;
            padding: 100px 24px 30px;
            box-sizing: border-box;
        }

        .konten-utama,
        .konten-utama * {
            box-sizing: border-box;
        }

        .konten-utama .judul-halaman {
            color: #173B67;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .konten-utama .deskripsi-halaman {
            color: #718096;
            font-size: 14px;
            margin-bottom: 0;
        }

        .konten-utama .btn-tambah {
            background: #245B96;
            border: 1px solid #245B96;
            color: #fff;
            border-radius: 8px;
            padding: 10px 16px;
            font-weight: 600;
        }

        .konten-utama .btn-tambah:hover {
            background: #173B67;
            border-color: #173B67;
            color: #fff;
        }

        .konten-utama .card-data {
            background: #fff;
            border: 1px solid #E5EAF2;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(23, 59, 103, 0.06);
        }

        .konten-utama .form-control {
            min-height: 42px;
            border: 1px solid #D8E1ED;
            border-radius: 8px;
            font-size: 14px;
        }

        .konten-utama .form-control:focus {
            border-color: #3779C5;
            box-shadow: 0 0 0 3px rgba(55, 121, 197, 0.12);
        }

        .konten-utama .btn-cari {
            background: #245B96;
            border-color: #245B96;
            color: #fff;
            border-radius: 8px;
            padding: 9px 17px;
        }

        .konten-utama .btn-cari:hover {
            background: #173B67;
            color: #fff;
        }

        .konten-utama .btn-reset {
            background: #E9EEF5;
            border-color: #E9EEF5;
            color: #40546B;
            border-radius: 8px;
            padding: 9px 17px;
        }

        .konten-utama .btn-reset:hover {
            background: #DCE5F0;
            color: #173B67;
        }

        .konten-utama .table {
            margin-bottom: 0;
            font-size: 14px;
            white-space: nowrap;
        }

        .konten-utama .table thead th {
            background: #173B67;
            color: #fff;
            font-weight: 600;
            padding: 13px 12px;
            border-color: #31577F;
        }

        .konten-utama .table tbody td {
            padding: 12px;
            color: #40546B;
            border-color: #E8EDF4;
        }

        .konten-utama .table tbody tr:hover {
            background: #F5F8FC;
        }

        .konten-utama .badge {
            padding: 7px 11px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .konten-utama .badge-aktif {
            background: #E3F5EA;
            color: #21834B;
        }

        .konten-utama .badge-nonaktif {
            background: #EDF0F4;
            color: #64748B;
        }

        .konten-utama .btn-edit {
            background: #FFF3D6;
            border: 1px solid #F5D58A;
            color: #946200;
            border-radius: 6px;
        }

        .konten-utama .btn-edit:hover {
            background: #F5D58A;
            color: #694600;
        }

        .konten-utama .btn-hapus {
            background: #FCE8E8;
            border: 1px solid #F3C4C4;
            color: #C03939;
            border-radius: 6px;
        }

        .konten-utama .btn-hapus:hover {
            background: #F3C4C4;
            color: #922B2B;
        }

        .konten-utama .alert {
            border: 0;
            border-radius: 9px;
            font-size: 14px;
        }

        .konten-utama .info-data {
            color: #718096;
            font-size: 13px;
        }

        .konten-utama .pagination {
            gap: 4px;
        }

        .konten-utama .page-link {
            border: 1px solid #DCE5F0;
            color: #245B96;
            border-radius: 6px !important;
            font-size: 13px;
        }

        .konten-utama .page-item.active .page-link {
            background: #245B96;
            border-color: #245B96;
            color: #fff;
        }

        .konten-utama .page-item.disabled .page-link {
            color: #A0AEC0;
        }

        @media (max-width: 767.98px) {
            .konten-utama {
                padding: 100px 15px 25px;
            }

            .konten-utama .judul-halaman {
                font-size: 22px;
            }
        }
    </style>
</head>

<body>

<?php include "includes/sidebar.php"; ?>
<?php include "includes/navbar.php"; ?>

<main class="konten-utama">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="judul-halaman">Kelola Kelas</h2>
            <p class="deskripsi-halaman">
                Kelola data kelas siswa yang terdaftar di sekolah.
            </p>
        </div>

        <a href="tambah_kelas.php" class="btn btn-tambah">
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

    <div class="card card-data">
        <div class="card-body p-3 p-md-4">

            <form method="GET" action="kelas.php" class="row g-2 mb-4">

                <div class="col-md-6 col-lg-5">
                    <input
                        type="search"
                        name="cari"
                        class="form-control"
                        placeholder="Cari nama, tingkat, atau jurusan..."
                        value="<?= htmlspecialchars($cari, ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-cari">
                        Cari
                    </button>
                </div>

                <?php if ($cari !== '') { ?>
                    <div class="col-auto">
                        <a href="kelas.php" class="btn btn-reset">
                            Reset
                        </a>
                    </div>
                <?php } ?>

            </form>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div class="info-data">
                    <?php if ($totalData > 0) { ?>
                        Menampilkan <?= $offset + 1; ?>–<?= min($offset + $batas, $totalData); ?>
                        dari <?= $totalData; ?> data kelas
                    <?php } else { ?>
                        Tidak ada data kelas
                    <?php } ?>
                </div>

                <div class="info-data">
                    Maksimal <?= $batas; ?> data per halaman
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">

                    <thead>
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
                                    <span class="badge badge-aktif">Aktif</span>
                                <?php } else { ?>
                                    <span class="badge badge-nonaktif">Tidak Aktif</span>
                                <?php } ?>
                            </td>

                            <td>
                                <a
                                    href="edit_kelas.php?id=<?= (int)$kelas['id']; ?>"
                                    class="btn btn-sm btn-edit mb-1">
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

                                    <button type="submit" class="btn btn-sm btn-hapus mb-1">
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
                            <td colspan="6" class="text-center py-4 text-muted">
                                Data kelas tidak ditemukan.
                            </td>
                        </tr>

                    <?php } ?>
                    </tbody>

                </table>
            </div>

            <?php if ($totalHalaman > 1) { ?>
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-4">

                    <small class="info-data">
                        Halaman <?= $halaman; ?> dari <?= $totalHalaman; ?>
                    </small>

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
                </div>
            <?php } ?>

        </div>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
