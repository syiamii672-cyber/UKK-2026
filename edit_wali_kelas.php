
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] !== "admin") {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "wali_kelas";
$error = "";

$id = (int) ($_GET['id'] ?? 0);

// Ambil data penugasan
$stmt = mysqli_prepare($koneksi, "SELECT * FROM t_wali_kelas WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$data) {
    header("Location: wali_kelas.php?pesan=gagal");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tahun = (int) ($_POST['tahun_ajaran_id'] ?? 0);
    $kelas = (int) ($_POST['kelas_id'] ?? 0);
    $guru = (int) ($_POST['guru_id'] ?? 0);
    $mulai = $_POST['tanggal_mulai'] ?? '';
    $selesai = $_POST['tanggal_selesai'] ?? '';
    $status = $_POST['status_aktif'] ?? '';

    if (!$tahun || !$kelas || !$guru || !$mulai || !$selesai ||
        !in_array($status, ['0', '1'], true)) {
        $error = "Semua data wajib diisi.";
    } elseif ($selesai < $mulai) {
        $error = "Tanggal selesai tidak boleh sebelum tanggal mulai.";
    } else {
        // Cek agar satu kelas tidak memiliki dua wali kelas aktif
        if ($status === '1') {
            $cek = mysqli_prepare($koneksi, "
                SELECT id FROM t_wali_kelas
                WHERE kelas_id = ?
                AND tahun_ajaran_id = ?
                AND status_aktif = 1
                AND id != ?
                LIMIT 1
            ");
            mysqli_stmt_bind_param($cek, "iii", $kelas, $tahun, $id);
            mysqli_stmt_execute($cek);

            if (mysqli_num_rows(mysqli_stmt_get_result($cek)) > 0) {
                $error = "Kelas tersebut sudah memiliki wali kelas aktif.";
            }
            mysqli_stmt_close($cek);
        }

        if ($error === "") {
            $stmt = mysqli_prepare($koneksi, "
                UPDATE t_wali_kelas
                SET tahun_ajaran_id = ?, kelas_id = ?, guru_id = ?,
                    tanggal_mulai = ?, tanggal_selesai = ?,
                    status_aktif = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");

            mysqli_stmt_bind_param(
                $stmt, "iiissii",
                $tahun, $kelas, $guru, $mulai, $selesai, $status, $id
            );

            if (mysqli_stmt_execute($stmt)) {
                header("Location: wali_kelas.php?pesan=edit");
                exit;
            }

            $error = "Gagal memperbarui data wali kelas.";
        }
    }

    // Tampilkan kembali data yang dikirim jika validasi gagal
    $data = array_merge($data, [
        'tahun_ajaran_id' => $tahun,
        'kelas_id' => $kelas,
        'guru_id' => $guru,
        'tanggal_mulai' => $mulai,
        'tanggal_selesai' => $selesai,
        'status_aktif' => $status
    ]);
}

// Ambil pilihan dropdown
$guruList = mysqli_query($koneksi, "SELECT id, nip, nama FROM t_guru WHERE status_aktif = 1 ORDER BY nama");
$kelasList = mysqli_query($koneksi, "SELECT id, nama FROM t_kelas WHERE status_aktif = 1 ORDER BY nama");
$tahunList = mysqli_query($koneksi, "SELECT id, nama FROM t_tahun_ajaran WHERE status_aktif = 1 ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Wali Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { margin: 0; background: #f8fafc; color: #172554; }
        .konten { margin-left: 220px; width: calc(100% - 220px); padding: 28px; }
        .kartu { background: white; border: 1px solid #e2e8f0; border-radius: 14px; padding: 24px; }
        .form-control, .form-select { padding: 10px; border-radius: 8px; }
        @media (max-width: 576px) { .konten { padding: 16px; } }
    </style>
</head>
<body>

<?php include "includes/sidebar.php"; ?>

<main class="konten">
    <h3 class="fw-bold mb-1">Edit Penugasan Wali Kelas</h3>
    <p class="text-secondary mb-4">Perbarui data penugasan guru.</p>

    <div class="kartu">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">Tahun Ajaran</label>
                    <select name="tahun_ajaran_id" class="form-select" required>
                        <option value="">Pilih Tahun Ajaran</option>
                        <?php while ($t = mysqli_fetch_assoc($tahunList)): ?>
                            <option value="<?= $t['id'] ?>"
                                <?= $data['tahun_ajaran_id'] == $t['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['nama']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Kelas</label>
                    <select name="kelas_id" class="form-select" required>
                        <option value="">Pilih Kelas</option>
                        <?php while ($k = mysqli_fetch_assoc($kelasList)): ?>
                            <option value="<?= $k['id'] ?>"
                                <?= $data['kelas_id'] == $k['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['nama']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Guru Wali Kelas</label>
                    <select name="guru_id" class="form-select" required>
                        <option value="">Pilih Guru</option>
                        <?php while ($g = mysqli_fetch_assoc($guruList)): ?>
                            <option value="<?= $g['id'] ?>"
                                <?= $data['guru_id'] == $g['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($g['nip'] . ' - ' . $g['nama']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" class="form-control"
                        value="<?= htmlspecialchars($data['tanggal_mulai']) ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" class="form-control"
                        value="<?= htmlspecialchars($data['tanggal_selesai']) ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Status Penugasan</label>
                    <select name="status_aktif" class="form-select" required>
                        <option value="1" <?= $data['status_aktif'] == 1 ? 'selected' : '' ?>>Aktif</option>
                        <option value="0" <?= $data['status_aktif'] == 0 ? 'selected' : '' ?>>Tidak Aktif</option>
                    </select>
                </div>
            </div>

            <hr class="my-4">

            <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
            <a href="wali_kelas.php" class="btn btn-outline-secondary px-4">Kembali</a>
        </form>
    </div>
</main>

</body>
</html>