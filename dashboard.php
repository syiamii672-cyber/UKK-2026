```php
<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

include "config/koneksi.php";

$nama = $_SESSION['name'];
$role = $_SESSION['role'];

$menuAktif = "dashboard";

// Mengambil jumlah data dari database
$querySiswa = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM t_siswa");
$totalSiswa = $querySiswa ? mysqli_fetch_assoc($querySiswa)['total'] : 0;

$queryGuru = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM t_guru");
$totalGuru = $queryGuru ? mysqli_fetch_assoc($queryGuru)['total'] : 0;

$queryKelas = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM t_kelas");
$totalKelas = $queryKelas ? mysqli_fetch_assoc($queryKelas)['total'] : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Informasi Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            margin: 0;
            background-color: #f8f9fa;
        }

        .konten-utama {
            margin-left: 220px;
            width: calc(100% - 220px);
            min-height: 100vh;
            padding: 24px;
            box-sizing: border-box;
        }

        .kartu-dashboard {
            border: none;
            border-radius: 10px;
        }

        @media (max-width: 575.98px) {
            .konten-utama {
                margin-left: 180px;
                width: calc(100% - 180px);
                padding: 15px;
            }
        }
    </style>
</head>

<body>

<?php include "includes/sidebar.php"; ?>

<main class="konten-utama">

    <h2 class="mb-1">Dashboard</h2>

    <p class="text-secondary mb-4">
        Selamat datang, <?= htmlspecialchars($nama); ?>!
        Kamu login sebagai <?= htmlspecialchars(ucfirst($role)); ?>.
    </p>

    <div class="row g-3">

        <?php if ($role == "admin") { ?>

            <div class="col-12 col-lg-4">
                <div class="card kartu-dashboard shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="text-secondary">Data Siswa</h6>
                        <h2><?= $totalSiswa; ?></h2>
                        <a href="siswa.php" class="btn btn-primary btn-sm">
                            Kelola Siswa
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card kartu-dashboard shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="text-secondary">Data Guru</h6>
                        <h2><?= $totalGuru; ?></h2>
                        <a href="data_guru.php" class="btn btn-primary btn-sm">
                            Kelola Guru
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card kartu-dashboard shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="text-secondary">Data Kelas</h6>
                        <h2><?= $totalKelas; ?></h2>
                        <a href="kelas.php" class="btn btn-primary btn-sm">
                            Kelola Kelas
                        </a>
                    </div>
                </div>
            </div>

        <?php } else { ?>

            <div class="col-12">
                <div class="card kartu-dashboard shadow-sm">
                    <div class="card-body">
                        <h5>Menu Guru</h5>
                        <p class="text-secondary mb-0">
                            Silakan gunakan menu yang tersedia untuk menjalankan tugas guru.
                        </p>
                    </div>
                </div>
            </div>

        <?php } ?>

    </div>

</main>

</body>
</html>
```