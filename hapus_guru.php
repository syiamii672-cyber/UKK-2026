
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

// Hanya admin yang boleh menghapus guru
if ($_SESSION['role'] !== "admin") {
    header("Location: dashboard.php");
    exit;
}

// Penghapusan hanya boleh melalui POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: data_guru.php");
    exit;
}

// Ambil ID guru
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: data_guru.php?pesan=gagal_hapus");
    exit;
}

// Periksa apakah guru memiliki riwayat pelanggaran
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT COUNT(*) AS jumlah
     FROM t_pelanggaran_siswa
     WHERE guru_id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

// Jika masih memiliki riwayat, jangan hapus guru
if ($data['jumlah'] > 0) {
    header("Location: data_guru.php?pesan=guru_digunakan");
    exit;
}

// Hapus guru jika tidak memiliki riwayat pelanggaran
$stmt = mysqli_prepare(
    $koneksi,
    "DELETE FROM t_guru WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);

try {
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) > 0) {
        mysqli_stmt_close($stmt);
        header("Location: data_guru.php?pesan=hapus");
        exit;
    }

    mysqli_stmt_close($stmt);
    header("Location: data_guru.php?pesan=gagal_hapus");
    exit;

} catch (mysqli_sql_exception $e) {
    mysqli_stmt_close($stmt);

    // Jika masih terhubung dengan tabel lain
    header("Location: data_guru.php?pesan=guru_digunakan");
    exit;
}
?>
