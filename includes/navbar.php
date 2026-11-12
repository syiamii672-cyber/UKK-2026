
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
        box-sizing: border-box;
    }

    .navbar-judul {
        color: #173B67;
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
        position: relative;
    }

    .navbar-profil-btn {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 7px 10px;
        background: transparent;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        text-align: left;
    }

    .navbar-profil-btn:hover,
    .navbar-profil-btn[aria-expanded="true"] {
        background: #F1F5FB;
    }

    .navbar-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #E9EEF2;
        color: #173B67;
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

    .navbar-menu-profil {
        display: none;
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        min-width: 220px;
        margin: 0;
        padding: 8px;
        list-style: none;
        background: #FFFFFF;
        border: 1px solid #E9EEF2;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(23, 59, 103, 0.12);
        z-index: 1200;
    }

    .navbar-menu-profil.show {
        display: block;
    }

    .navbar-menu-profil .dropdown-item {
        display: block;
        width: 100%;
        padding: 10px 12px;
        border-radius: 8px;
        color: #34495E;
        font-size: 13px;
        text-decoration: none;
        box-sizing: border-box;
    }

    .navbar-menu-profil .dropdown-item:hover {
        background: #F1F5FB;
        color: #173B67;
    }

    .navbar-menu-profil .dropdown-item i {
        width: 22px;
    }

    @media (max-width: 768px) {
        .navbar-ukk {
            left: 220px;
            padding: 0 12px;
        }

        .navbar-judul {
            font-size: 15px;
        }

        .navbar-subjudul {
            display: none;
        }

        .navbar-profil-btn {
            gap: 7px;
            padding: 6px;
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

    <div class="navbar-profil" id="navbarProfil">

        <button
            class="navbar-profil-btn"
            type="button"
            id="navbarProfilBtn"
            aria-expanded="false"
            aria-controls="navbarProfilMenu">

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

            <i class="bi bi-chevron-down text-secondary small"></i>
        </button>

        <ul class="navbar-menu-profil" id="navbarProfilMenu">
            <li>
                <a class="dropdown-item" href="profil.php">
                    <i class="bi bi-person"></i>
                    Profil Saya
                </a>
            </li>
        </ul>

    </div>

</header>

<script>
(function () {
    const tombol = document.getElementById('navbarProfilBtn');
    const menu = document.getElementById('navbarProfilMenu');
    const wadah = document.getElementById('navbarProfil');

    if (!tombol || !menu || !wadah) {
        return;
    }

    tombol.addEventListener('click', function (event) {
        event.stopPropagation();

        const sedangTerbuka = menu.classList.contains('show');

        menu.classList.toggle('show', !sedangTerbuka);
        tombol.setAttribute('aria-expanded', String(!sedangTerbuka));
    });

    document.addEventListener('click', function (event) {
        if (!wadah.contains(event.target)) {
            menu.classList.remove('show');
            tombol.setAttribute('aria-expanded', 'false');
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            menu.classList.remove('show');
            tombol.setAttribute('aria-expanded', 'false');
        }
    });
})();
</script>
