<?php
include 'includes/cek_session.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>About Me</title>

    <link rel="stylesheet" href="assets/css/about.css">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>

    <div class="about-wrapper">

        <div class="about-card">

            <!-- BAGIAN FOTO -->
            <div class="profile-section">

                <div class="profile-image-wrapper">
                    <img src="assets/img/profile.jpg"
                         alt="Foto Profil"
                         class="profile-image">
                </div>

                <h2>[Nama Kamu]</h2>

                <p class="role">
                    <i class="bi bi-code-slash"></i>
                    Student & Web Developer
                </p>

                <div class="line"></div>

                <div class="social">

                    <a href="#" title="Instagram">
                        <i class="bi bi-instagram"></i>
                    </a>

                    <a href="#" title="GitHub">
                        <i class="bi bi-github"></i>
                    </a>

                </div>

            </div>


            <!-- BAGIAN ABOUT -->
            <div class="about-content">

                <span class="small-title">
                    ABOUT THE DEVELOPER
                </span>

                <h1>
                    Hello, I'm <span>Fitri Nur Syiami</span>.
                </h1>

                <div class="quote-line"></div>


                <div class="description">

                    <p>
                        <strong>
                            Di balik setiap halaman, ada seseorang yang
                            pernah duduk berjam-jam di depan layar.
                        </strong>
                    </p>

                    <p>
                        Mencoba membuat sesuatu yang awalnya hanya ada
                        di kepala, lalu perlahan menjadi nyata.
                    </p>

                    <p>
                        Aku <strong>Syiami</strong>, orang di balik
                        aplikasi ini.
                    </p>

                    <p>
                        Bukan seorang yang sudah tahu semuanya, hanya
                        seseorang yang terus mencoba, belajar dari
                        kesalahan, dan mencari cara agar sesuatu yang
                        dibuatnya bisa menjadi lebih baik.
                    </p>

                    <p>
                        Aplikasi ini mungkin hanya sebuah project,
                        tapi bagiku, ia menyimpan cukup banyak cerita.
                    </p>

                </div>


                <!-- INFO PROJECT -->
                <div class="project-info">

                    <div class="info-item">

                        <i class="bi bi-laptop"></i>

                        <div>
                            <span>PROJECT</span>
                            <strong>
                                Sistem Pelanggaran Siswa
                            </strong>
                        </div>

                    </div>


                    <div class="info-item">

                        <i class="bi bi-mortarboard"></i>

                        <div>
                            <span>CREATED FOR</span>
                            <strong>
                                Uji Kompetensi Keahlian
                            </strong>
                        </div>

                    </div>


                    <div class="info-item">

                        <i class="bi bi-calendar3"></i>

                        <div>
                            <span>YEAR</span>
                            <strong>2026</strong>
                        </div>

                    </div>

                </div>


                <!-- KEMBALI -->
                <a href="dashboard.php" class="back-button">

                    <i class="bi bi-arrow-left"></i>

                    Kembali ke Dashboard

                </a>

            </div>

        </div>

    </div>

</body>
</html>