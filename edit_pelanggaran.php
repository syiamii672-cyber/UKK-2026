
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] != "admin") {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "pelanggaran";
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
    header("Location: pelanggaran.php?pesan=gagal");
    exit;
}

$stmt = mysqli_prepare($koneksi,
    "SELECT * FROM t_pelanggaran WHERE id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$data = mysqli_stmt_get_result($stmt);
$pelanggaran = mysqli_fetch_assoc($data);

if (!$pelanggaran) {
    header("Location: pelanggaran.php?pesan=gagal");
    exit;
}

$error = "";

$kategori = mysqli_query($koneksi,
    "SELECT id, nama FROM t_pelanggaran_kategori
     WHERE status_aktif = 1
        OR id = " . (int)$pelanggaran['pelanggaran_kategori_id'] . "
     ORDER BY nama ASC"
);

if (!$kategori) {
    die("Gagal mengambil kategori pelanggaran.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kategoriId = (int)($_POST['pelanggaran_kategori_id'] ?? 0);
    $nama = trim($_POST['nama'] ?? '');
    $poinInput = $_POST['poin'] ?? '';
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $status = $_POST['status_aktif'] ?? '1';

    if ($kategoriId <= 0 || $nama === '' || $poinInput === '') {
        $error = "Kategori, nama pelanggaran, dan poin wajib diisi.";
    } elseif (!ctype_digit((string)$poinInput) || (int)$poinInput < 0) {
        $error = "Poin harus berupa bilangan bulat minimal 0.";
    } elseif (!in_array($status, ['0', '1'], true)) {
        $error = "Status tidak valid.";
    } else {
        $cekKategori = mysqli_prepare($koneksi,
            "SELECT id FROM t_pelanggaran_kategori
             WHERE id = ? AND (status_aktif = 1 OR id = ?)"
        );
        mysqli_stmt_bind_param($cekKategori, "ii", $kategoriId, $kategoriId);
        mysqli_stmt_execute($cekKategori);

        if (mysqli_num_rows(mysqli_stmt_get_result($cekKategori)) === 0) {
            $error = "Kategori tidak ditemukan atau tidak bisa digunakan.";
        } else {
            $cekNama = mysqli_prepare($koneksi,
                "SELECT id FROM t_pelanggaran
                 WHERE nama = ? AND id != ?"
            );
            mysqli_stmt_bind_param($cekNama, "si", $nama, $id);
            mysqli_stmt_execute($cekNama);

            if (mysqli_num_rows(mysqli_stmt_get_result($cekNama)) > 0) {
                $error = "Nama jenis pelanggaran tersebut sudah digunakan.";
            } else {
                $poin = (int)$poinInput;

                $update = mysqli_prepare($koneksi,
                    "UPDATE t_pelanggaran
                     SET pelanggaran_kategori_id = ?,
                         nama = ?, poin = ?, deskripsi = ?,
                         status_aktif = ?, updated_at = CURRENT_TIMESTAMP
                     WHERE id = ?"
                );
                mysqli_stmt_bind_param($update, "isissi",
                    $kategoriId, $nama, $poin, $deskripsi, $status, $id
                );

                if (mysqli_stmt_execute($update)) {
                    header("Location: pelanggaran.php?pesan=edit");
                    exit;
                }

                $error = "Gagal memperbarui data pelanggaran.";
            }
        }
    }

    // Tampilkan kembali input yang dikirim jika validasi gagal.
    $pelanggaran['pelanggaran_kategori_id'] = $kategoriId;
    $pelanggaran['nama'] = $nama;
    $pelanggaran['poin'] = $poinInput;
    $pelanggaran['deskripsi'] = $deskripsi;
    $pelanggaran['status_aktif'] = $status;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Jenis Pelanggaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include "includes/sidebar.php"; ?>

<main style="margin-left:220px; padding:30px; min-height:100vh;">
    <h3 class="fw-bold">Edit Jenis Pelanggaran</h3>
    <p class="text-secondary mb-4">Perbarui informasi jenis pelanggaran.</p>

    <div class="card border-0 shadow-sm rounded-4" style="max-width:800px;">
        <div class="card-body p-4">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="id" value="<?= $id ?>">

                <div class="mb-3">
                    <label class="form-label">Kode Pelanggaran</label>
                    <input type="text" class="form-control"
                           value="<?= htmlspecialchars($pelanggaran['kode']) ?>" readonly>
                    <small class="text-secondary">Kode dibuat otomatis dan tidak dapat diubah.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kategori Pelanggaran</label>
                    <select name="pelanggaran_kategori_id" class="form-select" required>
                        <option value="">-- Pilih kategori --</option>
                        <?php while ($k = mysqli_fetch_assoc($kategori)): ?>
                            <option value="<?= (int)$k['id'] ?>"
                                <?= (int)$pelanggaran['pelanggaran_kategori_id'] === (int)$k['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['nama']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Pelanggaran</label>
                    <input type="text" name="nama" class="form-control"
                           maxlength="150" required
                           value="<?= htmlspecialchars($pelanggaran['nama']) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Poin Pelanggaran</label>
                    <input type="number" name="poin" class="form-control"
                           min="0" required
                           value="<?= htmlspecialchars((string)$pelanggaran['poin']) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($pelanggaran['deskripsi'] ?? '') ?></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label">Status</label>
                    <select name="status_aktif" class="form-select">
                        <option value="1" <?= (string)$pelanggaran['status_aktif'] === '1' ? 'selected' : '' ?>>Aktif</option>
                        <option value="0" <?= (string)$pelanggaran['status_aktif'] === '0' ? 'selected' : '' ?>>Nonaktif</option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="pelanggaran.php" class="btn btn-outline-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</main>
</body>
</html>
