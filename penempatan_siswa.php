
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] !== "admin") {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "penempatan";
$kataKunci = trim($_GET['cari'] ?? '');

$batas = 10;
$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$keyword = "%" . $kataKunci . "%";

$sqlJumlah = "SELECT COUNT(*) AS total
              FROM t_kelas_siswa ks
              JOIN t_siswa s ON ks.siswa_id = s.id
              JOIN t_kelas k ON ks.kelas_id = k.id
              JOIN t_tahun_ajaran ta ON ks.tahun_ajaran_id = ta.id
              WHERE s.nis LIKE ?
                 OR s.nama LIKE ?
                 OR k.nama LIKE ?
                 OR ta.nama LIKE ?";

$stmtJumlah = mysqli_prepare($koneksi, $sqlJumlah);
mysqli_stmt_bind_param(
    $stmtJumlah,
    "ssss",
    $keyword,
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
$halaman = min($halaman, $totalHalaman);
$offset = ($halaman - 1) * $batas;

$sqlData = "SELECT
                ks.id,
                ks.tanggal_mulai,
                ks.tanggal_selesai,
                ks.status_aktif,
                s.nis,
                s.nama AS nama_siswa,
                k.nama AS nama_kelas,
                k.tingkat,
                k.jurusan,
                ta.nama AS nama_tahun_ajaran
            FROM t_kelas_siswa ks
            JOIN t_siswa s ON ks.siswa_id = s.id
            JOIN t_kelas k ON ks.kelas_id = k.id
            JOIN t_tahun_ajaran ta ON ks.tahun_ajaran_id = ta.id
            WHERE s.nis LIKE ?
               OR s.nama LIKE ?
               OR k.nama LIKE ?
               OR ta.nama LIKE ?
            ORDER BY ks.id DESC
            LIMIT ? OFFSET ?";

$stmtData = mysqli_prepare($koneksi, $sqlData);
mysqli_stmt_bind_param(
    $stmtData,
    "ssssii",
    $keyword,
    $keyword,
    $keyword,
    $keyword,
    $batas,
    $offset
);
mysqli_stmt_execute($stmtData);
$result = mysqli_stmt_get_result($stmtData);

function urlHalamanPenempatan($nomorHalaman, $kataKunci)
{
    return "penempatan_siswa.php?" . http_build_query([
        'cari' => $kataKunci,
        'halaman' => $nomorHalaman
    ]);
}

function tanggalPenempatan($tanggal)
{
    if (empty($tanggal) || $tanggal === '0000-00-00') {
        return '-';
    }

    return date('d-m-Y', strtotime($tanggal));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Penempatan Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

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

        .judul-halaman {
            color: #173B67;
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .deskripsi-halaman {
            color: #718096;
            margin-bottom: 0;
        }

        .btn-tambah {
            background: #245B96;
            border: 1px solid #245B96;
            color: white;
            border-radius: 8px;
            padding: 10px 16px;
            font-weight: 600;
        }

        .btn-tambah:hover {
            background: #173B67;
            border-color: #173B67;
            color: white;
        }

        .card-data {
            border: 0;
            border-radius: 13px;
            box-shadow: 0 4px 18px rgba(23, 59, 103, .07);
        }

        .konten-utama .form-control {
            border: 1px solid #D8E1ED;
            border-radius: 8px;
            padding: 10px 12px;
        }

        .konten-utama .form-control:focus {
            border-color: #3779C5;
            box-shadow: 0 0 0 .2rem rgba(55, 121, 197, .13);
        }

        .btn-cari {
            background: #245B96;
            color: white;
            border: 0;
            border-radius: 8px;
            padding: 9px 17px;
        }

        .btn-cari:hover {
            background: #173B67;
            color: white;
        }

        .btn-reset {
            background: #E8EDF4;
            color: #40536B;
            border: 0;
            border-radius: 8px;
            padding: 9px 17px;
        }

        .konten-utama .table {
            margin-bottom: 0;
            white-space: nowrap;
        }

        .konten-utama .table thead th {
            background: #173B67;
            color: white;
            font-weight: 600;
            border-color: #31577F;
            padding: 13px 12px;
        }

        .konten-utama .table tbody td {
            padding: 12px;
            border-color: #E5EAF1;
        }

        .konten-utama .table tbody tr:hover {
            background: #F5F8FC;
        }

        .konten-utama .pagination .page-link {
            color: #245B96;
            border-color: #DCE5EF;
        }

        .konten-utama .pagination .active .page-link {
            background: #245B96;
            border-color: #245B96;
            color: white;
        }

        .konten-utama .alert {
            border: 0;
            border-radius: 9px;
        }

        @media (max-width: 575.98px) {
            .konten-utama {
                padding: 100px 15px 20px;
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
            <h2 class="judul-halaman">Penempatan Siswa</h2>
            <p class="deskripsi-halaman">
                Kelola penempatan siswa berdasarkan kelas dan tahun ajaran.
            </p>
        </div>

        <a href="tambah_penempatan_siswa.php" class="btn btn-tambah">
            + Tambah Penempatan
        </a>
    </div>

    <?php if (isset($_GET['pesan'])) { ?>
        <?php if ($_GET['pesan'] === 'tambah') { ?>
            <div class="alert alert-success">Penempatan siswa berhasil ditambahkan.</div>
        <?php } elseif ($_GET['pesan'] === 'edit') { ?>
            <div class="alert alert-success">Penempatan siswa berhasil diperbarui.</div>
        <?php } elseif ($_GET['pesan'] === 'hapus') { ?>
            <div class="alert alert-success">Penempatan siswa berhasil dihapus.</div>
        <?php } elseif ($_GET['pesan'] === 'duplikat') { ?>
            <div class="alert alert-warning">Siswa sudah memiliki penempatan pada tahun ajaran tersebut.</div>
        <?php } elseif ($_GET['pesan'] === 'gagal_hapus') { ?>
            <div class="alert alert-danger">Penempatan gagal dihapus karena masih terhubung dengan data lain.</div>
        <?php } elseif ($_GET['pesan'] === 'gagal') { ?>
            <div class="alert alert-danger">Penempatan siswa gagal disimpan.</div>
        <?php } ?>
    <?php } ?>

    <div class="card card-data">
        <div class="card-body p-4">

            <form method="GET" action="penempatan_siswa.php" class="row g-2 mb-3">
                <div class="col-md-6 col-lg-5">
                    <input
                        type="search"
                        name="cari"
                        class="form-control"
                        placeholder="Cari NIS, nama siswa, kelas, atau tahun ajaran..."
                        value="<?= htmlspecialchars($kataKunci, ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-cari">Cari</button>
                    <?php if ($kataKunci !== '') { ?>
                        <a href="penempatan_siswa.php" class="btn btn-reset">Reset</a>
                    <?php } ?>
                </div>
            </form>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <small class="text-muted">
                    <?php if ($totalData > 0) { ?>
                        Menampilkan <?= $offset + 1; ?>–<?= min($offset + $batas, $totalData); ?>
                        dari <?= $totalData; ?> data penempatan
                    <?php } else { ?>
                        Tidak ada data penempatan yang ditemukan.
                    <?php } ?>
                </small>

                <small class="text-muted">Maksimal <?= $batas; ?> data per halaman</small>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Tahun Ajaran</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if ($totalData > 0) { ?>
                        <?php $no = $offset + 1; ?>
                        <?php while ($data = mysqli_fetch_assoc($result)) { ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= htmlspecialchars($data['nis'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?= htmlspecialchars($data['nama_siswa'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <?= htmlspecialchars(
                                        $data['nama_kelas'] . ' - ' . $data['tingkat'] . ' ' . $data['jurusan'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>
                                </td>
                                <td><?= htmlspecialchars($data['nama_tahun_ajaran'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?= tanggalPenempatan($data['tanggal_mulai']); ?></td>
                                <td><?= tanggalPenempatan($data['tanggal_selesai']); ?></td>
                                <td>
                                    <?php if ((int)$data['status_aktif'] === 1) { ?>
                                        <span class="badge rounded-pill bg-success">Aktif</span>
                                    <?php } else { ?>
                                        <span class="badge rounded-pill bg-secondary">Tidak Aktif</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <a
                                        href="edit_penempatan_siswa.php?id=<?= (int)$data['id']; ?>"
                                        class="btn btn-sm btn-warning mb-1">
                                        Edit
                                    </a>

                                    <form
                                        action="hapus_penempatan_siswa.php"
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Yakin ingin menghapus penempatan ini?');">
                                        <input type="hidden" name="id" value="<?= (int)$data['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger mb-1">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                Belum ada data penempatan siswa.
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalHalaman > 1) { ?>
                <nav class="mt-4" aria-label="Navigasi halaman penempatan siswa">
                    <ul class="pagination pagination-sm justify-content-center flex-wrap mb-0">
                        <li class="page-item <?= $halaman <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link"
                               href="<?= htmlspecialchars(urlHalamanPenempatan(max(1, $halaman - 1), $kataKunci), ENT_QUOTES, 'UTF-8'); ?>">
                                Sebelumnya
                            </a>
                        </li>

                        <?php for ($i = 1; $i <= $totalHalaman; $i++) { ?>
                            <li class="page-item <?= $i === $halaman ? 'active' : ''; ?>">
                                <a class="page-link"
                                   href="<?= htmlspecialchars(urlHalamanPenempatan($i, $kataKunci), ENT_QUOTES, 'UTF-8'); ?>">
                                    <?= $i; ?>
                                </a>
                            </li>
                        <?php } ?>

                        <li class="page-item <?= $halaman >= $totalHalaman ? 'disabled' : ''; ?>">
                            <a class="page-link"
                               href="<?= htmlspecialchars(urlHalamanPenempatan(min($totalHalaman, $halaman + 1), $kataKunci), ENT_QUOTES, 'UTF-8'); ?>">
                                Berikutnya
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php } ?>

        </div>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
