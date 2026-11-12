
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

$role = strtolower(trim($_SESSION['role'] ?? ''));

if ($role !== "admin") {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "jenis";

function e($nilai)
{
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

$cari = trim($_GET['cari'] ?? '');
$batas = 10;
$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$keyword = "%" . $cari . "%";

$where = "
    WHERE p.kode LIKE ?
       OR p.nama LIKE ?
       OR k.nama LIKE ?
";

$sqlCount = "
    SELECT COUNT(*) AS total
    FROM t_pelanggaran p
    JOIN t_pelanggaran_kategori k
        ON k.id = p.pelanggaran_kategori_id
    $where
";

$stmtCount = mysqli_prepare($koneksi, $sqlCount);

if (!$stmtCount) {
    die("Gagal menyiapkan perhitungan data: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmtCount, "sss", $keyword, $keyword, $keyword);
mysqli_stmt_execute($stmtCount);
$hasilCount = mysqli_stmt_get_result($stmtCount);
$totalData = (int)(mysqli_fetch_assoc($hasilCount)['total'] ?? 0);

$totalHalaman = max(1, (int)ceil($totalData / $batas));
$halaman = min($halaman, $totalHalaman);
$offset = ($halaman - 1) * $batas;

$sql = "
    SELECT
        p.id,
        p.kode,
        p.nama,
        p.poin,
        p.deskripsi,
        p.status_aktif,
        k.nama AS nama_kategori
    FROM t_pelanggaran p
    JOIN t_pelanggaran_kategori k
        ON k.id = p.pelanggaran_kategori_id
    $where
    ORDER BY p.id DESC
    LIMIT ? OFFSET ?
";

$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {
    die("Gagal menyiapkan data jenis pelanggaran: " . mysqli_error($koneksi));
}

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
$data = mysqli_stmt_get_result($stmt);

if (!$data) {
    die("Gagal mengambil data jenis pelanggaran: " . mysqli_error($koneksi));
}

function urlHalamanJenis($nomorHalaman, $cari)
{
    return "jenis_pelanggaran.php?" . http_build_query([
        "cari" => $cari,
        "halaman" => $nomorHalaman
    ]);
}

$pesan = $_GET['pesan'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Jenis Pelanggaran</title>

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
            font-weight: 700;
            margin-bottom: 5px;
        }

        .deskripsi-halaman,
        .info-data {
            color: #718096;
            font-size: 13px;
        }

        .card-data {
            background: #fff;
            border: 1px solid #E5EAF2;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(23, 59, 103, .06);
        }

        .btn-tambah,
        .btn-cari {
            background: #245B96;
            border: 1px solid #245B96;
            color: #fff;
            border-radius: 8px;
            font-weight: 600;
        }

        .btn-tambah {
            padding: 10px 16px;
        }

        .btn-tambah:hover,
        .btn-cari:hover {
            background: #173B67;
            border-color: #173B67;
            color: #fff;
        }

        .form-control {
            min-height: 42px;
            border: 1px solid #D8E1ED;
            border-radius: 8px;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: #3779C5;
            box-shadow: 0 0 0 3px rgba(55, 121, 197, .12);
        }

        .btn-reset {
            background: #E9EEF5;
            border: 1px solid #E9EEF5;
            color: #40546B;
            border-radius: 8px;
        }

        .btn-reset:hover {
            background: #DCE5F0;
            color: #173B67;
        }

        .table {
            margin-bottom: 0;
            font-size: 14px;
            white-space: nowrap;
        }

        .table thead th {
            background: #173B67;
            color: #fff;
            font-weight: 600;
            padding: 13px 12px;
            border-color: #31577F;
        }

        .table tbody td {
            padding: 12px;
            color: #40546B;
            border-color: #E8EDF4;
        }

        .table tbody tr:hover {
            background: #F5F8FC;
        }

        .badge-status {
            padding: 7px 11px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-aktif {
            background: #E3F5EA;
            color: #21834B;
        }

        .badge-nonaktif {
            background: #EDF0F4;
            color: #64748B;
        }

        .badge-kode {
            background: #EDF3FA;
            color: #245B96;
            border: 1px solid #D8E5F3;
            padding: 6px 9px;
            border-radius: 6px;
            font-size: 12px;
        }

        .badge-poin {
            background: #FFF3D6;
            color: #946200;
            padding: 7px 10px;
            border-radius: 20px;
            font-size: 12px;
        }

        .pagination {
            gap: 4px;
        }

        .page-link {
            border: 1px solid #DCE5F0;
            color: #245B96;
            border-radius: 6px !important;
            font-size: 13px;
        }

        .page-item.active .page-link {
            background: #245B96;
            border-color: #245B96;
            color: #fff;
        }

        .page-item.disabled .page-link {
            color: #A0AEC0;
        }

        .alert {
            border: 0;
            border-radius: 9px;
            font-size: 14px;
        }

        .info-poin {
            background: #EAF2FB;
            border: 1px solid #D7E6F7;
            border-radius: 10px;
            color: #40546B;
            font-size: 13px;
        }

        @media (max-width: 767.98px) {
            .konten-utama {
                padding: 100px 15px 25px;
            }

            .judul-halaman {
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
            <h2 class="judul-halaman">Jenis Pelanggaran</h2>
            <p class="deskripsi-halaman mb-0">
                Kelola jenis pelanggaran dan besaran pengurangan poin siswa.
            </p>
        </div>

        <a href="tambah_pelanggaran.php" class="btn btn-tambah">
            + Tambah Jenis Pelanggaran
        </a>
    </div>

    <div class="info-poin p-3 mb-4">
        <div class="fw-semibold mb-1" style="color: #173B67;">
            Informasi Sistem Poin
        </div>
        Setiap siswa memiliki saldo awal <strong>100 poin</strong>.
        Setiap pelanggaran akan mengurangi saldo berdasarkan besaran poin
        yang telah ditentukan. Saldo poin tidak ditampilkan di bawah 0.
    </div>

    <?php if ($pesan === 'tambah'): ?>
        <div class="alert alert-success">Jenis pelanggaran berhasil ditambahkan.</div>
    <?php elseif ($pesan === 'edit'): ?>
        <div class="alert alert-success">Jenis pelanggaran berhasil diperbarui.</div>
    <?php elseif ($pesan === 'hapus'): ?>
        <div class="alert alert-success">Jenis pelanggaran berhasil dihapus.</div>
    <?php elseif ($pesan === 'gagal_hapus'): ?>
        <div class="alert alert-danger">
            Jenis pelanggaran tidak dapat dihapus karena sudah digunakan
            dalam catatan pelanggaran siswa. Jika masih dibutuhkan untuk riwayat,
            nonaktifkan jenis pelanggaran melalui fitur edit.
        </div>
    <?php elseif ($pesan === 'gagal'): ?>
        <div class="alert alert-danger">Proses gagal. Periksa kembali data dan relasinya.</div>
    <?php endif; ?>

    <div class="card card-data">
        <div class="card-body p-3 p-md-4">

            <form method="GET" action="jenis_pelanggaran.php" class="row g-2 mb-4">
                <div class="col-md-6 col-lg-5">
                    <input
                        type="search"
                        name="cari"
                        class="form-control"
                        placeholder="Cari kode, nama, atau kategori..."
                        value="<?= e($cari); ?>"
                        autocomplete="off">
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-cari px-3">Cari</button>
                </div>

                <?php if ($cari !== ''): ?>
                    <div class="col-auto">
                        <a href="jenis_pelanggaran.php" class="btn btn-reset px-3">Reset</a>
                    </div>
                <?php endif; ?>
            </form>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div class="info-data">
                    <?php if ($totalData > 0): ?>
                        Menampilkan <?= $offset + 1; ?>–<?= min($offset + $batas, $totalData); ?>
                        dari <?= $totalData; ?> data jenis pelanggaran
                    <?php else: ?>
                        Tidak ada data jenis pelanggaran
                    <?php endif; ?>
                </div>

                <div class="info-data">Maksimal <?= $batas; ?> data per halaman</div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Kode</th>
                            <th>Nama Pelanggaran</th>
                            <th>Kategori</th>
                            <th>Pengurangan Poin</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (mysqli_num_rows($data) > 0): ?>
                        <?php $no = $offset + 1; ?>

                        <?php while ($row = mysqli_fetch_assoc($data)): ?>
                            <tr>
                                <td><?= $no++; ?></td>

                                <td>
                                    <span class="badge badge-kode">
                                        <?= e($row['kode']); ?>
                                    </span>
                                </td>

                                <td style="min-width: 220px; white-space: normal;">
                                    <div class="fw-semibold"><?= e($row['nama']); ?></div>
                                    <small class="text-secondary">
                                        <?= e($row['deskripsi'] ?: '-'); ?>
                                    </small>
                                </td>

                                <td><?= e($row['nama_kategori']); ?></td>

                                <td>
                                    <span class="badge badge-poin">
                                        -<?= (int)$row['poin']; ?> poin
                                    </span>
                                </td>

                                <td>
                                    <?php if ((int)$row['status_aktif'] === 1): ?>
                                        <span class="badge badge-status badge-aktif">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge badge-status badge-nonaktif">Tidak Aktif</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="edit_pelanggaran.php?id=<?= (int)$row['id']; ?>"
                                           class="btn btn-sm btn-warning">
                                            Edit
                                        </a>

                                        <form action="hapus_pelanggaran.php"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Yakin ingin menghapus jenis pelanggaran ini?');">
                                            <input type="hidden" name="id" value="<?= (int)$row['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <?= $cari !== '' ? 'Data jenis pelanggaran tidak ditemukan.' : 'Data jenis pelanggaran belum tersedia.'; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalHalaman > 1): ?>
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-4">
                    <small class="info-data">
                        Halaman <?= $halaman; ?> dari <?= $totalHalaman; ?>
                    </small>

                    <nav aria-label="Navigasi halaman jenis pelanggaran">
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?= $halaman <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link"
                                   href="<?= e(urlHalamanJenis(max(1, $halaman - 1), $cari)); ?>">
                                    Sebelumnya
                                </a>
                            </li>

                            <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>
                                <li class="page-item <?= $i === $halaman ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?= e(urlHalamanJenis($i, $cari)); ?>">
                                        <?= $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?= $halaman >= $totalHalaman ? 'disabled' : ''; ?>">
                                <a class="page-link"
                                   href="<?= e(urlHalamanJenis(min($totalHalaman, $halaman + 1), $cari)); ?>">
                                    Berikutnya
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>

        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
