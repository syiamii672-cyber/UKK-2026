
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if (strtolower(trim($_SESSION['role'] ?? '')) !== 'guru') {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "catatan_pelanggaran";
$idUser = (int)($_SESSION['id'] ?? 0);
$pesanTindakan = '';

function e($nilai) {
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

// Identitas guru
$stmtGuru = mysqli_prepare($koneksi, "
    SELECT id, nama
    FROM t_guru
    WHERE user_id = ? AND status_aktif = 1
    LIMIT 1
");
mysqli_stmt_bind_param($stmtGuru, "i", $idUser);
mysqli_stmt_execute($stmtGuru);
$guru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtGuru));
mysqli_stmt_close($stmtGuru);

if (!$guru) {
    die("Data guru tidak ditemukan atau akun tidak aktif.");
}

$guruId = (int)$guru['id'];

// ID catatan yang dipilih untuk diperbarui
$idCard = (int)($_GET['tindakan'] ?? 0);
$dataCard = null;

// Proses pembaruan tindakan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi_tindakan'])) {
    $idPelanggaran = (int)($_POST['id_pelanggaran'] ?? 0);
    $isiTindakan = trim($_POST['tindakan'] ?? '');
    $statusBaru = trim($_POST['status'] ?? '');

    $statusDiizinkan = ['Diproses', 'Selesai'];

    if ($idPelanggaran <= 0) {
        $pesanTindakan = "Data pelanggaran tidak valid.";
    } elseif ($isiTindakan === '') {
        $pesanTindakan = "Catatan tindakan wajib diisi.";
    } elseif (!in_array($statusBaru, $statusDiizinkan, true)) {
        $pesanTindakan = "Status tindakan tidak valid.";
    } else {
        // Guru hanya boleh memperbarui catatan miliknya sendiri
        $stmtCek = mysqli_prepare($koneksi, "
            SELECT id, status
            FROM t_pelanggaran_siswa
            WHERE id = ? AND guru_id = ?
            LIMIT 1
        ");

        mysqli_stmt_bind_param($stmtCek, "ii", $idPelanggaran, $guruId);
        mysqli_stmt_execute($stmtCek);
        $dataCek = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCek));
        mysqli_stmt_close($stmtCek);

        if (!$dataCek) {
            $pesanTindakan = "Catatan tidak ditemukan atau bukan milik akun Anda.";
        } elseif ($dataCek['status'] === 'Selesai') {
            $pesanTindakan = "Catatan ini sudah selesai dan tidak dapat diubah lagi.";
        } else {
            $stmtUpdate = mysqli_prepare($koneksi, "
                UPDATE t_pelanggaran_siswa
                SET tindakan = ?, status = ?
                WHERE id = ? AND guru_id = ?
            ");

            mysqli_stmt_bind_param(
                $stmtUpdate,
                "ssii",
                $isiTindakan,
                $statusBaru,
                $idPelanggaran,
                $guruId
            );

            if (mysqli_stmt_execute($stmtUpdate)) {
                mysqli_stmt_close($stmtUpdate);
                header("Location: catatan_pelanggaran.php?berhasil_tindakan=1");
                exit;
            }

            $pesanTindakan = "Gagal menyimpan tindakan: " . mysqli_stmt_error($stmtUpdate);
            mysqli_stmt_close($stmtUpdate);
        }
    }

    $idCard = $idPelanggaran;
}

