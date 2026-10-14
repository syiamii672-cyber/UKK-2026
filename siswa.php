
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] != "admin") {
    header("Location: dashboard.php");
    exit;
}

$query = "SELECT id, nis, nisn, nama, jenis_kelamin,
                 tanggal_lahir, alamat, status_aktif
          FROM t_siswa
          ORDER BY id DESC";

$result = mysqli_query($koneksi, $query);

if (!$result) {
    die("Gagal mengambil data siswa.");
}

$menuAktif = "siswa";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Data Siswa - Sistem Informasi Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            margin: 0;
            background-color: #f8f9fa;
        }

        /* Konten utama tidak tertutup sidebar */
        .konten-utama {
            margin-left: 220px;
            width: calc(100% - 220px);
            min-height: 100vh;
            padding: 24px;
            box-sizing: border-box;
        }

        @media (max-width: 767.98px) {
            .konten-utama {
                margin-left: 180px;
                width: calc(100% - 180px);
                padding: 15px;
            }
        }
    </style>
</head>

<body>

<?php include "includes/sidebar.php"; ?>

<main class="konten-utama">

    <!-- Judul dan tombol tambah -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h2 class="mb-1">Data Siswa</h2>
            <p class="text-muted mb-0">Kelola data siswa</p>
        </div>

        <a href="tambah_siswa.php" class="btn btn-primary">
            + Tambah Siswa
        </a>
    </div>

    <!-- Notifikasi -->
    <?php if (isset($_GET['pesan'])) { ?>

        <?php if ($_GET['pesan'] == 'tambah') { ?>
            <div class="alert alert-success">
                Data siswa berhasil ditambahkan.
            </div>

        <?php } elseif ($_GET['pesan'] == 'edit') { ?>
            <div class="alert alert-success">
                Data siswa berhasil diperbarui.
            </div>

        <?php } elseif ($_GET['pesan'] == 'hapus') { ?>
            <div class="alert alert-success">
                Data siswa berhasil dihapus.
            </div>

        <?php } elseif ($_GET['pesan'] == 'gagal_hapus') { ?>
            <div class="alert alert-danger">
                Data siswa tidak dapat dihapus. Siswa mungkin masih memiliki
                data kelas atau catatan pelanggaran.
            </div>
        <?php } ?>

    <?php } ?>

    <!-- Tabel data siswa -->
    <div class="card shadow-sm border-0">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">

                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>NIS</th>
                            <th>NISN</th>
                            <th>Nama</th>
                            <th>Jenis Kelamin</th>
                            <th>Status</th>
                            <th style="min-width: 140px;">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php
                    $no = 1;

                    if (mysqli_num_rows($result) > 0) {
                        while ($siswa = mysqli_fetch_assoc($result)) {
                    ?>

                        <tr>
                            <td><?php echo $no++; ?></td>

                            <td>
                                <?php echo htmlspecialchars($siswa['nis']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($siswa['nisn']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($siswa['nama']); ?>
                            </td>

                            <td>
                                <?php
                                if ($siswa['jenis_kelamin'] == 'L') {
                                    echo 'Laki-laki';
                                } elseif ($siswa['jenis_kelamin'] == 'P') {
                                    echo 'Perempuan';
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>

                            <td>
                                <?php if ($siswa['status_aktif'] == 1) { ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php } else { ?>
                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                <?php } ?>
                            </td>

                            <td>
                                <!-- Tombol Edit -->
                                <a href="edit_siswa.php?id=<?php echo (int) $siswa['id']; ?>"
                                   class="btn btn-sm btn-warning mb-1">
                                    Edit
                                </a>

                                <!-- Tombol Hapus -->
                                <form action="hapus_siswa.php"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Yakin ingin menghapus data siswa ini?')">

                                    <input type="hidden"
                                           name="id"
                                           value="<?php echo (int) $siswa['id']; ?>">

                                    <button type="submit"
                                            class="btn btn-sm btn-danger mb-1">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>

                    <?php
                        }
                    } else {
                    ?>

                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Belum ada data siswa.
                            </td>
                        </tr>

                    <?php } ?>
                    </tbody>

                </table>
            </div>

        </div>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>