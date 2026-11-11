
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

// Identitas guru login
$stmtGuru = mysqli_prepare($koneksi, "
    SELECT id, nama
    FROM t_guru
    WHERE user_id = ? AND status_aktif = 1
    LIMIT 1
");

if (!$stmtGuru) {
    die("Gagal mengambil data guru: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmtGuru, "i", $idUser);
mysqli_stmt_execute($stmtGuru);
$guru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtGuru));
mysqli_stmt_close($stmtGuru);

if (!$guru) {
    die("Data guru tidak ditemukan atau akun tidak aktif.");
}

$guruId = (int)$guru['id'];

// Proses memperbarui tindakan
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

        // Pastikan catatan milik guru yang login dan belum selesai
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

            if (!$stmtUpdate) {
                $pesanTindakan = "Gagal menyiapkan pembaruan: " . mysqli_error($koneksi);
            } else {
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
    }
}

// Ambil seluruh catatan pelanggaran dari semua guru
$queryTerbaru = mysqli_query($koneksi, "
    SELECT
        ps.id,
        ps.guru_id,
        ps.nama_siswa,
        s.nis,
        ps.nama_kelas,
        ps.nama_pelanggaran,
        ps.tanggal,
        ps.keterangan,
        ps.poin,
        ps.nama_guru,
        ps.tindakan,
        ps.status
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

// Ambil catatan yang dipilih untuk card tindakan
$idCard = (int)($_GET['tindakan'] ?? 0);
$dataCard = null;

if ($idCard > 0) {
    $stmtCard = mysqli_prepare($koneksi, "
        SELECT
            id,
            nama_siswa,
            nama_kelas,
            nama_pelanggaran,
            tanggal,
            keterangan,
            tindakan,
            status
        FROM t_pelanggaran_siswa
        WHERE id = ? AND guru_id = ?
        LIMIT 1
    ");

    mysqli_stmt_bind_param($stmtCard, "ii", $idCard, $guruId);
    mysqli_stmt_execute($stmtCard);
    $dataCard = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCard));
    mysqli_stmt_close($stmtCard);

    if (!$dataCard) {
        $pesanTindakan = "Catatan tidak ditemukan atau bukan milik akun Anda.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Catatan Pelanggaran</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f5f7fb;
        }

        .konten {
            margin-left: 220px;
            padding: 30px;
            min-height: 100vh;
        }

        .kartu {
            border: 0;
            border-radius: 12px;
        }

        .tabel-terbaru th,
        .tabel-terbaru td {
            vertical-align: middle;
            white-space: nowrap;
        }

        .card-tindakan {
            border: 1px solid #b6d4fe;
            border-radius: 12px;
        }

        @media(max-width: 768px) {
            .konten {
                margin-left: 0;
                padding: 18px;
            }
        }
    </style>
</head>

<body>

<?php include "includes/sidebar.php"; ?>

<main class="konten">

    <div class="mb-4">
        <h3 class="fw-bold">Catatan Pelanggaran</h3>
        <p class="text-muted mb-0">
            Daftar seluruh catatan pelanggaran siswa yang dicatat oleh semua guru.
        </p>
    </div>

    <?php if (isset($_GET['berhasil'])): ?>
        <div class="alert alert-success">
            Pelanggaran berhasil dicatat.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['berhasil_tindakan'])): ?>
        <div class="alert alert-success">
            Tindakan pelanggaran berhasil diperbarui.
        </div>
    <?php endif; ?>

    <?php if ($pesanTindakan !== '' && $idCard <= 0): ?>
        <div class="alert alert-danger">
            <?= e($pesanTindakan) ?>
        </div>
    <?php endif; ?>

    <!-- CARD TINDAKAN -->
    <?php if ($idCard > 0): ?>
        <div class="card card-tindakan shadow-sm mb-4" id="form-tindakan">
            <div class="card-body p-4">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Tindakan Pelanggaran</h5>
                    <a href="catatan_pelanggaran.php" class="btn btn-sm btn-outline-secondary">
                        Tutup
                    </a>
                </div>

                <?php if ($pesanTindakan !== ''): ?>
                    <div class="alert alert-danger">
                        <?= e($pesanTindakan) ?>
                    </div>
                <?php endif; ?>

                <?php if ($dataCard): ?>

                    <?php if ($dataCard['status'] === 'Selesai'): ?>
                        <div class="alert alert-success">
                            Catatan pelanggaran ini sudah selesai ditangani.
                        </div>
                    <?php else: ?>

                        <div class="bg-light rounded p-3 mb-3">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <small class="text-muted">Nama Siswa</small>
                                    <div class="fw-semibold"><?= e($dataCard['nama_siswa']) ?></div>
                                </div>

                                <div class="col-md-6">
                                    <small class="text-muted">Kelas</small>
                                    <div class="fw-semibold"><?= e($dataCard['nama_kelas']) ?></div>
                                </div>

                                <div class="col-md-6">
                                    <small class="text-muted">Jenis Pelanggaran</small>
                                    <div class="fw-semibold"><?= e($dataCard['nama_pelanggaran']) ?></div>
                                </div>

                                <div class="col-md-6">
                                    <small class="text-muted">Tanggal</small>
                                    <div class="fw-semibold">
                                        <?= !empty($dataCard['tanggal']) && strtotime($dataCard['tanggal']) !== false
                                            ? e(date('d-m-Y', strtotime($dataCard['tanggal'])))
                                            : '-' ?>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <small class="text-muted">Keterangan Kejadian</small>
                                    <div><?= e($dataCard['keterangan']) ?></div>
                                </div>

                                <div class="col-12">
                                    <small class="text-muted">Tindakan Sebelumnya</small>
                                    <div><?= e($dataCard['tindakan'] ?: 'Belum ada tindakan') ?></div>
                                </div>

                            </div>
                        </div>

                        <form method="POST">
                            <input type="hidden"
                                   name="id_pelanggaran"
                                   value="<?= (int)$dataCard['id'] ?>">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Catatan Tindakan</label>
                                <textarea name="tindakan"
                                          class="form-control"
                                          rows="4"
                                          placeholder="Tuliskan tindakan atau pembinaan yang diberikan..."
                                          required><?= e($dataCard['tindakan'] === 'Belum ada tindakan' ? '' : $dataCard['tindakan']) ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Status Penanganan</label>
                                <select name="status" class="form-select" required>
                                    <option value="Diproses"
                                        <?= in_array($dataCard['status'], ['Diproses', 'Proses', 'Sedang diproses'], true) ? 'selected' : '' ?>>
                                        Diproses
                                    </option>
                                    <option value="Selesai"
                                        <?= $dataCard['status'] === 'Selesai' ? 'selected' : '' ?>>
                                        Selesai
                                    </option>
                                </select>
                            </div>

                            <button type="submit" name="aksi_tindakan" class="btn btn-primary">
                                Simpan Tindakan
                            </button>

                            <a href="catatan_pelanggaran.php" class="btn btn-secondary">
                                Batal
                            </a>
                        </form>

                    <?php endif; ?>
                <?php endif; ?>

            </div>
        </div>
    <?php endif; ?>

    <!-- TABEL SELURUH CATATAN -->
    <div class="card kartu shadow-sm">
        <div class="card-body p-4">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="fw-bold mb-1">Seluruh Catatan Pelanggaran</h5>
                    <p class="text-muted mb-0">
                        Termasuk catatan yang belum ditindak, sedang diproses, dan selesai.
                    </p>
                    <small class="text-muted">
                        Total <?= $totalData ?> catatan pelanggaran.
                    </small>
                </div>

                <!-- <a href="input_pelanggaran.php" class="btn btn-primary">
                    + Input Pelanggaran
                </a>
            </div> -->

            <div class="table-responsive">
                <table class="table table-bordered table-hover tabel-terbaru align-middle">
                    <thead class="table-light">
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
                                    $warnaStatus = 'warning';
                                } elseif (in_array($statusAsli, ['Diproses', 'Proses', 'Sedang diproses'], true)) {
                                    $statusTampil = 'Diproses';
                                    $warnaStatus = 'info';
                                } elseif ($statusAsli === 'Selesai') {
                                    $statusTampil = 'Selesai';
                                    $warnaStatus = 'success';
                                } else {
                                    $statusTampil = $statusAsli ?: '-';
                                    $warnaStatus = 'secondary';
                                }

                                $milikGuru = (int)$data['guru_id'] === $guruId;
                                $sudahSelesai = ($statusAsli === 'Selesai');

                                $tanggal = '-';
                                if (!empty($data['tanggal']) && strtotime($data['tanggal']) !== false) {
                                    $tanggal = date('d-m-Y', strtotime($data['tanggal']));
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
                                        <span class="badge text-bg-primary">
                                            <?= (int)$data['poin'] ?> Poin
                                        </span>
                                    </td>

                                    <td><?= e($data['nama_guru']) ?></td>

                                    <td>
                                        <span class="badge text-bg-<?= $warnaStatus ?>">
                                            <?= e($statusTampil) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?= e($data['tindakan'] ?: 'Belum ada tindakan') ?>
                                    </td>

                                    <td>
                                        <?php if ($milikGuru && !$sudahSelesai): ?>
                                            <a href="catatan_pelanggaran.php?tindakan=<?= (int)$data['id'] ?>#form-tindakan"
                                               class="btn btn-sm btn-primary">
                                                Perbarui Tindakan
                                            </a>
                                        <?php elseif ($milikGuru && $sudahSelesai): ?>
                                            <span class="badge bg-success">Sudah Selesai</span>
                                        <?php else: ?>
                                            <span class="text-muted small">Catatan guru lain</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    Belum ada catatan pelanggaran siswa.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</main>

</body>
</html>
