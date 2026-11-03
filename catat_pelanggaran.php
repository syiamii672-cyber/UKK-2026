
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] !== "guru") {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "catat_pelanggaran";

$idUser = (int) $_SESSION['id'];
$pesan = "";
$berhasil = isset($_GET['berhasil']);

// Ambil identitas guru yang sedang login
$queryGuru = mysqli_query($koneksi, "
    SELECT id, nama
    FROM t_guru
    WHERE user_id = $idUser
    AND status_aktif = 1
    LIMIT 1
");

$guru = mysqli_fetch_assoc($queryGuru);

if (!$guru) {
    die("Data guru tidak ditemukan atau akun guru tidak aktif.");
}

$guruId = (int) $guru['id'];
$namaGuru = $guru['nama'];

// Ambil tahun ajaran aktif
$queryTahun = mysqli_query($koneksi, "
    SELECT id, nama
    FROM t_tahun_ajaran
    WHERE status_aktif = 1
    LIMIT 1
");

$tahun = mysqli_fetch_assoc($queryTahun);

if (!$tahun) {
    die("Belum ada tahun ajaran yang aktif.");
}

$tahunId = (int) $tahun['id'];

// Ambil seluruh siswa dengan penempatan aktif
$querySiswa = mysqli_query($koneksi, "
    SELECT
        s.id,
        s.nis,
        s.nama,
        k.nama AS nama_kelas
    FROM t_kelas_siswa ks
    JOIN t_siswa s ON ks.siswa_id = s.id
    JOIN t_kelas k ON ks.kelas_id = k.id
    WHERE ks.tahun_ajaran_id = $tahunId
    AND ks.status_aktif = 1
    AND s.status_aktif = 1
    AND k.status_aktif = 1
    ORDER BY s.nama ASC
");

// Ambil jenis pelanggaran aktif
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

// Proses penyimpanan
if (isset($_POST['simpan'])) {

    $siswaId = (int) ($_POST['siswa_id'] ?? 0);
    $pelanggaranId = (int) ($_POST['pelanggaran_id'] ?? 0);
    $tanggal = $_POST['tanggal'] ?? '';
    $keterangan = trim($_POST['keterangan'] ?? '');

    if ($siswaId <= 0 || $pelanggaranId <= 0 || $tanggal == "" || $keterangan == "") {
        $pesan = "Semua data wajib diisi.";
    } else {

        // Pastikan siswa memiliki penempatan aktif
        $cekSiswa = mysqli_query($koneksi, "
            SELECT
                s.id,
                s.nama,
                ks.kelas_id,
                k.nama AS nama_kelas
            FROM t_kelas_siswa ks
            JOIN t_siswa s ON ks.siswa_id = s.id
            JOIN t_kelas k ON ks.kelas_id = k.id
            WHERE s.id = $siswaId
            AND ks.tahun_ajaran_id = $tahunId
            AND ks.status_aktif = 1
            AND s.status_aktif = 1
            AND k.status_aktif = 1
            LIMIT 1
        ");

        $dataSiswa = mysqli_fetch_assoc($cekSiswa);

        // Pastikan jenis pelanggaran masih aktif
        $cekPelanggaran = mysqli_query($koneksi, "
            SELECT id, nama, poin, pelanggaran_kategori_id
            FROM t_pelanggaran
            WHERE id = $pelanggaranId
            AND status_aktif = 1
            LIMIT 1
        ");

        $dataPelanggaran = mysqli_fetch_assoc($cekPelanggaran);

        if (!$dataSiswa) {
            $pesan = "Siswa tidak ditemukan atau belum memiliki kelas aktif.";
        } elseif (!$dataPelanggaran) {
            $pesan = "Jenis pelanggaran tidak ditemukan atau sudah tidak aktif.";
        } elseif (strtotime($tanggal) === false || $tanggal > date('Y-m-d')) {
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
                header("Location: catat_pelanggaran.php?berhasil=1");
                exit;
            } else {
                $pesan = "Gagal menyimpan pelanggaran.";
            }

            mysqli_stmt_close($stmt);
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
            <?= htmlspecialchars($pesan) ?>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Tahun Ajaran
                    </label>
                    <input type="text" class="form-control"
                        value="<?= htmlspecialchars($tahun['nama']) ?>"
                        readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Guru Pencatat
                    </label>
                    <input type="text" class="form-control"
                        value="<?= htmlspecialchars($namaGuru) ?>"
                        readonly>
                </div>

                <hr>

                <h5 class="mb-3">Data Siswa</h5>

                <div class="mb-3">
                    <label class="form-label">Pilih Siswa</label>

                    <select name="siswa_id" class="form-select" required>
                        <option value="">-- Pilih Siswa --</option>

                        <?php while ($s = mysqli_fetch_assoc($querySiswa)): ?>
                            <option value="<?= $s['id'] ?>"
                                <?= (($_POST['siswa_id'] ?? '') == $s['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['nis'] . " - " . $s['nama'] . " (" . $s['nama_kelas'] . ")") ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <hr>

                <h5 class="mb-3">Detail Pelanggaran</h5>

                <div class="mb-3">
                    <label class="form-label">Jenis Pelanggaran</label>

                    <select name="pelanggaran_id" class="form-select" required>
                        <option value="">-- Pilih Pelanggaran --</option>

                        <?php while ($p = mysqli_fetch_assoc($queryPelanggaran)): ?>
                            <option value="<?= $p['id'] ?>"
                                <?= (($_POST['pelanggaran_id'] ?? '') == $p['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['nama']) ?>
                                - <?= $p['poin'] ?> Poin
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tanggal Pelanggaran</label>
                    <input type="date" name="tanggal"
                        class="form-control"
                        max="<?= date('Y-m-d') ?>"
                        value="<?= htmlspecialchars($_POST['tanggal'] ?? date('Y-m-d')) ?>"
                        required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Keterangan Kejadian</label>
                    <textarea name="keterangan" class="form-control"
                        rows="4"
                        placeholder="Jelaskan kejadian pelanggaran siswa..."
                        required><?= htmlspecialchars($_POST['keterangan'] ?? '') ?></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" name="simpan" class="btn btn-primary">
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

</body>
</html>
