```php
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] != "admin") {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "guru";

$query = "SELECT id, nip, nama, email, status_aktif
          FROM t_guru
          ORDER BY id DESC";

$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Guru</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            margin: 0;
            background-color: #f8f9fa;
        }

        .konten-utama {
            margin-left: 220px;
            width: calc(100% - 220px);
            min-height: 100vh;
            padding: 24px;
            box-sizing: border-box;
        }

        @media (max-width: 575.98px) {
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

    <div class="d-flex flex-wrap justify-content-between
                align-items-center gap-3 mb-4">
        <div>
            <h2>Data Guru</h2>
            <p class="text-muted mb-0">Kelola data guru</p>
        </div>

        <div>
            <a href="tambah_guru.php" class="btn btn-primary">
                + Tambah Guru
            </a>
        </div>
    </div>

    <?php if (isset($_GET['pesan'])) { ?>
        <?php if ($_GET['pesan'] == 'tambah') { ?>
            <div class="alert alert-success">Data guru berhasil ditambahkan.</div>
        <?php } elseif ($_GET['pesan'] == 'edit') { ?>
            <div class="alert alert-success">Data guru berhasil diperbarui.</div>
        <?php } elseif ($_GET['pesan'] == 'hapus') { ?>
            <div class="alert alert-success">Data guru berhasil dihapus.</div>
        <?php } elseif ($_GET['pesan'] == 'gagal_hapus') { ?>
            <div class="alert alert-danger">
                Data guru gagal dihapus. Data mungkin masih digunakan oleh tabel lain.
            </div>
        <?php } ?>
    <?php } ?>

    <div class="card shadow-sm border-0">
        <div class="card-body">

            <?php if (!$result) { ?>

                <div class="alert alert-danger">
                    Gagal mengambil data guru.
                </div>

            <?php } else { ?>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">

                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>NIP</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php
                        $no = 1;

                        if (mysqli_num_rows($result) > 0) {
                            while ($guru = mysqli_fetch_assoc($result)) {
                        ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= htmlspecialchars($guru['nip'] ?? ''); ?></td>
                                <td><?= htmlspecialchars($guru['nama'] ?? ''); ?></td>
                                <td><?= htmlspecialchars($guru['email'] ?? ''); ?></td>

                                <td>
                                    <?php if ($guru['status_aktif'] == 1) { ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php } else { ?>
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    <?php } ?>
                                </td>

                                <td>
                                    <a href="edit_guru.php?id=<?= (int)$guru['id']; ?>"
                                       class="btn btn-sm btn-warning mb-1">
                                        Edit
                                    </a>

                                    <form action="hapus_guru.php" method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Yakin ingin menghapus data guru ini?');">

                                        <input type="hidden" name="id"
                                               value="<?= (int)$guru['id']; ?>">

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
                                <td colspan="6" class="text-center">
                                    Belum ada data guru.
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>

                    </table>
                </div>

            <?php } ?>

        </div>
    </div>

</main>
</body>
</html>
```