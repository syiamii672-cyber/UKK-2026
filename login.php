
<?php
session_start();

// Jika sudah login, langsung ke dashboard
if (isset($_SESSION['id'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Sistem Informasi Pelanggaran Siswa</title>

    <!-- Bootstrap 5 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f1f5fb;
            color: #1e293b;
        }

        .halaman-login {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 18px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 950px;
            min-height: 530px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 45px rgba(30, 58, 95, 0.12);
        }

        /* Bagian informasi */
        .login-info {
            background: #173b67;
            color: #ffffff;
            padding: 55px 42px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .login-info::before {
            content: "";
            position: absolute;
            width: 250px;
            height: 250px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            top: -95px;
            right: -90px;
        }

        .login-info::after {
            content: "";
            position: absolute;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            bottom: -80px;
            left: -65px;
        }

        .logo-sekolah {
            width: 66px;
            height: 66px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.13);
            color: #ffffff;
            font-size: 31px;
            margin-bottom: 28px;
            position: relative;
            z-index: 1;
        }

        .login-info h1 {
            font-size: 29px;
            font-weight: 700;
            line-height: 1.4;
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
        }

        .login-info p {
            color: #d5e3f5;
            font-size: 14px;
            line-height: 1.9;
            margin-bottom: 28px;
            position: relative;
            z-index: 1;
        }

        .info-label {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 10px 14px;
            width: fit-content;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 9px;
            font-size: 13px;
            color: #e7effa;
            position: relative;
            z-index: 1;
        }

        /* Bagian formulir */
        .login-form-area {
            padding: 55px 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-form-area h2 {
            font-size: 29px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #173b67;
        }

        .subjudul {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 30px;
            line-height: 1.7;
        }

        .form-label {
            font-size: 14px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
        }

        .input-group-text {
            background: #f8fafc;
            border-color: #dbe3ed;
            color: #64748b;
            padding-left: 13px;
            padding-right: 13px;
        }

        .form-control {
            min-height: 47px;
            border-color: #dbe3ed;
            font-size: 14px;
            box-shadow: none;
        }

        .form-control:focus {
            border-color: #3779c5;
            box-shadow: 0 0 0 3px rgba(55, 121, 197, 0.12);
        }

        .input-group:focus-within .input-group-text {
            border-color: #3779c5;
        }

        .btn-login {
            width: 100%;
            min-height: 48px;
            border: none;
            border-radius: 9px;
            background: #245b96;
            color: #ffffff;
            font-weight: 600;
            font-size: 15px;
            margin-top: 10px;
            transition: background 0.2s ease;
        }

        .btn-login:hover {
            background: #173f6c;
            color: #ffffff;
        }

        .catatan-login {
            margin-top: 24px;
            padding: 12px 14px;
            border-radius: 9px;
            background: #f1f6fc;
            color: #52677f;
            font-size: 12px;
            line-height: 1.7;
        }

        .footer-login {
            margin-top: 24px;
            color: #94a3b8;
            text-align: center;
            font-size: 12px;
        }

        @media (max-width: 767.98px) {
            .login-wrapper {
                max-width: 440px;
                grid-template-columns: 1fr;
                min-height: auto;
            }

            .login-info {
                padding: 30px 28px;
            }

            .logo-sekolah {
                width: 52px;
                height: 52px;
                font-size: 25px;
                margin-bottom: 18px;
            }

            .login-info h1 {
                font-size: 23px;
                margin-bottom: 8px;
            }

            .login-info p {
                margin-bottom: 16px;
            }

            .login-form-area {
                padding: 35px 28px;
            }

            .login-form-area h2 {
                font-size: 26px;
            }
        }
    </style>
</head>

<body>

<div class="halaman-login">

    <div class="login-wrapper">

        <!-- Informasi aplikasi -->
        <section class="login-info">

            <div class="logo-sekolah">
                <i class="bi bi-mortarboard-fill"></i>
            </div>

            <h1>
                Sistem Informasi<br>
                Pelanggaran Siswa
            </h1>

            <p>
                Platform pengelolaan data pelanggaran siswa untuk
                membantu sekolah melakukan pencatatan, pemantauan,
                dan pelaporan secara terstruktur.
            </p>

            <div class="info-label">
                <i class="bi bi-shield-check"></i>
                Sistem Pengelolaan Sekolah
            </div>

        </section>

        <!-- Form login -->
        <section class="login-form-area">

            <h2>Selamat Datang!</h2>

            <p class="subjudul">
                Silakan masuk menggunakan akun yang telah terdaftar
                untuk melanjutkan.
            </p>

            <form action="proses_login.php" method="POST">

                <div class="mb-4">
                    <label for="email" class="form-label">
                        Email
                    </label>

                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-envelope"></i>
                        </span>

                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            placeholder="Masukkan email"
                            autocomplete="username"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">
                        Password
                    </label>

                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock"></i>
                        </span>

                        <input
                            type="password"
                            class="form-control"
                            id="password"
                            name="password"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                            required
                        >
                    </div>
                </div>

                <button type="submit" class="btn btn-login">
                    Masuk ke Sistem
                    <i class="bi bi-arrow-right ms-2"></i>
                </button>

            </form>

            <div class="catatan-login">
                <i class="bi bi-info-circle me-1"></i>
                Gunakan akun Admin atau Guru yang sudah terdaftar
                di dalam sistem.
            </div>

            <div class="footer-login">
                Sistem Informasi Pelanggaran Siswa
            </div>

        </section>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
