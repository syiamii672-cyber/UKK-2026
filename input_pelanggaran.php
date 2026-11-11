
<?php
include "includes/cek_session.php";
include "config/koneksi.php";

if (strtolower(trim($_SESSION['role'] ?? '')) !== 'guru') {
    header("Location: dashboard.php");
    exit;
}

$menuAktif = "input_pelanggaran";
$idUser = (int)($_SESSION['id'] ?? 0);
$pesan = '';

function e($nilai) {
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

// Identitas guru login
$stmtGuru = mysqli_prepare($koneksi, "
    SELECT id, nama
    FROM t_guru
    WHERE user_id = ? AND status_aktif = 1
    LIMIT 1
");
mysqli_stmt_bind_param($stmtGuru, "i", $idUser);
mysqli_stmt_execute($stmtGuru);
$guru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtGuru));
mysqli_stmt_close($stmtGuru);

if (!$guru) {
    die("Data guru tidak ditemukan atau akun tidak aktif.");
}

$guruId = (int)$guru['id'];
$namaGuru = $guru['nama'];

$queryTahun = mysqli_query($koneksi, "
    SELECT id, nama
    FROM t_tahun_ajaran
    ORDER BY tanggal_mulai DESC
");

if (!$queryTahun) {
    die("Gagal mengambil daftar tahun ajaran: " . mysqli_error($koneksi));
}

$tahun = mysqli_fetch_assoc($queryTahun);

if (!$tahun) {
    die("Belum ada tahun ajaran yang aktif.");
}

$tahunId = (int)$tahun['id'];

// Daftar siswa tahun ajaran aktif
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
while ($row = mysqli_fetch_assoc($querySiswa)) {
    $daftarSiswa[] = $row;
}

// Jenis pelanggaran aktif
$queryPelanggaran = mysqli_query($koneksi, "
    SELECT id, pelanggaran_kategori_id, nama, poin
    FROM t_pelanggaran
    WHERE status_aktif = 1
    ORDER BY nama ASC
");

if (!$queryPelanggaran) {
    die("Gagal mengambil jenis pelanggaran: " . mysqli_error($koneksi));
}

$daftarPelanggaran = [];
while ($row = mysqli_fetch_assoc($queryPelanggaran)) {
    $daftarPelanggaran[] = $row;
}

// Mempertahankan pilihan form jika terjadi kesalahan
$siswaPilihan = (int)($_POST['siswa_id'] ?? 0);
$pelanggaranPilihan = (int)($_POST['pelanggaran_id'] ?? 0);

$siswaTerpilih = null;
$pelanggaranTerpilih = null;

foreach ($daftarSiswa as $row) {
    if ((int)$row['id'] === $siswaPilihan) {
        $siswaTerpilih = $row;
        break;
    }
}

foreach ($daftarPelanggaran as $row) {
    if ((int)$row['id'] === $pelanggaranPilihan) {
        $pelanggaranTerpilih = $row;
        break;
    }
}

// Proses simpan pelanggaran baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {

    $siswaId = (int)($_POST['siswa_id'] ?? 0);
    $pelanggaranId = (int)($_POST['pelanggaran_id'] ?? 0);
    $tanggal = trim($_POST['tanggal'] ?? '');
    $keterangan = trim($_POST['keterangan'] ?? '');

    // Tindakan bersifat opsional
    $isiTindakan = trim($_POST['tindakan'] ?? '');
    $statusPilihan = trim($_POST['status_tindakan'] ?? 'Diproses');

    $statusDiizinkan = ['Belum Ditindak', 'Diproses', 'Selesai'];

    if ($siswaId <= 0 || $pelanggaranId <= 0 || $tanggal === '' || $keterangan === '') {
        $pesan = "Data siswa, jenis pelanggaran, tanggal, dan keterangan wajib diisi.";
    } elseif (
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal) ||
        !checkdate(
            (int)substr($tanggal, 5, 2),
            (int)substr($tanggal, 8, 2),
            (int)substr($tanggal, 0, 4)
        ) ||
        $tanggal > date('Y-m-d')
    ) {
        $pesan = "Tanggal pelanggaran tidak valid.";
    } elseif (!in_array($statusPilihan, $statusDiizinkan, true)) {
        $pesan = "Status tindakan tidak valid.";
    } else {

        // Memastikan siswa masih terdaftar di tahun ajaran aktif
        $stmtSiswa = mysqli_prepare($koneksi, "
            SELECT s.nama, ks.kelas_id, k.nama AS nama_kelas
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

        mysqli_stmt_bind_param($stmtSiswa, "ii", $siswaId, $tahunId);
        mysqli_stmt_execute($stmtSiswa);
        $dataSiswa = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtSiswa));
        mysqli_stmt_close($stmtSiswa);

        // Memastikan jenis pelanggaran masih aktif
        $stmtJenis = mysqli_prepare($koneksi, "
            SELECT nama, poin, pelanggaran_kategori_id
            FROM t_pelanggaran
            WHERE id = ? AND status_aktif = 1
            LIMIT 1
        ");

        mysqli_stmt_bind_param($stmtJenis, "i", $pelanggaranId);
        mysqli_stmt_execute($stmtJenis);
        $dataPelanggaran = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtJenis));
        mysqli_stmt_close($stmtJenis);

        if (!$dataSiswa) {
            $pesan = "Siswa tidak ditemukan atau tidak terdaftar pada tahun ajaran aktif.";
        } elseif (!$dataPelanggaran) {
            $pesan = "Jenis pelanggaran tidak ditemukan atau sudah tidak aktif.";
        } elseif ($isiTindakan !== '' && $statusPilihan === 'Belum Ditindak') {
            $pesan = "Jika tindakan sudah diisi, pilih status Diproses atau Selesai.";
        } else {

            $namaSiswa = $dataSiswa['nama'];
            $kelasId = (int)$dataSiswa['kelas_id'];
            $namaKelas = $dataSiswa['nama_kelas'];

            $namaPelanggaran = $dataPelanggaran['nama'];
            $kategoriId = (int)$dataPelanggaran['pelanggaran_kategori_id'];
            $poin = (int)$dataPelanggaran['poin'];

            // Jika tindakan kosong, gunakan status awal
            if ($isiTindakan === '') {
                $tindakan = "Belum ada tindakan";
                $status = "Belum Ditindak";
            } else {
                $tindakan = $isiTindakan;
                $status = $statusPilihan;
            }

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
                    header("Location: catatan_pelanggaran.php?berhasil=1");
                    exit;
                }

                $pesan = "Gagal menyimpan pelanggaran: " . mysqli_stmt_error($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Data untuk pencarian siswa
$dataSiswaJSON = [];

foreach ($daftarSiswa as $row) {
    $dataSiswaJSON[] = [
        'id' => (int)$row['id'],
        'nis' => $row['nis'],
        'nisn' => $row['nisn'],
        'nama' => $row['nama'],
        'nama_kelas' => $row['nama_kelas']
    ];
}

// Data untuk pencarian jenis pelanggaran
$dataPelanggaranJSON = [];

foreach ($daftarPelanggaran as $row) {
    $dataPelanggaranJSON[] = [
        'id' => (int)$row['id'],
        'nama' => $row['nama'],
        'poin' => (int)$row['poin']
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Input Pelanggaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f5f7fb;
        }

        .konten {
            margin-left: 220px;
            padding: 30px;
            min-height: 100vh;
        }

        .kartu {
            border: 0;
            border-radius: 12px;
        }

        .pencarian {
            position: relative;
        }

        .area-hasil {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            max-height: 250px;
            overflow-y: auto;
            z-index: 20;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 4px 12px #00000012;
        }

        .item-hasil {
            display: block;
            width: 100%;
            padding: 10px 12px;
            border: 0;
            border-bottom: 1px solid #eee;
            background: white;
            text-align: left;
        }

        .item-hasil:hover {
            background: #f0f5ff;
        }

        .info {
            display: block;
            color: #6c757d;
            font-size: 12px;
            margin-top: 3px;
        }

        @media(max-width: 768px) {
            .konten {
                margin-left: 0;
                padding: 18px;
            }
        }
    </style>
</head>

<body>

<?php include "includes/sidebar.php"; ?>

<main class="konten">

    <div class="mb-4">
        <h3 class="fw-bold">Input Pelanggaran</h3>
        <p class="text-muted mb-0">
            Formulir untuk mencatat pelanggaran siswa dan memberikan tindakan jika diperlukan.
        </p>
    </div>

    <?php if (isset($_GET['berhasil'])): ?>
        <div class="alert alert-success">
            Pelanggaran berhasil disimpan.
        </div>
    <?php endif; ?>

    <?php if ($pesan !== ''): ?>
        <div class="alert alert-danger">
            <?= e($pesan) ?>
        </div>
    <?php endif; ?>

    <div class="card kartu shadow-sm">
        <div class="card-body p-4">

            <h5 class="fw-bold mb-3">Formulir Pelanggaran Siswa</h5>

            <form method="POST" id="formPelanggaran">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Tahun Ajaran</label>
                    <input class="form-control" value="<?= e($tahun['nama']) ?>" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Guru Pencatat</label>
                    <input class="form-control" value="<?= e($namaGuru) ?>" readonly>
                </div>

                <hr>
                <h5 class="mb-3">Data Siswa</h5>

                <div class="mb-3 pencarian">
                    <label for="cari_siswa" class="form-label fw-semibold">Cari Nama Siswa</label>

                    <input type="search"
                           id="cari_siswa"
                           class="form-control"
                           placeholder="Ketik nama siswa, NIS, atau NISN..."
                           autocomplete="off"
                           value="<?= $siswaTerpilih ? e($siswaTerpilih['nama']) : '' ?>">

                    <div class="area-hasil" id="hasil_siswa"></div>

                    <input type="hidden"
                           name="siswa_id"
                           id="siswa_id"
                           value="<?= $siswaTerpilih ? (int)$siswaTerpilih['id'] : '' ?>">

                    <small class="text-muted">Pilih siswa dari hasil pencarian.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Kelas Siswa</label>
                    <input type="text"
                           id="kelas_siswa"
                           class="form-control"
                           readonly
                           value="<?= $siswaTerpilih ? e($siswaTerpilih['nama_kelas']) : '' ?>"
                           placeholder="Kelas terisi otomatis">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Identitas Siswa</label>
                    <input type="text"
                           id="identitas_siswa"
                           class="form-control"
                           readonly
                           value="<?= $siswaTerpilih ? e('NIS: '.$siswaTerpilih['nis'].' | NISN: '.($siswaTerpilih['nisn'] ?: '-')) : '' ?>"
                           placeholder="Identitas terisi otomatis">
                </div>

                <hr>
                <h5 class="mb-3">Detail Pelanggaran</h5>

                <div class="mb-3 pencarian">
                    <label for="cari_pelanggaran" class="form-label fw-semibold">
                        Cari Jenis Pelanggaran
                    </label>

                    <input type="search"
                           id="cari_pelanggaran"
                           class="form-control"
                           placeholder="Ketik nama jenis pelanggaran..."
                           autocomplete="off"
                           value="<?= $pelanggaranTerpilih ? e($pelanggaranTerpilih['nama']) : '' ?>">

                    <div class="area-hasil" id="hasil_pelanggaran"></div>

                    <input type="hidden"
                           name="pelanggaran_id"
                           id="pelanggaran_id"
                           value="<?= $pelanggaranTerpilih ? (int)$pelanggaranTerpilih['id'] : '' ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Poin Pelanggaran</label>
                    <input type="text"
                           id="poin_pelanggaran"
                           class="form-control"
                           readonly
                           value="<?= $pelanggaranTerpilih ? (int)$pelanggaranTerpilih['poin'].' Poin' : '' ?>"
                           placeholder="Poin terisi otomatis">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Tanggal Pelanggaran</label>
                    <input type="date"
                           name="tanggal"
                           class="form-control"
                           max="<?= date('Y-m-d') ?>"
                           value="<?= e($_POST['tanggal'] ?? date('Y-m-d')) ?>"
                           required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Keterangan Kejadian</label>
                    <textarea name="keterangan"
                              class="form-control"
                              rows="4"
                              placeholder="Jelaskan kejadian pelanggaran siswa..."
                              required><?= e($_POST['keterangan'] ?? '') ?></textarea>
                </div>

                <hr>

                <div class="mb-3">
                    <h5 class="fw-bold mb-1">Tindakan Pelanggaran (Opsional)</h5>
                    <small class="text-muted">
                        Guru dapat langsung mengisi tindakan saat mencatat pelanggaran.
                        Jika belum ada tindakan, bagian ini boleh dikosongkan.
                    </small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Catatan Tindakan</label>
                    <textarea name="tindakan"
                              class="form-control"
                              rows="3"
                              placeholder="Contoh: Siswa diberikan teguran dan pembinaan..."><?= e($_POST['tindakan'] ?? '') ?></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Status Penanganan</label>
                    <select name="status_tindakan" class="form-select">
                        <option value="Belum Ditindak"
                            <?= ($_POST['status_tindakan'] ?? '') === 'Belum Ditindak' ? 'selected' : '' ?>>
                            Belum Ditindak
                        </option>
                        <option value="Diproses"
                            <?= ($_POST['status_tindakan'] ?? 'Diproses') === 'Diproses' ? 'selected' : '' ?>>
                            Diproses
                        </option>
                        <option value="Selesai"
                            <?= ($_POST['status_tindakan'] ?? '') === 'Selesai' ? 'selected' : '' ?>>
                            Selesai
                        </option>
                    </select>
                    <small class="text-muted">
                        Jika catatan tindakan dikosongkan, status otomatis menjadi Belum Ditindak.
                    </small>
                </div>

                <button type="submit" name="simpan" class="btn btn-primary">
                    Simpan Pelanggaran
                </button>

                <button type="reset" class="btn btn-secondary">
                    Reset
                </button>

            </form>
        </div>
    </div>

</main>

<script>
const siswa = <?= json_encode($dataSiswaJSON, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const pelanggaran = <?= json_encode($dataPelanggaranJSON, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

const cariSiswa = document.getElementById('cari_siswa');
const hasilSiswa = document.getElementById('hasil_siswa');
const siswaId = document.getElementById('siswa_id');
const kelasSiswa = document.getElementById('kelas_siswa');
const identitasSiswa = document.getElementById('identitas_siswa');

const cariPelanggaran = document.getElementById('cari_pelanggaran');
const hasilPelanggaran = document.getElementById('hasil_pelanggaran');
const pelanggaranId = document.getElementById('pelanggaran_id');
const poinPelanggaran = document.getElementById('poin_pelanggaran');

function buatHasil(container, data, pilih, informasi) {
    container.innerHTML = '';

    if (!data.length) {
        container.textContent = 'Data tidak ditemukan.';
    } else {
        data.slice(0, 30).forEach(item => {
            const tombol = document.createElement('button');
            tombol.type = 'button';
            tombol.className = 'item-hasil';

            const nama = document.createElement('strong');
            nama.textContent = item.nama;

            const info = document.createElement('span');
            info.className = 'info';
            info.textContent = informasi(item);

            tombol.append(nama, info);
            tombol.addEventListener('click', () => pilih(item));
            container.appendChild(tombol);
        });
    }

    container.style.display = 'block';
}

// Pencarian siswa
cariSiswa.addEventListener('input', function() {
    siswaId.value = '';
    kelasSiswa.value = '';
    identitasSiswa.value = '';

    const kata = this.value.trim().toLowerCase();

    if (!kata) {
        hasilSiswa.style.display = 'none';
        return;
    }

    const cocok = siswa.filter(item =>
        String(item.nama).toLowerCase().includes(kata) ||
        String(item.nis).toLowerCase().includes(kata) ||
        String(item.nisn || '').toLowerCase().includes(kata)
    );

    buatHasil(hasilSiswa, cocok, item => {
        siswaId.value = item.id;
        cariSiswa.value = item.nama;
        kelasSiswa.value = item.nama_kelas;
        identitasSiswa.value = 'NIS: ' + item.nis + ' | NISN: ' + (item.nisn || '-');
        hasilSiswa.style.display = 'none';
    }, item => 'NIS: ' + item.nis + ' | NISN: ' + (item.nisn || '-') + ' | Kelas: ' + item.nama_kelas);
});

// Pencarian jenis pelanggaran
cariPelanggaran.addEventListener('input', function() {
    pelanggaranId.value = '';
    poinPelanggaran.value = '';

    const kata = this.value.trim().toLowerCase();

    if (!kata) {
        hasilPelanggaran.style.display = 'none';
        return;
    }

    const cocok = pelanggaran.filter(item =>
        item.nama.toLowerCase().includes(kata)
    );

    buatHasil(hasilPelanggaran, cocok, item => {
        pelanggaranId.value = item.id;
        cariPelanggaran.value = item.nama;
        poinPelanggaran.value = item.poin + ' Poin';
        hasilPelanggaran.style.display = 'none';
    }, item => item.poin + ' Poin');
});

// Menutup hasil pencarian jika klik di luar
document.addEventListener('click', function(event) {
    if (!cariSiswa.contains(event.target) && !hasilSiswa.contains(event.target)) {
        hasilSiswa.style.display = 'none';
    }

    if (!cariPelanggaran.contains(event.target) && !hasilPelanggaran.contains(event.target)) {
        hasilPelanggaran.style.display = 'none';
    }
});

// Validasi form
document.getElementById('formPelanggaran').addEventListener('submit', function(event) {
    if (!siswaId.value) {
        event.preventDefault();
        alert('Silakan cari dan pilih nama siswa terlebih dahulu.');
        cariSiswa.focus();
        return;
    }

    if (!pelanggaranId.value) {
        event.preventDefault();
        alert('Silakan cari dan pilih jenis pelanggaran.');
        cariPelanggaran.focus();
    }
});

// Reset form
document.getElementById('formPelanggaran').addEventListener('reset', function() {
    setTimeout(() => {
        siswaId.value = '';
        kelasSiswa.value = '';
        identitasSiswa.value = '';
        pelanggaranId.value = '';
        poinPelanggaran.value = '';
        hasilSiswa.style.display = 'none';
        hasilPelanggaran.style.display = 'none';
    }, 0);
});
</script>

</body>
</html>
