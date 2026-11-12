
<?php
include "includes/cek_session.php";

if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "about";
$nama = "Fitri Nur Syiami";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tentang sipenaMU</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background: #F1F5FB;
            font-family: "Segoe UI", Arial, sans-serif;
            color: #24364B;
        }

        .konten-utama {
            margin-left: 220px;
            width: calc(100% - 220px);
            min-height: 100vh;
            padding: 100px 30px 35px;
        }

        .judul-halaman {
            color: #173B67;
            font-weight: 750;
        }

        .subjudul {
            color: #718096;
            font-size: 14px;
        }

        .about-card {
            background: white;
            border: 0;
            border-radius: 18px;
            box-shadow: 0 4px 18px rgba(23, 59, 103, .07);
            overflow: hidden;
        }

        .about-banner {
            padding: 28px;
            color: white;
            background: linear-gradient(120deg, #173B67, #245B96);
        }

        .about-banner h2 {
            font-size: 25px;
            font-weight: 750;
        }

        .about-banner p {
            max-width: 650px;
            margin: 10px 0 0;
            color: #DCE9FA;
            font-size: 14px;
            line-height: 1.8;
        }

        .brand-label {
            display: inline-block;
            padding: 6px 12px;
            margin-bottom: 13px;
            border: 1px solid rgba(255,255,255,.3);
            border-radius: 20px;
            font-size: 11px;
            letter-spacing: 1px;
        }

        .profile-photo {
            width: 145px;
            height: 145px;
            object-fit: cover;
            object-position: center;
            border-radius: 50%;
            border: 5px solid white;
            box-shadow: 0 5px 18px rgba(0,0,0,.12);
        }

        .section-title {
            color: #173B67;
            font-weight: 700;
            font-size: 17px;
        }

        .section-text {
            color: #64748B;
            font-size: 13px;
            line-height: 1.9;
        }

        .profile-name {
            color: #173B67;
            font-weight: 750;
            font-size: 21px;
        }

        .profile-role {
            color: #3779C5;
            font-size: 12px;
            font-weight: 600;
        }

        .info-card {
            height: 100%;
            padding: 18px;
            border: 1px solid #E5ECF5;
            border-radius: 13px;
            background: #FFFFFF;
        }

        .info-icon {
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 13px;
            border-radius: 11px;
            color: #245B96;
            background: #EAF2FC;
            font-size: 19px;
        }

        .info-label {
            color: #8291A5;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .7px;
        }

        .info-value {
            display: block;
            margin-top: 5px;
            color: #173B67;
            font-size: 13px;
            font-weight: 700;
        }

        .nilai-card {
            padding: 18px;
            border-left: 4px solid #3779C5;
            border-radius: 10px;
            background: #F1F6FD;
        }

        .nilai-card p {
            margin: 0;
            color: #52677F;
            font-size: 13px;
            line-height: 1.8;
        }

        .tagline {
            color: #245B96;
            font-weight: 750;
        }

        .footer-about {
            color: #8795A8;
            font-size: 11px;
        }

        @media (max-width: 768px) {
            .konten-utama {
                margin-left: 0;
                width: 100%;
                padding: 90px 15px 25px;
            }

            .about-banner {
                padding: 22px;
            }
        }
    </style>
</head>

<body>

<?php include "includes/sidebar.php"; ?>
<?php include "includes/navbar.php"; ?>

<main class="konten-utama">

    <div class="mb-4">
        <h3 class="judul-halaman mb-1">Tentang Aplikasi</h3>
        <p class="subjudul mb-0">
            Mengenal lebih dekat sipenaMU dan perjalanan di balik pembuatannya.
        </p>
    </div>

    <!-- IDENTITAS APLIKASI -->
    <div class="about-card mb-4">
        <div class="about-banner">
            <span class="brand-label">
                <i class="bi bi-shield-check me-1"></i>
                IDENTITAS APLIKASI
            </span>

            <h2>Selamat Datang di sipenaMU</h2>

            <p>
                Sistem Informasi Pelanggaran dan Pembinaan Siswa Muhammadiyah.
                Sebuah platform digital yang dirancang untuk membantu sekolah
                mencatat, memantau, dan menindaklanjuti pelanggaran siswa
                secara lebih tertib, terstruktur, dan terarah.
            </p>
        </div>

        <div class="card-body p-4 p-lg-5">

            <!-- PROFIL PEMBUAT -->
            <div class="row align-items-center g-4 mb-5">

                <div class="col-md-4 text-center">
                    <img
                        src="images/foto.jpeg"
                        alt="Foto Fitri Nur Syiami"
                        class="profile-photo mb-3"
                    >

                    <h4 class="profile-name mb-1">
                        <?= htmlspecialchars($nama) ?>
                    </h4>

                    <div class="profile-role">
                        <i class="bi bi-mortarboard me-1"></i>
                        Student & Web Developer
                    </div>
                </div>

                <div class="col-md-8">
                    <span class="info-label">TENTANG PENGEMBANG</span>

                    <h4 class="section-title mt-2 mb-3">
                        Berawal dari Belajar, Bertumbuh Melalui Karya.
                    </h4>

                    <p class="section-text">
                        Halo! Saya <strong>Fitri Nur Syiami</strong>,
                        seorang pelajar yang memiliki ketertarikan dalam
                        dunia teknologi dan pengembangan aplikasi.
                    </p>

                    <p class="section-text">
                        sipenaMU merupakan salah satu karya yang saya
                        kembangkan sebagai bagian dari Uji Kompetensi
                        Keahlian (UKK). Dalam proses pembuatannya, saya
                        belajar merancang sistem, mengelola basis data,
                        membangun antarmuka, serta menghubungkan berbagai
                        fitur agar dapat bekerja secara terintegrasi.
                    </p>

                    <p class="section-text mb-0">
                        Setiap proses, tantangan, dan kesalahan menjadi
                        pengalaman berharga untuk terus belajar,
                        memperbaiki diri, dan menghasilkan karya yang
                        lebih bermanfaat.
                    </p>
                </div>

            </div>

            <hr class="border-secondary-subtle mb-4">

            <!-- TUJUAN APLIKASI -->
            <div class="mb-4">
                <span class="info-label">MAKSUD DAN TUJUAN</span>

                <h4 class="section-title mt-2 mb-3">
                    Lebih dari Sekadar Pencatatan
                </h4>

                <p class="section-text">
                    Setiap pelanggaran bukan hanya untuk dicatat,
                    tetapi juga menjadi kesempatan untuk melakukan
                    pembinaan. Melalui sipenaMU, pengelolaan data
                    pelanggaran diharapkan menjadi lebih mudah,
                    transparan, dan terdokumentasi dengan baik.
                </p>
            </div>

            <div class="row g-3 mb-5">

                <div class="col-md-4">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="bi bi-journal-text"></i>
                        </div>
                        <strong class="section-title">Pencatatan Terstruktur</strong>
                        <p class="section-text mt-2 mb-0">
                            Membantu pencatatan pelanggaran siswa
                            secara sistematis dan terdokumentasi.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <strong class="section-title">Pemantauan Poin</strong>
                        <p class="section-text mt-2 mb-0">
                            Memudahkan pemantauan poin dan riwayat
                            pelanggaran setiap siswa.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="bi bi-heart-pulse"></i>
                        </div>
                        <strong class="section-title">Pembinaan Siswa</strong>
                        <p class="section-text mt-2 mb-0">
                            Mendukung tindak lanjut dan pembinaan
                            agar siswa dapat berkembang lebih baik.
                        </p>
                    </div>
                </div>

            </div>

            <!-- INFORMASI PROYEK -->
            <div class="mb-3">
                <span class="info-label">DI BALIK PEMBUATAN</span>
                <h4 class="section-title mt-2">Informasi Proyek</h4>
            </div>

            <div class="row g-3 mb-4">

                <div class="col-sm-6 col-lg-3">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="bi bi-window-stack"></i>
                        </div>
                        <span class="info-label">NAMA APLIKASI</span>
                        <strong class="info-value">sipenaMU</strong>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="bi bi-mortarboard"></i>
                        </div>
                        <span class="info-label">KEGIATAN</span>
                        <strong class="info-value">Uji Kompetensi Keahlian</strong>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="bi bi-code-slash"></i>
                        </div>
                        <span class="info-label">TEKNOLOGI</span>
                        <strong class="info-value">PHP, MySQL, Bootstrap</strong>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <span class="info-label">TAHUN PROYEK</span>
                        <strong class="info-value">2026</strong>
                    </div>
                </div>

            </div>

            <!-- TAGLINE -->
            <div class="nilai-card mb-4">
                <div class="tagline mb-2">
                    <i class="bi bi-stars me-1"></i>
                    Membangun Kedisiplinan, Menguatkan Pembinaan,
                    Menciptakan Generasi Berkarakter.
                </div>

                <p>
                    Karena tujuan pendidikan bukan hanya membentuk
                    siswa yang taat terhadap aturan, tetapi juga
                    membimbing mereka menjadi pribadi yang bertanggung
                    jawab dan berkarakter baik.
                </p>
            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="footer-about">
                    © 2026 sipenaMU · Sistem Informasi Pelanggaran dan Pembinaan Siswa Muhammadiyah
                </span>

                <a href="dashboard.php" class="btn btn-primary px-4"
                   style="background:#245B96;border-color:#245B96;">
                    <i class="bi bi-arrow-left me-1"></i>
                    Kembali ke Dashboard
                </a>
            </div>

        </div>
    </div>

</main>

</body>
</html>
