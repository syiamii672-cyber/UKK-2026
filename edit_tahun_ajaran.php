```php
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] != "admin") {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "tahun_ajaran";
$error = "";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header("Location: tahun_ajaran.php");
    exit;
}

// Ambil data tahun ajaran berdasarkan ID
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id, nama, tanggal_mulai, tanggal_selesai, status_aktif
     FROM t_tahun_ajaran
     WHERE id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$hasil = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($hasil);
mysqli_stmt_close($stmt);

if (!$data) {
    header("Location: tahun_ajaran.php?pesan=data_tidak_ditemukan");
    exit;
}

$nama = $data['nama'];
$tanggal_mulai = $data['tanggal_mulai'];
$tanggal_selesai = $data['tanggal_selesai'];
$status_aktif = (string) $data['status_aktif'];

// Proses saat tombol Simpan Perubahan ditekan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
    $tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
    $status_aktif = $_POST['status_aktif'] ?? '';

    if (
        $nama === '' ||
        $tanggal_mulai === '' ||
        $tanggal_selesai === '' ||
        $status_aktif === ''
    ) {
        $error = "Semua kolom wajib diisi.";
    } elseif (!preg_match('/^(\d{4})\/(\d{4})$/', $nama, $cocok)) {
        $error = "Format tahun ajaran harus seperti 2026/2027.";
    } elseif ((int) $cocok[2] !== (int) $cocok[1] + 1) {
        $error = "Tahun akhir harus satu tahun setelah tahun awal.";
    } elseif (
        !in_array($status_aktif, ['0', '1'], true)
    ) {
        $error = "Status tahun ajaran tidak valid.";
    } else {
        $tanggalMulaiValid = DateTime::createFromFormat('!Y-m-d', $tanggal_mulai);
        $tanggalSelesaiValid = DateTime::createFromFormat('!Y-m-d', $tanggal_selesai);

        if (
            !$tanggalMulaiValid ||
            $tanggalMulaiValid->format('Y-m-d') !== $tanggal_mulai ||
            !$tanggalSelesaiValid ||
            $tanggalSelesaiValid->format('Y-m-d') !== $tanggal_selesai
        ) {
            $error = "Format tanggal tidak valid.";
        } elseif ($tanggal_mulai > $tanggal_selesai) {
            $error = "Tanggal mulai tidak boleh melewati tanggal selesai.";
        }
    }

    // Pastikan nama tahun ajaran tidak digunakan data lain
    if ($error === '') {
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id FROM t_tahun_ajaran
             WHERE nama = ? AND id != ?"
        );
        mysqli_stmt_bind_param($stmt, "si", $nama, $id);
        mysqli_stmt_execute($stmt);

        $hasil = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($hasil) > 0) {
            $error = "Nama tahun ajaran sudah digunakan.";
        }

        mysqli_stmt_close($stmt);
    }

    // Simpan perubahan
    if ($error === '') {
        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE t_tahun_ajaran
             SET nama = ?,
                 tanggal_mulai = ?,
                 tanggal_selesai = ?,
                 status_aktif = ?,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sssii",
            $nama,
            $tanggal_mulai,
            $tanggal_selesai,
            $status_aktif,
            $id
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header("Location: tahun_ajaran.php?pesan=edit");
            exit;
        } else {
            $error = "Gagal menyimpan perubahan tahun ajaran.";
        }

        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tahun Ajaran - Sistem Informasi Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include "includes/sidebar.php"; ?>

<main class="konten-utama">
    <div class="container-fluid">
        <div class="mb-4">
            <h3 class="fw-bold">Edit Tahun Ajaran</h3>
            <p class="text-muted">Ubah informasi tahun ajaran sesuai kebutuhan.</p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST">
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Tahun Ajaran</label>
                        <input
                            type="text"
                            name="nama"
                            id="nama"
                            class="form-control"
                            placeholder="Contoh: 2026/2027"
                            value="<?= htmlspecialchars($nama, ENT_QUOTES, 'UTF-8'); ?>"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                        <input
                            type="date"
                            name="tanggal_mulai"
                            id="tanggal_mulai"
                            class="form-control"
                            value="<?= htmlspecialchars($tanggal_mulai, ENT_QUOTES, 'UTF-8'); ?>"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                        <input
                            type="date"
                            name="tanggal_selesai"
                            id="tanggal_selesai"
                            class="form-control"
                            value="<?= htmlspecialchars($tanggal_selesai, ENT_QUOTES, 'UTF-8'); ?>"
                            required
                        >
                    </div>

                    <div class="mb-4">
                        <label for="status_aktif" class="form-label">Status Tahun Ajaran</label>
                        <select name="status_aktif" id="status_aktif" class="form-select" required>
                            <option value="1" <?= $status_aktif === '1' ? 'selected' : ''; ?>>
                                Aktif
                            </option>
                            <option value="0" <?= $status_aktif === '0' ? 'selected' : ''; ?>>
                                Nonaktif
                            </option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Simpan Perubahan
                    </button>

                    <a href="tahun_ajaran.php" class="btn btn-secondary">
                        Kembali
                    </a>
                </form>
            </div>
        </div>
    </div>
</main>

<style>
    .konten-utama {
        margin-left: 220px;
        width: calc(100% - 220px);
        min-height: 100vh;
        padding: 24px;
        box-sizing: border-box;
    }

    @media (max-width: 768px) {
        .konten-utama {
            margin-left: 180px;
            width: calc(100% - 180px);
            padding: 16px;
        }
    }
</style>

</body>
</html>
```