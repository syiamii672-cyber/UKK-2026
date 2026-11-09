
<?php

// Default: bukan wali kelas
$isWaliKelas = false;
$namaKelasWali = '';
$kelasWali = 0;
$tahunWali = 0;

// Cek hanya untuk akun guru
if (isset($koneksi) && ($_SESSION['role'] ?? '') === 'guru') {

    $idUser = (int) ($_SESSION['id'] ?? 0);

    $queryWali = "
        SELECT
            w.kelas_id,
            w.tahun_ajaran_id,
            k.nama AS nama_kelas
        FROM t_guru g
        JOIN t_wali_kelas w ON w.guru_id = g.id
        JOIN t_kelas k ON k.id = w.kelas_id
        JOIN t_tahun_ajaran ta ON ta.id = w.tahun_ajaran_id
        WHERE g.user_id = ?
          AND g.status_aktif = 1
          AND w.status_aktif = 1
          AND k.status_aktif = 1
          AND ta.status_aktif = 1
        LIMIT 1
    ";

    $stmtWali = mysqli_prepare($koneksi, $queryWali);

    if ($stmtWali) {
        mysqli_stmt_bind_param($stmtWali, "i", $idUser);
        mysqli_stmt_execute($stmtWali);

        $hasilWali = mysqli_stmt_get_result($stmtWali);

        if ($dataWali = mysqli_fetch_assoc($hasilWali)) {
            $isWaliKelas = true;
            $namaKelasWali = $dataWali['nama_kelas'];
            $kelasWali = (int) $dataWali['kelas_id'];
            $tahunWali = (int) $dataWali['tahun_ajaran_id'];
        }

        mysqli_stmt_close($stmtWali);
    }
}
