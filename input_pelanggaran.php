
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

// Identitas guru
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

// Tahun ajaran aktif saja
$queryTahun = mysqli_query($koneksi, "
    SELECT id, nama
    FROM t_tahun_ajaran
    WHERE status_aktif = 1
    ORDER BY tanggal_mulai DESC
    LIMIT 1
");

if (!$queryTahun) {
    die("Gagal mengambil tahun ajaran: " . mysqli_error($koneksi));
}

$tahun = mysqli_fetch_assoc($queryTahun);

if (!$tahun) {
    die("Belum ada tahun ajaran yang aktif.");
}

$tahunId = (int)$tahun['id'];

// Siswa pada tahun ajaran aktif
$stmtSiswa = mysqli_prepare($koneksi, "
    SELECT s.id, s.nis, s.nisn, s.nama,
           ks.kelas_id, k.nama AS nama_kelas
    FROM t_kelas_siswa ks
    JOIN t_siswa s ON ks.siswa_id = s.id
    JOIN t_kelas k ON ks.kelas_id = k.id
    WHERE ks.tahun_ajaran_id = ?
      AND ks.status_aktif = 1
      AND s.status_aktif = 1
      AND k.status_aktif = 1
    ORDER BY s.nama ASC
");

mysqli_stmt_bind_param($stmtSiswa, "i", $tahunId);
mysqli_stmt_execute($stmtSiswa);
$resultSiswa = mysqli_stmt_get_result($stmtSiswa);

$daftarSiswa = [];
while ($row = mysqli_fetch_assoc($resultSiswa)) {
    $daftarSiswa[] = $row;
}
mysqli_stmt_close($stmtSiswa);

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

// Pilihan form jika validasi gagal
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

// Proses simpan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {
    $siswaId = (int)($_POST['siswa_id'] ?? 0);
    $pelanggaranId = (int)($_POST['pelanggaran_id'] ?? 0);
    $tanggal = trim($_POST['tanggal'] ?? '');
    $keterangan = trim($_POST['keterangan'] ?? '');
    $isiTindakan = trim($_POST['tindakan'] ?? '');
    $statusPilihan = trim($_POST['status_tindakan'] ?? 'Belum Ditindak');

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
        // Pastikan siswa terdaftar pada tahun aktif
        $stmtCekSiswa = mysqli_prepare($koneksi, "
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
        mysqli_stmt_bind_param($stmtCekSiswa, "ii", $siswaId, $tahunId);
        mysqli_stmt_execute($stmtCekSiswa);
        $dataSiswa = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCekSiswa));
        mysqli_stmt_close($stmtCekSiswa);

        // Pastikan jenis pelanggaran aktif
        $stmtCekPelanggaran = mysqli_prepare($koneksi, "
            SELECT nama, poin, pelanggaran_kategori_id
            FROM t_pelanggaran
            WHERE id = ? AND status_aktif = 1
            LIMIT 1
        ");
        mysqli_stmt_bind_param($stmtCekPelanggaran, "i", $pelanggaranId);
        mysqli_stmt_execute($stmtCekPelanggaran);
        $dataPelanggaran = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCekPelanggaran));
        mysqli_stmt_close($stmtCekPelanggaran);

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

            if ($isiTindakan === '') {
                $tindakan = "Belum ada tindakan";
                $status = "Belum Ditindak";
            } else {
                $tindakan = $isiTindakan;
                $status = $statusPilihan;
            }

            $stmtSimpan = mysqli_prepare($koneksi, "
                INSERT INTO t_pelanggaran_siswa (
                    tahun_ajaran_id, siswa_id, nama_siswa,
                    kelas_id, nama_kelas, pelanggaran_id,
                    nama_pelanggaran, pelanggaran_kategori_id,
                    guru_id, nama_guru, tanggal, keterangan,
                    poin, tindakan, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            if (!$stmtSimpan) {
                $pesan = "Gagal menyiapkan penyimpanan: " . mysqli_error($koneksi);
            } else {
                mysqli_stmt_bind_param(
                    $stmtSimpan,
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

                if (mysqli_stmt_execute($stmtSimpan)) {
                    mysqli_stmt_close($stmtSimpan);
                    header("Location: catatan_pelanggaran.php?berhasil=1");
                    exit;
                }

                $pesan = "Gagal menyimpan pelanggaran: " . mysqli_stmt_error($stmtSimpan);
                mysqli_stmt_close($stmtSimpan);
            }
        }
    }
}

