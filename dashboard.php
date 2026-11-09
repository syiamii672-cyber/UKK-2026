
<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

include "config/koneksi.php";
require_once "includes/cek_wali_kelas.php";

$nama = $_SESSION['name'] ?? '';
$role = $_SESSION['role'] ?? '';

$menuAktif = "dashboard";

// Total data untuk Dashboard Admin
$totalSiswa = 0;
$totalGuru = 0;
$totalKelas = 0;

if ($role === "admin") {

    $querySiswa = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM t_siswa");
    $queryGuru = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM t_guru");
    $queryKelas = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM t_kelas");

    if ($querySiswa) {
        $totalSiswa = mysqli_fetch_assoc($querySiswa)['total'];
    }

    if ($queryGuru) {
        $totalGuru = mysqli_fetch_assoc($queryGuru)['total'];
    }

    if ($queryKelas) {
        $totalKelas = mysqli_fetch_assoc($queryKelas)['total'];
    }
}
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
            transition: 0.2s;
        }

        .kartu-menu {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .kartu-menu:hover .kartu-dashboard {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.10) !important;
        }

        @media (max-width: 575.98px) {
            .konten-utama {
                margin-left: 220px;
                width: calc(100% - 220px);
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
        Kamu login sebagai
        <?= $role === "admin"
            ? "Administrator"
            : ($isWaliKelas ? "Guru / Wali Kelas" : "Guru"); ?>.
    </p>

    <div class="row g-3">

        <?php if ($role === "admin"): ?>

            <!-- Dashboard Admin -->

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

        <?php elseif ($role === "guru"): ?>

            <!-- Informasi Guru -->

            <div class="col-12 mb-2">
                <div class="card kartu-dashboard shadow-sm">
                    <div class="card-body">

                        <h5 class="fw-bold">
                            <?= $isWaliKelas ? "Dashboard Wali Kelas" : "Dashboard Guru"; ?>
                        </h5>

                        <?php if ($isWaliKelas): ?>

                            <p class="text-secondary mb-2">
                                Anda bertugas sebagai Wali Kelas.
                                Silakan gunakan menu untuk mencatat pelanggaran,
                                mengelola tindakan, memantau rekap poin,
                                riwayat, dan laporan siswa.
                            </p>

                            <span class="badge bg-primary">
                                Wali Kelas <?= htmlspecialchars($namaKelasWali); ?>
                            </span>

                        <?php else: ?>

                            <p class="text-secondary mb-0">
                                Silakan gunakan menu untuk mencatat pelanggaran,
                                mengelola tindakan, melihat rekap poin,
                                dan memantau riwayat pelanggaran siswa.
                            </p>

                        <?php endif; ?>

                    </div>
                </div>
            </div>

            <!-- Menu yang dimiliki semua Guru -->

            <div class="col-12 col-md-6 col-lg-4">
                <a href="catat_pelanggaran.php" class="kartu-menu">
                    <div class="card kartu-dashboard shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-secondary">Catat Pelanggaran</h6>
                            <h5 class="fw-bold mt-3">Catat Pelanggaran Siswa</h5>
                            <p class="text-muted small mb-0">
                                Masukkan catatan pelanggaran siswa.
                            </p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-12 col-md-6 col-lg-4">
                <a href="tindakan.php" class="kartu-menu">
                    <div class="card kartu-dashboard shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-secondary">Tindakan</h6>
                            <h5 class="fw-bold mt-3">Kelola Tindakan</h5>
                            <p class="text-muted small mb-0">
                                Lihat dan kelola tindak lanjut pelanggaran.
                            </p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-12 col-md-6 col-lg-4">
                <a href="rekap_poin.php" class="kartu-menu">
                    <div class="card kartu-dashboard shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-secondary">Rekap Poin</h6>
                            <h5 class="fw-bold mt-3">Lihat Rekap Poin</h5>
                            <p class="text-muted small mb-0">
                                Pantau jumlah pelanggaran dan total poin siswa.
                            </p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-12 col-md-6 col-lg-4">
                <a href="riwayat_pelanggaran.php" class="kartu-menu">
                    <div class="card kartu-dashboard shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-secondary">Riwayat</h6>
                            <h5 class="fw-bold mt-3">Lihat Riwayat</h5>
                            <p class="text-muted small mb-0">
                                Lihat riwayat pelanggaran siswa.
                            </p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Menu tambahan khusus Wali Kelas -->

            <?php if ($isWaliKelas): ?>

                <div class="col-12 col-md-6 col-lg-4">
                    <a href="laporan.php" class="kartu-menu">
                        <div class="card kartu-dashboard shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-secondary">Laporan</h6>
                                <h5 class="fw-bold mt-3">Lihat Laporan</h5>
                                <p class="text-muted small mb-0">
                                    Lihat laporan pelanggaran siswa kelas Anda.
                                </p>
                            </div>
                        </div>
                    </a>
                </div>

            <?php endif; ?>

        <?php endif; ?>

    </div>

</main>

</body>
</html>
