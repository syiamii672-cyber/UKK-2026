<?php

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$nama = $_SESSION['name'];
$role = $_SESSION['role'];

?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Sistem Informasi Pelanggaran Siswa</title>
</head>
<body>

    <h2>Dashboard</h2>

    <p>
        Selamat datang, <?php echo $nama; ?>
    </p>

    <p>
        Role: <?php echo ucfirst($role); ?>
    </p>

    <hr>

    <h3>Menu</h3>

    <?php if ($role == "admin") { ?>

        <h4>Menu 1 - Kelola Data</h4>
        <p>
            <a href="#">Kelola Data</a>
        </p>

        <h4>Menu 2 - Data Pelanggaran</h4>
        <p>
            <a href="#">Data Pelanggaran</a>
        </p>

    <?php } ?>

    <h4>Menu 3 - Catat Pelanggaran</h4>
    <p>
        <a href="#">Catat Pelanggaran</a>
    </p>

    <h4>Menu 4 - Laporan</h4>
    <p>
        <a href="#">Laporan</a>
    </p>

    <hr>

    <p>
        <a href="logout.php">Logout</a>
    </p>

</body>
</html>