// Data untuk dropdown pencarian
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
<title>Input Pelanggaran | sipenaMU</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

<style>
body { background:#F5F6F7; color:#374151; }
.konten-utama { margin-left:220px; min-height:100vh; padding:28px; }
.judul-halaman { color:#34495E; font-weight:700; }
.deskripsi-halaman { color:#7B8794; }
.kartu { border:1px solid #E9EEF2; border-radius:12px; background:#fff; }
.kartu .card-body { padding:25px; }
.judul-bagian { color:#34495E; font-size:1.05rem; font-weight:700; }
.form-label { color:#465568; font-weight:600; }
.form-control, .form-select { border-color:#DCE3E8; border-radius:8px; min-height:42px; }
.form-control:focus, .form-select:focus { border-color:#8FA5B8; box-shadow:0 0 0 .2rem rgba(96,125,148,.13); }
textarea.form-control { min-height:unset; }
.btn-primary { background:#607D94; border-color:#607D94; }
.btn-primary:hover { background:#526D83; border-color:#526D83; }
.btn-secondary { background:#E9EEF2; border-color:#E9EEF2; color:#526D83; }
.btn-secondary:hover { background:#DCE5EB; border-color:#DCE5EB; color:#34495E; }
.info-tahun { background:#F3F6F8; border:1px solid #E5EBEF; color:#526D83; border-radius:9px; padding:12px 15px; }
.pencarian { position:relative; }
.area-hasil { display:none; position:absolute; top:100%; left:0; width:100%; max-height:260px; overflow-y:auto; z-index:1050; background:#fff; border:1px solid #DCE3E8; border-radius:9px; box-shadow:0 8px 22px rgba(40,55,70,.12); }
.item-hasil { display:block; width:100%; padding:11px 14px; border:0; border-bottom:1px solid #EDF0F2; background:#fff; color:#374151; text-align:left; }
.item-hasil:hover, .item-hasil:focus { background:#F3F6F8; outline:none; }
.item-hasil strong { display:block; color:#34495E; font-size:14px; }
.item-hasil .info { display:block; margin-top:3px; color:#7B8794; font-size:12px; }
.pesan-kosong { padding:13px; color:#7B8794; font-size:14px; }
.pemisah-bagian { border-color:#E9EEF2; opacity:1; margin:24px 0; }
@media(max-width:768px) {
    .konten-utama { margin-left:0; padding:22px 15px; }
    .kartu .card-body { padding:18px; }
}
</style>
</head>
<body>

<?php include "includes/sidebar.php"; ?>
<?php include "includes/navbar.php"; ?>

<main class="konten-utama">
    <div class="mb-4">
        <h3 class="judul-halaman mb-1">Input Pelanggaran</h3>
        <p class="deskripsi-halaman mb-0">Catat pelanggaran siswa dan berikan tindakan pembinaan jika diperlukan.</p>
    </div>

    <?php if (isset($_GET['berhasil'])): ?>
        <div class="alert alert-success border-0 shadow-sm">Pelanggaran berhasil disimpan.</div>
    <?php endif; ?>

    <?php if ($pesan !== ''): ?>
        <div class="alert alert-danger border-0 shadow-sm"><?= e($pesan) ?></div>
    <?php endif; ?>

    <div class="card kartu shadow-sm">
        <div class="card-body">
            <div class="d-flex align-items-center gap-2 mb-4">
                <i class="bi bi-clipboard2-plus fs-4" style="color:#607D94"></i>
                <div>
                    <h5 class="judul-bagian mb-0">Formulir Pelanggaran Siswa</h5>
                    <small class="text-secondary">Lengkapi data berikut dengan benar.</small>
                </div>
            </div>

            <form method="POST" id="formPelanggaran">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Tahun Ajaran Aktif</label>
                        <div class="info-tahun">
                            <i class="bi bi-calendar-check me-2"></i><?= e($tahun['nama']) ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Guru Pencatat</label>
                        <input type="text" class="form-control" value="<?= e($namaGuru) ?>" readonly>
                    </div>
                </div>

                <hr class="pemisah-bagian">
                <h6 class="judul-bagian mb-3"><i class="bi bi-person me-2"></i>Data Siswa</h6>

                <div class="row g-3">
                    <div class="col-12">
                        <label for="cari_siswa" class="form-label">Cari Nama Siswa</label>
                        <div class="pencarian">
                            <input type="search" id="cari_siswa" class="form-control"
                                placeholder="Ketik nama, NIS, NISN, atau kelas siswa..."
                                autocomplete="off"
                                value="<?= $siswaTerpilih ? e($siswaTerpilih['nama']) : '' ?>">
                            <div class="area-hasil" id="hasil_siswa"></div>
                        </div>
                        <input type="hidden" name="siswa_id" id="siswa_id"
                            value="<?= $siswaTerpilih ? (int)$siswaTerpilih['id'] : '' ?>">
                        <small class="text-secondary">Ketik untuk mencari, lalu pilih siswa dari daftar.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Kelas Siswa</label>
                        <input type="text" id="kelas_siswa" class="form-control" readonly
                            value="<?= $siswaTerpilih ? e($siswaTerpilih['nama_kelas']) : '' ?>"
                            placeholder="Terisi otomatis setelah siswa dipilih">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Identitas Siswa</label>
                        <input type="text" id="identitas_siswa" class="form-control" readonly
                            value="<?= $siswaTerpilih ? e('NIS: '.$siswaTerpilih['nis'].' | NISN: '.($siswaTerpilih['nisn'] ?: '-')) : '' ?>"
                            placeholder="Terisi otomatis setelah siswa dipilih">
                    </div>
                </div>

                <hr class="pemisah-bagian">
                <h6 class="judul-bagian mb-3"><i class="bi bi-exclamation-circle me-2"></i>Detail Pelanggaran</h6>

                <div class="row g-3">
                    <div class="col-12">
                        <label for="cari_pelanggaran" class="form-label">Cari Jenis Pelanggaran</label>
                        <div class="pencarian">
                            <input type="search" id="cari_pelanggaran" class="form-control"
                                placeholder="Ketik nama jenis pelanggaran..."
                                autocomplete="off"
                                value="<?= $pelanggaranTerpilih ? e($pelanggaranTerpilih['nama']) : '' ?>">
                            <div class="area-hasil" id="hasil_pelanggaran"></div>
                        </div>
                        <input type="hidden" name="pelanggaran_id" id="pelanggaran_id"
                            value="<?= $pelanggaranTerpilih ? (int)$pelanggaranTerpilih['id'] : '' ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Poin Pelanggaran</label>
                        <input type="text" id="poin_pelanggaran" class="form-control" readonly
                            value="<?= $pelanggaranTerpilih ? (int)$pelanggaranTerpilih['poin'].' Poin' : '' ?>"
                            placeholder="Terisi otomatis setelah jenis dipilih">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Pelanggaran</label>
                        <input type="date" name="tanggal" class="form-control"
                            max="<?= date('Y-m-d') ?>"
                            value="<?= e($_POST['tanggal'] ?? date('Y-m-d')) ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Keterangan Kejadian</label>
                        <textarea name="keterangan" class="form-control" rows="4"
                            placeholder="Jelaskan kejadian pelanggaran siswa..." required><?= e($_POST['keterangan'] ?? '') ?></textarea>
                    </div>
                </div>

                <hr class="pemisah-bagian">
                <h6 class="judul-bagian mb-1"><i class="bi bi-chat-square-text me-2"></i>Tindakan Pelanggaran</h6>
                <p class="text-secondary small mb-3">Bagian ini opsional. Jika belum ada tindakan, biarkan kosong.</p>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Catatan Tindakan</label>
                        <textarea name="tindakan" class="form-control" rows="3"
                            placeholder="Contoh: Siswa diberikan teguran dan pembinaan..."><?= e($_POST['tindakan'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status Penanganan</label>
                        <select name="status_tindakan" class="form-select">
                            <option value="Belum Ditindak" <?= ($_POST['status_tindakan'] ?? 'Belum Ditindak') === 'Belum Ditindak' ? 'selected' : '' ?>>Belum Ditindak</option>
                            <option value="Diproses" <?= ($_POST['status_tindakan'] ?? '') === 'Diproses' ? 'selected' : '' ?>>Diproses</option>
                            <option value="Selesai" <?= ($_POST['status_tindakan'] ?? '') === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                        </select>
                        <small class="text-secondary">Jika catatan tindakan kosong, status otomatis menjadi Belum Ditindak.</small>
                    </div>
                </div>

                <hr class="pemisah-bagian">
                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" name="simpan" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Simpan Pelanggaran
                    </button>
                    <button type="reset" class="btn btn-secondary px-4">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </button>
                </div>
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

function tampilkanHasil(container, data, pilih, informasi) {
    container.innerHTML = '';

    if (!data.length) {
        const kosong = document.createElement('div');
        kosong.className = 'pesan-kosong';
        kosong.textContent = 'Data tidak ditemukan.';
        container.appendChild(kosong);
        container.style.display = 'block';
        return;
    }

    data.slice(0, 60).forEach(item => {
        const tombol = document.createElement('button');
        tombol.type = 'button';
        tombol.className = 'item-hasil';

        const nama = document.createElement('strong');
        nama.textContent = item.nama;

        const info = document.createElement('span');
        info.className = 'info';
        info.textContent = informasi(item);

        tombol.append(nama, info);
        tombol.addEventListener('click', () => {
            pilih(item);
            container.style.display = 'none';
        });
        container.appendChild(tombol);
    });

    container.style.display = 'block';
}

function cariDataSiswa() {
    const kata = cariSiswa.value.trim().toLowerCase();
    const hasil = siswa.filter(item =>
        String(item.nama || '').toLowerCase().includes(kata) ||
        String(item.nis || '').toLowerCase().includes(kata) ||
        String(item.nisn || '').toLowerCase().includes(kata) ||
        String(item.nama_kelas || '').toLowerCase().includes(kata)
    );

    tampilkanHasil(hasilSiswa, hasil, item => {
        siswaId.value = item.id;
        cariSiswa.value = item.nama;
        kelasSiswa.value = item.nama_kelas;
        identitasSiswa.value = 'NIS: ' + item.nis + ' | NISN: ' + (item.nisn || '-');
    }, item => 'NIS: ' + item.nis + ' | NISN: ' + (item.nisn || '-') + ' | Kelas: ' + item.nama_kelas);
}

function cariDataPelanggaran() {
    const kata = cariPelanggaran.value.trim().toLowerCase();
    const hasil = pelanggaran.filter(item =>
        String(item.nama || '').toLowerCase().includes(kata)
    );

    tampilkanHasil(hasilPelanggaran, hasil, item => {
        pelanggaranId.value = item.id;
        cariPelanggaran.value = item.nama;
        poinPelanggaran.value = item.poin + ' Poin';
    }, item => item.poin + ' Poin');
}

cariSiswa.addEventListener('focus', cariDataSiswa);
cariSiswa.addEventListener('input', () => {
    siswaId.value = '';
    kelasSiswa.value = '';
    identitasSiswa.value = '';
    cariDataSiswa();
});

cariPelanggaran.addEventListener('focus', cariDataPelanggaran);
cariPelanggaran.addEventListener('input', () => {
    pelanggaranId.value = '';
    poinPelanggaran.value = '';
    cariDataPelanggaran();
});

document.addEventListener('click', event => {
    if (!cariSiswa.contains(event.target) && !hasilSiswa.contains(event.target)) {
        hasilSiswa.style.display = 'none';
    }
    if (!cariPelanggaran.contains(event.target) && !hasilPelanggaran.contains(event.target)) {
        hasilPelanggaran.style.display = 'none';
    }
});

document.getElementById('formPelanggaran').addEventListener('submit', event => {
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

document.getElementById('formPelanggaran').addEventListener('reset', () => {
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
