
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] != "admin") {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "penempatan";
$error = "";

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

// Mengambil data penempatan
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id, siswa_id, kelas_id, tahun_ajaran_id,
            tanggal_mulai, tanggal_selesai
     FROM t_kelas_siswa
     WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$data) {
    header("Location: penempatan_siswa.php?pesan=gagal");
    exit;
}

// Mengambil tahun ajaran aktif
$sqlTahun = "SELECT id, nama, tanggal_mulai, tanggal_selesai
             FROM t_tahun_ajaran
             WHERE status_aktif = 1
             ORDER BY tanggal_mulai DESC
             LIMIT 1";

$resultTahun = mysqli_query($koneksi, $sqlTahun);
$tahunAktif = mysqli_fetch_assoc($resultTahun);
$tahunId = $tahunAktif ? (int)$tahunAktif['id'] : 0;

// Nilai form
$siswaId = (int)($_POST['siswa_id'] ?? $data['siswa_id']);
$kelasId = (int)($_POST['kelas_id'] ?? $data['kelas_id']);

$tanggalMulai = $_POST['tanggal_mulai'] ?? $data['tanggal_mulai'];
$tanggalSelesai = $_POST['tanggal_selesai'] ?? $data['tanggal_selesai'];

// Jika tanggal lama berada di luar periode tahun ajaran aktif,
// gunakan tanggal awal dan akhir tahun ajaran aktif.
if ($tahunAktif && $_SERVER['REQUEST_METHOD'] !== 'POST') {

    if (
        $tanggalMulai < $tahunAktif['tanggal_mulai'] ||
        $tanggalMulai > $tahunAktif['tanggal_selesai']
    ) {
        $tanggalMulai = $tahunAktif['tanggal_mulai'];
    }

    if (
        $tanggalSelesai < $tahunAktif['tanggal_mulai'] ||
        $tanggalSelesai > $tahunAktif['tanggal_selesai']
    ) {
        $tanggalSelesai = $tahunAktif['tanggal_selesai'];
    }
}

