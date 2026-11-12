<?php
include "includes/cek_session.php";
include "config/koneksi.php";

$role = strtolower(trim($_SESSION['role'] ?? ''));

if ($role !== "admin") {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "kategori";

function e($nilai)
{
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

$nama = '';
$deskripsi = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if ($nama === '') {
        $error = "Nama kategori wajib diisi.";
    } else {
        // Periksa apakah nama kategori sudah tersedia.
        $sqlCek = "SELECT id FROM t_pelanggaran_kategori WHERE nama = ? LIMIT 1";
        $stmtCek = mysqli_prepare($koneksi, $sqlCek);
        mysqli_stmt_bind_param($stmtCek, "s", $nama);
        mysqli_stmt_execute($stmtCek);

        $hasilCek = mysqli_stmt_get_result($stmtCek);

        if (mysqli_num_rows($hasilCek) > 0) {
            $error = "Nama kategori tersebut sudah tersedia.";
        } else {
            $statusAktif = 1;

            $sql = "
                INSERT INTO t_pelanggaran_kategori
                    (nama, deskripsi, status_aktif)
                VALUES (?, ?, ?)
            ";

            $stmt = mysqli_prepare($koneksi, $sql);
            mysqli_stmt_bind_param(
                $stmt,
                "ssi",
                $nama,
                $deskripsi,
                $statusAktif
            );

            if (mysqli_stmt_execute($stmt)) {
                header("Location: kategori_pelanggaran.php?pesan=tambah");
                exit;
            } else {
                $error = "Kategori gagal ditambahkan. Silakan coba kembali.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Kategori Pelanggaran</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            background: #F1F5FB;
        }

        .konten-utama {
            margin-left: 220px !important;
            width: calc(100% - 220px) !important;
            min-height: 100vh;
            padding: 100px 24px 30px;
            box-sizing: border-box;
        }

        .judul-halaman {
            color: #173B67;
            font-weight: 700;
        }

        .deskripsi-halaman {
            color: #718096;
            font-size: 13px;
        }

        .card-data {
            background: #fff;
            border: 1px solid #E5EAF2;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(23, 59, 103, .06);
        }

        .form-label {
            color: #40546B;
            font-weight: 600;
            font-size: 14px;
        }

        .form-control {
            min-height: 43px;
            border: 1px solid #D8E1ED;
            border-radius: 8px;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: #3779C5;
            box-shadow: 0 0 0 3px rgba(55, 121, 197, .12);
        }

        .btn-simpan {
            background: #245B96;
            border-color: #245B96;
            color: #fff;
            font-weight: 600;
            border-radius: 8px;
        }

        .btn-simpan:hover {
            background: #173B67;
            border-color: #173B67;
            color: #fff;
        }

        .btn-kembali {
            background: #E9EEF5;
            border-color: #E9EEF5;
            color: #40546B;
            border-radius: 8px;
        }

        .btn-kembali:hover {
            background: #DCE5F0;
            color: #173B67;
        }

        @media (max-width: 767.98px) {
            .konten-utama {
                padding: 100px 15px 25px;
            }
        }
    </style>
</head>

<body>

<?php include "includes/sidebar.php"; ?>
<?php include "includes/navbar.php"; ?>

<main class="konten-utama">

    <div class="mb-4">
        <h2 class="judul-halaman">Tambah Kategori Pelanggaran</h2>
        <p class="deskripsi-halaman mb-0">
            Tambahkan kategori baru untuk mengelompokkan jenis pelanggaran siswa.
        </p>
    </div>

    <div class="card card-data">
        <div class="card-body p-3 p-md-4">

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger">
                    <?= e($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">

                <div class="mb-3">
                    <label for="nama" class="form-label">
                        Nama Kategori <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        name="nama"
                        id="nama"
                        class="form-control"
                        placeholder="Contoh: Kedisiplinan"
                        value="<?= e($nama); ?>"
                        maxlength="150"
                        required
                        autofocus>
                </div>

                <div class="mb-4">
                    <label for="deskripsi" class="form-label">
                        Deskripsi
                    </label>

                    <textarea
                        name="deskripsi"
                        id="deskripsi"
                        class="form-control"
                        rows="4"
                        placeholder="Masukkan deskripsi kategori pelanggaran..."><?= e($deskripsi); ?></textarea>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-simpan px-4 py-2">
                        Simpan Kategori
                    </button>

                    <a href="kategori_pelanggaran.php" class="btn btn-kembali px-4 py-2">
                        Kembali
                    </a>
                </div>

            </form>

        </div>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>