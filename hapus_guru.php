
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] != "admin") {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: data_guru.php");
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: data_guru.php?pesan=gagal_hapus");
    exit;
}

$stmt = mysqli_prepare($koneksi, "DELETE FROM t_guru WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
    header("Location: data_guru.php?pesan=hapus");
} else {
    header("Location: data_guru.php?pesan=gagal_hapus");
}

mysqli_stmt_close($stmt);
exit;