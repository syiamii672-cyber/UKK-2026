
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] !== "admin") {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: wali_kelas.php");
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    header("Location: wali_kelas.php?pesan=gagal_hapus");
    exit;
}

// Hapus penugasan wali kelas
$stmt = mysqli_prepare(
    $koneksi,
    "DELETE FROM t_wali_kelas WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
    header("Location: wali_kelas.php?pesan=hapus");
} else {
    header("Location: wali_kelas.php?pesan=gagal_hapus");
}

mysqli_stmt_close($stmt);
exit;
?>