
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] !== "admin") {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "kelas";
$pesanError = "";

$nama = "";
$tingkat = "";
$jurusan = "";
$status_aktif = "1";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $tingkat = trim($_POST['tingkat'] ?? '');
    $jurusan = trim($_POST['jurusan'] ?? '');
    $status_aktif = $_POST['status_aktif'] ?? '1';

    // Validasi input
    if ($nama === '' || $tingkat === '' || $jurusan === '') {
        $pesanError = "Semua data kelas wajib diisi.";
    } elseif (!in_array($status_aktif, ['0', '1'], true)) {
        $pesanError = "Status kelas tidak valid.";
    } else {
        // Cek apakah nama kelas sudah digunakan
        $stmtCek = mysqli_prepare(
            $koneksi,
            "SELECT id FROM t_kelas WHERE nama = ? LIMIT 1"
        );

        mysqli_stmt_bind_param($stmtCek, "s", $nama);
        mysqli_stmt_execute($stmtCek);
        $hasilCek = mysqli_stmt_get_result($stmtCek);

        if (mysqli_num_rows($hasilCek) > 0) {
            $pesanError = "Nama kelas tersebut sudah terdaftar.";
        }

        mysqli_stmt_close($stmtCek);

        // Simpan data jika lolos validasi
        if ($pesanError === '') {
            $status = (int) $status_aktif;

            $stmtSimpan = mysqli_prepare(
                $koneksi,
                "INSERT INTO t_kelas
                (nama, tingkat, jurusan, status_aktif)
                VALUES (?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmtSimpan,
                "sssi",
                $nama,
                $tingkat,
                $jurusan,
                $status
            );

            try {
                if (mysqli_stmt_execute($stmtSimpan)) {
                    mysqli_stmt_close($stmtSimpan);
                    header("Location: kelas.php?pesan=tambah");
                    exit;
                } else {
                    $pesanError = "Data kelas gagal disimpan.";
                }
            } catch (mysqli_sql_exception $e) {
                $pesanError = "Data kelas gagal disimpan. Periksa kembali data yang dimasukkan.";
            }

            if ($stmtSimpan) {
                mysqli_stmt_close($stmtSimpan);
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

    <title>Tambah Kelas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            margin: 0;
            background: #f8f9fa;
        }

        .konten-utama {
            margin-left: 220px;
            width: calc(100% - 220px);
            min-height: 100vh;
            padding: 24px;
            box-sizing: border-box;
        }

        @media (max-width: 575.98px) {
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
        <h2>Tambah Kelas</h2>
        <p class="text-muted mb-0">
            Masukkan informasi kelas baru.
        </p>
    </div>

    <?php if ($pesanError !== '') { ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($pesanError, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php } ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            <form method="POST" action="tambah_kelas.php">

                <div class="mb-3">
                    <label for="nama" class="form-label">
                        Nama Kelas
                    </label>
                    <input
                        type="text"
                        id="nama"
                        name="nama"
                        class="form-control"
                        maxlength="100"
                        placeholder="Contoh: 12 RPL 2"
                        value="<?= htmlspecialchars($nama, ENT_QUOTES, 'UTF-8'); ?>"
                        required>
                </div>

                <div class="mb-3">
                    <label for="tingkat" class="form-label">
                        Tingkat
                    </label>
                    <select
                        id="tingkat"
                        name="tingkat"
                        class="form-select"
                        required>
                        <option value="">-- Pilih Tingkat --</option>
                        <option value="10" <?= $tingkat === '10' ? 'selected' : ''; ?>>10</option>
                        <option value="11" <?= $tingkat === '11' ? 'selected' : ''; ?>>11</option>
                        <option value="12" <?= $tingkat === '12' ? 'selected' : ''; ?>>12</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="jurusan" class="form-label">
                        Jurusan
                    </label>
                    <input
                        type="text"
                        id="jurusan"
                        name="jurusan"
                        class="form-control"
                        maxlength="100"
                        placeholder="Contoh: RPL, TKJ, AKL"
                        value="<?= htmlspecialchars($jurusan, ENT_QUOTES, 'UTF-8'); ?>"
                        required>
                </div>

                <div class="mb-4">
                    <label for="status_aktif" class="form-label">
                        Status Kelas
                    </label>
                    <select
                        id="status_aktif"
                        name="status_aktif"
                        class="form-select"
                        required>
                        <option value="1" <?= $status_aktif === '1' ? 'selected' : ''; ?>>
                            Aktif
                        </option>
                        <option value="0" <?= $status_aktif === '0' ? 'selected' : ''; ?>>
                            Tidak Aktif
                        </option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    Simpan Kelas
                </button>

                <a href="kelas.php" class="btn btn-secondary">
                    Kembali
                </a>

            </form>

        </div>
    </div>

</main>

</body>
</html>
