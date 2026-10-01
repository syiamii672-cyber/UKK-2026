
<?php

include "includes/cek_session.php";
include "config/koneksi.php";

if ($_SESSION['role'] != "admin") {
    header("Location: dashboard.php");
    exit;
}

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

    <title>Kelola Data Guru - Sistem Informasi Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

</head>

<body>

<div class="container-fluid">

    <div class="row">

        <!-- SIDEBAR -->
        <?php include "includes/sidebar.php"; ?>


        <!-- KONTEN -->
        <main class="col-md-9 col-lg-10 p-4">

            <!-- JUDUL -->
            <div class="mb-4">

                <h2 class="mb-1">
                    Kelola Data Guru
                </h2>

                <p class="text-muted">
                    Kelola data guru yang terdaftar dalam sistem.
                </p>

            </div>


            <!-- CARD DATA GURU -->
            <div class="card shadow-sm">

                <div class="card-body">


                    <!-- HEADER CARD -->
                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <h5 class="card-title mb-0">
                            Daftar Guru
                        </h5>

                        <a href="tambah_guru.php" class="btn btn-primary">
                            + Tambah Guru
                        </a>

                    </div>


                    <!-- TABEL -->
                    <div class="table-responsive">

                        <table class="table table-bordered table-hover align-middle mb-0">

                            <thead class="table-dark">

                                <tr>

                                    <th width="60">No</th>
                                    <th>NIP</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th width="160">Aksi</th>

                                </tr>

                            </thead>


                            <tbody>

                                <?php

                                $no = 1;

                                if (mysqli_num_rows($result) > 0) {

                                    while ($guru = mysqli_fetch_assoc($result)) {

                                ?>

                                    <tr>

                                        <td>
                                            <?php echo $no++; ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($guru['nip']); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($guru['nama']); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($guru['email']); ?>
                                        </td>

                                        <td>

                                            <?php if ($guru['status_aktif'] == 1) { ?>

                                                <span class="badge bg-success">
                                                    Aktif
                                                </span>

                                            <?php } else { ?>

                                                <span class="badge bg-secondary">
                                                    Tidak Aktif
                                                </span>

                                            <?php } ?>

                                        </td>

                                        <td>

                                            <a href="#"
                                               class="btn btn-sm btn-warning">
                                                Edit
                                            </a>

                                            <a href="#"
                                               class="btn btn-sm btn-danger">
                                                Hapus
                                            </a>

                                        </td>

                                    </tr>

                                <?php

                                    }

                                } else {

                                ?>

                                    <tr>

                                        <td colspan="6" class="text-center text-muted py-4">

                                            Belum ada data guru.

                                        </td>

                                    </tr>

                                <?php

                                }

                                ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </main>

    </div>

</div>

</body>

</html>