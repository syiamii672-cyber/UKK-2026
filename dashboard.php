
<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

include "config/koneksi.php";
require_once "includes/cek_wali_kelas.php";

$nama = $_SESSION['name'] ?? '';
$role = strtolower(trim($_SESSION['role'] ?? ''));

$menuAktif = "dashboard";
$judulHalaman = "Dashboard";

// Total data untuk Dashboard Admin
$totalSiswa = 0;
$totalGuru = 0;
$totalKelas = 0;

if ($role === "admin") {

    $querySiswa = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM t_siswa");
    $queryGuru = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM t_guru");
    $queryKelas = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM t_kelas");

    if ($querySiswa) {
        $totalSiswa = (int) mysqli_fetch_assoc($querySiswa)['total'];
    }

    if ($queryGuru) {
        $totalGuru = (int) mysqli_fetch_assoc($queryGuru)['total'];
    }

    if ($queryKelas) {
        $totalKelas = (int) mysqli_fetch_assoc($queryKelas)['total'];
    }
}

// Menentukan jenis pengguna
if ($role === "admin") {
    $labelRole = "Administrator";
} elseif ($isWaliKelas) {
    $labelRole = "Guru / Wali Kelas";
} else {
    $labelRole = "Guru";
}

// Daftar kartu dashboard
$daftarKartu = [];

if ($role === "admin") {

    $daftarKartu = [
        [
            "judul" => "Data Siswa",
            "jumlah" => $totalSiswa,
            "deskripsi" => "Jumlah seluruh data siswa.",
            "link" => "siswa.php",
            "tombol" => "Kelola Siswa",
            "ikon" => "bi-people"
        ],
        [
            "judul" => "Data Guru",
            "jumlah" => $totalGuru,
            "deskripsi" => "Jumlah seluruh data guru.",
            "link" => "data_guru.php",
            "tombol" => "Kelola Guru",
            "ikon" => "bi-person-badge"
        ],
        [
            "judul" => "Data Kelas",
            "jumlah" => $totalKelas,
            "deskripsi" => "Jumlah seluruh kelas.",
            "link" => "kelas.php",
            "tombol" => "Kelola Kelas",
            "ikon" => "bi-building"
        ]
    ];

} elseif ($role === "guru") {

    $daftarKartu = [
        [
            "judul" => "Input Pelanggaran",
            "deskripsi" => "Masukkan data pelanggaran siswa.",
            "link" => "input_pelanggaran.php",
            "tombol" => "Input Sekarang",
            "ikon" => "bi-pencil-square"
        ],
        [
            "judul" => "Catatan Pelanggaran",
            "deskripsi" => "Lihat catatan pelanggaran dan kelola tindakannya.",
            "link" => "catatan_pelanggaran.php",
            "tombol" => "Lihat Catatan",
            "ikon" => "bi-journal-text"
        ],
        [
            "judul" => "Rekap Poin",
            "deskripsi" => "Pantau jumlah pelanggaran dan poin siswa.",
            "link" => "rekap_poin.php",
            "tombol" => "Lihat Rekap",
            "ikon" => "bi-bar-chart"
        ],
        [
            "judul" => "Riwayat",
            "deskripsi" => "Lihat riwayat pelanggaran yang Anda laporkan.",
            "link" => "riwayat.php",
            "tombol" => "Lihat Riwayat",
            "ikon" => "bi-clock-history"
        ]
    ];

    if ($isWaliKelas) {

        $daftarKartu[] = [
            "judul" => "Rekap Kelas",
            "deskripsi" => "Pantau rekap pelanggaran siswa di kelas Anda.",
            "link" => "rekap_kelas.php",
            "tombol" => "Lihat Rekap Kelas",
            "ikon" => "bi-people"
        ];

        $daftarKartu[] = [
            "judul" => "Laporan",
            "deskripsi" => "Lihat laporan pelanggaran siswa kelas Anda.",
            "link" => "laporan.php",
            "tombol" => "Lihat Laporan",
            "ikon" => "bi-file-earmark-text"
        ];
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

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background: #F5F6F7;
            color: #374151;
        }

        .konten-utama {
            margin-left: 220px;
            min-height: 100vh;
        }

        .isi-dashboard {
            padding: 100px 28px 28px;
        }

        .kartu-dashboard {
            border: 1px solid #E5E9ED;
            border-radius: 12px;
            transition: 0.2s;
        }

        .kartu-dashboard:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(52,73,94,0.08) !important;
        }

        .ikon-dashboard {
            width: 43px;
            height: 43px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: #E9EEF2;
            color: #526D83;
            font-size: 20px;
        }

        .btn-ukk {
            background: #607D94;
            color: white;
            border: none;
        }

        .btn-ukk:hover {
            background: #4E687E;
            color: white;
        }

        @media (max-width: 768px) {
            .konten-utama {
                margin-left: 220px;
            }

            .isi-dashboard {
                padding: 95px 18px 18px;
            }
        }
    </style>
