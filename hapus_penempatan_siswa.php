
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] != "admin") {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: penempatan_siswa.php");
    exit;
}

$id = (int)($_POST['id'] ?? 0);

if ($id < 1) {
    header("Location: penempatan_siswa.php?pesan=gagal_hapus");
    exit;
}

$stmt = mysqli_prepare(
    $koneksi,
    "DELETE FROM t_kelas_siswa WHERE id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id);

try {
    if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
        header("Location: penempatan_siswa.php?pesan=hapus");
        exit;
    }

    header("Location: penempatan_siswa.php?pesan=gagal_hapus");
    exit;
} catch (mysqli_sql_exception $e) {
    header("Location: penempatan_siswa.php?pesan=gagal_hapus");
    exit;
}
?>
