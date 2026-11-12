<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] !== "admin") {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: jenis_pelanggaran.php");
    exit;
}

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    header("Location: jenis_pelanggaran.php?pesan=gagal");
    exit;
}

try {
    // Pastikan data tersedia.
    $cek = mysqli_prepare(
        $koneksi,
        "SELECT id FROM t_pelanggaran WHERE id = ?"
    );

    mysqli_stmt_bind_param($cek, "i", $id);
    mysqli_stmt_execute($cek);

    if (mysqli_num_rows(mysqli_stmt_get_result($cek)) === 0) {
        header("Location: jenis_pelanggaran.php?pesan=gagal");
        exit;
    }

    // Cek apakah jenis pelanggaran sudah pernah digunakan.
    $cekRelasi = mysqli_prepare(
        $koneksi,
        "SELECT id FROM t_pelanggaran_siswa
         WHERE pelanggaran_id = ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param($cekRelasi, "i", $id);
    mysqli_stmt_execute($cekRelasi);

    if (mysqli_num_rows(mysqli_stmt_get_result($cekRelasi)) > 0) {
        header("Location: jenis_pelanggaran.php?pesan=gagal_hapus");
        exit;
    }

    // Hapus data.
    $hapus = mysqli_prepare(
        $koneksi,
        "DELETE FROM t_pelanggaran WHERE id = ?"
    );

    mysqli_stmt_bind_param($hapus, "i", $id);
    mysqli_stmt_execute($hapus);

    header("Location: jenis_pelanggaran.php?pesan=hapus");
    exit;

} catch (Throwable $e) {
    header("Location: jenis_pelanggaran.php?pesan=gagal");
    exit;
}