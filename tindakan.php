
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] !== "guru") {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "catat_pelanggaran";

$idUser = (int)$_SESSION['id'];
$idPelanggaran = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$pesan = "";

// Ambil ID guru berdasarkan akun yang sedang login
$stmtGuru = mysqli_prepare($koneksi, "
    SELECT id, nama
    FROM t_guru
    WHERE user_id = ? AND status_aktif = 1
    LIMIT 1
");

mysqli_stmt_bind_param($stmtGuru, "i", $idUser);
mysqli_stmt_execute($stmtGuru);
$resultGuru = mysqli_stmt_get_result($stmtGuru);
$guru = mysqli_fetch_assoc($resultGuru);
mysqli_stmt_close($stmtGuru);

if (!$guru) {
    die("Data guru tidak ditemukan atau akun tidak aktif.");
}

$idGuru = (int)$guru['id'];

// ID laporan wajib tersedia
if ($idPelanggaran <= 0) {
    header("Location: catatan_pelanggaran.php");
    exit;
}

// Ambil laporan milik guru yang sedang login
$stmtData = mysqli_prepare($koneksi, "
    SELECT *
    FROM t_pelanggaran_siswa
    WHERE id = ? AND guru_id = ?
    LIMIT 1
");

mysqli_stmt_bind_param($stmtData, "ii", $idPelanggaran, $idGuru);
mysqli_stmt_execute($stmtData);
$resultData = mysqli_stmt_get_result($stmtData);
$data = mysqli_fetch_assoc($resultData);
mysqli_stmt_close($stmtData);

// Tolak akses jika laporan bukan milik guru tersebut
if (!$data) {
    die("Akses ditolak. Anda hanya dapat mengubah tindakan dari laporan yang Anda catat sendiri.");
}

// Proses simpan tindakan
if (isset($_POST['simpan'])) {
    $tindakan = trim($_POST['tindakan'] ?? '');
    $status = $_POST['status'] ?? '';

    $statusValid = [
        "Belum Ditindak",
        "Selesai"
    ];

    if ($tindakan === "") {
        $pesan = "Catatan tindakan wajib diisi.";
    } elseif (!in_array($status, $statusValid, true)) {
        $pesan = "Status tindakan tidak valid.";
    } else {
        $stmtUpdate = mysqli_prepare($koneksi, "
            UPDATE t_pelanggaran_siswa
            SET tindakan = ?, status = ?
            WHERE id = ? AND guru_id = ?
        ");

        mysqli_stmt_bind_param(
            $stmtUpdate,
            "ssii",
            $tindakan,
            $status,
            $idPelanggaran,
            $idGuru
        );

        if (mysqli_stmt_execute($stmtUpdate)) {
            mysqli_stmt_close($stmtUpdate);

            header("Location: catatan_pelanggaran.php?berhasil_tindakan=1");
            exit;
        } else {
            $pesan = "Gagal memperbarui tindakan.";
            mysqli_stmt_close($stmtUpdate);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tindakan Pelanggaran</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<?php include "includes/sidebar.php"; ?>
<?php include "includes/navbar.php"; ?>

<div style="margin-left:220px; padding:30px;">

    <div class="mb-4">
        <h3 class="fw-bold">Tindakan Pelanggaran</h3>
        <p class="text-muted">
            Isi tindak lanjut dari laporan pelanggaran yang Anda catat.
        </p>
    </div>

    <?php if ($pesan !== ""): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($pesan) ?>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <h5 class="fw-bold mb-3">Data Pelanggaran</h5>

            <div class="row mb-2">
                <div class="col-md-3 text-muted">Nama Siswa</div>
                <div class="col-md-9 fw-semibold">
                    <?= htmlspecialchars($data['nama_siswa']) ?>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-3 text-muted">Kelas</div>
                <div class="col-md-9">
                    <?= htmlspecialchars($data['nama_kelas']) ?>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-3 text-muted">Pelanggaran</div>
                <div class="col-md-9">
                    <?= htmlspecialchars($data['nama_pelanggaran']) ?>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-3 text-muted">Tanggal</div>
                <div class="col-md-9">
                    <?= date('d-m-Y', strtotime($data['tanggal'])) ?>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-3 text-muted">Poin</div>
                <div class="col-md-9">
                    <span class="badge bg-primary">
                        <?= (int)$data['poin'] ?> Poin
                    </span>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-3 text-muted">Guru Pencatat</div>
                <div class="col-md-9">
                    <?= htmlspecialchars($data['nama_guru']) ?>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-3 text-muted">Keterangan</div>
                <div class="col-md-9">
                    <?= htmlspecialchars($data['keterangan'] ?: '-') ?>
                </div>
            </div>

            <hr>

            <h5 class="fw-bold mb-3">Form Tindakan</h5>

            <form method="POST">

                <input type="hidden" name="id"
                    value="<?= $idPelanggaran ?>">

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Catatan Tindakan
                    </label>

                    <textarea
                        name="tindakan"
                        class="form-control"
                        rows="4"
                        placeholder="Tuliskan tindakan atau pembinaan yang diberikan kepada siswa..."
                        required><?= htmlspecialchars($_POST['tindakan'] ?? $data['tindakan']) ?></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">
                        Status Tindakan
                    </label>

                    <select name="status" class="form-select" required>
                        <option value="Belum Ditindak"
                            <?= (($_POST['status'] ?? $data['status']) === "Belum Ditindak" ||
                                ($_POST['status'] ?? $data['status']) === "Belum ditangani")
                                ? "selected" : "" ?>>
                            Belum Ditindak
                        </option>

                        <option value="Selesai"
                            <?= ($_POST['status'] ?? $data['status']) === "Selesai"
                                ? "selected" : "" ?>>
                            Selesai
                        </option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <a href="catatan_pelanggaran.php"
                        class="btn btn-outline-secondary">
                        Kembali
                    </a>

                    <button type="submit" name="simpan"
                        class="btn btn-primary">
                        Simpan Tindakan
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

</body>
</html>
