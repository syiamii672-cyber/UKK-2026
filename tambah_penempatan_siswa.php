
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] != "admin") {
    header("Location: dashboard.php");
    exit;
}

$error = "";
$siswaId = (int)($_POST['siswa_id'] ?? 0);
$kelasId = (int)($_POST['kelas_id'] ?? 0);
$tahunId = (int)($_POST['tahun_ajaran_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($siswaId < 1 || $kelasId < 1 || $tahunId < 1) {
        $error = "Semua pilihan wajib diisi.";
    } else {
        // Pastikan siswa, kelas, dan tahun ajaran tersedia
        $sqlValid = "SELECT
                        (SELECT COUNT(*) FROM t_siswa
                         WHERE id = ? AND status_aktif = 1) AS siswa_valid,
                        (SELECT COUNT(*) FROM t_kelas
                         WHERE id = ? AND status_aktif = 1) AS kelas_valid,
                        (SELECT COUNT(*) FROM t_tahun_ajaran
                         WHERE id = ? AND status_aktif = 1) AS tahun_valid";

        $stmtValid = mysqli_prepare($koneksi, $sqlValid);
        mysqli_stmt_bind_param(
            $stmtValid, "iii", $siswaId, $kelasId, $tahunId
        );
        mysqli_stmt_execute($stmtValid);
        $valid = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtValid));

        if (!$valid['siswa_valid'] || !$valid['kelas_valid'] ||
            !$valid['tahun_valid']) {
            $error = "Siswa, kelas, atau tahun ajaran tidak tersedia atau tidak aktif.";
        } else {
            // Satu siswa hanya boleh punya satu penempatan per tahun ajaran
            $sqlCek = "SELECT id FROM t_kelas_siswa
                       WHERE siswa_id = ? AND tahun_ajaran_id = ?
                       LIMIT 1";
            $stmtCek = mysqli_prepare($koneksi, $sqlCek);
            mysqli_stmt_bind_param($stmtCek, "ii", $siswaId, $tahunId);
            mysqli_stmt_execute($stmtCek);

            if (mysqli_num_rows(mysqli_stmt_get_result($stmtCek)) > 0) {
                $error = "Siswa sudah memiliki penempatan pada tahun ajaran tersebut.";
            } else {
                $sqlSimpan = "INSERT INTO t_kelas_siswa
                              (siswa_id, kelas_id, tahun_ajaran_id)
                              VALUES (?, ?, ?)";
                $stmtSimpan = mysqli_prepare($koneksi, $sqlSimpan);
                mysqli_stmt_bind_param(
                    $stmtSimpan, "iii", $siswaId, $kelasId, $tahunId
                );

                try {
                    if (mysqli_stmt_execute($stmtSimpan)) {
                        header("Location: penempatan_siswa.php?pesan=tambah");
                        exit;
                    }
                    $error = "Penempatan gagal disimpan.";
                } catch (mysqli_sql_exception $e) {
                    $error = "Penempatan gagal disimpan. Periksa kembali relasi dan data yang dipilih.";
                }
            }
        }
    }
}

$siswa = mysqli_query(
    $koneksi,
    "SELECT id, nis, nama FROM t_siswa
     WHERE status_aktif = 1 ORDER BY nama ASC"
);
$kelas = mysqli_query(
    $koneksi,
    "SELECT id, nama, tingkat, jurusan FROM t_kelas
     WHERE status_aktif = 1 ORDER BY nama ASC"
);
$tahun = mysqli_query(
    $koneksi,
    "SELECT id, nama FROM t_tahun_ajaran
     WHERE status_aktif = 1 ORDER BY tanggal_mulai DESC"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Penempatan Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">
    <style>
        body { margin: 0; background: #f8f9fa; }
        .konten-utama {
            margin-left: 220px; width: calc(100% - 220px);
            min-height: 100vh; padding: 24px; box-sizing: border-box;
        }
        @media (max-width: 575.98px) {
            .konten-utama {
                margin-left: 180px; width: calc(100% - 180px);
                padding: 15px;
            }
        }
    </style>
</head>
<body>

<?php $menuAktif = "penempatan"; include "includes/sidebar.php"; ?>

<main class="konten-utama">
    <h2>Tambah Penempatan Siswa</h2>
    <p class="text-muted">Pilih siswa, kelas, dan tahun ajaran.</p>

    <?php if ($error !== "") { ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php } ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Siswa</label>
                    <select name="siswa_id" class="form-select" required>
                        <option value="">-- Pilih Siswa --</option>
                        <?php while ($s = mysqli_fetch_assoc($siswa)) { ?>
                            <option value="<?= $s['id']; ?>"
                                <?= $siswaId == $s['id'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($s['nis'] . ' - ' . $s['nama'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kelas</label>
                    <select name="kelas_id" class="form-select" required>
                        <option value="">-- Pilih Kelas --</option>
                        <?php while ($k = mysqli_fetch_assoc($kelas)) { ?>
                            <option value="<?= $k['id']; ?>"
                                <?= $kelasId == $k['id'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars(
                                    $k['nama'] . ' - ' . $k['tingkat'] . ' ' . $k['jurusan'],
                                    ENT_QUOTES, 'UTF-8'
                                ); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tahun Ajaran</label>
                    <select name="tahun_ajaran_id" class="form-select" required>
                        <option value="">-- Pilih Tahun Ajaran --</option>
                        <?php while ($t = mysqli_fetch_assoc($tahun)) { ?>
                            <option value="<?= $t['id']; ?>"
                                <?= $tahunId == $t['id'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($t['nama'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    Simpan
                </button>
                <a href="penempatan_siswa.php" class="btn btn-secondary">
                    Kembali
                </a>
            </form>
        </div>
    </div>
</main>
</body>
</html>
