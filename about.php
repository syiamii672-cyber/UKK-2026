
<?php
include 'includes/cek_session.php';

$nama = 'Fitri Nur Syiami';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>About Me | SIPES</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --navy: #0b1735;
            --navy-light: #12264d;
            --blue: #3978f6;
            --blue-light: #76a6ff;
            --text: #edf3ff;
            --muted: #a6b5d1;
            --border: rgba(151, 179, 231, .16);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", Arial, sans-serif;
            color: var(--text);
            background:
                radial-gradient(ellipse at 10% 10%,
                    rgba(57, 120, 246, .18), transparent 35%),
                radial-gradient(ellipse at 90% 90%,
                    rgba(74, 83, 210, .14), transparent 35%),
                linear-gradient(135deg, #08132c, #0b1937 55%, #101e40);
        }

        a {
            text-decoration: none;
        }

        .page-wrapper {
            min-height: 100vh;
            padding: 28px 0;
        }

        /* HEADER */

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 28px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--text);
        }

        .brand-icon {
            width: 46px;
            height: 46px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            background: linear-gradient(135deg, #4c8dff, #2454d7);
            box-shadow: 0 8px 24px rgba(46, 107, 242, .22);
            font-size: 23px;
        }

        .brand-name {
            margin: 0;
            font-size: 17px;
            font-weight: 750;
            letter-spacing: 2px;
        }

        .brand-description {
            display: block;
            margin-top: 3px;
            color: var(--muted);
            font-size: 11px;
        }

        .btn-back-top {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 15px;
            color: #dce8ff;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: rgba(255, 255, 255, .035);
            transition: .25s;
            font-size: 13px;
        }

        .btn-back-top:hover {
            color: white;
            background: rgba(57, 120, 246, .17);
            border-color: rgba(118, 166, 255, .5);
        }

        /* MAIN CARD */

        .about-card {
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 24px;
            background: rgba(13, 28, 58, .82);
            box-shadow: 0 28px 80px rgba(0, 0, 0, .25);
            backdrop-filter: blur(18px);
        }

        /* PROFILE */

        .profile-panel {
            height: 100%;
            padding: 32px 25px;
            text-align: center;
            background:
                radial-gradient(circle at 50% 25%,
                    rgba(58, 111, 222, .2), transparent 45%),
                linear-gradient(160deg,
                    rgba(26, 52, 100, .85),
                    rgba(11, 25, 52, .96));
            border-right: 1px solid var(--border);
        }

        .profile-label {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: #a9caff;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.3px;
        }

        .profile-image-wrapper {
            position: relative;
            width: 170px;
            height: 170px;
            margin: 34px auto 25px;
        }

        .profile-image-wrapper::before {
            content: "";
            position: absolute;
            inset: -8px;
            border: 1px solid rgba(111, 163, 255, .55);
            border-radius: 50%;
        }

        .profile-image {
            position: relative;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            border: 5px solid #1c3769;
            border-radius: 50%;
            background: #17294a;
            box-shadow: 0 12px 35px rgba(0, 0, 0, .3);
        }

        .profile-status {
            position: absolute;
            right: 3px;
            bottom: 6px;
            z-index: 2;
            width: 34px;
            height: 34px;
            display: grid;
            place-items: center;
            border: 4px solid #12264b;
            border-radius: 50%;
            color: white;
            background: var(--blue);
        }

        .profile-name {
            font-size: 21px;
            font-weight: 750;
            overflow-wrap: anywhere;
        }

        .profile-role {
            margin-top: 9px;
            color: #9bbcff;
            font-size: 12px;
        }

        .profile-divider {
            width: 48px;
            height: 3px;
            margin: 23px auto;
            border-radius: 10px;
            background: linear-gradient(90deg, #3978f6, #8db6ff);
        }

        .profile-quote {
            color: #b8c7e2;
            font-size: 13px;
            line-height: 1.9;
            font-style: italic;
        }

        .profile-badge {
            display: inline-block;
            margin: 5px 3px 0;
            padding: 8px 11px;
            border: 1px solid rgba(124, 164, 236, .2);
            border-radius: 9px;
            color: #c5d9ff;
            background: rgba(67, 117, 209, .09);
            font-size: 11px;
        }

        .profile-footer {
            margin-top: 28px;
            color: #8195b9;
            font-size: 10px;
        }

        /* ABOUT CONTENT */

        .about-content {
            padding: 38px;
        }

        .eyebrow {
            color: #7eabff;
            font-size: 10px;
            font-weight: 750;
            letter-spacing: 2px;
        }

        .main-title {
            margin-top: 12px;
            font-size: clamp(30px, 3.2vw, 43px);
            font-weight: 750;
            letter-spacing: -1px;
        }

        .main-title span {
            color: var(--blue-light);
        }

        .subtitle {
            margin-top: 12px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.8;
        }

        .title-line {
            width: 62px;
            height: 4px;
            margin-top: 22px;
            border-radius: 10px;
            background: linear-gradient(90deg, #3978f6, #76b4ff);
        }

        .description {
            margin-top: 24px;
        }

        .description p {
            margin-bottom: 13px;
            color: #b4c2dc;
            font-size: 12.5px;
            line-height: 1.95;
        }

        .description strong {
            color: #edf4ff;
        }

        .opening-text {
            color: #e1ebff !important;
            font-size: 14px !important;
            font-weight: 650;
        }

        /* PROJECT */

        .project-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin: 30px 0 17px;
        }

        .project-heading h3 {
            margin: 7px 0 0;
            font-size: 17px;
            font-weight: 700;
        }

        .project-heading-icon {
            width: 40px;
            height: 40px;
            display: grid;
            place-items: center;
            border: 1px solid var(--border);
            border-radius: 12px;
            color: #85b2ff;
            background: rgba(71, 123, 224, .1);
            font-size: 18px;
        }

        .project-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            height: 100%;
            padding: 15px 13px;
            border: 1px solid var(--border);
            border-radius: 13px;
            background: rgba(255, 255, 255, .025);
            transition: .25s;
        }

        .project-item:hover {
            transform: translateY(-3px);
            border-color: rgba(108, 157, 255, .4);
            background: rgba(66, 117, 219, .08);
        }

        .project-icon {
            flex-shrink: 0;
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border-radius: 10px;
            font-size: 17px;
        }

        .icon-blue {
            color: #8ab5ff;
            background: rgba(57, 120, 246, .15);
        }

        .icon-purple {
            color: #c0a8ff;
            background: rgba(142, 100, 246, .14);
        }

        .icon-cyan {
            color: #7ce2f4;
            background: rgba(39, 183, 211, .13);
        }

        .icon-orange {
            color: #ffc78d;
            background: rgba(242, 151, 69, .13);
        }

        .project-detail {
            min-width: 0;
        }

        .project-detail span {
            display: block;
            margin-bottom: 7px;
            color: #8297bb;
            font-size: 9px;
            font-weight: 750;
            letter-spacing: 1px;
        }

        .project-detail strong {
            display: block;
            color: #e3edff;
            font-size: 11.5px;
            line-height: 1.65;
            overflow-wrap: anywhere;
        }

        /* CLOSING */

        .closing-note {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-top: 22px;
            padding: 15px;
            border: 1px solid rgba(81, 132, 229, .18);
            border-radius: 12px;
            background: linear-gradient(
                100deg,
                rgba(52, 101, 196, .12),
                rgba(52, 101, 196, .025)
            );
        }

        .closing-note > i {
            color: #85adff;
            font-size: 17px;
        }

        .closing-note p {
            margin: 0;
            line-height: 1.7;
        }

        .closing-note strong {
            display: block;
            color: #dce8ff;
            font-size: 11px;
        }

        .closing-note span {
            display: block;
            margin-top: 5px;
            color: #98aac9;
            font-size: 10.5px;
        }

        /* BOTTOM */

        .content-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }

        .footer-caption {
            color: #7d91b5;
            font-size: 9px;
            letter-spacing: 1px;
        }

        .footer-caption i,
        .page-footer i {
            color: #6b9cff;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 12px 17px;
            border: 1px solid rgba(111, 161, 255, .3);
            border-radius: 11px;
            color: white;
            background: linear-gradient(135deg, #3978f6, #285bd3);
            box-shadow: 0 7px 20px rgba(39, 91, 208, .2);
            font-size: 11px;
            font-weight: 650;
            transition: .25s;
        }

        .btn-back:hover {
            color: white;
            transform: translateY(-2px);
            background: linear-gradient(135deg, #528dff, #3269e5);
        }

        .page-footer {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            padding: 20px 3px 0;
            color: #7185a9;
            font-size: 10px;
        }

        /* RESPONSIVE */

        @media (max-width: 767.98px) {
            .page-wrapper {
                padding-top: 18px;
            }

            .brand-icon {
                width: 40px;
                height: 40px;
            }

            .brand-name {
                font-size: 15px;
            }

            .brand-description {
                max-width: 180px;
                font-size: 9px;
            }

            .btn-back-top {
                padding: 10px;
                font-size: 11px;
            }

            .profile-panel {
                border-right: 0;
                border-bottom: 1px solid var(--border);
                padding: 26px 20px;
            }

            .profile-image-wrapper {
                width: 150px;
                height: 150px;
            }

            .about-content {
                padding: 27px 20px;
            }

            .main-title {
                font-size: 34px;
            }

            .content-footer {
                align-items: stretch;
                flex-direction: column;
            }

            .btn-back {
                width: 100%;
            }

            .page-footer {
                flex-direction: column;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                transition: none !important;
            }
        }
    </style>
</head>

<body>

<div class="page-wrapper">
    <div class="container">

        <!-- HEADER -->
        <header class="topbar">

            <a href="dashboard.php" class="brand">
                <div class="brand-icon">
                    <i class="bi bi-shield-check"></i>
                </div>

                <div>
                    <h2 class="brand-name">SIPES</h2>
                    <span class="brand-description">
                        Sistem Informasi Pelanggaran Siswa
                    </span>
                </div>
            </a>

            <a href="dashboard.php" class="btn-back-top">
                <i class="bi bi-arrow-left"></i>
                Dashboard
            </a>

        </header>


        <!-- ABOUT CARD -->
        <main class="about-card">

            <div class="row g-0">

                <!-- PROFIL -->
                <aside class="col-md-4">

                    <div class="profile-panel">

                        <span class="profile-label">
                            <i class="bi bi-person-badge"></i>
                            DEVELOPER PROFILE
                        </span>

                        <div class="profile-image-wrapper">

                            <!-- FOTO KAMU -->
                            <img
                                src="images/foto.jpeg"
                                alt="Foto Fitri Nur Syiami"
                                class="profile-image"
                            >

                            <span class="profile-status">
                                <i class="bi bi-check-lg"></i>
                            </span>

                        </div>

                        <h2 class="profile-name">
                            <?php echo htmlspecialchars($nama); ?>
                        </h2>

                        <p class="profile-role">
                            <i class="bi bi-code-slash"></i>
                            Student & Web Developer
                        </p>

                        <div class="profile-divider"></div>

                        <p class="profile-quote">
                            "Terus belajar, terus mencoba,
                            dan terus berkembang."
                        </p>

                        <div>
                            <span class="profile-badge">
                                <i class="bi bi-mortarboard"></i>
                                Student
                            </span>

                            <span class="profile-badge">
                                <i class="bi bi-laptop"></i>
                                Developer
                            </span>
                        </div>

                        <div class="profile-footer">
                            <i class="bi bi-stars"></i>
                            Learning & Growing Every Day
                        </div>

                    </div>

                </aside>


                <!-- KONTEN ABOUT -->
                <section class="col-md-8">

                    <div class="about-content">

                        <div class="section-heading">

                            <span class="eyebrow">
                                GET TO KNOW ME
                            </span>

                            <h1 class="main-title">
                                Hello, I'm <span>Fitri.</span>
                            </h1>

                            <p class="subtitle">
                                Sedikit cerita tentang aku dan perjalanan
                                di balik project ini.
                            </p>

                            <div class="title-line"></div>

                        </div>


                        <!-- CERITA -->
                        <div class="description">

                            <p class="opening-text">
                                Di balik setiap halaman, ada proses
                                yang tidak selalu terlihat.
                            </p>

                            <p>
                                Aku <strong>Fitri Nur Syiami</strong>,
                                seorang pelajar yang sedang belajar mengenal
                                dunia pemrograman dan pengembangan website.
                            </p>

                            <p>
                                Bagiku, membuat aplikasi bukan hanya tentang
                                menulis kode, tetapi juga memahami masalah,
                                mencoba berbagai solusi, dan belajar dari
                                setiap kesalahan.
                            </p>

                            <p>
                                Melalui project ini, aku belajar bagaimana
                                merancang dan membangun sistem informasi
                                yang dapat membantu pengelolaan data
                                pelanggaran siswa dengan lebih terstruktur.
                            </p>

                            <p>
                                Perjalananku masih panjang. Namun, setiap
                                proses yang dilalui menjadi bagian dari
                                pengalaman untuk terus berkembang dan
                                menghasilkan sesuatu yang lebih baik.
                            </p>

                        </div>


                        <!-- INFORMASI PROJECT -->
                        <div class="project-heading">

                            <div>
                                <span class="eyebrow">
                                    WHAT I'VE BUILT
                                </span>

                                <h3>Project Information</h3>
                            </div>

                            <div class="project-heading-icon">
                                <i class="bi bi-grid-1x2"></i>
                            </div>

                        </div>


                        <div class="row g-3">

                            <div class="col-sm-6">
                                <div class="project-item">
                                    <div class="project-icon icon-blue">
                                        <i class="bi bi-shield-check"></i>
                                    </div>

                                    <div class="project-detail">
                                        <span>PROJECT NAME</span>
                                        <strong>
                                            Sistem Informasi Pelanggaran Siswa
                                        </strong>
                                    </div>
                                </div>
                            </div>


                            <div class="col-sm-6">
                                <div class="project-item">
                                    <div class="project-icon icon-purple">
                                        <i class="bi bi-mortarboard"></i>
                                    </div>

                                    <div class="project-detail">
                                        <span>PURPOSE</span>
                                        <strong>
                                            Uji Kompetensi Keahlian
                                        </strong>
                                    </div>
                                </div>
                            </div>


                            <div class="col-sm-6">
                                <div class="project-item">
                                    <div class="project-icon icon-cyan">
                                        <i class="bi bi-code-square"></i>
                                    </div>

                                    <div class="project-detail">
                                        <span>TECHNOLOGY</span>
                                        <strong>
                                            PHP, MySQL & Bootstrap
                                        </strong>
                                    </div>
                                </div>
                            </div>


                            <div class="col-sm-6">
                                <div class="project-item">
                                    <div class="project-icon icon-orange">
                                        <i class="bi bi-calendar3"></i>
                                    </div>

                                    <div class="project-detail">
                                        <span>PROJECT YEAR</span>
                                        <strong>2026</strong>
                                    </div>
                                </div>
                            </div>

                        </div>


                        <!-- PENUTUP -->
                        <div class="closing-note">

                            <i class="bi bi-stars"></i>

                            <p>
                                <strong>
                                    Every expert was once a beginner.
                                </strong>

                                <span>
                                    Ini adalah salah satu langkah kecilku
                                    untuk terus berkembang.
                                </span>
                            </p>

                        </div>


                        <!-- TOMBOL KEMBALI -->
                        <div class="content-footer">

                            <span class="footer-caption">
                                MADE WITH
                                <i class="bi bi-heart-fill"></i>
                                & LEARNING
                            </span>

                            <a href="dashboard.php" class="btn-back">
                                <i class="bi bi-arrow-left"></i>
                                Kembali ke Dashboard
                            </a>

                        </div>

                    </div>

                </section>

            </div>

        </main>


        <!-- FOOTER -->
        <footer class="page-footer">
            <span>
                © 2026 Sistem Informasi Pelanggaran Siswa
            </span>

            <span>
                Designed with purpose
                <i class="bi bi-heart-fill"></i>
            </span>
        </footer>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
