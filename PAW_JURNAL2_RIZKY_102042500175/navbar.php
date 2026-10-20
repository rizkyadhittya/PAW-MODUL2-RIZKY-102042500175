<?php
$current_page = basename($_SERVER['PHP_SELF']);
$is_mk = in_array($current_page, ['daftar_mata_kuliah.php', 'tambah_mata_kuliah.php', 'detail_mata_kuliah.php', 'edit_mata_kuliah.php', 'hapus_mata_kuliah.php']);
$is_materi = in_array($current_page, ['daftar_materi.php', 'tambah_materi.php', 'detail_materi.php', 'edit_materi.php', 'hapus_materi.php']);
$is_tugas = in_array($current_page, ['daftar_tugas.php', 'tambah_tugas.php', 'detail_tugas.php', 'edit_tugas.php', 'hapus_tugas.php']);
?>
<nav class="navigasi">
    <a href="index.php" class="nav-brand">LMS EAD</a>
    <a href="index.php" class="nav-link <?= ($current_page === 'index.php') ? 'active' : '' ?>">Beranda</a>
    <a href="daftar_mata_kuliah.php" class="nav-link <?= $is_mk ? 'active' : '' ?>">Mata Kuliah</a>
    <a href="daftar_materi.php" class="nav-link <?= $is_materi ? 'active' : '' ?>">Materi</a>
    <a href="daftar_tugas.php" class="nav-link <?= $is_tugas ? 'active' : '' ?>">Tugas</a>
</nav>
