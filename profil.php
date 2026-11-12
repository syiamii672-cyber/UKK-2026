
<?php
require_once 'config/koneksi.php';
require_once 'includes/cek_session.php';

// Mengambil koneksi database
$db = $conn ?? $koneksi ?? null;

if (!$db) {
    die("Koneksi database tidak ditemukan. Periksa config/koneksi.php.");
}

// Mengambil ID akun yang sedang login
$idUser = $_SESSION['id'] ?? 0;

if (!$idUser) {
    header("Location: login.php");
    exit;
}

// Mengambil data akun
$stmt = mysqli_prepare($db, "
    SELECT id, name, email, role, created_at
    FROM t_users
    WHERE id = ?
    LIMIT 1
");

mysqli_stmt_bind_param($stmt, "i", $idUser);
mysqli_stmt_execute($stmt);

$resultUser = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($resultUser);

mysqli_stmt_close($stmt);

if (!$user) {
    die("Data akun tidak ditemukan.");
}

$nama = $user['name'];
$email = $user['email'];
$role = strtolower($user['role']);

$inisial = strtoupper(substr($nama, 0, 1));

$jenisAkun = ($role === 'admin') ? 'Administrator' : 'Guru';

// Data tambahan guru
$guru = null;

if ($role === 'guru') {
    $stmtGuru = mysqli_prepare($db, "
        SELECT nip, nama, email, status_aktif
        FROM t_guru
        WHERE user_id = ?
        LIMIT 1
    ");

    mysqli_stmt_bind_param($stmtGuru, "i", $idUser);
    mysqli_stmt_execute($stmtGuru);

    $resultGuru = mysqli_stmt_get_result($stmtGuru);
    $guru = mysqli_fetch_assoc($resultGuru);

    mysqli_stmt_close($stmtGuru);
}

// Memeriksa hak akses wali kelas
$isWaliKelas = false;
$namaKelasWali = '';

if ($role === 'guru') {
    require_once 'includes/cek_wali_kelas.php';

    $isWaliKelas = $isWaliKelas ?? false;
    $namaKelasWali = $namaKelasWali ?? '';
}

$judulHalaman = "Profil Saya";
$menuAktif = "profil";

function aman($nilai)
{
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Profil Saya - Sistem Informasi Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body {
            background: #F1F5FB;
            font-family: Arial, sans-serif;
            margin: 0;
        }

        .konten-utama {
            margin-left: 220px;
            width: calc(100% - 220px);
            min-height: 100vh;
            padding: 100px 28px 30px;
            box-sizing: border-box;
        }

        .judul-halaman {
            color: #173B67;
            font-weight: 700;
        }

        .kartu-profil {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #E3EAF3;
            overflow: hidden;
        }

        .header-profil {
            background: linear-gradient(135deg, #173B67, #3779C5);
            padding: 35px 25px;
            color: white;
        }

        .avatar-profil {
            width: 95px;
            height: 95px;
            border-radius: 50%;
            background: white;
            color: #245B96;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 38px;
            font-weight: 700;
            border: 4px solid rgba(255,255,255,0.4);
            margin-bottom: 15px;
        }

        .avatar-kecil {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #E6EFFB;
            color: #245B96;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .isi-profil {
            padding: 25px;
        }

        .judul-bagian {
            color: #173B67;
            font-weight: 700;
            font-size: 17px;
            margin-bottom: 20px;
        }

        .item-profil {
            padding: 15px 0;
            border-bottom: 1px solid #E9EEF5;
        }

        .item-profil:last-child {
            border-bottom: none;
        }

        .label-profil {
            color: #758399;
            font-size: 13px;
            margin-bottom: 5px;
        }

        .nilai-profil {
            color: #25364A;
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        .badge-admin {
            background: #E5EDFA;
            color: #245B96;
        }

        .badge-guru {
            background: #E3F5EC;
            color: #198754;
        }

        .tombol-password {
            background: #245B96;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
        }

        .tombol-password:hover {
            background: #173B67;
            color: white;
        }

        @media (max-width: 768px) {
            .konten-utama {
                margin-left: 220px;
                width: calc(100% - 220px);
                padding: 100px 12px 20px;
            }

            .header-profil {
                padding: 25px 15px;
            }

            .isi-profil {
                padding: 18px;
            }
        }
    </style>
</head>

<body>

<?php
include 'includes/sidebar.php';
include 'includes/navbar.php';
?>

<main class="konten-utama">

    <div class="mb-4">
        <h3 class="judul-halaman mb-1">Profil Saya</h3>
        <p class="text-secondary mb-0">
            Informasi akun pengguna Sistem Informasi Pelanggaran Siswa.
        </p>
    </div>

    <div class="row g-4">

        <!-- Kartu profil utama -->
        <div class="col-lg-4">

            <div class="kartu-profil h-100">

                <div class="header-profil text-center">

                    <div class="avatar-profil mx-auto">
                        <?= aman($inisial); ?>
                    </div>

                    <h5 class="fw-bold mb-2">
                        <?= aman($nama); ?>
                    </h5>

                    <p class="mb-3 opacity-75">
                        <?= aman($email); ?>
                    </p>

                    <span class="badge rounded-pill px-3 py-2
                        <?= $role === 'admin' ? 'badge-admin' : 'badge-guru'; ?>">
                        <?= aman($jenisAkun); ?>
                    </span>

                </div>

                <div class="isi-profil">

                    <h6 class="judul-bagian">
                        <i class="bi bi-person-badge me-2"></i>
                        Informasi Akun
                    </h6>

                    <div class="item-profil">
                        <div class="label-profil">Nama Pengguna</div>
                        <div class="nilai-profil">
                            <?= aman($nama); ?>
                        </div>
                    </div>

                    <div class="item-profil">
                        <div class="label-profil">Email</div>
                        <div class="nilai-profil">
                            <?= aman($email); ?>
                        </div>
                    </div>

                    <div class="item-profil">
                        <div class="label-profil">Jenis Akun</div>
                        <div class="nilai-profil">
                            <?= aman($jenisAkun); ?>
                        </div>
                    </div>

                    <div class="item-profil">
                        <div class="label-profil">ID Pengguna</div>
                        <div class="nilai-profil">
                            #<?= aman($user['id']); ?>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        <!-- Detail akun -->
        <div class="col-lg-8">

            <div class="kartu-profil mb-4">

                <div class="isi-profil">

                    <h6 class="judul-bagian">
                        <i class="bi bi-card-list me-2"></i>
                        Detail Pengguna
                    </h6>

                    <?php if ($role === 'admin'): ?>

                        <div class="item-profil">
                            <div class="label-profil">Nama Jabatan</div>
                            <div class="nilai-profil">Administrator Sistem</div>
                        </div>

                        <div class="item-profil">
                            <div class="label-profil">Hak Akses</div>
                            <div class="nilai-profil">Pengelolaan seluruh sistem</div>
                        </div>

                    <?php else: ?>

                        <div class="item-profil">
                            <div class="label-profil">Nama Guru</div>
                            <div class="nilai-profil">
                                <?= aman($guru['nama'] ?? $nama); ?>
                            </div>
                        </div>

                        <div class="item-profil">
                            <div class="label-profil">NIP</div>
                            <div class="nilai-profil">
                                <?= aman($guru['nip'] ?? '-'); ?>
                            </div>
                        </div>

                        <div class="item-profil">
                            <div class="label-profil">Status Guru</div>
                            <div class="nilai-profil">
                                <?php if ($guru && (int)$guru['status_aktif'] === 1): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="item-profil">
                            <div class="label-profil">Status Wali Kelas</div>
                            <div class="nilai-profil">
                                <?php if ($isWaliKelas): ?>
                                    <span class="badge bg-primary">Wali Kelas</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Guru</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($isWaliKelas): ?>
                            <div class="item-profil">
                                <div class="label-profil">Kelas yang Diampu</div>
                                <div class="nilai-profil">
                                    <?= aman($namaKelasWali ?: 'Informasi kelas belum tersedia'); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                    <?php endif; ?>

                    <div class="item-profil">
                        <div class="label-profil">Tanggal Akun Dibuat</div>
                        <div class="nilai-profil">
                            <?php
                            if (!empty($user['created_at'])) {
                                echo date('d-m-Y', strtotime($user['created_at']));
                            } else {
                                echo '-';
                            }
                            ?>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Keamanan akun -->
            <div class="kartu-profil">

                <div class="isi-profil">

                    <h6 class="judul-bagian">
                        <i class="bi bi-shield-lock me-2"></i>
                        Keamanan Akun
                    </h6>

                    <p class="text-secondary mb-3">
                        Jaga keamanan akun dengan mengganti kata sandi
                        secara berkala.
                    </p>

                    <a href="ubah_password.php" class="tombol-password">
                        <i class="bi bi-key me-2"></i>
                        Ubah Kata Sandi
                    </a>

                </div>

            </div>

        </div>

    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
