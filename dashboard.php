
<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

include "config/koneksi.php";

$nama = $_SESSION['name'] ?? '';
$role = $_SESSION['role'] ?? '';
$idUser = (int) $_SESSION['id'];

$menuAktif = "dashboard";

$isWaliKelas = false;
$namaKelasWali = "";

// Cek penugasan Wali Kelas
if ($role === "guru") {

    $queryWali = mysqli_query($koneksi, "
        SELECT k.nama AS nama_kelas
        FROM t_guru g
        JOIN t_wali_kelas w ON g.id = w.guru_id
        JOIN t_kelas k ON w.kelas_id = k.id
        JOIN t_tahun_ajaran ta ON w.tahun_ajaran_id = ta.id
        WHERE g.user_id = $idUser
          AND g.status_aktif = 1
          AND w.status_aktif = 1
          AND k.status_aktif = 1
          AND ta.status_aktif = 1
        LIMIT 1
    ");

    if ($queryWali && mysqli_num_rows($queryWali) > 0) {
        $dataWali = mysqli_fetch_assoc($queryWali);
        $isWaliKelas = true;
        $namaKelasWali = $dataWali['nama_kelas'];
    }
}

// Mengambil jumlah data untuk Dashboard Admin
$querySiswa = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM t_siswa"
);
$totalSiswa = $querySiswa
    ? mysqli_fetch_assoc($querySiswa)['total']
    : 0;

$queryGuru = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM t_guru"
);
$totalGuru = $queryGuru
    ? mysqli_fetch_assoc($queryGuru)['total']
    : 0;

$queryKelas = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM t_kelas"
);
$totalKelas = $queryKelas
    ? mysqli_fetch_assoc($queryKelas)['total']
    : 0;
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

            <!-- Dashboard Semua Guru -->
            <div class="col-12 mb-2">
                <div class="card kartu-dashboard shadow-sm">
                    <div class="card-body">
                        <h5 class="fw-bold">
                            <?= $isWaliKelas
                                ? "Menu Guru / Wali Kelas"
                                : "Menu Guru"; ?>
                        </h5>

                        <p class="text-secondary mb-2">
                            Silakan gunakan menu berikut untuk menjalankan
                            tugas dan memantau pelanggaran siswa.
                        </p>

                        <?php if ($isWaliKelas): ?>
                            <span class="badge bg-primary">
                                Wali Kelas <?= htmlspecialchars($namaKelasWali); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Catat Pelanggaran -->
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

            <!-- Tindakan -->
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

            <!-- Rekap Poin -->
            <div class="col-12 col-md-6 col-lg-4">
                <a href="rekap_poin.php" class="kartu-menu">
                    <div class="card kartu-dashboard shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-secondary">Rekap Poin</h6>
                            <h5 class="fw-bold mt-3">Lihat Rekap Poin</h5>
                            <p class="text-muted small mb-0">
                                Pantau akumulasi poin pelanggaran siswa.
                            </p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Riwayat -->
            <div class="col-12 col-md-6 col-lg-4">
                <a href="riwayat_pelanggaran.php" class="kartu-menu">
                    <div class="card kartu-dashboard shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-secondary">Riwayat</h6>
                            <h5 class="fw-bold mt-3">Lihat Riwayat</h5>
                            <p class="text-muted small mb-0">
                                Lihat catatan riwayat pelanggaran siswa.
                            </p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Laporan khusus Wali Kelas -->
            <?php if ($isWaliKelas): ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <a href="laporan.php" class="kartu-menu">
                        <div class="card kartu-dashboard shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="text-secondary">Laporan</h6>
                                <h5 class="fw-bold mt-3">Lihat Laporan</h5>
                                <p class="text-muted small mb-0">
                                    Lihat laporan pelanggaran kelas Anda.
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
