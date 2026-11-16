
<?php
require_once 'config/koneksi.php';
require_once 'includes/cek_session.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =========================================
   CEK HAK AKSES
========================================= */
$role = strtolower(trim($_SESSION['role'] ?? ''));

if (!in_array($role, ['admin', 'guru'], true)) {
    header('Location: login.php');
    exit;
}

/* =========================================
   AMBIL PARAMETER
========================================= */
$siswaId = (int) ($_GET['siswa_id'] ?? 0);
$kelasId = (int) ($_GET['kelas_id'] ?? 0);
$tahunId = (int) ($_GET['tahun_ajaran_id'] ?? 0);

if ($siswaId <= 0 || $kelasId <= 0 || $tahunId <= 0) {
    die('Parameter data siswa tidak lengkap.');
}

/* =========================================
   AMBIL DATA SISWA DAN KELAS
========================================= */
$querySiswa = mysqli_query($koneksi, "
    SELECT
        s.id AS siswa_id,
        s.nis,
        s.nama,
        k.id AS kelas_id,
        k.nama AS nama_kelas
    FROM t_siswa s
    INNER JOIN t_kelas_siswa ks
        ON ks.siswa_id = s.id
    INNER JOIN t_kelas k
        ON k.id = ks.kelas_id
    WHERE s.id = $siswaId
      AND k.id = $kelasId
      AND ks.tahun_ajaran_id = $tahunId
      AND ks.status_aktif = 1
    LIMIT 1
");

if (!$querySiswa) {
    die('Gagal mengambil data siswa: ' . htmlspecialchars(mysqli_error($koneksi)));
}

if (mysqli_num_rows($querySiswa) === 0) {
    die('Data siswa tidak ditemukan atau penempatannya tidak aktif.');
}

$siswa = mysqli_fetch_assoc($querySiswa);

/* =========================================
   AMBIL NAMA TAHUN AJARAN
========================================= */
$queryTahun = mysqli_query($koneksi, "
    SELECT *
    FROM t_tahun_ajaran
    WHERE id = $tahunId
    LIMIT 1
");

$tahunAjaran = '-';

if ($queryTahun && mysqli_num_rows($queryTahun) > 0) {
    $dataTahun = mysqli_fetch_assoc($queryTahun);

    if (!empty($dataTahun['nama_tahun_ajaran'])) {
        $tahunAjaran = $dataTahun['nama_tahun_ajaran'];
    } elseif (!empty($dataTahun['tahun_ajaran'])) {
        $tahunAjaran = $dataTahun['tahun_ajaran'];
    } elseif (!empty($dataTahun['nama'])) {
        $tahunAjaran = $dataTahun['nama'];
    }
}

/* =========================================
   HITUNG TOTAL POIN
========================================= */
$queryRekap = mysqli_query($koneksi, "
    SELECT
        COUNT(*) AS total_pelanggaran,
        COALESCE(SUM(poin), 0) AS total_pengurangan
    FROM t_pelanggaran_siswa
    WHERE siswa_id = $siswaId
      AND kelas_id = $kelasId
      AND tahun_ajaran_id = $tahunId
");

if (!$queryRekap) {
    die('Gagal menghitung poin: ' . htmlspecialchars(mysqli_error($koneksi)));
}

$rekap = mysqli_fetch_assoc($queryRekap);

$totalPelanggaran = (int) $rekap['total_pelanggaran'];
$totalPengurangan = (int) $rekap['total_pengurangan'];

$poinAwal = 100;
$sisaPoin = max(0, $poinAwal - $totalPengurangan);

/* =========================================
   CEK SYARAT SURAT PEMANGGILAN
   SURAT HANYA UNTUK SISA POIN <= 18
========================================= */
if ($sisaPoin > 18) {
    die("
        <div style='font-family:Arial;text-align:center;margin:80px auto;max-width:600px;'>
            <h2>Surat Belum Dapat Dibuat</h2>
            <p>Sisa poin siswa masih <strong>$sisaPoin</strong>.</p>
            <p>Surat pemanggilan hanya dapat dibuat jika sisa poin mencapai 18 atau kurang.</p>
            <button onclick='history.back()'>Kembali</button>
        </div>
    ");
}

/* =========================================
   AMBIL RIWAYAT PELANGGARAN
========================================= */
$queryRiwayat = mysqli_query($koneksi, "
    SELECT
        id,
        tanggal,
        nama_pelanggaran,
        poin,
        keterangan,
        nama_guru,
        tindakan,
        status
    FROM t_pelanggaran_siswa
    WHERE siswa_id = $siswaId
      AND kelas_id = $kelasId
      AND tahun_ajaran_id = $tahunId
    ORDER BY tanggal ASC, id ASC
");

if (!$queryRiwayat) {
    die('Gagal mengambil riwayat pelanggaran: ' . htmlspecialchars(mysqli_error($koneksi)));
}

/* =========================================
   SIMPAN RIWAYAT KE ARRAY
========================================= */
$daftarRiwayat = [];

while ($row = mysqli_fetch_assoc($queryRiwayat)) {
    $daftarRiwayat[] = $row;
}

/* =========================================
   FUNGSI FORMAT TANGGAL
========================================= */
function tanggalIndonesia($tanggal)
{
    if (empty($tanggal)) {
        return '-';
    }

    $bulan = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    ];

    $pecah = explode('-', $tanggal);

    if (count($pecah) !== 3) {
        return $tanggal;
    }

    $nomorBulan = (int) $pecah[1];

    if (!isset($bulan[$nomorBulan])) {
        return $tanggal;
    }

    return (int) $pecah[2] . ' ' .
        $bulan[$nomorBulan] . ' ' .
        $pecah[0];
}

/* =========================================
   TANGGAL SURAT
========================================= */
$tanggalSurat = tanggalIndonesia(date('Y-m-d'));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Surat Pemanggilan Orang Tua - SIPENA MU</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #e9edf3;
            font-family: "Times New Roman", serif;
            color: #000;
        }

        .toolbar {
            max-width: 210mm;
            margin: 20px auto;
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .lembar {
            width: 210mm;
            min-height: 297mm;
            padding: 15mm 18mm;
            margin: 0 auto 25px;
            background: #fff;
            box-sizing: border-box;
            box-shadow: 0 2px 12px rgba(0,0,0,.12);
        }

        .kop-surat {
            display: flex;
            align-items: center;
            gap: 15px;
            text-align: center;
        }

        .logo-sekolah {
            width: 105px;
            height: 105px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .identitas-sekolah {
            flex: 1;
        }

        .identitas-sekolah h2 {
            font-size: 19px;
            font-weight: bold;
            margin: 0 0 3px;
        }

        .identitas-sekolah p {
            font-size: 11px;
            margin: 2px 0;
            line-height: 1.3;
        }

        .garis-kop {
            border-top: 3px solid #000;
            border-bottom: 1px solid #000;
            height: 6px;
            margin-top: 12px;
            margin-bottom: 25px;
        }

        .judul-surat {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            font-size: 16px;
            margin-bottom: 3px;
        }

        .nomor-surat {
            text-align: center;
            margin-bottom: 25px;
            font-size: 12px;
        }

        .isi-surat {
            font-size: 12pt;
            line-height: 1.5;
            text-align: justify;
        }

        .identitas {
            margin: 12px 0 12px 25px;
        }

        .identitas td {
            padding: 2px 5px;
            vertical-align: top;
        }

        .ttd {
            width: 250px;
            margin-left: auto;
            margin-top: 25px;
            text-align: left;
            font-size: 12pt;
        }

        .nama-ttd {
            margin-top: 65px;
            font-weight: bold;
            text-decoration: underline;
        }

        /* HALAMAN LAMPIRAN */
        .judul-lampiran {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 5px;
        }

        .subjudul-lampiran {
            text-align: center;
            font-size: 12px;
            margin-bottom: 25px;
        }

        .tabel-riwayat {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            margin: 15px 0;
        }

        .tabel-riwayat th,
        .tabel-riwayat td {
            border: 1px solid #000;
            padding: 7px;
            vertical-align: top;
        }

        .tabel-riwayat th {
            text-align: center;
            background: #f1f1f1;
        }

        .identitas-lampiran {
            font-size: 12pt;
            margin-top: 20px;
        }

        .catatan-lampiran {
            font-size: 11pt;
            margin-top: 20px;
            line-height: 1.5;
        }

        
@media print {
    @page {
        size: A4 portrait;
        margin: 10mm;
    }

    html,
    body {
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
        color: #000 !important;
        font-family: "Times New Roman", serif;
    }

    .toolbar {
        display: none !important;
    }

    .lembar {
        width: 100% !important;
        min-height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        box-sizing: border-box !important;
        box-shadow: none !important;
    }

    /* SURAT UTAMA: DIPADATKAN AGAR MUAT 1 HALAMAN */
    .halaman-pertama {
        break-after: page;
        page-break-after: always;
    }

    .halaman-pertama .kop-surat {
        gap: 10px;
    }

    .halaman-pertama .logo-sekolah {
        width: 78px;
        height: 78px;
    }

    .halaman-pertama .identitas-sekolah h2 {
        font-size: 16px;
        margin-bottom: 2px;
    }

    .halaman-pertama .identitas-sekolah p {
        font-size: 9px;
        line-height: 1.15;
        margin: 1px 0;
    }

    .halaman-pertama .garis-kop {
        margin-top: 7px;
        margin-bottom: 13px;
    }

    .halaman-pertama .judul-surat {
        font-size: 14px;
        margin-bottom: 2px;
    }

    .halaman-pertama .nomor-surat {
        font-size: 10px;
        margin-bottom: 12px;
    }

    .halaman-pertama .isi-surat {
        font-size: 10.5pt;
        line-height: 1.22;
    }

    .halaman-pertama .isi-surat p {
        margin-top: 0;
        margin-bottom: 7px;
    }

    .halaman-pertama .identitas {
        margin: 5px 0 7px 18px;
        font-size: 10.5pt;
    }

    .halaman-pertama .identitas td {
        padding: 1px 3px;
    }

    .halaman-pertama .ttd {
        width: 220px;
        margin-top: 8px;
        font-size: 10.5pt;
    }

    .halaman-pertama .nama-ttd {
        margin-top: 35px;
    }

    /* LAMPIRAN DIMULAI PADA HALAMAN BARU */
    .halaman-lampiran {
        break-before: page;
        page-break-before: always;
    }

    .halaman-lampiran .tabel-riwayat {
        font-size: 9pt;
    }

    .halaman-lampiran .tabel-riwayat th,
    .halaman-lampiran .tabel-riwayat td {
        padding: 5px;
    }

    .tabel-riwayat thead {
        display: table-header-group;
    }

    .tabel-riwayat tr {
        break-inside: avoid;
        page-break-inside: avoid;
    }
}

        @media screen and (max-width: 800px) {
            .lembar {
                width: 100%;
                min-height: auto;
                padding: 20px;
            }

            .kop-surat {
                gap: 8px;
            }

            .logo-sekolah {
                width: 75px;
                height: 75px;
            }

            .identitas-sekolah h2 {
                font-size: 14px;
            }

            .identitas-sekolah p {
                font-size: 9px;
            }
        }
    </style>
</head>

<body>

<!-- TOMBOL CETAK -->
<div class="toolbar">
    <a href="detail_rekap_poin.php?siswa_id=<?= $siswaId; ?>&kelas_id=<?= $kelasId; ?>&tahun_ajaran_id=<?= $tahunId; ?>"
   class="btn btn-secondary">
    <i class="bi bi-arrow-left"></i> Kembali
</a>

    <button onclick="window.print()" class="btn btn-primary">
        Cetak Surat
    </button>
</div>


<!-- =====================================
     HALAMAN PERTAMA: SURAT PEMBERITAHUAN
===================================== -->
<div class="lembar halaman-pertama">

    <!-- KOP SURAT -->
    <div class="kop-surat">

        <img
            src="images/logo-sekolah.png"
            alt="Logo SMK Muhammadiyah Kota Tasikmalaya"
            class="logo-sekolah"
        >

        <div class="identitas-sekolah">

            <h2>SMK MUHAMMADIYAH KOTA TASIKMALAYA</h2>

            <p><strong>Terakreditasi "A" (Unggul) – Sekolah Pusat Keunggulan</strong></p>

            <p>NPSN: 20224553</p>

            <p>
                Jl. Rumah Sakit No. 29, Kel. Empangsari, Kec. Tawang,
                Kota Tasikmalaya, Jawa Barat 46113
            </p>

            <p>
                Telepon: (0265) 327380 |
                Laman: www.smkmtsm.sch.id
            </p>

            <p>Pos-el: info@smkmtsm.sch.id</p>

        </div>
    </div>

    <div class="garis-kop"></div>

    <!-- JUDUL SURAT -->
    <div class="judul-surat">
        SURAT PEMANGGILAN ORANG TUA/WALI SISWA
    </div>

    <div class="nomor-surat">
        Nomor: ................................................
    </div>

    <!-- ISI SURAT -->
    <div class="isi-surat">

        <div style="text-align:right; margin-bottom:20px;">
            Tasikmalaya, <?= htmlspecialchars($tanggalSurat); ?>
        </div>

        <p>
            Perihal: <strong>Pemanggilan Orang Tua/Wali Siswa</strong>
        </p>

        <p>
            Kepada Yth.<br>
            Bapak/Ibu Orang Tua/Wali dari:
        </p>

        <table class="identitas">
            <tr>
                <td width="115">Nama</td>
                <td width="10">:</td>
                <td><strong><?= htmlspecialchars($siswa['nama']); ?></strong></td>
            </tr>

            <tr>
                <td>NIS</td>
                <td>:</td>
                <td><?= htmlspecialchars($siswa['nis']); ?></td>
            </tr>

            <tr>
                <td>Kelas</td>
                <td>:</td>
                <td><?= htmlspecialchars($siswa['nama_kelas']); ?></td>
            </tr>

            <tr>
                <td>Tahun Ajaran</td>
                <td>:</td>
                <td><?= htmlspecialchars($tahunAjaran); ?></td>
            </tr>
        </table>

        <p>Dengan hormat,</p>

        <p>
            Sehubungan dengan hasil pemantauan dan pencatatan pelanggaran
            tata tertib siswa melalui Sistem Informasi Pelanggaran dan
            Pembinaan Siswa Muhammadiyah (SIPENA MU), kami memberitahukan
            bahwa putra/putri Bapak/Ibu telah memperoleh sejumlah catatan
            pelanggaran yang mengakibatkan berkurangnya poin kedisiplinan.
        </p>

        <p>
            Berdasarkan catatan tersebut, siswa yang bersangkutan telah
            mencapai tahap yang memerlukan perhatian dan pembinaan khusus
            dari pihak sekolah bersama orang tua/wali.
        </p>

        <p>
            Adapun ringkasan poin kedisiplinan siswa adalah sebagai berikut:
        </p>

        <table class="identitas">
            <tr>
                <td width="170">Poin Awal</td>
                <td width="10">:</td>
                <td>100 poin</td>
            </tr>

            <tr>
                <td>Total Pengurangan</td>
                <td>:</td>
                <td><?= $totalPengurangan; ?> poin</td>
            </tr>

            <tr>
                <td>Sisa Poin</td>
                <td>:</td>
                <td><strong><?= $sisaPoin; ?> poin</strong></td>
            </tr>

            <tr>
                <td>Jumlah Pelanggaran</td>
                <td>:</td>
                <td><?= $totalPelanggaran; ?> kali</td>
            </tr>
        </table>

        <p>
            Rincian riwayat pelanggaran siswa tercantum pada lembar
            lampiran yang menjadi bagian tidak terpisahkan dari surat ini.
        </p>

        <p>
            Oleh karena itu, kami mengharapkan kehadiran Bapak/Ibu
            Orang Tua/Wali untuk melakukan koordinasi dan pembinaan
            bersama pihak sekolah demi meningkatkan kedisiplinan
            serta perkembangan perilaku siswa.
        </p>

        <p>
            Kami mengundang Bapak/Ibu untuk hadir pada:
        </p>

        <table class="identitas">
            <tr>
                <td width="100">Hari</td>
                <td width="10">:</td>
                <td>................................................</td>
            </tr>

            <tr>
                <td>Tanggal</td>
                <td>:</td>
                <td>................................................</td>
            </tr>

            <tr>
                <td>Waktu</td>
                <td>:</td>
                <td>................................................</td>
            </tr>

            <tr>
                <td>Tempat</td>
                <td>:</td>
                <td>SMK Muhammadiyah Kota Tasikmalaya</td>
            </tr>
        </table>

        <p>
            Demikian surat pemanggilan ini kami sampaikan.
            Atas perhatian dan kerja sama Bapak/Ibu, kami mengucapkan
            terima kasih.
        </p>

    </div>

    <!-- TANDA TANGAN -->
    <div class="ttd">

        <p>
            Tasikmalaya, <?= htmlspecialchars($tanggalSurat); ?><br>
            Hormat kami,
        </p>

        <p class="nama-ttd">
            Wawan Kurniawan, S.Pd.I.
        </p>

    </div>

</div>


<!-- =====================================
     HALAMAN KEDUA: LAMPIRAN PELANGGARAN
===================================== -->
<div class="lembar halaman-lampiran">

    <div class="judul-lampiran">
        LAMPIRAN SURAT PEMANGGILAN ORANG TUA/WALI
    </div>

    <div class="subjudul-lampiran">
        RINCIAN RIWAYAT PELANGGARAN SISWA
    </div>

    <div class="identitas-lampiran">

        <table class="identitas">
            <tr>
                <td width="115">Nama Siswa</td>
                <td width="10">:</td>
                <td><strong><?= htmlspecialchars($siswa['nama']); ?></strong></td>
            </tr>

            <tr>
                <td>NIS</td>
                <td>:</td>
                <td><?= htmlspecialchars($siswa['nis']); ?></td>
            </tr>

            <tr>
                <td>Kelas</td>
                <td>:</td>
                <td><?= htmlspecialchars($siswa['nama_kelas']); ?></td>
            </tr>

            <tr>
                <td>Tahun Ajaran</td>
                <td>:</td>
                <td><?= htmlspecialchars($tahunAjaran); ?></td>
            </tr>
        </table>

    </div>

    <p style="margin-top:20px; font-size:12pt;">
        Berikut adalah rincian pelanggaran tata tertib yang telah
        tercatat selama tahun ajaran tersebut:
    </p>

    <!-- TABEL RIWAYAT PELANGGARAN -->
    <table class="tabel-riwayat">

        <thead>
            <tr>
                <th width="35">No.</th>
                <th width="90">Tanggal</th>
                <th>Jenis Pelanggaran</th>
                <th width="55">Poin</th>
                <th>Keterangan</th>
            </tr>
        </thead>

        <tbody>

            <?php if (count($daftarRiwayat) > 0): ?>

                <?php $no = 1; ?>

                <?php foreach ($daftarRiwayat as $row): ?>

                    <tr>
                        <td style="text-align:center;">
                            <?= $no++; ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(tanggalIndonesia($row['tanggal'])); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['nama_pelanggaran']); ?>
                        </td>

                        <td style="text-align:center;">
                            <?= (int) $row['poin']; ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['keterangan'] ?: '-'); ?>
                        </td>
                    </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>
                    <td colspan="5" style="text-align:center;">
                        Tidak ada riwayat pelanggaran.
                    </td>
                </tr>

            <?php endif; ?>

        </tbody>

        <tfoot>
            <tr>
                <th colspan="3" style="text-align:right;">
                    Total Pengurangan Poin
                </th>
                <th style="text-align:center;">
                    <?= $totalPengurangan; ?>
                </th>
                <th>Poin</th>
            </tr>
        </tfoot>

    </table>

    <div class="catatan-lampiran">
        <strong>Catatan:</strong><br>
        Lampiran ini merupakan rincian catatan pelanggaran siswa
        yang menjadi dasar pemberitahuan dan pemanggilan orang tua/wali
        sebagaimana tercantum dalam surat pemanggilan.
    </div>

</div>

</body>
</html>
