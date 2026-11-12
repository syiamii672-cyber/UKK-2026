<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] != "admin") {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "tahun_ajaran";

$cari = trim($_GET['cari'] ?? '');
$batas = 10;
$halaman = max(1, (int)($_GET['halaman'] ?? 1));

$where = "";
$keyword = "%" . $cari . "%";

if ($cari !== '') {
    $where = " WHERE nama LIKE ?";
}

// Hitung jumlah data tahun ajaran
$sqlJumlah = "SELECT COUNT(*) AS total
              FROM t_tahun_ajaran" . $where;

$stmtJumlah = mysqli_prepare($koneksi, $sqlJumlah);

if (!$stmtJumlah) {
    die("Gagal menghitung data tahun ajaran.");
}

if ($cari !== '') {
    mysqli_stmt_bind_param($stmtJumlah, "s", $keyword);
}

mysqli_stmt_execute($stmtJumlah);
$hasilJumlah = mysqli_stmt_get_result($stmtJumlah);
$dataJumlah = mysqli_fetch_assoc($hasilJumlah);
$totalData = (int)$dataJumlah['total'];

mysqli_stmt_close($stmtJumlah);

// Pagination
$totalHalaman = max(1, (int)ceil($totalData / $batas));
$halaman = min($halaman, $totalHalaman);
$offset = ($halaman - 1) * $batas;

// Ambil data tahun ajaran
$sqlData = "SELECT id, nama, tanggal_mulai, tanggal_selesai, status_aktif
            FROM t_tahun_ajaran"
            . $where .
            " ORDER BY id DESC
              LIMIT ? OFFSET ?";

$stmtData = mysqli_prepare($koneksi, $sqlData);

if (!$stmtData) {
    die("Gagal menyiapkan data tahun ajaran.");
}

if ($cari !== '') {
    mysqli_stmt_bind_param($stmtData, "sii", $keyword, $batas, $offset);
} else {
    mysqli_stmt_bind_param($stmtData, "ii", $batas, $offset);
}

mysqli_stmt_execute($stmtData);
$result = mysqli_stmt_get_result($stmtData);

if (!$result) {
    die("Gagal mengambil data tahun ajaran.");
}

$nomorAwal = $offset + 1;
$nomorAkhir = min($offset + $batas, $totalData);

function urlHalamanTahunAjaran($nomorHalaman, $cari)
{
    $parameter = ['halaman' => $nomorHalaman];

    if ($cari !== '') {
        $parameter['cari'] = $cari;
    }

    return "tahun_ajaran.php?" . http_build_query($parameter);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kelola Tahun Ajaran - Sistem Informasi Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            margin: 0;
            background-color: #F1F5FB;
        }

        /* CSS dibatasi pada konten halaman, tidak menyentuh sidebar/navbar */
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

        .konten-utama .form-control {
            border-radius: 8px;
            border-color: #D8E1EE;
            font-size: 14px;
            min-height: 39px;
        }

        .konten-utama .form-control:focus {
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

        .btn-cari:hover {
            background-color: #173B67;
            border-color: #173B67;
        }

        .btn-reset {
            border-radius: 8px;
            font-size: 14px;
            padding: 8px 17px;
        }

        .konten-utama .table {
            margin-bottom: 0;
            font-size: 13px;
        }

        .konten-utama .table thead th {
            background-color: #173B67;
            color: #FFFFFF;
            font-weight: 600;
            padding: 13px 12px;
            white-space: nowrap;
            border-color: #31577F;
        }

        .konten-utama .table tbody td {
            padding: 12px;
            color: #35465C;
            border-color: #E5EAF1;
        }

        .konten-utama .table tbody tr:hover {
            background-color: #F4F8FD;
        }

        .konten-utama .badge {
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

        .konten-utama .pagination {
            gap: 4px;
        }

        .konten-utama .pagination .page-link {
            border: 1px solid #D8E1EE;
            border-radius: 6px;
            color: #245B96;
            font-size: 13px;
            padding: 7px 12px;
        }

        .konten-utama .pagination .page-item.active .page-link {
            background-color: #245B96;
            border-color: #245B96;
            color: #FFFFFF;
        }

        .konten-utama .pagination .page-item.disabled .page-link {
            color: #A0AEC0;
        }

        .konten-utama .alert {
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
            <h2 class="mb-1">Kelola Tahun Ajaran</h2>
            <p class="mb-0">Kelola data tahun ajaran sekolah.</p>
        </div>

        <a href="tambah_tahun_ajaran.php" class="btn btn-primary btn-tambah">
            + Tambah Tahun Ajaran
        </a>
    </div>

    <!-- NOTIFIKASI -->
    <?php if (isset($_GET['pesan'])): ?>

        <?php if ($_GET['pesan'] == 'tambah'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Tahun ajaran berhasil ditambahkan.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

        <?php elseif ($_GET['pesan'] == 'edit'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Tahun ajaran berhasil diperbarui.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

        <?php elseif ($_GET['pesan'] == 'hapus'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Tahun ajaran berhasil dihapus.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

        <?php elseif ($_GET['pesan'] == 'tahun_digunakan'): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                Tahun ajaran tidak dapat dihapus karena masih digunakan
                oleh data penempatan siswa atau penugasan wali kelas.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

        <?php elseif ($_GET['pesan'] == 'gagal_hapus'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                Tahun ajaran gagal dihapus.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

    <?php endif; ?>

    <!-- DATA TAHUN AJARAN -->
    <div class="card card-data">
        <div class="card-body">

            <!-- PENCARIAN -->
            <form action="tahun_ajaran.php" method="GET" class="row g-2 mb-3">

                <div class="col-md-6 col-lg-5">
                    <input
                        type="search"
                        name="cari"
                        class="form-control"
                        placeholder="Cari tahun ajaran, contoh 2026/2027..."
                        value="<?= htmlspecialchars($cari, ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-cari">
                        Cari
                    </button>
                </div>

                <?php if ($cari !== ''): ?>
                    <div class="col-auto">
                        <a href="tahun_ajaran.php" class="btn btn-secondary btn-reset">
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
                        dari <strong><?= $totalData; ?></strong> data tahun ajaran
                    <?php else: ?>
                        Tidak ada data tahun ajaran yang ditemukan.
                    <?php endif; ?>
                </small>

                <small class="text-muted">
                    Maksimal <?= $batas; ?> data per halaman
                </small>

            </div>

            <!-- TABEL TAHUN AJARAN -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">

                    <thead>
                        <tr>
                            <th width="55">No</th>
                            <th>Nama Tahun Ajaran</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th width="120">Status</th>
                            <th width="145">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php
                    $nomor = $nomorAwal;

                    if (mysqli_num_rows($result) > 0):
                        while ($tahun = mysqli_fetch_assoc($result)):
                    ?>

                        <tr>
                            <td><?= $nomor++; ?></td>

                            <td class="fw-semibold">
                                <?= htmlspecialchars($tahun['nama'], ENT_QUOTES, 'UTF-8'); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($tahun['tanggal_mulai'], ENT_QUOTES, 'UTF-8'); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($tahun['tanggal_selesai'], ENT_QUOTES, 'UTF-8'); ?>
                            </td>

                            <td>
                                <?php if ($tahun['status_aktif'] == 1): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Nonaktif</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="d-flex flex-wrap gap-1">

                                    <a
                                        href="edit_tahun_ajaran.php?id=<?= (int)$tahun['id']; ?>"
                                        class="btn btn-warning btn-aksi">
                                        Edit
                                    </a>

                                    <form
                                        action="hapus_tahun_ajaran.php"
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Yakin ingin menghapus tahun ajaran ini?');">

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int)$tahun['id']; ?>">

                                        <button
                                            type="submit"
                                            class="btn btn-danger btn-aksi">
                                            Hapus
                                        </button>

                                    </form>

                                </div>
                            </td>
                        </tr>

                    <?php
                        endwhile;
                    else:
                    ?>

                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <?= $cari !== ''
                                    ? 'Tahun ajaran yang dicari tidak ditemukan.'
                                    : 'Belum ada data tahun ajaran.'; ?>
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            <?php if ($totalHalaman > 1): ?>

                <nav class="mt-4" aria-label="Navigasi halaman tahun ajaran">
                    <ul class="pagination justify-content-center flex-wrap mb-0">

                        <li class="page-item <?= $halaman <= 1 ? 'disabled' : ''; ?>">
                            <a
                                class="page-link"
                                href="<?= htmlspecialchars(urlHalamanTahunAjaran(max(1, $halaman - 1), $cari), ENT_QUOTES, 'UTF-8'); ?>">
                                Sebelumnya
                            </a>
                        </li>

                        <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>

                            <li class="page-item <?= $halaman == $i ? 'active' : ''; ?>">
                                <a
                                    class="page-link"
                                    href="<?= htmlspecialchars(urlHalamanTahunAjaran($i, $cari), ENT_QUOTES, 'UTF-8'); ?>">
                                    <?= $i; ?>
                                </a>
                            </li>

                        <?php endfor; ?>

                        <li class="page-item <?= $halaman >= $totalHalaman ? 'disabled' : ''; ?>">
                            <a
                                class="page-link"
                                href="<?= htmlspecialchars(urlHalamanTahunAjaran(min($totalHalaman, $halaman + 1), $cari), ENT_QUOTES, 'UTF-8'); ?>">
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