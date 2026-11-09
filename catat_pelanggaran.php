
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

// Hanya akun guru yang boleh membuka halaman ini.
// Guru biasa dan guru yang menjadi wali kelas sama-sama diizinkan.
if (($_SESSION['role'] ?? '') !== "guru") {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "catat_pelanggaran";
$idUser = (int) $_SESSION['id'];
$pesan = "";
$berhasil = isset($_GET['berhasil']);

function e($nilai)
{
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
}

// Identitas guru yang login
$stmtGuru = mysqli_prepare($koneksi, "
    SELECT id, nama
    FROM t_guru
    WHERE user_id = ?
    AND status_aktif = 1
    LIMIT 1
");

if (!$stmtGuru) {
    die("Gagal mengambil data guru: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmtGuru, "i", $idUser);
mysqli_stmt_execute($stmtGuru);
$hasilGuru = mysqli_stmt_get_result($stmtGuru);
$guru = mysqli_fetch_assoc($hasilGuru);
mysqli_stmt_close($stmtGuru);

if (!$guru) {
    die("Data guru tidak ditemukan atau akun guru tidak aktif.");
}

$guruId = (int) $guru['id'];
$namaGuru = $guru['nama'];

// Tahun ajaran aktif
$queryTahun = mysqli_query($koneksi, "
    SELECT id, nama
    FROM t_tahun_ajaran
    WHERE status_aktif = 1
    LIMIT 1
");

if (!$queryTahun) {
    die("Gagal mengambil tahun ajaran: " . mysqli_error($koneksi));
}

$tahun = mysqli_fetch_assoc($queryTahun);

if (!$tahun) {
    die("Belum ada tahun ajaran yang aktif.");
}

$tahunId = (int) $tahun['id'];

// Ambil semua kelas aktif yang memiliki siswa aktif.
// Tidak dibatasi berdasarkan kelas wali kelas.
$queryKelas = mysqli_query($koneksi, "
    SELECT DISTINCT
        k.id,
        k.nama
    FROM t_kelas_siswa ks
    JOIN t_kelas k ON ks.kelas_id = k.id
    WHERE ks.tahun_ajaran_id = $tahunId
    AND ks.status_aktif = 1
    AND k.status_aktif = 1
    ORDER BY k.nama ASC
");

if (!$queryKelas) {
    die("Gagal mengambil daftar kelas: " . mysqli_error($koneksi));
}

$daftarKelas = [];

while ($kelas = mysqli_fetch_assoc($queryKelas)) {
    $daftarKelas[] = $kelas;
}

// Ambil semua siswa aktif dari seluruh kelas pada tahun ajaran aktif.
$querySiswa = mysqli_query($koneksi, "
    SELECT
        s.id,
        s.nis,
        s.nama,
        ks.kelas_id
    FROM t_kelas_siswa ks
    JOIN t_siswa s ON ks.siswa_id = s.id
    JOIN t_kelas k ON ks.kelas_id = k.id
    WHERE ks.tahun_ajaran_id = $tahunId
    AND ks.status_aktif = 1
    AND s.status_aktif = 1
    AND k.status_aktif = 1
    ORDER BY s.nama ASC
");

if (!$querySiswa) {
    die("Gagal mengambil daftar siswa: " . mysqli_error($koneksi));
}

$daftarSiswa = [];

while ($siswa = mysqli_fetch_assoc($querySiswa)) {
    $daftarSiswa[] = $siswa;
}

// Jenis pelanggaran aktif
$queryPelanggaran = mysqli_query($koneksi, "
    SELECT
        id,
        pelanggaran_kategori_id,
        nama,
        poin
    FROM t_pelanggaran
    WHERE status_aktif = 1
    ORDER BY nama ASC
");

if (!$queryPelanggaran) {
    die("Gagal mengambil jenis pelanggaran: " . mysqli_error($koneksi));
}

// Nilai pilihan agar tetap terisi jika terjadi kesalahan
$kelasPilihan = (int) ($_POST['kelas_id'] ?? 0);
$siswaPilihan = (int) ($_POST['siswa_id'] ?? 0);

// Proses penyimpanan
if (isset($_POST['simpan'])) {
    $kelasPilihan = (int) ($_POST['kelas_id'] ?? 0);
    $siswaId = (int) ($_POST['siswa_id'] ?? 0);
    $pelanggaranId = (int) ($_POST['pelanggaran_id'] ?? 0);
    $tanggal = $_POST['tanggal'] ?? '';
    $keterangan = trim($_POST['keterangan'] ?? '');

    if (
        $kelasPilihan <= 0 ||
        $siswaId <= 0 ||
        $pelanggaranId <= 0 ||
        $tanggal == "" ||
        $keterangan == ""
    ) {
        $pesan = "Semua data wajib diisi.";
    } else {
        // Pastikan siswa berada di kelas yang dipilih.
        // Pemeriksaan ini berlaku untuk semua guru dan wali kelas.
        $stmtSiswa = mysqli_prepare($koneksi, "
            SELECT
                s.id,
                s.nama,
                ks.kelas_id,
                k.nama AS nama_kelas
            FROM t_kelas_siswa ks
            JOIN t_siswa s ON ks.siswa_id = s.id
            JOIN t_kelas k ON ks.kelas_id = k.id
            WHERE s.id = ?
            AND ks.kelas_id = ?
            AND ks.tahun_ajaran_id = ?
            AND ks.status_aktif = 1
            AND s.status_aktif = 1
            AND k.status_aktif = 1
            LIMIT 1
        ");

        if (!$stmtSiswa) {
            $pesan = "Gagal memeriksa data siswa: " . mysqli_error($koneksi);
        } else {
            mysqli_stmt_bind_param(
                $stmtSiswa,
                "iii",
                $siswaId,
                $kelasPilihan,
                $tahunId
            );

            mysqli_stmt_execute($stmtSiswa);
            $hasilSiswa = mysqli_stmt_get_result($stmtSiswa);
            $dataSiswa = mysqli_fetch_assoc($hasilSiswa);
            mysqli_stmt_close($stmtSiswa);

            // Pastikan jenis pelanggaran masih aktif
            $stmtPelanggaran = mysqli_prepare($koneksi, "
                SELECT id, nama, poin, pelanggaran_kategori_id
                FROM t_pelanggaran
                WHERE id = ?
                AND status_aktif = 1
                LIMIT 1
            ");

            if (!$stmtPelanggaran) {
                $pesan = "Gagal memeriksa jenis pelanggaran: " . mysqli_error($koneksi);
                $dataPelanggaran = null;
            } else {
                mysqli_stmt_bind_param(
                    $stmtPelanggaran,
                    "i",
                    $pelanggaranId
                );

                mysqli_stmt_execute($stmtPelanggaran);
                $hasilPelanggaran = mysqli_stmt_get_result($stmtPelanggaran);
                $dataPelanggaran = mysqli_fetch_assoc($hasilPelanggaran);
                mysqli_stmt_close($stmtPelanggaran);
            }

            if ($pesan == "") {
                if (!$dataSiswa) {
                    $pesan = "Siswa tidak ditemukan dalam kelas yang dipilih atau sudah tidak aktif.";
                } elseif (!$dataPelanggaran) {
                    $pesan = "Jenis pelanggaran tidak ditemukan atau sudah tidak aktif.";
                } elseif (
                    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal) ||
                    !checkdate(
                        (int) substr($tanggal, 5, 2),
                        (int) substr($tanggal, 8, 2),
                        (int) substr($tanggal, 0, 4)
                    ) ||
                    $tanggal > date('Y-m-d')
                ) {
                    $pesan = "Tanggal pelanggaran tidak valid.";
                } else {
                    $namaSiswa = $dataSiswa['nama'];
                    $kelasId = (int) $dataSiswa['kelas_id'];
                    $namaKelas = $dataSiswa['nama_kelas'];

                    $namaPelanggaran = $dataPelanggaran['nama'];
                    $kategoriId = (int) $dataPelanggaran['pelanggaran_kategori_id'];
                    $poin = (int) $dataPelanggaran['poin'];

                    $tindakan = "Belum ada tindakan";
                    $status = "Belum ditangani";

                    $stmt = mysqli_prepare($koneksi, "
                        INSERT INTO t_pelanggaran_siswa (
                            tahun_ajaran_id,
                            siswa_id,
                            nama_siswa,
                            kelas_id,
                            nama_kelas,
                            pelanggaran_id,
                            nama_pelanggaran,
                            pelanggaran_kategori_id,
                            guru_id,
                            nama_guru,
                            tanggal,
                            keterangan,
                            poin,
                            tindakan,
                            status
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");

                    if (!$stmt) {
                        $pesan = "Gagal menyiapkan penyimpanan: " . mysqli_error($koneksi);
                    } else {
                        mysqli_stmt_bind_param(
                            $stmt,
                            "iisisisiisssiss",
                            $tahunId,
                            $siswaId,
                            $namaSiswa,
                            $kelasId,
                            $namaKelas,
                            $pelanggaranId,
                            $namaPelanggaran,
                            $kategoriId,
                            $guruId,
                            $namaGuru,
                            $tanggal,
                            $keterangan,
                            $poin,
                            $tindakan,
                            $status
                        );

                        if (mysqli_stmt_execute($stmt)) {
                            mysqli_stmt_close($stmt);
                            header("Location: catat_pelanggaran.php?berhasil=1");
                            exit;
                        } else {
                            $pesan = "Gagal menyimpan pelanggaran: " . mysqli_stmt_error($stmt);
                        }

                        mysqli_stmt_close($stmt);
                    }
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Catat Pelanggaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<?php include "includes/sidebar.php"; ?>

<div style="margin-left:220px; padding:30px;">

    <div class="mb-4">
        <h3 class="fw-bold">Catat Pelanggaran</h3>
        <p class="text-muted">
            Form pencatatan pelanggaran siswa.
        </p>
    </div>

    <?php if ($berhasil): ?>
        <div class="alert alert-success">
            Pelanggaran berhasil dicatat!
        </div>
    <?php endif; ?>

    <?php if ($pesan != ""): ?>
        <div class="alert alert-danger">
            <?= e($pesan) ?>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Tahun Ajaran
                    </label>
                    <input
                        type="text"
                        class="form-control"
                        value="<?= e($tahun['nama']) ?>"
                        readonly
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Guru Pencatat
                    </label>
                    <input
                        type="text"
                        class="form-control"
                        value="<?= e($namaGuru) ?>"
                        readonly
                    >
                </div>

                <hr>

                <h5 class="mb-3">Data Siswa</h5>

                <div class="mb-3">
                    <label for="kelas_id" class="form-label fw-semibold">
                        Pilih Kelas
                    </label>

                    <select
                        name="kelas_id"
                        id="kelas_id"
                        class="form-select"
                        required
                    >
                        <option value="">-- Pilih Kelas Terlebih Dahulu --</option>

                        <?php foreach ($daftarKelas as $k): ?>
                            <option
                                value="<?= (int) $k['id'] ?>"
                                <?= $kelasPilihan == $k['id'] ? 'selected' : '' ?>
                            >
                                <?= e($k['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-2">
                    <label for="cari_siswa" class="form-label fw-semibold">
                        Cari Siswa
                    </label>

                    <input
                        type="search"
                        id="cari_siswa"
                        class="form-control"
                        placeholder="Ketik nama atau NIS siswa..."
                        disabled
                    >
                </div>

                <div class="mb-3">
                    <label for="siswa_id" class="form-label fw-semibold">
                        Pilih Siswa
                    </label>

                    <select
                        name="siswa_id"
                        id="siswa_id"
                        class="form-select"
                        required
                        disabled
                    >
                        <option value="">-- Pilih Kelas Terlebih Dahulu --</option>

                        <?php foreach ($daftarSiswa as $s): ?>
                            <option
                                value="<?= (int) $s['id'] ?>"
                                data-kelas="<?= (int) $s['kelas_id'] ?>"
                                data-cari="<?= e(strtolower($s['nis'] . ' ' . $s['nama'])) ?>"
                                <?= $siswaPilihan == $s['id'] ? 'selected' : '' ?>
                            >
                                <?= e($s['nis'] . ' - ' . $s['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <small class="text-muted" id="info_siswa">
                        Pilih kelas untuk menampilkan daftar siswanya.
                    </small>
                </div>

                <hr>

                <h5 class="mb-3">Detail Pelanggaran</h5>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Jenis Pelanggaran
                    </label>

                    <select name="pelanggaran_id" class="form-select" required>
                        <option value="">-- Pilih Pelanggaran --</option>

                        <?php while ($p = mysqli_fetch_assoc($queryPelanggaran)): ?>
                            <option
                                value="<?= (int) $p['id'] ?>"
                                <?= (($_POST['pelanggaran_id'] ?? '') == $p['id']) ? 'selected' : '' ?>
                            >
                                <?= e($p['nama']) ?> - <?= (int) $p['poin'] ?> Poin
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Tanggal Pelanggaran
                    </label>

                    <input
                        type="date"
                        name="tanggal"
                        class="form-control"
                        max="<?= date('Y-m-d') ?>"
                        value="<?= e($_POST['tanggal'] ?? date('Y-m-d')) ?>"
                        required
                    >
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">
                        Keterangan Kejadian
                    </label>

                    <textarea
                        name="keterangan"
                        class="form-control"
                        rows="4"
                        placeholder="Jelaskan kejadian pelanggaran siswa..."
                        required
                    ><?= e($_POST['keterangan'] ?? '') ?></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button
                        type="submit"
                        name="simpan"
                        class="btn btn-primary"
                    >
                        Simpan Pelanggaran
                    </button>

                    <button type="reset" class="btn btn-secondary">
                        Reset
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
const pilihKelas = document.getElementById('kelas_id');
const cariSiswa = document.getElementById('cari_siswa');
const pilihSiswa = document.getElementById('siswa_id');
const infoSiswa = document.getElementById('info_siswa');

function tampilkanSiswa() {
    const kelasId = pilihKelas.value;
    const keyword = cariSiswa.value.toLowerCase().trim();

    let jumlahSiswa = 0;
    let jumlahCocok = 0;

    const pilihanSiswa = pilihSiswa.options;

    for (let i = 0; i < pilihanSiswa.length; i++) {
        const opsi = pilihanSiswa[i];

        if (!opsi.value) {
            continue;
        }

        const kelasSiswa = opsi.dataset.kelas;
        const teksSiswa = opsi.dataset.cari || '';

        const sesuaiKelas = kelasSiswa === kelasId;
        const sesuaiCari = teksSiswa.includes(keyword);

        opsi.hidden = !(sesuaiKelas && sesuaiCari);
        opsi.disabled = !(sesuaiKelas && sesuaiCari);

        if (sesuaiKelas) {
            jumlahSiswa++;
        }

        if (sesuaiKelas && sesuaiCari) {
            jumlahCocok++;
        }
    }

    pilihSiswa.disabled = !kelasId;
    cariSiswa.disabled = !kelasId;

    if (!kelasId) {
        pilihSiswa.value = '';
        pilihSiswa.options[0].text = '-- Pilih Kelas Terlebih Dahulu --';
        infoSiswa.textContent = 'Pilih kelas untuk menampilkan daftar siswanya.';
        return;
    }

    pilihSiswa.options[0].text = '-- Pilih Siswa --';

    if (jumlahSiswa === 0) {
        infoSiswa.textContent = 'Belum ada siswa aktif di kelas ini.';
    } else if (jumlahCocok === 0) {
        infoSiswa.textContent = 'Siswa yang dicari tidak ditemukan.';
    } else {
        infoSiswa.textContent =
            jumlahCocok + ' siswa ditemukan dari ' +
            jumlahSiswa + ' siswa di kelas ini.';
    }
}

pilihKelas.addEventListener('change', function () {
    pilihSiswa.value = '';
    cariSiswa.value = '';
    tampilkanSiswa();
});

cariSiswa.addEventListener('input', function () {
    pilihSiswa.value = '';
    tampilkanSiswa();
});

// Atur tampilan awal, termasuk ketika form dikirim ulang karena error.
tampilkanSiswa();
</script>

</body>
</html>
