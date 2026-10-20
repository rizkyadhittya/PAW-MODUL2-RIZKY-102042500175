<?php
require 'koneksi.php';

// Hitung jumlah mata kuliah, materi, dan tugas dengan COUNT dari database
$res_mk = mysqli_query($conn, "SELECT COUNT(*) AS total FROM mata_kuliah");
$row_mk = mysqli_fetch_assoc($res_mk);
$total_mk = $row_mk['total'] ?? 0;

$res_mat = mysqli_query($conn, "SELECT COUNT(*) AS total FROM materi");
$row_mat = mysqli_fetch_assoc($res_mat);
$total_mat = $row_mat['total'] ?? 0;

$res_tug = mysqli_query($conn, "SELECT COUNT(*) AS total FROM tugas");
$row_tug = mysqli_fetch_assoc($res_tug);
$total_tug = $row_tug['total'] ?? 0;

// Ambil daftar mata kuliah yang tersedia
$res_daftar_mk = mysqli_query($conn, "SELECT id, kode, nama FROM mata_kuliah ORDER BY kode ASC");

// Ambil pesan notifikasi jika ada
$pesan = $_SESSION['pesan'] ?? ($_GET['pesan'] ?? '');
unset($_SESSION['pesan']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main>
    <?php require 'navbar.php'; ?>

    <p class="label">PRAKTIKUM PAW / JURNAL 2</p>
    <h1>Learning Management System</h1>
    <p>Pengelolaan mata kuliah, materi pembelajaran, dan tugas.</p>

    <?php if ($pesan): ?>
        <p class="pesan"><?= e($pesan) ?></p>
    <?php endif; ?>

    <!-- Ringkasan Statistik -->
    <div class="kartu-ringkasan">
        <div class="kartu">
            <div class="judul-kartu">Mata kuliah</div>
            <div class="angka"><?= $total_mk ?></div>
            <a href="daftar_mata_kuliah.php" class="btn-aksi">Kelola &rarr;</a>
        </div>
        <div class="kartu">
            <div class="judul-kartu">Materi</div>
            <div class="angka"><?= $total_mat ?></div>
            <a href="daftar_materi.php" class="btn-aksi">Kelola &rarr;</a>
        </div>
        <div class="kartu">
            <div class="judul-kartu">Tugas</div>
            <div class="angka"><?= $total_tug ?></div>
            <a href="daftar_tugas.php" class="btn-aksi">Kelola &rarr;</a>
        </div>
    </div>

    <!-- Mata Kuliah Tersedia -->
    <div class="kartu-detail">
        <h2>Mata kuliah tersedia</h2>
        <?php if (mysqli_num_rows($res_daftar_mk) > 0): ?>
            <ul>
                <?php while ($mk = mysqli_fetch_assoc($res_daftar_mk)): ?>
                    <li>
                        <a href="detail_mata_kuliah.php?id=<?= $mk['id'] ?>">
                            <strong><?= e($mk['kode']) ?></strong> - <?= e($mk['nama']) ?>
                        </a>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>Belum ada mata kuliah yang terdaftar.</p>
        <?php endif; ?>
    </div>

    <div style="margin-top: 24px;">
        <a class="tombol" href="daftar_mata_kuliah.php">Kelola Mata Kuliah</a>
        <a class="tombol tombol-outline" href="daftar_materi.php">Kelola Materi</a>
        <a class="tombol tombol-outline" href="daftar_tugas.php">Kelola Tugas</a>
    </div>
</main>
</body>
</html>
