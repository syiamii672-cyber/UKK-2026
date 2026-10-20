```php
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

// Hanya admin yang boleh menghapus
if ($_SESSION['role'] != "admin") {
    header("Location: dashboard.php");
    exit;
}

// Hanya menerima penghapusan melalui POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: tahun_ajaran.php");
    exit;
}

// Ambil dan validasi ID
$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

if ($id <= 0) {
    header("Location: tahun_ajaran.php?pesan=gagal_hapus");
    exit;
}

// Pastikan tahun ajaran tersedia
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id FROM t_tahun_ajaran WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$hasil = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($hasil);

mysqli_stmt_close($stmt);

if (!$data) {
    header("Location: tahun_ajaran.php?pesan=data_tidak_ditemukan");
    exit;
}

// Periksa apakah tahun ajaran masih digunakan tabel lain
$tabelRelasi = [
    "t_kelas_siswa",
    "t_pelanggaran_siswa",
    "t_wali_kelas"
];

foreach ($tabelRelasi as $tabel) {
    $sql = "SELECT id FROM `$tabel`
            WHERE tahun_ajaran_id = ?
            LIMIT 1";

    $stmt = mysqli_prepare($koneksi, $sql);

    if (!$stmt) {
        header("Location: tahun_ajaran.php?pesan=gagal_hapus");
        exit;
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $hasil = mysqli_stmt_get_result($stmt);
    $adaRelasi = mysqli_num_rows($hasil) > 0;

    mysqli_stmt_close($stmt);

    if ($adaRelasi) {
        header("Location: tahun_ajaran.php?pesan=tahun_digunakan");
        exit;
    }
}

// Hapus jika tidak digunakan oleh tabel relasi
$stmt = mysqli_prepare(
    $koneksi,
    "DELETE FROM t_tahun_ajaran WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header("Location: tahun_ajaran.php?pesan=hapus");
    exit;
}

mysqli_stmt_close($stmt);

header("Location: tahun_ajaran.php?pesan=gagal_hapus");
exit;
?>
```