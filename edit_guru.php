
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] != "admin") {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "guru";
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: data_guru.php");
    exit;
}

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT nip, nama, email, status_aktif
     FROM t_guru WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$guru = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$guru) {
    header("Location: data_guru.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nip = trim($_POST['nip'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $status_aktif = $_POST['status_aktif'] ?? '';

    // NIP wajib tepat 18 digit angka.
    if (!preg_match('/^[0-9]{18}$/', $nip)) {
        $error = "NIP harus tepat 18 digit angka. Periksa kembali NIP yang dimasukkan.";
    } elseif (
        $nama == '' ||
        $email == '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        !in_array($status_aktif, ['0', '1'], true)
    ) {
        $error = "Semua kolom wajib diisi dengan benar.";
    } else {
        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id FROM t_guru
             WHERE (nip = ? OR email = ?) AND id != ?"
        );

        mysqli_stmt_bind_param($cek, "ssi", $nip, $email, $id);
        mysqli_stmt_execute($cek);
        $hasil = mysqli_stmt_get_result($cek);

        if (mysqli_num_rows($hasil) > 0) {
            $error = "NIP atau email sudah digunakan guru lain.";
        }

        mysqli_stmt_close($cek);

        if ($error == '') {
            $status = (int) $status_aktif;

            $update = mysqli_prepare(
                $koneksi,
                "UPDATE t_guru
                 SET nip = ?, nama = ?, email = ?,
                     status_aktif = ?, updated_at = CURRENT_TIMESTAMP
                 WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $update,
                "sssii",
                $nip,
                $nama,
                $email,
                $status,
                $id
            );

            if (mysqli_stmt_execute($update)) {
                mysqli_stmt_close($update);
                header("Location: data_guru.php?pesan=edit");
                exit;
            } else {
                $error = "Data guru gagal diperbarui.";
            }

            mysqli_stmt_close($update);
        }
    }

    // Pertahankan isian saat validasi gagal.
    $guru = [
        'nip' => $nip,
        'nama' => $nama,
        'email' => $email,
        'status_aktif' => $status_aktif
    ];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Guru</title>

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
    <h2>Edit Data Guru</h2>
    <p class="text-muted mb-4">Ubah informasi guru yang dipilih.</p>

    <?php if ($error != '') { ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php } ?>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">NIP</label>
                    <input
                        type="text"
                        name="nip"
                        class="form-control"
                        minlength="18"
                        maxlength="18"
                        pattern="[0-9]{18}"
                        inputmode="numeric"
                        title="NIP harus tepat 18 digit angka."
                        placeholder="Masukkan 18 digit NIP"
                        required
                        value="<?= htmlspecialchars($guru['nip'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    >
                    <small class="text-muted">
                        NIP wajib terdiri dari tepat 18 digit angka.
                    </small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Guru</label>
                    <input
                        type="text"
                        name="nama"
                        class="form-control"
                        maxlength="150"
                        required
                        value="<?= htmlspecialchars($guru['nama'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        maxlength="150"
                        required
                        value="<?= htmlspecialchars($guru['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status_aktif" class="form-select" required>
                        <option value="1" <?= (string)$guru['status_aktif'] === '1' ? 'selected' : ''; ?>>
                            Aktif
                        </option>
                        <option value="0" <?= (string)$guru['status_aktif'] === '0' ? 'selected' : ''; ?>>
                            Tidak Aktif
                        </option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    Simpan Perubahan
                </button>

                <a href="data_guru.php" class="btn btn-secondary">
                    Batal
                </a>

            </form>
        </div>
    </div>
</main>

</body>
</html>
