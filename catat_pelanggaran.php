
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

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

// Identitas guru
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

// Ambil semua siswa aktif beserta kelasnya
$querySiswa = mysqli_query($koneksi, "
    SELECT
        s.id,
        s.nis,
        s.nisn,
        s.nama,
        ks.kelas_id,
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

$daftarPelanggaran = [];

while ($p = mysqli_fetch_assoc($queryPelanggaran)) {
    $daftarPelanggaran[] = $p;
}

// Nilai pilihan saat form dikirim
$siswaPilihan = (int) ($_POST['siswa_id'] ?? 0);
$pelanggaranPilihan = (int) ($_POST['pelanggaran_id'] ?? 0);

// Periksa siswa yang dipilih
$siswaTerpilih = null;

foreach ($daftarSiswa as $siswa) {
    if ((int) $siswa['id'] === $siswaPilihan) {
        $siswaTerpilih = $siswa;
        break;
    }
}

// Periksa jenis pelanggaran
$pelanggaranTerpilih = null;

foreach ($daftarPelanggaran as $p) {
    if ((int) $p['id'] === $pelanggaranPilihan) {
        $pelanggaranTerpilih = $p;
        break;
    }
}

// Proses penyimpanan
if (isset($_POST['simpan'])) {
    $siswaId = (int) ($_POST['siswa_id'] ?? 0);
    $pelanggaranId = (int) ($_POST['pelanggaran_id'] ?? 0);
    $tanggal = $_POST['tanggal'] ?? '';
    $keterangan = trim($_POST['keterangan'] ?? '');

    if (
        $siswaId <= 0 ||
        $pelanggaranId <= 0 ||
        $tanggal == "" ||
        $keterangan == ""
    ) {
        $pesan = "Semua data wajib diisi.";
    } else {
        // Periksa siswa berdasarkan tahun ajaran aktif
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
            AND ks.tahun_ajaran_id = ?
            AND ks.status_aktif = 1
            AND s.status_aktif = 1
            AND k.status_aktif = 1
            LIMIT 1
        ");

        if (!$stmtSiswa) {
            $pesan = "Gagal memeriksa data siswa: " . mysqli_error($koneksi);
            $dataSiswa = null;
        } else {
            mysqli_stmt_bind_param(
                $stmtSiswa,
                "ii",
                $siswaId,
                $tahunId
            );

            mysqli_stmt_execute($stmtSiswa);
            $hasilSiswa = mysqli_stmt_get_result($stmtSiswa);
            $dataSiswa = mysqli_fetch_assoc($hasilSiswa);
            mysqli_stmt_close($stmtSiswa);
        }

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
                $pesan = "Siswa tidak ditemukan atau tidak terdaftar pada tahun ajaran aktif.";
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

// Data siswa untuk pencarian
$dataSiswaJSON = [];

foreach ($daftarSiswa as $siswa) {
    $dataSiswaJSON[] = [
        'id' => (int) $siswa['id'],
        'nis' => $siswa['nis'],
        'nisn' => $siswa['nisn'],
        'nama' => $siswa['nama'],
        'kelas_id' => (int) $siswa['kelas_id'],
        'nama_kelas' => $siswa['nama_kelas']
    ];
}

// Data jenis pelanggaran untuk pencarian
$dataPelanggaranJSON = [];

foreach ($daftarPelanggaran as $p) {
    $dataPelanggaranJSON[] = [
        'id' => (int) $p['id'],
        'nama' => $p['nama'],
        'poin' => (int) $p['poin']
    ];
}

// Ambil seluruh data pelanggaran tanpa memfilter status
$queryTerbaru = mysqli_query($koneksi, "
    SELECT
        id,
        nama_siswa,
        nama_kelas,
        nama_pelanggaran,
        tanggal,
        poin,
        nama_guru,
        status
    FROM t_pelanggaran_siswa
    ORDER BY tanggal DESC, id DESC
");

if (!$queryTerbaru) {
    die("Gagal mengambil data pelanggaran terbaru: " . mysqli_error($koneksi));
}

$daftarTerbaru = [];

while ($data = mysqli_fetch_assoc($queryTerbaru)) {
    $daftarTerbaru[] = $data;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Catat Pelanggaran</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .dropdown-siswa,
        .hasil-pencarian {
            position: relative;
        }

        .menu-siswa,
        .daftar-hasil {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            max-height: 260px;
            overflow-y: auto;
            z-index: 100;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .menu-siswa {
            padding: 10px;
        }

        .item-siswa {
            display: block;
            width: 100%;
            padding: 10px 12px;
            border: 0;
            border-bottom: 1px solid #eee;
            background: white;
            text-align: left;
        }

        .item-siswa:hover {
            background: #f0f5ff;
        }

        .info-tambahan {
            display: block;
            margin-top: 3px;
            font-size: 12px;
            color: #6c757d;
        }

        .tabel-terbaru th,
        .tabel-terbaru td {
            vertical-align: middle;
            white-space: nowrap;
        }
    </style>
</head>

<body class="bg-light">

<?php include "includes/sidebar.php"; ?>

<div style="margin-left:220px; padding:30px;">

    <div class="mb-4">
        <h3 class="fw-bold">Catat Pelanggaran</h3>
        <p class="text-muted">
            Form pencatatan dan daftar seluruh pelanggaran siswa.
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

    <!-- FORM CATAT PELANGGARAN -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">

            <form method="POST" id="formPelanggaran">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Tahun Ajaran</label>
                    <input
                        type="text"
                        class="form-control"
                        value="<?= e($tahun['nama']) ?>"
                        readonly
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Guru Pencatat</label>
                    <input
                        type="text"
                        class="form-control"
                        value="<?= e($namaGuru) ?>"
                        readonly
                    >
                </div>

                <hr>

                <h5 class="mb-3">Data Siswa</h5>

                <!-- PENCARIAN SISWA LANGSUNG -->
                <div class="mb-3 dropdown-siswa">
                    <label for="cari_siswa" class="form-label fw-semibold">
                        Cari Nama Siswa
                    </label>

                    <input
                        type="search"
                        id="cari_siswa"
                        class="form-control"
                        placeholder="Ketik nama siswa, NIS, atau NISN..."
                        autocomplete="off"
                        value="<?= $siswaTerpilih ? e($siswaTerpilih['nama']) : '' ?>"
                    >

                    <div class="menu-siswa" id="menuSiswa">
                        <div id="daftarSiswa"></div>
                    </div>

                    <input
                        type="hidden"
                        name="siswa_id"
                        id="siswa_id"
                        value="<?= $siswaTerpilih ? (int) $siswaTerpilih['id'] : '' ?>"
                    >

                    <small class="text-muted">
                        Ketik nama siswa untuk menampilkan hasil pencarian.
                    </small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Kelas Siswa</label>
                    <input
                        type="text"
                        id="kelas_siswa"
                        class="form-control"
                        value="<?= $siswaTerpilih ? e($siswaTerpilih['nama_kelas']) : '' ?>"
                        placeholder="Kelas akan terisi otomatis"
                        readonly
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Identitas Siswa</label>
                    <input
                        type="text"
                        id="identitas_siswa"
                        class="form-control"
                        value="<?=
                            $siswaTerpilih
                                ? e(
                                    'NIS: ' . $siswaTerpilih['nis'] .
                                    ' | NISN: ' . ($siswaTerpilih['nisn'] ?: '-')
                                )
                                : ''
                        ?>"
                        placeholder="Identitas siswa akan terisi otomatis"
                        readonly
                    >
                </div>

                <hr>

                <h5 class="mb-3">Detail Pelanggaran</h5>

                <!-- PENCARIAN JENIS PELANGGARAN -->
                <div class="mb-2 hasil-pencarian">
                    <label for="cari_pelanggaran" class="form-label fw-semibold">
                        Cari Jenis Pelanggaran
                    </label>

                    <input
                        type="search"
                        id="cari_pelanggaran"
                        class="form-control"
                        placeholder="Ketik nama jenis pelanggaran..."
                        autocomplete="off"
                        value="<?= $pelanggaranTerpilih ? e($pelanggaranTerpilih['nama']) : '' ?>"
                    >

                    <div id="daftar_pelanggaran" class="daftar-hasil"></div>

                    <small class="text-muted">
                        Cari dan pilih jenis pelanggaran.
                    </small>
                </div>

                <input
                    type="hidden"
                    name="pelanggaran_id"
                    id="pelanggaran_id"
                    value="<?= $pelanggaranTerpilih ? (int) $pelanggaranTerpilih['id'] : '' ?>"
                >

                <div class="mb-3">
                    <label for="poin_pelanggaran" class="form-label fw-semibold">
                        Poin Pelanggaran
                    </label>

                    <input
                        type="text"
                        id="poin_pelanggaran"
                        class="form-control"
                        value="<?= $pelanggaranTerpilih ? (int) $pelanggaranTerpilih['poin'] . ' Poin' : '' ?>"
                        placeholder="Poin akan terisi otomatis setelah memilih pelanggaran"
                        readonly
                    >
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

    <!-- DATA PELANGGARAN TERBARU -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            <div class="mb-3">
                <h5 class="fw-bold mb-1">Data Pelanggaran Terbaru</h5>
                <p class="text-muted mb-0">
                    Seluruh catatan pelanggaran siswa, termasuk yang sudah ditindak.
                </p>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover tabel-terbaru">
                    <thead class="table-light">
                        <tr>
                            <th>No.</th>
                            <th>Tanggal</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Jenis Pelanggaran</th>
                            <th>Poin</th>
                            <th>Guru Pencatat</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (count($daftarTerbaru) > 0): ?>

                            <?php $no = 1; ?>

                            <?php foreach ($daftarTerbaru as $data): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= date('d-m-Y', strtotime($data['tanggal'])) ?></td>
                                    <td><?= e($data['nama_siswa']) ?></td>
                                    <td><?= e($data['nama_kelas']) ?></td>
                                    <td><?= e($data['nama_pelanggaran']) ?></td>
                                    <td><?= (int) $data['poin'] ?></td>
                                    <td><?= e($data['nama_guru']) ?></td>
                                    <td>
                                        <?php
                                        $warnaStatus = 'secondary';

                                        if ($data['status'] == 'Belum ditangani') {
                                            $warnaStatus = 'danger';
                                        } elseif ($data['status'] == 'Diproses') {
                                            $warnaStatus = 'warning';
                                        } elseif ($data['status'] == 'Selesai') {
                                            $warnaStatus = 'success';
                                        }
                                        ?>

                                        <span class="badge text-bg-<?= $warnaStatus ?>">
                                            <?= e($data['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    Belum ada catatan pelanggaran siswa.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <small class="text-muted">
                Total <?= count($daftarTerbaru) ?> catatan pelanggaran.
            </small>

        </div>
    </div>

</div>

<script>
const daftarSiswa = <?= json_encode(
    $dataSiswaJSON,
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
) ?>;

const daftarPelanggaran = <?= json_encode(
    $dataPelanggaranJSON,
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
) ?>;

const cariSiswa = document.getElementById('cari_siswa');
const menuSiswa = document.getElementById('menuSiswa');
const daftarSiswaElement = document.getElementById('daftarSiswa');

const inputSiswaId = document.getElementById('siswa_id');
const inputKelasSiswa = document.getElementById('kelas_siswa');
const inputIdentitas = document.getElementById('identitas_siswa');

const cariPelanggaran = document.getElementById('cari_pelanggaran');
const hasilPelanggaran = document.getElementById('daftar_pelanggaran');
const inputPelanggaranId = document.getElementById('pelanggaran_id');
const inputPoinPelanggaran = document.getElementById('poin_pelanggaran');

const formPelanggaran = document.getElementById('formPelanggaran');

// ============================
// PENCARIAN SISWA
// ============================

function resetPilihanSiswa() {
    inputSiswaId.value = '';
    inputKelasSiswa.value = '';
    inputIdentitas.value = '';
}

function nilaiKemiripan(nama, keyword) {
    nama = nama.toLowerCase();
    keyword = keyword.toLowerCase();

    if (nama === keyword) return 1000;
    if (nama.startsWith(keyword)) return 500;
    if (nama.includes(keyword)) return 300;

    const kataNama = nama.split(/\s+/);
    const kataCari = keyword.split(/\s+/);

    let skor = 0;

    kataCari.forEach(function(kata) {
        if (!kata) return;

        kataNama.forEach(function(kataSiswa) {
            if (kataSiswa.startsWith(kata)) {
                skor += 50;
            } else if (kataSiswa.includes(kata)) {
                skor += 20;
            }
        });
    });

    // Kemiripan sederhana untuk salah ketik
    if (skor === 0 && keyword.length >= 3) {
        const jarak = jarakLevenshtein(nama, keyword);

        if (jarak <= 2) {
            skor = 10 - jarak;
        }
    }

    return skor;
}

function jarakLevenshtein(a, b) {
    const matriks = [];

    for (let i = 0; i <= b.length; i++) {
        matriks[i] = [i];
    }

    for (let j = 0; j <= a.length; j++) {
        matriks[0][j] = j;
    }

    for (let i = 1; i <= b.length; i++) {
        for (let j = 1; j <= a.length; j++) {
            const biaya = b.charAt(i - 1) === a.charAt(j - 1) ? 0 : 1;

            matriks[i][j] = Math.min(
                matriks[i - 1][j] + 1,
                matriks[i][j - 1] + 1,
                matriks[i - 1][j - 1] + biaya
            );
        }
    }

    return matriks[b.length][a.length];
}

function tampilkanSiswa() {
    const keyword = cariSiswa.value.trim().toLowerCase();

    daftarSiswaElement.innerHTML = '';

    if (keyword === '') {
        menuSiswa.style.display = 'none';
        return;
    }

    const hasil = daftarSiswa.map(function(siswa) {
        const skorNama = nilaiKemiripan(siswa.nama, keyword);

        const cocokNIS = String(siswa.nis).toLowerCase().includes(keyword);
        const cocokNISN = String(siswa.nisn || '').toLowerCase().includes(keyword);

        let skor = skorNama;

        if (cocokNIS || cocokNISN) {
            skor = Math.max(skor, 600);
        }

        return {
            siswa: siswa,
            skor: skor
        };
    }).filter(function(item) {
        return item.skor > 0;
    });

    hasil.sort(function(a, b) {
        return b.skor - a.skor ||
            a.siswa.nama.localeCompare(b.siswa.nama);
    });

    if (hasil.length === 0) {
        daftarSiswaElement.innerHTML =
            '<div class="p-3 text-muted">Nama siswa tidak ditemukan.</div>';

        menuSiswa.style.display = 'block';
        return;
    }

    hasil.forEach(function(item) {
        const siswa = item.siswa;

        const tombol = document.createElement('button');
        tombol.type = 'button';
        tombol.className = 'item-siswa';

        const nama = document.createElement('strong');
        nama.textContent = siswa.nama;

        const informasi = document.createElement('span');
        informasi.className = 'info-tambahan';
        informasi.textContent =
            'NIS: ' + siswa.nis +
            ' | NISN: ' + (siswa.nisn || '-') +
            ' | Kelas: ' + siswa.nama_kelas;

        tombol.appendChild(nama);
        tombol.appendChild(informasi);

        tombol.addEventListener('click', function() {
            pilihSiswa(siswa);
        });

        daftarSiswaElement.appendChild(tombol);
    });

    menuSiswa.style.display = 'block';
}

function pilihSiswa(siswa) {
    inputSiswaId.value = siswa.id;
    cariSiswa.value = siswa.nama;

    inputKelasSiswa.value = siswa.nama_kelas;

    inputIdentitas.value =
        'NIS: ' + siswa.nis +
        ' | NISN: ' + (siswa.nisn || '-');

    menuSiswa.style.display = 'none';
}

cariSiswa.addEventListener('input', function() {
    resetPilihanSiswa();
    tampilkanSiswa();
});

cariSiswa.addEventListener('focus', tampilkanSiswa);

// ============================
// PENCARIAN JENIS PELANGGARAN
// ============================

function tampilkanPelanggaran() {
    const keyword = cariPelanggaran.value.trim().toLowerCase();

    hasilPelanggaran.innerHTML = '';

    if (keyword === '') {
        hasilPelanggaran.style.display = 'none';
        return;
    }

    const hasil = daftarPelanggaran.filter(function(pelanggaran) {
        return String(pelanggaran.nama).toLowerCase().includes(keyword);
    });

    if (hasil.length === 0) {
        hasilPelanggaran.innerHTML =
            '<div class="p-3 text-muted">Jenis pelanggaran tidak ditemukan.</div>';

        hasilPelanggaran.style.display = 'block';
        return;
    }

    hasil.forEach(function(pelanggaran) {
        const tombol = document.createElement('button');

        tombol.type = 'button';
        tombol.className = 'item-siswa';

        const nama = document.createElement('strong');
        nama.textContent = pelanggaran.nama;

        const informasi = document.createElement('span');
        informasi.className = 'info-tambahan';
        informasi.textContent = pelanggaran.poin + ' Poin';

        tombol.appendChild(nama);
        tombol.appendChild(informasi);

        tombol.addEventListener('click', function() {
            pilihPelanggaran(pelanggaran);
        });

        hasilPelanggaran.appendChild(tombol);
    });

    hasilPelanggaran.style.display = 'block';
}

function pilihPelanggaran(pelanggaran) {
    inputPelanggaranId.value = pelanggaran.id;
    inputPoinPelanggaran.value = pelanggaran.poin + ' Poin';
    cariPelanggaran.value = pelanggaran.nama;

    hasilPelanggaran.innerHTML = '';
    hasilPelanggaran.style.display = 'none';
}

cariPelanggaran.addEventListener('input', function() {
    inputPelanggaranId.value = '';
    inputPoinPelanggaran.value = '';
    tampilkanPelanggaran();
});

cariPelanggaran.addEventListener('focus', tampilkanPelanggaran);

// ============================
// TUTUP HASIL PENCARIAN
// ============================

document.addEventListener('click', function(event) {
    if (
        !cariSiswa.contains(event.target) &&
        !menuSiswa.contains(event.target)
    ) {
        menuSiswa.style.display = 'none';
    }

    if (
        !cariPelanggaran.contains(event.target) &&
        !hasilPelanggaran.contains(event.target)
    ) {
        hasilPelanggaran.style.display = 'none';
    }
});

// ============================
// VALIDASI FORM
// ============================

formPelanggaran.addEventListener('submit', function(event) {
    if (inputSiswaId.value === '') {
        event.preventDefault();
        alert('Silakan cari dan pilih nama siswa terlebih dahulu.');
        cariSiswa.focus();
        return;
    }

    if (inputPelanggaranId.value === '') {
        event.preventDefault();
        alert('Silakan cari dan pilih jenis pelanggaran.');
        cariPelanggaran.focus();
        return;
    }
});

// ============================
// RESET FORM
// ============================

formPelanggaran.addEventListener('reset', function() {
    setTimeout(function() {
        resetPilihanSiswa();

        menuSiswa.style.display = 'none';
        daftarSiswaElement.innerHTML = '';

        hasilPelanggaran.innerHTML = '';
        hasilPelanggaran.style.display = 'none';
    }, 0);
});
</script>

</body>
</html>
