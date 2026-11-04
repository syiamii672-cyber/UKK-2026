
<?php
$menuAktif = $menuAktif ?? "";
$role = $_SESSION['role'] ?? "";
$idUser = (int) ($_SESSION['id'] ?? 0);

$isWaliKelas = false;
$namaKelasWali = "";

// Cek apakah guru memiliki penugasan Wali Kelas aktif
if ($role === "guru" && isset($koneksi)) {

    $queryWali = mysqli_query($koneksi, "
        SELECT k.nama AS nama_kelas
        FROM t_guru g
        JOIN t_wali_kelas w ON g.id = w.guru_id
        JOIN t_kelas k ON w.kelas_id = k.id
        JOIN t_tahun_ajaran ta ON w.tahun_ajaran_id = ta.id
        WHERE g.user_id = $idUser
          AND g.status_aktif = 1
          AND w.status_aktif = 1
          AND k.status_aktif = 1
          AND ta.status_aktif = 1
        LIMIT 1
    ");

    if ($queryWali && mysqli_num_rows($queryWali) > 0) {
        $dataWali = mysqli_fetch_assoc($queryWali);
        $isWaliKelas = true;
        $namaKelasWali = $dataWali['nama_kelas'];
    }
}

// Menu Admin
$menuAdmin = [
    ["Dashboard", "dashboard.php", "dashboard"],
    ["Kelola Guru", "data_guru.php", "guru"],
    ["Kelola Siswa", "siswa.php", "siswa"],
    ["Kelola Kelas", "kelas.php", "kelas"],
    ["Kelola Tahun Ajaran", "tahun_ajaran.php", "tahun_ajaran"],
    ["Penempatan Siswa", "penempatan_siswa.php", "penempatan"],
    ["Kelola Wali Kelas", "wali_kelas.php", "wali_kelas"],
    ["Kategori Pelanggaran", "kategori_pelanggaran.php", "kategori"],
    ["Jenis Pelanggaran", "jenis_pelanggaran.php", "jenis"],
    ["About Me", "about.php", "about"],
    ["Laporan", "laporan.php", "laporan"]
];

// Menu dasar untuk SEMUA guru
$menuGuru = [
    ["Dashboard", "dashboard.php", "dashboard"],
    ["Catat Pelanggaran", "catat_pelanggaran.php", "catat_pelanggaran"],
    ["Tindakan", "tindakan.php", "tindakan"],
    ["Rekap Poin", "rekap_poin.php", "rekap_poin"],
    ["Riwayat", "riwayat_pelanggaran.php", "riwayat"]
];

// Menentukan menu
if ($role === "admin") {

    $daftarMenu = $menuAdmin;
    $labelRole = "Administrator";

} elseif ($role === "guru") {

    $daftarMenu = $menuGuru;
    $labelRole = "Guru";

    // Laporan hanya untuk guru yang ditugaskan sebagai Wali Kelas
    if ($isWaliKelas) {
        $daftarMenu[] = ["Laporan", "laporan.php", "laporan"];
        $labelRole = "Guru / Wali Kelas";
    }

} else {
    $daftarMenu = [];
    $labelRole = "Pengguna";
}
?>

<style>
.sidebar-ukk {
    position: fixed;
    top: 0;
    left: 0;
    width: 220px;
    height: 100vh;
    padding: 20px 14px;
    background-color: #212529;
    color: white;
    overflow-y: auto;
    z-index: 1000;
    box-sizing: border-box;
}

.sidebar-ukk .nav-link {
    border-radius: 6px;
    padding: 10px 12px;
    color: white;
    font-size: 14px;
}

.sidebar-ukk .nav-link.active {
    background-color: #0d6efd;
    color: white;
}

.sidebar-ukk .nav-link:not(.active):hover {
    background-color: #343a40;
    color: white;
}

.sidebar-ukk .nav-link.logout {
    color: #ff6b6b;
}
</style>

<div class="sidebar-ukk">

    <h5 class="text-white mb-4">Sistem Pelanggaran</h5>

    <div class="text-secondary small mb-3">
        <?= htmlspecialchars($labelRole); ?>
    </div>

    <?php if ($isWaliKelas): ?>
        <div class="small text-info mb-3">
            Kelas: <?= htmlspecialchars($namaKelasWali); ?>
        </div>
    <?php endif; ?>

    <ul class="nav nav-pills flex-column">

        <?php foreach ($daftarMenu as $menu): ?>
            <li class="nav-item mb-2">
                <a href="<?= htmlspecialchars($menu[1]); ?>"
                   class="nav-link <?= $menuAktif === $menu[2] ? 'active' : ''; ?>">
                    <?= htmlspecialchars($menu[0]); ?>
                </a>
            </li>
        <?php endforeach; ?>

        <hr class="text-secondary">

        <li class="nav-item">
            <a href="logout.php" class="nav-link logout">
                Logout
            </a>
        </li>

    </ul>
</div>
