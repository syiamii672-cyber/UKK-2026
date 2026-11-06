<?php
include "config/koneksi.php";

$akun = [
    "aldi@gmail.com" => "Guru12345!",
    "ari@gmail.com" => "Guru12345!"
];

foreach ($akun as $email => $password) {

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT password FROM t_users WHERE email = ? LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);

    echo "<hr>";
    echo "Email: " . htmlspecialchars($email) . "<br>";

    if ($data) {
        if (password_verify($password, $data['password'])) {
            echo "HASIL: PASSWORD COCOK";
        } else {
            echo "HASIL: PASSWORD TIDAK COCOK";
        }
    } else {
        echo "HASIL: AKUN TIDAK DITEMUKAN";
    }

    mysqli_stmt_close($stmt);
}

mysqli_close($koneksi);
?>