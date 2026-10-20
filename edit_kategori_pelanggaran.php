
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] !== "admin") {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "kategori_pelanggaran";

$id = (int)($_GET['id'] ?? 0);
$error = "";

// Ambil data kategori
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT * FROM t_pelanggaran_kategori WHERE id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($hasil);

if (!$data) {
    header("Location: kategori_pelanggaran.php");
    exit;
}

$nama = $data['nama'];
$deskripsi = $data['deskripsi'];
$statusAktif = (string)$data['status_aktif'];

// Proses perubahan data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $statusAktif = $_POST['status_aktif'] ?? '';

    if ($nama === '' || $deskripsi === '') {
        $error = "Nama kategori dan deskripsi wajib diisi.";
    } elseif (!in_array($statusAktif, ['0', '1'], true)) {
        $error = "Status kategori tidak valid.";
    } else {
        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE t_pelanggaran_kategori
             SET nama = ?, deskripsi = ?, status_aktif = ?,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = ?"
        );

        $status = (int)$statusAktif;
        mysqli_stmt_bind_param(
            $stmt,
            "ssii",
            $nama,
            $deskripsi,
            $status,
            $id
        );

        if (mysqli_stmt_execute($stmt)) {
            header("Location: kategori_pelanggaran.php?pesan=edit");
            exit;
        } else {
            $error = "Gagal memperbarui kategori. Silakan coba lagi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Kategori Pelanggaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            background: #f4f7fc;
            color: #172554;
        }

        .konten {
            margin-left: 220px;
            padding: 28px;
        }

        .kartu {
            background: white;
            padding: 24px;
            border-radius: 14px;
            box-shadow: 0 3px 12px rgba(15, 35, 75, 0.05);
            max-width: 800px;
        }

        .form-label {
            font-weight: 600;
        }

        .btn-primary {
            background: #2563eb;
            border-color: #2563eb;
        }

        @media (max-width: 576px) {
            .konten {
                padding: 16px;
            }
        }
    </style>
</head>
<body>

<?php include "includes/sidebar.php"; ?>

<main class="konten">
    <h3 class="fw-bold mb-1">Edit Kategori Pelanggaran</h3>
    <p class="text-secondary mb-4">Ubah informasi kategori pelanggaran.</p>

    <div class="kartu">
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="nama" class="form-label">Nama Kategori</label>
                <input
                    type="text"
                    id="nama"
                    name="nama"
                    class="form-control"
                    maxlength="100"
                    value="<?= htmlspecialchars($nama) ?>"
                    required
                >
            </div>

            <div class="mb-3">
                <label for="deskripsi" class="form-label">Deskripsi</label>
                <textarea
                    id="deskripsi"
                    name="deskripsi"
                    class="form-control"
                    rows="4"
                    required
                ><?= htmlspecialchars($deskripsi) ?></textarea>
            </div>

            <div class="mb-4">
                <label for="status_aktif" class="form-label">Status</label>
                <select id="status_aktif" name="status_aktif"
                        class="form-select" required>
                    <option value="1" <?= $statusAktif === '1' ? 'selected' : '' ?>>
                        Aktif
                    </option>
                    <option value="0" <?= $statusAktif === '0' ? 'selected' : '' ?>>
                        Tidak Aktif
                    </option>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    Simpan Perubahan
                </button>
                <a href="kategori_pelanggaran.php"
                   class="btn btn-outline-secondary btn-sm">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</main>

</body>
</html>