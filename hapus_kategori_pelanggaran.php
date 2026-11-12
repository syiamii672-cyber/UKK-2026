<?php
include "includes/cek_session.php";
include "config/koneksi.php";

$role = strtolower(trim($_SESSION['role'] ?? ''));

if ($role !== "admin") {
    header("Location: dashboard.php");
    exit;
}

// Proses hanya menerima POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: kategori_pelanggaran.php");
    exit;
}

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    header("Location: kategori_pelanggaran.php?pesan=gagal");
    exit;
}

// Pastikan kategori tersedia.
$sqlCek = "SELECT id FROM t_pelanggaran_kategori WHERE id = ? LIMIT 1";
$stmtCek = mysqli_prepare($koneksi, $sqlCek);
mysqli_stmt_bind_param($stmtCek, "i", $id);
mysqli_stmt_execute($stmtCek);

$hasilCek = mysqli_stmt_get_result($stmtCek);

if (mysqli_num_rows($hasilCek) === 0) {
    header("Location: kategori_pelanggaran.php?pesan=gagal");
    exit;
}

// Hapus kategori.
// Jika masih digunakan oleh tabel lain melalui foreign key,
// database akan menolak penghapusan.
$sqlHapus = "DELETE FROM t_pelanggaran_kategori WHERE id = ?";
$stmtHapus = mysqli_prepare($koneksi, $sqlHapus);
mysqli_stmt_bind_param($stmtHapus, "i", $id);

if (mysqli_stmt_execute($stmtHapus)) {
    header("Location: kategori_pelanggaran.php?pesan=hapus");
    exit;
}

// Error 1451 berarti data masih direferensikan tabel lain.
$errorDatabase = mysqli_stmt_errno($stmtHapus);

if ($errorDatabase === 1451 || $errorDatabase === 1452) {
    header("Location: kategori_pelanggaran.php?pesan=gagal_hapus");
    exit;
}

header("Location: kategori_pelanggaran.php?pesan=gagal");
exit;
?>