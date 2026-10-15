
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] != "admin") {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "guru";
$error = "";

$nip = "";
$nama = "";
$email = "";
$status_aktif = "1";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nip = trim($_POST['nip'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $status_aktif = $_POST['status_aktif'] ?? '';

    if (
        $nip == '' || $nama == '' || $email == '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        !in_array($status_aktif, ['0', '1'], true)
    ) {
        $error = "Semua kolom wajib diisi dengan benar.";
    } else {
        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id FROM t_guru WHERE nip = ? OR email = ?"
        );

        mysqli_stmt_bind_param($cek, "ss", $nip, $email);
        mysqli_stmt_execute($cek);
        $hasil = mysqli_stmt_get_result($cek);

        if (mysqli_num_rows($hasil) > 0) {
            $error = "NIP atau email sudah digunakan guru lain.";
        } else {
            $status = (int)$status_aktif;

            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO t_guru (nip, nama, email, status_aktif)
                 VALUES (?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt, "sssi",
                $nip, $nama, $email, $status
            );

            if (mysqli_stmt_execute($stmt)) {
                header("Location: data_guru.php?pesan=tambah");
                exit;
            } else {
                $error = "Data guru gagal disimpan.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Guru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body { margin: 0; background: #f8f9fa; }
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
    <h2>Tambah Data Guru</h2>
    <p class="text-muted mb-4">Masukkan informasi guru baru.</p>

    <?php if ($error != '') { ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error); ?>
        </div>
    <?php } ?>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">NIP</label>
                    <input type="text" name="nip" class="form-control"
                           maxlength="50" required
                           value="<?= htmlspecialchars($nip); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Guru</label>
                    <input type="text" name="nama" class="form-control"
                           maxlength="150" required
                           value="<?= htmlspecialchars($nama); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control"
                           maxlength="150" required
                           value="<?= htmlspecialchars($email); ?>">
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

                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="data_guru.php" class="btn btn-secondary">Batal</a>

            </form>
        </div>
    </div>
</main>
</body>
</html>