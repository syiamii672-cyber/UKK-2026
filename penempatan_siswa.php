
<?php
include "includes/cek_session.php";
include "config/koneksi.php";
// include "includes/sidebar.php";

if ($_SESSION['role'] != "admin") {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "penempatan";
include "includes/sidebar.php";
$kataKunci = trim($_GET['cari'] ?? '');
$batas = 10;
$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$keyword = "%" . $kataKunci . "%";

// Menghitung jumlah data
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
    $stmtJumlah, "ssss",
    $keyword, $keyword, $keyword, $keyword
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
$nomorAwal = $offset + 1;
$nomorAkhir = min($offset + $batas, $totalData);

// Mengambil data penempatan
$sqlData = "SELECT
                ks.id,
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
    $stmtData, "ssssii",
    $keyword, $keyword, $keyword, $keyword,
    $batas, $offset
);
mysqli_stmt_execute($stmtData);
$result = mysqli_stmt_get_result($stmtData);

// URL pagination mempertahankan pencarian
function urlHalamanPenempatan($nomorHalaman, $kataKunci) {
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
    <title>Penempatan Siswa</title>
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
            <h2>Penempatan Siswa</h2>
            <p class="text-muted mb-0">
                Kelola penempatan siswa berdasarkan kelas dan tahun ajaran.
            </p>
        </div>

        <a href="tambah_penempatan_siswa.php"
           class="btn btn-primary">
            + Tambah Penempatan
        </a>
    </div>

    <?php if (isset($_GET['pesan'])) { ?>
        <?php if ($_GET['pesan'] == 'tambah') { ?>
            <div class="alert alert-success">
                Penempatan siswa berhasil ditambahkan.
            </div>
        <?php } elseif ($_GET['pesan'] == 'edit') { ?>
            <div class="alert alert-success">
                Penempatan siswa berhasil diperbarui.
            </div>
        <?php } elseif ($_GET['pesan'] == 'hapus') { ?>
            <div class="alert alert-success">
                Penempatan siswa berhasil dihapus.
            </div>
        <?php } elseif ($_GET['pesan'] == 'duplikat') { ?>
            <div class="alert alert-warning">
                Siswa sudah ditempatkan di kelas pada tahun ajaran tersebut.
            </div>
        <?php } elseif ($_GET['pesan'] == 'gagal_hapus') { ?>
            <div class="alert alert-danger">
                Penempatan gagal dihapus karena masih terhubung
                dengan data lain.
            </div>
        <?php } elseif ($_GET['pesan'] == 'gagal') { ?>
            <div class="alert alert-danger">
                Penempatan siswa gagal disimpan.
            </div>
        <?php } ?>
    <?php } ?>

    <div class="card shadow-sm border-0">
        <div class="card-body">

            <form method="GET" action="penempatan_siswa.php"
                  class="row g-2 mb-3">
                <div class="col-md-6 col-lg-5">
                    <input type="search" name="cari"
                           class="form-control"
                           placeholder="Cari NIS, nama siswa, kelas, atau tahun ajaran..."
                           value="<?= htmlspecialchars($kataKunci, ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        Cari
                    </button>
                </div>

                <?php if ($kataKunci !== '') { ?>
                    <div class="col-auto">
                        <a href="penempatan_siswa.php"
                           class="btn btn-secondary">Reset</a>
                    </div>
                <?php } ?>
            </form>

            <div class="d-flex flex-wrap justify-content-between
                        align-items-center gap-2 mb-3">
                <small class="text-muted">
                    <?php if ($totalData > 0) { ?>
                        Menampilkan <?= $nomorAwal; ?>–<?= $nomorAkhir; ?>
                        dari <?= $totalData; ?> data penempatan
                    <?php } else { ?>
                        Tidak ada data penempatan yang ditemukan.
                    <?php } ?>
                </small>

                <small class="text-muted">
                    Maksimal <?= $batas; ?> data per halaman
                </small>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Tahun Ajaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($totalData > 0) { ?>
                        <?php while ($data = mysqli_fetch_assoc($result)) { ?>
                            <tr>
                                <td><?= $nomorAwal++; ?></td>
                                <td><?= htmlspecialchars($data['nis'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?= htmlspecialchars($data['nama_siswa'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <?= htmlspecialchars(
                                        $data['nama_kelas'] . ' - ' .
                                        $data['tingkat'] . ' ' .
                                        $data['jurusan'],
                                        ENT_QUOTES, 'UTF-8'
                                    ); ?>
                                </td>
                                <td><?= htmlspecialchars($data['nama_tahun_ajaran'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <a href="edit_penempatan_siswa.php?id=<?= (int)$data['id']; ?>"
                                       class="btn btn-sm btn-warning mb-1">
                                        Edit
                                    </a>

                                    <form action="hapus_penempatan_siswa.php"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Yakin ingin menghapus penempatan ini?');">
                                        <input type="hidden" name="id"
                                               value="<?= (int)$data['id']; ?>">
                                        <button type="submit"
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
                                Belum ada data penempatan siswa.
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalHalaman > 1) { ?>
                <nav aria-label="Navigasi halaman penempatan siswa"
                     class="mt-3">
                    <ul class="pagination justify-content-center flex-wrap">
                        <li class="page-item <?= $halaman <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link"
                               href="<?= htmlspecialchars(urlHalamanPenempatan(max(1, $halaman - 1), $kataKunci), ENT_QUOTES, 'UTF-8'); ?>">
                                Sebelumnya
                            </a>
                        </li>

                        <?php for ($i = 1; $i <= $totalHalaman; $i++) { ?>
                            <li class="page-item <?= $i == $halaman ? 'active' : ''; ?>">
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
</body>
</html>
