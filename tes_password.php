<?php
include "config/koneksi.php";

$email = "jg@gmail.com";

$query = mysqli_prepare(
    $koneksi,
    "SELECT password FROM t_users WHERE email = ?"
);

mysqli_stmt_bind_param($query, "s", $email);
mysqli_stmt_execute($query);

$result = mysqli_stmt_get_result($query);
$data = mysqli_fetch_assoc($result);

if ($data) {
    $passwordTes = "ISI_PASSWORD_YANG_KAMU_COBALOGIN";

    if (password_verify($passwordTes, $data['password'])) {
        echo "PASSWORD BENAR";
    } else {
        echo "PASSWORD SALAH";
    }
} else {
    echo "AKUN TIDAK DITEMUKAN";
}