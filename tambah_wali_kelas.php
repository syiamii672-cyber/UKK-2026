<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] !== "admin") {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "wali_kelas";
$error = "";

$guruTerpilih = "";
$kelasTerpilih = "";
$tahunTerpilih = "";
$tanggalMulai = "";
$tanggalSelesai = "";
$statusAktif = "1";

// Mengambil data guru aktif
$queryGuru = mysqli_query(
    $koneksi,
    "SELECT id, nip, nama
     FROM t_guru
     WHERE status_aktif = 1
     ORDER BY nama ASC"
);

if (!$queryGuru) {
    die("Gagal mengambil data guru: " . mysqli_error($koneksi));
}

// Mengambil data kelas aktif
$queryKelas = mysqli_query(
    $koneksi,
    "SELECT id, nama
     FROM t_kelas
     WHERE status_aktif = 1
     ORDER BY nama ASC"
);

if (!$queryKelas) {
    die("Gagal mengambil data kelas: " . mysqli_error($koneksi));
}

// Mengambil data tahun ajaran aktif
$queryTahun = mysqli_query(
    $koneksi,
    "SELECT id, nama
     FROM t_tahun_ajaran
     WHERE status_aktif = 1
     ORDER BY id DESC"
);

if (!$queryTahun) {
    die("Gagal mengambil data tahun ajaran: " . mysqli_error($koneksi));
}

// Proses penyimpanan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $guruTerpilih = (int) ($_POST['guru_id'] ?? 0);
    $kelasTerpilih = (int) ($_POST['kelas_id'] ?? 0);
    $tahunTerpilih = (int) ($_POST['tahun_ajaran_id'] ?? 0);
    $tanggalMulai = trim($_POST['tanggal_mulai'] ?? '');
    $tanggalSelesai = trim($_POST['tanggal_selesai'] ?? '');
    $statusAktif = $_POST['status_aktif'] ?? '';

    // Validasi tanggal
    $formatMulai = DateTime::createFromFormat('!Y-m-d', $tanggalMulai);
    $formatSelesai = DateTime::createFromFormat('!Y-m-d', $tanggalSelesai);

    $tanggalValid =
        $formatMulai &&
        $formatMulai->format('Y-m-d') === $tanggalMulai &&
        $formatSelesai &&
        $formatSelesai->format('Y-m-d') === $tanggalSelesai;

    if (
        $guruTerpilih <= 0 ||
        $kelasTerpilih <= 0 ||
        $tahunTerpilih <= 0 ||
        !$tanggalValid ||
        !in_array($statusAktif, ['0', '1'], true)
    ) {
        $error = "Semua data wajib diisi dengan benar.";
    } elseif ($tanggalSelesai < $tanggalMulai) {
        $error = "Tanggal selesai tidak boleh sebelum tanggal mulai.";
    } else {
        // Pastikan pilihan berasal dari data yang aktif
        $sqlValidasi = "
            SELECT
                (SELECT COUNT(*) FROM t_guru
                 WHERE id = ? AND status_aktif = 1) AS jumlah_guru,
                (SELECT COUNT(*) FROM t_kelas
                 WHERE id = ? AND status_aktif = 1) AS jumlah_kelas,
                (SELECT COUNT(*) FROM t_tahun_ajaran
                 WHERE id = ? AND status_aktif = 1) AS jumlah_tahun
        ";

        $stmtValidasi = mysqli_prepare($koneksi, $sqlValidasi);
        mysqli_stmt_bind_param(
            $stmtValidasi,
            "iii",
            $guruTerpilih,
            $kelasTerpilih,
            $tahunTerpilih
        );
        mysqli_stmt_execute($stmtValidasi);

        $dataValidasi = mysqli_fetch_assoc(
            mysqli_stmt_get_result($stmtValidasi)
        );

        mysqli_stmt_close($stmtValidasi);

        if (
            (int) $dataValidasi['jumlah_guru'] !== 1 ||
            (int) $dataValidasi['jumlah_kelas'] !== 1 ||
            (int) $dataValidasi['jumlah_tahun'] !== 1
        ) {
            $error = "Guru, kelas, atau tahun ajaran tidak valid atau sudah tidak aktif.";
        } else {
            // Cegah dua wali kelas aktif pada kelas dan tahun ajaran yang sama
            if ($statusAktif === '1') {
                $sqlCek = "
                    SELECT id
                    FROM t_wali_kelas
                    WHERE kelas_id = ?
                      AND tahun_ajaran_id = ?
                      AND status_aktif = 1
                    LIMIT 1
                ";

                $stmtCek = mysqli_prepare($koneksi, $sqlCek);
                mysqli_stmt_bind_param(
                    $stmtCek,
                    "ii",
                    $kelasTerpilih,
                    $tahunTerpilih
                );
                mysqli_stmt_execute($stmtCek);

                $hasilCek = mysqli_stmt_get_result($stmtCek);
                $sudahAda = mysqli_num_rows($hasilCek) > 0;

                mysqli_stmt_close($stmtCek);

                if ($sudahAda) {
                    $error = "Kelas tersebut sudah memiliki wali kelas aktif pada tahun ajaran yang dipilih.";
                }
            }

            if ($error === "") {
                $sqlSimpan = "
                    INSERT INTO t_wali_kelas (
                        tahun_ajaran_id,
                        kelas_id,
                        guru_id,
                        tanggal_mulai,
                        tanggal_selesai,
                        status_aktif
                    ) VALUES (?, ?, ?, ?, ?, ?)
                ";

                $stmtSimpan = mysqli_prepare($koneksi, $sqlSimpan);

                mysqli_stmt_bind_param(
                    $stmtSimpan,
                    "iiissi",
                    $tahunTerpilih,
                    $kelasTerpilih,
                    $guruTerpilih,
                    $tanggalMulai,
                    $tanggalSelesai,
                    $statusAktif
                );

                if (mysqli_stmt_execute($stmtSimpan)) {
                    mysqli_stmt_close($stmtSimpan);
                    header("Location: wali_kelas.php?pesan=tambah");
                    exit;
                } else {
                    $error = "Gagal menyimpan penugasan wali kelas.";
                }

                if (isset($stmtSimpan)) {
                    mysqli_stmt_close($stmtSimpan);
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Wali Kelas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            margin: 0;
            background: #f8fafc;
            color: #172554;
        }

        .konten-utama {
            margin-left: 220px;
            width: calc(100% - 220px);
            min-height: 100vh;
            padding: 28px;
            box-sizing: border-box;
        }

        .kartu-form {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 4px 18px rgba(23, 37, 84, 0.05);
        }

        .judul-halaman {
            color: #172554;
            font-weight: 700;
        }

        .form-label {
            color: #334155;
            font-weight: 600;
        }

        .form-control,
        .form-select {
            padding: 11px 12px;
            border-radius: 8px;
        }

        .btn-simpan {
            background: #2563eb;
            border-color: #2563eb;
            color: #fff;
        }

        .btn-simpan:hover {
            background: #1d4ed8;
            color: #fff;
        }

        @media (max-width: 575.98px) {
            .konten-utama {
                padding: 16px;
            }

            .kartu-form {
                padding: 18px;
            }
        }
    </style>
</head>

<body>

<?php include "includes/sidebar.php"; ?>

<main class="konten-utama">

    <div class="mb-4">
        <h3 class="judul-halaman mb-1">Tambah Penugasan Wali Kelas</h3>
        <p class="text-secondary mb-0">
            Tentukan guru, kelas, tahun ajaran, dan masa penugasan.
        </p>
    </div>

    <div class="kartu-form">

        <?php if ($error !== ""): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="row g-3">

                <div class="col-md-6">
                    <label for="tahun_ajaran_id" class="form-label">
                        Tahun Ajaran
                    </label>

                    <select name="tahun_ajaran_id"
                            id="tahun_ajaran_id"
                            class="form-select"
                            required>
                        <option value="">-- Pilih Tahun Ajaran --</option>

                        <?php while ($tahun = mysqli_fetch_assoc($queryTahun)): ?>
                            <option
                                value="<?= (int) $tahun['id'] ?>"
                                <?= (string) $tahunTerpilih === (string) $tahun['id'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($tahun['nama']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="kelas_id" class="form-label">Kelas</label>

                    <select name="kelas_id"
                            id="kelas_id"
                            class="form-select"
                            required>
                        <option value="">-- Pilih Kelas --</option>

                        <?php while ($kelas = mysqli_fetch_assoc($queryKelas)): ?>
                            <option
                                value="<?= (int) $kelas['id'] ?>"
                                <?= (string) $kelasTerpilih === (string) $kelas['id'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($kelas['nama']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-12">
                    <label for="guru_id" class="form-label">Guru Wali Kelas</label>

                    <select name="guru_id"
                            id="guru_id"
                            class="form-select"
                            required>
                        <option value="">-- Pilih Guru --</option>

                        <?php while ($guru = mysqli_fetch_assoc($queryGuru)): ?>
                            <option
                                value="<?= (int) $guru['id'] ?>"
                                <?= (string) $guruTerpilih === (string) $guru['id'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($guru['nip'] . ' - ' . $guru['nama']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="tanggal_mulai" class="form-label">
                        Tanggal Mulai
                    </label>

                    <input
                        type="date"
                        name="tanggal_mulai"
                        id="tanggal_mulai"
                        class="form-control"
                        value="<?= htmlspecialchars($tanggalMulai) ?>"
                        required
                    >
                </div>

                <div class="col-md-6">
                    <label for="tanggal_selesai" class="form-label">
                        Tanggal Selesai
                    </label>

                    <input
                        type="date"
                        name="tanggal_selesai"
                        id="tanggal_selesai"
                        class="form-control"
                        value="<?= htmlspecialchars($tanggalSelesai) ?>"
                        required
                    >
                </div>

                <div class="col-md-6">
                    <label for="status_aktif" class="form-label">
                        Status Penugasan
                    </label>

                    <select name="status_aktif"
                            id="status_aktif"
                            class="form-select"
                            required>
                        <option value="1" <?= $statusAktif === '1' ? 'selected' : '' ?>>
                            Aktif
                        </option>

                        <option value="0" <?= $statusAktif === '0' ? 'selected' : '' ?>>
                            Tidak Aktif
                        </option>
                    </select>
                </div>

            </div>

            <hr class="my-4">

            <div class="d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-simpan px-4">
                    Simpan Penugasan
                </button>

                <a href="wali_kelas.php"
                   class="btn btn-outline-secondary px-4">
                    Kembali
                </a>
            </div>

        </form>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>