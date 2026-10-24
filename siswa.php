
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] != "admin") {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "siswa";

// Pengaturan pencarian dan pagination
$cari = trim($_GET['cari'] ?? '');
$batas = 10;
$halaman = max(1, (int) ($_GET['halaman'] ?? 1));

$keyword = "%" . $cari . "%";

// Hitung jumlah data siswa sesuai pencarian
$stmtJumlah = mysqli_prepare(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM t_siswa
     WHERE nis LIKE ?
        OR nisn LIKE ?
        OR nama LIKE ?"
);

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

$totalData = (int) $dataJumlah['total'];
$totalHalaman = max(1, (int) ceil($totalData / $batas));

mysqli_stmt_close($stmtJumlah);

// Pastikan halaman sesuai jumlah data
if ($halaman > $totalHalaman) {
    $halaman = $totalHalaman;
}

$offset = ($halaman - 1) * $batas;

// Ambil data siswa untuk halaman saat ini
$stmtData = mysqli_prepare(
    $koneksi,
    "SELECT id, nis, nisn, nama, jenis_kelamin,
            tanggal_lahir, alamat, status_aktif
     FROM t_siswa
     WHERE nis LIKE ?
        OR nisn LIKE ?
        OR nama LIKE ?
     ORDER BY id DESC
     LIMIT ? OFFSET ?"
);

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

// URL pagination tetap membawa kata pencarian
function urlHalamanSiswa($nomorHalaman, $cari)
{
    return '?' . http_build_query([
        'cari' => $cari,
        'halaman' => $nomorHalaman
    ]);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Data Siswa - Sistem Informasi Pelanggaran Siswa</title>

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

        @media (max-width: 767.98px) {
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

    <!-- Judul dan tombol tambah -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h2 class="mb-1">Data Siswa</h2>
            <p class="text-muted mb-0">Kelola data siswa</p>
        </div>

        <a href="tambah_siswa.php" class="btn btn-primary">
            + Tambah Siswa
        </a>
    </div>

    <!-- Notifikasi -->
    <?php if (isset($_GET['pesan'])) { ?>

        <?php if ($_GET['pesan'] == 'tambah') { ?>
            <div class="alert alert-success">
                Data siswa berhasil ditambahkan.
            </div>

        <?php } elseif ($_GET['pesan'] == 'edit') { ?>
            <div class="alert alert-success">
                Data siswa berhasil diperbarui.
            </div>

        <?php } elseif ($_GET['pesan'] == 'hapus') { ?>
            <div class="alert alert-success">
                Data siswa berhasil dihapus.
            </div>

        <?php } elseif ($_GET['pesan'] == 'gagal_hapus') { ?>
            <div class="alert alert-danger">
                Data siswa tidak dapat dihapus karena masih memiliki
                data kelas atau catatan pelanggaran.
            </div>
        <?php } ?>

    <?php } ?>

    <!-- Tabel dan pencarian -->
    <div class="card shadow-sm border-0">
        <div class="card-body">

            <!-- Form pencarian -->
            <form action="siswa.php" method="GET" class="row g-2 mb-3">

                <div class="col-md-6 col-lg-5">
                    <input
                        type="search"
                        name="cari"
                        class="form-control"
                        placeholder="Cari NIS, NISN, atau nama siswa..."
                        value="<?= htmlspecialchars($cari, ENT_QUOTES, 'UTF-8'); ?>"
                    >
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        Cari
                    </button>
                </div>

                <?php if ($cari !== '') { ?>
                    <div class="col-auto">
                        <a href="siswa.php" class="btn btn-secondary">
                            Reset
                        </a>
                    </div>
                <?php } ?>

            </form>

            <!-- Informasi jumlah data -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">

                <small class="text-muted">
                    <?php if ($totalData > 0) { ?>
                        Menampilkan <?= $nomorAwal; ?>–<?= $nomorAkhir; ?>
                        dari <?= $totalData; ?> data siswa
                    <?php } else { ?>
                        Tidak ada data siswa yang ditemukan.
                    <?php } ?>
                </small>

                <small class="text-muted">
                    Maksimal <?= $batas; ?> data per halaman
                </small>

            </div>

            <!-- Tabel data siswa -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">

                  
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>NIS</th>
                    <th>NISN</th>
                    <th>Nama</th>
                    <th>Jenis Kelamin</th>
                    <th>Alamat</th>
                    <th>Status</th>
                    <th style="min-width: 140px;">Aksi</th>
                </tr>
            </thead>


                    <tbody>

                    <?php if ($totalData > 0) { ?>

                        <?php while ($siswa = mysqli_fetch_assoc($result)) { ?>

                            <tr>
                                <td><?= $nomorAwal++; ?></td>

                                <td>
                                    <?= htmlspecialchars($siswa['nis'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($siswa['nisn'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($siswa['nama'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    <?php
                                    if ($siswa['jenis_kelamin'] == 'L') {
                                        echo 'Laki-laki';
                                    } elseif ($siswa['jenis_kelamin'] == 'P') {
                                        echo 'Perempuan';
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>

                                
                                    <td style="min-width: 200px; white-space: normal;">
                                        <?= htmlspecialchars($siswa['alamat'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                                    </td>


                                <td>
                                    <?php if ($siswa['status_aktif'] == 1) { ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php } else { ?>
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    <?php } ?>
                                </td>

                                <td>
                                    <a href="edit_siswa.php?id=<?= (int) $siswa['id']; ?>"
                                       class="btn btn-sm btn-warning mb-1">
                                        Edit
                                    </a>

                                    <form action="hapus_siswa.php"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Yakin ingin menghapus data siswa ini?');">

                                        <input type="hidden"
                                               name="id"
                                               value="<?= (int) $siswa['id']; ?>">

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
                            <td colspan="8" class="text-center text-muted py-4">
                                <?php if ($cari !== '') { ?>
                                    Siswa yang kamu cari tidak ditemukan.
                                <?php } else { ?>
                                    Belum ada data siswa.
                                <?php } ?>
                            </td>
                        </tr>

                    <?php } ?>

                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalHalaman > 1) { ?>

                <nav aria-label="Navigasi halaman data siswa" class="mt-3">

                    <ul class="pagination justify-content-center flex-wrap mb-0">

                        <li class="page-item <?= $halaman <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link"
                               href="<?= htmlspecialchars(urlHalamanSiswa(max(1, $halaman - 1), $cari), ENT_QUOTES, 'UTF-8'); ?>">
                                Sebelumnya
                            </a>
                        </li>

                        <?php for ($i = 1; $i <= $totalHalaman; $i++) { ?>

                            <li class="page-item <?= $i == $halaman ? 'active' : ''; ?>">
                                <a class="page-link"
                                   href="<?= htmlspecialchars(urlHalamanSiswa($i, $cari), ENT_QUOTES, 'UTF-8'); ?>">
                                    <?= $i; ?>
                                </a>
                            </li>

                        <?php } ?>

                        <li class="page-item <?= $halaman >= $totalHalaman ? 'disabled' : ''; ?>">
                            <a class="page-link"
                               href="<?= htmlspecialchars(urlHalamanSiswa(min($totalHalaman, $halaman + 1), $cari), ENT_QUOTES, 'UTF-8'); ?>">
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