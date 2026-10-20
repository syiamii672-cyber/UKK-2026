
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] != "admin") {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "tahun_ajaran";

// Pengaturan pencarian dan pagination
$cari = trim($_GET['cari'] ?? '');
$batas = 10;
$halaman = max(1, (int) ($_GET['halaman'] ?? 1));

// Kondisi pencarian
$where = "";
$keyword = "";

if ($cari !== '') {
    $where = " WHERE nama LIKE ?";
    $keyword = "%" . $cari . "%";
}

// Hitung jumlah seluruh data
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
$totalData = (int) $dataJumlah['total'];

mysqli_stmt_close($stmtJumlah);

// Hitung jumlah halaman
$totalHalaman = max(1, (int) ceil($totalData / $batas));

// Pastikan halaman yang diminta tersedia
if ($halaman > $totalHalaman) {
    $halaman = $totalHalaman;
}

$offset = ($halaman - 1) * $batas;

// Ambil data sesuai halaman
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
    mysqli_stmt_bind_param(
        $stmtData,
        "sii",
        $keyword,
        $batas,
        $offset
    );
} else {
    mysqli_stmt_bind_param(
        $stmtData,
        "ii",
        $batas,
        $offset
    );
}

mysqli_stmt_execute($stmtData);
$result = mysqli_stmt_get_result($stmtData);

if (!$result) {
    die("Gagal mengambil data tahun ajaran.");
}

// Membuat URL pagination dengan mempertahankan pencarian
function urlHalamanTahunAjaran($nomorHalaman, $cari)
{
    $parameter = [
        "halaman" => $nomorHalaman
    ];

    if ($cari !== '') {
        $parameter["cari"] = $cari;
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
            <h2 class="mb-1">Kelola Tahun Ajaran</h2>
            <p class="text-muted mb-0">
                Kelola data tahun ajaran sekolah.
            </p>
        </div>

        <a href="tambah_tahun_ajaran.php" class="btn btn-primary">
            + Tambah Tahun Ajaran
        </a>
    </div>

    <!-- Notifikasi -->
    <?php if (isset($_GET['pesan'])) { ?>

        <?php if ($_GET['pesan'] == 'tambah') { ?>
            <div class="alert alert-success">
                Tahun ajaran berhasil ditambahkan.
            </div>

        <?php } elseif ($_GET['pesan'] == 'edit') { ?>
            <div class="alert alert-success">
                Tahun ajaran berhasil diperbarui.
            </div>

        <?php } elseif ($_GET['pesan'] == 'hapus') { ?>
            <div class="alert alert-success">
                Tahun ajaran berhasil dihapus.
            </div>

        <?php } elseif ($_GET['pesan'] == 'tahun_digunakan') { ?>
            <div class="alert alert-warning">
                Tahun ajaran tidak dapat dihapus karena masih digunakan
                oleh data penempatan siswa atau penugasan wali kelas.
            </div>

        <?php } elseif ($_GET['pesan'] == 'gagal_hapus') { ?>
            <div class="alert alert-danger">
                Tahun ajaran gagal dihapus.
            </div>
        <?php } ?>

    <?php } ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <!-- Form pencarian -->
            <form action="tahun_ajaran.php" method="GET"
                  class="row g-2 mb-3">

                <div class="col-md-6 col-lg-5">
                    <input
                        type="text"
                        name="cari"
                        class="form-control"
                        placeholder="Cari tahun ajaran, contoh 2026/2027..."
                        value="<?= htmlspecialchars($cari, ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        Cari
                    </button>
                </div>

                <?php if ($cari !== '') { ?>
                    <div class="col-auto">
                        <a href="tahun_ajaran.php" class="btn btn-secondary">
                            Reset
                        </a>
                    </div>
                <?php } ?>

            </form>

            <!-- Informasi jumlah data -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <small class="text-muted">
                    Menampilkan
                    <?= $totalData > 0 ? $offset + 1 : 0 ?>
                    -
                    <?= min($offset + $batas, $totalData) ?>
                    dari <?= $totalData ?> data tahun ajaran
                </small>

                <small class="text-muted">
                    Maksimal 10 data per halaman
                </small>
            </div>

            <!-- Tabel data tahun ajaran -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">

                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Tahun Ajaran</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th>Status</th>
                            <th style="min-width: 140px;">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php
                    $no = $offset + 1;

                    if (mysqli_num_rows($result) > 0) {
                        while ($tahun = mysqli_fetch_assoc($result)) {
                    ?>

                        <tr>
                            <td><?= $no++ ?></td>

                            <td>
                                <?= htmlspecialchars($tahun['nama'], ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($tahun['tanggal_mulai'], ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($tahun['tanggal_selesai'], ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <td>
                                <?php if ($tahun['status_aktif'] == 1) { ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php } else { ?>
                                    <span class="badge bg-secondary">Nonaktif</span>
                                <?php } ?>
                            </td>

                            <td>
                                <a
                                    href="edit_tahun_ajaran.php?id=<?= (int) $tahun['id'] ?>"
                                    class="btn btn-sm btn-warning mb-1">
                                    Edit
                                </a>

                                <form
                                    action="hapus_tahun_ajaran.php"
                                    method="POST"
                                    class="d-inline"
                                    onsubmit="return confirm('Yakin ingin menghapus tahun ajaran ini?')">

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int) $tahun['id'] ?>">

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
                            <td colspan="6" class="text-center text-muted py-4">
                                <?php if ($cari !== '') { ?>
                                    Tahun ajaran yang dicari tidak ditemukan.
                                <?php } else { ?>
                                    Belum ada data tahun ajaran.
                                <?php } ?>
                            </td>
                        </tr>

                    <?php } ?>
                    </tbody>

                </table>
            </div>

            <!-- Navigasi pagination -->
            <?php if ($totalHalaman > 1) { ?>
                <nav class="mt-3" aria-label="Navigasi halaman tahun ajaran">
                    <ul class="pagination justify-content-center flex-wrap mb-0">

                        <!-- Tombol sebelumnya -->
                        <li class="page-item <?= $halaman <= 1 ? 'disabled' : '' ?>">
                            <a
                                class="page-link"
                                href="<?= htmlspecialchars(urlHalamanTahunAjaran(max(1, $halaman - 1), $cari), ENT_QUOTES, 'UTF-8') ?>">
                                Sebelumnya
                            </a>
                        </li>

                        <!-- Nomor halaman -->
                        <?php for ($i = 1; $i <= $totalHalaman; $i++) { ?>
                            <li class="page-item <?= $halaman == $i ? 'active' : '' ?>">
                                <a
                                    class="page-link"
                                    href="<?= htmlspecialchars(urlHalamanTahunAjaran($i, $cari), ENT_QUOTES, 'UTF-8') ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php } ?>

                        <!-- Tombol berikutnya -->
                        <li class="page-item <?= $halaman >= $totalHalaman ? 'disabled' : '' ?>">
                            <a
                                class="page-link"
                                href="<?= htmlspecialchars(urlHalamanTahunAjaran(min($totalHalaman, $halaman + 1), $cari), ENT_QUOTES, 'UTF-8') ?>">
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
