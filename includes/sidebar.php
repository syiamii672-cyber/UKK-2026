
<?php
$menuAktif = $menuAktif ?? "";
?>

<div class="position-fixed top-0 start-0 bg-dark text-white vh-100 p-3"
     style="width: 220px; overflow-y: auto; z-index: 1000;">

    <h5 class="text-white mb-4">Sistem Pelanggaran</h5>

    <ul class="nav nav-pills flex-column">

        <li class="nav-item mb-2">
            <a href="dashboard.php"
               class="nav-link <?php echo $menuAktif == 'dashboard' ? 'active' : 'text-white'; ?>">
                Dashboard
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="siswa.php"
               class="nav-link <?php echo $menuAktif == 'siswa' ? 'active' : 'text-white'; ?>">
                Data Siswa
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="data_guru.php"
               class="nav-link <?php echo $menuAktif == 'guru' ? 'active' : 'text-white'; ?>">
                Data Guru
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="kelas.php"
               class="nav-link <?php echo $menuAktif == 'kelas' ? 'active' : 'text-white'; ?>">
                Kelas
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="#" class="nav-link text-white">
                Laporan
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="about.php" class="nav-link text-white">
                About Me
            </a>
        </li>

        <hr class="text-secondary">

        <li class="nav-item">
            <a href="logout.php" class="nav-link text-danger">
                Logout
            </a>
        </li>

    </ul>
</div>