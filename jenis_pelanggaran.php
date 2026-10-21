<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] != "admin") {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "jenis";

// Pengaturan pencarian dan pagination
$cari = trim($_GET['cari'] ?? '');
$batas = 10;
$halaman = max(1, (int)($_GET['halaman'] ?? 1));

$where = "";
$kata = "%$cari%";

if ($cari !== '') {
    $where = " WHERE p.kode LIKE ?
               OR p.nama LIKE ?
               OR k.nama LIKE ?";
}

// Menghitung total data
if ($cari !== '') {
    $sqlCount = "SELECT COUNT(*) AS total
                 FROM t_pelanggaran p
                 JOIN t_pelanggaran_kategori k
                   ON k.id = p.pelanggaran_kategori_id
                 $where";

    $stmt = mysqli_prepare($koneksi, $sqlCount);
    mysqli_stmt_bind_param($stmt, "sss", $kata, $kata, $kata);
    mysqli_stmt_execute($stmt);
    $hasilCount = mysqli_stmt_get_result($stmt);
} else {
    $hasilCount = mysqli_query(
        $koneksi,
        "SELECT COUNT(*) AS total FROM t_pelanggaran"
    );
}

if (!$hasilCount) {
    die("Gagal menghitung data jenis pelanggaran.");
}

$totalData = (int) mysqli_fetch_assoc($hasilCount)['total'];
$totalHalaman = max(1, (int) ceil($totalData / $batas));
$halaman = min($halaman, $totalHalaman);
$offset = ($halaman - 1) * $batas;

// Mengambil data jenis pelanggaran
$sql = "SELECT
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
        LIMIT $batas OFFSET $offset";

if ($cari !== '') {
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $kata, $kata, $kata);
    mysqli_stmt_execute($stmt);
    $data = mysqli_stmt_get_result($stmt);
} else {
    $data = mysqli_query($koneksi, $sql);
}

if (!$data) {
    die("Gagal mengambil data jenis pelanggaran.");
}

$pesan = $_GET['pesan'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Jenis Pelanggaran - Sistem Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-light">

<?php include "includes/sidebar.php"; ?>

<main style="margin-left:220px; padding:30px; min-height:100vh;">

    <div class="mb-4">
        <h3 class="fw-bold mb-1">Jenis Pelanggaran</h3>
        <p class="text-secondary mb-0">
            Kelola jenis pelanggaran, kategori, dan poin siswa.
        </p>
    </div>

    <!-- Pesan proses -->
    <?php if ($pesan == 'tambah'): ?>
        <div class="alert alert-success alert-dismissible fade show">
            Jenis pelanggaran berhasil ditambahkan.
            <button type="button" class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($pesan == 'edit'): ?>
        <div class="alert alert-success alert-dismissible fade show">
            Jenis pelanggaran berhasil diperbarui.
            <button type="button" class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($pesan == 'hapus'): ?>
        <div class="alert alert-success alert-dismissible fade show">
            Jenis pelanggaran berhasil dihapus.
            <button type="button" class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($pesan == 'gagal_hapus'): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            Jenis pelanggaran tidak dapat dihapus karena sudah digunakan
            dalam catatan pelanggaran siswa.
            <button type="button" class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($pesan == 'gagal'): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            Proses gagal. Periksa kembali data dan relasinya.
            <button type="button" class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">

            <div class="d-flex flex-wrap justify-content-between
                        align-items-center gap-3 mb-4">

                <!-- Pencarian -->
                <form method="GET" action="jenis_pelanggaran.php"
                      class="d-flex flex-wrap gap-2">

                    <input type="text"
                           name="cari"
                           class="form-control"
                           placeholder="Cari kode, nama, kategori..."
                           value="<?= htmlspecialchars($cari, ENT_QUOTES, 'UTF-8') ?>">

                    <button type="submit" class="btn btn-primary">
                        Cari
                    </button>

                    <a href="jenis_pelanggaran.php"
                       class="btn btn-outline-secondary">
                        Reset
                    </a>
                </form>

                <!-- Tambah data -->
                <a href="tambah_pelanggaran.php"
                   class="btn btn-primary">
                    + Tambah Jenis Pelanggaran
                </a>
            </div>

            <!-- Tabel data -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">

                    <thead class="table-light">
                        <tr>
                            <th>No.</th>
                            <th>Kode</th>
                            <th>Nama Pelanggaran</th>
                            <th>Kategori</th>
                            <th>Poin</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (mysqli_num_rows($data) > 0): ?>
                        <?php $no = $offset + 1; ?>

                        <?php while ($row = mysqli_fetch_assoc($data)): ?>
                            <tr>
                                <td><?= $no++ ?></td>

                                <td>
                                    <span class="badge text-bg-light border">
                                        <?= htmlspecialchars($row['kode'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="fw-semibold">
                                        <?= htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>

                                    <small class="text-secondary">
                                        <?= htmlspecialchars($row['deskripsi'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                    </small>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['nama_kategori'], ENT_QUOTES, 'UTF-8') ?>
                                </td>

                                <td>
                                    <span class="badge text-bg-primary">
                                        <?= (int) $row['poin'] ?> poin
                                    </span>
                                </td>

                                <td>
                                    <?php if ((int) $row['status_aktif'] === 1): ?>
                                        <span class="badge text-bg-success">
                                            Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="badge text-bg-secondary">
                                            Nonaktif
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <div class="d-flex gap-1">

                                        <a href="edit_pelanggaran.php?id=<?= (int) $row['id'] ?>"
                                           class="btn btn-sm btn-warning">
                                            Edit
                                        </a>

                                        <form action="hapus_pelanggaran.php"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Yakin ingin menghapus jenis pelanggaran ini?');">

                                            <input type="hidden"
                                                   name="id"
                                                   value="<?= (int) $row['id'] ?>">

                                            <button type="submit"
                                                    class="btn btn-sm btn-danger">
                                                Hapus
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="7"
                                class="text-center text-secondary py-4">
                                <?php if ($cari !== ''): ?>
                                    Data jenis pelanggaran tidak ditemukan.
                                <?php else: ?>
                                    Data jenis pelanggaran belum tersedia.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Informasi data dan pagination -->
            <div class="d-flex flex-wrap justify-content-between
                        align-items-center gap-3 mt-3">

                <small class="text-secondary">
                    Total <?= $totalData ?> data jenis pelanggaran
                </small>

                <?php if ($totalData > 0): ?>
                    <nav aria-label="Navigasi halaman">
                        <ul class="pagination pagination-sm mb-0">

                            <li class="page-item <?= $halaman <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link"
                                   href="?cari=<?= urlencode($cari) ?>&halaman=<?= max(1, $halaman - 1) ?>">
                                    Sebelumnya
                                </a>
                            </li>

                            <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>
                                <li class="page-item <?= $i == $halaman ? 'active' : '' ?>">
                                    <a class="page-link"
                                       href="?cari=<?= urlencode($cari) ?>&halaman=<?= $i ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?= $halaman >= $totalHalaman ? 'disabled' : '' ?>">
                                <a class="page-link"
                                   href="?cari=<?= urlencode($cari) ?>&halaman=<?= min($totalHalaman, $halaman + 1) ?>">
                                    Berikutnya
                                </a>
                            </li>

                        </ul>
                    </nav>
                <?php endif; ?>

            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>