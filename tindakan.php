
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

// ==========================================
// HAK AKSES
// ==========================================

$role = strtolower(trim($_SESSION['role'] ?? ''));

if (!in_array($role, ['admin', 'guru'], true)) {
    header("Location: dashboard.php");
    exit;
}

if ($role === 'admin') {
    header("Location: dashboard.php");
    exit;
}

$idUser = (int)($_SESSION['id'] ?? 0);
$menuAktif = "tindakan";

// ==========================================
// FUNGSI KEAMANAN OUTPUT
// ==========================================

function e($nilai) {
    return htmlspecialchars(
        (string)($nilai ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

// ==========================================
// CEK WALI KELAS
// ==========================================

$isWaliKelas = false;
$kelasWali = 0;
$tahunWali = 0;
$namaKelasWali = '';

$stmtWali = mysqli_prepare($koneksi, "
    SELECT
        w.kelas_id,
        w.tahun_ajaran_id,
        k.nama AS nama_kelas
    FROM t_guru g
    JOIN t_wali_kelas w
        ON g.id = w.guru_id
    JOIN t_kelas k
        ON w.kelas_id = k.id
    JOIN t_tahun_ajaran ta
        ON w.tahun_ajaran_id = ta.id
    WHERE g.user_id = ?
        AND g.status_aktif = 1
        AND w.status_aktif = 1
        AND k.status_aktif = 1
        AND ta.status_aktif = 1
    LIMIT 1
");

if (!$stmtWali) {
    die("Gagal menyiapkan pemeriksaan wali kelas.");
}

mysqli_stmt_bind_param($stmtWali, "i", $idUser);
mysqli_stmt_execute($stmtWali);

mysqli_stmt_bind_result(
    $stmtWali,
    $kelasWaliData,
    $tahunWaliData,
    $namaKelasWaliData
);

if (mysqli_stmt_fetch($stmtWali)) {
    $isWaliKelas = true;
    $kelasWali = (int)$kelasWaliData;
    $tahunWali = (int)$tahunWaliData;
    $namaKelasWali = $namaKelasWaliData;
}

mysqli_stmt_close($stmtWali);

// ==========================================
// TOKEN CSRF
// ==========================================

if (empty($_SESSION['csrf_tindakan'])) {
    $_SESSION['csrf_tindakan'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_tindakan'];

// ==========================================
// PENCARIAN DAN FILTER
// ==========================================

$cari = isset($_GET['cari']) && is_scalar($_GET['cari'])
    ? trim((string)$_GET['cari'])
    : '';

$statusValid = [
    "Belum ditangani",
    "Sedang diproses"
];

$statusFilter = isset($_GET['status']) && is_scalar($_GET['status'])
    ? (string)$_GET['status']
    : '';

if (!in_array($statusFilter, $statusValid, true)) {
    $statusFilter = '';
}

// ==========================================
// PAGINATION
// ==========================================

$batas = 10;

$halaman = isset($_GET['halaman']) && is_scalar($_GET['halaman'])
    ? max(1, (int)$_GET['halaman'])
    : 1;

// ==========================================
// PESAN
// ==========================================

$pesan = '';

// ==========================================
// PROSES SIMPAN TINDAKAN
// ==========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = isset($_POST['id']) && is_scalar($_POST['id'])
        ? (int)$_POST['id']
        : 0;

    $tindakan = isset($_POST['tindakan']) && is_scalar($_POST['tindakan'])
        ? trim((string)$_POST['tindakan'])
        : '';

    $status = isset($_POST['status']) && is_scalar($_POST['status'])
        ? (string)$_POST['status']
        : '';

    $tokenInput = isset($_POST['csrf_token']) && is_scalar($_POST['csrf_token'])
        ? (string)$_POST['csrf_token']
        : '';

    if (!hash_equals($csrfToken, $tokenInput)) {

        $pesan = "Permintaan tidak valid. Silakan muat ulang halaman.";

    } elseif (
        $id <= 0 ||
        $tindakan === '' ||
        !in_array($status, [
            "Belum ditangani",
            "Sedang diproses",
            "Selesai"
        ], true)
    ) {

        $pesan = "Data tindakan tidak valid. Periksa kembali isian.";

    } else {

        // Periksa data pelanggaran terlebih dahulu.
        $stmtCek = mysqli_prepare($koneksi, "
            SELECT
                status,
                kelas_id,
                tahun_ajaran_id
            FROM t_pelanggaran_siswa
            WHERE id = ?
            LIMIT 1
        ");

        if (!$stmtCek) {

            $pesan = "Gagal memeriksa data pelanggaran.";

        } else {

            mysqli_stmt_bind_param($stmtCek, "i", $id);
            mysqli_stmt_execute($stmtCek);

            mysqli_stmt_bind_result(
                $stmtCek,
                $statusLama,
                $kelasData,
                $tahunData
            );

            $dataDitemukan = mysqli_stmt_fetch($stmtCek);

            mysqli_stmt_close($stmtCek);

            if (!$dataDitemukan) {

                $pesan = "Data pelanggaran tidak ditemukan.";

            } elseif (
                $isWaliKelas &&
                (
                    (int)$kelasData !== $kelasWali ||
                    (int)$tahunData !== $tahunWali
                )
            ) {

                $pesan = "Anda hanya boleh menangani pelanggaran siswa di kelas tanggung jawab Anda.";

            } elseif (!in_array($statusLama, [
                "Belum ditangani",
                "Sedang diproses"
            ], true)) {

                $pesan = "Pelanggaran sudah selesai dan tidak dapat diubah lagi.";

            } else {

                // Guru biasa boleh menangani seluruh data.
                // Wali kelas hanya boleh menangani kelas dan tahun tugasnya.

                if ($isWaliKelas) {

                    $stmt = mysqli_prepare($koneksi, "
                        UPDATE t_pelanggaran_siswa
                        SET tindakan = ?, status = ?
                        WHERE id = ?
                            AND kelas_id = ?
                            AND tahun_ajaran_id = ?
                            AND status IN (
                                'Belum ditangani',
                                'Sedang diproses'
                            )
                    ");

                    if ($stmt) {
                        mysqli_stmt_bind_param(
                            $stmt,
                            "ssiii",
                            $tindakan,
                            $status,
                            $id,
                            $kelasWali,
                            $tahunWali
                        );
                    }

                } else {

                    $stmt = mysqli_prepare($koneksi, "
                        UPDATE t_pelanggaran_siswa
                        SET tindakan = ?, status = ?
                        WHERE id = ?
                            AND status IN (
                                'Belum ditangani',
                                'Sedang diproses'
                            )
                    ");

                    if ($stmt) {
                        mysqli_stmt_bind_param(
                            $stmt,
                            "ssi",
                            $tindakan,
                            $status,
                            $id
                        );
                    }
                }

                if (!$stmt) {

                    $pesan = "Gagal menyiapkan pembaruan tindakan.";

                } else {

                    if (mysqli_stmt_execute($stmt)) {

                        mysqli_stmt_close($stmt);

                        $parameter = [
                            'cari' => $cari,
                            'status' => $statusFilter,
                            'halaman' => $halaman,
                            'berhasil' => 1
                        ];

                        header(
                            "Location: tindakan.php?" .
                            http_build_query($parameter)
                        );
                        exit;

                    } else {

                        $pesan = "Gagal memperbarui tindakan.";

                        mysqli_stmt_close($stmt);
                    }
                }
            }
        }
    }
}

// ==========================================
// KONDISI QUERY
// ==========================================

$where = "
    WHERE ps.status IN (
        'Belum ditangani',
        'Sedang diproses'
    )
";

// Pembatasan Wali Kelas
if ($isWaliKelas) {

    $where .= "
        AND ps.kelas_id = $kelasWali
        AND ps.tahun_ajaran_id = $tahunWali
    ";
}

// ==========================================
// PENCARIAN
// ==========================================

if ($cari !== '') {

    $kata = mysqli_real_escape_string($koneksi, $cari);

    $where .= "
        AND (
            ps.nama_siswa LIKE '%$kata%'
            OR s.nis LIKE '%$kata%'
            OR ps.nama_kelas LIKE '%$kata%'
            OR ps.nama_pelanggaran LIKE '%$kata%'
            OR ps.nama_guru LIKE '%$kata%'
        )
    ";
}

// ==========================================
// FILTER STATUS
// ==========================================

if ($statusFilter !== '') {

    $statusSQL = mysqli_real_escape_string(
        $koneksi,
        $statusFilter
    );

    $where .= "
        AND ps.status = '$statusSQL'
    ";
}

// ==========================================
// HITUNG JUMLAH DATA
// ==========================================

$queryJumlah = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total
    FROM t_pelanggaran_siswa ps
    LEFT JOIN t_siswa s
        ON ps.siswa_id = s.id
    $where
");

if (!$queryJumlah) {
    die("Gagal menghitung data: " . mysqli_error($koneksi));
}

$dataJumlah = mysqli_fetch_assoc($queryJumlah);
$totalData = (int)$dataJumlah['total'];

$totalHalaman = max(
    1,
    (int)ceil($totalData / $batas)
);

$halaman = min($halaman, $totalHalaman);

$offset = ($halaman - 1) * $batas;

// ==========================================
// AMBIL DATA TINDAKAN
// ==========================================

$query = mysqli_query($koneksi, "
    SELECT
        ps.*,
        s.nis
    FROM t_pelanggaran_siswa ps
    LEFT JOIN t_siswa s
        ON ps.siswa_id = s.id
    $where
    ORDER BY
        ps.tanggal DESC,
        ps.id DESC
    LIMIT $offset, $batas
");

if (!$query) {
    die("Gagal mengambil data: " . mysqli_error($koneksi));
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tindakan Pelanggaran</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            margin: 0;
            background: #f5f7fb;
        }

        .konten-utama {
            margin-left: 220px;
            width: calc(100% - 220px);
            min-height: 100vh;
            padding: 25px;
            box-sizing: border-box;
        }

        .kartu {
            border: none;
            border-radius: 12px;
        }

        .table th {
            white-space: nowrap;
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

    <!-- JUDUL HALAMAN -->

    <div class="mb-4">

        <h2 class="fw-bold mb-1">
            Tindakan Pelanggaran
        </h2>

        <p class="text-secondary mb-0">
            Kelola tindak lanjut pelanggaran siswa yang belum selesai ditangani.
        </p>

    </div>

    <!-- INFORMASI WALI KELAS -->

    <?php if ($isWaliKelas): ?>

        <div class="alert alert-primary">
            <strong>Wali Kelas</strong>
            <span class="mx-1">|</span>
            Kelas: <?= e($namaKelasWali); ?>
        </div>

    <?php endif; ?>

    <!-- PESAN BERHASIL -->

    <?php if (
        isset($_GET['berhasil']) &&
        $_GET['berhasil'] === '1'
    ): ?>

        <div class="alert alert-success">
            Tindakan berhasil diperbarui!
        </div>

    <?php endif; ?>

    <!-- PESAN ERROR -->

    <?php if ($pesan !== ''): ?>

        <div class="alert alert-danger">
            <?= e($pesan); ?>
        </div>

    <?php endif; ?>

    <!-- TABEL DATA -->

    <div class="card kartu shadow-sm">

        <div class="card-body">

            <!-- PENCARIAN DAN FILTER -->

            <form method="GET" action="tindakan.php" class="row g-2 mb-3">

                <div class="col-md-6">

                    <input
                        type="text"
                        name="cari"
                        class="form-control"
                        placeholder="Cari NIS, siswa, kelas, atau pelanggaran..."
                        value="<?= e($cari); ?>"
                    >

                </div>

                <div class="col-md-4">

                    <select
                        name="status"
                        class="form-select"
                    >

                        <option value="">
                            Semua Status Aktif
                        </option>

                        <?php foreach ($statusValid as $st): ?>

                            <option
                                value="<?= e($st); ?>"
                                <?= $statusFilter === $st ? 'selected' : ''; ?>
                            >
                                <?= e($st); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="col-md-2">

                    <button
                        type="submit"
                        class="btn btn-primary w-100"
                    >
                        Cari
                    </button>

                </div>

            </form>

            <!-- INFORMASI DATA -->

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">

                <small class="text-secondary">

                    Menampilkan
                    <?= $totalData > 0 ? $offset + 1 : 0; ?>
                    -
                    <?= min($offset + $batas, $totalData); ?>
                    dari <?= $totalData; ?> data

                </small>

                <small class="text-secondary">

                    Halaman <?= $halaman; ?>
                    dari <?= $totalHalaman; ?>

                </small>

            </div>

            <!-- TABEL TINDAKAN -->

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-light">

                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Pelanggaran</th>
                            <th>Poin</th>
                            <th>Guru Pencatat</th>
                            <th>Tindakan dan Status</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php if (mysqli_num_rows($query) > 0): ?>

                            <?php $no = $offset + 1; ?>

                            <?php while ($data = mysqli_fetch_assoc($query)): ?>

                                <?php
                                    $tanggal = '-';

                                    if (
                                        !empty($data['tanggal']) &&
                                        strtotime($data['tanggal']) !== false
                                    ) {
                                        $tanggal = date(
                                            'd-m-Y',
                                            strtotime($data['tanggal'])
                                        );
                                    }
                                ?>

                                <tr>

                                    <td>
                                        <?= $no++; ?>
                                    </td>

                                    <td>
                                        <?= e($tanggal); ?>
                                    </td>

                                    <td>
                                        <?= e($data['nis'] ?? '-'); ?>
                                    </td>

                                    <td>
                                        <?= e($data['nama_siswa']); ?>
                                    </td>

                                    <td>
                                        <?= e($data['nama_kelas']); ?>
                                    </td>

                                    <td>
                                        <?= e($data['nama_pelanggaran']); ?>
                                    </td>

                                    <td>
                                        <span class="badge bg-primary">
                                            <?= (int)$data['poin']; ?> Poin
                                        </span>
                                    </td>

                                    <td>
                                        <?= e($data['nama_guru']); ?>
                                    </td>

                                    <td style="min-width: 240px;">

                                        <form
                                            method="POST"
                                            action="tindakan.php?<?= e(http_build_query([
                                                'cari' => $cari,
                                                'status' => $statusFilter,
                                                'halaman' => $halaman
                                            ])); ?>"
                                        >

                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= e($csrfToken); ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= (int)$data['id']; ?>"
                                            >

                                            <textarea
                                                name="tindakan"
                                                class="form-control form-control-sm mb-2"
                                                rows="2"
                                                placeholder="Isi tindakan..."
                                                required
                                            ><?= e($data['tindakan']); ?></textarea>

                                            <select
                                                name="status"
                                                class="form-select form-select-sm mb-2"
                                                required
                                            >

                                                <option
                                                    value="Belum ditangani"
                                                    <?= $data['status'] === 'Belum ditangani' ? 'selected' : ''; ?>
                                                >
                                                    Belum ditangani
                                                </option>

                                                <option
                                                    value="Sedang diproses"
                                                    <?= $data['status'] === 'Sedang diproses' ? 'selected' : ''; ?>
                                                >
                                                    Sedang diproses
                                                </option>

                                                <option value="Selesai">
                                                    Selesai
                                                </option>

                                            </select>

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-primary w-100"
                                            >
                                                Simpan Tindakan
                                            </button>

                                        </form>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="9"
                                    class="text-center text-secondary py-4"
                                >
                                    Tidak ada pelanggaran yang perlu ditangani.
                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

            <!-- PAGINATION -->

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                <small class="text-secondary">
                    Total <?= $totalData; ?> pelanggaran aktif
                </small>

                <?php if ($totalHalaman > 1): ?>

                    <nav>

                        <ul class="pagination pagination-sm mb-0">

                            <li class="page-item <?= $halaman <= 1 ? 'disabled' : ''; ?>">

                                <a
                                    class="page-link"
                                    href="?<?= e(http_build_query([
                                        'cari' => $cari,
                                        'status' => $statusFilter,
                                        'halaman' => max(1, $halaman - 1)
                                    ])); ?>"
                                >
                                    Sebelumnya
                                </a>

                            </li>

                            <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>

                                <li class="page-item <?= $halaman === $i ? 'active' : ''; ?>">

                                    <a
                                        class="page-link"
                                        href="?<?= e(http_build_query([
                                            'cari' => $cari,
                                            'status' => $statusFilter,
                                            'halaman' => $i
                                        ])); ?>"
                                    >
                                        <?= $i; ?>
                                    </a>

                                </li>

                            <?php endfor; ?>

                            <li class="page-item <?= $halaman >= $totalHalaman ? 'disabled' : ''; ?>">

                                <a
                                    class="page-link"
                                    href="?<?= e(http_build_query([
                                        'cari' => $cari,
                                        'status' => $statusFilter,
                                        'halaman' => min($totalHalaman, $halaman + 1)
                                    ])); ?>"
                                >
                                    Selanjutnya
                                </a>

                            </li>

                        </ul>

                    </nav>

                <?php endif; ?>

            </div>

        </div>

    </div>

</main>

</body>
</html>
