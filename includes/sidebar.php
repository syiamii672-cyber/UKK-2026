
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = strtolower(trim($_SESSION['role'] ?? ''));

require_once __DIR__ . '/cek_wali_kelas.php';

$menuAktif = $menuAktif ?? "";

// Menu Admin
$menuAdmin = [
    ["Dashboard", "dashboard.php", "dashboard", "bi-grid"],
    ["Kelola Guru", "data_guru.php", "guru", "bi-person-badge"],
    ["Kelola Siswa", "siswa.php", "siswa", "bi-people"],
    ["Kelola Kelas", "kelas.php", "kelas", "bi-building"],
    ["Kelola Tahun Ajaran", "tahun_ajaran.php", "tahun_ajaran", "bi-calendar3"],
    ["Penempatan Siswa", "penempatan_siswa.php", "penempatan", "bi-diagram-3"],
    ["Kelola Wali Kelas", "wali_kelas.php", "wali_kelas", "bi-person-workspace"],
    ["Kategori Pelanggaran", "kategori_pelanggaran.php", "kategori", "bi-tags"],
    ["Jenis Pelanggaran", "jenis_pelanggaran.php", "jenis", "bi-list-check"],
    ["Rekap Poin", "rekap_poin.php", "rekap_poin", "bi-bar-chart"],
    ["About Me", "about.php", "about", "bi-info-circle"],
    ["Laporan", "laporan.php", "laporan", "bi-file-earmark-text"]
];

// Menu Guru
$menuGuru = [
    ["Dashboard", "dashboard.php", "dashboard", "bi-grid"],
    ["Input Pelanggaran", "input_pelanggaran.php", "input_pelanggaran", "bi-pencil-square"],
    ["Catatan Pelanggaran", "catatan_pelanggaran.php", "catatan_pelanggaran", "bi-journal-text"],
    ["Rekap Poin", "rekap_poin.php", "rekap_poin", "bi-bar-chart"],
    ["Riwayat", "riwayat.php", "riwayat", "bi-clock-history"]
];

// Menu Guru / Wali Kelas
$menuWaliKelas = [
    ["Dashboard", "dashboard.php", "dashboard", "bi-grid"],
    ["Input Pelanggaran", "input_pelanggaran.php", "input_pelanggaran", "bi-pencil-square"],
    ["Catatan Pelanggaran", "catatan_pelanggaran.php", "catatan_pelanggaran", "bi-journal-text"],
    ["Rekap Poin", "rekap_poin.php", "rekap_poin", "bi-bar-chart"],
    ["Rekap Kelas", "rekap_kelas.php", "rekap_kelas", "bi-people"],
    ["Riwayat", "riwayat.php", "riwayat", "bi-clock-history"],
    ["Laporan", "laporan.php", "laporan", "bi-file-earmark-text"]
];

// Menentukan menu berdasarkan role
if ($role === "admin") {

    $daftarMenu = $menuAdmin;
    $labelRole = "Administrator";

} elseif ($role === "guru" && $isWaliKelas) {

    $daftarMenu = $menuWaliKelas;
    $labelRole = "Guru / Wali Kelas";

} elseif ($role === "guru") {

    $daftarMenu = $menuGuru;
    $labelRole = "Guru";

} else {

    $daftarMenu = [];
    $labelRole = "Pengguna";
}

?>

