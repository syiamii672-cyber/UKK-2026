
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

$role = strtolower(trim($_SESSION['role'] ?? ''));
$idUser = (int)($_SESSION['id'] ?? 0);
$menuAktif = "riwayat";

// Hak akses
if (!in_array($role, ['admin', 'guru'], true)) {
    header("Location: dashboard.php");
    exit;
}

function e($nilai) {
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

// Identitas guru
$idGuru = 0;

if ($role === 'guru') {
    $stmtGuru = mysqli_prepare($koneksi, "
        SELECT id
        FROM t_guru
        WHERE user_id = ? AND status_aktif = 1
        LIMIT 1
    ");

    if (!$stmtGuru) {
        die("Gagal mengambil identitas guru: " . mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param($stmtGuru, "i", $idUser);
    mysqli_stmt_execute($stmtGuru);
    $dataGuru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtGuru));
    mysqli_stmt_close($stmtGuru);

    $idGuru = (int)($dataGuru['id'] ?? 0);

    if ($idGuru <= 0) {
        die("Data guru tidak ditemukan atau akun tidak aktif.");
    }
}

// Tahun ajaran aktif
$queryAktif = mysqli_query($koneksi, "
    SELECT id, nama
    FROM t_tahun_ajaran
    WHERE status_aktif = 1
    ORDER BY tanggal_mulai DESC
    LIMIT 1
");

if (!$queryAktif) {
    die("Gagal mengambil tahun ajaran aktif: " . mysqli_error($koneksi));
}

$dataAktif = mysqli_fetch_assoc($queryAktif);

if (!$dataAktif) {
    die("Belum ada tahun ajaran yang aktif.");
}

$tahunAktif = (int)$dataAktif['id'];
$namaTahunAktif = $dataAktif['nama'];

// Filter pencarian
$cari = isset($_GET['cari']) && is_scalar($_GET['cari'])
    ? trim((string)$_GET['cari'])
    : '';

$cariSQL = mysqli_real_escape_string($koneksi, $cari);

// Kondisi query: selalu tahun ajaran aktif
$where = " WHERE ps.tahun_ajaran_id = $tahunAktif ";

// Guru hanya melihat catatan yang dibuat sendiri
if ($role === 'guru') {
    $where .= " AND ps.guru_id = $idGuru ";
}

// Pencarian
if ($cari !== '') {
    $where .= "
        AND (
            ps.nama_siswa LIKE '%$cariSQL%'
            OR s.nis LIKE '%$cariSQL%'
            OR ps.nama_kelas LIKE '%$cariSQL%'
            OR ps.nama_pelanggaran LIKE '%$cariSQL%'
            OR ps.nama_guru LIKE '%$cariSQL%'
            OR ps.keterangan LIKE '%$cariSQL%'
            OR ps.tindakan LIKE '%$cariSQL%'
        )
    ";
}

// Pagination
$batas = 10;
$halaman = isset($_GET['halaman']) && is_scalar($_GET['halaman'])
    ? max(1, (int)$_GET['halaman'])
    : 1;

// Hitung jumlah data
$queryJumlah = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total
    FROM t_pelanggaran_siswa ps
    LEFT JOIN t_siswa s ON ps.siswa_id = s.id
    $where
");

if (!$queryJumlah) {
    die("Gagal menghitung riwayat: " . mysqli_error($koneksi));
}

$dataJumlah = mysqli_fetch_assoc($queryJumlah);
$totalData = (int)($dataJumlah['total'] ?? 0);

$totalHalaman = max(1, (int)ceil($totalData / $batas));
$halaman = min($halaman, $totalHalaman);
$offset = ($halaman - 1) * $batas;

// Ambil data riwayat
$queryRiwayat = mysqli_query($koneksi, "
    SELECT ps.*, s.nis
    FROM t_pelanggaran_siswa ps
    LEFT JOIN t_siswa s ON ps.siswa_id = s.id
    $where
    ORDER BY ps.tanggal DESC, ps.id DESC
    LIMIT $offset, $batas
");

if (!$queryRiwayat) {
    die("Gagal mengambil riwayat: " . mysqli_error($koneksi));
}

// Parameter pagination
$parameter = [
    'cari' => $cari
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Riwayat Pelanggaran | sipenaMU</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

<style>

body {
    margin: 0;
    background: #F1F5FB;
    color: #26374A;
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
}

.deskripsi-halaman {
    color: #718096;
    font-size: 13px;
}

.kartu {
    background: #fff;
    border: 1px solid #E5EAF2;
    border-radius: 12px;
    box-shadow: 0 3px 12px rgba(23,59,103,.06);
}

.kartu .card-body {
    padding: 24px;
}

.judul-bagian {
    color: #173B67;
    font-size: 1.05rem;
    font-weight: 700;
}

.info-tahun {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 13px;
    border-radius: 8px;
    background: #EAF2FB;
    border: 1px solid #D7E6F7;
    color: #245B96;
    font-size: 13px;
    font-weight: 600;
}

.form-control {
    min-height: 42px;
    border: 1px solid #D8E1ED;
    border-radius: 8px;
    font-size: 14px;
}

.form-control:focus {
    border-color: #3779C5;
    box-shadow: 0 0 0 3px rgba(55,121,197,.12);
}

.btn {
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    padding: 9px 15px;
}

.btn-primary {
    background: #245B96;
    border-color: #245B96;
    color: #fff;
}

.btn-primary:hover {
    background: #173B67;
    border-color: #173B67;
    color: #fff;
}

.btn-secondary {
    background: #E9EEF5;
    border-color: #E9EEF5;
    color: #40546B;
}

.btn-secondary:hover {
    background: #DCE5F0;
    border-color: #DCE5F0;
    color: #173B67;
}

.table {
    margin-bottom: 0;
    font-size: 13px;
    color: #40546B;
    vertical-align: middle;
}

.table thead th {
    background: #173B67;
    color: #fff;
    font-weight: 600;
    padding: 13px 12px;
    border-color: #31577F;
    white-space: nowrap;
}

.table tbody td {
    padding: 12px;
    color: #40546B;
    border-color: #E8EDF4;
}

.table tbody tr:hover {
    background: #F7FAFE;
}

.badge-poin {
    background: #E5F0FC;
    color: #245B96;
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.badge-status {
    display: inline-block;
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status-belum {
    background: #FFF3D6;
    color: #946200;
}

.status-proses {
    background: #E5F0FC;
    color: #245B96;
}

.status-selesai {
    background: #E3F5EA;
    color: #21834B;
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

.page-link:hover {
    color: #173B67;
    background: #EAF2FB;
    border-color: #DCE5F0;
}

.page-item.active .page-link {
    background: #245B96;
    border-color: #245B96;
    color: #fff;
}

@media (max-width: 767.98px) {
    .konten-utama {
        padding: 100px 15px 25px;
    }

    .kartu .card-body {
        padding: 16px;
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
    <div class="mb-4">
        <h3 class="judul-halaman mb-1">Riwayat Pelanggaran</h3>
        <p class="deskripsi-halaman mb-2">
            Daftar pelanggaran yang pernah dicatat oleh akun Anda.
        </p>
        <span class="info-tahun">
            <i class="bi bi-calendar-check"></i>
            Tahun Ajaran <?= e($namaTahunAktif) ?>
        </span>
    </div>

    <div class="card kartu shadow-sm">
        <div class="card-body">
            <div class="mb-3">
                <h5 class="judul-bagian mb-1">Data Riwayat Pelanggaran</h5>
                <p class="text-secondary small mb-0">
                    <?php if ($role === 'guru'): ?>
                        Menampilkan catatan yang dibuat oleh akun Anda, termasuk pelanggaran dari kelas mana pun.
                    <?php else: ?>
                        Menampilkan seluruh catatan pelanggaran pada tahun ajaran aktif.
                    <?php endif; ?>
                </p>
            </div>

            <form method="GET" action="riwayat.php" class="row g-2 mb-3">
                <div class="col-md-8">
                    <input
                        type="search"
                        name="cari"
                        class="form-control"
                        placeholder="Cari NIS, nama siswa, kelas, pelanggaran, atau keterangan..."
                        value="<?= e($cari) ?>"
                    >
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="bi bi-search me-1"></i>Cari
                    </button>
                    <a href="riwayat.php" class="btn btn-secondary flex-fill">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                    </a>
                </div>
            </form>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <small class="text-secondary">
                    Menampilkan <?= $totalData > 0 ? $offset + 1 : 0 ?>
                    - <?= min($offset + $batas, $totalData) ?>
                    dari <?= $totalData ?> riwayat
                </small>
                <small class="text-secondary">
                    Halaman <?= $halaman ?> dari <?= $totalHalaman ?>
                </small>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Tanggal</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Pelanggaran</th>
                            <th>Pengurangan Poin</th>
                            <th>Guru</th>
                            <th>Tindakan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (mysqli_num_rows($queryRiwayat) > 0): ?>
                        <?php $no = $offset + 1; ?>
                        <?php while ($data = mysqli_fetch_assoc($queryRiwayat)): ?>
                            <?php
                            $tanggal = (!empty($data['tanggal']) && strtotime($data['tanggal']))
                                ? date('d-m-Y', strtotime($data['tanggal']))
                                : '-';

                            $statusAsli = trim($data['status'] ?? '');

                            if (in_array($statusAsli, ['Belum Ditindak', 'Belum ditindak', 'Belum ditangani', ''], true)) {
                                $statusTampil = 'Belum Ditindak';
                                $kelasStatus = 'status-belum';
                            } elseif (in_array($statusAsli, ['Diproses', 'Proses', 'Sedang diproses'], true)) {
                                $statusTampil = 'Diproses';
                                $kelasStatus = 'status-proses';
                            } elseif ($statusAsli === 'Selesai') {
                                $statusTampil = 'Selesai';
                                $kelasStatus = 'status-selesai';
                            } else {
                                $statusTampil = $statusAsli;
                                $kelasStatus = 'status-belum';
                            }
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= e($tanggal) ?></td>
                                <td><?= e($data['nis'] ?? '-') ?></td>
                                <td><?= e($data['nama_siswa']) ?></td>
                                <td><?= e($data['nama_kelas']) ?></td>
                                <td><?= e($data['nama_pelanggaran']) ?></td>
                                <td>
                                    <span class="badge badge-poin">-<?= (int)$data['poin'] ?> Poin</span>
                                </td>
                                <td><?= e($data['nama_guru']) ?></td>
                                <td><?= e($data['tindakan'] ?: 'Belum ada tindakan') ?></td>
                                <td>
                                    <span class="badge-status <?= e($kelasStatus) ?>">
                                        <?= e($statusTampil) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center text-secondary py-4">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                Tidak ada riwayat pelanggaran ditemukan pada tahun ajaran aktif.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                <small class="text-secondary">Total <?= $totalData ?> riwayat pelanggaran</small>

                <?php if ($totalHalaman > 1): ?>
                    <nav aria-label="Navigasi halaman riwayat">
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?= $halaman <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link"
                                   href="?<?= e(http_build_query(array_merge($parameter, ['halaman' => max(1, $halaman - 1)]))) ?>">
                                    Sebelumnya
                                </a>
                            </li>

                            <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>
                                <li class="page-item <?= $halaman === $i ? 'active' : '' ?>">
                                    <a class="page-link"
                                       href="?<?= e(http_build_query(array_merge($parameter, ['halaman' => $i]))) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?= $halaman >= $totalHalaman ? 'disabled' : '' ?>">
                                <a class="page-link"
                                   href="?<?= e(http_build_query(array_merge($parameter, ['halaman' => min($totalHalaman, $halaman + 1)]))) ?>">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
