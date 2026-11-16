
<?php

// Memulai session jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mencegah halaman tersimpan di cache browser
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Memeriksa session pengguna
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit;
}

?>
