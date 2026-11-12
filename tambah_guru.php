
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
    $password = $_POST['password'] ?? '';
    $konfirmasiPassword = $_POST['konfirmasi_password'] ?? '';
    $status_aktif = $_POST['status_aktif'] ?? '';

    // Validasi NIP
    if (!preg_match('/^[0-9]{18}$/', $nip)) {
        $error = "NIP harus tepat 18 digit angka.";

    } elseif (
        $nama == '' ||
        $email == '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        !in_array($status_aktif, ['0', '1'], true)
    ) {
        $error = "Periksa kembali data guru yang dimasukkan.";

    } elseif (strlen($password) < 8) {
        $error = "Password minimal 8 karakter.";

    } elseif ($password !== $konfirmasiPassword) {
        $error = "Konfirmasi password tidak sama.";

    } else {
        try {
            // Cek NIP
            $cekNip = mysqli_prepare(
                $koneksi,
                "SELECT id FROM t_guru WHERE nip = ? LIMIT 1"
            );

            mysqli_stmt_bind_param($cekNip, "s", $nip);
            mysqli_stmt_execute($cekNip);
            $hasilNip = mysqli_stmt_get_result($cekNip);

            if (mysqli_num_rows($hasilNip) > 0) {
                $error = "NIP sudah digunakan guru lain.";
            }

            mysqli_stmt_close($cekNip);

            // Cek email
            if ($error == '') {
                $cekEmail = mysqli_prepare(
                    $koneksi,
                    "SELECT id FROM t_users WHERE email = ? LIMIT 1"
                );

                mysqli_stmt_bind_param($cekEmail, "s", $email);
                mysqli_stmt_execute($cekEmail);
                $hasilEmail = mysqli_stmt_get_result($cekEmail);

                if (mysqli_num_rows($hasilEmail) > 0) {
                    $error = "Email sudah digunakan akun lain.";
                }

                mysqli_stmt_close($cekEmail);
            }

            // Simpan akun dan data guru
            if ($error == '') {
                mysqli_begin_transaction($koneksi);

                // Hash password
                $passwordHash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                // Buat akun login
                $stmtUser = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO t_users
                        (name, email, password, role, created_at, updated_at)
                     VALUES (?, ?, ?, 'guru', NOW(), NOW())"
                );

                mysqli_stmt_bind_param(
                    $stmtUser,
                    "sss",
                    $nama,
                    $email,
                    $passwordHash
                );

                mysqli_stmt_execute($stmtUser);
                $user_id = mysqli_insert_id($koneksi);
                mysqli_stmt_close($stmtUser);

                // Simpan data guru
                $status = (int) $status_aktif;

                $stmtGuru = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO t_guru
                        (nip, nama, email, status_aktif, user_id,
                         created_at, updated_at)
                     VALUES (?, ?, ?, ?, ?, NOW(), NOW())"
                );

                mysqli_stmt_bind_param(
                    $stmtGuru,
                    "sssii",
                    $nip,
                    $nama,
                    $email,
                    $status,
                    $user_id
                );

                mysqli_stmt_execute($stmtGuru);
                mysqli_stmt_close($stmtGuru);

                mysqli_commit($koneksi);

                header("Location: data_guru.php?pesan=tambah");
                exit;
            }

        } catch (Throwable $e) {
            mysqli_rollback($koneksi);

            $error = "Data guru gagal disimpan. Periksa kembali data dan database.";
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
<?php include "includes/navbar.php"; ?>

<main class="konten-utama">

    <h2>Tambah Data Guru</h2>
    <p class="text-muted mb-4">Masukkan informasi guru baru.</p>

    <?php if ($error != ''): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

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
                        placeholder="Masukkan 18 digit NIP"
                        required
                        value="<?= htmlspecialchars($nip, ENT_QUOTES, 'UTF-8'); ?>"
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
                        value="<?= htmlspecialchars($nama, ENT_QUOTES, 'UTF-8'); ?>"
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
                        value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Password Akun</label>
                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >
                    <small class="text-muted">
                        Minimal 8 karakter.
                    </small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Konfirmasi Password</label>
                    <input
                        type="password"
                        name="konfirmasi_password"
                        class="form-control"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >
                    <small class="text-muted">
                        Masukkan kembali password yang sama.
                    </small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status_aktif" class="form-select" required>
                        <option value="1"
                            <?= $status_aktif == '1' ? 'selected' : ''; ?>>
                            Aktif
                        </option>

                        <option value="0"
                            <?= $status_aktif == '0' ? 'selected' : ''; ?>>
                            Tidak Aktif
                        </option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    Simpan
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
