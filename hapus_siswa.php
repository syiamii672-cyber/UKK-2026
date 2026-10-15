
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] != "admin") {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: siswa.php");
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: siswa.php?pesan=gagal_hapus");
    exit;
}

// Periksa apakah siswa masih digunakan di data kelas
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT COUNT(*) AS total FROM t_kelas_siswa WHERE siswa_id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$dataKelas = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

// Periksa apakah siswa masih memiliki riwayat pelanggaran
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT COUNT(*) AS total FROM t_pelanggaran_siswa WHERE siswa_id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$dataPelanggaran = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if ($dataKelas['total'] > 0 || $dataPelanggaran['total'] > 0) {
    header("Location: siswa.php?pesan=gagal_hapus");
    exit;
}

// Hapus data siswa
$stmt = mysqli_prepare($koneksi, "DELETE FROM t_siswa WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
$berhasil = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if ($berhasil) {
    header("Location: siswa.php?pesan=hapus");
} else {
    header("Location: siswa.php?pesan=gagal_hapus");
}
exit;