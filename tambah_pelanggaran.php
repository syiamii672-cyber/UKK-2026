
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] != "admin") {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "pelanggaran";
$error = "";

$kategori = mysqli_query($koneksi,
    "SELECT id, nama FROM t_pelanggaran_kategori
     WHERE status_aktif = 1 ORDER BY nama ASC"
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
             WHERE id = ? AND status_aktif = 1"
        );
        mysqli_stmt_bind_param($cekKategori, "i", $kategoriId);
        mysqli_stmt_execute($cekKategori);

        if (mysqli_num_rows(mysqli_stmt_get_result($cekKategori)) === 0) {
            $error = "Kategori tidak ditemukan atau sudah nonaktif.";
        } else {
            $cekNama = mysqli_prepare($koneksi,
                "SELECT id FROM t_pelanggaran WHERE nama = ?"
            );
            mysqli_stmt_bind_param($cekNama, "s", $nama);
            mysqli_stmt_execute($cekNama);

            if (mysqli_num_rows(mysqli_stmt_get_result($cekNama)) > 0) {
                $error = "Nama jenis pelanggaran tersebut sudah ada.";
            } else {
                $kode = '';
                $nomor = 1;
                $hasilMax = mysqli_query($koneksi,
                    "SELECT COALESCE(MAX(id), 0) + 1 AS nomor
                     FROM t_pelanggaran"
                );

                if ($hasilMax) {
                    $nomor = (int)mysqli_fetch_assoc($hasilMax)['nomor'];
                }

                do {
                    $kode = 'PLG' . str_pad((string)$nomor, 3, '0', STR_PAD_LEFT);
                    $cekKode = mysqli_prepare($koneksi,
                        "SELECT id FROM t_pelanggaran WHERE kode = ?"
                    );
                    mysqli_stmt_bind_param($cekKode, "s", $kode);
                    mysqli_stmt_execute($cekKode);
                    $kodeAda = mysqli_num_rows(
                        mysqli_stmt_get_result($cekKode)
                    ) > 0;
                    $nomor++;
                } while ($kodeAda);

                $poin = (int)$poinInput;
                $stmt = mysqli_prepare($koneksi,
                    "INSERT INTO t_pelanggaran
                    (pelanggaran_kategori_id, kode, nama, poin,
                     deskripsi, status_aktif)
                    VALUES (?, ?, ?, ?, ?, ?)"
                );

                mysqli_stmt_bind_param($stmt, "issisi",
                    $kategoriId, $kode, $nama, $poin, $deskripsi, $status
                );

                if (mysqli_stmt_execute($stmt)) {
                    header("Location: pelanggaran.php?pesan=tambah");
                    exit;
                }

                $error = "Gagal menyimpan data pelanggaran.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Jenis Pelanggaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include "includes/sidebar.php"; ?>

<main style="margin-left:220px; padding:30px; min-height:100vh;">
    <h3 class="fw-bold">Tambah Jenis Pelanggaran</h3>
    <p class="text-secondary mb-4">Masukkan data jenis pelanggaran baru.</p>

    <div class="card border-0 shadow-sm rounded-4" style="max-width:800px;">
        <div class="card-body p-4">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if (mysqli_num_rows($kategori) === 0): ?>
                <div class="alert alert-warning">
                    Belum ada kategori aktif. Tambahkan kategori terlebih dahulu.
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Kategori Pelanggaran</label>
                    <select name="pelanggaran_kategori_id" class="form-select" required>
                        <option value="">-- Pilih kategori --</option>
                        <?php while ($k = mysqli_fetch_assoc($kategori)): ?>
                            <option value="<?= (int)$k['id'] ?>"
                                <?= (($_POST['pelanggaran_kategori_id'] ?? '') == $k['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['nama']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Pelanggaran</label>
                    <input type="text" name="nama" class="form-control"
                           maxlength="150" required
                           value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>"
                           placeholder="Contoh: Terlambat datang ke sekolah">
                </div>

                <div class="mb-3">
                    <label class="form-label">Poin Pelanggaran</label>
                    <input type="number" name="poin" class="form-control"
                           min="0" required
                           value="<?= htmlspecialchars($_POST['poin'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label">Status</label>
                    <select name="status_aktif" class="form-select">
                        <option value="1" <?= ($_POST['status_aktif'] ?? '1') === '1' ? 'selected' : '' ?>>Aktif</option>
                        <option value="0" <?= ($_POST['status_aktif'] ?? '') === '0' ? 'selected' : '' ?>>Nonaktif</option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"
                        <?= mysqli_num_rows($kategori) === 0 ? 'disabled' : '' ?>>
                        Simpan
                    </button>
                    <a href="pelanggaran.php" class="btn btn-outline-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</main>
</body>
</html>
