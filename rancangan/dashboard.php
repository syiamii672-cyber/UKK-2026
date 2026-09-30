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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
<html>
<head>
    <title>Dashboard - Sistem Informasi Pelanggaran Siswa</title>
</head>
<body>
<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-md-3 col-lg-2 bg-dark min-vh-100 p-3">

            <h4 class="text-white mb-4">
                STUDENT CARE
            </h4>

            <ul class="nav nav-pills flex-column">

                <li class="nav-item mb-2">
                    <a href="Dashboard" class="nav-link active">
                        Dashboard
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="Catat" class="nav-link text-white">
                        Catat Pelanggaran
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="Tindakan" class="nav-link text-white">
                        Tindakan
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="Laporan" class="nav-link text-white">
                        Laporan
                    </a>
                </li>

                <li class="nav-item mb-2">
                    <a href="Rekap Poin"class="nav-link text-white">Rekap Poin</a>
                </li>

                <li class="nav-item mb-2">
                    <a href="About Me"class="nav-link text-white">About Me</a>
                </li>

                <hr class="text-secondary">

                <li class="nav-item">
                    <p>
                    <a href="logout.php "class="nav-link text-white">Logout</a>
                    </p>
                </li>

            </ul>

        </div>


        <!-- KONTEN -->
        <main class="col-md-9 col-lg-10 p-4">

            <h2>Dashboard</h2>

            <p>
                Selamat datang di halaman dashboard.
            </p>

            <div class="row">

                <!-- CARD DATA SISWA -->
                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm">

                        <div class="card-body">

                            <h5 class="card-title">
                                Data Siswa
                            </h5>

                            <h2>
                                120
                            </h2>

                        </div>

                    </div>
                </div>


                <!-- CARD DATA GURU -->
                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm">

                        <div class="card-body">

                            <h5 class="card-title">
                                Data Guru
                            </h5>

                            <h2>
                                25
                            </h2>

                        </div>

                    </div>
                </div>


                <!-- CARD KELAS -->
                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm">

                        <div class="card-body">

                            <h5 class="card-title">
                                Kelas
                            </h5>

                            <h2>
                                12
                            </h2>

                        </div>

                    </div>
                </div>

            </div>

        </main>

    </div>
</div>

    <!-- <h2>Dashboard</h2>

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


    <h4>Menu 4 - Laporan</h4>
    <p>
        
    </p>

    <hr>

    


</body>
</html> -->


    <!-- <h2>Dashboard</h2>

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
</html> -->