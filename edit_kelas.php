<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] != "admin") {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "kelas";
$error = "";

// Ambil ID kelas dari URL
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header("Location: kelas.php");
    exit;
}

// Ambil data kelas berdasarkan ID
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id, nama, tingkat, jurusan, status_aktif
     FROM t_kelas
     WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$hasil = mysqli_stmt_get_result($stmt);
$kelas = mysqli_fetch_assoc($hasil);

mysqli_stmt_close($stmt);

if (!$kelas) {
    header("Location: kelas.php?pesan=gagal_hapus");
    exit;
}

// Isi awal form
$nama = $kelas['nama'];
$tingkat = $kelas['tingkat'];
$jurusan = $kelas['jurusan'];
$status_aktif = (string) $kelas['status_aktif'];

// Proses saat tombol Simpan Perubahan ditekan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $tingkat = trim($_POST['tingkat'] ?? '');
    $jurusan = trim($_POST['jurusan'] ?? '');
    $status_aktif = (string) ($_POST['status_aktif'] ?? '');

    if (
        $nama === '' ||
        $tingkat === '' ||
        $jurusan === '' ||
        !in_array($status_aktif, ['0', '1'], true)
    ) {
        $error = "Semua data wajib diisi dengan benar.";
    } else {
        // Cek apakah nama kelas sudah digunakan kelas lain
        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id FROM t_kelas
             WHERE nama = ? AND id != ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param($cek, "si", $nama, $id);
        mysqli_stmt_execute($cek);

        $hasilCek = mysqli_stmt_get_result($cek);
        $duplikat = mysqli_fetch_assoc($hasilCek);

        mysqli_stmt_close($cek);

        if ($duplikat) {
            $error = "Nama kelas tersebut sudah digunakan.";
        } else {
            // Update data kelas
            $update = mysqli_prepare(
                $koneksi,
                "UPDATE t_kelas
                 SET nama = ?, tingkat = ?, jurusan = ?, status_aktif = ?
                 WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $update,
                "sssii",
                $nama,
                $tingkat,
                $jurusan,
                $status_aktif,
                $id
            );

            if (mysqli_stmt_execute($update)) {
                mysqli_stmt_close($update);
                header("Location: kelas.php?pesan=edit");
                exit;
            } else {
                $error = "Gagal mengubah data kelas. Silakan coba lagi.";
            }

            mysqli_stmt_close($update);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kelas - Sistem Informasi Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">

<?php include "includes/sidebar.php"; ?>

<div class="konten-utama">
    <div class="container-fluid">
        <div class="mb-4">
            <h3 class="fw-bold mb-1">Edit Data Kelas</h3>
            <p class="text-secondary mb-0">
                Ubah informasi kelas sesuai data terbaru.
            </p>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">

                <?php if ($error !== ""): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Kelas</label>
                        <input
                            type="text"
                            name="nama"
                            id="nama"
                            class="form-control"
                            value="<?= htmlspecialchars($nama) ?>"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="tingkat" class="form-label">Tingkat</label>
                        <select name="tingkat" id="tingkat"
                                class="form-select" required>
                            <option value="">-- Pilih Tingkat --</option>
                            <option value="10" <?= $tingkat == '10' ? 'selected' : '' ?>>10</option>
                            <option value="11" <?= $tingkat == '11' ? 'selected' : '' ?>>11</option>
                            <option value="12" <?= $tingkat == '12' ? 'selected' : '' ?>>12</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="jurusan" class="form-label">Jurusan</label>
                        <input
                            type="text"
                            name="jurusan"
                            id="jurusan"
                            class="form-control"
                            value="<?= htmlspecialchars($jurusan) ?>"
                            required
                        >
                    </div>

                    <div class="mb-4">
                        <label for="status_aktif" class="form-label">Status</label>
                        <select name="status_aktif" id="status_aktif"
                                class="form-select" required>
                            <option value="1" <?= $status_aktif === '1' ? 'selected' : '' ?>>
                                Aktif
                            </option>
                            <option value="0" <?= $status_aktif === '0' ? 'selected' : '' ?>>
                                Nonaktif
                            </option>
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            Simpan Perubahan
                        </button>

                        <a href="kelas.php" class="btn btn-secondary">
                            Kembali
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<style>
    .konten-utama {
        margin-left: 220px;
        width: calc(100% - 220px);
        min-height: 100vh;
        padding: 24px;
        box-sizing: border-box;
    }

    @media (max-width: 768px) {
        .konten-utama {
            margin-left: 180px;
            width: calc(100% - 180px);
            padding: 15px;
        }
    }
</style>

</body>
</html>