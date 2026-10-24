
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] != "admin") {
    header("Location: dashboard.php");
    exit;
}

$error = "";
$nis = "";
$nisn = "";
$nama = "";
$jenis_kelamin = "";
$tanggal_lahir = "";
$alamat = "";
$status_aktif = "1";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nis = trim($_POST['nis'] ?? '');
    $nisn = trim($_POST['nisn'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $alamat = trim($_POST['alamat'] ?? '');
    $status_aktif = $_POST['status_aktif'] ?? '';

    if (
        $nis == '' || $nisn == '' || $nama == '' ||
        !in_array($jenis_kelamin, ['L', 'P'], true) ||
        $tanggal_lahir == '' || $alamat == '' ||
        !in_array($status_aktif, ['0', '1'], true)
    ) {
        $error = "Semua kolom wajib diisi dengan benar.";
    } elseif (!preg_match('/^[0-9]{10}$/', $nis)) {
        $error = "NIS harus tepat 10 digit angka.";
    } elseif (!preg_match('/^[0-9]{10}$/', $nisn)) {
        $error = "NISN harus tepat 10 digit angka.";
    } elseif (
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_lahir) ||
        !checkdate(
            (int) substr($tanggal_lahir, 5, 2),
            (int) substr($tanggal_lahir, 8, 2),
            (int) substr($tanggal_lahir, 0, 4)
        )
    ) {
        $error = "Tanggal lahir tidak valid.";
    } else {
        // Cek NIS atau NISN yang sudah digunakan.
        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id FROM t_siswa
             WHERE nis = ? OR nisn = ?"
        );

        mysqli_stmt_bind_param($cek, "ss", $nis, $nisn);
        mysqli_stmt_execute($cek);
        $hasilCek = mysqli_stmt_get_result($cek);

        if (mysqli_num_rows($hasilCek) > 0) {
            $error = "NIS atau NISN sudah digunakan siswa lain.";
        }

        mysqli_stmt_close($cek);

        if ($error == '') {
            $query = "INSERT INTO t_siswa
                (nis, nisn, nama, jenis_kelamin, tanggal_lahir,
                 alamat, status_aktif)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = mysqli_prepare($koneksi, $query);
            $status = (int) $status_aktif;

            mysqli_stmt_bind_param(
                $stmt,
                "ssssssi",
                $nis,
                $nisn,
                $nama,
                $jenis_kelamin,
                $tanggal_lahir,
                $alamat,
                $status
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: siswa.php?pesan=tambah");
                exit;
            } else {
                $error = "Data siswa gagal disimpan.";
            }

            mysqli_stmt_close($stmt);
        }
    }
}

$menuAktif = "siswa";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Siswa</title>

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
    <h2>Tambah Data Siswa</h2>
    <p class="text-muted mb-4">Masukkan informasi siswa baru.</p>

    <?php if ($error != '') { ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php } ?>

    <div class="card shadow-sm border-0">
        <div class="card-body">

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">NIS</label>
                    <input
                        type="text"
                        name="nis"
                        class="form-control"
                        minlength="10"
                        maxlength="10"
                        pattern="[0-9]{10}"
                        inputmode="numeric"
                        title="NIS harus tepat 10 digit angka."
                        placeholder="Masukkan 10 digit NIS"
                        required
                        value="<?= htmlspecialchars($nis, ENT_QUOTES, 'UTF-8'); ?>"
                    >
                    <small class="text-muted">
                        NIS wajib tepat 10 digit angka.
                    </small>
                </div>

                <div class="mb-3">
                    <label class="form-label">NISN</label>
                    <input
                        type="text"
                        name="nisn"
                        class="form-control"
                        minlength="10"
                        maxlength="10"
                        pattern="[0-9]{10}"
                        inputmode="numeric"
                        title="NISN harus tepat 10 digit angka."
                        placeholder="Masukkan 10 digit NISN"
                        required
                        value="<?= htmlspecialchars($nisn, ENT_QUOTES, 'UTF-8'); ?>"
                    >
                    <small class="text-muted">
                        NISN wajib tepat 10 digit angka.
                    </small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Lengkap</label>
                    <input
                        type="text"
                        name="nama"
                        class="form-control"
                        maxlength="150"
                        required
                        value="<?= htmlspecialchars($nama, ENT_QUOTES, 'UTF-8'); ?>"
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-select" required>
                        <option value="">-- Pilih Jenis Kelamin --</option>
                        <option value="L" <?= $jenis_kelamin == 'L' ? 'selected' : ''; ?>>
                            Laki-laki
                        </option>
                        <option value="P" <?= $jenis_kelamin == 'P' ? 'selected' : ''; ?>>
                            Perempuan
                        </option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tanggal Lahir</label>
                    <input
                        type="date"
                        name="tanggal_lahir"
                        class="form-control"
                        required
                        value="<?= htmlspecialchars($tanggal_lahir, ENT_QUOTES, 'UTF-8'); ?>"
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea
                        name="alamat"
                        class="form-control"
                        rows="3"
                        required
                    ><?= htmlspecialchars($alamat, ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status_aktif" class="form-select" required>
                        <option value="1" <?= $status_aktif == '1' ? 'selected' : ''; ?>>
                            Aktif
                        </option>
                        <option value="0" <?= $status_aktif == '0' ? 'selected' : ''; ?>>
                            Tidak Aktif
                        </option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    Simpan
                </button>
                <a href="siswa.php" class="btn btn-secondary">
                    Batal
                </a>

            </form>
        </div>
    </div>
</main>

</body>
</html>