// Proses update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!$tahunAktif) {
        $error = "Tidak ada tahun ajaran aktif. Silakan aktifkan tahun ajaran terlebih dahulu.";

    } elseif (
        $siswaId < 1 ||
        $kelasId < 1 ||
        $tanggalMulai === '' ||
        $tanggalSelesai === ''
    ) {
        $error = "Semua data wajib diisi.";

    } elseif (
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalMulai) ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalSelesai) ||
        strtotime($tanggalMulai) === false ||
        strtotime($tanggalSelesai) === false
    ) {
        $error = "Format tanggal tidak valid.";

    } elseif ($tanggalMulai > $tanggalSelesai) {
        $error = "Tanggal mulai tidak boleh melebihi tanggal selesai.";

    } elseif (
        $tanggalMulai < $tahunAktif['tanggal_mulai'] ||
        $tanggalSelesai > $tahunAktif['tanggal_selesai']
    ) {
        $error = "Tanggal penempatan harus berada dalam periode tahun ajaran aktif.";

    } else {

        // Memastikan siswa dan kelas valid
        $sqlValid = "SELECT
                        (SELECT COUNT(*) FROM t_siswa
                         WHERE id = ? AND status_aktif = 1) AS siswa_valid,

                        (SELECT COUNT(*) FROM t_kelas
                         WHERE id = ? AND status_aktif = 1) AS kelas_valid";

        $stmtValid = mysqli_prepare($koneksi, $sqlValid);

        mysqli_stmt_bind_param(
            $stmtValid,
            "ii",
            $siswaId,
            $kelasId
        );

        mysqli_stmt_execute($stmtValid);

        $valid = mysqli_fetch_assoc(
            mysqli_stmt_get_result($stmtValid)
        );

        // Data lama tetap boleh dipilih meskipun sudah tidak aktif
        $siswaValid = $valid['siswa_valid'] ||
                      $siswaId == (int)$data['siswa_id'];

        $kelasValid = $valid['kelas_valid'] ||
                      $kelasId == (int)$data['kelas_id'];

        if (!$siswaValid) {
            $error = "Siswa tidak ditemukan atau sudah tidak aktif.";

        } elseif (!$kelasValid) {
            $error = "Kelas tidak ditemukan atau sudah tidak aktif.";

        } else {

            // Memeriksa duplikasi penempatan
            $sqlCek = "SELECT id
                       FROM t_kelas_siswa
                       WHERE siswa_id = ?
                         AND tahun_ajaran_id = ?
                         AND id <> ?
                       LIMIT 1";

            $stmtCek = mysqli_prepare($koneksi, $sqlCek);

            mysqli_stmt_bind_param(
                $stmtCek,
                "iii",
                $siswaId,
                $tahunId,
                $id
            );

            mysqli_stmt_execute($stmtCek);

            $hasilCek = mysqli_stmt_get_result($stmtCek);

            if (mysqli_num_rows($hasilCek) > 0) {
                $error = "Siswa sudah memiliki penempatan pada tahun ajaran aktif.";

            } else {

                // Memperbarui data penempatan
                // Tahun ajaran otomatis mengikuti tahun aktif
                $sqlUpdate = "UPDATE t_kelas_siswa
                              SET siswa_id = ?,
                                  kelas_id = ?,
                                  tahun_ajaran_id = ?,
                                  tanggal_mulai = ?,
                                  tanggal_selesai = ?,
                                  status_aktif = 1
                              WHERE id = ?";

                $stmtUpdate = mysqli_prepare($koneksi, $sqlUpdate);

                mysqli_stmt_bind_param(
                    $stmtUpdate,
                    "iiissi",
                    $siswaId,
                    $kelasId,
                    $tahunId,
                    $tanggalMulai,
                    $tanggalSelesai,
                    $id
                );

                try {
                    if (mysqli_stmt_execute($stmtUpdate)) {
                        header("Location: penempatan_siswa.php?pesan=edit");
                        exit;
                    }

                    $error = "Penempatan gagal diperbarui.";

                } catch (mysqli_sql_exception $e) {
                    $error = "Penempatan gagal diperbarui. Periksa kembali data yang dipilih.";
                }
            }
        }
    }
}

// Mengambil siswa aktif dan siswa lama yang sedang diedit
$siswa = mysqli_query(
    $koneksi,
    "SELECT id, nis, nama
     FROM t_siswa
     WHERE status_aktif = 1
        OR id = " . (int)$data['siswa_id'] . "
     ORDER BY nama ASC"
);

