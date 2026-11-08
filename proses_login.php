
<?php

session_start();
include "config/koneksi.php";

// Pastikan proses login menggunakan POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    header("Location: login.php?error=1");
    exit;
}

// Cari akun berdasarkan email.
$query = "SELECT id, name, email, password, role
          FROM t_users
          WHERE email = ?
          LIMIT 1";

$stmt = mysqli_prepare($koneksi, $query);

if (!$stmt) {
    header("Location: login.php?error=1");
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

$loginBerhasil = false;

if ($data) {

    // Hanya admin dan guru yang boleh login.
    $roleDiizinkan = in_array(
        $data['role'],
        ['admin', 'guru'],
        true
    );

    if ($roleDiizinkan) {

        $passwordTersimpan = $data['password'];

        // Verifikasi password yang sudah di-hash.
        $passwordBenar = password_verify(
            $password,
            $passwordTersimpan
        );

        // Kompatibilitas sementara untuk password lama.
        if (
            !$passwordBenar &&
            !str_starts_with($passwordTersimpan, '$2y$') &&
            !str_starts_with($passwordTersimpan, '$argon2')
        ) {

            $passwordBenar = hash_equals(
                $passwordTersimpan,
                $password
            );

            // Ubah password lama menjadi hash.
            if ($passwordBenar) {

                $hashBaru = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $update = mysqli_prepare(
                    $koneksi,
                    "UPDATE t_users SET password = ? WHERE id = ?"
                );

                if ($update) {
                    mysqli_stmt_bind_param(
                        $update,
                        "si",
                        $hashBaru,
                        $data['id']
                    );

                    mysqli_stmt_execute($update);
                    mysqli_stmt_close($update);
                }
            }
        }

        if ($passwordBenar) {
            $loginBerhasil = true;
        }
    }
}

mysqli_stmt_close($stmt);
mysqli_close($koneksi);

// Jika login berhasil.
if ($loginBerhasil) {

    session_regenerate_id(true);

    $_SESSION['id'] = $data['id'];
    $_SESSION['name'] = $data['name'];
    $_SESSION['email'] = $data['email'];
    $_SESSION['role'] = $data['role'];

    header("Location: dashboard.php");
    exit;
}

// Jika login gagal.
header("Location: login.php?error=1");
exit;

?>
