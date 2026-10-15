```php
<?php
$menuAktif = $menuAktif ?? "";
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
    }

    .sidebar-ukk .nav-link.active {
        background-color: #0d6efd;
        color: white;
    }

    .sidebar-ukk .nav-link:not(.active) {
        color: white;
    }

    .sidebar-ukk .nav-link:hover {
        background-color: #343a40;
        color: white;
    }

    .sidebar-ukk .nav-link.logout {
        color: #ff6b6b;
    }
</style>

<div class="sidebar-ukk">
    <h5 class="text-white mb-4">Sistem Pelanggaran</h5>

    <ul class="nav nav-pills flex-column">
        <li class="nav-item mb-2">
            <a href="dashboard.php"
               class="nav-link <?= $menuAktif == 'dashboard' ? 'active' : '' ?>">
                Dashboard
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="siswa.php"
               class="nav-link <?= $menuAktif == 'siswa' ? 'active' : '' ?>">
                Data Siswa
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="data_guru.php"
               class="nav-link <?= $menuAktif == 'guru' ? 'active' : '' ?>">
                Data Guru
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="kelas.php"
               class="nav-link <?= $menuAktif == 'kelas' ? 'active' : '' ?>">
                Kelas
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="#" class="nav-link">Laporan</a>
        </li>

        <li class="nav-item mb-2">
            <a href="about.php" class="nav-link">About Me</a>
        </li>

        <hr class="text-secondary">

        <li class="nav-item">
            <a href="logout.php" class="nav-link logout">Logout</a>
        </li>
    </ul>
</div>
```