// Mengambil kelas aktif dan kelas lama yang sedang diedit
$kelas = mysqli_query(
    $koneksi,
    "SELECT id, nama, tingkat, jurusan
     FROM t_kelas
     WHERE status_aktif = 1
        OR id = " . (int)$data['kelas_id'] . "
     ORDER BY nama ASC"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Edit Penempatan Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.bootstrap5.min.css"
          rel="stylesheet">

          
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

        .konten-utama,
        .konten-utama * {
            box-sizing: border-box;
        }

        .konten-utama .judul-halaman {
            color: #173B67;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .konten-utama .deskripsi-halaman {
            color: #718096;
            font-size: 14px;
            margin-bottom: 0;
        }

        .konten-utama .card-form {
            background: #fff;
            border: 1px solid #E5EAF2;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(23, 59, 103, 0.06);
        }

        .konten-utama .form-label {
            color: #34495E;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .konten-utama .form-control,
        .konten-utama .form-select {
            min-height: 43px;
            border: 1px solid #D8E1ED;
            border-radius: 8px;
            font-size: 14px;
        }

        .konten-utama .form-control:focus,
        .konten-utama .form-select:focus {
            border-color: #3779C5;
            box-shadow: 0 0 0 3px rgba(55, 121, 197, 0.12);
        }

        .konten-utama .tahun-aktif {
            background: #EDF4FC;
            color: #173B67;
            font-weight: 600;
        }

        .konten-utama .keterangan {
            color: #718096;
            font-size: 12px;
            margin-top: 6px;
        }

        .konten-utama .btn-simpan {
            background: #245B96;
            border: 1px solid #245B96;
            color: #fff;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
        }

        .konten-utama .btn-simpan:hover {
            background: #173B67;
            border-color: #173B67;
            color: #fff;
        }

        .konten-utama .btn-kembali {
            background: #E9EEF5;
            border: 1px solid #E9EEF5;
            color: #40546B;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
        }

        .konten-utama .btn-kembali:hover {
            background: #DCE5F0;
            color: #173B67;
        }

        .konten-utama .alert {
            border: 0;
            border-radius: 9px;
            font-size: 14px;
        }

        .konten-utama .bagian-form {
            border-bottom: 1px solid #E8EDF4;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .konten-utama .judul-bagian {
            color: #173B67;
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 18px;
        }

        /* Tampilan dropdown pencarian */
        .konten-utama .ts-control {
            min-height: 43px;
            border: 1px solid #D8E1ED;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 14px;
            box-shadow: none;
        }

        .konten-utama .ts-control.focus {
            border-color: #3779C5;
            box-shadow: 0 0 0 3px rgba(55, 121, 197, 0.12);
        }

        .konten-utama .ts-dropdown {
            border: 1px solid #D8E1ED;
            border-radius: 8px;
            overflow: hidden;
            font-size: 14px;
        }

        .konten-utama .ts-dropdown .option {
            padding: 10px 12px;
        }

        .konten-utama .ts-dropdown .active {
            background: #EDF4FC;
            color: #173B67;
        }

        .konten-utama .ts-dropdown-content {
            max-height: 250px;
        }

        @media (max-width: 767.98px) {
            .konten-utama {
                padding: 100px 15px 25px;
            }

            .konten-utama .judul-halaman {
                font-size: 22px;
            }
        }
    </style>
</head>

<body>

<?php include "includes/sidebar.php"; ?>
<?php include "includes/navbar.php"; ?>

<main class="konten-utama">

    <div class="mb-4">
        <h2 class="judul-halaman">Edit Penempatan Siswa</h2>
        <p class="deskripsi-halaman">
            Perbarui data siswa, kelas, dan periode penempatan.
        </p>
    </div>

    <?php if ($error !== '') { ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php } ?>

    <?php if (!$tahunAktif) { ?>
        <div class="alert alert-warning">
            Belum ada tahun ajaran aktif. Silakan aktifkan tahun ajaran terlebih dahulu sebelum mengubah penempatan siswa.
        </div>
    <?php } ?>

    <div class="card card-form">
        <div class="card-body p-3 p-md-4">

            <form method="POST">

                <input type="hidden" name="id" value="<?= $id; ?>">

                <div class="bagian-form">
                    <h6 class="judul-bagian">Data Penempatan</h6>

                    <div class="mb-4">
                        <label for="siswa_id" class="form-label">
                            Siswa <span class="text-danger">*</span>
                        </label>

                        <select
                            name="siswa_id"
                            id="siswa_id"
                            class="form-select"
                            placeholder="Cari NIS atau nama siswa..."
                            required>

                            <option value="">-- Pilih Siswa --</option>

                            <?php while ($s = mysqli_fetch_assoc($siswa)) { ?>
                                <option
                                    value="<?= (int)$s['id']; ?>"
                                    <?= $siswaId == $s['id'] ? 'selected' : ''; ?>>

                                    <?= htmlspecialchars(
                                        $s['nis'] . ' - ' . $s['nama'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>

                                </option>
                            <?php } ?>

                        </select>

                        <div class="keterangan">
                            Pilih siswa dengan mencari berdasarkan NIS atau nama.
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="kelas_id" class="form-label">
                            Kelas <span class="text-danger">*</span>
                        </label>

                        <select
                            name="kelas_id"
                            id="kelas_id"
                            class="form-select"
                            placeholder="Cari nama kelas atau jurusan..."
                            required>

                            <option value="">-- Pilih Kelas --</option>

                            <?php while ($k = mysqli_fetch_assoc($kelas)) { ?>
                                <option
                                    value="<?= (int)$k['id']; ?>"
                                    <?= $kelasId == $k['id'] ? 'selected' : ''; ?>>

                                    <?= htmlspecialchars(
                                        $k['nama'] . ' - ' . $k['tingkat'] . ' ' . $k['jurusan'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>

                                </option>
                            <?php } ?>

                        </select>

                        <div class="keterangan">
                            Pilih kelas dengan mencari nama kelas, tingkat, atau jurusan.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tahun Ajaran Aktif</label>

                        <?php if ($tahunAktif) { ?>
                            <input
                                type="text"
                                class="form-control tahun-aktif"
                                value="<?= htmlspecialchars($tahunAktif['nama'], ENT_QUOTES, 'UTF-8'); ?>"
                                readonly>

                            <div class="keterangan">
                                Tahun ajaran ditentukan otomatis berdasarkan pengaturan tahun ajaran aktif.
                            </div>
                        <?php } else { ?>
                            <input
                                type="text"
                                class="form-control"
                                value="Belum ada tahun ajaran aktif"
                                readonly>
                        <?php } ?>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="judul-bagian">Periode Penempatan</h6>

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label for="tanggal_mulai" class="form-label">
                                Tanggal Mulai <span class="text-danger">*</span>
                            </label>

                            <input
                                type="date"
                                name="tanggal_mulai"
                                id="tanggal_mulai"
                                class="form-control"
                                value="<?= htmlspecialchars($tanggalMulai, ENT_QUOTES, 'UTF-8'); ?>"
                                min="<?= $tahunAktif ? htmlspecialchars($tahunAktif['tanggal_mulai'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                                max="<?= $tahunAktif ? htmlspecialchars($tahunAktif['tanggal_selesai'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                                required
                                <?= !$tahunAktif ? 'disabled' : ''; ?>>
                        </div>

                        <div class="col-md-6">
                            <label for="tanggal_selesai" class="form-label">
                                Tanggal Selesai <span class="text-danger">*</span>
                            </label>

                            <input
                                type="date"
                                name="tanggal_selesai"
                                id="tanggal_selesai"
                                class="form-control"
                                value="<?= htmlspecialchars($tanggalSelesai, ENT_QUOTES, 'UTF-8'); ?>"
                                min="<?= $tahunAktif ? htmlspecialchars($tahunAktif['tanggal_mulai'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                                max="<?= $tahunAktif ? htmlspecialchars($tahunAktif['tanggal_selesai'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                                required
                                <?= !$tahunAktif ? 'disabled' : ''; ?>>
                        </div>

                    </div>

                    <div class="keterangan mt-2">
                        Tanggal penempatan harus berada dalam periode tahun ajaran aktif.
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">

                    <button
                        type="submit"
                        class="btn btn-simpan"
                        <?= !$tahunAktif ? 'disabled' : ''; ?>>
                        Simpan Perubahan
                    </button>

                    <a href="penempatan_siswa.php" class="btn btn-kembali">
                        Kembali
                    </a>

                </div>

            </form>
        </div>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js"></script>

<script>
// Dropdown siswa dengan pencarian di dalam dropdown
new TomSelect('#siswa_id', {
    create: false,
    searchField: ['text'],
    placeholder: 'Cari NIS atau nama siswa...',
    allowEmptyOption: true,
    maxOptions: 1000,
    closeAfterSelect: true
});

// Dropdown kelas dengan pencarian di dalam dropdown
new TomSelect('#kelas_id', {
    create: false,
    searchField: ['text'],
    placeholder: 'Cari nama kelas atau jurusan...',
    allowEmptyOption: true,
    maxOptions: 1000,
    closeAfterSelect: true
});

// Validasi tanggal
const tanggalMulai = document.getElementById('tanggal_mulai');
const tanggalSelesai = document.getElementById('tanggal_selesai');

if (tanggalMulai && tanggalSelesai) {
    tanggalMulai.addEventListener('change', function () {
        tanggalSelesai.min = this.value;

        if (
            tanggalSelesai.value !== '' &&
            tanggalSelesai.value < this.value
        ) {
            tanggalSelesai.value = this.value;
        }
    });

    tanggalSelesai.addEventListener('change', function () {
        tanggalMulai.max = this.value;
    });
}
</script>

</body>
</html>
