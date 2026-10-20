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

$nama = "";
$tanggal_mulai = "";
$tanggal_selesai = "";
$status_aktif = "1";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $tanggal_mulai = trim($_POST['tanggal_mulai'] ?? '');
    $tanggal_selesai = trim($_POST['tanggal_selesai'] ?? '');
    $status_aktif = (string) ($_POST['status_aktif'] ?? '');

    // Validasi input
    if (
        $nama === '' ||
        $tanggal_mulai === '' ||
        $tanggal_selesai === '' ||
        !in_array($status_aktif, ['0', '1'], true)
    ) {
        $error = "Semua data wajib diisi dengan benar.";
    } elseif (
        !preg_match('/^\d{4}\/\d{4}$/', $nama)
    ) {
        $error = "Format nama tahun ajaran harus seperti 2026/2027.";
    } elseif (
    //   } elseif (
    (int) substr($nama, 5, 4) !== (int) substr($nama, 0, 4) + 1
    ) {
        $error = "Tahun akhir harus satu tahun setelah tahun awal. Contoh: 2026/2027.";
        } elseif (
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_mulai) ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_selesai) ||
        !checkdate(
            (int) substr($tanggal_mulai, 5, 2),
            (int) substr($tanggal_mulai, 8, 2),
            (int) substr($tanggal_mulai, 0, 4)
        ) ||
        !checkdate(
            (int) substr($tanggal_selesai, 5, 2),
            (int) substr($tanggal_selesai, 8, 2),
            (int) substr($tanggal_selesai, 0, 4)
        )
    ) {
        $error = "Tanggal yang dimasukkan tidak valid.";
    } elseif ($tanggal_mulai > $tanggal_selesai) {
        $error = "Tanggal mulai tidak boleh melewati tanggal selesai.";
    } else {
        // Cek nama tahun ajaran agar tidak duplikat
        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id FROM t_tahun_ajaran WHERE nama = ? LIMIT 1"
        );

        mysqli_stmt_bind_param($cek, "s", $nama);
        mysqli_stmt_execute($cek);

        $hasilCek = mysqli_stmt_get_result($cek);
        $duplikat = mysqli_fetch_assoc($hasilCek);

        mysqli_stmt_close($cek);

        if ($duplikat) {
            $error = "Nama tahun ajaran tersebut sudah tersedia.";
        } else {
            // Simpan data tahun ajaran
            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO t_tahun_ajaran
                    (nama, tanggal_mulai, tanggal_selesai, status_aktif)
                 VALUES (?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "sssi",
                $nama,
                $tanggal_mulai,
                $tanggal_selesai,
                $status_aktif
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: tahun_ajaran.php?pesan=tambah");
                exit;
            } else {
                $error = "Gagal menyimpan tahun ajaran.";
            }

            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Tahun Ajaran - Sistem Informasi Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            margin: 0;
            background-color: #f8f9fa;
        }

        .konten-utama {
            margin-left: 220px;
            width: calc(100% - 220px);
            min-height: 100vh;
            padding: 24px;
            box-sizing: border-box;
        }

        @media (max-width: 767.98px) {
            .konten-utama {
                margin-left: 180px;
                width: calc(100% - 180px);
                padding: 15px;
            }
        }
    </style>
</head>

<body>

<?php include "includes/sidebar.php"; ?>

<main class="konten-utama">

    <div class="mb-4">
        <h2 class="mb-1">Tambah Tahun Ajaran</h2>
        <p class="text-muted mb-0">
            Masukkan informasi tahun ajaran baru.
        </p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            <?php if ($error !== '') { ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php } ?>

            <form method="POST" action="">

                <div class="mb-3">
                    <label for="nama" class="form-label">
                        Nama Tahun Ajaran
                    </label>

                    <input
                        type="text"
                        name="nama"
                        id="nama"
                        class="form-control"
                        placeholder="Contoh: 2026/2027"
                        value="<?= htmlspecialchars($nama, ENT_QUOTES, 'UTF-8') ?>"
                        pattern="[0-9]{4}/[0-9]{4}"
                        required>

                    <div class="form-text">
                        Gunakan format tahun awal/tahun akhir, misalnya 2026/2027.
                    </div>
                </div>

                <div class="mb-3">
                    <label for="tanggal_mulai" class="form-label">
                        Tanggal Mulai
                    </label>

                    <input
                        type="date"
                        name="tanggal_mulai"
                        id="tanggal_mulai"
                        class="form-control"
                        value="<?= htmlspecialchars($tanggal_mulai, ENT_QUOTES, 'UTF-8') ?>"
                        required>
                </div>

                <div class="mb-3">
                    <label for="tanggal_selesai" class="form-label">
                        Tanggal Selesai
                    </label>

                    <input
                        type="date"
                        name="tanggal_selesai"
                        id="tanggal_selesai"
                        class="form-control"
                        value="<?= htmlspecialchars($tanggal_selesai, ENT_QUOTES, 'UTF-8') ?>"
                        required>
                </div>

                <div class="mb-4">
                    <label for="status_aktif" class="form-label">
                        Status Tahun Ajaran
                    </label>

                    <select name="status_aktif"
                            id="status_aktif"
                            class="form-select"
                            required>
                        <option value="1" <?= $status_aktif === '1' ? 'selected' : '' ?>>
                            Aktif
                        </option>

                        <option value="0" <?= $status_aktif === '0' ? 'selected' : '' ?>>
                            Nonaktif
                        </option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        Simpan Tahun Ajaran
                    </button>

                    <a href="tahun_ajaran.php" class="btn btn-secondary">
                        Kembali
                    </a>
                </div>

            </form>

        </div>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
```