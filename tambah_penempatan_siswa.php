
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "admin") {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "penempatan";
$error = "";

$siswaId = (int)($_POST['siswa_id'] ?? 0);
$kelasId = (int)($_POST['kelas_id'] ?? 0);
$tanggalMulai = $_POST['tanggal_mulai'] ?? '';
$tanggalSelesai = $_POST['tanggal_selesai'] ?? '';

// Mengambil tahun ajaran aktif
$sqlTahun = "SELECT id, nama, tanggal_mulai, tanggal_selesai
             FROM t_tahun_ajaran
             WHERE status_aktif = 1
             ORDER BY tanggal_mulai DESC
             LIMIT 1";

$resultTahun = mysqli_query($koneksi, $sqlTahun);
$tahunAktif = mysqli_fetch_assoc($resultTahun);
$tahunId = $tahunAktif ? (int)$tahunAktif['id'] : 0;

// Nilai awal tanggal mengikuti tahun ajaran aktif
if ($tahunAktif) {
    if ($tanggalMulai === '') {
        $tanggalMulai = $tahunAktif['tanggal_mulai'];
    }

    if ($tanggalSelesai === '') {
        $tanggalSelesai = $tahunAktif['tanggal_selesai'];
    }
}

// Proses penyimpanan
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
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalSelesai)
    ) {
        $error = "Format tanggal tidak valid.";

    } elseif (
        !checkdate(
            (int)substr($tanggalMulai, 5, 2),
            (int)substr($tanggalMulai, 8, 2),
            (int)substr($tanggalMulai, 0, 4)
        ) ||
        !checkdate(
            (int)substr($tanggalSelesai, 5, 2),
            (int)substr($tanggalSelesai, 8, 2),
            (int)substr($tanggalSelesai, 0, 4)
        )
    ) {
        $error = "Tanggal yang dimasukkan tidak valid.";

    } elseif ($tanggalMulai > $tanggalSelesai) {
        $error = "Tanggal mulai tidak boleh melebihi tanggal selesai.";

    } elseif (
        $tanggalMulai < $tahunAktif['tanggal_mulai'] ||
        $tanggalSelesai > $tahunAktif['tanggal_selesai']
    ) {
        $error = "Tanggal penempatan harus berada dalam periode tahun ajaran aktif.";

    } else {

        // Memastikan siswa dan kelas masih aktif
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
        $valid = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtValid));

        if (!$valid || !$valid['siswa_valid']) {
            $error = "Siswa tidak ditemukan atau sudah tidak aktif.";

        } elseif (!$valid['kelas_valid']) {
            $error = "Kelas tidak ditemukan atau sudah tidak aktif.";

        } else {

            // Memeriksa penempatan siswa pada tahun ajaran aktif
            $sqlCek = "SELECT id
                       FROM t_kelas_siswa
                       WHERE siswa_id = ?
                       AND tahun_ajaran_id = ?
                       LIMIT 1";

            $stmtCek = mysqli_prepare($koneksi, $sqlCek);

            mysqli_stmt_bind_param(
                $stmtCek,
                "ii",
                $siswaId,
                $tahunId
            );

            mysqli_stmt_execute($stmtCek);
            $hasilCek = mysqli_stmt_get_result($stmtCek);

            if (mysqli_num_rows($hasilCek) > 0) {
                $error = "Siswa sudah memiliki penempatan pada tahun ajaran aktif.";

            } else {

                // Menyimpan penempatan siswa
                $sqlSimpan = "INSERT INTO t_kelas_siswa
                              (
                                  siswa_id,
                                  kelas_id,
                                  tahun_ajaran_id,
                                  tanggal_mulai,
                                  tanggal_selesai,
                                  status_aktif
                              )
                              VALUES (?, ?, ?, ?, ?, 1)";

                $stmtSimpan = mysqli_prepare($koneksi, $sqlSimpan);

                mysqli_stmt_bind_param(
                    $stmtSimpan,
                    "iiiss",
                    $siswaId,
                    $kelasId,
                    $tahunId,
                    $tanggalMulai,
                    $tanggalSelesai
                );

                try {
                    if (mysqli_stmt_execute($stmtSimpan)) {
                        header("Location: penempatan_siswa.php?pesan=tambah");
                        exit;
                    }

                    $error = "Penempatan siswa gagal disimpan.";

                } catch (mysqli_sql_exception $e) {
                    $error = "Penempatan siswa gagal disimpan. Periksa kembali data yang dipilih.";
                }
            }
        }
    }
}