</head>

<body>

    <?php include "includes/sidebar.php"; ?>

    <?php include "includes/navbar.php"; ?>

    <main class="konten-utama">

        <div class="isi-dashboard">

            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body p-4">

                    <h5 class="fw-semibold mb-2">
                        Selamat datang, <?= htmlspecialchars($nama); ?>!
                    </h5>

                    <p class="text-secondary mb-0">
                        Anda login sebagai <?= htmlspecialchars($labelRole); ?>.
                        Silakan gunakan menu yang tersedia untuk menjalankan tugas Anda.
                    </p>

                    <?php if ($role === "guru" && $isWaliKelas): ?>
                        <div class="mt-3">
                            <span class="badge rounded-pill px-3 py-2"
                                  style="background:#E9EEF2;color:#526D83;">
                                <i class="bi bi-building me-1"></i>
                                Wali Kelas <?= htmlspecialchars($namaKelasWali); ?>
                            </span>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

            <div class="mb-3">

                <h5 class="fw-semibold mb-1">
                    <?= $role === "admin" ? "Ringkasan Data" : "Menu Utama"; ?>
                </h5>

                <p class="text-secondary small mb-0">
                    <?= $role === "admin"
                        ? "Informasi jumlah data yang tersimpan dalam sistem."
                        : "Pilih fitur yang ingin Anda gunakan."; ?>
                </p>

            </div>

            <div class="row g-3">

                <?php foreach ($daftarKartu as $kartu): ?>

                    <div class="col-12 col-md-6 col-xl-4">

                        <div class="card kartu-dashboard shadow-sm h-100">

                            <div class="card-body p-4">

                                <div class="d-flex justify-content-between align-items-center mb-3">

                                    <h6 class="text-secondary mb-0">
                                        <?= htmlspecialchars($kartu['judul']); ?>
                                    </h6>

                                    <div class="ikon-dashboard">
                                        <i class="bi <?= htmlspecialchars($kartu['ikon']); ?>"></i>
                                    </div>

                                </div>

                                <?php if (isset($kartu['jumlah'])): ?>

                                    <h2 class="fw-bold mb-2">
                                        <?= $kartu['jumlah']; ?>
                                    </h2>

                                <?php else: ?>

                                    <h5 class="fw-semibold mb-2">
                                        <?= htmlspecialchars($kartu['judul']); ?>
                                    </h5>

                                <?php endif; ?>

                                <p class="text-secondary small mb-4">
                                    <?= htmlspecialchars($kartu['deskripsi']); ?>
                                </p>

                                <a href="<?= htmlspecialchars($kartu['link']); ?>"
                                   class="btn btn-ukk btn-sm px-3">

                                    <?= htmlspecialchars($kartu['tombol']); ?>
                                    <i class="bi bi-arrow-right ms-1"></i>

                                </a>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
