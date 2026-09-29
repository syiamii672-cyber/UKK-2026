```php
<?php

session_start();

include "koneksi.php";

$email = $_POST['email'];
$password = $_POST['password'];

$query = "SELECT id, name, email, password, role 
          FROM t_users 
          WHERE email = ? 
          AND password = ?";

$stmt = mysqli_prepare($koneksi, $query);

mysqli_stmt_bind_param($stmt, "ss", $email, $password);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {

    $data = mysqli_fetch_assoc($result);

    $_SESSION['id'] = $data['id'];
    $_SESSION['name'] = $data['name'];
    $_SESSION['email'] = $data['email'];
    $_SESSION['role'] = $data['role'];

    header("Location: dashboard.php");
    exit;

} else {

    echo "Email atau password tidak sesuai.";

}

mysqli_stmt_close($stmt);
mysqli_close($koneksi);

?>
```
