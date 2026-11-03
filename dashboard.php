
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

$isWaliKelas = false;
$namaKelasWali = "";

// Mengambil jumlah data
$querySiswa = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM t_siswa");
$totalSiswa = $querySiswa ? mysqli_fetch_assoc($querySiswa)['total'] : 0;

$queryGuru = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM t_guru");
$totalGuru = $queryGuru ? mysqli_fetch_assoc($queryGuru)['total'] : 0;

$queryKelas = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM t_kelas");
$totalKelas = $queryKelas ? mysqli_fetch_assoc($queryKelas)['total'] : 0;

// Mengecek penugasan Wali Kelas
if ($role === "guru") {

    $idUser = (int)$_SESSION['id'];

    $queryGuruLogin = mysqli_query($koneksi, "
        SELECT id FROM t_guru
        WHERE user_id = $idUser
        AND status_aktif = 1
        LIMIT 1
    ");

    $guruLogin = $queryGuruLogin
        ? mysqli_fetch_assoc($queryGuruLogin)
        : null;

    if ($guruLogin) {

        $idGuru = (int)$guruLogin['id'];

        $queryWali = mysqli_query($koneksi, "
            SELECT k.nama AS nama_kelas
            FROM t_wali_kelas wk
            JOIN t_kelas k ON wk.kelas_id = k.id
            JOIN t_tahun_ajaran ta ON wk.tahun_ajaran_id = ta.id
            WHERE wk.guru_id = $idGuru
            AND wk.status_aktif = 1
            AND k.status_aktif = 1
            AND ta.status_aktif = 1
            LIMIT 1
        ");

        $dataWali = $queryWali
            ? mysqli_fetch_assoc($queryWali)
            : null;

        if ($dataWali) {
            $isWaliKelas = true;
            $namaKelasWali = $dataWali['nama_kelas'];
        }
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
            box-shadow: 0 8px 20px rgba(0,0,0,0.10) !important;
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

        <?php if ($role === "admin"): ?>

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

        <?php elseif ($role === "guru" && $isWaliKelas): ?>

            <div class="col-12 mb-2">
                <div class="card kartu-dashboard shadow-sm">
                    <div class="card-body">
                        <h5 class="fw-bold">Menu Wali Kelas</h5>
                        <p class="text-secondary mb-2">
                            Silakan gunakan menu berikut untuk memantau
                            poin dan riwayat pelanggaran siswa di kelas Anda.
                        </p>
                        <span class="badge bg-primary">
                            Wali Kelas <?= htmlspecialchars($namaKelasWali); ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <a href="rekap_poin.php" class="kartu-menu">
                    <div class="card kartu-dashboard shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-secondary">Rekap Poin</h6>
                            <h5 class="fw-bold mt-3">Lihat Rekap Poin</h5>
                            <p class="text-muted small mb-0">
                                Pantau poin pelanggaran siswa kelas Anda.
                            </p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-12 col-md-4">
                <a href="riwayat_pelanggaran.php" class="kartu-menu">
                    <div class="card kartu-dashboard shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-secondary">Riwayat Pelanggaran</h6>
                            <h5 class="fw-bold mt-3">Lihat Riwayat</h5>
                            <p class="text-muted small mb-0">
                                Lihat catatan pelanggaran siswa kelas Anda.
                            </p>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-12 col-md-4">
                <a href="laporan.php" class="kartu-menu">
                    <div class="card kartu-dashboard shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-secondary">Laporan</h6>
                            <h5 class="fw-bold mt-3">Lihat Laporan</h5>
                            <p class="text-muted small mb-0">
                                Lihat dan cetak laporan pelanggaran kelas Anda.
                            </p>
                        </div>
                    </div>
                </a>
            </div>

        <?php else: ?>

            <div class="col-12">
                <div class="card kartu-dashboard shadow-sm">
                    <div class="card-body">
                        <h5 class="fw-bold">Menu Guru</h5>
                        <p class="text-secondary mb-0">
                            Silakan gunakan menu yang tersedia untuk
                            menjalankan tugas guru.
                        </p>
                    </div>
                </div>
            </div>

        <?php endif; ?>

    </div>

</main>

</body>
</html>
