
<?php
$namaPengguna = $_SESSION['name'] ?? 'Pengguna';
$rolePengguna = strtolower(trim($_SESSION['role'] ?? ''));

if ($rolePengguna === 'admin') {
    $labelRoleNavbar = 'Administrator';
} elseif (!empty($isWaliKelas)) {
    $labelRoleNavbar = 'Guru / Wali Kelas';
} else {
    $labelRoleNavbar = 'Guru';
}

$inisial = strtoupper(substr(trim($namaPengguna), 0, 1));
$judulNavbar = $judulHalaman ?? 'Sistem Informasi Pelanggaran Siswa';
?>

<style>
    .navbar-ukk {
        position: fixed;
        top: 0;
        left: 220px;
        right: 0;
        height: 76px;
        padding: 0 28px;
        background: #FFFFFF;
        border-bottom: 1px solid #E9EEF2;
        display: flex;
        align-items: center;
        justify-content: space-between;
        z-index: 1050;
    }

    .navbar-judul {
        color: #34495E;
        font-size: 18px;
        font-weight: 700;
        margin: 0;
    }

    .navbar-subjudul {
        color: #8995A1;
        font-size: 12px;
        margin-top: 3px;
    }

    .navbar-profil {
        display: flex;
        align-items: center;
        gap: 11px;
        padding-left: 22px;
        border-left: 1px solid #E9EEF2;
    }

    .navbar-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #E9EEF2;
        color: #34495E;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 15px;
        flex-shrink: 0;
    }

    .navbar-nama {
        color: #34495E;
        font-size: 13px;
        font-weight: 600;
        margin: 0;
    }

    .navbar-role {
        color: #8995A1;
        font-size: 11px;
        margin: 2px 0 0;
    }

    @media (max-width: 768px) {
        .navbar-ukk {
            left: 220px;
            padding: 0 14px;
        }

        .navbar-judul {
            font-size: 15px;
        }

        .navbar-subjudul {
            display: none;
        }

        .navbar-profil {
            padding-left: 12px;
            gap: 8px;
        }

        .navbar-avatar {
            width: 35px;
            height: 35px;
        }

        .navbar-nama {
            font-size: 11px;
        }

        .navbar-role {
            font-size: 10px;
        }
    }
</style>

<header class="navbar-ukk">
    <div>
        <h5 class="navbar-judul">
            <?= htmlspecialchars($judulNavbar, ENT_QUOTES, 'UTF-8'); ?>
        </h5>

        <div class="navbar-subjudul">
            Sistem Informasi Pelanggaran Siswa
        </div>
    </div>

    <div class="navbar-profil">
        <div class="navbar-avatar">
            <?= htmlspecialchars($inisial, ENT_QUOTES, 'UTF-8'); ?>
        </div>

        <div>
            <p class="navbar-nama">
                <?= htmlspecialchars($namaPengguna, ENT_QUOTES, 'UTF-8'); ?>
            </p>

            <p class="navbar-role">
                <?= htmlspecialchars($labelRoleNavbar, ENT_QUOTES, 'UTF-8'); ?>
            </p>
        </div>
    </div>
</header>
