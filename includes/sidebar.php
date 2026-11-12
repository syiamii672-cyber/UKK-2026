
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = strtolower(trim($_SESSION['role'] ?? ''));

require_once __DIR__ . '/cek_wali_kelas.php';

$menuAktif = $menuAktif ?? '';

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

$menuGuru = [
    ["Dashboard", "dashboard.php", "dashboard", "bi-grid"],
    ["Input Pelanggaran", "input_pelanggaran.php", "input_pelanggaran", "bi-pencil-square"],
    ["Catatan Pelanggaran", "catatan_pelanggaran.php", "catatan_pelanggaran", "bi-journal-text"],
    ["Rekap Poin", "rekap_poin.php", "rekap_poin", "bi-bar-chart"],
    ["Riwayat", "riwayat.php", "riwayat", "bi-clock-history"]
];

$menuWaliKelas = [
    ["Dashboard", "dashboard.php", "dashboard", "bi-grid"],
    ["Input Pelanggaran", "input_pelanggaran.php", "input_pelanggaran", "bi-pencil-square"],
    ["Catatan Pelanggaran", "catatan_pelanggaran.php", "catatan_pelanggaran", "bi-journal-text"],
    ["Rekap Poin", "rekap_poin.php", "rekap_poin", "bi-bar-chart"],
    ["Rekap Kelas", "rekap_kelas.php", "rekap_kelas", "bi-people"],
    ["Riwayat", "riwayat.php", "riwayat", "bi-clock-history"],
    ["Laporan", "laporan.php", "laporan", "bi-file-earmark-text"]
];

if ($role === 'admin') {
    $daftarMenu = $menuAdmin;
    $labelRole = 'Administrator';
} elseif ($role === 'guru' && $isWaliKelas) {
    $daftarMenu = $menuWaliKelas;
    $labelRole = 'Guru / Wali Kelas';
} elseif ($role === 'guru') {
    $daftarMenu = $menuGuru;
    $labelRole = 'Guru';
} else {
    $daftarMenu = [];
    $labelRole = 'Pengguna';
}
?>

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
.sidebar-ukk {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: auto !important;

    width: 220px !important;
    min-width: 220px !important;
    max-width: 220px !important;

    height: 100vh !important;
    padding: 20px 14px !important;

    display: flex !important;
    flex: 0 0 220px !important;
    flex-direction: column !important;
    flex-wrap: nowrap !important;

    background: #34495E !important;
    color: #FFFFFF !important;

    overflow-x: hidden !important;
    overflow-y: auto !important;

    box-sizing: border-box !important;
    transform: none !important;
    z-index: 1100 !important;
}

.sidebar-ukk .brand-sidebar {
    display: flex !important;
    align-items: center !important;
    flex: 0 0 auto !important;
    gap: 10px !important;

    padding: 4px 8px 20px !important;
    margin-bottom: 15px !important;

    border-bottom: 1px solid rgba(255,255,255,0.13) !important;
}

.sidebar-ukk .brand-icon {
    width: 36px !important;
    min-width: 36px !important;
    height: 36px !important;
    min-height: 36px !important;

    display: flex !important;
    align-items: center !important;
    justify-content: center !important;

    border-radius: 10px !important;
    background: rgba(255,255,255,0.13) !important;
    color: #FFFFFF !important;
    font-size: 18px !important;
}

.sidebar-ukk .brand-title {
    margin: 0 !important;
    color: #FFFFFF !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    line-height: 1.4 !important;
}

.sidebar-ukk .role-label {
    flex: 0 0 auto !important;
    padding: 0 10px !important;
    margin-bottom: 5px !important;

    color: #D1DCE6 !important;
    font-size: 12px !important;
    line-height: 1.5 !important;
}

.sidebar-ukk .kelas-label {
    flex: 0 0 auto !important;
    padding: 0 10px !important;
    margin-bottom: 18px !important;

    color: #C4D3E0 !important;
    font-size: 11px !important;
    line-height: 1.5 !important;
}

.sidebar-ukk .menu-label {
    flex: 0 0 auto !important;
    padding: 0 10px !important;
    margin: 10px 0 !important;

    color: #AEBECD !important;
    font-size: 10px !important;
    font-weight: 700 !important;
    letter-spacing: 1px !important;
    line-height: 1.5 !important;
    text-transform: uppercase !important;
}

.sidebar-ukk .sidebar-menu {
    display: flex !important;
    flex-direction: column !important;
    flex-wrap: nowrap !important;

    width: 100% !important;
    min-width: 0 !important;

    padding: 0 !important;
    margin: 0 !important;
    list-style: none !important;
}

.sidebar-ukk .sidebar-menu .nav-item {
    display: block !important;
    width: 100% !important;
    min-width: 0 !important;

    flex: 0 0 auto !important;
    margin: 0 0 4px 0 !important;
    padding: 0 !important;
}