// Mengambil daftar siswa aktif
$siswa = mysqli_query(
    $koneksi,
    "SELECT id, nis, nama
     FROM t_siswa
     WHERE status_aktif = 1
     ORDER BY nama ASC"
);

// Mengambil daftar kelas aktif
$kelas = mysqli_query(
    $koneksi,
    "SELECT id, nama, tingkat, jurusan
     FROM t_kelas
     WHERE status_aktif = 1
     ORDER BY nama ASC"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Tambah Penempatan Siswa</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

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

        .konten-utama .form-control {
            min-height: 43px;
            border: 1px solid #D8E1ED;
            border-radius: 8px;
            font-size: 14px;
        }

        .konten-utama .form-control:focus {
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

        /* Dropdown pencarian */
        .konten-utama .dropdown-cari {
            position: relative;
            width: 100%;
        }

        .konten-utama .dropdown-cari-tombol {
            width: 100%;
            min-height: 43px;
            padding: 10px 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            background: #fff;
            border: 1px solid #D8E1ED;
            border-radius: 8px;
            color: #34495E;
            font-size: 14px;
            text-align: left;
            cursor: pointer;
        }

        .konten-utama .dropdown-cari-tombol:focus {
            outline: none;
            border-color: #3779C5;
            box-shadow: 0 0 0 3px rgba(55, 121, 197, 0.12);
        }

        .konten-utama .dropdown-cari-tombol .teks-pilihan {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .konten-utama .dropdown-cari-menu {
            display: none;
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            width: 100%;
            background: #fff;
            border: 1px solid #D8E1ED;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(23, 59, 103, 0.12);
            z-index: 1050;
            padding: 8px;
        }

        .konten-utama .dropdown-cari-menu.aktif {
            display: block;
        }

        .konten-utama .dropdown-cari-input {
            width: 100%;
            height: 40px;
            padding: 8px 10px;
            border: 1px solid #D8E1ED;
            border-radius: 7px;
            outline: none;
            font-size: 14px;
            margin-bottom: 7px;
        }

        .konten-utama .dropdown-cari-input:focus {
            border-color: #3779C5;
            box-shadow: 0 0 0 2px rgba(55, 121, 197, 0.10);
        }

        .konten-utama .dropdown-cari-daftar {
            max-height: 230px;
            overflow-y: auto;
        }

        .konten-utama .dropdown-cari-item {
            padding: 10px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
        }

        .konten-utama .dropdown-cari-item:hover {
            background: #EDF4FC;
            color: #173B67;
        }

        .konten-utama .dropdown-cari-item.sembunyi {
            display: none;
        }

        .konten-utama .dropdown-cari-kosong {
            padding: 12px;
            color: #718096;
            font-size: 13px;
            text-align: center;
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
        <h2 class="judul-halaman">Tambah Penempatan Siswa</h2>

        <p class="deskripsi-halaman">
            Tambahkan penempatan siswa berdasarkan kelas dan tahun ajaran aktif.
        </p>
    </div>

    <?php if ($error !== '') { ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php } ?>

    <?php if (!$tahunAktif) { ?>
        <div class="alert alert-warning">
            Belum ada tahun ajaran aktif. Silakan aktifkan tahun ajaran terlebih dahulu sebelum menambahkan penempatan siswa.
        </div>
    <?php } ?>

    <div class="card card-form">
        <div class="card-body p-3 p-md-4">

            <form method="POST" id="formPenempatan">

                <div class="bagian-form">

                    <h6 class="judul-bagian">Data Penempatan</h6>

                    <!-- Dropdown siswa -->
                    <div class="mb-4">

                        <label class="form-label">
                            Siswa <span class="text-danger">*</span>
                        </label>

                        <div class="dropdown-cari" id="dropdownSiswa">

                            <button
                                type="button"
                                class="dropdown-cari-tombol"
                                onclick="bukaDropdown('dropdownSiswa')">

                                <span class="teks-pilihan" id="labelSiswa">
                                    -- Pilih Siswa --
                                </span>

                                <span>▾</span>

                            </button>

                            <div class="dropdown-cari-menu">

                                <input
                                    type="text"
                                    class="dropdown-cari-input"
                                    placeholder="Cari NIS atau nama siswa..."
                                    autocomplete="off"
                                    oninput="filterDropdown(this)">

                                <div class="dropdown-cari-daftar">

                                    <?php while ($s = mysqli_fetch_assoc($siswa)) {
                                        $teksSiswa = $s['nis'] . ' - ' . $s['nama'];
                                    ?>

                                        <div
                                            class="dropdown-cari-item"
                                            data-value="<?= (int)$s['id']; ?>"
                                            onclick="pilihDropdown('dropdownSiswa', this)">

                                            <?= htmlspecialchars(
                                                $teksSiswa,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>

                                        </div>

                                    <?php } ?>

                                </div>

                            </div>

                            <input
                                type="hidden"
                                name="siswa_id"
                                id="siswa_id"
                                value="<?= $siswaId; ?>">

                        </div>

                        <div class="keterangan">
                            Cari siswa berdasarkan NIS atau nama langsung di dalam dropdown.
                        </div>

                    </div>

                    <!-- Dropdown kelas -->
                    <div class="mb-4">

                        <label class="form-label">
                            Kelas <span class="text-danger">*</span>
                        </label>

                        <div class="dropdown-cari" id="dropdownKelas">

                            <button
                                type="button"
                                class="dropdown-cari-tombol"
                                onclick="bukaDropdown('dropdownKelas')">

                                <span class="teks-pilihan" id="labelKelas">
                                    -- Pilih Kelas --
                                </span>

                                <span>▾</span>

                            </button>

                            <div class="dropdown-cari-menu">

                                <input
                                    type="text"
                                    class="dropdown-cari-input"
                                    placeholder="Cari nama kelas atau jurusan..."
                                    autocomplete="off"
                                    oninput="filterDropdown(this)">

                                <div class="dropdown-cari-daftar">

                                    <?php while ($k = mysqli_fetch_assoc($kelas)) {
                                        $teksKelas = $k['nama'] . ' - ' . $k['tingkat'] . ' ' . $k['jurusan'];
                                    ?>

                                        <div
                                            class="dropdown-cari-item"
                                            data-value="<?= (int)$k['id']; ?>"
                                            onclick="pilihDropdown('dropdownKelas', this)">

                                            <?= htmlspecialchars(
                                                $teksKelas,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>

                                        </div>

                                    <?php } ?>

                                </div>

                            </div>

                            <input
                                type="hidden"
                                name="kelas_id"
                                id="kelas_id"
                                value="<?= $kelasId; ?>">

                        </div>

                        <div class="keterangan">
                            Cari kelas berdasarkan nama, tingkat, atau jurusan langsung di dalam dropdown.
                        </div>

                    </div>

                    <!-- Tahun ajaran -->
                    <div class="mb-3">

                        <label class="form-label">
                            Tahun Ajaran Aktif
                        </label>

                        <?php if ($tahunAktif) { ?>

                            <input
                                type="text"
                                class="form-control tahun-aktif"
                                value="<?= htmlspecialchars(
                                    $tahunAktif['nama'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>"
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

                <!-- Periode penempatan -->
                <div class="mb-4">

                    <h6 class="judul-bagian">
                        Periode Penempatan
                    </h6>

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
                                value="<?= htmlspecialchars(
                                    $tanggalMulai,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>"
                                min="<?= $tahunAktif ? htmlspecialchars(
                                    $tahunAktif['tanggal_mulai'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) : ''; ?>"
                                max="<?= $tahunAktif ? htmlspecialchars(
                                    $tahunAktif['tanggal_selesai'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) : ''; ?>"
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
                                value="<?= htmlspecialchars(
                                    $tanggalSelesai,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>"
                                min="<?= $tahunAktif ? htmlspecialchars(
                                    $tahunAktif['tanggal_mulai'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) : ''; ?>"
                                max="<?= $tahunAktif ? htmlspecialchars(
                                    $tahunAktif['tanggal_selesai'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) : ''; ?>"
                                required
                                <?= !$tahunAktif ? 'disabled' : ''; ?>>

                        </div>

                    </div>

                    <div class="keterangan mt-2">
                        Tanggal penempatan harus berada dalam periode tahun ajaran aktif.
                    </div>

                </div>

                <!-- Tombol -->
                <div class="d-flex flex-wrap gap-2">

                    <button
                        type="submit"
                        class="btn btn-simpan"
                        <?= !$tahunAktif ? 'disabled' : ''; ?>>

                        Simpan Penempatan

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

<script>
// Membuka dropdown
function bukaDropdown(id) {

    const dropdown = document.getElementById(id);
    const menu = dropdown.querySelector('.dropdown-cari-menu');

    const sedangTerbuka = menu.classList.contains('aktif');

    // Tutup dropdown lainnya
    document.querySelectorAll('.dropdown-cari-menu').forEach(function(item) {
        item.classList.remove('aktif');
    });

    if (!sedangTerbuka) {

        menu.classList.add('aktif');

        const input = menu.querySelector('.dropdown-cari-input');

        input.value = '';

        filterDropdown(input);

        input.focus();
    }
}

// Pencarian di dalam dropdown
function filterDropdown(input) {

    const daftar = input.parentElement.querySelector('.dropdown-cari-daftar');

    const item = daftar.querySelectorAll('.dropdown-cari-item');

    let jumlah = 0;

    item.forEach(function(elemen) {

        const teks = elemen.textContent.toLowerCase();
        const kataCari = input.value.toLowerCase();

        const cocok = teks.includes(kataCari);

        elemen.classList.toggle('sembunyi', !cocok);

        if (cocok) {
            jumlah++;
        }
    });

    let kosong = daftar.querySelector('.dropdown-cari-kosong');

    if (jumlah === 0) {

        if (!kosong) {
            kosong = document.createElement('div');
            kosong.className = 'dropdown-cari-kosong';
            kosong.textContent = 'Data tidak ditemukan';

            daftar.appendChild(kosong);
        }

    } else if (kosong) {

        kosong.remove();

    }
}

// Memilih data dari dropdown
function pilihDropdown(id, elemen) {

    const dropdown = document.getElementById(id);

    const inputHidden = dropdown.querySelector('input[type="hidden"]');

    const label = dropdown.querySelector('.teks-pilihan');

    inputHidden.value = elemen.dataset.value;

    label.textContent = elemen.textContent.trim();

    dropdown.querySelector('.dropdown-cari-menu').classList.remove('aktif');
}

// Menutup dropdown ketika klik di luar
document.addEventListener('click', function(event) {

    document.querySelectorAll('.dropdown-cari').forEach(function(dropdown) {

        if (!dropdown.contains(event.target)) {

            dropdown.querySelector('.dropdown-cari-menu')
                .classList.remove('aktif');

        }

    });

});

// Menampilkan pilihan kembali jika validasi gagal
document.addEventListener('DOMContentLoaded', function() {

    const pilihan = [
        {
            id: 'dropdownSiswa',
            value: '<?= $siswaId; ?>',
            label: '-- Pilih Siswa --'
        },
        {
            id: 'dropdownKelas',
            value: '<?= $kelasId; ?>',
            label: '-- Pilih Kelas --'
        }
    ];

    pilihan.forEach(function(data) {

        const dropdown = document.getElementById(data.id);

        const item = dropdown.querySelector(
            '.dropdown-cari-item[data-value="' + data.value + '"]'
        );

        if (item) {

            dropdown.querySelector('.teks-pilihan').textContent =
                item.textContent.trim();

        }

    });

});

// Validasi pilihan sebelum dikirim
document.getElementById('formPenempatan').addEventListener('submit', function(event) {

    const siswa = document.getElementById('siswa_id').value;
    const kelas = document.getElementById('kelas_id').value;

    if (!siswa) {
        event.preventDefault();
        alert('Silakan pilih siswa terlebih dahulu.');
        bukaDropdown('dropdownSiswa');
        return;
    }

    if (!kelas) {
        event.preventDefault();
        alert('Silakan pilih kelas terlebih dahulu.');
        bukaDropdown('dropdownKelas');
        return;
    }

});

// Validasi tanggal
const tanggalMulai = document.getElementById('tanggal_mulai');
const tanggalSelesai = document.getElementById('tanggal_selesai');

if (tanggalMulai && tanggalSelesai) {

    tanggalMulai.addEventListener('change', function() {

        tanggalSelesai.min = this.value;

        if (
            tanggalSelesai.value !== '' &&
            tanggalSelesai.value < this.value
        ) {
            tanggalSelesai.value = this.value;
        }

    });

    tanggalSelesai.addEventListener('change', function() {

        tanggalMulai.max = this.value;

    });

}
</script>

</body>
</html>
