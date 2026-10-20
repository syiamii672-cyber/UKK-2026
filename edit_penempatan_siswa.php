
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] != "admin") {
    header("Location: dashboard.php");
    exit;
}

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id, siswa_id, kelas_id, tahun_ajaran_id
     FROM t_kelas_siswa WHERE id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$data) {
    header("Location: penempatan_siswa.php?pesan=gagal");
    exit;
}

$error = "";
$siswaId = (int)($_POST['siswa_id'] ?? $data['siswa_id']);
$kelasId = (int)($_POST['kelas_id'] ?? $data['kelas_id']);
$tahunId = (int)($_POST['tahun_ajaran_id'] ?? $data['tahun_ajaran_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($siswaId < 1 || $kelasId < 1 || $tahunId < 1) {
        $error = "Semua pilihan wajib diisi.";
    } else {
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

        // Data yang sedang diedit boleh tetap dipilih walaupun sudah tidak aktif.
        // Karena itu, validasi juga menerima pilihan yang sama dengan data lama.
        $siswaValid = $valid['siswa_valid'] ||
                      $siswaId == (int)$data['siswa_id'];
        $kelasValid = $valid['kelas_valid'] ||
                      $kelasId == (int)$data['kelas_id'];
        $tahunValid = $valid['tahun_valid'] ||
                      $tahunId == (int)$data['tahun_ajaran_id'];

        if (!$siswaValid || !$kelasValid || !$tahunValid) {
            $error = "Pilihan siswa, kelas, atau tahun ajaran tidak valid.";
        } else {
            $sqlCek = "SELECT id FROM t_kelas_siswa
                       WHERE siswa_id = ? AND tahun_ajaran_id = ?
                         AND id <> ? LIMIT 1";
            $stmtCek = mysqli_prepare($koneksi, $sqlCek);
            mysqli_stmt_bind_param(
                $stmtCek, "iii", $siswaId, $tahunId, $id
            );
            mysqli_stmt_execute($stmtCek);

            if (mysqli_num_rows(mysqli_stmt_get_result($stmtCek)) > 0) {
                $error = "Siswa sudah memiliki penempatan pada tahun ajaran tersebut.";
            } else {
                $sqlUpdate = "UPDATE t_kelas_siswa
                              SET siswa_id = ?, kelas_id = ?,
                                  tahun_ajaran_id = ?
                              WHERE id = ?";
                $stmtUpdate = mysqli_prepare($koneksi, $sqlUpdate);
                mysqli_stmt_bind_param(
                    $stmtUpdate, "iiii",
                    $siswaId, $kelasId, $tahunId, $id
                );

                try {
                    if (mysqli_stmt_execute($stmtUpdate)) {
                        header("Location: penempatan_siswa.php?pesan=edit");
                        exit;
                    }
                    $error = "Penempatan gagal diperbarui.";
                } catch (mysqli_sql_exception $e) {
                    $error = "Penempatan gagal diperbarui. Periksa kembali data yang dipilih.";
                }
            }
        }
    }
}

// Sertakan pilihan aktif dan pilihan lama agar data lama tetap terlihat.
$siswa = mysqli_query(
    $koneksi,
    "SELECT id, nis, nama FROM t_siswa
     WHERE status_aktif = 1 OR id = " . (int)$data['siswa_id'] . "
     ORDER BY nama ASC"
);
$kelas = mysqli_query(
    $koneksi,
    "SELECT id, nama, tingkat, jurusan FROM t_kelas
     WHERE status_aktif = 1 OR id = " . (int)$data['kelas_id'] . "
     ORDER BY nama ASC"
);
$tahun = mysqli_query(
    $koneksi,
    "SELECT id, nama FROM t_tahun_ajaran
     WHERE status_aktif = 1 OR id = " . (int)$data['tahun_ajaran_id'] . "
     ORDER BY tanggal_mulai DESC"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Penempatan Siswa</title>
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
    <h2>Edit Penempatan Siswa</h2>
    <p class="text-muted">Ubah kelas atau tahun ajaran siswa.</p>

    <?php if ($error !== "") { ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php } ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="id" value="<?= $id; ?>">

                <div class="mb-3">
                    <label class="form-label">Siswa</label>
                    <select name="siswa_id" class="form-select" required>
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
                        <?php while ($t = mysqli_fetch_assoc($tahun)) { ?>
                            <option value="<?= $t['id']; ?>"
                                <?= $tahunId == $t['id'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($t['nama'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    Simpan Perubahan
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
