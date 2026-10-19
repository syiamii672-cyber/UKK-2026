
<?php

session_start();
include "config/koneksi.php";

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$query = "SELECT id, name, email, password, role
          FROM t_users
          WHERE email = ?
          LIMIT 1";

$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if ($data) {

    $passwordTersimpan = $data['password'];

    // Periksa password hash terlebih dahulu.
    $passwordBenar = password_verify(
        $password,
        $passwordTersimpan
    );

    // Kompatibilitas sementara untuk password lama
    // yang masih tersimpan sebagai teks biasa.
    if (
        !$passwordBenar &&
        !str_starts_with($passwordTersimpan, '$2y$') &&
        !str_starts_with($passwordTersimpan, '$argon2')
    ) {
        $passwordBenar = hash_equals(
            $passwordTersimpan,
            $password
        );

        // Jika password lama benar, ubah menjadi hash.
        if ($passwordBenar) {
            $hashBaru = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $update = mysqli_prepare(
                $koneksi,
                "UPDATE t_users SET password = ? WHERE id = ?"
            );

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

    if (
        $passwordBenar &&
        in_array($data['role'], ['admin', 'guru'], true)
    ) {
        session_regenerate_id(true);

        $_SESSION['id'] = $data['id'];
        $_SESSION['name'] = $data['name'];
        $_SESSION['email'] = $data['email'];
        $_SESSION['role'] = $data['role'];

        header("Location: dashboard.php");
        exit;
    }
}

echo "Email atau password tidak sesuai.";

mysqli_stmt_close($stmt);
mysqli_close($koneksi);

?>
