
<form action="hapus_siswa.php" method="POST" class="d-inline"
      onsubmit="return confirm('Yakin ingin menghapus data siswa ini?')">
    <input type="hidden" name="id"
           value="<?php echo (int) $siswa['id']; ?>">
    <button type="submit" class="btn btn-sm btn-danger mb-1">
        Hapus
    </button>
</form>

