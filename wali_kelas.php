<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] !== "admin") {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "wali_kelas";

// Pengaturan pencarian dan pagination
$kataKunci = trim($_GET['cari'] ?? '');
$batas = 10;
$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$keyword = "%" . $kataKunci . "%";

// Hitung jumlah data
$sqlJumlah = "SELECT COUNT(*) AS total
              FROM t_wali_kelas w
              JOIN t_guru g ON w.guru_id = g.id
              JOIN t_kelas k ON w.kelas_id = k.id
              JOIN t_tahun_ajaran ta ON w.tahun_ajaran_id = ta.id
              WHERE g.nama LIKE ?
                 OR g.nip LIKE ?
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

$hasilJumlah = mysqli_stmt_get_result($stmtJumlah);
$totalData = (int) mysqli_fetch_assoc($hasilJumlah)['total'];

$totalHalaman = max(1, (int) ceil($totalData / $batas));

if ($halaman > $totalHalaman) {
    $halaman = $totalHalaman;
}

$offset = ($halaman - 1) * $batas;

// Ambil data penugasan wali kelas
$sqlData = "SELECT
                w.id,
                g.nip,
                g.nama AS nama_guru,
                k.nama AS nama_kelas,
                ta.nama AS nama_tahun_ajaran,
                w.tanggal_mulai,
                w.tanggal_selesai,
                w.status_aktif
            FROM t_wali_kelas w
            JOIN t_guru g ON w.guru_id = g.id
            JOIN t_kelas k ON w.kelas_id = k.id
            JOIN t_tahun_ajaran ta ON w.tahun_ajaran_id = ta.id
            WHERE g.nama LIKE ?
               OR g.nip LIKE ?
               OR k.nama LIKE ?
               OR ta.nama LIKE ?
            ORDER BY w.id DESC
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

$nomorAwal = $totalData > 0 ? $offset + 1 : 0;
$nomorAkhir = min($offset + $batas, $totalData);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Wali Kelas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            margin: 0;
            background: #f8fafc;
            color: #172554;
        }

        .konten-utama {
            margin-left: 220px;
            width: calc(100% - 220px);
            min-height: 100vh;
            padding: 28px;
            box-sizing: border-box;
        }

        .kartu-data {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 4px 18px rgba(23, 37, 84, 0.04);
        }

        .judul-halaman {
            color: #172554;
            font-weight: 700;
        }

        .btn-tambah {
            background: #2563eb;
            border-color: #2563eb;
            color: #fff;
            border-radius: 8px;
        }

        .btn-tambah:hover {
            background: #1d4ed8;
            color: #fff;
        }

        .form-control {
            border-radius: 8px;
            padding: 10px 12px;
        }

        .table thead th {
            background: #eff6ff;
            color: #172554;
            white-space: nowrap;
            font-size: 13px;
        }

        .table td {
            vertical-align: middle;
            font-size: 14px;
        }

        .badge-aktif {
            background: #dcfce7;
            color: #166534;
        }

        .badge-nonaktif {
            background: #fee2e2;
            color: #991b1b;
        }

        .pagination .page-link {
            color: #2563eb;
        }

        .pagination .active .page-link {
            background: #2563eb;
            border-color: #2563eb;
            color: #fff;
        }

        @media (max-width: 575.98px) {
            .konten-utama {
                padding: 16px;
            }
        }
    </style>
</head>

<body>

<?php include "includes/sidebar.php"; ?>

