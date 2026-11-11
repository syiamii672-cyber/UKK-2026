
<?php
require_once __DIR__ . '/config/koneksi.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Pastikan akun admin tersedia
    $cekAdmin = mysqli_query(
        $koneksi,
        "SELECT COUNT(*) AS total
         FROM t_users
         WHERE id = 1 AND role = 'admin'"
    );
    $admin = mysqli_fetch_assoc($cekAdmin);

    // Pastikan 55 akun guru tersedia
    $cekGuru = mysqli_query(
        $koneksi,
        "SELECT COUNT(*) AS total
         FROM t_users
         WHERE id BETWEEN 51 AND 105
         AND role = 'guru'"
    );
    $guru = mysqli_fetch_assoc($cekGuru);

    if ((int)$admin['total'] !== 1 || (int)$guru['total'] !== 55) {
        throw new Exception(
            "Jumlah akun tidak sesuai. Password tidak diubah."
        );
    }

    // Buat hash password
    $hashAdmin = password_hash('admin123', PASSWORD_DEFAULT);
    $hashGuru = password_hash('guru123', PASSWORD_DEFAULT);

    mysqli_begin_transaction($koneksi);

    // Ubah password admin
    $stmtAdmin = mysqli_prepare(
        $koneksi,
        "UPDATE t_users
         SET password = ?, updated_at = NOW()
         WHERE id = 1 AND role = 'admin'"
    );

    mysqli_stmt_bind_param($stmtAdmin, "s", $hashAdmin);
    mysqli_stmt_execute($stmtAdmin);

    if (mysqli_stmt_affected_rows($stmtAdmin) !== 1) {
        throw new Exception("Password admin gagal diperbarui.");
    }

    // Ubah password seluruh guru
    $stmtGuru = mysqli_prepare(
        $koneksi,
        "UPDATE t_users
         SET password = ?, updated_at = NOW()
         WHERE id BETWEEN 51 AND 105
         AND role = 'guru'"
    );

    mysqli_stmt_bind_param($stmtGuru, "s", $hashGuru);
    mysqli_stmt_execute($stmtGuru);

    if (mysqli_stmt_affected_rows($stmtGuru) !== 55) {
        throw new Exception("Password guru gagal diperbarui seluruhnya.");
    }

    mysqli_commit($koneksi);

    echo "BERHASIL!<br>";
    echo "Password admin berhasil diperbarui.<br>";
    echo "Password 55 akun guru berhasil diperbarui.";

} catch (Throwable $e) {
    mysqli_rollback($koneksi);
    echo "GAGAL: " . htmlspecialchars($e->getMessage());
}
