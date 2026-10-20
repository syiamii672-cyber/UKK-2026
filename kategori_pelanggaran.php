
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] !== "admin") {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "kategori_pelanggaran";

$cari = trim($_GET['cari'] ?? '');
$batas = 10;
$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$kata = "%$cari%";

// Hitung jumlah data
$sqlJumlah = "SELECT COUNT(*) AS total
              FROM t_pelanggaran_kategori
              WHERE nama LIKE ?";
$stmtJumlah = mysqli_prepare($koneksi, $sqlJumlah);
mysqli_stmt_bind_param($stmtJumlah, "s", $kata);
mysqli_stmt_execute($stmtJumlah);
$hasilJumlah = mysqli_stmt_get_result($stmtJumlah);
$totalData = mysqli_fetch_assoc($hasilJumlah)['total'];

$totalHalaman = max(1, ceil($totalData / $batas));
$halaman = min($halaman, $totalHalaman);
$offset = ($halaman - 1) * $batas;

// Ambil data sesuai halaman
$sql = "SELECT * FROM t_pelanggaran_kategori
        WHERE nama LIKE ?
        ORDER BY id DESC
        LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, "sii", $kata, $batas, $offset);
mysqli_stmt_execute($stmt);
$dataKategori = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kategori Pelanggaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            background: #f4f7fc;
            color: #172554;
        }

        .konten {
            margin-left: 220px;
            padding: 28px;
        }

        .kartu {
            background: white;
            padding: 22px;
            border-radius: 14px;
            box-shadow: 0 3px 12px rgba(15, 35, 75, 0.05);
        }

        .btn-primary {
            background: #2563eb;
            border-color: #2563eb;
        }

        .pagination .page-link {
            color: #2563eb;
        }

        .pagination .active .page-link {
            background: #2563eb;
            border-color: #2563eb;
            color: white;
        }

        @media (max-width: 576px) {
            .konten {
                padding: 16px;
            }
        }
    </style>
</head>
<body>

<?php include "includes/sidebar.php"; ?>

<main class="konten">
    <h3 class="fw-bold mb-1">Kategori Pelanggaran</h3>
    <p class="text-secondary mb-4">Kelola kategori pelanggaran siswa.</p>

    <div class="kartu">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">

            <form method="GET" class="d-flex gap-2">
                <input
                    type="text"
                    name="cari"
                    class="form-control form-control-sm"
                    placeholder="Cari kategori..."
                    value="<?= htmlspecialchars($cari) ?>"
                >
                <button type="submit" class="btn btn-primary btn-sm">
                    Cari
                </button>

                <?php if ($cari !== ''): ?>
                    <a href="kategori_pelanggaran.php"
                       class="btn btn-outline-secondary btn-sm">
                        Reset
                    </a>
                <?php endif; ?>
            </form>

            <a href="tambah_kategori_pelanggaran.php"
               class="btn btn-primary btn-sm">
                + Tambah Kategori
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama Kategori</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                        <th style="min-width: 125px;">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (mysqli_num_rows($dataKategori) > 0): ?>
                        <?php $no = $offset + 1; ?>
                        <?php while ($row = mysqli_fetch_assoc($dataKategori)): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['nama']) ?></td>
                                <td><?= htmlspecialchars($row['deskripsi']) ?></td>
                                <td>
                                    <?php if ($row['status_aktif'] == 1): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="edit_kategori_pelanggaran.php?id=<?= $row['id'] ?>"
                                       class="btn btn-sm btn-warning">
                                        Edit
                                    </a>

                                    <form action="hapus_kategori_pelanggaran.php"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
                                        <input type="hidden" name="id"
                                               value="<?= $row['id'] ?>">
                                        <button type="submit"
                                                class="btn btn-sm btn-danger">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-4">
                                Data kategori belum tersedia.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalData > 0): ?>
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <small class="text-secondary">
                    Menampilkan <?= $offset + 1 ?>–<?= min($offset + $batas, $totalData) ?>
                    dari <?= $totalData ?> data
                </small>

                <?php if ($totalHalaman > 1): ?>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?= ($halaman <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link"
                                   href="?cari=<?= urlencode($cari) ?>&halaman=<?= $halaman - 1 ?>">
                                    Sebelumnya
                                </a>
                            </li>

                            <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>
                                <li class="page-item <?= ($i == $halaman) ? 'active' : '' ?>">
                                    <a class="page-link"
                                       href="?cari=<?= urlencode($cari) ?>&halaman=<?= $i ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?= ($halaman >= $totalHalaman) ? 'disabled' : '' ?>">
                                <a class="page-link"
                                   href="?cari=<?= urlencode($cari) ?>&halaman=<?= $halaman + 1 ?>">
                                    Berikutnya
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

</body>
</html>