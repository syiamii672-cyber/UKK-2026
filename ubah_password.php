
<?php
require_once 'config/koneksi.php';
require_once 'includes/cek_session.php';

$db = $conn ?? $koneksi ?? null;

if (!$db) {
    die("Koneksi database tidak ditemukan. Periksa config/koneksi.php.");
}

$idUser = $_SESSION['id'] ?? 0;

if (!$idUser) {
    header("Location: login.php");
    exit;
}

$namaPengguna = $_SESSION['name'] ?? 'Pengguna';
$rolePengguna = strtolower($_SESSION['role'] ?? 'guru');

$pesan = '';
$jenisPesan = '';

if (empty($_SESSION['csrf_ubah_password'])) {
    $_SESSION['csrf_ubah_password'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_ubah_password'], $token)) {
        $pesan = "Permintaan tidak valid. Silakan muat ulang halaman.";
        $jenisPesan = "danger";

    } else {

        $passwordLama = $_POST['password_lama'] ?? '';
        $passwordBaru = $_POST['password_baru'] ?? '';
        $konfirmasiPassword = $_POST['konfirmasi_password'] ?? '';

        if (
            $passwordLama === '' ||
            $passwordBaru === '' ||
            $konfirmasiPassword === ''
        ) {
            $pesan = "Semua kolom wajib diisi.";
            $jenisPesan = "danger";

        } elseif (strlen($passwordBaru) < 6) {
            $pesan = "Kata sandi baru minimal 6 karakter.";
            $jenisPesan = "danger";

        } elseif ($passwordBaru !== $konfirmasiPassword) {
            $pesan = "Konfirmasi kata sandi baru tidak sama.";
            $jenisPesan = "danger";

        } elseif ($passwordLama === $passwordBaru) {
            $pesan = "Kata sandi baru tidak boleh sama dengan kata sandi lama.";
            $jenisPesan = "danger";

        } else {

            // Ambil password akun yang sedang login
            $stmt = mysqli_prepare(
                $db,
                "SELECT password FROM t_users WHERE id = ? LIMIT 1"
            );

            mysqli_stmt_bind_param($stmt, "i", $idUser);
            mysqli_stmt_execute($stmt);

            $hasil = mysqli_stmt_get_result($stmt);
            $dataUser = mysqli_fetch_assoc($hasil);

            mysqli_stmt_close($stmt);

            if (!$dataUser) {
                $pesan = "Data akun tidak ditemukan.";
                $jenisPesan = "danger";

            } elseif (!password_verify($passwordLama, $dataUser['password'])) {
                $pesan = "Kata sandi lama yang kamu masukkan salah.";
                $jenisPesan = "danger";

            } else {

                // Hash password baru
                $hashPassword = password_hash($passwordBaru, PASSWORD_DEFAULT);

                $stmtUpdate = mysqli_prepare(
                    $db,
                    "UPDATE t_users SET password = ?, updated_at = NOW() WHERE id = ?"
                );

                mysqli_stmt_bind_param(
                    $stmtUpdate,
                    "si",
                    $hashPassword,
                    $idUser
                );

                if (mysqli_stmt_execute($stmtUpdate)) {
                    $pesan = "Kata sandi berhasil diperbarui.";
                    $jenisPesan = "success";

                    // Kosongkan token agar form tidak dikirim ulang
                    $_SESSION['csrf_ubah_password'] = bin2hex(random_bytes(32));

                } else {
                    $pesan = "Gagal memperbarui kata sandi. Silakan coba kembali.";
                    $jenisPesan = "danger";
                }

                mysqli_stmt_close($stmtUpdate);
            }
        }
    }
}

$judulHalaman = "Ubah Kata Sandi";
$menuAktif = "profil";

