```php
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

// Hanya admin yang boleh menghapus data kelas
if ($_SESSION['role'] != "admin") {
    header("Location: dashboard.php");
    exit;
}

// Penghapusan hanya boleh melalui POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: kelas.php");
    exit;
}

// Ambil ID kelas
$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

if ($id <= 0) {
    header("Location: kelas.php?pesan=gagal_hapus");
    exit;
}

// Pastikan kelas tersedia
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id FROM t_kelas WHERE id = ? LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$hasil = mysqli_stmt_get_result($stmt);
$kelas = mysqli_fetch_assoc($hasil);

mysqli_stmt_close($stmt);

if (!$kelas) {
    header("Location: kelas.php?pesan=gagal_hapus");
    exit;
}

// Cek apakah kelas masih digunakan untuk penempatan siswa
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT COUNT(*) AS jumlah
     FROM t_kelas_siswa
     WHERE kelas_id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$hasil = mysqli_stmt_get_result($stmt);
$dataSiswa = mysqli_fetch_assoc($hasil);

mysqli_stmt_close($stmt);

// Cek apakah kelas masih digunakan untuk penugasan wali kelas
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT COUNT(*) AS jumlah
     FROM t_wali_kelas
     WHERE kelas_id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$hasil = mysqli_stmt_get_result($stmt);
$dataWali = mysqli_fetch_assoc($hasil);

mysqli_stmt_close($stmt);

// Jangan hapus jika kelas masih memiliki relasi
if (
    (int) $dataSiswa['jumlah'] > 0 ||
    (int) $dataWali['jumlah'] > 0
) {
    header("Location: kelas.php?pesan=kelas_digunakan");
    exit;
}

// Hapus data kelas jika tidak digunakan
$stmt = mysqli_prepare(
    $koneksi,
    "DELETE FROM t_kelas WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header("Location: kelas.php?pesan=hapus");
    exit;
}

mysqli_stmt_close($stmt);

header("Location: kelas.php?pesan=gagal_hapus");
exit;
?>
```