.sidebar-ukk .sidebar-menu .nav-link,
.sidebar-ukk .sidebar-menu .nav-link:link,
.sidebar-ukk .sidebar-menu .nav-link:visited,
.sidebar-ukk .sidebar-menu .nav-link:hover,
.sidebar-ukk .sidebar-menu .nav-link:focus,
.sidebar-ukk .sidebar-menu .nav-link:active,
.sidebar-ukk .sidebar-menu .nav-link.active {
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;

    width: 100% !important;
    min-width: 0 !important;
    max-width: 100% !important;

    height: 42px !important;
    min-height: 42px !important;
    max-height: 42px !important;

    flex: 0 0 42px !important;
    box-sizing: border-box !important;

    padding: 0 11px !important;
    margin: 0 !important;
    gap: 11px !important;

    border: none !important;
    border-radius: 8px !important;

    background: transparent !important;
    color: #E1E8EF !important;

    font-family: inherit !important;
    font-size: 12.5px !important;
    font-weight: 500 !important;
    line-height: 1 !important;
    letter-spacing: normal !important;

    text-align: left !important;
    text-decoration: none !important;
    text-transform: none !important;
    white-space: nowrap !important;

    transform: none !important;
    box-shadow: none !important;
    outline-offset: 0 !important;

    transition: background-color 0.15s ease, color 0.15s ease !important;
}

.sidebar-ukk .sidebar-menu .nav-link i {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;

    width: 17px !important;
    min-width: 17px !important;
    max-width: 17px !important;

    height: 17px !important;
    flex: 0 0 17px !important;

    margin: 0 !important;
    padding: 0 !important;

    color: #C0CEDA !important;
    font-size: 15px !important;
    line-height: 1 !important;
}

.sidebar-ukk .sidebar-menu .nav-link span {
    display: block !important;

    width: auto !important;
    min-width: 0 !important;
    max-width: 100% !important;

    flex: 1 1 auto !important;

    margin: 0 !important;
    padding: 0 !important;

    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;

    font-size: 12.5px !important;
    font-weight: 500 !important;
    line-height: 1.2 !important;
}

.sidebar-ukk .sidebar-menu .nav-link.active {
    background: #607D94 !important;
    color: #FFFFFF !important;
}

.sidebar-ukk .sidebar-menu .nav-link.active i {
    color: #FFFFFF !important;
}

.sidebar-ukk .sidebar-menu .nav-link:not(.active):hover {
    background: rgba(255,255,255,0.09) !important;
    color: #FFFFFF !important;
}

.sidebar-ukk .sidebar-bottom {
    flex: 0 0 auto !important;
    width: 100% !important;

    margin-top: 22px !important;
    padding-top: 12px !important;

    border-top: 1px solid rgba(255,255,255,0.13) !important;
}

.sidebar-ukk .sidebar-bottom .nav-link,
.sidebar-ukk .sidebar-bottom .nav-link:hover,
.sidebar-ukk .sidebar-bottom .nav-link:focus,
.sidebar-ukk .sidebar-bottom .nav-link:active {
    display: flex !important;
    align-items: center !important;

    width: 100% !important;
    height: 42px !important;
    min-height: 42px !important;
    max-height: 42px !important;

    flex: 0 0 42px !important;
    box-sizing: border-box !important;

    padding: 0 11px !important;
    margin: 0 !important;
    gap: 11px !important;

    border-radius: 8px !important;
    background: transparent !important;
    color: #F0C5C5 !important;

    font-size: 12.5px !important;
    font-weight: 500 !important;
    line-height: 1 !important;
    text-decoration: none !important;

    transform: none !important;
    box-shadow: none !important;
}

.sidebar-ukk .sidebar-bottom .nav-link i {
    width: 17px !important;
    min-width: 17px !important;
    flex: 0 0 17px !important;

    color: #F0C5C5 !important;
    font-size: 15px !important;
}

.sidebar-ukk .sidebar-bottom .nav-link span {
    white-space: nowrap !important;
    font-size: 12.5px !important;
}

.sidebar-ukk .sidebar-bottom .nav-link:hover {
    background: rgba(220,80,80,0.13) !important;
    color: #FFFFFF !important;
}

/* PENGUNCI SIDEBAR UNTUK SEMUA HALAMAN */
html body .sidebar-ukk {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: auto !important;

    width: 220px !important;
    min-width: 220px !important;
    max-width: 220px !important;

    height: 100vh !important;
    box-sizing: border-box !important;

    flex: 0 0 220px !important;
    transform: none !important;
}

@media (max-width: 768px) {
    .sidebar-ukk {
        width: 220px !important;
        min-width: 220px !important;
        max-width: 220px !important;
    }
}
</style>

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

    <?php if ($role === 'guru' && $isWaliKelas): ?>
        <div class="kelas-label">
            <i class="bi bi-building me-1"></i>
            Kelas: <?= htmlspecialchars($namaKelasWali, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <div class="menu-label">Menu Utama</div>

    <ul class="sidebar-menu nav nav-pills">
        <?php foreach ($daftarMenu as $menu): ?>
            <li class="nav-item">
                <a
                    href="<?= htmlspecialchars($menu[1], ENT_QUOTES, 'UTF-8'); ?>"
                    class="nav-link <?= $menuAktif === $menu[2] ? 'active' : ''; ?>"
                >
                    <i class="bi <?= htmlspecialchars($menu[3], ENT_QUOTES, 'UTF-8'); ?>"></i>
                    <span><?= htmlspecialchars($menu[0], ENT_QUOTES, 'UTF-8'); ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="sidebar-bottom">
        <div class="menu-label">Akun</div>

        <a
            href="logout.php"
            class="nav-link logout"
            onclick="return confirm('Apakah Anda yakin ingin keluar?')"
        >
            <i class="bi bi-box-arrow-right"></i>
            <span>Keluar</span>
        </a>
    </div>

</div>