function amanPassword($nilai)
{
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Ubah Kata Sandi - Sistem Informasi Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body {
            background: #F1F5FB;
            font-family: Arial, sans-serif;
            margin: 0;
        }

        .konten-utama {
            margin-left: 220px;
            width: calc(100% - 220px);
            min-height: 100vh;
            padding: 100px 28px 30px;
            box-sizing: border-box;
        }

        .judul-halaman {
            color: #173B67;
            font-weight: 700;
        }

        .kartu-password {
            background: white;
            border: 1px solid #E3EAF3;
            border-radius: 16px;
            padding: 28px;
        }

        .ikon-password {
            width: 55px;
            height: 55px;
            border-radius: 13px;
            background: #E8F0FB;
            color: #245B96;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 25px;
            margin-bottom: 18px;
        }

        .form-label {
            color: #34465C;
            font-weight: 600;
            font-size: 14px;
        }

        .form-control {
            padding: 11px 13px;
            border-radius: 8px;
            border-color: #DCE4EF;
        }

        .form-control:focus {
            border-color: #3779C5;
            box-shadow: 0 0 0 0.2rem rgba(55, 121, 197, 0.15);
        }

        .tombol-simpan {
            background: #245B96;
            color: white;
            border: none;
            padding: 11px 20px;
            border-radius: 8px;
            font-weight: 600;
        }

        .tombol-simpan:hover {
            background: #173B67;
            color: white;
        }

        .tombol-kembali {
            background: #E9EEF5;
            color: #34465C;
            padding: 11px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }

        .tombol-kembali:hover {
            background: #DCE4EF;
            color: #173B67;
        }

        .petunjuk {
            background: #F1F5FB;
            border-radius: 10px;
            padding: 15px;
            color: #52647A;
            font-size: 13px;
        }

        @media (max-width: 768px) {
            .konten-utama {
                margin-left: 220px;
                width: calc(100% - 220px);
                padding: 100px 12px 20px;
            }

            .kartu-password {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

<?php
include 'includes/sidebar.php';
include 'includes/navbar.php';
?>

<main class="konten-utama">

    <div class="mb-4">
        <h3 class="judul-halaman mb-1">Ubah Kata Sandi</h3>
        <p class="text-secondary mb-0">
            Perbarui kata sandi untuk menjaga keamanan akun.
        </p>
    </div>

    <div class="row">
        <div class="col-lg-7 col-xl-6">

            <div class="kartu-password">

                <div class="ikon-password">
                    <i class="bi bi-shield-lock"></i>
                </div>

                <h5 class="fw-bold mb-1" style="color:#173B67;">
                    Keamanan Akun
                </h5>

                <p class="text-secondary mb-4">
                    Halo, <?= amanPassword($namaPengguna); ?>.
                    Silakan isi formulir berikut untuk mengganti kata sandi.
                </p>

                <?php if ($pesan !== ''): ?>
                    <div class="alert alert-<?= amanPassword($jenisPesan); ?> alert-dismissible fade show" role="alert">
                        <?= amanPassword($pesan); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= amanPassword($_SESSION['csrf_ubah_password']); ?>"
                    >

                    <div class="mb-3">
                        <label class="form-label" for="password_lama">
                            Kata Sandi Lama
                        </label>

                        <input
                            type="password"
                            class="form-control"
                            id="password_lama"
                            name="password_lama"
                            placeholder="Masukkan kata sandi lama"
                            autocomplete="current-password"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password_baru">
                            Kata Sandi Baru
                        </label>

                        <input
                            type="password"
                            class="form-control"
                            id="password_baru"
                            name="password_baru"
                            placeholder="Masukkan kata sandi baru"
                            minlength="6"
                            autocomplete="new-password"
                            required
                        >

                        <small class="text-secondary">
                            Minimal 6 karakter.
                        </small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="konfirmasi_password">
                            Konfirmasi Kata Sandi Baru
                        </label>

                        <input
                            type="password"
                            class="form-control"
                            id="konfirmasi_password"
                            name="konfirmasi_password"
                            placeholder="Ulangi kata sandi baru"
                            minlength="6"
                            autocomplete="new-password"
                            required
                        >
                    </div>

                    <div class="petunjuk mb-4">
                        <i class="bi bi-info-circle me-2"></i>
                        Pastikan kata sandi baru mudah kamu ingat
                        dan tidak dibagikan kepada orang lain.
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="tombol-simpan">
                            <i class="bi bi-check-circle me-1"></i>
                            Simpan Kata Sandi
                        </button>

                        <a href="profil.php" class="tombol-kembali">
                            Kembali ke Profil
                        </a>
                    </div>

                </form>

            </div>

        </div>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