<link rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    .sidebar-ukk {
        position: fixed;
        top: 0;
        left: 0;
        width: 220px;
        height: 100vh;
        padding: 22px 14px;
        background: #34495E;
        color: #FFFFFF;
        overflow-y: auto;
        z-index: 1100;
        box-sizing: border-box;
        border-right: 1px solid rgba(255,255,255,0.06);
    }

    .sidebar-ukk .brand-sidebar {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 4px 8px 22px;
        margin-bottom: 16px;
        border-bottom: 1px solid rgba(255,255,255,0.13);
    }

    .sidebar-ukk .brand-icon {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 10px;
        background: rgba(255,255,255,0.13);
        color: #FFFFFF;
        font-size: 18px;
    }

    .sidebar-ukk .brand-title {
        font-size: 13px;
        font-weight: 700;
        line-height: 1.4;
        color: #FFFFFF;
        margin: 0;
    }

    .sidebar-ukk .role-label {
        color: #D1DCE6;
        font-size: 12px;
        padding: 0 10px;
        margin-bottom: 5px;
    }

    .sidebar-ukk .kelas-label {
        color: #C4D3E0;
        font-size: 11px;
        padding: 0 10px;
        margin-bottom: 20px;
    }

    .sidebar-ukk .menu-label {
        color: #AEBECD;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1px;
        padding: 0 10px;
        margin-bottom: 10px;
        text-transform: uppercase;
    }

    .sidebar-ukk .nav-link {
        display: flex;
        align-items: center;
        gap: 11px;
        border-radius: 8px;
        padding: 10px 11px;
        margin-bottom: 4px;
        color: #E1E8EF;
        font-size: 12.5px;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .sidebar-ukk .nav-link i {
        width: 17px;
        font-size: 15px;
        text-align: center;
        color: #C0CEDA;
    }

    .sidebar-ukk .nav-link.active {
        background: #607D94;
        color: #FFFFFF;
        font-weight: 600;
    }

    .sidebar-ukk .nav-link.active i {
        color: #FFFFFF;
    }

    .sidebar-ukk .nav-link:not(.active):hover {
        background: rgba(255,255,255,0.09);
        color: #FFFFFF;
    }

    .sidebar-ukk .nav-link.logout {
        color: #F0C5C5;
    }

    .sidebar-ukk .nav-link.logout i {
        color: #F0C5C5;
    }

    .sidebar-ukk .nav-link.logout:hover {
        background: rgba(220,80,80,0.13);
        color: #FFFFFF;
    }

    .sidebar-ukk .sidebar-bottom {
        margin-top: 22px;
        padding-top: 12px;
        border-top: 1px solid rgba(255,255,255,0.13);
    }
</style>

<!-- SIDEBAR -->
<div class="sidebar-ukk">

    <div class="brand-sidebar">

        <div class="brand-icon">
            <i class="bi bi-mortarboard-fill"></i>
        </div>

        <p class="brand-title">
            Sistem Informasi<br>
            Pelanggaran Siswa
        </p>

    </div>

    <div class="role-label">
        <?= htmlspecialchars($labelRole, ENT_QUOTES, 'UTF-8'); ?>
    </div>

    <?php if ($role === "guru" && $isWaliKelas): ?>

        <div class="kelas-label">
            <i class="bi bi-building me-1"></i>
            Kelas: <?= htmlspecialchars($namaKelasWali, ENT_QUOTES, 'UTF-8'); ?>
        </div>

    <?php else: ?>

        <div class="mb-3"></div>

    <?php endif; ?>

    <div class="menu-label">Menu Utama</div>

    <ul class="nav nav-pills flex-column">

        <?php foreach ($daftarMenu as $menu): ?>

            <li class="nav-item">

                <a href="<?= htmlspecialchars($menu[1], ENT_QUOTES, 'UTF-8'); ?>"
                   class="nav-link <?= $menuAktif === $menu[2] ? 'active' : ''; ?>">

                    <i class="bi <?= htmlspecialchars($menu[3], ENT_QUOTES, 'UTF-8'); ?>"></i>

                    <span>
                        <?= htmlspecialchars($menu[0], ENT_QUOTES, 'UTF-8'); ?>
                    </span>

                </a>

            </li>

        <?php endforeach; ?>

    </ul>

    <div class="sidebar-bottom">

        <div class="menu-label">Akun</div>

        <a href="profil.php"
           class="nav-link <?= $menuAktif === 'profil' ? 'active' : ''; ?>">

            <i class="bi bi-person-circle"></i>
            <span>Profil</span>

        </a>

        <a href="logout.php" class="nav-link logout">

            <i class="bi bi-box-arrow-right"></i>
            <span>Keluar</span>

        </a>

    </div>

</div>