// Seluruh catatan dari semua guru dan tahun
$queryTerbaru = mysqli_query($koneksi, "
    SELECT
        ps.id, ps.guru_id, ps.nama_siswa, s.nis,
        ps.nama_kelas, ps.nama_pelanggaran, ps.tanggal,
        ps.keterangan, ps.poin, ps.nama_guru,
        ps.tindakan, ps.status
    FROM t_pelanggaran_siswa ps
    LEFT JOIN t_siswa s ON ps.siswa_id = s.id
    ORDER BY ps.tanggal DESC, ps.id DESC
");

if (!$queryTerbaru) {
    die("Gagal mengambil catatan pelanggaran: " . mysqli_error($koneksi));
}

$daftarTerbaru = [];
while ($data = mysqli_fetch_assoc($queryTerbaru)) {
    $daftarTerbaru[] = $data;
}
$totalData = count($daftarTerbaru);

// Catatan yang dipilih untuk form tindakan
if ($idCard > 0) {
    $stmtCard = mysqli_prepare($koneksi, "
        SELECT id, nama_siswa, nama_kelas, nama_pelanggaran,
               tanggal, keterangan, tindakan, status
        FROM t_pelanggaran_siswa
        WHERE id = ? AND guru_id = ?
        LIMIT 1
    ");

    mysqli_stmt_bind_param($stmtCard, "ii", $idCard, $guruId);
    mysqli_stmt_execute($stmtCard);
    $dataCard = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCard));
    mysqli_stmt_close($stmtCard);

    if (!$dataCard && $pesanTindakan === '') {
        $pesanTindakan = "Catatan tidak ditemukan atau bukan milik akun Anda.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Catatan Pelanggaran | sipenaMU</title>
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

.btn {
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
}

.btn-primary {
    background: #245B96;
    border: 1px solid #245B96;
    color: #fff;
}

.btn-primary:hover {
    background: #173B67;
    border-color: #173B67;
    color: #fff;
}

.btn-secondary {
    background: #E9EEF5;
    border: 1px solid #E9EEF5;
    color: #40546B;
}

.btn-secondary:hover {
    background: #DCE5F0;
    border-color: #DCE5F0;
    color: #173B67;
}

.form-control,
.form-select {
    min-height: 42px;
    border: 1px solid #D8E1ED;
    border-radius: 8px;
    font-size: 14px;
}

.form-control:focus,
.form-select:focus {
    border-color: #3779C5;
    box-shadow: 0 0 0 3px rgba(55,121,197,.12);
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

.card-tindakan {
    border: 1px solid #E5EAF2;
    border-radius: 12px;
}

.data-ringkas {
    background: #F1F5FB;
    border: 1px solid #E5EAF2;
    border-radius: 10px;
    padding: 16px;
}

.label-data {
    color: #718096;
    font-size: 12px;
    margin-bottom: 4px;
}

.nilai-data {
    color: #173B67;
    font-weight: 600;
    overflow-wrap: anywhere;
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

.badge-poin {
    background: #E5F0FC;
    color: #245B96;
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.alert {
    border-radius: 8px;
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
        <h3 class="judul-halaman mb-1">Catatan Pelanggaran</h3>
        <p class="deskripsi-halaman mb-0">Daftar seluruh catatan pelanggaran siswa yang dicatat oleh semua guru.</p>
    </div>

    <?php if (isset($_GET['berhasil'])): ?>
        <div class="alert alert-success border-0 shadow-sm">Pelanggaran berhasil dicatat.</div>
    <?php endif; ?>

    <?php if (isset($_GET['berhasil_tindakan'])): ?>
        <div class="alert alert-success border-0 shadow-sm">Tindakan pelanggaran berhasil diperbarui.</div>
    <?php endif; ?>

    <?php if ($pesanTindakan !== '' && $idCard <= 0): ?>
        <div class="alert alert-danger border-0 shadow-sm"><?= e($pesanTindakan) ?></div>
    <?php endif; ?>

    <?php if ($idCard > 0): ?>
        <div class="card kartu card-tindakan shadow-sm mb-4" id="form-tindakan">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div>
                        <h5 class="judul-bagian mb-1"><i class="bi bi-chat-square-text me-2"></i>Tindakan Pelanggaran</h5>
                        <small class="text-secondary">Perbarui tindak lanjut untuk catatan milik akun Anda.</small>
                    </div>
                    <a href="catatan_pelanggaran.php" class="btn btn-sm btn-secondary">
                        <i class="bi bi-x-lg me-1"></i>Tutup
                    </a>
                </div>

                <?php if ($pesanTindakan !== ''): ?>
                    <div class="alert alert-danger"><?= e($pesanTindakan) ?></div>
                <?php endif; ?>

                <?php if ($dataCard): ?>
                    <?php if ($dataCard['status'] === 'Selesai'): ?>
                        <div class="alert alert-success mb-0">Catatan pelanggaran ini sudah selesai ditangani.</div>
                    <?php else: ?>
                        <div class="data-ringkas mb-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="label-data">Nama Siswa</div>
                                    <div class="nilai-data"><?= e($dataCard['nama_siswa']) ?></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="label-data">Kelas</div>
                                    <div class="nilai-data"><?= e($dataCard['nama_kelas']) ?></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="label-data">Jenis Pelanggaran</div>
                                    <div class="nilai-data"><?= e($dataCard['nama_pelanggaran']) ?></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="label-data">Tanggal</div>
                                    <div class="nilai-data">
                                        <?= !empty($dataCard['tanggal']) && strtotime($dataCard['tanggal']) ? e(date('d-m-Y', strtotime($dataCard['tanggal']))) : '-' ?>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="label-data">Keterangan Kejadian</div>
                                    <div><?= e($dataCard['keterangan']) ?></div>
                                </div>
                                <div class="col-12">
                                    <div class="label-data">Tindakan Sebelumnya</div>
                                    <div><?= e($dataCard['tindakan'] ?: 'Belum ada tindakan') ?></div>
                                </div>
                            </div>
                        </div>

                        <form method="POST">
                            <input type="hidden" name="id_pelanggaran" value="<?= (int)$dataCard['id'] ?>">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Catatan Tindakan</label>
                                <textarea name="tindakan" class="form-control" rows="4"
                                    placeholder="Tuliskan tindakan atau pembinaan yang diberikan..."
                                    required><?= e($dataCard['tindakan'] === 'Belum ada tindakan' ? '' : $dataCard['tindakan']) ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Status Penanganan</label>
                                <select name="status" class="form-select" required>
                                    <option value="Diproses" <?= in_array($dataCard['status'], ['Diproses', 'Proses', 'Sedang diproses'], true) ? 'selected' : '' ?>>Diproses</option>
                                    <option value="Selesai" <?= $dataCard['status'] === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                                </select>
                            </div>

                            <button type="submit" name="aksi_tindakan" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i>Simpan Tindakan
                            </button>
                            <a href="catatan_pelanggaran.php" class="btn btn-secondary px-4">Batal</a>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="card kartu shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="judul-bagian mb-1">Seluruh Catatan Pelanggaran</h5>
                    <p class="text-secondary small mb-1">Termasuk catatan belum ditindak, diproses, dan selesai.</p>
                    <span class="text-secondary small">Total <?= $totalData ?> catatan pelanggaran.</span>
                </div>
                <a href="input_pelanggaran.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>Input Pelanggaran
                </a>
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
                            <th>Jenis Pelanggaran</th>
                            <th>Poin</th>
                            <th>Guru Pencatat</th>
                            <th>Status</th>
                            <th>Catatan Tindakan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($totalData > 0): ?>
                        <?php $no = 1; ?>
                        <?php foreach ($daftarTerbaru as $data): ?>
                            <?php
                            $statusAsli = trim($data['status'] ?? '');

                            if (in_array($statusAsli, ['Belum Ditindak', 'Belum ditindak', 'Belum ditangani'], true)) {
                                $statusTampil = 'Belum Ditindak';
                                $kelasStatus = 'status-belum';
                            } elseif (in_array($statusAsli, ['Diproses', 'Proses', 'Sedang diproses'], true)) {
                                $statusTampil = 'Diproses';
                                $kelasStatus = 'status-proses';
                            } elseif ($statusAsli === 'Selesai') {
                                $statusTampil = 'Selesai';
                                $kelasStatus = 'status-selesai';
                            } else {
                                $statusTampil = $statusAsli ?: '-';
                                $kelasStatus = 'status-belum';
                            }

                            $milikGuru = (int)$data['guru_id'] === $guruId;
                            $sudahSelesai = ($statusAsli === 'Selesai');
                            $tanggalTampil = (!empty($data['tanggal']) && strtotime($data['tanggal']))
                                ? date('d-m-Y', strtotime($data['tanggal'])) : '-';
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= e($tanggalTampil) ?></td>
                                <td><?= e($data['nis'] ?? '-') ?></td>
                                <td><?= e($data['nama_siswa']) ?></td>
                                <td><?= e($data['nama_kelas']) ?></td>
                                <td><?= e($data['nama_pelanggaran']) ?></td>
                                <td><span class="badge badge-poin"><?= (int)$data['poin'] ?> Poin</span></td>
                                <td><?= e($data['nama_guru']) ?></td>
                                <td><span class="badge-status <?= e($kelasStatus) ?>"><?= e($statusTampil) ?></span></td>
                                <td><?= e($data['tindakan'] ?: 'Belum ada tindakan') ?></td>
                                <td>
                                    <?php if ($milikGuru && !$sudahSelesai): ?>
                                        <a href="catatan_pelanggaran.php?tindakan=<?= (int)$data['id'] ?>#form-tindakan"
                                           class="btn btn-sm btn-primary text-nowrap">
                                            <i class="bi bi-pencil-square me-1"></i>Perbarui
                                        </a>
                                    <?php elseif ($milikGuru && $sudahSelesai): ?>
                                        <span class="badge-status status-selesai">Sudah Selesai</span>
                                    <?php else: ?>
                                        <span class="text-secondary small">Catatan guru lain</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="text-center text-secondary py-4">Belum ada catatan pelanggaran siswa.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