<main class="konten-utama">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="judul-halaman mb-1">Kelola Wali Kelas</h3>
            <p class="text-secondary mb-0">
                Kelola penugasan guru sebagai wali kelas.
            </p>
        </div>

        <a href="tambah_wali_kelas.php" class="btn btn-tambah px-3 py-2">
            + Tambah Penugasan
        </a>
    </div>

    <?php if (isset($_GET['pesan'])): ?>
        <?php if ($_GET['pesan'] === 'tambah'): ?>
            <div class="alert alert-success">Penugasan wali kelas berhasil ditambahkan.</div>
        <?php elseif ($_GET['pesan'] === 'edit'): ?>
            <div class="alert alert-success">Penugasan wali kelas berhasil diperbarui.</div>
        <?php elseif ($_GET['pesan'] === 'hapus'): ?>
            <div class="alert alert-success">Penugasan wali kelas berhasil dihapus.</div>
        <?php elseif ($_GET['pesan'] === 'gagal_hapus'): ?>
            <div class="alert alert-danger">Penugasan gagal dihapus. Periksa relasi data terlebih dahulu.</div>
        <?php elseif ($_GET['pesan'] === 'gagal'): ?>
            <div class="alert alert-danger">Terjadi kesalahan. Silakan periksa data kembali.</div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="kartu-data p-3 p-md-4">

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-6 col-lg-5">
                <input
                    type="text"
                    name="cari"
                    class="form-control"
                    placeholder="Cari nama guru, NIP, kelas, atau tahun ajaran..."
                    value="<?= htmlspecialchars($kataKunci) ?>"
                >
            </div>

            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Cari</button>
                <a href="wali_kelas.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>

        <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
            <span class="text-secondary small">
                Menampilkan <?= $nomorAwal ?>–<?= $nomorAkhir ?>
                dari <?= $totalData ?> data penugasan
            </span>
            <span class="text-secondary small">Maksimal 10 data per halaman</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Guru</th>
                        <th>Kelas</th>
                        <th>Tahun Ajaran</th>
                        <th>Masa Penugasan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php $no = $offset + 1; ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $no++ ?></td>

                            <td>
                                <div class="fw-semibold">
                                    <?= htmlspecialchars($row['nama_guru']) ?>
                                </div>
                                <small class="text-secondary">
                                    NIP: <?= htmlspecialchars($row['nip']) ?>
                                </small>
                            </td>

                            <td><?= htmlspecialchars($row['nama_kelas']) ?></td>

                            <td><?= htmlspecialchars($row['nama_tahun_ajaran']) ?></td>

                            <td>
                                <?= date('d-m-Y', strtotime($row['tanggal_mulai'])) ?>
                                <span class="text-secondary">s.d.</span>
                                <?= date('d-m-Y', strtotime($row['tanggal_selesai'])) ?>
                            </td>

                            <td>
                                <?php if ((int)$row['status_aktif'] === 1): ?>
                                    <span class="badge badge-aktif rounded-pill px-3 py-2">
                                        Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-nonaktif rounded-pill px-3 py-2">
                                        Tidak Aktif
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="d-flex gap-1">
                                    <a
                                        href="edit_wali_kelas.php?id=<?= (int)$row['id'] ?>"
                                        class="btn btn-sm btn-warning"
                                    >Edit</a>

                                    <form
                                        action="hapus_wali_kelas.php"
                                        method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus penugasan ini?')"
                                    >
                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int)$row['id'] ?>"
                                        >
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
                        <td colspan="7" class="text-center py-4 text-secondary">
                            Belum ada data penugasan wali kelas.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

 
        <?php if ($totalHalaman > 1): ?>
            <nav aria-label="Navigasi halaman">
                <ul class="pagination justify-content-center flex-wrap mb-0">

                    <!-- Tombol Sebelumnya -->
                    <li class="page-item <?= $halaman <= 1 ? 'disabled' : '' ?>">
                        <a
                            class="page-link"
                            href="?<?= http_build_query([
                                'cari' => $kataKunci,
                                'halaman' => max(1, $halaman - 1)
                            ]) ?>"
                        >
                            Sebelumnya
                        </a>
                    </li>

                    <!-- Nomor Halaman -->
                    <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>
                        <li class="page-item <?= $halaman === $i ? 'active' : '' ?>">
                            <a
                                class="page-link"
                                href="?<?= http_build_query([
                                    'cari' => $kataKunci,
                                    'halaman' => $i
                                ]) ?>"
                            >
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <!-- Tombol Berikutnya -->
                    <li class="page-item <?= $halaman >= $totalHalaman ? 'disabled' : '' ?>">
                        <a
                            class="page-link"
                            href="?<?= http_build_query([
                                'cari' => $kataKunci,
                                'halaman' => min($totalHalaman, $halaman + 1)
                            ]) ?>"
                        >
                            Berikutnya
                        </a>
                    </li>

                </ul>
            </nav>
        <?php endif; ?>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
