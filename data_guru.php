
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

    <title>Data Guru - Sistem Informasi Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

</head>

<body>

<div class="container-fluid">

    <div class="row">

        <main class="col-12 p-4">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>
                    <h2>Data Guru</h2>
                    <p class="text-muted mb-0">
                        Kelola data guru
                    </p>
                </div>

                <a href="dashboard.php" class="btn btn-secondary">
                    Kembali
                </a>

            </div>


            <div class="card shadow-sm">

                <div class="card-body">

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

                                            <button class="btn btn-sm btn-warning" disabled>
                                                Edit
                                            </button>

                                            <button class="btn btn-sm btn-danger" disabled>
                                                Hapus
                                            </button>

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
